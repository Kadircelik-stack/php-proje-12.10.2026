<?php
declare(strict_types=1);

/** @var callable $formatPrice */
/** @var array $db */
/** @var \Martello\Controllers\WebAndApiController $controller */
/** @var string $activeCurrency */

$helpData = $controller->getHelpCenterData();
$systemRules = $helpData['systemRules'] ?? [];
$auctionGuide = $helpData['auctionGuide'] ?? [];
$faqList = $helpData['faqList'] ?? [];

$buyerId = 3; // Emre K. (Örnek Alıcı)
$userTickets = $controller->getSupportTickets($buyerId);
$ticketStats = $controller->getSupportTicketStatistics();

$chatSessions = $db['liveChatSessions'] ?? [];
$activeSession = !empty($chatSessions) ? $chatSessions[0] : null;

$activeHelpTab = $_GET['htab'] ?? 'kurallar';
if (!in_array($activeHelpTab, ['kurallar', 'rehber', 'sss', 'biletler', 'canli_sohbet'], true)) {
    if ($activeHelpTab === 'destek') {
        $activeHelpTab = 'biletler';
    } else {
        $activeHelpTab = 'kurallar';
    }
}
?>

<div class="app-container">
  <!-- Sol Menü (Sidebar) -->
  <aside class="sidebar">
    <div>
      <div class="sidebar-section-title">Yardım Masası</div>
      <ul class="nav-menu">
        <li class="nav-menu-item <?= $activeHelpTab === 'kurallar' ? 'active' : '' ?>">
          <button type="button" data-htab="kurallar" onclick="switchHelpTab('kurallar', this)">
            <span class="menu-icon"><svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/></svg></span>
            <span>Sistem Kuralları</span>
            <span style="margin-left:auto; font-size:0.68rem; background:#1e293b; color:#cbd5e1; padding:1px 6px; border-radius:10px;"><?= count($systemRules) ?></span>
          </button>
        </li>
        <li class="nav-menu-item <?= $activeHelpTab === 'rehber' ? 'active' : '' ?>">
          <button type="button" data-htab="rehber" onclick="switchHelpTab('rehber', this)">
            <span class="menu-icon"><svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="12" cy="12" r="10"/><polygon points="16.24 7.76 14.12 14.12 7.76 16.24 9.88 9.88 16.24 7.76"/></svg></span>
            <span>Müzayede Rehberi</span>
            <span style="margin-left:auto; font-size:0.68rem; background:#1e293b; color:#cbd5e1; padding:1px 6px; border-radius:10px;">6 Adım</span>
          </button>
        </li>
        <li class="nav-menu-item <?= $activeHelpTab === 'sss' ? 'active' : '' ?>">
          <button type="button" data-htab="sss" onclick="switchHelpTab('sss', this)">
            <span class="menu-icon"><svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="12" cy="12" r="10"/><path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"/><path d="M12 17h.01"/></svg></span>
            <span>Sıkça Sorulanlar (SSS)</span>
            <span style="margin-left:auto; font-size:0.68rem; background:#1e293b; color:#cbd5e1; padding:1px 6px; border-radius:10px;"><?= count($faqList) ?></span>
          </button>
        </li>
        <li class="nav-menu-item <?= $activeHelpTab === 'biletler' ? 'active' : '' ?>">
          <button type="button" data-htab="biletler" onclick="switchHelpTab('biletler', this)">
            <span class="menu-icon"><svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect width="20" height="14" x="2" y="5" rx="2"/><line x1="2" x2="22" y1="10" y2="10"/></svg></span>
            <span>Destek Biletlerim</span>
            <span style="margin-left:auto; font-size:0.68rem; background:rgba(212,175,55,0.2); color:#d4af37; padding:1px 6px; border-radius:10px; font-weight:700;"><?= count($userTickets) ?></span>
          </button>
        </li>
        <li class="nav-menu-item <?= $activeHelpTab === 'canli_sohbet' ? 'active' : '' ?>">
          <button type="button" data-htab="canli_sohbet" onclick="switchHelpTab('canli_sohbet', this)" style="color:#34d399;">
            <span class="menu-icon"><svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg></span>
            <span>Canlı Destek Hattı</span>
            <span style="margin-left:auto; font-size:0.65rem; background:#065f46; color:#a7f3d0; padding:1px 6px; border-radius:10px;">🟢 Canlı</span>
          </button>
        </li>
      </ul>

      <!-- Hızlı Özet Bilgi -->
      <div style="margin-top:22px;">
        <div class="sidebar-section-title">Temel İhale Esasları</div>
        <div style="background:#11151f; border:1px solid #1e293b; border-radius:8px; padding:12px; font-size:0.75rem; color:#94a3b8; display:flex; flex-direction:column; gap:8px;">
          <div style="display:flex; justify-content:space-between;">
            <span>Anti-Sniping Süresi:</span>
            <strong style="color:#d4af37;">+60 Saniye</strong>
          </div>
          <div style="display:flex; justify-content:space-between;">
            <span>Emanet Teminat:</span>
            <strong style="color:#34d399;">%10 - %20 Bloke</strong>
          </div>
          <div style="display:flex; justify-content:space-between;">
            <span>Ekspertiz Heyeti:</span>
            <strong style="color:#38bdf8;">Çift Onaylı</strong>
          </div>
          <div style="display:flex; justify-content:space-between;">
            <span>Alıcı Primi:</span>
            <strong style="color:#f0f3f6;">%12.0</strong>
          </div>
        </div>
      </div>

      <!-- Pazar Yeri Kısayolları -->
      <div style="margin-top:20px;">
        <div class="sidebar-section-title">Pazar Yeri</div>
        <ul class="nav-menu">
          <li class="nav-menu-item">
            <a href="?role=vitrin&currency=<?= htmlspecialchars($activeCurrency) ?>">
              <span class="menu-icon"><svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="12" cy="12" r="10"/><line x1="2" x2="22" y1="12" y2="12"/><path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/></svg></span>
              Vitrin & Keşfet
            </a>
          </li>
          <li class="nav-menu-item">
            <a href="?role=buyer&tab=canli&currency=<?= htmlspecialchars($activeCurrency) ?>">
              <span class="menu-icon"><svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg></span>
              Canlı Müzayedeler
            </a>
          </li>
          <li class="nav-menu-item">
            <a href="?role=buyer&currency=<?= htmlspecialchars($activeCurrency) ?>">
              <span class="menu-icon"><svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M6 9H4.5a2.5 2.5 0 0 1 0-5H6"/><path d="M18 9h1.5a2.5 2.5 0 0 0 0-5H18"/><path d="M4 22h16"/><path d="M18 2H6v7a6 6 0 0 0 12 0V2Z"/></svg></span>
              Alıcı Çalışma Alanı
            </a>
          </li>
        </ul>
      </div>
    </div>

    <!-- Temsilci Durum Rozeti -->
    <div class="sidebar-demo-badge" style="margin-top:auto; background:#111622; border:1px solid #1f293d; border-radius:8px; padding:12px;">
      <div style="display:flex; align-items:center; gap:8px; margin-bottom:4px;">
        <span style="display:inline-block; width:8px; height:8px; background:#10b981; border-radius:50%; box-shadow:0 0 8px #10b981;"></span>
        <span style="font-weight:700; font-size:0.75rem; color:#f0f3f6;">Canlı Destek Çevrimiçi</span>
      </div>
      <div style="font-size:0.7rem; color:#848d9a;">Temsilci: <strong>Elif Çelik</strong></div>
      <div style="font-size:0.68rem; color:#d4af37; margin-top:2px;">Ort. Yanıt Süresi: ~2 Dakika</div>
    </div>
  </aside>

  <!-- Ana İçerik Bölümü -->
  <main class="main-content">
    
    <!-- Breadcrumb & Başlık -->
    <div class="page-breadcrumb">MARTELLO / YARDIM & DESTEK MERKEZİ</div>
    
    <div style="display:flex; justify-content:space-between; align-items:flex-start; flex-wrap:wrap; gap:16px; margin-bottom:20px;">
      <div>
        <h1 class="page-heading">Yardım ve Bilgi Merkezi</h1>
        <p class="page-subheading" style="color:#d4af37; font-weight:500; font-size:0.92rem; margin-top:2px;">
          ⚡ "Kullanıcılara sistem kurallarını, müzayede rehberini, sıkça sorulan soruları (SSS) ve canlı/bilet tabanlı destek kanalını sunan yardım merkezidir."
        </p>
      </div>

      <!-- Canlı Arama Inputu -->
      <div style="min-width:320px; max-width:420px; position:relative;">
        <input type="text" id="helpCenterLiveSearch" onkeyup="searchHelpCenter(this.value)" placeholder="Kurallar, rehber, SSS veya destek konularında arayın..." style="width:100%; padding:9px 36px 9px 14px; background:#10141d; border:1px solid #28354a; border-radius:20px; color:#f1f5f9; font-size:0.82rem; outline:none; transition:all 0.2s;" onfocus="this.style.borderColor='var(--gold-primary)'; this.style.boxShadow='0 0 10px rgba(212,175,55,0.2)';" onblur="this.style.borderColor='#28354a'; this.style.boxShadow='none';">
        <span style="position:absolute; right:12px; top:50%; transform:translateY(-50%); color:#d4af37; font-size:0.9rem; pointer-events:none;">🔍</span>
      </div>
    </div>

    <!-- 4 Temel KPI Kartı (Sistem Özeti) -->
    <div class="kpi-grid" style="grid-template-columns: repeat(4, 1fr); gap:14px; margin-bottom:24px;">
      <div class="kpi-card" style="border-left:4px solid var(--gold-primary); cursor:pointer;" onclick="switchHelpTab('kurallar')">
        <div class="kpi-label" style="display:flex; align-items:center; gap:6px;">
          <span>📜</span>
          <span>1. SİSTEM KURALLARI</span>
        </div>
        <div class="kpi-value" style="color:#d4af37; margin:4px 0; font-size:1.45rem;">
          <?= count($systemRules) ?> Temel Kural
        </div>
        <div style="font-size:0.72rem; color:#94a3b8;">
          Anti-Sniping, Pey Basamağı, Escrow
        </div>
      </div>

      <div class="kpi-card" style="border-left:4px solid #38bdf8; cursor:pointer;" onclick="switchHelpTab('rehber')">
        <div class="kpi-label" style="display:flex; align-items:center; gap:6px;">
          <span>🏛️</span>
          <span>2. MÜZAYEDE REHBERİ</span>
        </div>
        <div class="kpi-value" style="color:#38bdf8; margin:4px 0; font-size:1.45rem;">
          6 Aşamalı Kılavuz
        </div>
        <div style="font-size:0.72rem; color:#94a3b8;">
          Üyelikten Sigortalı Teslimata
        </div>
      </div>

      <div class="kpi-card" style="border-left:4px solid #a78bfa; cursor:pointer;" onclick="switchHelpTab('sss')">
        <div class="kpi-label" style="display:flex; align-items:center; gap:6px;">
          <span>❓</span>
          <span>3. SIKÇA SORULANLAR</span>
        </div>
        <div class="kpi-value" style="color:#a78bfa; margin:4px 0; font-size:1.45rem;">
          <?= count($faqList) ?> Soru & Cevap
        </div>
        <div style="font-size:0.72rem; color:#94a3b8;">
          Teklif, Ödeme, Ekspertiz, Kargo
        </div>
      </div>

      <div class="kpi-card" style="border-left:4px solid #34d399; cursor:pointer;" onclick="switchHelpTab('biletler')">
        <div class="kpi-label" style="display:flex; align-items:center; gap:6px;">
          <span>🎫</span>
          <span>4. DESTEK MASASI</span>
        </div>
        <div class="kpi-value" style="color:#34d399; margin:4px 0; font-size:1.45rem;">
          <?= (int)$ticketStats['total'] ?> Talep (<?= (int)$ticketStats['resolved'] ?> Çözüldü)
        </div>
        <div style="font-size:0.72rem; color:#94a3b8;">
          Bilet Takibi & Canlı Sohbet
        </div>
      </div>
    </div>

    <!-- =======================================================================
         1. TAB: SİSTEM KURALLARI (SYSTEM RULES)
         ======================================================================= -->
    <div id="helpTab_kurallar" class="tab-content <?= $activeHelpTab === 'kurallar' ? 'active' : '' ?>">
      <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:16px;">
        <div>
          <h2 style="font-size:1.25rem; color:#fff; font-weight:600; margin:0 0 2px 0;">📜 Platform İşletim ve İhale Esasları</h2>
          <p style="font-size:0.8rem; color:#94a3b8; margin:0;">Teklif verme, teminat yönetimi ve uyuşmazlık çözümü kuralları</p>
        </div>
        <span class="badge badge-gold" style="font-size:0.72rem; padding:4px 10px;">SRS Madde 1-6</span>
      </div>

      <!-- Kurallar 3x2 Grid Düzeni -->
      <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(340px, 1fr)); gap:16px; margin-bottom:24px;">
        <?php foreach ($systemRules as $rule): ?>
          <div class="panel-card help-rule-item" style="border-left:4px solid var(--gold-primary); background:#121622; padding:20px; display:flex; flex-direction:column; justify-content:space-between; margin-bottom:0;">
            <div>
              <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:10px;">
                <span style="font-size:1.4rem;"><?= $rule['ikon'] ?></span>
                <span class="badge badge-gold" style="font-size:0.65rem; padding:2px 8px;"><?= htmlspecialchars($rule['rozet']) ?></span>
              </div>
              <div style="font-size:0.7rem; color:#d4af37; font-weight:700; text-transform:uppercase; letter-spacing:0.5px;"><?= htmlspecialchars($rule['kod']) ?></div>
              <h3 style="font-size:1.05rem; color:#fff; font-weight:600; margin:4px 0 8px 0;"><?= htmlspecialchars($rule['baslik']) ?></h3>
              <p style="font-size:0.82rem; color:#cbd5e1; font-weight:500; margin-bottom:10px; line-height:1.45;">
                <?= htmlspecialchars($rule['ozet']) ?>
              </p>
              <div style="font-size:0.78rem; color:#94a3b8; line-height:1.55; background:#0b0e14; border:1px solid #1e293b; border-radius:6px; padding:10px 12px;">
                <?= htmlspecialchars($rule['detay']) ?>
              </div>
            </div>
          </div>
        <?php endforeach; ?>
      </div>

      <!-- Kademeli Asgari Pey Cetveli -->
      <div class="panel-card" style="background:#121622; border:1px solid #243044; border-radius:10px; padding:20px;">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:14px;">
          <div>
            <h3 class="panel-title" style="margin-bottom:2px;">📊 Kademeli Asgari Pey ve Artış Basamakları Tablosu</h3>
            <span style="font-size:0.75rem; color:#94a3b8;">Teklif motorunun doğruladığı zorunlu basamak tarifesi</span>
          </div>
          <span class="badge badge-info" style="font-size:0.7rem;">Adil Artış Kuralı</span>
        </div>

        <div style="overflow-x:auto;">
          <table class="data-table" style="width:100%; font-size:0.82rem;">
            <thead>
              <tr style="background:#0b0e14; color:#d4af37;">
                <th style="padding:10px 12px;">Mevcut Teklif Aralığı</th>
                <th style="padding:10px 12px;">Zorunlu Artış Adımı</th>
                <th style="padding:10px 12px;">Örnek Senaryo</th>
                <th style="padding:10px 12px; text-align:center;">Anti-Sniping Uzatması</th>
                <th style="padding:10px 12px; text-align:center;">Emanet Teminatı</th>
              </tr>
            </thead>
            <tbody>
              <tr>
                <td style="color:#fff; font-weight:600;">₺0 — ₺25.000</td>
                <td style="color:#34d399; font-weight:700;">+₺1.500</td>
                <td style="color:#94a3b8;">₺15.000 pey üzerine bir sonraki teklif en az ₺16.500 olmalıdır.</td>
                <td style="text-align:center; color:#38bdf8; font-weight:600;">+60 Sn</td>
                <td style="text-align:center; color:#fbbf24;">%10 Bloke</td>
              </tr>
              <tr>
                <td style="color:#fff; font-weight:600;">₺25.001 — ₺100.000</td>
                <td style="color:#34d399; font-weight:700;">+₺3.000</td>
                <td style="color:#94a3b8;">₺50.000 pey üzerine bir sonraki teklif en az ₺53.000 olmalıdır.</td>
                <td style="text-align:center; color:#38bdf8; font-weight:600;">+60 Sn</td>
                <td style="text-align:center; color:#fbbf24;">%10 Bloke</td>
              </tr>
              <tr>
                <td style="color:#fff; font-weight:600;">₺100.001 — ₺500.000</td>
                <td style="color:#34d399; font-weight:700;">+₺10.000</td>
                <td style="color:#94a3b8;">₺340.000 pey üzerine bir sonraki teklif en az ₺350.000 olmalıdır.</td>
                <td style="text-align:center; color:#38bdf8; font-weight:600;">+60 Sn</td>
                <td style="text-align:center; color:#fbbf24;">%15 Bloke</td>
              </tr>
              <tr>
                <td style="color:#fff; font-weight:600;">₺500.001 ve Üzeri (VIP)</td>
                <td style="color:#eab308; font-weight:700;">+₺25.000</td>
                <td style="color:#94a3b8;">₺1.200.000 pey üzerine bir sonraki teklif en az ₺1.225.000 olmalıdır.</td>
                <td style="text-align:center; color:#38bdf8; font-weight:600;">+90 Sn (VIP)</td>
                <td style="text-align:center; color:#fbbf24;">%20 Bloke</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- =======================================================================
         2. TAB: MÜZAYEDE REHBERİ (AUCTION GUIDE)
         ======================================================================= -->
    <div id="helpTab_rehber" class="tab-content <?= $activeHelpTab === 'rehber' ? 'active' : '' ?>">
      <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:16px;">
        <div>
          <h2 style="font-size:1.25rem; color:#fff; font-weight:600; margin:0 0 2px 0;">🏛️ 6 Aşamada Müzayede Yol Haritası</h2>
          <p style="font-size:0.8rem; color:#94a3b8; margin:0;">Eser keşfinden pey sürmeye ve sigortalı teslimata adım adım rehber</p>
        </div>
        <span class="badge badge-info" style="font-size:0.72rem; padding:4px 10px;">Rehber Kılavuzu</span>
      </div>

      <!-- 6 Adım Yol Haritası Kartları -->
      <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(320px, 1fr)); gap:16px; margin-bottom:24px;">
        <?php foreach ($auctionGuide as $step): ?>
          <div class="panel-card help-guide-item" style="background:#121622; border:1px solid #243044; border-radius:10px; padding:20px; position:relative; margin-bottom:0;">
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:12px;">
              <div style="display:flex; align-items:center; gap:8px;">
                <span style="display:inline-flex; align-items:center; justify-content:center; width:26px; height:26px; background:var(--gold-primary); color:#0c0f14; border-radius:50%; font-weight:700; font-size:0.8rem;">
                  <?= $step['adim'] ?>
                </span>
                <span style="font-size:1.3rem;"><?= $step['ikon'] ?></span>
              </div>
              <span style="font-size:0.7rem; color:#d4af37; text-transform:uppercase; font-weight:700;">AŞAMA <?= $step['adim'] ?></span>
            </div>
            <h3 style="font-size:1rem; color:#fff; font-weight:600; margin:0 0 6px 0;"><?= htmlspecialchars($step['baslik']) ?></h3>
            <p style="font-size:0.8rem; color:#94a3b8; line-height:1.55; margin:0;">
              <?= htmlspecialchars($step['aciklama']) ?>
            </p>
          </div>
        <?php endforeach; ?>
      </div>

      <!-- Teklif Türleri Karşılaştırma Matrisi -->
      <div class="panel-card" style="background:#121622; border:1px solid #243044; border-radius:10px; padding:20px;">
        <h3 class="panel-title" style="margin-bottom:4px;">⚡ Pey Verme Türleri Strateji Rehberi</h3>
        <p style="font-size:0.8rem; color:#94a3b8; margin-bottom:16px;">Hedeflediğiniz lota ve müzayede hızına en uygun yöntemi seçin.</p>

        <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(230px, 1fr)); gap:14px;">
          <div style="background:#0b0e14; border:1px solid #1e293b; border-radius:8px; padding:16px;">
            <div style="font-size:1.2rem; margin-bottom:6px;">🖱️</div>
            <div style="font-size:0.88rem; color:#fff; font-weight:600; margin-bottom:4px;">1. Manuel Canlı Pey</div>
            <div style="font-size:0.76rem; color:#94a3b8; line-height:1.5;">
              "Pey Ver" butonuna basarak mevcut teklifin tam bir asgari artış adımı üzerine çıkarsınız. Canlı yayını takip ederken anlık kontrol sağlar.
            </div>
          </div>

          <div style="background:#0b0e14; border:1px solid #1e293b; border-radius:8px; padding:16px;">
            <div style="font-size:1.2rem; margin-bottom:6px;">🚀</div>
            <div style="font-size:0.88rem; color:#fff; font-weight:600; margin-bottom:4px;">2. Hızlı Artış Butonları</div>
            <div style="font-size:0.76rem; color:#94a3b8; line-height:1.5;">
              +1.500₺, +5.000₺ veya +10.000₺ butonlarına basarak rakiplerinizi psikolojik olarak saf dışı bırakacak süratli ve iddialı pey sürebilirsiniz.
            </div>
          </div>

          <div style="background:#0b0e14; border:1px solid #1e293b; border-radius:8px; padding:16px;">
            <div style="font-size:1.2rem; margin-bottom:6px;">🤖</div>
            <div style="font-size:0.88rem; color:#fff; font-weight:600; margin-bottom:4px;">3. Otomatik Pey (Proxy Bid)</div>
            <div style="font-size:0.76rem; color:#94a3b8; line-height:1.5;">
              Tavan bütçenizi girin. Sistem sizin yerinize yalnızca rakip teklif geldiğinde bir asgari artış kadar yükselir. İnternetiniz kopsa dahi liderliği korur.
            </div>
          </div>

          <div style="background:#0b0e14; border:1px solid #1e293b; border-radius:8px; padding:16px;">
            <div style="font-size:1.2rem; margin-bottom:6px;">📞</div>
            <div style="font-size:0.88rem; color:#fff; font-weight:600; margin-bottom:4px;">4. VIP Telefonla Temsilci</div>
            <div style="font-size:0.76rem; color:#94a3b8; line-height:1.5;">
              Müzayede anında özel müşteri yöneticiniz sizi telefonla bağlar; salondan canlı aktarımla komutunuz üzerine fiziki ve dijital çekiçte pey sürer.
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- =======================================================================
         3. TAB: SIKÇA SORULAN SORULAR (SSS / FAQ)
         ======================================================================= -->
    <div id="helpTab_sss" class="tab-content <?= $activeHelpTab === 'sss' ? 'active' : '' ?>">
      <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:12px; margin-bottom:16px;">
        <div>
          <h2 style="font-size:1.25rem; color:#fff; font-weight:600; margin:0 0 2px 0;">❓ Sıkça Sorulan Sorular (SSS)</h2>
          <p style="font-size:0.8rem; color:#94a3b8; margin:0;">Müzayedeler, ödemeler, teminatlar, ekspertiz ve kargo konuları</p>
        </div>

        <!-- Kategori Filtre Butonları -->
        <div style="display:flex; gap:6px; flex-wrap:wrap;">
          <button type="button" class="faq-filter-btn active btn-gold-pill" onclick="filterFaqCategory('ALL', this)" style="padding:5px 12px; font-size:0.76rem; cursor:pointer;">Tümü (<?= count($faqList) ?>)</button>
          <button type="button" class="faq-filter-btn btn-dark-pill" onclick="filterFaqCategory('PEY_MUZAYEDE', this)" style="padding:5px 12px; font-size:0.76rem; cursor:pointer;">Teklif & Pey</button>
          <button type="button" class="faq-filter-btn btn-dark-pill" onclick="filterFaqCategory('ODEME_EMANET', this)" style="padding:5px 12px; font-size:0.76rem; cursor:pointer;">Ödeme & Emanet</button>
          <button type="button" class="faq-filter-btn btn-dark-pill" onclick="filterFaqCategory('EKSPERTIZ_ORIJINALLIK', this)" style="padding:5px 12px; font-size:0.76rem; cursor:pointer;">Ekspertiz & Belge</button>
          <button type="button" class="faq-filter-btn btn-dark-pill" onclick="filterFaqCategory('KARGO_TESLIMAT', this)" style="padding:5px 12px; font-size:0.76rem; cursor:pointer;">Kargo & Teslimat</button>
          <button type="button" class="faq-filter-btn btn-dark-pill" onclick="filterFaqCategory('IPTAL_IHTILAF', this)" style="padding:5px 12px; font-size:0.76rem; cursor:pointer;">İptal & İhtilaf</button>
        </div>
      </div>

      <!-- SSS Akordeon Listesi -->
      <div id="faqAccordionContainer" style="display:flex; flex-direction:column; gap:10px;">
        <?php foreach ($faqList as $idx => $faq): ?>
          <div class="faq-card-item" data-cat="<?= htmlspecialchars($faq['kategori']) ?>" style="background:#121622; border:1px solid #243044; border-radius:8px; overflow:hidden; transition:border-color 0.2s;">
            <button type="button" onclick="toggleFaqAccordion(<?= $idx ?>)" style="width:100%; text-align:left; background:none; border:none; padding:16px 18px; display:flex; justify-content:space-between; align-items:center; cursor:pointer; gap:12px;">
              <div style="display:flex; align-items:center; gap:10px;">
                <span class="badge badge-gold" style="font-size:0.65rem; padding:2px 7px;"><?= htmlspecialchars($faq['kategoriAd']) ?></span>
                <span class="faq-question-text" style="color:#fff; font-size:0.9rem; font-weight:600;"><?= htmlspecialchars($faq['soru']) ?></span>
              </div>
              <span id="faqIcon_<?= $idx ?>" style="color:var(--gold-primary); font-size:1.1rem; font-weight:700;">+</span>
            </button>
            <div id="faqBody_<?= $idx ?>" class="faq-body" style="display:none; padding:0 18px 16px 18px; color:#cbd5e1; font-size:0.84rem; line-height:1.6; border-top:1px solid rgba(255,255,255,0.04);">
              <div style="background:#0b0e14; border:1px solid #1e293b; border-radius:6px; padding:12px 14px; margin-top:8px;">
                <?= htmlspecialchars($faq['cevap']) ?>
              </div>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    </div>

    <!-- =======================================================================
         4. TAB: DESTEK BİLETLERİM (SUPPORT TICKETS DESK)
         ======================================================================= -->
    <div id="helpTab_biletler" class="tab-content <?= $activeHelpTab === 'biletler' ? 'active' : '' ?>">
      <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:16px;">
        <div>
          <h2 style="font-size:1.25rem; color:#fff; font-weight:600; margin:0 0 2px 0;">🎫 Bilet Tabanlı Destek Masası</h2>
          <p style="font-size:0.8rem; color:#94a3b8; margin:0;">Teknik, finansal, teklif ve ekspertiz biletlerinizin resmi kaydı</p>
        </div>
        <button type="button" onclick="toggleNewTicketForm()" class="btn-gold-pill" style="padding:7px 16px; font-size:0.8rem; cursor:pointer;">
          + Yeni Destek Talebi Aç
        </button>
      </div>

      <!-- Yeni Bilet Açma Formu Container -->
      <div id="newTicketFormContainer" class="panel-card" style="display:none; background:#121622; border:1px solid var(--gold-primary); border-radius:10px; padding:20px; margin-bottom:20px; box-shadow:0 8px 24px rgba(0,0,0,0.5);">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:12px;">
          <div style="color:#d4af37; font-weight:700; font-size:0.9rem;">Yeni Destek Bileti Oluştur</div>
          <button type="button" onclick="toggleNewTicketForm()" style="background:none; border:none; color:#888; font-size:1.3rem; cursor:pointer; line-height:1;">&times;</button>
        </div>

        <form method="POST">
          <input type="hidden" name="action" value="create_support_ticket">
          <input type="hidden" name="user_id" value="<?= $buyerId ?>">
          <input type="hidden" name="user_name" value="Emre K. (Alıcı)">
          <input type="hidden" name="user_email" value="alici@martello.com">

          <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px; margin-bottom:12px;">
            <div>
              <label class="form-label" style="font-size:0.78rem; color:#f0f3f6;">Talep Kategorisi *</label>
              <select name="category" class="form-control" style="background:#0b0e14; border:1px solid #28354a; color:#fff; border-radius:6px; font-size:0.82rem;" required>
                <option value="Sistem Kuralları & Pey">Sistem Kuralları & Pey</option>
                <option value="Ödeme & Emanet Bloke">Ödeme & Emanet Bloke</option>
                <option value="Ekspertiz & Orijinallik">Ekspertiz & Orijinallik</option>
                <option value="Kargo & Sigortalı Teslimat">Kargo & Sigortalı Teslimat</option>
                <option value="İptal & İhtilaf">İptal & İhtilaf</option>
                <option value="Genel Bilgi">Genel Bilgi</option>
              </select>
            </div>

            <div>
              <label class="form-label" style="font-size:0.78rem; color:#f0f3f6;">Öncelik Derecesi *</label>
              <select name="priority" class="form-control" style="background:#0b0e14; border:1px solid #28354a; color:#fff; border-radius:6px; font-size:0.82rem;" required>
                <option value="NORMAL">Normal Öncelik</option>
                <option value="YUKSEK">Yüksek Öncelik</option>
                <option value="ACIL">Acil Durum (Pey/Ödeme)</option>
              </select>
            </div>
          </div>

          <div style="display:grid; grid-template-columns:2fr 1fr; gap:12px; margin-bottom:12px;">
            <div>
              <label class="form-label" style="font-size:0.78rem; color:#f0f3f6;">Konu Başlığı *</label>
              <input type="text" name="subject" class="form-control" placeholder="Örn: Pey teminatımın iade durumu..." style="background:#0b0e14; border:1px solid #28354a; color:#fff; border-radius:6px; font-size:0.82rem;" required>
            </div>
            <div>
              <label class="form-label" style="font-size:0.78rem; color:#f0f3f6;">İlgili Lot No (Opsiyonel)</label>
              <input type="number" name="lot_id" class="form-control" placeholder="Örn: 1002" style="background:#0b0e14; border:1px solid #28354a; color:#fff; border-radius:6px; font-size:0.82rem;">
            </div>
          </div>

          <div style="margin-bottom:14px;">
            <label class="form-label" style="font-size:0.78rem; color:#f0f3f6;">Detaylı Açıklama *</label>
            <textarea name="message" class="form-control" rows="3" placeholder="Lütfen sorununuzu veya talebinizi detaylı olarak açıklayınız..." style="background:#0b0e14; border:1px solid #28354a; color:#fff; border-radius:6px; font-size:0.82rem;" required></textarea>
          </div>

          <div style="display:flex; justify-content:flex-end; gap:8px;">
            <button type="button" onclick="toggleNewTicketForm()" class="btn-dark-pill" style="padding:6px 16px; font-size:0.78rem; cursor:pointer;">Vazgeç</button>
            <button type="submit" class="btn-gold-pill" style="padding:6px 18px; font-size:0.8rem; cursor:pointer;">Talebi Gönder &rarr;</button>
          </div>
        </form>
      </div>

      <!-- Biletler Listesi -->
      <div class="panel-card" style="background:#121622; border:1px solid #243044; border-radius:10px; padding:18px;">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:14px;">
          <div style="font-size:0.88rem; color:#fff; font-weight:600;">Destek Biletlerim (<?= count($userTickets) ?>)</div>
          <div style="display:flex; gap:6px;">
            <span class="badge badge-warning" style="font-size:0.65rem;"><?= (int)$ticketStats['open'] ?> Açık</span>
            <span class="badge badge-success" style="font-size:0.65rem;"><?= (int)$ticketStats['resolved'] ?> Çözüldü</span>
          </div>
        </div>

        <?php if (empty($userTickets)): ?>
          <div style="text-align:center; padding:30px; color:#64748b; font-size:0.85rem;">
            Henüz oluşturulmuş bir destek talebiniz bulunmuyor.
          </div>
        <?php else: ?>
          <div style="display:flex; flex-direction:column; gap:12px;">
            <?php foreach ($userTickets as $t): ?>
              <?php
                $durum = strtoupper((string)($t['durum'] ?? 'ACIK'));
                $bColor = ($durum === 'COZULDU') ? '#64748b' : (($durum === 'YANITLANDI') ? '#34d399' : '#fbbf24');
              ?>
              <div style="background:#0b0e14; border:1px solid #1e293b; border-left:3px solid <?= $bColor ?>; border-radius:8px; padding:14px 16px;">
                <div style="display:flex; justify-content:space-between; align-items:flex-start; flex-wrap:wrap; gap:8px; margin-bottom:8px;">
                  <div>
                    <span style="font-family:monospace; color:#d4af37; font-weight:700; font-size:0.82rem;"><?= htmlspecialchars($t['biletNo']) ?></span>
                    <span style="font-size:0.72rem; color:#64748b; margin-left:8px;"><?= htmlspecialchars(substr($t['olusturmaTarihi'] ?? '', 0, 16)) ?></span>
                    <?php if (!empty($t['lotId'])): ?>
                      <span style="font-size:0.7rem; color:#cbd5e1; background:#1e293b; padding:1px 6px; border-radius:4px; margin-left:6px;">Lot #<?= (int)$t['lotId'] ?></span>
                    <?php endif; ?>
                  </div>
                  <div style="display:flex; align-items:center; gap:6px;">
                    <span class="badge" style="background:rgba(212,175,55,0.12); color:#d4af37; border:1px solid rgba(212,175,55,0.3); font-size:0.65rem; padding:2px 6px;">
                      <?= htmlspecialchars($t['kategori']) ?>
                    </span>
                    <span class="badge" style="background:rgba(0,0,0,0.4); color:<?= $bColor ?>; border:1px solid <?= $bColor ?>; font-size:0.65rem; padding:2px 6px;">
                      ● <?= $durum ?>
                    </span>
                  </div>
                </div>

                <div style="font-size:0.88rem; color:#fff; font-weight:600; margin-bottom:4px;">
                  <?= htmlspecialchars($t['konu']) ?>
                </div>
                <div style="font-size:0.8rem; color:#94a3b8; line-height:1.5; margin-bottom:10px;">
                  <?= htmlspecialchars($t['mesaj']) ?>
                </div>

                <!-- Bilet Görüşme Geçmişi -->
                <?php if (!empty($t['cevaplar'])): ?>
                  <div style="border-top:1px solid #1e293b; padding-top:10px; margin-top:8px; display:flex; flex-direction:column; gap:8px;">
                    <div style="font-size:0.7rem; color:#d4af37; font-weight:700; text-transform:uppercase;">Görüşme Geçmişi (<?= count($t['cevaplar']) ?>)</div>
                    <?php foreach ($t['cevaplar'] as $ans): ?>
                      <?php $isStaff = in_array(strtoupper($ans['yazanRol'] ?? ''), ['DESTEK_UZMANI', 'ADMIN', 'CANLI DESTEK'], true); ?>
                      <div style="background:<?= $isStaff ? '#131924' : '#0e1219' ?>; border-left:3px solid <?= $isStaff ? '#d4af37' : '#38bdf8' ?>; border-radius:4px; padding:8px 12px;">
                        <div style="display:flex; justify-content:space-between; font-size:0.7rem; color:#848d9a; margin-bottom:2px;">
                          <strong style="color:<?= $isStaff ? '#d4af37' : '#38bdf8' ?>;"><?= htmlspecialchars($ans['yazanAd'] ?? '') ?> (<?= htmlspecialchars($ans['yazanRol'] ?? '') ?>)</strong>
                          <span><?= htmlspecialchars($ans['tarih'] ?? '') ?></span>
                        </div>
                        <div style="font-size:0.78rem; color:#e2e8f0; line-height:1.4;">
                          <?= htmlspecialchars($ans['mesaj'] ?? '') ?>
                        </div>
                      </div>
                    <?php endforeach; ?>
                  </div>
                <?php endif; ?>

                <!-- Yanıt Formu -->
                <?php if ($durum !== 'COZULDU'): ?>
                  <form method="POST" style="margin-top:10px; display:flex; gap:6px;">
                    <input type="hidden" name="action" value="reply_support_ticket">
                    <input type="hidden" name="ticket_id" value="<?= (int)$t['id'] ?>">
                    <input type="hidden" name="user_id" value="<?= $buyerId ?>">
                    <input type="hidden" name="user_name" value="Emre K.">
                    <input type="hidden" name="user_role" value="ALICI">
                    <input type="text" name="reply_text" class="form-control" placeholder="Bu talebe ek açıklama yazınız..." style="background:#161c28; border:1px solid #28354a; font-size:0.78rem; color:#fff; border-radius:6px;" required>
                    <button type="submit" class="btn-outline-gold btn-sm" style="white-space:nowrap; padding:4px 12px; font-size:0.75rem;">Yanıtla</button>
                  </form>
                <?php else: ?>
                  <div style="font-size:0.7rem; color:#64748b; margin-top:6px;">
                    ✓ Bu bilet başarıyla çözümlendi olarak arşivlenmiştir.
                  </div>
                <?php endif; ?>
              </div>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>
      </div>
    </div>

    <!-- =======================================================================
         5. TAB: CANLI DESTEK HATTI (LIVE SUPPORT CHANNEL)
         ======================================================================= -->
    <div id="helpTab_canli_sohbet" class="tab-content <?= $activeHelpTab === 'canli_sohbet' ? 'active' : '' ?>">
      <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:16px;">
        <div>
          <h2 style="font-size:1.25rem; color:#fff; font-weight:600; margin:0 0 2px 0;">💬 Canlı Destek ve Müzayede Danışmanlığı</h2>
          <p style="font-size:0.8rem; color:#94a3b8; margin:0;">Platform uzmanı ile şifreli, gerçek zamanlı danışmanlık kanalı</p>
        </div>
        <span class="badge badge-success" style="font-size:0.72rem; padding:4px 10px;">🟢 Canlı Sohbet Aktif</span>
      </div>

      <div class="panel-card" style="background:#121622; border:1px solid #243044; border-radius:10px; padding:20px; display:flex; flex-direction:column; height:600px;">
        <!-- Temsilci Header -->
        <div style="display:flex; justify-content:space-between; align-items:center; padding-bottom:12px; border-bottom:1px solid #1e293b; margin-bottom:12px;">
          <div style="display:flex; align-items:center; gap:10px;">
            <div style="position:relative;">
              <img src="public/assets/images/avatar.jpg" alt="Support" style="width:36px; height:36px; border-radius:50%; object-fit:cover; border:2px solid #10b981;">
              <span style="position:absolute; bottom:0; right:0; width:9px; height:9px; background:#10b981; border:2px solid #121622; border-radius:50%;"></span>
            </div>
            <div>
              <div style="font-size:0.9rem; color:#fff; font-weight:600;">Elif Çelik</div>
              <div style="font-size:0.7rem; color:#10b981; font-weight:600;">● Canlı Destek & Koleksiyon Danışmanı</div>
            </div>
          </div>
          <div style="font-size:0.72rem; color:#94a3b8;">
            Oturum: <span style="color:#d4af37; font-family:monospace;"><?= htmlspecialchars($activeSession['oturumId'] ?? 'CHAT-1') ?></span>
          </div>
        </div>

        <!-- Mesaj Akışı -->
        <div style="flex:1; overflow-y:auto; padding:12px; background:#0b0e14; border-radius:8px; border:1px solid #1e293b; display:flex; flex-direction:column; gap:10px; margin-bottom:12px;">
          <div style="text-align:center; margin:2px 0 8px 0;">
            <span style="font-size:0.68rem; color:#64748b; background:#161c28; padding:2px 10px; border-radius:10px;">
              256-bit SSL Korumalı Canlı Görüşme
            </span>
          </div>

          <?php foreach ($activeSession['mesajlar'] ?? [] as $msg): ?>
            <?php $isStaff = ((int)($msg['gonderenId'] ?? 0) === 5); ?>
            <div style="align-self:<?= $isStaff ? 'flex-start' : 'flex-end' ?>; max-width:82%;">
              <div style="font-size:0.68rem; color:#64748b; margin-bottom:2px; text-align:<?= $isStaff ? 'left' : 'right' ?>;">
                <?= htmlspecialchars($msg['gonderenAd'] ?? '') ?> • <?= htmlspecialchars(substr($msg['zaman'] ?? '', 11, 5)) ?>
              </div>
              <div style="padding:9px 13px; border-radius:10px; font-size:0.84rem; line-height:1.45; background:<?= $isStaff ? '#1e293b' : 'linear-gradient(135deg, #c89e58 0%, #a47937 100%)' ?>; color:<?= $isStaff ? '#f1f5f9' : '#0c0f14' ?>; font-weight:<?= $isStaff ? '400' : '600' ?>; border:1px solid <?= $isStaff ? '#334155' : '#d4af37' ?>;">
                <?= htmlspecialchars($msg['icerik'] ?? '') ?>
              </div>
            </div>
          <?php endforeach; ?>
        </div>

        <!-- Hızlı Soru Butonları -->
        <div style="display:flex; gap:6px; flex-wrap:wrap; margin-bottom:8px;">
          <button type="button" class="btn-dark-pill" style="font-size:0.68rem; padding:3px 8px; cursor:pointer;" onclick="fillChatMessage('Lot #1002 için ekspertiz raporunun kondisyon notunu öğrenebilir miyim?')">Ekspertiz Notu</button>
          <button type="button" class="btn-dark-pill" style="font-size:0.68rem; padding:3px 8px; cursor:pointer;" onclick="fillChatMessage('Pey geçildiğinde teminat blokesi ne kadar sürede açılır?')">Teminat Blokesi</button>
          <button type="button" class="btn-dark-pill" style="font-size:0.68rem; padding:3px 8px; cursor:pointer;" onclick="fillChatMessage('Özel sigortalı sanat kargo teslimat randevusu nasıl planlanır?')">Kargo Randevusu</button>
        </div>

        <!-- Mesaj Gönderme Formu -->
        <form method="POST" style="display:flex; gap:8px;">
          <input type="hidden" name="action" value="send_chat">
          <input type="hidden" name="session_id" value="<?= $activeSession['id'] ?? 951 ?>">
          <input type="hidden" name="sender_id" value="<?= $buyerId ?>">
          <input type="hidden" name="sender_name" value="Emre K. (Alıcı)">
          <input type="text" id="liveChatInput" name="message" class="form-control" placeholder="Canlı destek uzmanına mesajınızı yazın..." required style="background:#0b0e14; border:1px solid #28354a; color:#fff; font-size:0.82rem; border-radius:6px;">
          <button type="submit" class="btn-gold" style="white-space:nowrap; padding:8px 16px; font-size:0.82rem; border-radius:6px;">
            Gönder &rarr;
          </button>
        </form>
      </div>
    </div>

  </main>
