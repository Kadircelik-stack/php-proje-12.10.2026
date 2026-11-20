<?php
declare(strict_types=1);

/** @var callable $formatPrice */
/** @var array $db */
/** @var \Martello\Controllers\WebAndApiController $controller */
/** @var \Martello\Services\PlatformWorkflowService $workflowService */

$filterOptions = $controller->getFilterOptions();
$buyerFilterParams = [
    'q'          => trim((string)($_GET['q'] ?? '')),
    'cat'        => trim((string)($_GET['cat'] ?? '')),
    'min_price'  => trim((string)($_GET['min_price'] ?? '')),
    'max_price'  => trim((string)($_GET['max_price'] ?? '')),
    'brand'      => trim((string)($_GET['brand'] ?? '')),
    'exp_status' => trim((string)($_GET['exp_status'] ?? '')),
];
$hasActiveBuyerFilters = (
    $buyerFilterParams['q'] !== '' ||
    $buyerFilterParams['cat'] !== '' ||
    $buyerFilterParams['min_price'] !== '' ||
    $buyerFilterParams['max_price'] !== '' ||
    $buyerFilterParams['brand'] !== '' ||
    ($buyerFilterParams['exp_status'] !== '' && $buyerFilterParams['exp_status'] !== 'TUMU')
);
$searchResults = $controller->searchCatalog($buyerFilterParams);

$allAuctions = $db['auctions'] ?? [];
$liveAuctions = [];
$upcomingAuctions = [];
foreach ($allAuctions as $auc) {
    $durum = strtoupper((string)($auc['durum'] ?? ''));
    if (in_array($durum, ['CANLI', 'AKTIF'], true)) {
        $liveAuctions[] = $auc;
    } elseif (in_array($durum, ['PLANLANDI', 'ONAYLANDI'], true)) {
        $upcomingAuctions[] = $auc;
    }
}

$pastAuctions = $controller->getPastAuctions();
$historyStats = $controller->getHistoryStatistics();
$pastSalesQuery = trim((string)($_GET['sales_q'] ?? ''));
$pastSalesCat = trim((string)($_GET['sales_cat'] ?? ''));
$pastSales = $controller->getPastSales($pastSalesCat, $pastSalesQuery);

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

$vipLots = array_values(array_filter($lots, static fn($l) => ($l['muzyedeTuru'] ?? '') === 'VIP' || (int)($l['muzayedeId'] ?? 0) === 802));
$selectedVipLotId = (int)($_GET['vip_lot_id'] ?? ($vipLots[0]['id'] ?? 1005));
$currentVipLot = null;
foreach ($vipLots as $vl) {
    if ((int)$vl['id'] === $selectedVipLotId) {
        $currentVipLot = $vl;
        break;
    }
}
if (!$currentVipLot && !empty($vipLots)) {
    $currentVipLot = $vipLots[0];
}

$vipLotBids = array_filter($allBids, static fn($b) => (int)($b['lotId'] ?? 0) === (int)($currentVipLot['id'] ?? 0));
usort($vipLotBids, static fn($a, $b) => ($b['id'] ?? 0) <=> ($a['id'] ?? 0));

$currentUserRecord = null;
foreach ($db['users'] ?? [] as $u) {
    if ((int)($u['id'] ?? 0) === $buyerId) {
        $currentUserRecord = $u;
        break;
    }
}
$userVipLevel = $currentUserRecord['aliciProfili']['vipSeviyesi'] ?? 'GOLD';
$userLimit = (float)($currentUserRecord['aliciProfili']['teklifLimiti'] ?? 2000000);
$minNextVipBid = (float)($currentVipLot['mevcutEnYuksekTeklif'] ?? 0) + (float)($currentVipLot['minimumArtisTutari'] ?? 10000);

$activeTab = $_GET['tab'] ?? 'kazanilanlar';

