<?php
declare(strict_types=1);

/** @var callable $formatPrice */
/** @var array $db */

$users = $db['users'] ?? [];
$lots = $db['lots'] ?? [];
$reports = $db['appraisalReports'] ?? [];
$disputes = $db['disputes'] ?? [];
$listings = $db['listings'] ?? [];
$categories = $db['categories'] ?? [];
$auditLogs = $db['auditLogs'] ?? [];
$activeTab = $_GET['tab'] ?? 'konsol';
?>

<div class="app-container">
  <!-- Sol Menü (Sidebar) — SRS Sayfa 34 Alt Görsel ile Birebir Uyumlu -->
  <aside class="sidebar">
    <div>
      <div class="sidebar-section-title">Çalışma Alanı</div>
      <ul class="nav-menu">
        <li class="nav-menu-item <?= $activeTab === 'konsol' ? 'active' : '' ?>">
          <button type="button" onclick="switchAdminTab('konsol')">
            <span class="menu-icon">⚙️</span> Yönetim konsolu
          </button>
        </li>
        <li class="nav-menu-item <?= $activeTab === 'kullanicilar' ? 'active' : '' ?>">
          <button type="button" onclick="switchAdminTab('kullanicilar')">
            <span class="menu-icon">👥</span> Kullanıcılar
          </button>
        </li>
        <li class="nav-menu-item <?= $activeTab === 'moderasyon' ? 'active' : '' ?>">
          <button type="button" onclick="switchAdminTab('moderasyon')">
            <span class="menu-icon">🛡️</span> İlan moderasyonu
          </button>
        </li>
        <li class="nav-menu-item <?= $activeTab === 'ekspertiz' ? 'active' : '' ?>">
          <button type="button" onclick="switchAdminTab('ekspertiz')">
            <span class="menu-icon">🔍</span> Ekspertiz atama
          </button>
        </li>
        <li class="nav-menu-item <?= $activeTab === 'kategoriler' ? 'active' : '' ?>">
          <button type="button" onclick="switchAdminTab('kategoriler')">
            <span class="menu-icon">📂</span> Kategoriler
          </button>
        </li>
        <li class="nav-menu-item <?= $activeTab === 'komisyon' ? 'active' : '' ?>">
          <button type="button" onclick="switchAdminTab('komisyon')">
            <span class="menu-icon">📊</span> Komisyon oranları
          </button>
        </li>
        <li class="nav-menu-item <?= $activeTab === 'raporlar' ? 'active' : '' ?>">
          <button type="button" onclick="switchAdminTab('raporlar')">
            <span class="menu-icon">📑</span> Raporlar
          </button>
        </li>
        <li class="nav-menu-item <?= $activeTab === 'ihtilaflar' ? 'active' : '' ?>">
          <button type="button" onclick="switchAdminTab('ihtilaflar')">
            <span class="menu-icon">⚖️</span> İhtilaflar
          </button>
        </li>
        <li class="nav-menu-item <?= $activeTab === 'denetim' ? 'active' : '' ?>">
          <button type="button" onclick="switchAdminTab('denetim')">
            <span class="menu-icon">📜</span> Denetim izi
          </button>
        </li>
        <li class="nav-menu-item <?= $activeTab === 'ayarlar' ? 'active' : '' ?>">
          <button type="button" onclick="switchAdminTab('ayarlar')">
            <span class="menu-icon">⚡</span> Sistem ayarları
          </button>
        </li>
      </ul>
    </div>
  </aside>

  <!-- Ana İçerik Alanı -->
  <main class="main-content">

    <!-- 1. TAB: YÖNETİM KONSOLU (media_1794924544608_2aec8b83.jpg ile Birebir) -->
    <div id="admTab_konsol" class="tab-content <?= $activeTab === 'konsol' ? 'active' : '' ?>">
      <div class="page-breadcrumb">ADMIN / ÇALIŞMA ALANI</div>
      <h1 class="page-heading">Yönetim konsolu</h1>
      <p class="page-subheading">Seçkin müzayede deneyiminizi yönetin</p>

      <!-- 4 Adet KPI Kartı (Sayfa 34 Alt Tasarım) -->
      <div class="kpi-grid">
        <div class="kpi-card">
          <div class="kpi-label">Kullanıcılar</div>
          <div class="kpi-value">3</div>
        </div>
        <div class="kpi-card highlight">
          <div class="kpi-label">Aktif Lotlar</div>
          <div class="kpi-value">6</div>
        </div>
        <div class="kpi-card">
          <div class="kpi-label">Raporlar</div>
          <div class="kpi-value">1</div>
        </div>
        <div class="kpi-card">
          <div class="kpi-label">Açık İhtilaflar</div>
          <div class="kpi-value">0</div>
        </div>
      </div>

      <!-- Yönetim Konsolu Kartı -->
      <div class="panel-card">
        <h2 class="panel-title" style="margin-bottom:6px;">Yönetim konsolu</h2>
        <p style="font-size:0.88rem; color:var(--text-muted);">
          Moderasyon, ekspertiz ve rapor akışlarını soldaki menüden yönetin.
        </p>
      </div>
    </div>

    <!-- 2. TAB: KULLANICILAR (FR-05, FR-06) -->
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
                  <?= !empty($u['kycDogrulama']['dogrulandiMi']) ? '<span style="color:#3fb950;">✓ Doğrulandı</span>' : '<span style="color:#d29922;">Bekliyor</span>' ?>
                </td>
                <td>
                  <span class="badge <?= ($u['durum'] ?? '') === 'AKTIF' ? 'badge-success' : 'badge-danger' ?>">
                    <?= htmlspecialchars($u['durum'] ?? 'AKTIF') ?>
                  </span>
                </td>
                <td>
                  <?php if ($u['rol'] !== 'ADMIN'): ?>
                    <form method="POST" style="margin:0;">
                      <input type="hidden" name="action" value="toggle_ban">
                      <input type="hidden" name="user_id" value="<?= $u['id'] ?>">
                      <?php if (($u['durum'] ?? '') === 'AKTIF'): ?>
                        <input type="hidden" name="new_status" value="ASKIDA">
                        <button type="submit" class="btn-outline-gold btn-sm" style="color:#f85149 !important; border-color:#f85149;">Askıya Al (Ban)</button>
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

    <!-- 3. TAB: İLAN MODERASYONU (FR-09, FR-10) -->
    <div id="admTab_moderasyon" class="tab-content <?= $activeTab === 'moderasyon' ? 'active' : '' ?>">
      <div class="page-breadcrumb">ADMIN / ÇALIŞMA ALANI</div>
      <h1 class="page-heading">İlan Moderasyonu</h1>
      <p class="page-subheading">Ekspertizden geçen eserleri onaylayarak açık artırmaya başlatın</p>

      <div class="panel-card">
        <table class="data-table">
          <thead>
            <tr><th>İlan Kodu</th><th>Ürün Bilgisi</th><th>Satıcı</th><th>Statü</th><th>Müzayede Başlat</th></tr>
          </thead>
          <tbody>
            <?php foreach ($listings as $l): ?>
              <tr>
                <td><span class="badge badge-gold"><?= htmlspecialchars($l['ilanKodu']) ?></span></td>
                <td style="font-weight:600; color:#fff;">Koleksiyonluk Eser (ID: <?= $l['urunId'] ?>)</td>
                <td>Satıcı #<?= $l['saticiId'] ?></td>
                <td><span class="badge badge-success"><?= htmlspecialchars($l['durum']) ?></span></td>
                <td>
                  <?php if ($l['durum'] !== 'YAYINDA'): ?>
                    <form method="POST" style="display:inline-flex; gap:6px;">
                      <input type="hidden" name="action" value="approve_listing">
                      <input type="hidden" name="listing_id" value="<?= $l['id'] ?>">
                      <input type="hidden" name="start_price" value="32000">
                      <input type="hidden" name="reserve_price" value="40000">
                      <input type="hidden" name="min_step" value="1500">
                      <button type="submit" class="btn-gold btn-sm">Müzayedeye Başlat</button>
                    </form>
                  <?php else: ?>
                    <span style="color:#3fb950; font-weight:600;">✓ Yayında</span>
                  <?php endif; ?>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>

    <!-- 4. TAB: EKSPERTİZ ATAMA (FR-17) -->
    <div id="admTab_ekspertiz" class="tab-content <?= $activeTab === 'ekspertiz' ? 'active' : '' ?>">
      <div class="page-breadcrumb">ADMIN / ÇALIŞMA ALANI</div>
      <h1 class="page-heading">Ekspertiz ve Kurum Atama</h1>
      <p class="page-subheading">Yeni ürünlere alanında uzman akredite eksper atayın</p>

      <div class="panel-card">
        <div style="font-size:0.85rem; color:var(--text-muted); margin-bottom:14px;">
          Atanmış Uzman: <strong>Burak Şahin (Marmara Ekspertiz A.Ş. — Sicil No: OD-IST-44910)</strong>
        </div>
        <p style="color:var(--text-muted);">Tüm gelen talepler uzmanlık alanlarına göre otomatik yönlendirilmektedir.</p>
      </div>
    </div>

    <!-- 5. TAB: KATEGORİLER (FR-08) -->
    <div id="admTab_kategoriler" class="tab-content <?= $activeTab === 'kategoriler' ? 'active' : '' ?>">
      <div class="page-breadcrumb">ADMIN / ÇALIŞMA ALANI</div>
      <h1 class="page-heading">Kategori Yönetimi</h1>
      <p class="page-subheading">Ürün kategorileri, komisyon ve zorunlu nitelik şablonları</p>

      <div class="panel-card">
        <table class="data-table">
          <thead>
            <tr><th>Kategori Adı</th><th>Kod</th><th>Komisyon</th><th>Ekspertiz Ücreti</th></tr>
          </thead>
          <tbody>
            <?php foreach ($categories as $c): ?>
              <tr>
                <td style="font-weight:600; color:#fff;"><?= htmlspecialchars($c['ad']) ?></td>
                <td><code><?= htmlspecialchars($c['kod']) ?></code></td>
                <td><span class="badge badge-gold">%<?= $c['komisyonOraniYuzde'] ?></span></td>
                <td><?= $formatPrice((float)$c['sabitEkspertizUcreti']) ?></td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>

    <!-- 6. TAB: KOMİSYON ORANLARI (FR-22) -->
    <div id="admTab_komisyon" class="tab-content <?= $activeTab === 'komisyon' ? 'active' : '' ?>">
      <div class="page-breadcrumb">ADMIN / ÇALIŞMA ALANI</div>
      <h1 class="page-heading">Komisyon ve Hizmet Bedeli Tanımlama</h1>
      <p class="page-subheading">Platform gelir modeli ve kategori bazlı komisyon kuralları</p>

      <div class="panel-card">
        <p style="color:var(--text-muted); margin-bottom:12px;">
          Antika & Sanat: <strong>%8</strong> • Horoloji & Saat: <strong>%7.5</strong> • Efemera & Filateli: <strong>%6</strong> • Taşıt: <strong>%4</strong>
        </p>
      </div>
    </div>

    <!-- 7. TAB: RAPORLAR -->
    <div id="admTab_raporlar" class="tab-content <?= $activeTab === 'raporlar' ? 'active' : '' ?>">
      <div class="page-breadcrumb">ADMIN / ÇALIŞMA ALANI</div>
      <h1 class="page-heading">Onaylı Ekspertiz Raporları</h1>
      <p class="page-subheading">Uzmanlar tarafından hazırlanan resmi değerlendirmeler</p>

      <div class="panel-card">
        <table class="data-table">
          <thead><tr><th>Rapor No</th><th>Lot Kodu</th><th>Piyasa Değeri</th><th>Durum</th></tr></thead>
          <tbody>
            <?php foreach ($reports as $r): ?>
              <tr>
                <td><code><?= $r['raporNo'] ?></code></td>
                <td>AUC-1001</td>
                <td style="color:var(--gold-bright); font-weight:700;"><?= $formatPrice((float)$r['onerilenPiyasaDegeri']) ?></td>
                <td><span class="badge badge-success">✓ Onaylandı</span></td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>

    <!-- 8. TAB: İHTİLAFLAR (FR-20) -->
    <div id="admTab_ihtilaflar" class="tab-content <?= $activeTab === 'ihtilaflar' ? 'active' : '' ?>">
      <div class="page-breadcrumb">ADMIN / ÇALIŞMA ALANI</div>
      <h1 class="page-heading">İhtilaf ve Tahkim Çözümü</h1>
      <p class="page-subheading">Alıcı-satıcı uyuşmazlıkları ve bloke iade kararları</p>

      <div class="panel-card">
        <p style="color:var(--text-muted);">İncelenmeyi bekleyen aktif uyuşmazlık dosyası bulunmamaktadır.</p>
      </div>
    </div>

    <!-- 9. TAB: WORM DENETİM İZİ (FR-25, R-12) -->
    <div id="admTab_denetim" class="tab-content <?= $activeTab === 'denetim' ? 'active' : '' ?>">
      <div class="page-breadcrumb">ADMIN / ÇALIŞMA ALANI</div>
      <h1 class="page-heading">Değiştirilemez Denetim İzi (WORM)</h1>
      <p class="page-subheading">SHA-256 Blok Zincirleme (Hash Chaining) ile adli bilişim kayıtları</p>

      <div class="panel-card">
        <table class="data-table">
          <thead>
            <tr><th>Log ID</th><th>Zaman Damgası</th><th>Kullanıcı</th><th>İşlem Tipi</th><th>Shill Bidding</th><th>SHA-256 Hash</th></tr>
          </thead>
          <tbody>
            <?php foreach ($auditLogs as $l): ?>
              <tr>
                <td><code><?= $l['logId'] ?></code></td>
                <td style="font-size:0.75rem; color:var(--text-muted);"><?= $l['zamanDamgasi'] ?></td>
                <td>#<?= $l['kullaniciId'] ?></td>
                <td><span class="badge badge-gold"><?= $l['islemTipi'] ?></span></td>
                <td><span class="badge badge-success"><?= $l['shillBiddingDurumu'] ?></span></td>
                <td><span style="font-family:monospace; font-size:0.7rem; color:var(--gold-bright);"><?= substr($l['currentHash'] ?? '0000', 0, 16) ?>...</span></td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>

    <!-- 10. TAB: SİSTEM AYARLARI -->
    <div id="admTab_ayarlar" class="tab-content <?= $activeTab === 'ayarlar' ? 'active' : '' ?>">
      <div class="page-breadcrumb">ADMIN / ÇALIŞMA ALANI</div>
      <h1 class="page-heading">Sistem Parametreleri</h1>
      <p class="page-subheading">Anti-Sniping eşikleri, kontenjan limitleri ve kur parametreleri</p>

      <div class="panel-card">
        <div style="display:grid; grid-template-columns:1fr 1fr; gap:20px;">
          <div>
            <div style="font-weight:600; color:#fff; margin-bottom:8px;">Anti-Sniping Kuralı</div>
            <p style="font-size:0.82rem; color:var(--text-muted);">Eşik Süre: <strong>30 saniye</strong> • İlk 2 uzatma: <strong>+30 sn</strong> • Sonraki uzatmalar: <strong>+10 sn</strong></p>
          </div>
          <div>
            <div style="font-weight:600; color:#fff; margin-bottom:8px;">Maksimum Katılımcı Kapasitesi</div>
            <p style="font-size:0.82rem; color:var(--text-muted);">Oturum Başı Kontenjan: <strong>50.000 kullanıcı (NFR-06)</strong></p>
          </div>
        </div>
      </div>
    </div>

  </main>
</div>

<script>
function switchAdminTab(tabId) {
  document.querySelectorAll('.sidebar .nav-menu-item').forEach(el => el.classList.remove('active'));
  document.querySelectorAll('.tab-content').forEach(el => el.classList.remove('active'));

  const activeBtn = event.currentTarget.closest('.nav-menu-item');
  if (activeBtn) activeBtn.classList.add('active');

  const target = document.getElementById('admTab_' + tabId);
  if (target) {
    target.classList.add('active');
  }
}
</script>
