<?php
declare(strict_types=1);

/** @var callable $formatPrice */
/** @var array $db */
/** @var \Martello\Controllers\WebAndApiController $controller */

$users                 = $db['users'] ?? [];
$lots                  = $db['lots'] ?? [];
$auctions              = $db['auctions'] ?? [];
$reports               = $db['appraisalReports'] ?? [];
$appraisalRequests     = $db['appraisalRequests'] ?? [];
$disputes              = $db['disputes'] ?? [];
$listings              = $db['listings'] ?? [];
$categories            = $db['categories'] ?? [];
$orders                = $db['orders'] ?? [];
$bids                  = $db['bids'] ?? [];
$products              = $db['products'] ?? [];
$expertAccreditations  = $db['expertAccreditations'] ?? [];
$commissionDefinitions = $controller->getCommissionDefinitions();
$escrowTransactions    = $controller->getEscrowTransactions();
$escrowSummary         = $controller->getEscrowSummary();
$auditLogList          = $controller->getAuditLogs();
$activeTab             = $_GET['tab'] ?? 'konsol';
if ($activeTab === 'ihtilaflar') {
    $activeTab = 'iptal_yonetimi';
}

$lotMap = [];
foreach ($lots as $l) {
    $lotMap[(int)($l['id'] ?? 0)] = $l;
}
$productMap = [];
foreach ($products as $p) {
    $productMap[(int)($p['id'] ?? 0)] = $p;
}
$userMap = [];
foreach ($users as $u) {
    $userMap[(int)($u['id'] ?? 0)] = $u;
}

$commMuz = $commissionDefinitions['muzayede'] ?? [];
$commSat = $commissionDefinitions['satis'] ?? [];
$commExp = $commissionDefinitions['ekspertiz'] ?? [];

$activeLotCount = count(array_filter($lots, static fn($l) => ($l['durum'] ?? '') === 'ACIK_ARTIRMADA'));
$openDisputeCount = count(array_filter($disputes, static fn($d) => strtoupper((string)($d['durum'] ?? '')) !== 'COZULDU'));
$cancelledCount = count(array_filter($bids, static fn($b) => ($b['durum'] ?? '') === 'IPTAL_EDILDI'))
                + count(array_filter($lots, static fn($l) => ($l['durum'] ?? '') === 'IPTAL_EDILDI'));
?>

