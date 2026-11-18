<?php
declare(strict_types=1);

/** @var callable $formatPrice */
/** @var array $db */

$buyerId = 3; // Emre K. / Zeynep D. (Alıcı)
$lots = $db['lots'] ?? [];
$selectedLotId = (int)($_GET['lot_id'] ?? 1002);

$currentLot = null;
foreach ($lots as $l) {
    if ((int)$l['id'] === $selectedLotId) {
        $currentLot = $l;
        break;
    }
}
if (!$currentLot && !empty($lots)) {
    $currentLot = $lots[0];
}

$allBids = $db['bids'] ?? [];
$lotBids = array_filter($allBids, static fn($b) => (int)($b['lotId'] ?? 0) === (int)$currentLot['id']);
usort($lotBids, static fn($a, $b) => ($b['id'] ?? 0) <=> ($a['id'] ?? 0));

$orders = $db['orders'] ?? [];
$wonOrders = array_filter($orders, static fn($o) => (int)($o['aliciId'] ?? 0) === $buyerId || in_array($o['lotKodu'] ?? '', ['AUC-0991', 'AUC-0984'], true));

$watchlists = $db['watchlists'] ?? [];
$isWatched = false;
foreach ($watchlists as $wl) {
    if ((int)($wl['kullaniciId'] ?? 0) === $buyerId && (int)($wl['lotId'] ?? 0) === (int)$currentLot['id']) {
        $isWatched = true;
        break;
    }
}

$proxyBids = $db['proxyBids'] ?? [];
$activeProxy = null;
foreach ($proxyBids as $pb) {
    if ((int)($pb['lotId'] ?? 0) === (int)$currentLot['id'] && (int)($pb['aliciId'] ?? 0) === $buyerId && !empty($pb['aktifMi'])) {
        $activeProxy = $pb;
        break;
    }
}

$minNextBid = (float)($currentLot['mevcutEnYuksekTeklif'] ?? 0) + (float)($currentLot['minimumArtisTutari'] ?? 1500);

$activeTab = $_GET['tab'] ?? 'kazanilanlar';
?>

