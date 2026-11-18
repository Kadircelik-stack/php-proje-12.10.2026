<?php
declare(strict_types=1);

/** @var callable $formatPrice */
/** @var array $db */

$expertId = 4; // Burak Şahin (Ekspertiz Uzmanı)
$requests = array_filter($db['appraisalRequests'] ?? [], static fn($r) => (int)($r['atananEksperId'] ?? 0) === $expertId);
$pendingRequests = array_filter($requests, static fn($r) => ($r['durum'] ?? '') === 'BEKLEYEN');
$completedReports = array_filter($db['appraisalReports'] ?? [], static fn($rep) => (int)($rep['eksperId'] ?? 0) === $expertId);
$activeTab = $_GET['tab'] ?? 'gorev';
?>

<div class="app-container">
  <!-- Sol Menü (Sidebar) — SRS Sayfa 35 ile Birebir Uyumlu -->
  <aside class="sidebar">
    <div>
      <div class="sidebar-section-title">Çalışma Alanı</div>
      <ul class="nav-menu">
        <li class="nav-menu-item <?= $activeTab === 'gorev' ? 'active' : '' ?>">
          <button type="button" onclick="switchAppraiserTab('gorev')">
            <span class="menu-icon">📋</span> Görev paneli
          </button>
        </li>
        <li class="nav-menu-item <?= $activeTab === 'inceleme' ? 'active' : '' ?>">
          <button type="button" onclick="switchAppraiserTab('inceleme')">
            <span class="menu-icon">🔬</span> İncelemeler
          </button>
        </li>
        <li class="nav-menu-item <?= $activeTab === 'gecmis' ? 'active' : '' ?>">
          <button type="button" onclick="switchAppraiserTab('gecmis')">
            <span class="menu-icon">📁</span> Rapor geçmişi
          </button>
        </li>
        <li class="nav-menu-item <?= $activeTab === 'itiraz' ? 'active' : '' ?>">
          <button type="button" onclick="switchAppraiserTab('itiraz')">
            <span class="menu-icon">⚖️</span> İtiraz talepleri
          </button>
        </li>
        <li class="nav-menu-item <?= $activeTab === 'bildirim' ? 'active' : '' ?>">
          <button type="button" onclick="switchAppraiserTab('bildirim')">
            <span class="menu-icon">🔔</span> Bildirimler
          </button>
        </li>
      </ul>
    </div>
  </aside>

  <!-- Ana İçerik Alanı -->
  <main class="main-content">

    <!-- 1. TAB: GÖREV PANELİ (media_1794924544606_60c5069a.jpg ile Birebir) -->
    <div id="appTab_gorev" class="tab-content <?= $activeTab === 'gorev' ? 'active' : '' ?>">
      <div class="page-breadcrumb">EKSPERTİZ UZMANI / ÇALIŞMA ALANI</div>
      <h1 class="page-heading">Görev paneli</h1>
      <p class="page-subheading">Seçkin müzayede deneyiminizi yönetin</p>

      <!-- 4 Adet KPI Kartı (Sayfa 35 Tasarımı) -->
      <div class="kpi-grid">
        <div class="kpi-card">
          <div class="kpi-label">Atanmış İşler</div>
          <div class="kpi-value">3</div>
        </div>
        <div class="kpi-card highlight">
          <div class="kpi-label">Bekleyen</div>
          <div class="kpi-value">3</div>
        </div>
        <div class="kpi-card">
          <div class="kpi-label">Raporlar</div>
          <div class="kpi-value">0</div>
        </div>
        <div class="kpi-card">
          <div class="kpi-label">İtirazlar</div>
          <div class="kpi-value">0</div>
        </div>
      </div>

      <!-- 3 Görev Kartı (Sayfa 35'teki Birebir AUC-1042, 1043, 1044 Kartları) -->
      <div style="display:grid; grid-template-columns:repeat(3, 1fr); gap:20px; margin-bottom:32px;">
        <!-- Kart 1: AUC-1042 -->
        <div class="panel-card" style="margin-bottom:0; display:flex; flex-direction:column; justify-content:space-between;">
          <div>
            <div style="font-size:0.75rem; color:var(--text-muted); font-weight:700; margin-bottom:6px;">AUC-1042</div>
            <h3 style="font-family:var(--font-serif); font-size:1.15rem; color:#fff; font-weight:600; margin-bottom:10px;">
              1968 İsviçre Vintage Saat
            </h3>
            <p style="font-size:0.85rem; color:var(--text-muted); line-height:1.5;">
              Kondisyon ve özgünlük incelemesi bekliyor.
            </p>
          </div>
          <div style="margin-top:24px;">
            <button type="button" class="btn-gold btn-sm" onclick="openAppraisalForm(202, 'AUC-1042', '1968 İsviçre Vintage Saat')">
              Rapor oluştur
            </button>
          </div>
        </div>

        <!-- Kart 2: AUC-1043 -->
        <div class="panel-card" style="margin-bottom:0; display:flex; flex-direction:column; justify-content:space-between;">
          <div>
            <div style="font-size:0.75rem; color:var(--text-muted); font-weight:700; margin-bottom:6px;">AUC-1043</div>
            <h3 style="font-family:var(--font-serif); font-size:1.15rem; color:#fff; font-weight:600; margin-bottom:10px;">
              Koleksiyon Analog Kamera
            </h3>
            <p style="font-size:0.85rem; color:var(--text-muted); line-height:1.5;">
              Kondisyon ve özgünlük incelemesi bekliyor.
            </p>
          </div>
          <div style="margin-top:24px;">
            <button type="button" class="btn-gold btn-sm" onclick="openAppraisalForm(203, 'AUC-1043', 'Koleksiyon Analog Kamera')">
              Rapor oluştur
            </button>
          </div>
        </div>

        <!-- Kart 3: AUC-1044 -->
        <div class="panel-card" style="margin-bottom:0; display:flex; flex-direction:column; justify-content:space-between;">
          <div>
            <div style="font-size:0.75rem; color:var(--text-muted); font-weight:700; margin-bottom:6px;">AUC-1044</div>
            <h3 style="font-family:var(--font-serif); font-size:1.15rem; color:#fff; font-weight:600; margin-bottom:10px;">
              Çağdaş Sanat Baskısı
            </h3>
            <p style="font-size:0.85rem; color:var(--text-muted); line-height:1.5;">
              Kondisyon ve özgünlük incelemesi bekliyor.
            </p>
          </div>
          <div style="margin-top:24px;">
            <button type="button" class="btn-gold btn-sm" onclick="openAppraisalForm(204, 'AUC-1044', 'Çağdaş Sanat Baskısı')">
              Rapor oluştur
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- 2. TAB: İNCELEMELER & RAPOR DÜZENLEME FORMU -->
    <div id="appTab_inceleme" class="tab-content <?= $activeTab === 'inceleme' ? 'active' : '' ?>">
      <div class="page-breadcrumb">EKSPERTİZ UZMANI / ÇALIŞMA ALANI</div>
      <h1 class="page-heading">Ekspertiz Raporu Düzenle</h1>
      <p class="page-subheading">Laboratuvar incelemesi, orijinallik onayı ve piyasa değerlemesi</p>

      <div class="panel-card">
        <form method="POST">
          <input type="hidden" name="action" value="submit_appraisal">
          <input type="hidden" name="expert_id" value="<?= $expertId ?>">
          <input type="hidden" id="appraisalReqId" name="request_id" value="202">

          <div style="background:#090c10; padding:12px 16px; border-radius:8px; border:1px solid var(--border-color); margin-bottom:18px;">
            Seçili Eser: <strong id="appraisalTargetText" style="color:var(--gold-bright);">AUC-1042 — 1968 İsviçre Vintage Saat</strong>
          </div>

          <div style="display:grid; grid-template-columns:1fr 1fr; gap:16px;">
            <div class="form-group">
              <label class="form-label">Önerilen Piyasa Değeri (TRY)</label>
              <input type="number" step="1000" name="suggested_value" class="form-control" value="65000" required>
            </div>
            <div class="form-group">
              <label class="form-label">Orijinallik Damga Notu</label>
              <input type="text" name="authenticity_note" class="form-control" value="Orijinal - Ekspertiz Seçki Damgalı" required>
            </div>
          </div>

          <div class="form-group">
            <label class="form-label">Teknik Bulgular ve Laboratuvar Notları (Her satıra bir madde)</label>
            <textarea name="notes" class="form-control" rows="4">Kasa, kadran ve mekanik bileşenler orijinal fabrikasyon kondisyonundadır.
Mikroskop altında kök boya ve malzeme testleri doğrulanmıştır.
Eser üzerinde sonradan yapılan restorasyon veya müdahale izine rastlanmamıştır.</textarea>
          </div>

          <button type="submit" class="btn-gold" style="padding:12px 28px;">
            🛡️ Raporu Mühürle ve Admin Onayına Gönder
          </button>
        </form>
      </div>
    </div>

    <!-- 3. TAB: RAPOR GEÇMİŞİ -->
    <div id="appTab_gecmis" class="tab-content <?= $activeTab === 'gecmis' ? 'active' : '' ?>">
      <div class="page-breadcrumb">EKSPERTİZ UZMANI / ÇALIŞMA ALANI</div>
      <h1 class="page-heading">Rapor Geçmişi</h1>
      <p class="page-subheading">Tamamlanan ve mühürlenen tüm ekspertiz belgeleri</p>

      <div class="panel-card">
        <table class="data-table">
          <thead>
            <tr><th>Rapor No</th><th>Lot Kodu</th><th>Piyasa Değeri</th><th>Damga</th><th>Tarih</th></tr>
          </thead>
          <tbody>
            <tr>
              <td><code>REP-2026-112</code></td>
              <td><span class="badge badge-gold">AUC-1001</span></td>
              <td style="color:var(--gold-bright); font-weight:700;"><?= $formatPrice(80000) ?></td>
              <td><span class="badge badge-success">✓ Orijinal / Seçki Damgalı</span></td>
              <td>09-Ekim-2026</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- 4. TAB: İTİRAZ TALEPLERİ -->
    <div id="appTab_itiraz" class="tab-content <?= $activeTab === 'itiraz' ? 'active' : '' ?>">
      <div class="page-breadcrumb">EKSPERTİZ UZMANI / ÇALIŞMA ALANI</div>
      <h1 class="page-heading">İtiraz Talepleri</h1>
      <p class="page-subheading">Raporlarınıza yönelik inceleme itirazları</p>
      <div class="panel-card">
        <p style="color:var(--text-muted);">Herhangi bir raporunuza itiraz yapılmamıştır.</p>
      </div>
    </div>

    <!-- 5. TAB: BİLDİRİMLER -->
    <div id="appTab_bildirim" class="tab-content <?= $activeTab === 'bildirim' ? 'active' : '' ?>">
      <div class="page-breadcrumb">EKSPERTİZ UZMANI / ÇALIŞMA ALANI</div>
      <h1 class="page-heading">Bildirimler</h1>
      <p class="page-subheading">Sistem atamaları ve bilgilendirme kayıtları</p>
      <div class="panel-card">
        <div style="padding:10px 0; border-bottom:1px solid var(--border-color);">
          <div style="font-weight:600; color:#fff;">Yeni Ekspertiz Görevi Atandı (AUC-1042)</div>
          <div style="font-size:0.75rem; color:var(--text-muted); margin-top:2px;">1968 İsviçre Vintage Saat için inceleme talebi panelinize aktarıldı.</div>
        </div>
      </div>
    </div>

  </main>
</div>

<script>
function switchAppraiserTab(tabId) {
  document.querySelectorAll('.sidebar .nav-menu-item').forEach(el => el.classList.remove('active'));
  document.querySelectorAll('.tab-content').forEach(el => el.classList.remove('active'));

  const activeBtn = event.currentTarget.closest('.nav-menu-item');
  if (activeBtn) activeBtn.classList.add('active');

  const target = document.getElementById('appTab_' + tabId);
  if (target) {
    target.classList.add('active');
  }
}

function openAppraisalForm(reqId, lotKodu, title) {
  document.getElementById('appraisalReqId').value = reqId;
  document.getElementById('appraisalTargetText').textContent = lotKodu + ' — ' + title;
  switchAppraiserTab('inceleme');
}
</script>