<div class="app-container">
  <!-- Sol Menü (Sade Obsidian & Şampanya Altın) -->
  <aside class="sidebar">
    <div>
      <div class="sidebar-section-title">Yönetim</div>
      <ul class="nav-menu">
        <li class="nav-menu-item <?= $activeTab === 'konsol' ? 'active' : '' ?>">
          <button type="button" data-tab="konsol" onclick="switchAdminTab('konsol', this)">Genel Bakış</button>
        </li>
        <li class="nav-menu-item <?= $activeTab === 'moderasyon' ? 'active' : '' ?>">
          <button type="button" data-tab="moderasyon" onclick="switchAdminTab('moderasyon', this)">Müzayede Yönetimi</button>
        </li>
        <li class="nav-menu-item <?= $activeTab === 'raporlar' ? 'active' : '' ?>">
          <button type="button" data-tab="raporlar" onclick="switchAdminTab('raporlar', this)">Ekspertiz Raporları</button>
        </li>
        <li class="nav-menu-item <?= $activeTab === 'heyet_inceleme' ? 'active' : '' ?>">
          <button type="button" data-tab="heyet_inceleme" onclick="switchAdminTab('heyet_inceleme', this)">Dosya &amp; Belge İnceleme</button>
        </li>
        <li class="nav-menu-item <?= $activeTab === 'ekspertiz' ? 'active' : '' ?>">
          <button type="button" data-tab="ekspertiz" onclick="switchAdminTab('ekspertiz', this)">Ekspertiz Atama</button>
        </li>
        <li class="nav-menu-item <?= $activeTab === 'akreditasyon' ? 'active' : '' ?>">
          <button type="button" data-tab="akreditasyon" onclick="switchAdminTab('akreditasyon', this)">Eksper Akreditasyonu</button>
        </li>
      </ul>
    </div>

    <div>
      <div class="sidebar-section-title">Finans &amp; Denetim</div>
      <ul class="nav-menu">
        <li class="nav-menu-item <?= $activeTab === 'iptal_yonetimi' ? 'active' : '' ?>">
          <button type="button" data-tab="iptal_yonetimi" onclick="switchAdminTab('iptal_yonetimi', this)">Pey &amp; İlan İptali</button>
        </li>
        <li class="nav-menu-item <?= $activeTab === 'ihtilaf_cozumu' ? 'active' : '' ?>">
          <button type="button" data-tab="ihtilaf_cozumu" onclick="switchAdminTab('ihtilaf_cozumu', this)">İhtilaf &amp; Tahkim</button>
        </li>
        <li class="nav-menu-item <?= $activeTab === 'komisyon' ? 'active' : '' ?>">
          <button type="button" data-tab="komisyon" onclick="switchAdminTab('komisyon', this)">Komisyon &amp; Bedeller</button>
        </li>
        <li class="nav-menu-item <?= $activeTab === 'escrow' ? 'active' : '' ?>">
          <button type="button" data-tab="escrow" onclick="switchAdminTab('escrow', this)">Emanet &amp; Teminat</button>
        </li>
        <li class="nav-menu-item <?= $activeTab === 'denetim' ? 'active' : '' ?>">
          <button type="button" data-tab="denetim" onclick="switchAdminTab('denetim', this)">Denetim Kayıtları</button>
        </li>
      </ul>
    </div>

    <div>
      <div class="sidebar-section-title">Sistem &amp; Yetki</div>
      <ul class="nav-menu">
        <li class="nav-menu-item <?= $activeTab === 'kategoriler' ? 'active' : '' ?>">
          <button type="button" data-tab="kategoriler" onclick="switchAdminTab('kategoriler', this)">Kategoriler</button>
        </li>
        <li class="nav-menu-item <?= $activeTab === 'kullanicilar' ? 'active' : '' ?>">
          <button type="button" data-tab="kullanicilar" onclick="switchAdminTab('kullanicilar', this)">Kullanıcılar &amp; KYC</button>
        </li>
        <li class="nav-menu-item <?= $activeTab === 'yetkiler' ? 'active' : '' ?>">
          <button type="button" data-tab="yetkiler" onclick="switchAdminTab('yetkiler', this)">Rol &amp; Yetki (RBAC)</button>
        </li>
        <li class="nav-menu-item <?= $activeTab === 'ayarlar' ? 'active' : '' ?>">
          <button type="button" data-tab="ayarlar" onclick="switchAdminTab('ayarlar', this)">Sistem Ayarları</button>
        </li>
        <li class="nav-menu-item">
          <a href="?role=buyer&tab=canli">Canlı Salonları İncele</a>
        </li>
        <li class="nav-menu-item">
          <a href="?role=buyer&tab=vip">VIP Salonu İncele</a>
        </li>
        <li class="nav-menu-item">
          <a href="?role=vitrin">Vitrini Görüntüle</a>
        </li>
      </ul>
    </div>

    <form method="POST" action="index.php" style="margin:10px 0 6px 0;">
      <input type="hidden" name="action" value="logout">
      <button type="submit" class="btn-logout-luxury" style="width:100%; display:flex; align-items:center; justify-content:center; gap:8px; padding:9px 14px; font-size:0.8rem; cursor:pointer;">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
        <span>Çıkış Yap</span>
      </button>
    </form>
  </aside>

  <!-- Ana İçerik Alanı -->
  <main class="main-content">

    <!-- ====================================================================
         1. TAB: GENEL BAKIŞ (YÖNETİM KONSOLU)
         ==================================================================== -->
    <div id="admTab_konsol" class="tab-content <?= $activeTab === 'konsol' ? 'active' : '' ?>">
      <div class="page-breadcrumb">ADMIN / YÖNETİM KONSOLU</div>
      <h1 class="page-heading">Yönetim Konsolu</h1>
      <p class="page-subheading">Müzayede operasyonları, finansal akış ve yetki yönetimi.</p>

      <div class="kpi-grid" style="grid-template-columns:repeat(4, 1fr);">
        <div class="kpi-card highlight" style="cursor:pointer;" onclick="switchAdminTab('moderasyon')">
          <div class="kpi-label">Aktif Lotlar</div>
          <div class="kpi-value"><?= $activeLotCount ?></div>
        </div>
        <div class="kpi-card" style="cursor:pointer;" onclick="switchAdminTab('raporlar')">
          <div class="kpi-label">Ekspertiz Raporu</div>
          <div class="kpi-value"><?= count($reports) ?></div>
        </div>
        <div class="kpi-card" style="cursor:pointer;" onclick="switchAdminTab('kullanicilar')">
          <div class="kpi-label">Kayıtlı Kullanıcı</div>
          <div class="kpi-value"><?= count($users) ?></div>
        </div>
        <div class="kpi-card" style="cursor:pointer;" onclick="switchAdminTab('ihtilaf_cozumu')">
          <div class="kpi-label">Açık İhtilaf / İptal</div>
          <div class="kpi-value" style="color:#E2C992;"><?= $openDisputeCount + $cancelledCount ?></div>
        </div>
      </div>

      <!-- Düzenli 3 Sütunlu Hızlı Erişim Kartları -->
      <div style="display:grid; grid-template-columns:repeat(3, 1fr); gap:16px;">
        <div class="panel-card" style="margin-bottom:0;">
          <div style="font-size:0.68rem; color:#C8AA6E; letter-spacing:1.4px; text-transform:uppercase; margin-bottom:6px;">Operasyon</div>
          <h3 class="panel-title" style="margin-bottom:6px;">Müzayede &amp; Ekspertiz</h3>
          <p style="font-size:0.79rem; color:#A39B8F; margin-bottom:14px;">Canlı salonları yönetin, rapor ve akreditasyonları onaylayın.</p>
          <div style="display:flex; flex-direction:column; gap:8px;">
            <button type="button" class="btn-gold" style="width:100%; justify-content:space-between;" onclick="switchAdminTab('moderasyon')">
              <span>Müzayede Yönetimi</span><span>&rarr;</span>
            </button>
            <button type="button" class="btn-outline-gold" style="width:100%; display:flex; justify-content:space-between;" onclick="switchAdminTab('raporlar')">
              <span>Ekspertiz Raporları</span><span>&rarr;</span>
            </button>
            <button type="button" class="btn-outline-gold" style="width:100%; display:flex; justify-content:space-between;" onclick="switchAdminTab('akreditasyon')">
              <span>Eksper Akreditasyonu</span><span>&rarr;</span>
            </button>
          </div>
        </div>

        <div class="panel-card" style="margin-bottom:0;">
          <div style="font-size:0.68rem; color:#C8AA6E; letter-spacing:1.4px; text-transform:uppercase; margin-bottom:6px;">Finans &amp; Güvenlik</div>
          <h3 class="panel-title" style="margin-bottom:6px;">Emanet, Komisyon &amp; Tahkim</h3>
          <p style="font-size:0.79rem; color:#A39B8F; margin-bottom:14px;">Bloke hesapları, komisyon oranlarını ve ihtilafları inceleyin.</p>
          <div style="display:flex; flex-direction:column; gap:8px;">
            <button type="button" class="btn-outline-gold" style="width:100%; display:flex; justify-content:space-between;" onclick="switchAdminTab('escrow')">
              <span>Emanet &amp; Teminat</span><span>&rarr;</span>
            </button>
            <button type="button" class="btn-outline-gold" style="width:100%; display:flex; justify-content:space-between;" onclick="switchAdminTab('komisyon')">
              <span>Komisyon &amp; Bedeller</span><span>&rarr;</span>
            </button>
            <button type="button" class="btn-outline-gold" style="width:100%; display:flex; justify-content:space-between;" onclick="switchAdminTab('iptal_yonetimi')">
              <span>Pey &amp; İlan İptal Masası</span><span>&rarr;</span>
            </button>
          </div>
        </div>

        <div class="panel-card" style="margin-bottom:0;">
          <div style="font-size:0.68rem; color:#C8AA6E; letter-spacing:1.4px; text-transform:uppercase; margin-bottom:6px;">Sistem</div>
          <h3 class="panel-title" style="margin-bottom:6px;">Kullanıcı, Rol &amp; Denetim</h3>
          <p style="font-size:0.79rem; color:#A39B8F; margin-bottom:14px;">Yetkili personel tanımlayın, KYC ve log kayıtlarını denetleyin.</p>
          <div style="display:flex; flex-direction:column; gap:8px;">
            <button type="button" class="btn-outline-gold" style="width:100%; display:flex; justify-content:space-between;" onclick="switchAdminTab('yetkiler')">
              <span>Rol &amp; Yetki (RBAC)</span><span>&rarr;</span>
            </button>
            <button type="button" class="btn-outline-gold" style="width:100%; display:flex; justify-content:space-between;" onclick="switchAdminTab('denetim')">
              <span>Denetim Kayıtları</span><span>&rarr;</span>
            </button>
            <button type="button" class="btn-outline-gold" style="width:100%; display:flex; justify-content:space-between;" onclick="switchAdminTab('ayarlar')">
              <span>Sistem &amp; Personel Ayarları</span><span>&rarr;</span>
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- ====================================================================
         2. TAB: MÜZAYEDE YÖNETİMİ
         ==================================================================== -->
    <div id="admTab_moderasyon" class="tab-content <?= $activeTab === 'moderasyon' ? 'active' : '' ?>">
      <div class="page-breadcrumb">ADMIN / MÜZAYEDE YÖNETİMİ</div>
      <div style="display:flex; justify-content:space-between; align-items:flex-end; flex-wrap:wrap; gap:12px; margin-bottom:20px;">
        <div>
          <h1 class="page-heading" style="margin-bottom:4px;">Müzayede Yönetimi</h1>
          <p class="page-subheading" style="margin:0;">Oturumları başlatın, duraklatın, zamanlayın ve lot adımlarını düzenleyin.</p>
        </div>
        <form method="POST" style="margin:0;">
          <input type="hidden" name="action" value="finalize_expired_auctions">
          <button type="submit" class="btn-gold">Süresi Dolanları Kapat</button>
        </form>
      </div>

      <!-- Müzayede Salonları -->
      <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(310px, 1fr)); gap:16px; margin-bottom:22px;">
        <?php foreach ($auctions as $auc):
          $aId = (int)($auc['id'] ?? 0);
          $aTitle = (string)($auc['baslik'] ?? "Müzayede #{$aId}");
          $aStatus = strtoupper((string)($auc['durum'] ?? 'PLANLANDI'));
          $isLive = in_array($aStatus, ['CANLI', 'AKTIF'], true);
          $isEnded = in_array($aStatus, ['SONLANDI', 'KAPANDI', 'TAMAMLANDI'], true);
          $defaultDt = !empty($auc['baslangicZamani']) ? date('Y-m-d\TH:i', strtotime((string)$auc['baslangicZamani'])) : date('Y-m-d\TH:i', strtotime('+1 hour'));
        ?>
          <div class="panel-card" style="margin-bottom:0; padding:18px; display:flex; flex-direction:column; justify-content:space-between; gap:12px;">
            <div>
              <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:8px;">
                <span style="font-size:0.7rem; color:#C8AA6E; letter-spacing:0.6px;">SALON #<?= $aId ?></span>
                <span class="badge <?= $isLive ? 'badge-success' : ($isEnded ? 'badge-danger' : 'badge-gold') ?>">
                  <?= htmlspecialchars($aStatus) ?>
                </span>
              </div>
              <div style="font-family:var(--font-serif); font-size:1.12rem; color:#F5F0E6; margin-bottom:8px;"><?= htmlspecialchars($aTitle) ?></div>
              <div style="font-size:0.76rem; color:#A39B8F; line-height:1.5;">
                Başlangıç: <?= !empty($auc['baslangicZamani']) ? date('d.m.Y H:i', strtotime((string)$auc['baslangicZamani'])) : '—' ?><br>
                Bitiş: <?= !empty($auc['bitisZamani']) ? date('d.m.Y H:i', strtotime((string)$auc['bitisZamani'])) : '—' ?>
              </div>
            </div>

            <div>
              <div style="display:grid; grid-template-columns:1fr 1fr 1fr; gap:6px; margin-bottom:8px;">
                <form method="POST" style="margin:0;">
                  <input type="hidden" name="action" value="control_auction_state">
                  <input type="hidden" name="auction_id" value="<?= $aId ?>">
                  <input type="hidden" name="auction_action" value="start">
                  <button type="submit" class="btn-outline-gold btn-sm" style="width:100%;">Başlat</button>
                </form>
                <form method="POST" style="margin:0;">
                  <input type="hidden" name="action" value="control_auction_state">
                  <input type="hidden" name="auction_id" value="<?= $aId ?>">
                  <input type="hidden" name="auction_action" value="pause">
                  <button type="submit" class="btn-table-action" style="width:100%;">Duraklat</button>
                </form>
                <form method="POST" style="margin:0;" onsubmit="return confirm('Bu müzayedeyi sonlandırmak istediğinize emin misiniz?');">
                  <input type="hidden" name="action" value="control_auction_state">
                  <input type="hidden" name="auction_id" value="<?= $aId ?>">
                  <input type="hidden" name="auction_action" value="end">
                  <button type="submit" class="btn-table-action" style="width:100%; color:#DDA3A3;">Sonlandır</button>
                </form>
              </div>
              <form method="POST" style="margin:0; display:flex; gap:6px;">
                <input type="hidden" name="action" value="control_auction_state">
                <input type="hidden" name="auction_id" value="<?= $aId ?>">
                <input type="hidden" name="auction_action" value="schedule">
                <input type="datetime-local" name="scheduled_start" value="<?= $defaultDt ?>" class="form-control" style="padding:5px 8px; font-size:0.74rem;">
                <button type="submit" class="btn-table-action" style="white-space:nowrap;">Planla</button>
              </form>
            </div>
          </div>
        <?php endforeach; ?>
      </div>

      <!-- Lot Zamanlama ve Teklif Adımı -->
      <div class="panel-card">
        <div class="panel-header">
          <h3 class="panel-title">Lot Zamanlama &amp; Teklif Adımı</h3>
        </div>
        <form method="POST" id="quickScheduleForm">
          <input type="hidden" name="action" value="configure_lot_schedule">
          <div style="display:grid; grid-template-columns:1.8fr 1fr 1fr; gap:12px; margin-bottom:12px;">
            <div>
              <label class="form-label">Lot</label>
              <select name="lot_id" id="quickScheduleLotSelect" class="form-control" onchange="updateQuickScheduleForm(this.value)">
                <?php foreach ($lots as $idx => $l):
                  $lStart = !empty($l['baslangicZamani']) ? date('Y-m-d\TH:i', strtotime((string)$l['baslangicZamani'])) : date('Y-m-d\TH:i');
                  $lEnd   = !empty($l['bitisZamani']) ? date('Y-m-d\TH:i', strtotime((string)$l['bitisZamani'])) : date('Y-m-d\TH:i', strtotime('+3 days'));
                ?>
                  <option value="<?= (int)$l['id'] ?>"
                          data-start="<?= $lStart ?>"
                          data-end="<?= $lEnd ?>"
                          data-step="<?= (float)($l['minimumArtisTutari'] ?? 1500) ?>"
                          data-steptype="<?= htmlspecialchars((string)($l['teklifAdimTipi'] ?? 'SABIT')) ?>"
                          data-startprice="<?= (float)($l['baslangicFiyati'] ?? 0) ?>"
                          data-reserve="<?= (float)($l['rezervFiyat'] ?? 0) ?>"
                          <?= $idx === 0 ? 'selected' : '' ?>>
                    [<?= htmlspecialchars((string)($l['lotKodu'] ?? '')) ?>] <?= htmlspecialchars((string)($l['baslik'] ?? '')) ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>
            <div>
              <label class="form-label">Başlangıç</label>
              <input type="datetime-local" name="baslangic_zamani" id="quickScheduleStart" class="form-control" required>
            </div>
            <div>
              <label class="form-label">Bitiş</label>
              <input type="datetime-local" name="bitis_zamani" id="quickScheduleEnd" class="form-control" required>
            </div>
          </div>

          <div style="display:grid; grid-template-columns:1fr 1fr 1fr 1fr auto; gap:12px; align-items:flex-end;">
            <div>
              <label class="form-label">Teklif Adımı (₺)</label>
              <input type="number" step="100" min="50" name="minimum_artis_tutari" id="quickScheduleStep" class="form-control" required>
            </div>
            <div>
              <label class="form-label">Adım Tipi</label>
              <select name="teklif_adim_tipi" id="quickScheduleStepType" class="form-control">
                <option value="SABIT">Sabit</option>
                <option value="KADEMELI">Kademeli</option>
              </select>
            </div>
            <div>
              <label class="form-label">Başlangıç Fiyatı (₺)</label>
              <input type="number" step="500" name="baslangic_fiyati" id="quickScheduleStartPrice" class="form-control">
            </div>
            <div>
              <label class="form-label">Rezerv Fiyat (₺)</label>
              <input type="number" step="500" name="rezerv_fiyat" id="quickScheduleReservePrice" class="form-control">
            </div>
            <button type="submit" class="btn-gold">Kaydet</button>
          </div>
        </form>
      </div>

      <!-- Katalog Lotları Tablosu -->
      <div class="panel-card" style="padding:0; overflow:hidden; margin-bottom:0;">
        <div style="padding:14px 18px; border-bottom:1px solid var(--border-color); display:flex; justify-content:space-between; align-items:center;">
          <h3 class="panel-title" style="margin:0;">Katalog Lotları (<?= count($lots) ?>)</h3>
          <span style="font-size:0.72rem; color:#C8AA6E;">Sağa kaydırarak tüm sütunları görün &rarr;</span>
        </div>
        <div class="table-scroll-x">
          <table class="data-table" style="min-width:920px;">
            <thead>
              <tr>
                <th style="padding-left:18px;">Kod</th>
                <th>Eser</th>
                <th>Kategori</th>
                <th>Başlangıç</th>
                <th>Güncel Teklif</th>
                <th>Adım</th>
                <th>Durum</th>
                <th style="padding-right:18px; text-align:right;">İşlem</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($lots as $lot): ?>
                <tr>
                  <td style="padding-left:18px;"><span class="badge badge-gold"><?= htmlspecialchars((string)($lot['lotKodu'] ?? '')) ?></span></td>
                  <td style="color:#F5F0E6; font-weight:500;"><?= htmlspecialchars((string)($lot['baslik'] ?? '')) ?></td>
                  <td style="color:#A39B8F;"><?= htmlspecialchars((string)($lot['kategoriAdi'] ?? '')) ?></td>
                  <td><?= $formatPrice((float)($lot['baslangicFiyati'] ?? 0)) ?></td>
                  <td style="color:#E2C992; font-weight:600;"><?= $formatPrice((float)($lot['mevcutEnYuksekTeklif'] ?? 0)) ?></td>
                  <td>+<?= $formatPrice((float)($lot['minimumArtisTutari'] ?? 0)) ?></td>
                  <td><span class="badge <?= ($lot['durum'] ?? '') === 'ACIK_ARTIRMADA' ? 'badge-success' : 'badge-warning' ?>"><?= htmlspecialchars((string)($lot['durum'] ?? '')) ?></span></td>
                  <td style="padding-right:18px; text-align:right; white-space:nowrap;">
                    <button type="button" class="btn-outline-gold btn-sm" onclick='openEditLotModal(<?= json_encode($lot, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>)'>Düzenle</button>
                    <form method="POST" style="display:inline; margin-left:4px;" onsubmit="return confirm('Son peyi iptal etmek istediğinize emin misiniz?');">
                      <input type="hidden" name="action" value="cancel_bid">
                      <input type="hidden" name="lot_id" value="<?= (int)$lot['id'] ?>">
                      <input type="hidden" name="reason" value="Admin kural dışı pey iptali">
                      <button type="submit" class="btn-table-action" style="color:#DDA3A3;">Son Peyi İptal</button>
                    </form>
                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- ====================================================================
         3. TAB: EKSPERTİZ RAPORLARI
         ==================================================================== -->
    <div id="admTab_raporlar" class="tab-content <?= $activeTab === 'raporlar' ? 'active' : '' ?>">
      <div class="page-breadcrumb">ADMIN / EKSPERTİZ RAPORLARI</div>
      <h1 class="page-heading">Ekspertiz Raporları</h1>
      <p class="page-subheading">Uzmanlar tarafından düzenlenen raporları onaylayın veya reddedin.</p>

      <div style="display:flex; flex-direction:column; gap:14px;">
        <?php foreach ($reports as $rep):
          $rId = (int)($rep['id'] ?? 0);
          $rStatus = strtoupper((string)($rep['denetimDurumu'] ?? 'ONAYLANDI'));
          $pRec = $productMap[(int)($rep['urunId'] ?? 0)] ?? null;
        ?>
          <div class="panel-card" style="margin-bottom:0;">
            <div style="display:flex; justify-content:space-between; align-items:flex-start; flex-wrap:wrap; gap:12px; margin-bottom:12px;">
              <div>
                <span class="badge badge-gold"><?= htmlspecialchars((string)($rep['raporNo'] ?? "REP-{$rId}")) ?></span>
                <h3 style="font-family:var(--font-serif); font-size:1.18rem; color:#F5F0E6; margin:6px 0 2px 0;">
                  <?= htmlspecialchars((string)($pRec['baslik'] ?? ('Ürün #' . ($rep['urunId'] ?? '')))) ?>
                </h3>
                <div style="font-size:0.78rem; color:#A39B8F;">
                  Önerilen Değer: <strong style="color:#E2C992;"><?= $formatPrice((float)($rep['onerilenPiyasaDegeri'] ?? 0)) ?></strong> · <?= htmlspecialchars((string)($rep['orijinallikNotu'] ?? '')) ?>
                </div>
              </div>
              <span class="badge <?= $rStatus === 'REDDEDILDI' ? 'badge-danger' : 'badge-success' ?>"><?= htmlspecialchars($rStatus) ?></span>
            </div>

            <?php if (!empty($rep['maddeler'])): ?>
              <ul style="margin:0 0 14px 18px; color:#A39B8F; font-size:0.79rem; line-height:1.55;">
                <?php foreach ($rep['maddeler'] as $m): ?>
                  <li><?= htmlspecialchars((string)$m) ?></li>
                <?php endforeach; ?>
              </ul>
            <?php endif; ?>

            <form method="POST" style="display:flex; gap:8px; flex-wrap:wrap; align-items:center; padding-top:10px; border-top:1px solid var(--border-color);">
              <input type="hidden" name="action" value="review_appraisal_report">
              <input type="hidden" name="report_id" value="<?= $rId ?>">
              <input type="text" name="admin_note" class="form-control" placeholder="İnceleme notu (opsiyonel)..." style="flex:1; min-width:200px;">
              <button type="submit" name="decision" value="approve" class="btn-gold btn-sm">Onayla</button>
              <button type="submit" name="decision" value="reject" class="btn-table-action" style="color:#DDA3A3;">Reddet</button>
            </form>
          </div>
        <?php endforeach; ?>
      </div>
    </div>

    <!-- ====================================================================
         4. TAB: DOSYA & BELGE İNCELEME (HEYET)
         ==================================================================== -->
    <div id="admTab_heyet_inceleme" class="tab-content <?= $activeTab === 'heyet_inceleme' ? 'active' : '' ?>">
      <div class="page-breadcrumb">ADMIN / DOSYA &amp; BELGE İNCELEME</div>
      <h1 class="page-heading">Rapor &amp; Belge İnceleme</h1>
      <p class="page-subheading">Ekspertiz dosyalarını, sertifikaları ve kurul kararlarını yönetin.</p>

      <div style="display:flex; gap:8px; flex-wrap:wrap; margin-bottom:16px;">
        <?php foreach ($reports as $idx => $rep): $riId = (int)($rep['id'] ?? 0); ?>
          <button type="button" class="dossier-select-btn <?= $idx === 0 ? 'btn-gold-pill' : 'btn-dark-pill' ?>" style="padding:6px 14px; font-size:0.78rem; cursor:pointer;" onclick="selectInspectionDossier(<?= $riId ?>, this)">
            <?= htmlspecialchars((string)($rep['raporNo'] ?? "Dosya #{$riId}")) ?>
          </button>
        <?php endforeach; ?>
      </div>

      <?php foreach ($reports as $idx => $rep):
        $riId = (int)($rep['id'] ?? 0);
        $pRec = $productMap[(int)($rep['urunId'] ?? 0)] ?? null;
      ?>
        <div id="dossier_box_<?= $riId ?>" class="dossier-box panel-card" style="display:<?= $idx === 0 ? 'block' : 'none' ?>;">
          <div class="panel-header">
            <div>
              <h3 class="panel-title"><?= htmlspecialchars((string)($pRec['baslik'] ?? ('Eser #' . ($rep['urunId'] ?? '')))) ?></h3>
              <div style="font-size:0.76rem; color:#A39B8F;"><?= htmlspecialchars((string)($rep['raporNo'] ?? '')) ?> · Değerleme: <?= $formatPrice((float)($rep['onerilenPiyasaDegeri'] ?? 0)) ?></div>
            </div>
            <span class="badge badge-gold"><?= htmlspecialchars((string)($rep['denetimDurumu'] ?? 'ONAYLANDI')) ?></span>
          </div>

          <form method="POST">
            <input type="hidden" name="action" value="review_appraisal_dossier">
            <input type="hidden" name="report_id" value="<?= $riId ?>">
            <div class="form-group">
              <label class="form-label">Kurul Değerlendirme Notu</label>
              <textarea name="committee_note" class="form-control" rows="2" placeholder="Belge ve kondisyon inceleme notu..."></textarea>
            </div>
            <div style="display:flex; gap:8px;">
              <button type="submit" name="decision" value="APPROVE" class="btn-gold btn-sm">Dosyayı Onayla</button>
              <button type="submit" name="decision" value="REVISION" class="btn-outline-gold btn-sm">Revizyon İste</button>
              <button type="submit" name="decision" value="REJECT" class="btn-table-action" style="color:#DDA3A3;">Reddet</button>
            </div>
          </form>
        </div>
      <?php endforeach; ?>
    </div>

    <!-- ====================================================================
         5. TAB: EKSPERTİZ ATAMA
         ==================================================================== -->
    <div id="admTab_ekspertiz" class="tab-content <?= $activeTab === 'ekspertiz' ? 'active' : '' ?>">
      <div class="page-breadcrumb">ADMIN / EKSPERTİZ ATAMA</div>
      <h1 class="page-heading">Ekspertiz Atama</h1>
      <p class="page-subheading">Gelen eserlere uzman ataması yapın ve talepleri takip edin.</p>

      <div class="panel-card" style="padding:0; overflow:hidden;">
        <div class="table-scroll-x">
          <table class="data-table" style="min-width:660px;">
            <thead>
              <tr>
                <th style="padding-left:18px;">Talep No</th>
                <th>Lot</th>
                <th>Eser</th>
                <th>Durum</th>
                <th style="padding-right:18px; text-align:right;">Uzman Ata</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($appraisalRequests as $req):
                $pRec = $productMap[(int)($req['urunId'] ?? 0)] ?? null;
              ?>
                <tr>
                  <td style="padding-left:18px;"><span class="badge badge-gold"><?= htmlspecialchars((string)($req['talepNo'] ?? '')) ?></span></td>
                  <td style="color:#E2C992;"><?= htmlspecialchars((string)($req['lotKodu'] ?? '')) ?></td>
                  <td style="color:#F5F0E6; font-weight:500;"><?= htmlspecialchars((string)($pRec['baslik'] ?? ('Ürün #' . ($req['urunId'] ?? '')))) ?></td>
                  <td><span class="badge <?= ($req['durum'] ?? '') === 'TAMAMLANDI' ? 'badge-success' : 'badge-warning' ?>"><?= htmlspecialchars((string)($req['durum'] ?? 'BEKLEYEN')) ?></span></td>
                  <td style="padding-right:18px; text-align:right;">
                    <form method="POST" style="display:inline-flex; gap:6px; align-items:center; margin:0;">
                      <input type="hidden" name="action" value="assign_expert">
                      <input type="hidden" name="request_id" value="<?= (int)($req['id'] ?? 0) ?>">
                      <select name="expert_id" class="form-control" style="width:auto; padding:4px 8px; font-size:0.76rem;">
                        <option value="4">Burak Şahin (Baş Eksper)</option>
                      </select>
                      <button type="submit" class="btn-outline-gold btn-sm">Ata</button>
                    </form>
                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- ====================================================================
         6. TAB: EKSPER AKREDİTASYONU
         ==================================================================== -->
    <div id="admTab_akreditasyon" class="tab-content <?= $activeTab === 'akreditasyon' ? 'active' : '' ?>">
      <div class="page-breadcrumb">ADMIN / AKREDİTASYON</div>
      <h1 class="page-heading">Eksper Akreditasyonu</h1>
      <p class="page-subheading">Bağımsız uzman ve kurum yetki belgelerini yönetin.</p>

      <div style="display:flex; gap:8px; margin-bottom:16px; flex-wrap:wrap;">
        <button type="button" class="accreditation-filter-btn btn-gold-pill" style="padding:6px 14px; font-size:0.78rem; cursor:pointer;" onclick="filterAccreditation('all', this)">Tümü</button>
        <button type="button" class="accreditation-filter-btn btn-dark-pill" style="padding:6px 14px; font-size:0.78rem; cursor:pointer;" onclick="filterAccreditation('ONAYLI', this)">Onaylı</button>
        <button type="button" class="accreditation-filter-btn btn-dark-pill" style="padding:6px 14px; font-size:0.78rem; cursor:pointer;" onclick="filterAccreditation('BEKLEMEDE', this)">Bekleyen</button>
      </div>

      <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(340px, 1fr)); gap:16px;">
        <?php if (empty($expertAccreditations)): ?>
          <div class="panel-card" style="color:#A39B8F;">Kayıtlı akreditasyon dosyası bulunmuyor.</div>
        <?php else: ?>
          <?php foreach ($expertAccreditations as $acc):
            $accStatus = strtoupper((string)($acc['durum'] ?? 'BEKLEMEDE'));
          ?>
            <div class="panel-card accreditation-card" data-type="<?= htmlspecialchars((string)($acc['tip'] ?? '')) ?>" data-status="<?= htmlspecialchars($accStatus) ?>" style="margin-bottom:0;">
              <div style="display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:10px;">
                <div>
                  <div style="font-family:var(--font-serif); font-size:1.12rem; color:#F5F0E6;"><?= htmlspecialchars((string)($acc['ad'] ?? $acc['kurumAdi'] ?? 'Eksper')) ?></div>
                  <div style="font-size:0.76rem; color:#A39B8F;"><?= htmlspecialchars((string)($acc['uzmanlikAlani'] ?? 'Antika & Horoloji')) ?></div>
                </div>
                <span class="badge <?= $accStatus === 'ONAYLI' ? 'badge-success' : 'badge-gold' ?>"><?= htmlspecialchars($accStatus) ?></span>
              </div>

              <form method="POST" style="display:flex; gap:6px; flex-wrap:wrap; margin-top:12px; padding-top:10px; border-top:1px solid var(--border-color);">
                <input type="hidden" name="action" value="manage_expert_accreditation">
                <input type="hidden" name="accreditation_id" value="<?= (int)($acc['id'] ?? 0) ?>">
                <select name="level" class="form-control" style="flex:1; min-width:140px; padding:5px 8px; font-size:0.76rem;">
                  <option value="A_SEVIYESI_BAS_EKSPER">A Seviyesi — Baş Eksper</option>
                  <option value="B_SEVIYESI_KIDEMLI">B Seviyesi — Kıdemli</option>
                </select>
                <button type="submit" name="decision" value="APPROVE" class="btn-gold btn-sm">Onayla</button>
                <button type="submit" name="decision" value="SUSPEND" class="btn-table-action" style="color:#DDA3A3;">Askıya Al</button>
              </form>
            </div>
          <?php endforeach; ?>
        <?php endif; ?>
      </div>
    </div>

    <!-- ====================================================================
         7. TAB: PEY & İLAN İPTAL MASASI
         ==================================================================== -->
    <div id="admTab_iptal_yonetimi" class="tab-content <?= $activeTab === 'iptal_yonetimi' ? 'active' : '' ?>">
      <div class="page-breadcrumb">ADMIN / İPTAL MASASI</div>
      <h1 class="page-heading">Pey &amp; İlan İptali</h1>
      <p class="page-subheading">Kural dışı peyleri geri alın veya sorunlu lotları yayından kaldırın.</p>

      <div style="display:flex; gap:8px; margin-bottom:16px;">
        <button type="button" id="btn_cancel_subtab_peyler" onclick="switchCancellationSubTab('peyler')" class="cancel-subtab-btn btn-gold-pill" style="padding:6px 15px; font-size:0.78rem; cursor:pointer;">Peyler (<?= count($bids) ?>)</button>
        <button type="button" id="btn_cancel_subtab_ilanlar" onclick="switchCancellationSubTab('ilanlar')" class="cancel-subtab-btn btn-dark-pill" style="padding:6px 15px; font-size:0.78rem; cursor:pointer;">Lotlar &amp; İlanlar (<?= count($lots) ?>)</button>
      </div>

      <!-- Alt Sekme 1: Peyler -->
      <div id="cancel_pane_peyler" class="cancel-subtab-pane panel-card" style="padding:0; overflow:hidden;">
        <div class="table-scroll-x">
          <table class="data-table" style="min-width:820px;">
            <thead>
              <tr>
                <th style="padding-left:18px;">No</th>
                <th>Eser</th>
                <th>Alıcı</th>
                <th>Tutar</th>
                <th>Durum</th>
                <th style="padding-right:18px;">İşlem</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach (array_reverse(array_slice($bids, -15, 15)) as $bid):
                $bLot = $lotMap[(int)($bid['lotId'] ?? 0)] ?? null;
                $isCancelled = ($bid['durum'] ?? '') === 'IPTAL_EDILDI';
              ?>
                <tr style="<?= $isCancelled ? 'opacity:0.55;' : '' ?>">
                  <td style="padding-left:18px; font-family:monospace;">#<?= (int)($bid['id'] ?? 0) ?></td>
                  <td style="color:#F5F0E6;"><?= htmlspecialchars((string)($bLot['baslik'] ?? ('Lot #' . ($bid['lotId'] ?? '')))) ?></td>
                  <td style="color:#A39B8F;"><?= htmlspecialchars((string)($bid['aliciAnonimKod'] ?? 'Anonim')) ?></td>
                  <td style="color:#E2C992; font-weight:600;"><?= $formatPrice((float)($bid['teklifTutari'] ?? 0)) ?></td>
                  <td><span class="badge <?= $isCancelled ? 'badge-danger' : 'badge-gold' ?>"><?= $isCancelled ? 'İptal Edildi' : 'Geçerli' ?></span></td>
                  <td style="padding-right:18px;">
                    <?php if ($isCancelled): ?>
                      <span style="font-size:0.74rem; color:#8C8478;"><?= htmlspecialchars((string)($bid['iptalSebebi'] ?? 'İptal')) ?></span>
                    <?php else: ?>
                      <form method="POST" style="display:flex; gap:6px; margin:0;" onsubmit="return confirm('Bu peyi iptal etmek istediğinize emin misiniz?');">
                        <input type="hidden" name="action" value="admin_cancel_bid">
                        <input type="hidden" name="bid_id" value="<?= (int)($bid['id'] ?? 0) ?>">
                        <select name="reason" class="form-control" style="padding:4px 8px; font-size:0.74rem; max-width:190px;">
                          <option value="Kural dışı teklif">Kural dışı teklif</option>
                          <option value="Hatalı tutar girişi">Hatalı tutar girişi</option>
                          <option value="Bot / yapay artış şüphesi">Yapay artış şüphesi</option>
                        </select>
                        <button type="submit" class="btn-table-action" style="color:#DDA3A3; white-space:nowrap;">İptal Et</button>
                      </form>
                    <?php endif; ?>
                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Alt Sekme 2: Lotlar -->
      <div id="cancel_pane_ilanlar" class="cancel-subtab-pane" style="display:none;">
        <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(300px, 1fr)); gap:14px;">
          <?php foreach ($lots as $lotItem):
            $isLotCancelled = ($lotItem['durum'] ?? '') === 'IPTAL_EDILDI';
          ?>
            <div class="panel-card" style="margin-bottom:0; padding:16px;">
              <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:8px;">
                <span class="badge badge-gold"><?= htmlspecialchars((string)($lotItem['lotKodu'] ?? '')) ?></span>
                <span class="badge <?= $isLotCancelled ? 'badge-danger' : 'badge-success' ?>"><?= htmlspecialchars((string)($lotItem['durum'] ?? '')) ?></span>
              </div>
              <div style="font-family:var(--font-serif); font-size:1.08rem; color:#F5F0E6; margin-bottom:10px;"><?= htmlspecialchars((string)($lotItem['baslik'] ?? '')) ?></div>
              <?php if (!$isLotCancelled): ?>
                <form method="POST" style="display:flex; gap:6px; margin:0;" onsubmit="return confirm('İlanı yayından kaldırmak istediğinize emin misiniz?');">
                  <input type="hidden" name="action" value="admin_cancel_lot">
                  <input type="hidden" name="lot_id" value="<?= (int)$lotItem['id'] ?>">
                  <select name="reason" class="form-control" style="padding:5px 8px; font-size:0.75rem;">
                    <option value="Eser hasarı / kondisyon kaybı">Eser hasarı</option>
                    <option value="Mülkiyet / hukuki ihtilaf">Hukuki ihtilaf</option>
                    <option value="Orijinallik şüphesi">Orijinallik şüphesi</option>
                  </select>
                  <button type="submit" class="btn-table-action" style="color:#DDA3A3; white-space:nowrap;">Kaldır</button>
                </form>
              <?php else: ?>
                <div style="font-size:0.75rem; color:#8C8478;">Yayından kaldırıldı.</div>
              <?php endif; ?>
            </div>
          <?php endforeach; ?>
        </div>
      </div>
    </div>

    <!-- ====================================================================
         8. TAB: İHTİLAF & TAHKİM
         ==================================================================== -->
    <div id="admTab_ihtilaf_cozumu" class="tab-content <?= $activeTab === 'ihtilaf_cozumu' ? 'active' : '' ?>">
      <div class="page-breadcrumb">ADMIN / İHTİLAF &amp; TAHKİM</div>
      <h1 class="page-heading">İhtilaf &amp; Tahkim</h1>
      <p class="page-subheading">Alıcı ve satıcı arasındaki uyuşmazlık dosyalarını karara bağlayın.</p>

      <?php if (empty($disputes)): ?>
        <div class="panel-card" style="color:#A39B8F;">Açık ihtilaf dosyası bulunmuyor.</div>
      <?php else: ?>
        <div style="display:flex; flex-direction:column; gap:14px;">
          <?php foreach ($disputes as $disp):
            $dId = (int)($disp['id'] ?? 0);
            $dStatus = strtoupper((string)($disp['durum'] ?? 'INCELEMEDE'));
            $isResolved = ($dStatus === 'COZULDU');
          ?>
            <div class="panel-card" style="margin-bottom:0;">
              <div style="display:flex; justify-content:space-between; align-items:flex-start; gap:10px; margin-bottom:8px;">
                <div>
                  <span class="badge badge-gold"><?= htmlspecialchars((string)($disp['talepNo'] ?? "IHT-{$dId}")) ?></span>
                  <h3 style="font-family:var(--font-serif); font-size:1.15rem; color:#F5F0E6; margin:6px 0 2px 0;"><?= htmlspecialchars((string)($disp['konu'] ?? 'Uyuşmazlık')) ?></h3>
                </div>
                <span class="badge <?= $isResolved ? 'badge-success' : 'badge-warning' ?>"><?= htmlspecialchars($dStatus) ?></span>
              </div>
              <p style="font-size:0.8rem; color:#A39B8F; margin-bottom:12px;"><?= htmlspecialchars((string)($disp['aciklama'] ?? '')) ?></p>

              <?php if (!$isResolved): ?>
                <form method="POST" style="display:flex; gap:8px; flex-wrap:wrap; align-items:center; padding-top:10px; border-top:1px solid var(--border-color);">
                  <input type="hidden" name="action" value="resolve_dispute">
                  <input type="hidden" name="dispute_id" value="<?= $dId ?>">
                  <input type="text" name="admin_note" class="form-control" placeholder="Karar gerekçesi..." style="flex:1; min-width:200px;">
                  <button type="submit" name="decision" value="ALICI_HAKLI" class="btn-gold btn-sm">Alıcı Haklı (İade)</button>
                  <button type="submit" name="decision" value="SATICI_HAKLI" class="btn-outline-gold btn-sm">Satıcı Haklı (Aktar)</button>
                </form>
              <?php else: ?>
                <div style="font-size:0.78rem; color:#9CC7B0;">Karar: <?= htmlspecialchars((string)($disp['kararAciklama'] ?? 'Sonuçlandırıldı')) ?></div>
              <?php endif; ?>
            </div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </div>

    <!-- ====================================================================
         9. TAB: KOMİSYON & HİZMET BEDELLERİ
         ==================================================================== -->
    <div id="admTab_komisyon" class="tab-content <?= $activeTab === 'komisyon' ? 'active' : '' ?>">
      <div class="page-breadcrumb">ADMIN / KOMİSYON &amp; BEDELLER</div>
      <h1 class="page-heading">Komisyon &amp; Hizmet Bedelleri</h1>
      <p class="page-subheading">Müzayede, satış ve ekspertiz kesinti oranlarını yapılandırın.</p>

      <form method="POST">
        <input type="hidden" name="action" value="update_commission_definitions">
        <input type="hidden" name="tab" value="komisyon">

        <div style="display:grid; grid-template-columns:repeat(3, 1fr); gap:16px; margin-bottom:18px;">
          <div class="panel-card" style="margin-bottom:0;">
            <h3 class="panel-title" style="margin-bottom:14px;">Müzayede Oranları</h3>
            <div class="form-group">
              <label class="form-label">Alıcı Primi (%)</label>
              <input type="number" step="0.1" name="aliciPrimiYuzde" class="form-control" value="<?= htmlspecialchars((string)($commMuz['aliciPrimiYuzde'] ?? 12.0)) ?>" required>
            </div>
            <div class="form-group">
              <label class="form-label">VIP Alıcı Primi (%)</label>
              <input type="number" step="0.1" name="vipAliciPrimiYuzde" class="form-control" value="<?= htmlspecialchars((string)($commMuz['vipAliciPrimiYuzde'] ?? 8.0)) ?>" required>
            </div>
            <div class="form-group">
              <label class="form-label">Katalog Bedeli (₺)</label>
              <input type="number" step="50" name="katalogSabitBedeli" class="form-control" value="<?= htmlspecialchars((string)($commMuz['katalogSabitBedeli'] ?? 2500.0)) ?>" required>
            </div>
            <div class="form-group" style="margin-bottom:0;">
              <label class="form-label">Yayın Altyapı Bedeli (₺)</label>
              <input type="number" step="50" name="canliYayinHizmetBedeli" class="form-control" value="<?= htmlspecialchars((string)($commMuz['canliYayinHizmetBedeli'] ?? 1200.0)) ?>" required>
            </div>
          </div>

          <div class="panel-card" style="margin-bottom:0;">
            <h3 class="panel-title" style="margin-bottom:14px;">Satış Kesintileri</h3>
            <div class="form-group">
              <label class="form-label">Satıcı Komisyonu (%)</label>
              <input type="number" step="0.1" name="saticiKomisyonYuzde" class="form-control" value="<?= htmlspecialchars((string)($commSat['saticiKomisyonYuzde'] ?? 8.0)) ?>" required>
            </div>
            <div class="form-group">
              <label class="form-label">Yüksek Hacim Komisyonu (%)</label>
              <input type="number" step="0.1" name="yuksekHacimKomisyonYuzde" class="form-control" value="<?= htmlspecialchars((string)($commSat['yuksekHacimKomisyonYuzde'] ?? 4.0)) ?>" required>
            </div>
            <div class="form-group">
              <label class="form-label">Yüksek Hacim Eşiği (₺)</label>
              <input type="number" step="10000" name="yuksekHacimEsigi" class="form-control" value="<?= htmlspecialchars((string)($commSat['yuksekHacimEsigi'] ?? 500000.0)) ?>" required>
            </div>
            <div class="form-group" style="margin-bottom:0;">
              <label class="form-label">Emanet Takas Bedeli (₺)</label>
              <input type="number" step="50" name="emanetTakasSabitBedeli" class="form-control" value="<?= htmlspecialchars((string)($commSat['emanetTakasSabitBedeli'] ?? 750.0)) ?>" required>
            </div>
          </div>

          <div class="panel-card" style="margin-bottom:0;">
            <h3 class="panel-title" style="margin-bottom:14px;">Ekspertiz Bedelleri</h3>
            <div class="form-group">
              <label class="form-label">Standart Ekspertiz (₺)</label>
              <input type="number" step="50" name="standartEkspertizSabitBedeli" class="form-control" value="<?= htmlspecialchars((string)($commExp['standartEkspertizSabitBedeli'] ?? 1500.0)) ?>" required>
            </div>
            <div class="form-group">
              <label class="form-label">İleri Laboratuvar (₺)</label>
              <input type="number" step="100" name="ileriLaboratuvarSabitBedeli" class="form-control" value="<?= htmlspecialchars((string)($commExp['ileriLaboratuvarSabitBedeli'] ?? 4500.0)) ?>" required>
            </div>
            <div class="form-group">
              <label class="form-label">Sertifika Bedeli (₺)</label>
              <input type="number" step="50" name="orijinallikSertifikaBedeli" class="form-control" value="<?= htmlspecialchars((string)($commExp['orijinallikSertifikaBedeli'] ?? 1000.0)) ?>" required>
            </div>
            <div style="display:grid; grid-template-columns:1fr 1fr; gap:8px;">
              <div>
                <label class="form-label">Eksper Payı (%)</label>
                <input type="number" step="1" name="eksperHakedisOraniYuzde" class="form-control" value="<?= htmlspecialchars((string)($commExp['eksperHakedisOraniYuzde'] ?? 80.0)) ?>" required>
              </div>
              <div>
                <label class="form-label">Platform (%)</label>
                <input type="number" step="1" name="platformEkspertizPayiYuzde" class="form-control" value="<?= htmlspecialchars((string)($commExp['platformEkspertizPayiYuzde'] ?? 20.0)) ?>" required>
              </div>
            </div>
          </div>
        </div>

        <button type="submit" class="btn-gold">Oranları Kaydet</button>
      </form>
    </div>

    <!-- ====================================================================
         10. TAB: EMANET & TEMİNAT
         ==================================================================== -->
    <div id="admTab_escrow" class="tab-content <?= $activeTab === 'escrow' ? 'active' : '' ?>">
      <div class="page-breadcrumb">ADMIN / EMANET &amp; TEMİNAT</div>
      <h1 class="page-heading">Emanet &amp; Teminat</h1>
      <p class="page-subheading">Bloke edilen pey teminatları ve satıcı hakediş aktarımları.</p>

      <div class="kpi-grid" style="grid-template-columns:repeat(3, 1fr);">
        <div class="kpi-card highlight">
          <div class="kpi-label">Emanette Bloke</div>
          <div class="kpi-value"><?= $formatPrice((float)($escrowSummary['totalBlockedAmount'] ?? 0)) ?></div>
        </div>
        <div class="kpi-card">
          <div class="kpi-label">Aktarılan Tutar</div>
          <div class="kpi-value"><?= $formatPrice((float)($escrowSummary['totalReleasedAmount'] ?? 0)) ?></div>
        </div>
        <div class="kpi-card">
          <div class="kpi-label">İade Edilen</div>
          <div class="kpi-value"><?= $formatPrice((float)($escrowSummary['totalRefundedAmount'] ?? 0)) ?></div>
        </div>
      </div>

      <div class="panel-card" style="padding:0; overflow:hidden; margin-bottom:0;">
        <div class="table-scroll-x">
          <table class="data-table" style="min-width:780px;">
            <thead>
              <tr>
                <th style="padding-left:18px;">Kayıt</th>
                <th>Lot</th>
                <th>Tutar</th>
                <th>Durum</th>
                <th style="padding-right:18px; text-align:right;">İşlem</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($escrowTransactions as $esc):
                $eStatus = strtoupper((string)($esc['durum'] ?? 'BLOKE'));
                $isActiveHold = in_array($eStatus, ['EMANET_BLOKE', 'BLOKE', 'AKTIF'], true);
              ?>
                <tr>
                  <td style="padding-left:18px; font-family:monospace; color:#C8AA6E;"><?= htmlspecialchars((string)($esc['islemNo'] ?? ('ESC-' . ($esc['id'] ?? '')))) ?></td>
                  <td style="color:#F5F0E6;"><?= htmlspecialchars((string)($esc['lotBaslik'] ?? ('Lot #' . ($esc['lotId'] ?? '')))) ?></td>
                  <td style="color:#E2C992; font-weight:600;"><?= $formatPrice((float)($esc['tutar'] ?? 0)) ?></td>
                  <td><span class="badge <?= $isActiveHold ? 'badge-gold' : 'badge-success' ?>"><?= htmlspecialchars($eStatus) ?></span></td>
                  <td style="padding-right:18px; text-align:right; white-space:nowrap;">
                    <?php if ($isActiveHold): ?>
                      <form method="POST" style="display:inline; margin:0;">
                        <input type="hidden" name="action" value="complete_escrow_payout">
                        <input type="hidden" name="escrow_id" value="<?= (int)($esc['id'] ?? 0) ?>">
                        <button type="submit" class="btn-outline-gold btn-sm">Satıcıya Aktar</button>
                      </form>
                      <form method="POST" style="display:inline; margin-left:4px;">
                        <input type="hidden" name="action" value="refund_escrow_hold">
                        <input type="hidden" name="escrow_id" value="<?= (int)($esc['id'] ?? 0) ?>">
                        <button type="submit" class="btn-table-action" style="color:#DDA3A3;">İade Et</button>
                      </form>
                    <?php else: ?>
                      <span style="font-size:0.75rem; color:#8C8478;">Tamamlandı</span>
                    <?php endif; ?>
                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- ====================================================================
         11. TAB: DENETİM KAYITLARI
         ==================================================================== -->
    <div id="admTab_denetim" class="tab-content <?= $activeTab === 'denetim' ? 'active' : '' ?>">
      <div class="page-breadcrumb">ADMIN / DENETİM KAYITLARI</div>
      <div style="display:flex; justify-content:space-between; align-items:flex-end; flex-wrap:wrap; gap:12px; margin-bottom:18px;">
        <div>
          <h1 class="page-heading" style="margin-bottom:4px;">Denetim Kayıtları</h1>
          <p class="page-subheading" style="margin:0;">Zaman damgalı kritik işlem geçmişi.</p>
        </div>
        <input type="text" id="auditSearchInput" onkeyup="filterAuditRows(this.value)" class="form-control" placeholder="İşlem veya kullanıcı ara..." style="width:240px;">
      </div>

      <div class="panel-card" style="padding:0; overflow:hidden; margin-bottom:0;">
        <div class="table-scroll-x">
          <table class="data-table" style="min-width:760px;">
            <thead>
              <tr>
                <th style="padding-left:18px;">Zaman</th>
                <th>Kategori</th>
                <th>İşlem</th>
                <th style="padding-right:18px;">Detay</th>
              </tr>
            </thead>
            <tbody id="auditTableBody">
              <?php foreach (array_slice($auditLogList, 0, 30) as $log): ?>
                <tr class="audit-row">
                  <td style="padding-left:18px; color:#A39B8F; white-space:nowrap; font-size:0.76rem;"><?= htmlspecialchars(substr((string)($log['tarih'] ?? $log['zamanDamgasi'] ?? ''), 0, 19)) ?></td>
                  <td><span class="badge badge-gold"><?= htmlspecialchars((string)($log['kategori'] ?? $log['rol'] ?? 'SISTEM')) ?></span></td>
                  <td style="color:#F5F0E6; font-weight:500; white-space:nowrap;"><?= htmlspecialchars((string)($log['islemTuru'] ?? $log['islemTipi'] ?? 'KAYIT')) ?></td>
                  <td style="padding-right:18px; color:#A39B8F;"><?= htmlspecialchars((string)($log['aciklama'] ?? $log['detay'] ?? '')) ?></td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- ====================================================================
         12. TAB: KATEGORİLER
         ==================================================================== -->
    <div id="admTab_kategoriler" class="tab-content <?= $activeTab === 'kategoriler' ? 'active' : '' ?>">
      <div class="page-breadcrumb">ADMIN / KATEGORİLER</div>
      <h1 class="page-heading">Kategoriler</h1>
      <p class="page-subheading">Platform kategorilerini ve komisyon oranlarını yönetin.</p>

      <div class="panel-card">
        <form method="POST" style="display:grid; grid-template-columns:1.6fr 1fr 1fr 1.6fr auto; gap:10px; align-items:flex-end;">
          <input type="hidden" name="action" value="add_category">
          <div>
            <label class="form-label">Kategori Adı</label>
            <input type="text" name="kategori_adi" class="form-control" placeholder="Örn: Hat Sanatı" required>
          </div>
          <div>
            <label class="form-label">Komisyon (%)</label>
            <input type="number" step="0.5" name="komisyon_orani" class="form-control" value="8.0" required>
          </div>
          <div>
            <label class="form-label">Ekspertiz (₺)</label>
            <input type="number" step="100" name="ekspertiz_ucreti" class="form-control" value="1500" required>
          </div>
          <div>
            <label class="form-label">Zorunlu Nitelikler</label>
            <input type="text" name="zorunlu_nitelikler" class="form-control" placeholder="Dönem, Ölçü, Materyal">
          </div>
          <button type="submit" class="btn-gold">Ekle</button>
        </form>
      </div>

      <div class="panel-card" style="padding:0; overflow:hidden; margin-bottom:0;">
        <div class="table-scroll-x">
          <table class="data-table" style="min-width:640px;">
            <thead>
              <tr>
                <th style="padding-left:18px;">ID</th>
                <th>Kategori</th>
                <th>Komisyon</th>
                <th>Ekspertiz Bedeli</th>
                <th style="padding-right:18px; text-align:right;">İşlem</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($categories as $cat): ?>
                <tr>
                  <td style="padding-left:18px; color:#A39B8F;">#<?= (int)($cat['id'] ?? 0) ?></td>
                  <td style="color:#F5F0E6; font-weight:500;"><?= htmlspecialchars((string)($cat['ad'] ?? '')) ?></td>
                  <td>%<?= htmlspecialchars((string)($cat['komisyonOraniYuzde'] ?? 8.0)) ?></td>
                  <td><?= $formatPrice((float)($cat['sabitEkspertizUcreti'] ?? 1500)) ?></td>
                  <td style="padding-right:18px; text-align:right;">
                    <form method="POST" style="margin:0; display:inline;" onsubmit="return confirm('Kategoriyi silmek istediğinize emin misiniz?');">
                      <input type="hidden" name="action" value="delete_category">
                      <input type="hidden" name="category_id" value="<?= (int)($cat['id'] ?? 0) ?>">
                      <button type="submit" class="btn-table-action" style="color:#DDA3A3;">Sil</button>
                    </form>
                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- ====================================================================
         13. TAB: KULLANICILAR & KYC
         ==================================================================== -->
    <div id="admTab_kullanicilar" class="tab-content <?= $activeTab === 'kullanicilar' ? 'active' : '' ?>">
      <div class="page-breadcrumb">ADMIN / KULLANICILAR</div>
      <h1 class="page-heading">Kullanıcılar &amp; KYC</h1>
      <p class="page-subheading">Üye hesaplarını, kimlik onaylarını ve erişim durumlarını yönetin.</p>

      <div class="panel-card" style="padding:0; overflow:hidden; margin-bottom:0;">
        <div class="table-scroll-x">
          <table class="data-table" style="min-width:760px;">
            <thead>
              <tr>
                <th style="padding-left:18px;">Kullanıcı</th>
                <th>E-Posta</th>
                <th>Rol</th>
                <th>Durum</th>
                <th style="padding-right:18px; text-align:right;">İşlem</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($users as $u):
                $uid = (int)($u['id'] ?? 0);
                $uStatus = strtoupper((string)($u['durum'] ?? 'AKTIF'));
                $uAvatar = \Martello\Services\UserAuthStore::resolveAvatar($u);
              ?>
                <tr>
                  <td style="padding-left:18px;">
                    <div style="display:flex; align-items:center; gap:10px;">
                      <img src="<?= htmlspecialchars($uAvatar) ?>" alt="User" style="width:30px; height:30px; border-radius:50%; object-fit:cover; border:1px solid rgba(200,170,110,0.4);">
                      <span style="color:#F5F0E6; font-weight:500;"><?= htmlspecialchars(trim(($u['ad'] ?? '') . ' ' . ($u['soyad'] ?? ''))) ?></span>
                    </div>
                  </td>
                  <td style="color:#A39B8F;"><?= htmlspecialchars((string)($u['email'] ?? '')) ?></td>
                  <td><span class="badge badge-gold"><?= htmlspecialchars((string)($u['rol'] ?? 'ALICI')) ?></span></td>
                  <td><span class="badge <?= $uStatus === 'AKTIF' ? 'badge-success' : 'badge-danger' ?>"><?= htmlspecialchars($uStatus) ?></span></td>
                  <td style="padding-right:18px; text-align:right; white-space:nowrap;">
                    <?php if ($uid === 1 || strtoupper((string)($u['rol'] ?? '')) === 'ADMIN'): ?>
                      <span style="color:#8C8478; font-size:0.76rem;">—</span>
                    <?php else: ?>
                      <form method="POST" style="display:inline; margin:0;">
                        <input type="hidden" name="action" value="verify_kyc">
                        <input type="hidden" name="user_id" value="<?= $uid ?>">
                        <input type="hidden" name="decision" value="approve">
                        <button type="submit" class="btn-outline-gold btn-sm">KYC Onayla</button>
                      </form>
                      <form method="POST" style="display:inline; margin-left:4px;">
                        <input type="hidden" name="action" value="toggle_ban">
                        <input type="hidden" name="user_id" value="<?= $uid ?>">
                        <input type="hidden" name="new_status" value="<?= $uStatus === 'ASKIDA' ? 'AKTIF' : 'ASKIDA' ?>">
                        <button type="submit" class="btn-table-action" style="color:#DDA3A3;"><?= $uStatus === 'ASKIDA' ? 'Aktifleştir' : 'Askıya Al' ?></button>
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

    <!-- ====================================================================
         14. TAB: ROL & YETKİ YÖNETİMİ (RBAC)
         ==================================================================== -->
    <div id="admTab_yetkiler" class="tab-content <?= $activeTab === 'yetkiler' ? 'active' : '' ?>">
      <div class="page-breadcrumb">ADMIN / ROL &amp; YETKİ</div>
      <h1 class="page-heading">Rol &amp; Yetki Yönetimi</h1>
      <p class="page-subheading">Kullanıcı rollerini ve yetki seviyelerini atayın (kimlik deposu ile senkronize).</p>

      <div class="panel-card">
        <form method="POST" style="display:grid; grid-template-columns:1.6fr 1.2fr 1.2fr auto; gap:12px; align-items:flex-end;">
          <input type="hidden" name="action" value="update_user_role_and_permissions">
          <div>
            <label class="form-label">Kullanıcı</label>
            <select name="user_id" class="form-control">
              <?php foreach ($users as $u):
                if ((int)($u['id'] ?? 0) === 1 || strtoupper((string)($u['rol'] ?? '')) === 'ADMIN') continue;
              ?>
                <option value="<?= (int)$u['id'] ?>"><?= htmlspecialchars(trim(($u['ad'] ?? '') . ' ' . ($u['soyad'] ?? ''))) ?> (<?= htmlspecialchars((string)($u['rol'] ?? '')) ?>)</option>
              <?php endforeach; ?>
            </select>
          </div>
          <div>
            <label class="form-label">Rol</label>
            <select name="new_role" class="form-control">
              <option value="ALICI">Alıcı</option>
              <option value="SATICI">Satıcı</option>
              <option value="EKSPERTIZ">Ekspertiz</option>
              <option value="CANLI_DESTEK">Canlı Destek</option>
              <option value="ADMIN">Admin</option>
            </select>
          </div>
          <div>
            <label class="form-label">Yetki Seviyesi</label>
            <select name="auth_tier" class="form-control">
              <option value="STANDART">Standart</option>
              <option value="GOLD">Gold VIP</option>
              <option value="BAS_EKSPER">Baş Eksper</option>
              <option value="SUPER_ADMIN">Süper Admin</option>
            </select>
          </div>
          <button type="submit" class="btn-gold">Yetkiyi Güncelle</button>
        </form>
      </div>

      <div class="panel-card" style="padding:0; overflow:hidden; margin-bottom:0;">
        <div class="table-scroll-x">
          <table class="data-table" style="min-width:720px;">
            <thead>
              <tr>
                <th style="padding-left:18px;">Kullanıcı</th>
                <th>Mevcut Rol</th>
                <th>Pey Kısıtlaması</th>
                <th style="padding-right:18px; text-align:right;">VIP Erişimi</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($users as $u):
                $uid = (int)($u['id'] ?? 0);
                $kis = $u['kisitlamalar'] ?? [];
                $peyBlok = !empty($kis['peyEngeli']);
                $vipBlok = !empty($kis['vipEngeli']);
                $uAvatar = \Martello\Services\UserAuthStore::resolveAvatar($u);
              ?>
                <tr>
                  <td style="padding-left:18px;">
                    <div style="display:flex; align-items:center; gap:10px;">
                      <img src="<?= htmlspecialchars($uAvatar) ?>" alt="User" style="width:30px; height:30px; border-radius:50%; object-fit:cover; border:1px solid rgba(200,170,110,0.4);">
                      <span style="color:#F5F0E6; font-weight:500;"><?= htmlspecialchars(trim(($u['ad'] ?? '') . ' ' . ($u['soyad'] ?? ''))) ?></span>
                    </div>
                  </td>
                  <td><span class="badge badge-gold"><?= htmlspecialchars((string)($u['rol'] ?? '')) ?></span></td>
                  <?php if ($uid === 1 || strtoupper((string)($u['rol'] ?? '')) === 'ADMIN'): ?>
                    <td><span style="color:#8C8478; font-size:0.76rem;">—</span></td>
                    <td style="padding-right:18px; text-align:right;"><span style="color:#8C8478; font-size:0.76rem;">—</span></td>
                  <?php else: ?>
                    <td>
                      <form method="POST" style="margin:0; display:inline;">
                        <input type="hidden" name="action" value="restrict_user_access">
                        <input type="hidden" name="user_id" value="<?= $uid ?>">
                        <input type="hidden" name="restriction_type" value="peyEngeli">
                        <input type="hidden" name="is_restricted" value="<?= $peyBlok ? '0' : '1' ?>">
                        <button type="submit" class="btn-table-action"><?= $peyBlok ? 'Engeli Kaldır' : 'Pey Engelle' ?></button>
                      </form>
                    </td>
                    <td style="padding-right:18px; text-align:right;">
                      <form method="POST" style="margin:0; display:inline;">
                        <input type="hidden" name="action" value="restrict_user_access">
                        <input type="hidden" name="user_id" value="<?= $uid ?>">
                        <input type="hidden" name="restriction_type" value="vipEngeli">
                        <input type="hidden" name="is_restricted" value="<?= $vipBlok ? '0' : '1' ?>">
                        <button type="submit" class="btn-table-action"><?= $vipBlok ? 'VIP Aç' : 'VIP Kısıtla' ?></button>
                      </form>
                    </td>
                  <?php endif; ?>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- ====================================================================
         15. TAB: SİSTEM AYARLARI & PERSONEL OLUŞTURMA
         ==================================================================== -->
    <div id="admTab_ayarlar" class="tab-content <?= $activeTab === 'ayarlar' ? 'active' : '' ?>">
      <div class="page-breadcrumb">ADMIN / SİSTEM AYARLARI</div>
      <h1 class="page-heading">Sistem Ayarları</h1>
      <p class="page-subheading">Temel ihale parametreleri ve yetkili personel hesapları.</p>

      <div class="panel-card">
        <h3 class="panel-title" style="margin-bottom:14px;">İhale Parametreleri</h3>
        <form method="POST" style="display:grid; grid-template-columns:1fr 1fr 1fr 1fr auto; gap:12px; align-items:flex-end;">
          <input type="hidden" name="action" value="update_system_params">
          <div>
            <label class="form-label">Anti-Sniping (Sn)</label>
            <input type="number" name="anti_sniping_seconds" class="form-control" value="60" required>
          </div>
          <div>
            <label class="form-label">Min. Pey Adımı (₺)</label>
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
          <button type="submit" class="btn-gold">Kaydet</button>
        </form>
      </div>

      <div class="panel-card" style="margin-bottom:0;">
        <h3 class="panel-title" style="margin-bottom:6px;">Yetkili Personel Tanımlama</h3>
        <p style="font-size:0.8rem; color:#A39B8F; margin-bottom:14px;">Ekspertiz, Canlı Destek veya Admin rolünde yeni personel hesabı oluşturun.</p>
        <button type="button" onclick="openDemoSupportModal()" class="btn-outline-gold">Yeni Personel Hesabı Ekle</button>
      </div>
    </div>

  </main>