$buyerSavedCards = $controller->getUserSavedCards($buyerId);
$buyerPaymentReceipts = $controller->getPaymentReceipts($buyerId);
$buyerWallet = null;
foreach ($db['wallets'] ?? [] as $w) {
    if ((int)($w['kullaniciId'] ?? 0) === $buyerId) {
        $buyerWallet = $w;
        break;
    }
}
$buyerAvailableBalance = (float)($buyerWallet['bakiye'] ?? 0);
$buyerBlockedBalance = (float)($buyerWallet['blokeBakiye'] ?? 0);
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
        <li class="nav-menu-item <?= $activeTab === 'kesfet' ? 'active' : '' ?>">
          <button type="button" data-tab="kesfet" onclick="switchBuyerTab('kesfet', this)">
            <span class="menu-icon"><svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polygon points="16.24 7.76 14.12 14.12 7.76 16.24 9.88 9.88 16.24 7.76"/></svg></span>
            Müzayede keşfet
          </button>
        </li>
        <li class="nav-menu-item <?= $activeTab === 'gecmis' ? 'active' : '' ?>">
          <button type="button" data-tab="gecmis" onclick="switchBuyerTab('gecmis', this)">
            <span class="menu-icon"><svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M12 8v4l3 3"/><circle cx="12" cy="12" r="10"/></svg></span>
            Müzayede & Satış Geçmişi
          </button>
        </li>
        <li class="nav-menu-item <?= $activeTab === 'arama' ? 'active' : '' ?>">
          <button type="button" data-tab="arama" onclick="switchBuyerTab('arama', this)">
            <span class="menu-icon"><svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg></span>
            Eser Arama & Filtre
          </button>
        </li>
        <li class="nav-menu-item <?= $activeTab === 'vip' ? 'active' : '' ?>">
          <button type="button" data-tab="vip" onclick="switchBuyerTab('vip', this)" style="border-left: 2px solid #d4af37; background: linear-gradient(90deg, rgba(212,175,55,0.14) 0%, transparent 100%);">
            <span class="menu-icon" style="color:#d4af37;">
              <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m2 4 3 12h14l3-12-6 7-4-7-4 7-6-7zm3 16h14"/></svg>
            </span>
            <span style="display:flex; align-items:center; justify-content:space-between; width:100%;">
              <span style="font-weight:600; color:#f0f3f6;">VIP Salonu</span>
              <span style="font-size:0.65rem; padding:1px 6px; background:#d4af37; color:#0b0e14; border-radius:10px; font-weight:700; letter-spacing:0.5px;">ÖZEL</span>
            </span>
          </button>
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
        <li class="nav-menu-item <?= $activeTab === 'yardim' ? 'active' : '' ?>">
          <a href="?role=yardim" style="color:#38bdf8; font-weight:600;">
            <span class="menu-icon" style="color:#38bdf8;"><svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"/><path d="M12 17h.01"/></svg></span>
            Yardım Merkezi & Destek
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
          <li class="nav-menu-item <?= $activeTab === 'gecmis' ? 'active' : '' ?>">
            <button type="button" data-tab="gecmis" onclick="switchBuyerTab('gecmis', this)">
              <span class="menu-icon"><svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M12 8v4l3 3"/><circle cx="12" cy="12" r="10"/></svg></span>
              Satış & Çekiç Fiyatı Arşivi
            </button>
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
         0. TAB: ESER ARAMA & ÇOK KRİTERLİ FİLTRELEME (SRS Filtreleme Gereksinimi)
         ==================================================================== -->
    <div id="buyerTab_arama" class="tab-content <?= $activeTab === 'arama' ? 'active' : '' ?>">
      <div class="page-breadcrumb">ALICI / ÇALIŞMA ALANI / ESER ARAMA & FİLTRELEME</div>
      
      <div style="display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:20px; flex-wrap:wrap; gap:10px;">
        <div>
          <h1 class="page-heading">Eser Arama ve Çok Kriterli Filtreleme</h1>
          <p class="page-subheading" style="color:var(--gold-primary); font-weight:500; font-size:0.92rem;">
            ⚡ "Kullanıcıların kategori, fiyat aralığı, ekspertiz durumu, marka ve anahtar kelimelere göre ürün araması ve filtreleme yapabilmesidir."
          </p>
        </div>
        <span class="badge badge-gold" style="font-size:0.78rem; padding:6px 14px;">SRS Arama & Keşif</span>
      </div>

      <!-- Filtreleme Kontrol Paneli -->
      <div class="panel-card" style="margin-bottom:24px; background:rgba(18,22,31,0.95); border:1px solid #334155; box-shadow:0 12px 30px rgba(0,0,0,0.6);">
        <form method="GET" action="index.php" style="display:grid; grid-template-columns:2fr 1.3fr 1.6fr 1.2fr 1.2fr auto; gap:12px; align-items:flex-end;">
          <input type="hidden" name="role" value="buyer">
          <input type="hidden" name="tab" value="arama">

          <!-- 1. Anahtar Kelime -->
          <div>
            <label class="form-label" style="font-size:0.75rem; color:#cbd5e1;">Anahtar Kelime</label>
            <input type="text" name="q" class="form-control" placeholder="Eser adı, lot no, dönem..." value="<?= htmlspecialchars($buyerFilterParams['q']) ?>" style="background:#0b0e14; border-color:#2a3649;">
          </div>

          <!-- 2. Kategori -->
          <div>
            <label class="form-label" style="font-size:0.75rem; color:#cbd5e1;">Kategori</label>
            <select name="cat" class="form-control" style="background:#0b0e14; border-color:#2a3649;">
              <option value="">Tüm Kategoriler</option>
              <?php foreach ($filterOptions['categories'] ?? [] as $cat): ?>
                <?php $catVal = (string)($cat['id'] ?? $cat['ad']); ?>
                <option value="<?= htmlspecialchars((string)($cat['ad'] ?? $catVal)) ?>" <?= ($buyerFilterParams['cat'] === (string)($cat['ad'] ?? '') || $buyerFilterParams['cat'] === (string)($cat['id'] ?? '')) ? 'selected' : '' ?>>
                  <?= htmlspecialchars((string)($cat['ad'] ?? '')) ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>

          <!-- 3. Fiyat Aralığı -->
          <div>
            <label class="form-label" style="font-size:0.75rem; color:#cbd5e1;">Fiyat Aralığı (<?= $currencyStrategy->getSymbol() ?> Min - Max)</label>
            <div style="display:grid; grid-template-columns:1fr 1fr; gap:6px;">
              <input type="number" name="min_price" class="form-control" placeholder="Min <?= $currencyStrategy->getSymbol() ?>" value="<?= htmlspecialchars($buyerFilterParams['min_price']) ?>" style="background:#0b0e14; border-color:#2a3649; padding:6px 8px; font-size:0.82rem;">
              <input type="number" name="max_price" class="form-control" placeholder="Max <?= $currencyStrategy->getSymbol() ?>" value="<?= htmlspecialchars($buyerFilterParams['max_price']) ?>" style="background:#0b0e14; border-color:#2a3649; padding:6px 8px; font-size:0.82rem;">
            </div>
          </div>

          <!-- 4. Marka / Atölye -->
          <div>
            <label class="form-label" style="font-size:0.75rem; color:#cbd5e1;">Marka / Atölye</label>
            <select name="brand" class="form-control" style="background:#0b0e14; border-color:#2a3649;">
              <option value="">Tüm Markalar</option>
              <?php foreach ($filterOptions['brands'] ?? [] as $br): ?>
                <option value="<?= htmlspecialchars($br) ?>" <?= ($buyerFilterParams['brand'] === $br) ? 'selected' : '' ?>>
                  <?= htmlspecialchars($br) ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>

          <!-- 5. Ekspertiz Durumu -->
          <div>
            <label class="form-label" style="font-size:0.75rem; color:#cbd5e1;">Ekspertiz Durumu</label>
            <select name="exp_status" class="form-control" style="background:#0b0e14; border-color:#2a3649;">
              <option value="">Tümü</option>
              <option value="ONAYLI" <?= ($buyerFilterParams['exp_status'] === 'ONAYLI') ? 'selected' : '' ?>>✓ Onaylı & Damgalı</option>
              <option value="BEKLEMEDE" <?= ($buyerFilterParams['exp_status'] === 'BEKLEMEDE') ? 'selected' : '' ?>>İncelemede / Heyet</option>
            </select>
          </div>

          <!-- Aksiyon Butonları -->
          <div style="display:flex; gap:6px;">
            <button type="submit" class="btn-register-glow" style="padding:9px 18px; font-size:0.85rem; font-weight:600; white-space:nowrap;">
              Filtrele
            </button>
            <?php if ($hasActiveBuyerFilters): ?>
              <a href="index.php?role=buyer&tab=arama" class="btn-outline-gold" style="padding:9px 14px; font-size:0.82rem; text-decoration:none; display:inline-flex; align-items:center; white-space:nowrap;" title="Filtreleri Sıfırla">
                ✕ Temizle
              </a>
            <?php endif; ?>
          </div>
        </form>

        <?php if ($hasActiveBuyerFilters): ?>
          <div style="margin-top:14px; padding-top:12px; border-top:1px solid rgba(255,255,255,0.08); display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:10px;">
            <div style="display:flex; align-items:center; gap:8px; flex-wrap:wrap; font-size:0.8rem;">
              <span style="color:#d4af37; font-weight:600;">Uygulanan Filtreler:</span>
              <?php if (!empty($buyerFilterParams['q'])): ?>
                <span class="badge badge-gold">Kelime: "<?= htmlspecialchars($buyerFilterParams['q']) ?>"</span>
              <?php endif; ?>
              <?php if (!empty($buyerFilterParams['cat'])): ?>
                <span class="badge badge-gold">Kategori: <?= htmlspecialchars($buyerFilterParams['cat']) ?></span>
              <?php endif; ?>
              <?php if (!empty($buyerFilterParams['min_price']) || !empty($buyerFilterParams['max_price'])): ?>
                <span class="badge badge-gold">
                  Fiyat: <?= !empty($buyerFilterParams['min_price']) ? $currencyStrategy->getSymbol() . number_format((float)$buyerFilterParams['min_price']) : '0' ?> - <?= !empty($buyerFilterParams['max_price']) ? $currencyStrategy->getSymbol() . number_format((float)$buyerFilterParams['max_price']) : '∞' ?>
                </span>
              <?php endif; ?>
              <?php if (!empty($buyerFilterParams['brand'])): ?>
                <span class="badge badge-gold">Marka: <?= htmlspecialchars($buyerFilterParams['brand']) ?></span>
              <?php endif; ?>
              <?php if (!empty($buyerFilterParams['exp_status'])): ?>
                <span class="badge badge-gold">Ekspertiz: <?= $buyerFilterParams['exp_status'] === 'ONAYLI' ? '✓ Onaylı' : 'İncelemede' ?></span>
              <?php endif; ?>
            </div>
            <div style="font-size:0.82rem; color:#38ef7d; font-weight:600;">
              <?= count($searchResults) ?> adet eşleşen eser listeleniyor
            </div>
          </div>
        <?php endif; ?>
      </div>

      <!-- Arama Sonuçları Grid -->
      <div class="panel-header" style="margin-bottom:16px;">
        <div>
          <h3 class="panel-title">Müzayede Lotları (<?= count($searchResults) ?> Eser)</h3>
          <p class="panel-subtitle">Doğrulanmış açık artırma kataloğu</p>
        </div>
      </div>

      <?php if (empty($searchResults)): ?>
        <div class="panel-card" style="text-align:center; padding:50px 20px; background:#0e1218; border:1px dashed #334155;">
          <div style="font-size:2.5rem; color:#d4af37; margin-bottom:12px;">🔍</div>
          <h4 style="color:#f0f3f6; font-size:1.2rem; margin-bottom:8px;">Aradığınız Kriterlere Uygun Eser Bulunamadı</h4>
          <p style="color:#848d9a; font-size:0.88rem; max-width:500px; margin:0 auto 20px auto;">
            Belirttiğiniz kategori, fiyat aralığı, marka veya anahtar kelimelere uyan açık lot bulunmamaktadır. Kriterlerinizi genişleterek tekrar deneyebilirsiniz.
          </p>
          <a href="index.php?role=buyer&tab=arama" class="btn-gold" style="display:inline-flex; align-items:center; gap:6px;">
            Tüm Kataloğu Göster
          </a>
        </div>
      <?php else: ?>
        <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(320px, 1fr)); gap:20px; margin-bottom:30px;">
          <?php foreach ($searchResults as $sLot): ?>
            <div class="panel-card" style="margin-bottom:0; display:flex; flex-direction:column; justify-content:space-between; transition:all 0.25s ease;" onmouseover="this.style.borderColor='rgba(212,175,55,0.4)';" onmouseout="this.style.borderColor='var(--border-color)';">
              <div>
                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:12px;">
                  <span class="badge badge-gold"><?= htmlspecialchars((string)($sLot['lotKodu'] ?? '')) ?></span>
                  <div style="display:flex; gap:6px;">
                    <?php if (!empty($sLot['ekspertizOnayli'])): ?>
                      <span class="badge badge-success" style="font-size:0.68rem; padding:2px 8px;">✓ Ekspertiz Onaylı</span>
                    <?php endif; ?>
                    <span class="badge <?= ($sLot['durum'] ?? '') === 'ACIK_ARTIRMADA' ? 'badge-success' : 'badge-warning' ?>" style="font-size:0.68rem; padding:2px 8px;">
                      <?= htmlspecialchars((string)($sLot['durum'] ?? '')) ?>
                    </span>
                  </div>
                </div>

                <!-- Eser Görseli -->
                <?php $sImg = !empty($sLot['gorselUrl']) ? $sLot['gorselUrl'] : 'public/assets/images/watch.jpg'; ?>
                <div style="height:160px; background:#070a0f; border-radius:8px; overflow:hidden; margin-bottom:12px; display:flex; align-items:center; justify-content:center; border:1px solid #1a2230;">
                  <img src="<?= htmlspecialchars($sImg) ?>" alt="<?= htmlspecialchars((string)($sLot['baslik'] ?? '')) ?>" style="max-height:100%; max-width:100%; object-fit:contain;">
                </div>

                <div style="display:flex; justify-content:space-between; align-items:baseline; margin-bottom:4px;">
                  <span style="font-size:0.75rem; color:#d4af37; font-weight:600; text-transform:uppercase; letter-spacing:0.5px;">
                    <?= htmlspecialchars((string)($sLot['markaGosterim'] ?? ($sLot['marka'] ?? 'Özel Koleksiyon'))) ?>
                  </span>
                  <span style="font-size:0.75rem; color:#848d9a;">
                    <?= htmlspecialchars((string)($sLot['kategoriAdi'] ?? 'Antika')) ?>
                  </span>
                </div>

                <h4 style="font-family:var(--font-serif); font-size:1.15rem; color:#fff; margin-bottom:10px; line-height:1.35;">
                  <?= htmlspecialchars((string)($sLot['baslik'] ?? '')) ?>
                </h4>
              </div>

              <div style="border-top:1px solid var(--border-color); padding-top:12px; display:flex; justify-content:space-between; align-items:center;">
                <div>
                  <div style="font-size:0.7rem; color:var(--text-muted); text-transform:uppercase; letter-spacing:0.5px;">Güncel Teklif</div>
                  <div style="font-family:var(--font-serif); font-size:1.3rem; font-weight:700; color:var(--gold-bright);">
                    <?= $formatPrice((float)($sLot['mevcutEnYuksekTeklif'] ?: ($sLot['baslangicFiyati'] ?? 0))) ?>
                  </div>
                </div>
                <div style="display:flex; gap:8px;">
                  <form method="POST" action="index.php?role=buyer&tab=arama" style="display:inline;">
                    <input type="hidden" name="action" value="toggle_watchlist">
                    <input type="hidden" name="user_id" value="3">
                    <input type="hidden" name="lot_id" value="<?= (int)$sLot['id'] ?>">
                    <input type="hidden" name="auction_id" value="<?= (int)($sLot['muzayedeId'] ?? 801) ?>">
                    <button type="submit" class="btn-dark-pill" style="padding:6px 10px; font-size:0.75rem;" title="Takibe Al / Çıkar">
                      ★ Takip
                    </button>
                  </form>
                  <a href="index.php?role=buyer&tab=canli&lot_id=<?= (int)$sLot['id'] ?>" class="btn-gold btn-sm" style="display:inline-flex; align-items:center; gap:4px; font-size:0.8rem; padding:6px 14px;">
                    Pey Ver &rarr;
                  </a>
                </div>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </div>

    <!-- ====================================================================
         MÜZAYEDE KEŞFET TAB: CANLI VE GELECEKTE YAPILAN MÜZAYEDELER
         ==================================================================== -->
    <div id="buyerTab_kesfet" class="tab-content <?= $activeTab === 'kesfet' ? 'active' : '' ?>">
      <div class="page-breadcrumb">ALICI / ÇALIŞMA ALANI / MÜZAYEDE KEŞFET</div>

      <div style="display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:24px; flex-wrap:wrap; gap:12px;">
        <div>
          <h1 class="page-heading">Müzayede Keşif Salonu</h1>
          <p class="page-subheading" style="color:var(--gold-primary); font-weight:500; font-size:0.92rem;">
            ⚡ Aktif canlı açık artırmalar ve takvimlendirilmiş gelecek müzayede oturumları
          </p>
        </div>
        <div style="display:flex; gap:8px;">
          <span class="badge badge-success" style="padding:6px 14px; font-size:0.8rem; display:inline-flex; align-items:center; gap:6px;">
            <span style="width:7px; height:7px; border-radius:50%; background:#2ecc71; display:inline-block; box-shadow:0 0 8px #2ecc71;"></span>
            <?= count($liveAuctions) ?> Canlı Müzayede
          </span>
          <span class="badge badge-gold" style="padding:6px 14px; font-size:0.8rem;">
            🕒 <?= count($upcomingAuctions) ?> Gelecek Oturum
          </span>
        </div>
      </div>

      <!-- Kategori / Oturum Filtre Düğmeleri -->
      <div style="display:flex; gap:10px; margin-bottom:24px; flex-wrap:wrap;">
        <button type="button" onclick="filterAuctionSection('all', this)" class="btn-gold-pill auction-filter-btn" style="padding:7px 18px; font-size:0.82rem;">
          Tüm Müzayedeler (<?= count($allAuctions) ?>)
        </button>
        <button type="button" onclick="filterAuctionSection('live', this)" class="btn-dark-pill auction-filter-btn" style="padding:7px 18px; font-size:0.82rem;">
          🔴 Canlı Yayında (<?= count($liveAuctions) ?>)
        </button>
        <button type="button" onclick="filterAuctionSection('upcoming', this)" class="btn-dark-pill auction-filter-btn" style="padding:7px 18px; font-size:0.82rem;">
          🕒 Gelecek Oturumlar (<?= count($upcomingAuctions) ?>)
        </button>
        <button type="button" onclick="switchBuyerTab('gecmis', this)" class="btn-dark-pill auction-filter-btn" style="padding:7px 18px; font-size:0.82rem; border-color:rgba(212,175,55,0.4);">
          📜 Müzayede & Satış Geçmişi (Arşiv)
        </button>
      </div>

      <!-- 1. BÖLÜM: CANLI MÜZAYEDELER -->
      <div id="section_live_auctions" style="margin-bottom:36px;">
        <div style="display:flex; align-items:center; gap:10px; margin-bottom:16px;">
          <span style="width:10px; height:10px; border-radius:50%; background:#2ecc71; display:inline-block; box-shadow:0 0 10px #2ecc71;"></span>
          <h2 style="font-family:var(--font-serif); font-size:1.45rem; color:#fff; margin:0;">
            Canlı Müzayedeler (Devam Eden Açık Artırmalar)
          </h2>
          <span class="badge badge-success" style="font-size:0.7rem; padding:2px 8px;">Anlık Pey Kabulü Aktif</span>
        </div>

        <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(360px, 1fr)); gap:20px;">
          <?php foreach ($liveAuctions as $lAuc): ?>
            <div class="panel-card" style="margin-bottom:0; background:linear-gradient(135deg, #0e121a 0%, #171d29 100%); border:1px solid rgba(46,204,113,0.35); box-shadow:0 8px 30px rgba(0,0,0,0.7); display:flex; flex-direction:column; justify-content:space-between;">
              <div>
                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:12px;">
                  <span class="badge badge-success" style="display:inline-flex; align-items:center; gap:6px; font-size:0.75rem; padding:3px 10px;">
                    <span style="width:6px; height:6px; border-radius:50%; background:#2ecc71; display:inline-block;"></span>
                    CANLI YAYINDA
                  </span>
                  <span class="badge badge-gold" style="font-size:0.72rem;">
                    <?= htmlspecialchars((string)($lAuc['tur'] ?? 'CANLI')) ?> OTURUMU
                  </span>
                </div>

                <?php $aucImg = !empty($lAuc['gorselUrl']) ? $lAuc['gorselUrl'] : 'public/assets/images/watch.jpg'; ?>
                <div style="height:150px; border-radius:8px; overflow:hidden; margin-bottom:14px; background:#070a0f; border:1px solid #222b3a; position:relative;">
                  <img src="<?= htmlspecialchars($aucImg) ?>" alt="<?= htmlspecialchars((string)($lAuc['baslik'] ?? '')) ?>" style="width:100%; height:100%; object-fit:cover; opacity:0.85;">
                  <div style="position:absolute; bottom:8px; left:10px; background:rgba(0,0,0,0.7); padding:3px 8px; border-radius:4px; font-size:0.72rem; color:#d4af37; font-weight:600;">
                    <?= htmlspecialchars((string)($lAuc['kategori'] ?? 'Sanat & Horoloji')) ?>
                  </div>
                </div>

                <h3 style="font-family:var(--font-serif); font-size:1.25rem; color:#fff; margin-bottom:8px; line-height:1.3;">
                  <?= htmlspecialchars((string)($lAuc['baslik'] ?? '')) ?>
                </h3>
                <p style="font-size:0.84rem; color:#94a3b8; line-height:1.5; margin-bottom:16px;">
                  <?= htmlspecialchars((string)($lAuc['aciklama'] ?? 'Canlı salon açık artırması devam etmektedir.')) ?>
                </p>

                <!-- Metrikler -->
                <div style="display:grid; grid-template-columns:1fr 1fr; gap:8px; padding:10px 12px; background:rgba(0,0,0,0.3); border:1px solid rgba(255,255,255,0.06); border-radius:8px; margin-bottom:16px; font-size:0.8rem;">
                  <div>
                    <div style="color:#848d9a; font-size:0.7rem;">Canlı Katılımcı</div>
                    <div style="color:#2ecc71; font-weight:700;">👥 <?= number_format((int)($lAuc['katilimciSayisi'] ?? 1420)) ?> Kişi</div>
                  </div>
                  <div>
                    <div style="color:#848d9a; font-size:0.7rem;">Açık Lot Sayısı</div>
                    <div style="color:#f0f3f6; font-weight:700;">🏷 <?= (int)($lAuc['lotSayisi'] ?? 12) ?> Eser</div>
                  </div>
                </div>
              </div>

              <div style="border-top:1px solid var(--border-color); padding-top:14px; display:flex; justify-content:space-between; align-items:center;">
                <div>
                  <div style="font-size:0.7rem; color:#848d9a;">Tahmini Hacim</div>
                  <div style="font-family:var(--font-serif); font-size:1.15rem; font-weight:700; color:#d4af37;">
                    <?= $formatPrice((float)($lAuc['tahminiHacim'] ?? 2450000)) ?>
                  </div>
                </div>
                <div style="display:flex; gap:8px;">
                  <?php if ((int)($lAuc['id'] ?? 0) === 802): ?>
                    <button type="button" onclick="switchBuyerTab('vip', this)" class="btn-register-glow" style="padding:8px 16px; font-size:0.82rem; font-weight:600;">
                      👑 VIP Salona Katıl &rarr;
                    </button>
                  <?php else: ?>
                    <button type="button" onclick="switchBuyerTab('canli', this)" class="btn-gold" style="padding:8px 16px; font-size:0.82rem; font-weight:600;">
                      ▶ Salona Katıl &rarr;
                    </button>
                  <?php endif; ?>
                </div>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      </div>

      <!-- 2. BÖLÜM: GELECEKTE YAPILAN MÜZAYEDELER -->
      <div id="section_upcoming_auctions">
        <div style="display:flex; align-items:center; gap:10px; margin-bottom:16px;">
          <span style="width:10px; height:10px; border-radius:50%; background:#d4af37; display:inline-block; box-shadow:0 0 10px #d4af37;"></span>
          <h2 style="font-family:var(--font-serif); font-size:1.45rem; color:#fff; margin:0;">
            Gelecekte Yapılan Müzayedeler (Planlanan Oturumlar)
          </h2>
          <span class="badge badge-gold" style="font-size:0.7rem; padding:2px 8px;">Katalog Ön İncelemede</span>
        </div>

        <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(360px, 1fr)); gap:20px;">
          <?php foreach ($upcomingAuctions as $uAuc): ?>
            <div class="panel-card" style="margin-bottom:0; background:linear-gradient(135deg, #0d1017 0%, #151a24 100%); border:1px solid #2a3449; display:flex; flex-direction:column; justify-content:space-between; transition:all 0.25s ease;" onmouseover="this.style.borderColor='rgba(212,175,55,0.4)';" onmouseout="this.style.borderColor='#2a3449';">
              <div>
                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:12px;">
                  <span class="badge badge-gold" style="font-size:0.75rem; padding:3px 10px;">
                    🕒 PLANLANDI
                  </span>
                  <span style="font-size:0.75rem; color:#d4af37; font-weight:600;">
                    📅 <?= !empty($uAuc['baslangicZamani']) ? date('d M Y • H:i', strtotime((string)$uAuc['baslangicZamani'])) : 'Çok Yakında' ?>
                  </span>
                </div>

                <?php $uImg = !empty($uAuc['gorselUrl']) ? $uAuc['gorselUrl'] : 'public/assets/images/cat_osmanli.jpg'; ?>
                <div style="height:150px; border-radius:8px; overflow:hidden; margin-bottom:14px; background:#070a0f; border:1px solid #222b3a; position:relative;">
                  <img src="<?= htmlspecialchars($uImg) ?>" alt="<?= htmlspecialchars((string)($uAuc['baslik'] ?? '')) ?>" style="width:100%; height:100%; object-fit:cover; opacity:0.8;">
                  <div style="position:absolute; bottom:8px; left:10px; background:rgba(0,0,0,0.7); padding:3px 8px; border-radius:4px; font-size:0.72rem; color:#f0f3f6;">
                    <?= htmlspecialchars((string)($uAuc['kategori'] ?? 'Koleksiyon')) ?>
                  </div>
                </div>

                <h3 style="font-family:var(--font-serif); font-size:1.25rem; color:#fff; margin-bottom:8px; line-height:1.3;">
                  <?= htmlspecialchars((string)($uAuc['baslik'] ?? '')) ?>
                </h3>
                <p style="font-size:0.84rem; color:#94a3b8; line-height:1.5; margin-bottom:16px;">
                  <?= htmlspecialchars((string)($uAuc['aciklama'] ?? 'Planlanan müzayede oturumu.')) ?>
                </p>

                <!-- Metrikler -->
                <div style="display:grid; grid-template-columns:1fr 1fr; gap:8px; padding:10px 12px; background:rgba(0,0,0,0.3); border:1px solid rgba(255,255,255,0.06); border-radius:8px; margin-bottom:16px; font-size:0.8rem;">
                  <div>
                    <div style="color:#848d9a; font-size:0.7rem;">Ön Kayıtlı Koleksiyoner</div>
                    <div style="color:#d4af37; font-weight:700;">👥 <?= number_format((int)($uAuc['katilimciSayisi'] ?? 250)) ?> Kişi</div>
                  </div>
                  <div>
                    <div style="color:#848d9a; font-size:0.7rem;">Katalog Lot Sayısı</div>
                    <div style="color:#f0f3f6; font-weight:700;">🏷 <?= (int)($uAuc['lotSayisi'] ?? 30) ?> Eser</div>
                  </div>
                </div>
              </div>

              <div style="border-top:1px solid var(--border-color); padding-top:14px; display:flex; justify-content:space-between; align-items:center;">
                <div>
                  <div style="font-size:0.7rem; color:#848d9a;">Tahmini Toplam Değer</div>
                  <div style="font-family:var(--font-serif); font-size:1.15rem; font-weight:700; color:#d4af37;">
                    <?= $formatPrice((float)($uAuc['tahminiHacim'] ?? 3500000)) ?>
                  </div>
                </div>
                <div style="display:flex; gap:8px;">
                  <button type="button" onclick="alert('✓ \'<?= addslashes((string)$uAuc['baslik']) ?>\' müzayedesi için takvim hatırlatmanız oluşturuldu. Başlangıçtan 1 saat önce bildirim alacaksınız.');" class="btn-dark-pill" style="padding:7px 12px; font-size:0.78rem;">
                    🔔 Hatırlat
                  </button>
                  <button type="button" onclick="switchBuyerTab('arama', this)" class="btn-outline-gold" style="padding:7px 14px; font-size:0.78rem;">
                    🔍 Ön Katalog
                  </button>
                </div>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      </div>
    </div>

    <!-- ====================================================================
         MÜZAYEDE VE SATIŞ GEÇMİŞİ ARŞİVİ (YAPILAN MÜZAYEDELER & SATILAN ÜRÜNLER)
         ==================================================================== -->
    <div id="buyerTab_gecmis" class="tab-content <?= $activeTab === 'gecmis' ? 'active' : '' ?>">
      <div class="page-breadcrumb">ARŞİV & İSTATİSTİK / GEÇMİŞ MÜZAYEDELER VE SATIŞLAR</div>
      <h1 class="page-heading" style="font-size:1.85rem; margin-bottom:6px;">
        📜 Müzayede ve Satış Geçmişi Arşivi
      </h1>
      <p class="page-subheading" style="margin-bottom:24px;">
        Sitemizde geçmişte gerçekleştirilen tüm açık artırma oturumları, nihai çekiç fiyatları, tescilli satış istatistikleri ve satılan koleksiyonluk eserler.
      </p>

      <!-- 4 Temel Metrik / KPI Kartları -->
      <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(220px, 1fr)); gap:16px; margin-bottom:28px;">
        <div class="panel-card" style="margin-bottom:0; background:linear-gradient(135deg, #121820 0%, #171f2c 100%); border-left:4px solid #c5a059;">
          <div style="font-size:0.75rem; text-transform:uppercase; letter-spacing:1px; color:#848d9a; margin-bottom:6px;">Tamamlanan Müzayedeler</div>
          <div style="font-size:1.65rem; font-weight:700; color:#fff; font-family:'Cinzel', serif;">
            <?= (int)($historyStats['totalCompletedAuctions'] ?? count($pastAuctions)) ?> <span style="font-size:0.95rem; font-family:sans-serif; color:#c5a059; font-weight:normal;">Oturum</span>
          </div>
          <div style="font-size:0.78rem; color:#10b981; margin-top:4px;">✓ Başarıyla Sonuçlandı</div>
        </div>

        <div class="panel-card" style="margin-bottom:0; background:linear-gradient(135deg, #121820 0%, #171f2c 100%); border-left:4px solid #10b981;">
          <div style="font-size:0.75rem; text-transform:uppercase; letter-spacing:1px; color:#848d9a; margin-bottom:6px;">Toplam Satılan Eser</div>
          <div style="font-size:1.65rem; font-weight:700; color:#fff; font-family:'Cinzel', serif;">
            <?= (int)($historyStats['totalSoldLots'] ?? 0) ?> <span style="font-size:0.95rem; font-family:sans-serif; color:#10b981; font-weight:normal;">Lot Alıcı Buldu</span>
          </div>
          <div style="font-size:0.78rem; color:#848d9a; margin-top:4px;">Koleksiyonculara Teslim Edildi</div>
        </div>

        <div class="panel-card" style="margin-bottom:0; background:linear-gradient(135deg, #121820 0%, #171f2c 100%); border-left:4px solid #d4af37;">
          <div style="font-size:0.75rem; text-transform:uppercase; letter-spacing:1px; color:#848d9a; margin-bottom:6px;">Toplam Satış Hacmi</div>
          <div style="font-size:1.65rem; font-weight:700; color:#c5a059; font-family:'Cinzel', serif;">
            <?= $formatPrice((float)($historyStats['totalVolume'] ?? 60000000.0)) ?>+
          </div>
          <div style="font-size:0.78rem; color:#848d9a; margin-top:4px;">Gerçekleşen Çekiç Toplamı</div>
        </div>

        <div class="panel-card" style="margin-bottom:0; background:linear-gradient(135deg, #121820 0%, #171f2c 100%); border-left:4px solid #60a5fa;">
          <div style="font-size:0.75rem; text-transform:uppercase; letter-spacing:1px; color:#848d9a; margin-bottom:6px;">Ortalama Satış Başarısı</div>
          <div style="font-size:1.65rem; font-weight:700; color:#60a5fa; font-family:'Cinzel', serif;">
            %<?= (float)($historyStats['averageSuccessRate'] ?? 91) ?>
          </div>
          <div style="font-size:0.78rem; color:#848d9a; margin-top:4px;">Katalog Eser Satış Oranı</div>
        </div>
      </div>

      <!-- Alt Görünüm Seçim Sekmeleri (Müzayedeler vs Satılan Ürünler) -->
      <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:12px; margin-bottom:20px; border-bottom:1px solid #1f2733; padding-bottom:14px;">
        <div style="display:flex; gap:10px;">
          <button type="button" id="btn_history_auctions" onclick="toggleHistoryView('auctions')" class="btn-gold-pill" style="font-weight:600; font-size:0.85rem; padding:8px 18px;">
            📜 Yapılan Müzayedeler (<?= count($pastAuctions) ?>)
          </button>
          <button type="button" id="btn_history_sales" onclick="toggleHistoryView('sales')" class="btn-dark-pill" style="font-weight:600; font-size:0.85rem; padding:8px 18px;">
            🏷 Satılan Ürünler & Çekiç Fiyatları (<?= count($pastSales) ?>)
          </button>
        </div>
        <div style="font-size:0.8rem; color:#848d9a;">
          Arşiv Verileri • Martello Sotheby's & Christie's Standartlarında Tescillidir
        </div>
      </div>

      <!-- 1. ALT GÖRÜNÜM: YAPILAN MÜZAYEDELER (AUCTIONS) -->
      <div id="history_auctions_view" style="display:block;">
        <div style="display:grid; grid-template-columns:repeat(auto-fill, minmax(360px, 1fr)); gap:22px;">
          <?php foreach ($pastAuctions as $pAuc): ?>
            <div class="panel-card" style="margin-bottom:0; display:flex; flex-direction:column; justify-content:space-between; border:1px solid #232d3d; transition:transform 0.2s, border-color 0.2s;" onmouseover="this.style.borderColor='#c5a059'" onmouseout="this.style.borderColor='#232d3d'">
              <div>
                <!-- Başlık ve Durum Rozeti -->
                <div style="display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:12px;">
                  <div>
                    <span style="font-size:0.75rem; color:#c5a059; font-weight:600; letter-spacing:0.5px;">NO: <?= htmlspecialchars((string)($pAuc['kod'] ?? ('AUC-' . $pAuc['id']))) ?></span>
                    <h3 style="font-size:1.15rem; color:#fff; font-family:'Cinzel', serif; margin:4px 0 0 0; line-height:1.3;">
                      <?= htmlspecialchars((string)($pAuc['baslik'] ?? 'Müzayede Oturumu')) ?>
                    </h3>
                  </div>
                  <span class="badge" style="background:rgba(16,185,129,0.15); color:#10b981; border:1px solid rgba(16,185,129,0.3); font-size:0.72rem; padding:4px 10px; border-radius:20px; font-weight:600;">
                    ✓ SONLANDI
                  </span>
                </div>

                <p style="font-size:0.83rem; color:#94a3b8; line-height:1.5; margin-bottom:16px;">
                  <?= htmlspecialchars((string)($pAuc['aciklama'] ?? 'Özel koleksiyon satışı başarıyla tamamlandı.')) ?>
                </p>

                <!-- Detay Kutuları -->
                <div style="background:#0b0e14; border:1px solid #1a222e; border-radius:8px; padding:14px; display:grid; grid-template-columns:1fr 1fr; gap:12px; margin-bottom:16px;">
                  <div>
                    <div style="font-size:0.72rem; color:#6b7280; text-transform:uppercase;">Kapanış Tarihi</div>
                    <div style="font-size:0.85rem; color:#e2e8f0; font-weight:600; margin-top:2px;">
                      📅 <?= htmlspecialchars((string)($pAuc['tamamlanmaTarihi'] ?? ($pAuc['bitisZamani'] ?? '15.10.2026'))) ?>
                    </div>
                  </div>
                  <div>
                    <div style="font-size:0.72rem; color:#6b7280; text-transform:uppercase;">Katılımcı Sayısı</div>
                    <div style="font-size:0.85rem; color:#e2e8f0; font-weight:600; margin-top:2px;">
                      👥 <?= htmlspecialchars((string)($pAuc['katilimciSayisi'] ?? 120)) ?> Alıcı
                    </div>
                  </div>
                  <div>
                    <div style="font-size:0.72rem; color:#6b7280; text-transform:uppercase;">Satılan / Toplam Lot</div>
                    <div style="font-size:0.85rem; color:#10b981; font-weight:600; margin-top:2px;">
                      🎯 <?= htmlspecialchars((string)($pAuc['satilanLotSayisi'] ?? 45)) ?> / <?= htmlspecialchars((string)($pAuc['toplamLotSayisi'] ?? 50)) ?> Lot
                    </div>
                  </div>
                  <div>
                    <div style="font-size:0.72rem; color:#6b7280; text-transform:uppercase;">Satış Başarısı</div>
                    <div style="font-size:0.85rem; color:#c5a059; font-weight:600; margin-top:2px;">
                      📈 %<?= htmlspecialchars((string)($pAuc['basariOrani'] ?? 90)) ?>
                    </div>
                  </div>
                </div>

                <!-- Rekor Lot ve Satış Hacmi -->
                <div style="border-top:1px dashed #232d3d; padding-top:12px; margin-bottom:14px;">
                  <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:6px;">
                    <span style="font-size:0.75rem; color:#848d9a;">Günün Rekor Eseri:</span>
                    <span style="font-size:0.8rem; color:#f1f5f9; font-weight:600;"><?= htmlspecialchars((string)($pAuc['rekorLot'] ?? 'Özel Eser')) ?></span>
                  </div>
                  <div style="display:flex; justify-content:space-between; align-items:center;">
                    <span style="font-size:0.78rem; color:#848d9a; font-weight:600;">Oturum Satış Hacmi:</span>
                    <span style="font-size:1.15rem; color:#c5a059; font-weight:700; font-family:'Cinzel', serif;"><?= $formatPrice((float)($pAuc['toplamSatisTutari'] ?? 15000000)) ?></span>
                  </div>
                </div>
              </div>

              <!-- Aksiyon Butonları -->
              <div style="display:flex; gap:8px; margin-top:10px;">
                <button type="button" onclick="alert('📜 [<?= addslashes((string)$pAuc['baslik']) ?>] müzayedesinin resmi sonuç kataloğu ve noter tasdikli satış cetveli görüntülendi.');" class="btn-outline-gold" style="flex:1; justify-content:center; padding:8px; font-size:0.82rem;">
                  Noter Cetveli &rarr;
                </button>
                <button type="button" onclick="toggleHistoryView('sales');" class="btn-dark-pill" style="padding:8px 14px; font-size:0.82rem;">
                  Satılan Eserler
                </button>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      </div>

      <!-- 2. ALT GÖRÜNÜM: SATILAN ÜRÜNLER & ÇEKİÇ FİYATLARI (SALES) -->
      <div id="history_sales_view" style="display:none;">
        <!-- Filtre ve Arama Çubuğu -->
        <div class="panel-card" style="background:#0f141c; border:1px solid #1f2733; padding:18px; margin-bottom:24px;">
          <form method="GET" action="index.php" style="display:grid; grid-template-columns:1fr auto auto auto; gap:12px; align-items:center;">
            <input type="hidden" name="role" value="buyer">
            <input type="hidden" name="tab" value="gecmis">

            <div>
              <input type="text" name="sales_q" value="<?= htmlspecialchars((string)$pastSalesQuery) ?>" placeholder="Satılan eser adı, marka veya lot kodu ara (Örn: Chronograph, Osman Hamdi, Rolex)..." class="martello-input" style="width:100%; font-size:0.88rem; background:#0b0e14;">
            </div>

            <div style="min-width:180px;">
              <select name="sales_cat" class="martello-select" style="width:100%; font-size:0.88rem; background:#0b0e14;">
                <option value="">Tüm Kategoriler</option>
                <option value="Saat" <?= $pastSalesCat === 'Saat' ? 'selected' : '' ?>>Lüks Saatler</option>
                <option value="Tablo" <?= $pastSalesCat === 'Tablo' ? 'selected' : '' ?>>Klasik & Çağdaş Tablo</option>
                <option value="Heykel" <?= $pastSalesCat === 'Heykel' ? 'selected' : '' ?>>Heykel & Bronz</option>
                <option value="El Yazması" <?= $pastSalesCat === 'El Yazması' ? 'selected' : '' ?>>Nadir El Yazması</option>
                <option value="Seramik" <?= $pastSalesCat === 'Seramik' ? 'selected' : '' ?>>Antika Seramik</option>
              </select>
            </div>

            <div>
              <button type="submit" class="btn-gold" style="padding:9px 20px; font-weight:600; font-size:0.88rem; display:flex; align-items:center; gap:6px;">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                Filtrele
              </button>
            </div>

            <?php if (!empty($pastSalesQuery) || !empty($pastSalesCat)): ?>
              <div>
                <a href="index.php?role=buyer&tab=gecmis" class="btn-dark-pill" style="padding:9px 14px; text-decoration:none; font-size:0.82rem; display:inline-block;">
                  Temizle
                </a>
              </div>
            <?php endif; ?>
          </form>
        </div>

        <!-- Satılan Ürünler Listesi / Grid -->
        <?php if (empty($pastSales)): ?>
          <div class="panel-card" style="text-align:center; padding:40px;">
            <div style="font-size:2rem; margin-bottom:8px;">🔍</div>
            <div style="color:#fff; font-size:1.1rem; font-weight:600;">Aradığınız Kriterlere Uygun Satılmış Eser Bulunamadı</div>
            <p style="color:#848d9a; font-size:0.85rem; margin-top:4px;">Arama filtrelerini temizleyerek tüm geçmiş satışları listeleyebilirsiniz.</p>
            <a href="index.php?role=buyer&tab=gecmis" class="btn-outline-gold" style="display:inline-block; margin-top:12px; padding:7px 18px;">Tüm Arşivi Göster</a>
          </div>
        <?php else: ?>
          <div style="display:grid; grid-template-columns:repeat(auto-fill, minmax(320px, 1fr)); gap:22px;">
            <?php foreach ($pastSales as $sale): 
              $sTitle = (string)($sale['urunBaslik'] ?? ($sale['baslik'] ?? 'Eser'));
              $sImg = (string)($sale['gorselUrl'] ?? ($sale['gorsel'] ?? 'public/assets/images/watch.jpg'));
              $sCat = (string)($sale['kategoriAdi'] ?? ($sale['kategori'] ?? 'Koleksiyon'));
              $sAuc = (string)($sale['muzayedeBaslik'] ?? ($sale['muzayedeAdi'] ?? 'Geçmiş Müzayede'));
              $sDate = (string)($sale['satisTarihi'] ?? ($sale['kapanisTarihi'] ?? ''));
              $sHammer = is_numeric($sale['cekiçFiyati']) ? $formatPrice((float)$sale['cekiçFiyati']) : (string)$sale['cekiçFiyati'];
              $sOpen = is_numeric($sale['acilisFiyati']) ? $formatPrice((float)$sale['acilisFiyati']) : (string)$sale['acilisFiyati'];
            ?>
              <div class="panel-card" style="margin-bottom:0; display:flex; flex-direction:column; justify-content:space-between; overflow:hidden; border:1px solid #1f2733; padding:0;">
                <!-- Üst: Görsel ve Rozetler -->
                <div style="position:relative; height:200px; background:#0b0e14; overflow:hidden;">
                  <img src="<?= htmlspecialchars($sImg) ?>" alt="<?= htmlspecialchars($sTitle) ?>" style="width:100%; height:100%; object-fit:cover; transition:transform 0.3s;" onmouseover="this.style.transform='scale(1.05)'" onmouseout="this.style.transform='scale(1)'">
                  <div style="position:absolute; top:12px; left:12px; background:rgba(0,0,0,0.75); backdrop-filter:blur(4px); color:#c5a059; font-size:0.75rem; font-weight:600; padding:4px 8px; border-radius:4px; border:1px solid rgba(197,160,89,0.3);">
                    LOT <?= htmlspecialchars((string)($sale['lotKodu'] ?? 'LOT-00')) ?>
                  </div>
                  <div style="position:absolute; top:12px; right:12px; background:#10b981; color:#fff; font-size:0.72rem; font-weight:700; padding:4px 10px; border-radius:20px; box-shadow:0 2px 6px rgba(0,0,0,0.5);">
                    🔨 SATILDI
                  </div>
                  <div style="position:absolute; bottom:10px; left:12px; background:rgba(15,20,28,0.85); color:#94a3b8; font-size:0.72rem; padding:3px 8px; border-radius:4px;">
                    🏷 <?= htmlspecialchars($sCat) ?>
                  </div>
                </div>

                <!-- Gövde: Fiyat ve Eser Bilgisi -->
                <div style="padding:18px; flex:1; display:flex; flex-direction:column; justify-content:space-between;">
                  <div>
                    <h3 style="font-size:1.1rem; color:#fff; font-family:'Cinzel', serif; margin:0 0 10px 0; line-height:1.35;">
                      <?= htmlspecialchars($sTitle) ?>
                    </h3>

                    <!-- Çekiç Fiyatı ve Değer Artışı Kutusu -->
                    <div style="background:#0b0e14; border:1px solid #1a222e; border-radius:8px; padding:12px; margin-bottom:12px;">
                      <div style="display:flex; justify-content:space-between; align-items:baseline;">
                        <span style="font-size:0.72rem; color:#848d9a; text-transform:uppercase;">Nihai Çekiç Fiyatı:</span>
                        <span style="font-size:1.3rem; font-weight:700; color:#c5a059; font-family:'Cinzel', serif;">
                          <?= htmlspecialchars($sHammer) ?>
                        </span>
                      </div>
                      <div style="display:flex; justify-content:space-between; align-items:center; margin-top:6px; font-size:0.75rem; border-top:1px solid #171f2b; pt-2; padding-top:6px;">
                        <span style="color:#64748b;">Açılış: <?= htmlspecialchars($sOpen) ?></span>
                        <span style="color:#10b981; font-weight:600; background:rgba(16,185,129,0.1); padding:2px 6px; border-radius:4px;">
                          ▲ <?= htmlspecialchars((string)($sale['artisOrani'] ?? '')) ?>
                        </span>
                      </div>
                    </div>

                    <!-- Müzayede ve Kazanan Alıcı Bilgisi -->
                    <div style="font-size:0.78rem; color:#94a3b8; margin-bottom:10px; display:flex; flex-direction:column; gap:4px;">
                      <div>🏛 <strong>Müzayede:</strong> <?= htmlspecialchars($sAuc) ?></div>
                      <div>📅 <strong>Kapanış:</strong> <?= htmlspecialchars($sDate) ?></div>
                      <div>👤 <strong>Kazanan Alıcı:</strong> <span style="color:#f1f5f9; font-weight:600;"><?= htmlspecialchars((string)($sale['kazananAliciAnonim'] ?? 'Koleksiyoner')) ?></span></div>
                    </div>
                  </div>

                  <!-- Sertifika ve Aksiyon -->
                  <div style="border-top:1px solid #1a222e; padding-top:12px; display:flex; justify-content:space-between; align-items:center;">
                    <span style="font-size:0.72rem; color:#10b981; display:flex; align-items:center; gap:4px;">
                      <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
                      Orijinallik Tescilli
                    </span>
                    <button type="button" onclick="alert('🏛 [<?= addslashes($sTitle) ?>]\n\nNihai Çekiç Fiyatı: <?= addslashes($sHammer) ?>\nAçılış Fiyatı: <?= addslashes($sOpen) ?> (<?= addslashes((string)($sale['artisOrani'] ?? '')) ?>)\nMüzayede: <?= addslashes($sAuc) ?>\nKazanan: <?= addslashes((string)($sale['kazananAliciAnonim'] ?? '')) ?>\nEkspertiz Durumu: <?= addslashes((string)($sale['ekspertizNotu'] ?? '')) ?>\n\nBu eser Martello Arşivi ve Noter Satış Cetvelinde resmen tescil edilmiştir.');" class="btn-outline-gold" style="padding:6px 14px; font-size:0.78rem;">
                      Detay Cetveli &rarr;
                    </button>
                  </div>
                </div>
              </div>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>
      </div>
    </div>

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
                <span class="live-countdown" id="standardCountdown" data-target="standard" data-seconds="<?= (!empty($currentLot['antiSnipingSayaci']['sonBitisZamani']) && strtotime($currentLot['antiSnipingSayaci']['sonBitisZamani']) > time()) ? (strtotime($currentLot['antiSnipingSayaci']['sonBitisZamani']) - time()) : 19320 ?>">5 saat 23 dakika</span>
              </div>
              <div style="display:flex; gap:4px;">
                <button type="button" class="btn-outline-gold" style="font-size:0.7rem; padding:3px 8px; border-radius:4px; cursor:pointer;" onclick="setDemoTimer(25, 'standard')" title="Kademeli uzatma testi: İlk 2 basımda +30 sn, 3. ve sonrakilerde +10 sn">25sn Test</button>
                <button type="button" class="btn-outline-gold" style="font-size:0.7rem; padding:3px 8px; border-radius:4px; cursor:pointer;" onclick="setDemoTimer(5, 'standard')" title="Otomatik sonlandırma testi: Sayacı 5 saniyeye ayarlar">5sn Test</button>
                <button type="button" class="btn-outline-gold" style="font-size:0.7rem; padding:3px 8px; border-radius:4px; cursor:pointer; color:#f87171 !important; border-color:rgba(239,68,68,0.5);" onclick="setDemoTimer(0, 'standard')" title="İhaleyi hemen sonlandırır ve pey verme sayısını sıfırlar">0sn Bitir</button>
              </div>
            </div>

            <!-- Kademeli Anti-Sniping Bilgi ve Sayaç Durumu -->
            <?php 
              $isAuctionEnded = (($currentLot['durum'] ?? '') !== 'ACIK_ARTIRMADA') || 
                                (!empty($currentLot['antiSnipingSayaci']['sonBitisZamani']) && strtotime($currentLot['antiSnipingSayaci']['sonBitisZamani']) <= time());
              $stdExtCount = $isAuctionEnded ? 0 : (int)($currentLot['antiSnipingSayaci']['uzatmaSayisi'] ?? 0);
              $nextExtSeconds = ($stdExtCount < 2) ? 30 : 10;
            ?>
            <div style="background:rgba(212,175,55,0.08); border:1px solid rgba(212,175,55,0.2); border-radius:6px; padding:6px 10px; margin:8px 0 10px 0; font-size:0.74rem; display:flex; justify-content:space-between; align-items:center;">
              <span>⚡ Kural: <strong style="color:#d4af37;">1. & 2. basım +30sn</strong>, <strong style="color:#c5cbd3;">3.+ basım +10sn</strong></span>
              <span>Uzatma Sayısı: <strong id="standardExtCountBadge" style="color:#3fb950;"><?= $stdExtCount ?></strong> (Sıradaki: <strong id="standardNextExtBadge" style="color:<?= ($nextExtSeconds === 30) ? '#3fb950' : '#e3b341' ?>;">+<?= $nextExtSeconds ?> sn</strong>)</span>
            </div>

            <div id="standardAntiSnipingWarning" style="display:none; color:#f87171; font-size:0.78rem; margin:8px 0 12px 0; background:rgba(239,68,68,0.12); border:1px solid rgba(239,68,68,0.3); padding:6px 10px; border-radius:6px; align-items:center; gap:6px;">
              <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#f87171" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m21.73 18-8-14a2 2 0 0 0-3.48 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3Z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
              <span>Son 30 saniye! Pey verilirse süre kademeli uzatılacaktır (İlk 2 basımda +30 sn, 3. ve sonrakilerde +10 sn). Pey gelmezse ihale sonlanacaktır.</span>
            </div>

            <!-- Pey Ver Formu -->
            <form method="POST" id="standardBidForm">
              <input type="hidden" name="action" value="place_bid">
              <input type="hidden" name="lot_id" value="<?= $currentLot['id'] ?>">
              <input type="hidden" name="user_id" value="<?= $buyerId ?>">
              <input type="hidden" id="standardRemainingSecondsInput" name="remaining_seconds" value="<?= (!empty($currentLot['antiSnipingSayaci']['sonBitisZamani']) && strtotime($currentLot['antiSnipingSayaci']['sonBitisZamani']) > time()) ? (strtotime($currentLot['antiSnipingSayaci']['sonBitisZamani']) - time()) : 19320 ?>">

              <div class="form-group" style="margin-bottom:12px;">
                <label class="form-label">Teklifiniz (Asgari: <?= $formatPrice($minNextBid) ?>)</label>
                <input type="number" step="500" id="bidAmountInput" name="bid_amount" class="form-control" value="<?= $minNextBid ?>" required>
              </div>

              <div style="display:grid; grid-template-columns:1fr 1fr; gap:8px; margin-bottom:14px;">
                <button type="button" class="btn-outline-gold btn-sm btn-quick-bid" data-add="1500" onclick="const bi=document.getElementById('bidAmountInput'); if(bi) bi.value=parseFloat(bi.value||0)+1500;">+<?= $formatPrice(1500) ?></button>
                <button type="button" class="btn-outline-gold btn-sm btn-quick-bid" data-add="3000" onclick="const bi=document.getElementById('bidAmountInput'); if(bi) bi.value=parseFloat(bi.value||0)+3000;">+<?= $formatPrice(3000) ?></button>
              </div>

              <button type="submit" id="standardBidSubmitBtn" class="btn-register-glow">
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
         VIP MÜZAYEDE / SALON D'HONNEUR (Özel Müşteriler VIP Müzayede Salonu)
         ==================================================================== -->
    <div id="buyerTab_vip" class="tab-content <?= $activeTab === 'vip' ? 'active' : '' ?>">
      <!-- VIP Özel Statü & Konsiyerj Danışma Şeridi -->
      <div style="background:linear-gradient(135deg, #11151f 0%, #171d2b 50%, #0d1017 100%); border:1px solid #c5a059; border-radius:14px; padding:20px 24px; margin-bottom:24px; box-shadow:0 8px 30px rgba(0,0,0,0.65), inset 0 0 40px rgba(197,160,89,0.08); position:relative; overflow:hidden;">
        <div style="position:absolute; right:-20px; top:-20px; width:160px; height:160px; background:radial-gradient(circle, rgba(212,175,55,0.15) 0%, transparent 70%); pointer-events:none;"></div>
        
        <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:16px;">
          <div>
            <div style="display:flex; align-items:center; gap:8px; margin-bottom:4px;">
              <span style="color:#d4af37; display:inline-flex; align-items:center;">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m2 4 3 12h14l3-12-6 7-4-7-4 7-6-7zm3 16h14"/></svg>
              </span>
              <span style="font-family:var(--font-brand); font-size:0.75rem; letter-spacing:2px; color:#d4af37; text-transform:uppercase; font-weight:700;">MARTELLO PRIVATE CLIENT SERVICES • SALON D'HONNEUR</span>
            </div>
            <h1 style="font-family:var(--font-serif); font-size:1.95rem; color:#f5f0e6; margin:0 0 6px 0; font-weight:500;">
              Özel Müşteriler VIP Müzayede Salonu
            </h1>
            <div style="font-size:0.84rem; color:#a0aab8; display:flex; align-items:center; gap:16px; flex-wrap:wrap;">
              <span>Üyelik: <strong style="color:#3fb950;">✓ <?= htmlspecialchars($userVipLevel) ?> TIER (Akredite)</strong></span>
              <span>•</span>
              <span>Özel Temsilciniz: <strong style="color:#f0f3f6;">Leyla Alptekin (Private Client Executive)</strong></span>
              <span>•</span>
              <span>VIP Doğrudan Hat: <strong style="color:#d4af37;">+90 (212) 980 00 24</strong></span>
            </div>
          </div>

          <div style="display:flex; align-items:center; gap:12px;">
            <div style="background:rgba(0,0,0,0.5); border:1px solid #334155; border-radius:10px; padding:10px 16px; text-align:right;">
              <div style="font-size:0.72rem; color:#8b949e; text-transform:uppercase; letter-spacing:0.5px;">VIP Teklif Limitiniz</div>
              <div style="font-size:1.25rem; font-weight:700; color:#d4af37; font-family:var(--font-serif);"><?= $formatPrice($userLimit) ?></div>
            </div>
            <button type="button" class="btn-gold" style="padding:11px 18px; font-size:0.82rem; font-weight:600; white-space:nowrap;" onclick="document.getElementById('vipLimitModal').style.display='flex'">
              Limit Artır ↗
            </button>
            <button type="button" class="btn-outline-gold" style="padding:11px 18px; font-size:0.82rem; font-weight:600; white-space:nowrap;" onclick="document.getElementById('vipPhoneModal').style.display='flex'">
              Telefonla Katılım Rezerve Et
            </button>
          </div>
        </div>
      </div>

      <!-- VIP Eser Seçim Sekmeleri (Lot Switcher Bar) -->
      <div style="margin-bottom:20px;">
        <div style="font-size:0.8rem; color:#c5a059; font-weight:700; text-transform:uppercase; letter-spacing:1px; margin-bottom:10px; display:flex; align-items:center; gap:6px;">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polygon points="12 6 12 12 16 14"/></svg>
          <span>Aktif VIP Başyapıtlar (Salon Oturumu #802)</span>
        </div>
        <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(280px, 1fr)); gap:12px;">
          <?php foreach ($vipLots as $vlot): 
            $isSelected = (int)$vlot['id'] === (int)$currentVipLot['id'];
          ?>
            <a href="?role=buyer&tab=vip&vip_lot_id=<?= $vlot['id'] ?>" style="text-decoration:none; display:flex; align-items:center; gap:12px; padding:12px 14px; border-radius:10px; background:<?= $isSelected ? 'rgba(212,175,55,0.12)' : '#11151f' ?>; border:1px solid <?= $isSelected ? '#d4af37' : '#232b3b' ?>; transition:all 0.2s;" onmouseover="this.style.borderColor='#d4af37';" onmouseout="this.style.borderColor='<?= $isSelected ? '#d4af37' : '#232b3b' ?>';">
              <img src="<?= htmlspecialchars($vlot['gorselUrl']) ?>" alt="<?= htmlspecialchars($vlot['baslik']) ?>" style="width:48px; height:48px; border-radius:6px; object-fit:cover; border:1px solid #3d4a61;">
              <div style="flex:1; overflow:hidden;">
                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:2px;">
                  <span style="font-size:0.7rem; font-weight:700; color:#d4af37; background:rgba(212,175,55,0.15); padding:1px 6px; border-radius:4px;"><?= htmlspecialchars($vlot['lotKodu']) ?></span>
                  <span style="font-size:0.75rem; color:#3fb950; font-weight:600;">CANLI</span>
                </div>
                <div style="font-size:0.83rem; color:#fff; font-weight:600; white-space:nowrap; text-overflow:ellipsis; overflow:hidden;"><?= htmlspecialchars($vlot['baslik']) ?></div>
                <div style="font-size:0.78rem; color:#aaa; margin-top:2px;">Teklif: <strong style="color:#d4af37;"><?= $formatPrice((float)$vlot['mevcutEnYuksekTeklif']) ?></strong></div>
              </div>
            </a>
          <?php endforeach; ?>
        </div>
      </div>

      <!-- VIP 2 SÜTUNLU DETAY VE PEY ALANI -->
      <div class="lot-detail-container" style="display:grid; grid-template-columns:1.3fr 380px; gap:26px;">
        <!-- SOL SÜTUN: VIP Eser Galerisi, Tarihçe, Bilimsel Ekspertiz ve Zırhlı Lojistik -->
        <div>
          <!-- Galeri Çerçevesi -->
          <div class="gallery-wrapper" style="border:1px solid rgba(212,175,55,0.3); box-shadow:0 10px 35px rgba(0,0,0,0.85);">
            <div class="main-watch-frame" style="background:#080a0f; display:flex; align-items:center; justify-content:center; overflow:hidden; position:relative; min-height:340px;">
              <img id="mainVipLotImage" src="<?= htmlspecialchars($currentVipLot['gorselUrl']) ?>" alt="<?= htmlspecialchars($currentVipLot['baslik']) ?>" style="max-height:310px; width:auto; object-fit:contain; filter:drop-shadow(0 15px 35px rgba(0,0,0,0.95)); transition:all 0.3s ease;">
              
              <div style="position:absolute; top:14px; left:16px; font-size:0.72rem; color:#0b0e14; background:linear-gradient(135deg, #dfbe7e 0%, #aa8037 100%); padding:4px 10px; border-radius:4px; font-weight:700; letter-spacing:0.5px; box-shadow:0 2px 8px rgba(0,0,0,0.5);">
                ★ SARAY KOLEKSİYONU & MÜZE STATÜSÜ
              </div>

              <div style="position:absolute; bottom:12px; left:16px; font-size:0.75rem; color:#c5cbd3; background:rgba(0,0,0,0.75); padding:4px 10px; border-radius:4px; border:1px solid rgba(255,255,255,0.1);">
                <?= htmlspecialchars($currentVipLot['baslik']) ?> • Lot: <?= htmlspecialchars($currentVipLot['lotKodu']) ?>
              </div>
            </div>

            <!-- 4 Makro İnceleme Thumbnail'i -->
            <div class="watch-thumbnails-grid">
              <div class="watch-thumb" title="Optik Spektrometre İncelemesi" style="display:flex; align-items:center; justify-content:center; color:#c5a059; cursor:pointer;" onclick="document.getElementById('mainVipLotImage').src='<?= htmlspecialchars($currentVipLot['gorselUrl']) ?>';">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="11" cy="11" r="8"/><line x1="21" x2="16.65" y1="21" y2="16.65"/><circle cx="11" cy="11" r="3"/></svg>
              </div>
              <div class="watch-thumb" title="Usta İmzası ve Hicri Tarih Damgası" style="display:flex; align-items:center; justify-content:center; color:#c5a059; cursor:pointer;" onclick="document.getElementById('mainVipLotImage').src='<?= htmlspecialchars($currentVipLot['gorselUrl']) ?>';">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
              </div>
              <div class="watch-thumb" title="Alaşımsal Metalurji Analiz Raporu" style="display:flex; align-items:center; justify-content:center; color:#c5a059; cursor:pointer;" onclick="document.getElementById('mainVipLotImage').src='<?= htmlspecialchars($currentVipLot['gorselUrl']) ?>';">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="12" cy="12" r="10"/><path d="m4.93 4.93 4.24 4.24"/><path d="m14.83 9.17 4.24-4.24"/><path d="m14.83 14.83 4.24 4.24"/><path d="m9.17 14.83-4.24 4.24"/></svg>
              </div>
              <div class="watch-thumb" title="Provenans ve Hanedan Şeceresi" style="display:flex; align-items:center; justify-content:center; color:#c5a059; cursor:pointer;" onclick="document.getElementById('mainVipLotImage').src='<?= htmlspecialchars($currentVipLot['gorselUrl']) ?>';">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
              </div>
            </div>
          </div>

          <!-- Eser Açıklaması ve Sanat Tarihsel Önemi -->
          <div style="background:#11151f; border:1px solid #232b3b; border-radius:12px; padding:18px 22px; margin-top:16px; margin-bottom:16px;">
            <div style="font-size:0.75rem; color:#d4af37; font-weight:700; letter-spacing:1px; text-transform:uppercase; margin-bottom:6px;">PROVENANS VE SANAT TARİHİ DETAYLARI</div>
            <p style="font-size:0.87rem; line-height:1.7; color:#cbd5e1; margin:0 0 10px 0;">
              <?= htmlspecialchars($currentVipLot['baslik']) ?>: Dönemin saray astronomları ve en seçkin sanatkârları tarafından imal edilmiştir. Gezegensel hareketler, 12 burç takımyıldızı ve kubbe koordinatları aslına uygun olarak kusursuz korunmuştur. Eser, 2863 Sayılı Kültür ve Tabiat Varlıklarını Koruma Kanunu tescil ve yurtiçi özel koleksiyon devir izinlerine haizdir.
            </p>
            <div style="display:flex; gap:16px; font-size:0.78rem; color:#94a3b8; border-top:1px dashed #2a3449; padding-top:10px;">
              <span>Dönem: <strong>Klasik Osmanlı / 16. Yüzyıl</strong></span>
              <span>Materyal: <strong>Yaldızlı Gravürlü Pirinç</strong></span>
              <span>Mevcut Koruma: <strong>Koleksiyoner Kasası</strong></span>
            </div>
          </div>

          <!-- Beyaz Lüks Bilimsel Ekspertiz Kartı -->
          <div class="expert-white-card" style="box-shadow:0 8px 30px rgba(0,0,0,0.5);">
            <div class="expert-card-top">
              <span class="expert-card-title">Arkeometrik & Bilimsel Ekspertiz Raporu</span>
              <span class="expert-card-badge" style="background:#0c0f14; color:#d4af37; border:1px solid #d4af37;">VIP SEÇKİ BAŞYAPIT</span>
            </div>
            
            <ul class="expert-card-list">
              <li>XRF Spektrometre Metalurjik Analizi: Alaşım kompozisyonu (%88 Cu, %12 Zn) 16. Yy. saray dökmeciliği ile %99.4 eşleşmektedir.</li>
              <li>İmza & Ebced Tetkiki: İmzalı usta mührü ve döküm üzerindeki hicri tarih dökümanter arşiv kayıtlarıyla doğrulanmıştır.</li>
              <li>Topkapı Sarayı 1912 Hazine İntikal Defteri ve Viyana Sanat Tarihi Enstitüsü mukayeseli şeceresi tamdır.</li>
            </ul>

            <div class="expert-card-bottom">
              <div>
                <div class="expert-reviewer-title">Akredite Kurul İncelemesi:</div>
                <div class="expert-stars">★ ★ ★ ★ ★</div>
              </div>
              
              <div class="expert-stamp-seal" style="border-color:#b45309; color:#b45309;">
                MARTELLO<br>VIP SALON<br>★ ★ ★<br>SARAY MÜHRÜ
              </div>
            </div>
          </div>

          <!-- Zırhlı Özel Lojistik ve Lloyds Sanat Sigortası Güvencesi -->
          <div style="background:rgba(212,175,55,0.06); border:1px solid rgba(212,175,55,0.3); border-radius:10px; padding:14px 18px; margin-top:16px; display:flex; align-items:center; gap:14px;">
            <div style="color:#d4af37;">
              <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
            </div>
            <div style="font-size:0.82rem; color:#cbd5e1; line-height:1.5;">
              <strong style="color:#fff;">Özel Zırhlı Taşıma ve %100 Teminat:</strong> Kazanan VIP alıcıya teslimat, silahlı sanat kuryeleri ve Lloyds of London tam kıymet sigortasıyla noter huzurunda teslim edilir.
            </div>
          </div>
        </div>

        <!-- SAĞ SÜTUN: VIP Canlı Teklif Kartı, Telefonla Pey ve VIP Katılımcı Akışı -->
        <div>
          <!-- VIP Canlı Teklif Kartı -->
          <div class="bid-panel-card" style="border-color:#c5a059; box-shadow:0 8px 30px rgba(0,0,0,0.7), inset 0 0 20px rgba(212,175,55,0.06);">
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:8px;">
              <div class="bid-title-muted" style="color:#d4af37; font-weight:700; letter-spacing:1px; text-transform:uppercase;">VIP GÜNCEL TEKLİF</div>
              <span class="badge" style="background:rgba(63,185,80,0.15); color:#3fb950; border:1px solid #3fb950; font-size:0.7rem;">LİDER TEKLİF</span>
            </div>

            <div class="bid-price-large" style="color:#dfbe7e; font-size:2.2rem;">
              <?= $formatPrice((float)$currentVipLot['mevcutEnYuksekTeklif']) ?>
            </div>

            <div class="bid-timer-text" style="display:flex; align-items:center; justify-content:space-between; margin-bottom:12px;">
              <div style="display:flex; align-items:center; gap:6px;">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="#d4af37" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                <span class="live-countdown" id="vipCountdown" data-target="vip" data-seconds="<?= (!empty($currentVipLot['antiSnipingSayaci']['sonBitisZamani']) && strtotime($currentVipLot['antiSnipingSayaci']['sonBitisZamani']) > time()) ? (strtotime($currentVipLot['antiSnipingSayaci']['sonBitisZamani']) - time()) : 43200 ?>">12 gün 04 saat</span>
              </div>
              <div style="display:flex; gap:4px;">
                <button type="button" class="btn-outline-gold" style="font-size:0.7rem; padding:3px 8px; border-radius:4px; cursor:pointer;" onclick="setDemoTimer(25, 'vip')" title="VIP kademeli uzatma testi (1. ve 2. basımda +30 sn, 3.+ basımda +10 sn)">VIP 25sn Test</button>
                <button type="button" class="btn-outline-gold" style="font-size:0.7rem; padding:3px 8px; border-radius:4px; cursor:pointer;" onclick="setDemoTimer(5, 'vip')" title="VIP otomatik sonlandırma testi">VIP 5sn Test</button>
                <button type="button" class="btn-outline-gold" style="font-size:0.7rem; padding:3px 8px; border-radius:4px; cursor:pointer; color:#f87171 !important; border-color:rgba(239,68,68,0.5);" onclick="setDemoTimer(0, 'vip')" title="VIP ihaleyi hemen sonlandırır ve pey sayısını sıfırlar">VIP 0sn Bitir</button>
              </div>
            </div>

            <!-- VIP Kademeli Anti-Sniping Bilgi ve Sayaç Durumu -->
            <?php 
              $isVipEnded = (($currentVipLot['durum'] ?? '') !== 'ACIK_ARTIRMADA') || 
                            (!empty($currentVipLot['antiSnipingSayaci']['sonBitisZamani']) && strtotime($currentVipLot['antiSnipingSayaci']['sonBitisZamani']) <= time());
              $vipExtCount = $isVipEnded ? 0 : (int)($currentVipLot['antiSnipingSayaci']['uzatmaSayisi'] ?? 0);
              $nextVipExtSeconds = ($vipExtCount < 2) ? 30 : 10;
            ?>
            <div style="background:rgba(212,175,55,0.08); border:1px solid rgba(212,175,55,0.2); border-radius:6px; padding:6px 10px; margin:8px 0 10px 0; font-size:0.74rem; display:flex; justify-content:space-between; align-items:center;">
              <span>👑 VIP Kural: <strong style="color:#d4af37;">1. & 2. basım +30sn</strong>, <strong style="color:#c5cbd3;">3.+ basım +10sn</strong></span>
              <span>Uzatma: <strong id="vipExtCountBadge" style="color:#3fb950;"><?= $vipExtCount ?></strong> (Sıradaki: <strong id="vipNextExtBadge" style="color:<?= ($nextVipExtSeconds === 30) ? '#3fb950' : '#e3b341' ?>;">+<?= $nextVipExtSeconds ?> sn</strong>)</span>
            </div>

            <div id="vipAntiSnipingWarning" style="display:none; color:#f87171; font-size:0.78rem; margin:8px 0 12px 0; background:rgba(239,68,68,0.12); border:1px solid rgba(239,68,68,0.3); padding:6px 10px; border-radius:6px; align-items:center; gap:6px;">
              <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#f87171" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m21.73 18-8-14a2 2 0 0 0-3.48 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3Z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
              <span>Son 30 saniye! VIP pey verilirse süre kademeli uzatılacaktır (İlk 2 basımda +30 sn, 3. ve sonrakilerde +10 sn). Pey gelmezse ihale sonlanacaktır.</span>
            </div>

            <!-- VIP Kurallar Bilgi Notu -->
            <div style="background:rgba(255,255,255,0.03); border:1px solid #232b3b; border-radius:6px; padding:8px 10px; font-size:0.74rem; color:#8b949e; margin-bottom:14px; line-height:1.4;">
              ⚡ <strong style="color:#ddd;">VIP Müzayede Kuralı (SRS NFR-14):</strong> Asgari artırım adımı <strong><?= $formatPrice((float)($currentVipLot['minimumArtisTutari'] ?? 10000)) ?></strong>. Son 30 sn içinde pey verilirse kademeli uzatma: İlk 2 basımda +30 sn, 3. ve sonrakilerde +10 sn. Son 30 sn içinde yeni pey gelmezse ihale sonlandırılır.
            </div>

            <!-- VIP Pey Ver Formu -->
            <form method="POST" id="vipBidForm">
              <input type="hidden" name="action" value="place_bid">
              <input type="hidden" name="lot_id" value="<?= $currentVipLot['id'] ?>">
              <input type="hidden" name="user_id" value="<?= $buyerId ?>">
              <input type="hidden" id="vipRemainingSecondsInput" name="remaining_seconds" value="<?= (!empty($currentVipLot['antiSnipingSayaci']['sonBitisZamani']) && strtotime($currentVipLot['antiSnipingSayaci']['sonBitisZamani']) > time()) ? (strtotime($currentVipLot['antiSnipingSayaci']['sonBitisZamani']) - time()) : 43200 ?>">

              <div class="form-group" style="margin-bottom:12px;">
                <label class="form-label" style="color:#c5cbd3;">Teklifiniz (Asgari: <?= $formatPrice($minNextVipBid) ?>)</label>
                <input type="number" step="5000" id="vipBidAmountInput" name="bid_amount" class="form-control" value="<?= $minNextVipBid ?>" required style="font-size:1.15rem; font-weight:700; color:#d4af37; background:#07090e; border-color:#c5a059;">
              </div>

              <div style="display:grid; grid-template-columns:1fr 1fr; gap:8px; margin-bottom:14px;">
                <button type="button" class="btn-outline-gold btn-sm" onclick="const bi=document.getElementById('vipBidAmountInput'); if(bi) bi.value=parseFloat(bi.value||0)+10000;">+10.000</button>
                <button type="button" class="btn-outline-gold btn-sm" onclick="const bi=document.getElementById('vipBidAmountInput'); if(bi) bi.value=parseFloat(bi.value||0)+25000;">+25.000</button>
                <button type="button" class="btn-outline-gold btn-sm" onclick="const bi=document.getElementById('vipBidAmountInput'); if(bi) bi.value=parseFloat(bi.value||0)+50000;">+50.000</button>
                <button type="button" class="btn-outline-gold btn-sm" onclick="const bi=document.getElementById('vipBidAmountInput'); if(bi) bi.value=parseFloat(bi.value||0)+100000;">+100.000</button>
              </div>

              <button type="submit" id="vipBidSubmitBtn" class="btn-register-glow" style="font-size:1rem; padding:12px;">
                👑 VIP Resmi Pey Ver
              </button>
            </form>

            <!-- VIP Rakip Teklif Canlı Simülasyonu Butonu -->
            <div style="border-top:1px dashed #2a3449; margin-top:14px; padding-top:12px;">
              <form method="POST" style="margin:0;">
                <input type="hidden" name="action" value="simulate_vip_bid">
                <input type="hidden" name="lot_id" value="<?= $currentVipLot['id'] ?>">
                <button type="submit" class="btn-outline-gold btn-sm" style="width:100%; font-size:0.75rem; justify-content:center; color:#60a5fa !important; border-color:#60a5fa; cursor:pointer;" title="Salondaki diğer bir VIP alıcının canlı pey sürmesini test eder">
                  ⚡ Başka VIP Alıcıdan Rakip Pey Gönder (+<?= $formatPrice((float)($currentVipLot['minimumArtisTutari'] ?? 10000)) ?>)
                </button>
              </form>
            </div>
          </div>

          <!-- VIP Telefonla Katılım & Konsiyerj Hizmeti Kartı -->
          <div class="bid-panel-card" style="margin-top:16px;">
            <div style="display:flex; align-items:center; gap:10px; margin-bottom:10px;">
              <div style="width:38px; height:38px; border-radius:50%; background:linear-gradient(135deg, #dfbe7e 0%, #aa8037 100%); display:flex; align-items:center; justify-content:center; color:#0c0f14; font-weight:700;">
                LA
              </div>
              <div>
                <div style="font-size:0.85rem; font-weight:700; color:#fff;">Leyla Alptekin</div>
                <div style="font-size:0.72rem; color:#d4af37;">Kıdemli Özel Müşteri Danışmanı</div>
              </div>
            </div>
            <p style="font-size:0.79rem; color:#94a3b8; line-height:1.45; margin-bottom:12px;">
              Salonda fiziksel olarak bulunamayan müşterilerimiz için özel şifreli hat üzerinden canlı telefonla pey hizmeti tahsis edilmektedir.
            </p>
            <button type="button" class="btn-outline-gold" style="width:100%; justify-content:center; font-size:0.8rem; padding:8px;" onclick="document.getElementById('vipPhoneModal').style.display='flex'">
              Telefonla Katılım Rezerve Et ↗
            </button>
          </div>

          <!-- VIP Salondaki Son Teklifler Akışı -->
          <div class="other-bids-card" style="margin-top:16px;">
            <div class="other-bids-title" style="color:#d4af37; display:flex; align-items:center; justify-content:space-between;">
              <span>VIP Salonu Teklif Geçmişi</span>
              <span style="font-size:0.7rem; color:#8b949e;">Canlı Akış</span>
            </div>
            
            <?php if (!empty($vipLotBids)): ?>
              <?php foreach (array_slice($vipLotBids, 0, 5) as $vb): ?>
                <div class="other-bid-row">
                  <div class="other-bid-user" style="display:flex; align-items:center; gap:6px;">
                    <span style="color:#d4af37;">★</span>
                    <span><?= htmlspecialchars($vb['anonimKod'] ?? $vb['aliciAnonimKod'] ?? 'VIP Katılımcı') ?></span>
                  </div>
                  <div class="other-bid-time">
                    <strong style="color:#dfbe7e;"><?= $formatPrice((float)$vb['teklifTutari']) ?></strong>
                  </div>
                </div>
              <?php endforeach; ?>
            <?php else: ?>
              <div class="other-bid-row">
                <div class="other-bid-user" style="display:flex; align-items:center; gap:6px;">
                  <span style="color:#d4af37;">★</span>
                  <span>damror0ki (VIP)</span>
                </div>
                <div class="other-bid-time"><strong style="color:#dfbe7e;"><?= $formatPrice((float)$currentVipLot['mevcutEnYuksekTeklif']) ?></strong></div>
              </div>
              <div class="other-bid-row">
                <div class="other-bid-user" style="display:flex; align-items:center; gap:6px;">
                  <span style="color:#d4af37;">★</span>
                  <span>yortunen... (VIP)</span>
                </div>
                <div class="other-bid-time"><strong style="color:#dfbe7e;"><?= $formatPrice((float)$currentVipLot['mevcutEnYuksekTeklif'] - 10000) ?></strong></div>
              </div>
            <?php endif; ?>
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
          <!-- 100% Martello Escrow Koruması Bannerı -->
          <div style="background:rgba(52,211,153,0.08); border-bottom:1px solid rgba(52,211,153,0.25); padding:12px 20px; display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:10px;">
            <div style="display:flex; align-items:center; gap:8px;">
              <span style="font-size:1.25rem;">🛡️</span>
              <div>
                <strong style="color:#34d399; font-size:0.83rem;">%100 Martello Escrow & Emanet Koruması:</strong>
                <span style="color:#94a3b8; font-size:0.77rem; display:block;">
                  Teklif pey teminatınız ve nihai satış tutarı emanet hesapta bloke edilir. Eseri sağlam teslim alıp onaylayana kadar bedel satıcıya aktarılmaz.
                </span>
              </div>
            </div>
            <span class="badge" style="background:#064e3b; color:#6ee7b7; border:1px solid #059669; font-size:0.72rem; padding:3px 8px;">Emanet Havuzu Aktif</span>
          </div>

          <div style="padding:14px 20px; border-bottom:1px solid var(--border-color); display:flex; justify-content:space-between; align-items:center;">
            <div style="font-weight:600; color:#fff; font-size:0.95rem;">Kazanılan Lotlar Listesi</div>
            <span class="badge badge-success">Emanet Korumalı Satışlar</span>
          </div>

          <table class="data-table" style="margin:0;">
            <thead>
              <tr>
                <th style="padding-left:20px;">Lot</th>
                <th>Ürün</th>
                <th>Kategori</th>
                <th>Son Tarih</th>
                <th>Son Fiyat</th>
                <th>Emanet / Ödeme</th>
                <th style="padding-right:20px;">İşlemler</th>
              </tr>
            </thead>
            <tbody>
              <tr>
                <td style="padding-left:20px;">
                  <div style="display:flex; align-items:center; gap:10px;">
                    <img src="public/assets/images/carpet.jpg" alt="Halı" style="width:40px; height:40px; border-radius:6px; object-fit:cover; border:1px solid #2a3449;" onerror="this.src='public/assets/images/vase.jpg'">
                    <span style="font-weight:600; color:#fff;">AUC-1001</span>
                  </div>
                </td>
                <td style="font-weight:600; color:#fff;">1920'ler Osmanlı Halı Levhası</td>
                <td style="color:var(--text-muted);">Antika & Sanat</td>
                <td>12-Ekim-2026</td>
                <td style="font-family:var(--font-serif); font-weight:700; color:var(--gold-bright);"><?= $formatPrice(70200) ?></td>
                <td>
                  <span class="badge" style="background:#854d0e; color:#fef08a; border:1px solid #ca8a04; font-size:0.72rem; padding:3px 8px;">
                    🔒 Emanette Bloke
                  </span>
                </td>
                <td style="padding-right:20px;">
                  <form method="POST" style="margin:0;" onsubmit="return confirm('Eseri eksiksiz teslim aldığınızı onaylıyor musunuz?\\n\\nEmanet hesaptaki satış bedeli satıcı hesabına aktarılacaktır.');">
                    <input type="hidden" name="action" value="complete_escrow_payout">
                    <input type="hidden" name="escrow_id" value="7001">
                    <input type="hidden" name="actor_id" value="<?= $buyerId ?>">
                    <input type="hidden" name="actor_role" value="ALICI">
                    <input type="hidden" name="release_note" value="Alıcı kargoyu teslim aldı ve satıcıya hakediş aktarımını onayladı.">
                    <button type="submit" class="btn-sm btn-gold" style="font-size:0.75rem; padding:5px 10px; cursor:pointer; background:#10b981; border-color:#059669; color:#fff;">
                      ✓ Teslim Aldım (Satıcıya Aktar)
                    </button>
                  </form>
                  <a href="javascript:void(0)" onclick="alert('Kargo: KRG-99887766 (Dağıtımda)')" style="color:#d1b179; font-size:0.75rem; display:block; margin-top:4px; text-decoration:none;">Kargoyu Takip Et</a>
                </td>
              </tr>

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
                <td style="font-family:var(--font-serif); font-weight:700; color:var(--gold-bright);"><?= $formatPrice(43500) ?></td>
                <td>
                  <span class="badge" style="background:#14532d; color:#86efac; border:1px solid #16a34a; font-size:0.72rem; padding:3px 8px;">
                    ✓ Satıcıya Aktarıldı
                  </span>
                </td>
                <td style="padding-right:20px;">
                  <span style="color:#4ade80; font-size:0.75rem; font-weight:600; display:block;">İşlem Tamamlandı</span>
                  <a href="javascript:void(0)" onclick="alert('Fatura No: FAT-2026-0991 (PDF olarak indirildi)')" style="color:#d1b179; font-size:0.75rem; display:block; margin-top:2px; text-decoration:none;">Faturayı İndir</a>
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
                <td style="font-family:var(--font-serif); font-weight:700; color:var(--gold-bright);"><?= $formatPrice(3200) ?></td>
                <td>
                  <span class="badge" style="background:#14532d; color:#86efac; border:1px solid #16a34a; font-size:0.72rem; padding:3px 8px;">
                    ✓ Satıcıya Aktarıldı
                  </span>
                </td>
                <td style="padding-right:20px;">
                  <span style="color:#4ade80; font-size:0.75rem; font-weight:600; display:block;">İşlem Tamamlandı</span>
                  <a href="javascript:void(0)" onclick="alert('Fatura No: FAT-2026-0984 (PDF olarak indirildi)')" style="color:#d1b179; font-size:0.75rem; display:block; margin-top:2px; text-decoration:none;">Faturayı İndir</a>
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
              <span style="color:#fff; font-weight:500;"><?= $formatPrice(43500) ?></span>
            </div>

            <div style="display:flex; justify-content:space-between; margin-bottom:10px; font-size:0.85rem;">
              <span style="color:var(--text-muted);">Ödeme Durumu</span>
              <span style="color:#4ade80; font-weight:600;"><?= $formatPrice(9200) ?> (Ödendi)</span>
            </div>

            <div style="display:flex; justify-content:space-between; margin-bottom:10px; font-size:0.85rem;">
              <span style="color:var(--text-muted);">Ödeme Son Günü</span>
              <span style="color:#fff; font-weight:500;">12-Ekim-2026</span>
            </div>

            <div style="display:flex; justify-content:space-between; margin-top:12px; padding-top:10px; border-top:1px solid var(--border-color); font-size:0.9rem;">
              <span style="color:var(--text-muted);">Ödeme Detayları</span>
              <span style="color:var(--gold-bright); font-weight:700;"><?= $formatPrice(12800) ?></span>
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

      <!-- Müzayede Keşfet Banner Kartı -->
      <div class="panel-card" style="margin-bottom:24px; background:linear-gradient(135deg, #090d14 0%, #161e2b 100%); border:1px solid rgba(212,175,55,0.45); box-shadow:0 10px 30px rgba(0,0,0,0.7); display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:16px; padding:22px 28px;">
        <div style="max-width:680px;">
          <div style="color:var(--gold-primary); font-size:0.75rem; font-weight:700; text-transform:uppercase; letter-spacing:1.5px; margin-bottom:4px; display:flex; align-items:center; gap:6px;">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polygon points="16.24 7.76 14.12 14.12 7.76 16.24 9.88 9.88 16.24 7.76"/></svg>
            <span>MARTELLO • SALON REHBERİ</span>
          </div>
          <h3 style="font-family:var(--font-serif); font-size:1.45rem; color:#f0f3f6; margin-bottom:6px; font-weight:500;">
            Canlı ve Gelecekte Yapılan Müzayedeler
          </h3>
          <p style="font-size:0.88rem; color:#94a3b8; margin:0; line-height:1.5;">
            Şu an devam eden <strong><?= count($liveAuctions) ?> canlı açık artırma</strong> ve takvimlendirilmiş <strong><?= count($upcomingAuctions) ?> gelecek müzayede oturumunu</strong> keşfedin, katalogları inceleyin veya canlı yayına katılın.
          </p>
        </div>
        <button type="button" onclick="switchBuyerTab('kesfet', this)" class="btn-register-glow" style="padding:11px 26px; font-weight:600; font-size:0.9rem; display:inline-flex; align-items:center; gap:8px;">
          Müzayedeleri Keşfet &rarr;
        </button>
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
                  <div style="font-size:0.75rem; color:var(--text-muted);">AUC-0991 • Son Fiyat: <?= $formatPrice(43500) ?></div>
                </div>
              </div>
              <span class="badge badge-success">ÖDENDİ</span>
            </div>
            <div style="display:flex; justify-content:space-between; align-items:center; padding:10px 14px; background:#0e1218; border:1px solid var(--border-color); border-radius:8px;">
              <div style="display:flex; align-items:center; gap:12px;">
                <img src="public/assets/images/books.jpg" alt="Kitap" style="width:40px; height:40px; border-radius:6px; object-fit:cover;">
                <div>
                  <div style="font-weight:600; color:#fff; font-size:0.9rem;">Nadir Kitap Koleksiyonu</div>
                  <div style="font-size:0.75rem; color:var(--text-muted);">AUC-0984 • Son Fiyat: <?= $formatPrice(3200) ?></div>
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
            <button type="button" onclick="switchBuyerTab('kesfet', this)" class="btn-register-glow" style="width:100%; justify-content:center; padding:11px; font-weight:600; display:flex; align-items:center; gap:8px;">
              <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polygon points="16.24 7.76 14.12 14.12 7.76 16.24 9.88 9.88 16.24 7.76"/></svg>
              Müzayede Keşfet (Canlı & Gelecek)
            </button>
            <button type="button" onclick="switchBuyerTab('gecmis', this)" class="btn-outline-gold" style="width:100%; justify-content:center; padding:10px; display:flex; align-items:center; gap:8px; font-weight:600; border-color:rgba(197,160,89,0.5);">
              <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
              📜 Müzayede & Satış Geçmişi Arşivi
            </button>
            <button type="button" onclick="switchBuyerTab('canli', this)" class="btn-gold" style="width:100%; justify-content:center; padding:10px;">
              Canlı Müzayedeye Katıl
            </button>
            <button type="button" onclick="switchBuyerTab('arama', this)" class="btn-outline-gold" style="width:100%; justify-content:center; padding:9px;">
              Eser Arama & Çok Kriterli Filtre
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

    <!-- 4. TAB: BAKİYE, KART YÖNETİMİ VE 3D SECURE ÖDEME MASASI
         SRS: "Sistem, kullanıcının kredi kartı, banka kartı veya tanımlı kayıtlı kartlar
               (3D Secure destekli) ile ödeme yapmasına olanak tanımalıdır." -->
    <div id="buyerTab_bakiye" class="tab-content <?= $activeTab === 'bakiye' ? 'active' : '' ?>">
      <div class="page-breadcrumb">ALICI / ÇALIŞMA ALANI / FİNANS & 3D SECURE ÖDEME MASASI</div>
      
      <div style="display:flex; justify-content:space-between; align-items:flex-start; flex-wrap:wrap; gap:16px; margin-bottom:20px;">
        <div>
          <h1 class="page-heading" style="margin-bottom:4px;">Bakiye, Kart Yönetimi ve 3D Secure Ödeme Masası</h1>
          <p class="page-subheading" style="color:var(--gold-primary); font-weight:500; font-size:0.92rem; margin:0;">
            ⚡ "Sistem, kullanıcının kredi kartı, banka kartı veya tanımlı kayıtlı kartlar (3D Secure destekli) ile ödeme yapmasına olanak tanımalıdır."
          </p>
        </div>
        <div style="display:flex; align-items:center; gap:8px; background:rgba(52,211,153,0.1); border:1px solid rgba(52,211,153,0.3); border-radius:8px; padding:8px 14px;">
          <span style="font-size:1.2rem;">🔒</span>
          <div>
            <div style="font-size:0.72rem; color:#34d399; font-weight:700; text-transform:uppercase;">PCI-DSS v4.0 & 3D SECURE 2.0</div>
            <div style="font-size:0.75rem; color:#e2e8f0;">Banka onaylı şifreli tokenizasyon</div>
          </div>
        </div>
      </div>

      <!-- 4 Finansal KPI Kartı -->
      <div class="kpi-grid" style="grid-template-columns: repeat(4, 1fr); gap:14px; margin-bottom:24px;">
        <div class="kpi-card" style="border-left:4px solid var(--gold-primary); background:linear-gradient(135deg, #131b2a 0%, #0c111c 100%); padding:16px;">
          <div class="kpi-label" style="display:flex; align-items:center; gap:6px;">
            <span>💰</span>
            <span>KULLANILABİLİR BAKİYE</span>
          </div>
          <div class="kpi-value" style="color:#d4af37; font-size:1.5rem; margin:4px 0;">
            <?= $formatPrice($buyerAvailableBalance) ?>
          </div>
          <div style="font-size:0.72rem; color:#94a3b8;">
            Teklife hazır net cüzdan varlığı
          </div>
        </div>

        <div class="kpi-card" style="border-left:4px solid #38bdf8; background:linear-gradient(135deg, #131b2a 0%, #0c111c 100%); padding:16px;">
          <div class="kpi-label" style="display:flex; align-items:center; gap:6px;">
            <span>🛡️</span>
            <span>EMANETTE BLOKE TUTAR</span>
          </div>
          <div class="kpi-value" style="color:#38bdf8; font-size:1.5rem; margin:4px 0;">
            <?= $formatPrice($buyerBlockedBalance) ?>
          </div>
          <div style="font-size:0.72rem; color:#94a3b8;">
            Aktif pey teminatları güvencesi
          </div>
        </div>

        <div class="kpi-card" style="border-left:4px solid #a78bfa; background:linear-gradient(135deg, #131b2a 0%, #0c111c 100%); padding:16px;">
          <div class="kpi-label" style="display:flex; align-items:center; gap:6px;">
            <span>💳</span>
            <span>TANIMLI KAYITLI KARTLAR</span>
          </div>
          <div class="kpi-value" style="color:#a78bfa; font-size:1.5rem; margin:4px 0;">
            <?= count($buyerSavedCards) ?> Aktif Kart
          </div>
          <div style="font-size:0.72rem; color:#94a3b8;">
            Kredi & Banka kartları tanımlı
          </div>
        </div>

        <div class="kpi-card" style="border-left:4px solid #34d399; background:linear-gradient(135deg, #131b2a 0%, #0c111c 100%); padding:16px;">
          <div class="kpi-label" style="display:flex; align-items:center; gap:6px;">
            <span>⚡</span>
            <span>3D SECURE DURUMU</span>
          </div>
          <div class="kpi-value" style="color:#34d399; font-size:1.5rem; margin:4px 0;">
            Aktif & Onaylı
          </div>
          <div style="font-size:0.72rem; color:#94a3b8;">
            SMS OTP doğrulama süresi: ≤3 sn
          </div>
        </div>
      </div>

      <!-- Ana 2 Sütunlu Ödeme ve Kart Masası -->
      <div style="display:grid; grid-template-columns:1.5fr 1fr; gap:24px; align-items:start; margin-bottom:28px;">
        
        <!-- SOL SÜTUN: 3D Secure Kart ile Ödeme / Bakiye Yükleme Formu -->
        <div class="panel-card" style="background:#11151f; border:1px solid #243044; border-radius:12px; padding:24px; margin-bottom:0;">
          <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:16px; border-bottom:1px solid #1e293b; padding-bottom:12px;">
            <div>
              <h3 class="panel-title" style="margin-bottom:2px;">💳 3D Secure ile Ödeme ve Bakiye Yükleme</h3>
              <p style="font-size:0.8rem; color:#94a3b8; margin:0;">Kredi Kartı, Banka Kartı veya Tanımlı Kartınızla güvenli tahsilat</p>
            </div>
            <span class="badge badge-gold" style="font-size:0.7rem; padding:3px 8px;">Komisyonsuz (%0)</span>
          </div>

          <form id="cardPaymentForm" method="POST" onsubmit="return handleCardPaymentSubmit(event);">
            <input type="hidden" name="action" value="process_card_payment">
            <input type="hidden" name="user_id" value="<?= $buyerId ?>">
            <input type="hidden" name="payment_target" value="WALLET">
            <input type="hidden" id="selectedPaymentMethod" name="payment_method" value="SAVED_CARD">
            <input type="hidden" id="finalSmsCode" name="sms_code" value="123456">

            <!-- 1. Adım: Kart Seçim Sekmeleri (Kayıtlı Kartlar vs Yeni Kredi Kartı vs Yeni Banka Kartı) -->
            <div style="margin-bottom:18px;">
              <label class="form-label" style="font-size:0.8rem; color:#f0f3f6; margin-bottom:8px; display:block;">1. Ödeme Aracını Seçiniz</label>
              <div style="display:grid; grid-template-columns:repeat(3, 1fr); gap:8px;">
                <button type="button" id="btnMethodSaved" class="btn-method-tab active" onclick="switchPaymentMethodTab('SAVED_CARD')" style="padding:10px 12px; font-size:0.82rem; font-weight:600; background:#182133; border:1px solid var(--gold-primary); color:#fff; border-radius:8px; cursor:pointer; display:flex; align-items:center; justify-content:center; gap:6px;">
                  <span>💳</span>
                  <span>Tanımlı Kartlarım</span>
                </button>
                <button type="button" id="btnMethodCredit" class="btn-method-tab" onclick="switchPaymentMethodTab('NEW_CREDIT')" style="padding:10px 12px; font-size:0.82rem; font-weight:600; background:#0b0e14; border:1px solid #28354a; color:#94a3b8; border-radius:8px; cursor:pointer; display:flex; align-items:center; justify-content:center; gap:6px;">
                  <span>💳</span>
                  <span>Yeni Kredi Kartı</span>
                </button>
                <button type="button" id="btnMethodDebit" class="btn-method-tab" onclick="switchPaymentMethodTab('NEW_DEBIT')" style="padding:10px 12px; font-size:0.82rem; font-weight:600; background:#0b0e14; border:1px solid #28354a; color:#94a3b8; border-radius:8px; cursor:pointer; display:flex; align-items:center; justify-content:center; gap:6px;">
                  <span>🏦</span>
                  <span>Yeni Banka Kartı</span>
                </button>
              </div>
            </div>

            <!-- [SEÇENEK A] Tanımlı Kayıtlı Kartlar Listesi -->
            <div id="paneSavedCards" style="display:block; margin-bottom:18px;">
              <?php if (empty($buyerSavedCards)): ?>
                <div style="background:#0b0e14; border:1px dashed #28354a; border-radius:8px; padding:16px; text-align:center; color:#94a3b8; font-size:0.82rem;">
                  Henüz kayıtlı bir kartınız bulunmuyor. Lütfen "Yeni Kredi Kartı" veya "Yeni Banka Kartı" sekmesini kullanınız.
                </div>
              <?php else: ?>
                <div style="display:flex; flex-direction:column; gap:8px;">
                  <?php foreach ($buyerSavedCards as $idx => $sc): ?>
                    <label style="display:flex; align-items:center; justify-content:space-between; background:#0e131d; border:1px solid <?= $idx === 0 ? 'var(--gold-primary)' : '#232d3f' ?>; border-radius:8px; padding:12px 16px; cursor:pointer; transition:all 0.2s;" class="saved-card-option">
                      <div style="display:flex; align-items:center; gap:12px;">
                        <input type="radio" name="token_id" value="<?= htmlspecialchars($sc['tokenId']) ?>" <?= $idx === 0 ? 'checked' : '' ?> onchange="highlightSelectedSavedCard(this)" style="accent-color:var(--gold-primary);">
                        <div>
                          <div style="font-weight:600; color:#fff; font-size:0.88rem; display:flex; align-items:center; gap:8px;">
                            <span><?= htmlspecialchars($sc['kartAdi']) ?></span>
                            <span class="badge" style="background:<?= ($sc['kartTipi'] ?? '') === 'BANKA_KARTI' ? '#1e3a8a' : '#451a03' ?>; color:<?= ($sc['kartTipi'] ?? '') === 'BANKA_KARTI' ? '#93c5fd' : '#fde047' ?>; font-size:0.65rem; padding:1px 6px;">
                              <?= htmlspecialchars($sc['kartTipiEtiket'] ?? 'Kredi Kartı') ?>
                            </span>
                          </div>
                          <div style="font-family:monospace; color:#94a3b8; font-size:0.82rem; margin-top:2px;">
                            <?= htmlspecialchars($sc['maskeliKartNo']) ?> • SKT: <?= htmlspecialchars($sc['sonKullanma']) ?>
                          </div>
                        </div>
                      </div>
                      <div style="display:flex; align-items:center; gap:6px;">
                        <span class="badge badge-success" style="font-size:0.65rem; padding:2px 6px;">✓ 3DS Hazır</span>
                      </div>
                    </label>
                  <?php endforeach; ?>
                </div>

                <div style="margin-top:10px; max-width:200px;">
                  <label class="form-label" style="font-size:0.75rem; color:#94a3b8;">Kart Güvenlik Kodu (CVV) *</label>
                  <input type="password" name="saved_card_cvv" maxlength="4" value="482" class="form-control" style="background:#0b0e14; border:1px solid #28354a; color:#fff; font-size:0.85rem; letter-spacing:3px;" required>
                </div>
              <?php endif; ?>
            </div>

            <!-- [SEÇENEK B & C] Yeni Kredi Kartı / Banka Kartı Giriş Paneli -->
            <div id="paneNewCard" style="display:none; margin-bottom:18px;">
              <input type="hidden" id="newCardTypeInput" name="card_type" value="KREDI_KARTI">

              <div style="background:#0b0e14; border:1px solid #28354a; border-radius:10px; padding:18px; margin-bottom:12px;">
                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:12px;">
                  <span id="newCardTypeBadge" class="badge badge-gold" style="font-size:0.7rem;">Kredi Kartı ile Ödeme</span>
                  <div style="display:flex; gap:6px;">
                    <span style="font-size:0.75rem; color:#94a3b8;">Mastercard / Visa / Troy</span>
                  </div>
                </div>

                <div class="form-group" style="margin-bottom:12px;">
                  <label class="form-label" style="font-size:0.78rem; color:#f0f3f6;">Kart Üzerindeki İsim ve Soyisim *</label>
                  <input type="text" name="card_holder" class="form-control" placeholder="Örn: EMRE KARAHAN" style="background:#131824; border:1px solid #2a3449; color:#fff; font-size:0.85rem; text-transform:uppercase;">
                </div>

                <div class="form-group" style="margin-bottom:12px;">
                  <label class="form-label" style="font-size:0.78rem; color:#f0f3f6;">16 Haneli Kart Numarası *</label>
                  <input type="text" name="card_number" id="newCardNumInput" class="form-control" placeholder="5400 0000 0000 0000" maxlength="19" oninput="formatCreditCardNumber(this)" style="background:#131824; border:1px solid #2a3449; color:#fff; font-size:0.9rem; font-family:monospace; letter-spacing:1px;">
                </div>

                <div style="display:grid; grid-template-columns:1fr 1fr 1fr; gap:12px;">
                  <div class="form-group">
                    <label class="form-label" style="font-size:0.78rem; color:#f0f3f6;">Son Kullanma Ay *</label>
                    <select name="expire_month" class="form-control" style="background:#131824; border:1px solid #2a3449; color:#fff; font-size:0.82rem;">
                      <?php for ($m = 1; $m <= 12; $m++): ?>
                        <option value="<?= sprintf('%02d', $m) ?>"><?= sprintf('%02d', $m) ?></option>
                      <?php endfor; ?>
                    </select>
                  </div>

                  <div class="form-group">
                    <label class="form-label" style="font-size:0.78rem; color:#f0f3f6;">Son Kullanma Yıl *</label>
                    <select name="expire_year" class="form-control" style="background:#131824; border:1px solid #2a3449; color:#fff; font-size:0.82rem;">
                      <?php for ($y = 2026; $y <= 2035; $y++): ?>
                        <option value="<?= substr((string)$y, -2) ?>"><?= $y ?></option>
                      <?php endfor; ?>
                    </select>
                  </div>

                  <div class="form-group">
                    <label class="form-label" style="font-size:0.78rem; color:#f0f3f6;">CVV Kodu *</label>
                    <input type="password" name="cvv" placeholder="•••" maxlength="4" class="form-control" style="background:#131824; border:1px solid #2a3449; color:#fff; font-size:0.85rem; letter-spacing:2px;">
                  </div>
                </div>

                <!-- Kartı Kaydet Onay Kutusu -->
                <div style="margin-top:14px; padding-top:12px; border-top:1px solid #1e293b;">
                  <label style="display:flex; align-items:center; gap:8px; font-size:0.8rem; color:#cbd5e1; cursor:pointer;">
                    <input type="checkbox" name="save_card" value="1" checked style="accent-color:var(--gold-primary); width:16px; height:16px;">
                    <span>Bu kartı sonraki ödemelerim için güvenle kaydet (3D Secure PCI-DSS Tokenizasyonu)</span>
                  </label>
                </div>
              </div>
            </div>

            <!-- 2. Adım: Yüklenecek / Ödenecek Tutar Seçimi -->
            <div style="margin-bottom:20px;">
              <label class="form-label" style="font-size:0.8rem; color:#f0f3f6; margin-bottom:8px; display:block;">2. Yüklenecek Tutar (TRY)</label>
              
              <!-- Hızlı Tutar Hapları -->
              <div style="display:flex; gap:8px; flex-wrap:wrap; margin-bottom:10px;">
                <button type="button" class="btn-dark-pill" style="font-size:0.78rem; padding:4px 12px; cursor:pointer;" onclick="setPaymentAmount(10000)">+10.000 ₺</button>
                <button type="button" class="btn-dark-pill" style="font-size:0.78rem; padding:4px 12px; cursor:pointer;" onclick="setPaymentAmount(25000)">+25.000 ₺</button>
                <button type="button" class="btn-dark-pill" style="font-size:0.78rem; padding:4px 12px; cursor:pointer;" onclick="setPaymentAmount(50000)">+50.000 ₺</button>
                <button type="button" class="btn-dark-pill" style="font-size:0.78rem; padding:4px 12px; cursor:pointer;" onclick="setPaymentAmount(100000)">+100.000 ₺</button>
                <button type="button" class="btn-dark-pill" style="font-size:0.78rem; padding:4px 12px; cursor:pointer;" onclick="setPaymentAmount(250000)">+250.000 ₺</button>
              </div>

              <div style="position:relative;">
                <span style="position:absolute; left:14px; top:50%; transform:translateY(-50%); color:#d4af37; font-weight:700; font-size:1.1rem;">₺</span>
                <input type="number" id="paymentAmountInput" name="amount" class="form-control" value="25000" min="100" step="500" required style="padding-left:36px; font-size:1.1rem; font-weight:700; background:#0b0e14; border:1px solid #28354a; color:#fff; border-radius:8px;">
              </div>
            </div>

            <!-- 3. Adım: 3D Secure Doğrulama ve Ödeme Butonu -->
            <button type="submit" class="btn-gold-pill" style="width:100%; padding:14px; font-size:0.95rem; font-weight:700; cursor:pointer; display:flex; align-items:center; justify-content:center; gap:8px; box-shadow:0 4px 15px rgba(212,175,55,0.3);">
              <span>🛡️</span>
              <span id="btnPayText">3D Secure ile Güvenli Bakiye Yükle (₺25.000,00)</span>
            </button>
            <div style="font-size:0.72rem; color:#848d9a; text-align:center; margin-top:8px;">
              İşlem bankanızın 3D Secure SMS doğrulama protokolü üzerinden gerçekleştirilir.
            </div>
          </form>
        </div>

        <!-- SAĞ SÜTUN: Kayıtlı Kartlarım & Kart Cüzdanı -->
        <div>
          <!-- Kart Cüzdanı Kartı -->
          <div class="panel-card" style="background:#11151f; border:1px solid #243044; border-radius:12px; padding:20px; margin-bottom:16px;">
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:14px;">
              <h3 class="panel-title" style="margin:0;">Kayıtlı Kartlarım (<?= count($buyerSavedCards) ?>)</h3>
              <button type="button" onclick="switchPaymentMethodTab('NEW_CREDIT')" class="btn-outline-gold btn-sm" style="font-size:0.72rem; padding:3px 8px;">+ Kart Tanımla</button>
            </div>

            <?php if (empty($buyerSavedCards)): ?>
              <div style="text-align:center; padding:20px; color:#64748b; font-size:0.8rem;">
                Tanımlı kart bulunamadı.
              </div>
            <?php else: ?>
              <div style="display:flex; flex-direction:column; gap:12px;">
                <?php foreach ($buyerSavedCards as $sc): ?>
                  <?php 
                    $isDebit = (($sc['kartTipi'] ?? '') === 'BANKA_KARTI');
                    $cardGrad = $isDebit ? 'linear-gradient(135deg, #0f1e36 0%, #08101e 100%)' : 'linear-gradient(135deg, #2b1f09 0%, #150f05 100%)';
                    $borderColor = $isDebit ? '#1d4ed8' : '#d4af37';
                  ?>
                  <!-- Görsel Kredi / Banka Kartı Tasarımı -->
                  <div style="background:<?= $cardGrad ?>; border:1px solid <?= $borderColor ?>; border-radius:12px; padding:16px; position:relative; overflow:hidden; box-shadow:0 4px 16px rgba(0,0,0,0.4);">
                    <div style="display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:12px;">
                      <div>
                        <div style="font-size:0.72rem; color:<?= $isDebit ? '#93c5fd' : '#fef08a' ?>; font-weight:700; text-transform:uppercase; letter-spacing:0.5px;">
                          <?= htmlspecialchars($sc['banka']) ?>
                        </div>
                        <div style="font-size:0.85rem; color:#fff; font-weight:600; margin-top:2px;">
                          <?= htmlspecialchars($sc['kartAdi']) ?>
                        </div>
                      </div>
                      <span class="badge" style="background:rgba(0,0,0,0.5); color:<?= $borderColor ?>; border:1px solid <?= $borderColor ?>; font-size:0.65rem; padding:2px 6px;">
                        <?= htmlspecialchars($sc['kartTipiEtiket'] ?? 'Kredi Kartı') ?>
                      </span>
                    </div>

                    <!-- Çip İkonu ve Maskeli No -->
                    <div style="display:flex; align-items:center; gap:8px; margin-bottom:12px;">
                      <div style="width:28px; height:20px; background:linear-gradient(135deg, #eab308 0%, #ca8a04 100%); border-radius:4px; border:1px solid #fef08a;"></div>
                      <div style="font-family:monospace; font-size:1rem; letter-spacing:2px; color:#fff; font-weight:700;">
                        <?= htmlspecialchars($sc['maskeliKartNo']) ?>
                      </div>
                    </div>

                    <div style="display:flex; justify-content:space-between; align-items:flex-end;">
                      <div>
                        <div style="font-size:0.65rem; color:#848d9a; text-transform:uppercase;">KART SAHİBİ</div>
                        <div style="font-size:0.78rem; color:#e2e8f0; font-weight:600; text-transform:uppercase;">
                          <?= htmlspecialchars($sc['kartSahibi'] ?? 'EMRE KARAHAN') ?>
                        </div>
                      </div>
                      <div style="text-align:right;">
                        <div style="font-size:0.65rem; color:#848d9a; text-transform:uppercase;">SKT</div>
                        <div style="font-size:0.78rem; color:#e2e8f0; font-weight:600; font-family:monospace;">
                          <?= htmlspecialchars($sc['sonKullanma'] ?? '09/28') ?>
                        </div>
                      </div>
                    </div>

                    <!-- Kart Silme Butonu -->
                    <form method="POST" style="position:absolute; right:12px; bottom:12px; margin:0;" onsubmit="return confirm('Bu kartı kayıtlı kartlarınızdan silmek istediğinize emin misiniz?');">
                      <input type="hidden" name="action" value="delete_card">
                      <input type="hidden" name="user_id" value="<?= $buyerId ?>">
                      <input type="hidden" name="token_id" value="<?= htmlspecialchars($sc['tokenId']) ?>">
                      <button type="submit" style="background:none; border:none; color:#ef4444; font-size:0.72rem; cursor:pointer; padding:2px 6px; text-decoration:underline;">Sil</button>
                    </form>
                  </div>
                <?php endforeach; ?>
              </div>
            <?php endif; ?>
          </div>

          <!-- 3D Secure Güvenlik Protokolü Özeti -->
          <div class="panel-card" style="background:#11151f; border:1px solid #243044; border-radius:12px; padding:18px;">
            <div style="font-size:0.85rem; color:#fff; font-weight:600; margin-bottom:8px; display:flex; align-items:center; gap:6px;">
              <span>🛡️</span>
              <span>3D Secure Güvenlik Standartları</span>
            </div>
            <div style="font-size:0.78rem; color:#94a3b8; line-height:1.55; display:flex; flex-direction:column; gap:8px;">
              <div>
                <strong style="color:#d4af37;">1. SMS OTP Onayı:</strong> Bankanız tarafından cep telefonunuza iletilen tek kullanımlık SMS şifresi ile iki adımlı doğrulama sağlanır.
              </div>
              <div>
                <strong style="color:#34d399;">2. PCI-DSS Tokenizasyonu:</strong> Kart numaranız sunucularımızda ham olarak tutulmaz, BKM lisanslı tokenler ile güvenle saklanır.
              </div>
              <div>
                <strong style="color:#38bdf8;">3. Anında Yansıma:</strong> İşlem onaylandığında bakiye veya sipariş durumu ≤3 saniyede cüzdanınıza yansır.
              </div>
            </div>
          </div>
        </div>

      </div>

      <!-- ALT BÖLÜM: 3D Secure Ödeme Geçmişi ve Makbuzlar Tablosu -->
      <div class="panel-card" style="background:#11151f; border:1px solid #243044; border-radius:12px; padding:22px;">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:14px;">
          <div>
            <h3 class="panel-title" style="margin-bottom:2px;">📜 3D Secure Ödeme Makbuzları ve İşlem Geçmişi</h3>
            <p style="font-size:0.8rem; color:#94a3b8; margin:0;">Kredi kartı, banka kartı ve kayıtlı kartlarla gerçekleştirilen doğrulanmış işlemler</p>
          </div>
          <span class="badge badge-success"><?= count($buyerPaymentReceipts) ?> Onaylı İşlem</span>
        </div>

        <?php if (empty($buyerPaymentReceipts)): ?>
          <div style="text-align:center; padding:30px; color:#64748b; font-size:0.85rem;">
            Henüz 3D Secure ile gerçekleştirilmiş bir ödeme kaydı bulunmuyor.
          </div>
        <?php else: ?>
          <div style="overflow-x:auto;">
            <table class="data-table" style="width:100%; font-size:0.82rem;">
              <thead>
                <tr style="background:#0b0e14; color:#d4af37;">
                  <th style="padding:10px 12px;">Makbuz No</th>
                  <th style="padding:10px 12px;">İşlem Kodu & Onay</th>
                  <th style="padding:10px 12px;">Ödeme Aracı & Banka</th>
                  <th style="padding:10px 12px;">Tarih</th>
                  <th style="padding:10px 12px;">Tutar (TRY)</th>
                  <th style="padding:10px 12px; text-align:center;">3D Secure Durumu</th>
                  <th style="padding:10px 12px; text-align:right;">Makbuz</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($buyerPaymentReceipts as $rc): ?>
                  <tr>
                    <td style="font-family:monospace; color:#d4af37; font-weight:700;">
                      <?= htmlspecialchars($rc['makbuzNo'] ?? 'MAKBUZ-3DS') ?>
                    </td>
                    <td>
                      <div style="font-family:monospace; color:#fff; font-size:0.8rem;"><?= htmlspecialchars($rc['islemKodu'] ?? '') ?></div>
                      <div style="font-size:0.7rem; color:#64748b;">Onay: <?= htmlspecialchars($rc['onayKodu'] ?? '') ?></div>
                    </td>
                    <td>
                      <div style="color:#f0f3f6; font-weight:600;"><?= htmlspecialchars($rc['kartTuru'] ?? 'Kredi Kartı') ?></div>
                      <div style="font-family:monospace; color:#94a3b8; font-size:0.75rem;"><?= htmlspecialchars($rc['maskeliKart'] ?? '') ?></div>
                    </td>
                    <td style="color:#94a3b8;">
                      <?= htmlspecialchars(substr($rc['tarih'] ?? '', 0, 16)) ?>
                    </td>
                    <td style="font-family:var(--font-serif); font-weight:700; color:#34d399; font-size:0.95rem;">
                      ₺<?= number_format((float)($rc['tutar'] ?? 0), 2, ',', '.') ?>
                    </td>
                    <td style="text-align:center;">
                      <span class="badge" style="background:#064e3b; color:#6ee7b7; border:1px solid #059669; font-size:0.68rem; padding:2px 8px;">
                        ✓ 3D Secure Onaylandı
                      </span>
                    </td>
                    <td style="text-align:right;">
                      <button type="button" onclick="alert('Makbuz: <?= htmlspecialchars($rc['makbuzNo'] ?? '') ?>\nTutar: ₺<?= number_format((float)($rc['tutar'] ?? 0), 2, ',', '.') ?>\nBanka: <?= htmlspecialchars($rc['odemeKanali'] ?? '') ?>\n3D Secure SMS Kodu ile onaylanmıştır.');" class="btn-table-action" style="font-size:0.72rem; padding:3px 8px;">
                        Makbuz Görünümü
                      </button>
                    </td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        <?php endif; ?>
      </div>
    </div>

    <!-- Simüle Edilen 3D Secure Banka SMS Onay Modalı -->
    <div id="threeDSecureModal" class="martello-modal" style="display:none; position:fixed; z-index:99999; left:0; top:0; width:100%; height:100%; background:rgba(0,0,0,0.85); backdrop-filter:blur(6px); align-items:center; justify-content:center;">
      <div style="background:#131824; border:1px solid #d4af37; border-radius:12px; max-width:440px; width:92%; padding:24px; box-shadow:0 12px 40px rgba(0,0,0,0.85); position:relative;">
        <button type="button" onclick="close3DSecureModal()" style="position:absolute; right:16px; top:16px; background:none; border:none; color:#888; font-size:1.5rem; cursor:pointer;">&times;</button>
        
        <div style="display:flex; align-items:center; gap:8px; margin-bottom:4px;">
          <span style="font-size:1.3rem;">🛡️</span>
          <div style="color:#d4af37; font-size:0.75rem; font-weight:700; letter-spacing:1px; text-transform:uppercase;">3D SECURE DOĞRULAMA SERVİSİ</div>
        </div>
        <h3 id="modalBankTitle" style="color:#fff; font-size:1.25rem; font-weight:600; margin:0 0 12px 0;">Banka 3D Secure Onayı</h3>
        
        <div style="background:#0b0e14; border:1px solid #1e293b; border-radius:8px; padding:12px; margin-bottom:16px; font-size:0.8rem; color:#cbd5e1;">
          <div style="display:flex; justify-content:space-between; margin-bottom:4px;">
            <span style="color:#848d9a;">İşyeri:</span>
            <strong style="color:#fff;">MARTELLO AUCTION</strong>
          </div>
          <div style="display:flex; justify-content:space-between; margin-bottom:4px;">
            <span style="color:#848d9a;">İşlem Tutarı:</span>
            <strong id="modalAmountDisplay" style="color:#d4af37; font-size:0.95rem;">₺25.000,00</strong>
          </div>
          <div style="display:flex; justify-content:space-between;">
            <span style="color:#848d9a;">Gönderilen Numara:</span>
            <span style="color:#fff; font-family:monospace;">+90 532 *** ** 21</span>
          </div>
        </div>

        <div style="margin-bottom:16px;">
          <label class="form-label" style="font-size:0.8rem; color:#f0f3f6;">SMS Onay Kodu (Tek Kullanımlık Şifre)</label>
          <input type="text" id="modalSmsInput" class="form-control" value="123456" maxlength="6" style="background:#0b0e14; border:1px solid #28354a; color:#34d399; font-size:1.3rem; font-weight:700; text-align:center; letter-spacing:6px; padding:10px;" required>
          <div style="display:flex; justify-content:space-between; font-size:0.72rem; color:#848d9a; margin-top:6px;">
            <span>Kalan Süre: <strong style="color:#38bdf8;">178 sn</strong></span>
            <span style="color:#d4af37;">Demo Kodu: 123456</span>
          </div>
        </div>

        <button type="button" onclick="confirm3DSecurePayment()" class="btn-gold-pill" style="width:100%; padding:12px; font-size:0.9rem; font-weight:700; cursor:pointer; display:flex; align-items:center; justify-content:center; gap:6px;">
          <span>✓</span>
          <span>Doğrula ve Tahsilatı Tamamla</span>
        </button>
      </div>
    </div>

    <!-- 3D Secure Javascript Fonksiyonları -->
    <script>
      function switchPaymentMethodTab(method) {
        document.getElementById('selectedPaymentMethod').value = method;
        document.querySelectorAll('.btn-method-tab').forEach(b => {
          b.classList.remove('active');
          b.style.background = '#0b0e14';
          b.style.borderColor = '#28354a';
          b.style.color = '#94a3b8';
        });

        const pSaved = document.getElementById('paneSavedCards');
        const pNew = document.getElementById('paneNewCard');
        const cardTypeInput = document.getElementById('newCardTypeInput');
        const cardTypeBadge = document.getElementById('newCardTypeBadge');

        if (method === 'SAVED_CARD') {
          const btn = document.getElementById('btnMethodSaved');
          if (btn) {
            btn.classList.add('active');
            btn.style.background = '#182133';
            btn.style.borderColor = 'var(--gold-primary)';
            btn.style.color = '#fff';
          }
          if (pSaved) pSaved.style.display = 'block';
          if (pNew) pNew.style.display = 'none';
        } else if (method === 'NEW_CREDIT') {
          const btn = document.getElementById('btnMethodCredit');
          if (btn) {
            btn.classList.add('active');
            btn.style.background = '#182133';
            btn.style.borderColor = 'var(--gold-primary)';
            btn.style.color = '#fff';
          }
          if (pSaved) pSaved.style.display = 'none';
          if (pNew) pNew.style.display = 'block';
          if (cardTypeInput) cardTypeInput.value = 'KREDI_KARTI';
          if (cardTypeBadge) cardTypeBadge.innerText = 'Yeni Kredi Kartı ile 3D Secure';
        } else if (method === 'NEW_DEBIT') {
          const btn = document.getElementById('btnMethodDebit');
          if (btn) {
            btn.classList.add('active');
            btn.style.background = '#182133';
            btn.style.borderColor = 'var(--gold-primary)';
            btn.style.color = '#fff';
          }
          if (pSaved) pSaved.style.display = 'none';
          if (pNew) pNew.style.display = 'block';
          if (cardTypeInput) cardTypeInput.value = 'BANKA_KARTI';
          if (cardTypeBadge) cardTypeBadge.innerText = 'Yeni Banka Kartı (Debit) ile 3D Secure';
        }
      }

      function highlightSelectedSavedCard(radio) {
        document.querySelectorAll('.saved-card-option').forEach(el => {
          el.style.borderColor = '#232d3f';
        });
        if (radio && radio.closest) {
          const parent = radio.closest('.saved-card-option');
          if (parent) parent.style.borderColor = 'var(--gold-primary)';
        }
      }

      function setPaymentAmount(val) {
        const input = document.getElementById('paymentAmountInput');
        const text = document.getElementById('btnPayText');
        if (input) {
          input.value = val;
        }
        if (text) {
          text.innerText = '3D Secure ile Güvenli Bakiye Yükle (₺' + Number(val).toLocaleString('tr-TR', {minimumFractionDigits: 2}) + ')';
        }
      }

      document.getElementById('paymentAmountInput')?.addEventListener('input', function() {
        const val = this.value || 0;
        const text = document.getElementById('btnPayText');
        if (text) {
          text.innerText = '3D Secure ile Güvenli Bakiye Yükle (₺' + Number(val).toLocaleString('tr-TR', {minimumFractionDigits: 2}) + ')';
        }
      });

      function formatCreditCardNumber(input) {
        let val = input.value.replace(/\D/g, '');
        let formatted = '';
        for (let i = 0; i < val.length; i++) {
          if (i > 0 && i % 4 === 0) formatted += ' ';
          formatted += val[i];
        }
        input.value = formatted;
      }

      function handleCardPaymentSubmit(e) {
        e.preventDefault();
        const amt = document.getElementById('paymentAmountInput')?.value || 25000;
        const modalAmt = document.getElementById('modalAmountDisplay');
        if (modalAmt) {
          modalAmt.innerText = '₺' + Number(amt).toLocaleString('tr-TR', {minimumFractionDigits: 2});
        }
        const modal = document.getElementById('threeDSecureModal');
        if (modal) {
          modal.style.display = 'flex';
        }
        return false;
      }

      function close3DSecureModal() {
        const modal = document.getElementById('threeDSecureModal');
        if (modal) modal.style.display = 'none';
      }

      function confirm3DSecurePayment() {
        const sms = document.getElementById('modalSmsInput')?.value || '123456';
        const finalSms = document.getElementById('finalSmsCode');
        if (finalSms) finalSms.value = sms;
        close3DSecureModal();
        const form = document.getElementById('cardPaymentForm');
        if (form) {
          form.submit();
        }
      }
    </script>


    <!-- 5. TAB: İTİRAZLAR -->
    <div id="buyerTab_itirazlar" class="tab-content <?= $activeTab === 'itirazlar' ? 'active' : '' ?>">
      <div class="page-breadcrumb">ALICI / ÇALIŞMA ALANI</div>
      <h1 class="page-heading">İtiraz ve İhtilaf Talepleri</h1>
      <p class="page-subheading" style="margin-bottom:20px;">Uyuşmazlık bildirimlerinizi ve hakem heyeti kararlarını takip edin</p>

      <!-- SRS Bloke İade Güvencesi -->
      <div style="background:rgba(239,68,68,0.08); border:1px solid rgba(239,68,68,0.25); border-radius:8px; padding:12px 18px; margin-bottom:24px; display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:12px;">
        <div style="display:flex; align-items:center; gap:10px;">
          <span style="font-size:1.4rem;">↩</span>
          <div>
            <strong style="color:#fca5a5; font-size:0.85rem;">SRS Otomatik Teminat & Emanet İade Güvencesi:</strong>
            <span style="color:#94a3b8; font-size:0.8rem; display:block;">
              ⚡ Teklif iptali, müzayedenin geçersiz kalması veya ihtilaf sonucu alıcı lehine karar verilmesi durumunda bloke edilen tutarın kullanıcıya iade edilmesidir.
            </span>
          </div>
        </div>
        <span class="badge" style="background:#450a0a; color:#fca5a5; border:1px solid #7f1d1d; font-size:0.75rem; padding:4px 10px;">%100 İade Koruması</span>
      </div>
      
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

  <!-- VIP Limit Artırım Modalı -->
