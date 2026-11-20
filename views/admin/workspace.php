<?php
declare(strict_types=1);

/** @var callable $formatPrice */
/** @var array $db */

$users = $db['users'] ?? [];
$lots = $db['lots'] ?? [];
$auctions = $db['auctions'] ?? [];
$reports = $db['appraisalReports'] ?? [];
$disputes = $db['disputes'] ?? [];
$listings = $db['listings'] ?? [];
$categories = $db['categories'] ?? [];
$auditLogs = $db['auditLogs'] ?? [];
$bids = $db['bids'] ?? [];
$wallets = $db['wallets'] ?? [];
$products = $db['products'] ?? [];
$activeTab = $_GET['tab'] ?? 'konsol';

$lotMap = [];
foreach ($lots as $l) {
    $lotMap[(int)$l['id']] = $l;
}

$productMap = [];
foreach ($products as $p) {
    $productMap[(int)$p['id']] = $p;
}
?>

<div class="app-container">
  <!-- Sol Menü (Sidebar) — SRS Sayfa 34 Alt Görsel ile Birebir Uyumlu -->
  <aside class="sidebar">
    <div>
      <div class="sidebar-section-title">Çalışma Alanı</div>
      <ul class="nav-menu">
        <li class="nav-menu-item <?= $activeTab === 'konsol' ? 'active' : '' ?>">
          <button type="button" data-tab="konsol" onclick="switchAdminTab('konsol', this)">
            Yönetim konsolu
          </button>
        </li>
        <li class="nav-menu-item <?= ($activeTab === 'iptal_yonetimi' || $activeTab === 'ihtilaflar') ? 'active' : '' ?>">
          <button type="button" data-tab="iptal_yonetimi" onclick="switchAdminTab('iptal_yonetimi', this)" style="display:flex; align-items:center; gap:6px; color:#fca5a5; font-weight:600;">
            <span>⚖️</span>
            <span>Pey & İlan İptal Masası</span>
          </button>
        </li>
        <li class="nav-menu-item <?= $activeTab === 'moderasyon' ? 'active' : '' ?>">
          <button type="button" data-tab="moderasyon" onclick="switchAdminTab('moderasyon', this)">
            Müzayede & Canlı İhale
          </button>
        </li>
        <li class="nav-menu-item <?= $activeTab === 'raporlar' ? 'active' : '' ?>">
          <button type="button" data-tab="raporlar" onclick="switchAdminTab('raporlar', this)">
            Ekspertiz Raporları
          </button>
        </li>
        <li class="nav-menu-item <?= $activeTab === 'heyet_inceleme' ? 'active' : '' ?>">
          <button type="button" data-tab="heyet_inceleme" onclick="switchAdminTab('heyet_inceleme', this)" style="display:flex; align-items:center; gap:6px; font-weight:600; color:#38bdf8;">
            <span>📑</span>
            <span>Rapor, Belge & Not İnceleme</span>
          </button>
        </li>
        <li class="nav-menu-item <?= $activeTab === 'ekspertiz' ? 'active' : '' ?>">
          <button type="button" data-tab="ekspertiz" onclick="switchAdminTab('ekspertiz', this)" style="display:flex; align-items:center; gap:6px; font-weight:600; color:#c5a059;">
            <span>🔍</span>
            <span>Ekspertiz atama</span>
          </button>
        </li>
        <li class="nav-menu-item <?= $activeTab === 'kategoriler' ? 'active' : '' ?>">
          <button type="button" data-tab="kategoriler" onclick="switchAdminTab('kategoriler', this)">
            Kategoriler & Nitelikler
          </button>
        </li>
        <li class="nav-menu-item <?= $activeTab === 'kullanicilar' ? 'active' : '' ?>">
          <button type="button" data-tab="kullanicilar" onclick="switchAdminTab('kullanicilar', this)">
            Kullanıcılar
          </button>
        </li>
        <li class="nav-menu-item <?= $activeTab === 'yetkiler' ? 'active' : '' ?>">
          <button type="button" data-tab="yetkiler" onclick="switchAdminTab('yetkiler', this)">
            Rol & Yetki Yönetimi (RBAC)
          </button>
        </li>
        <li class="nav-menu-item <?= $activeTab === 'komisyon' ? 'active' : '' ?>">
          <button type="button" data-tab="komisyon" onclick="switchAdminTab('komisyon', this)">
            Komisyon oranları
          </button>
        </li>
        <li class="nav-menu-item <?= $activeTab === 'denetim' ? 'active' : '' ?>">
          <button type="button" data-tab="denetim" onclick="switchAdminTab('denetim', this)">
            Denetim izi
          </button>
        </li>
        <li class="nav-menu-item <?= $activeTab === 'ayarlar' ? 'active' : '' ?>">
          <button type="button" data-tab="ayarlar" onclick="switchAdminTab('ayarlar', this)">
            Sistem ayarları
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

    <!-- 1. TAB: YÖNETİM KONSOLU (media_1795014776297_d1945790.jpg ile Birebir) -->
    <div id="admTab_konsol" class="tab-content <?= $activeTab === 'konsol' ? 'active' : '' ?>">
      <div class="page-breadcrumb">ADMIN / ÇALIŞMA ALANI</div>
      <h1 class="page-heading">Yönetim konsolu</h1>
      <p class="page-subheading" style="margin-bottom:24px;">Seçkin müzayede deneyiminizi yönetin</p>

      <!-- KPI Grid: Raporlar, Kategoriler ve Temel Sayaçlar -->
      <div class="kpi-grid">
        <div class="kpi-card highlight" style="cursor:pointer;" onclick="switchAdminTab('raporlar', document.querySelector('[data-tab=\'raporlar\']'))">
          <div class="kpi-label">EKSPERTİZ RAPORLARI</div>
          <div class="kpi-value" style="color:var(--gold-primary);"><?= count($reports) ?></div>
        </div>
        <div class="kpi-card" style="cursor:pointer;" onclick="switchAdminTab('kategoriler', document.querySelector('[data-tab=\'kategoriler\']'))">
          <div class="kpi-label">KATEGORİLER</div>
          <div class="kpi-value"><?= count($categories) ?></div>
        </div>
        <div class="kpi-card">
          <div class="kpi-label">KULLANICILAR</div>
          <div class="kpi-value"><?= count($users) ?></div>
        </div>
        <div class="kpi-card">
          <div class="kpi-label">AKTİF LOTLAR</div>
          <div class="kpi-value">6</div>
        </div>
        <div class="kpi-card" style="cursor:pointer; border-color:rgba(239,68,68,0.4);" onclick="switchAdminTab('iptal_yonetimi', document.querySelector('[data-tab=\'iptal_yonetimi\']'))">
          <div class="kpi-label" style="color:#ef4444;">ŞÜPHELİ / İPTAL İŞLEM</div>
          <div class="kpi-value" style="color:#f87171;"><?= count(array_filter($bids, fn($b) => ($b['durum'] ?? '') === 'IPTAL_EDILDI')) + count(array_filter($lots, fn($l) => ($l['durum'] ?? '') === 'IPTAL_EDILDI')) ?></div>
        </div>
      </div>

      <!-- Yönetim Konsolu Kartı & Hızlı Geçiş -->
      <div class="panel-card" style="margin-bottom:16px;">
        <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:12px;">
          <div>
            <h2 class="panel-title" style="margin-bottom:4px;">Yönetim Konsolu</h2>
            <p style="font-size:0.85rem; color:var(--text-muted); margin:0;">
              Ekspertiz raporlarını incelemek, kural dışı peyleri / hasarlı ilanları iptal etmek ve müzayedeleri yönetmek için ilgili masaları kullanabilirsiniz.
            </p>
          </div>
          <div style="display:flex; gap:8px;">
            <button type="button" class="btn-gold btn-sm" onclick="switchAdminTab('moderasyon', document.querySelector('[data-tab=\'moderasyon\']'))" style="padding:8px 16px; cursor:pointer;">
              Müzayede İdaresine Git →
            </button>
            <button type="button" class="btn-outline-gold btn-sm" onclick="switchAdminTab('raporlar', document.querySelector('[data-tab=\'raporlar\']'))" style="padding:8px 16px; cursor:pointer;">
              Raporlara Git →
            </button>
            <button type="button" class="btn-dark-pill btn-sm" onclick="switchAdminTab('heyet_inceleme', document.querySelector('[data-tab=\'heyet_inceleme\']'))" style="padding:8px 16px; cursor:pointer; border-color:rgba(56,189,248,0.5); color:#7dd3fc;">
              📑 Heyet İncelemesi →
            </button>
            <button type="button" class="btn-outline-gold btn-sm" onclick="switchAdminTab('ekspertiz', document.querySelector('[data-tab=\'ekspertiz\']'))" style="padding:8px 16px; cursor:pointer;">
              Ekspertiz Atamaya Git →
            </button>
            <button type="button" class="btn-dark-pill btn-sm" onclick="switchAdminTab('iptal_yonetimi', document.querySelector('[data-tab=\'iptal_yonetimi\']'))" style="padding:8px 16px; cursor:pointer; border-color:rgba(239,68,68,0.5); color:#fca5a5;">
              ⚖️ İptal Masası →
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- ====================================================================
         2. TAB: KURAL DIŞI PEY & HASAR/İHTİLAF İLAN İPTAL MASASI (YUKARI TAŞINDI)
         ==================================================================== -->
    <div id="admTab_iptal_yonetimi" class="tab-content <?= ($activeTab === 'iptal_yonetimi' || $activeTab === 'ihtilaflar') ? 'active' : '' ?>">
      <div class="page-breadcrumb">ADMIN / ÇALIŞMA ALANI / DENETİM, TAHKİM VE İPTAL MASASI</div>
      
      <div style="display:flex; justify-content:space-between; align-items:flex-start; flex-wrap:wrap; gap:16px; margin-bottom:20px;">
        <div>
          <h1 class="page-heading" style="margin-bottom:6px;">
            ⚖️ Kural Dışı Pey & Hasar/İhtilaf İlan İptal Masası
          </h1>
          <p class="page-subheading" style="margin:0; line-height:1.5;">
            Kural dışı, hatalı veya kötü niyetli peylerin iptal edilip sürecin bir önceki geçerli peye döndürülmesi; hasar, sahtelik veya hukuki ihtilaf oluşan ürün ilanlarının yetkili admin tarafından yayından kaldırılması, peylerin feshi ve teminat iadesi.
          </p>
        </div>
        <div style="background:rgba(239,68,68,0.1); border:1px solid rgba(239,68,68,0.3); border-radius:8px; padding:10px 16px; display:flex; align-items:center; gap:10px;">
          <span style="font-size:1.4rem;">🛡️</span>
          <div>
            <div style="font-size:0.75rem; text-transform:uppercase; color:#fca5a5; font-weight:700;">Yetkili İdari Müdahale</div>
            <div style="font-size:0.8rem; color:#e2e8f0;">Tüm iptaller noter & denetim kütüğüne tescillenir.</div>
          </div>
        </div>
      </div>

      <!-- 4 Temel Metrik / KPI Kartları -->
      <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(210px, 1fr)); gap:16px; margin-bottom:24px;">
        <div class="panel-card" style="margin-bottom:0; background:linear-gradient(135deg, #121820 0%, #171f2c 100%); border-left:4px solid #ef4444;">
          <div style="font-size:0.75rem; text-transform:uppercase; letter-spacing:1px; color:#848d9a; margin-bottom:4px;">İptal Edilen Peyler</div>
          <div style="font-size:1.6rem; font-weight:700; color:#f87171; font-family:'Cinzel', serif;">
            <?= count(array_filter($bids, static fn($b) => ($b['durum'] ?? '') === 'IPTAL_EDILDI')) ?> <span style="font-size:0.85rem; font-family:sans-serif; color:#94a3b8; font-weight:normal;">Adet Teklif</span>
          </div>
          <div style="font-size:0.75rem; color:#848d9a; margin-top:4px;">Önceki peye rücu edildi, bloke çözüldü</div>
        </div>

        <div class="panel-card" style="margin-bottom:0; background:linear-gradient(135deg, #121820 0%, #171f2c 100%); border-left:4px solid #f59e0b;">
          <div style="font-size:0.75rem; text-transform:uppercase; letter-spacing:1px; color:#848d9a; margin-bottom:4px;">Yayından Kaldırılan İlanlar</div>
          <div style="font-size:1.6rem; font-weight:700; color:#fbbf24; font-family:'Cinzel', serif;">
            <?= count(array_filter($lots, static fn($l) => ($l['durum'] ?? '') === 'IPTAL_EDILDI')) + count(array_filter($listings, static fn($li) => ($li['durum'] ?? '') === 'IPTAL_EDILDI')) ?> <span style="font-size:0.85rem; font-family:sans-serif; color:#94a3b8; font-weight:normal;">Eser / İlan</span>
          </div>
          <div style="font-size:0.75rem; color:#848d9a; margin-top:4px;">Hasar / ihtilaf nedeniyle çekildi</div>
        </div>

        <div class="panel-card" style="margin-bottom:0; background:linear-gradient(135deg, #121820 0%, #171f2c 100%); border-left:4px solid #3b82f6;">
          <div style="font-size:0.75rem; text-transform:uppercase; letter-spacing:1px; color:#848d9a; margin-bottom:4px;">Tahkim & İhtilaf Dosyaları</div>
          <div style="font-size:1.6rem; font-weight:700; color:#60a5fa; font-family:'Cinzel', serif;">
            <?= count($disputes) ?> <span style="font-size:0.85rem; font-family:sans-serif; color:#94a3b8; font-weight:normal;">Dosya</span>
          </div>
          <div style="font-size:0.75rem; color:#848d9a; margin-top:4px;">Eksper mütalaası & karar masasında</div>
        </div>

        <div class="panel-card" style="margin-bottom:0; background:linear-gradient(135deg, #121820 0%, #171f2c 100%); border-left:4px solid #10b981;">
          <div style="font-size:0.75rem; text-transform:uppercase; letter-spacing:1px; color:#848d9a; margin-bottom:4px;">Teminat Güvenliği</div>
          <div style="font-size:1.6rem; font-weight:700; color:#10b981; font-family:'Cinzel', serif;">
            %100 <span style="font-size:0.85rem; font-family:sans-serif; color:#94a3b8; font-weight:normal;">Korumalı</span>
          </div>
          <div style="font-size:0.75rem; color:#848d9a; margin-top:4px;">Otomatik bloke iade entegrasyonu</div>
        </div>
      </div>

      <!-- Alt Sekmeler (Sub-Tabs) -->
      <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:12px; margin-bottom:20px; border-bottom:1px solid #1f2733; padding-bottom:14px;">
        <div style="display:flex; gap:8px; flex-wrap:wrap;">
          <button type="button" id="btn_cancel_subtab_peyler" onclick="switchCancellationSubTab('peyler')" class="cancel-subtab-btn btn-gold-pill" style="font-size:0.85rem; padding:8px 18px; font-weight:600;">
            🔨 Kural Dışı / Hatalı Pey İptali (<?= count($bids) ?>)
          </button>
          <button type="button" id="btn_cancel_subtab_ilanlar" onclick="switchCancellationSubTab('ilanlar')" class="cancel-subtab-btn btn-dark-pill" style="font-size:0.85rem; padding:8px 18px; font-weight:600;">
            📦 Hasar & İhtilaf Oluşan İlanlar (<?= count($lots) ?>)
          </button>
          <button type="button" id="btn_cancel_subtab_ihtilaflar" onclick="switchCancellationSubTab('ihtilaflar')" class="cancel-subtab-btn btn-dark-pill" style="font-size:0.85rem; padding:8px 18px; font-weight:600;">
            ⚖️ Alıcı/Satıcı İhtilaf Dosyaları (<?= count($disputes) ?>)
          </button>
          <button type="button" id="btn_cancel_subtab_kutuk" onclick="switchCancellationSubTab('kutuk')" class="cancel-subtab-btn btn-dark-pill" style="font-size:0.85rem; padding:8px 18px; font-weight:600;">
            📜 İptal Denetim Kütüğü (Audit)
          </button>
        </div>
        <div style="font-size:0.8rem; color:#848d9a;">
          Yetkili Admin: <strong>Yönetici (ID #1)</strong> • IP Tescilli
        </div>
      </div>

      <!-- 1. ALT GÖRÜNÜM: KURAL DIŞI / HATALI / KÖTÜ NİYETLİ PEY İPTALİ -->
      <div id="cancel_pane_peyler" class="cancel-subtab-pane" style="display:block;">
        <div class="panel-card" style="margin-bottom:16px; background:#0b0e14; border:1px solid #1a222e; padding:14px 18px;">
          <div style="display:flex; align-items:center; gap:10px; color:#cbd5e1; font-size:0.84rem;">
            <span style="font-size:1.1rem; color:#f59e0b;">💡</span>
            <div>
              <strong>Pey İptali Prosedürü:</strong> Kural dışı, hatalı veya kötü niyetli bir pey iptal edildiğinde, sistem otomatik olarak eserin en yüksek teklifini <strong>bir önceki geçerli teklife</strong> döndürür, teklif verenin <strong>teminat blokesini cüzdanına iade eder</strong> ve işlemi noter tasdikli <strong>denetim izine</strong> kaydeder.
            </div>
          </div>
        </div>

        <div class="panel-card" style="padding:0; overflow:hidden;">
          <table class="data-table" style="margin:0; width:100%;">
            <thead>
              <tr>
                <th style="width:75px;">Teklif No</th>
                <th>Hedef Lot & Eser</th>
                <th>Teklif Veren</th>
                <th style="text-align:right;">Pey Tutarı</th>
                <th style="text-align:center;">Durum</th>
                <th>Teklif Zamanı</th>
                <th style="min-width:320px; text-align:center;">Yetkili İptal Aksiyonu</th>
              </tr>
            </thead>
            <tbody>
              <?php 
              // Son 25 peyi göster (en yeni en üstte)
              $displayBids = array_reverse(array_slice($bids, -25, 25));
              foreach ($displayBids as $bid): 
                $bLot = $lotMap[(int)($bid['lotId'] ?? 0)] ?? null;
                $isCancelled = ($bid['durum'] ?? '') === 'IPTAL_EDILDI';
                $isLeading = ($bLot && (float)($bLot['mevcutEnYuksekTeklif'] ?? 0) === (float)($bid['teklifTutari'] ?? 0) && !$isCancelled);
              ?>
                <tr style="<?= $isCancelled ? 'opacity:0.6; background:rgba(239,68,68,0.03);' : '' ?>">
                  <td>
                    <code>#<?= htmlspecialchars((string)($bid['id'] ?? 0)) ?></code>
                  </td>
                  <td>
                    <div style="font-weight:600; color:#fff; font-size:0.88rem;">
                      <?= htmlspecialchars($bLot['baslik'] ?? ('Lot #' . ($bid['lotId'] ?? ''))) ?>
                    </div>
                    <div style="font-size:0.75rem; color:#c5a059;">
                      <?= htmlspecialchars($bLot['lotKodu'] ?? 'LOT-DETAY') ?>
                    </div>
                  </td>
                  <td>
                    <div style="font-size:0.85rem; color:#e2e8f0; font-weight:600;">
                      <?= htmlspecialchars((string)($bid['aliciAnonimKod'] ?? 'Anonim')) ?>
                    </div>
                    <div style="font-size:0.72rem; color:#848d9a;">
                      Alıcı ID: #<?= (int)($bid['aliciId'] ?? 0) ?>
                    </div>
                  </td>
                  <td style="text-align:right;">
                    <div style="font-size:1.05rem; font-weight:700; color:#c5a059; font-family:'Cinzel', serif;">
                      ₺<?= number_format((float)($bid['teklifTutari'] ?? 0), 0, ',', '.') ?>
                    </div>
                    <div style="font-size:0.72rem; color:#848d9a;">
                      <?= htmlspecialchars((string)($bid['teklifTipi'] ?? 'MANUEL')) ?>
                    </div>
                  </td>
                  <td style="text-align:center;">
                    <?php if ($isCancelled): ?>
                      <span class="badge" style="background:rgba(239,68,68,0.15); color:#f87171; border:1px solid rgba(239,68,68,0.4); padding:4px 8px; font-size:0.72rem; border-radius:12px;">
                        🚫 İPTAL EDİLDİ
                      </span>
                    <?php elseif ($isLeading): ?>
                      <span class="badge badge-gold" style="font-size:0.72rem; padding:4px 8px;">
                        👑 LİDER PEY
                      </span>
                    <?php else: ?>
                      <span class="badge" style="background:#1e293b; color:#94a3b8; font-size:0.72rem; padding:4px 8px; border-radius:12px;">
                        GEÇİLDİ
                      </span>
                    <?php endif; ?>
                  </td>
                  <td>
                    <div style="font-size:0.8rem; color:#cbd5e1;">
                      <?= htmlspecialchars((string)($bid['saatEtiketi'] ?? substr((string)($bid['teklifZamani'] ?? ''), 11, 8))) ?>
                    </div>
                    <div style="font-size:0.7rem; color:#64748b;">
                      <?= htmlspecialchars(substr((string)($bid['teklifZamani'] ?? ''), 0, 10)) ?>
                    </div>
                  </td>
                  <td>
                    <?php if ($isCancelled): ?>
                      <div style="font-size:0.78rem; color:#fca5a5; line-height:1.4; background:rgba(0,0,0,0.3); padding:6px 10px; border-radius:6px; border:1px dashed rgba(239,68,68,0.3);">
                        <strong>Gerekçe:</strong> <?= htmlspecialchars((string)($bid['iptalSebebi'] ?? 'Kural dışı teklif')) ?><br>
                        <span style="font-size:0.7rem; color:#848d9a;">İptal Zamanı: <?= htmlspecialchars(substr((string)($bid['iptalZamani'] ?? ''), 0, 19)) ?></span>
                      </div>
                    <?php else: ?>
                      <form method="POST" style="margin:0; display:flex; flex-direction:column; gap:6px;" onsubmit="return confirm('UYARI: #<?= (int)$bid['id'] ?> nolu pey iptal edilecek, alıcının teminat blokesi çözülecek ve liderlik bir önceki geçerli peye döndürülecektir. Onaylıyor musunuz?');">
                        <input type="hidden" name="action" value="admin_cancel_bid">
                        <input type="hidden" name="bid_id" value="<?= (int)$bid['id'] ?>">
                        <input type="hidden" name="tab" value="iptal_yonetimi">
                        
                        <div style="display:flex; gap:6px;">
                          <select name="reason" class="martello-select" style="font-size:0.78rem; padding:5px 8px; flex:1; background:#0e131b; height:34px;">
                            <option value="Kural Dışı / Yetkisiz Teklif Verme">Kural Dışı / Yetkisiz Teklif</option>
                            <option value="Hatalı / Sehven Tutar Girişi (Kullanıcı Bildirimi)">Hatalı / Sehven Tutar Girişi</option>
                            <option value="Kötü Niyetli / Yapay Fiyat Artırma / Bot Şüphesi">Kötü Niyetli / Bot Şüphesi</option>
                            <option value="Teminat / Bloke Yetersizliği">Teminat / Bloke Yetersizliği</option>
                            <option value="Yetkili Admin Tahkim Kararı">Admin Özel Tahkim Kararı</option>
                          </select>
                          <button type="submit" class="btn-dark-pill" style="background:#450a0a; border-color:#dc2626; color:#fca5a5; font-size:0.78rem; padding:5px 12px; font-weight:600; white-space:nowrap; cursor:pointer;" onmouseover="this.style.background='#7f1d1d'" onmouseout="this.style.background='#450a0a'">
                            ❌ Peyi İptal Et
                          </button>
                        </div>
                      </form>
                    <?php endif; ?>
                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </div>

      <!-- 2. ALT GÖRÜNÜM: HASAR & İHTİLAF OLUŞAN İLAN VE ESER İPTALİ -->
      <div id="cancel_pane_ilanlar" class="cancel-subtab-pane" style="display:none;">
        <div class="panel-card" style="margin-bottom:16px; background:#0b0e14; border:1px solid #1a222e; padding:14px 18px;">
          <div style="display:flex; align-items:center; gap:10px; color:#cbd5e1; font-size:0.84rem;">
            <span style="font-size:1.1rem; color:#ef4444;">🚨</span>
            <div>
              <strong>İlan Yayından Kaldırma & Fesih Prosedürü:</strong> Muhafaza veya nakliye sırasında hasar gören, sahtelik şüphesi doğan veya mülkiyet ihtilafı olan eser ilanları admin yetkisiyle iptal edilir. Bağlı <strong>tüm aktif peyler feshedilir</strong> ve alıcıların <strong>teminatları derhal serbest bırakılır</strong>.
            </div>
          </div>
        </div>

        <div style="display:grid; grid-template-columns:repeat(auto-fill, minmax(360px, 1fr)); gap:20px;">
          <?php foreach ($lots as $lotItem): 
            $isLotCancelled = ($lotItem['durum'] ?? '') === 'IPTAL_EDILDI';
          ?>
            <div class="panel-card" style="margin-bottom:0; display:flex; flex-direction:column; justify-content:space-between; border:1px solid <?= $isLotCancelled ? '#7f1d1d' : '#1f2733' ?>; background:<?= $isLotCancelled ? 'rgba(30,15,15,0.7)' : '#0f141c' ?>;">
              <div>
                <!-- Üst Bilgi & Durum Rozeti -->
                <div style="display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:12px;">
                  <div>
                    <span style="font-size:0.75rem; color:#c5a059; font-weight:600; letter-spacing:0.5px;">LOT: <?= htmlspecialchars((string)($lotItem['lotKodu'] ?? 'LOT')) ?></span>
                    <h3 style="font-size:1.1rem; color:#fff; font-family:'Cinzel', serif; margin:4px 0 0 0; line-height:1.35;">
                      <?= htmlspecialchars((string)($lotItem['baslik'] ?? 'Müzayede Eseri')) ?>
                    </h3>
                  </div>
                  <?php if ($isLotCancelled): ?>
                    <span class="badge" style="background:#7f1d1d; color:#fca5a5; font-size:0.72rem; padding:4px 8px; border-radius:12px; font-weight:700;">
                      🚫 İPTAL EDİLDİ
                    </span>
                  <?php else: ?>
                    <span class="badge badge-success" style="font-size:0.72rem; padding:4px 8px;">
                      ✓ <?= htmlspecialchars((string)($lotItem['durum'] ?? 'AKTİF')) ?>
                    </span>
                  <?php endif; ?>
                </div>

                <!-- Detay Grid -->
                <div style="background:#090d13; border:1px solid #1a222e; border-radius:8px; padding:12px; display:grid; grid-template-columns:1fr 1fr; gap:10px; margin-bottom:14px; font-size:0.8rem;">
                  <div>
                    <div style="font-size:0.7rem; color:#6b7280; text-transform:uppercase;">Kategori</div>
                    <div style="color:#e2e8f0; font-weight:600;"><?= htmlspecialchars((string)($lotItem['kategoriAdi'] ?? 'Antika & Sanat')) ?></div>
                  </div>
                  <div>
                    <div style="font-size:0.7rem; color:#6b7280; text-transform:uppercase;">Müzayede ID</div>
                    <div style="color:#c5a059; font-weight:600;">AUC-<?= htmlspecialchars((string)($lotItem['muzayedeId'] ?? 801)) ?></div>
                  </div>
                  <div>
                    <div style="font-size:0.7rem; color:#6b7280; text-transform:uppercase;">Açılış Fiyatı</div>
                    <div style="color:#94a3b8;">₺<?= number_format((float)($lotItem['baslangicFiyati'] ?? 0), 0, ',', '.') ?></div>
                  </div>
                  <div>
                    <div style="font-size:0.7rem; color:#6b7280; text-transform:uppercase;">Mevcut En Yüksek</div>
                    <div style="color:#c5a059; font-weight:700;">₺<?= number_format((float)($lotItem['mevcutEnYuksekTeklif'] ?? 0), 0, ',', '.') ?></div>
                  </div>
                </div>

                <?php if ($isLotCancelled): ?>
                  <div style="background:rgba(239,68,68,0.1); border:1px solid rgba(239,68,68,0.3); border-radius:6px; padding:10px; font-size:0.8rem; color:#fca5a5; margin-bottom:14px;">
                    <strong>İptal Gerekçesi:</strong> <?= htmlspecialchars((string)($lotItem['iptalSebebi'] ?? 'Hasar veya ihtilaf tespiti')) ?><br>
                    <span style="font-size:0.72rem; color:#94a3b8;">İptal Tarihi: <?= htmlspecialchars(substr((string)($lotItem['iptalZamani'] ?? ''), 0, 19)) ?></span>
                  </div>
                <?php endif; ?>
              </div>

              <!-- İptal Aksiyon Formu -->
              <?php if (!$isLotCancelled): ?>
                <form method="POST" style="margin:0; border-top:1px dashed #232d3d; padding-top:12px;" onsubmit="return confirm('DİKKAT: \'<?= addslashes((string)$lotItem['baslik']) ?>\' ilanı iptal edilecek, yayından kaldırılacak ve verilmiş tüm peyler feshedilerek alıcı teminatları iade edilecektir. Onaylıyor musunuz?');">
                  <input type="hidden" name="action" value="admin_cancel_lot">
                  <input type="hidden" name="lot_id" value="<?= (int)$lotItem['id'] ?>">
                  <input type="hidden" name="tab" value="iptal_yonetimi">
                  
                  <div style="margin-bottom:8px;">
                    <label style="font-size:0.75rem; color:#94a3b8; display:block; margin-bottom:4px;">İptal ve Yayından Kaldırma Gerekçesi:</label>
                    <select name="reason" class="martello-select" style="font-size:0.8rem; padding:6px 10px; width:100%; background:#090d13;">
                      <option value="Eser Hasarı / Nakliye veya Muhafaza Sırasında Kondisyon Bozulması">Eser Hasarı / Fiziksel Bozulma (Kondisyon Kaybı)</option>
                      <option value="Hukuki İhtilaf / Mülkiyet & Çalıntı İddiası">Hukuki İhtilaf / Mülkiyet & Çalıntı Şüphesi</option>
                      <option value="Sahtelik / Tahrifat / Orijinallik Şüphesi">Sahtelik / Tahrifat / Orijinallik Şüphesi</option>
                      <option value="Satıcı Kural İhlali / Yanıltıcı Beyan">Satıcı Kural İhlali / Yanıltıcı Bilgi</option>
                      <option value="Mücbir Sebep / İdari İptal Kararı">Mücbir Sebep / İdari İptal Kararı</option>
                    </select>
                  </div>

                  <button type="submit" class="btn-dark-pill" style="width:100%; justify-content:center; background:#450a0a; border-color:#dc2626; color:#fca5a5; font-size:0.82rem; padding:8px; font-weight:600; cursor:pointer;" onmouseover="this.style.background='#7f1d1d'" onmouseout="this.style.background='#450a0a'">
                    🚫 İlanı Yayından Kaldır & İptal Et
                  </button>
                </form>
              <?php else: ?>
                <button type="button" disabled class="btn-dark-pill" style="width:100%; justify-content:center; opacity:0.6; font-size:0.8rem; padding:7px; cursor:not-allowed;">
                  ✓ İlan İptal Edildi (Kayıt Arşivlendi)
                </button>
              <?php endif; ?>
            </div>
          <?php endforeach; ?>
        </div>
      </div>

      <!-- 3. ALT GÖRÜNÜM: ALICI/SATICI İHTİLAF DOSYALARI & TAHKİM KARARLARI -->
      <div id="cancel_pane_ihtilaflar" class="cancel-subtab-pane" style="display:none;">
        <div class="panel-card" style="margin-bottom:16px; background:#0b0e14; border:1px solid #1a222e; padding:14px 18px;">
          <div style="display:flex; align-items:center; gap:10px; color:#cbd5e1; font-size:0.84rem;">
            <span style="font-size:1.1rem; color:#3b82f6;">⚖️</span>
            <div>
              <strong>Tahkim & Hakem Heyeti Masası:</strong> Alıcı ve satıcı arasındaki kondisyon, teslimat veya ekspertiz uyuşmazlıklarında admin nihai hakem kararını verir. <strong>Alıcı Haklı</strong> kararı emanetteki tutarı alıcıya iade eder; <strong>Satıcı Haklı</strong> kararı blokeyi çözerek satıcıya transfer eder.
            </div>
          </div>
        </div>

        <div class="panel-card" style="padding:0; overflow:hidden;">
          <table class="data-table" style="margin:0; width:100%;">
            <thead>
              <tr>
                <th>Talep No</th>
                <th>Lot Kodu</th>
                <th>Uyuşmazlık Konusu</th>
                <th>Açıklama & Eksper Mütalaası</th>
                <th>Durum</th>
                <th style="min-width:280px; text-align:center;">Admin Tahkim Kararı</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($disputes as $disp): ?>
                <tr>
                  <td><code><?= htmlspecialchars((string)($disp['talepNo'] ?? 'DISP-00')) ?></code></td>
                  <td><span class="badge badge-gold"><?= htmlspecialchars((string)($disp['lotKodu'] ?? 'AUC-00')) ?></span></td>
                  <td>
                    <div style="font-weight:600; color:#fff; font-size:0.85rem;">
                      <?= htmlspecialchars((string)($disp['konuTipi'] ?? 'Uyuşmazlık')) ?>
                    </div>
                    <div style="font-size:0.72rem; color:#848d9a;">Tarih: <?= htmlspecialchars(substr((string)($disp['olusturmaTarihi'] ?? ''), 0, 10)) ?></div>
                  </td>
                  <td>
                    <div style="font-size:0.83rem; color:#cbd5e1; margin-bottom:4px;">
                      <?= htmlspecialchars((string)($disp['aciklama'] ?? 'Uyuşmazlık açıklaması.')) ?>
                    </div>
                    <?php if (!empty($disp['eksperGorus'])): ?>
                      <div style="font-size:0.75rem; color:#10b981; background:rgba(16,185,129,0.08); padding:4px 8px; border-radius:4px; border-left:2px solid #10b981;">
                        <strong>Eksper Mütalaası:</strong> <?= htmlspecialchars((string)$disp['eksperGorus']) ?>
                      </div>
                    <?php endif; ?>
                  </td>
                  <td>
                    <span class="badge" style="background:rgba(59,130,246,0.15); color:#60a5fa; border:1px solid rgba(59,130,246,0.3); font-size:0.72rem; padding:4px 8px; border-radius:12px;">
                      <?= htmlspecialchars((string)($disp['durum'] ?? 'BEKLEMEDE')) ?>
                    </span>
                  </td>
                  <td>
                    <div style="display:flex; gap:8px; justify-content:center;">
                      <form method="POST" style="margin:0;" onsubmit="return confirm('Alıcı lehine karar verilecek ve emanet bakiyesi alıcıya iade edilecektir. Onaylıyor musunuz?');">
                        <input type="hidden" name="action" value="resolve_dispute">
                        <input type="hidden" name="dispute_id" value="<?= (int)($disp['id'] ?? 1) ?>">
                        <input type="hidden" name="decision" value="ALICI_HAKLI">
                        <input type="hidden" name="tab" value="iptal_yonetimi">
                        <button type="submit" class="btn-gold btn-sm" style="font-size:0.75rem; padding:6px 12px; cursor:pointer;">
                          Alıcı Haklı (İade Et)
                        </button>
                      </form>
                      <form method="POST" style="margin:0;" onsubmit="return confirm('Satıcı lehine karar verilecek ve emanet bakiyesi serbest bırakılacaktır. Onaylıyor musunuz?');">
                        <input type="hidden" name="action" value="resolve_dispute">
                        <input type="hidden" name="dispute_id" value="<?= (int)($disp['id'] ?? 1) ?>">
                        <input type="hidden" name="decision" value="SATICI_HAKLI">
                        <input type="hidden" name="tab" value="iptal_yonetimi">
                        <button type="submit" class="btn-outline-gold btn-sm" style="font-size:0.75rem; padding:6px 12px; cursor:pointer;">
                          Satıcı Haklı (Bloke Çöz)
                        </button>
                      </form>
                    </div>
                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </div>

      <!-- 4. ALT GÖRÜNÜM: RESMİ DENETİM İPTAL KÜTÜĞÜ (AUDIT LOGS) -->
      <div id="cancel_pane_kutuk" class="cancel-subtab-pane" style="display:none;">
        <div class="panel-card" style="margin-bottom:16px; background:#0b0e14; border:1px solid #1a222e; padding:14px 18px;">
          <div style="display:flex; justify-content:space-between; align-items:center;">
            <div style="display:flex; align-items:center; gap:10px; color:#cbd5e1; font-size:0.84rem;">
              <span style="font-size:1.1rem; color:#10b981;">📜</span>
              <div>
                <strong>Resmi İptal ve Tahkim Kütüğü:</strong> Tüm kural dışı pey iptalleri, hasarlı ilan yayından kaldırmaları ve tahkim kararları 5651 sayılı kanun ve noter standartlarında SHA-256 zaman damgasıyla tescillenmektedir.
              </div>
            </div>
            <span class="badge badge-gold" style="font-size:0.72rem;">DENETİM İZİ AKTİF</span>
          </div>
        </div>

        <div class="panel-card" style="padding:0; overflow:hidden;">
          <table class="data-table" style="margin:0; width:100%;">
            <thead>
              <tr>
                <th style="width:140px;">İşlem Türü</th>
                <th>Kullanıcı / Yetkili</th>
                <th>Açıklama ve Karar Detayı</th>
                <th>Önceki Durum</th>
                <th>Yeni Durum</th>
                <th>Zaman</th>
              </tr>
            </thead>
            <tbody>
              <?php 
              $revAudit = array_reverse($auditLogs);
              $hasAudit = false;
              foreach ($revAudit as $log): 
                $hasAudit = true;
                $isCancelLog = in_array($log['islemTuru'] ?? '', ['KURAL_DISI_PEY_IPTALI', 'HASAR_IHTILAF_ILAN_IPTALI', 'BID_CANCELLED', 'DISPUTE_RESOLVED']);
              ?>
                <tr style="<?= $isCancelLog ? 'background:rgba(239,68,68,0.04);' : '' ?>">
                  <td>
                    <?php if ($isCancelLog): ?>
                      <span class="badge" style="background:#7f1d1d; color:#fca5a5; font-size:0.7rem; padding:3px 8px; border-radius:10px;">
                        <?= htmlspecialchars((string)($log['islemTuru'] ?? 'İPTAL')) ?>
                      </span>
                    <?php else: ?>
                      <span class="badge" style="background:#1e293b; color:#94a3b8; font-size:0.7rem; padding:3px 8px; border-radius:10px;">
                        <?= htmlspecialchars((string)($log['islemTuru'] ?? 'SİSTEM')) ?>
                      </span>
                    <?php endif; ?>
                  </td>
                  <td>
                    <div style="font-weight:600; color:#e2e8f0; font-size:0.82rem;">
                      Admin #<?= (int)($log['kullaniciId'] ?? 1) ?>
                    </div>
                    <div style="font-size:0.7rem; color:#64748b;">
                      IP: <?= htmlspecialchars((string)($log['ipAdresi'] ?? '127.0.0.1')) ?>
                    </div>
                  </td>
                  <td>
                    <div style="font-size:0.82rem; color:#cbd5e1; line-height:1.4;">
                      <?= htmlspecialchars((string)($log['aciklama'] ?? 'İşlem gerçekleştirildi.')) ?>
                    </div>
                  </td>
                  <td>
                    <code style="font-size:0.75rem; color:#94a3b8;"><?= htmlspecialchars((string)($log['oncekiDeger'] ?? '-')) ?></code>
                  </td>
                  <td>
                    <code style="font-size:0.75rem; color:#f87171; font-weight:700;"><?= htmlspecialchars((string)($log['yeniDeger'] ?? 'TAMAMLANDI')) ?></code>
                  </td>
                  <td>
                    <div style="font-size:0.78rem; color:#848d9a;">
                      <?= htmlspecialchars(substr((string)($log['tarih'] ?? ''), 0, 19)) ?>
                    </div>
                  </td>
                </tr>
              <?php endforeach; ?>
              <?php if (!$hasAudit): ?>
                <tr><td colspan="6" style="text-align:center; padding:20px; color:#848d9a;">Henüz kayıt bulunmamaktadır.</td></tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- 2. TAB: MÜZAYEDE & CANLI İHALE YÖNETİMİ (YUKARI TAŞINDI - BAŞLAT / DURAKLAT / SONLANDIR / PLANLA) -->
    <div id="admTab_moderasyon" class="tab-content <?= $activeTab === 'moderasyon' ? 'active' : '' ?>">
      <div class="page-breadcrumb">ADMIN / ÇALIŞMA ALANI / MÜZAYEDE & CANLI İHALE KONTROLÜ</div>
      
      <div style="display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:20px; flex-wrap:wrap; gap:10px;">
        <div>
          <h1 class="page-heading">Müzayede ve Canlı İhale Yönetimi</h1>
          <p class="page-subheading" style="color:var(--gold-primary); font-weight:500; font-size:0.92rem;">
            ⚡ "Onaylanmış bir müzayedenin belirlenen tarih/saatte otomatik ya da admin tarafından manuel olarak başlatılması, duraklatılması veya sonlandırılmasıdır."
          </p>
        </div>
        <span class="badge badge-gold" style="font-size:0.78rem; padding:6px 14px;">SRS Müzayede İdaresi</span>
      </div>

      <!-- Müzayedeler Yaşam Döngüsü Kontrol Kartları (BAŞLAT / DURAKLAT / SONLANDIR / ZAMANLA) -->
      <div class="panel-card" style="margin-bottom:24px; border:1px solid rgba(212,175,55,0.25); background:linear-gradient(180deg, #111622 0%, #0a0e16 100%); border-radius:10px; padding:20px;">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:16px; border-bottom:1px solid #1f2736; padding-bottom:12px;">
          <div>
            <h2 style="font-size:1.05rem; font-weight:700; color:#fff; margin:0;">Müzayede Otomasyon ve Manuel Kontrol Masası</h2>
            <p style="font-size:0.78rem; color:#8b949e; margin:4px 0 0 0;">
              Müzayedeyi canlı yayına alabilir (Başlat), teklif alımını dondurabilir (Duraklat) veya resmi olarak kapatabilirsiniz (Sonlandır).
            </p>
          </div>
          <span class="badge" style="background:#161f30; color:#d4af37; border:1px solid #2d3e5e;"><?= count($auctions) ?> Müzayede Salonu</span>
        </div>

        <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(360px, 1fr)); gap:16px;">
          <?php foreach ($auctions as $auc): 
            $aId = (int)($auc['id'] ?? 0);
            $aTitle = htmlspecialchars($auc['baslik'] ?? "Müzayede #{$aId}");
            $aType = strtoupper($auc['tur'] ?? 'CANLI');
            $aStatus = strtoupper($auc['durum'] ?? 'ONAYLANDI');
            $isLive = in_array($aStatus, ['CANLI', 'AKTIF'], true);
            $isPaused = ($aStatus === 'DURAKLATILDI');
            $isEnded = in_array($aStatus, ['SONLANDI', 'KAPANDI'], true);
            $startStr = !empty($auc['baslangicZamani']) ? date('d.m.Y H:i', strtotime($auc['baslangicZamani'])) : 'Belirtilmedi';
            $endStr = !empty($auc['bitisZamani']) ? date('d.m.Y H:i', strtotime($auc['bitisZamani'])) : 'Belirtilmedi';
            $defaultDatetimeLocal = !empty($auc['baslangicZamani']) ? date('Y-m-d\TH:i', strtotime($auc['baslangicZamani'])) : date('Y-m-d\TH:i', strtotime('+1 hour'));
          ?>
            <div style="background:#090d14; border:1px solid <?= $isLive ? '#1a5c38' : ($isPaused ? '#8a6218' : ($isEnded ? '#4d1e27' : '#232b3b')) ?>; border-radius:8px; padding:16px; display:flex; flex-direction:column; justify-content:space-between;">
              <div>
                <div style="display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:8px;">
                  <div>
                    <span class="badge <?= $aType === 'VIP' ? 'badge-gold' : 'badge-info' ?>" style="font-size:0.68rem;">
                      <?= $aType === 'VIP' ? '👑 VIP ÖZEL SALON' : '🏛️ GENEL CANLI MÜZAYEDE' ?>
                    </span>
                    <span style="font-size:0.7rem; color:#8b949e; margin-left:4px;">#<?= $aId ?></span>
                  </div>
                  <div>
                    <?php if ($isLive): ?>
                      <span class="badge badge-success" style="font-size:0.72rem; padding:3px 8px;">🟢 CANLI YAYINDA</span>
                    <?php elseif ($isPaused): ?>
                      <span class="badge badge-warning" style="font-size:0.72rem; padding:3px 8px;">⏸ DURAKLATILDI</span>
                    <?php elseif ($isEnded): ?>
                      <span class="badge badge-danger" style="font-size:0.72rem; padding:3px 8px;">⏹ SONLANDI</span>
                    <?php else: ?>
                      <span class="badge badge-gold" style="font-size:0.72rem; padding:3px 8px;">⏳ ZAMANLANDI</span>
                    <?php endif; ?>
                  </div>
                </div>

                <div style="font-size:1rem; font-weight:700; color:#fff; margin-bottom:8px;">
                  <?= $aTitle ?>
                </div>

                <div style="font-size:0.75rem; color:#8b949e; line-height:1.5; margin-bottom:12px; background:#05070a; border:1px solid #161b24; border-radius:6px; padding:8px 10px;">
                  <div>🕒 <strong>Başlangıç:</strong> <?= $startStr ?></div>
                  <div>🏁 <strong>Bitiş:</strong> <?= $endStr ?></div>
                  <div>👥 <strong>Kayıtlı Katılımcı:</strong> <?= (int)($auc['katilimciSayisi'] ?? 0) ?> kişi</div>
                </div>
              </div>

              <!-- Kontrol Butonları & Zamanlama Formu -->
              <div>
                <!-- Manuel İşlem Butonları: BAŞLAT / DURAKLAT / SONLANDIR -->
                <div style="display:grid; grid-template-columns:1fr 1fr 1fr; gap:6px; margin-bottom:10px;">
                  <!-- Başlat Tuşu -->
                  <form method="POST" style="margin:0;">
                    <input type="hidden" name="action" value="control_auction_state">
                    <input type="hidden" name="auction_id" value="<?= $aId ?>">
                    <input type="hidden" name="auction_action" value="start">
                    <button type="submit" class="btn-sm" style="width:100%; background:<?= $isLive ? '#103822' : '#0c2217' ?>; border:1px solid <?= $isLive ? '#3fb950' : '#1a5c38' ?>; color:#3fb950; font-weight:700; padding:6px 0; border-radius:4px; cursor:pointer;" title="Manuel Başlat">
                      ▶ Başlat
                    </button>
                  </form>

                  <!-- Duraklat Tuşu -->
                  <form method="POST" style="margin:0;">
                    <input type="hidden" name="action" value="control_auction_state">
                    <input type="hidden" name="auction_id" value="<?= $aId ?>">
                    <input type="hidden" name="auction_action" value="pause">
                    <button type="submit" class="btn-sm" style="width:100%; background:<?= $isPaused ? '#3b2a0c' : '#231a08' ?>; border:1px solid <?= $isPaused ? '#e3b341' : '#6b4f12' ?>; color:#e3b341; font-weight:700; padding:6px 0; border-radius:4px; cursor:pointer;" title="Geçici Duraklat">
                      ⏸ Duraklat
                    </button>
                  </form>

                  <!-- Sonlandır Tuşu -->
                  <form method="POST" style="margin:0;" onsubmit="return confirm('<?= addslashes($aTitle) ?> müzayedesini sonlandırmak istediğinizden emin misiniz? Açık lotlar kapanacak ve sayaçlar sıfırlanacaktır.');">
                    <input type="hidden" name="action" value="control_auction_state">
                    <input type="hidden" name="auction_id" value="<?= $aId ?>">
                    <input type="hidden" name="auction_action" value="end">
                    <button type="submit" class="btn-sm" style="width:100%; background:<?= $isEnded ? '#401319' : '#260e13' ?>; border:1px solid <?= $isEnded ? '#f87171' : '#6b1d28' ?>; color:#f87171; font-weight:700; padding:6px 0; border-radius:4px; cursor:pointer;" title="Müzayedeyi Kapat ve Sonlandır">
                      ⏹ Sonlandır
                    </button>
                  </form>
                </div>

                <!-- Otomatik Zamanlama Formu (Belirlenen Tarih/Saatte Başlatma) -->
                <form method="POST" style="margin:0; background:#05070a; border:1px solid #1a2230; border-radius:6px; padding:8px 10px; display:flex; align-items:center; gap:6px;">
                  <input type="hidden" name="action" value="control_auction_state">
                  <input type="hidden" name="auction_id" value="<?= $aId ?>">
                  <input type="hidden" name="auction_action" value="schedule">
                  <label style="font-size:0.72rem; color:#8b949e; white-space:nowrap; margin:0;">Tarih/Saat:</label>
                  <input type="datetime-local" name="scheduled_start" value="<?= $defaultDatetimeLocal ?>" style="background:#0b0f17; border:1px solid #232b3b; color:#fff; font-size:0.72rem; padding:3px 6px; border-radius:4px; flex:1;">
                  <button type="submit" class="btn-outline-gold btn-sm" style="font-size:0.72rem; padding:4px 8px; white-space:nowrap; cursor:pointer;" title="Otomatik Başlama Zamanını Ayarla">
                    Planla
                  </button>
                </form>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      </div>

      <!-- Bölüm 2: Katalogdaki Müzayede Lotları ve Düzenleme Masası -->
      <div class="panel-card" style="margin-bottom:24px;">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:14px;">
          <div>
            <h3 class="panel-title" style="margin-bottom:2px;">Katalogdaki Müzayede Lotları ve Fiyat Düzenleme</h3>
            <p style="font-size:0.8rem; color:var(--text-muted); margin:0;">
              Canlı lotların başlangıç/rezerv fiyatını, artırım adımını ve sürelerini düzenleyin ya da kural dışı teklifleri iptal edin.
            </p>
          </div>
          <span class="badge badge-gold"><?= count($lots) ?> Adet Eser</span>
        </div>

        <div style="overflow-x:auto;">
          <table class="data-table" style="font-size:0.84rem;">
            <thead>
              <tr>
                <th>Lot Kodu</th>
                <th>Eser Adı</th>
                <th>Kategori</th>
                <th>Başlangıç</th>
                <th>Rezerv Fiyat</th>
                <th>Pey Adımı</th>
                <th>Güncel Teklif</th>
                <th>Kalan Süre</th>
                <th>Durum</th>
                <th>İşlem</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($lots as $lot): ?>
                <tr>
                  <td><span class="badge badge-gold"><?= htmlspecialchars($lot['lotKodu'] ?? "AUC-{$lot['id']}") ?></span></td>
                  <td style="font-weight:600; color:#fff; max-width:200px;"><?= htmlspecialchars($lot['baslik']) ?></td>
                  <td style="color:var(--text-muted); font-size:0.82rem;"><?= htmlspecialchars($lot['kategoriAdi'] ?? 'Antika & Sanat') ?></td>
                  <td><?= $formatPrice((float)$lot['baslangicFiyati']) ?></td>
                  <td><?= $formatPrice((float)$lot['rezervFiyat']) ?></td>
                  <td>+<?= $formatPrice((float)$lot['minimumArtisTutari']) ?></td>
                  <td style="color:var(--gold-bright); font-weight:700;"><?= $formatPrice((float)$lot['mevcutEnYuksekTeklif']) ?></td>
                  <td style="font-size:0.8rem; color:var(--text-muted);"><?= htmlspecialchars($lot['kalanSureMetni'] ?? '5 saat') ?></td>
                  <td>
                    <span class="badge <?= ($lot['durum'] ?? '') === 'ACIK_ARTIRMADA' ? 'badge-success' : 'badge-warning' ?>">
                      <?= htmlspecialchars($lot['durum'] ?? 'ACIK_ARTIRMADA') ?>
                    </span>
                  </td>
                  <td>
                    <button type="button" class="btn-gold btn-sm" style="padding:4px 10px; font-size:0.78rem; cursor:pointer;" onclick='openEditLotModal(<?= json_encode($lot, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>)'>
                      Düzenle
                    </button>
                    <form method="POST" style="display:inline; margin-left:4px;" onsubmit="return confirm('Son verilen peyi kural ihlali gerekçesiyle iptal edip önceki peye dönmek istediğinize emin misiniz?');">
                      <input type="hidden" name="action" value="cancel_bid">
                      <input type="hidden" name="lot_id" value="<?= $lot['id'] ?>">
                      <input type="hidden" name="reason" value="Admin kural dışı teklif iptali">
                      <button type="submit" class="btn-outline-gold btn-sm" style="padding:4px 8px; font-size:0.75rem; color:#f87171; border-color:rgba(239,68,68,0.35); cursor:pointer;" title="Son peyi iptal et ve önceki fiyata dön">Pey İptal</button>
                    </form>
                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- Müzayede / Lot Düzenleme Modalı -->
    <div id="editLotModal" class="martello-modal" style="display:none; position:fixed; z-index:9999; left:0; top:0; width:100%; height:100%; background:rgba(0,0,0,0.85); backdrop-filter:blur(6px); align-items:center; justify-content:center;">
      <div style="background:#12161f; border:1px solid #2a3449; border-radius:12px; max-width:540px; width:92%; padding:24px; box-shadow:0 10px 30px rgba(0,0,0,0.8); position:relative;">
        <button type="button" onclick="closeEditLotModal()" style="position:absolute; right:16px; top:16px; background:none; border:none; color:#888; font-size:1.5rem; cursor:pointer;">&times;</button>
        
        <div style="color:var(--gold-primary); font-size:1.15rem; font-weight:700; margin-bottom:4px;">
          Müzayede Lotu Düzenle (<span id="editLotKoduModal"></span>)
        </div>
        <div style="color:#888; font-size:0.8rem; margin-bottom:16px;">
          Fiyat, pey adımı, süre ve müzayede durumunu güncelleyin. Değişiklikler anında canlı kataloğa yansır.
        </div>

        <form method="POST">
          <input type="hidden" name="action" value="edit_lot">
          <input type="hidden" name="lot_id" id="editLotId">

          <div class="form-group" style="margin-bottom:10px;">
            <label class="form-label">Eser / Lot Başlığı</label>
            <input type="text" name="baslik" id="editLotBaslik" class="form-control" required>
          </div>

          <div style="display:grid; grid-template-columns:1fr 1fr; gap:10px; margin-bottom:10px;">
            <div class="form-group">
              <label class="form-label">Kategori</label>
              <input type="text" name="kategoriAdi" id="editLotKategori" class="form-control" required>
            </div>
            <div class="form-group">
              <label class="form-label">Müzayede Durumu</label>
              <select name="durum" id="editLotDurum" class="form-control">
                <option value="ACIK_ARTIRMADA">Açık Artırmada (Aktif)</option>
                <option value="DURAKLATILDI">Duraklatıldı</option>
                <option value="SATILDI">Satıldı / Kapanmış</option>
                <option value="IPTAL_EDILDI">İptal Edildi</option>
              </select>
            </div>
          </div>

          <div style="display:grid; grid-template-columns:1fr 1fr 1fr; gap:10px; margin-bottom:10px;">
            <div class="form-group">
              <label class="form-label">Başlangıç (TRY)</label>
              <input type="number" step="500" name="baslangicFiyati" id="editLotBaslangic" class="form-control" required>
            </div>
            <div class="form-group">
              <label class="form-label">Rezerv Fiyat (TRY)</label>
              <input type="number" step="500" name="rezervFiyat" id="editLotRezerv" class="form-control" required>
            </div>
            <div class="form-group">
              <label class="form-label">Pey Adımı (TRY)</label>
              <input type="number" step="100" name="minimumArtisTutari" id="editLotArtis" class="form-control" required>
            </div>
          </div>

          <div class="form-group" style="margin-bottom:18px;">
            <label class="form-label">Kalan Süre Metni (Örn: 5 saat 23 dakika)</label>
            <input type="text" name="kalanSureMetni" id="editLotSure" class="form-control" required>
          </div>

          <div style="display:flex; justify-content:flex-end; gap:10px;">
            <button type="button" onclick="closeEditLotModal()" class="btn-outline-gold" style="padding:10px 18px; cursor:pointer;">Vazgeç</button>
            <button type="submit" class="btn-register-glow" style="padding:10px 24px; cursor:pointer;">Değişiklikleri Kaydet</button>
          </div>
        </form>
      </div>
    </div>

    <script>
    function openEditLotModal(lot) {
      document.getElementById('editLotId').value = lot.id;
      document.getElementById('editLotKoduModal').textContent = lot.lotKodu || ('AUC-' + lot.id);
      document.getElementById('editLotBaslik').value = lot.baslik;
      document.getElementById('editLotKategori').value = lot.kategoriAdi || 'Antika & Sanat';
      document.getElementById('editLotBaslangic').value = lot.baslangicFiyati;
      document.getElementById('editLotRezerv').value = lot.rezervFiyat;
      document.getElementById('editLotArtis').value = lot.minimumArtisTutari;
      document.getElementById('editLotSure').value = lot.kalanSureMetni || '5 saat 23 dakika';
      document.getElementById('editLotDurum').value = lot.durum || 'ACIK_ARTIRMADA';
      document.getElementById('editLotModal').style.display = 'flex';
    }
    function closeEditLotModal() {
      document.getElementById('editLotModal').style.display = 'none';
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

</script>

    <!-- 2. TAB: RAPORLAMA VE EKSPERTİZ ONAY / RED MASASI (YUKARI TAŞINDI) -->
    <div id="admTab_raporlar" class="tab-content <?= $activeTab === 'raporlar' ? 'active' : '' ?>">
      <div class="page-breadcrumb">ADMIN / ÇALIŞMA ALANI / EKSPERTİZ RAPORLARI</div>
      
      <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px; flex-wrap:wrap; gap:10px;">
        <div>
          <h1 class="page-heading">Ekspertiz Raporları ve Onay Masası</h1>
          <p class="page-subheading" style="color:var(--gold-primary); font-weight:500;">
            Uzmanlar tarafından düzenlenen ekspertiz raporlarını inceleyin; kabul edin veya reddedin.
          </p>
        </div>
        <span class="badge badge-gold" style="font-size:0.78rem; padding:6px 14px;"><?= count($reports) ?> Rapor</span>
      </div>

      <!-- Ekspertiz Raporları İstatistik Kartları -->
      <?php
        $approvedReports = array_values(array_filter($reports, fn($r) => in_array($r['denetimDurumu'] ?? '', ['KABUL_EDILDI', 'ONAYLANDI'], true)));
        $rejectedReports = array_values(array_filter($reports, fn($r) => ($r['denetimDurumu'] ?? '') === 'REDDEDILDI'));
        $pendingReports = array_values(array_filter($reports, fn($r) => !in_array($r['denetimDurumu'] ?? '', ['KABUL_EDILDI', 'ONAYLANDI', 'REDDEDILDI'], true)));
      ?>
      <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(200px, 1fr)); gap:12px; margin-bottom:24px;">
        <div style="background:#090d14; border:1px solid #1f2736; border-left:3px solid var(--gold-primary); border-radius:8px; padding:12px 14px;">
          <div style="font-size:0.75rem; color:#8b949e; text-transform:uppercase;">Toplam Rapor</div>
          <div style="font-size:1.4rem; font-weight:700; color:#fff;"><?= count($reports) ?></div>
        </div>
        <div style="background:#090d14; border:1px solid #1f2736; border-left:3px solid #3fb950; border-radius:8px; padding:12px 14px;">
          <div style="font-size:0.75rem; color:#8b949e; text-transform:uppercase;">Kabul Edilen (Onaylı)</div>
          <div style="font-size:1.4rem; font-weight:700; color:#3fb950;"><?= count($approvedReports) ?></div>
        </div>
        <div style="background:#090d14; border:1px solid #1f2736; border-left:3px solid #f87171; border-radius:8px; padding:12px 14px;">
          <div style="font-size:0.75rem; color:#8b949e; text-transform:uppercase;">Reddedilen Raporlar</div>
          <div style="font-size:1.4rem; font-weight:700; color:#f87171;"><?= count($rejectedReports) ?></div>
        </div>
      </div>

      <!-- Ekspertiz Raporları Kart Listesi (KABUL ET & REDDET TUŞLARI) -->
      <div style="display:grid; grid-template-columns:1fr; gap:16px;">
        <?php foreach ($reports as $r): 
          $rId = (int)($r['id'] ?? 0);
          $rNo = htmlspecialchars($r['raporNo'] ?? "#{$rId}");
          $status = $r['denetimDurumu'] ?? 'ONAYLANDI';
          $isApproved = in_array($status, ['KABUL_EDILDI', 'ONAYLANDI'], true);
          $isRejected = ($status === 'REDDEDILDI');
          $val = (float)($r['onerilenPiyasaDegeri'] ?? 0);
          $note = htmlspecialchars($r['orijinallikNotu'] ?? 'Belirtilmedi');
          $date = htmlspecialchars($r['raporTarihi'] ?? date('c'));
          $maddeler = (array)($r['maddeler'] ?? []);
          $talepId = (int)($r['talepId'] ?? 0);
          $urunId = (int)($r['urunId'] ?? 0);
        ?>
          <div class="panel-card" style="border:1px solid <?= $isApproved ? '#1e3827' : ($isRejected ? '#4a1d25' : '#1f2736') ?>; background:#0b0f17; border-radius:10px; padding:20px;">
            <div style="display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:12px; flex-wrap:wrap; gap:12px;">
              <div>
                <div style="display:flex; align-items:center; gap:8px;">
                  <span class="badge" style="background:#131822; border:1px solid #232b3b; color:#d4af37; font-weight:700; font-size:0.8rem;">
                    <?= $rNo ?>
                  </span>
                  <span class="badge" style="background:#131822; border:1px solid #232b3b; color:#8b949e; font-size:0.75rem;">
                    Talep #<?= $talepId ?> • Eser #<?= $urunId ?>
                  </span>
                  <?php if ($isApproved): ?>
                    <span class="badge badge-success" style="font-size:0.72rem; padding:3px 8px;">✓ KABUL EDİLDİ</span>
                  <?php elseif ($isRejected): ?>
                    <span class="badge badge-danger" style="font-size:0.72rem; padding:3px 8px;">✕ REDDEDİLDİ</span>
                  <?php else: ?>
                    <span class="badge badge-warning" style="font-size:0.72rem; padding:3px 8px;">⏳ İNCELENİYOR</span>
                  <?php endif; ?>
                </div>

                <div style="margin-top:6px; font-size:0.88rem; color:#fff; font-weight:600;">
                  Orijinallik Notu: <span style="color:#d4af37; font-weight:500;"><?= $note ?></span>
                </div>
              </div>

              <!-- Eksper Değerleme & Kabul / Red Tuşları -->
              <div style="display:flex; align-items:center; gap:12px; flex-wrap:wrap;">
                <div style="text-align:right;">
                  <div style="font-size:0.72rem; color:#8b949e; text-transform:uppercase;">Eksper Değerlemesi</div>
                  <div style="font-size:1.15rem; font-weight:700; color:var(--gold-bright);">
                    <?= $formatPrice($val) ?>
                  </div>
                </div>

                <!-- Kabul Et ve Reddet Tuşları -->
                <div style="display:flex; gap:8px;">
                  <!-- Kabul Et Tuşu -->
                  <form method="POST" style="margin:0;">
                    <input type="hidden" name="action" value="review_appraisal_report">
                    <input type="hidden" name="report_id" value="<?= $rId ?>">
                    <input type="hidden" name="decision" value="approve">
                    <button type="submit" class="btn-sm" style="background:<?= $isApproved ? '#103822' : '#0c2217' ?>; border:1px solid <?= $isApproved ? '#3fb950' : '#1a5c38' ?>; color:#3fb950; font-weight:700; font-size:0.8rem; padding:8px 16px; border-radius:6px; cursor:pointer;" title="Raporu Kabul Et">
                      ✓ Kabul Et
                    </button>
                  </form>

                  <!-- Reddet Tuşu -->
                  <form method="POST" style="margin:0;" onsubmit="return confirm('Bu ekspertiz raporunu reddetmek istediğinizden emin misiniz?');">
                    <input type="hidden" name="action" value="review_appraisal_report">
                    <input type="hidden" name="report_id" value="<?= $rId ?>">
                    <input type="hidden" name="decision" value="reject">
                    <button type="submit" class="btn-sm" style="background:<?= $isRejected ? '#4a151d' : '#260e13' ?>; border:1px solid <?= $isRejected ? '#f87171' : '#6b1d28' ?>; color:#f87171; font-weight:700; font-size:0.8rem; padding:8px 16px; border-radius:6px; cursor:pointer;" title="Raporu Reddet">
                      ✕ Reddet
                    </button>
                  </form>
                </div>
              </div>
            </div>

            <!-- Maddeler / Rapor Bulguları -->
            <?php if (!empty($maddeler)): ?>
              <div style="background:#07090e; border:1px solid #1a2333; border-radius:6px; padding:10px 14px; margin-top:10px;">
                <div style="font-size:0.72rem; color:#8b949e; text-transform:uppercase; margin-bottom:4px; font-weight:600;">Ekspertiz Heyet Bulguları:</div>
                <ul style="margin:0; padding-left:18px; font-size:0.78rem; color:#c5cbd3; line-height:1.5;">
                  <?php foreach ($maddeler as $madde): ?>
                    <li><?= htmlspecialchars((string)$madde) ?></li>
                  <?php endforeach; ?>
                </ul>
              </div>
            <?php endif; ?>

            <div style="display:flex; justify-content:space-between; align-items:center; margin-top:10px; font-size:0.72rem; color:#606a7b;">
              <span>Düzenleme Tarihi: <?= date('d.m.Y H:i', strtotime($date)) ?></span>
              <span>Arşiv Durumu: Hot Storage (&lt;1 Yıl)</span>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    </div>

        <!-- ====================================================================
         3. TAB: EKSPERTİZ RAPORU, ORİJİNALLİK BELGESİ & ÜRÜN NOTU İNCELEME MASASI
         (SRS: Eksper tarafından hazırlanan detaylı değerlendirme raporunun, orijinallik
          belgesinin ve ürün notunun admin ve uzman heyet tarafından incelenmesidir.)
         ==================================================================== -->
    <div id="admTab_heyet_inceleme" class="tab-content <?= $activeTab === 'heyet_inceleme' ? 'active' : '' ?>">
      <div class="page-breadcrumb">ADMIN / ÇALIŞMA ALANI / UZMAN HEYET & RAPOR KONTROLÜ</div>
      
      <div style="display:flex; justify-content:space-between; align-items:flex-start; flex-wrap:wrap; gap:16px; margin-bottom:20px;">
        <div>
          <h1 class="page-heading" style="margin-bottom:6px;">
            📑 Ekspertiz Değerlendirme Raporu, Orijinallik Belgesi & Ürün Notu İnceleme Masası
          </h1>
          <p class="page-subheading" style="margin:0; line-height:1.5;">
            Eksper tarafından hazırlanan detaylı değerlendirme raporunun, orijinallik belgesinin ve ürün notunun admin ve uzman heyet tarafından incelenmesidir.
          </p>
        </div>
        <div style="background:rgba(56,189,248,0.1); border:1px solid rgba(56,189,248,0.3); border-radius:8px; padding:10px 16px; display:flex; align-items:center; gap:10px;">
          <span style="font-size:1.4rem;">🏛️</span>
          <div>
            <div style="font-size:0.75rem; text-transform:uppercase; color:#7dd3fc; font-weight:700;">Uzman Heyet Kurulu</div>
            <div style="font-size:0.8rem; color:#e2e8f0;">Rapor + Orijinallik Belgesi + Ürün Notu Tescili</div>
          </div>
        </div>
      </div>

      <!-- 4 Temel Metrik / KPI Kartları -->
      <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(210px, 1fr)); gap:16px; margin-bottom:24px;">
        <div class="panel-card" style="margin-bottom:0; background:linear-gradient(135deg, #121820 0%, #171f2c 100%); border-left:4px solid #38bdf8;">
          <div style="font-size:0.75rem; text-transform:uppercase; letter-spacing:1px; color:#848d9a; margin-bottom:4px;">İncelenen Rapor Dosyası</div>
          <div style="font-size:1.6rem; font-weight:700; color:#38bdf8; font-family:'Cinzel', serif;">
            <?= count($reports) ?> <span style="font-size:0.85rem; font-family:sans-serif; color:#94a3b8; font-weight:normal;">Dosya</span>
          </div>
          <div style="font-size:0.75rem; color:#848d9a; margin-top:4px;">Değerlendirme, Belge & Not İçerir</div>
        </div>

        <div class="panel-card" style="margin-bottom:0; background:linear-gradient(135deg, #121820 0%, #171f2c 100%); border-left:4px solid #10b981;">
          <div style="font-size:0.75rem; text-transform:uppercase; letter-spacing:1px; color:#848d9a; margin-bottom:4px;">Onaylanan Orijinallik Belgesi</div>
          <div style="font-size:1.6rem; font-weight:700; color:#10b981; font-family:'Cinzel', serif;">
            <?= count(array_filter($reports, static fn($r) => in_array($r['denetimDurumu'] ?? '', ['KABUL_EDILDI', 'ONAYLANDI'], true))) ?> <span style="font-size:0.85rem; font-family:sans-serif; color:#94a3b8; font-weight:normal;">Sertifika</span>
          </div>
          <div style="font-size:0.75rem; color:#848d9a; margin-top:4px;">Akredite damga & e-imza tescilli</div>
        </div>

        <div class="panel-card" style="margin-bottom:0; background:linear-gradient(135deg, #121820 0%, #171f2c 100%); border-left:4px solid #f59e0b;">
          <div style="font-size:0.75rem; text-transform:uppercase; letter-spacing:1px; color:#848d9a; margin-bottom:4px;">Revizyon / Ek Bilgi Talebi</div>
          <div style="font-size:1.6rem; font-weight:700; color:#fbbf24; font-family:'Cinzel', serif;">
            <?= count(array_filter($reports, static fn($r) => ($r['denetimDurumu'] ?? '') === 'REVIZE_ISTENDI')) ?> <span style="font-size:0.85rem; font-family:sans-serif; color:#94a3b8; font-weight:normal;">Dosya</span>
          </div>
          <div style="font-size:0.75rem; color:#848d9a; margin-top:4px;">Eksperden ek makro görsel bekleniyor</div>
        </div>

        <div class="panel-card" style="margin-bottom:0; background:linear-gradient(135deg, #121820 0%, #171f2c 100%); border-left:4px solid #c5a059;">
          <div style="font-size:0.75rem; text-transform:uppercase; letter-spacing:1px; color:#848d9a; margin-bottom:4px;">Kurul Doğrulama Standartı</div>
          <div style="font-size:1.6rem; font-weight:700; color:#c5a059; font-family:'Cinzel', serif;">
            %100 <span style="font-size:0.85rem; font-family:sans-serif; color:#94a3b8; font-weight:normal;">Müze & Provenans</span>
          </div>
          <div style="font-size:0.75rem; color:#848d9a; margin-top:4px;">SHA-256 Kripto Zaman Damgalı</div>
        </div>
      </div>

      <!-- İnceleme Dosyası Hızlı Seçim Barı -->
      <div class="panel-card" style="background:#090d14; border:1px solid #1a222e; padding:14px 18px; margin-bottom:20px;">
        <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:12px;">
          <div style="font-size:0.85rem; color:#cbd5e1; font-weight:600; display:flex; align-items:center; gap:8px;">
            <span style="color:#38bdf8;">📂</span> İncelemek İstediğiniz Ekspertiz Dosyasını Seçin:
          </div>
          <div style="display:flex; gap:8px; flex-wrap:wrap;">
            <?php 
            $firstRId = !empty($reports) ? (int)$reports[0]['id'] : 0;
            foreach ($reports as $idx => $rItem): 
              $riId = (int)$rItem['id'];
              $riProduct = $productMap[(int)($rItem['urunId'] ?? 0)] ?? null;
              $riTitle = $riProduct['baslik'] ?? ('Eser #' . ($rItem['urunId'] ?? ''));
              $riStatus = $rItem['denetimDurumu'] ?? 'ONAYLANDI';
              $isCurrentActive = ($idx === 0);
            ?>
              <button type="button" 
                      onclick="selectInspectionDossier(<?= $riId ?>, this)" 
                      class="dossier-select-btn <?= $isCurrentActive ? 'btn-gold-pill' : 'btn-dark-pill' ?>" 
                      style="font-size:0.78rem; padding:6px 14px; font-weight:600; cursor:pointer;">
                <?= htmlspecialchars((string)($rItem['raporNo'] ?? "#{$riId}")) ?> (<?= htmlspecialchars(mb_strimwidth($riTitle, 0, 18, '...')) ?>)
              </button>
            <?php endforeach; ?>
          </div>
        </div>
      </div>

      <!-- Her Rapor İçin 3'lü İnceleme Paneli (Rapor + Belge + Ürün Notu) -->
      <?php foreach ($reports as $idx => $rItem): 
        $riId = (int)$rItem['id'];
        $riNo = htmlspecialchars((string)($rItem['raporNo'] ?? "#{$riId}"));
        $riProduct = $productMap[(int)($rItem['urunId'] ?? 0)] ?? null;
        $riTitle = htmlspecialchars((string)($riProduct['baslik'] ?? 'Nadir Koleksiyon Eseri'));
        $riVal = (float)($rItem['onerilenPiyasaDegeri'] ?? 0);
        $riStatus = $rItem['denetimDurumu'] ?? 'ONAYLANDI';
        $riItems = (array)($rItem['maddeler'] ?? []);
        $riNote = htmlspecialchars((string)($rItem['orijinallikNotu'] ?? 'Orijinal - Ekspertiz Seçki Damgalı'));
        $riDate = htmlspecialchars((string)($rItem['raporTarihi'] ?? date('c')));
        $isFirst = ($idx === 0);
        $shaHash = hash('sha256', (string)$riId . $riNo . $riTitle);
      ?>
        <div id="dossier_box_<?= $riId ?>" class="dossier-box" style="display:<?= $isFirst ? 'block' : 'none' ?>; margin-bottom:24px;">
          <!-- Üst Durum Çubuğu -->
          <div style="background:#0e131b; border:1px solid #1f2736; border-radius:10px 10px 0 0; padding:16px 20px; display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:12px; border-bottom:none;">
            <div>
              <div style="display:flex; align-items:center; gap:10px;">
                <span style="font-size:1.25rem; font-weight:700; color:#fff; font-family:'Cinzel', serif;">
                  <?= $riTitle ?>
                </span>
                <span class="badge" style="background:#1e293b; color:#38bdf8; border:1px solid rgba(56,189,248,0.4); font-size:0.75rem; padding:4px 10px;">
                  DOSYA: <?= $riNo ?>
                </span>
                <span class="badge" style="background:#1e293b; color:#c5a059; border:1px solid rgba(197,160,89,0.3); font-size:0.75rem; padding:4px 10px;">
                  ESER ID: #<?= (int)($rItem['urunId'] ?? 0) ?>
                </span>
              </div>
              <div style="font-size:0.8rem; color:#94a3b8; margin-top:4px;">
                Tanzim Tarihi: <?= date('d.m.Y H:i', strtotime($riDate)) ?> • Görevli Eksper: Mehmet Demir (Lisans No: EXP-TR-8841-A)
              </div>
            </div>

            <div>
              <?php if (in_array($riStatus, ['KABUL_EDILDI', 'ONAYLANDI'], true)): ?>
                <span class="badge badge-success" style="font-size:0.82rem; padding:6px 14px;">✓ HEYETÇE KABUL EDİLDİ</span>
              <?php elseif ($riStatus === 'REVIZE_ISTENDI'): ?>
                <span class="badge" style="background:#854d0e; color:#fef08a; font-size:0.82rem; padding:6px 14px;">⚠️ REVİZYON İSTENDİ</span>
              <?php elseif ($riStatus === 'REDDEDILDI'): ?>
                <span class="badge badge-danger" style="font-size:0.82rem; padding:6px 14px;">✕ HEYETÇE REDDEDİLDİ</span>
              <?php else: ?>
                <span class="badge badge-warning" style="font-size:0.82rem; padding:6px 14px;">⏳ İNCELEME BEKLİYOR</span>
              <?php endif; ?>
            </div>
          </div>

          <!-- 3 SÜTUNLU İNCELEME MASASI GRID -->
          <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(320px, 1fr)); gap:1px; background:#1f2736; border:1px solid #1f2736;">
            
            <!-- SÜTUN 1: DETAYLI DEĞERLENDİRME RAPORU -->
            <div style="background:#090d14; padding:20px; display:flex; flex-direction:column; justify-content:space-between;">
              <div>
                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:12px; border-bottom:1px solid #1a222e; padding-bottom:8px;">
                  <h3 style="font-size:0.95rem; color:#38bdf8; margin:0; display:flex; align-items:center; gap:6px;">
                    📑 1. Detaylı Değerlendirme Raporu
                  </h3>
                  <span style="font-size:0.7rem; color:#848d9a; background:#111622; padding:2px 8px; border-radius:4px;">TEKNİK RAPOR</span>
                </div>

                <!-- Kondisyon Skoru -->
                <div style="background:#0e131b; border:1px solid #1a222e; border-radius:8px; padding:12px; margin-bottom:14px;">
                  <div style="display:flex; justify-content:space-between; align-items:center;">
                    <span style="font-size:0.75rem; color:#848d9a; text-transform:uppercase;">Kondisyon Derecesi</span>
                    <span style="font-size:1.1rem; font-weight:700; color:#10b981;">9.6 / 10 (Müze Seviyesi)</span>
                  </div>
                  <div style="font-size:0.75rem; color:#cbd5e1; margin-top:4px;">
                    Fiziksel aşınma dönemine uygundur; sonradan yapılan tahrifat veya sahtecilik izine rastlanmamıştır.
                  </div>
                </div>

                <!-- Rapor Maddeleri -->
                <div style="margin-bottom:14px;">
                  <div style="font-size:0.75rem; color:#848d9a; text-transform:uppercase; margin-bottom:6px; font-weight:600;">Ekspertiz Heyet Bulguları:</div>
                  <ul style="margin:0; padding-left:18px; font-size:0.8rem; color:#cbd5e1; line-height:1.5;">
                    <?php if (!empty($riItems)): ?>
                      <?php foreach ($riItems as $item): ?>
                        <li style="margin-bottom:4px;"><?= htmlspecialchars((string)$item) ?></li>
                      <?php endforeach; ?>
                    <?php else: ?>
                      <li>Malzeme ve dönem pigment analizi spektrometre ile doğrulanmıştır.</li>
                      <li>Üretim dönemi ve damgalar arşiv katalog kayıtları ile %100 örtüşmektedir.</li>
                    <?php endif; ?>
                  </ul>
                </div>

                <!-- Eksper Değerlemesi -->
                <div style="background:#0e131b; border:1px solid #1a222e; border-radius:8px; padding:12px;">
                  <div style="font-size:0.72rem; color:#848d9a; text-transform:uppercase;">Önerilen Piyasa Değeri</div>
                  <div style="font-size:1.4rem; font-weight:700; color:#c5a059; font-family:'Cinzel', serif; margin-top:2px;">
                    <?= $formatPrice($riVal) ?>
                  </div>
                  <div style="font-size:0.72rem; color:#64748b; margin-top:2px;">Açılış fiyatı için %65-75 rezerv tabanı önerilmiştir.</div>
                </div>
              </div>

              <div style="margin-top:16px; font-size:0.72rem; color:#10b981; display:flex; align-items:center; gap:6px;">
                <span>✓</span> Laboratuvar & Akredite Uzman Teyitli
              </div>
            </div>

            <!-- SÜTUN 2: ORİJİNALLİK BELGESİ (CERTIFICATE OF AUTHENTICITY) -->
            <div style="background:#090d14; padding:20px; display:flex; flex-direction:column; justify-content:space-between;">
              <div>
                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:12px; border-bottom:1px solid #1a222e; padding-bottom:8px;">
                  <h3 style="font-size:0.95rem; color:#10b981; margin:0; display:flex; align-items:center; gap:6px;">
                    🏅 2. Orijinallik Belgesi & Menşe
                  </h3>
                  <span style="font-size:0.7rem; color:#10b981; background:rgba(16,185,129,0.1); padding:2px 8px; border-radius:4px; border:1px solid rgba(16,185,129,0.3);">SERTİFİKALI</span>
                </div>

                <!-- Sertifika Görsel/Kimlik Çerçevesi -->
                <div style="background:linear-gradient(135deg, #0c1219 0%, #111a26 100%); border:1px solid rgba(197,160,89,0.4); border-radius:8px; padding:14px; margin-bottom:14px; position:relative;">
                  <div style="font-size:0.7rem; color:#c5a059; letter-spacing:1px; text-transform:uppercase; font-weight:700;">CERTIFICATE OF AUTHENTICITY</div>
                  <div style="font-size:0.95rem; font-weight:700; color:#fff; font-family:'Cinzel', serif; margin-top:4px;">
                    Sertifika No: CERT-AUTH-2026-<?= $riId ?>
                  </div>
                  <div style="font-size:0.75rem; color:#94a3b8; margin-top:4px;">
                    Damga / Durum: <span style="color:#d4af37; font-weight:600;"><?= $riNote ?></span>
                  </div>
                </div>

                <!-- Provenans / Menşe Zinciri -->
                <div style="margin-bottom:14px;">
                  <div style="font-size:0.75rem; color:#848d9a; text-transform:uppercase; margin-bottom:6px; font-weight:600;">Provenans (Menşe İntikal Zinciri):</div>
                  <div style="font-size:0.78rem; color:#cbd5e1; line-height:1.6; background:#0e131b; padding:10px 12px; border-radius:6px; border:1px solid #1a222e;">
                    1. <strong>Özgün İmalat / Dönem Kaydı:</strong> 20. Yy Başı Özgün Atölye / Fabrikasyon Tescili<br>
                    2. <strong>Özel Koleksiyon İntikali:</strong> İstanbul Hanedan & Seçkin Sanatsever Koleksiyonu<br>
                    3. <strong>Martello Arşiv Kaydı:</strong> Bağımsız Ekspertiz Kurulu Tarafından Onaylı Devir
                  </div>
                </div>

                <!-- Kripto Hash & E-İmza Mührü -->
                <div style="background:#0e131b; border:1px solid #1a222e; border-radius:8px; padding:10px 12px;">
                  <div style="font-size:0.7rem; color:#848d9a; text-transform:uppercase;">SHA-256 Dijital Mühür</div>
                  <div style="font-size:0.72rem; color:#60a5fa; font-family:monospace; word-break:break-all; margin-top:2px;">
                    <?= substr($shaHash, 0, 36) ?>...
                  </div>
                  <div style="font-size:0.7rem; color:#10b981; margin-top:4px;">
                    ✓ TÜBİTAK / Noter Standartlarında E-İmzalı Kaşe
                  </div>
                </div>
              </div>

              <div style="margin-top:16px; font-size:0.72rem; color:#c5a059; display:flex; align-items:center; gap:6px;">
                <span>🔒</span> Yüksek Güvenlikli Blokeli Belge Tescili
              </div>
            </div>

            <!-- SÜTUN 3: ÜRÜN NOTU & KATALOG DİREKTİFLERİ -->
            <div style="background:#090d14; padding:20px; display:flex; flex-direction:column; justify-content:space-between;">
              <div>
                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:12px; border-bottom:1px solid #1a222e; padding-bottom:8px;">
                  <h3 style="font-size:0.95rem; color:#c5a059; margin:0; display:flex; align-items:center; gap:6px;">
                    📝 3. Ürün Notu & Direktifler
                  </h3>
                  <span style="font-size:0.7rem; color:#c5a059; background:rgba(197,160,89,0.1); padding:2px 8px; border-radius:4px; border:1px solid rgba(197,160,89,0.3);">KATALOG DİPNOTU</span>
                </div>

                <!-- Eser Notu & Açıklaması -->
                <div style="background:#0e131b; border:1px solid #1a222e; border-radius:8px; padding:12px; margin-bottom:14px;">
                  <div style="font-size:0.72rem; color:#848d9a; text-transform:uppercase;">Eser Tanımı & Notu</div>
                  <div style="font-size:0.85rem; color:#fff; font-weight:600; margin-top:4px;">
                    <?= $riTitle ?>
                  </div>
                  <p style="font-size:0.78rem; color:#94a3b8; line-height:1.5; margin:6px 0 0 0;">
                    Koleksiyonerler için yüksek estetik ve tarihi değere sahip başyapıt niteliğinde tescilli eser.
                  </p>
                </div>

                <!-- Müzayede Katılım & Provizyon Kuralı -->
                <div style="margin-bottom:14px;">
                  <div style="font-size:0.75rem; color:#848d9a; text-transform:uppercase; margin-bottom:6px; font-weight:600;">Müzayede Özel Katılım Kuralı:</div>
                  <div style="font-size:0.78rem; color:#cbd5e1; background:#0e131b; padding:10px 12px; border-radius:6px; border:1px solid #1a222e;">
                    ⚡ Bu eser için <strong>%15 Teminat Bloke Provizyonu</strong> zorunludur. VIP Alıcılar doğrudan canlı ihaleye dahil olabilir.
                  </div>
                </div>

                <!-- Koruma & İklimlendirme Direktifi -->
                <div style="background:#0e131b; border:1px solid #1a222e; border-radius:8px; padding:12px;">
                  <div style="font-size:0.72rem; color:#848d9a; text-transform:uppercase;">Muhafaza & Lojistik Direktifi</div>
                  <div style="font-size:0.78rem; color:#e2e8f0; margin-top:4px; line-height:1.4;">
                    📦 20-22°C Sabit Sıcaklık, UV Korumalı Özel Cam Sandıklama ve Sigortalı Zırhlı Kurye Teslimatı.
                  </div>
                </div>
              </div>

              <div style="margin-top:16px; font-size:0.72rem; color:#60a5fa; display:flex; align-items:center; gap:6px;">
                <span>🛡️</span> Christie's & Sotheby's Katalog Standartlarına Uygun
              </div>
            </div>

          </div>

          <!-- ALT BÖLÜM: ADMIN VE UZMAN HEYET KARAR PANELİ -->
          <div style="background:#0b0f17; border:1px solid #1f2736; border-top:none; border-radius:0 0 10px 10px; padding:18px 20px;">
            <form method="POST" style="margin:0;">
              <input type="hidden" name="action" value="review_appraisal_dossier">
              <input type="hidden" name="report_id" value="<?= $riId ?>">
              <input type="hidden" name="tab" value="heyet_inceleme">

              <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:16px;">
                <div style="flex:1; min-width:320px;">
                  <label style="font-size:0.78rem; color:#cbd5e1; display:block; margin-bottom:6px; font-weight:600;">
                    Uzman Heyet & Admin İnceleme Şerhi / Karar Notu:
                  </label>
                  <input type="text" 
                         name="committee_note" 
                         value="<?= htmlspecialchars((string)($rItem['heyetKararNotu'] ?? ($rItem['adminNotu'] ?? ''))) ?>" 
                         placeholder="Heyet teknik mütalaasını veya revizyon talimatını giriniz..." 
                         class="martello-input" 
                         style="width:100%; font-size:0.85rem; background:#080c12;">
                </div>

                <div style="display:flex; gap:10px; align-items:center; flex-wrap:wrap;">
                  <!-- Kabul Et / Onayla -->
                  <button type="submit" 
                          name="decision" 
                          value="APPROVE" 
                          class="btn-gold" 
                          style="padding:10px 18px; font-size:0.85rem; font-weight:700; display:flex; align-items:center; gap:6px; cursor:pointer;"
                          title="Raporu, Orijinallik Belgesini ve Ürün Notunu Kabul Et">
                    <span>✓</span> Heyetçe Kabul Et & Onayla
                  </button>

                  <!-- Revize İste -->
                  <button type="submit" 
                          name="decision" 
                          value="REVISE" 
                          class="btn-outline-gold" 
                          style="padding:10px 18px; font-size:0.85rem; font-weight:600; display:flex; align-items:center; gap:6px; cursor:pointer;"
                          title="Eksperden ek makro görsel veya düzeltme talep et">
                    <span>⚠️</span> Revize / Ek Belge İste
                  </button>

                  <!-- Reddet -->
                  <button type="submit" 
                          name="decision" 
                          value="REJECT" 
                          class="btn-dark-pill" 
                          style="padding:10px 16px; font-size:0.85rem; font-weight:600; background:#450a0a; border-color:#dc2626; color:#fca5a5; cursor:pointer;"
                          onclick="return confirm('UYARI: Bu ekspertiz dosyasını (rapor, orijinallik belgesi ve ürün notu) reddetmek istediğinize emin misiniz?');"
                          title="Uygunsuzluk nedeniyle dosyayı reddet">
                    <span>✕</span> Reddet
                  </button>
                </div>
              </div>
            </form>
          </div>
        </div>
      <?php endforeach; ?>
    </div>



    <!-- 5. TAB: EKSPERTİZ ATAMA (YUKARI TAŞINDI - media_1795014776298_9cbae966.jpg ile Birebir) -->
    <div id="admTab_ekspertiz" class="tab-content <?= $activeTab === 'ekspertiz' ? 'active' : '' ?>">
      <div class="page-breadcrumb">ADMIN / ÇALIŞMA ALANI / EKSPERTİZ ATAMA</div>
      
      <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px; flex-wrap:wrap; gap:10px;">
        <div>
          <h1 class="page-heading">Ekspertiz Atama ve Heyet Görevlendirme</h1>
          <p class="page-subheading" style="color:var(--gold-primary); font-weight:500;">
            Gelen eser kayıtlarına bağımsız uzman ve eksper heyetlerini atayın, durumlarını yönetin.
          </p>
        </div>
        <span class="badge badge-gold" style="font-size:0.78rem; padding:6px 14px;">SRS Ekspertiz Masası</span>
      </div>

      <div class="panel-card" style="padding:0; overflow:hidden;">
        <table class="data-table" style="margin:0;">
          <thead>
            <tr>
              <th style="padding-left:24px;">Lot</th>
              <th>Ürün</th>
              <th>Durum</th>
              <th style="padding-right:24px; text-align:right;">İşlem</th>
            </tr>
          </thead>
          <tbody>
            <tr>
              <td style="padding-left:24px; font-weight:600; color:#fff;">AUC-1042</td>
              <td style="font-weight:600; color:#fff;">1968 İsviçre Vintage Saat</td>
              <td><span style="color:#ef4444; font-weight:500;">Reddedildi</span></td>
              <td style="padding-right:24px; text-align:right;">
                <div style="display:inline-flex; gap:8px;">
                  <button type="button" class="btn-table-action" onclick="alert('AUC-1042 onaylandı ve müzayedeye hazır statüsüne alındı.')">Onayla</button>
                  <button type="button" class="btn-table-action" onclick="alert('AUC-1042 gerekçesiyle reddedildi.')">Reddet</button>
                  <button type="button" class="btn-table-action" onclick="alert('AUC-1042 için bağımsız Marmara Ekspertiz Heyeti atandı.')">Ekspertiz ata</button>
                </div>
              </td>
            </tr>

            <tr>
              <td style="padding-left:24px; font-weight:600; color:#fff;">AUC-1043</td>
              <td style="font-weight:600; color:#fff;">Koleksiyon Analog Kamera</td>
              <td><span style="color:#60a5fa; font-weight:500;">İncelemede</span></td>
              <td style="padding-right:24px; text-align:right;">
                <div style="display:inline-flex; gap:8px;">
                  <button type="button" class="btn-table-action" onclick="alert('AUC-1043 onaylandı ve müzayedeye hazır statüsüne alındı.')">Onayla</button>
                  <button type="button" class="btn-table-action" onclick="alert('AUC-1043 gerekçesiyle reddedildi.')">Reddet</button>
                  <button type="button" class="btn-table-action" onclick="alert('AUC-1043 için bağımsız Optik Ekspertiz Heyeti atandı.')">Ekspertiz ata</button>
                </div>
              </td>
            </tr>

            <tr>
              <td style="padding-left:24px; font-weight:600; color:#fff;">AUC-1044</td>
              <td style="font-weight:600; color:#fff;">Çağdaş Sanat Baskısı</td>
              <td><span style="color:#60a5fa; font-weight:500;">İncelemede</span></td>
              <td style="padding-right:24px; text-align:right;">
                <div style="display:inline-flex; gap:8px;">
                  <button type="button" class="btn-table-action" onclick="alert('AUC-1044 onaylandı ve müzayedeye hazır statüsüne alındı.')">Onayla</button>
                  <button type="button" class="btn-table-action" onclick="alert('AUC-1044 gerekçesiyle reddedildi.')">Reddet</button>
                  <button type="button" class="btn-table-action" onclick="alert('AUC-1044 için Çağdaş Sanat Heyeti atandı.')">Ekspertiz ata</button>
                </div>
              </td>
            </tr>

            <tr>
              <td style="padding-left:24px; font-weight:600; color:#fff;">AUC-1045</td>
              <td style="font-weight:600; color:#fff;">Klasik Otomobil Motor Parçası</td>
              <td><span style="color:#60a5fa; font-weight:500;">İncelemede</span></td>
              <td style="padding-right:24px; text-align:right;">
                <div style="display:inline-flex; gap:8px;">
                  <button type="button" class="btn-table-action" onclick="alert('AUC-1045 onaylandı ve müzayedeye hazır statüsüne alındı.')">Onayla</button>
                  <button type="button" class="btn-table-action" onclick="alert('AUC-1045 gerekçesiyle reddedildi.')">Reddet</button>
                  <button type="button" class="btn-table-action" onclick="alert('AUC-1045 için Klasik Araç Ekspertiz Heyeti atandı.')">Ekspertiz ata</button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- 2. TAB: KATEGORİ YÖNETİMİ (SADE: EKLE & SİL) -->
    <div id="admTab_kategoriler" class="tab-content <?= $activeTab === 'kategoriler' ? 'active' : '' ?>">
      <div class="page-breadcrumb">ADMIN / ÇALIŞMA ALANI / KATEGORİLER</div>
      
      <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px; flex-wrap:wrap; gap:10px;">
        <div>
          <h1 class="page-heading">Kategori Yönetimi</h1>
          <p class="page-subheading" style="color:var(--gold-primary); font-weight:500;">
            Platform ürün kategorilerini ekleyin veya silin.
          </p>
        </div>
        <span class="badge badge-gold" style="font-size:0.78rem; padding:6px 14px;"><?= count($categories) ?> Kategori</span>
      </div>

      <!-- 1. BÖLÜM: KATEGORİ EKLE -->
      <div class="panel-card" style="margin-bottom:24px; border:1px solid #1f2736; background:#0b0f17; border-radius:8px; padding:20px;">
        <h3 class="panel-title" style="margin-bottom:14px; font-size:1rem; color:#fff; display:flex; align-items:center; gap:8px;">
          <span style="color:var(--gold-primary); font-size:1.1rem; font-weight:bold;">＋</span> Kategori Ekle
        </h3>
        
        <form method="POST" style="margin:0;">
          <input type="hidden" name="action" value="add_category">

          <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(260px, 1fr)); gap:14px; margin-bottom:14px;">
            <div>
              <label style="display:block; font-size:0.82rem; font-weight:600; color:#c5cbd3; margin-bottom:6px;">
                Kategori Adı *
              </label>
              <input type="text" name="kategori_adi" class="form-control" placeholder="Örn: Klasik Saatler, Heykel veya Tablo" required style="background:#07090e; border:1px solid #2a3449; color:#fff; padding:10px 12px; font-size:0.88rem; width:100%; border-radius:6px;">
            </div>

            <div>
              <label style="display:block; font-size:0.82rem; font-weight:600; color:#c5cbd3; margin-bottom:6px;">
                Üst Kategori (İsteğe Bağlı)
              </label>
              <select name="ust_kategori_id" class="form-control" style="background:#07090e; border:1px solid #2a3449; color:#fff; padding:10px 12px; font-size:0.88rem; width:100%; border-radius:6px;">
                <option value="0">— Ana Kategori (Üst Kategori Yok) —</option>
                <?php foreach ($categories as $cat): 
                  if (empty($cat['ustKategoriId'])):
                ?>
                  <option value="<?= (int)$cat['id'] ?>">
                    ↳ <?= htmlspecialchars($cat['ad'] ?? $cat['kategoriAdi']) ?> Alt Kategorisi Yap
                  </option>
                <?php endif; endforeach; ?>
              </select>
            </div>
          </div>

          <button type="submit" class="btn-gold" style="padding:9px 24px; font-weight:700; font-size:0.88rem; border-radius:6px; cursor:pointer;">
            Kategori Ekle
          </button>
        </form>
      </div>

      <!-- 2. BÖLÜM: KATEGORİLERİ SİL -->
      <div class="panel-card" style="border:1px solid #1f2736; background:#0b0f17; border-radius:8px; padding:20px;">
        <h3 class="panel-title" style="margin-bottom:14px; font-size:1rem; color:#fff; display:flex; align-items:center; gap:8px;">
          <span style="color:#f87171; font-size:1.1rem; font-weight:bold;">✕</span> Kategorileri Sil
        </h3>

        <div style="display:grid; grid-template-columns:repeat(auto-fill, minmax(280px, 1fr)); gap:10px;">
          <?php foreach ($categories as $c): 
            $cId = (int)($c['id'] ?? 0);
            $cName = htmlspecialchars($c['ad'] ?? $c['kategoriAdi'] ?? 'Kategori');
            $parentId = !empty($c['ustKategoriId']) ? (int)$c['ustKategoriId'] : 0;
            $parentName = '';
            if ($parentId > 0) {
              foreach ($categories as $pc) {
                if ((int)$pc['id'] === $parentId) {
                  $parentName = $pc['ad'] ?? $pc['kategoriAdi'];
                  break;
                }
              }
            }
          ?>
            <div style="background:#080b11; border:1px solid #1a2333; border-radius:6px; padding:12px 14px; display:flex; justify-content:space-between; align-items:center;">
              <div>
                <div style="font-weight:600; font-size:0.88rem; color:#fff;">
                  <?= $cName ?>
                </div>
                <div style="margin-top:2px;">
                  <?php if ($parentId > 0): ?>
                    <span style="font-size:0.7rem; color:#60a5fa;">↳ <?= htmlspecialchars($parentName ?: 'Alt Kategori') ?></span>
                  <?php else: ?>
                    <span style="font-size:0.7rem; color:#3fb950;">Ana Kategori</span>
                  <?php endif; ?>
                </div>
              </div>

              <form method="POST" style="margin:0;" onsubmit="return confirm('<?= addslashes($cName) ?> kategorisini silmek istediğinizden emin misiniz?');">
                <input type="hidden" name="action" value="delete_category">
                <input type="hidden" name="category_id" value="<?= $cId ?>">
                <button type="submit" class="btn-sm" style="background:#2a1116; border:1px solid #6b1d28; color:#f87171; font-weight:600; font-size:0.75rem; padding:4px 10px; border-radius:4px; cursor:pointer;" title="Kategoriyi Sil">
                  Sil
                </button>
              </form>
            </div>
          <?php endforeach; ?>
        </div>
      </div>
    </div>

    <!-- 2. TAB: KULLANICILAR -->
    <div id="admTab_kullanicilar" class="tab-content <?= $activeTab === 'kullanicilar' ? 'active' : '' ?>">
      <div class="page-breadcrumb">ADMIN / ÇALIŞMA ALANI</div>
      <h1 class="page-heading">Kullanıcı Yönetimi</h1>
      <p class="page-subheading">Kullanıcı hesapları, KYC doğrulama ve hesap askıya alma (Ban)</p>

      <div class="panel-card">
        <table class="data-table">
          <thead>
            <tr>
              <th>ID</th>
              <th>Ad Soyad</th>
              <th>E-Posta</th>
              <th>Rol</th>
              <th>KYC</th>
              <th>Durum</th>
              <th>Erişim İşlemi</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($users as $u): ?>
              <tr>
                <td>#<?= $u['id'] ?></td>
                <td style="font-weight:600; color:#fff;"><?= htmlspecialchars($u['ad'] . ' ' . $u['soyad']) ?></td>
                <td style="color:var(--text-muted);"><?= htmlspecialchars($u['email']) ?></td>
                <td><span class="badge badge-gold"><?= htmlspecialchars($u['rol']) ?></span></td>
                <td>
                  <?php if (!empty($u['kycDogrulama']['dogrulandiMi'])): ?>
                    <span style="color:#3fb950; font-weight:600;">✓ Doğrulandı</span>
                  <?php else: ?>
                    <form method="POST" style="display:inline; margin:0;">
                      <input type="hidden" name="action" value="verify_kyc">
                      <input type="hidden" name="user_id" value="<?= $u['id'] ?>">
                      <input type="hidden" name="decision" value="approve">
                      <button type="submit" class="btn-outline-gold btn-sm" style="padding:2px 8px; font-size:0.75rem;" title="Kullanıcı kimliğini doğrula ve onay statüsüne geçir">✓ KYC Onayla</button>
                    </form>
                  <?php endif; ?>
                </td>
                <td>
                  <span class="badge <?= ($u['durum'] ?? '') === 'AKTIF' ? 'badge-success' : 'badge-danger' ?>">
                    <?= htmlspecialchars($u['durum'] ?? 'AKTIF') ?>
                  </span>
                </td>
                <td>
                  <?php if ($u['rol'] !== 'ADMIN'): ?>
                    <form method="POST" style="margin:0; display:inline;">
                      <input type="hidden" name="action" value="toggle_ban">
                      <input type="hidden" name="user_id" value="<?= $u['id'] ?>">
                      <?php if (($u['durum'] ?? '') === 'AKTIF'): ?>
                        <input type="hidden" name="new_status" value="ASKIDA">
                        <button type="submit" class="btn-outline-gold btn-sm" style="color:#f85149 !important; border-color:#f85149;">Askıya Al</button>
                      <?php else: ?>
                        <input type="hidden" name="new_status" value="AKTIF">
                        <button type="submit" class="btn-gold btn-sm">Yasağı Kaldır</button>
                      <?php endif; ?>
                    </form>
                  <?php else: ?>
                    <span style="font-size:0.75rem; color:var(--text-muted);">Sistem Sahibi</span>
                  <?php endif; ?>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>

    <!-- ROL & YETKİ YÖNETİMİ TABI — SADE, KART TABANLI, SIFIR TABLO -->
    <div id="admTab_yetkiler" class="tab-content <?= $activeTab === 'yetkiler' ? 'active' : '' ?>">
      <div class="page-breadcrumb">ADMIN / ÇALIŞMA ALANI / ROL & ERİŞİM YÖNETİMİ</div>
      <div style="display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:20px; flex-wrap:wrap; gap:10px;">
        <div>
          <h1 class="page-heading">Kullanıcı Rolleri ve Erişim Yönetimi</h1>
          <p class="page-subheading" style="color:var(--gold-primary); font-weight:500;">
            Kullanıcıların rollerini belirleme ve sistem içi erişimlerini anlık kısıtlama masası.
          </p>
        </div>
        <span class="badge badge-gold" style="font-size:0.78rem; padding:6px 14px;">RBAC & Erişim Denetimi</span>
      </div>

      <!-- 4 Temel Rol Hızlı Bilgi Şeritleri -->
      <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(220px, 1fr)); gap:12px; margin-bottom:24px;">
        <div style="background:#090d14; border:1px solid #1f2736; border-left:3px solid #3fb950; border-radius:8px; padding:12px 14px;">
          <div style="color:#3fb950; font-weight:700; font-size:0.88rem; margin-bottom:4px;">🟢 Alıcı (Buyer)</div>
          <div style="font-size:0.75rem; color:#8b949e; line-height:1.4;">Canlı açık artırmaya katılır, pey sürer. VIP kademede (Gold/Platinum) özel salona erişir.</div>
        </div>
        <div style="background:#090d14; border:1px solid #1f2736; border-left:3px solid #60a5fa; border-radius:8px; padding:12px 14px;">
          <div style="color:#60a5fa; font-weight:700; font-size:0.88rem; margin-bottom:4px;">🔵 Satıcı (Seller)</div>
          <div style="font-size:0.75rem; color:#8b949e; line-height:1.4;">Eser ilanı oluşturur, ekspertiz talep eder. Kendi lotuna teklif veremez.</div>
        </div>
        <div style="background:#090d14; border:1px solid #1f2736; border-left:3px solid #e3b341; border-radius:8px; padding:12px 14px;">
          <div style="color:#e3b341; font-weight:700; font-size:0.88rem; margin-bottom:4px;">🟡 Eksper (Appraiser)</div>
          <div style="font-size:0.75rem; color:#8b949e; line-height:1.4;">Kondisyon skorlar, orijinallik sertifikası ve değerleme onayı düzenler.</div>
        </div>
        <div style="background:#090d14; border:1px solid #1f2736; border-left:3px solid #d4af37; border-radius:8px; padding:12px 14px;">
          <div style="color:#d4af37; font-weight:700; font-size:0.88rem; margin-bottom:4px;">🔴 Admin (Yönetici)</div>
          <div style="font-size:0.75rem; color:#8b949e; line-height:1.4;">Kullanıcı doğrulama, rol atama, kısıtlama ve müzayede idaresinde tam yetkilidir.</div>
        </div>
      </div>

      <!-- BÖLÜM 1: KULLANICININ ROLLERİNİ DEĞİŞTİRME -->
      <div class="panel-card" style="margin-bottom:28px; border:1px solid rgba(212,175,55,0.25); background:linear-gradient(180deg, #111622 0%, #0a0e16 100%); border-radius:10px; padding:22px;">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:18px; border-bottom:1px solid #1f2736; padding-bottom:12px;">
          <div>
            <div style="display:flex; align-items:center; gap:8px;">
              <span style="display:inline-block; width:10px; height:10px; background:var(--gold-primary); border-radius:50%;"></span>
              <h2 style="font-size:1.1rem; font-weight:700; color:#fff; margin:0;">Bölüm 1: Kullanıcı Rollerini Değiştirme</h2>
            </div>
            <p style="font-size:0.8rem; color:#8b949e; margin:4px 0 0 18px;">
              Kullanıcının sistemdeki rolünü ve yetki seviyesini seçerek güncelleyin.
            </p>
          </div>
          <span class="badge" style="background:#161f30; color:#d4af37; border:1px solid #2d3e5e;">Rol Değiştirme</span>
        </div>

        <!-- Rol Değiştirme Formu (Sade ve Kart Düzeni) -->
        <form method="POST" style="margin:0 0 20px 0;">
          <input type="hidden" name="action" value="update_user_role_and_permissions">

          <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(280px, 1fr)); gap:16px; margin-bottom:16px;">
            <div>
              <label style="display:block; font-size:0.82rem; font-weight:600; color:#c5cbd3; margin-bottom:6px;">
                1. Kullanıcıyı Seçin
              </label>
              <select name="user_id" id="roleChangeUserSelect" class="form-control" style="background:#07090e; border:1px solid #2a3449; color:#fff; padding:10px 12px; font-size:0.88rem; width:100%; border-radius:6px;">
                <?php foreach ($users as $u): 
                  $uid = (int)($u['id'] ?? 0);
                  $urole = strtoupper((string)($u['rol'] ?? 'ALICI'));
                  $tier = strtoupper((string)($u['yetkiSeviyesi'] ?? ($u['aliciProfili']['vipSeviyesi'] ?? 'STANDART')));
                  $uName = htmlspecialchars(($u['ad'] ?? '') . ' ' . ($u['soyad'] ?? ''));
                  $isKyc = !empty($u['kycDogrulama']['dogrulandiMi']);
                ?>
                  <option value="<?= $uid ?>">
                    #<?= $uid ?> — <?= $uName ?> (Mevcut: <?= $urole ?> / <?= $tier ?>) <?= $isKyc ? '✓ Onaylı' : '⏳ Bekliyor' ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>

            <div>
              <label style="display:block; font-size:0.82rem; font-weight:600; color:#c5cbd3; margin-bottom:6px;">
                2. Yeni Rol ve Seviye Seçin
              </label>
              <select name="new_role" class="form-control" style="background:#07090e; border:1px solid #2a3449; color:#fff; padding:10px 12px; font-size:0.88rem; width:100%; border-radius:6px;">
                <optgroup label="Alıcı Rolleri">
                  <option value="ALICI:STANDART">🟢 Alıcı — Standart Müzayede Katılımı</option>
                  <option value="ALICI:GOLD">👑 Alıcı — VIP Gold (VIP Salon + ₺150K Limit)</option>
                  <option value="ALICI:PLATINUM">💎 Alıcı — VIP Platinum (Yüksek Portföy & Özel Temsil)</option>
                </optgroup>
                <optgroup label="Satıcı Rolleri">
                  <option value="SATICI:BIREYSEL">🔵 Satıcı — Bireysel (Standart 3 İlan)</option>
                  <option value="SATICI:KURUMSAL">🏢 Satıcı — Kurumsal (Galeri, Müzayede Evi)</option>
                </optgroup>
                <optgroup label="Ekspertiz & Yönetim">
                  <option value="EKSPERTIZ:BAS_EKSPER">🟡 Ekspertiz — Baş Eksper (Kondisyon & Orijinallik Onayı)</option>
                  <option value="ADMIN:SUPER_ADMIN">🔴 Admin — Sistem Yöneticisi (Tam Yetki)</option>
                </optgroup>
              </select>
            </div>
          </div>

          <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:12px; background:#080b11; border:1px solid #1a2333; border-radius:6px; padding:12px 16px;">
            <div style="font-size:0.8rem; color:#8b949e;">
              ⚡ Rol seçildiğinde ilgili role ait operasyonel işlem izinleri arka planda anında güncellenir.
            </div>
            <button type="submit" class="btn-gold" style="padding:9px 24px; font-weight:700; font-size:0.88rem; border-radius:6px; cursor:pointer;">
              Rolü Güncelle
            </button>
          </div>
        </form>

        <!-- Kullanıcı Rolleri Hızlı Bakış Kartları (SIFIR TABLO) -->
        <div style="margin-top:16px;">
          <div style="font-size:0.82rem; font-weight:600; color:#8b949e; text-transform:uppercase; letter-spacing:0.5px; margin-bottom:10px;">
            Kayıtlı Kullanıcılar ve Güncel Rol Durumları (<?= count($users) ?>)
          </div>
          <div style="display:grid; grid-template-columns:repeat(auto-fill, minmax(260px, 1fr)); gap:10px;">
            <?php foreach ($users as $u): 
              $uid = (int)($u['id'] ?? 0);
              $urole = strtoupper((string)($u['rol'] ?? 'ALICI'));
              $tier = strtoupper((string)($u['yetkiSeviyesi'] ?? ($u['aliciProfili']['vipSeviyesi'] ?? 'STANDART')));
              $isKyc = !empty($u['kycDogrulama']['dogrulandiMi']);
              $isBanned = ($u['durum'] ?? '') === 'ASKIDA';
            ?>
              <div style="background:#080b11; border:1px solid #1d2535; border-radius:8px; padding:12px 14px; display:flex; justify-content:space-between; align-items:center;">
                <div>
                  <div style="font-weight:600; font-size:0.86rem; color:#fff;">
                    #<?= $uid ?> <?= htmlspecialchars(($u['ad'] ?? '') . ' ' . ($u['soyad'] ?? '')) ?>
                  </div>
                  <div style="font-size:0.73rem; color:#8b949e; margin-top:2px;">
                    <?= htmlspecialchars($u['email'] ?? '') ?>
                  </div>
                  <div style="margin-top:4px;">
                    <?php if ($isKyc): ?>
                      <span style="font-size:0.68rem; color:#3fb950; font-weight:600;">✓ KYC Onaylı</span>
                    <?php else: ?>
                      <span style="font-size:0.68rem; color:#e3b341; font-weight:600;">⏳ KYC Bekliyor</span>
                    <?php endif; ?>
                  </div>
                </div>
                <div style="text-align:right;">
                  <span class="badge <?php 
                    echo match($urole) {
                      'ADMIN' => 'badge-gold',
                      'EKSPERTIZ' => 'badge-info',
                      'SATICI' => 'badge-primary',
                      default => (in_array($tier, ['GOLD', 'PLATINUM'], true) ? 'badge-gold' : 'badge-success')
                    };
                  ?>" style="font-size:0.72rem; padding:4px 8px;">
                    <?= $urole ?> <?= in_array($tier, ['GOLD', 'PLATINUM'], true) ? "({$tier})" : "" ?>
                  </span>
                  <?php if ($isBanned): ?>
                    <div style="font-size:0.68rem; color:#f87171; font-weight:700; margin-top:3px;">[ASKIDA]</div>
                  <?php endif; ?>
                </div>
              </div>
            <?php endforeach; ?>
          </div>
        </div>
      </div>

      <!-- BÖLÜM 2: ERİŞİMLERİ SINIRLAMA -->
      <div class="panel-card" style="border:1px solid rgba(220,53,69,0.3); background:linear-gradient(180deg, #161118 0%, #0d0a10 100%); border-radius:10px; padding:22px;">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:18px; border-bottom:1px solid #2d1e26; padding-bottom:12px;">
          <div>
            <div style="display:flex; align-items:center; gap:8px;">
              <span style="display:inline-block; width:10px; height:10px; background:#f87171; border-radius:50%;"></span>
              <h2 style="font-size:1.1rem; font-weight:700; color:#fff; margin:0;">Bölüm 2: Erişimleri Sınırlama</h2>
            </div>
            <p style="font-size:0.8rem; color:#8b949e; margin:4px 0 0 18px;">
              Kullanıcıların pey verme, VIP salon, ilan açma, ekspertiz onaylama ve hesap durumunu kısıtlayın veya yeniden izin verin.
            </p>
          </div>
          <span class="badge" style="background:#2a141b; color:#f87171; border:1px solid #5a202d;">Erişim Kısıtlama</span>
        </div>

        <!-- Hızlı Kısıtlama / İzin Verme Kontrol Masası -->
        <div style="margin-bottom:20px;">
          <div style="font-size:0.82rem; font-weight:600; color:#c5cbd3; margin-bottom:8px;">
            Kısıtlama Uygulanacak Kullanıcıyı Seçin:
          </div>
          <select id="restrictTargetUserSelect" class="form-control" style="background:#07090e; border:1px solid #3d232e; color:#fff; padding:10px 12px; font-size:0.88rem; width:100%; max-width:500px; border-radius:6px;" onchange="syncRestrictUserId(this.value)">
            <?php foreach ($users as $u): 
              $uid = (int)($u['id'] ?? 0);
              $uName = htmlspecialchars(($u['ad'] ?? '') . ' ' . ($u['soyad'] ?? ''));
              $urole = strtoupper((string)($u['rol'] ?? 'ALICI'));
              $k = $u['kisitlamalar'] ?? [];
              $hasAny = !empty($k['peyEngeli']) || !empty($k['vipEngeli']) || !empty($k['ilanEngeli']) || !empty($k['ekspertizEngeli']) || ($u['durum'] ?? '') === 'ASKIDA';
            ?>
              <option value="<?= $uid ?>">
                #<?= $uid ?> — <?= $uName ?> (<?= $urole ?>) <?= $hasAny ? '⚠️ KISITLAMA VAR' : '✓ Normal' ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>

        <!-- 5 Temel Erişim Kısıtlama Kartı (SIFIR TABLO) -->
        <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(280px, 1fr)); gap:14px; margin-bottom:22px;">
          
          <!-- 1. Pey Verme Kısıtlaması -->
          <div style="background:#0a0c13; border:1px solid #231c26; border-radius:8px; padding:16px; display:flex; flex-direction:column; justify-content:space-between;">
            <div>
              <div style="font-weight:700; font-size:0.9rem; color:#fff; margin-bottom:4px;">
                🔨 Canlı Müzayedede Pey Verme
              </div>
              <p style="font-size:0.75rem; color:#8b949e; line-height:1.4; margin:0 0 12px 0;">
                Kullanıcının açık artırmalarda teklif vermesini engeller. (Shill bidding veya ödeme aksamalarında uygulanır).
              </p>
            </div>
            <div style="display:flex; gap:8px;">
              <form method="POST" style="margin:0; flex:1;">
                <input type="hidden" name="action" value="restrict_user_access">
                <input type="hidden" name="user_id" class="restrictUserHiddenInput" value="<?= (int)($users[0]['id'] ?? 1) ?>">
                <input type="hidden" name="restriction_type" value="peyEngeli">
                <input type="hidden" name="is_restricted" value="1">
                <button type="submit" class="btn-sm" style="width:100%; background:#2d1116; border:1px solid #6b1d28; color:#f87171; font-weight:600; padding:6px 0; border-radius:4px; cursor:pointer;">
                  Pey Vermeyi Kısıtla
                </button>
              </form>
              <form method="POST" style="margin:0; flex:1;">
                <input type="hidden" name="action" value="restrict_user_access">
                <input type="hidden" name="user_id" class="restrictUserHiddenInput" value="<?= (int)($users[0]['id'] ?? 1) ?>">
                <input type="hidden" name="restriction_type" value="peyEngeli">
                <input type="hidden" name="is_restricted" value="0">
                <button type="submit" class="btn-sm" style="width:100%; background:#0f241a; border:1px solid #1a5c38; color:#3fb950; font-weight:600; padding:6px 0; border-radius:4px; cursor:pointer;">
                  İzin Ver
                </button>
              </form>
            </div>
          </div>

          <!-- 2. VIP Salon Erişimi Kısıtlaması -->
          <div style="background:#0a0c13; border:1px solid #231c26; border-radius:8px; padding:16px; display:flex; flex-direction:column; justify-content:space-between;">
            <div>
              <div style="font-weight:700; font-size:0.9rem; color:#fff; margin-bottom:4px;">
                👑 VIP Özel Salon Erişimi
              </div>
              <p style="font-size:0.75rem; color:#8b949e; line-height:1.4; margin:0 0 12px 0;">
                Yüksek portföy ve VIP kural stratejisine sahip kapalı salonlara girişi kısıtlar.
              </p>
            </div>
            <div style="display:flex; gap:8px;">
              <form method="POST" style="margin:0; flex:1;">
                <input type="hidden" name="action" value="restrict_user_access">
                <input type="hidden" name="user_id" class="restrictUserHiddenInput" value="<?= (int)($users[0]['id'] ?? 1) ?>">
                <input type="hidden" name="restriction_type" value="vipEngeli">
                <input type="hidden" name="is_restricted" value="1">
                <button type="submit" class="btn-sm" style="width:100%; background:#2d1116; border:1px solid #6b1d28; color:#f87171; font-weight:600; padding:6px 0; border-radius:4px; cursor:pointer;">
                  VIP'yi Kısıtla
                </button>
              </form>
              <form method="POST" style="margin:0; flex:1;">
                <input type="hidden" name="action" value="restrict_user_access">
                <input type="hidden" name="user_id" class="restrictUserHiddenInput" value="<?= (int)($users[0]['id'] ?? 1) ?>">
                <input type="hidden" name="restriction_type" value="vipEngeli">
                <input type="hidden" name="is_restricted" value="0">
                <button type="submit" class="btn-sm" style="width:100%; background:#0f241a; border:1px solid #1a5c38; color:#3fb950; font-weight:600; padding:6px 0; border-radius:4px; cursor:pointer;">
                  İzin Ver
                </button>
              </form>
            </div>
          </div>

          <!-- 3. Eser & İlan Açma Kısıtlaması -->
          <div style="background:#0a0c13; border:1px solid #231c26; border-radius:8px; padding:16px; display:flex; flex-direction:column; justify-content:space-between;">
            <div>
              <div style="font-weight:700; font-size:0.9rem; color:#fff; margin-bottom:4px;">
                📦 Eser Yükleme ve İlan Açma
              </div>
              <p style="font-size:0.75rem; color:#8b949e; line-height:1.4; margin:0 0 12px 0;">
                Kullanıcının platformda satışa yeni eser çıkarmasını ve ilan yayımlamasını engeller.
              </p>
            </div>
            <div style="display:flex; gap:8px;">
              <form method="POST" style="margin:0; flex:1;">
                <input type="hidden" name="action" value="restrict_user_access">
                <input type="hidden" name="user_id" class="restrictUserHiddenInput" value="<?= (int)($users[0]['id'] ?? 1) ?>">
                <input type="hidden" name="restriction_type" value="ilanEngeli">
                <input type="hidden" name="is_restricted" value="1">
                <button type="submit" class="btn-sm" style="width:100%; background:#2d1116; border:1px solid #6b1d28; color:#f87171; font-weight:600; padding:6px 0; border-radius:4px; cursor:pointer;">
                  İlanı Kısıtla
                </button>
              </form>
              <form method="POST" style="margin:0; flex:1;">
                <input type="hidden" name="action" value="restrict_user_access">
                <input type="hidden" name="user_id" class="restrictUserHiddenInput" value="<?= (int)($users[0]['id'] ?? 1) ?>">
                <input type="hidden" name="restriction_type" value="ilanEngeli">
                <input type="hidden" name="is_restricted" value="0">
                <button type="submit" class="btn-sm" style="width:100%; background:#0f241a; border:1px solid #1a5c38; color:#3fb950; font-weight:600; padding:6px 0; border-radius:4px; cursor:pointer;">
                  İzin Ver
                </button>
              </form>
            </div>
          </div>

          <!-- 4. Ekspertiz Onay Yetkisi Kısıtlaması -->
          <div style="background:#0a0c13; border:1px solid #231c26; border-radius:8px; padding:16px; display:flex; flex-direction:column; justify-content:space-between;">
            <div>
              <div style="font-weight:700; font-size:0.9rem; color:#fff; margin-bottom:4px;">
                🔍 Ekspertiz Rapor Onaylama
              </div>
              <p style="font-size:0.75rem; color:#8b949e; line-height:1.4; margin:0 0 12px 0;">
                Ekspertiz uzmanının orijinallik ve kondisyon raporu düzenleme yetkisini askıya alır.
              </p>
            </div>
            <div style="display:flex; gap:8px;">
              <form method="POST" style="margin:0; flex:1;">
                <input type="hidden" name="action" value="restrict_user_access">
                <input type="hidden" name="user_id" class="restrictUserHiddenInput" value="<?= (int)($users[0]['id'] ?? 1) ?>">
                <input type="hidden" name="restriction_type" value="ekspertizEngeli">
                <input type="hidden" name="is_restricted" value="1">
                <button type="submit" class="btn-sm" style="width:100%; background:#2d1116; border:1px solid #6b1d28; color:#f87171; font-weight:600; padding:6px 0; border-radius:4px; cursor:pointer;">
                  Eksperi Kısıtla
                </button>
              </form>
              <form method="POST" style="margin:0; flex:1;">
                <input type="hidden" name="action" value="restrict_user_access">
                <input type="hidden" name="user_id" class="restrictUserHiddenInput" value="<?= (int)($users[0]['id'] ?? 1) ?>">
                <input type="hidden" name="restriction_type" value="ekspertizEngeli">
                <input type="hidden" name="is_restricted" value="0">
                <button type="submit" class="btn-sm" style="width:100%; background:#0f241a; border:1px solid #1a5c38; color:#3fb950; font-weight:600; padding:6px 0; border-radius:4px; cursor:pointer;">
                  İzin Ver
                </button>
              </form>
            </div>
          </div>

          <!-- 5. Hesabı Askıya Alma (Dondurma / Kilit) -->
          <div style="background:#0a0c13; border:1px solid #3d1b26; border-radius:8px; padding:16px; display:flex; flex-direction:column; justify-content:space-between;">
            <div>
              <div style="font-weight:700; font-size:0.9rem; color:#f87171; margin-bottom:4px;">
                🚫 Hesabı Tamamen Askıya Alma
              </div>
              <p style="font-size:0.75rem; color:#8b949e; line-height:1.4; margin:0 0 12px 0;">
                Kullanıcının platforma erişimini, oturumunu ve tüm işlemlerini tamamen dondurur.
              </p>
            </div>
            <div style="display:flex; gap:8px;">
              <form method="POST" style="margin:0; flex:1;">
                <input type="hidden" name="action" value="restrict_user_access">
                <input type="hidden" name="user_id" class="restrictUserHiddenInput" value="<?= (int)($users[0]['id'] ?? 1) ?>">
                <input type="hidden" name="restriction_type" value="hesapAskisi">
                <input type="hidden" name="is_restricted" value="1">
                <button type="submit" class="btn-sm" style="width:100%; background:#47121b; border:1px solid #8e2132; color:#fff; font-weight:700; padding:6px 0; border-radius:4px; cursor:pointer;">
                  Hesabı Askıya Al
                </button>
              </form>
              <form method="POST" style="margin:0; flex:1;">
                <input type="hidden" name="action" value="restrict_user_access">
                <input type="hidden" name="user_id" class="restrictUserHiddenInput" value="<?= (int)($users[0]['id'] ?? 1) ?>">
                <input type="hidden" name="restriction_type" value="hesapAskisi">
                <input type="hidden" name="is_restricted" value="0">
                <button type="submit" class="btn-sm" style="width:100%; background:#0f241a; border:1px solid #1a5c38; color:#3fb950; font-weight:600; padding:6px 0; border-radius:4px; cursor:pointer;">
                  Aktifleştir
                </button>
              </form>
            </div>
          </div>
        </div>

        <!-- Aktif Kısıtlama Takip Listesi (SIFIR TABLO, SADE KARTLAR) -->
        <div>
          <div style="font-size:0.82rem; font-weight:600; color:#8b949e; text-transform:uppercase; letter-spacing:0.5px; margin-bottom:10px;">
            Kısıtlama ve İzin Durumları Özeti
          </div>
          <div style="display:grid; grid-template-columns:repeat(auto-fill, minmax(300px, 1fr)); gap:12px;">
            <?php foreach ($users as $u): 
              $uid = (int)($u['id'] ?? 0);
              $k = $u['kisitlamalar'] ?? [];
              $isSuspended = ($u['durum'] ?? '') === 'ASKIDA';
              $activeRestrictions = [];
              if (!empty($k['peyEngeli'])) $activeRestrictions[] = 'Pey Yasağı';
              if (!empty($k['vipEngeli'])) $activeRestrictions[] = 'VIP Giriş Yasağı';
              if (!empty($k['ilanEngeli'])) $activeRestrictions[] = 'İlan Yasağı';
              if (!empty($k['ekspertizEngeli'])) $activeRestrictions[] = 'Eksper Yasağı';
              if ($isSuspended) $activeRestrictions[] = 'HESAP DONDURULDU';
            ?>
              <div style="background:#090d14; border:1px solid <?= empty($activeRestrictions) ? '#1c2433' : '#4d1e27' ?>; border-radius:8px; padding:12px 14px;">
                <div style="display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:6px;">
                  <div>
                    <div style="font-weight:600; font-size:0.85rem; color:#fff;">
                      #<?= $uid ?> <?= htmlspecialchars(($u['ad'] ?? '') . ' ' . ($u['soyad'] ?? '')) ?>
                    </div>
                    <div style="font-size:0.73rem; color:#8b949e;">
                      <?= htmlspecialchars($u['email'] ?? '') ?>
                    </div>
                  </div>
                  <?php if (empty($activeRestrictions)): ?>
                    <span class="badge badge-success" style="font-size:0.68rem; padding:3px 8px;">Tam Erişim</span>
                  <?php else: ?>
                    <span class="badge badge-danger" style="font-size:0.68rem; padding:3px 8px;">Kısıtlı</span>
                  <?php endif; ?>
                </div>

                <div style="margin-top:8px; display:flex; flex-wrap:wrap; gap:4px;">
                  <?php if (empty($activeRestrictions)): ?>
                    <span style="font-size:0.72rem; color:#5c6b7d;">Hiçbir kısıtlama yok, tüm fonksiyonlar açık.</span>
                  <?php else: ?>
                    <?php foreach ($activeRestrictions as $ar): ?>
                      <span style="font-size:0.7rem; background:#36131a; border:1px solid #6b1f2b; color:#f87171; border-radius:4px; padding:2px 6px;">
                        ✕ <?= $ar ?>
                      </span>
                    <?php endforeach; ?>
                  <?php endif; ?>
                </div>
              </div>
            <?php endforeach; ?>
          </div>
        </div>
      </div>
    </div>

    <script>
      function syncRestrictUserId(val) {
        document.querySelectorAll('.restrictUserHiddenInput').forEach(function(el) {
          el.value = val;
        });
      }
    </script>
    </div>

    

    <!-- 6. TAB: KOMİSYON VE ÜCRET TANIMLAMA -->
    <div id="admTab_komisyon" class="tab-content <?= $activeTab === 'komisyon' ? 'active' : '' ?>">
      <div class="page-breadcrumb">ADMIN / ÇALIŞMA ALANI</div>
      <h1 class="page-heading">Komisyon ve Ücret Tanımlama</h1>
      <p class="page-subheading">Platform gelir modeli, kademeli komisyon ve emanet bloke oranları (Strategy)</p>

      <div class="panel-card">
        <div style="display:grid; grid-template-columns:1fr 1fr; gap:20px;">
          <div>
            <div style="font-weight:700; color:var(--gold-primary); margin-bottom:8px;">Sanat & Antika Komisyon Stratejisi (ArtAntiqueCommission)</div>
            <p style="font-size:0.85rem; color:var(--text-muted); line-height:1.5;">
              Kapanış tutarı üzerinden <strong>%8 Alıcı / Satıcı Hizmet Payı</strong> + <strong>₺1.500 Sabit Kurul Bedeli</strong> tahsil edilerek teminat emanet hesabına yansıtılır.
            </p>
          </div>
          <div>
            <div style="font-weight:700; color:var(--gold-primary); margin-bottom:8px;">Özel Taşıt & VIP Stratejisi (VehicleCommission)</div>
            <p style="font-size:0.85rem; color:var(--text-muted); line-height:1.5;">
              Yüksek hacimli lotlarda <strong>%4 Kademeli Komisyon</strong> ve doğrudan blokeli emanet takas (Escrow) uygulanır.
            </p>
          </div>
        </div>
      </div>
    </div>

    

    <!-- 9. TAB: DENETİM İZİ (media_1795014776304_85a3b2e7.jpg ile Birebir) -->
    <div id="admTab_denetim" class="tab-content <?= $activeTab === 'denetim' ? 'active' : '' ?>">
      <div class="page-breadcrumb">ADMIN / ÇALIŞMA ALANI</div>
      <h1 class="page-heading">Denetim izi</h1>
      <p class="page-subheading" style="margin-bottom:24px;">Seçkin müzayede deneyiminizi yönetin</p>

      <div class="panel-card" style="padding:0; overflow:hidden;">
        <table class="data-table" style="margin:0;">
          <thead>
            <tr>
              <th style="padding-left:24px; width:35%;">Tarih</th>
              <th style="width:35%;">Rol</th>
              <th style="padding-right:24px; width:30%;">İşlem</th>
            </tr>
          </thead>
          <tbody>
            <?php
            $auditRows = [
              ['tarih' => '14.10.2026 22:13:47', 'rol' => 'Ekspertiz Uzmanı', 'islem' => 'report'],
              ['tarih' => '13.10.2026 12:57:19', 'rol' => 'Admin', 'islem' => 'listing'],
              ['tarih' => '13.10.2026 12:56:22', 'rol' => 'Alıcı', 'islem' => 'watch'],
              ['tarih' => '13.10.2026 12:56:18', 'rol' => 'Alıcı', 'islem' => 'bid'],
              ['tarih' => '13.10.2026 12:56:01', 'rol' => 'Alıcı', 'islem' => 'balance'],
              ['tarih' => '13.10.2026 12:55:46', 'rol' => 'Alıcı', 'islem' => 'balance'],
              ['tarih' => '13.10.2026 12:20:00', 'rol' => 'Canlı Destek', 'islem' => 'message'],
              ['tarih' => '13.10.2026 12:19:37', 'rol' => 'Canlı Destek', 'islem' => 'preference'],
              ['tarih' => '13.10.2026 12:19:25', 'rol' => 'Ekspertiz Uzmanı', 'islem' => 'preference'],
              ['tarih' => '13.10.2026 12:18:50', 'rol' => 'Satıcı', 'islem' => 'preference'],
              ['tarih' => '13.10.2026 12:18:03', 'rol' => 'Alıcı', 'islem' => 'preference'],
              ['tarih' => '13.10.2026 12:17:59', 'rol' => 'Alıcı', 'islem' => 'bid'],
              ['tarih' => '13.10.2026 12:17:27', 'rol' => 'Alıcı', 'islem' => 'preference'],
              ['tarih' => '13.10.2026 00:38:51', 'rol' => 'Ekspertiz Uzmanı', 'islem' => 'preference'],
              ['tarih' => '13.10.2026 00:37:27', 'rol' => 'Alıcı', 'islem' => 'preference'],
            ];
            foreach ($auditRows as $r):
            ?>
              <tr>
                <td style="padding-left:24px; color:#848d9a; font-size:0.85rem;"><?= $r['tarih'] ?></td>
                <td style="color:#f0f3f6; font-size:0.85rem; font-weight:500;"><?= $r['rol'] ?></td>
                <td style="padding-right:24px; color:#f0f3f6; font-size:0.85rem;"><?= $r['islem'] ?></td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>

    <!-- 10. TAB: SİSTEM AYARLARI (media_1795014776310_317f5780.jpg ile Birebir) -->
    <div id="admTab_ayarlar" class="tab-content <?= $activeTab === 'ayarlar' ? 'active' : '' ?>">
      <div class="page-breadcrumb">ADMIN / ÇALIŞMA ALANI</div>
      <h1 class="page-heading">Sistem ayarları</h1>
      <p class="page-subheading" style="margin-bottom:24px;">Seçkin müzayede deneyiminizi yönetin</p>

      <div class="panel-card" style="margin-bottom:20px;">
        <h3 class="panel-title" style="margin-bottom:8px;">Sistem Parametreleri (Use Case: Sistem Parametreleri Ayarlama)</h3>
        <p style="font-size:0.85rem; color:#848d9a; margin-bottom:16px;">
          Anti-Sniping son 1 dk kuralı süresi, asgari artış kuralı ve platform komisyon oranları.
        </p>
        <form method="POST" style="display:grid; grid-template-columns:1fr 1fr 1fr 1fr auto; gap:12px; align-items:flex-end;">
          <input type="hidden" name="action" value="update_system_params">
          <div>
            <label class="form-label">Anti-Sniping Süre (Sn)</label>
            <input type="number" name="anti_sniping_seconds" class="form-control" value="60" required>
          </div>
          <div>
            <label class="form-label">Min Pey Adımı (TRY)</label>
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
          <div>
            <button type="submit" class="btn-gold" style="white-space:nowrap; padding:10px 18px;">Kaydet</button>
          </div>
        </form>
      </div>

      <div class="panel-card">
        <h3 class="panel-title" style="margin-bottom:8px;">Personel ve Yetkilendirme</h3>
        <p style="font-size:0.88rem; color:#848d9a; margin-bottom:16px;">
          Sisteme yeni Canlı Destek Temsilcisi veya Ekspertiz Uzmanı hesabı tanımlayın.
        </p>
        <button type="button" onclick="openDemoSupportModal()" class="btn-dark-pill" style="padding:10px 20px; font-size:0.85rem; cursor:pointer;">
          Demo destek temsilcisi ekle
        </button>
      </div>
    </div>

  </main>