</div>

<!-- Lot Düzenleme Modalı -->
<div id="editLotModal" class="martello-modal" onclick="if(event.target===this) closeEditLotModal();" style="display:none; position:fixed; z-index:10050; inset:0; width:100vw; height:100vh; background:rgba(6,5,4,0.84); backdrop-filter:blur(8px); align-items:center; justify-content:center; padding:20px;">
  <div style="background:#141210; border:1px solid rgba(200,170,110,0.32); border-radius:14px; max-width:520px; width:100%; padding:24px; position:relative;">
    <button type="button" onclick="closeEditLotModal()" style="position:absolute; right:16px; top:14px; background:none; border:none; color:#A39B8F; font-size:1.4rem; cursor:pointer;">&times;</button>
    <div style="font-family:var(--font-brand); font-size:0.68rem; color:#C8AA6E; letter-spacing:1.6px; margin-bottom:4px;">LOT DÜZENLE · <span id="editLotKoduModal"></span></div>
    <h3 style="font-family:var(--font-serif); font-size:1.35rem; color:#F5F0E6; margin-bottom:14px;">Fiyat &amp; Süreç Güncelle</h3>

    <form method="POST">
      <input type="hidden" name="action" value="edit_lot">
      <input type="hidden" name="lot_id" id="editLotId">
      <div class="form-group">
        <label class="form-label">Eser Başlığı</label>
        <input type="text" name="baslik" id="editLotBaslik" class="form-control" required>
      </div>
      <div style="display:grid; grid-template-columns:1fr 1fr 1fr; gap:10px; margin-bottom:14px;">
        <div>
          <label class="form-label">Başlangıç (₺)</label>
          <input type="number" step="500" name="baslangicFiyati" id="editLotBaslangic" class="form-control">
        </div>
        <div>
          <label class="form-label">Rezerv (₺)</label>
          <input type="number" step="500" name="rezervFiyat" id="editLotRezerv" class="form-control">
        </div>
        <div>
          <label class="form-label">Adım (₺)</label>
          <input type="number" step="100" name="minimumArtisTutari" id="editLotAdim" class="form-control">
        </div>
      </div>
      <button type="submit" class="btn-gold-pill" style="width:100%; padding:10px; cursor:pointer;">Güncelle</button>
    </form>
  </div>
