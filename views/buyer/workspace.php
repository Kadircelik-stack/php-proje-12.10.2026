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
          <button type="button" data-tab="genel" onclick="switchBuyerTab('genel', this)">
            <span class="menu-icon"><svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="m3 9 9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg></span>
            Genel bakış
          </button>
        </li>
        <li class="nav-menu-item">
          <a href="?role=vitrin">
            <span class="menu-icon"><svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polygon points="16.24 7.76 14.12 14.12 7.76 16.24 9.88 9.88 16.24 7.76"/></svg></span>
            Müzayede keşfet
          </a>
        </li>
        <li class="nav-menu-item <?= $activeTab === 'canli' ? 'active' : '' ?>">
          <button type="button" data-tab="canli" onclick="switchBuyerTab('canli', this)">
            <span class="menu-icon"><svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg></span>
            Canlı müzayede
          </button>
        </li>
        <li class="nav-menu-item <?= $activeTab === 'kazanilanlar' ? 'active' : '' ?>">
          <button type="button" data-tab="kazanilanlar" onclick="switchBuyerTab('kazanilanlar', this)">
            <span class="menu-icon"><svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M6 9H4.5a2.5 2.5 0 0 1 0-5H6"/><path d="M18 9h1.5a2.5 2.5 0 0 0 0-5H18"/><path d="M4 22h16"/><path d="M10 14.66V17c0 .55-.47.98-.97 1.21C7.85 18.75 7 20.24 7 22"/><path d="M14 14.66V17c0 .55.47.98.97 1.21C16.15 18.75 17 20.24 17 22"/><path d="M18 2H6v7a6 6 0 0 0 12 0V2Z"/></svg></span>
            Kazanılan lotlar
          </button>
        </li>
        <li class="nav-menu-item <?= $activeTab === 'takip' ? 'active' : '' ?>">
          <button type="button" data-tab="takip" onclick="switchBuyerTab('takip', this)">
            <span class="menu-icon"><svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="m19 21-7-4-7 4V5a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2v16z"/></svg></span>
            Takip edilenler
          </button>
        </li>
        <li class="nav-menu-item <?= $activeTab === 'bakiye' ? 'active' : '' ?>">
          <button type="button" data-tab="bakiye" onclick="switchBuyerTab('bakiye', this)">
            <span class="menu-icon"><svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect width="20" height="14" x="2" y="5" rx="2"/><line x1="2" x2="22" y1="10" y2="10"/></svg></span>
            Bakiye ve faturalar
          </button>
        </li>
                <li class="nav-menu-item <?= $activeTab === 'itirazlar' ? 'active' : '' ?>">
          <button type="button" data-tab="itirazlar" onclick="switchBuyerTab('itirazlar', this)">
            <span class="menu-icon"><svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 11 12 14 22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/></svg></span>
            İtiraz ve İhtilaf
          </button>
        </li>
        <li class="nav-menu-item <?= $activeTab === 'ayarlar' ? 'active' : '' ?>">
          <button type="button" data-tab="ayarlar" onclick="switchBuyerTab('ayarlar', this)">
            <span class="menu-icon"><svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><line x1="4" x2="4" y1="21" y2="14"/><line x1="4" x2="4" y1="10" y2="3"/><line x1="12" x2="12" y1="21" y2="12"/><line x1="12" x2="12" y1="8" y2="3"/><line x1="20" x2="20" y1="21" y2="16"/><line x1="20" x2="20" y1="12" y2="3"/><line x1="1" x2="7" y1="14" y2="14"/><line x1="9" x2="15" y1="8" y2="8"/><line x1="17" x2="23" y1="16" y2="16"/></svg></span>
            Ayarlar
          </button>
        </li>
        <li class="nav-menu-item">
          <a href="?role=support">
            <span class="menu-icon"><svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"/><path d="M12 17h.01"/></svg></span>
            Destek ve Yardım
          </a>
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

    <!-- ====================================================================
         1. TAB: CANLI MÜZAYEDE / LOT DETAYI (media_1794924544618_e29ceefd.jpg)
         ==================================================================== -->
    <div id="buyerTab_canli" class="tab-content <?= $activeTab === 'canli' ? 'active' : '' ?>">
      <div class="page-breadcrumb">LOT DETAYI</div>
      <h1 class="page-heading" style="font-size:1.85rem; margin-bottom:20px;">
        <?= htmlspecialchars($currentLot['baslik']) ?> - Müzayede No: <?= htmlspecialchars($currentLot['lotKodu']) ?>
      </h1>

      <div class="lot-detail-container">
        <!-- SOL SÜTUN: Galeri, Açıklama, Beyaz Ekspertiz Kartı ve Ekler Butonu -->
        <div>
          <!-- Galeri -->
          <div class="gallery-wrapper">
            <!-- 1 Büyük Saat Görseli (Gerçekçi Müze Kalitesi Eser Görseli) -->
            <div class="main-watch-frame" style="background:#090b10; display:flex; align-items:center; justify-content:center; overflow:hidden; position:relative; min-height:300px;">
              <img id="mainLotImage" src="public/assets/images/watch.jpg" alt="1968 Swiss Chronograph" style="max-height:280px; width:auto; object-fit:contain; filter:drop-shadow(0 15px 30px rgba(0,0,0,0.9));">
              <div style="position:absolute; bottom:12px; left:16px; font-size:0.75rem; color:#848d9a; background:rgba(0,0,0,0.6); padding:3px 8px; border-radius:4px;">
                1968 Swiss Chronograph Cal. 861 • Orijinal Kadran
              </div>
            </div>

            <!-- 4 Küçük Görsel / Detay İnceleme (SVG İkonik Makro Açıları) -->
            <div class="watch-thumbnails-grid">
              <div class="watch-thumb" title="Kadran Yakın Çekim" style="display:flex; align-items:center; justify-content:center; color:#c5a059; cursor:pointer;" onclick="document.getElementById('mainLotImage').src='public/assets/images/watch.jpg';">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="11" cy="11" r="8"/><line x1="21" x2="16.65" y1="21" y2="16.65"/><circle cx="11" cy="11" r="3"/></svg>
              </div>
              <div class="watch-thumb" title="Tepe ve Kasa" style="display:flex; align-items:center; justify-content:center; color:#c5a059; cursor:pointer;" onclick="document.getElementById('mainLotImage').src='public/assets/images/watch.jpg';">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-4 0v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1 0-4h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 2.83-2.83l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 4 0v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 0 4h-.09a1.65 1.65 0 0 0-1.51 1z"/></svg>
              </div>
              <div class="watch-thumb" title="Mekanizma Kalibresi" style="display:flex; align-items:center; justify-content:center; color:#c5a059; cursor:pointer;" onclick="document.getElementById('mainLotImage').src='public/assets/images/watch.jpg';">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/></svg>
              </div>
              <div class="watch-thumb" title="Akredite Damga" style="display:flex; align-items:center; justify-content:center; color:#c5a059; cursor:pointer;" onclick="document.getElementById('mainLotImage').src='public/assets/images/watch.jpg';">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
              </div>
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
                <div class="expert-reviewer-title">Uzman İncelemesi:</div>
                <div class="expert-stars">★ ★ ★ ★ ★</div>
              </div>
              
              <div class="expert-stamp-seal">
                EKSPERTİZ<br>SEÇKİ<br>★ ★ ★<br>DAMGALI
              </div>
            </div>
          </div>

          <!-- Müzayede Ekleri Butonu -->
          <div style="margin-top:14px;">
            <button type="button" class="btn-outline-gold" style="width:100%; justify-content:center; font-size:0.84rem; padding:10px 16px;" onclick="document.getElementById('lotEklerModal').style.display='flex'">
              Müzayede Ekleri ve Provenans Raporu &rarr;
            </button>
          </div>
        </div>

        <!-- SAĞ SÜTUN: Güncel Teklif, Diğer Teklifler ve Yan Koleksiyon -->
        <div>
          <!-- Güncel Teklif Kartı -->
          <div class="bid-panel-card">
            <div class="bid-title-muted">Güncel teklif</div>
            <div class="bid-price-large">
              <?= $formatPrice((float)$currentLot['mevcutEnYuksekTeklif']) ?>
            </div>
            <div class="bid-timer-text" style="display:flex; align-items:center; justify-content:space-between;">
              <div style="display:flex; align-items:center; gap:6px;">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                <span class="live-countdown" data-seconds="<?= (!empty($currentLot['antiSnipingSayaci']['sonBitisZamani']) && strtotime($currentLot['antiSnipingSayaci']['sonBitisZamani']) > time()) ? (strtotime($currentLot['antiSnipingSayaci']['sonBitisZamani']) - time()) : 19320 ?>">5 saat 23 dakika</span>
              </div>
              <button type="button" class="btn-outline-gold" style="font-size:0.7rem; padding:3px 8px; border-radius:4px; cursor:pointer;" onclick="setDemoTimer(45)" title="Jüriye göstermek için sayacı son 45 saniyeye ayarlar">45sn Test</button>
            </div>

            <div id="antiSnipingWarning" style="display:none; color:#f87171; font-size:0.78rem; margin:8px 0 12px 0; background:rgba(239,68,68,0.12); border:1px solid rgba(239,68,68,0.3); padding:6px 10px; border-radius:6px; align-items:center; gap:6px;">
              <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#f87171" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m21.73 18-8-14a2 2 0 0 0-3.48 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3Z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
              <span>Son 1 dakika! Bu süre içinde pey verilirse süre 1 dakika uzatılacaktır.</span>
            </div>

            <!-- Pey Ver Formu -->
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
                <button type="button" class="btn-outline-gold btn-sm btn-quick-bid" data-add="1500" onclick="const bi=document.getElementById('bidAmountInput'); if(bi) bi.value=parseFloat(bi.value||0)+1500;">+1.500</button>
                <button type="button" class="btn-outline-gold btn-sm btn-quick-bid" data-add="3000" onclick="const bi=document.getElementById('bidAmountInput'); if(bi) bi.value=parseFloat(bi.value||0)+3000;">+3.000</button>
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
              <button type="submit" class="btn-outline-gold btn-sm" style="width:100%; justify-content:center; display:inline-flex; align-items:center; gap:6px;">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="<?= $isWatched ? 'currentColor' : 'none' ?>" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m19 21-7-4-7 4V5a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2v16z"/></svg>
                <span><?= $isWatched ? 'Takip Ediliyor (Listeden Çıkar)' : 'Listeye Ekle / Takip Et' ?></span>
              </button>
            </form>
          </div>

          <!-- Otomatik Teklif / Temsili Bütçe Kartı (Proxy Bidding) -->
          <div class="bid-panel-card" style="margin-top:16px;">
            <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:8px;">
              <div style="font-size:0.95rem; font-weight:700; color:var(--gold-primary); display:flex; align-items:center; gap:8px;">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
                <span>Otomatik Teklif</span>
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
                <div style="color:#3fb950; font-weight:600; margin-bottom:2px;">Otomatik Teklifiniz Devrede</div>
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

            <!-- Canlı Simülasyon / Test Butonu -->
            <div style="border-top:1px dashed #2a3449; margin-top:14px; padding-top:12px;">
              <div style="font-size:0.75rem; color:var(--text-muted); margin-bottom:6px;">Otomatik Teklifi Canlı Test Et:</div>
              <form method="POST" style="margin:0;">
                <input type="hidden" name="action" value="simulate_competitor_bid">
                <input type="hidden" name="lot_id" value="<?= $currentLot['id'] ?>">
                <button type="submit" class="btn-outline-gold btn-sm" style="width:100%; font-size:0.75rem; justify-content:center; color:#60a5fa !important; border-color:#60a5fa; cursor:pointer;" title="Başka bir katılımcı adına pey verir, sistemin hemen sizin adınıza otomatik karşı teklif üretmesini test edersiniz">
                  Başka Alıcıdan Rakip Pey Gönder (+<?= $formatPrice((float)($currentLot['minimumArtisTutari'] ?? 1500)) ?>)
                </button>
              </form>
            </div>
          </div>

          <!-- Diğer Teklifler Listesi -->
          <div class="other-bids-card">
            <div class="other-bids-title">Diğer Teklifler</div>
            <div class="other-bid-row">
              <div class="other-bid-user" style="display:flex; align-items:center; gap:6px;">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                <span>yortiunann_</span>
              </div>
              <div class="other-bid-time">5 saat 23:34</div>
            </div>
            <div class="other-bid-row">
              <div class="other-bid-user" style="display:flex; align-items:center; gap:6px;">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                <span>itamrorOki</span>
              </div>
              <div class="other-bid-time">5 saat 23:33</div>
            </div>
            <div class="other-bid-row">
              <div class="other-bid-user" style="display:flex; align-items:center; gap:6px;">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                <span>sarat1024</span>
              </div>
              <div class="other-bid-time">5 saat 23:33</div>
            </div>
            <div class="other-bid-row">
              <div class="other-bid-user" style="display:flex; align-items:center; gap:6px;">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                <span>duniharty</span>
              </div>
              <div class="other-bid-time">5 saat 23:34</div>
            </div>
          </div>

          <!-- Seçkin Sanat Koleksiyonu -->
          <div class="side-collection-card">
            <div style="font-size:0.85rem; font-weight:600; color:#fff; margin-bottom:12px;">Seçkin Sanat Koleksiyonu</div>
            
            <div class="side-collection-item" onclick="window.location.href='?role=buyer&tab=canli&lot_id=1002'">
              <img src="public/assets/images/watch.jpg" alt="Saat" style="width:36px; height:36px; border-radius:6px; object-fit:cover; border:1px solid #333e50;">
              <div>
                <div style="font-size:0.82rem; color:#fff; font-weight:500;">1968 İsviçre Vintage Saat</div>
                <div style="font-size:0.75rem; color:var(--text-muted);">AUC-1642</div>
              </div>
            </div>

            <div class="side-collection-item" onclick="window.location.href='?role=buyer&tab=canli&lot_id=1001'">
              <img src="public/assets/images/books.jpg" alt="Kitap" style="width:36px; height:36px; border-radius:6px; object-fit:cover; border:1px solid #333e50;">
              <div>
                <div style="font-size:0.82rem; color:#fff; font-weight:500;">Nadir Kitap Koleksiyonu</div>
                <div style="font-size:0.75rem; color:var(--text-muted);">AUC-0984</div>
              </div>
            </div>

            <div class="side-collection-item" onclick="window.location.href='?role=buyer&tab=canli&lot_id=1003'">
              <img src="public/assets/images/vase.jpg" alt="Vazo" style="width:36px; height:36px; border-radius:6px; object-fit:cover; border:1px solid #333e50;">
              <div>
                <div style="font-size:0.82rem; color:#fff; font-weight:500;">Antika Seramik Vazo</div>
                <div style="font-size:0.75rem; color:var(--text-muted);">AUC-0991</div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Müzayede Ekleri Modalı -->
      <div id="lotEklerModal" class="martello-modal" style="display:none; position:fixed; z-index:9999; left:0; top:0; width:100%; height:100%; background:rgba(0,0,0,0.85); backdrop-filter:blur(6px); align-items:center; justify-content:center;">
        <div style="background:#12161f; border:1px solid #2a3449; border-radius:12px; max-width:540px; width:92%; padding:24px; position:relative;">
          <button type="button" onclick="document.getElementById('lotEklerModal').style.display='none'" style="position:absolute; right:16px; top:16px; background:none; border:none; color:#888; font-size:1.5rem; cursor:pointer;">&times;</button>
          <div style="color:var(--gold-primary); font-size:0.75rem; font-weight:700; letter-spacing:1px; text-transform:uppercase; margin-bottom:4px;">MARTELLO • LOT DÖKÜMANLARI</div>
          <h3 style="color:#fff; font-size:1.25rem; margin-bottom:12px;">Müzayede Ekleri & Sertifikalar</h3>
          <div style="display:flex; flex-direction:column; gap:10px; font-size:0.85rem;">
            <div style="padding:10px 14px; background:#0a0d13; border:1px solid #263042; border-radius:8px; display:flex; justify-content:space-between; align-items:center;">
              <div>
                <div style="font-weight:600; color:#fff;">Laboratuvar Kondisyon Belgesi (PDF)</div>
                <div style="color:#848d9a; font-size:0.75rem;">SHA-256 E-İmza ile mühürlü • 2.4 MB</div>
              </div>
              <a href="javascript:void(0)" onclick="alert('Kondisyon raporu açıldı.')" class="btn-gold btn-sm" style="font-size:0.75rem;">Görüntüle</a>
            </div>
            <div style="padding:10px 14px; background:#0a0d13; border:1px solid #263042; border-radius:8px; display:flex; justify-content:space-between; align-items:center;">
              <div>
                <div style="font-weight:600; color:#fff;">Provenans & Sahiplik Şeceresi</div>
                <div style="color:#848d9a; font-size:0.75rem;">1968-2026 Koleksiyoner zincir kaydı • 1.1 MB</div>
              </div>
              <a href="javascript:void(0)" onclick="alert('Provenans belgesi doğrulandı.')" class="btn-gold btn-sm" style="font-size:0.75rem;">Görüntüle</a>
            </div>
            <div style="padding:10px 14px; background:#0a0d13; border:1px solid #263042; border-radius:8px; display:flex; justify-content:space-between; align-items:center;">
              <div>
                <div style="font-weight:600; color:#fff;">Kültür Varlığı İnceleme Belgesi</div>
                <div style="color:#848d9a; font-size:0.75rem;">2863 Sayılı Yasa kapsam dışı belgesi • 850 KB</div>
              </div>
              <a href="javascript:void(0)" onclick="alert('Müze müdürlüğü izin belgesi geçerli.')" class="btn-gold btn-sm" style="font-size:0.75rem;">Görüntüle</a>
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

      <!-- Çalışma Alanı ve Yan Paneller (Yan Yana 2 Sütunlu Yerleşim) -->
      <div style="display:grid; grid-template-columns:1.6fr 1fr; gap:24px; align-items:start;">
        
        <!-- Sol Sütun: Kazanılan Lotlar Tablosu (Ana Çalışma Alanı) -->
        <div class="panel-card" style="padding:0; overflow:hidden; margin-bottom:0;">
          <div style="padding:16px 20px; border-bottom:1px solid var(--border-color); display:flex; justify-content:space-between; align-items:center;">
            <div style="font-weight:600; color:#fff; font-size:0.95rem;">Kazanılan Lotlar Listesi</div>
            <span class="badge badge-success">2 Lot Ödendi</span>
          </div>

          <table class="data-table" style="margin:0;">
            <thead>
              <tr>
                <th style="padding-left:20px;">Lot</th>
                <th>Ürün</th>
                <th>Kategori</th>
                <th>Son Tarih</th>
                <th>Son Fiyat</th>
                <th>Ödeme</th>
                <th style="padding-right:20px;">İşlemler</th>
              </tr>
            </thead>
            <tbody>
              <tr>
                <td style="padding-left:20px;">
                  <div style="display:flex; align-items:center; gap:10px;">
                    <img src="public/assets/images/vase.jpg" alt="Vazo" style="width:40px; height:40px; border-radius:6px; object-fit:cover; border:1px solid #2a3449;">
                    <span style="font-weight:600; color:#fff;">AUC-0991</span>
                  </div>
                </td>
                <td style="font-weight:600; color:#fff;">Antika Seramik Vazo</td>
                <td style="color:var(--text-muted);">Sanat</td>
                <td>12-Ekim-2026</td>
                <td style="font-family:var(--font-serif); font-weight:700; color:var(--gold-bright);">₺43.500</td>
                <td><span style="color:#4ade80; font-weight:600;">Ödendi</span></td>
                <td style="padding-right:20px;">
                  <a href="javascript:void(0)" onclick="alert('Fatura No: FAT-2026-0991 (PDF olarak indirildi)')" style="color:#d1b179; font-size:0.8rem; display:block; text-decoration:none;">Faturayı İndir</a>
                  <a href="javascript:void(0)" onclick="alert('Kargo: MTR-98213840 (Teslim Edildi)')" style="color:#d1b179; font-size:0.8rem; display:block; margin-top:2px; text-decoration:none;">Kargoyu Takip Et</a>
                </td>
              </tr>

              <tr>
                <td style="padding-left:20px;">
                  <div style="display:flex; align-items:center; gap:10px;">
                    <img src="public/assets/images/books.jpg" alt="Kitap" style="width:40px; height:40px; border-radius:6px; object-fit:cover; border:1px solid #2a3449;">
                    <span style="font-weight:600; color:#fff;">AUC-0984</span>
                  </div>
                </td>
                <td style="font-weight:600; color:#fff;">Nadir Kitap Koleksiyonu</td>
                <td style="color:var(--text-muted);">Sanat</td>
                <td>12-Ekim-2026</td>
                <td style="font-family:var(--font-serif); font-weight:700; color:var(--gold-bright);">₺3.200</td>
                <td><span style="color:#4ade80; font-weight:600;">Ödendi</span></td>
                <td style="padding-right:20px;">
                  <a href="javascript:void(0)" onclick="alert('Fatura No: FAT-2026-0984 (PDF olarak indirildi)')" style="color:#d1b179; font-size:0.8rem; display:block; text-decoration:none;">Faturayı İndir</a>
                  <a href="javascript:void(0)" onclick="alert('Kargo: MTR-87321044 (Teslim Edildi)')" style="color:#d1b179; font-size:0.8rem; display:block; margin-top:2px; text-decoration:none;">Kargoyu Takip Et</a>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Sağ Sütun: Altta Kalan Bölümler (Ödeme Detayları ve Son Kazandığınız Lotlar) -->
        <div style="display:flex; flex-direction:column; gap:20px;">
          
          <!-- Ödeme Detayları Kutusu -->
          <div class="panel-card" style="margin-bottom:0;">
            <div style="font-size:0.95rem; font-weight:600; color:#fff; margin-bottom:14px; display:flex; justify-content:space-between; align-items:center;">
              <span>Ödeme Detayları</span>
              <span class="badge badge-gold" style="font-size:0.7rem;">Dekont Kayıtlı</span>
            </div>
            
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
              <span style="color:#4ade80; font-weight:600;">₺9.200 (Ödendi)</span>
            </div>

            <div style="display:flex; justify-content:space-between; margin-bottom:10px; font-size:0.85rem;">
              <span style="color:var(--text-muted);">Ödeme Son Günü</span>
              <span style="color:#fff; font-weight:500;">12-Ekim-2026</span>
            </div>

            <div style="display:flex; justify-content:space-between; margin-top:12px; padding-top:10px; border-top:1px solid var(--border-color); font-size:0.9rem;">
              <span style="color:var(--text-muted);">Ödeme Detayları</span>
              <span style="color:var(--gold-bright); font-weight:700;">₺12.800</span>
            </div>
          </div>

          <!-- Son Kazandığınız Lotlar (Kartlar + İncele Butonları) -->
          <div class="panel-card" style="margin-bottom:0;">
            <div style="font-size:0.95rem; font-weight:600; color:#fff; margin-bottom:14px;">Son Kazandığınız Lotlar</div>
            
            <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px;">
              <!-- Kart 1: Vazo -->
              <div style="background:#161c27; border:1px solid #263143; border-radius:10px; padding:12px; display:flex; flex-direction:column; justify-content:space-between;">
                <div style="display:flex; gap:10px; align-items:center; margin-bottom:12px;">
                  <img src="public/assets/images/vase.jpg" alt="Vazo" style="width:40px; height:40px; border-radius:8px; object-fit:cover; border:1px solid #333e50;">
                  <div>
                    <div style="font-size:0.75rem; color:var(--text-muted);">AUC-0991</div>
                    <div style="font-size:0.82rem; font-weight:600; color:#fff;">Antika Seramik Vazo</div>
                  </div>
                </div>
                <button type="button" onclick="switchBuyerTab('canli', this)" class="btn-gold-pill" style="width:100%; padding:7px 0; font-size:0.8rem; cursor:pointer;">
                  İncele
                </button>
              </div>

              <!-- Kart 2: Kitap -->
              <div style="background:#161c27; border:1px solid #263143; border-radius:10px; padding:12px; display:flex; flex-direction:column; justify-content:space-between;">
                <div style="display:flex; gap:10px; align-items:center; margin-bottom:12px;">
                  <img src="public/assets/images/books.jpg" alt="Kitap" style="width:40px; height:40px; border-radius:8px; object-fit:cover; border:1px solid #333e50;">
                  <div>
                    <div style="font-size:0.75rem; color:var(--text-muted);">AUC-0984</div>
                    <div style="font-size:0.82rem; font-weight:600; color:#fff;">Nadir Kitap Koleksiyonu</div>
                  </div>
                </div>
                <button type="button" onclick="switchBuyerTab('canli', this)" class="btn-gold-pill" style="width:100%; padding:7px 0; font-size:0.8rem; cursor:pointer;">
                  İncele
                </button>
              </div>
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
      <p class="page-subheading" style="margin-bottom:24px;">Seçkin müzayede deneyiminizi yönetin</p>

      <!-- 4 KPI Kartı Grid -->
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

      <!-- Yan Yana Çalışma Alanı ve Seçenekler Grid -->
      <div style="display:grid; grid-template-columns:1.5fr 1fr; gap:24px; align-items:start;">
        <!-- Sol: Son Kazanılan Eserler Hızlı Görünüm -->
        <div class="panel-card" style="margin-bottom:0;">
          <div class="panel-header">
            <h3 class="panel-title">Son Kazandığınız Parçalar</h3>
            <button type="button" onclick="switchBuyerTab('kazanilanlar', this)" class="btn-outline-gold btn-sm">Tümünü Gör &rarr;</button>
          </div>
          <div style="display:flex; flex-direction:column; gap:12px;">
            <div style="display:flex; justify-content:space-between; align-items:center; padding:10px 14px; background:#0e1218; border:1px solid var(--border-color); border-radius:8px;">
              <div style="display:flex; align-items:center; gap:12px;">
                <img src="public/assets/images/vase.jpg" alt="Vazo" style="width:40px; height:40px; border-radius:6px; object-fit:cover;">
                <div>
                  <div style="font-weight:600; color:#fff; font-size:0.9rem;">Antika Seramik Vazo</div>
                  <div style="font-size:0.75rem; color:var(--text-muted);">AUC-0991 • Son Fiyat: ₺43.500</div>
                </div>
              </div>
              <span class="badge badge-success">ÖDENDİ</span>
            </div>
            <div style="display:flex; justify-content:space-between; align-items:center; padding:10px 14px; background:#0e1218; border:1px solid var(--border-color); border-radius:8px;">
              <div style="display:flex; align-items:center; gap:12px;">
                <img src="public/assets/images/books.jpg" alt="Kitap" style="width:40px; height:40px; border-radius:6px; object-fit:cover;">
                <div>
                  <div style="font-weight:600; color:#fff; font-size:0.9rem;">Nadir Kitap Koleksiyonu</div>
                  <div style="font-size:0.75rem; color:var(--text-muted);">AUC-0984 • Son Fiyat: ₺3.200</div>
                </div>
              </div>
              <span class="badge badge-success">ÖDENDİ</span>
            </div>
          </div>
        </div>

        <!-- Sağ: Hızlı Aksiyon & Seçenekler Kartı -->
        <div class="panel-card" style="margin-bottom:0;">
          <div class="panel-header">
            <h3 class="panel-title">Hızlı İşlemler</h3>
          </div>
          <div style="display:flex; flex-direction:column; gap:10px;">
            <button type="button" onclick="switchBuyerTab('canli', this)" class="btn-gold" style="width:100%; justify-content:center; padding:10px;">
              Canlı Müzayedeye Katıl
            </button>
            <button type="button" onclick="switchBuyerTab('bakiye', this)" class="btn-outline-gold" style="width:100%; justify-content:center; padding:9px;">
              Bakiye & Teminat Yükle
            </button>
            <button type="button" onclick="switchBuyerTab('takip', this)" class="btn-outline-gold" style="width:100%; justify-content:center; padding:9px;">
              Takip Listemi İncele
            </button>
            <button type="button" onclick="switchBuyerTab('ayarlar', this)" class="btn-outline-gold" style="width:100%; justify-content:center; padding:9px;">
              Profil ve KYC Ayarları
            </button>
          </div>
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
          <div class="panel-header"><h3 class="panel-title">Bakiye Yükle (3D Secure POS)</h3></div>
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
      <p class="page-subheading" style="margin-bottom:24px;">Uyuşmazlık bildirimlerinizi ve hakem heyeti kararlarını takip edin</p>
      
      <div style="display:grid; grid-template-columns:1.5fr 1fr; gap:24px; align-items:start;">
        <!-- Sol: İtiraz Bildirim Formu -->
        <div class="panel-card" style="margin-bottom:0;">
          <div class="panel-header">
            <h3 class="panel-title">Yeni İhtilaf Talebi Oluştur</h3>
            <span class="badge badge-gold">Emanet Korumalı</span>
          </div>
          <form method="POST">
            <input type="hidden" name="action" value="create_dispute">
            <input type="hidden" name="user_id" value="<?= $buyerId ?>">
            <input type="hidden" name="lot_id" value="1002">
            <div class="form-group">
              <label class="form-label">Uyuşmazlık Konusu</label>
              <select name="topic" class="form-control">
                <option value="KONDİSYON_UYUŞMAZLIĞI">Kondisyon / Orijinallik Uyuşmazlığı</option>
                <option value="TESLİMAT_GECİKMESİ">Teslimat Gecikmesi / Kargo Hasarı</option>
                <option value="TANIM_FARKLILIGI">Eser Tanımı ve Fotoğraf Uyuşmazlığı</option>
              </select>
            </div>
            <div class="form-group">
              <label class="form-label">Detaylı Açıklama & Kanıtlar</label>
              <textarea name="description" class="form-control" rows="4" placeholder="Uyuşmazlık detayını, kargo takip numarasını veya görsel bulguları belirtiniz..." required></textarea>
            </div>
            <button type="submit" class="btn-gold" style="padding:10px 24px;">İtiraz Dosyası Aç</button>
          </form>
        </div>

        <!-- Sağ: Hakem Heyeti ve Çözüm Politikası -->
        <div class="panel-card" style="margin-bottom:0;">
          <div class="panel-header">
            <h3 class="panel-title">Çözüm Politikası & Güvence</h3>
          </div>
          <div style="display:flex; flex-direction:column; gap:12px; font-size:0.84rem; color:var(--text-muted); line-height:1.5;">
            <div style="padding:10px 12px; background:#0e1218; border:1px solid var(--border-color); border-radius:8px;">
              <strong style="color:#fff;">1. Emanet Blokesi:</strong> İhtilaf açıldığı anda ilgili lotun satıcıya aktarımı dondurulur, paranız güvendedir.
            </div>
            <div style="padding:10px 12px; background:#0e1218; border:1px solid var(--border-color); border-radius:8px;">
              <strong style="color:#fff;">2. Bağımsız Ekspertiz:</strong> Eser tarafsız kıdemli heyet tarafından fiziki muayeneye alınır.
            </div>
            <div style="padding:10px 12px; background:#0e1218; border:1px solid var(--border-color); border-radius:8px;">
              <strong style="color:#fff;">3. 48 Saat İade Garantisi:</strong> Haklı itirazlarda tam tutar derhal cüzdanınıza iade edilir.
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- 6. TAB: TAKİP EDİLENLER -->
    <div id="buyerTab_takip" class="tab-content <?= $activeTab === 'takip' ? 'active' : '' ?>">
      <div class="page-breadcrumb">ALICI / ÇALIŞMA ALANI</div>
      <h1 class="page-heading">Takip Ettiklerim</h1>
      <p class="page-subheading" style="margin-bottom:24px;">İlgilendiğiniz veya teklif vermeyi düşündüğünüz eserleri tek panelden izleyin</p>

      <div style="display:grid; grid-template-columns:1.5fr 1fr; gap:24px; align-items:start;">
        <!-- Sol: Takip Tablosu -->
        <div class="panel-card" style="margin-bottom:0;">
          <table class="data-table">
            <thead>
              <tr><th>Lot Kodu</th><th>Eser Başlığı</th><th>Güncel Fiyat</th><th>Kalan Süre</th><th>İşlem</th></tr>
            </thead>
            <tbody>
              <tr>
                <td><span class="badge badge-gold">AUC-1642</span></td>
                <td style="font-weight:600; color:#fff;">1968 İsviçre Vintage Saat</td>
                <td style="color:var(--gold-bright); font-weight:700;"><?= $formatPrice((float)$currentLot['mevcutEnYuksekTeklif']) ?></td>
                <td>5 saat 23 dk</td>
                <td>
                  <a href="?role=buyer&tab=canli&lot_id=1002" class="btn-gold btn-sm" style="text-decoration:none;">Müzayedeye Git &rarr;</a>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Sağ: Takip Listesi İpuçları & Bildirim Ayarları -->
        <div class="panel-card" style="margin-bottom:0;">
          <div class="panel-header">
            <h3 class="panel-title">Takip Bildirimleri</h3>
          </div>
          <div style="display:flex; flex-direction:column; gap:12px; font-size:0.84rem;">
            <div style="padding:10px 12px; background:#0e1218; border:1px solid var(--border-color); border-radius:8px;">
              <strong style="color:var(--gold-bright);">✓ Son 10 Dakika Uyarısı Açık:</strong> Listenizdeki eserde müzayede kapanışına 10 dk kala SMS/E-posta bildirimi alırsınız.
            </div>
            <div style="padding:10px 12px; background:#0e1218; border:1px solid var(--border-color); border-radius:8px;">
              <strong style="color:#58a6ff;">✓ Otomatik Pey Entegrasyonu:</strong> Takip ettiğiniz eserlere tek tıkla otomatik tavan bütçe belirleyebilirsiniz.
            </div>
            <button type="button" onclick="switchBuyerTab('canli', this)" class="btn-outline-gold" style="width:100%; justify-content:center; padding:9px;">
              Canlı Lot Ekranına Dön
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- 7. TAB: AYARLAR (Profil Güncelleme - Use Case 4) -->
    <div id="buyerTab_ayarlar" class="tab-content <?= $activeTab === 'ayarlar' ? 'active' : '' ?>">
      <div class="page-breadcrumb">ALICI / ÇALIŞMA ALANI</div>
      <h1 class="page-heading">Hesap & Profil Ayarları</h1>
      <p class="page-subheading">Kişisel bilgilerinizi, teslimat adresinizi ve güvenlik ayarlarınızı yönetin</p>

      <div style="display:grid; grid-template-columns:1.5fr 1fr; gap:24px;">
        <div class="panel-card">
          <div class="panel-header">
            <h3 class="panel-title">Profil Bilgilerini Güncelle</h3>
            <span class="badge badge-success">✓ KYC Onaylı</span>
          </div>
          <form method="POST">
            <input type="hidden" name="action" value="update_profile">
            <input type="hidden" name="user_id" value="<?= $buyerId ?>">

            <div style="display:grid; grid-template-columns:1fr 1fr; gap:16px;">
              <div class="form-group">
                <label class="form-label">Ad Soyad</label>
                <input type="text" name="adSoyad" class="form-control" value="Emre Karahan" required>
              </div>
              <div class="form-group">
                <label class="form-label">E-posta Adresi</label>
                <input type="email" name="eposta" class="form-control" value="emre.karahan@antika.example" required>
              </div>
            </div>

            <div style="display:grid; grid-template-columns:1fr 1fr; gap:16px;">
              <div class="form-group">
                <label class="form-label">Telefon Numarası</label>
                <input type="text" name="telefon" class="form-control" value="+90 532 555 10 20" required>
              </div>
              <div class="form-group">
                <label class="form-label">T.C. Kimlik / Vergi No</label>
                <input type="text" class="form-control" value="12847592014" disabled style="opacity:0.65; cursor:not-allowed;">
              </div>
            </div>

            <div class="form-group">
              <label class="form-label">Fatura & Teslimat Adresi</label>
              <textarea name="adres" class="form-control" rows="3">Teşvikiye Mah. Abdi İpekçi Cad. No: 42 Daire: 6, Şişli / İstanbul</textarea>
            </div>

            <button type="submit" class="btn-gold" style="padding:10px 24px;">
              Bilgileri Güncelle
            </button>
          </form>
        </div>

        <!-- Güvenlik & Bildirim Tercihleri -->
        <div class="panel-card">
          <div class="panel-header">
            <h3 class="panel-title">Güvenlik & Bildirimler</h3>
          </div>
          <div style="display:flex; flex-direction:column; gap:14px;">
            <div style="display:flex; justify-content:space-between; align-items:center; padding:12px; background:#0e1218; border:1px solid var(--border-color); border-radius:8px;">
              <div>
                <div style="font-weight:600; color:#fff; font-size:0.9rem;">İki Adımlı Doğrulama (2FA)</div>
                <div style="font-size:0.75rem; color:var(--text-muted);">Giriş ve yüksek peylerde SMS onayı</div>
              </div>
              <span class="badge badge-success">AKTİF</span>
            </div>

            <div style="display:flex; justify-content:space-between; align-items:center; padding:12px; background:#0e1218; border:1px solid var(--border-color); border-radius:8px;">
              <div>
                <div style="font-weight:600; color:#fff; font-size:0.9rem;">Canlı Müzayede Sesli Uyarı</div>
                <div style="font-size:0.75rem; color:var(--text-muted);">Pey geçildiğinde çekiç sesi efekti</div>
              </div>
              <span class="badge badge-gold">AÇIK</span>
            </div>

            <div style="display:flex; justify-content:space-between; align-items:center; padding:12px; background:#0e1218; border:1px solid var(--border-color); border-radius:8px;">
              <div>
                <div style="font-weight:600; color:#fff; font-size:0.9rem;">E-Posta Bülteni</div>
                <div style="font-size:0.75rem; color:var(--text-muted);">Haftalık seçki ve koleksiyon bültenleri</div>
              </div>
              <span class="badge badge-info">ABONE</span>
            </div>
          </div>
        </div>
      </div>
    </div>

  </main>
</div>

<script>
function switchBuyerTab(tabId, btn) {
  // Hide all tab contents and remove active from nav items
  document.querySelectorAll('.tab-content').forEach(el => el.classList.remove('active'));
  document.querySelectorAll('.sidebar .nav-menu-item').forEach(el => el.classList.remove('active'));

  // Activate target tab content
  const target = document.getElementById('buyerTab_' + tabId);
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
