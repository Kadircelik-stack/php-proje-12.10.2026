<?php
declare(strict_types=1);

/** @var callable $formatPrice */
/** @var array $db */

$expertId = 4; // Burak Şahin (Ekspertiz Uzmanı)
$currentUser = null;
foreach ($db['users'] ?? [] as $u) {
    if ((int)($u['id'] ?? 0) === $expertId) {
        $currentUser = $u;
        break;
    }
}

$allRequests = $db['appraisalRequests'] ?? [];
$myRequests = array_values(array_filter($allRequests, static fn($r) => (int)($r['atananEksperId'] ?? 0) === $expertId || empty($r['atananEksperId'])));
$pendingRequests = array_values(array_filter($myRequests, static fn($r) => in_array($r['durum'] ?? '', ['BEKLEYEN', 'INCELENIYOR', 'REVIZE_ISTENDI'], true)));
$completedReports = array_values(array_filter($db['appraisalReports'] ?? [], static fn($rep) => (int)($rep['eksperId'] ?? 0) === $expertId));

$disputes = $db['disputes'] ?? [];
$notifications = array_values(array_filter($db['notifications'] ?? [], static fn($n) => (int)($n['kullaniciId'] ?? 0) === $expertId || in_array($n['oncelik'] ?? '', ['EKSPERTIZ', 'SYSTEM'], true)));

$products = $db['products'] ?? [];
$categories = $db['categories'] ?? [];
$lots = $db['lots'] ?? [];

$activeTab = $_GET['tab'] ?? 'gorev';
?>