</div>

<!-- Personel Ekleme Modalı -->
<div id="demoSupportModal" class="martello-modal" onclick="if(event.target===this) closeDemoSupportModal();" style="display:none; position:fixed; z-index:10050; inset:0; width:100vw; height:100vh; background:rgba(6,5,4,0.84); backdrop-filter:blur(8px); align-items:center; justify-content:center; padding:20px;">
  <div style="background:#141210; border:1px solid rgba(200,170,110,0.32); border-radius:14px; max-width:440px; width:100%; padding:24px; position:relative;">
    <button type="button" onclick="closeDemoSupportModal()" style="position:absolute; right:16px; top:14px; background:none; border:none; color:#A39B8F; font-size:1.4rem; cursor:pointer;">&times;</button>
    <div style="font-family:var(--font-brand); font-size:0.68rem; color:#C8AA6E; letter-spacing:1.6px; margin-bottom:4px;">YETKİLİ PERSONEL</div>
    <h3 style="font-family:var(--font-serif); font-size:1.35rem; color:#F5F0E6; margin-bottom:16px;">Yeni Personel Hesabı</h3>

    <form method="POST">
      <input type="hidden" name="action" value="create_staff">
      <div class="form-group">
        <label class="form-label">Ad Soyad</label>
        <input type="text" name="ad" class="form-control" placeholder="Örn: Cansu Güneş" required>
      </div>
      <div class="form-group">
        <label class="form-label">E-Posta</label>
        <input type="email" name="email" class="form-control" placeholder="cansu@martello.com" required>
      </div>
      <div class="form-group">
        <label class="form-label">Görev / Rol</label>
        <select name="rol" class="form-control">
          <option value="CANLI_DESTEK">Canlı Destek</option>
          <option value="EKSPERTIZ">Ekspertiz</option>
          <option value="ADMIN">Admin</option>
        </select>
      </div>
      <div class="form-group">
        <label class="form-label">Geçici Şifre</label>
        <input type="password" name="sifre" class="form-control" placeholder="En az 6 karakter" value="Personel123!" required>
      </div>
      <button type="submit" class="btn-gold-pill" style="width:100%; padding:10px; cursor:pointer;">Hesabı Oluştur</button>
    </form>
  </div>
