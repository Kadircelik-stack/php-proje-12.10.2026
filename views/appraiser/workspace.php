<?php
declare(strict_types=1);

/** @var callable $formatPrice */
/** @var array $db */

$expertId = 4; // Burak Şahin (Ekspertiz Uzmanı)
$requests = array_filter($db['appraisalRequests'] ?? [], static fn($r) => (int)($r['atananEksperId'] ?? 0) === $expertId);
$pendingRequests = array_filter($requests, static fn($r) => ($r['durum'] ?? '') === 'BEKLEYEN');
$completedReports = array_filter($db['appraisalReports'] ?? [], static fn($rep) => (int)($rep['eksperId'] ?? 0) === $expertId);
?>

<div class="app-container">
  <!-- Sol Menü (Sidebar) — SRS Sayfa 35 ile Birebir Uyumlu -->
  <aside class="sidebar">
    <div>
      <div class="sidebar-section-title">Çalışma Alanı</div>
      <ul class="nav-menu">
        <li class="nav-menu-item active"><a href="?role=appraiser">📋 Görev Paneli</a></li>
        <li class="nav-menu-item"><a href="#incelemeler">🔍 İncelemeler</a></li>
        <li class="nav-menu-item"><a href="#rapor-gecmisi">📁 Rapor Geçmişi</a></li>
        <li class="nav-menu-item"><a href="#itiraz-talepleri">⚖️ İtiraz Talepleri</a></li>
        <li class="nav-menu-item"><a href="#bildirimler">🔔 Bildirimler</a></li>
      </ul>
    </div>

    <!-- Akreditasyon Kartı -->
    <div style="background:#11161f; border:1px solid var(--border-color); border-radius:8px; padding:14px;">
      <div style="font-size:0.75rem; color:var(--text-muted);">AKREDİTASYON</div>
      <div style="font-weight:700; color:var(--gold-bright); font-size:0.9rem; margin-top:4px;">
        Marmara Ekspertiz A.Ş.
      </div>
      <div style="font-size:0.75rem; color:#3fb950; margin-top:4px;">✓ Sicil No: OD-IST-44910</div>
    </div>
  </aside>

  <!-- Ana İçerik Alanı -->
  <main class="main-content">
    <div style="font-size:0.8rem; color:var(--gold-primary); text-transform:uppercase; margin-bottom:4px;">
      Ekspertiz Uzmanı / Çalışma Alanı
    </div>
    <h1 style="font-size:2rem; color:#fff; font-weight:600; margin-bottom:6px;">Görev paneli</h1>
    <p style="color:var(--text-muted); font-size:0.9rem; margin-bottom:28px;">
      Seçkin müzayede deneyiminizi yönetin
    </p>

    <!-- 4 Adet KPI Kartı (Sayfa 35 Tasarımı) -->
    <div class="kpi-grid">
      <div class="kpi-card highlight">
        <div class="kpi-label">Atanmış İşler</div>
        <div class="kpi-value"><?= count($requests) ?></div>
      </div>
      <div class="kpi-card">
        <div class="kpi-label">Bekleyen</div>
        <div class="kpi-value"><?= count($pendingRequests) ?></div>
      </div>
      <div class="kpi-card">
        <div class="kpi-label">Raporlar</div>
        <div class="kpi-value"><?= count($completedReports) ?></div>
      </div>
      <div class="kpi-card">
        <div class="kpi-label">İtirazlar</div>
        <div class="kpi-value">0</div>
      </div>
    </div>

    <!-- 3 Görev Kartı (Sayfa 35'teki AUC-1042, AUC-1043, AUC-1044 Kartları) -->
    <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(280px, 1fr)); gap:20px; margin-bottom:32px;">
      <?php foreach ($pendingRequests as $req): ?>
        <?php
          $prod = null;
          foreach ($db['products'] ?? [] as $p) {
              if ((int)$p['id'] === (int)($req['urunId'] ?? 0)) {
                  $prod = $p;
                  break;
              }
          }
        ?>
        <div class="panel-card" style="margin-bottom:0; display:flex; flex-direction:column; justify-content:space-between;">
          <div>
            <div style="font-size:0.75rem; color:var(--gold-bright); font-weight:700; margin-bottom:6px;">
              <?= htmlspecialchars($req['lotKodu'] ?? 'AUC-1042') ?>
            </div>
            <h3 style="font-size:1.15rem; color:#fff; font-weight:600; margin-bottom:8px;">
              <?= htmlspecialchars($prod['baslik'] ?? 'Müzayede Lotu') ?>
            </h3>
            <p style="font-size:0.85rem; color:var(--text-muted); line-height:1.5;">
              <?= htmlspecialchars($req['not'] ?? 'Kondisyon ve özgünlük incelemesi bekliyor.') ?>
            </p>
          </div>

          <div style="margin-top:20px; border-top:1px solid rgba(255,255,255,0.06); padding-top:14px;">
            <button type="button" class="btn-gold btn-sm" onclick="openAppraisalModal(<?= $req['id'] ?>, '<?= htmlspecialchars(addslashes($prod['baslik'] ?? 'Lot')) ?>')">
              Rapor Oluştur &rarr;
            </button>
          </div>
        </div>
      <?php endforeach; ?>
    </div>

    <!-- Ekspertiz Raporu Düzenleme Formu (FR-18) -->
    <div id="rapor-form-box" class="panel-card" style="border-color:var(--border-gold);">
      <div class="panel-header">
        <h3 class="panel-title">📝 Fiziksel Değerlendirme ve Ekspertiz Raporu Düzenle (FR-18)</h3>
      </div>
      <form method="POST">
        <input type="hidden" name="action" value="submit_appraisal">
        <input type="hidden" name="expert_id" value="<?= $expertId ?>">

        <div style="display:grid; grid-template-columns:1fr 1fr; gap:16px;">
          <div class="form-group">
            <label class="form-label">İncelenecek Görev Talebi</label>
            <select name="request_id" id="selectedRequestSelect" class="form-control">
              <?php foreach ($pendingRequests as $r): ?>
                <option value="<?= $r['id'] ?>"><?= $r['lotKodu'] ?> — <?= $r['talepNo'] ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="form-group">
            <label class="form-label">Önerilen Piyasa Değeri (TRY)</label>
            <input type="number" step="1000" name="suggested_value" class="form-control" value="65000" required>
          </div>
        </div>

        <div class="form-group">
          <label class="form-label">Orijinallik ve Kondisyon Notu</label>
          <input type="text" name="authenticity_note" class="form-control" value="Orijinal - Ekspertiz Seçki Damgalı" required>
        </div>

        <div class="form-group">
          <label class="form-label">Teknik Bulgular ve Madde Açıklamaları (Her satıra bir madde)</label>
          <textarea name="notes" class="form-control" rows="4">Kasa, kadran ve mekanik bileşenler orijinal fabrikasyon kondisyonundadır.
Mikroskop altında kök boya ve malzeme testleri doğrulanmıştır.
Eser üzerinde sonradan yapılan restorasyon veya müdahale izine rastlanmamıştır.</textarea>
        </div>

        <button type="submit" class="btn-gold" style="width:100%; justify-content:center; padding:12px;">
          🛡️ Raporu Onayla ve E-İmzalı Olarak Kaydet
        </button>
      </form>
    </div>
  </main>
</div>

<script>
function openAppraisalModal(reqId, title) {
  const sel = document.getElementById('selectedRequestSelect');
  if (sel) {
    sel.value = reqId;
  }
  const formBox = document.getElementById('rapor-form-box');
  if (formBox) {
    formBox.scrollIntoView({ behavior: 'smooth' });
  }
}
</script>
