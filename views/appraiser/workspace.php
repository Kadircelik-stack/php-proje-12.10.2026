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
            Görev paneli
          </button>
        </li>
        <li class="nav-menu-item <?= $activeTab === 'inceleme' ? 'active' : '' ?>">
          <button type="button" onclick="switchAppraiserTab('inceleme')">
            İncelemeler
          </button>
        </li>
        <li class="nav-menu-item <?= $activeTab === 'gecmis' ? 'active' : '' ?>">
          <button type="button" onclick="switchAppraiserTab('gecmis')">
            Rapor geçmişi
          </button>
        </li>
        <li class="nav-menu-item <?= $activeTab === 'itiraz' ? 'active' : '' ?>">
          <button type="button" onclick="switchAppraiserTab('itiraz')">
            İtiraz talepleri
          </button>
        </li>
        <li class="nav-menu-item <?= $activeTab === 'bildirim' ? 'active' : '' ?>">
          <button type="button" onclick="switchAppraiserTab('bildirim')">
            Bildirimler
          </button>
        </li>
      </ul>
    </div>

    <!-- Sidebar Demo Rozeti -->
    <div class="sidebar-demo-badge">
      <div style="font-weight:700; font-size:0.75rem; color:#d4af37; letter-spacing:0.5px;">MARTELLO / DEMO</div>
      <div style="font-size:0.7rem; color:#888; margin-top:2px;">İşlemler sunum amaçlıdır.</div>
    </div>
  </aside>

  <!-- Ana İçerik Alanı -->
  <main class="main-content">

    <!-- 1. TAB: GÖREV PANELİ (media_1795014776304_7eb23357.jpg ile Birebir) -->
    <div id="appTab_gorev" class="tab-content <?= $activeTab === 'gorev' ? 'active' : '' ?>">
      <div class="page-breadcrumb">EKSPERTİZ UZMANI / ÇALIŞMA ALANI</div>
      <h1 class="page-heading">Görev paneli</h1>
      <p class="page-subheading" style="margin-bottom:24px;">Seçkin müzayede deneyiminizi yönetin</p>

      <!-- 4 Adet KPI Kartı -->
      <div class="kpi-grid">
        <div class="kpi-card">
          <div class="kpi-label">ATANMIŞ İŞLER</div>
          <div class="kpi-value">3</div>
        </div>
        <div class="kpi-card highlight">
          <div class="kpi-label">BEKLEYEN</div>
          <div class="kpi-value">3</div>
        </div>
        <div class="kpi-card">
          <div class="kpi-label">RAPORLAR</div>
          <div class="kpi-value">0</div>
        </div>
        <div class="kpi-card">
          <div class="kpi-label">İTİRAZLAR</div>
          <div class="kpi-value">0</div>
        </div>
      </div>

      <!-- 3 Görev Kartı (AUC-1042, 1043, 1044) -->
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
            <button type="button" class="btn-dark-pill" style="padding:8px 18px; cursor:pointer;" onclick="openExpertReportModal(202, 'AUC-1042', '1968 İsviçre Vintage Saat', 65000, 'Saat mekanizması ve kronograf fonksiyonları laboratuvar testlerinde incelenmiş, orijinal fabrikasyon kondisyonunda bulunmuştur.')">
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
            <button type="button" class="btn-dark-pill" style="padding:8px 18px; cursor:pointer;" onclick="openExpertReportModal(203, 'AUC-1043', 'Koleksiyon Analog Kamera', 25000, 'Kamera aksamı oldukça iyi durumda gerekli incelemeler neticesinde satışa uygun bulunmuştur')">
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
            <button type="button" class="btn-dark-pill" style="padding:8px 18px; cursor:pointer;" onclick="openExpertReportModal(204, 'AUC-1044', 'Çağdaş Sanat Baskısı', 18000, 'Kağıt ve baskı kalitesi laboratuvar ortamında spektral testlerle incelenmiş ve doğrulanmıştır.')">
              Rapor oluştur
            </button>
          </div>
        </div>
      </div>
    </div>
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
            Raporu Mühürle ve Admin Onayına Gönder
          </button>
        </form>
      </div>
    </div>

    <!-- 3. TAB: RAPOR GEÇMİŞİ (media_1795014776291_27bc4f96.jpg ile Birebir) -->
    <div id="appTab_gecmis" class="tab-content <?= $activeTab === 'gecmis' ? 'active' : '' ?>">
      <div class="page-breadcrumb">EKSPERTİZ UZMANI / ÇALIŞMA ALANI</div>
      <h1 class="page-heading">Rapor geçmişi</h1>
      <p class="page-subheading" style="margin-bottom:24px;">Seçkin müzayede deneyiminizi yönetin</p>

      <div class="panel-card" style="padding:0; overflow:hidden;">
        <table class="data-table" style="margin:0;">
          <thead>
            <tr>
              <th style="padding-left:24px; width:30%;">Lot</th>
              <th style="width:35%;">Kondisyon</th>
              <th style="padding-right:24px; width:35%;">Tahmini değer</th>
            </tr>
          </thead>
          <tbody>
            <tr>
              <td style="padding-left:24px; font-weight:600; color:#fff;">AUC-1043</td>
              <td style="color:#f0f3f6;">İyi</td>
              <td style="padding-right:24px; font-family:var(--font-serif); font-weight:600; color:#fff;">₺25.000</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- 4. TAB: İTİRAZ TALEPLERİ -->
    <div id="appTab_itiraz" class="tab-content <?= $activeTab === 'itiraz' ? 'active' : '' ?>">
      <div class="page-breadcrumb">EKSPERTİZ UZMANI / ÇALIŞMA ALANI</div>
      <h1 class="page-heading">İtiraz talepleri</h1>
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

