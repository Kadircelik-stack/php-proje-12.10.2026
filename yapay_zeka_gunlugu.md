# MARTELLO Çevrimiçi Müzayede ve Ekspertiz Platformu
# Yapay Zeka Mühendislik Günlüğü: Mimari Eksikliklerin Giderilmesi ve Güvenlik Sıkılaştırması

**Proje:** MARTELLO Çevrimiçi Müzayede ve Ekspertiz Platformu  
**Standart Referansları:** IEEE Std 830-1998, ISO/IEC/IEEE 29148:2018, OWASP Top 10 (2021), MITRE ATT&CK Enterprise  
**Mimari:** Native PHP 8.2+, JSON DataStore (`flock` & ACID), GoF Tasarım Desenleri (Observer & Strategy), 5 Rol RBAC  
**Belge Kapsamı:** İlk geliştirme fazı sonrasında yapılan teknik denetimde tespit edilen mimari, fonksiyonel ve siber güvenlik eksikliklerinin mühendislik çözümleri  

---

## 1. Yönetici Özeti

Bu mühendislik günlüğü; MARTELLO platformunun **Post-Implementation (Uygulama Sonrası) Mimari ve Güvenlik Denetimi** sürecini belgelemektedir. Denetim kapsamında sistemde tespit edilen güvenlik zafiyetleri, istemci-sunucu senkronizasyon eksiklikleri, yetkilendirme boşlukları ve statik veri bağımlılıkları; yazılım mühendisliği prensiplerine uygun olarak giderilmiş ve NFR (Fonksiyonel Olmayan Gereksinimler) birim testleriyle doğrulanmıştır.

---

## 2. Tespit Edilen Mimari / Güvenlik Eksiklikleri ve Uygulanan Çözümler

### 2.1. Veri Katmanı İzolasyonu ve Doğrudan Dosya Erişimi Koruması (MITRE T1213 / OWASP A01)

* **Tespit Edilen Eksiklik:**  
  Front-Controller (`index.php`) üzerindeki yerleşik yönlendirici (router), dosya sisteminde var olan herhangi bir yolu (`is_file($requestedFile)`) doğrudan sunucuya teslim edecek şekilde yapılandırılmıştı. Bu durum, `/storage/martello_datastore.json` ve `/storage/users_auth.json` veri depoları ile `/src` ve `/tests` dizinlerinin HTTP üzerinden doğrudan okunabilmesine yol açıyordu.
* **Teknik Risk:**  
  Veri deposunun doğrudan indirilmesi; kullanıcı kayıtlarının, parola özetlerinin (`bcrypt`), cüzdan bakiyelerinin ve denetim izlerinin ifşa olması (**CWE-538 / OWASP A01:2021 Broken Access Control**).
* **Uygulanan Mühendislik Çözümü (`index.php`):**
  1. `/storage`, `/src`, `/tests`, `/tools`, `/vendor`, `/views`, `/vs-tests`, `/docs` dizinlerine ve `.json`, `.lock`, `.md`, `.xml`, `.bat`, `.sh`, `.ps1`, `.phar`, `.sln` uzantılı dosyalara gelen doğrudan HTTP istekleri yönlendirici seviyesinde yakalanarak `HTTP 403 Forbidden` ile engellendi.
  2. Statik varlık sunumu, `realpath()` dizin doğrulaması (Path Traversal koruması) ile yalnızca `/public/` dizini altındaki izin verilen medya, stil ve betik uzantılarıyla (`.css`, `.js`, `.jpg`, `.png`, `.webp`, `.svg`, `.ico`, `.woff2`) sınırlandırıldı.

---

### 2.2. Sunucu Tarafı Durum Değiştirici İşlemler İçin Zorunlu RBAC Yetki Matrisi (MITRE T1548 / OWASP A01)

* **Tespit Edilen Eksiklik:**  
  Rol Tabanlı Erişim Denetimi (RBAC) yalnızca görünüm yönlendirmelerinde (`GET ?role=...`) uygulanıyor; ancak durum değiştirici `POST` komutlarını işleyen `WebAndApiController::handlePostActions()` metodunda çağrıyı yapan oturumun rolü sunucu tarafında doğrulanmıyordu.
