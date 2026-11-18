<?php
declare(strict_types=1);

/** @var callable $formatPrice */
/** @var array $db */

$sellerId = 2; // Mehmet Kaya (Satıcı)
$products = array_filter($db['products'] ?? [], static fn($p) => (int)($p['saticiId'] ?? 0) === $sellerId);
$listings = array_filter($db['listings'] ?? [], static fn($l) => (int)($l['saticiId'] ?? 0) === $sellerId);
$categories = $db['categories'] ?? [];
$activeTab = $_GET['tab'] ?? 'genel';
?>

<div class="app-container">
  <!-- Sol Menü (Sidebar) — SRS Sayfa 34 Üst Görsel ile Birebir Uyumlu -->
  <aside class="sidebar">
    <div>
      <div class="sidebar-section-title">Çalışma Alanı</div>
      <ul class="nav-menu">
        <li class="nav-menu-item <?= $activeTab === 'genel' ? 'active' : '' ?>">
          <button type="button" data-tab="genel" onclick="switchSellerTab('genel', this)">
            <span class="menu-icon">
              <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                <path d="m3 9 9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path>
                <polyline points="9 22 9 12 15 12 15 22"></polyline>
              </svg>
            </span> Genel bakış
          </button>
        </li>
        <li class="nav-menu-item <?= $activeTab === 'yeni' ? 'active' : '' ?>">
          <button type="button" data-tab="yeni" onclick="switchSellerTab('yeni', this)">
            <span class="menu-icon">
              <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                <path d="M20.59 13.41l-7.17 7.17a2 2 0 0 1-2.83 0L2 12V2h10l8.59 8.59a2 2 0 0 1 0 2.82z"></path>
                <line x1="7" y1="7" x2="7.01" y2="7"></line>
              </svg>
            </span> Yeni ürün ekle
          </button>
        </li>
        <li class="nav-menu-item <?= $activeTab === 'ilanlar' ? 'active' : '' ?>">
          <button type="button" data-tab="ilanlar" onclick="switchSellerTab('ilanlar', this)">
            <span class="menu-icon">
              <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                <path d="M2 4l3 12h14l3-12-6 7-4-7-4 7-6-7z"></path>
                <line x1="4" y1="19" x2="20" y2="19"></line>
              </svg>
            </span> İlanlarım
          </button>
        </li>
        <li class="nav-menu-item <?= $activeTab === 'teklifler' ? 'active' : '' ?>">
          <button type="button" data-tab="teklifler" onclick="switchSellerTab('teklifler', this)">
            <span class="menu-icon">
              <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"></path>
              </svg>
            </span> Teklif geçmişi
          </button>
        </li>
        <li class="nav-menu-item <?= $activeTab === 'bakiye' ? 'active' : '' ?>">
          <button type="button" data-tab="bakiye" onclick="switchSellerTab('bakiye', this)">
            <span class="menu-icon">
              <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                <rect width="20" height="14" x="2" y="5" rx="2"></rect>
                <line x1="2" y1="10" x2="22" y2="10"></line>
              </svg>
            </span> Bakiye ve komisyon
          </button>
        </li>
        <li class="nav-menu-item <?= $activeTab === 'itirazlar' ? 'active' : '' ?>">                <li class="nav-menu-item <?= $activeTab === 'profil' ? 'active' : '' ?>">
          <button type="button" data-tab="profil" onclick="switchSellerTab('profil', this)">
            <span class="menu-icon"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg></span>
            Profil & Mağaza
          </button>
        </li>

          <button type="button" data-tab="itirazlar" onclick="switchSellerTab('itirazlar', this)">
            <span class="menu-icon">
              <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                <polyline points="9 11 12 14 22 4"></polyline>
                <path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"></path>
              </svg>
            </span> İtirazlar
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

    <!-- 1. TAB: GENEL BAKIŞ (media_1795014776287_77f7a951.jpg ile Birebir) -->
    <div id="sellerTab_genel" class="tab-content <?= $activeTab === 'genel' ? 'active' : '' ?>">
      <div class="page-breadcrumb">SATICI / ÇALIŞMA ALANI</div>
      <h1 class="page-heading">Genel bakış</h1>
      <p class="page-subheading" style="margin-bottom:24px;">Seçkin müzayede deneyiminizi yönetin</p>

      <!-- 4 KPI Kartı -->
      <div class="kpi-grid">
        <div class="kpi-card highlight">
          <div class="kpi-label">AKTİF İLANLAR</div>
          <div class="kpi-value">1</div>
        </div>
        <div class="kpi-card">
          <div class="kpi-label">TOPLAM SATIŞ</div>
          <div class="kpi-value"><?= $formatPrice(142800) ?></div>
        </div>
        <div class="kpi-card">
          <div class="kpi-label">TASLAKLAR</div>
          <div class="kpi-value">0</div>
        </div>
        <div class="kpi-card">
          <div class="kpi-label">TEKLİFLER</div>
          <div class="kpi-value">48</div>
        </div>
      </div>

      <!-- Yan Yana Çalışma Alanı ve Satış Seçenekleri -->
      <div style="display:grid; grid-template-columns:1.4fr 1fr; gap:24px; align-items:start;">
        <!-- Sol: Satış Performansı Kutusu -->
        <div class="panel-card" style="margin-bottom:0;">
          <h2 class="panel-title" style="margin-bottom:6px;">Satış performansı</h2>
          <p style="font-size:0.88rem; color:var(--text-muted); margin-bottom:20px;">
            İlanlarınızı ve yeni teklifleri tek yerden yönetin.
          </p>
          <div style="display:flex; gap:12px;">
            <button type="button" class="btn-gold-pill" style="padding:10px 22px; cursor:pointer;" data-tab="yeni" onclick="switchSellerTab('yeni', this)">
              Yeni ilan oluştur
            </button>
            <button type="button" class="btn-outline-gold" style="padding:10px 18px; cursor:pointer;" data-tab="ilanlar" onclick="switchSellerTab('ilanlar', this)">
              İlanlarımı İncele
            </button>
          </div>
        </div>

        <!-- Sağ: Satıcı Hızlı İpuçları & Seçenekler -->
        <div class="panel-card" style="margin-bottom:0;">
          <h3 class="panel-title" style="margin-bottom:12px;">Hakediş & Ekspertiz Durumu</h3>
          <div style="display:flex; flex-direction:column; gap:10px; font-size:0.85rem;">
            <div style="padding:10px 12px; background:#0e1218; border-radius:6px; border:1px solid var(--border-color); display:flex; justify-content:space-between; align-items:center;">
              <span>Net Çekilebilir Bakiye</span>
              <strong style="color:var(--gold-bright);">₺ 142.800</strong>
            </div>
            <button type="button" onclick="switchSellerTab('bakiye', this)" class="btn-outline-gold btn-sm" style="width:100%; justify-content:center; padding:8px;">
              Banka Hesabına Para Çek &rarr;
            </button>
            <button type="button" onclick="switchSellerTab('profil', this)" class="btn-outline-gold btn-sm" style="width:100%; justify-content:center; padding:8px;">
              Mağaza & IBAN Bilgilerini Güncelle
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- 2. TAB: YENİ ÜRÜN EKLE -->
    <div id="sellerTab_yeni" class="tab-content <?= $activeTab === 'yeni' ? 'active' : '' ?>">
      <div class="page-breadcrumb">SATICI / ÇALIŞMA ALANI</div>
      <h1 class="page-heading">Yeni Ürün Ekle</h1>
      <p class="page-subheading">Eserinizi açık artırmaya sunmak üzere ekspertiz incelemesine gönderin</p>

      <div class="panel-card">
        <form method="POST">
          <input type="hidden" name="action" value="create_listing">
          <input type="hidden" name="seller_id" value="<?= $sellerId ?>">

          <div style="display:grid; grid-template-columns:1fr 1fr; gap:16px;">
            <div class="form-group">
              <label class="form-label">Eser / Ürün Başlığı</label>
              <input type="text" name="baslik" class="form-control" placeholder="Örn: 1968 İsviçre Vintage Saat" required>
            </div>
            <div class="form-group">
              <label class="form-label">Kategori</label>
              <select name="kategoriId" class="form-control">
                <?php foreach ($categories as $cat): ?>
                  <option value="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['ad']) ?> (Komisyon: %<?= $cat['komisyonOraniYuzde'] ?>)</option>
                <?php endforeach; ?>
              </select>
            </div>
          </div>

          <div class="form-group">
            <label class="form-label">Detaylı Açıklama</label>
            <textarea name="aciklama" class="form-control" rows="3" placeholder="Eserin tarihi, kökeni ve kondisyonu..."></textarea>
          </div>

          <div style="display:grid; grid-template-columns:1fr 1fr 1fr; gap:16px;">
            <div class="form-group">
              <label class="form-label">Başlangıç Fiyatı (TRY)</label>
              <input type="number" step="500" name="baslangicFiyati" class="form-control" value="32000" required>
            </div>
            <div class="form-group">
              <label class="form-label">Rezerv Fiyat (TRY)</label>
              <input type="number" step="500" name="rezervFiyat" class="form-control" value="40000" required>
            </div>
            <div class="form-group">
              <label class="form-label">Asgari Pey Adımı (TRY)</label>
              <input type="number" step="100" name="minimumArtisTutari" class="form-control" value="1500" required>
            </div>
          </div>

          <button type="submit" class="btn-gold" style="padding:12px 28px;">
            Ekspertiz Onayına Gönder
          </button>
        </form>
      </div>
    </div>

    <!-- 3. TAB: İLANLARIM -->
    <div id="sellerTab_ilanlar" class="tab-content <?= $activeTab === 'ilanlar' ? 'active' : '' ?>">
      <div class="page-breadcrumb">SATICI / ÇALIŞMA ALANI</div>
      <h1 class="page-heading">İlanlarım</h1>
      <p class="page-subheading">Açık artırma ve onay sürecindeki tüm ilanlarınız</p>

      <div class="panel-card">
        <table class="data-table">
          <thead>
            <tr>
              <th>İlan Kodu</th>
              <th>Ürün Adı</th>
              <th>Ekspertiz Durumu</th>
              <th>Statü</th>
              <th>İşlem</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($listings as $l): ?>
              <tr>
                <td><span class="badge badge-gold"><?= htmlspecialchars($l['ilanKodu']) ?></span></td>
                <td style="font-weight:600; color:#fff;">1968 İsviçre Vintage Saat</td>
                <td><span style="color:#3fb950;">✓ Ekspertiz Seçki Damgalı</span></td>
                <td><span class="badge badge-success"><?= htmlspecialchars($l['durum']) ?></span></td>
                <td>
                  <a href="?role=buyer&lot_id=1002" class="btn-outline-gold btn-sm">Müzayedeyi Gör</a>
                  <?php if (($l['durum'] ?? '') !== 'IPTAL_EDILDI'): ?>
                    <form method="POST" style="display:inline; margin-left:6px;" onsubmit="return confirm('Bu ilanı iptal etmek ve yayından çekmek istediğinize emin misiniz?');">
                      <input type="hidden" name="action" value="cancel_listing">
                      <input type="hidden" name="listing_id" value="<?= $l['id'] ?>">
                      <input type="hidden" name="seller_id" value="<?= $sellerId ?>">
                      <button type="submit" class="btn-outline-gold btn-sm" style="color:#f87171; border-color:rgba(239,68,68,0.35); padding:4px 8px; font-size:0.75rem;">İptal Et</button>
                    </form>
                  <?php endif; ?>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>

    <!-- 4. TAB: TEKLİF GEÇMİŞİ -->
    <div id="sellerTab_teklifler" class="tab-content <?= $activeTab === 'teklifler' ? 'active' : '' ?>">
      <div class="page-breadcrumb">SATICI / ÇALIŞMA ALANI</div>
      <h1 class="page-heading">Teklif Geçmişi</h1>
      <p class="page-subheading">Eserlerinize gelen tüm tekliflerin şeffaf kayıtları</p>
      
      <div class="panel-card">
        <table class="data-table">
          <thead>
            <tr><th>Lot No</th><th>Anonim Alıcı</th><th>Teklif Tutarı</th><th>Saat</th><th>Durum</th></tr>
          </thead>
          <tbody>
            <tr>
              <td>AUC-1642</td>
              <td>yortiunann_</td>
              <td style="font-weight:700; color:var(--gold-bright);"><?= $formatPrice(43500) ?></td>
              <td>5 saat 23:34</td>
              <td><span class="badge badge-gold">LİDER</span></td>
            </tr>
            <tr>
              <td>AUC-1642</td>
              <td>itamrorOki</td>
              <td><?= $formatPrice(42000) ?></td>
              <td>5 saat 23:33</td>
              <td><span class="badge badge-info">GEÇİLDİ</span></td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- 5. TAB: BAKİYE VE KOMİSYON -->
    <div id="sellerTab_bakiye" class="tab-content <?= $activeTab === 'bakiye' ? 'active' : '' ?>">
      <div class="page-breadcrumb">SATICI / ÇALIŞMA ALANI</div>
      <h1 class="page-heading">Bakiye ve Komisyon</h1>
      <p class="page-subheading">Kategori bazlı komisyon kesintileri ve hakedişleriniz</p>

      <!-- Yan Yana 2 Sütunlu Yerleşim: Bakiye Özeti ve Hakediş Transfer Formu -->
      <div style="display:grid; grid-template-columns:1fr 1.2fr; gap:24px; align-items:start;">
        <div class="panel-card" style="margin-bottom:0;">
          <div style="font-size:0.8rem; color:var(--text-muted);">TOPLAM HAKEDİŞ BAKİYESİ</div>
          <div style="font-family:var(--font-serif); font-size:2rem; font-weight:700; color:var(--gold-bright); margin:6px 0;">
            <?= $formatPrice(142800) ?>
          </div>
          <div style="color:var(--text-muted); font-size:0.85rem; margin-top:8px;">
            Antika & Sanat kategorisi komisyon oranı: <strong>%8</strong> • Sabit Ekspertiz Bedeli: <strong>1.500 TL</strong>
          </div>
          <div style="margin-top:16px; padding-top:12px; border-top:1px solid var(--border-color); font-size:0.82rem; color:#8b949e;">
            Bloke Edilen Ekspertiz Bedeli: <strong>₺ 1.500</strong><br>
            Net Çekilebilir Tutar: <strong style="color:#3fb950;">₺ 141.300</strong>
          </div>
        </div>

        <!-- Banka Hesabına Para Çekme Formu (Payout) -->
        <div class="panel-card" style="margin-bottom:0;">
          <h3 style="font-size:1.05rem; color:#fff; margin-bottom:12px;">Banka Hesabına Transfer (Hakediş Çekimi)</h3>
          <form method="POST">
            <input type="hidden" name="action" value="request_payout">
            <input type="hidden" name="seller_id" value="<?= $sellerId ?>">
            <div class="form-group" style="margin-bottom:12px;">
              <label class="form-label">Çekilecek Tutar (TRY)</label>
              <input type="number" name="amount" class="form-control" value="50000" min="1000" max="142800" required>
            </div>
            <div class="form-group" style="margin-bottom:14px;">
              <label class="form-label">Transfer Yapılacak IBAN</label>
              <input type="text" name="iban" class="form-control" value="TR34 0006 1005 1930 2938 4720 01" required>
            </div>
            <button type="submit" class="btn-gold" style="width:100%; justify-content:center; padding:10px;">
              Hakedişi Banka Hesabıma Aktar
            </button>
          </form>
        </div>
      </div>
    </div>

    <!-- 6. TAB: İTİRAZLAR -->
    <div id="sellerTab_itirazlar" class="tab-content <?= $activeTab === 'itirazlar' ? 'active' : '' ?>">
      <div class="page-breadcrumb">SATICI / ÇALIŞMA ALANI</div>
      <h1 class="page-heading">İtiraz ve İhtilaflar</h1>
      <p class="page-subheading">Alıcılardan gelen uyuşmazlıklar ve tahkim durumu</p>

      <div class="panel-card">
        <p style="color:var(--text-muted);">Şu anda adınıza açılmış aktif bir uyuşmazlık veya itiraz bulunmamaktadır.</p>
      </div>
    <!-- 7. TAB: PROFİL & MAĞAZA (Use Case 2: Profil Güncelleme) -->
    <div id="sellerTab_profil" class="tab-content <?= $activeTab === 'profil' ? 'active' : '' ?>">
      <div class="page-breadcrumb">SATICI / ÇALIŞMA ALANI</div>
      <h1 class="page-heading">Satıcı Profili & Doğrulama</h1>
      <p class="page-subheading">Koleksiyoner / Galeri bilgilerinizi ve hakediş transfer IBAN adresinizi güncelleyin</p>

      <div style="display:grid; grid-template-columns:1.5fr 1fr; gap:24px;">
        <div class="panel-card">
          <div class="panel-header">
            <h3 class="panel-title">Satıcı Bilgilerini Güncelle</h3>
            <span class="badge badge-success">✓ Onaylı Satıcı</span>
          </div>
          <form method="POST">
            <input type="hidden" name="action" value="update_profile">
            <input type="hidden" name="user_id" value="<?= $sellerId ?>">

            <div style="display:grid; grid-template-columns:1fr 1fr; gap:16px;">
              <div class="form-group">
                <label class="form-label">Mağaza / Temsilci Adı</label>
                <input type="text" name="adSoyad" class="form-control" value="Mehmet Kaya (Antika Galeri)" required>
              </div>
              <div class="form-group">
                <label class="form-label">E-posta</label>
                <input type="email" name="eposta" class="form-control" value="mehmet.kaya@antika.example" required>
              </div>
            </div>

            <div style="display:grid; grid-template-columns:1fr 1fr; gap:16px;">
              <div class="form-group">
                <label class="form-label">Telefon Numarası</label>
                <input type="text" name="telefon" class="form-control" value="+90 532 100 20 30" required>
              </div>
              <div class="form-group">
                <label class="form-label">Kayıtlı IBAN (Hakediş Hesabı)</label>
                <input type="text" name="iban" class="form-control" value="TR34 0006 1005 1930 2938 4720 01" required>
              </div>
            </div>

            <div class="form-group">
              <label class="form-label">Galeri / Mağaza Açıklaması</label>
              <textarea name="adres" class="form-control" rows="3">Çukurcuma Antikacılar Sokağı No: 18 Beyoğlu / İstanbul. 35 yıllık nümismatik ve mekanik saat koleksiyoncusu.</textarea>
            </div>

            <button type="submit" class="btn-gold" style="padding:10px 24px;">
              Profil ve IBAN Güncelle
            </button>
          </form>
        </div>

        <div class="panel-card">
          <div class="panel-header"><h3 class="panel-title">Doğrulama ve Rozetler</h3></div>
          <div style="display:flex; flex-direction:column; gap:14px;">
            <div style="padding:12px; background:#0e1218; border:1px solid var(--border-color); border-radius:8px;">
              <div style="font-weight:600; color:var(--gold-bright); font-size:0.9rem;">★ Kıdemli Koleksiyoner</div>
              <div style="font-size:0.75rem; color:var(--text-muted); margin-top:2px;">Platformda 50+ başarılı satış tamamlandı</div>
            </div>
            <div style="padding:12px; background:#0e1218; border:1px solid var(--border-color); border-radius:8px;">
              <div style="font-weight:600; color:#3fb950; font-size:0.9rem;">✓ T.C. Kimlik & Vergi Levhası Onaylı</div>
              <div style="font-size:0.75rem; color:var(--text-muted); margin-top:2px;">Tüm kurumsal evraklar eksiksiz denetlendi</div>
            </div>
            <div style="padding:12px; background:#0e1218; border:1px solid var(--border-color); border-radius:8px;">
              <div style="font-weight:600; color:#58a6ff; font-size:0.9rem;">Güvenilir Kargo Partneri</div>
              <div style="font-size:0.75rem; color:var(--text-muted); margin-top:2px;">Özel zırhlı ve sigortalı teslimat anlaşması</div>
            </div>
          </div>
        </div>
      </div>
    </div>

    </div>

  </main>
</div>

<script>
function switchSellerTab(tabId, btn) {
  // Hide all tab contents and remove active from nav items
  document.querySelectorAll('.tab-content').forEach(el => el.classList.remove('active'));
  document.querySelectorAll('.sidebar .nav-menu-item').forEach(el => el.classList.remove('active'));

  // Activate target tab content
  const target = document.getElementById('sellerTab_' + tabId);
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
</script>
