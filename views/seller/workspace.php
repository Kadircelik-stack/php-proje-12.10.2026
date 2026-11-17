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
            <span class="menu-icon">⌂</span> Genel bakış
          </button>
        </li>
        <li class="nav-menu-item <?= $activeTab === 'yeni' ? 'active' : '' ?>">
          <button type="button" onclick="switchSellerTab('yeni')">
            <span class="menu-icon">🏷️</span> Yeni ürün ekle
          </button>
        </li>
        <li class="nav-menu-item <?= $activeTab === 'ilanlar' ? 'active' : '' ?>">
          <button type="button" onclick="switchSellerTab('ilanlar')">
            <span class="menu-icon">⭐</span> İlanlarım
          </button>
        </li>
        <li class="nav-menu-item <?= $activeTab === 'teklifler' ? 'active' : '' ?>">
          <button type="button" onclick="switchSellerTab('teklifler')">
            <span class="menu-icon">♡</span> Teklif geçmişi
          </button>
        </li>
        <li class="nav-menu-item <?= $activeTab === 'bakiye' ? 'active' : '' ?>">
          <button type="button" onclick="switchSellerTab('bakiye')">
            <span class="menu-icon">💳</span> Bakiye ve komisyon
          </button>
        </li>
        <li class="nav-menu-item <?= $activeTab === 'itirazlar' ? 'active' : '' ?>">
          <button type="button" onclick="switchSellerTab('itirazlar')">
            <span class="menu-icon">⚖️</span> İtirazlar
          </button>
        </li>
      </ul>
    </div>
  </aside>

  <!-- Ana İçerik Alanı -->
  <main class="main-content">

    <!-- 1. TAB: GENEL BAKIŞ (media_1794924544603_fd16da41.jpg ile Birebir) -->
    <div id="sellerTab_genel" class="tab-content <?= $activeTab === 'genel' ? 'active' : '' ?>">
      <div class="page-breadcrumb">SATICI / ÇALIŞMA ALANI</div>
      <h1 class="page-heading">Genel bakış</h1>
      <p class="page-subheading">Seçkin müzayede deneyiminizi yönetin</p>

      <!-- 4 Adet KPI Kartı (Sayfa 34 Üst Tasarım) -->
      <div class="kpi-grid">
        <div class="kpi-card">
          <div class="kpi-label">Aktif İlanlar</div>
          <div class="kpi-value">1</div>
        </div>
        <div class="kpi-card highlight">
          <div class="kpi-label">Toplam Satış</div>
          <div class="kpi-value"><?= $formatPrice(142800) ?></div>
        </div>
        <div class="kpi-card">
          <div class="kpi-label">Taslaklar</div>
          <div class="kpi-value">0</div>
        </div>
        <div class="kpi-card">
          <div class="kpi-label">Teklifler</div>
          <div class="kpi-value">48</div>
        </div>
      </div>

      <!-- Satış Performansı Kutusu -->
      <div class="panel-card">
        <h2 class="panel-title" style="margin-bottom:6px;">Satış performansı</h2>
        <p style="font-size:0.88rem; color:var(--text-muted); margin-bottom:20px;">
          İlanlarınızı ve yeni teklifleri tek yerden yönetin.
        </p>
        <button type="button" class="btn-gold" onclick="switchSellerTab('yeni')">
          Yeni İlan Oluştur
        </button>
      </div>
    </div>

    <!-- 2. TAB: YENİ ÜRÜN EKLE (FR-07, FR-31) -->
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