* **Teknik Risk:**  
  Standart yetkili bir kullanıcının veya oturum açmamış bir istemcinin, özel hazırlanmış `POST` istekleriyle (`action=update_commissions`, `action=void_auction`, `action=moderate_listing`) dikey yetki yükseltmesi (**Privilege Escalation - CWE-285**) gerçekleştirebilmesi.
* **Uygulanan Mühendislik Çözümü (`src/Controllers/WebAndApiController.php`):**
  1. `enforceActionRbac(string $action)` metodu geliştirilerek tüm `POST` aksiyonları sunucu tarafı zorunlu yetki matrisine bağlandı:
     - **`ADMIN` Yetki Grubu:** `update_commissions`, `moderate_listing`, `cancel_bid`, `void_auction`, `edit_lot`, `configure_lot_schedule`, `resolve_dispute`, `admin_create_user`, `admin_toggle_user`, `admin_change_role`, `update_exchange_rates`, `send_official_notice`.
     - **`SATICI` / `ADMIN` Yetki Grubu:** `create_listing`, `cancel_listing`, `withdraw_funds`, `update_seller_profile`.
     - **`EKSPERTIZ` / `ADMIN` Yetki Grubu:** `submit_appraisal`, `request_appraisal_revision`, `escalate_appraisal`, `submit_dispute_opinion`, `update_appraiser_profile`.
     - **`DESTEK` / `ADMIN` Yetki Grubu:** `reply_support_ticket`, `assign_support_ticket`, `escalate_support_ticket`, `close_support_ticket`.
     - **Kimliği Doğrulanmış Oturum Grubu:** `place_bid`, `set_proxy_bid`, `toggle_watchlist`, `confirm_delivery`, `process_card_payment`, `delete_card`, `update_profile`, `create_dispute`, `vip_request_limit`, `vip_reserve_phone`, `create_support_ticket`, `send_live_chat`.
  2. Yetkisiz her `POST` girişimi iş mantığına ulaşmadan reddedilerek WORM denetim günlüklerine `RBAC_YETKISIZ_POST_ENGELLENDI` olayı olarak kaydedildi.

---

### 2.3. Güvensiz Doğrudan Nesne Referansı (IDOR) Önleme ve Oturum Kimliği Bağlama (MITRE T1078 / OWASP A01)

* **Tespit Edilen Eksiklik:**  
  Pey sürme, otomatik teklif tanımlama, kayıtlı kart silme, profil güncelleme ve ilan oluşturma formlarında işlem yapılacak kullanıcı kimliği, istemciden gelen gizli form alanlarından (`$_POST['user_id']`, `$_POST['seller_id']`, `$_POST['expert_id']`) okunuyordu.
* **Teknik Risk:**  
  İstemci tarafında `user_id` parametresinin manipüle edilmesiyle başka bir kullanıcı adına finansal işlem veya veri değişikliği yapılabilmesi (**Insecure Direct Object Reference - CWE-639**).
* **Uygulanan Mühendislik Çözümü (`src/Controllers/WebAndApiController.php` & `views/*/workspace.php`):**
  1. `bindSessionIdentity(array $post)` metodu ile sunucu tarafında oturum kimliği zorlaması getirildi; aktif kullanıcı `ADMIN` değilse istemciden gönderilen `user_id`, `seller_id` ve `expert_id` değerleri yok sayılarak doğrudan `(int)$_SESSION['auth_user']['id']` değeri atandı.
  2. Alıcı (`views/buyer/workspace.php`), Satıcı (`views/seller/workspace.php`) ve Ekspertiz (`views/appraiser/workspace.php`) çalışma alanlarındaki sabit (hardcoded) kullanıcı referansları kaldırılarak tüm veri sorguları aktif oturum kimliğine bağlandı.

---

### 2.4. Oturum Güvenliği, CSRF Savunması ve Sabit Zamanlı Kimlik Doğrulama (MITRE T1110 & T1185 / OWASP A07)

* **Tespit Edilen Eksiklik:**  
  - Oturum başlatılırken çerez güvenlik bayrakları (`HttpOnly`, `SameSite`, `use_strict_mode`) tanımlanmamış ve kimlik doğrulama sonrasında oturum kimliği yenilenmemişti (`session_regenerate_id`).
  - Durum değiştirici `POST` isteklerinde CSRF token ve `Origin`/`Referer` doğrulaması bulunmuyordu.
  - `UserAuthStore::authenticate()` metodunda kayıtlı olmayan e-posta adresleri için `password_verify()` adımı atlanarak erken dönüş yapılıyor ve ardışık hatalı giriş denemelerine sınır uygulanmıyordu.