<!-- Ekspertiz Raporu Modalı (Görseldeki Birebir Modal) -->
<div id="expertReportModal" class="martello-modal" style="display:none; position:fixed; z-index:9999; left:0; top:0; width:100%; height:100%; background:rgba(0,0,0,0.85); backdrop-filter:blur(6px); align-items:center; justify-content:center;">
  <div style="background:#141822; border:1px solid #2a3449; border-radius:12px; max-width:480px; width:92%; padding:26px; box-shadow:0 10px 30px rgba(0,0,0,0.8); position:relative;">
    <button type="button" onclick="closeExpertModal()" style="position:absolute; right:16px; top:16px; background:none; border:none; color:#888; font-size:1.4rem; cursor:pointer;">&times;</button>
    
    <div style="color:#d4af37; font-size:0.72rem; font-weight:700; letter-spacing:1px; text-transform:uppercase; margin-bottom:4px;">MARTELLO • DEMO İŞLEM</div>
    <div style="font-family:var(--font-serif); font-size:1.45rem; font-weight:600; color:#fff; margin-bottom:2px;">Ekspertiz raporu • <span id="modalLotKodu">AUC-1043</span></div>
    <div id="modalItemTitle" style="color:#8893a4; font-size:0.85rem; margin-bottom:18px;">Koleksiyon Analog Kamera</div>

    <form method="POST">
      <input type="hidden" name="action" value="submit_appraisal">
      <input type="hidden" name="expert_id" value="<?= $expertId ?>">
      <input type="hidden" name="request_id" id="modalReqId" value="203">

      <div class="form-group" style="margin-bottom:14px;">
        <label class="form-label" style="font-size:0.82rem; color:#f0f3f6;">İnceleme notları *</label>
        <textarea name="notes" id="modalNotes" class="form-control" rows="3" required style="border-radius:8px; background:#10131b; font-size:0.85rem;">Kamera aksamı oldukça iyi durumda gerekli incelemeler neticesinde satışa uygun bulunmuştur</textarea>
      </div>

      <div class="form-group" style="margin-bottom:14px;">
        <label class="form-label" style="font-size:0.82rem; color:#f0f3f6;">Kondisyon</label>
        <select name="kondisyon" class="form-control" style="border-radius:8px; background:#10131b;">
          <option value="İyi" selected>İyi</option>
          <option value="Mükemmel">Mükemmel</option>
          <option value="Orta">Orta</option>
          <option value="Restorasyonlu">Restorasyonlu</option>
        </select>
      </div>

      <div class="form-group" style="margin-bottom:14px;">
        <label class="form-label" style="font-size:0.82rem; color:#f0f3f6;">Özgünlük</label>
        <select name="authenticity_note" class="form-control" style="border-radius:8px; background:#10131b;">
          <option value="Doğrulandı" selected>Doğrulandı</option>
          <option value="Şüpheli">Şüpheli</option>
          <option value="Taklit">Taklit</option>
        </select>
      </div>

      <div class="form-group" style="margin-bottom:22px;">
        <label class="form-label" style="font-size:0.82rem; color:#f0f3f6;">Tahmini değer (₺) *</label>
        <input type="number" step="500" name="suggested_value" id="modalValue" class="form-control" value="25000" required style="border-radius:8px; background:#10131b;">
      </div>

      <button type="submit" class="btn-gold-pill" style="width:100%; padding:12px; font-size:0.9rem; cursor:pointer;">
        Raporu gönder
      </button>
    </form>
  </div>
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

function openExpertReportModal(reqId, lotKodu, title, value, notes) {
  document.getElementById('modalReqId').value = reqId;
  document.getElementById('modalLotKodu').textContent = lotKodu;
  document.getElementById('modalItemTitle').textContent = title;
  document.getElementById('modalValue').value = value || 25000;
  if (notes) {
    document.getElementById('modalNotes').value = notes;
  }
  document.getElementById('expertReportModal').style.display = 'flex';
}

function closeExpertModal() {
  document.getElementById('expertReportModal').style.display = 'none';
}
</script>