<div class="app-container">
  <!-- Sol Menü (Sidebar) -->
  <aside class="sidebar">
    <div>
      <div class="sidebar-section-title">Çalışma Alanı</div>
      <ul class="nav-menu">
        <li class="nav-menu-item <?= $activeTab === 'gorev' ? 'active' : '' ?>">
          <button type="button" data-tab="gorev" onclick="switchAppraiserTab('gorev', this)">
            <span class="menu-icon"><svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg></span>
            Görev paneli
          </button>
        </li>
        <li class="nav-menu-item <?= $activeTab === 'inceleme' ? 'active' : '' ?>">
          <button type="button" data-tab="inceleme" onclick="switchAppraiserTab('inceleme', this)">
            <span class="menu-icon"><svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg></span>
            İnceleme & Rapor
          </button>
        </li>
        <li class="nav-menu-item <?= $activeTab === 'gecmis' ? 'active' : '' ?>">
          <button type="button" data-tab="gecmis" onclick="switchAppraiserTab('gecmis', this)">
            <span class="menu-icon"><svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg></span>
            Rapor geçmişi
          </button>
        </li>
        <li class="nav-menu-item <?= $activeTab === 'itiraz' ? 'active' : '' ?>">
          <button type="button" data-tab="itiraz" onclick="switchAppraiserTab('itiraz', this)">
            <span class="menu-icon"><svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 11 12 14 22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/></svg></span>
            İtiraz talepleri
          </button>
        </li>
        <li class="nav-menu-item <?= $activeTab === 'arama' ? 'active' : '' ?>">
          <button type="button" data-tab="arama" onclick="switchAppraiserTab('arama', this)">
            <span class="menu-icon"><svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/><circle cx="11" cy="11" r="3"/></svg></span>
            Katalog & Arama
          </button>
        </li>
        <li class="nav-menu-item <?= $activeTab === 'bildirim' ? 'active' : '' ?>">
          <button type="button" data-tab="bildirim" onclick="switchAppraiserTab('bildirim', this)">
            <span class="menu-icon"><svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/></svg></span>
            Bildirimler
          </button>
        </li>
        <li class="nav-menu-item <?= $activeTab === 'profil' ? 'active' : '' ?>">
          <button type="button" data-tab="profil" onclick="switchAppraiserTab('profil', this)">
            <span class="menu-icon"><svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg></span>
            Profil & Yetki
          </button>
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
          <a href="?role=buyer&tab=vip">
            <span class="menu-icon" style="color:#d4af37;"><svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="m2 4 3 12h14l3-12-6 7-4-7-4 7-6-7zm3 16h14"/></svg></span>
            <span style="color:#dfbe7e; font-weight:600;">VIP Müzayede Salonu</span>
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
         1. TAB: GÖREV PANELİ (media_1795014776304_7eb23357.jpg)
         ==================================================================== -->
    <div id="appTab_gorev" class="tab-content <?= $activeTab === 'gorev' ? 'active' : '' ?>">
      <div class="page-breadcrumb">EKSPERTİZ UZMANI / ÇALIŞMA ALANI</div>
      <h1 class="page-heading">Görev paneli</h1>
      <p class="page-subheading" style="margin-bottom:24px;">Atanmış eser inceleme taleplerinizi ve ekspertiz süreçlerini yönetin</p>

      <!-- 4 Adet Dinamik KPI Kartı -->
      <div class="kpi-grid">
        <div class="kpi-card">
          <div class="kpi-label">ATANMIŞ İŞLER</div>
          <div class="kpi-value"><?= count($myRequests) ?></div>
        </div>
        <div class="kpi-card highlight">
          <div class="kpi-label">BEKLEYEN İNCELEME</div>
          <div class="kpi-value"><?= count($pendingRequests) ?></div>
        </div>
        <div class="kpi-card">
          <div class="kpi-label">HAZIRLANAN RAPORLAR</div>
          <div class="kpi-value"><?= count($completedReports) ?></div>
        </div>
        <div class="kpi-card">
          <div class="kpi-label">İTİRAZ TALEPLERİ</div>
          <div class="kpi-value"><?= count($disputes) ?></div>
        </div>
      </div>

      <!-- Görev Kartları Grid -->
      <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:14px;">
        <div style="font-size:0.88rem; color:var(--gold-primary); font-weight:700; text-transform:uppercase; letter-spacing:1px;">
          İnceleme Bekleyen Eser Talepleri
        </div>
        <button type="button" class="btn-outline-gold btn-sm" onclick="switchAppraiserTab('inceleme', this)">
          Doğrudan Rapor Düzenle →
        </button>
      </div>

      <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(310px, 1fr)); gap:20px; margin-bottom:32px;">
        <!-- Örnek Görev 1: AUC-1042 (1968 İsviçre Vintage Saat) -->
        <div class="panel-card" style="margin-bottom:0; display:flex; flex-direction:column; justify-content:space-between; border-color:rgba(197,160,89,0.3);">
          <div>
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:8px;">
              <span class="badge badge-gold">AUC-1042</span>
              <span class="badge badge-warning">BEKLEYEN</span>
            </div>
            <h3 style="font-family:var(--font-serif); font-size:1.2rem; color:#fff; font-weight:600; margin-bottom:8px;">
              1968 İsviçre Vintage Saat
            </h3>
            <p style="font-size:0.84rem; color:var(--text-muted); line-height:1.5; margin-bottom:12px;">
              Calibre 861 mekanizma, kronograf fonksiyonu ve orijinal kadran mikroskop incelemesi bekliyor.
            </p>
            <div style="background:#090c10; padding:8px 12px; border-radius:6px; font-size:0.75rem; color:#848d9a; border:1px solid #1f2735;">
              Talep No: <strong>EXP-REQ-441</strong> • Kategori: Horoloji & Saat
            </div>
          </div>
          <div style="margin-top:20px; display:flex; gap:8px;">
            <button type="button" class="btn-gold btn-sm" style="flex:1; justify-content:center;" onclick="openExpertReportModal(202, 'AUC-1042', '1968 İsviçre Vintage Saat', 65000, 'Saat mekanizması ve kronograf fonksiyonları laboratuvar testlerinde incelenmiş, orijinal fabrikasyon kondisyonunda bulunmuştur.')">
              Rapor Oluştur
            </button>
            <button type="button" class="btn-outline-gold btn-sm" onclick="loadRequestIntoForm(202, 'AUC-1042', '1968 İsviçre Vintage Saat', 65000, 'Saat mekanizması ve kronograf fonksiyonları laboratuvar testlerinde incelenmiş, orijinal fabrikasyon kondisyonunda bulunmuştur.')" title="Detaylı laboratuvar inceleme formuna aktar">
              Detaylı İncele →
            </button>
          </div>
        </div>

        <!-- Örnek Görev 2: AUC-1043 (Koleksiyon Analog Kamera) -->
        <div class="panel-card" style="margin-bottom:0; display:flex; flex-direction:column; justify-content:space-between; border-color:rgba(197,160,89,0.3);">
          <div>
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:8px;">
              <span class="badge badge-gold">AUC-1043</span>
              <span class="badge badge-warning">BEKLEYEN</span>
            </div>
            <h3 style="font-family:var(--font-serif); font-size:1.2rem; color:#fff; font-weight:600; margin-bottom:8px;">
              Koleksiyon Analog Kamera
            </h3>
            <p style="font-size:0.84rem; color:var(--text-muted); line-height:1.5; margin-bottom:12px;">
              Optik lens şeffaflığı, diyafram kademesi ve deklanşör hızı testleri yapılmalıdır.
            </p>
            <div style="background:#090c10; padding:8px 12px; border-radius:6px; font-size:0.75rem; color:#848d9a; border:1px solid #1f2735;">
              Talep No: <strong>EXP-REQ-442</strong> • Kategori: Efemera & Antika
            </div>
          </div>
          <div style="margin-top:20px; display:flex; gap:8px;">
            <button type="button" class="btn-gold btn-sm" style="flex:1; justify-content:center;" onclick="openExpertReportModal(203, 'AUC-1043', 'Koleksiyon Analog Kamera', 25000, 'Kamera aksamı oldukça iyi durumda gerekli incelemeler neticesinde satışa uygun bulunmuştur.')">
              Rapor Oluştur
            </button>
            <button type="button" class="btn-outline-gold btn-sm" onclick="loadRequestIntoForm(203, 'AUC-1043', 'Koleksiyon Analog Kamera', 25000, 'Kamera aksamı oldukça iyi durumda gerekli incelemeler neticesinde satışa uygun bulunmuştur.')" title="Detaylı laboratuvar inceleme formuna aktar">
              Detaylı İncele →
            </button>
          </div>
        </div>

        <!-- Örnek Görev 3: AUC-1044 (Çağdaş Sanat Baskısı) -->
        <div class="panel-card" style="margin-bottom:0; display:flex; flex-direction:column; justify-content:space-between; border-color:rgba(197,160,89,0.3);">
          <div>
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:8px;">
              <span class="badge badge-gold">AUC-1044</span>
              <span class="badge badge-warning">BEKLEYEN</span>
            </div>
            <h3 style="font-family:var(--font-serif); font-size:1.2rem; color:#fff; font-weight:600; margin-bottom:8px;">
              Çağdaş Sanat Baskısı
            </h3>
            <p style="font-size:0.84rem; color:var(--text-muted); line-height:1.5; margin-bottom:12px;">
              Kağıt dokusu, edisyon numarası, sanatçı el ıslak imzası ve spektral asitsizlik değerlendirmesi.
            </p>
            <div style="background:#090c10; padding:8px 12px; border-radius:6px; font-size:0.75rem; color:#848d9a; border:1px solid #1f2735;">
              Talep No: <strong>EXP-REQ-443</strong> • Kategori: Antika & Sanat
            </div>
          </div>
          <div style="margin-top:20px; display:flex; gap:8px;">
            <button type="button" class="btn-gold btn-sm" style="flex:1; justify-content:center;" onclick="openExpertReportModal(204, 'AUC-1044', 'Çağdaş Sanat Baskısı', 18000, 'Kağıt ve baskı kalitesi laboratuvar ortamında spektral testlerle incelenmiş ve doğrulanmıştır.')">
              Rapor Oluştur
            </button>
            <button type="button" class="btn-outline-gold btn-sm" onclick="loadRequestIntoForm(204, 'AUC-1044', 'Çağdaş Sanat Baskısı', 18000, 'Kağıt ve baskı kalitesi laboratuvar ortamında spektral testlerle incelenmiş ve doğrulanmıştır.')" title="Detaylı laboratuvar inceleme formuna aktar">
              Detaylı İncele →
            </button>
          </div>
        </div>

        <!-- Varsa Ekstra Talepler -->
        <?php foreach ($pendingRequests as $preq): 
          if (in_array((int)$preq['id'], [202, 203, 204], true)) continue;
        ?>
          <div class="panel-card" style="margin-bottom:0; display:flex; flex-direction:column; justify-content:space-between; border-color:rgba(197,160,89,0.3);">
            <div>
              <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:8px;">
                <span class="badge badge-gold"><?= htmlspecialchars($preq['lotKodu'] ?? 'AUC-YENİ') ?></span>
                <span class="badge badge-warning"><?= htmlspecialchars($preq['durum'] ?? 'BEKLEYEN') ?></span>
              </div>
              <h3 style="font-family:var(--font-serif); font-size:1.2rem; color:#fff; font-weight:600; margin-bottom:8px;">
                <?= htmlspecialchars($preq['not'] ?? 'Yeni Eser İnceleme Talebi') ?>
              </h3>
              <p style="font-size:0.84rem; color:var(--text-muted); line-height:1.5; margin-bottom:12px;">
                Fiziksel kondisyon, köken ve piyasa değerleme incelemesi bekliyor.
              </p>
              <div style="background:#090c10; padding:8px 12px; border-radius:6px; font-size:0.75rem; color:#848d9a; border:1px solid #1f2735;">
                Talep No: <strong><?= htmlspecialchars($preq['talepNo'] ?? 'EXP-REQ') ?></strong>
              </div>
            </div>
            <div style="margin-top:20px; display:flex; gap:8px;">
              <button type="button" class="btn-gold btn-sm" style="flex:1; justify-content:center;" onclick="openExpertReportModal(<?= $preq['id'] ?>, '<?= htmlspecialchars($preq['lotKodu'] ?? 'AUC-YENİ') ?>', 'Yeni Eser', 30000, 'Fiziksel kondisyon incelendi.')">
                Rapor Oluştur
              </button>
              <button type="button" class="btn-outline-gold btn-sm" onclick="loadRequestIntoForm(<?= $preq['id'] ?>, '<?= htmlspecialchars($preq['lotKodu'] ?? 'AUC-YENİ') ?>', 'Yeni Eser', 30000, 'Fiziksel kondisyon incelendi.')">
                Detaylı İncele →
              </button>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    </div>

    <!-- ====================================================================
         2. TAB: İNCELEMELER & RAPOR DÜZENLEME FORMU
         ==================================================================== -->
    <div id="appTab_inceleme" class="tab-content <?= $activeTab === 'inceleme' ? 'active' : '' ?>">
      <div class="page-breadcrumb">EKSPERTİZ UZMANI / ÇALIŞMA ALANI</div>
      <h1 class="page-heading">Ekspertiz Raporu Düzenle</h1>
      <p class="page-subheading">Laboratuvar incelemesi, orijinallik onayı, piyasa değerlemesi ve eskalasyon yönetimi</p>

      <div class="panel-card" style="border-color:rgba(197,160,89,0.35);">
        <!-- Hızlı Eser Seçici -->
        <div style="margin-bottom:18px; display:flex; align-items:center; gap:14px; flex-wrap:wrap; background:#0a0d14; padding:12px 18px; border-radius:8px; border:1px solid #232b3c;">
          <span style="font-size:0.84rem; color:var(--gold-primary); font-weight:700; text-transform:uppercase; letter-spacing:0.5px;">İncelenecek Eseri Seçin:</span>
          <select id="requestSelector" class="form-control" style="max-width:420px; font-size:0.85rem; font-weight:600;" onchange="onSelectRequest(this.value)">
            <option value="202" data-lot="AUC-1042" data-title="1968 İsviçre Vintage Saat" data-val="65000" data-notes="Kasa, kadran ve mekanik bileşenler orijinal fabrikasyon kondisyonundadır.&#10;Mikroskop altında kök boya ve malzeme testleri doğrulanmıştır.&#10;Eser üzerinde sonradan yapılan restorasyon veya müdahale izine rastlanmamıştır.">AUC-1042 — 1968 İsviçre Vintage Saat (Tahmini: ₺65.000)</option>
            <option value="203" data-lot="AUC-1043" data-title="Koleksiyon Analog Kamera" data-val="25000" data-notes="Kamera aksamı oldukça iyi durumda gerekli incelemeler neticesinde satışa uygun bulunmuştur.&#10;Optik lens ve perde mekanizması kalibre edilmiştir.">AUC-1043 — Koleksiyon Analog Kamera (Tahmini: ₺25.000)</option>
            <option value="204" data-lot="AUC-1044" data-title="Çağdaş Sanat Baskısı" data-val="18000" data-notes="Kağıt ve baskı kalitesi laboratuvar ortamında spektral testlerle incelenmiş ve doğrulanmıştır.&#10;Edisyon no ve ıslak imza arşiv ile uyumludur.">AUC-1044 — Çağdaş Sanat Baskısı (Tahmini: ₺18.000)</option>
            <?php foreach ($pendingRequests as $pr): if (in_array((int)$pr['id'], [202, 203, 204], true)) continue; ?>
              <option value="<?= $pr['id'] ?>" data-lot="<?= htmlspecialchars($pr['lotKodu'] ?? 'AUC-YENİ') ?>" data-title="<?= htmlspecialchars($pr['not'] ?? 'Yeni Eser') ?>" data-val="30000" data-notes="Fiziksel kondisyon kontrolü tamamlandı.">
                <?= htmlspecialchars($pr['lotKodu'] ?? 'AUC-YENİ') ?> — <?= htmlspecialchars($pr['not'] ?? 'Yeni Eser') ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>

        <form method="POST">
          <input type="hidden" name="action" value="submit_appraisal">
          <input type="hidden" name="expert_id" value="<?= $expertId ?>">
          <input type="hidden" id="appraisalReqId" name="request_id" value="202">

          <div style="background:#090c10; padding:12px 16px; border-radius:8px; border:1px solid var(--border-color); margin-bottom:18px; display:flex; justify-content:space-between; align-items:center;">
            <div>
              Seçili Eser: <strong id="appraisalTargetText" style="color:var(--gold-bright);">AUC-1042 — 1968 İsviçre Vintage Saat</strong>
            </div>
            <span class="badge badge-gold" id="appraisalBadge">MÜZE SEÇKİ ADAYI</span>
          </div>

          <div style="display:grid; grid-template-columns:1fr 1fr; gap:16px; margin-bottom:14px;">
            <div class="form-group">
              <label class="form-label">Önerilen Piyasa Değeri (TRY) *</label>
              <input type="number" step="500" id="appraisalValue" name="suggested_value" class="form-control" value="65000" required>
            </div>
            <div class="form-group">
              <label class="form-label">Orijinallik & Damga Notu *</label>
              <input type="text" id="appraisalAuthNote" name="authenticity_note" class="form-control" value="Orijinal - Ekspertiz Seçki Damgalı" required>
            </div>
          </div>

          <div style="display:grid; grid-template-columns:1fr 1fr; gap:16px; margin-bottom:14px;">
            <div class="form-group">
              <label class="form-label">Kondisyon Seviyesi</label>
              <select name="kondisyon" class="form-control">
                <option value="Mükemmel / Fabrikasyon Orijinal" selected>Mükemmel / Fabrikasyon Orijinal</option>
                <option value="Çok İyi / Dönem Kondisyonunda">Çok İyi / Dönem Kondisyonunda</option>
                <option value="İyi / Hafif Yaş İzi">İyi / Hafif Yaş İzi</option>
                <option value="Restorasyonlu / Müdahaleli">Restorasyonlu / Müdahaleli</option>
              </select>
            </div>
            <div class="form-group">
              <label class="form-label">Özgünlük Değerlendirmesi</label>
              <select name="ozgunluk" class="form-control">
                <option value="Doğrulandı" selected>✓ Doğrulandı (Orijinal Eser)</option>
                <option value="Şüpheli">? Şüpheli / İleri İnceleme Gerekli</option>
                <option value="Taklit">✗ Taklit / Sahte</option>
              </select>
            </div>
          </div>

          <div class="form-group" style="margin-bottom:20px;">
            <label class="form-label">Teknik Bulgular ve Laboratuvar Notları (Her satıra bir madde yazın)</label>
            <textarea id="appraisalNotes" name="notes" class="form-control" rows="4">Kasa, kadran ve mekanik bileşenler orijinal fabrikasyon kondisyonundadır.