* **Teknik Risk:**  
  Oturum sabitleme (**Session Fixation - CWE-384**), Siteler Arası İstek Sahteciliği (**CSRF - CWE-352**), yanıt süresi analiziyle kullanıcı numaralandırma (**Timing Attack User Enumeration - CWE-208**) ve kaba kuvvet parola saldırıları (**Brute-Force - CWE-307**).
* **Uygulanan Mühendislik Çözümü (`index.php` & `src/Services/UserAuthStore.php`):**
  1. `session_start()` yapılandırması `cookie_httponly => true`, `cookie_samesite => 'Lax'`, `use_strict_mode => true` parametreleriyle sıkılaştırıldı; giriş, kayıt ve çıkış adımlarında `session_regenerate_id(true)` zorunlu kılındı.
  2. Oturum bazlı 256-bit kriptografik `csrf_token` üretimi, `hash_equals()` doğrulaması ve `Origin`/`Referer` Same-Origin kontrolü eklendi.
  3. `UserAuthStore::authenticate()` içerisine sabit zamanlı (`constant-time`) sahte bcrypt doğrulaması (`password_verify($password, $dummyHash)`) ve **5 başarısız giriş denemesinde 15 dakika (900 sn) hesap kilitleme (Account Lockout)** mekanizması entegre edildi.

---

### 2.5. Merkezi WAF Sanitizasyon Katmanı ve Çok Katmanlı İkili Dosya Doğrulama (MITRE T1190 & T1505.003 / OWASP A03 & A04)

* **Tespit Edilen Eksiklik:**  
  İstemci girdileri için merkezi bir ön-temizleme katmanı bulunmuyor; ürün görseli yükleme noktasında ise yalnızca MIME tipine bakılarak görüntü başlığı arkasına gizlenmiş betik yükleri (Polyglot Web Shell) kontrol edilmiyordu. Ayrıca `vip_request_limit` aksiyonunda talep edilen limit tutarı sunucu tarafı bir beyaz listeyle sınırlandırılmamıştı.
* **Teknik Risk:**  
  Depolanmış XSS (**Stored XSS - CWE-79**), NoSQL/JSON operatör enjeksiyonu, zararlı dosya yükleme yoluyla uzaktan kod çalıştırma (**Unrestricted File Upload - CWE-434**) ve iş mantığı manipülasyonu (**Business Logic Bypass - CWE-840**).
* **Uygulanan Mühendislik Çözümü (`src/Controllers/WebAndApiController.php` & `index.php`):**
  1. `sanitizePostPayload(array $input)` WAF katmanı ile tüm `POST` verilerindeki Null-Byte (`\0`), kontrol karakterleri, `$` veya `__proto__` operatör anahtarları ve `<script>`, `javascript:`, `onerror=`, `onload=` vektörleri merkezi olarak temizlendi.
  2. Dosya yükleme modülüne 3 katmanlı doğrulama eklendi: `finfo_file()` MIME kontrolü + `getimagesize()` ile gerçek piksel ve `IMAGETYPE_JPEG/PNG/WEBP` Magic Bytes doğrulaması + dosya ikili içeriğinde `<?php`, `<?=`, `<script`, `eval(`, `base64_decode` imza taraması.
  3. VIP teklif limiti (`vip_request_limit`) sunucu tarafında yalnızca izin verilen kurumsal kademelerle (`2.000.000 TL`, `3.500.000 TL`, `5.000.000 TL`, `10.000.000 TL`) sınırlandırıldı ve tüm HTTP yanıtlarına `X-Content-Type-Options: nosniff`, `X-Frame-Options: SAMEORIGIN`, `Referrer-Policy` güvenlik başlıkları eklendi.

---

### 2.6. İstemci-Sunucu Arası Asenkron Durum Senkronizasyonu (Non-Blocking AJAX Polling & NFR-20 Bellek Yönetimi)

* **Tespit Edilen Eksiklik:**  
  Canlı müzayede ekranında başka bir katılımcı pey verdiğinde veya otomatik teklif (Proxy Bidding) tetiklendiğinde; güncel en yüksek teklif, asgari yeni pey adımı ve Kademeli Anti-Sniping uzatma sayaçları yalnızca tam sayfa yenilemesiyle (Full Page Reload) güncelleniyordu.