</div>

<!-- Demo Destek Temsilcisi Modalı (Görseldeki Birebir Modal) -->
<div id="demoSupportModal" class="martello-modal" style="display:none; position:fixed; z-index:9999; left:0; top:0; width:100%; height:100%; background:rgba(0,0,0,0.85); backdrop-filter:blur(6px); align-items:center; justify-content:center;">
  <div style="background:#131722; border:1px solid #2a3449; border-radius:12px; max-width:440px; width:90%; padding:24px; box-shadow:0 10px 30px rgba(0,0,0,0.8); position:relative;">
    <button type="button" onclick="closeDemoSupportModal()" style="position:absolute; right:16px; top:16px; background:none; border:none; color:#888; font-size:1.4rem; cursor:pointer;">&times;</button>
    
    <div style="color:#d4af37; font-size:0.72rem; font-weight:700; letter-spacing:1px; text-transform:uppercase; margin-bottom:4px;">MARTELLO • DEMO İŞLEM</div>
    <div style="font-family:var(--font-serif); font-size:1.45rem; font-weight:600; color:#fff; margin-bottom:18px;">Demo destek temsilcisi</div>

    <form method="POST">
      <input type="hidden" name="action" value="create_staff">
      <input type="hidden" name="rol" value="CANLI_DESTEK">
      <div class="form-group" style="margin-bottom:14px;">
        <label class="form-label" style="font-size:0.82rem; color:#f0f3f6;">Temsilci adı *</label>
        <input type="text" name="ad" class="form-control" placeholder="Örn: Cansu Güneş" required style="border-radius:8px; background:#10131b;">
      </div>

      <div class="form-group" style="margin-bottom:14px;">
        <label class="form-label" style="font-size:0.82rem; color:#f0f3f6;">İş e-postası *</label>
        <input type="email" name="email" class="form-control" placeholder="cansu@martello.com" required style="border-radius:8px; background:#10131b;">
      </div>

      <div style="font-size:0.75rem; color:#788394; margin-bottom:20px;">
        Kayıt doğrudan sisteme işlenir ve Canlı Destek yetkisi tanımlanır.
      </div>

      <button type="submit" class="btn-gold-pill" style="width:100%; padding:12px; font-size:0.9rem; cursor:pointer;">
        Demo kaydı oluştur
      </button>
    </form>
  </div>