Mikroskop altında kök boya ve malzeme testleri doğrulanmıştır.
Eser üzerinde sonradan yapılan restorasyon veya müdahale izine rastlanmamıştır.</textarea>
          </div>

          <div style="display:flex; gap:12px; align-items:center; flex-wrap:wrap;">
            <button type="submit" class="btn-gold" style="padding:12px 28px; font-weight:700;">
              ✓ Raporu Mühürle ve Admin Onayına Gönder
            </button>
            <button type="button" class="btn-outline-gold" style="padding:12px 20px;" onclick="openRevizeModal()">
              Düzeltme & Revize Talep Et
            </button>
            <button type="button" class="btn-outline-gold" style="padding:12px 20px; color:#f87171 !important; border-color:rgba(239,68,68,0.5);" onclick="openEskaleModal()">
              Üst Heyete Eskale Et
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- ====================================================================
         3. TAB: RAPOR GEÇMİŞİ (media_1795014776291_27bc4f96.jpg)
         ==================================================================== -->
    <div id="appTab_gecmis" class="tab-content <?= $activeTab === 'gecmis' ? 'active' : '' ?>">
      <div class="page-breadcrumb">EKSPERTİZ UZMANI / ÇALIŞMA ALANI</div>
      <h1 class="page-heading">Rapor geçmişi</h1>
      <p class="page-subheading" style="margin-bottom:24px;">Düzenlediğiniz ve onaylanan resmi ekspertiz raporları arşivi</p>

      <div class="panel-card" style="padding:0; overflow:hidden;">
        <table class="data-table" style="margin:0;">
          <thead>
            <tr>
              <th style="padding-left:24px;">Rapor No</th>
              <th>Talep / Eser</th>
              <th>Özgünlük & Damga</th>
              <th>Önerilen Piyasa Değeri</th>
              <th>Durum</th>
              <th>Tarih</th>
              <th style="padding-right:24px; text-align:right;">İşlem</th>
            </tr>
          </thead>
          <tbody>
            <?php if (!empty($completedReports)): ?>
              <?php foreach ($completedReports as $rep): ?>
                <tr>
                  <td style="padding-left:24px; font-weight:700; color:var(--gold-primary);">
                    <?= htmlspecialchars($rep['raporNo']) ?>
                  </td>
                  <td style="color:#fff; font-weight:600;">
                    Talep #<?= htmlspecialchars((string)($rep['talepId'] ?? '')) ?>
                  </td>
                  <td style="color:#cbd5e1; font-size:0.85rem;">
                    <?= htmlspecialchars($rep['orijinallikNotu'] ?? 'Orijinal') ?>
                  </td>
                  <td style="font-family:var(--font-serif); font-weight:700; color:var(--gold-bright);">
                    <?= $formatPrice((float)($rep['onerilenPiyasaDegeri'] ?? 0)) ?>
                  </td>
                  <td>
                    <span class="badge badge-success">✓ ONAYLANDI</span>
                  </td>
                  <td style="color:#848d9a; font-size:0.8rem;">
                    <?= !empty($rep['raporTarihi']) ? date('d.m.Y H:i', strtotime($rep['raporTarihi'])) : date('d.m.Y') ?>
                  </td>
                  <td style="padding-right:24px; text-align:right;">
                    <button type="button" class="btn-outline-gold btn-sm" onclick="openCertificateModal('<?= htmlspecialchars($rep['raporNo']) ?>', 'Talep #<?= htmlspecialchars((string)($rep['talepId'] ?? '')) ?>', 'Koleksiyon Eseri', <?= (float)($rep['onerilenPiyasaDegeri'] ?? 0) ?>, '<?= htmlspecialchars($rep['orijinallikNotu'] ?? 'Orijinal') ?>', '<?= !empty($rep['raporTarihi']) ? date('d.m.Y', strtotime($rep['raporTarihi'])) : date('d.m.Y') ?>')">
                      Sertifikayı İncele
                    </button>
                  </td>
                </tr>
              <?php endforeach; ?>
            <?php else: ?>
              <tr>
                <td style="padding-left:24px; font-weight:700; color:var(--gold-primary);">REP-2026-112</td>
                <td style="color:#fff; font-weight:600;">AUC-1042 — 1968 İsviçre Vintage Saat</td>
                <td style="color:#cbd5e1;">Orijinal - Ekspertiz Seçki Damgalı</td>
                <td style="font-family:var(--font-serif); font-weight:700; color:var(--gold-bright);">₺65.000</td>
                <td><span class="badge badge-success">✓ ONAYLANDI</span></td>
                <td style="color:#848d9a; font-size:0.8rem;"><?= date('d.m.Y') ?></td>
                <td style="padding-right:24px; text-align:right;">
                  <button type="button" class="btn-outline-gold btn-sm" onclick="openCertificateModal('REP-2026-112', 'AUC-1042', '1968 İsviçre Vintage Saat', 65000, 'Orijinal - Ekspertiz Seçki Damgalı', '<?= date('d.m.Y') ?>')">
                    Sertifikayı İncele
                  </button>
                </td>
              </tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>

    <!-- ====================================================================
         4. TAB: İTİRAZ & İHTİLAF TALEPLERİ (Use Case: İhtilaf Raporu İnceleme)
         ==================================================================== -->
    <div id="appTab_itiraz" class="tab-content <?= $activeTab === 'itiraz' ? 'active' : '' ?>">
      <div class="page-breadcrumb">EKSPERTİZ UZMANI / ÇALIŞMA ALANI</div>
      <h1 class="page-heading">İtiraz ve İhtilaf İncelemeleri</h1>
      <p class="page-subheading">Alıcı veya satıcı tarafından ekspertiz raporlarına yönelik açılan teknik ihtilaf dosyaları</p>

      <?php if (!empty($disputes)): ?>
        <?php foreach ($disputes as $disp): ?>
          <div class="panel-card" style="border:1px solid #c5a059; margin-bottom:18px;">
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:12px;">
              <div style="display:flex; align-items:center; gap:8px;">
                <span class="badge badge-gold"><?= htmlspecialchars($disp['talepNo'] ?? 'DISP') ?></span>
                <span style="font-size:0.85rem; color:#fff; font-weight:600;">Lot Kodu: <strong><?= htmlspecialchars($disp['lotKodu'] ?? 'AUC') ?></strong></span>
              </div>
              <span class="badge <?= ($disp['durum'] ?? '') === 'COZULDU' ? 'badge-success' : 'badge-warning' ?>">
                <?= htmlspecialchars($disp['durum'] ?? 'INCELENIYOR') ?>
              </span>
            </div>

            <div style="background:#090c10; padding:12px 16px; border-radius:8px; border:1px solid #1f2735; margin-bottom:16px;">
              <div style="color:var(--gold-primary); font-size:0.75rem; font-weight:700; text-transform:uppercase; margin-bottom:4px;">İhtilaf Konusu: <?= htmlspecialchars($disp['konuTipi'] ?? 'KONDİSYON') ?></div>
              <p style="color:#cbd5e1; font-size:0.88rem; line-height:1.5; margin:0;">
                "<?= htmlspecialchars($disp['aciklama'] ?? '') ?>"
              </p>
              <div style="font-size:0.75rem; color:#848d9a; margin-top:8px;">
                Başvuru Tarihi: <?= !empty($disp['olusturmaTarihi']) ? date('d.m.Y H:i', strtotime($disp['olusturmaTarihi'])) : date('d.m.Y') ?>
              </div>
            </div>

            <?php if (!empty($disp['eksperGorus'])): ?>
              <div style="background:rgba(63,185,80,0.1); border:1px solid rgba(63,185,80,0.3); padding:10px 14px; border-radius:8px; margin-bottom:14px;">
                <div style="color:#3fb950; font-weight:700; font-size:0.8rem; margin-bottom:2px;">✓ Ekspertiz Teknik Mütalaanız Kaydedildi</div>
                <div style="color:#e2e8f0; font-size:0.84rem;"><?= htmlspecialchars($disp['eksperGorus']) ?></div>
              </div>
            <?php else: ?>
              <form method="POST" style="margin:0;">
                <input type="hidden" name="action" value="submit_dispute_opinion">
                <input type="hidden" name="dispute_id" value="<?= $disp['id'] ?>">
                <input type="hidden" name="expert_id" value="<?= $expertId ?>">

                <div class="form-group" style="margin-bottom:12px;">
                  <label class="form-label">Ekspertiz Heyeti Teknik Mütalaası ve Nihai Görüşü *</label>
                  <textarea name="expert_opinion" class="form-control" rows="3" placeholder="Optik spektrum ve arşiv incelemesi neticesinde bildirilen izin döküm orijinali olduğu teyit edilmiştir..." required></textarea>
                </div>

                <div style="display:flex; justify-content:flex-end;">
                  <button type="submit" class="btn-gold" style="padding:9px 22px;">
                    Teknik Mütalaayı Kaydet ve Hakem Heyetine İlet
                  </button>
                </div>
              </form>
            <?php endif; ?>
          </div>
        <?php endforeach; ?>
      <?php else: ?>
        <div class="panel-card">
          <p style="color:var(--text-muted); margin:0;">Raporlarınıza yönelik aktif bir itiraz veya ihtilaf bulunmamaktadır.</p>
        </div>
      <?php endif; ?>
    </div>

    <!-- ====================================================================
         5. TAB: KATALOG & ESER ARAMA (Use Case: Ürün Arama)
         ==================================================================== -->
    <div id="appTab_arama" class="tab-content <?= $activeTab === 'arama' ? 'active' : '' ?>">
      <div class="page-breadcrumb">EKSPERTİZ UZMANI / ÇALIŞMA ALANI</div>
      <h1 class="page-heading">Katalog ve Eser Sorgulama</h1>
      <p class="page-subheading">Geçmiş satış kayıtları, referans müzayede fiyatları ve eser arşivinde arama yapın</p>

      <div class="panel-card" style="margin-bottom:20px;">
        <div class="form-group" style="margin:0;">
          <input type="text" id="appSearchInput" class="form-control" placeholder="Lot kodu, eser başlığı veya kategori ile anlık ara (Örn: Saat, Halı, Vazo, AUC...)" oninput="filterAppraiserSearch(this.value)">
        </div>
      </div>

      <div class="panel-card" style="padding:0; overflow:hidden;">
        <table class="data-table" id="appSearchTable" style="margin:0;">
          <thead>
            <tr>
              <th style="padding-left:24px;">Görsel & Lot</th>
              <th>Eser Adı</th>
              <th>Kategori</th>
              <th>Güncel Teklif</th>
              <th>Ekspertiz Durumu</th>
              <th style="padding-right:24px; text-align:right;">İşlem</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($lots as $lt): ?>
              <tr class="searchable-row" data-text="<?= htmlspecialchars(strtolower($lt['lotKodu'] . ' ' . $lt['baslik'] . ' ' . ($lt['kategoriAdi'] ?? ''))) ?>">
                <td style="padding-left:24px; display:flex; align-items:center; gap:10px;">
                  <img src="<?= htmlspecialchars($lt['gorselUrl'] ?? 'public/assets/images/watch.jpg') ?>" alt="Lot" style="width:36px; height:36px; border-radius:4px; object-fit:cover; border:1px solid #334155;">
                  <strong style="color:var(--gold-primary);"><?= htmlspecialchars($lt['lotKodu']) ?></strong>
                </td>
                <td style="color:#fff; font-weight:600;"><?= htmlspecialchars($lt['baslik']) ?></td>
                <td style="color:#cbd5e1;"><?= htmlspecialchars($lt['kategoriAdi'] ?? 'Antika') ?></td>
                <td style="font-family:var(--font-serif); font-weight:700; color:var(--gold-bright);">
                  <?= $formatPrice((float)$lt['mevcutEnYuksekTeklif']) ?>
                </td>
                <td>
                  <span class="badge badge-success">✓ ONAYLI</span>
                </td>
                <td style="padding-right:24px; text-align:right;">
                  <button type="button" class="btn-outline-gold btn-sm" onclick="openCertificateModal('REP-2026-<?= $lt['id'] ?>', '<?= htmlspecialchars($lt['lotKodu']) ?>', '<?= htmlspecialchars($lt['baslik']) ?>', <?= (float)$lt['mevcutEnYuksekTeklif'] ?>, 'Ekspertiz Seçki Mührü Onaylı', '<?= date('d.m.Y') ?>')">
                    Raporu Aç
                  </button>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>

    <!-- ====================================================================
         6. TAB: BİLDİRİMLER (Use Case: Bildirimleri İnceleme)
         ==================================================================== -->
    <div id="appTab_bildirim" class="tab-content <?= $activeTab === 'bildirim' ? 'active' : '' ?>">
      <div class="page-breadcrumb">EKSPERTİZ UZMANI / ÇALIŞMA ALANI</div>
      <h1 class="page-heading">Bildirimler</h1>
      <p class="page-subheading">Sistem atamaları, kurul kararları ve bilgilendirme kayıtları</p>

      <div class="panel-card" style="padding:0; overflow:hidden;">
        <div style="display:flex; flex-direction:column;">
          <div style="padding:16px 20px; border-bottom:1px solid var(--border-color); display:flex; justify-content:space-between; align-items:center;">
            <div>
              <div style="font-weight:700; color:#fff; display:flex; align-items:center; gap:8px;">
                <span style="color:#d4af37;">●</span>
                <span>Yeni Ekspertiz Görevi Atandı (AUC-1042)</span>
              </div>
              <div style="font-size:0.82rem; color:var(--text-muted); margin-top:4px;">
                1968 İsviçre Vintage Saat için fiziksel ve optik inceleme görevi panelinize aktarıldı.
              </div>
            </div>
            <span style="font-size:0.75rem; color:#848d9a;">Bugün, 14:30</span>
          </div>

          <div style="padding:16px 20px; border-bottom:1px solid var(--border-color); display:flex; justify-content:space-between; align-items:center;">
            <div>
              <div style="font-weight:700; color:#fff; display:flex; align-items:center; gap:8px;">
                <span style="color:#3fb950;">●</span>
                <span>Ekspertiz Akreditasyonu Yenilendi</span>
              </div>
              <div style="font-size:0.82rem; color:var(--text-muted); margin-top:4px;">
                Marmara Ekspertiz A.Ş. 2026/2. Dönem Horoloji ve Sanat yetkilendirmesi onaylanmıştır.
              </div>
            </div>
            <span style="font-size:0.75rem; color:#848d9a;">Dün, 09:15</span>
          </div>

          <div style="padding:16px 20px; display:flex; justify-content:space-between; align-items:center;">
            <div>
              <div style="font-weight:700; color:#fff; display:flex; align-items:center; gap:8px;">
                <span style="color:#60a5fa;">●</span>
                <span>İhtilaf Dosyası Görüş Bildirimi (AUC-1001)</span>
              </div>
              <div style="font-size:0.82rem; color:var(--text-muted); margin-top:4px;">
                Osmanlı Halı Levhası ihtilafı için teknik mütalaa talebi iletildi.
              </div>
            </div>
            <span style="font-size:0.75rem; color:#848d9a;">12.10.2026</span>
          </div>
        </div>
      </div>
    </div>

    <!-- ====================================================================
         7. TAB: PROFİL & YETKİ (Use Case: Profil Bilgileri Güncelleme)
         ==================================================================== -->
    <div id="appTab_profil" class="tab-content <?= $activeTab === 'profil' ? 'active' : '' ?>">
      <div class="page-breadcrumb">EKSPERTİZ UZMANI / ÇALIŞMA ALANI</div>
      <h1 class="page-heading">Profil ve Uzmanlık Bilgileri</h1>
      <p class="page-subheading">Yetkili eksper kimliği, meslek odası sicil kaydı ve iletişim bilgilerini yönetin</p>

      <div style="display:grid; grid-template-columns:1.5fr 1fr; gap:24px;">
        <div class="panel-card">
          <form method="POST">
            <input type="hidden" name="action" value="update_profile">
            <input type="hidden" name="user_id" value="<?= $expertId ?>">

            <div style="display:grid; grid-template-columns:1fr 1fr; gap:16px; margin-bottom:14px;">
              <div class="form-group">
                <label class="form-label">Ad</label>
                <input type="text" name="ad" class="form-control" value="<?= htmlspecialchars($currentUser['ad'] ?? 'Burak') ?>" required>
              </div>
              <div class="form-group">
                <label class="form-label">Soyad</label>
                <input type="text" name="soyad" class="form-control" value="<?= htmlspecialchars($currentUser['soyad'] ?? 'Şahin') ?>" required>
              </div>
            </div>

            <div style="display:grid; grid-template-columns:1fr 1fr; gap:16px; margin-bottom:14px;">
              <div class="form-group">
                <label class="form-label">E-Posta</label>
                <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($currentUser['email'] ?? 'burak@ekspertiz.com') ?>" required>
              </div>
              <div class="form-group">
                <label class="form-label">Telefon</label>
                <input type="text" name="telefon" class="form-control" value="<?= htmlspecialchars($currentUser['telefon'] ?? '+90 533 444 55 66') ?>" required>
              </div>
            </div>

            <div class="form-group" style="margin-bottom:14px;">
              <label class="form-label">Bağlı Olduğu Ekspertiz Kuruluşu / Kurum Adı</label>
              <input type="text" class="form-control" value="<?= htmlspecialchars($currentUser['ekspertizProfili']['kurumAdi'] ?? 'Marmara Ekspertiz A.Ş.') ?>" readonly style="background:#0a0d13; color:#8b949e;">
            </div>

            <div class="form-group" style="margin-bottom:14px;">
              <label class="form-label">Uzmanlık Alanı ve İhtisas Branşları</label>
              <input type="text" class="form-control" value="<?= htmlspecialchars($currentUser['ekspertizProfili']['uzmanlikAlani'] ?? 'Klasik Halı, Saat ve Tablolar') ?>" readonly style="background:#0a0d13; color:#8b949e;">
            </div>

            <div class="form-group" style="margin-bottom:20px;">
              <label class="form-label">Parola Değiştir (İsteğe bağlı)</label>
              <input type="password" name="password" class="form-control" placeholder="Mevcut parolayı korumak için boş bırakın">
            </div>

            <button type="submit" class="btn-gold" style="padding:11px 24px;">
              Profil Bilgilerini Güncelle
            </button>
          </form>
        </div>

        <div>
          <div class="panel-card" style="border-color:#c5a059;">
            <div style="color:var(--gold-primary); font-size:0.75rem; font-weight:700; text-transform:uppercase; letter-spacing:1px; margin-bottom:8px;">AKREDİTASYON VE LİSANS</div>
            <h3 style="color:#fff; font-size:1.15rem; margin-bottom:12px;">Yetkili Eksper Belgesi</h3>
            <div style="display:flex; flex-direction:column; gap:8px; font-size:0.83rem; color:#cbd5e1;">
              <div>Akreditasyon No: <strong style="color:#d4af37;"><?= htmlspecialchars($currentUser['ekspertizProfili']['akreditasyonNo'] ?? 'EXP-2024-889') ?></strong></div>
              <div>Meslek Odası Sicil: <strong><?= htmlspecialchars($currentUser['ekspertizProfili']['meslekOdasiSicilNo'] ?? 'OD-IST-44910') ?></strong></div>
              <div>Yetki Seviyesi: <span class="badge badge-success">TAM YETKİLİ & ONAYLI</span></div>
              <div>Denetim İmzası: <strong>E-İmza & SHA-256 Mühürlü</strong></div>
            </div>
            <div style="margin-top:16px; border-top:1px dashed #2a3449; padding-top:12px; font-size:0.75rem; color:#848d9a; line-height:1.4;">
              Raporlarınız 5070 Sayılı Elektronik İmza Kanunu kapsamında hukuki geçerliliğe ve müzayede platformu kurumsal garantisine sahiptir.
            </div>
          </div>
        </div>
      </div>
    </div>

  </main>
