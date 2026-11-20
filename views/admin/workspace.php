<?php
declare(strict_types=1);

/** @var callable $formatPrice */
/** @var array $db */

$users = $db['users'] ?? [];
$lots = $db['lots'] ?? [];
$reports = $db['appraisalReports'] ?? [];
$disputes = $db['disputes'] ?? [];
$listings = $db['listings'] ?? [];
$categories = $db['categories'] ?? [];
$auditLogs = $db['auditLogs'] ?? [];
$activeTab = $_GET['tab'] ?? 'konsol';
?>

<div class="app-container">
  <!-- Sol Menü (Sidebar) — SRS Sayfa 34 Alt Görsel ile Birebir Uyumlu -->
  <aside class="sidebar">
    <div>
      <div class="sidebar-section-title">Çalışma Alanı</div>
      <ul class="nav-menu">
        <li class="nav-menu-item <?= $activeTab === 'konsol' ? 'active' : '' ?>">
          <button type="button" data-tab="konsol" onclick="switchAdminTab('konsol', this)">
            Yönetim konsolu
          </button>
        </li>
        <li class="nav-menu-item <?= $activeTab === 'kategoriler' ? 'active' : '' ?>">
          <button type="button" data-tab="kategoriler" onclick="switchAdminTab('kategoriler', this)">
            Kategoriler & Nitelikler
          </button>
        </li>
        <li class="nav-menu-item <?= $activeTab === 'kullanicilar' ? 'active' : '' ?>">
          <button type="button" data-tab="kullanicilar" onclick="switchAdminTab('kullanicilar', this)">
            Kullanıcılar
          </button>
        </li>
        <li class="nav-menu-item <?= $activeTab === 'yetkiler' ? 'active' : '' ?>">
          <button type="button" data-tab="yetkiler" onclick="switchAdminTab('yetkiler', this)">
            Rol & Yetki Yönetimi (RBAC)
          </button>
        </li>
        <li class="nav-menu-item <?= $activeTab === 'moderasyon' ? 'active' : '' ?>">
          <button type="button" data-tab="moderasyon" onclick="switchAdminTab('moderasyon', this)">
            Müzayede & Lot Düzenleme
          </button>
        </li>
        <li class="nav-menu-item <?= $activeTab === 'ekspertiz' ? 'active' : '' ?>">
          <button type="button" data-tab="ekspertiz" onclick="switchAdminTab('ekspertiz', this)">
            Ekspertiz atama
          </button>
        </li>
        <li class="nav-menu-item <?= $activeTab === 'komisyon' ? 'active' : '' ?>">
          <button type="button" data-tab="komisyon" onclick="switchAdminTab('komisyon', this)">
            Komisyon oranları
          </button>
        </li>
        <li class="nav-menu-item <?= $activeTab === 'raporlar' ? 'active' : '' ?>">
          <button type="button" data-tab="raporlar" onclick="switchAdminTab('raporlar', this)">
            Raporlar
          </button>
        </li>
        <li class="nav-menu-item <?= $activeTab === 'ihtilaflar' ? 'active' : '' ?>">
          <button type="button" data-tab="ihtilaflar" onclick="switchAdminTab('ihtilaflar', this)">
            İhtilaflar
          </button>
        </li>
        <li class="nav-menu-item <?= $activeTab === 'denetim' ? 'active' : '' ?>">
          <button type="button" data-tab="denetim" onclick="switchAdminTab('denetim', this)">
            Denetim izi
          </button>
        </li>
        <li class="nav-menu-item <?= $activeTab === 'ayarlar' ? 'active' : '' ?>">
          <button type="button" data-tab="ayarlar" onclick="switchAdminTab('ayarlar', this)">
            Sistem ayarları
          </button>
        </li>
      </ul>
    </div>

    <!-- Sidebar Demo Rozeti -->
    
      <!-- 2. BÖLÜM: PAZAR YERİ SEÇENEKLERİ -->
      <div style="margin-top:16px;">
        <div class="sidebar-section-title">Pazar Yeri</div>
        <ul class="nav-menu">
          <li class="nav-menu-item">
            <a href="?role=vitrin">
              <span class="menu-icon"><svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="12" cy="12" r="10"/><polygon points="16.24 7.76 14.12 14.12 7.76 16.24 9.88 9.88 16.24 7.76"/></svg></span>
              Koleksiyonlar & Keşif
            </a>
          </li>
          <li class="nav-menu-item">
            <a href="?role=buyer&tab=canli">
              <span class="menu-icon"><svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg></span>
              Canlı Müzayedeler
            </a>
          </li>
          <li class="nav-menu-item">
            <button type="button" onclick="openInfoModal('categories')">
              <span class="menu-icon"><svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg></span>
              Ürün Kategorileri
            </button>
          </li>
          <li class="nav-menu-item">
            <button type="button" onclick="openInfoModal('about')">
              <span class="menu-icon"><svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/></svg></span>
              Hakkımızda
            </button>
          </li>
        </ul>
      </div>

      <!-- 3. BÖLÜM: MÜZAYEDE ŞARTLARI & GİZLİLİK SEÇENEKLERİ -->
      <div style="margin-top:16px;">
        <div class="sidebar-section-title">Şartlar & Gizlilik</div>
        <ul class="nav-menu">
          <li class="nav-menu-item">
            <button type="button" onclick="openInfoModal('terms')">
              <span class="menu-icon"><svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg></span>
              Müzayede Şartları
            </button>
          </li>
          <li class="nav-menu-item">
            <button type="button" onclick="openInfoModal('privacy')">
              <span class="menu-icon"><svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg></span>
              Gizlilik Politikası & KVK
            </button>
          </li>
          <li class="nav-menu-item">
            <button type="button" onclick="openInfoModal('help')">
              <span class="menu-icon"><svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="12" cy="12" r="10"/><path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg></span>
              Yardım Merkezi & SSS
            </button>
          </li>
          <li class="nav-menu-item">
            <a href="?role=support">
              <span class="menu-icon"><svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg></span>
              Canlı Destek Masası
            </a>
          </li>
        </ul>
      </div>

      <!-- 4. BÖLÜM: HIZLI ROL ERİŞİMİ -->
      <div style="margin-top:16px;">
        <div class="sidebar-section-title">Hızlı Panel Erişimi</div>
        <ul class="nav-menu" style="font-size:0.8rem;">
          <li class="nav-menu-item"><a href="?role=buyer" style="padding:7px 12px; font-size:0.8rem;">Alıcı Paneli</a></li>
          <li class="nav-menu-item"><a href="?role=seller" style="padding:7px 12px; font-size:0.8rem;">Satıcı Paneli</a></li>
          <li class="nav-menu-item"><a href="?role=appraiser" style="padding:7px 12px; font-size:0.8rem;">Ekspertiz Paneli</a></li>
          <li class="nav-menu-item"><a href="?role=support" style="padding:7px 12px; font-size:0.8rem;">Canlı Destek & Kargo</a></li>
          <li class="nav-menu-item"><a href="?role=admin" style="padding:7px 12px; font-size:0.8rem;">Admin Konsolu</a></li>
        </ul>
      </div>

    <div class="sidebar-demo-badge">
      <div style="font-weight:700; font-size:0.75rem; color:#d4af37; letter-spacing:0.5px;">MARTELLO / DEMO</div>
      <div style="font-size:0.7rem; color:#888; margin-top:2px;">İşlemler sunum amaçlıdır.</div>
    </div>
  </aside>

  <!-- Ana İçerik Alanı -->
  <main class="main-content">

    <!-- 1. TAB: YÖNETİM KONSOLU (media_1795014776297_d1945790.jpg ile Birebir) -->
    <div id="admTab_konsol" class="tab-content <?= $activeTab === 'konsol' ? 'active' : '' ?>">
      <div class="page-breadcrumb">ADMIN / ÇALIŞMA ALANI</div>
      <h1 class="page-heading">Yönetim konsolu</h1>
      <p class="page-subheading" style="margin-bottom:24px;">Seçkin müzayede deneyiminizi yönetin</p>

      <!-- KPI Grid: Kategoriler ve Temel Sayaçlar -->
      <div class="kpi-grid">
        <div class="kpi-card" style="cursor:pointer;" onclick="switchAdminTab('kategoriler', document.querySelector('[data-tab=\'kategoriler\']'))">
          <div class="kpi-label">KATEGORİLER</div>
          <div class="kpi-value" style="color:var(--gold-primary);"><?= count($categories) ?></div>
        </div>
        <div class="kpi-card highlight">
          <div class="kpi-label">AKTİF LOTLAR</div>
          <div class="kpi-value">6</div>
        </div>
        <div class="kpi-card">
          <div class="kpi-label">KULLANICILAR</div>
          <div class="kpi-value"><?= count($users) ?></div>
        </div>
        <div class="kpi-card">
          <div class="kpi-label">AÇIK İHTİLAFLAR</div>
          <div class="kpi-value">0</div>
        </div>
      </div>

      <!-- Yönetim Konsolu Kartı & Kategori Hızlı Geçiş -->
      <div class="panel-card" style="margin-bottom:16px;">
        <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:12px;">
          <div>
            <h2 class="panel-title" style="margin-bottom:4px;">Yönetim Konsolu ve Katalog Mimarisi</h2>
            <p style="font-size:0.85rem; color:var(--text-muted); margin:0;">
              Ürün kategorileri, alt kategoriler ve nitelik şablonlarını yukarı taşınan kategori masasından yönetebilirsiniz.
            </p>
          </div>
          <button type="button" class="btn-gold btn-sm" onclick="switchAdminTab('kategoriler', document.querySelector('[data-tab=\'kategoriler\']'))" style="padding:8px 16px; cursor:pointer;">
            Kategoriler & Şablonlar Masasına Git →
          </button>
        </div>
      </div>
    </div>

    <!-- 2. TAB: ÜRÜN KATEGORİLERİ, ALT KATEGORİLER VE NİTELİK ŞABLONLARI (YUKARI TAŞINDI) -->
    <div id="admTab_kategoriler" class="tab-content <?= $activeTab === 'kategoriler' ? 'active' : '' ?>">
      <div class="page-breadcrumb">ADMIN / ÇALIŞMA ALANI / KATEGORİ & NİTELİK ŞABLONLARI</div>
      <div style="display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:20px; flex-wrap:wrap; gap:10px;">
        <div>
          <h1 class="page-heading">Ürün Kategorileri ve Nitelik Şablonları</h1>
          <p class="page-subheading" style="color:var(--gold-primary); font-weight:500; font-size:0.92rem;">
            ⚡ "Admin tarafından ürün kategorilerinin, alt kategorilerinin ve her kategoriye özgü zorunlu/seçimli nitelik şablonlarının tanımlanmasıdır."
          </p>
        </div>
        <span class="badge badge-gold" style="font-size:0.78rem; padding:6px 14px;">SRS Kategori Mimarisi</span>
      </div>

      <!-- Kategori İstatistik Kartları -->
      <?php
        $mainCategories = array_values(array_filter($categories, fn($c) => empty($c['ustKategoriId'])));
        $subCategories = array_values(array_filter($categories, fn($c) => !empty($c['ustKategoriId'])));
      ?>
      <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(200px, 1fr)); gap:12px; margin-bottom:24px;">
        <div style="background:#090d14; border:1px solid #1f2736; border-left:3px solid var(--gold-primary); border-radius:8px; padding:12px 14px;">
          <div style="font-size:0.75rem; color:#8b949e; text-transform:uppercase;">Toplam Kategori</div>
          <div style="font-size:1.4rem; font-weight:700; color:#fff;"><?= count($categories) ?></div>
        </div>
        <div style="background:#090d14; border:1px solid #1f2736; border-left:3px solid #3fb950; border-radius:8px; padding:12px 14px;">
          <div style="font-size:0.75rem; color:#8b949e; text-transform:uppercase;">Ana Kategoriler</div>
          <div style="font-size:1.4rem; font-weight:700; color:#3fb950;"><?= count($mainCategories) ?></div>
        </div>
        <div style="background:#090d14; border:1px solid #1f2736; border-left:3px solid #60a5fa; border-radius:8px; padding:12px 14px;">
          <div style="font-size:0.75rem; color:#8b949e; text-transform:uppercase;">Alt Kategoriler</div>
          <div style="font-size:1.4rem; font-weight:700; color:#60a5fa;"><?= count($subCategories) ?></div>
        </div>
        <div style="background:#090d14; border:1px solid #1f2736; border-left:3px solid #e3b341; border-radius:8px; padding:12px 14px;">
          <div style="font-size:0.75rem; color:#8b949e; text-transform:uppercase;">Nitelik Şablon Alanları</div>
          <div style="font-size:1.4rem; font-weight:700; color:#e3b341;">Zorunlu + Seçimli</div>
        </div>
      </div>

      <!-- BÖLÜM 1: YENİ KATEGORİ / ALT KATEGORİ VE NİTELİK ŞABLONU TANIMLAMA FORMU -->
      <div class="panel-card" style="margin-bottom:28px; border:1px solid rgba(212,175,55,0.25); background:linear-gradient(180deg, #111622 0%, #0a0e16 100%); border-radius:10px; padding:22px;">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:18px; border-bottom:1px solid #1f2736; padding-bottom:12px;">
          <div>
            <div style="display:flex; align-items:center; gap:8px;">
              <span style="display:inline-block; width:10px; height:10px; background:var(--gold-primary); border-radius:50%;"></span>
              <h2 style="font-size:1.1rem; font-weight:700; color:#fff; margin:0;">Yeni Kategori / Alt Kategori & Nitelik Şablonu Tanımla</h2>
            </div>
            <p style="font-size:0.8rem; color:#8b949e; margin:4px 0 0 18px;">
              Ürün sınıflandırmasını belirleyin; ana kategori veya alt kategori oluşturun, zorunlu ve seçimli alan şablonlarını atayın.
            </p>
          </div>
          <span class="badge" style="background:#161f30; color:#d4af37; border:1px solid #2d3e5e;">Tanımlama Formu</span>
        </div>

        <form method="POST" style="margin:0;">
          <input type="hidden" name="action" value="add_category">
          
          <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(260px, 1fr)); gap:16px; margin-bottom:14px;">
            <div>
              <label style="display:block; font-size:0.82rem; font-weight:600; color:#c5cbd3; margin-bottom:6px;">
                Kategori Adı *
              </label>
              <input type="text" name="kategori_adi" id="formCatName" class="form-control" placeholder="Örn: Klasik Heykel & Bronz Eserler" required style="background:#07090e; border:1px solid #2a3449; color:#fff; padding:10px 12px; font-size:0.88rem; width:100%; border-radius:6px;">
            </div>

            <div>
              <label style="display:block; font-size:0.82rem; font-weight:600; color:#c5cbd3; margin-bottom:6px;">
                Kategori Kodu (Slug / Sistem Kodu)
              </label>
              <input type="text" name="kategori_kodu" id="formCatCode" class="form-control" placeholder="Örn: heykel-bronz (Boş ise otomatik üretilir)" style="background:#07090e; border:1px solid #2a3449; color:#fff; padding:10px 12px; font-size:0.88rem; width:100%; border-radius:6px;">
            </div>

            <div>
              <label style="display:block; font-size:0.82rem; font-weight:600; color:#c5cbd3; margin-bottom:6px;">
                Hiyerarşi / Üst Kategori (Alt Kategori Belirleme)
              </label>
              <select name="ust_kategori_id" id="formCatParentSelect" class="form-control" style="background:#07090e; border:1px solid #2a3449; color:#fff; padding:10px 12px; font-size:0.88rem; width:100%; border-radius:6px;" onchange="updateCatTypeIndicator(this.value)">
                <option value="0">⭐ Ana Kategori (Üst Kategori Yok)</option>
                <?php foreach ($mainCategories as $mc): ?>
                  <option value="<?= (int)$mc['id'] ?>">
                    ↳ <?= htmlspecialchars($mc['ad'] ?? $mc['kategoriAdi']) ?> Alt Kategorisi Olarak Tanımla
                  </option>
                <?php endforeach; ?>
              </select>
            </div>
          </div>

          <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(260px, 1fr)); gap:16px; margin-bottom:16px;">
            <div>
              <label style="display:block; font-size:0.82rem; font-weight:600; color:#c5cbd3; margin-bottom:6px;">
                Kategori Açıklaması
              </label>
              <input type="text" name="aciklama" class="form-control" placeholder="Örn: 19. ve 20. Yüzyıl Avrupa ve Osmanlı Bronz Döküm Sanatı" style="background:#07090e; border:1px solid #2a3449; color:#fff; padding:10px 12px; font-size:0.88rem; width:100%; border-radius:6px;">
            </div>
            <div style="display:grid; grid-template-columns:1fr 1fr; gap:10px;">
              <div>
                <label style="display:block; font-size:0.82rem; font-weight:600; color:#c5cbd3; margin-bottom:6px;">
                  Komisyon Oranı (%)
                </label>
                <input type="number" step="0.1" name="komisyon_orani" value="8.0" class="form-control" style="background:#07090e; border:1px solid #2a3449; color:#fff; padding:10px 12px; font-size:0.88rem; width:100%; border-radius:6px;">
              </div>
              <div>
                <label style="display:block; font-size:0.82rem; font-weight:600; color:#c5cbd3; margin-bottom:6px;">
                  Ekspertiz Ücreti (₺)
                </label>
                <input type="number" step="50" name="ekspertiz_ucreti" value="1500" class="form-control" style="background:#07090e; border:1px solid #2a3449; color:#fff; padding:10px 12px; font-size:0.88rem; width:100%; border-radius:6px;">
              </div>
            </div>
          </div>

          <!-- NİTELİK ŞABLONLARI: ZORUNLU VE SEÇİMLİ ALANLAR -->
          <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(320px, 1fr)); gap:16px; margin-bottom:18px;">
            <!-- Zorunlu Nitelikler Şablonu -->
            <div style="background:#080b11; border:1px solid #2e1c22; border-left:3px solid #f87171; border-radius:6px; padding:14px;">
              <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:6px;">
                <label style="font-size:0.84rem; font-weight:700; color:#f87171; margin:0;">
                  🔒 Zorunlu Nitelikler Şablonu (Required Attributes)
                </label>
                <span style="font-size:0.7rem; color:#f87171; background:#2a1116; padding:2px 6px; border-radius:4px;">Girişi Mecburi</span>
              </div>
              <p style="font-size:0.73rem; color:#8b949e; margin:0 0 8px 0; line-height:1.4;">
                Satıcı eser yüklerken ve eksper rapor düzenlerken bu alanları doldurmak ZORUNDADIR. (Virgülle ayırın).
              </p>
              <input type="text" name="zorunlu_nitelikler" class="form-control" value="Dönem / Yıl, Sanatçı / İmalatçı, Kondisyon Derecesi, Materyal / Teknik" style="background:#05070a; border:1px solid #3d1e26; color:#fff; padding:9px 12px; font-size:0.84rem; width:100%; border-radius:6px;">
            </div>

            <!-- Seçimli Nitelikler Şablonu -->
            <div style="background:#080b11; border:1px solid #1c2738; border-left:3px solid #60a5fa; border-radius:6px; padding:14px;">
              <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:6px;">
                <label style="font-size:0.84rem; font-weight:700; color:#60a5fa; margin:0;">
                  ✨ Seçimli (Opsiyonel) Nitelikler Şablonu (Optional Attributes)
                </label>
                <span style="font-size:0.7rem; color:#60a5fa; background:#122138; padding:2px 6px; border-radius:4px;">İsteğe Bağlı</span>
              </div>
              <p style="font-size:0.73rem; color:#8b949e; margin:0 0 8px 0; line-height:1.4;">
                Satıcı veya eksperin eserin değerini artırmak için isteğe bağlı girebileceği ek alanlardır. (Virgülle ayırın).
              </p>
              <input type="text" name="secimli_nitelikler" class="form-control" value="Islak İmzalı / Damgalı, Provenans (Koleksiyon Geçmişi), Eser Ebatları, Çerçeve / Kutu, Sertifika No" style="background:#05070a; border:1px solid #203554; color:#fff; padding:9px 12px; font-size:0.84rem; width:100%; border-radius:6px;">
            </div>
          </div>

          <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:12px; background:#080b11; border:1px solid #1a2333; border-radius:6px; padding:12px 16px;">
            <div id="catTypeIndicator" style="font-size:0.8rem; color:#8b949e;">
              ⚡ <strong>Durum:</strong> Bağımsız Ana Kategori tanımlanıyor.
            </div>
            <button type="submit" class="btn-gold" style="padding:9px 24px; font-weight:700; font-size:0.88rem; border-radius:6px; cursor:pointer;">
              Kategori ve Şablonu Tanımla
            </button>
          </div>
        </form>
      </div>

      <!-- BÖLÜM 2: TANIMLI KATEGORİLER, ALT KATEGORİLER VE NİTELİK ŞABLONLARI KATALOĞU (SIFIR TABLO) -->
      <div>
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:14px;">
          <div>
            <h2 style="font-size:1.05rem; font-weight:700; color:#fff; margin:0;">Tanımlı Ürün Kategorileri ve Nitelik Şablonları</h2>
            <p style="font-size:0.78rem; color:#8b949e; margin:2px 0 0 0;">
              Sistemde aktif tüm ana kategoriler, alt kategorileri ve her birine özgü tanımlanmış nitelik şablonları.
            </p>
          </div>
          <span class="badge" style="background:#161b22; border:1px solid #30363d; color:#c5cbd3;"><?= count($categories) ?> Kategori</span>
        </div>

        <div style="display:grid; grid-template-columns:1fr; gap:18px;">
          <?php foreach ($mainCategories as $mc): 
            $mcId = (int)($mc['id'] ?? 0);
            $mcName = htmlspecialchars($mc['ad'] ?? $mc['kategoriAdi'] ?? 'Kategori');
            $mcCode = htmlspecialchars($mc['kod'] ?? $mc['kategoriKodu'] ?? '');
            $mcDesc = htmlspecialchars($mc['aciklama'] ?? '');
            $mcReq = (array)($mc['zorunluNitelikler'] ?? ['Dönem', 'Kondisyon']);
            $mcOpt = (array)($mc['secimliNitelikler'] ?? ['Provenans', 'İmza']);
            $mcComm = (float)($mc['komisyonOraniYuzde'] ?? 8.0);
            $mcFee = (float)($mc['sabitEkspertizUcreti'] ?? 1500.0);

            // Bu ana kategoriye bağlı alt kategorileri bul
            $attachedSubs = array_values(array_filter($categories, fn($sub) => (int)($sub['ustKategoriId'] ?? 0) === $mcId));
          ?>
            <div class="panel-card" style="border:1px solid #1f2736; background:#0b0f17; border-radius:10px; padding:20px;">
              <!-- Ana Kategori Başlığı -->
              <div style="display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:12px; flex-wrap:wrap; gap:10px;">
                <div>
                  <div style="display:flex; align-items:center; gap:8px;">
                    <span class="badge badge-success" style="font-size:0.72rem; padding:3px 8px;">🟢 ANA KATEGORİ</span>
                    <span class="badge" style="background:#131822; border:1px solid #232b3b; color:#8b949e; font-size:0.7rem;">#<?= $mcId ?> • <?= $mcCode ?></span>
                    <h3 style="font-size:1.15rem; font-weight:700; color:#fff; margin:0;"><?= $mcName ?></h3>
                  </div>
                  <?php if ($mcDesc): ?>
                    <p style="font-size:0.8rem; color:#8b949e; margin:6px 0 0 0;"><?= $mcDesc ?></p>
                  <?php endif; ?>
                </div>

                <div style="display:flex; align-items:center; gap:8px;">
                  <span class="badge badge-gold" style="font-size:0.72rem;">%<?= $mcComm ?> Komisyon</span>
                  <span class="badge badge-info" style="font-size:0.72rem;">₺<?= number_format($mcFee, 0, ',', '.') ?> Ekspertiz</span>
                  <button type="button" class="btn-outline-gold btn-sm" style="font-size:0.72rem; padding:4px 10px;" onclick="setFormParentCategory(<?= $mcId ?>, '<?= addslashes($mcName) ?>')">
                    + Alt Kategori Ekle
                  </button>
                  <form method="POST" style="margin:0;" onsubmit="return confirm('Bu kategoriyi silmek istediğinizden emin misiniz?');">
                    <input type="hidden" name="action" value="delete_category">
                    <input type="hidden" name="category_id" value="<?= $mcId ?>">
                    <button type="submit" class="btn-sm" style="background:#2a1116; border:1px solid #6b1d28; color:#f87171; font-size:0.72rem; padding:4px 8px; border-radius:4px; cursor:pointer;" title="Kategoriyi Sil">
                      ✕ Sil
                    </button>
                  </form>
                </div>
              </div>

              <!-- Ana Kategori Nitelik Şablonları Şeridi -->
              <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(280px, 1fr)); gap:12px; background:#07090e; border:1px solid #1a2333; border-radius:8px; padding:12px; margin-bottom:14px;">
                <div>
                  <div style="font-size:0.72rem; font-weight:700; color:#f87171; text-transform:uppercase; margin-bottom:6px;">
                    🔒 Zorunlu Nitelik Şablonu:
                  </div>
                  <div style="display:flex; flex-wrap:wrap; gap:4px;">
                    <?php foreach ($mcReq as $req): ?>
                      <span style="font-size:0.72rem; background:#241217; border:1px solid #4d1c25; color:#fca5a5; padding:2px 8px; border-radius:4px;">
                        <?= htmlspecialchars((string)$req) ?>
                      </span>
                    <?php endforeach; ?>
                  </div>
                </div>

                <div>
                  <div style="font-size:0.72rem; font-weight:700; color:#60a5fa; text-transform:uppercase; margin-bottom:6px;">
                    ✨ Seçimli (Opsiyonel) Nitelik Şablonu:
                  </div>
                  <div style="display:flex; flex-wrap:wrap; gap:4px;">
                    <?php if (!empty($mcOpt)): ?>
                      <?php foreach ($mcOpt as $opt): ?>
                        <span style="font-size:0.72rem; background:#101a2c; border:1px solid #1d3356; color:#93c5fd; padding:2px 8px; border-radius:4px;">
                          <?= htmlspecialchars((string)$opt) ?>
                        </span>
                      <?php endforeach; ?>
                    <?php else: ?>
                      <span style="font-size:0.72rem; color:#5c6b7d;">Özel opsiyonel nitelik girilmedi.</span>
                    <?php endif; ?>
                  </div>
                </div>
              </div>

              <!-- Alt Kategoriler Bölümü (Eğer varsa) -->
              <?php if (!empty($attachedSubs)): ?>
                <div style="margin-top:14px; border-top:1px dashed #1f2736; padding-top:14px;">
                  <div style="font-size:0.8rem; font-weight:600; color:#c5cbd3; margin-bottom:10px;">
                    ↳ <?= $mcName ?> Altındaki Alt Kategoriler (<?= count($attachedSubs) ?>)
                  </div>
                  <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(280px, 1fr)); gap:10px;">
                    <?php foreach ($attachedSubs as $sub): 
                      $subId = (int)($sub['id'] ?? 0);
                      $subName = htmlspecialchars($sub['ad'] ?? $sub['kategoriAdi'] ?? 'Alt Kategori');
                      $subCode = htmlspecialchars($sub['kod'] ?? $sub['kategoriKodu'] ?? '');
                      $subDesc = htmlspecialchars($sub['aciklama'] ?? '');
                      $subReq = (array)($sub['zorunluNitelikler'] ?? []);
                      $subOpt = (array)($sub['secimliNitelikler'] ?? []);
                    ?>
                      <div style="background:#080b11; border:1px solid #192233; border-left:3px solid #60a5fa; border-radius:6px; padding:12px;">
                        <div style="display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:6px;">
                          <div>
                            <div style="font-weight:700; font-size:0.88rem; color:#fff;">
                              <?= $subName ?>
                            </div>
                            <div style="font-size:0.7rem; color:#8b949e;"><?= $subCode ?></div>
                          </div>
                          <form method="POST" style="margin:0;" onsubmit="return confirm('Bu alt kategoriyi silmek istediğinizden emin misiniz?');">
                            <input type="hidden" name="action" value="delete_category">
                            <input type="hidden" name="category_id" value="<?= $subId ?>">
                            <button type="submit" style="background:none; border:none; color:#f87171; font-size:0.72rem; cursor:pointer;" title="Alt Kategoriyi Sil">
                              ✕ Sil
                            </button>
                          </form>
                        </div>
                        <?php if ($subDesc): ?>
                          <p style="font-size:0.73rem; color:#8b949e; margin:0 0 6px 0;"><?= $subDesc ?></p>
                        <?php endif; ?>

                        <!-- Alt Kategori Nitelikleri -->
                        <div style="font-size:0.68rem; color:#f87171; font-weight:600; margin-bottom:2px;">Özel Zorunlu Şablon:</div>
                        <div style="display:flex; flex-wrap:wrap; gap:3px; margin-bottom:6px;">
                          <?php foreach ($subReq as $sr): ?>
                            <span style="font-size:0.68rem; background:#241217; color:#fca5a5; padding:1px 5px; border-radius:3px;">
                              <?= htmlspecialchars((string)$sr) ?>
                            </span>
                          <?php endforeach; ?>
                        </div>
                        <?php if (!empty($subOpt)): ?>
                          <div style="font-size:0.68rem; color:#60a5fa; font-weight:600; margin-bottom:2px;">Özel Seçimli Şablon:</div>
                          <div style="display:flex; flex-wrap:wrap; gap:3px;">
                            <?php foreach ($subOpt as $so): ?>
                              <span style="font-size:0.68rem; background:#101a2c; color:#93c5fd; padding:1px 5px; border-radius:3px;">
                                <?= htmlspecialchars((string)$so) ?>
                              </span>
                            <?php endforeach; ?>
                          </div>
                        <?php endif; ?>
                      </div>
                    <?php endforeach; ?>
                  </div>
                </div>
              <?php else: ?>
                <div style="font-size:0.75rem; color:#5c6b7d; margin-top:8px;">
                  Bu ana kategoriye henüz özel bir alt kategori tanımlanmamış. Yukarıdaki "+ Alt Kategori Ekle" butonuna basarak alt kategori tanımlayabilirsiniz.
                </div>
              <?php endif; ?>
            </div>
          <?php endforeach; ?>
        </div>
      </div>
    </div>

    <script>
      function updateCatTypeIndicator(val) {
        const ind = document.getElementById('catTypeIndicator');
        if (!ind) return;
        if (parseInt(val) > 0) {
          ind.innerHTML = '⚡ <strong>Durum:</strong> Seçilen üst kategoriye bağlı <span style=\"color:#60a5fa; font-weight:700;\">ALT KATEGORİ</span> tanımlanıyor.';
        } else {
          ind.innerHTML = '⚡ <strong>Durum:</strong> Bağımsız <span style=\"color:#3fb950; font-weight:700;\">ANA KATEGORİ</span> tanımlanıyor.';
        }
      }

      function setFormParentCategory(parentId, parentName) {
        const sel = document.getElementById('formCatParentSelect');
        if (sel) {
          sel.value = parentId;
          updateCatTypeIndicator(parentId);
        }
        const nameInput = document.getElementById('formCatName');
        if (nameInput) {
          nameInput.focus();
        }
        window.scrollTo({ top: 0, behavior: 'smooth' });
      }
    </script>

    <!-- 2. TAB: KULLANICILAR -->
    <div id="admTab_kullanicilar" class="tab-content <?= $activeTab === 'kullanicilar' ? 'active' : '' ?>">
      <div class="page-breadcrumb">ADMIN / ÇALIŞMA ALANI</div>
      <h1 class="page-heading">Kullanıcı Yönetimi</h1>
      <p class="page-subheading">Kullanıcı hesapları, KYC doğrulama ve hesap askıya alma (Ban)</p>

      <div class="panel-card">
        <table class="data-table">
          <thead>
            <tr>
              <th>ID</th>
              <th>Ad Soyad</th>
              <th>E-Posta</th>
              <th>Rol</th>
              <th>KYC</th>
              <th>Durum</th>
              <th>Erişim İşlemi</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($users as $u): ?>
              <tr>
                <td>#<?= $u['id'] ?></td>
                <td style="font-weight:600; color:#fff;"><?= htmlspecialchars($u['ad'] . ' ' . $u['soyad']) ?></td>
                <td style="color:var(--text-muted);"><?= htmlspecialchars($u['email']) ?></td>
                <td><span class="badge badge-gold"><?= htmlspecialchars($u['rol']) ?></span></td>
                <td>
                  <?php if (!empty($u['kycDogrulama']['dogrulandiMi'])): ?>
                    <span style="color:#3fb950; font-weight:600;">✓ Doğrulandı</span>
                  <?php else: ?>
                    <form method="POST" style="display:inline; margin:0;">
                      <input type="hidden" name="action" value="verify_kyc">
                      <input type="hidden" name="user_id" value="<?= $u['id'] ?>">
                      <input type="hidden" name="decision" value="approve">
                      <button type="submit" class="btn-outline-gold btn-sm" style="padding:2px 8px; font-size:0.75rem;" title="Kullanıcı kimliğini doğrula ve onay statüsüne geçir">✓ KYC Onayla</button>
                    </form>
                  <?php endif; ?>
                </td>
                <td>
                  <span class="badge <?= ($u['durum'] ?? '') === 'AKTIF' ? 'badge-success' : 'badge-danger' ?>">
                    <?= htmlspecialchars($u['durum'] ?? 'AKTIF') ?>
                  </span>
                </td>
                <td>
                  <?php if ($u['rol'] !== 'ADMIN'): ?>
                    <form method="POST" style="margin:0; display:inline;">
                      <input type="hidden" name="action" value="toggle_ban">
                      <input type="hidden" name="user_id" value="<?= $u['id'] ?>">
                      <?php if (($u['durum'] ?? '') === 'AKTIF'): ?>
                        <input type="hidden" name="new_status" value="ASKIDA">
                        <button type="submit" class="btn-outline-gold btn-sm" style="color:#f85149 !important; border-color:#f85149;">Askıya Al</button>
                      <?php else: ?>
                        <input type="hidden" name="new_status" value="AKTIF">
                        <button type="submit" class="btn-gold btn-sm">Yasağı Kaldır</button>
                      <?php endif; ?>
                    </form>
                  <?php else: ?>
                    <span style="font-size:0.75rem; color:var(--text-muted);">Sistem Sahibi</span>
                  <?php endif; ?>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>

    <!-- ROL & YETKİ YÖNETİMİ TABI — SADE, KART TABANLI, SIFIR TABLO -->
    <div id="admTab_yetkiler" class="tab-content <?= $activeTab === 'yetkiler' ? 'active' : '' ?>">
      <div class="page-breadcrumb">ADMIN / ÇALIŞMA ALANI / ROL & ERİŞİM YÖNETİMİ</div>
      <div style="display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:20px; flex-wrap:wrap; gap:10px;">
        <div>
          <h1 class="page-heading">Kullanıcı Rolleri ve Erişim Yönetimi</h1>
          <p class="page-subheading" style="color:var(--gold-primary); font-weight:500;">
            Kullanıcıların rollerini belirleme ve sistem içi erişimlerini anlık kısıtlama masası.
          </p>
        </div>
        <span class="badge badge-gold" style="font-size:0.78rem; padding:6px 14px;">RBAC & Erişim Denetimi</span>
      </div>

      <!-- 4 Temel Rol Hızlı Bilgi Şeritleri -->
      <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(220px, 1fr)); gap:12px; margin-bottom:24px;">
        <div style="background:#090d14; border:1px solid #1f2736; border-left:3px solid #3fb950; border-radius:8px; padding:12px 14px;">
          <div style="color:#3fb950; font-weight:700; font-size:0.88rem; margin-bottom:4px;">🟢 Alıcı (Buyer)</div>
          <div style="font-size:0.75rem; color:#8b949e; line-height:1.4;">Canlı açık artırmaya katılır, pey sürer. VIP kademede (Gold/Platinum) özel salona erişir.</div>
        </div>
        <div style="background:#090d14; border:1px solid #1f2736; border-left:3px solid #60a5fa; border-radius:8px; padding:12px 14px;">
          <div style="color:#60a5fa; font-weight:700; font-size:0.88rem; margin-bottom:4px;">🔵 Satıcı (Seller)</div>
          <div style="font-size:0.75rem; color:#8b949e; line-height:1.4;">Eser ilanı oluşturur, ekspertiz talep eder. Kendi lotuna teklif veremez.</div>
        </div>
        <div style="background:#090d14; border:1px solid #1f2736; border-left:3px solid #e3b341; border-radius:8px; padding:12px 14px;">
          <div style="color:#e3b341; font-weight:700; font-size:0.88rem; margin-bottom:4px;">🟡 Eksper (Appraiser)</div>
          <div style="font-size:0.75rem; color:#8b949e; line-height:1.4;">Kondisyon skorlar, orijinallik sertifikası ve değerleme onayı düzenler.</div>
        </div>
        <div style="background:#090d14; border:1px solid #1f2736; border-left:3px solid #d4af37; border-radius:8px; padding:12px 14px;">
          <div style="color:#d4af37; font-weight:700; font-size:0.88rem; margin-bottom:4px;">🔴 Admin (Yönetici)</div>
          <div style="font-size:0.75rem; color:#8b949e; line-height:1.4;">Kullanıcı doğrulama, rol atama, kısıtlama ve müzayede idaresinde tam yetkilidir.</div>
        </div>
      </div>

      <!-- BÖLÜM 1: KULLANICININ ROLLERİNİ DEĞİŞTİRME -->
      <div class="panel-card" style="margin-bottom:28px; border:1px solid rgba(212,175,55,0.25); background:linear-gradient(180deg, #111622 0%, #0a0e16 100%); border-radius:10px; padding:22px;">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:18px; border-bottom:1px solid #1f2736; padding-bottom:12px;">
          <div>
            <div style="display:flex; align-items:center; gap:8px;">
              <span style="display:inline-block; width:10px; height:10px; background:var(--gold-primary); border-radius:50%;"></span>
              <h2 style="font-size:1.1rem; font-weight:700; color:#fff; margin:0;">Bölüm 1: Kullanıcı Rollerini Değiştirme</h2>
            </div>
            <p style="font-size:0.8rem; color:#8b949e; margin:4px 0 0 18px;">
              Kullanıcının sistemdeki rolünü ve yetki seviyesini seçerek güncelleyin.
            </p>
          </div>
          <span class="badge" style="background:#161f30; color:#d4af37; border:1px solid #2d3e5e;">Rol Değiştirme</span>
        </div>

        <!-- Rol Değiştirme Formu (Sade ve Kart Düzeni) -->
        <form method="POST" style="margin:0 0 20px 0;">
          <input type="hidden" name="action" value="update_user_role_and_permissions">

          <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(280px, 1fr)); gap:16px; margin-bottom:16px;">
            <div>
              <label style="display:block; font-size:0.82rem; font-weight:600; color:#c5cbd3; margin-bottom:6px;">
                1. Kullanıcıyı Seçin
              </label>
              <select name="user_id" id="roleChangeUserSelect" class="form-control" style="background:#07090e; border:1px solid #2a3449; color:#fff; padding:10px 12px; font-size:0.88rem; width:100%; border-radius:6px;">
                <?php foreach ($users as $u): 
                  $uid = (int)($u['id'] ?? 0);
                  $urole = strtoupper((string)($u['rol'] ?? 'ALICI'));
                  $tier = strtoupper((string)($u['yetkiSeviyesi'] ?? ($u['aliciProfili']['vipSeviyesi'] ?? 'STANDART')));
                  $uName = htmlspecialchars(($u['ad'] ?? '') . ' ' . ($u['soyad'] ?? ''));
                  $isKyc = !empty($u['kycDogrulama']['dogrulandiMi']);
                ?>
                  <option value="<?= $uid ?>">
                    #<?= $uid ?> — <?= $uName ?> (Mevcut: <?= $urole ?> / <?= $tier ?>) <?= $isKyc ? '✓ Onaylı' : '⏳ Bekliyor' ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>

            <div>
              <label style="display:block; font-size:0.82rem; font-weight:600; color:#c5cbd3; margin-bottom:6px;">
                2. Yeni Rol ve Seviye Seçin
              </label>
              <select name="new_role" class="form-control" style="background:#07090e; border:1px solid #2a3449; color:#fff; padding:10px 12px; font-size:0.88rem; width:100%; border-radius:6px;">
                <optgroup label="Alıcı Rolleri">
                  <option value="ALICI:STANDART">🟢 Alıcı — Standart Müzayede Katılımı</option>
                  <option value="ALICI:GOLD">👑 Alıcı — VIP Gold (VIP Salon + ₺150K Limit)</option>
                  <option value="ALICI:PLATINUM">💎 Alıcı — VIP Platinum (Yüksek Portföy & Özel Temsil)</option>
                </optgroup>
                <optgroup label="Satıcı Rolleri">
                  <option value="SATICI:BIREYSEL">🔵 Satıcı — Bireysel (Standart 3 İlan)</option>
                  <option value="SATICI:KURUMSAL">🏢 Satıcı — Kurumsal (Galeri, Müzayede Evi)</option>
                </optgroup>
                <optgroup label="Ekspertiz & Yönetim">
                  <option value="EKSPERTIZ:BAS_EKSPER">🟡 Ekspertiz — Baş Eksper (Kondisyon & Orijinallik Onayı)</option>
                  <option value="ADMIN:SUPER_ADMIN">🔴 Admin — Sistem Yöneticisi (Tam Yetki)</option>
                </optgroup>
              </select>
            </div>
          </div>

          <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:12px; background:#080b11; border:1px solid #1a2333; border-radius:6px; padding:12px 16px;">
            <div style="font-size:0.8rem; color:#8b949e;">
              ⚡ Rol seçildiğinde ilgili role ait operasyonel işlem izinleri arka planda anında güncellenir.
            </div>
            <button type="submit" class="btn-gold" style="padding:9px 24px; font-weight:700; font-size:0.88rem; border-radius:6px; cursor:pointer;">
              Rolü Güncelle
            </button>
          </div>
        </form>

        <!-- Kullanıcı Rolleri Hızlı Bakış Kartları (SIFIR TABLO) -->
        <div style="margin-top:16px;">
          <div style="font-size:0.82rem; font-weight:600; color:#8b949e; text-transform:uppercase; letter-spacing:0.5px; margin-bottom:10px;">
            Kayıtlı Kullanıcılar ve Güncel Rol Durumları (<?= count($users) ?>)
          </div>
          <div style="display:grid; grid-template-columns:repeat(auto-fill, minmax(260px, 1fr)); gap:10px;">
            <?php foreach ($users as $u): 
              $uid = (int)($u['id'] ?? 0);
              $urole = strtoupper((string)($u['rol'] ?? 'ALICI'));
              $tier = strtoupper((string)($u['yetkiSeviyesi'] ?? ($u['aliciProfili']['vipSeviyesi'] ?? 'STANDART')));
              $isKyc = !empty($u['kycDogrulama']['dogrulandiMi']);
              $isBanned = ($u['durum'] ?? '') === 'ASKIDA';
            ?>
              <div style="background:#080b11; border:1px solid #1d2535; border-radius:8px; padding:12px 14px; display:flex; justify-content:space-between; align-items:center;">
                <div>
                  <div style="font-weight:600; font-size:0.86rem; color:#fff;">
                    #<?= $uid ?> <?= htmlspecialchars(($u['ad'] ?? '') . ' ' . ($u['soyad'] ?? '')) ?>
                  </div>
                  <div style="font-size:0.73rem; color:#8b949e; margin-top:2px;">
                    <?= htmlspecialchars($u['email'] ?? '') ?>
                  </div>
                  <div style="margin-top:4px;">
                    <?php if ($isKyc): ?>
                      <span style="font-size:0.68rem; color:#3fb950; font-weight:600;">✓ KYC Onaylı</span>
                    <?php else: ?>
                      <span style="font-size:0.68rem; color:#e3b341; font-weight:600;">⏳ KYC Bekliyor</span>
                    <?php endif; ?>
                  </div>
                </div>
                <div style="text-align:right;">
                  <span class="badge <?php 
                    echo match($urole) {
                      'ADMIN' => 'badge-gold',
                      'EKSPERTIZ' => 'badge-info',
                      'SATICI' => 'badge-primary',
                      default => (in_array($tier, ['GOLD', 'PLATINUM'], true) ? 'badge-gold' : 'badge-success')
                    };
                  ?>" style="font-size:0.72rem; padding:4px 8px;">
                    <?= $urole ?> <?= in_array($tier, ['GOLD', 'PLATINUM'], true) ? "({$tier})" : "" ?>
                  </span>
                  <?php if ($isBanned): ?>
                    <div style="font-size:0.68rem; color:#f87171; font-weight:700; margin-top:3px;">[ASKIDA]</div>
                  <?php endif; ?>
                </div>
              </div>
            <?php endforeach; ?>
          </div>
        </div>
      </div>

      <!-- BÖLÜM 2: ERİŞİMLERİ SINIRLAMA -->
      <div class="panel-card" style="border:1px solid rgba(220,53,69,0.3); background:linear-gradient(180deg, #161118 0%, #0d0a10 100%); border-radius:10px; padding:22px;">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:18px; border-bottom:1px solid #2d1e26; padding-bottom:12px;">
          <div>
            <div style="display:flex; align-items:center; gap:8px;">
              <span style="display:inline-block; width:10px; height:10px; background:#f87171; border-radius:50%;"></span>
              <h2 style="font-size:1.1rem; font-weight:700; color:#fff; margin:0;">Bölüm 2: Erişimleri Sınırlama</h2>
            </div>
            <p style="font-size:0.8rem; color:#8b949e; margin:4px 0 0 18px;">
              Kullanıcıların pey verme, VIP salon, ilan açma, ekspertiz onaylama ve hesap durumunu kısıtlayın veya yeniden izin verin.
            </p>
          </div>
          <span class="badge" style="background:#2a141b; color:#f87171; border:1px solid #5a202d;">Erişim Kısıtlama</span>
        </div>

        <!-- Hızlı Kısıtlama / İzin Verme Kontrol Masası -->
        <div style="margin-bottom:20px;">
          <div style="font-size:0.82rem; font-weight:600; color:#c5cbd3; margin-bottom:8px;">
            Kısıtlama Uygulanacak Kullanıcıyı Seçin:
          </div>
          <select id="restrictTargetUserSelect" class="form-control" style="background:#07090e; border:1px solid #3d232e; color:#fff; padding:10px 12px; font-size:0.88rem; width:100%; max-width:500px; border-radius:6px;" onchange="syncRestrictUserId(this.value)">
            <?php foreach ($users as $u): 
              $uid = (int)($u['id'] ?? 0);
              $uName = htmlspecialchars(($u['ad'] ?? '') . ' ' . ($u['soyad'] ?? ''));
              $urole = strtoupper((string)($u['rol'] ?? 'ALICI'));
              $k = $u['kisitlamalar'] ?? [];
              $hasAny = !empty($k['peyEngeli']) || !empty($k['vipEngeli']) || !empty($k['ilanEngeli']) || !empty($k['ekspertizEngeli']) || ($u['durum'] ?? '') === 'ASKIDA';
            ?>
              <option value="<?= $uid ?>">
                #<?= $uid ?> — <?= $uName ?> (<?= $urole ?>) <?= $hasAny ? '⚠️ KISITLAMA VAR' : '✓ Normal' ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>

        <!-- 5 Temel Erişim Kısıtlama Kartı (SIFIR TABLO) -->
        <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(280px, 1fr)); gap:14px; margin-bottom:22px;">
          
          <!-- 1. Pey Verme Kısıtlaması -->
          <div style="background:#0a0c13; border:1px solid #231c26; border-radius:8px; padding:16px; display:flex; flex-direction:column; justify-content:space-between;">
            <div>
              <div style="font-weight:700; font-size:0.9rem; color:#fff; margin-bottom:4px;">
                🔨 Canlı Müzayedede Pey Verme
              </div>
              <p style="font-size:0.75rem; color:#8b949e; line-height:1.4; margin:0 0 12px 0;">
                Kullanıcının açık artırmalarda teklif vermesini engeller. (Shill bidding veya ödeme aksamalarında uygulanır).
              </p>
            </div>
            <div style="display:flex; gap:8px;">
              <form method="POST" style="margin:0; flex:1;">
                <input type="hidden" name="action" value="restrict_user_access">
                <input type="hidden" name="user_id" class="restrictUserHiddenInput" value="<?= (int)($users[0]['id'] ?? 1) ?>">
                <input type="hidden" name="restriction_type" value="peyEngeli">
                <input type="hidden" name="is_restricted" value="1">
                <button type="submit" class="btn-sm" style="width:100%; background:#2d1116; border:1px solid #6b1d28; color:#f87171; font-weight:600; padding:6px 0; border-radius:4px; cursor:pointer;">
                  Pey Vermeyi Kısıtla
                </button>
              </form>
              <form method="POST" style="margin:0; flex:1;">
                <input type="hidden" name="action" value="restrict_user_access">
                <input type="hidden" name="user_id" class="restrictUserHiddenInput" value="<?= (int)($users[0]['id'] ?? 1) ?>">
                <input type="hidden" name="restriction_type" value="peyEngeli">
                <input type="hidden" name="is_restricted" value="0">
                <button type="submit" class="btn-sm" style="width:100%; background:#0f241a; border:1px solid #1a5c38; color:#3fb950; font-weight:600; padding:6px 0; border-radius:4px; cursor:pointer;">
                  İzin Ver
                </button>
              </form>
            </div>
          </div>

          <!-- 2. VIP Salon Erişimi Kısıtlaması -->
          <div style="background:#0a0c13; border:1px solid #231c26; border-radius:8px; padding:16px; display:flex; flex-direction:column; justify-content:space-between;">
            <div>
              <div style="font-weight:700; font-size:0.9rem; color:#fff; margin-bottom:4px;">
                👑 VIP Özel Salon Erişimi
              </div>
              <p style="font-size:0.75rem; color:#8b949e; line-height:1.4; margin:0 0 12px 0;">
                Yüksek portföy ve VIP kural stratejisine sahip kapalı salonlara girişi kısıtlar.
              </p>
            </div>
            <div style="display:flex; gap:8px;">
              <form method="POST" style="margin:0; flex:1;">
                <input type="hidden" name="action" value="restrict_user_access">
                <input type="hidden" name="user_id" class="restrictUserHiddenInput" value="<?= (int)($users[0]['id'] ?? 1) ?>">
                <input type="hidden" name="restriction_type" value="vipEngeli">
                <input type="hidden" name="is_restricted" value="1">
                <button type="submit" class="btn-sm" style="width:100%; background:#2d1116; border:1px solid #6b1d28; color:#f87171; font-weight:600; padding:6px 0; border-radius:4px; cursor:pointer;">
                  VIP'yi Kısıtla
                </button>
              </form>
              <form method="POST" style="margin:0; flex:1;">
                <input type="hidden" name="action" value="restrict_user_access">
                <input type="hidden" name="user_id" class="restrictUserHiddenInput" value="<?= (int)($users[0]['id'] ?? 1) ?>">
                <input type="hidden" name="restriction_type" value="vipEngeli">
                <input type="hidden" name="is_restricted" value="0">
                <button type="submit" class="btn-sm" style="width:100%; background:#0f241a; border:1px solid #1a5c38; color:#3fb950; font-weight:600; padding:6px 0; border-radius:4px; cursor:pointer;">
                  İzin Ver
                </button>
              </form>
            </div>
          </div>

          <!-- 3. Eser & İlan Açma Kısıtlaması -->
          <div style="background:#0a0c13; border:1px solid #231c26; border-radius:8px; padding:16px; display:flex; flex-direction:column; justify-content:space-between;">
            <div>
              <div style="font-weight:700; font-size:0.9rem; color:#fff; margin-bottom:4px;">
                📦 Eser Yükleme ve İlan Açma
              </div>
              <p style="font-size:0.75rem; color:#8b949e; line-height:1.4; margin:0 0 12px 0;">
                Kullanıcının platformda satışa yeni eser çıkarmasını ve ilan yayımlamasını engeller.
              </p>
            </div>
            <div style="display:flex; gap:8px;">
              <form method="POST" style="margin:0; flex:1;">
                <input type="hidden" name="action" value="restrict_user_access">
                <input type="hidden" name="user_id" class="restrictUserHiddenInput" value="<?= (int)($users[0]['id'] ?? 1) ?>">
                <input type="hidden" name="restriction_type" value="ilanEngeli">
                <input type="hidden" name="is_restricted" value="1">
                <button type="submit" class="btn-sm" style="width:100%; background:#2d1116; border:1px solid #6b1d28; color:#f87171; font-weight:600; padding:6px 0; border-radius:4px; cursor:pointer;">
                  İlanı Kısıtla
                </button>
              </form>
              <form method="POST" style="margin:0; flex:1;">
                <input type="hidden" name="action" value="restrict_user_access">
                <input type="hidden" name="user_id" class="restrictUserHiddenInput" value="<?= (int)($users[0]['id'] ?? 1) ?>">
                <input type="hidden" name="restriction_type" value="ilanEngeli">
                <input type="hidden" name="is_restricted" value="0">
                <button type="submit" class="btn-sm" style="width:100%; background:#0f241a; border:1px solid #1a5c38; color:#3fb950; font-weight:600; padding:6px 0; border-radius:4px; cursor:pointer;">
                  İzin Ver
                </button>
              </form>
            </div>
          </div>

          <!-- 4. Ekspertiz Onay Yetkisi Kısıtlaması -->
          <div style="background:#0a0c13; border:1px solid #231c26; border-radius:8px; padding:16px; display:flex; flex-direction:column; justify-content:space-between;">
            <div>
              <div style="font-weight:700; font-size:0.9rem; color:#fff; margin-bottom:4px;">
                🔍 Ekspertiz Rapor Onaylama
              </div>
              <p style="font-size:0.75rem; color:#8b949e; line-height:1.4; margin:0 0 12px 0;">
                Ekspertiz uzmanının orijinallik ve kondisyon raporu düzenleme yetkisini askıya alır.
              </p>
            </div>
            <div style="display:flex; gap:8px;">
              <form method="POST" style="margin:0; flex:1;">
                <input type="hidden" name="action" value="restrict_user_access">
                <input type="hidden" name="user_id" class="restrictUserHiddenInput" value="<?= (int)($users[0]['id'] ?? 1) ?>">
                <input type="hidden" name="restriction_type" value="ekspertizEngeli">
                <input type="hidden" name="is_restricted" value="1">
                <button type="submit" class="btn-sm" style="width:100%; background:#2d1116; border:1px solid #6b1d28; color:#f87171; font-weight:600; padding:6px 0; border-radius:4px; cursor:pointer;">
                  Eksperi Kısıtla
                </button>
              </form>
              <form method="POST" style="margin:0; flex:1;">
                <input type="hidden" name="action" value="restrict_user_access">
                <input type="hidden" name="user_id" class="restrictUserHiddenInput" value="<?= (int)($users[0]['id'] ?? 1) ?>">
                <input type="hidden" name="restriction_type" value="ekspertizEngeli">
                <input type="hidden" name="is_restricted" value="0">
                <button type="submit" class="btn-sm" style="width:100%; background:#0f241a; border:1px solid #1a5c38; color:#3fb950; font-weight:600; padding:6px 0; border-radius:4px; cursor:pointer;">
                  İzin Ver
                </button>
              </form>
            </div>
          </div>

          <!-- 5. Hesabı Askıya Alma (Dondurma / Kilit) -->
          <div style="background:#0a0c13; border:1px solid #3d1b26; border-radius:8px; padding:16px; display:flex; flex-direction:column; justify-content:space-between;">
            <div>
              <div style="font-weight:700; font-size:0.9rem; color:#f87171; margin-bottom:4px;">
                🚫 Hesabı Tamamen Askıya Alma
              </div>
              <p style="font-size:0.75rem; color:#8b949e; line-height:1.4; margin:0 0 12px 0;">
                Kullanıcının platforma erişimini, oturumunu ve tüm işlemlerini tamamen dondurur.
              </p>
            </div>
            <div style="display:flex; gap:8px;">
              <form method="POST" style="margin:0; flex:1;">
                <input type="hidden" name="action" value="restrict_user_access">
                <input type="hidden" name="user_id" class="restrictUserHiddenInput" value="<?= (int)($users[0]['id'] ?? 1) ?>">
                <input type="hidden" name="restriction_type" value="hesapAskisi">
                <input type="hidden" name="is_restricted" value="1">
                <button type="submit" class="btn-sm" style="width:100%; background:#47121b; border:1px solid #8e2132; color:#fff; font-weight:700; padding:6px 0; border-radius:4px; cursor:pointer;">
                  Hesabı Askıya Al
                </button>
              </form>
              <form method="POST" style="margin:0; flex:1;">
                <input type="hidden" name="action" value="restrict_user_access">
                <input type="hidden" name="user_id" class="restrictUserHiddenInput" value="<?= (int)($users[0]['id'] ?? 1) ?>">
                <input type="hidden" name="restriction_type" value="hesapAskisi">
                <input type="hidden" name="is_restricted" value="0">
                <button type="submit" class="btn-sm" style="width:100%; background:#0f241a; border:1px solid #1a5c38; color:#3fb950; font-weight:600; padding:6px 0; border-radius:4px; cursor:pointer;">
                  Aktifleştir
                </button>
              </form>
            </div>
          </div>
        </div>

        <!-- Aktif Kısıtlama Takip Listesi (SIFIR TABLO, SADE KARTLAR) -->
        <div>
          <div style="font-size:0.82rem; font-weight:600; color:#8b949e; text-transform:uppercase; letter-spacing:0.5px; margin-bottom:10px;">
            Kısıtlama ve İzin Durumları Özeti
          </div>
          <div style="display:grid; grid-template-columns:repeat(auto-fill, minmax(300px, 1fr)); gap:12px;">
            <?php foreach ($users as $u): 
              $uid = (int)($u['id'] ?? 0);
              $k = $u['kisitlamalar'] ?? [];
              $isSuspended = ($u['durum'] ?? '') === 'ASKIDA';
              $activeRestrictions = [];
              if (!empty($k['peyEngeli'])) $activeRestrictions[] = 'Pey Yasağı';
              if (!empty($k['vipEngeli'])) $activeRestrictions[] = 'VIP Giriş Yasağı';
              if (!empty($k['ilanEngeli'])) $activeRestrictions[] = 'İlan Yasağı';
              if (!empty($k['ekspertizEngeli'])) $activeRestrictions[] = 'Eksper Yasağı';
              if ($isSuspended) $activeRestrictions[] = 'HESAP DONDURULDU';
            ?>
              <div style="background:#090d14; border:1px solid <?= empty($activeRestrictions) ? '#1c2433' : '#4d1e27' ?>; border-radius:8px; padding:12px 14px;">
                <div style="display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:6px;">
                  <div>
                    <div style="font-weight:600; font-size:0.85rem; color:#fff;">
                      #<?= $uid ?> <?= htmlspecialchars(($u['ad'] ?? '') . ' ' . ($u['soyad'] ?? '')) ?>
                    </div>
                    <div style="font-size:0.73rem; color:#8b949e;">
                      <?= htmlspecialchars($u['email'] ?? '') ?>
                    </div>
                  </div>
                  <?php if (empty($activeRestrictions)): ?>
                    <span class="badge badge-success" style="font-size:0.68rem; padding:3px 8px;">Tam Erişim</span>
                  <?php else: ?>
                    <span class="badge badge-danger" style="font-size:0.68rem; padding:3px 8px;">Kısıtlı</span>
                  <?php endif; ?>
                </div>

                <div style="margin-top:8px; display:flex; flex-wrap:wrap; gap:4px;">
                  <?php if (empty($activeRestrictions)): ?>
                    <span style="font-size:0.72rem; color:#5c6b7d;">Hiçbir kısıtlama yok, tüm fonksiyonlar açık.</span>
                  <?php else: ?>
                    <?php foreach ($activeRestrictions as $ar): ?>
                      <span style="font-size:0.7rem; background:#36131a; border:1px solid #6b1f2b; color:#f87171; border-radius:4px; padding:2px 6px;">
                        ✕ <?= $ar ?>
                      </span>
                    <?php endforeach; ?>
                  <?php endif; ?>
                </div>
              </div>
            <?php endforeach; ?>
          </div>
        </div>
      </div>
    </div>

    <script>
      function syncRestrictUserId(val) {
        document.querySelectorAll('.restrictUserHiddenInput').forEach(function(el) {
          el.value = val;
        });
      }
    </script>
    </div>

    <!-- 3. TAB: MÜZAYEDE & LOT DÜZENLEME (ADMİN DÜZENLEME YETKİSİ) -->
    <div id="admTab_moderasyon" class="tab-content <?= $activeTab === 'moderasyon' ? 'active' : '' ?>">
      <div class="page-breadcrumb">ADMIN / ÇALIŞMA ALANI</div>
      <h1 class="page-heading">Müzayede ve Lot Düzenleme</h1>
      <p class="page-subheading">Açık artırmadaki lotların fiyatlarını, pey adımlarını, sürelerini ve durumlarını yönetin</p>

      <!-- Aktif Lotlar Düzenleme Tablosu -->
      <div class="panel-card" style="margin-bottom:24px;">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:14px;">
          <div>
            <h3 class="panel-title" style="margin-bottom:2px;">Katalogdaki Müzayede Lotları</h3>
            <p style="font-size:0.8rem; color:var(--text-muted);">Tüm canlı lotların başlangıç/rezerv fiyatını, artırım adımını ve durumunu buradan düzenleyebilirsiniz.</p>
          </div>
          <span class="badge badge-gold"><?= count($lots) ?> Adet Eser</span>
        </div>

        <table class="data-table">
          <thead>
            <tr>
              <th>Lot Kodu</th>
              <th>Eser Adı</th>
              <th>Kategori</th>
              <th>Başlangıç</th>
              <th>Rezerv Fiyat</th>
              <th>Pey Adımı</th>
              <th>Güncel Teklif</th>
              <th>Kalan Süre</th>
              <th>Durum</th>
              <th>İşlem</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($lots as $lot): ?>
              <tr>
                <td><span class="badge badge-gold"><?= htmlspecialchars($lot['lotKodu'] ?? "AUC-{$lot['id']}") ?></span></td>
                <td style="font-weight:600; color:#fff; max-width:200px;"><?= htmlspecialchars($lot['baslik']) ?></td>
                <td style="color:var(--text-muted); font-size:0.82rem;"><?= htmlspecialchars($lot['kategoriAdi'] ?? 'Antika & Sanat') ?></td>
                <td><?= $formatPrice((float)$lot['baslangicFiyati']) ?></td>
                <td><?= $formatPrice((float)$lot['rezervFiyat']) ?></td>
                <td>+<?= $formatPrice((float)$lot['minimumArtisTutari']) ?></td>
                <td style="color:var(--gold-bright); font-weight:700;"><?= $formatPrice((float)$lot['mevcutEnYuksekTeklif']) ?></td>
                <td style="font-size:0.8rem; color:var(--text-muted);"><?= htmlspecialchars($lot['kalanSureMetni'] ?? '5 saat') ?></td>
                <td>
                  <span class="badge <?= ($lot['durum'] ?? '') === 'ACIK_ARTIRMADA' ? 'badge-success' : 'badge-warning' ?>">
                    <?= htmlspecialchars($lot['durum'] ?? 'ACIK_ARTIRMADA') ?>
                  </span>
                </td>
                <td>
                  <button type="button" class="btn-gold btn-sm" style="padding:4px 10px; font-size:0.78rem; cursor:pointer;" onclick='openEditLotModal(<?= json_encode($lot, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>)'>
                    Düzenle
                  </button>
                  <form method="POST" style="display:inline; margin-left:4px;" onsubmit="return confirm('Son verilen peyi kural ihlali gerekçesiyle iptal edip önceki peye dönmek istediğinize emin misiniz?');">
                    <input type="hidden" name="action" value="cancel_bid">
                    <input type="hidden" name="lot_id" value="<?= $lot['id'] ?>">
                    <input type="hidden" name="reason" value="Admin kural dışı teklif iptali">
                    <button type="submit" class="btn-outline-gold btn-sm" style="padding:4px 8px; font-size:0.75rem; color:#f87171; border-color:rgba(239,68,68,0.35);" title="Son peyi iptal et ve önceki fiyata dön">Pey İptal</button>
                  </form>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>

      <!-- Onay Bekleyen Taslak İlanlar -->
      <div class="panel-card">
        <h3 class="panel-title" style="margin-bottom:12px;">Yeni Taslak İlanlar (Onay ve Yayına Alma)</h3>
        <table class="data-table">
          <thead>
            <tr><th>İlan Kodu</th><th>Ürün Bilgisi</th><th>Satıcı</th><th>Statü</th><th>Müzayede Başlat</th></tr>
          </thead>
          <tbody>
            <?php foreach ($listings as $l): ?>
              <tr>
                <td><span class="badge badge-gold"><?= htmlspecialchars($l['ilanKodu']) ?></span></td>
                <td style="font-weight:600; color:#fff;">Koleksiyonluk Eser (ID: <?= $l['urunId'] ?>)</td>
                <td>Satıcı #<?= $l['saticiId'] ?></td>
                <td><span class="badge badge-success"><?= htmlspecialchars($l['durum']) ?></span></td>
                <td>
                  <?php if ($l['durum'] !== 'YAYINDA'): ?>
                    <form method="POST" style="display:inline-flex; gap:6px; margin:0;">
                      <input type="hidden" name="action" value="approve_listing">
                      <input type="hidden" name="listing_id" value="<?= $l['id'] ?>">
                      <input type="hidden" name="start_price" value="32000">
                      <input type="hidden" name="reserve_price" value="40000">
                      <input type="hidden" name="min_step" value="1500">
                      <button type="submit" class="btn-gold btn-sm">Müzayedeye Başlat</button>
                    </form>
                  <?php else: ?>
                    <span style="color:#3fb950; font-weight:600;">✓ Yayında</span>
                  <?php endif; ?>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Müzayede / Lot Düzenleme Modalı -->
    <div id="editLotModal" class="martello-modal" style="display:none; position:fixed; z-index:9999; left:0; top:0; width:100%; height:100%; background:rgba(0,0,0,0.85); backdrop-filter:blur(6px); align-items:center; justify-content:center;">
      <div style="background:#12161f; border:1px solid #2a3449; border-radius:12px; max-width:540px; width:92%; padding:24px; box-shadow:0 10px 30px rgba(0,0,0,0.8); position:relative;">
        <button type="button" onclick="closeEditLotModal()" style="position:absolute; right:16px; top:16px; background:none; border:none; color:#888; font-size:1.5rem; cursor:pointer;">&times;</button>
        
        <div style="color:var(--gold-primary); font-size:1.15rem; font-weight:700; margin-bottom:4px;">
          Müzayede Lotu Düzenle (<span id="editLotKoduModal"></span>)
        </div>
        <div style="color:#888; font-size:0.8rem; margin-bottom:16px;">
          Fiyat, pey adımı, süre ve müzayede durumunu güncelleyin. Değişiklikler anında canlı kataloğa yansır.
        </div>

        <form method="POST">
          <input type="hidden" name="action" value="edit_lot">
          <input type="hidden" name="lot_id" id="editLotId">

          <div class="form-group" style="margin-bottom:10px;">
            <label class="form-label">Eser / Lot Başlığı</label>
            <input type="text" name="baslik" id="editLotBaslik" class="form-control" required>
          </div>

          <div style="display:grid; grid-template-columns:1fr 1fr; gap:10px; margin-bottom:10px;">
            <div class="form-group">
              <label class="form-label">Kategori</label>
              <input type="text" name="kategoriAdi" id="editLotKategori" class="form-control" required>
            </div>
            <div class="form-group">
              <label class="form-label">Müzayede Durumu</label>
              <select name="durum" id="editLotDurum" class="form-control">
                <option value="ACIK_ARTIRMADA">Açık Artırmada (Aktif)</option>
                <option value="DURAKLATILDI">Duraklatıldı</option>
                <option value="SATILDI">Satıldı / Kapanmış</option>
                <option value="IPTAL_EDILDI">İptal Edildi</option>
              </select>
            </div>
          </div>

          <div style="display:grid; grid-template-columns:1fr 1fr 1fr; gap:10px; margin-bottom:10px;">
            <div class="form-group">
              <label class="form-label">Başlangıç (TRY)</label>
              <input type="number" step="500" name="baslangicFiyati" id="editLotBaslangic" class="form-control" required>
            </div>
            <div class="form-group">
              <label class="form-label">Rezerv Fiyat (TRY)</label>
              <input type="number" step="500" name="rezervFiyat" id="editLotRezerv" class="form-control" required>
            </div>
            <div class="form-group">
              <label class="form-label">Pey Adımı (TRY)</label>
              <input type="number" step="100" name="minimumArtisTutari" id="editLotArtis" class="form-control" required>
            </div>
          </div>

          <div class="form-group" style="margin-bottom:18px;">
            <label class="form-label">Kalan Süre Metni (Örn: 5 saat 23 dakika)</label>
            <input type="text" name="kalanSureMetni" id="editLotSure" class="form-control" required>
          </div>

          <div style="display:flex; justify-content:flex-end; gap:10px;">
            <button type="button" onclick="closeEditLotModal()" class="btn-outline-gold" style="padding:10px 18px; cursor:pointer;">Vazgeç</button>
            <button type="submit" class="btn-register-glow" style="padding:10px 24px; cursor:pointer;">Değişiklikleri Kaydet</button>
          </div>
        </form>
      </div>
    </div>

    <script>
    function openEditLotModal(lot) {
      document.getElementById('editLotId').value = lot.id;
      document.getElementById('editLotKoduModal').textContent = lot.lotKodu || ('AUC-' + lot.id);
      document.getElementById('editLotBaslik').value = lot.baslik;
      document.getElementById('editLotKategori').value = lot.kategoriAdi || 'Antika & Sanat';
      document.getElementById('editLotBaslangic').value = lot.baslangicFiyati;
      document.getElementById('editLotRezerv').value = lot.rezervFiyat;
      document.getElementById('editLotArtis').value = lot.minimumArtisTutari;
      document.getElementById('editLotSure').value = lot.kalanSureMetni || '5 saat 23 dakika';
      document.getElementById('editLotDurum').value = lot.durum || 'ACIK_ARTIRMADA';
      document.getElementById('editLotModal').style.display = 'flex';
    }
    function closeEditLotModal() {
      document.getElementById('editLotModal').style.display = 'none';
    }
    </script>

    <!-- 4. TAB: EKSPERTİZ ATAMA (media_1795014776298_9cbae966.jpg ile Birebir) -->
    <div id="admTab_ekspertiz" class="tab-content <?= $activeTab === 'ekspertiz' ? 'active' : '' ?>">
      <div class="page-breadcrumb">ADMIN / ÇALIŞMA ALANI</div>
      <h1 class="page-heading">Ekspertiz atama</h1>
      <p class="page-subheading" style="margin-bottom:24px;">Seçkin müzayede deneyiminizi yönetin</p>

      <div class="panel-card" style="padding:0; overflow:hidden;">
        <table class="data-table" style="margin:0;">
          <thead>
            <tr>
              <th style="padding-left:24px;">Lot</th>
              <th>Ürün</th>
              <th>Durum</th>
              <th style="padding-right:24px; text-align:right;">İşlem</th>
            </tr>
          </thead>
          <tbody>
            <tr>
              <td style="padding-left:24px; font-weight:600; color:#fff;">AUC-1042</td>
              <td style="font-weight:600; color:#fff;">1968 İsviçre Vintage Saat</td>
              <td><span style="color:#ef4444; font-weight:500;">Reddedildi</span></td>
              <td style="padding-right:24px; text-align:right;">
                <div style="display:inline-flex; gap:8px;">
                  <button type="button" class="btn-table-action" onclick="alert('AUC-1042 onaylandı ve müzayedeye hazır statüsüne alındı.')">Onayla</button>
                  <button type="button" class="btn-table-action" onclick="alert('AUC-1042 gerekçesiyle reddedildi.')">Reddet</button>
                  <button type="button" class="btn-table-action" onclick="alert('AUC-1042 için bağımsız Marmara Ekspertiz Heyeti atandı.')">Ekspertiz ata</button>
                </div>
              </td>
            </tr>

            <tr>
              <td style="padding-left:24px; font-weight:600; color:#fff;">AUC-1043</td>
              <td style="font-weight:600; color:#fff;">Koleksiyon Analog Kamera</td>
              <td><span style="color:#60a5fa; font-weight:500;">İncelemede</span></td>
              <td style="padding-right:24px; text-align:right;">
                <div style="display:inline-flex; gap:8px;">
                  <button type="button" class="btn-table-action" onclick="alert('AUC-1043 onaylandı ve müzayedeye hazır statüsüne alındı.')">Onayla</button>
                  <button type="button" class="btn-table-action" onclick="alert('AUC-1043 gerekçesiyle reddedildi.')">Reddet</button>
                  <button type="button" class="btn-table-action" onclick="alert('AUC-1043 için bağımsız Optik Ekspertiz Heyeti atandı.')">Ekspertiz ata</button>
                </div>
              </td>
            </tr>

            <tr>
              <td style="padding-left:24px; font-weight:600; color:#fff;">AUC-1044</td>
              <td style="font-weight:600; color:#fff;">Çağdaş Sanat Baskısı</td>
              <td><span style="color:#60a5fa; font-weight:500;">İncelemede</span></td>
              <td style="padding-right:24px; text-align:right;">
                <div style="display:inline-flex; gap:8px;">
                  <button type="button" class="btn-table-action" onclick="alert('AUC-1044 onaylandı ve müzayedeye hazır statüsüne alındı.')">Onayla</button>
                  <button type="button" class="btn-table-action" onclick="alert('AUC-1044 gerekçesiyle reddedildi.')">Reddet</button>
                  <button type="button" class="btn-table-action" onclick="alert('AUC-1044 için Çağdaş Sanat Heyeti atandı.')">Ekspertiz ata</button>
                </div>
              </td>
            </tr>

            <tr>
              <td style="padding-left:24px; font-weight:600; color:#fff;">AUC-1045</td>
              <td style="font-weight:600; color:#fff;">Klasik Otomobil Motor Parçası</td>
              <td><span style="color:#60a5fa; font-weight:500;">İncelemede</span></td>
              <td style="padding-right:24px; text-align:right;">
                <div style="display:inline-flex; gap:8px;">
                  <button type="button" class="btn-table-action" onclick="alert('AUC-1045 onaylandı ve müzayedeye hazır statüsüne alındı.')">Onayla</button>
                  <button type="button" class="btn-table-action" onclick="alert('AUC-1045 gerekçesiyle reddedildi.')">Reddet</button>
                  <button type="button" class="btn-table-action" onclick="alert('AUC-1045 için Klasik Araç Ekspertiz Heyeti atandı.')">Ekspertiz ata</button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- 6. TAB: KOMİSYON VE ÜCRET TANIMLAMA -->
    <div id="admTab_komisyon" class="tab-content <?= $activeTab === 'komisyon' ? 'active' : '' ?>">
      <div class="page-breadcrumb">ADMIN / ÇALIŞMA ALANI</div>
      <h1 class="page-heading">Komisyon ve Ücret Tanımlama</h1>
      <p class="page-subheading">Platform gelir modeli, kademeli komisyon ve emanet bloke oranları (Strategy)</p>

      <div class="panel-card">
        <div style="display:grid; grid-template-columns:1fr 1fr; gap:20px;">
          <div>
            <div style="font-weight:700; color:var(--gold-primary); margin-bottom:8px;">Sanat & Antika Komisyon Stratejisi (ArtAntiqueCommission)</div>
            <p style="font-size:0.85rem; color:var(--text-muted); line-height:1.5;">
              Kapanış tutarı üzerinden <strong>%8 Alıcı / Satıcı Hizmet Payı</strong> + <strong>₺1.500 Sabit Kurul Bedeli</strong> tahsil edilerek teminat emanet hesabına yansıtılır.
            </p>
          </div>
          <div>
            <div style="font-weight:700; color:var(--gold-primary); margin-bottom:8px;">Özel Taşıt & VIP Stratejisi (VehicleCommission)</div>
            <p style="font-size:0.85rem; color:var(--text-muted); line-height:1.5;">
              Yüksek hacimli lotlarda <strong>%4 Kademeli Komisyon</strong> ve doğrudan blokeli emanet takas (Escrow) uygulanır.
            </p>
          </div>
        </div>
      </div>
    </div>

    <!-- 7. TAB: RAPORLAR -->
    <div id="admTab_raporlar" class="tab-content <?= $activeTab === 'raporlar' ? 'active' : '' ?>">
      <div class="page-breadcrumb">ADMIN / ÇALIŞMA ALANI</div>
      <h1 class="page-heading">Ekspertiz ve Müzayede Raporları</h1>
      <p class="page-subheading">Onaylı değerlendirmeler ve 1 yıldan eski verilerin arşivleme politikası (Cold Storage)</p>

      <div class="panel-card">
        <table class="data-table">
          <thead><tr><th>Rapor No</th><th>Lot Kodu</th><th>Piyasa Değeri</th><th>Durum</th><th>Arşiv Durumu</th></tr></thead>
          <tbody>
            <?php foreach ($reports as $r): ?>
              <tr>
                <td><code><?= $r['raporNo'] ?></code></td>
                <td>AUC-1001</td>
                <td style="color:var(--gold-bright); font-weight:700;"><?= $formatPrice((float)$r['onerilenPiyasaDegeri']) ?></td>
                <td><span class="badge badge-success">✓ Onaylandı</span></td>
                <td><span class="badge badge-gold">Hot Storage (&lt;1 Yıl)</span></td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>

    <!-- 8. TAB: İHTİLAF ÇÖZÜMÜ -->
    <div id="admTab_ihtilaflar" class="tab-content <?= $activeTab === 'ihtilaflar' ? 'active' : '' ?>">
      <div class="page-breadcrumb">ADMIN / ÇALIŞMA ALANI</div>
      <h1 class="page-heading">İhtilaf ve Tahkim Çözümü</h1>
      <p class="page-subheading">Alıcı ve satıcı uyuşmazlıklarında admin tahkim kararı ve bloke iade süreçleri</p>

      <div class="panel-card">
        <table class="data-table">
          <thead>
            <tr><th>Talep No</th><th>Lot Kodu</th><th>Konu</th><th>Açıklama</th><th>Tahkim Kararı</th></tr>
          </thead>
          <tbody>
            <tr>
              <td><code>DISP-2026-01</code></td>
              <td><span class="badge badge-gold">AUC-1001</span></td>
              <td>Kondisyon Uyuşmazlığı</td>
              <td>Teslim edilen eserin çerçevesinde mikro çizik tespit edildi.</td>
              <td>
                <div style="display:flex; gap:8px;">
                  <form method="POST" style="margin:0;">
                    <input type="hidden" name="action" value="resolve_dispute">
                    <input type="hidden" name="dispute_id" value="1">
                    <input type="hidden" name="decision" value="ALICI_HAKLI">
                    <button type="submit" class="btn-gold btn-sm" style="font-size:0.75rem;">Alıcı Haklı (İade Et)</button>
                  </form>
                  <form method="POST" style="margin:0;">
                    <input type="hidden" name="action" value="resolve_dispute">
                    <input type="hidden" name="dispute_id" value="1">
                    <input type="hidden" name="decision" value="SATICI_HAKLI">
                    <button type="submit" class="btn-outline-gold btn-sm" style="font-size:0.75rem;">Satıcı Haklı (Bloke Çöz)</button>
                  </form>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- 9. TAB: DENETİM İZİ (media_1795014776304_85a3b2e7.jpg ile Birebir) -->
    <div id="admTab_denetim" class="tab-content <?= $activeTab === 'denetim' ? 'active' : '' ?>">
      <div class="page-breadcrumb">ADMIN / ÇALIŞMA ALANI</div>
      <h1 class="page-heading">Denetim izi</h1>
      <p class="page-subheading" style="margin-bottom:24px;">Seçkin müzayede deneyiminizi yönetin</p>

      <div class="panel-card" style="padding:0; overflow:hidden;">
        <table class="data-table" style="margin:0;">
          <thead>
            <tr>
              <th style="padding-left:24px; width:35%;">Tarih</th>
              <th style="width:35%;">Rol</th>
              <th style="padding-right:24px; width:30%;">İşlem</th>
            </tr>
          </thead>
          <tbody>
            <?php
            $auditRows = [
              ['tarih' => '14.10.2026 22:13:47', 'rol' => 'Ekspertiz Uzmanı', 'islem' => 'report'],
              ['tarih' => '13.10.2026 12:57:19', 'rol' => 'Admin', 'islem' => 'listing'],
              ['tarih' => '13.10.2026 12:56:22', 'rol' => 'Alıcı', 'islem' => 'watch'],
              ['tarih' => '13.10.2026 12:56:18', 'rol' => 'Alıcı', 'islem' => 'bid'],
              ['tarih' => '13.10.2026 12:56:01', 'rol' => 'Alıcı', 'islem' => 'balance'],
              ['tarih' => '13.10.2026 12:55:46', 'rol' => 'Alıcı', 'islem' => 'balance'],
              ['tarih' => '13.10.2026 12:20:00', 'rol' => 'Canlı Destek', 'islem' => 'message'],
              ['tarih' => '13.10.2026 12:19:37', 'rol' => 'Canlı Destek', 'islem' => 'preference'],
              ['tarih' => '13.10.2026 12:19:25', 'rol' => 'Ekspertiz Uzmanı', 'islem' => 'preference'],
              ['tarih' => '13.10.2026 12:18:50', 'rol' => 'Satıcı', 'islem' => 'preference'],
              ['tarih' => '13.10.2026 12:18:03', 'rol' => 'Alıcı', 'islem' => 'preference'],
              ['tarih' => '13.10.2026 12:17:59', 'rol' => 'Alıcı', 'islem' => 'bid'],
              ['tarih' => '13.10.2026 12:17:27', 'rol' => 'Alıcı', 'islem' => 'preference'],
              ['tarih' => '13.10.2026 00:38:51', 'rol' => 'Ekspertiz Uzmanı', 'islem' => 'preference'],
              ['tarih' => '13.10.2026 00:37:27', 'rol' => 'Alıcı', 'islem' => 'preference'],
            ];
            foreach ($auditRows as $r):
            ?>
              <tr>
                <td style="padding-left:24px; color:#848d9a; font-size:0.85rem;"><?= $r['tarih'] ?></td>
                <td style="color:#f0f3f6; font-size:0.85rem; font-weight:500;"><?= $r['rol'] ?></td>
                <td style="padding-right:24px; color:#f0f3f6; font-size:0.85rem;"><?= $r['islem'] ?></td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>

    <!-- 10. TAB: SİSTEM AYARLARI (media_1795014776310_317f5780.jpg ile Birebir) -->
    <div id="admTab_ayarlar" class="tab-content <?= $activeTab === 'ayarlar' ? 'active' : '' ?>">
      <div class="page-breadcrumb">ADMIN / ÇALIŞMA ALANI</div>
      <h1 class="page-heading">Sistem ayarları</h1>
      <p class="page-subheading" style="margin-bottom:24px;">Seçkin müzayede deneyiminizi yönetin</p>

      <div class="panel-card" style="margin-bottom:20px;">
        <h3 class="panel-title" style="margin-bottom:8px;">Sistem Parametreleri (Use Case: Sistem Parametreleri Ayarlama)</h3>
        <p style="font-size:0.85rem; color:#848d9a; margin-bottom:16px;">
          Anti-Sniping son 1 dk kuralı süresi, asgari artış kuralı ve platform komisyon oranları.
        </p>
        <form method="POST" style="display:grid; grid-template-columns:1fr 1fr 1fr 1fr auto; gap:12px; align-items:flex-end;">
          <input type="hidden" name="action" value="update_system_params">
          <div>
            <label class="form-label">Anti-Sniping Süre (Sn)</label>
            <input type="number" name="anti_sniping_seconds" class="form-control" value="60" required>
          </div>
          <div>
            <label class="form-label">Min Pey Adımı (TRY)</label>
            <input type="number" name="min_bid_step" class="form-control" value="1500" required>
          </div>
          <div>
            <label class="form-label">Satıcı Komisyonu (%)</label>
            <input type="number" step="0.5" name="seller_commission" class="form-control" value="8.0" required>
          </div>
          <div>
            <label class="form-label">Alıcı Primi (%)</label>
            <input type="number" step="0.5" name="buyer_premium" class="form-control" value="12.0" required>
          </div>
          <div>
            <button type="submit" class="btn-gold" style="white-space:nowrap; padding:10px 18px;">Kaydet</button>
          </div>
        </form>
      </div>

      <div class="panel-card">
        <h3 class="panel-title" style="margin-bottom:8px;">Personel ve Yetkilendirme</h3>
        <p style="font-size:0.88rem; color:#848d9a; margin-bottom:16px;">
          Sisteme yeni Canlı Destek Temsilcisi veya Ekspertiz Uzmanı hesabı tanımlayın.
        </p>
        <button type="button" onclick="openDemoSupportModal()" class="btn-dark-pill" style="padding:10px 20px; font-size:0.85rem; cursor:pointer;">
          Demo destek temsilcisi ekle
        </button>
      </div>
    </div>

  </main>
