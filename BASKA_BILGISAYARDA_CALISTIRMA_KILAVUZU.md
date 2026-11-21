# MARTELLO Çevrimiçi Müzayede ve Ekspertiz Platformu
## Başka Bir Bilgisayarda Çalıştırma ve Kurulum Kılavuzu

Bu belge, **MARTELLO** platformunu herhangi bir başka bilgisayarda (Windows, macOS veya Linux) sıfır konfigürasyonla ve en hızlı şekilde çalıştırmak için gerekli tüm adımları ve yöntemleri içerir.

---

### 🌟 Önemli Mimari Avantajı: Harici Veritabanı Gerekmez!
MARTELLO platformu **Pure Native PHP 8.2+** ve **ACID Destekli JSON Veri Deposu (`storage/martello_datastore.json`)** ile çalışmaktadır.
* ❌ **MySQL, MariaDB, PostgreSQL veya SQL Server kurmanıza GEREK YOKTUR.**
* ❌ **`composer install` çalıştırmanıza GEREK YOKTUR** (Yerleşik PSR-4 autoloader `src/autoload.php` mevcuttur).
* ✅ Proje klasörünü kopyalayıp çalıştırmanız yeterlidir. Tüm kullanıcılar, müzayedeler, peyler, emanet hesapları ve siparişler JSON veri deposunda anında hazırdır.

---

## 🚀 1. YÖNTEM: Windows'ta Tek Tıkla Çalıştırma (En Kolay)

1. Proje klasöründeki **`baslat.bat`** dosyasına çift tıklayın.
2. Betik bilgisayarınızdaki ortamı otomatik tarar:
   - Sistem PATH üzerinde PHP varsa onu kullanır.
   - WAMP (`C:\wamp64`), XAMPP (`C:\xampp`), Laragon (`C:\laragon`) veya `C:\php` varsa otomatik algılar.
   - **Eğer bilgisayarınızda PHP hiç kurulu değilse:** Betik size sorarak resmi taşınabilir (portable) PHP 8.2 sürümünü 10 saniyede otomatik indirir ve hiçbir kurulum/ayar gerektirmeden çalıştırır.
3. Otomatik olarak tarayıcınızda açılır:
   👉 **`http://localhost:8000`**

---

## 📦 2. YÖNTEM: GitHub Üzerinden İndirip Çalıştırma

Hedef bilgisayarda komut satırını (Terminal veya PowerShell) açın:

```bash
# 1. Projeyi klonlayın:
git clone https://github.com/Kadircelik-stack/php-proje-12.10.2026.git martello

# 2. Klasöre girin:
cd martello

# 3. Windows için:
baslat.bat

# Veya macOS / Linux için:
chmod +x start.sh
./start.sh
```

Tarayıcınızda `http://localhost:8000` adresine girin.

---

## 🐳 3. YÖNTEM: Docker ile Tek Komutla Çalıştırma

Bilgisayarınızda Docker ve Docker Desktop kuruluysa, sıfır PHP kurulumu ile anında çalıştırabilirsiniz:

```bash
# Proje ana dizininde:
docker compose up -d
```

* Sunucu ayağa kalkar: **`http://localhost:8000`**
* Kapatmak için: `docker compose down`

---

## 🌐 4. YÖNTEM: WAMP / XAMPP / Laragon İçine Atarak Çalıştırma

Eğer hedef bilgisayarda hazır bir web sunucu paketi varsa:

1. **WAMP kullanıyorsanız:**
   - Proje klasörünü `C:\wamp64\www\martello` içine yapıştırın.
   - Tarayıcıda açın: `http://localhost/martello`
2. **XAMPP kullanıyorsanız:**
   - Proje klasörünü `C:\xampp\htdocs\martello` içine yapıştırın.
   - Tarayıcıda açın: `http://localhost/martello`
3. **Laragon kullanıyorsanız:**
   - Proje klasörünü `C:\laragon\www\martello` içine yapıştırın.
   - Tarayıcıda açın: `http://martello.test` veya `http://localhost/martello`

---

## 🍎 5. YÖNTEM: macOS veya Linux'ta Çalıştırma

Terminali açın ve proje klasöründe şu komutları çalıştırın:

```bash
chmod +x start.sh
./start.sh
```

Veya doğrudan yerleşik PHP ile:

```bash
php -S 0.0.0.0:8000 index.php
```

Tarayıcınızda açın: **`http://localhost:8000`**

*(Eğer PHP kurulu değilse: Ubuntu/Debian: `sudo apt install php-cli`, macOS: `brew install php`)*

---

## 💾 Projeyi USB Belleğe veya Başka Bilgisayara Aktarma (Paketleme)

Proje klasöründeki **`paketi_hazirla.bat`** dosyasına çift tıklayarak gereksiz `.git` geçmişi olmadan temiz ve hafif bir **`MARTELLO_PROJE_PAKETI.zip`** arşivi oluşturabilirsiniz.
Bu ZIP dosyasını dilediğiniz bilgisayara kopyalayıp zipten çıkararak `baslat.bat` ile çalıştırabilirsiniz.

---

## 🔑 Hazır Test Hesapları ve Hızlı Erişim Linkleri

Sistemde 5 farklı rol için tüm roller ve yetkiler tanımlıdır. Parola tüm hesaplar için varsayılan olarak **`123456`**'dır:

| Rol | Kullanıcı Adı | E-Posta | Hızlı Doğrudan Erişim Linki |
| :--- | :--- | :--- | :--- |
| 👑 **Yönetici (Admin)** | Ahmet Yılmaz | `admin@muzayede.com` | `http://localhost:8000/?role=admin` |
| 🛡️ **Alıcı (Buyer)** | Emre Kaya | `zeynep@koleksiyon.com` | `http://localhost:8000/?role=buyer` |
| 🏷️ **Satıcı (Seller)** | Mehmet Kaya | `mehmet@antik.com` | `http://localhost:8000/?role=seller` |
| 🔍 **Ekspertiz Uzmanı** | Burak Şahin | `burak@ekspertiz.com` | `http://localhost:8000/?role=appraiser` |
| 🎧 **Canlı Destek / Müşteri Hizmetleri** | Elif Çelik | `elif@destek.com` | `http://localhost:8000/?role=support` |

---

## 🛠️ Sistem Gereksinimleri ve Sorun Giderme

- **PHP Sürümü:** PHP 8.1, 8.2 veya 8.3 (Önerilen: 8.2+).
- **Gerekli PHP Uzantıları:** `json`, `mbstring`, `openssl` (Tüm standart PHP dağıtımlarında varsayılan olarak açıktır).
- **Port 8000 Meşgulse:** Komut satırında alternatif port belirtebilirsiniz:
  ```bash
  php -S localhost:8080 index.php
  ```
  Ardından `http://localhost:8080` adresinden erişebilirsiniz.
- **Dosya İzinleri (Linux/macOS):** `storage/` klasörünün yazma iznine sahip olduğundan emin olun:
  ```bash
  chmod -R 777 storage
  ```
