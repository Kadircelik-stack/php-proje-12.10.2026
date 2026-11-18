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

$minNextBid = (float)($currentLot['mevcutEnYuksekTeklif'] ?? 0) + (float)($currentLot['minimumArtisTutari'] ?? 1500);

$activeTab = $_GET['tab'] ?? 'canli';
?>

<div class="app-container">
  <!-- Sol Menü (Sidebar) — SRS Sayfa 33 ile Birebir Uyumlu -->
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
            <span class="menu-icon">⚲</span> Müzayede keşfet
          </a>
        </li>
        <li class="nav-menu-item <?= $activeTab === 'canli' ? 'active' : '' ?>">
          <button type="button" onclick="switchBuyerTab('canli')">
            <span class="menu-icon">((•))</span> Canlı müzayede
          </button>
        </li>
        <li class="nav-menu-item <?= $activeTab === 'kazanilanlar' ? 'active' : '' ?>">
          <button type="button" onclick="switchBuyerTab('kazanilanlar')">
            <span class="menu-icon">🏆</span> Kazanılan lotlar
          </button>
        </li>
        <li class="nav-menu-item <?= $activeTab === 'takip' ? 'active' : '' ?>">
          <button type="button" onclick="switchBuyerTab('takip')">
            <span class="menu-icon">♡</span> Takip edilenler
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
        <li class="nav-menu-item <?= $activeTab === 'itirazlar' ? 'active' : '' ?>">
          <button type="button" onclick="switchBuyerTab('itirazlar')">
            <span class="menu-icon">⚖️</span> İtirazlar
          </button>
        </li>
      </ul>
    </div>

    <!-- Cüzdan Durumu Kutusu -->
    <div style="background:rgba(18,22,31,0.8); border:1px solid var(--border-color); border-radius:8px; padding:16px;">
      <div style="font-size:0.72rem; color:var(--text-muted); text-transform:uppercase; letter-spacing:1px;">Cüzdan Bakiyesi</div>
      <div style="font-family:var(--font-serif); font-size:1.45rem; font-weight:700; color:var(--gold-bright); margin:4px 0;">
        <?= $formatPrice(120000) ?>
      </div>
      <div style="font-size:0.75rem; color:var(--text-muted); display:flex; justify-content:space-between; margin-top:4px;">
        <span>Bloke Teminat:</span>
        <span style="color:#f85149; font-weight:600;"><?= $formatPrice(65000) ?></span>
      </div>
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
         2. TAB: KAZANILAN LOTLAR (media_1794924544606_6befb3fa.jpg)
         ==================================================================== -->
    <div id="buyerTab_kazanilanlar" class="tab-content <?= $activeTab === 'kazanilanlar' ? 'active' : '' ?>">
      <div class="page-breadcrumb">ALICI / ÇALIŞMA ALANI</div>
      <h1 class="page-heading">Kazanılan lotlar</h1>
      <p class="page-subheading">Müzayede deneyiminizi profesyonelce yönetin.</p>

      <!-- Tablo Kutusu -->
      <div class="won-lots-table-box">
        <table class="data-table">
          <thead>
            <tr>
              <th>Lot</th>
              <th>Ürün</th>
              <th>Kategori</th>
              <th>Son Tarih</th>
              <th>Son Fiyat</th>
              <th>Ödeme Durumu</th>
              <th>İşlemler</th>
            </tr>
          </thead>
          <tbody>
            <tr>
              <td>
                <div style="display:flex; align-items:center; gap:8px;">
                  <span style="font-size:1.4rem;">🏺</span>
                  <span class="badge badge-gold">AUC-0991</span>
                </div>
              </td>
              <td style="font-weight:600; color:#fff;">Antika Seramik Vazo</td>
              <td style="color:var(--text-muted);">Sanat</td>
              <td>12-Ekim-2026</td>
              <td style="font-family:var(--font-serif); font-weight:700; color:var(--gold-bright);">
                <?= $formatPrice(12800) ?>
              </td>
              <td><span style="color:#3fb950; font-weight:600;">Ödendi</span></td>
              <td>
                <a href="javascript:void(0)" onclick="alert('Fatura No: FAT-2026-1088\nTutar: <?= $formatPrice(12800) ?>')" style="color:var(--gold-primary); font-size:0.82rem; margin-right:12px;">Faturayı İndir</a>
                <a href="javascript:void(0)" onclick="alert('Kargo: KRG-99887701 (Özel Taşıma / Dağıtımda)')" style="color:var(--gold-primary); font-size:0.82rem;">Kargoyu Takip Et</a>
              </td>
            </tr>

            <tr>
              <td>
                <div style="display:flex; align-items:center; gap:8px;">
                  <span style="font-size:1.4rem;">📚</span>
                  <span class="badge badge-gold">AUC-0984</span>
                </div>
              </td>
              <td style="font-weight:600; color:#fff;">Nadir Kitap Koleksiyonu</td>
              <td style="color:var(--text-muted);">Sanat</td>
              <td>12-Ekim-2026</td>
              <td style="font-family:var(--font-serif); font-weight:700; color:var(--gold-bright);">
                <?= $formatPrice(9200) ?>
              </td>
              <td><span style="color:#3fb950; font-weight:600;">Ödendi</span></td>
              <td>
                <a href="javascript:void(0)" onclick="alert('Fatura No: FAT-2026-1089\nTutar: <?= $formatPrice(9200) ?>')" style="color:var(--gold-primary); font-size:0.82rem; margin-right:12px;">Faturayı İndir</a>
                <a href="javascript:void(0)" onclick="alert('Kargo: KRG-99887702 (Özel Taşıma / Dağıtımda)')" style="color:var(--gold-primary); font-size:0.82rem;">Kargoyu Takip Et</a>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Ödeme Detayları Kutusu (Görsel 3 Alt) -->
      <div class="order-summary-box">
        <div class="order-summary-title">Ödeme Detayları</div>
        <div class="order-summary-row">
          <span style="color:var(--text-muted);">Lot</span>
          <span>ALIC-0991</span>
        </div>
        <div class="order-summary-row">
          <span style="color:var(--text-muted);">Son Fiyat</span>
          <span><?= $formatPrice(12800) ?></span>
        </div>
        <div class="order-summary-row">
          <span style="color:var(--text-muted);">Ödeme Durumu</span>
          <span style="color:#3fb950;">Ödendi</span>
        </div>
        <div class="order-summary-row">
          <span style="color:var(--text-muted);">Ödeme Son yni</span>
          <span><?= $formatPrice(3200) ?></span>
        </div>
        <div class="order-summary-row" style="border-top:1px solid var(--border-color); padding-top:8px; margin-top:8px; font-weight:700; color:var(--gold-bright);">
          <span>Ödeme Detayları</span>
          <span><?= $formatPrice(12800) ?></span>
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