</div>

<!-- Demo Destek Temsilcisi Modalı (Görseldeki Birebir Modal) -->
<div id="demoSupportModal" class="martello-modal" style="display:none; position:fixed; z-index:9999; left:0; top:0; width:100%; height:100%; background:rgba(0,0,0,0.85); backdrop-filter:blur(6px); align-items:center; justify-content:center;">
  <div style="background:#131722; border:1px solid #2a3449; border-radius:12px; max-width:440px; width:90%; padding:24px; box-shadow:0 10px 30px rgba(0,0,0,0.8); position:relative;">
    <button type="button" onclick="closeDemoSupportModal()" style="position:absolute; right:16px; top:16px; background:none; border:none; color:#888; font-size:1.4rem; cursor:pointer;">&times;</button>
    
    <div style="color:#d4af37; font-size:0.72rem; font-weight:700; letter-spacing:1px; text-transform:uppercase; margin-bottom:4px;">MARTELLO • DEMO İŞLEM</div>
    <div style="font-family:var(--font-serif); font-size:1.45rem; font-weight:600; color:#fff; margin-bottom:18px;">Demo destek temsilcisi</div>

    <form method="POST">
      <input type="hidden" name="action" value="create_staff">
      <input type="hidden" name="rol" value="CANLI_DESTEK">
      <div class="form-group" style="margin-bottom:14px;">
        <label class="form-label" style="font-size:0.82rem; color:#f0f3f6;">Temsilci adı *</label>
        <input type="text" name="ad" class="form-control" placeholder="Örn: Cansu Güneş" required style="border-radius:8px; background:#10131b;">
      </div>

      <div class="form-group" style="margin-bottom:14px;">
        <label class="form-label" style="font-size:0.82rem; color:#f0f3f6;">İş e-postası *</label>
        <input type="email" name="email" class="form-control" placeholder="cansu@martello.com" required style="border-radius:8px; background:#10131b;">
      </div>

      <div style="font-size:0.75rem; color:#788394; margin-bottom:20px;">
        Kayıt doğrudan sisteme işlenir ve Canlı Destek yetkisi tanımlanır.
      </div>

      <button type="submit" class="btn-gold-pill" style="width:100%; padding:12px; font-size:0.9rem; cursor:pointer;">
        Demo kaydı oluştur
      </button>
    </form>
  </div>