</div>

<!-- ====================================================================
     MODALLER (Ekspertiz Raporu, Revize, Eskalasyon ve Sertifika Görüntüleme)
     ==================================================================== -->

<!-- 1. Hızlı Ekspertiz Raporu Modalı (Görev Kartından Açılır) -->
<div id="expertReportModal" class="martello-modal" style="display:none; position:fixed; z-index:9999; left:0; top:0; width:100%; height:100%; background:rgba(0,0,0,0.85); backdrop-filter:blur(6px); align-items:center; justify-content:center;">
  <div style="background:#141822; border:1px solid #c5a059; border-radius:12px; max-width:500px; width:92%; padding:26px; box-shadow:0 12px 40px rgba(0,0,0,0.9); position:relative;">
    <button type="button" onclick="closeExpertModal()" style="position:absolute; right:16px; top:16px; background:none; border:none; color:#888; font-size:1.6rem; cursor:pointer; line-height:1;">&times;</button>
    
    <div style="color:#d4af37; font-size:0.72rem; font-weight:700; letter-spacing:1px; text-transform:uppercase; margin-bottom:4px;">MARTELLO EKSPERTİZ HEYETİ</div>
    <div style="font-family:var(--font-serif); font-size:1.4rem; font-weight:600; color:#fff; margin-bottom:2px;">Ekspertiz Raporu • <span id="modalLotKodu" style="color:var(--gold-bright);">AUC-1042</span></div>
    <div id="modalItemTitle" style="color:#8893a4; font-size:0.85rem; margin-bottom:16px;">1968 İsviçre Vintage Saat</div>

    <form method="POST">
      <input type="hidden" name="action" value="submit_appraisal">
      <input type="hidden" name="expert_id" value="<?= $expertId ?>">
      <input type="hidden" name="request_id" id="modalReqId" value="202">

      <div class="form-group" style="margin-bottom:12px;">
        <label class="form-label" style="font-size:0.82rem; color:#f0f3f6;">İnceleme Notları ve Bulgular *</label>
        <textarea name="notes" id="modalNotes" class="form-control" rows="3" required style="border-radius:8px; background:#10131b; font-size:0.85rem;">Saat mekanizması ve kronograf fonksiyonları laboratuvar testlerinde incelenmiş, orijinal fabrikasyon kondisyonunda bulunmuştur.</textarea>
      </div>

      <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px; margin-bottom:12px;">
        <div class="form-group" style="margin:0;">
          <label class="form-label" style="font-size:0.82rem; color:#f0f3f6;">Kondisyon</label>
          <select name="kondisyon" class="form-control" style="border-radius:8px; background:#10131b;">
            <option value="Mükemmel / Fabrikasyon Orijinal" selected>Mükemmel / Orijinal</option>
            <option value="Çok İyi">Çok İyi</option>
            <option value="İyi">İyi</option>
            <option value="Restorasyonlu">Restorasyonlu</option>
          </select>
        </div>
        <div class="form-group" style="margin:0;">
          <label class="form-label" style="font-size:0.82rem; color:#f0f3f6;">Özgünlük</label>
          <select name="authenticity_note" class="form-control" style="border-radius:8px; background:#10131b;">
            <option value="Orijinal - Ekspertiz Seçki Damgalı" selected>✓ Orijinal - Damgalı</option>
            <option value="Şüpheli">? Şüpheli</option>
            <option value="Taklit">✗ Taklit</option>
          </select>
        </div>
      </div>

      <div class="form-group" style="margin-bottom:20px;">
        <label class="form-label" style="font-size:0.82rem; color:#f0f3f6;">Tahmini Değer (TRY) *</label>
        <input type="number" step="500" name="suggested_value" id="modalValue" class="form-control" value="65000" required style="border-radius:8px; background:#10131b; font-weight:700; color:#d4af37;">
      </div>

      <button type="submit" class="btn-register-glow" style="width:100%; padding:12px; font-size:0.9rem; cursor:pointer;">
        Raporu Mühürle ve Kaydet
      </button>
    </form>
  </div>
