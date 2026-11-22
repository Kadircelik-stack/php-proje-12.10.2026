<?php
declare(strict_types=1);

/** @var callable $formatPrice */
/** @var array $db */

$sellerId = (!empty($_SESSION['auth_user']['id']) && strtoupper((string)($_SESSION['auth_user']['rol'] ?? '')) === 'SATICI')
    ? (int)$_SESSION['auth_user']['id']
    : 2; // Mehmet Kaya (Satıcı)
$sellerRecord = null;
foreach ($db['users'] ?? [] as $u) {
    if ((int)($u['id'] ?? 0) === $sellerId) {
        $sellerRecord = $u;
        break;
    }
}
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
        <li class="nav-menu-item <?= $activeTab === 'itirazlar' ? 'active' : '' ?>">
          <button type="button" data-tab="itirazlar" onclick="switchSellerTab('itirazlar', this)">
            <span class="menu-icon">
              <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                <polyline points="9 11 12 14 22 4"></polyline>
                <path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"></path>
              </svg>
            </span> İtirazlar
          </button>
        </li>
        <li class="nav-menu-item <?= $activeTab === 'profil' ? 'active' : '' ?>">
          <button type="button" data-tab="profil" onclick="switchSellerTab('profil', this)">
            <span class="menu-icon"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg></span>
            Profil &amp; Mağaza
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
            <a href="?role=buyer&tab=kesfet">
              <span class="menu-icon"><svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="12" cy="12" r="10"/><path d="M12 8v4l3 3"/></svg></span>
              Müzayede Keşfet
            </a>
          </li>
          <li class="nav-menu-item">
            <a href="?role=buyer&tab=canli">
              <span class="menu-icon"><svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg></span>
              Canlı Müzayede Salonları
            </a>
          </li>
          <li class="nav-menu-item">
            <button type="button" onclick="return openInfoModal('categories', event)">
              <span class="menu-icon"><svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg></span>
              Ürün Kategorileri
            </button>
          </li>
          <li class="nav-menu-item">
            <button type="button" onclick="return openInfoModal('about', event)">
              <span class="menu-icon"><svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/></svg></span>
              Hakkımızda
            </button>
          </li>
          <li class="nav-menu-item">
            <button type="button" onclick="return openInfoModal('terms', event)">
              <span class="menu-icon"><svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg></span>
              Müzayede Şartları
            </button>
          </li>
          <li class="nav-menu-item">
            <a href="?role=yardim">
              <span class="menu-icon"><svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="12" cy="12" r="10"/><path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg></span>
              Yardım Merkezi
            </a>
          </li>
        </ul>
      </div>

    <form method="POST" action="index.php" style="margin:12px 0 8px 0;">
      <input type="hidden" name="action" value="logout">
      <button type="submit" class="btn-logout-luxury" style="width:100%; display:flex; align-items:center; justify-content:center; gap:8px; padding:10px 14px; font-size:0.82rem; cursor:pointer;">
        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
        <span>Çıkış Yap</span>
      </button>
    </form>

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
      <?php
        $activeSellerListingsCount = count(array_filter($listings, static fn($l) => ($l['durum'] ?? '') !== 'IPTAL_EDILDI'));
        $allLotsMap = [];
        foreach (($db['lots'] ?? []) as $ltItem) {
            $allLotsMap[(int)($ltItem['id'] ?? 0)] = $ltItem;
        }
        $allBidsList = $db['bids'] ?? [];
        usort($allBidsList, static fn($a, $b) => ((int)($b['id'] ?? 0)) <=> ((int)($a['id'] ?? 0)));
      ?>
      <div class="kpi-grid">
        <div class="kpi-card highlight">
          <div class="kpi-label">AKTİF İLANLAR</div>
          <div class="kpi-value"><?= max(1, $activeSellerListingsCount) ?></div>
        </div>
        <div class="kpi-card">
          <div class="kpi-label">TOPLAM SATIŞ</div>
          <div class="kpi-value"><?= $formatPrice(142800) ?></div>
        </div>
        <div class="kpi-card">
          <div class="kpi-label">KAYITLI ESERLER</div>
          <div class="kpi-value"><?= count($products) ?></div>
        </div>
        <div class="kpi-card">
          <div class="kpi-label">TEKLİFLER</div>
          <div class="kpi-value"><?= max(2, count($allBidsList)) ?></div>
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
          <h3 class="panel-title" style="margin-bottom:12px;">Hakediş &amp; Ekspertiz Durumu</h3>
          <div style="display:flex; flex-direction:column; gap:10px; font-size:0.84rem;">
            <div style="padding:10px 14px; background:#0D0B0A; border-radius:8px; border:1px solid rgba(200,170,110,0.16); display:flex; justify-content:space-between; align-items:center;">
              <span style="color:#A39B8F;">Net Çekilebilir Bakiye</span>
              <strong style="color:#E2C992; font-family:var(--font-serif); font-size:1.05rem;"><?= $formatPrice(142800) ?></strong>
            </div>
            <button type="button" onclick="switchSellerTab('bakiye', this)" class="btn-outline-gold btn-sm" style="width:100%; justify-content:center; padding:9px;">
              Banka Hesabına Para Çek &rarr;
            </button>
            <button type="button" onclick="switchSellerTab('profil', this)" class="btn-outline-gold btn-sm" style="width:100%; justify-content:center; padding:9px;">
              Mağaza &amp; IBAN Bilgilerini Güncelle
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- 2. TAB: YENİ ÜRÜN EKLE (Görsel Yükleme Destekli) -->
    <div id="sellerTab_yeni" class="tab-content <?= $activeTab === 'yeni' ? 'active' : '' ?>">
      <div class="page-breadcrumb">SATICI / YENİ ÜRÜN</div>
      <h1 class="page-heading">Yeni Ürün Ekle</h1>
      <p class="page-subheading">Eser bilgilerini ve fotoğrafını ekleyerek ekspertiz incelemesine gönderin.</p>

      <div class="panel-card">
        <form method="POST" enctype="multipart/form-data">
          <input type="hidden" name="action" value="create_listing">
          <input type="hidden" name="seller_id" value="<?= $sellerId ?>">

          <div style="display:grid; grid-template-columns:1fr 1fr; gap:14px;">
            <div class="form-group">
              <label class="form-label">Eser / Ürün Başlığı</label>
              <input type="text" name="baslik" class="form-control" placeholder="Örn: 1968 İsviçre Vintage Saat" required>
            </div>
            <div class="form-group">
              <label class="form-label">Kategori</label>
              <select name="kategoriId" class="form-control">
                <?php foreach ($categories as $cat): ?>
                  <option value="<?= (int)$cat['id'] ?>"><?= htmlspecialchars((string)($cat['ad'] ?? $cat['kategoriAdi'] ?? 'Kategori')) ?> (%<?= $cat['komisyonOraniYuzde'] ?? 7.0 ?>)</option>
                <?php endforeach; ?>
              </select>
            </div>
          </div>

          <!-- Ürün Görseli Yükleme Alanı -->
          <div class="form-group">
            <label class="form-label">Eser Fotoğrafı (JPG, PNG, WEBP — Maks. 5 MB)</label>
            <div style="display:flex; align-items:center; gap:16px; padding:14px 16px; background:#0D0B0A; border:1px dashed rgba(200,170,110,0.34); border-radius:10px; flex-wrap:wrap;">
              <div id="sellerImgPreviewBox" style="width:82px; height:82px; border-radius:8px; background:#141210; border:1px solid rgba(200,170,110,0.18); display:flex; align-items:center; justify-content:center; overflow:hidden; flex-shrink:0; color:#8C8478; font-size:0.72rem; text-align:center;">
                Önizleme
              </div>
              <div style="flex:1; min-width:220px;">
                <input type="file" name="product_image" id="sellerProductImage" accept="image/jpeg,image/png,image/webp" onchange="previewSellerImage(this)" class="form-control" style="padding:7px 10px; cursor:pointer;">
                <div style="font-size:0.73rem; color:#8C8478; margin-top:6px;">
                  Yüksek çözünürlüklü ve net bir eser fotoğrafı seçiniz. Yüklenen görsel ilanınızda ve vitrinde gösterilir.
                </div>
              </div>
            </div>
          </div>

          <div class="form-group">
            <label class="form-label">Açıklama</label>
            <textarea name="aciklama" class="form-control" rows="3" placeholder="Eserin dönemi, kökeni ve kondisyonu..."></textarea>
          </div>

          <div style="display:grid; grid-template-columns:1fr 1fr 1fr; gap:14px;">
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

          <button type="submit" class="btn-gold" style="padding:10px 24px;">
            Ekspertiz Onayına Gönder
          </button>
        </form>
      </div>
    </div>

    <script>
      function previewSellerImage(input) {
        const box = document.getElementById('sellerImgPreviewBox');
        if (!box || !input.files || !input.files[0]) return;
        const reader = new FileReader();
        reader.onload = function(e) {
          box.innerHTML = '<img src="' + e.target.result + '" alt="Önizleme" style="width:100%; height:100%; object-fit:cover; display:block;">';
        };
        reader.readAsDataURL(input.files[0]);
      }
    </script>

    <!-- 3. TAB: İLANLARIM -->
    <div id="sellerTab_ilanlar" class="tab-content <?= $activeTab === 'ilanlar' ? 'active' : '' ?>">
      <div class="page-breadcrumb">SATICI / İLANLARIM</div>
      <h1 class="page-heading">İlanlarım</h1>
      <p class="page-subheading">Açık artırma ve onay sürecindeki tüm ilanlarınız.</p>

      <?php
        $sellerProdMap = [];
        foreach (($db['products'] ?? []) as $sp) {
            $sellerProdMap[(int)($sp['id'] ?? 0)] = $sp;
        }
      ?>
      <div class="panel-card" style="padding:0; overflow:hidden;">
        <div class="table-scroll-x">
          <table class="data-table" style="min-width:680px;">
            <thead>
              <tr>
                <th style="padding-left:18px;">İlan Kodu</th>
                <th>Eser</th>
                <th>Ekspertiz</th>
                <th>Durum</th>
                <th style="padding-right:18px;">İşlem</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($listings as $l):
                $pRec = $sellerProdMap[(int)($l['urunId'] ?? 0)] ?? null;
                $pTitle = (string)($pRec['baslik'] ?? 'Müzayede Eseri');
                $pImg = (string)($pRec['gorselUrl'] ?? ($pRec['gorseller'][0] ?? 'public/assets/images/lot_ceramic_vase.jpg'));
                if (!str_contains($pImg, '/')) {
                    $pImg = 'public/assets/images/' . $pImg;
                }
              ?>
                <tr>
                  <td style="padding-left:18px;"><span class="badge badge-gold"><?= htmlspecialchars((string)$l['ilanKodu']) ?></span></td>
                  <td>
                    <div style="display:flex; align-items:center; gap:10px;">
                      <img src="<?= htmlspecialchars($pImg) ?>" alt="Eser" style="width:36px; height:36px; border-radius:6px; object-fit:cover; border:1px solid rgba(200,170,110,0.2);" onerror="this.src='public/assets/images/lot_ceramic_vase.jpg'">
                      <span style="font-weight:500; color:#F5F0E6;"><?= htmlspecialchars($pTitle) ?></span>
                    </div>
                  </td>
                  <td><span style="color:#A39B8F; font-size:0.78rem;"><?= !empty($l['ekspertizRaporId']) ? 'Onaylı' : 'İncelemede' ?></span></td>
                  <td><span class="badge <?= ($l['durum'] ?? '') === 'ONAYLANDI' || ($l['durum'] ?? '') === 'YAYINDA' ? 'badge-success' : 'badge-warning' ?>"><?= htmlspecialchars((string)$l['durum']) ?></span></td>
                  <td style="padding-right:18px; white-space:nowrap;">
                    <a href="?role=vitrin#katalog" class="btn-outline-gold btn-sm">Vitrin</a>
                    <?php if (($l['durum'] ?? '') !== 'IPTAL_EDILDI'): ?>
                      <form method="POST" style="display:inline; margin-left:6px;" onsubmit="return confirm('Bu ilanı iptal etmek istediğinize emin misiniz?');">
                        <input type="hidden" name="action" value="cancel_listing">
                        <input type="hidden" name="listing_id" value="<?= (int)$l['id'] ?>">
                        <input type="hidden" name="seller_id" value="<?= $sellerId ?>">
                        <button type="submit" class="btn-outline-gold btn-sm" style="color:#DDA3A3 !important; border-color:rgba(195,108,108,0.35);">İptal</button>
                      </form>
                    <?php endif; ?>
                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- 4. TAB: TEKLİF GEÇMİŞİ -->
    <div id="sellerTab_teklifler" class="tab-content <?= $activeTab === 'teklifler' ? 'active' : '' ?>">
      <div class="page-breadcrumb">SATICI / ÇALIŞMA ALANI</div>
      <h1 class="page-heading">Teklif Geçmişi</h1>
      <p class="page-subheading">Eserlerinize gelen tüm tekliflerin şeffaf kayıtları</p>
      
      <div class="panel-card" style="padding:0; overflow:hidden;">
        <table class="data-table" style="margin:0;">
          <thead>
            <tr>
              <th style="padding-left:20px;">Eser &amp; Lot No</th>
              <th>Anonim Alıcı</th>
              <th>Teklif Tutarı</th>
              <th>Zaman</th>
              <th style="padding-right:20px; text-align:right;">Durum</th>
            </tr>
          </thead>
          <tbody>
            <?php
              $highestSeenByLot = [];
              foreach ($allBidsList as $bRow):
                $bLotId = (int)($bRow['lotId'] ?? 1002);
                $bLot   = $allLotsMap[$bLotId] ?? null;
                $bCode  = (string)($bLot['lotKodu'] ?? 'AUC-1642');
                $bTitle = (string)($bLot['baslik'] ?? '1968 İsviçre Vintage Saat');
                $bImg   = (string)($bLot['gorselUrl'] ?? 'public/assets/images/lot_swiss_watch_1968.jpg');
                $bAmt   = (float)($bRow['tutar'] ?? $bRow['teklifTutari'] ?? 43500);
                $isLeader = empty($highestSeenByLot[$bLotId]);
                $highestSeenByLot[$bLotId] = true;
            ?>
              <tr>
                <td style="padding-left:20px;">
                  <div style="display:flex; align-items:center; gap:10px;">
                    <img src="<?= htmlspecialchars($bImg) ?>" alt="Lot" style="width:36px; height:36px; border-radius:6px; object-fit:cover; border:1px solid rgba(200,170,110,0.2);" onerror="this.src='public/assets/images/lot_swiss_watch_1968.jpg'">
                    <div>
                      <span class="badge badge-gold"><?= htmlspecialchars($bCode) ?></span>
                      <div style="font-size:0.83rem; color:#F5F0E6; font-weight:500; margin-top:2px;"><?= htmlspecialchars($bTitle) ?></div>
                    </div>
                  </div>
                </td>
                <td style="font-family:monospace; color:#EAE2D5;"><?= htmlspecialchars((string)($bRow['anonimKod'] ?? ('Bidder #' . ($bRow['aliciId'] ?? '101')))) ?></td>
                <td style="font-family:var(--font-serif); font-weight:700; color:#E2C992;"><?= $formatPrice($bAmt) ?></td>
                <td style="color:#A39B8F; font-size:0.8rem;"><?= !empty($bRow['zamanDamgasi']) ? date('d.m.Y H:i:s', strtotime((string)$bRow['zamanDamgasi'])) : 'Bugün' ?></td>
                <td style="padding-right:20px; text-align:right;">
                  <span class="badge <?= $isLeader ? 'badge-gold' : 'badge-info' ?>"><?= $isLeader ? 'LİDER' : 'GEÇİLDİ' ?></span>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>

    <!-- 5. TAB: BAKİYE VE KOMİSYON -->
    <div id="sellerTab_bakiye" class="tab-content <?= $activeTab === 'bakiye' ? 'active' : '' ?>">
      <div class="page-breadcrumb">SATICI / ÇALIŞMA ALANI</div>
      <h1 class="page-heading">Bakiye ve Komisyon</h1>
      <p class="page-subheading">Kategori bazlı komisyon kesintileri, emanet blokajları ve hakedişleriniz</p>

      <!-- Emanet Güvence Özeti -->
      <div style="background:rgba(200,170,110,0.06); border:1px solid rgba(200,170,110,0.22); border-radius:8px; padding:12px 16px; margin-bottom:18px; display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:10px;">
        <div>
          <strong style="color:#E2C992; font-size:0.82rem;">Emanet (Escrow) Koruması</strong>
          <span style="color:#A39B8F; font-size:0.77rem; display:block;">Satış tutarları alıcı teslimat onayına kadar emanet havuzunda korunur.</span>
        </div>
        <span class="badge badge-gold">Güvenli Tahsilat</span>
      </div>

      <!-- Yan Yana 2 Sütunlu Yerleşim: Bakiye Özeti ve Hakediş Transfer Formu -->
      <div style="display:grid; grid-template-columns:1fr 1.2fr; gap:24px; align-items:start;">
        <div class="panel-card" style="margin-bottom:0;">
          <div style="font-size:0.74rem; color:#A39B8F; text-transform:uppercase; letter-spacing:0.8px;">TOPLAM HAKEDİŞ BAKİYESİ</div>
          <div style="font-family:var(--font-serif); font-size:2rem; font-weight:700; color:#E2C992; margin:6px 0;">
            <?= $formatPrice(142800) ?>
          </div>
          <div style="color:#A39B8F; font-size:0.82rem; margin-top:8px;">
            Antika &amp; Sanat kategorisi komisyon oranı: <strong style="color:#F5F0E6;">%8</strong> · Sabit Ekspertiz Bedeli: <strong style="color:#F5F0E6;"><?= $formatPrice(1500) ?></strong>
          </div>
          <div style="margin-top:16px; padding-top:12px; border-top:1px solid rgba(200,170,110,0.14); font-size:0.81rem; color:#A39B8F; line-height:1.7;">
            Emanette Bloke Bekleyen Satış: <strong style="color:#E2C992;"><?= $formatPrice(64250) ?></strong><br>
            Net Çekilebilir Bakiye: <strong style="color:#F5F0E6;"><?= $formatPrice(141300) ?></strong>
          </div>
        </div>

        <!-- Banka Hesabına Para Çekme Formu (Payout) -->
        <div class="panel-card" style="margin-bottom:0;">
          <h3 class="panel-title" style="margin-bottom:12px;">Banka Hesabına Transfer (Hakediş Çekimi)</h3>
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

      <!-- Emanetteki Satışlarım ve Hakediş Durumu Tablosu -->
      <div class="panel-card" style="margin-top:24px; padding:0; overflow:hidden;">
        <div style="padding:14px 20px; border-bottom:1px solid rgba(200,170,110,0.14); display:flex; justify-content:space-between; align-items:center;">
          <div style="font-family:var(--font-serif); font-weight:600; color:#F5F0E6; font-size:1.05rem;">Emanetteki Satışlarım &amp; Hakediş Durumu</div>
          <span class="badge badge-gold">Emanet Takip Kütüğü</span>
        </div>
        <div class="table-scroll-x">
          <table class="data-table" style="margin:0; min-width:680px;">
            <thead>
              <tr>
                <th style="padding-left:20px;">Eser / Lot</th>
                <th>Alıcı</th>
                <th>Brüt Satış</th>
                <th>Komisyon &amp; Harç</th>
                <th>Net Hakedişiniz</th>
                <th>Emanet Durumu</th>
                <th style="padding-right:20px;">İşlem Durumu</th>
              </tr>
            </thead>
            <tbody>
              <tr>
                <td style="padding-left:20px; font-weight:500; color:#F5F0E6;">
                  1920'ler Osmanlı İpek Halı Levhası <span style="color:#C8AA6E; font-size:0.75rem;">(AUC-1001)</span>
                </td>
                <td>Zeynep Demir (#3)</td>
                <td style="font-family:var(--font-serif); font-weight:700; color:#E2C992;"><?= $formatPrice(70200) ?></td>
                <td style="color:#A39B8F; font-size:0.78rem;">-%8 (<?= $formatPrice(5200) ?>) + <?= $formatPrice(750) ?></td>
                <td style="font-family:var(--font-serif); font-weight:700; color:#F5F0E6;"><?= $formatPrice(64250) ?></td>
                <td>
                  <span class="badge badge-warning">Emanette Bloke</span>
                </td>
                <td style="padding-right:20px; color:#A39B8F; font-size:0.78rem;">
                  Kargo Dağıtımda (Alıcı onayı bekleniyor)
                </td>
              </tr>
              <tr>
                <td style="padding-left:20px; font-weight:500; color:#F5F0E6;">
                  Antika Sır Altı Çini ve Seramik Vazo <span style="color:#C8AA6E; font-size:0.75rem;">(AUC-0991)</span>
                </td>
                <td>Emre Karahan (#6)</td>
                <td style="font-family:var(--font-serif); font-weight:700; color:#E2C992;"><?= $formatPrice(13824) ?></td>
                <td style="color:#A39B8F; font-size:0.78rem;">-%8 (<?= $formatPrice(1024) ?>) + <?= $formatPrice(500) ?></td>
                <td style="font-family:var(--font-serif); font-weight:700; color:#F5F0E6;"><?= $formatPrice(12300) ?></td>
                <td>
                  <span class="badge badge-success">Satıcıya Aktarıldı</span>
                </td>
                <td style="padding-right:20px; color:#C8AA6E; font-size:0.78rem; font-weight:500;">
                  Hesaba Aktarıldı
                </td>
              </tr>
              <tr>
                <td style="padding-left:20px; font-weight:500; color:#F5F0E6;">
                  1885 Matbaa-i Âmire Nadir Kitap Koleksiyonu <span style="color:#C8AA6E; font-size:0.75rem;">(AUC-0984)</span>
                </td>
                <td>Emre Karahan (#6)</td>
                <td style="font-family:var(--font-serif); font-weight:700; color:#E2C992;"><?= $formatPrice(9752) ?></td>
                <td style="color:#A39B8F; font-size:0.78rem;">-%6 (<?= $formatPrice(552) ?>) + <?= $formatPrice(500) ?></td>
                <td style="font-family:var(--font-serif); font-weight:700; color:#F5F0E6;"><?= $formatPrice(8700) ?></td>
                <td>
                  <span class="badge badge-success">Satıcıya Aktarıldı</span>
                </td>
                <td style="padding-right:20px; color:#C8AA6E; font-size:0.78rem; font-weight:500;">
                  Hesaba Aktarıldı
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- 6. TAB: İTİRAZLAR -->
    <div id="sellerTab_itirazlar" class="tab-content <?= $activeTab === 'itirazlar' ? 'active' : '' ?>">
      <div class="page-breadcrumb">SATICI / İTİRAZ VE İHTİLAFLAR</div>
      <h1 class="page-heading">İtiraz ve İhtilaflar</h1>
      <p class="page-subheading">Alıcılardan gelen uyuşmazlıklar ve tahkim kurulu durumu.</p>

      <div class="panel-card">
        <p style="color:#A39B8F; margin:0;">Şu anda adınıza açılmış aktif bir uyuşmazlık veya itiraz dosyası bulunmamaktadır.</p>
      </div>
    </div>

    <!-- 7. TAB: PROFİL & MAĞAZA -->
    <?php
      $sellerFullName = trim((string)($sellerRecord['ad'] ?? 'Mehmet') . ' ' . (string)($sellerRecord['soyad'] ?? 'Kaya'));
      $sellerEmail    = (string)($sellerRecord['email'] ?? 'mehmet@antik.com');
      $sellerPhone    = (string)($sellerRecord['telefon'] ?? '+90 532 222 33 44');
    ?>
    <div id="sellerTab_profil" class="tab-content <?= $activeTab === 'profil' ? 'active' : '' ?>">
      <div class="page-breadcrumb">SATICI / PROFİL &amp; MAĞAZA</div>
      <h1 class="page-heading">Satıcı Profili &amp; Doğrulama</h1>
      <p class="page-subheading">Koleksiyoner / Galeri bilgilerinizi ve hakediş transfer IBAN adresinizi güncelleyin.</p>

      <div style="display:grid; grid-template-columns:1.5fr 1fr; gap:20px;">
        <div class="panel-card" style="margin-bottom:0;">
          <div class="panel-header">
            <h3 class="panel-title">Satıcı Bilgilerini Güncelle</h3>
            <span class="badge badge-success">Onaylı Satıcı</span>
          </div>
          <form method="POST">
            <input type="hidden" name="action" value="update_profile">
            <input type="hidden" name="user_id" value="<?= $sellerId ?>">

            <div style="display:grid; grid-template-columns:1fr 1fr; gap:14px;">
              <div class="form-group">
                <label class="form-label">Mağaza / Temsilci Adı</label>
                <input type="text" name="adSoyad" class="form-control" value="<?= htmlspecialchars($sellerFullName) ?>" required>
              </div>
              <div class="form-group">
                <label class="form-label">E-posta</label>
                <input type="email" name="eposta" class="form-control" value="<?= htmlspecialchars($sellerEmail) ?>" required>
              </div>
            </div>

            <div style="display:grid; grid-template-columns:1fr 1fr; gap:14px;">
              <div class="form-group">
                <label class="form-label">Telefon Numarası</label>
                <input type="text" name="telefon" class="form-control" value="<?= htmlspecialchars($sellerPhone) ?>" required>
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

        <div class="panel-card" style="margin-bottom:0;">
          <div class="panel-header"><h3 class="panel-title">Doğrulama ve Rozetler</h3></div>
          <div style="display:flex; flex-direction:column; gap:12px;">
            <div style="padding:12px 14px; background:#0D0B0A; border:1px solid rgba(200,170,110,0.14); border-radius:8px;">
              <div style="font-weight:600; color:#E2C992; font-size:0.86rem;">Kıdemli Koleksiyoner</div>
              <div style="font-size:0.75rem; color:#A39B8F; margin-top:2px;">Platformda 50+ başarılı satış tamamlandı</div>
            </div>
            <div style="padding:12px 14px; background:#0D0B0A; border:1px solid rgba(200,170,110,0.14); border-radius:8px;">
              <div style="font-weight:600; color:#F5F0E6; font-size:0.86rem;">T.C. Kimlik &amp; Vergi Levhası Onaylı</div>
              <div style="font-size:0.75rem; color:#A39B8F; margin-top:2px;">Tüm kurumsal evraklar eksiksiz denetlendi</div>
            </div>
            <div style="padding:12px 14px; background:#0D0B0A; border:1px solid rgba(200,170,110,0.14); border-radius:8px;">
              <div style="font-weight:600; color:#C8AA6E; font-size:0.86rem;">Güvenilir Kargo Partneri</div>
              <div style="font-size:0.75rem; color:#A39B8F; margin-top:2px;">Özel zırhlı ve sigortalı teslimat anlaşması</div>
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
