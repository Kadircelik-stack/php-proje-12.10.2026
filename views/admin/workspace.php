<?php
declare(strict_types=1);

/** @var callable $formatPrice */
/** @var array $db */

$users = $db['users'] ?? [];
$lots = $db['lots'] ?? [];
$reports = $db['appraisalReports'] ?? [];
$disputes = $db['disputes'] ?? [];
$listings = $db['listings'] ?? [];
$auditLogs = $db['auditLogs'] ?? [];

$openDisputes = array_filter($disputes, static fn($d) => ($d['durum'] ?? '') === 'INCELENIYOR');
?>

<div class="app-container">
  <!-- Sol Menü (Sidebar) — SRS Sayfa 34 Alt Görsel ile Birebir Uyumlu -->
  <aside class="sidebar">
    <div>
      <div class="sidebar-section-title">Çalışma Alanı</div>
      <ul class="nav-menu">
        <li class="nav-menu-item active"><a href="?role=admin">⚙️ Yönetim Konsolu</a></li>
        <li class="nav-menu-item"><a href="#kullanicilar">👥 Kullanıcılar</a></li>
        <li class="nav-menu-item"><a href="#ilan-moderasyonu">🛡️ İlan Moderasyonu</a></li>
        <li class="nav-menu-item"><a href="#ekspertiz-atama">🔍 Ekspertiz Atama</a></li>
        <li class="nav-menu-item"><a href="#kategoriler">📂 Kategoriler</a></li>
        <li class="nav-menu-item"><a href="#komisyon">📊 Komisyon Oranları</a></li>
        <li class="nav-menu-item"><a href="#raporlar">📑 Raporlar</a></li>
        <li class="nav-menu-item"><a href="#ihtilaflar">⚖️ İhtilaflar</a></li>
        <li class="nav-menu-item"><a href="#denetim-izi">📜 Denetim İzi (WORM)</a></li>
        <li class="nav-menu-item"><a href="#sistem-ayarlari">⚡ Sistem Ayarları</a></li>
      </ul>
    </div>
  </aside>

  <!-- Ana İçerik Alanı -->
  <main class="main-content">
    <div style="font-size:0.8rem; color:var(--gold-primary); text-transform:uppercase; margin-bottom:4px;">
      Admin / Çalışma Alanı
    </div>
    <h1 style="font-size:2rem; color:#fff; font-weight:600; margin-bottom:6px;">Yönetim konsolu</h1>
    <p style="color:var(--text-muted); font-size:0.9rem; margin-bottom:28px;">
      Seçkin müzayede deneyiminizi yönetin
    </p>

    <!-- 4 Adet KPI Kartı (Sayfa 34 Alt Tasarımı) -->
    <div class="kpi-grid">
      <div class="kpi-card highlight">
        <div class="kpi-label">Kullanıcılar</div>
        <div class="kpi-value"><?= count($users) ?></div>
      </div>
      <div class="kpi-card">
        <div class="kpi-label">Aktif Lotlar</div>
        <div class="kpi-value"><?= count($lots) ?></div>
      </div>
      <div class="kpi-card">
        <div class="kpi-label">Raporlar</div>
        <div class="kpi-value"><?= count($reports) ?></div>
      </div>
      <div class="kpi-card">
        <div class="kpi-label">Açık İhtilaflar</div>
        <div class="kpi-value"><?= count($openDisputes) ?></div>
      </div>
    </div>

    <!-- Yönetim Konsolu Bilgi Kartı -->
    <div class="panel-card">
      <div class="panel-header">
        <h2 class="panel-title">Yönetim konsolu</h2>
      </div>
      <p style="color:var(--text-muted); font-size:0.9rem;">
        Moderasyon, ekspertiz ve rapor akışlarını soldaki menüden yönetin.
      </p>
    </div>

    <!-- 1. İlan Moderasyonu ve Onaylama (FR-09, FR-10) -->
    <div id="ilan-moderasyonu" class="panel-card">
      <div class="panel-header">
        <h3 class="panel-title">🛡️ İlan Moderasyonu ve Canlı Müzayedeye Başlatma (FR-09, FR-10)</h3>
      </div>
      <table class="data-table">
        <thead>
          <tr>
            <th>İlan Kodu</th>
            <th>Ürün ID</th>
            <th>Satıcı ID</th>
            <th>Durum</th>
            <th>İşlem</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($listings as $l): ?>
            <tr>
              <td><span class="badge badge-gold"><?= htmlspecialchars($l['ilanKodu']) ?></span></td>
              <td>Ürün #<?= $l['urunId'] ?></td>
              <td>Satıcı #<?= $l['saticiId'] ?></td>
              <td>
                <span class="badge <?= $l['durum'] === 'ONAYLANDI' ? 'badge-success' : 'badge-warning' ?>">
                  <?= htmlspecialchars($l['durum']) ?>
                </span>
              </td>
              <td>
                <?php if ($l['durum'] !== 'YAYINDA'): ?>
                  <form method="POST" style="display:inline-flex; gap:6px;">
                    <input type="hidden" name="action" value="approve_listing">
                    <input type="hidden" name="listing_id" value="<?= $l['id'] ?>">
                    <input type="hidden" name="start_price" value="15000">
                    <input type="hidden" name="reserve_price" value="20000">
                    <input type="hidden" name="min_step" value="1000">
                    <button type="submit" class="btn-gold btn-sm">Müzayedeye Başlat</button>
                  </form>
                <?php else: ?>
                  <span style="color:#3fb950; font-size:0.8rem; font-weight:600;">Yayında</span>
                <?php endif; ?>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>

    <!-- 2. Kullanıcı Yönetimi ve Askıya Alma (FR-04, FR-06) -->
    <div id="kullanicilar" class="panel-card">
      <div class="panel-header">
        <h3 class="panel-title">👥 Kullanıcı ve Rol Yönetimi / Askıya Alma (FR-05, FR-06)</h3>
      </div>
      <table class="data-table">
        <thead>
          <tr>
            <th>ID</th>
            <th>Ad Soyad</th>
            <th>E-Posta</th>
            <th>Rol</th>
            <th>KYC</th>
            <th>Durum</th>
            <th>Erişim Yaptırımı</th>
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
                <?= !empty($u['kycDogrulama']['dogrulandiMi']) ? '<span style="color:#3fb950;">✓ Onaylı</span>' : '<span style="color:#d29922;">Bekliyor</span>' ?>
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
                      <button type="submit" class="btn-outline-gold btn-sm" style="color:#f85149; border-color:#f85149;">Askıya Al (Ban)</button>
                    <?php else: ?>
                      <input type="hidden" name="new_status" value="AKTIF">
                      <button type="submit" class="btn-gold btn-sm">Yasağı Kaldır</button>
                    <?php endif; ?>
                  </form>
                <?php else: ?>
                  <span style="color:var(--text-muted); font-size:0.8rem;">Sistem Sahibi</span>
                <?php endif; ?>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>

    <!-- 3. Değiştirilemez WORM Denetim İzi (FR-25, R-12) -->
    <div id="denetim-izi" class="panel-card">
      <div class="panel-header">
        <div>
          <h3 class="panel-title">📜 Değiştirilemez Denetim İzi (WORM Audit Trail)</h3>
          <p style="font-size:0.8rem; color:var(--text-muted); margin-top:2px;">
            SHA-256 Blok Zincirleme (Hash Chaining) ile adli bilişim ve hukuki delil kaydı.
          </p>
        </div>
        <span class="badge badge-info">SIEM & WORM Aktif</span>
      </div>
      <table class="data-table">
        <thead>
          <tr>
            <th>Log ID</th>
            <th>Zaman</th>
            <th>Kullanıcı</th>
            <th>İşlem Tipi</th>
            <th>Shill Bidding</th>
            <th>SHA-256 Hash</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($auditLogs as $log): ?>
            <tr>
              <td><code><?= htmlspecialchars($log['logId'] ?? '') ?></code></td>
              <td style="font-size:0.75rem; color:var(--text-muted);"><?= htmlspecialchars($log['zamanDamgasi'] ?? '') ?></td>
              <td>#<?= $log['kullaniciId'] ?? 0 ?> (<?= htmlspecialchars($log['kullaniciRol'] ?? 'USER') ?>)</td>
              <td><span class="badge badge-gold"><?= htmlspecialchars($log['islemTipi'] ?? '') ?></span></td>
              <td>
                <span class="badge <?= ($log['shillBiddingDurumu'] ?? '') === 'TEMIZ' ? 'badge-success' : 'badge-danger' ?>">
                  <?= htmlspecialchars($log['shillBiddingDurumu'] ?? 'TEMIZ') ?>
                </span>
              </td>
              <td>
                <span style="font-family:monospace; font-size:0.7rem; color:var(--gold-bright);" title="<?= htmlspecialchars($log['currentHash'] ?? '') ?>">
                  <?= substr($log['currentHash'] ?? '0000', 0, 16) ?>...
                </span>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </main>
</div>