</div>

<!-- 2. Revize Talep Modalı -->
<div id="revizeModal" class="martello-modal" style="display:none; position:fixed; z-index:9999; left:0; top:0; width:100%; height:100%; background:rgba(0,0,0,0.85); backdrop-filter:blur(6px); align-items:center; justify-content:center;">
  <div style="background:#12161f; border:1px solid #c5a059; border-radius:12px; max-width:480px; width:90%; padding:24px; position:relative; box-shadow:0 12px 40px rgba(0,0,0,0.9);">
    <button type="button" onclick="closeRevizeModal()" style="position:absolute; right:16px; top:16px; background:none; border:none; color:#888; font-size:1.5rem; cursor:pointer; line-height:1;">&times;</button>
    <div style="color:var(--gold-primary); font-size:0.75rem; font-weight:700; letter-spacing:1px; text-transform:uppercase; margin-bottom:4px;">EKSPERTİZ REVİZE TALEBİ</div>
    <h3 style="color:#fff; font-size:1.25rem; margin-bottom:8px;">Satıcıdan Ek Belge / Fotoğraf İste</h3>
    <p style="color:#8893a4; font-size:0.82rem; margin-bottom:14px;">Satıcıya ekspertiz için gereken ek görsel veya orijinallik belgesi talebini iletin.</p>
    <form method="POST">
      <input type="hidden" name="action" value="request_revision">
      <input type="hidden" name="expert_id" value="<?= $expertId ?>">
      <input type="hidden" id="revizeModalReqId" name="request_id" value="202">
      <div class="form-group" style="margin-bottom:16px;">
        <label class="form-label">Revize ve Ek Belge Gerekçesi</label>
        <textarea name="reason" class="form-control" rows="3" required placeholder="Mekanizma seri numarasının ve bezel köşelerinin yüksek çözünürlüklü makro fotoğrafları gereklidir.">Mekanizma seri numarasının ve bezel köşelerinin yüksek çözünürlüklü makro fotoğrafları gereklidir.</textarea>
      </div>
      <button type="submit" class="btn-gold" style="width:100%;">Talebi Satıcıya İlet</button>
    </form>
  </div>