</div>

<script>
function switchHelpTab(tabId, btn) {
  // Hide all tab contents
  document.querySelectorAll('.main-content .tab-content').forEach(el => el.classList.remove('active'));
  document.querySelectorAll('.sidebar .nav-menu-item').forEach(el => el.classList.remove('active'));

  // Show target tab
  const target = document.getElementById('helpTab_' + tabId);
  if (target) {
    target.classList.add('active');
  }

  // Active menu item
  let navItem = null;
  if (btn && btn.closest) {
    navItem = btn.closest('.nav-menu-item');
  }
  if (!navItem) {
    const matchingBtn = document.querySelector(`.sidebar button[data-htab="${tabId}"]`);
    if (matchingBtn) {
      navItem = matchingBtn.closest('.nav-menu-item');
    }
  }
  if (navItem) {
    navItem.classList.add('active');
  }

  // URL state update
  if (window.history && window.history.replaceState) {
    try {
      const url = new URL(window.location.href);
      url.searchParams.set('htab', tabId);
      window.history.replaceState({}, '', url);
    } catch(e) {}
  }
}

function toggleFaqAccordion(idx) {
  const body = document.getElementById('faqBody_' + idx);
  const icon = document.getElementById('faqIcon_' + idx);
  if (!body) return;
  if (body.style.display === 'none' || body.style.display === '') {
    body.style.display = 'block';
    if (icon) icon.innerText = '−';
  } else {
    body.style.display = 'none';
    if (icon) icon.innerText = '+';
  }
}

