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

$buyerId = (!empty($_SESSION['auth_user']['id']) && strtoupper((string)($_SESSION['auth_user']['rol'] ?? '')) === 'ALICI')
    ? (int)$_SESSION['auth_user']['id']
    : 3; // Varsayılan Alıcı: Zeynep Demir (#3)
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
usort($wonOrders, static fn($a, $b) => ($b['id'] ?? 0) <=> ($a['id'] ?? 0));

$watchlists = $db['watchlists'] ?? [];
$isWatched = false;
$userWatchedLotIds = [];
foreach ($watchlists as $wl) {
    if ((int)($wl['kullaniciId'] ?? 0) === $buyerId) {
        $wlLotId = (int)($wl['lotId'] ?? 0);
        if ($wlLotId > 0) {
            $userWatchedLotIds[$wlLotId] = true;
        }
        if ($wlLotId === (int)$currentLot['id']) {
            $isWatched = true;
        }
    }
}
$userWatchlistLots = [];
foreach ($lots as $lItem) {
    if (!empty($userWatchedLotIds[(int)($lItem['id'] ?? 0)])) {
        $userWatchlistLots[] = $lItem;
    }
}
if (empty($userWatchlistLots) && $currentLot !== null) {
    $userWatchlistLots[] = $currentLot;
}