</div>

<!-- 3. Eskalasyon Modalı -->
<div id="eskaleModal" class="martello-modal" style="display:none; position:fixed; z-index:9999; left:0; top:0; width:100%; height:100%; background:rgba(0,0,0,0.85); backdrop-filter:blur(6px); align-items:center; justify-content:center;">
  <div style="background:#12161f; border:1px solid #ef4444; border-radius:12px; max-width:480px; width:90%; padding:24px; position:relative; box-shadow:0 12px 40px rgba(0,0,0,0.9);">
    <button type="button" onclick="closeEskaleModal()" style="position:absolute; right:16px; top:16px; background:none; border:none; color:#888; font-size:1.5rem; cursor:pointer; line-height:1;">&times;</button>
    <div style="color:#f87171; font-size:0.75rem; font-weight:700; letter-spacing:1px; text-transform:uppercase; margin-bottom:4px;">HAKEM HEYETİ ESKALASYONU</div>
    <h3 style="color:#fff; font-size:1.25rem; margin-bottom:8px;">Üst İhtisas Kuruluna Eskale Et</h3>
    <p style="color:#8893a4; font-size:0.82rem; margin-bottom:14px;">Yüksek kıymetli veya ihtilaflı eseri baş eksper ve yönetim heyetine yönlendirin.</p>
    <form method="POST">
      <input type="hidden" name="action" value="escalate_appraisal">
      <input type="hidden" name="expert_id" value="<?= $expertId ?>">
      <input type="hidden" id="eskaleModalReqId" name="request_id" value="202">
      <div class="form-group" style="margin-bottom:16px;">
        <label class="form-label">Eskalasyon Gerekçesi</label>
        <textarea name="reason" class="form-control" rows="3" required placeholder="Eser nadir müzeolojik nitelik taşımaktadır; rezerv fiyatın tespiti için üst kurul onayı gereklidir.">Eser nadir müzeolojik nitelik taşımaktadır; rezerv fiyatın tespiti için kurul onayı gereklidir.</textarea>
      </div>
      <button type="submit" class="btn-gold" style="width:100%; background:#dc2626; border-color:#ef4444; color:#fff;">Üst Heyete Eskale Et</button>
    </form>
  </div>