<div class="app-container">
  <!-- Sol Menü (Sidebar) -->
  <aside class="sidebar">
    <div>
      <div class="sidebar-section-title">Çalışma Alanı</div>
      <ul class="nav-menu">
        <li class="nav-menu-item <?= $activeTab === 'genel' ? 'active' : '' ?>">
          <button type="button" onclick="switchBuyerTab('genel')">
            <span class="menu-icon">⌂</span> Genel bakış
          </button>
        </li>
        <li class="nav-menu-item">
          <a href="?role=vitrin">
            <span class="menu-icon">🚀</span> Müzayede keşfet
          </a>
        </li>
        <li class="nav-menu-item <?= $activeTab === 'canli' ? 'active' : '' ?>">
          <button type="button" onclick="switchBuyerTab('canli')">
            <span class="menu-icon">⏱️</span> Canlı müzayede
          </button>
        </li>
        <li class="nav-menu-item <?= $activeTab === 'kazanilanlar' ? 'active' : '' ?>">
          <button type="button" onclick="switchBuyerTab('kazanilanlar')">
            <span class="menu-icon">🏆</span> Kazanılan lotlar
          </button>
        </li>
        <li class="nav-menu-item <?= $activeTab === 'takip' ? 'active' : '' ?>">
          <button type="button" onclick="switchBuyerTab('takip')">
            <span class="menu-icon">🔖</span> Takip edilenler
          </button>
        </li>
        <li class="nav-menu-item <?= $activeTab === 'bakiye' ? 'active' : '' ?>">
          <button type="button" onclick="switchBuyerTab('bakiye')">
            <span class="menu-icon">💳</span> Bakiye ve faturalar
          </button>
        </li>
        <li class="nav-menu-item <?= $activeTab === 'ayarlar' ? 'active' : '' ?>">
          <button type="button" onclick="switchBuyerTab('ayarlar')">
            <span class="menu-icon">⚙️</span> Ayarlar
          </button>
        </li>
        <li class="nav-menu-item">
          <a href="?role=support">
            <span class="menu-icon">❓</span> Destek ve Yardım
          </a>
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

    <!-- ====================================================================
         1. TAB: CANLI MÜZAYEDE / LOT DETAYI (media_1794924544618_e29ceefd.jpg)
         ==================================================================== -->
    <div id="buyerTab_canli" class="tab-content <?= $activeTab === 'canli' ? 'active' : '' ?>">
      <div class="page-breadcrumb">LOT DETAYI</div>
      <h1 class="page-heading" style="font-size:1.85rem; margin-bottom:20px;">
        <?= htmlspecialchars($currentLot['baslik']) ?> - Müzayede No: <?= htmlspecialchars($currentLot['lotKodu']) ?>
      </h1>

      <div class="lot-detail-container">
        <!-- Sol Sütun: Galeri (1 Büyük + 4 Küçük) ve Beyaz Ekspertiz Kartı -->
        <div>
          <!-- Galeri -->
          <div class="gallery-wrapper">
            <!-- 1 Büyük Saat Görseli -->
            <div class="main-watch-frame">
              <div style="font-size:7rem; filter:drop-shadow(0 10px 20px rgba(0,0,0,0.6));">⏱️</div>
              <div style="position:absolute; bottom:12px; left:16px; font-size:0.75rem; color:var(--text-muted);">
                1968 Swiss Chronograph Cal. 861 • Orijinal Kadran
              </div>
            </div>

            <!-- 4 Küçük Küçük Küçük Görseller (2x2 Grid) -->
            <div class="watch-thumbnails-grid">
              <div class="watch-thumb" title="Kadran Yakın Çekim" style="font-size:1.8rem;">🔍</div>
              <div class="watch-thumb" title="Tepe ve Kasa" style="font-size:1.8rem;">⚙️</div>
              <div class="watch-thumb" title="Mekanizma Bakımı" style="font-size:1.8rem;">🔬</div>
              <div class="watch-thumb" title="Orijinal Toka" style="font-size:1.8rem;">⌚</div>
            </div>
          </div>

          <!-- Açıklama Metni -->
          <p style="font-size:0.85rem; line-height:1.7; color:#a3abb6; margin-bottom:18px;">
            1968 İsviçre Vintage Saat: Nadir mekanik kalibre, kusursuz dönem orijinal kadranı, tepe ve tokasıyla eksiksiz koleksiyon niteliğindedir. Bağımsız ekspertiz heyetimizce laboratuvar ortamında fiziksel ve optik mikroskop değerlendirmelerinden geçirilmiştir.
          </p>

          <!-- Beyaz Ekspertiz Raporu Kartı (Görsel 2'deki Birebir Kart) -->
          <div class="expert-white-card">
            <div class="expert-card-top">
              <span class="expert-card-title">Ekspertiz Raporu</span>
              <span class="expert-card-badge">Expertiz seçki</span>
            </div>
            
            <ul class="expert-card-list">
              <li>Saat mekanizması ve kronograf fonksiyonları laboratuvar testlerinde kusursuz çalışmaktadır.</li>
              <li>Kadran, ibreler ve kasa %100 dönem fabrikasyon orijinali olup revizyon görmemiştir.</li>
              <li>E-imzalı blokzincir QR kodu ile orijinalliği platform ve akredite heyet garantisi altındadır.</li>
            </ul>

            <div class="expert-card-bottom">
              <div>
                <div style="font-size:0.75rem; color:#777;">Expertiz Stamp:</div>
                <div style="font-weight:700; color:#222; font-size:0.85rem;">29 saat 2022 • Burak Şahin</div>
              </div>
              <div class="expert-stamp-seal">
                EKSPERTİZ<br>SEÇKİ<br>★ ★ ★<br>DAMGALI
              </div>
            </div>
          </div>
        </div>

        <!-- Sağ Sütun: Güncel Teklif, Diğer Teklifler ve Yan Koleksiyon -->
        <div>
          <!-- Güncel Teklif Kartı -->
          <div class="bid-panel-card">
            <div class="bid-title-muted">Güncel teklif</div>
            <div class="bid-price-large">
              <?= $formatPrice((float)$currentLot['mevcutEnYuksekTeklif']) ?>
            </div>
            <div class="bid-timer-text" style="display:flex; align-items:center; justify-content:space-between;">
              <div>⏱️ <span class="live-countdown" data-seconds="<?= (!empty($currentLot['antiSnipingSayaci']['sonBitisZamani']) && strtotime($currentLot['antiSnipingSayaci']['sonBitisZamani']) > time()) ? (strtotime($currentLot['antiSnipingSayaci']['sonBitisZamani']) - time()) : 19320 ?>">5 saat 23 dakika</span></div>
              <button type="button" class="btn-outline-gold" style="font-size:0.7rem; padding:3px 8px; border-radius:4px; cursor:pointer;" onclick="setDemoTimer(45)" title="Jüriye göstermek için sayacı son 45 saniyeye ayarlar">⏱️ 45sn Test</button>
            </div>

            <div id="antiSnipingWarning" style="display:none; color:#f87171; font-size:0.78rem; margin:8px 0 12px 0; background:rgba(239,68,68,0.12); border:1px solid rgba(239,68,68,0.3); padding:6px 10px; border-radius:6px;">
              ⚠️ Son 1 dakika! Bu süre içinde pey verilirse süre 1 dakika uzatılacaktır.
            </div>

            <!-- Pey Ver / Kayıt Ol Butonu -->
            <form method="POST">
              <input type="hidden" name="action" value="place_bid">
              <input type="hidden" name="lot_id" value="<?= $currentLot['id'] ?>">
              <input type="hidden" name="user_id" value="<?= $buyerId ?>">
              <input type="hidden" id="remainingSecondsInput" name="remaining_seconds" value="19320">
              
              <div class="form-group" style="margin-bottom:12px;">
                <label class="form-label">Teklifiniz (Asgari: <?= $formatPrice($minNextBid) ?>)</label>
                <input type="number" step="500" id="bidAmountInput" name="bid_amount" class="form-control" value="<?= $minNextBid ?>" required>
              </div>

              <div style="display:grid; grid-template-columns:1fr 1fr; gap:8px; margin-bottom:14px;">
                <button type="button" class="btn-outline-gold btn-sm btn-quick-bid" data-add="1500">+1.500</button>
                <button type="button" class="btn-outline-gold btn-sm btn-quick-bid" data-add="3000">+3.000</button>
              </div>

              <button type="submit" class="btn-register-glow">
                Pey Ver
              </button>
            </form>

            <!-- Takip Listesine Ekle / Çıkar Butonu -->
            <form method="POST" style="margin-top:10px;">
              <input type="hidden" name="action" value="toggle_watchlist">
              <input type="hidden" name="user_id" value="<?= $buyerId ?>">
              <input type="hidden" name="lot_id" value="<?= $currentLot['id'] ?>">
              <input type="hidden" name="current_price" value="<?= $currentLot['mevcutEnYuksekTeklif'] ?>">
              <button type="submit" class="btn-outline-gold btn-sm" style="width:100%; justify-content:center;">
                <?= $isWatched ? '★ Takip Ediliyor (Listeden Çıkar)' : '☆ Listeye Ekle / Takip Et' ?>
              </button>
            </form>
          </div>

          <!-- Otomatik Teklif / Temsili Bütçe Kartı (Proxy Bidding) -->
          <div class="bid-panel-card" style="margin-top:16px;">
            <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:8px;">
              <div style="font-size:0.95rem; font-weight:700; color:var(--gold-primary); display:flex; align-items:center; gap:6px;">
                <span>🤖</span> Otomatik Teklif
              </div>
              <span class="badge <?= $activeProxy ? 'badge-success' : 'badge-gold' ?>">
                <?= $activeProxy ? 'Aktif' : 'Tanımlı Değil' ?>
              </span>
            </div>
            
            <p style="font-size:0.8rem; color:var(--text-muted); line-height:1.4; margin-bottom:12px;">
              Maksimum bütçenizi tanımlayın; siz ekran başında değilken bile sistem, rakip tekliflerin bir adım üzerine çıkacak şekilde adınıza otomatik teklif versin.
            </p>

            <?php if ($activeProxy): ?>
              <div style="background:rgba(63,185,80,0.1); border:1px solid rgba(63,185,80,0.3); border-radius:8px; padding:10px 12px; margin-bottom:12px; font-size:0.82rem;">
                <div style="color:#3fb950; font-weight:600; margin-bottom:2px;">✓ Otomatik Teklifiniz Devrede</div>
                <div style="color:#ddd;">Tavan Bütçeniz: <strong><?= $formatPrice((float)$activeProxy['maksimumButceTutari']) ?></strong></div>
                <div style="font-size:0.75rem; color:#aaa; margin-top:4px;">Yeni rakip teklif geldiğinde sistem anında karşı teklif basacaktır.</div>
              </div>
            <?php endif; ?>

            <form method="POST">
              <input type="hidden" name="action" value="set_proxy_bid">
              <input type="hidden" name="lot_id" value="<?= $currentLot['id'] ?>">
              <input type="hidden" name="user_id" value="<?= $buyerId ?>">
              
              <div class="form-group" style="margin-bottom:12px;">
                <label class="form-label">Maksimum Tavan Bütçeniz (TRY)</label>
                <input type="number" step="500" name="max_budget" class="form-control" value="<?= $activeProxy ? (float)$activeProxy['maksimumButceTutari'] : ($minNextBid + 15000) ?>" min="<?= $minNextBid ?>" required>
              </div>

              <button type="submit" class="btn-outline-gold" style="width:100%; justify-content:center; padding:10px; cursor:pointer;">
                <?= $activeProxy ? 'Tavan Bütçeyi Güncelle' : 'Otomatik Teklifi Başlat' ?>
              </button>
            </form>

            <!-- Canlı Simülasyon / Test Butonu (Jüriye Göstermek İçin) -->
            <div style="border-top:1px dashed #2a3449; margin-top:14px; padding-top:12px;">
              <div style="font-size:0.75rem; color:var(--text-muted); margin-bottom:6px;">🧪 Otomatik Teklifi Canlı Test Et:</div>
              <form method="POST" style="margin:0;">
                <input type="hidden" name="action" value="simulate_competitor_bid">
                <input type="hidden" name="lot_id" value="<?= $currentLot['id'] ?>">
                <button type="submit" class="btn-outline-gold btn-sm" style="width:100%; font-size:0.75rem; justify-content:center; color:#60a5fa !important; border-color:#60a5fa; cursor:pointer;" title="Başka bir katılımcı adına pey verir, sistemin hemen sizin adınıza otomatik karşı teklif üretmesini test edersiniz">
                  ⚡ Başka Alıcıdan Rakip Pey Gönder (+<?= $formatPrice((float)($currentLot['minimumArtisTutari'] ?? 1500)) ?>)
                </button>
              </form>
            </div>
          </div>

          <!-- Diğer Teklifler Listesi (Görsel 2'deki Birebir Liste) -->
          <div class="other-bids-card">
            <div class="other-bids-title">Diğer Teklifler</div>
            <div class="other-bid-row">
              <div class="other-bid-user">👤 yortiunann_</div>
              <div class="other-bid-time">5 saat 23:34</div>
            </div>
            <div class="other-bid-row">
              <div class="other-bid-user">👤 itamrorOki</div>
              <div class="other-bid-time">5 saat 23:33</div>
            </div>
            <div class="other-bid-row">
              <div class="other-bid-user">👤 sarat1024</div>
              <div class="other-bid-time">5 saat 23:33</div>
            </div>
            <div class="other-bid-row">
              <div class="other-bid-user">👤 duniharty</div>
              <div class="other-bid-time">5 saat 23:34</div>
            </div>
          </div>

          <!-- Yantoş Sanat Kollektion (Görsel 2 Alt Sağ) -->
          <div class="side-collection-card">
            <div style="font-size:0.85rem; font-weight:600; color:#fff; margin-bottom:12px;">Yantoş Sanat Kollektion</div>
            
            <div class="side-collection-item" onclick="window.location.href='?role=buyer&lot_id=1002'">
              <div class="side-collection-thumb">⏱️</div>
              <div>
                <div style="font-size:0.82rem; color:#fff; font-weight:500;">196B İsviçre Vintage Saat</div>
                <div style="font-size:0.75rem; color:var(--text-muted);">AUC-1642</div>
              </div>
            </div>

            <div class="side-collection-item" onclick="window.location.href='?role=buyer&lot_id=1001'">
              <div class="side-collection-thumb">📜</div>
              <div>
                <div style="font-size:0.82rem; color:#fff; font-weight:500;">1920'ler Osmanlı Halı Levhası</div>
                <div style="font-size:0.75rem; color:var(--text-muted);">AUC-1001</div>
              </div>
            </div>

            <div class="side-collection-item" onclick="window.location.href='?role=buyer&lot_id=1003'">
              <div class="side-collection-thumb">🏺</div>
              <div>
                <div style="font-size:0.82rem; color:#fff; font-weight:500;">Antika Seramik Vazo</div>
                <div style="font-size:0.75rem; color:var(--text-muted);">AUC-0991</div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- ====================================================================
         2. TAB: KAZANILAN LOTLAR (media_1795014776286_1ee53420.jpg ile Birebir)
         ==================================================================== -->
    <div id="buyerTab_kazanilanlar" class="tab-content <?= $activeTab === 'kazanilanlar' ? 'active' : '' ?>">
      <div class="page-breadcrumb">ALICI / ÇALIŞMA ALANI</div>
      <h1 class="page-heading">Kazanılan lotlar</h1>
      <p class="page-subheading" style="margin-bottom:24px;">Müzayede deneyiminizi profesyonelce yönetin.</p>

      <!-- Tablo Kutusu -->
      <div class="panel-card" style="padding:0; overflow:hidden; margin-bottom:24px;">
        <table class="data-table" style="margin:0;">
          <thead>
            <tr>
              <th style="padding-left:24px;">Lot</th>
              <th>Ürün</th>
              <th>Kategori</th>
              <th>Son Tarih</th>
              <th>Son Fiyat</th>
              <th>Ödeme Durumu</th>
              <th style="padding-right:24px;">İşlemler</th>
            </tr>
          </thead>
          <tbody>
            <tr>
              <td style="padding-left:24px;">
                <div style="display:flex; align-items:center; gap:12px;">
                  <img src="public/assets/images/vase.svg" alt="Vazo" style="width:42px; height:42px; border-radius:6px; object-fit:cover; border:1px solid #2a3449;">
                  <span style="font-weight:600; color:#fff;">AUC-0991</span>
                </div>
              </td>
              <td style="font-weight:600; color:#fff;">Antika Seramik Vazo</td>
              <td style="color:var(--text-muted);">Sanat</td>
              <td>12-Ekim-2026</td>
              <td style="font-family:var(--font-serif); font-weight:700; color:#fff;">₺43.500</td>
              <td><span style="color:#4ade80; font-weight:600;">Ödendi</span></td>
              <td style="padding-right:24px;">
                <a href="javascript:void(0)" onclick="alert('Fatura No: FAT-2026-0991 (PDF olarak indirildi)')" style="color:#d1b179; font-size:0.82rem; display:block; text-decoration:none;">Faturayı İndir</a>
                <a href="javascript:void(0)" onclick="alert('Kargo: MTR-98213840 (Teslim Edildi)')" style="color:#d1b179; font-size:0.82rem; display:block; margin-top:4px; text-decoration:none;">Kargoyu Takip Et</a>
              </td>
            </tr>

            <tr>
              <td style="padding-left:24px;">
                <div style="display:flex; align-items:center; gap:12px;">
                  <img src="public/assets/images/books.svg" alt="Kitap" style="width:42px; height:42px; border-radius:6px; object-fit:cover; border:1px solid #2a3449;">
                  <span style="font-weight:600; color:#fff;">AUC-0984</span>
                </div>
              </td>
              <td style="font-weight:600; color:#fff;">Nadir Kitap Koleksiyonu</td>
              <td style="color:var(--text-muted);">Sanat</td>
              <td>12-Ekim-2026</td>
              <td style="font-family:var(--font-serif); font-weight:700; color:#fff;">₺3.200</td>
              <td><span style="color:#4ade80; font-weight:600;">Ödendi</span></td>
              <td style="padding-right:24px;">
                <a href="javascript:void(0)" onclick="alert('Fatura No: FAT-2026-0984 (PDF olarak indirildi)')" style="color:#d1b179; font-size:0.82rem; display:block; text-decoration:none;">Faturayı İndir</a>
                <a href="javascript:void(0)" onclick="alert('Kargo: MTR-87321044 (Teslim Edildi)')" style="color:#d1b179; font-size:0.82rem; display:block; margin-top:4px; text-decoration:none;">Kargoyu Takip Et</a>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Alt İkili Panel: Ödeme Detayları (Sol) ve Son Kazandığınız Lotlar (Sağ) -->
      <div style="display:grid; grid-template-columns:1fr 1.35fr; gap:20px; align-items:start;">
        <!-- Sol: Ödeme Detayları Kutusu -->
        <div class="panel-card" style="margin-bottom:0;">
          <div style="font-size:0.95rem; font-weight:600; color:#fff; margin-bottom:14px;">Ödeme Detayları</div>
          
          <div style="display:flex; justify-content:space-between; margin-bottom:10px; font-size:0.85rem;">
            <span style="color:var(--text-muted);">Lot</span>
            <span style="color:#fff; font-weight:500;">AUC-0991</span>
          </div>

          <div style="display:flex; justify-content:space-between; margin-bottom:10px; font-size:0.85rem;">
            <span style="color:var(--text-muted);">Son Fiyat</span>
            <span style="color:#fff; font-weight:500;">₺43.500</span>
          </div>

          <div style="display:flex; justify-content:space-between; margin-bottom:10px; font-size:0.85rem;">
            <span style="color:var(--text-muted);">Ödeme Durumu</span>
            <span style="color:#fff; font-weight:500;">₺9.200</span>
          </div>

          <div style="display:flex; justify-content:space-between; margin-bottom:10px; font-size:0.85rem;">
            <span style="color:var(--text-muted);">Ödeme Son jini</span>
            <span style="color:#fff; font-weight:500;">₺3.200</span>
          </div>

          <div style="display:flex; justify-content:space-between; margin-top:12px; padding-top:10px; border-top:1px solid var(--border-color); font-size:0.85rem;">
            <span style="color:var(--text-muted);">Ödeme Detayları</span>
            <span style="color:#fff; font-weight:700;">₺12.800</span>
          </div>
        </div>

        <!-- Sağ: Son Kazandığınız Lotlar (Kartlar + İncele Butonları) -->
        <div class="panel-card" style="margin-bottom:0;">
          <div style="font-size:0.95rem; font-weight:600; color:#fff; margin-bottom:14px;">Son Kazandığınız Lotlar</div>
          
          <div style="display:grid; grid-template-columns:1fr 1fr; gap:16px;">
            <!-- Kart 1: Vazo -->
            <div style="background:#161c27; border:1px solid #263143; border-radius:10px; padding:14px; display:flex; flex-direction:column; justify-content:space-between;">
              <div style="display:flex; gap:12px; align-items:center; margin-bottom:14px;">
                <img src="public/assets/images/vase.svg" alt="Vazo" style="width:48px; height:48px; border-radius:8px; object-fit:cover; border:1px solid #333e50;">
                <div>
                  <div style="font-size:0.75rem; color:var(--text-muted);">AUC-0991</div>
                  <div style="font-size:0.84rem; font-weight:600; color:#fff;">Antika Seramik Vazo</div>
                </div>
              </div>
              <button type="button" onclick="switchBuyerTab('canli')" class="btn-gold-pill" style="width:100%; padding:8px 0; font-size:0.82rem; cursor:pointer;">
                İncele
              </button>
            </div>

            <!-- Kart 2: Kitap -->
            <div style="background:#161c27; border:1px solid #263143; border-radius:10px; padding:14px; display:flex; flex-direction:column; justify-content:space-between;">
              <div style="display:flex; gap:12px; align-items:center; margin-bottom:14px;">
                <img src="public/assets/images/books.svg" alt="Kitap" style="width:48px; height:48px; border-radius:8px; object-fit:cover; border:1px solid #333e50;">
                <div>
                  <div style="font-size:0.75rem; color:var(--text-muted);">AUC-0984</div>
                  <div style="font-size:0.84rem; font-weight:600; color:#fff;">Nadir Kitap Koleksiyonu</div>
                </div>
              </div>
              <button type="button" onclick="switchBuyerTab('canli')" class="btn-gold-pill" style="width:100%; padding:8px 0; font-size:0.82rem; cursor:pointer;">
                İncele
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- ====================================================================
         3. TAB: GENEL BAKIŞ & DİĞER SEKMELER
         ==================================================================== -->
    <div id="buyerTab_genel" class="tab-content <?= $activeTab === 'genel' ? 'active' : '' ?>">
      <div class="page-breadcrumb">ALICI / ÇALIŞMA ALANI</div>
      <h1 class="page-heading">Genel bakış</h1>
      <p class="page-subheading">Seçkin müzayede deneyiminizi yönetin</p>

      <div class="kpi-grid">
        <div class="kpi-card highlight">
          <div class="kpi-label">Kazanılan Lotlar</div>
          <div class="kpi-value">2</div>
        </div>
        <div class="kpi-card">
          <div class="kpi-label">Aktif Teklifler</div>
          <div class="kpi-value">1</div>
        </div>
        <div class="kpi-card">
          <div class="kpi-label">Takip Ettiklerim</div>
          <div class="kpi-value">3</div>
        </div>
        <div class="kpi-card">
          <div class="kpi-label">Toplam Harcama</div>
          <div class="kpi-value"><?= $formatPrice(22000) ?></div>
        </div>
      </div>
    </div>

    <!-- 4. TAB: BAKİYE VE FATURALAR -->
    <div id="buyerTab_bakiye" class="tab-content <?= $activeTab === 'bakiye' ? 'active' : '' ?>">
      <div class="page-breadcrumb">ALICI / ÇALIŞMA ALANI</div>
      <h1 class="page-heading">Bakiye ve Faturalar</h1>
      <p class="page-subheading">Cüzdanınızı yönetin ve 3D Secure ile bakiye yükleyin</p>

      <div style="display:grid; grid-template-columns:1fr 1fr; gap:24px;">
        <div class="panel-card">
          <div class="panel-header"><h3 class="panel-title">💳 Bakiye Yükle (3D Secure POS)</h3></div>
          <form method="POST">
            <input type="hidden" name="action" value="deposit_wallet">
            <input type="hidden" name="user_id" value="<?= $buyerId ?>">
            <div class="form-group">
              <label class="form-label">Yüklenecek Tutar (TRY)</label>
              <input type="number" name="amount" class="form-control" value="25000" required>
            </div>
            <button type="submit" class="btn-gold" style="width:100%; justify-content:center;">Bakiye Yükle</button>
          </form>
        </div>
      </div>
    </div>

    <!-- 5. TAB: İTİRAZLAR -->
    <div id="buyerTab_itirazlar" class="tab-content <?= $activeTab === 'itirazlar' ? 'active' : '' ?>">
      <div class="page-breadcrumb">ALICI / ÇALIŞMA ALANI</div>
      <h1 class="page-heading">İtiraz ve İhtilaf Talepleri</h1>
      <p class="page-subheading">Uyuşmazlık bildirimlerinizi takip edin</p>
      
      <div class="panel-card">
        <form method="POST">
          <input type="hidden" name="action" value="create_dispute">
          <input type="hidden" name="user_id" value="<?= $buyerId ?>">
          <input type="hidden" name="lot_id" value="1002">
          <div class="form-group">
            <label class="form-label">Konu</label>
            <select name="topic" class="form-control">
              <option value="KONDİSYON_UYUŞMAZLIĞI">Kondisyon Uyuşmazlığı</option>
              <option value="TESLİMAT_GECİKMESİ">Teslimat Gecikmesi</option>
            </select>
          </div>
          <div class="form-group">
            <label class="form-label">Açıklama</label>
            <textarea name="description" class="form-control" rows="3" required></textarea>
          </div>
          <button type="submit" class="btn-outline-gold">İtiraz Bildir</button>
        </form>
      </div>
    </div>

    <!-- 6. TAB: TAKİP EDİLENLER -->
    <div id="buyerTab_takip" class="tab-content <?= $activeTab === 'takip' ? 'active' : '' ?>">
      <div class="page-breadcrumb">ALICI / ÇALIŞMA ALANI</div>
      <h1 class="page-heading">Takip Ettiklerim</h1>
      <p class="page-subheading">İlgilendiğiniz veya teklif vermeyi düşündüğünüz eserleri tek panelden izleyin</p>

      <div class="panel-card">
        <table class="data-table">
          <thead>
            <tr><th>Lot Kodu</th><th>Eser Başlığı</th><th>Güncel Fiyat</th><th>Kalan Süre</th><th>İşlem</th></tr>
          </thead>
          <tbody>
            <tr>
              <td><span class="badge badge-gold">AUC-1642</span></td>
              <td style="font-weight:600; color:#fff;">1968 İsviçre Vintage Saat</td>
              <td style="color:var(--gold-bright); font-weight:700;"><?= $formatPrice((float)$currentLot['mevcutEnYuksekTeklif']) ?></td>
              <td>⏱️ 5 saat 23 dk</td>
              <td>
                <a href="?role=buyer&tab=canli&lot_id=1002" class="btn-gold btn-sm" style="text-decoration:none;">Müzayedeye Git</a>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

  </main>
</div>

<script>
function switchBuyerTab(tabId) {
  document.querySelectorAll('.sidebar .nav-menu-item').forEach(el => el.classList.remove('active'));
  document.querySelectorAll('.tab-content').forEach(el => el.classList.remove('active'));

  const activeBtn = event.currentTarget.closest('.nav-menu-item');
  if (activeBtn) activeBtn.classList.add('active');

  const target = document.getElementById('buyerTab_' + tabId);
  if (target) {
    target.classList.add('active');
  }
}
</script>