* **Teknik Risk / Kısıt:**  
  Gerçek zamanlı açık artırma akışında istemcinin güncel fiyatı gecikmeli görmesi nedeniyle geçersiz pey çakışmalarının artması.
* **Uygulanan Mühendislik Çözümü (`index.php` & `public/assets/js/martello.js`):**
  1. `index.php` üzerinde hafifletilmiş `GET index.php?ajax=lot_status&lot_id={id}` JSON uç noktası oluşturularak ilgili lotun anlık fiyatı, döviz stratejisine göre biçimlendirilmiş tutarı, asgari bir sonraki pey adımı, Anti-Sniping uzatma sayısı ve son 5 teklif kaydı sunuldu.
  2. `public/assets/js/martello.js` içerisine NFR-20 bellek sızıntısı önleme standartlarına (`activeIntervalIds` yaşam döngüsü yönetimi ve 100 elemanlı sabit `RingBuffer`) tam uyumlu 4 saniyelik asenkron `fetch()` yoklama mekanizması entegre edildi.

---

### 2.7. Durumsal Veri Bağlama (Stateful Data Binding), Modüler Sekme İzolasyonu ve Kriptografik Belge Doğrulama

* **Tespit Edilen Eksiklik:**  
  - Alıcı panelindeki `Takip Ettiklerim` tablosu ile `Genel Bakış` metrikleri ve Satıcı panelindeki `Teklif Geçmişi` tablosu veritabanı koleksiyonları yerine statik örnek satırları gösteriyordu.
  - Satıcı panelinde kapanmamış bir DOM kapsayıcısı nedeniyle `Profil & Mağaza` modülü görüntülenemiyor; Canlı Destek paneli ise sekme izolasyonu yerine tek sayfa kaydırma (`#anchor`) yapısıyla çalışıyordu.
  - Sistemdeki tüm kullanıcı hesapları için `wallets` koleksiyonunda başlangıç cüzdan kaydı bulunmuyordu.
* **Uygulanan Mühendislik Çözümü (`views/*/workspace.php`, `src/Services/PlatformWorkflowService.php`, `storage/martello_datastore.json`):**
  1. Alıcı panelinde `Takip Ettiklerim` ve KPI metrikleri `$db['watchlists']`, `$db['bids']` ve `$db['orders']` koleksiyonlarına; Satıcı panelinde `Teklif Geçmişi` ve KPI metrikleri `$db['bids']`, `$db['lots']` ve `$db['listings']` koleksiyonlarına dinamik olarak bağlandı.
  2. `PlatformWorkflowService::getHistoryStatistics()` metodunun döndürdüğü anahtar kümesi ile görünüm katmanı senkronize edildi; tüm aktif kullanıcılar için `wallets` kayıtları tamamlandı.
  3. Satıcı panelindeki DOM kapsayıcı hatası giderildi ve Canlı Destek çalışma alanı 4 bağımsız modüler sekmeye (`Bilet Havuzu`, `Canlı Sohbet`, `İtiraz & İhtilaf`, `SSS & Rehber`) ayrıştırıldı.
  4. Ekspertiz sertifikası ve resmi ihtarname çıktılarına SHA-256 veri bütünlüğü doğrulama rozeti ve doğrudan belge yazdırma/PDF üretimi (`window.print()`) yeteneği kazandırıldı.

---

## 3. Doğrulama ve Test Sonuçları

Uygulanan tüm güvenlik ve mimari iyileştirmeler sonrasında:
* **Sözdizimi ve Statik Doğrulama:** Değiştirilen tüm çekirdek servis, denetleyici ve görünüm dosyaları `php -l` denetiminden **0 hata** ile geçmiştir.
* **SRS NFR Birim Test Paketi (PHPUnit 10.5):** Performans (NFR1–3), Eşzamanlılık ve ACID Kilitleme (NFR4–5), Yük/Kapasite (NFR6–8), PCI-DSS/WORM/KVKK/OWASP Güvenlik (NFR9–19), İstemci Bellek Yönetimi (NFR20), Arşivleme ve VIP İzolasyonu (NFR21–22), Kademeli Anti-Sniping, Otomatik Teklif ve PII Maskeleme (NFR23–25) testlerinin tamamı çalıştırılmış ve **25/25 Test (%100 Başarı, 717 Assertion)** ile doğrulanmıştır.