</div>

<!-- 4. Sertifika Görüntüleme Modalı -->
<div id="viewCertificateModal" class="martello-modal" style="display:none; position:fixed; z-index:9999; left:0; top:0; width:100%; height:100%; background:rgba(0,0,0,0.85); backdrop-filter:blur(6px); align-items:center; justify-content:center;">
  <div style="background:#141822; border:1px solid #c5a059; border-radius:12px; max-width:520px; width:92%; padding:28px; position:relative; box-shadow:0 12px 40px rgba(0,0,0,0.9);">
    <button type="button" onclick="closeCertificateModal()" style="position:absolute; right:16px; top:16px; background:none; border:none; color:#888; font-size:1.6rem; cursor:pointer; line-height:1;">&times;</button>
    
    <div class="expert-white-card" style="margin:0; box-shadow:none;">
      <div class="expert-card-top">
        <span class="expert-card-title">Resmi Ekspertiz Raporu & Sertifikası</span>
        <span class="expert-card-badge" id="certReportNo">REP-2026-112</span>
      </div>
      
      <div style="margin-bottom:12px;">
        <div style="font-size:1.15rem; font-weight:700; color:#0c0f14;" id="certTitle">1968 İsviçre Vintage Saat</div>
        <div style="font-size:0.8rem; color:#555;" id="certLot">Lot Kodu: AUC-1042</div>
      </div>

      <ul class="expert-card-list">
        <li>Mekanizma kalibresi, kadran ve kasa optik mikroskop altında incelenmiştir.</li>
        <li>Eser %100 dönem fabrikasyon orijinali olup revizyon görmemiştir.</li>
        <li>Önerilen Piyasa Değeri: <strong id="certValue" style="color:#b45309;">₺65.000</strong></li>
        <li id="certNote">Orijinal - Ekspertiz Seçki Damgalı</li>
      </ul>

      <div class="expert-card-bottom">
        <div>
          <div class="expert-reviewer-title">Uzman İncelemesi:</div>
          <div class="expert-stars">★ ★ ★ ★ ★</div>
          <div style="font-size:0.7rem; color:#666; margin-top:2px;">Eksper: Burak Şahin (EXP-2024-889)</div>
          <div style="font-size:0.7rem; color:#888;" id="certDate">Onay: 12.10.2026</div>
        </div>
        
        <div class="expert-stamp-seal">
          EKSPERTİZ<br>SEÇKİ<br>★ ★ ★<br>DAMGALI
        </div>
      </div>
    </div>
  </div>