function filterFaqCategory(cat, btn) {
  document.querySelectorAll('.faq-filter-btn').forEach(b => {
    b.classList.remove('btn-gold-pill');
    b.classList.add('btn-dark-pill');
  });
  if (btn) {
    btn.classList.remove('btn-dark-pill');
    btn.classList.add('btn-gold-pill');
  }

  const items = document.querySelectorAll('.faq-card-item');
  items.forEach(el => {
    if (cat === 'ALL' || el.getAttribute('data-cat') === cat) {
      el.style.display = 'block';
    } else {
      el.style.display = 'none';
    }
  });
}

function searchHelpCenter(q) {
  q = (q || '').toLowerCase().trim();
  
  // SSS Arama
  document.querySelectorAll('.faq-card-item').forEach(el => {
    const text = el.innerText.toLowerCase();
    el.style.display = (!q || text.indexOf(q) !== -1) ? 'block' : 'none';
  });

  // Kurallar Arama
  document.querySelectorAll('.help-rule-item').forEach(el => {
    const text = el.innerText.toLowerCase();
    el.style.display = (!q || text.indexOf(q) !== -1) ? 'flex' : 'none';
  });

  // Rehber Arama
  document.querySelectorAll('.help-guide-item').forEach(el => {
    const text = el.innerText.toLowerCase();
    el.style.display = (!q || text.indexOf(q) !== -1) ? 'block' : 'none';
  });
}

function toggleNewTicketForm() {
  const form = document.getElementById('newTicketFormContainer');
  if (form) {
    form.style.display = (form.style.display === 'none' || form.style.display === '') ? 'block' : 'none';
    if (form.style.display === 'block') {
      form.scrollIntoView({ behavior: 'smooth' });
    }
  }
}

function fillChatMessage(text) {
  const input = document.getElementById('liveChatInput');
  if (input) {
    input.value = text;
    input.focus();
  }
}
</script>