$buyerActiveBids = array_filter($allBids, static fn($b) => (int)($b['aliciId'] ?? 0) === $buyerId);
$buyerActiveBidsCount = max(1, count($buyerActiveBids));
$buyerTotalSpent = 0.0;
foreach ($wonOrders as $wo) {
    $buyerTotalSpent += (float)($wo['toplamTutar'] ?? $wo['cekicFiyati'] ?? $wo['tutar'] ?? 0);
}
if ($buyerTotalSpent <= 0.0) {
    $buyerTotalSpent = 22700.0;
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
$authUserNow = $_SESSION['auth_user'] ?? null;
$viewerRoleCode = is_array($authUserNow) ? strtoupper((string)($authUserNow['rol'] ?? 'ALICI')) : 'MISAFIR';
$isBuyerOwnView = ($viewerRoleCode === 'ALICI');
$isAdminViewer  = ($viewerRoleCode === 'ADMIN');
$viewerPanelUrl = is_array($authUserNow) ? ('?role=' . urlencode((string)($authUserNow['panel'] ?? 'vitrin'))) : '?role=vitrin';
if (!$isBuyerOwnView && !$isAdminViewer && !in_array($activeTab, ['canli', 'kesfet', 'arama', 'gecmis'], true)) {
    $activeTab = 'canli';
}

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
      <div class="sidebar-section-title"><?= ($isBuyerOwnView || $isAdminViewer) ? 'Çalışma Alanı' : 'Müzayede Salonları' ?></div>
      <ul class="nav-menu">
        <?php if (!$isBuyerOwnView && is_array($authUserNow)): ?>
          <li class="nav-menu-item">
            <a href="<?= htmlspecialchars($viewerPanelUrl) ?>" style="color:#E2C992; font-weight:600; background:rgba(200,170,110,0.08); border:1px solid rgba(200,170,110,0.28);">
              <span class="menu-icon">&larr;</span>
              Kendi Panelime Dön
            </a>
          </li>
        <?php endif; ?>
        <?php if ($isBuyerOwnView || $isAdminViewer): ?>
        <li class="nav-menu-item <?= $activeTab === 'genel' ? 'active' : '' ?>">
          <button type="button" data-tab="genel" onclick="switchBuyerTab('genel', this)">
            <span class="menu-icon"><svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="m3 9 9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg></span>
            Genel bakış
          </button>
        </li>
        <?php endif; ?>
        <li class="nav-menu-item <?= $activeTab === 'kesfet' ? 'active' : '' ?>">
          <button type="button" data-tab="kesfet" onclick="switchBuyerTab('kesfet', this)">
            <span class="menu-icon"><svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polygon points="16.24 7.76 14.12 14.12 7.76 16.24 9.88 9.88 16.24 7.76"/></svg></span>
            Müzayede keşfet
          </button>
        </li>
        <li class="nav-menu-item <?= $activeTab === 'gecmis' ? 'active' : '' ?>">
          <button type="button" data-tab="gecmis" onclick="switchBuyerTab('gecmis', this)">
            <span class="menu-icon"><svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M12 8v4l3 3"/><circle cx="12" cy="12" r="10"/></svg></span>
            Müzayede &amp; Satış Geçmişi
          </button>
        </li>
        <li class="nav-menu-item <?= $activeTab === 'arama' ? 'active' : '' ?>">
          <button type="button" data-tab="arama" onclick="switchBuyerTab('arama', this)">
            <span class="menu-icon"><svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg></span>
            Eser Arama &amp; Filtre
          </button>
        </li>
        <?php if ($isAdminViewer || ($isBuyerOwnView && in_array(strtoupper((string)$userVipLevel), ['GOLD', 'PLATINUM', 'DIAMOND'], true))): ?>
        <li class="nav-menu-item <?= $activeTab === 'vip' ? 'active' : '' ?>">
          <button type="button" data-tab="vip" onclick="switchBuyerTab('vip', this)" style="border-left: 2px solid #C8AA6E; background: linear-gradient(90deg, rgba(200,170,110,0.12) 0%, transparent 100%);">
            <span class="menu-icon" style="color:#C8AA6E;">
              <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="m2 4 3 12h14l3-12-6 7-4-7-4 7-6-7zm3 16h14"/></svg>
            </span>
            <span style="display:flex; align-items:center; justify-content:space-between; width:100%;">
              <span style="font-weight:600; color:#F5F0E6;">VIP Salonu</span>
              <span class="badge badge-gold" style="font-size:0.62rem; padding:1px 6px;">ÖZEL</span>
            </span>
          </button>
        </li>
        <?php endif; ?>
        <li class="nav-menu-item <?= $activeTab === 'canli' ? 'active' : '' ?>">
          <button type="button" data-tab="canli" onclick="switchBuyerTab('canli', this)">
            <span class="menu-icon"><svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg></span>
            Canlı müzayede
          </button>
        </li>
        <?php if ($isBuyerOwnView || $isAdminViewer): ?>
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
        <?php endif; ?>
        <li class="nav-menu-item <?= $activeTab === 'yardim' ? 'active' : '' ?>">
          <a href="?role=yardim" style="color:#C8AA6E; font-weight:500;">
            <span class="menu-icon" style="color:#C8AA6E;"><svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"/><path d="M12 17h.01"/></svg></span>
            Yardım Merkezi
          </a>
        </li>
      </ul>
    </div>

      <!-- 2. BÖLÜM: PAZAR YERİ SEÇENEKLERİ -->
      <div style="margin-top:16px;">
        <div class="sidebar-section-title">Pazar Yeri</div>
        <ul class="nav-menu">
          <li class="nav-menu-item">
            <a href="?role=vitrin">
              <span class="menu-icon"><svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="12" cy="12" r="10"/><polygon points="16.24 7.76 14.12 14.12 7.76 16.24 9.88 9.88 16.24 7.76"/></svg></span>
              Vitrin &amp; Koleksiyonlar
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
            <button type="button" onclick="return openInfoModal('privacy', event)">
              <span class="menu-icon"><svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg></span>
              Gizlilik &amp; KVKK
            </button>
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
    <?php if (!$isBuyerOwnView && is_array($authUserNow)): ?>
      <div style="margin-bottom:16px; padding:10px 16px; background:rgba(200,170,110,0.07); border:1px solid rgba(200,170,110,0.25); border-radius:10px; display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:10px; font-size:0.79rem;">
        <span style="color:#EAE2D5;">
          <strong style="color:#E2C992;">Salon İnceleme Modu</strong> · <?= htmlspecialchars((string)($authUserNow['rolEtiket'] ?? $viewerRoleCode)) ?> hesabıyla açık müzayede salonlarını görüntülüyorsunuz.
        </span>
        <a href="<?= htmlspecialchars($viewerPanelUrl) ?>" class="btn-outline-gold btn-sm">&larr; Kendi Panelime Dön</a>
      </div>
    <?php endif; ?>

    <!-- ====================================================================
         0. TAB: ESER ARAMA & ÇOK KRİTERLİ FİLTRELEME
         ==================================================================== -->
    <div id="buyerTab_arama" class="tab-content <?= $activeTab === 'arama' ? 'active' : '' ?>">
      <div class="page-breadcrumb">ALICI / ESER ARAMA &amp; FİLTRELEME</div>
      
      <div style="display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:20px; flex-wrap:wrap; gap:10px;">
        <div>
          <h1 class="page-heading">Eser Arama ve Katalog Filtreleme</h1>
          <p class="page-subheading" style="margin-bottom:0;">
            Kategori, fiyat aralığı, ekspertiz durumu, marka ve anahtar kelimelere göre açık artırma kataloğunu filtreleyin.
          </p>
        </div>
        <span class="badge badge-gold" style="padding:6px 14px;"><?= count($searchResults) ?> Eser Listeleniyor</span>
      </div>

      <!-- Filtreleme Kontrol Paneli -->
      <div class="panel-card" style="margin-bottom:24px;">
        <form method="GET" action="index.php" style="display:grid; grid-template-columns:2fr 1.3fr 1.6fr 1.2fr 1.2fr auto; gap:12px; align-items:flex-end;">
          <input type="hidden" name="role" value="buyer">
          <input type="hidden" name="tab" value="arama">

          <div>
            <label class="form-label">Anahtar Kelime</label>
            <input type="text" name="q" class="form-control" placeholder="Eser adı, lot no, dönem..." value="<?= htmlspecialchars($buyerFilterParams['q']) ?>">
          </div>

          <div>
            <label class="form-label">Kategori</label>
            <select name="cat" class="form-control">
              <option value="">Tüm Kategoriler</option>
              <?php foreach ($filterOptions['categories'] ?? [] as $cat): ?>
                <?php $catVal = (string)($cat['id'] ?? $cat['ad']); ?>
                <option value="<?= htmlspecialchars((string)($cat['ad'] ?? $catVal)) ?>" <?= ($buyerFilterParams['cat'] === (string)($cat['ad'] ?? '') || $buyerFilterParams['cat'] === (string)($cat['id'] ?? '')) ? 'selected' : '' ?>>
                  <?= htmlspecialchars((string)($cat['ad'] ?? '')) ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>

          <div>
            <label class="form-label">Fiyat Aralığı (<?= $currencyStrategy->getSymbol() ?>)</label>
            <div style="display:grid; grid-template-columns:1fr 1fr; gap:6px;">
              <input type="number" name="min_price" class="form-control" placeholder="Min" value="<?= htmlspecialchars($buyerFilterParams['min_price']) ?>" style="padding:7px 9px; font-size:0.82rem;">
              <input type="number" name="max_price" class="form-control" placeholder="Max" value="<?= htmlspecialchars($buyerFilterParams['max_price']) ?>" style="padding:7px 9px; font-size:0.82rem;">
            </div>
          </div>

          <div>
            <label class="form-label">Marka / Atölye</label>
            <select name="brand" class="form-control">
              <option value="">Tüm Markalar</option>
              <?php foreach ($filterOptions['brands'] ?? [] as $br): ?>
                <option value="<?= htmlspecialchars($br) ?>" <?= ($buyerFilterParams['brand'] === $br) ? 'selected' : '' ?>>
                  <?= htmlspecialchars($br) ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>

          <div>
            <label class="form-label">Ekspertiz Durumu</label>
            <select name="exp_status" class="form-control">
              <option value="">Tümü</option>
              <option value="ONAYLI" <?= ($buyerFilterParams['exp_status'] === 'ONAYLI') ? 'selected' : '' ?>>Onaylı &amp; Damgalı</option>
              <option value="BEKLEMEDE" <?= ($buyerFilterParams['exp_status'] === 'BEKLEMEDE') ? 'selected' : '' ?>>İncelemede / Heyet</option>
            </select>
          </div>

          <div style="display:flex; gap:6px;">
            <button type="submit" class="btn-gold" style="padding:9px 18px; white-space:nowrap;">
              Filtrele
            </button>
            <?php if ($hasActiveBuyerFilters): ?>
              <a href="index.php?role=buyer&tab=arama" class="btn-dark-pill" style="padding:9px 14px; text-decoration:none; display:inline-flex; align-items:center; white-space:nowrap;">
                Temizle
              </a>
            <?php endif; ?>
          </div>
        </form>

        <?php if ($hasActiveBuyerFilters): ?>
          <div style="margin-top:14px; padding-top:12px; border-top:1px solid rgba(200,170,110,0.14); display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:10px;">
            <div style="display:flex; align-items:center; gap:8px; flex-wrap:wrap; font-size:0.8rem;">
              <span style="color:#E2C992; font-weight:600;">Aktif Filtreler:</span>
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
                <span class="badge badge-gold">Ekspertiz: <?= $buyerFilterParams['exp_status'] === 'ONAYLI' ? 'Onaylı' : 'İncelemede' ?></span>
              <?php endif; ?>
            </div>
            <div style="font-size:0.8rem; color:#C8AA6E; font-weight:600;">
              <?= count($searchResults) ?> eşleşen eser bulundu
            </div>
          </div>
        <?php endif; ?>
      </div>

      <?php if (empty($searchResults)): ?>
        <div class="panel-card" style="text-align:center; padding:48px 20px;">
          <h4 style="color:#F5F0E6; font-family:var(--font-serif); font-size:1.25rem; margin-bottom:8px;">Kriterlere Uygun Eser Bulunamadı</h4>
          <p style="color:#A39B8F; font-size:0.85rem; max-width:460px; margin:0 auto 18px auto;">
            Seçtiğiniz filtrelerle eşleşen açık lot bulunmamaktadır. Filtreleri sıfırlayarak tüm kataloğu inceleyebilirsiniz.
          </p>
          <a href="index.php?role=buyer&tab=arama" class="btn-gold">Tüm Kataloğu Göster</a>
        </div>
      <?php else: ?>
        <div style="display:grid; grid-template-columns:repeat(auto-fill, minmax(310px, 1fr)); gap:18px; margin-bottom:28px;">
          <?php foreach ($searchResults as $sLot): ?>
            <div class="panel-card" style="margin-bottom:0; padding:0; overflow:hidden; display:flex; flex-direction:column; justify-content:space-between;">
              <div>
                <?php $sImg = !empty($sLot['gorselUrl']) ? $sLot['gorselUrl'] : 'public/assets/images/lot_swiss_watch_1968.jpg'; ?>
                <div style="height:185px; background:#0A0908; position:relative; overflow:hidden; border-bottom:1px solid rgba(200,170,110,0.14);">
                  <img src="<?= htmlspecialchars($sImg) ?>" alt="<?= htmlspecialchars((string)($sLot['baslik'] ?? '')) ?>" style="width:100%; height:100%; object-fit:cover;">
                  <div style="position:absolute; top:12px; left:12px;">
                    <span class="badge badge-gold"><?= htmlspecialchars((string)($sLot['lotKodu'] ?? '')) ?></span>
                  </div>
                  <div style="position:absolute; top:12px; right:12px; display:flex; gap:6px;">
                    <?php if (!empty($sLot['ekspertizOnayli'])): ?>
                      <span class="badge badge-success">Ekspertiz Onaylı</span>
                    <?php endif; ?>
                  </div>
                </div>

                <div style="padding:16px 18px 0 18px;">
                  <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:6px; font-size:0.73rem;">
                    <span style="color:#C8AA6E; font-weight:600; text-transform:uppercase; letter-spacing:0.6px;">
                      <?= htmlspecialchars((string)($sLot['markaGosterim'] ?? ($sLot['marka'] ?? 'Özel Koleksiyon'))) ?>
                    </span>
                    <span style="color:#A39B8F;">
                      <?= htmlspecialchars((string)($sLot['kategoriAdi'] ?? 'Koleksiyon')) ?>
                    </span>
                  </div>

                  <h4 style="font-family:var(--font-serif); font-size:1.15rem; color:#F5F0E6; margin:0 0 12px 0; line-height:1.35;">
                    <?= htmlspecialchars((string)($sLot['baslik'] ?? '')) ?>
                  </h4>
                </div>
              </div>

              <div style="padding:12px 18px 16px 18px; border-top:1px solid rgba(200,170,110,0.12); display:flex; justify-content:space-between; align-items:center;">
                <div>
                  <div style="font-size:0.68rem; color:#A39B8F; text-transform:uppercase; letter-spacing:0.6px;">Güncel Teklif</div>
                  <div style="font-family:var(--font-serif); font-size:1.25rem; font-weight:700; color:#E2C992;">
                    <?= $formatPrice((float)($sLot['mevcutEnYuksekTeklif'] ?: ($sLot['baslangicFiyati'] ?? 0))) ?>
                  </div>
                </div>
                <div style="display:flex; gap:8px;">
                  <form method="POST" action="index.php?role=buyer&tab=arama" style="display:inline;">
                    <input type="hidden" name="action" value="toggle_watchlist">
                    <input type="hidden" name="user_id" value="3">
                    <input type="hidden" name="lot_id" value="<?= (int)$sLot['id'] ?>">
                    <input type="hidden" name="auction_id" value="<?= (int)($sLot['muzayedeId'] ?? 801) ?>">
                    <button type="submit" class="btn-dark-pill btn-sm">Takip Et</button>
                  </form>
                  <a href="index.php?role=buyer&tab=canli&lot_id=<?= (int)$sLot['id'] ?>" class="btn-gold btn-sm">
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
         MÜZAYEDE KEŞFET TAB: CANLI VE PLANLANAN MÜZAYEDELER
         ==================================================================== -->
    <div id="buyerTab_kesfet" class="tab-content <?= $activeTab === 'kesfet' ? 'active' : '' ?>">
      <div class="page-breadcrumb">ALICI / MÜZAYEDE KEŞFET</div>

      <div style="display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:20px; flex-wrap:wrap; gap:12px;">
        <div>
          <h1 class="page-heading">Müzayede Keşif Salonu</h1>
          <p class="page-subheading" style="margin-bottom:0;">
            Devam eden canlı açık artırma salonları ve takvimlendirilmiş gelecek müzayede oturumları.
          </p>
        </div>
        <div style="display:flex; gap:8px;">
          <span class="badge badge-gold" style="padding:6px 14px;"><?= count($liveAuctions) ?> Canlı Salon</span>
          <span class="badge badge-info" style="padding:6px 14px;"><?= count($upcomingAuctions) ?> Planlanan Oturum</span>
        </div>
      </div>

      <!-- Oturum Filtre Düğmeleri -->
      <div style="display:flex; gap:8px; margin-bottom:24px; flex-wrap:wrap; border-bottom:1px solid rgba(200,170,110,0.14); padding-bottom:14px;">
        <button type="button" onclick="filterAuctionSection('all', this)" class="btn-gold-pill auction-filter-btn">
          Tüm Oturumlar (<?= count($liveAuctions) + count($upcomingAuctions) ?>)
        </button>
        <button type="button" onclick="filterAuctionSection('live', this)" class="btn-dark-pill auction-filter-btn">
          Canlı Salonlar (<?= count($liveAuctions) ?>)
        </button>
        <button type="button" onclick="filterAuctionSection('upcoming', this)" class="btn-dark-pill auction-filter-btn">
          Gelecek Oturumlar (<?= count($upcomingAuctions) ?>)
        </button>
        <button type="button" onclick="switchBuyerTab('gecmis', this)" class="btn-dark-pill auction-filter-btn">
          Müzayede &amp; Satış Geçmişi &rarr;
        </button>
      </div>

      <!-- 1. BÖLÜM: CANLI MÜZAYEDELER -->
      <div id="section_live_auctions" style="margin-bottom:32px;">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:14px;">
          <h2 style="font-family:var(--font-serif); font-size:1.35rem; color:#F5F0E6; margin:0;">
            Canlı Müzayede Salonları
          </h2>
          <span class="badge badge-gold">Anlık Pey Kabulü Aktif</span>
        </div>

        <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(340px, 1fr)); gap:18px;">
          <?php foreach ($liveAuctions as $lAuc): ?>
            <div class="panel-card" style="margin-bottom:0; padding:0; overflow:hidden; display:flex; flex-direction:column; justify-content:space-between;">
              <div>
                <?php $aucImg = !empty($lAuc['gorselUrl']) ? $lAuc['gorselUrl'] : 'public/assets/images/lot_swiss_watch_1968.jpg'; ?>
                <div style="height:165px; position:relative; overflow:hidden; background:#0A0908; border-bottom:1px solid rgba(200,170,110,0.14);">
                  <img src="<?= htmlspecialchars($aucImg) ?>" alt="<?= htmlspecialchars((string)($lAuc['baslik'] ?? '')) ?>" style="width:100%; height:100%; object-fit:cover; opacity:0.88;">
                  <div style="position:absolute; top:12px; left:12px;">
                    <span class="badge badge-gold">CANLI SALON</span>
                  </div>
                  <div style="position:absolute; top:12px; right:12px;">
                    <span class="badge badge-info"><?= htmlspecialchars((string)($lAuc['tur'] ?? 'CANLI')) ?></span>
                  </div>
                  <div style="position:absolute; bottom:10px; left:12px; background:rgba(13,11,10,0.84); padding:3px 10px; border-radius:6px; font-size:0.72rem; color:#E2C992; border:1px solid rgba(200,170,110,0.22);">
                    <?= htmlspecialchars((string)($lAuc['kategori'] ?? 'Sanat & Horoloji')) ?>
                  </div>
                </div>

                <div style="padding:18px 20px 0 20px;">
                  <h3 style="font-family:var(--font-serif); font-size:1.22rem; color:#F5F0E6; margin:0 0 8px 0; line-height:1.3;">
                    <?= htmlspecialchars((string)($lAuc['baslik'] ?? '')) ?>
                  </h3>
                  <p style="font-size:0.82rem; color:#A39B8F; line-height:1.55; margin:0 0 14px 0;">
                    <?= htmlspecialchars((string)($lAuc['aciklama'] ?? 'Canlı salon açık artırması devam etmektedir.')) ?>
                  </p>

                  <div style="display:grid; grid-template-columns:1fr 1fr; gap:10px; padding:10px 14px; background:#0D0B0A; border:1px solid rgba(200,170,110,0.14); border-radius:8px; margin-bottom:16px;">
                    <div>
                      <div style="color:#A39B8F; font-size:0.68rem; text-transform:uppercase; letter-spacing:0.6px;">Katılımcı</div>
                      <div style="color:#F5F0E6; font-weight:600; font-size:0.84rem; margin-top:2px;"><?= number_format((int)($lAuc['katilimciSayisi'] ?? 420)) ?> Koleksiyoner</div>
                    </div>
                    <div>
                      <div style="color:#A39B8F; font-size:0.68rem; text-transform:uppercase; letter-spacing:0.6px;">Açık Lot</div>
                      <div style="color:#E2C992; font-weight:600; font-size:0.84rem; margin-top:2px;"><?= (int)($lAuc['lotSayisi'] ?? 12) ?> Eser</div>
                    </div>
                  </div>
                </div>
              </div>

              <div style="padding:12px 20px 16px 20px; border-top:1px solid rgba(200,170,110,0.12); display:flex; justify-content:space-between; align-items:center;">
                <div>
                  <div style="font-size:0.68rem; color:#A39B8F; text-transform:uppercase; letter-spacing:0.6px;">Tahmini Hacim</div>
                  <div style="font-family:var(--font-serif); font-size:1.2rem; font-weight:700; color:#E2C992;">
                    <?= $formatPrice((float)($lAuc['tahminiHacim'] ?? 2450000)) ?>
                  </div>
                </div>
                <div>
                  <?php if ((int)($lAuc['id'] ?? 0) === 802): ?>
                    <button type="button" onclick="switchBuyerTab('vip', this)" class="btn-gold btn-sm">
                      VIP Salona Katıl &rarr;
                    </button>
                  <?php else: ?>
                    <button type="button" onclick="switchBuyerTab('canli', this)" class="btn-gold btn-sm">
                      Salona Katıl &rarr;
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
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:14px;">
          <h2 style="font-family:var(--font-serif); font-size:1.35rem; color:#F5F0E6; margin:0;">
            Planlanan Gelecek Müzayedeler
          </h2>
          <span class="badge badge-info">Katalog Ön İncelemede</span>
        </div>

        <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(340px, 1fr)); gap:18px;">
          <?php foreach ($upcomingAuctions as $uAuc): ?>
            <div class="panel-card" style="margin-bottom:0; padding:0; overflow:hidden; display:flex; flex-direction:column; justify-content:space-between;">
              <div>
                <?php $uImg = !empty($uAuc['gorselUrl']) ? $uAuc['gorselUrl'] : 'public/assets/images/lot_ottoman_carpet.jpg'; ?>
                <div style="height:155px; position:relative; overflow:hidden; background:#0A0908; border-bottom:1px solid rgba(200,170,110,0.14);">
                  <img src="<?= htmlspecialchars($uImg) ?>" alt="<?= htmlspecialchars((string)($uAuc['baslik'] ?? '')) ?>" style="width:100%; height:100%; object-fit:cover; opacity:0.84;">
                  <div style="position:absolute; top:12px; left:12px;">
                    <span class="badge badge-info">PLANLANDI</span>
                  </div>
                  <div style="position:absolute; top:12px; right:12px; background:rgba(13,11,10,0.84); padding:3px 10px; border-radius:6px; font-size:0.72rem; color:#E2C992; border:1px solid rgba(200,170,110,0.22);">
                    <?= !empty($uAuc['baslangicZamani']) ? date('d.m.Y · H:i', strtotime((string)$uAuc['baslangicZamani'])) : 'Yakında' ?>
                  </div>
                  <div style="position:absolute; bottom:10px; left:12px; background:rgba(13,11,10,0.84); padding:3px 10px; border-radius:6px; font-size:0.72rem; color:#EAE2D5;">
                    <?= htmlspecialchars((string)($uAuc['kategori'] ?? 'Koleksiyon')) ?>
                  </div>
                </div>

                <div style="padding:18px 20px 0 20px;">
                  <h3 style="font-family:var(--font-serif); font-size:1.18rem; color:#F5F0E6; margin:0 0 8px 0; line-height:1.3;">
                    <?= htmlspecialchars((string)($uAuc['baslik'] ?? '')) ?>
                  </h3>
                  <p style="font-size:0.81rem; color:#A39B8F; line-height:1.55; margin:0 0 14px 0;">
                    <?= htmlspecialchars((string)($uAuc['aciklama'] ?? 'Planlanan müzayede oturumu.')) ?>
                  </p>

                  <div style="display:grid; grid-template-columns:1fr 1fr; gap:10px; padding:10px 14px; background:#0D0B0A; border:1px solid rgba(200,170,110,0.14); border-radius:8px; margin-bottom:16px;">
                    <div>
                      <div style="color:#A39B8F; font-size:0.68rem; text-transform:uppercase; letter-spacing:0.6px;">Ön Kayıt</div>
                      <div style="color:#F5F0E6; font-weight:600; font-size:0.84rem; margin-top:2px;"><?= number_format((int)($uAuc['katilimciSayisi'] ?? 250)) ?> Koleksiyoner</div>
                    </div>
                    <div>
                      <div style="color:#A39B8F; font-size:0.68rem; text-transform:uppercase; letter-spacing:0.6px;">Katalog</div>
                      <div style="color:#E2C992; font-weight:600; font-size:0.84rem; margin-top:2px;"><?= (int)($uAuc['lotSayisi'] ?? 30) ?> Eser</div>
                    </div>
                  </div>
                </div>
              </div>

              <div style="padding:12px 20px 16px 20px; border-top:1px solid rgba(200,170,110,0.12); display:flex; justify-content:space-between; align-items:center;">
                <div>
                  <div style="font-size:0.68rem; color:#A39B8F; text-transform:uppercase; letter-spacing:0.6px;">Tahmini Değer</div>
                  <div style="font-family:var(--font-serif); font-size:1.15rem; font-weight:700; color:#E2C992;">
                    <?= $formatPrice((float)($uAuc['tahminiHacim'] ?? 3500000)) ?>
                  </div>
                </div>
                <div style="display:flex; gap:8px;">
                  <button type="button" onclick="alert('<?= addslashes((string)$uAuc['baslik']) ?> oturumu için takvim hatırlatmanız oluşturuldu.');" class="btn-dark-pill btn-sm">
                    Hatırlatıcı
                  </button>
                  <button type="button" onclick="switchBuyerTab('arama', this)" class="btn-outline-gold btn-sm">
                    Ön Katalog &rarr;
                  </button>
                </div>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      </div>
    </div>

    <!-- ====================================================================
         MÜZAYEDE VE SATIŞ GEÇMİŞİ ARŞİVİ
         ==================================================================== -->
    <?php
      $showSalesSubView = ($pastSalesQuery !== '' || $pastSalesCat !== '' || ($_GET['sub'] ?? '') === 'sales');
    ?>
    <div id="buyerTab_gecmis" class="tab-content <?= $activeTab === 'gecmis' ? 'active' : '' ?>">
      <div class="page-breadcrumb">ARŞİV / MÜZAYEDE VE SATIŞ GEÇMİŞİ</div>
      <div style="display:flex; justify-content:space-between; align-items:flex-start; flex-wrap:wrap; gap:12px; margin-bottom:20px;">
        <div>
          <h1 class="page-heading">Müzayede ve Satış Geçmişi</h1>
          <p class="page-subheading" style="margin-bottom:0;">
            Tamamlanan açık artırma oturumları, nihai çekiç fiyatları ve tescilli satış arşivi.
          </p>
        </div>
        <span class="badge badge-gold" style="padding:6px 14px;">Noter Tasdikli Arşiv</span>
      </div>

      <!-- 4 Temel KPI Kartı (Diğer Sayfalarla Birebir Uyumlu) -->
      <div class="kpi-grid" style="grid-template-columns:repeat(4, 1fr);">
        <div class="kpi-card highlight" style="cursor:pointer;" onclick="toggleHistoryView('auctions')">
          <div class="kpi-label">Tamamlanan Oturum</div>
          <div class="kpi-value"><?= (int)($historyStats['totalAuctions'] ?? count($pastAuctions)) ?></div>
        </div>
        <div class="kpi-card" style="cursor:pointer;" onclick="toggleHistoryView('sales')">
          <div class="kpi-label">Satılan Eser</div>
          <div class="kpi-value"><?= (int)($historyStats['totalItemsSold'] ?? count($pastSales)) ?> <span style="font-size:0.88rem; color:#C8AA6E; font-weight:500;">Lot</span></div>
        </div>
        <div class="kpi-card">
          <div class="kpi-label">Toplam Satış Hacmi</div>
          <div class="kpi-value" style="color:#E2C992; font-size:1.45rem;"><?= $formatPrice((float)($historyStats['totalVolume'] ?? 60000000.0)) ?></div>
        </div>
        <div class="kpi-card">
          <div class="kpi-label">Satış Başarı Oranı</div>
          <div class="kpi-value">%<?= htmlspecialchars(str_replace('%', '', (string)($historyStats['avgSuccessRate'] ?? '94.2'))) ?></div>
        </div>
      </div>

      <!-- Alt Görünüm Seçim Sekmeleri (Tamamlanan Müzayedeler vs Satılan Eserler) -->
      <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:12px; margin-bottom:20px; border-bottom:1px solid rgba(200,170,110,0.14); padding-bottom:14px;">
        <div style="display:flex; gap:8px;">
          <button type="button" id="btn_history_auctions" onclick="toggleHistoryView('auctions')" class="<?= $showSalesSubView ? 'btn-dark-pill' : 'btn-gold-pill' ?>" style="padding:8px 18px; font-size:0.82rem; font-weight:600;">
            Tamamlanan Müzayedeler (<?= count($pastAuctions) ?>)
          </button>
          <button type="button" id="btn_history_sales" onclick="toggleHistoryView('sales')" class="<?= $showSalesSubView ? 'btn-gold-pill' : 'btn-dark-pill' ?>" style="padding:8px 18px; font-size:0.82rem; font-weight:600;">
            Satılan Eserler &amp; Çekiç Fiyatları (<?= count($pastSales) ?>)
          </button>
        </div>
        <div style="font-size:0.78rem; color:#A39B8F;">
          Tüm kapanış fiyatları ekspertiz ve satış cetveli ile tescillidir
        </div>
      </div>

      <!-- 1. ALT GÖRÜNÜM: TAMAMLANAN MÜZAYEDELER -->
      <div id="history_auctions_view" style="display:<?= $showSalesSubView ? 'none' : 'block' ?>;">
        <div style="display:grid; grid-template-columns:repeat(auto-fill, minmax(340px, 1fr)); gap:18px;">
          <?php foreach ($pastAuctions as $pAuc): ?>
            <?php $pImg = !empty($pAuc['gorselUrl']) ? (string)$pAuc['gorselUrl'] : 'public/assets/images/lot_ottoman_calligraphy.jpg'; ?>
            <div class="panel-card" style="margin-bottom:0; padding:0; overflow:hidden; display:flex; flex-direction:column; justify-content:space-between;">
              <div>
                <!-- Kapak Görseli & Üst Rozetler -->
                <div style="height:155px; position:relative; overflow:hidden; background:#0A0908; border-bottom:1px solid rgba(200,170,110,0.14);">
                  <img src="<?= htmlspecialchars($pImg) ?>" alt="<?= htmlspecialchars((string)($pAuc['baslik'] ?? '')) ?>" style="width:100%; height:100%; object-fit:cover; opacity:0.84;">
                  <div style="position:absolute; top:12px; left:12px;">
                    <span class="badge badge-gold"><?= htmlspecialchars((string)($pAuc['kod'] ?? ('AUC-' . $pAuc['id']))) ?></span>
                  </div>
                  <div style="position:absolute; top:12px; right:12px;">
                    <span class="badge badge-success">TAMAMLANDI</span>
                  </div>
                </div>

                <!-- İçerik -->
                <div style="padding:18px 20px 0 20px;">
                  <h3 style="font-family:var(--font-serif); font-size:1.2rem; color:#F5F0E6; margin:0 0 8px 0; line-height:1.3;">
                    <?= htmlspecialchars((string)($pAuc['baslik'] ?? 'Müzayede Oturumu')) ?>
                  </h3>
                  <p style="font-size:0.81rem; color:#A39B8F; line-height:1.55; margin:0 0 14px 0;">
                    <?= htmlspecialchars((string)($pAuc['aciklama'] ?? 'Özel koleksiyon satışı başarıyla tamamlandı.')) ?>
                  </p>

                  <!-- 2x2 Hizalı Metrik Kutusu -->
                  <div style="background:#0D0B0A; border:1px solid rgba(200,170,110,0.14); border-radius:8px; padding:12px 14px; display:grid; grid-template-columns:1fr 1fr; gap:10px 14px; margin-bottom:14px;">
                    <div>
                      <div style="font-size:0.68rem; color:#A39B8F; text-transform:uppercase; letter-spacing:0.6px;">Kapanış Tarihi</div>
                      <div style="font-size:0.83rem; color:#F5F0E6; font-weight:600; margin-top:2px;">
                        <?= htmlspecialchars((string)($pAuc['tamamlanmaTarihi'] ?? ($pAuc['bitisZamani'] ?? '15.10.2026'))) ?>
                      </div>
                    </div>
                    <div>
                      <div style="font-size:0.68rem; color:#A39B8F; text-transform:uppercase; letter-spacing:0.6px;">Katılımcı</div>
                      <div style="font-size:0.83rem; color:#F5F0E6; font-weight:600; margin-top:2px;">
                        <?= htmlspecialchars((string)($pAuc['katilimciSayisi'] ?? 240)) ?> Koleksiyoner
                      </div>
                    </div>
                    <div>
                      <div style="font-size:0.68rem; color:#A39B8F; text-transform:uppercase; letter-spacing:0.6px;">Satılan / Toplam</div>
                      <div style="font-size:0.83rem; color:#E2C992; font-weight:600; margin-top:2px;">
                        <?= htmlspecialchars((string)($pAuc['satilanLotSayisi'] ?? 45)) ?> / <?= htmlspecialchars((string)($pAuc['toplamLotSayisi'] ?? 48)) ?> Lot
                      </div>
                    </div>
                    <div>
                      <div style="font-size:0.68rem; color:#A39B8F; text-transform:uppercase; letter-spacing:0.6px;">Başarı Oranı</div>
                      <div style="font-size:0.83rem; color:#E2C992; font-weight:600; margin-top:2px;">
                        %<?= htmlspecialchars((string)($pAuc['basariOrani'] ?? 94)) ?>
                      </div>
                    </div>
                  </div>

                  <!-- Rekor Eser & Oturum Hacmi -->
                  <div style="padding-top:10px; border-top:1px solid rgba(200,170,110,0.1); margin-bottom:14px;">
                    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:6px; font-size:0.78rem;">
                      <span style="color:#A39B8F;">Oturum Rekor Eseri</span>
                      <span style="color:#F5F0E6; font-weight:500; text-align:right;"><?= htmlspecialchars((string)($pAuc['rekorLot'] ?? 'Özel Eser')) ?></span>
                    </div>
                    <div style="display:flex; justify-content:space-between; align-items:baseline;">
                      <span style="font-size:0.76rem; color:#A39B8F; text-transform:uppercase; letter-spacing:0.5px;">Oturum Satış Hacmi</span>
                      <span style="font-family:var(--font-serif); font-size:1.2rem; color:#E2C992; font-weight:700;">
                        <?= $formatPrice((float)($pAuc['toplamSatisTutari'] ?? 15000000)) ?>
                      </span>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Alt Aksiyon Satırı -->
              <div style="padding:12px 20px 16px 20px; border-top:1px solid rgba(200,170,110,0.12); display:flex; gap:8px;">
                <button type="button" onclick="alert('<?= addslashes((string)$pAuc['baslik']) ?> oturumunun resmi sonuç kataloğu ve noter tasdikli satış cetveli doğrulandı.');" class="btn-outline-gold btn-sm" style="flex:1; justify-content:center;">
                  Noter Cetveli &rarr;
                </button>
                <button type="button" onclick="toggleHistoryView('sales');" class="btn-dark-pill btn-sm">
                  Satılan Eserler
                </button>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      </div>

      <!-- 2. ALT GÖRÜNÜM: SATILAN ESERLER & ÇEKİÇ FİYATLARI -->
      <div id="history_sales_view" style="display:<?= $showSalesSubView ? 'block' : 'none' ?>;">
        <!-- Filtre Çubuğu -->
        <div class="panel-card" style="padding:16px 20px; margin-bottom:20px;">
          <form method="GET" action="index.php" style="display:grid; grid-template-columns:1fr 220px auto auto; gap:12px; align-items:center;">
            <input type="hidden" name="role" value="buyer">
            <input type="hidden" name="tab" value="gecmis">
            <input type="hidden" name="sub" value="sales">

            <div>
              <input type="text" name="sales_q" value="<?= htmlspecialchars((string)$pastSalesQuery) ?>" placeholder="Satılan eser adı, marka veya lot kodu ara (Örn: Rolex, Hilye-i Şerif, Leica)..." class="form-control">
            </div>

            <div>
              <select name="sales_cat" class="form-control">
                <option value="">Tüm Kategoriler</option>
                <option value="Horoloji" <?= $pastSalesCat === 'Horoloji' ? 'selected' : '' ?>>Horoloji &amp; Yüksek Saatçilik</option>
                <option value="Antika" <?= $pastSalesCat === 'Antika' ? 'selected' : '' ?>>Antika &amp; Görsel Sanatlar</option>
                <option value="Osmanlı" <?= $pastSalesCat === 'Osmanlı' ? 'selected' : '' ?>>Osmanlı Dönemi Koleksiyonları</option>
                <option value="Efemera" <?= $pastSalesCat === 'Efemera' ? 'selected' : '' ?>>Efemera &amp; Nadir Kitaplar</option>
              </select>
            </div>

            <div>
              <button type="submit" class="btn-gold" style="padding:9px 20px;">
                Filtrele
              </button>
            </div>

            <?php if (!empty($pastSalesQuery) || !empty($pastSalesCat)): ?>
              <div>
                <a href="index.php?role=buyer&tab=gecmis&sub=sales" class="btn-dark-pill" style="padding:9px 14px; text-decoration:none; display:inline-block;">
                  Temizle
                </a>
              </div>
            <?php endif; ?>
          </form>
        </div>

        <!-- Satılan Eserler Grid -->
        <?php if (empty($pastSales)): ?>
          <div class="panel-card" style="text-align:center; padding:44px 20px;">
            <h4 style="color:#F5F0E6; font-family:var(--font-serif); font-size:1.22rem; margin-bottom:8px;">Aradığınız Kriterlere Uygun Satılmış Eser Bulunamadı</h4>
            <p style="color:#A39B8F; font-size:0.84rem; margin-bottom:16px;">Arama filtrelerini temizleyerek tüm geçmiş satışları listeleyebilirsiniz.</p>
            <a href="index.php?role=buyer&tab=gecmis&sub=sales" class="btn-outline-gold btn-sm">Tüm Arşivi Göster</a>
          </div>
        <?php else: ?>
          <div style="display:grid; grid-template-columns:repeat(auto-fill, minmax(310px, 1fr)); gap:18px;">
            <?php foreach ($pastSales as $sale): 
              $sTitle = (string)($sale['urunBaslik'] ?? ($sale['baslik'] ?? 'Eser'));
              $sImg = (string)($sale['gorselUrl'] ?? ($sale['gorsel'] ?? 'public/assets/images/lot_swiss_watch_1968.jpg'));
              $sCat = (string)($sale['kategoriAdi'] ?? ($sale['kategori'] ?? 'Koleksiyon'));
              $sAuc = (string)($sale['muzayedeBaslik'] ?? ($sale['muzayedeAdi'] ?? 'Geçmiş Müzayede'));
              $sDate = (string)($sale['satisTarihi'] ?? ($sale['kapanisTarihi'] ?? ''));
              $sHammer = is_numeric($sale['cekiçFiyati']) ? $formatPrice((float)$sale['cekiçFiyati']) : (string)$sale['cekiçFiyati'];
              $sOpen = is_numeric($sale['acilisFiyati']) ? $formatPrice((float)$sale['acilisFiyati']) : (string)$sale['acilisFiyati'];
            ?>
              <div class="panel-card" style="margin-bottom:0; padding:0; overflow:hidden; display:flex; flex-direction:column; justify-content:space-between;">
                <!-- Üst: Görsel ve Rozetler -->
                <div style="position:relative; height:190px; background:#0A0908; overflow:hidden; border-bottom:1px solid rgba(200,170,110,0.14);">
                  <img src="<?= htmlspecialchars($sImg) ?>" alt="<?= htmlspecialchars($sTitle) ?>" style="width:100%; height:100%; object-fit:cover;">
                  <div style="position:absolute; top:12px; left:12px;">
                    <span class="badge badge-gold">LOT <?= htmlspecialchars((string)($sale['lotKodu'] ?? 'AUC-00')) ?></span>
                  </div>
                  <div style="position:absolute; top:12px; right:12px;">
                    <span class="badge badge-success">SATILDI</span>
                  </div>
                  <div style="position:absolute; bottom:10px; left:12px; background:rgba(13,11,10,0.85); color:#EAE2D5; font-size:0.71rem; padding:3px 9px; border-radius:6px; border:1px solid rgba(200,170,110,0.2);">
                    <?= htmlspecialchars($sCat) ?>
                  </div>
                </div>

                <!-- Gövde: Fiyat ve Eser Bilgisi -->
                <div style="padding:16px 18px; flex:1; display:flex; flex-direction:column; justify-content:space-between;">
                  <div>
                    <h3 style="font-size:1.12rem; color:#F5F0E6; font-family:var(--font-serif); margin:0 0 12px 0; line-height:1.35;">
                      <?= htmlspecialchars($sTitle) ?>
                    </h3>

                    <!-- Çekiç Fiyatı ve Değer Artışı Kutusu -->
                    <div style="background:#0D0B0A; border:1px solid rgba(200,170,110,0.15); border-radius:8px; padding:12px; margin-bottom:12px;">
                      <div style="display:flex; justify-content:space-between; align-items:baseline;">
                        <span style="font-size:0.68rem; color:#A39B8F; text-transform:uppercase; letter-spacing:0.6px;">Nihai Çekiç Fiyatı</span>
                        <span style="font-size:1.25rem; font-weight:700; color:#E2C992; font-family:var(--font-serif);">
                          <?= htmlspecialchars($sHammer) ?>
                        </span>
                      </div>
                      <div style="display:flex; justify-content:space-between; align-items:center; margin-top:8px; padding-top:8px; border-top:1px solid rgba(200,170,110,0.1); font-size:0.75rem;">
                        <span style="color:#A39B8F;">Açılış: <?= htmlspecialchars($sOpen) ?></span>
                        <span style="color:#E2C992; font-weight:600; background:rgba(200,170,110,0.1); border:1px solid rgba(200,170,110,0.25); padding:2px 8px; border-radius:99px; font-size:0.7rem;">
                          <?= htmlspecialchars((string)($sale['artisOrani'] ?? '')) ?>
                        </span>
                      </div>
                    </div>

                    <!-- Müzayede ve Kazanan Alıcı Bilgisi (Hizalı Satırlar) -->
                    <div style="display:flex; flex-direction:column; gap:5px; font-size:0.77rem; margin-bottom:14px;">
                      <div style="display:flex; justify-content:space-between; gap:8px;">
                        <span style="color:#A39B8F;">Müzayede</span>
                        <span style="color:#EAE2D5; text-align:right; font-weight:500;"><?= htmlspecialchars($sAuc) ?></span>
                      </div>
                      <div style="display:flex; justify-content:space-between; gap:8px;">
                        <span style="color:#A39B8F;">Kapanış Tarihi</span>
                        <span style="color:#EAE2D5; text-align:right;"><?= htmlspecialchars($sDate) ?></span>
                      </div>
                      <div style="display:flex; justify-content:space-between; gap:8px;">
                        <span style="color:#A39B8F;">Kazanan Alıcı</span>
                        <span style="color:#E2C992; font-weight:600; text-align:right;"><?= htmlspecialchars((string)($sale['kazananAliciAnonim'] ?? 'Koleksiyoner')) ?></span>
                      </div>
                    </div>
                  </div>

                  <!-- Sertifika ve Aksiyon -->
                  <div style="border-top:1px solid rgba(200,170,110,0.12); padding-top:12px; display:flex; justify-content:space-between; align-items:center;">
                    <span style="font-size:0.73rem; color:#C8AA6E; display:inline-flex; align-items:center; gap:5px; font-weight:500;">
                      <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><polyline points="20 6 9 17 4 12"/></svg>
                      Ekspertiz Tescilli
                    </span>
                    <button type="button" onclick="alert('<?= addslashes($sTitle) ?>\n\nNihai Çekiç Fiyatı: <?= addslashes($sHammer) ?>\nAçılış Fiyatı: <?= addslashes($sOpen) ?> (<?= addslashes((string)($sale['artisOrani'] ?? '')) ?>)\nMüzayede: <?= addslashes($sAuc) ?>\nKazanan: <?= addslashes((string)($sale['kazananAliciAnonim'] ?? '')) ?>\nEkspertiz Durumu: <?= addslashes((string)($sale['ekspertizNotu'] ?? 'Lisanslı Eksper Damgalı & Onaylı')) ?>');" class="btn-outline-gold btn-sm">
                      Satış Cetveli &rarr;
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
          <?php $currentLotImg = !empty($currentLot['gorselUrl']) ? (string)$currentLot['gorselUrl'] : 'public/assets/images/lot_swiss_watch_1968.jpg'; ?>
          <div class="gallery-wrapper">
            <div class="main-watch-frame" style="background:#0A0908; display:flex; align-items:center; justify-content:center; overflow:hidden; position:relative; min-height:300px;">
              <img id="mainLotImage" src="<?= htmlspecialchars($currentLotImg) ?>" alt="<?= htmlspecialchars((string)$currentLot['baslik']) ?>" style="width:100%; height:100%; object-fit:cover;">
              <div style="position:absolute; bottom:12px; left:16px; font-size:0.74rem; color:#EAE2D5; background:rgba(10,9,8,0.78); padding:4px 10px; border-radius:6px; border:1px solid rgba(200,170,110,0.22);">
                <?= htmlspecialchars((string)$currentLot['baslik']) ?> · <?= htmlspecialchars((string)($currentLot['kategoriAdi'] ?? 'Koleksiyon')) ?>
              </div>
            </div>

            <!-- 4 Küçük Detay İnceleme -->
            <div class="watch-thumbnails-grid">
              <div class="watch-thumb" title="Ön Görünüm" style="display:flex; align-items:center; justify-content:center; color:#C8AA6E; cursor:pointer;" onclick="document.getElementById('mainLotImage').src='<?= htmlspecialchars($currentLotImg) ?>';">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><circle cx="11" cy="11" r="8"/><line x1="21" x2="16.65" y1="21" y2="16.65"/><circle cx="11" cy="11" r="3"/></svg>
              </div>
              <div class="watch-thumb" title="Detay İnceleme" style="display:flex; align-items:center; justify-content:center; color:#C8AA6E; cursor:pointer;" onclick="document.getElementById('mainLotImage').src='<?= htmlspecialchars($currentLotImg) ?>';">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-4 0v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1 0-4h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 2.83-2.83l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 4 0v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 0 4h-.09a1.65 1.65 0 0 0-1.51 1z"/></svg>
              </div>
              <div class="watch-thumb" title="Dönem Analizi" style="display:flex; align-items:center; justify-content:center; color:#C8AA6E; cursor:pointer;" onclick="document.getElementById('mainLotImage').src='<?= htmlspecialchars($currentLotImg) ?>';">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/></svg>
              </div>
              <div class="watch-thumb" title="Akredite Damga" style="display:flex; align-items:center; justify-content:center; color:#C8AA6E; cursor:pointer;" onclick="document.getElementById('mainLotImage').src='<?= htmlspecialchars($currentLotImg) ?>';">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
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
                <button type="button" class="btn-outline-gold" style="font-size:0.7rem; padding:3px 8px; border-radius:4px; cursor:pointer; color:#DDA3A3 !important; border-color:rgba(195,108,108,0.4);" onclick="setDemoTimer(0, 'standard')" title="İhaleyi hemen sonlandırır ve pey verme sayısını sıfırlar">0sn Bitir</button>
              </div>
            </div>

            <!-- Kademeli Anti-Sniping Bilgi ve Sayaç Durumu -->
            <?php 
              $isAuctionEnded = (($currentLot['durum'] ?? '') !== 'ACIK_ARTIRMADA') || 
                                (!empty($currentLot['antiSnipingSayaci']['sonBitisZamani']) && strtotime($currentLot['antiSnipingSayaci']['sonBitisZamani']) <= time());
              $stdExtCount = $isAuctionEnded ? 0 : (int)($currentLot['antiSnipingSayaci']['uzatmaSayisi'] ?? 0);
              $nextExtSeconds = ($stdExtCount < 2) ? 30 : 10;
            ?>
            <div style="background:rgba(200,170,110,0.07); border:1px solid rgba(200,170,110,0.22); border-radius:6px; padding:6px 10px; margin:8px 0 10px 0; font-size:0.74rem; display:flex; justify-content:space-between; align-items:center;">
              <span style="color:#A39B8F;">Kural: <strong style="color:#E2C992;">1. &amp; 2. basım +30sn</strong>, <strong style="color:#EAE2D5;">3.+ basım +10sn</strong></span>
              <span style="color:#A39B8F;">Uzatma Sayısı: <strong id="standardExtCountBadge" style="color:#E2C992;"><?= $stdExtCount ?></strong> (Sıradaki: <strong id="standardNextExtBadge" style="color:#C8AA6E;">+<?= $nextExtSeconds ?> sn</strong>)</span>
            </div>

            <div id="standardAntiSnipingWarning" style="display:none; color:#EAE2D5; font-size:0.78rem; margin:8px 0 12px 0; background:rgba(200,170,110,0.1); border:1px solid rgba(200,170,110,0.36); padding:6px 10px; border-radius:6px; align-items:center; gap:6px;">
              <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#E2C992" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
              <span>Son 30 saniye: Pey verilirse süre kademeli uzatılır (+30 sn / +10 sn).</span>
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
              <div style="background:rgba(200,170,110,0.08); border:1px solid rgba(200,170,110,0.28); border-radius:8px; padding:10px 12px; margin-bottom:12px; font-size:0.82rem;">
                <div style="color:#E2C992; font-weight:600; margin-bottom:2px;">Otomatik Teklifiniz Devrede</div>
                <div style="color:#EAE2D5;">Tavan Bütçeniz: <strong><?= $formatPrice((float)$activeProxy['maksimumButceTutari']) ?></strong></div>
                <div style="font-size:0.75rem; color:#A39B8F; margin-top:4px;">Yeni rakip teklif geldiğinde sistem anında karşı teklif basacaktır.</div>
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
            <div style="border-top:1px dashed rgba(200,170,110,0.22); margin-top:14px; padding-top:12px;">
              <div style="font-size:0.75rem; color:var(--text-muted); margin-bottom:6px;">Otomatik Teklifi Canlı Test Et:</div>
              <form method="POST" style="margin:0;">
                <input type="hidden" name="action" value="simulate_competitor_bid">
                <input type="hidden" name="lot_id" value="<?= $currentLot['id'] ?>">
                <button type="submit" class="btn-outline-gold btn-sm" style="width:100%; font-size:0.75rem; justify-content:center; color:#E2C992 !important; border-color:rgba(200,170,110,0.38); cursor:pointer;" title="Başka bir katılımcı adına pey verir, sistemin hemen sizin adınıza otomatik karşı teklif üretmesini test edersiniz">
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
            <div style="font-size:0.84rem; font-weight:600; color:#F5F0E6; margin-bottom:12px;">Seçkin Sanat Koleksiyonu</div>
            
            <div class="side-collection-item" onclick="window.location.href='?role=buyer&tab=canli&lot_id=1002'">
              <img src="public/assets/images/lot_swiss_watch_1968.jpg" alt="Saat" style="width:36px; height:36px; border-radius:6px; object-fit:cover; border:1px solid rgba(200,170,110,0.22);">
              <div>
                <div style="font-size:0.8rem; color:#F5F0E6; font-weight:500;">1968 İsviçre Vintage Saat</div>
                <div style="font-size:0.72rem; color:var(--text-muted);">AUC-1642</div>
              </div>
            </div>

            <div class="side-collection-item" onclick="window.location.href='?role=buyer&tab=canli&lot_id=1001'">
              <img src="public/assets/images/lot_ottoman_carpet.jpg" alt="Halı" style="width:36px; height:36px; border-radius:6px; object-fit:cover; border:1px solid rgba(200,170,110,0.22);">
              <div>
                <div style="font-size:0.8rem; color:#F5F0E6; font-weight:500;">1920'ler Osmanlı İpek Halı</div>
                <div style="font-size:0.72rem; color:var(--text-muted);">AUC-1001</div>
              </div>
            </div>

            <div class="side-collection-item" onclick="window.location.href='?role=buyer&tab=canli&lot_id=1003'">
              <img src="public/assets/images/lot_ceramic_vase.jpg" alt="Vazo" style="width:36px; height:36px; border-radius:6px; object-fit:cover; border:1px solid rgba(200,170,110,0.22);">
              <div>
                <div style="font-size:0.8rem; color:#F5F0E6; font-weight:500;">İznik Kobalt Seramik Vazo</div>
                <div style="font-size:0.72rem; color:var(--text-muted);">AUC-0991</div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Müzayede Ekleri Modalı -->
      <div id="lotEklerModal" class="martello-modal" style="display:none; position:fixed; z-index:9999; left:0; top:0; width:100%; height:100%; background:rgba(6,5,4,0.85); backdrop-filter:blur(6px); align-items:center; justify-content:center;">
        <div style="background:#141210; border:1px solid rgba(200,170,110,0.34); border-radius:14px; max-width:540px; width:92%; padding:24px; position:relative;">
          <button type="button" onclick="document.getElementById('lotEklerModal').style.display='none'" style="position:absolute; right:16px; top:14px; background:none; border:none; color:#A39B8F; font-size:1.5rem; cursor:pointer;">&times;</button>
          <div style="color:#C8AA6E; font-size:0.72rem; font-weight:600; letter-spacing:1.4px; text-transform:uppercase; margin-bottom:4px;">MARTELLO · LOT DÖKÜMANLARI</div>
          <h3 style="color:#F5F0E6; font-family:var(--font-serif); font-size:1.3rem; margin-bottom:14px;">Müzayede Ekleri &amp; Sertifikalar</h3>
          <div style="display:flex; flex-direction:column; gap:10px; font-size:0.84rem;">
            <div style="padding:12px 14px; background:#0D0B0A; border:1px solid rgba(200,170,110,0.16); border-radius:8px; display:flex; justify-content:space-between; align-items:center;">
              <div>
                <div style="font-weight:500; color:#F5F0E6;">Laboratuvar Kondisyon Belgesi (PDF)</div>
                <div style="color:#A39B8F; font-size:0.74rem;">SHA-256 E-İmza ile mühürlü · 2.4 MB</div>
              </div>
              <a href="javascript:void(0)" onclick="alert('Laboratuvar Kondisyon Raporu\n\nLot: <?= addslashes((string)$currentLot['lotKodu']) ?> - <?= addslashes((string)$currentLot['baslik']) ?>\nSHA-256 Mühür: Doğrulandı\nDurum: %100 Orijinal ve Kusursuz Kondisyon')" class="btn-gold btn-sm">Görüntüle</a>
            </div>
            <div style="padding:12px 14px; background:#0D0B0A; border:1px solid rgba(200,170,110,0.16); border-radius:8px; display:flex; justify-content:space-between; align-items:center;">
              <div>
                <div style="font-weight:500; color:#F5F0E6;">Provenans &amp; Sahiplik Şeceresi</div>
                <div style="color:#A39B8F; font-size:0.74rem;">1968–2026 Koleksiyoner zincir kaydı · 1.1 MB</div>
              </div>
              <a href="javascript:void(0)" onclick="alert('Provenans & Sahiplik Şeceresi\n\nLot: <?= addslashes((string)$currentLot['lotKodu']) ?>\n1968–2026 arası koleksiyoner devir zinciri noter ve arşiv kayıtlarıyla doğrulanmıştır.')" class="btn-gold btn-sm">Görüntüle</a>
            </div>
            <div style="padding:12px 14px; background:#0D0B0A; border:1px solid rgba(200,170,110,0.16); border-radius:8px; display:flex; justify-content:space-between; align-items:center;">
              <div>
                <div style="font-weight:500; color:#F5F0E6;">Kültür Varlığı İnceleme Belgesi</div>
                <div style="color:#A39B8F; font-size:0.74rem;">2863 Sayılı Yasa tescil uygunluk belgesi · 850 KB</div>
              </div>
              <a href="javascript:void(0)" onclick="alert('Kültür Varlığı İnceleme Belgesi\n\n2863 Sayılı Kültür ve Tabiat Varlıklarını Koruma Kanunu kapsamında yurtiçi özel koleksiyon satışına uygundur.')" class="btn-gold btn-sm">Görüntüle</a>
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
      <div class="panel-card" style="background:linear-gradient(135deg, #171411 0%, #0E0C0A 100%); border:1px solid rgba(200,170,110,0.38); padding:20px 24px; margin-bottom:22px;">
        <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:16px;">
          <div>
            <div style="font-family:var(--font-brand); font-size:0.7rem; letter-spacing:2px; color:#C8AA6E; text-transform:uppercase; font-weight:600; margin-bottom:4px;">
              MARTELLO PRIVATE CLIENT SERVICES · SALON D'HONNEUR
            </div>
            <h1 style="font-family:var(--font-serif); font-size:1.8rem; color:#F5F0E6; margin:0 0 6px 0; font-weight:500;">
              Özel Müşteriler VIP Müzayede Salonu
            </h1>
            <div style="font-size:0.81rem; color:#A39B8F; display:flex; align-items:center; gap:14px; flex-wrap:wrap;">
              <span>Akreditasyon: <strong style="color:#E2C992;"><?= htmlspecialchars($userVipLevel) ?> TIER</strong></span>
              <span>·</span>
              <span>Özel Temsilci: <strong style="color:#F5F0E6;">Leyla Alptekin</strong></span>
              <span>·</span>
              <span>Doğrudan Hat: <strong style="color:#C8AA6E;">+90 (212) 980 00 24</strong></span>
            </div>
          </div>

          <div style="display:flex; align-items:center; gap:10px; flex-wrap:wrap;">
            <div style="background:#0D0B0A; border:1px solid rgba(200,170,110,0.22); border-radius:10px; padding:10px 16px; text-align:right;">
              <div style="font-size:0.68rem; color:#A39B8F; text-transform:uppercase; letter-spacing:0.6px;">VIP Teklif Limitiniz</div>
              <div style="font-size:1.22rem; font-weight:700; color:#E2C992; font-family:var(--font-serif);"><?= $formatPrice($userLimit) ?></div>
            </div>
            <button type="button" class="btn-gold" style="padding:10px 16px;" onclick="document.getElementById('vipLimitModal').style.display='flex'">
              Limit Artır &rarr;
            </button>
            <button type="button" class="btn-outline-gold" style="padding:10px 16px;" onclick="document.getElementById('vipPhoneModal').style.display='flex'">
              Telefonla Katılım
            </button>
          </div>
        </div>
      </div>

      <!-- VIP Eser Seçim Sekmeleri (Lot Switcher Bar) -->
      <div style="margin-bottom:20px;">
        <div style="font-size:0.74rem; color:#C8AA6E; font-weight:600; text-transform:uppercase; letter-spacing:1.2px; margin-bottom:10px;">
          Aktif VIP Başyapıtlar (Salon Oturumu #802)
        </div>
        <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(260px, 1fr)); gap:12px;">
          <?php foreach ($vipLots as $vlot): 
            $isSelected = (int)$vlot['id'] === (int)$currentVipLot['id'];
          ?>
            <a href="?role=buyer&tab=vip&vip_lot_id=<?= $vlot['id'] ?>" style="text-decoration:none; display:flex; align-items:center; gap:12px; padding:12px 14px; border-radius:10px; background:<?= $isSelected ? 'rgba(200,170,110,0.12)' : '#141210' ?>; border:1px solid <?= $isSelected ? '#C8AA6E' : 'rgba(200,170,110,0.16)' ?>; transition:all 0.2s;">
              <img src="<?= htmlspecialchars($vlot['gorselUrl']) ?>" alt="<?= htmlspecialchars($vlot['baslik']) ?>" style="width:46px; height:46px; border-radius:6px; object-fit:cover; border:1px solid rgba(200,170,110,0.25);">
              <div style="flex:1; overflow:hidden;">
                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:2px;">
                  <span class="badge badge-gold" style="font-size:0.65rem; padding:1px 6px;"><?= htmlspecialchars($vlot['lotKodu']) ?></span>
                  <span style="font-size:0.7rem; color:#E2C992; font-weight:600;">CANLI</span>
                </div>
                <div style="font-size:0.83rem; color:#F5F0E6; font-weight:500; white-space:nowrap; text-overflow:ellipsis; overflow:hidden;"><?= htmlspecialchars($vlot['baslik']) ?></div>
                <div style="font-size:0.76rem; color:#A39B8F; margin-top:2px;">Teklif: <strong style="color:#E2C992;"><?= $formatPrice((float)$vlot['mevcutEnYuksekTeklif']) ?></strong></div>
              </div>
            </a>
          <?php endforeach; ?>
        </div>
      </div>

      <!-- VIP 2 SÜTUNLU DETAY VE PEY ALANI -->
      <div class="lot-detail-container" style="display:grid; grid-template-columns:1.3fr 380px; gap:24px;">
        <!-- SOL SÜTUN: VIP Eser Galerisi, Tarihçe, Bilimsel Ekspertiz -->
        <div>
          <div class="gallery-wrapper" style="border:1px solid rgba(200,170,110,0.28);">
            <div class="main-watch-frame" style="background:#0A0908; display:flex; align-items:center; justify-content:center; overflow:hidden; position:relative; min-height:330px;">
              <img id="mainVipLotImage" src="<?= htmlspecialchars($currentVipLot['gorselUrl']) ?>" alt="<?= htmlspecialchars($currentVipLot['baslik']) ?>" style="max-height:305px; width:auto; object-fit:contain;">
              
              <div style="position:absolute; top:14px; left:16px;">
                <span class="badge badge-gold">SARAY KOLEKSİYONU &amp; MÜZE STATÜSÜ</span>
              </div>

              <div style="position:absolute; bottom:12px; left:16px; font-size:0.74rem; color:#EAE2D5; background:rgba(13,11,10,0.82); padding:4px 10px; border-radius:6px; border:1px solid rgba(200,170,110,0.2);">
                <?= htmlspecialchars($currentVipLot['baslik']) ?> · Lot: <?= htmlspecialchars($currentVipLot['lotKodu']) ?>
              </div>
            </div>

            <div class="watch-thumbnails-grid">
              <div class="watch-thumb" title="Optik Spektrometre İncelemesi" style="display:flex; align-items:center; justify-content:center; color:#C8AA6E; cursor:pointer;" onclick="document.getElementById('mainVipLotImage').src='<?= htmlspecialchars($currentVipLot['gorselUrl']) ?>';">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><circle cx="11" cy="11" r="8"/><line x1="21" x2="16.65" y1="21" y2="16.65"/><circle cx="11" cy="11" r="3"/></svg>
              </div>
              <div class="watch-thumb" title="Usta İmzası ve Hicri Tarih Damgası" style="display:flex; align-items:center; justify-content:center; color:#C8AA6E; cursor:pointer;" onclick="document.getElementById('mainVipLotImage').src='<?= htmlspecialchars($currentVipLot['gorselUrl']) ?>';">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
              </div>
              <div class="watch-thumb" title="Alaşımsal Metalurji Analiz Raporu" style="display:flex; align-items:center; justify-content:center; color:#C8AA6E; cursor:pointer;" onclick="document.getElementById('mainVipLotImage').src='<?= htmlspecialchars($currentVipLot['gorselUrl']) ?>';">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/></svg>
              </div>
              <div class="watch-thumb" title="Provenans ve Hanedan Şeceresi" style="display:flex; align-items:center; justify-content:center; color:#C8AA6E; cursor:pointer;" onclick="document.getElementById('mainVipLotImage').src='<?= htmlspecialchars($currentVipLot['gorselUrl']) ?>';">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
              </div>
            </div>
          </div>

          <!-- Eser Açıklaması ve Sanat Tarihsel Önemi -->
          <div class="panel-card" style="margin-top:16px; margin-bottom:16px;">
            <div style="font-size:0.72rem; color:#C8AA6E; font-weight:600; letter-spacing:1.2px; text-transform:uppercase; margin-bottom:6px;">PROVENANS VE SANAT TARİHİ DETAYLARI</div>
            <p style="font-size:0.84rem; line-height:1.65; color:#EAE2D5; margin:0 0 10px 0;">
              <?= htmlspecialchars($currentVipLot['baslik']) ?>: Dönemin saray astronomları ve seçkin sanatkârları tarafından imal edilmiştir. 2863 Sayılı Kültür ve Tabiat Varlıklarını Koruma Kanunu tescil ve yurtiçi özel koleksiyon devir izinlerine sahiptir.
            </p>
            <div style="display:flex; gap:16px; flex-wrap:wrap; font-size:0.77rem; color:#A39B8F; border-top:1px solid rgba(200,170,110,0.14); padding-top:10px;">
              <span>Dönem: <strong style="color:#F5F0E6;">Klasik Osmanlı / 16. Yüzyıl</strong></span>
              <span>Materyal: <strong style="color:#F5F0E6;">Yaldızlı Gravürlü Pirinç</strong></span>
              <span>Koruma: <strong style="color:#E2C992;">Koleksiyoner Kasası</strong></span>
            </div>
          </div>

          <!-- Bilimsel Ekspertiz Kartı -->
          <div class="expert-white-card">
            <div class="expert-card-top">
              <span class="expert-card-title">Arkeometrik &amp; Bilimsel Ekspertiz Raporu</span>
              <span class="expert-card-badge">VIP SEÇKİ BAŞYAPIT</span>
            </div>
            
            <ul class="expert-card-list">
              <li>XRF Spektrometre Metalurjik Analizi: Alaşım kompozisyonu (%88 Cu, %12 Zn) 16. Yy. saray dökmeciliği ile %99.4 eşleşmektedir.</li>
              <li>İmza &amp; Ebced Tetkiki: İmzalı usta mührü ve döküm üzerindeki hicri tarih arşiv kayıtlarıyla doğrulanmıştır.</li>
              <li>Topkapı Sarayı 1912 Hazine İntikal Defteri ve Viyana Sanat Tarihi Enstitüsü mukayeseli şeceresi tamdır.</li>
            </ul>

            <div class="expert-card-bottom">
              <div>
                <div class="expert-reviewer-title">Akredite Kurul İncelemesi:</div>
                <div class="expert-stars">★ ★ ★ ★ ★</div>
              </div>
              
              <div class="expert-stamp-seal">
                MARTELLO<br>VIP SALON<br>★ ★ ★<br>SARAY MÜHRÜ
              </div>
            </div>
          </div>

          <!-- Zırhlı Özel Lojistik Güvencesi -->
          <div style="background:rgba(200,170,110,0.07); border:1px solid rgba(200,170,110,0.24); border-radius:10px; padding:12px 16px; margin-top:16px; font-size:0.8rem; color:#EAE2D5; line-height:1.5;">
            <strong style="color:#E2C992;">Özel Zırhlı Taşıma ve %100 Teminat:</strong> Kazanan VIP alıcıya teslimat, özel sanat kuryeleri ve tam kıymet sigortasıyla noter huzurunda gerçekleştirilir.
          </div>
        </div>

        <!-- SAĞ SÜTUN: VIP Canlı Teklif Kartı, Telefonla Pey ve VIP Katılımcı Akışı -->
        <div>
          <div class="bid-panel-card" style="border-color:rgba(200,170,110,0.36);">
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:8px;">
              <div class="bid-title-muted" style="color:#C8AA6E; font-weight:600; letter-spacing:1px; text-transform:uppercase;">VIP GÜNCEL TEKLİF</div>
              <span class="badge badge-gold">LİDER TEKLİF</span>
            </div>

            <div class="bid-price-large" style="color:#E2C992; font-size:2.1rem;">
              <?= $formatPrice((float)$currentVipLot['mevcutEnYuksekTeklif']) ?>
            </div>

            <div class="bid-timer-text" style="display:flex; align-items:center; justify-content:space-between; margin-bottom:12px;">
              <div style="display:flex; align-items:center; gap:6px;">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="#C8AA6E" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                <span class="live-countdown" id="vipCountdown" data-target="vip" data-seconds="<?= (!empty($currentVipLot['antiSnipingSayaci']['sonBitisZamani']) && strtotime($currentVipLot['antiSnipingSayaci']['sonBitisZamani']) > time()) ? (strtotime($currentVipLot['antiSnipingSayaci']['sonBitisZamani']) - time()) : 43200 ?>">12 gün 04 saat</span>
              </div>
              <div style="display:flex; gap:4px;">
                <button type="button" class="btn-outline-gold" style="font-size:0.68rem; padding:3px 8px;" onclick="setDemoTimer(25, 'vip')">25sn Test</button>
                <button type="button" class="btn-outline-gold" style="font-size:0.68rem; padding:3px 8px;" onclick="setDemoTimer(5, 'vip')">5sn Test</button>
                <button type="button" class="btn-outline-gold" style="font-size:0.68rem; padding:3px 8px;" onclick="setDemoTimer(0, 'vip')">0sn Bitir</button>
              </div>
            </div>

            <?php 
              $isVipEnded = (($currentVipLot['durum'] ?? '') !== 'ACIK_ARTIRMADA') || 
                            (!empty($currentVipLot['antiSnipingSayaci']['sonBitisZamani']) && strtotime($currentVipLot['antiSnipingSayaci']['sonBitisZamani']) <= time());
              $vipExtCount = $isVipEnded ? 0 : (int)($currentVipLot['antiSnipingSayaci']['uzatmaSayisi'] ?? 0);
              $nextVipExtSeconds = ($vipExtCount < 2) ? 30 : 10;
            ?>
            <div style="background:rgba(200,170,110,0.07); border:1px solid rgba(200,170,110,0.22); border-radius:6px; padding:6px 10px; margin:8px 0 10px 0; font-size:0.73rem; display:flex; justify-content:space-between; align-items:center;">
              <span>Kural: <strong style="color:#E2C992;">1. &amp; 2. basım +30sn</strong>, <strong style="color:#A39B8F;">3.+ basım +10sn</strong></span>
              <span>Uzatma: <strong id="vipExtCountBadge" style="color:#E2C992;"><?= $vipExtCount ?></strong> (<strong id="vipNextExtBadge" style="color:#C8AA6E;">+<?= $nextVipExtSeconds ?> sn</strong>)</span>
            </div>

            <div id="vipAntiSnipingWarning" style="display:none; color:#EAE2D5; font-size:0.78rem; margin:8px 0 12px 0; background:rgba(200,170,110,0.1); border:1px solid rgba(200,170,110,0.36); padding:6px 10px; border-radius:6px; align-items:center; gap:6px;">
              <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#E2C992" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
              <span>Son 30 saniye: VIP pey verilirse süre kademeli uzatılır (+30 sn / +10 sn).</span>
            </div>

            <!-- VIP Pey Ver Formu -->
            <form method="POST" id="vipBidForm">
              <input type="hidden" name="action" value="place_bid">
              <input type="hidden" name="lot_id" value="<?= $currentVipLot['id'] ?>">
              <input type="hidden" name="user_id" value="<?= $buyerId ?>">
              <input type="hidden" id="vipRemainingSecondsInput" name="remaining_seconds" value="<?= (!empty($currentVipLot['antiSnipingSayaci']['sonBitisZamani']) && strtotime($currentVipLot['antiSnipingSayaci']['sonBitisZamani']) > time()) ? (strtotime($currentVipLot['antiSnipingSayaci']['sonBitisZamani']) - time()) : 43200 ?>">

              <div class="form-group" style="margin-bottom:12px;">
                <label class="form-label">Teklifiniz (Asgari: <?= $formatPrice($minNextVipBid) ?>)</label>
                <input type="number" step="5000" id="vipBidAmountInput" name="bid_amount" class="form-control" value="<?= $minNextVipBid ?>" required style="font-size:1.1rem; font-weight:700; color:#E2C992;">
              </div>

              <div style="display:grid; grid-template-columns:1fr 1fr; gap:8px; margin-bottom:14px;">
                <button type="button" class="btn-outline-gold btn-sm" onclick="const bi=document.getElementById('vipBidAmountInput'); if(bi) bi.value=parseFloat(bi.value||0)+10000;">+10.000</button>
                <button type="button" class="btn-outline-gold btn-sm" onclick="const bi=document.getElementById('vipBidAmountInput'); if(bi) bi.value=parseFloat(bi.value||0)+25000;">+25.000</button>
                <button type="button" class="btn-outline-gold btn-sm" onclick="const bi=document.getElementById('vipBidAmountInput'); if(bi) bi.value=parseFloat(bi.value||0)+50000;">+50.000</button>
                <button type="button" class="btn-outline-gold btn-sm" onclick="const bi=document.getElementById('vipBidAmountInput'); if(bi) bi.value=parseFloat(bi.value||0)+100000;">+100.000</button>
              </div>

              <button type="submit" id="vipBidSubmitBtn" class="btn-register-glow" style="font-size:0.95rem; padding:11px;">
                VIP Resmi Pey Ver
              </button>
            </form>

            <!-- VIP Rakip Teklif Canlı Simülasyonu Butonu -->
            <div style="border-top:1px solid rgba(200,170,110,0.14); margin-top:14px; padding-top:12px;">
              <form method="POST" style="margin:0;">
                <input type="hidden" name="action" value="simulate_vip_bid">
                <input type="hidden" name="lot_id" value="<?= $currentVipLot['id'] ?>">
                <button type="submit" class="btn-outline-gold btn-sm" style="width:100%; font-size:0.74rem; justify-content:center; cursor:pointer;">
                  Rakip VIP Pey Simülasyonu (+<?= $formatPrice((float)($currentVipLot['minimumArtisTutari'] ?? 10000)) ?>)
                </button>
              </form>
            </div>
          </div>

          <!-- VIP Telefonla Katılım & Konsiyerj Hizmeti Kartı -->
          <div class="bid-panel-card" style="margin-top:16px;">
            <div style="display:flex; align-items:center; gap:10px; margin-bottom:10px;">
              <div style="width:36px; height:36px; border-radius:50%; background:linear-gradient(135deg, #E2C992 0%, #A98743 100%); display:flex; align-items:center; justify-content:center; color:#110E0B; font-weight:700; font-size:0.8rem;">
                LA
              </div>
              <div>
                <div style="font-size:0.85rem; font-weight:600; color:#F5F0E6;">Leyla Alptekin</div>
                <div style="font-size:0.72rem; color:#C8AA6E;">Kıdemli Özel Müşteri Danışmanı</div>
              </div>
            </div>
            <p style="font-size:0.78rem; color:#A39B8F; line-height:1.5; margin-bottom:12px;">
              Salonda fiziksel olarak bulunamayan müşterilerimiz için özel şifreli hat üzerinden canlı telefonla pey hizmeti tahsis edilmektedir.
            </p>
            <button type="button" class="btn-outline-gold btn-sm" style="width:100%; justify-content:center; padding:8px;" onclick="document.getElementById('vipPhoneModal').style.display='flex'">
              Telefonla Katılım Rezerve Et &rarr;
            </button>
          </div>

          <!-- VIP Salondaki Son Teklifler Akışı -->
          <div class="other-bids-card" style="margin-top:16px;">
            <div class="other-bids-title" style="color:#E2C992; display:flex; align-items:center; justify-content:space-between;">
              <span>VIP Salonu Teklif Geçmişi</span>
              <span style="font-size:0.7rem; color:#A39B8F;">Canlı Akış</span>
            </div>
            
            <?php if (!empty($vipLotBids)): ?>
              <?php foreach (array_slice($vipLotBids, 0, 5) as $vb): ?>
                <div class="other-bid-row">
                  <div class="other-bid-user" style="display:flex; align-items:center; gap:6px;">
                    <span style="color:#C8AA6E;">★</span>
                    <span><?= htmlspecialchars($vb['anonimKod'] ?? $vb['aliciAnonimKod'] ?? 'VIP Katılımcı') ?></span>
                  </div>
                  <div class="other-bid-time">
                    <strong style="color:#E2C992;"><?= $formatPrice((float)$vb['teklifTutari']) ?></strong>
                  </div>
                </div>
              <?php endforeach; ?>
            <?php else: ?>
              <div class="other-bid-row">
                <div class="other-bid-user" style="display:flex; align-items:center; gap:6px;">
                  <span style="color:#C8AA6E;">★</span>
                  <span>damror0ki (VIP)</span>
                </div>
                <div class="other-bid-time"><strong style="color:#E2C992;"><?= $formatPrice((float)$currentVipLot['mevcutEnYuksekTeklif']) ?></strong></div>
              </div>
              <div class="other-bid-row">
                <div class="other-bid-user" style="display:flex; align-items:center; gap:6px;">
                  <span style="color:#C8AA6E;">★</span>
                  <span>yortunen... (VIP)</span>
                </div>
                <div class="other-bid-time"><strong style="color:#E2C992;"><?= $formatPrice((float)$currentVipLot['mevcutEnYuksekTeklif'] - 10000) ?></strong></div>
              </div>
            <?php endif; ?>
          </div>
        </div>
      </div>
    </div>

    <!-- ====================================================================
         2. TAB: KAZANILAN LOTLAR (Yatay Kaydırmalı & Sade Obsidian/Altın)
         ==================================================================== -->
    <div id="buyerTab_kazanilanlar" class="tab-content <?= $activeTab === 'kazanilanlar' ? 'active' : '' ?>">
      <div class="page-breadcrumb">ALICI / KAZANILAN LOTLAR</div>
      <h1 class="page-heading">Kazanılan Lotlar</h1>
      <p class="page-subheading">Kazandığınız eserler, emanet durumu ve teslimat takibi.</p>

      <div style="display:grid; grid-template-columns:minmax(0, 1.7fr) minmax(280px, 1fr); gap:20px; align-items:start;">
        
        <!-- Sol Sütun: Kazanılan Lotlar Tablosu (Yatay Kaydırılabilir) -->
        <div class="panel-card" style="padding:0; overflow:hidden; margin-bottom:0; min-width:0;">
          <div style="padding:14px 18px; border-bottom:1px solid var(--border-color); display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:8px;">
            <div>
              <div style="font-family:var(--font-serif); font-weight:500; color:#F5F0E6; font-size:1.08rem;">Kazanılan Lotlar Listesi</div>
              <div style="font-size:0.74rem; color:#8C8478;">Ödemeleriniz teslimat onayına kadar emanet havuzunda korunur.</div>
            </div>
            <span style="font-size:0.72rem; color:#C8AA6E; background:rgba(200,170,110,0.08); border:1px solid rgba(200,170,110,0.22); padding:3px 10px; border-radius:999px;">
              Tamamı için sağa kaydırın &rarr;
            </span>
          </div>

          <div class="table-scroll-x">
            <table class="data-table" style="margin:0; min-width:860px;">
              <thead>
                <tr>
                  <th style="padding-left:18px;">Lot &amp; Sipariş</th>
                  <th>Eser</th>
                  <th>Satıcı</th>
                  <th>Kategori</th>
                  <th>Tarih</th>
                  <th>Kapanış Fiyatı</th>
                  <th>Durum</th>
                  <th style="padding-right:18px;">İşlemler</th>
                </tr>
              </thead>
              <tbody>
                <?php if (empty($wonOrders)): ?>
                  <tr>
                    <td colspan="8" style="text-align:center; padding:28px; color:#8C8478;">
                      Henüz kazandığınız bir lot bulunmuyor.
                    </td>
                  </tr>
                <?php else: ?>
                  <?php foreach ($wonOrders as $wo): 
                    $lotImg = !empty($wo['urunGorsel']) ? $wo['urunGorsel'] : 'public/assets/images/lot_ottoman_carpet.jpg';
                    $lotCode = $wo['lotKodu'] ?? ('AUC-' . ($wo['lotId'] ?? ''));
                    $sipNo = $wo['siparisNo'] ?? ('ORD-2026-' . ($wo['id'] ?? ''));
                    $kapanis = (float)($wo['kapanisFiyati'] ?? $wo['genelToplamTutar'] ?? 0);
                    $durum = $wo['siparisDurumu'] ?? 'Emanet_Bloke';
                    $sellerName = $wo['saticiAdi'] ?? 'Mehmet Kaya';
                    $isPaidOut = in_array($durum, ['Teslim Edildi & Satıcıya Aktarıldı', 'SATICIYA_AKTARILDI', 'Ödendi'], true);
                  ?>
                    <tr>
                      <td style="padding-left:18px; white-space:nowrap;">
                        <div style="display:flex; align-items:center; gap:10px;">
                          <img src="<?= htmlspecialchars($lotImg) ?>" alt="Lot" style="width:38px; height:38px; border-radius:6px; object-fit:cover; border:1px solid rgba(200,170,110,0.2);" onerror="this.src='public/assets/images/lot_ceramic_vase.jpg'">
                          <div>
                            <span style="font-weight:600; color:#F5F0E6; display:block; font-size:0.8rem;"><?= htmlspecialchars($lotCode) ?></span>
                            <span style="font-size:0.68rem; color:#A39B8F;"><?= htmlspecialchars($sipNo) ?></span>
                          </div>
                        </div>
                      </td>
                      <td style="font-weight:500; color:#F5F0E6; min-width:160px;">
                        <?= htmlspecialchars($wo['urunBaslik'] ?? 'Müzayede Eseri') ?>
                      </td>
                      <td style="color:#C8AA6E; font-size:0.78rem; white-space:nowrap;">
                        <?= htmlspecialchars($sellerName) ?>
                      </td>
                      <td style="color:#A39B8F; font-size:0.78rem; white-space:nowrap;"><?= htmlspecialchars($wo['kategoriAdi'] ?? 'Antika & Sanat') ?></td>
                      <td style="font-size:0.78rem; color:#A39B8F; white-space:nowrap;"><?= htmlspecialchars($wo['sonTarih'] ?? date('d-M-Y')) ?></td>
                      <td style="white-space:nowrap;">
                        <div style="font-family:var(--font-serif); font-weight:600; font-size:0.95rem; color:#E2C992;"><?= $formatPrice($kapanis) ?></div>
                      </td>
                      <td style="white-space:nowrap;">
                        <?php if ($isPaidOut): ?>
                          <span class="badge badge-success">Teslim Edildi</span>
                        <?php else: ?>
                          <span class="badge badge-gold">Emanette Bloke</span>
                        <?php endif; ?>
                      </td>
                      <td style="padding-right:18px; white-space:nowrap;">
                        <?php if (!$isPaidOut): ?>
                          <form method="POST" style="margin:0 0 4px 0;" onsubmit="return confirm('Eseri teslim aldığınızı onaylıyor musunuz?');">
                            <input type="hidden" name="action" value="complete_escrow_payout">
                            <input type="hidden" name="escrow_id" value="7001">
                            <input type="hidden" name="actor_id" value="<?= $buyerId ?>">
                            <input type="hidden" name="actor_role" value="ALICI">
                            <input type="hidden" name="release_note" value="Alıcı kargoyu teslim aldı ve hakediş aktarımını onayladı.">
                            <button type="submit" class="btn-outline-gold btn-sm" style="padding:4px 10px; font-size:0.72rem;">
                              Teslim Aldım
                            </button>
                          </form>
                        <?php else: ?>
                          <span style="color:#9CC7B0; font-size:0.74rem; display:block;">Tamamlandı</span>
                        <?php endif; ?>
                        <a href="javascript:void(0)" onclick="alert('Sipariş: <?= htmlspecialchars($sipNo) ?>\nKargo Takip: <?= htmlspecialchars($wo['kargoTakipNo'] ?? 'KRG-2026-998877') ?>')" style="color:#C8AA6E; font-size:0.72rem;">Kargo Takibi</a>
                      </td>
                    </tr>
                  <?php endforeach; ?>
                <?php endif; ?>
              </tbody>
            </table>
          </div>
        </div>

        <!-- Sağ Sütun: Ödeme Özeti ve Son Kazanılanlar -->
        <div style="display:flex; flex-direction:column; gap:16px; min-width:0;">
          <div class="panel-card" style="margin-bottom:0; padding:18px;">
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:12px;">
              <span style="font-family:var(--font-serif); font-size:1.05rem; color:#F5F0E6;">Ödeme Özeti</span>
              <span class="badge badge-gold">Dekont Kayıtlı</span>
            </div>
            <div style="display:flex; justify-content:space-between; margin-bottom:8px; font-size:0.8rem;">
              <span style="color:#A39B8F;">Son Lot</span>
              <span style="color:#F5F0E6;">AUC-0991</span>
            </div>
            <div style="display:flex; justify-content:space-between; margin-bottom:8px; font-size:0.8rem;">
              <span style="color:#A39B8F;">Çekiç Fiyatı</span>
              <span style="color:#F5F0E6;"><?= $formatPrice(19500) ?></span>
            </div>
            <div style="display:flex; justify-content:space-between; margin-bottom:8px; font-size:0.8rem;">
              <span style="color:#A39B8F;">Durum</span>
              <span style="color:#9CC7B0;">Ödendi</span>
            </div>
            <div style="display:flex; justify-content:space-between; margin-top:10px; padding-top:10px; border-top:1px solid var(--border-color); font-size:0.84rem;">
              <span style="color:#A39B8F;">Toplam Tutar</span>
              <span style="color:#E2C992; font-weight:600;"><?= $formatPrice(19500) ?></span>
            </div>
          </div>

          <div class="panel-card" style="margin-bottom:0; padding:18px;">
            <div style="font-family:var(--font-serif); font-size:1.05rem; color:#F5F0E6; margin-bottom:12px;">Son Kazanılan Eserler</div>
            <div style="display:grid; grid-template-columns:1fr 1fr; gap:10px;">
              <div style="background:#0D0B0A; border:1px solid rgba(200,170,110,0.16); border-radius:8px; padding:10px; display:flex; flex-direction:column; justify-content:space-between; gap:10px;">
                <div style="display:flex; gap:8px; align-items:center;">
                  <img src="public/assets/images/lot_ceramic_vase.jpg" alt="Vazo" style="width:36px; height:36px; border-radius:6px; object-fit:cover;">
                  <div style="min-width:0;">
                    <div style="font-size:0.68rem; color:#C8AA6E;">AUC-0991</div>
                    <div style="font-size:0.76rem; color:#F5F0E6; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">İznik Seramik Vazo</div>
                  </div>
                </div>
                <button type="button" onclick="switchBuyerTab('canli', this)" class="btn-outline-gold btn-sm" style="width:100%; justify-content:center;">İncele</button>
              </div>

              <div style="background:#0D0B0A; border:1px solid rgba(200,170,110,0.16); border-radius:8px; padding:10px; display:flex; flex-direction:column; justify-content:space-between; gap:10px;">
                <div style="display:flex; gap:8px; align-items:center;">
                  <img src="public/assets/images/lot_rare_books.jpg" alt="Kitap" style="width:36px; height:36px; border-radius:6px; object-fit:cover;">
                  <div style="min-width:0;">
                    <div style="font-size:0.68rem; color:#C8AA6E;">AUC-0984</div>
                    <div style="font-size:0.76rem; color:#F5F0E6; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">Nadir Kitap Seçkisi</div>
                  </div>
                </div>
                <button type="button" onclick="switchBuyerTab('canli', this)" class="btn-outline-gold btn-sm" style="width:100%; justify-content:center;">İncele</button>
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
          <div class="kpi-value"><?= max(2, count($wonOrders)) ?></div>
        </div>
        <div class="kpi-card">
          <div class="kpi-label">Aktif Teklifler</div>
          <div class="kpi-value"><?= $buyerActiveBidsCount ?></div>
        </div>
        <div class="kpi-card">
          <div class="kpi-label">Takip Ettiklerim</div>
          <div class="kpi-value"><?= count($userWatchlistLots) ?></div>
        </div>
        <div class="kpi-card">
          <div class="kpi-label">Toplam Harcama</div>
          <div class="kpi-value"><?= $formatPrice($buyerTotalSpent) ?></div>
        </div>
      </div>

      <!-- Müzayede Keşfet Banner Kartı -->
      <div class="panel-card" style="margin-bottom:24px; display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:16px; padding:22px 26px;">
        <div style="max-width:680px;">
          <div style="color:#C8AA6E; font-size:0.72rem; font-weight:600; text-transform:uppercase; letter-spacing:1.5px; margin-bottom:4px;">
            MARTELLO · SALON REHBERİ
          </div>
          <h3 style="font-family:var(--font-serif); font-size:1.4rem; color:#F5F0E6; margin-bottom:6px; font-weight:500;">
            Canlı ve Planlanan Müzayedeler
          </h3>
          <p style="font-size:0.84rem; color:#A39B8F; margin:0; line-height:1.55;">
            Devam eden <strong style="color:#E2C992;"><?= count($liveAuctions) ?> canlı açık artırma</strong> ve takvimlendirilmiş <strong style="color:#E2C992;"><?= count($upcomingAuctions) ?> gelecek müzayede oturumunu</strong> inceleyin veya canlı salona katılın.
          </p>
        </div>
        <button type="button" onclick="switchBuyerTab('kesfet', this)" class="btn-gold" style="padding:10px 22px;">
          Müzayedeleri Keşfet &rarr;
        </button>
      </div>

      <!-- Yan Yana Çalışma Alanı ve Seçenekler Grid -->
      <div style="display:grid; grid-template-columns:1.5fr 1fr; gap:20px; align-items:start;">
        <!-- Sol: Son Kazanılan Eserler Hızlı Görünüm -->
        <div class="panel-card" style="margin-bottom:0;">
          <div class="panel-header">
            <h3 class="panel-title">Son Kazandığınız Parçalar</h3>
            <button type="button" onclick="switchBuyerTab('kazanilanlar', this)" class="btn-outline-gold btn-sm">Tümünü Gör &rarr;</button>
          </div>
          <div style="display:flex; flex-direction:column; gap:10px;">
            <div style="display:flex; justify-content:space-between; align-items:center; padding:10px 14px; background:#0D0B0A; border:1px solid rgba(200,170,110,0.14); border-radius:8px;">
              <div style="display:flex; align-items:center; gap:12px;">
                <img src="public/assets/images/lot_ceramic_vase.jpg" alt="Vazo" style="width:40px; height:40px; border-radius:6px; object-fit:cover; border:1px solid rgba(200,170,110,0.2);">
                <div>
                  <div style="font-weight:500; color:#F5F0E6; font-size:0.86rem;">Antika Sır Altı Çini ve Seramik Vazo</div>
                  <div style="font-size:0.74rem; color:#A39B8F;">AUC-0991 · Çekiç Fiyatı: <?= $formatPrice(43500) ?></div>
                </div>
              </div>
              <span class="badge badge-success">ÖDENDİ</span>
            </div>
            <div style="display:flex; justify-content:space-between; align-items:center; padding:10px 14px; background:#0D0B0A; border:1px solid rgba(200,170,110,0.14); border-radius:8px;">
              <div style="display:flex; align-items:center; gap:12px;">
                <img src="public/assets/images/lot_rare_books.jpg" alt="Kitap" style="width:40px; height:40px; border-radius:6px; object-fit:cover; border:1px solid rgba(200,170,110,0.2);">
                <div>
                  <div style="font-weight:500; color:#F5F0E6; font-size:0.86rem;">1885 Matbaa-i Âmire Nadir Kitap Koleksiyonu</div>
                  <div style="font-size:0.74rem; color:#A39B8F;">AUC-0984 · Çekiç Fiyatı: <?= $formatPrice(3200) ?></div>
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
          <div style="display:flex; flex-direction:column; gap:8px;">
            <button type="button" onclick="switchBuyerTab('kesfet', this)" class="btn-gold" style="width:100%; justify-content:space-between; padding:10px 14px;">
              <span>Müzayede Keşfet (Canlı &amp; Gelecek)</span><span>&rarr;</span>
            </button>
            <button type="button" onclick="switchBuyerTab('gecmis', this)" class="btn-outline-gold" style="width:100%; display:flex; justify-content:space-between; padding:9px 14px;">
              <span>Müzayede &amp; Satış Geçmişi</span><span>&rarr;</span>
            </button>
            <button type="button" onclick="switchBuyerTab('canli', this)" class="btn-outline-gold" style="width:100%; display:flex; justify-content:space-between; padding:9px 14px;">
              <span>Canlı Müzayede Salonu</span><span>&rarr;</span>
            </button>
            <button type="button" onclick="switchBuyerTab('arama', this)" class="btn-outline-gold" style="width:100%; display:flex; justify-content:space-between; padding:9px 14px;">
              <span>Eser Arama &amp; Filtre</span><span>&rarr;</span>
            </button>
            <button type="button" onclick="switchBuyerTab('bakiye', this)" class="btn-outline-gold" style="width:100%; display:flex; justify-content:space-between; padding:9px 14px;">
              <span>Bakiye &amp; Teminat Yükle</span><span>&rarr;</span>
            </button>
            <button type="button" onclick="switchBuyerTab('takip', this)" class="btn-outline-gold" style="width:100%; display:flex; justify-content:space-between; padding:9px 14px;">
              <span>Takip Listem</span><span>&rarr;</span>
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- 4. TAB: BAKİYE VE FATURALAR (Sade, Bütüne Uyumlu Obsidian & Şampanya Altın) -->
    <div id="buyerTab_bakiye" class="tab-content <?= $activeTab === 'bakiye' ? 'active' : '' ?>">
      <div class="page-breadcrumb">ALICI / BAKİYE VE FATURALAR</div>
      <div style="display:flex; justify-content:space-between; align-items:flex-end; flex-wrap:wrap; gap:12px; margin-bottom:20px;">
        <div>
          <h1 class="page-heading" style="margin-bottom:4px;">Bakiye ve Faturalar</h1>
          <p class="page-subheading" style="margin:0;">Cüzdan bakiyeniz, kayıtlı kartlarınız ve ödeme makbuzları.</p>
        </div>
        <span class="badge badge-gold">3D Secure Korumalı</span>
      </div>

      <!-- 4 Sade Finansal Özet Kartı -->
      <div class="kpi-grid" style="grid-template-columns:repeat(4, 1fr); gap:14px; margin-bottom:20px;">
        <div class="kpi-card highlight">
          <div class="kpi-label">Kullanılabilir Bakiye</div>
          <div class="kpi-value"><?= $formatPrice($buyerAvailableBalance) ?></div>
        </div>
        <div class="kpi-card">
          <div class="kpi-label">Emanette Bloke</div>
          <div class="kpi-value" style="color:#EAE2D5;"><?= $formatPrice($buyerBlockedBalance) ?></div>
        </div>
        <div class="kpi-card">
          <div class="kpi-label">Kayıtlı Kartlar</div>
          <div class="kpi-value" style="color:#EAE2D5;"><?= count($buyerSavedCards) ?> Kart</div>
        </div>
        <div class="kpi-card">
          <div class="kpi-label">Güvenlik</div>
          <div class="kpi-value" style="color:#C8AA6E; font-size:1.35rem;">3D Secure Aktif</div>
        </div>
      </div>

      <!-- Ana 2 Sütunlu Ödeme ve Kart Alanı -->
      <div style="display:grid; grid-template-columns:1.45fr 1fr; gap:20px; align-items:start; margin-bottom:22px;">
        
        <!-- SOL: Bakiye Yükleme -->
        <div class="panel-card" style="margin-bottom:0;">
          <div class="panel-header">
            <h3 class="panel-title">Bakiye Yükle</h3>
            <span class="badge badge-gold">%0 Komisyon</span>
          </div>

          <form id="cardPaymentForm" method="POST" onsubmit="return handleCardPaymentSubmit(event);">
            <input type="hidden" name="action" value="process_card_payment">
            <input type="hidden" name="user_id" value="<?= $buyerId ?>">
            <input type="hidden" name="payment_target" value="WALLET">
            <input type="hidden" id="selectedPaymentMethod" name="payment_method" value="SAVED_CARD">
            <input type="hidden" id="finalSmsCode" name="sms_code" value="123456">

            <div style="margin-bottom:16px;">
              <label class="form-label">Ödeme Yöntemi</label>
              <div style="display:grid; grid-template-columns:repeat(3, 1fr); gap:8px;">
                <button type="button" id="btnMethodSaved" class="btn-method-tab active" onclick="switchPaymentMethodTab('SAVED_CARD')" style="padding:8px 10px; font-size:0.78rem; font-weight:500; background:rgba(200,170,110,0.14); border:1px solid #C8AA6E; color:#F5F0E6; border-radius:8px; cursor:pointer;">
                  Kayıtlı Kartlar
                </button>
                <button type="button" id="btnMethodCredit" class="btn-method-tab" onclick="switchPaymentMethodTab('NEW_CREDIT')" style="padding:8px 10px; font-size:0.78rem; font-weight:500; background:#0D0B0A; border:1px solid rgba(200,170,110,0.16); color:#A39B8F; border-radius:8px; cursor:pointer;">
                  Kredi Kartı
                </button>
                <button type="button" id="btnMethodDebit" class="btn-method-tab" onclick="switchPaymentMethodTab('NEW_DEBIT')" style="padding:8px 10px; font-size:0.78rem; font-weight:500; background:#0D0B0A; border:1px solid rgba(200,170,110,0.16); color:#A39B8F; border-radius:8px; cursor:pointer;">
                  Banka Kartı
                </button>
              </div>
            </div>

            <!-- Kayıtlı Kartlar -->
            <div id="paneSavedCards" style="display:block; margin-bottom:16px;">
              <?php if (empty($buyerSavedCards)): ?>
                <div style="background:#0D0B0A; border:1px dashed rgba(200,170,110,0.2); border-radius:8px; padding:14px; text-align:center; color:#8C8478; font-size:0.8rem;">
                  Kayıtlı kartınız bulunmuyor.
                </div>
              <?php else: ?>
                <div style="display:flex; flex-direction:column; gap:8px;">
                  <?php foreach ($buyerSavedCards as $idx => $sc): ?>
                    <label style="display:flex; align-items:center; justify-content:space-between; background:#0D0B0A; border:1px solid <?= $idx === 0 ? '#C8AA6E' : 'rgba(200,170,110,0.16)' ?>; border-radius:8px; padding:10px 14px; cursor:pointer;" class="saved-card-option">
                      <div style="display:flex; align-items:center; gap:10px;">
                        <input type="radio" name="token_id" value="<?= htmlspecialchars($sc['tokenId']) ?>" <?= $idx === 0 ? 'checked' : '' ?> onchange="highlightSelectedSavedCard(this)" style="accent-color:#C8AA6E;">
                        <div>
                          <div style="font-weight:500; color:#F5F0E6; font-size:0.82rem;">
                            <?= htmlspecialchars($sc['kartAdi']) ?>
                            <span style="font-size:0.7rem; color:#A39B8F; margin-left:6px;"><?= htmlspecialchars($sc['kartTipiEtiket'] ?? 'Kart') ?></span>
                          </div>
                          <div style="font-family:monospace; color:#8C8478; font-size:0.76rem; margin-top:2px;">
                            <?= htmlspecialchars($sc['maskeliKartNo']) ?> · <?= htmlspecialchars($sc['sonKullanma']) ?>
                          </div>
                        </div>
                      </div>
                      <span class="badge badge-gold">3DS</span>
                    </label>
                  <?php endforeach; ?>
                </div>

                <div style="margin-top:10px; max-width:160px;">
                  <label class="form-label">CVV</label>
                  <input type="password" name="saved_card_cvv" maxlength="4" value="482" class="form-control" style="letter-spacing:3px;" required>
                </div>
              <?php endif; ?>
            </div>

            <!-- Yeni Kart Formu -->
            <div id="paneNewCard" style="display:none; margin-bottom:16px;">
              <input type="hidden" id="newCardTypeInput" name="card_type" value="KREDI_KARTI">
              <div style="background:#0D0B0A; border:1px solid rgba(200,170,110,0.16); border-radius:10px; padding:14px;">
                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:10px;">
                  <span id="newCardTypeBadge" class="badge badge-gold">Kredi Kartı</span>
                  <span style="font-size:0.72rem; color:#8C8478;">Visa / Mastercard / Troy</span>
                </div>
                <div class="form-group">
                  <label class="form-label">Kart Sahibi</label>
                  <input type="text" name="card_holder" class="form-control" placeholder="AD SOYAD" style="text-transform:uppercase;">
                </div>
                <div class="form-group">
                  <label class="form-label">Kart Numarası</label>
                  <input type="text" name="card_number" id="newCardNumInput" class="form-control" placeholder="5400 0000 0000 0000" maxlength="19" oninput="formatCreditCardNumber(this)" style="font-family:monospace; letter-spacing:1px;">
                </div>
                <div style="display:grid; grid-template-columns:1fr 1fr 1fr; gap:10px;">
                  <div class="form-group" style="margin-bottom:0;">
                    <label class="form-label">Ay</label>
                    <select name="expire_month" class="form-control">
                      <?php for ($m = 1; $m <= 12; $m++): ?>
                        <option value="<?= sprintf('%02d', $m) ?>"><?= sprintf('%02d', $m) ?></option>
                      <?php endfor; ?>
                    </select>
                  </div>
                  <div class="form-group" style="margin-bottom:0;">
                    <label class="form-label">Yıl</label>
                    <select name="expire_year" class="form-control">
                      <?php for ($y = 2026; $y <= 2035; $y++): ?>
                        <option value="<?= substr((string)$y, -2) ?>"><?= $y ?></option>
                      <?php endfor; ?>
                    </select>
                  </div>
                  <div class="form-group" style="margin-bottom:0;">
                    <label class="form-label">CVV</label>
                    <input type="password" name="cvv" placeholder="•••" maxlength="4" class="form-control">
                  </div>
                </div>
                <label style="display:flex; align-items:center; gap:8px; font-size:0.76rem; color:#A39B8F; margin-top:12px; cursor:pointer;">
                  <input type="checkbox" name="save_card" value="1" checked style="accent-color:#C8AA6E;">
                  <span>Kartı sonraki işlemler için kaydet</span>
                </label>
              </div>
            </div>

            <!-- Tutar -->
            <div style="margin-bottom:16px;">
              <label class="form-label">Yüklenecek Tutar (TRY)</label>
              <div style="display:flex; gap:6px; flex-wrap:wrap; margin-bottom:8px;">
                <button type="button" class="btn-dark-pill" style="font-size:0.74rem; padding:4px 10px; cursor:pointer;" onclick="setPaymentAmount(10000)">10.000 ₺</button>
                <button type="button" class="btn-dark-pill" style="font-size:0.74rem; padding:4px 10px; cursor:pointer;" onclick="setPaymentAmount(25000)">25.000 ₺</button>
                <button type="button" class="btn-dark-pill" style="font-size:0.74rem; padding:4px 10px; cursor:pointer;" onclick="setPaymentAmount(50000)">50.000 ₺</button>
                <button type="button" class="btn-dark-pill" style="font-size:0.74rem; padding:4px 10px; cursor:pointer;" onclick="setPaymentAmount(100000)">100.000 ₺</button>
              </div>
              <input type="number" id="paymentAmountInput" name="amount" class="form-control" value="25000" min="100" step="500" required style="font-size:0.98rem; font-weight:600; color:#E2C992;">
            </div>

            <button type="submit" class="btn-gold-pill" style="width:100%; padding:11px; font-size:0.85rem; cursor:pointer;">
              <span id="btnPayText">3D Secure ile Bakiye Yükle (₺25.000,00)</span>
            </button>
          </form>
        </div>

        <!-- SAĞ: Kayıtlı Kartlarım -->
        <div class="panel-card" style="margin-bottom:0;">
          <div class="panel-header">
            <h3 class="panel-title">Kayıtlı Kartlarım</h3>
            <button type="button" onclick="switchPaymentMethodTab('NEW_CREDIT')" class="btn-outline-gold btn-sm">+ Yeni Kart</button>
          </div>

          <?php if (empty($buyerSavedCards)): ?>
            <div style="text-align:center; padding:18px; color:#8C8478; font-size:0.8rem;">Tanımlı kart bulunamadı.</div>
          <?php else: ?>
            <div style="display:flex; flex-direction:column; gap:10px;">
              <?php foreach ($buyerSavedCards as $sc): ?>
                <div style="background:linear-gradient(135deg, #191612 0%, #0E0C0A 100%); border:1px solid rgba(200,170,110,0.28); border-radius:10px; padding:14px; position:relative;">
                  <div style="display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:10px;">
                    <div>
                      <div style="font-size:0.68rem; color:#C8AA6E; letter-spacing:0.8px; text-transform:uppercase; font-weight:600;"><?= htmlspecialchars($sc['banka']) ?></div>
                      <div style="font-size:0.82rem; color:#F5F0E6; font-weight:500; margin-top:2px;"><?= htmlspecialchars($sc['kartAdi']) ?></div>
                    </div>
                    <span class="badge badge-gold"><?= htmlspecialchars($sc['kartTipiEtiket'] ?? 'Kart') ?></span>
                  </div>
                  <div style="font-family:monospace; font-size:0.92rem; letter-spacing:1.6px; color:#F5F0E6; margin-bottom:10px;">
                    <?= htmlspecialchars($sc['maskeliKartNo']) ?>
                  </div>
                  <div style="display:flex; justify-content:space-between; align-items:center; font-size:0.74rem; color:#A39B8F;">
                    <span><?= htmlspecialchars($sc['kartSahibi'] ?? 'ZEYNEP DEMİR') ?> · SKT <?= htmlspecialchars($sc['sonKullanma'] ?? '09/28') ?></span>
                    <form method="POST" style="margin:0;" onsubmit="return confirm('Kartı silmek istediğinize emin misiniz?');">
                      <input type="hidden" name="action" value="delete_card">
                      <input type="hidden" name="user_id" value="<?= $buyerId ?>">
                      <input type="hidden" name="token_id" value="<?= htmlspecialchars($sc['tokenId']) ?>">
                      <button type="submit" style="background:none; border:none; color:#C96A6A; font-size:0.72rem; cursor:pointer;">Sil</button>
                    </form>
                  </div>
                </div>
              <?php endforeach; ?>
            </div>
          <?php endif; ?>
        </div>
      </div>

      <!-- Makbuzlar ve İşlem Geçmişi -->
      <div class="panel-card" style="margin-bottom:0;">
        <div class="panel-header">
          <h3 class="panel-title">Ödeme Makbuzları</h3>
          <span class="badge badge-gold"><?= count($buyerPaymentReceipts) ?> İşlem</span>
        </div>

        <?php if (empty($buyerPaymentReceipts)): ?>
          <div style="text-align:center; padding:24px; color:#8C8478; font-size:0.82rem;">Henüz ödeme kaydı bulunmuyor.</div>
        <?php else: ?>
          <div class="table-scroll-x">
            <table class="data-table" style="min-width:680px;">
              <thead>
                <tr>
                  <th>Makbuz No</th>
                  <th>İşlem Kodu</th>
                  <th>Kart</th>
                  <th>Tarih</th>
                  <th>Tutar</th>
                  <th>Durum</th>
                  <th style="text-align:right;">Detay</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($buyerPaymentReceipts as $rc): ?>
                  <tr>
                    <td style="font-family:monospace; color:#C8AA6E; font-weight:600;"><?= htmlspecialchars($rc['makbuzNo'] ?? 'MAKBUZ-3DS') ?></td>
                    <td style="font-family:monospace; color:#EAE2D5;"><?= htmlspecialchars($rc['islemKodu'] ?? '') ?></td>
                    <td>
                      <div style="color:#F5F0E6;"><?= htmlspecialchars($rc['kartTuru'] ?? 'Kart') ?></div>
                      <div style="font-family:monospace; color:#8C8478; font-size:0.72rem;"><?= htmlspecialchars($rc['maskeliKart'] ?? '') ?></div>
                    </td>
                    <td style="color:#A39B8F;"><?= htmlspecialchars(substr($rc['tarih'] ?? '', 0, 16)) ?></td>
                    <td style="font-family:var(--font-serif); font-weight:600; color:#E2C992; font-size:0.92rem;">
                      ₺<?= number_format((float)($rc['tutar'] ?? 0), 2, ',', '.') ?>
                    </td>
                    <td><span class="badge badge-success">Onaylı</span></td>
                    <td style="text-align:right;">
                      <button type="button" onclick="alert('Makbuz: <?= htmlspecialchars($rc['makbuzNo'] ?? '') ?>\nTutar: ₺<?= number_format((float)($rc['tutar'] ?? 0), 2, ',', '.') ?>\n3D Secure onaylı.');" class="btn-table-action">Görüntüle</button>
                    </td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        <?php endif; ?>
      </div>
    </div>

    <!-- 3D Secure Onay Modalı -->
    <div id="threeDSecureModal" class="martello-modal" style="display:none; position:fixed; z-index:10050; inset:0; width:100vw; height:100vh; background:rgba(6,5,4,0.84); backdrop-filter:blur(8px); align-items:center; justify-content:center;">
      <div style="background:#141210; border:1px solid rgba(200,170,110,0.34); border-radius:14px; max-width:420px; width:92%; padding:24px; box-shadow:0 24px 60px rgba(0,0,0,0.88); position:relative;">
        <button type="button" onclick="close3DSecureModal()" style="position:absolute; right:16px; top:14px; background:none; border:none; color:#A39B8F; font-size:1.4rem; cursor:pointer;">&times;</button>
        <div style="font-family:var(--font-brand); color:#C8AA6E; font-size:0.68rem; letter-spacing:1.8px; text-transform:uppercase; margin-bottom:4px;">3D SECURE ONAY</div>
        <h3 id="modalBankTitle" style="font-family:var(--font-serif); color:#F5F0E6; font-size:1.3rem; font-weight:500; margin:0 0 12px 0;">SMS Doğrulama</h3>
        <div style="background:#0D0B0A; border:1px solid var(--border-color); border-radius:8px; padding:12px; margin-bottom:14px; font-size:0.8rem;">
          <div style="display:flex; justify-content:space-between; margin-bottom:4px;">
            <span style="color:#A39B8F;">İşyeri</span><strong style="color:#F5F0E6;">MARTELLO</strong>
          </div>
          <div style="display:flex; justify-content:space-between;">
            <span style="color:#A39B8F;">Tutar</span><strong id="modalAmountDisplay" style="color:#E2C992;">₺25.000,00</strong>
          </div>
        </div>
        <div style="margin-bottom:14px;">
          <label class="form-label">SMS Kodu (Demo: 123456)</label>
          <input type="text" id="modalSmsInput" class="form-control" value="123456" maxlength="6" style="text-align:center; font-size:1.15rem; letter-spacing:6px; color:#E2C992;" required>
        </div>
        <button type="button" onclick="confirm3DSecurePayment()" class="btn-gold-pill" style="width:100%; padding:10px; font-size:0.84rem; cursor:pointer;">
          Onayla ve Tamamla
        </button>
      </div>
    </div>

    <!-- 3D Secure Javascript Fonksiyonları -->
    <script>
      function switchPaymentMethodTab(method) {
        document.getElementById('selectedPaymentMethod').value = method;
        document.querySelectorAll('.btn-method-tab').forEach(b => {
          b.classList.remove('active');
          b.style.background = '#0D0B0A';
          b.style.borderColor = 'rgba(200,170,110,0.16)';
          b.style.color = '#A39B8F';
        });

        const pSaved = document.getElementById('paneSavedCards');
        const pNew = document.getElementById('paneNewCard');
        const cardTypeInput = document.getElementById('newCardTypeInput');
        const cardTypeBadge = document.getElementById('newCardTypeBadge');

        if (method === 'SAVED_CARD') {
          const btn = document.getElementById('btnMethodSaved');
          if (btn) {
            btn.classList.add('active');
            btn.style.background = 'rgba(200,170,110,0.14)';
            btn.style.borderColor = '#C8AA6E';
            btn.style.color = '#F5F0E6';
          }
          if (pSaved) pSaved.style.display = 'block';
          if (pNew) pNew.style.display = 'none';
        } else if (method === 'NEW_CREDIT') {
          const btn = document.getElementById('btnMethodCredit');
          if (btn) {
            btn.classList.add('active');
            btn.style.background = 'rgba(200,170,110,0.14)';
            btn.style.borderColor = '#C8AA6E';
            btn.style.color = '#F5F0E6';
          }
          if (pSaved) pSaved.style.display = 'none';
          if (pNew) pNew.style.display = 'block';
          if (cardTypeInput) cardTypeInput.value = 'KREDI_KARTI';
          if (cardTypeBadge) cardTypeBadge.innerText = 'Kredi Kartı';
        } else if (method === 'NEW_DEBIT') {
          const btn = document.getElementById('btnMethodDebit');
          if (btn) {
            btn.classList.add('active');
            btn.style.background = 'rgba(200,170,110,0.14)';
            btn.style.borderColor = '#C8AA6E';
            btn.style.color = '#F5F0E6';
          }
          if (pSaved) pSaved.style.display = 'none';
          if (pNew) pNew.style.display = 'block';
          if (cardTypeInput) cardTypeInput.value = 'BANKA_KARTI';
          if (cardTypeBadge) cardTypeBadge.innerText = 'Banka Kartı';
        }
      }

      function highlightSelectedSavedCard(radio) {
        document.querySelectorAll('.saved-card-option').forEach(el => {
          el.style.borderColor = 'rgba(200,170,110,0.16)';
        });
        if (radio && radio.closest) {
          const parent = radio.closest('.saved-card-option');
          if (parent) parent.style.borderColor = '#C8AA6E';
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

      <!-- Teminat İade Güvencesi -->
      <div style="background:rgba(200,170,110,0.07); border:1px solid rgba(200,170,110,0.24); border-radius:8px; padding:12px 16px; margin-bottom:20px; display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:10px;">
        <div>
          <strong style="color:#E2C992; font-size:0.82rem;">Otomatik Teminat &amp; Emanet İade Güvencesi</strong>
          <span style="color:#A39B8F; font-size:0.77rem; display:block;">
            Teklif iptali veya ihtilafın lehinize sonuçlanması durumunda bloke edilen tutar cüzdanınıza iade edilir.
          </span>
        </div>
        <span class="badge badge-gold">%100 İade Koruması</span>
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
            <h3 class="panel-title">Çözüm Politikası &amp; Güvence</h3>
          </div>
          <div style="display:flex; flex-direction:column; gap:10px; font-size:0.82rem; color:#A39B8F; line-height:1.55;">
            <div style="padding:10px 14px; background:#0D0B0A; border:1px solid rgba(200,170,110,0.14); border-radius:8px;">
              <strong style="color:#E2C992;">1. Emanet Blokesi:</strong> İhtilaf açıldığı anda ilgili lotun satıcıya aktarımı dondurulur, tutarınız güvence altına alınır.
            </div>
            <div style="padding:10px 14px; background:#0D0B0A; border:1px solid rgba(200,170,110,0.14); border-radius:8px;">
              <strong style="color:#E2C992;">2. Bağımsız Ekspertiz:</strong> Eser tarafsız kıdemli heyet tarafından fiziki muayeneye alınır.
            </div>
            <div style="padding:10px 14px; background:#0D0B0A; border:1px solid rgba(200,170,110,0.14); border-radius:8px;">
              <strong style="color:#E2C992;">3. 48 Saat İade Garantisi:</strong> Haklı itirazlarda tam tutar derhal cüzdanınıza iade edilir.
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

      <div style="display:grid; grid-template-columns:1.5fr 1fr; gap:20px; align-items:start;">
        <!-- Sol: Takip Tablosu (Dinamik Watchlist) -->
        <div class="panel-card" style="margin-bottom:0; padding:0; overflow:hidden;">
          <table class="data-table" style="margin:0;">
            <thead>
              <tr>
                <th style="padding-left:20px;">Eser &amp; Lot Kodu</th>
                <th>Kategori</th>
                <th>Güncel Fiyat</th>
                <th>Durum</th>
                <th style="padding-right:20px; text-align:right;">İşlem</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($userWatchlistLots as $wLot):
                $wImg = (string)($wLot['gorselUrl'] ?? 'public/assets/images/lot_swiss_watch_1968.jpg');
                $wTabTarget = (($wLot['muzyedeTuru'] ?? '') === 'VIP' || (int)($wLot['muzayedeId'] ?? 0) === 802)
                    ? ('?role=buyer&tab=vip&vip_lot_id=' . (int)$wLot['id'])
                    : ('?role=buyer&tab=canli&lot_id=' . (int)$wLot['id']);
              ?>
                <tr>
                  <td style="padding-left:20px;">
                    <div style="display:flex; align-items:center; gap:10px;">
                      <img src="<?= htmlspecialchars($wImg) ?>" alt="Lot" style="width:38px; height:38px; border-radius:6px; object-fit:cover; border:1px solid rgba(200,170,110,0.22);" onerror="this.src='public/assets/images/lot_swiss_watch_1968.jpg'">
                      <div>
                        <span class="badge badge-gold" style="margin-bottom:2px;"><?= htmlspecialchars((string)($wLot['lotKodu'] ?? 'AUC-1642')) ?></span>
                        <div style="font-weight:500; color:#F5F0E6; font-size:0.85rem;"><?= htmlspecialchars((string)($wLot['baslik'] ?? 'Müzayede Eseri')) ?></div>
                      </div>
                    </div>
                  </td>
                  <td style="color:#A39B8F; font-size:0.82rem;"><?= htmlspecialchars((string)($wLot['kategoriAdi'] ?? 'Koleksiyon')) ?></td>
                  <td style="color:#E2C992; font-family:var(--font-serif); font-size:0.95rem; font-weight:700;"><?= $formatPrice((float)($wLot['mevcutEnYuksekTeklif'] ?? 0)) ?></td>
                  <td><span class="badge badge-success">CANLI</span></td>
                  <td style="padding-right:20px; text-align:right; white-space:nowrap;">
                    <a href="<?= htmlspecialchars($wTabTarget) ?>" class="btn-gold btn-sm" style="text-decoration:none;">Müzayedeye Git &rarr;</a>
                    <form method="POST" style="display:inline; margin-left:6px;">
                      <input type="hidden" name="action" value="toggle_watchlist">
                      <input type="hidden" name="user_id" value="<?= $buyerId ?>">
                      <input type="hidden" name="lot_id" value="<?= (int)$wLot['id'] ?>">
                      <input type="hidden" name="current_price" value="<?= (float)($wLot['mevcutEnYuksekTeklif'] ?? 0) ?>">
                      <button type="submit" class="btn-outline-gold btn-sm" title="Takip Listesinden Çıkar">Çıkar</button>
                    </form>
                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>

        <!-- Sağ: Takip Listesi İpuçları & Bildirim Ayarları -->
        <div class="panel-card" style="margin-bottom:0;">
          <div class="panel-header">
            <h3 class="panel-title">Takip Bildirimleri</h3>
          </div>
          <div style="display:flex; flex-direction:column; gap:10px; font-size:0.82rem; color:#A39B8F;">
            <div style="padding:10px 14px; background:#0D0B0A; border:1px solid rgba(200,170,110,0.14); border-radius:8px;">
              <strong style="color:#E2C992;">Son 10 Dakika Uyarısı Açık:</strong> Listenizdeki eserde müzayede kapanışına 10 dk kala bildirim alırsınız.
            </div>
            <div style="padding:10px 14px; background:#0D0B0A; border:1px solid rgba(200,170,110,0.14); border-radius:8px;">
              <strong style="color:#C8AA6E;">Otomatik Pey Entegrasyonu:</strong> Takip ettiğiniz eserlere tek tıkla otomatik tavan bütçe belirleyebilirsiniz.
            </div>
            <button type="button" onclick="switchBuyerTab('canli', this)" class="btn-outline-gold" style="width:100%; justify-content:center; padding:9px;">
              Canlı Lot Ekranına Dön
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- 7. TAB: AYARLAR (Dinamik Oturum Kullanıcısı Profil Güncelleme) -->
    <?php
      $profileFullName = trim((string)($currentUserRecord['ad'] ?? 'Zeynep') . ' ' . (string)($currentUserRecord['soyad'] ?? 'Demir'));
      $profileEmail    = (string)($currentUserRecord['email'] ?? 'zeynep@koleksiyon.com');
      $profilePhone    = (string)($currentUserRecord['telefon'] ?? '+90 544 333 44 55');
      $profileAddress  = (string)($currentUserRecord['adres'] ?? 'Teşvikiye Mah. Abdi İpekçi Cad. No: 42 Daire: 6, Şişli / İstanbul');
    ?>
    <div id="buyerTab_ayarlar" class="tab-content <?= $activeTab === 'ayarlar' ? 'active' : '' ?>">
      <div class="page-breadcrumb">ALICI / HESAP &amp; PROFİL AYARLARI</div>
      <h1 class="page-heading">Hesap &amp; Profil Ayarları</h1>
      <p class="page-subheading">Kişisel bilgilerinizi, teslimat adresinizi ve güvenlik tercihlerinizi yönetin.</p>

      <div style="display:grid; grid-template-columns:1.5fr 1fr; gap:20px;">
        <div class="panel-card" style="margin-bottom:0;">
          <div class="panel-header">
            <h3 class="panel-title">Profil Bilgilerini Güncelle</h3>
            <span class="badge badge-success"><?= !empty($currentUserRecord['kycOnayli']) ? 'KYC Onaylı' : 'Standart Üye' ?></span>
          </div>
          <form method="POST">
            <input type="hidden" name="action" value="update_profile">
            <input type="hidden" name="user_id" value="<?= $buyerId ?>">

            <div style="display:grid; grid-template-columns:1fr 1fr; gap:14px;">
              <div class="form-group">
                <label class="form-label">Ad Soyad</label>
                <input type="text" name="adSoyad" class="form-control" value="<?= htmlspecialchars($profileFullName) ?>" required>
              </div>
              <div class="form-group">
                <label class="form-label">E-posta Adresi</label>
                <input type="email" name="eposta" class="form-control" value="<?= htmlspecialchars($profileEmail) ?>" required>
              </div>
            </div>

            <div style="display:grid; grid-template-columns:1fr 1fr; gap:14px;">
              <div class="form-group">
                <label class="form-label">Telefon Numarası</label>
                <input type="text" name="telefon" class="form-control" value="<?= htmlspecialchars($profilePhone) ?>" required>
              </div>
              <div class="form-group">
                <label class="form-label">Yeni Şifre (İsteğe Bağlı)</label>
                <input type="password" name="yeni_sifre" class="form-control" placeholder="Değiştirmek için en az 6 karakter">
              </div>
            </div>

            <div class="form-group">
              <label class="form-label">Fatura &amp; Teslimat Adresi</label>
              <textarea name="adres" class="form-control" rows="3"><?= htmlspecialchars($profileAddress) ?></textarea>
            </div>

            <button type="submit" class="btn-gold" style="padding:10px 24px;">
              Bilgileri Güncelle
            </button>
          </form>
        </div>

        <!-- Güvenlik & Bildirim Tercihleri -->
        <div class="panel-card" style="margin-bottom:0;">
          <div class="panel-header">
            <h3 class="panel-title">Güvenlik &amp; Bildirimler</h3>
          </div>
          <div style="display:flex; flex-direction:column; gap:12px;">
            <div style="display:flex; justify-content:space-between; align-items:center; padding:12px 14px; background:#0D0B0A; border:1px solid rgba(200,170,110,0.14); border-radius:8px;">
              <div>
                <div style="font-weight:500; color:#F5F0E6; font-size:0.86rem;">İki Adımlı Doğrulama (2FA)</div>
                <div style="font-size:0.74rem; color:#A39B8F;">Giriş ve yüksek peylerde SMS onayı</div>
              </div>
              <span class="badge badge-success">AKTİF</span>
            </div>

            <div style="display:flex; justify-content:space-between; align-items:center; padding:12px 14px; background:#0D0B0A; border:1px solid rgba(200,170,110,0.14); border-radius:8px;">
              <div>
                <div style="font-weight:500; color:#F5F0E6; font-size:0.86rem;">Canlı Müzayede Sesli Uyarı</div>
                <div style="font-size:0.74rem; color:#A39B8F;">Pey geçildiğinde çekiç sesi bildirimi</div>
              </div>
              <span class="badge badge-gold">AÇIK</span>
            </div>

            <div style="display:flex; justify-content:space-between; align-items:center; padding:12px 14px; background:#0D0B0A; border:1px solid rgba(200,170,110,0.14); border-radius:8px;">
              <div>
                <div style="font-weight:500; color:#F5F0E6; font-size:0.86rem;">Koleksiyon Bülteni</div>
                <div style="font-size:0.74rem; color:#A39B8F;">Haftalık seçki ve müzayede takvimi</div>
              </div>
              <span class="badge badge-info">ABONE</span>
            </div>
          </div>
        </div>
      </div>
    </div>

  <!-- VIP Limit Artırım Modalı -->
<div id="vipLimitModal" class="martello-modal" style="display:none; position:fixed; z-index:9999; left:0; top:0; width:100%; height:100%; background:rgba(6,5,4,0.85); backdrop-filter:blur(6px); align-items:center; justify-content:center;">
  <div style="background:#141210; border:1px solid rgba(200,170,110,0.38); border-radius:14px; max-width:480px; width:92%; padding:26px; position:relative; box-shadow:0 20px 50px rgba(0,0,0,0.9);">
    <button type="button" onclick="document.getElementById('vipLimitModal').style.display='none'" style="position:absolute; right:16px; top:14px; background:none; border:none; color:#A39B8F; font-size:1.5rem; cursor:pointer;">&times;</button>
    <div style="color:#C8AA6E; font-size:0.72rem; font-weight:600; letter-spacing:1.4px; text-transform:uppercase; margin-bottom:4px;">MARTELLO PRIVATE CLIENTS</div>
    <h3 style="color:#F5F0E6; font-family:var(--font-serif); font-size:1.35rem; margin-bottom:8px;">VIP Teklif Limiti Güncelleme</h3>
    <p style="font-size:0.82rem; color:#A39B8F; line-height:1.55; margin-bottom:16px;">
      Akredite VIP statünüz gereği teminat limitiniz sistem tarafından anında tahsis edilebilir.
    </p>
    <form method="POST">
      <input type="hidden" name="action" value="vip_request_limit">
      <input type="hidden" name="user_id" value="<?= $buyerId ?>">
      <div class="form-group" style="margin-bottom:16px;">
        <label class="form-label">Talep Edilen VIP Teklif Limiti (<?= $currencyStrategy->getCode() ?>)</label>
        <select name="target_limit" class="form-control" style="font-size:0.95rem; font-weight:600; color:#E2C992;">
          <option value="2000000" <?= $userLimit == 2000000 ? 'selected' : '' ?>><?= $formatPrice(2000000) ?> (Standart VIP Limit)</option>
          <option value="3500000" <?= $userLimit == 3500000 ? 'selected' : '' ?>><?= $formatPrice(3500000) ?> (Platinum Seviye)</option>
          <option value="5000000" <?= $userLimit == 5000000 ? 'selected' : '' ?>><?= $formatPrice(5000000) ?> (Diamond Seviye)</option>
          <option value="10000000" <?= $userLimit == 10000000 ? 'selected' : '' ?>><?= $formatPrice(10000000) ?> (Özel Konsorsiyum Limiti)</option>
        </select>
      </div>
      <button type="submit" class="btn-gold" style="width:100%; justify-content:center; padding:11px;">
        Limiti Onayla ve Güncelle
      </button>
    </form>
  </div>
</div>

<!-- VIP Telefonla Katılım Rezervasyon Modalı -->
<div id="vipPhoneModal" class="martello-modal" style="display:none; position:fixed; z-index:9999; left:0; top:0; width:100%; height:100%; background:rgba(6,5,4,0.85); backdrop-filter:blur(6px); align-items:center; justify-content:center;">
  <div style="background:#141210; border:1px solid rgba(200,170,110,0.38); border-radius:14px; max-width:480px; width:92%; padding:26px; position:relative; box-shadow:0 20px 50px rgba(0,0,0,0.9);">
    <button type="button" onclick="document.getElementById('vipPhoneModal').style.display='none'" style="position:absolute; right:16px; top:14px; background:none; border:none; color:#A39B8F; font-size:1.5rem; cursor:pointer;">&times;</button>
    <div style="color:#C8AA6E; font-size:0.72rem; font-weight:600; letter-spacing:1.4px; text-transform:uppercase; margin-bottom:4px;">SALON D'HONNEUR · TELEFONLA KATILIM</div>
    <h3 style="color:#F5F0E6; font-family:var(--font-serif); font-size:1.35rem; margin-bottom:8px;">Canlı Salonda Telefonla Temsil</h3>
    <p style="font-size:0.82rem; color:#A39B8F; line-height:1.55; margin-bottom:16px;">
      Özel temsilciniz müzayede salonunda masanızda hazır bulunacak ve canlı telefon bağlantısıyla verdiğiniz talimatları salonda adınıza icra edecektir.
    </p>
    <form method="POST">
      <input type="hidden" name="action" value="vip_reserve_phone">
      <input type="hidden" name="user_id" value="<?= $buyerId ?>">
      <input type="hidden" name="lot_id" value="<?= $currentVipLot['id'] ?>">
      
      <div class="form-group" style="margin-bottom:12px;">
        <label class="form-label">Aranacak VIP Telefon Numarası</label>
        <input type="text" name="phone" class="form-control" value="<?= htmlspecialchars($profilePhone) ?>" required>
      </div>

      <div class="form-group" style="margin-bottom:16px;">
        <label class="form-label">Tercih Edilen Temsil Dili</label>
        <select name="preferred_language" class="form-control">
          <option value="Türkçe" selected>Türkçe (Ana Dil)</option>
          <option value="İngilizce">İngilizce (English)</option>
          <option value="Fransızca">Fransızca (Français)</option>
        </select>
      </div>

      <button type="submit" class="btn-gold" style="width:100%; justify-content:center; padding:11px;">
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