</div>

<script>
function switchAppraiserTab(tabId, btn) {
  // Tüm sekmeleri ve menü elemanlarını pasife al
  document.querySelectorAll('.tab-content').forEach(el => el.classList.remove('active'));
  document.querySelectorAll('.sidebar .nav-menu-item').forEach(el => el.classList.remove('active'));

  // Hedef sekmeyi aktif yap
  const target = document.getElementById('appTab_' + tabId);
  if (target) {
    target.classList.add('active');
  }

  // Menü öğesini aktif yap
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

  // URL state güncelle
  if (window.history && window.history.replaceState) {
    try {
      const url = new URL(window.location.href);
      url.searchParams.set('tab', tabId);
      window.history.replaceState({}, '', url);
    } catch(e) {}
  }
}

// 1. Hızlı Ekspertiz Raporu Modalı Aç/Kapat
function openExpertReportModal(reqId, lotKodu, title, value, notes) {
  const m = document.getElementById('expertReportModal');
  if (!m) return;
  document.getElementById('modalReqId').value = reqId || '';
  document.getElementById('modalLotKodu').innerText = lotKodu || '';
  document.getElementById('modalItemTitle').innerText = title || '';
  document.getElementById('modalValue').value = value || '';
  if (notes) {
    document.getElementById('modalNotes').value = notes;
  }
  m.style.display = 'flex';
}

function closeExpertModal() {
  const m = document.getElementById('expertReportModal');
  if (m) m.style.display = 'none';
}

// 2. Detaylı Forma Aktar ve İnceleme Sekmesine Geç
function loadRequestIntoForm(reqId, lotKodu, title, value, notes) {
  document.getElementById('appraisalReqId').value = reqId || '';
  document.getElementById('revizeModalReqId').value = reqId || '';
  document.getElementById('eskaleModalReqId').value = reqId || '';
  document.getElementById('appraisalTargetText').innerText = (lotKodu ? lotKodu + ' — ' : '') + (title || 'Eser');
  if (value) {
    document.getElementById('appraisalValue').value = value;
  }
  if (notes) {
    document.getElementById('appraisalNotes').value = notes;
  }
  const sel = document.getElementById('requestSelector');
  if (sel) {
    sel.value = reqId;
  }
  switchAppraiserTab('inceleme');
}

// Dropdown üzerinden eser seçimi değiştiğinde
function onSelectRequest(reqId) {
  const sel = document.getElementById('requestSelector');
  if (!sel) return;
  const opt = sel.options[sel.selectedIndex];
  if (!opt) return;
  const lot = opt.getAttribute('data-lot') || '';
  const title = opt.getAttribute('data-title') || '';
  const val = opt.getAttribute('data-val') || '';
  const notes = opt.getAttribute('data-notes') || '';
  loadRequestIntoForm(reqId, lot, title, val, notes);
}

// 3. Revize Modalı
function openRevizeModal() {
  const curReqId = document.getElementById('appraisalReqId').value || '202';
  document.getElementById('revizeModalReqId').value = curReqId;
  const m = document.getElementById('revizeModal');
  if (m) m.style.display = 'flex';
}

function closeRevizeModal() {
  const m = document.getElementById('revizeModal');
  if (m) m.style.display = 'none';
}

// 4. Eskale Modalı
function openEskaleModal() {
  const curReqId = document.getElementById('appraisalReqId').value || '202';
  document.getElementById('eskaleModalReqId').value = curReqId;
  const m = document.getElementById('eskaleModal');
  if (m) m.style.display = 'flex';
}

function closeEskaleModal() {
  const m = document.getElementById('eskaleModal');
  if (m) m.style.display = 'none';
}

// 5. Sertifika Görüntüleme Modalı
function openCertificateModal(raporNo, lotKodu, title, value, note, date) {
  const m = document.getElementById('viewCertificateModal');
  if (!m) return;
  document.getElementById('certReportNo').innerText = raporNo || 'REP';
  document.getElementById('certTitle').innerText = title || '';
  document.getElementById('certLot').innerText = 'Lot Kodu: ' + (lotKodu || '');
  document.getElementById('certValue').innerText = '₺' + parseFloat(value || 0).toLocaleString('tr-TR');
  document.getElementById('certNote').innerText = note || 'Orijinal - Ekspertiz Seçki Damgalı';
  if (date) {
    document.getElementById('certDate').innerText = 'Onay: ' + date;
  }
  m.style.display = 'flex';
}

function closeCertificateModal() {
  const m = document.getElementById('viewCertificateModal');
  if (m) m.style.display = 'none';
}

// 6. Katalog / Eser Arama Canlı Filtreleme
function filterAppraiserSearch(query) {
  const q = (query || '').toLowerCase().trim();
  const rows = document.querySelectorAll('#appSearchTable tbody tr.searchable-row');
  rows.forEach(r => {
    const text = r.getAttribute('data-text') || '';
    if (!q || text.includes(q)) {
      r.style.display = '';
    } else {
      r.style.display = 'none';
    }
  });
}
</script>