</div>

<script>
function switchAdminTab(tabId, btn) {
  if (tabId === 'ihtilaflar') tabId = 'iptal_yonetimi';
  document.querySelectorAll('.tab-content').forEach(el => el.classList.remove('active'));
  document.querySelectorAll('.sidebar .nav-menu-item').forEach(el => el.classList.remove('active'));

  const target = document.getElementById('admTab_' + tabId);
  if (target) target.classList.add('active');

  let navItem = (btn && btn.closest) ? btn.closest('.nav-menu-item') : null;
  if (!navItem) {
    const match = document.querySelector(`.sidebar button[data-tab="${tabId}"]`);
    if (match) navItem = match.closest('.nav-menu-item');
  }
  if (navItem) navItem.classList.add('active');

  if (window.history && window.history.replaceState) {
    try {
      const url = new URL(window.location.href);
      url.searchParams.set('tab', tabId);
      window.history.replaceState({}, '', url);
    } catch (e) {}
  }
}

function switchCancellationSubTab(subTab) {
  document.querySelectorAll('.cancel-subtab-pane').forEach(el => el.style.display = 'none');
  document.querySelectorAll('.cancel-subtab-btn').forEach(b => {
    b.classList.remove('btn-gold-pill');
    b.classList.add('btn-dark-pill');
  });
  const pane = document.getElementById('cancel_pane_' + subTab);
  const btn  = document.getElementById('btn_cancel_subtab_' + subTab);
  if (pane) pane.style.display = 'block';
  if (btn) {
    btn.classList.remove('btn-dark-pill');
    btn.classList.add('btn-gold-pill');
  }
}