</div>

<script>
function switchAdminTab(tabId, btn) {
  // Hide all tab contents and remove active from nav items
  document.querySelectorAll('.tab-content').forEach(el => el.classList.remove('active'));
  document.querySelectorAll('.sidebar .nav-menu-item').forEach(el => el.classList.remove('active'));

  // Activate target tab content
  const target = document.getElementById('admTab_' + tabId);
  if (target) {
    target.classList.add('active');
  }

  // Activate menu item
  let navItem = null;
  if (btn && btn.closest) {
    navItem = btn.closest('.nav-menu-item');
  }
  if (!navItem) {
    const matchingBtn = document.querySelector(`.sidebar button[data-tab="${tabId}"]`) ||
                        document.querySelector(`.sidebar button[onclick*="'${tabId}'"]`);
    if (matchingBtn) {
      navItem = matchingBtn.closest('.nav-menu-item');
    }
  }
  if (navItem) {
    navItem.classList.add('active');
  }

  // Update URL state without page reload
  if (window.history && window.history.replaceState) {
    try {
      const url = new URL(window.location.href);
      url.searchParams.set('tab', tabId);
      window.history.replaceState({}, '', url);
    } catch(e) {}
  }
}

function openDemoSupportModal() {
  document.getElementById('demoSupportModal').style.display = 'flex';
}

function closeDemoSupportModal() {
  document.getElementById('demoSupportModal').style.display = 'none';
}
</script>
