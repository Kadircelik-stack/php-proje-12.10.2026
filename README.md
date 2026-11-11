# MARTELLO — Çevrimiçi Müzayede ve Ekspertiz Platformu (PHP Katmanlı Mimari & JSON Veri Deposu)

**Gümüşhane Üniversitesi — Yazılım Mühendisliği (ZeroDay Ekibi)**
* **Esra Yıldırım** — `2407231069`
* **Abdullah Kadir Çelik** — `2407231061`
* **Rümeysa Şevval Kandilci** — `2407231031`

---

## 1. Proje Özeti ve 2. Aşama Kapsamı

Bu depo, **Martello Açık Artırma Sitesi SRS Dokümanı** (IEEE Std 830-1998 & ISO/IEC/IEEE 29148:2018) kapsamında tanımlanan tüm fonksiyonel (**FR-01 – FR-37**), fonksiyonel olmayan (**NFR-01 – NFR-25**) ve risk (**R-01 – R-12**) gereksinimlerini **PHP 8+ Katmanlı Mimari (Layered Architecture)** ve **JSON Veri Deposu (`storage/martello_datastore.json`)** üzerinde eksiksiz olarak uygular:

1. **Sunum ve İstemci Katmanı (`views/` & `public/`):** Bileşen Tabanlı Mimari (CBSE) prensibiyle geliştirilmiş 5 rol çalışma alanı (**Alıcı, Satıcı, Ekspertiz Uzmanı, Canlı Destek, Admin**) ve yeniden kullanılabilir bileşenler (`LotCard`, `AuctionTimer`, `CurrentBidDisplay`, `BidHistory`, `EscrowStatus`, `CurrencySelector`).
2. **Güvenlik, RBAC ve Maskeleme Katmanı (`src/Middlewares/`):** 5 rol erişim kontrolü (**FR-05**), VIP Tier kontrolü (**NFR-14**), 50.000 eşzamanlı katılımcı kontenjan sınırı (**NFR-06**), Admin FIDO2/WebAuthn donanım tabanlı MFA (**NFR-18**) ve katılımcı kimliğini `Anonim-XYZ` olarak maskeleme (**NFR-25**).
3. **İş Mantığı ve Tasarım Desenleri Katmanı (`src/Services/` & `src/Patterns/`):**
   * **Strategy Deseni (`src/Patterns/Strategy/`):** `IBiddingRuleStrategy` (`LiveAuctionStrategy`, `VipAuctionStrategy`), `ICommissionStrategy` (`ArtAntiqueCommission`, `VehicleCommission`), `IPaymentStrategy` (`EscrowWalletStrategy`, `ThreeDSecurePosStrategy`), `ICurrencyStrategy` (`TryCurrencyStrategy`, `UsdCurrencyStrategy`, `EurCurrencyStrategy`).
   * **Observer Deseni (`src/Patterns/Observer/`):** `AuctionSubject` yayımcısı ve 7 somut gözlemci (`LiveBidScreenObserver`, `TimerSyncObserver`, `WatchlistObserver`, `EscrowReleaseObserver`, `NotificationObserver`, `OrderAndReminderObserver`, `AuditAndFraudObserver`).
   * **Çekirdek İş Kuralları:** Otomatik Teklif / Proxy Bidding (**Bölüm 2.6.1, NFR-24**), Son Saniye Uzatması / Anti-Sniping (`<30 sn` kala ilk 2 uzamada `+30 sn`, 3+ uzamada `+10 sn` — **FR-33, NFR-23**), Eşzamanlı Teklif Yarışı Karamsar Kilitleme (`flock(LOCK_EX)` — **Bölüm 2.6.3, NFR-04**).
4. **Veri Erişim ve JSON Veri Deposu Katmanı (`src/Core/JsonDataStore.php`, `src/Repositories/`, `storage/`):** Tam ACID Transaction (Atomicity, Consistency, Isolation, Durability) ve Rollback destekli 20 koleksiyonlu aktif JSON veri deposu (`storage/martello_datastore.json`), 1+ yıl soğuk arşiv deposu (`storage/martello_archive_store.json` — **NFR-21**), Argon2id parola hashleme (**NFR-19**), AES-256-GCM şifreleme (**NFR-12**) ve SHA-256 blok zincirlemeli WORM denetim izi (**FR-25, R-12**).

---

## 2. Kurulum ve Çalıştırma

### Windows (XAMPP veya Yerleşik PHP)
`baslat.bat` dosyasına çift tıklayın veya terminalde şu komutu çalıştırın:
```bash
php -S localhost:8000 index.php
```
Ardından tarayıcınızda `http://localhost:8000` adresini açın.

### macOS / Linux / GitHub Codespaces
```bash
./baslat.command
# veya
php -S localhost:8000 index.php
```

### 2. Aşama Otomatik Testlerini Çalıştırma
```bash
php tests/Stage2ArchitectureAndRulesTest.php
```