function selectInspectionDossier(reportId, btn) {
  document.querySelectorAll('.dossier-box').forEach(el => el.style.display = 'none');
  document.querySelectorAll('.dossier-select-btn').forEach(b => {
    b.classList.remove('btn-gold-pill');
    b.classList.add('btn-dark-pill');
  });
  const target = document.getElementById('dossier_box_' + reportId);
  if (target) target.style.display = 'block';
  if (btn) {
    btn.classList.remove('btn-dark-pill');
    btn.classList.add('btn-gold-pill');
  }
}

function filterAccreditation(filter, btn) {
  document.querySelectorAll('.accreditation-filter-btn').forEach(b => {
    b.classList.remove('btn-gold-pill');
    b.classList.add('btn-dark-pill');
  });
  if (btn) {
    btn.classList.remove('btn-dark-pill');
    btn.classList.add('btn-gold-pill');
  }
  document.querySelectorAll('.accreditation-card').forEach(card => {
    const status = card.getAttribute('data-status');
    card.style.display = (filter === 'all' || status === filter) ? 'block' : 'none';
  });
}

function filterAuditRows(q) {
  q = (q || '').toLowerCase().trim();
  document.querySelectorAll('#auditTableBody .audit-row').forEach(row => {
    row.style.display = (!q || row.innerText.toLowerCase().indexOf(q) !== -1) ? '' : 'none';
  });
}

