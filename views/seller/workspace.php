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
          <button type="button" onclick="switchSellerTab('genel')">
            <span class="menu-icon">
              <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                <path d="m3 9 9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path>
                <polyline points="9 22 9 12 15 12 15 22"></polyline>
              </svg>
            </span> Genel bakış
          </button>
        </li>
        <li class="nav-menu-item <?= $activeTab === 'yeni' ? 'active' : '' ?>">
          <button type="button" onclick="switchSellerTab('yeni')">
            <span class="menu-icon">
              <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                <path d="M20.59 13.41l-7.17 7.17a2 2 0 0 1-2.83 0L2 12V2h10l8.59 8.59a2 2 0 0 1 0 2.82z"></path>
                <line x1="7" y1="7" x2="7.01" y2="7"></line>
              </svg>
            </span> Yeni ürün ekle
          </button>
        </li>
        <li class="nav-menu-item <?= $activeTab === 'ilanlar' ? 'active' : '' ?>">
          <button type="button" onclick="switchSellerTab('ilanlar')">
            <span class="menu-icon">
              <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                <path d="M2 4l3 12h14l3-12-6 7-4-7-4 7-6-7z"></path>
                <line x1="4" y1="19" x2="20" y2="19"></line>
              </svg>
            </span> İlanlarım
          </button>
        </li>
        <li class="nav-menu-item <?= $activeTab === 'teklifler' ? 'active' : '' ?>">
          <button type="button" onclick="switchSellerTab('teklifler')">
            <span class="menu-icon">
              <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"></path>
              </svg>
            </span> Teklif geçmişi
          </button>
        </li>
        <li class="nav-menu-item <?= $activeTab === 'bakiye' ? 'active' : '' ?>">
          <button type="button" onclick="switchSellerTab('bakiye')">
            <span class="menu-icon">
              <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                <rect width="20" height="14" x="2" y="5" rx="2"></rect>
                <line x1="2" y1="10" x2="22" y2="10"></line>
              </svg>
            </span> Bakiye ve komisyon
          </button>
        </li>
        <li class="nav-menu-item <?= $activeTab === 'itirazlar' ? 'active' : '' ?>">
          <button type="button" onclick="switchSellerTab('itirazlar')">
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

      <!-- 4 Adet KPI Kartı -->
      <div class="kpi-grid">
        <div class="kpi-card">
          <div class="kpi-label">AKTİF İLANLAR</div>
          <div class="kpi-value">1</div>
        </div>
        <div class="kpi-card highlight">
          <div class="kpi-label">TOPLAM SATIŞ</div>
          <div class="kpi-value">₺142.800</div>
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

      <!-- Satış Performansı Kutusu -->
      <div class="panel-card">
        <h2 class="panel-title" style="margin-bottom:6px;">Satış performansı</h2>
        <p style="font-size:0.88rem; color:var(--text-muted); margin-bottom:20px;">
          İlanlarınızı ve yeni teklifleri tek yerden yönetin.
        </p>
        <button type="button" class="btn-gold-pill" style="padding:10px 22px; cursor:pointer;" onclick="switchSellerTab('yeni')">
          Yeni ilan oluştur
        </button>
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
                <td><a href="?role=buyer&lot_id=1002" class="btn-outline-gold btn-sm">Müzayedeyi Gör</a></td>
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

      <div class="panel-card">
        <div style="font-size:0.8rem; color:var(--text-muted);">TOPLAM HAKEDİŞ BAKİYESİ</div>
        <div style="font-family:var(--font-serif); font-size:2rem; font-weight:700; color:var(--gold-bright); margin:6px 0;">
          <?= $formatPrice(142800) ?>
        </div>
        <div style="color:var(--text-muted); font-size:0.85rem; margin-top:8px;">
          Antika & Sanat kategorisi komisyon oranı: <strong>%8</strong> • Sabit Ekspertiz Bedeli: <strong>1.500 TL</strong>
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
    </div>

  </main>
</div>

<script>
function switchSellerTab(tabId) {
  document.querySelectorAll('.sidebar .nav-menu-item').forEach(el => el.classList.remove('active'));
  document.querySelectorAll('.tab-content').forEach(el => el.classList.remove('active'));

  const activeBtn = event.currentTarget.closest('.nav-menu-item');
  if (activeBtn) activeBtn.classList.add('active');

  const target = document.getElementById('sellerTab_' + tabId);
  if (target) {
    target.classList.add('active');
  }
}
</script>