</div>

<script>
function switchAdminTab(tabId, btn) {
  if (tabId === 'ihtilaflar') {
    tabId = 'iptal_yonetimi';
  }
  // Hide all tab contents and remove active from nav items
  document.querySelectorAll('.tab-content').forEach(el => el.classList.remove('active'));
  document.querySelectorAll('.sidebar .nav-menu-item').forEach(el => el.classList.remove('active'));

  // Activate target tab content
  const target = document.getElementById('admTab_' + tabId);
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

function switchCancellationSubTab(subTab) {
  document.querySelectorAll('.cancel-subtab-pane').forEach(el => el.style.display = 'none');
  document.querySelectorAll('.cancel-subtab-btn').forEach(btn => {
    btn.classList.remove('btn-gold-pill');
    btn.classList.add('btn-dark-pill');
  });
  const targetPane = document.getElementById('cancel_pane_' + subTab);
  const targetBtn = document.getElementById('btn_cancel_subtab_' + subTab);
  if (targetPane) targetPane.style.display = 'block';
  if (targetBtn) {
    targetBtn.classList.remove('btn-dark-pill');
    targetBtn.classList.add('btn-gold-pill');
  }
}

function openDemoSupportModal() {
  document.getElementById('demoSupportModal').style.display = 'flex';
}

function closeDemoSupportModal() {
  document.getElementById('demoSupportModal').style.display = 'none';
}
</script>