function updateQuickScheduleForm(lotId) {
  const sel = document.getElementById('quickScheduleLotSelect');
  if (!sel) return;
  const opt = sel.options[sel.selectedIndex];
  if (!opt) return;
  const s = document.getElementById('quickScheduleStart');
  const e = document.getElementById('quickScheduleEnd');
  const st = document.getElementById('quickScheduleStep');
  const tp = document.getElementById('quickScheduleStepType');
  const sp = document.getElementById('quickScheduleStartPrice');
  const rp = document.getElementById('quickScheduleReservePrice');
  if (s) s.value = opt.getAttribute('data-start') || '';
  if (e) e.value = opt.getAttribute('data-end') || '';
  if (st) st.value = opt.getAttribute('data-step') || 1500;
  if (tp) tp.value = opt.getAttribute('data-steptype') || 'SABIT';
  if (sp) sp.value = opt.getAttribute('data-startprice') || '';
  if (rp) rp.value = opt.getAttribute('data-reserve') || '';
}

function openEditLotModal(lot) {
  document.getElementById('editLotId').value = lot.id || '';
  document.getElementById('editLotKoduModal').innerText = lot.lotKodu || '';
  document.getElementById('editLotBaslik').value = lot.baslik || '';
  document.getElementById('editLotBaslangic').value = lot.baslangicFiyati || '';
  document.getElementById('editLotRezerv').value = lot.rezervFiyat || '';
  document.getElementById('editLotAdim').value = lot.minimumArtisTutari || '';
  document.getElementById('editLotModal').style.display = 'flex';
}
function closeEditLotModal() {
  document.getElementById('editLotModal').style.display = 'none';
}
function openDemoSupportModal() {
  document.getElementById('demoSupportModal').style.display = 'flex';
}
function closeDemoSupportModal() {
  document.getElementById('demoSupportModal').style.display = 'none';
}

document.addEventListener('DOMContentLoaded', function() {
  const sel = document.getElementById('quickScheduleLotSelect');
  if (sel) updateQuickScheduleForm(sel.value);
});
</script>
