# MARTELLO — Güvenlik Denetimi, MITRE ATT&CK & Enjeksiyon Analizi ve İyileştirme Raporu

> [!IMPORTANT]
> Bu rapor, **MARTELLO Çevrimiçi Müzayede ve Ekspertiz Platformu** kaynak kodları üzerinde gerçekleştirilen kapsamlı güvenlik taraması sonucunda tespit edilen zafiyetleri, bunların **MITRE ATT&CK** ve **OWASP Top 10** karşılıklarını ve kod üzerinde uygulanan kalıcı savunma yamalarını **"Önceden Böyleydi → Şu Tehlikeye Sebep Olabilirdi → Şu Şekilde Değiştirildi"** formatında belgelemektedir.

---

## Özet Güvenlik Matrisi

| # | Güvenlik Alanı / Bulgu | MITRE ATT&CK Tekniği | OWASP Top 10 Kategorisi | İlgili Dosya | Durum |
| :--- | :--- | :--- | :--- | :--- | :--- |
| **1** | Statik Sunucuda `storage/*.json` İfşası ve Path Traversal | `T1083` File Discovery / `T1552.001` Credentials in Files | **A01:2021** Broken Access Control | [index.php](file:///C:/Users/sewal/OneDrive/Masaüstü/php-proje-12.10.2026/index.php#L4-L30) | **Kapatıldı** |
| **2** | POST İsteklerinde RBAC Atlatma ve Yetki Yükseltme | `T1548` Abuse Elevation Control / `T1078` Valid Accounts | **A01:2021** Broken Access Control | [WebAndApiController.php](file:///C:/Users/sewal/OneDrive/Masaüstü/php-proje-12.10.2026/src/Controllers/WebAndApiController.php#L75-L128) | **Kapatıldı** |
| **3** | IDOR (`user_id` Manipülasyonu) ve Keyfi VIP Limit Artırımı | `T1565.002` Transmitted Data Manipulation | **A01:2021** IDOR / **A04:2021** Insecure Design | [WebAndApiController.php](file:///C:/Users/sewal/OneDrive/Masaüstü/php-proje-12.10.2026/src/Controllers/WebAndApiController.php#L130-L159) | **Kapatıldı** |
| **4** | XSS, CRLF Log Injection ve JSON/NoSQL Operatör Enjeksiyonu | `T1059.007` JavaScript / `T1190` Exploit Application | **A03:2021** Injection | [WebAndApiController.php](file:///C:/Users/sewal/OneDrive/Masaüstü/php-proje-12.10.2026/src/Controllers/WebAndApiController.php#L37-L73) | **Kapatıldı** |
| **5** | Dosya Yüklemede MIME Spoofing ve Polyglot Betik Enjeksiyonu | `T1505.003` Web Shell / `T1190` Exploit Application | **A03:2021** Injection / **A08:2021** Integrity Failures | [WebAndApiController.php](file:///C:/Users/sewal/OneDrive/Masaüstü/php-proje-12.10.2026/src/Controllers/WebAndApiController.php#L334-L377) | **Kapatıldı** |
| **6** | Brute-Force, Timing Attack ve Kullanıcı Tespiti (Enumeration) | `T1110` Brute Force / `T1589` Gather Victim Identity | **A07:2021** Auth Failures | [UserAuthStore.php](file:///C:/Users/sewal/OneDrive/Masaüstü/php-proje-12.10.2026/src/Services/UserAuthStore.php#L267-L303) | **Kapatıldı** |
| **7** | Session Fixation, Çerez Bayrakları (`HttpOnly`) ve CSRF Koruması | `T1539` Steal Web Session Cookie / `T1189` Drive-by | **A01:2021** & **A07:2021** Auth Failures | [index.php](file:///C:/Users/sewal/OneDrive/Masaüstü/php-proje-12.10.2026/index.php#L97-L175) | **Kapatıldı** |
| **8** | HTTP Güvenlik Başlıkları (Clickjacking & MIME-Sniffing) | `T1557` Adversary-in-the-Middle / UI Redressing | **A05:2021** Security Misconfiguration | [index.php](file:///C:/Users/sewal/OneDrive/Masaüstü/php-proje-12.10.2026/index.php#L98-L105) | **Kapatıldı** |

---

## Detaylı Bulgular ve Yapılan Kod Değişiklikleri

### 1. Hassas Veri Deposu (`storage/*.json`) Doğrudan Erişim İfşası ve Path Traversal
- **MITRE ATT&CK:** `T1083` (File and Directory Discovery), `T1552.001` (Unsecured Credentials: Credentials In Files)
- **OWASP:** `A01:2021 — Broken Access Control`
- **Önceden Böyleydi ([index.php](file:///C:/Users/sewal/OneDrive/Masaüstü/php-proje-12.10.2026/index.php#L4-L30)):**
  PHP yerleşik sunucusu (`cli-server`) yönlendiricisinde yalnızca dosya uzantısının `.php` olup olmadığı kontrol ediliyordu:
  ```php
  if (php_sapi_name() === 'cli-server') {
      $urlPath = parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH);
      $filePath = __DIR__ . $urlPath;
      if ($urlPath !== '' && $urlPath !== '/' && file_exists($filePath) && !is_dir($filePath) && strtolower((string)pathinfo($filePath, PATHINFO_EXTENSION)) !== 'php') {
          return false;
      }
  }
  ```
- **Şu Tehlikeye Sebep Olabilirdi:**
  `return false;` ifadesi PHP sunucusuna dosyayı olduğu gibi tarayıcıya indirmesini söyler. Uzantısı `.php` olmadığı için, dışarıdan bir kullanıcı tarayıcı adres çubuğuna `/storage/users_auth.json` veya `/storage/martello_datastore.json` yazdığında tüm kullanıcıların parola hash'lerini, e-posta adreslerini, telefon numaralarını, cüzdan bakiyelerini ve özel teklif kayıtlarını ham JSON dosyası olarak indirebilirdi.
- **Şu Şekilde Değiştirildi:**
  `index.php` girişine dizin karalistesi (`/storage`, `/src`, `/tests`, `/vs-tests`, `/tools`, `/vendor`, `/.git`), uzantı karalistesi (`json`, `xml`, `ps1`, `bat`, `sln`, `md`, `log`, `lock`, `env`) ve `realpath()` tabanlı `public/` dizin sınırı doğrulaması eklendi:
  ```php
  foreach ($forbiddenPrefixes as $pref) {
      if (str_starts_with(strtolower($urlPath), $pref)) {
          http_response_code(403);
          exit('403 Forbidden: Hassas sistem dizinine dogrudan erisim engellendi.');
      }
  }
  if ($ext !== '' && in_array($ext, $forbiddenExts, true)) {
      http_response_code(403);
      exit('403 Forbidden: Hassas veri dosyasina dogrudan erisim engellendi.');
  }
  $publicRoot = realpath(__DIR__ . '/public');
  $realTarget = ($urlPath !== '' && $urlPath !== '/') ? realpath(__DIR__ . $urlPath) : false;
  if ($realTarget !== false && $publicRoot !== false && str_starts_with($realTarget, $publicRoot) && !is_dir($realTarget) && in_array($ext, $allowedStaticExts, true)) {
      return false;
  }
  ```

---

### 2. POST Eylemlerinde Rol Tabanlı Yetki (RBAC) Atlatma ve Yetki Yükseltme
- **MITRE ATT&CK:** `T1548` (Abuse Elevation Control Mechanism), `T1078` (Valid Accounts)
- **OWASP:** `A01:2021 — Broken Access Control`
- **Önceden Böyleydi ([WebAndApiController.php](file:///C:/Users/sewal/OneDrive/Masaüstü/php-proje-12.10.2026/src/Controllers/WebAndApiController.php#L75-L128)):**
  Sayfa görüntüleme (`GET ?role=admin`) isteklerinde RBAC kontrolü yapılmasına rağmen, `POST` istekleri doğrudan `$controller->handlePostAction($_POST)` metoduna düşüyor ve içeride işlemi yapan kullanıcının rolü kontrol edilmiyordu.
- **Şu Tehlikeye Sebep Olabilirdi:**
  Standart bir `ALICI` veya giriş yapmamış bir ziyaretçi, doğrudan `POST` isteğiyle `action=update_user_role_and_permissions` göndererek kendi hesabını `ADMIN` yapabilir, `action=admin_cancel_bid` ile rakiplerinin peylerini iptal edebilir, `action=toggle_ban` ile diğer kullanıcıları askıya alabilir veya `action=update_commission_definitions` ile platform komisyon oranlarını değiştirebilirdi.
- **Şu Şekilde Değiştirildi:**
  `WebAndApiController::enforceActionRbac()` metodu eklendi. 26 adet yönetici (`ADMIN`) aksiyonu, 4 adet eksper (`EKSPERTIZ`) aksiyonu ve 4 adet satıcı (`SATICI`) aksiyonu sunucu tarafında oturum rolüyle eşleştirildi. Yetkisiz bir rol `POST` isteği gönderdiğinde işlem reddedilmekte ve `auditLogs` koleksiyonuna `RBAC_YETKISIZ_POST_ENGELLENDI` koduyla IP adresi ve zaman damgasıyla kaydedilmektedir.

---

### 3. IDOR (Başkasının Kimliğiyle İşlem Yapma) ve Parametre Manipülasyonu
- **MITRE ATT&CK:** `T1565.002` (Data Manipulation: Transmitted Data Manipulation)
- **OWASP:** `A01:2021 — Broken Access Control (IDOR)`, `A04:2021 — Insecure Design`
- **Önceden Böyleydi ([WebAndApiController.php](file:///C:/Users/sewal/OneDrive/Masaüstü/php-proje-12.10.2026/src/Controllers/WebAndApiController.php#L130-L159)):**
  `place_bid`, `set_proxy_bid`, `delete_card`, `deposit_wallet`, `create_dispute`, `update_profile` ve `vip_request_limit` gibi işlemlerde kullanıcı kimliği doğrudan HTML formundaki gizli `<input type="hidden" name="user_id" value="3">` alanından (`$post['user_id']`) alınıyordu. Ayrıca `vip_request_limit` içinde talep edilen limit (`target_limit`) için kademe veya VIP statü doğrulaması yoktu.
- **Şu Tehlikeye Sebep Olabilirdi:**
  Tarayıcı geliştirici araçlarından (F12) `user_id` değerini `6` veya `8` olarak değiştiren bir kullanıcı, başkasının cüzdan bakiyesini kullanarak pey sürebilir, başkasının kayıtlı kredi kartını silebilir (`delete_card`) veya `target_limit=999999999` göndererek VIP akreditasyonu olmadan kendine sınırsız teklif limiti tanımlayabilirdi.
- **Şu Şekilde Değiştirildi:**
  1. `WebAndApiController::bindSessionIdentity()` metodu ile oturumdaki kullanıcı `ADMIN` değilse, formdan gelen `user_id`, `seller_id`, `expert_id`, `actor_id` ve `sender_id` parametreleri ezilerek doğrudan sunucu oturumundaki `$_SESSION['auth_user']['id']` kimliğine sabitlendi.
  2. `vip_request_limit` aksiyonuna yalnızca izin verilen 4 kademe (`[2000000.0, 3500000.0, 5000000.0, 10000000.0]`) ve kullanıcının gerçek `GOLD` / `PLATINUM` / `DIAMOND` VIP akreditasyonuna sahip olma zorunluluğu eklendi.

---

### 4. Stored XSS, CRLF Log Injection ve JSON/NoSQL Operatör Enjeksiyonları
- **MITRE ATT&CK:** `T1059.007` (Command and Scripting Interpreter: JavaScript), `T1190` (Exploit Public-Facing Application)
- **OWASP:** `A03:2021 — Injection`
- **Önceden Böyleydi ([WebAndApiController.php](file:///C:/Users/sewal/OneDrive/Masaüstü/php-proje-12.10.2026/src/Controllers/WebAndApiController.php#L37-L73)):**
  Çıktı katmanında `htmlspecialchars()` kullanılmasına rağmen, `POST` ile gelen metinler JSON veri deposuna (`martello_datastore.json`) ve denetim kayıtlarına (`auditLogs`) yazılmadan önce merkezi bir WAF girdi temizleme katmanından geçmiyordu.
- **Şu Tehlikeye Sebep Olabilirdi:**
  - Saldırgan tek satırlık alanlara `\r\n` (CRLF) karakterleri enjekte ederek denetim izi (`auditLogs`) kayıtlarında sahte log satırları üretebilirdi (Log Injection).
  - `$ne`, `$gt`, `__proto__`, `constructor` gibi JSON/NoSQL operatör anahtarları veya `<script>`, `javascript:`, `onerror=` vektörleri veri deposuna kirli veri olarak yazılabilirdi.
- **Şu Şekilde Değiştirildi:**
  `WebAndApiController::sanitizePostPayload()` metodu yazılarak `handlePostAction` girişinde tüm `$_POST` ağacı özyinelemeli (recursive) olarak temizlendi:
  - Null-byte (`\0`) karakterleri tamamen kaldırıldı,
  - `$ne`, `$gt`, `$where`, `__proto__`, `constructor` anahtarları bloklandı,
  - `<script>`, `<iframe>`, `<object>`, `<embed>`, `<svg>` etiketleri, `javascript:` / `data:` protokolleri ve `on[event]=` öznitelikleri giriş katmanında etkisizleştirildi.

---

### 5. Dosya Yüklemede MIME Spoofing, Polyglot Görsel ve Web Shell Enjeksiyonu
- **MITRE ATT&CK:** `T1505.003` (Server Software Component: Web Shell)
- **OWASP:** `A03:2021 — Injection`, `A08:2021 — Software and Data Integrity Failures`
- **Önceden Böyleydi ([WebAndApiController.php](file:///C:/Users/sewal/OneDrive/Masaüstü/php-proje-12.10.2026/src/Controllers/WebAndApiController.php#L334-L377)):**
  Satıcı panelindeki ürün görseli yükleme (`create_listing`) adımında yalnızca dosya adının uzantısına (`pathinfo($origName, PATHINFO_EXTENSION)`) ve dosya boyutuna (`<= 5 MB`) bakılıyordu.
- **Şu Tehlikeye Sebep Olabilirdi:**
  İçerisine çalıştırılabilir PHP (`<?php ... ?>`) veya HTML/JS kodu yazılmış sahte bir metin dosyası `antika_saat.jpg` adıyla yüklenebilirdi (MIME Spoofing / Polyglot File Upload).
- **Şu Şekilde Değiştirildi:**
  Görsel yükleme adımına **3 Katmanlı Doğrulama** eklendi:
  1. Uzantı Beyaz Listesi (`jpg`, `jpeg`, `png`, `webp`),
  2. `getimagesize($tmpPath)` ile dosyanın gerçek binary başlığının (`IMAGETYPE_JPEG`, `IMAGETYPE_PNG`, `IMAGETYPE_WEBP` Magic Bytes) ve geçerli piksel boyutlarının (`width > 0`, `height > 0`) doğrulanması,
  3. Dosya içeriğinin ilk 4 KB'lık bölümünde gömülü `<?php`, `<?=` veya `<script` imzası taraması.

---

### 6. Brute-Force (Kaba Kuvvet), Timing Attack ve Kullanıcı Tespiti (User Enumeration)
- **MITRE ATT&CK:** `T1110` (Brute Force), `T1589` (Gather Victim Identity Information)
- **OWASP:** `A07:2021 — Identification and Authentication Failures`
- **Önceden Böyleydi ([UserAuthStore.php](file:///C:/Users/sewal/OneDrive/Masaüstü/php-proje-12.10.2026/src/Services/UserAuthStore.php#L267-L303)):**
  1. E-posta bulunamadığında `password_verify()` hiç çağrılmadan anında `'E-posta adresi veya şifre hatalı.'` dönülüyor, e-posta bulunduğunda ise `password_verify()` çalıştırılıp `'Girdiğiniz şifre hatalı.'` mesajı dönülüyordu.
  2. Ardışık başarısız giriş denemelerine karşı herhangi bir sayaç veya geçici hesap kilitleme mekanizması yoktu.
- **Şu Tehlikeye Sebep Olabilirdi:**
  - Farklı hata mesajlarından ve yanıt süresi farkından (Timing Side-Channel) hangi e-posta adreslerinin sistemde kayıtlı olduğu tespit edilebilirdi (User Enumeration).
  - Otomatik bir bot saniyede yüzlerce şifre deneyerek (`T1110` Brute Force / Credential Stuffing) kullanıcı hesaplarını ele geçirebilirdi.
- **Şu Şekilde Değiştirildi:**
  1. E-posta bulunamadığında da sabit zamanlı dummy bcrypt doğrulaması (`password_verify($password, '$2y$10$...')`) çalıştırılarak zamanlama farkı kapatıldı ve hata mesajları tekilleştirildi (`'E-posta adresi veya şifre hatalı.'`).
  2. Hesap başına `hataliGirisSayisi` sayacı eklendi; 5 ardışık hatalı denemede hesap `kilitBitisZamani` ile 15 dakika süreyle otomatik olarak geçici kilitlenmektedir.

---

### 7. Session Fixation (Oturum Sabitleme), Çerez Bayrakları ve CSRF Koruması
- **MITRE ATT&CK:** `T1539` (Steal Web Session Cookie), `T1550.004` (Use Alternate Authentication Material: Web Session Cookie), `T1189` (Drive-by Compromise)
- **OWASP:** `A01:2021 — Broken Access Control (CSRF)`, `A07:2021 — Identification and Authentication Failures`
- **Önceden Böyleydi ([index.php](file:///C:/Users/sewal/OneDrive/Masaüstü/php-proje-12.10.2026/index.php#L97-L175)):**
  - `session_start()` varsayılan parametrelerle çağrılıyor, giriş/kayıt/çıkış işlemlerinde oturum kimliği (`session_regenerate_id`) yenilenmiyordu.
  - `POST` isteklerinde `Origin` / `Referer` veya `csrf_token` kontrolü yapılmıyordu.
- **Şu Tehlikeye Sebep Olabilirdi:**
  - **Session Fixation:** Saldırganın belirlediği bir `PHPSESSID` ile giriş yapan kullanıcının oturum kimliği değişmediği için oturumu çalınabilirdi.
  - **CSRF:** Kullanıcının tarayıcısında başka bir sekmede açık olan zararlı bir sayfa, kullanıcının haberi olmadan Martello'ya arka planda `POST` isteği göndererek pey sürebilir veya işlem yapabilirdi.
- **Şu Şekilde Değiştirildi:**
  1. `session_start(['cookie_httponly' => true, 'cookie_samesite' => 'Lax', 'use_strict_mode' => true])` ile oturum çerezinin JavaScript (`document.cookie`) tarafından okunması engellendi.
  2. `login`, `register` ve `logout` anında `session_regenerate_id(true)` çağrılarak eski oturum kimliği imha edildi.
  3. Tüm `POST` isteklerinde `HTTP_ORIGIN` / `HTTP_REFERER` alan adı doğrulaması ve kriptografik `$_SESSION['csrf_token']` (`hash_equals`) doğrulaması devreye alındı.

---

### 8. HTTP Güvenlik Başlıkları (Clickjacking ve MIME-Sniffing Kalkanı)
- **MITRE ATT&CK:** `T1557` (Adversary-in-the-Middle / UI Redressing)
- **OWASP:** `A05:2021 — Security Misconfiguration`
- **Önceden Böyleydi ([index.php](file:///C:/Users/sewal/OneDrive/Masaüstü/php-proje-12.10.2026/index.php#L98-L105)):**
  Sunucu yanıtlarında tarayıcı güvenlik politikası başlıkları gönderilmiyordu.
- **Şu Tehlikeye Sebep Olabilirdi:**
  Site başka bir alan adındaki görünmez bir `<iframe>` içine gömülerek kullanıcıya farkında olmadan "Pey Ver" veya "Teslim Aldım" butonuna tıklatılabilirdi (**Clickjacking / UI Redressing**).
- **Şu Şekilde Değiştirildi:**
  `index.php` yanıt katmanına aşağıdaki savunma başlıkları eklendi:
  ```php
  header('X-Content-Type-Options: nosniff');
  header('X-Frame-Options: SAMEORIGIN');
  header('Referrer-Policy: strict-origin-when-cross-origin');
  header('X-XSS-Protection: 1; mode=block');
  header('Permissions-Policy: geolocation=(), microphone=(), camera=()');
  ```