<div id="vipLimitModal" class="martello-modal" style="display:none; position:fixed; z-index:9999; left:0; top:0; width:100%; height:100%; background:rgba(0,0,0,0.85); backdrop-filter:blur(6px); align-items:center; justify-content:center;">
  <div style="background:#12161f; border:1px solid #c5a059; border-radius:12px; max-width:480px; width:92%; padding:26px; position:relative; box-shadow:0 12px 40px rgba(0,0,0,0.9);">
    <button type="button" onclick="document.getElementById('vipLimitModal').style.display='none'" style="position:absolute; right:16px; top:16px; background:none; border:none; color:#888; font-size:1.5rem; cursor:pointer;">&times;</button>
    <div style="color:#d4af37; font-size:0.75rem; font-weight:700; letter-spacing:1px; text-transform:uppercase; margin-bottom:4px;">MARTELLO PRIVATE CLIENTS</div>
    <h3 style="color:#fff; font-size:1.3rem; margin-bottom:8px;">VIP Teklif Limiti Güncelleme</h3>
    <p style="font-size:0.83rem; color:#94a3b8; line-height:1.5; margin-bottom:16px;">
      Akredite GOLD/PLATINUM statünüz gereği teminat limitiniz konsiyerj onayına gerek kalmaksızın sistem tarafından anında tahsis edilebilir.
    </p>
    <form method="POST">
      <input type="hidden" name="action" value="vip_request_limit">
      <input type="hidden" name="user_id" value="<?= $buyerId ?>">
      <div class="form-group" style="margin-bottom:16px;">
        <label class="form-label">Talep Edilen VIP Teklif Limiti (<?= $currencyStrategy->getCode() ?>)</label>
        <select name="target_limit" class="form-control" style="font-size:1rem; font-weight:600; color:#d4af37;">
          <option value="2000000" <?= $userLimit == 2000000 ? 'selected' : '' ?>><?= $formatPrice(2000000) ?> (Standart VIP Limit)</option>
          <option value="3500000" <?= $userLimit == 3500000 ? 'selected' : '' ?>><?= $formatPrice(3500000) ?> (Platinum Seviye)</option>
          <option value="5000000" <?= $userLimit == 5000000 ? 'selected' : '' ?>><?= $formatPrice(5000000) ?> (Diamond Seviye)</option>
          <option value="10000000" <?= $userLimit == 10000000 ? 'selected' : '' ?>><?= $formatPrice(10000000) ?> (Özel Konsorsiyum Limiti)</option>
        </select>
      </div>
      <button type="submit" class="btn-register-glow" style="width:100%;">
        Limiti Anında Onayla ve Güncelle
      </button>
    </form>
  </div>
</div>

<!-- VIP Telefonla Katılım Rezervasyon Modalı -->
<div id="vipPhoneModal" class="martello-modal" style="display:none; position:fixed; z-index:9999; left:0; top:0; width:100%; height:100%; background:rgba(0,0,0,0.85); backdrop-filter:blur(6px); align-items:center; justify-content:center;">
  <div style="background:#12161f; border:1px solid #c5a059; border-radius:12px; max-width:480px; width:92%; padding:26px; position:relative; box-shadow:0 12px 40px rgba(0,0,0,0.9);">
    <button type="button" onclick="document.getElementById('vipPhoneModal').style.display='none'" style="position:absolute; right:16px; top:16px; background:none; border:none; color:#888; font-size:1.5rem; cursor:pointer;">&times;</button>
    <div style="color:#d4af37; font-size:0.75rem; font-weight:700; letter-spacing:1px; text-transform:uppercase; margin-bottom:4px;">SALON D'HONNEUR • TELEFONLA KATILIM</div>
    <h3 style="color:#fff; font-size:1.3rem; margin-bottom:8px;">Canlı Salonda Telefonla Temsil</h3>
    <p style="font-size:0.83rem; color:#94a3b8; line-height:1.5; margin-bottom:16px;">
      Özel temsilciniz müzayede salonunda masanızda hazır bulunacak ve canlı telefon bağlantısıyla verdiğiniz talimatları salonda adınıza icra edecektir.
    </p>
    <form method="POST">
      <input type="hidden" name="action" value="vip_reserve_phone">
      <input type="hidden" name="user_id" value="<?= $buyerId ?>">
      <input type="hidden" name="lot_id" value="<?= $currentVipLot['id'] ?>">
      
      <div class="form-group" style="margin-bottom:12px;">
        <label class="form-label">Aranacak VIP Telefon Numarası</label>
        <input type="text" name="phone" class="form-control" value="<?= htmlspecialchars($currentUserRecord['telefon'] ?? '+90 532 100 20 30') ?>" required>
      </div>

      <div class="form-group" style="margin-bottom:16px;">
        <label class="form-label">Tercih Edilen Temsil Dili</label>
        <select name="preferred_language" class="form-control">
          <option value="Türkçe" selected>Türkçe (Ana Dil)</option>
          <option value="İngilizce">İngilizce (English)</option>
          <option value="Fransızca">Fransızca (Français)</option>
        </select>
      </div>

      <button type="submit" class="btn-register-glow" style="width:100%;">
        Telefonla Katılımı Kesinleştir
      </button>
    </form>
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

function filterAuctionSection(type, btn) {
  document.querySelectorAll('.auction-filter-btn').forEach(b => {
    b.classList.remove('btn-gold-pill');
    b.classList.add('btn-dark-pill');
  });
  if (btn) {
    btn.classList.remove('btn-dark-pill');
    btn.classList.add('btn-gold-pill');
  }
  const liveSec = document.getElementById('section_live_auctions');
  const upSec = document.getElementById('section_upcoming_auctions');
  if (type === 'live') {
    if (liveSec) liveSec.style.display = 'block';
    if (upSec) upSec.style.display = 'none';
  } else if (type === 'upcoming') {
    if (liveSec) liveSec.style.display = 'none';
    if (upSec) upSec.style.display = 'block';
  } else {
    if (liveSec) liveSec.style.display = 'block';
    if (upSec) upSec.style.display = 'block';
  }
}

function toggleHistoryView(view) {
  const aucView = document.getElementById('history_auctions_view');
  const salesView = document.getElementById('history_sales_view');
  const btnAuc = document.getElementById('btn_history_auctions');
  const btnSales = document.getElementById('btn_history_sales');

  if (view === 'sales') {
    if (aucView) aucView.style.display = 'none';
    if (salesView) salesView.style.display = 'block';
    if (btnAuc) {
      btnAuc.classList.remove('btn-gold-pill');
      btnAuc.classList.add('btn-dark-pill');
    }
    if (btnSales) {
      btnSales.classList.remove('btn-dark-pill');
      btnSales.classList.add('btn-gold-pill');
    }
  } else {
    if (aucView) aucView.style.display = 'block';
    if (salesView) salesView.style.display = 'none';
    if (btnAuc) {
      btnAuc.classList.remove('btn-dark-pill');
      btnAuc.classList.add('btn-gold-pill');
    }
    if (btnSales) {
      btnSales.classList.remove('btn-gold-pill');
      btnSales.classList.add('btn-dark-pill');
    }
  }
}
</script>
