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
if (!in_array($activeHelpTab, ['kurallar', 'rehber', 'sss', 'destek'], true)) {
    $activeHelpTab = 'kurallar';
}
?>

<div class="app-container" style="max-width:1440px; margin:0 auto; padding:24px 20px;">

  <!-- Üst Başlık ve Hero Banner -->
  <div style="background:linear-gradient(135deg, #131924 0%, #0c1017 100%); border:1px solid #28354a; border-radius:14px; padding:32px 36px; margin-bottom:28px; position:relative; overflow:hidden; box-shadow:0 12px 36px rgba(0,0,0,0.45);">
    <div style="position:absolute; right:-20px; top:-20px; width:220px; height:220px; background:radial-gradient(circle, rgba(212,175,55,0.12) 0%, transparent 70%); pointer-events:none;"></div>
    
    <div style="display:flex; justify-content:space-between; align-items:flex-start; flex-wrap:wrap; gap:20px; position:relative; z-index:2;">
      <div style="max-width:760px;">
        <div style="display:inline-flex; align-items:center; gap:8px; font-size:0.75rem; text-transform:uppercase; letter-spacing:1.5px; color:#d4af37; font-weight:700; margin-bottom:8px; background:rgba(212,175,55,0.1); border:1px solid rgba(212,175,55,0.25); border-radius:20px; padding:4px 14px;">
          <span>🏛️</span>
          <span>MARTELLO YARDIM VE BİLGİ MERKEZİ</span>
        </div>
        <h1 style="font-size:2.2rem; color:#fff; font-family:var(--font-serif); font-weight:600; margin:0 0 10px 0; letter-spacing:0.5px;">
          Kullanıcı Rehberi, Sistem Kuralları ve Destek Masası
        </h1>
        <p style="color:#94a3b8; font-size:0.95rem; line-height:1.6; margin:0;">
          ⚡ <em>"Kullanıcılara sistem kurallarını, müzayede rehberini, sıkça sorulan soruları (SSS) ve canlı/bilet tabanlı destek kanalını sunan yardım merkezidir."</em>
        </p>
      </div>

      <!-- Hızlı İstatistik Rozetleri -->
      <div style="display:grid; grid-template-columns:repeat(2, 1fr); gap:12px; min-width:280px;">
        <div style="background:#0e1219; border:1px solid #222b3d; border-radius:8px; padding:12px 16px;">
          <div style="font-size:0.7rem; color:#64748b; text-transform:uppercase; font-weight:700;">CANLI DESTEK</div>
          <div style="color:#34d399; font-weight:700; font-size:0.95rem; display:flex; align-items:center; gap:6px; margin-top:2px;">
            <span style="display:inline-block; width:8px; height:8px; background:#10b981; border-radius:50%; box-shadow:0 0 8px #10b981;"></span>
            <span>Çevrimiçi (Elif Ç.)</span>
          </div>
          <div style="font-size:0.72rem; color:#94a3b8; margin-top:2px;">Yanıt süresi: ~2 dk</div>
        </div>

        <div style="background:#0e1219; border:1px solid #222b3d; border-radius:8px; padding:12px 16px;">
          <div style="font-size:0.7rem; color:#64748b; text-transform:uppercase; font-weight:700;">DESTEK BİLETLERİ</div>
          <div style="color:#d4af37; font-weight:700; font-size:0.95rem; margin-top:2px;">
            <?= (int)$ticketStats['total'] ?> Talep (<?= (int)$ticketStats['resolved'] ?> Çözüldü)
          </div>
          <div style="font-size:0.72rem; color:#94a3b8; margin-top:2px;">7/24 Bilet Takibi</div>
        </div>
      </div>
    </div>

    <!-- Canlı Arama Çubuğu -->
    <div style="margin-top:26px; max-width:680px; position:relative;">
      <input type="text" id="helpCenterSearch" onkeyup="searchHelpCenter(this.value)" placeholder="Kurallar, müzayede adımları, SSS veya destek konularında arayın..." style="width:100%; padding:14px 44px 14px 18px; background:#0b0e14; border:1px solid #334155; border-radius:10px; color:#f1f5f9; font-size:0.92rem; outline:none; transition:all 0.2s;" onfocus="this.style.borderColor='var(--gold-primary)'; this.style.boxShadow='0 0 14px rgba(212,175,55,0.2)';" onblur="this.style.borderColor='#334155'; this.style.boxShadow='none';">
      <span style="position:absolute; right:16px; top:50%; transform:translateY(-50%); color:#d4af37; font-size:1.1rem; pointer-events:none;">🔍</span>
    </div>
  </div>

  <!-- Ana Sekme Butonları (4 Temel Sütun) -->
  <div style="display:flex; gap:10px; border-bottom:1px solid #243044; margin-bottom:28px; overflow-x:auto; padding-bottom:2px;">
    <button type="button" class="help-tab-btn <?= $activeHelpTab === 'kurallar' ? 'active' : '' ?>" onclick="switchHelpTab('kurallar', this)" style="display:flex; align-items:center; gap:8px; padding:12px 22px; font-size:0.92rem; font-weight:600; cursor:pointer; background:none; border:none; border-bottom:3px solid <?= $activeHelpTab === 'kurallar' ? 'var(--gold-primary)' : 'transparent' ?>; color:<?= $activeHelpTab === 'kurallar' ? '#fff' : '#94a3b8' ?>; transition:all 0.2s;">
      <span>📜</span>
      <span>Sistem Kuralları</span>
      <span style="font-size:0.7rem; background:#1e293b; color:#cbd5e1; padding:2px 8px; border-radius:10px;"><?= count($systemRules) ?></span>
    </button>

    <button type="button" class="help-tab-btn <?= $activeHelpTab === 'rehber' ? 'active' : '' ?>" onclick="switchHelpTab('rehber', this)" style="display:flex; align-items:center; gap:8px; padding:12px 22px; font-size:0.92rem; font-weight:600; cursor:pointer; background:none; border:none; border-bottom:3px solid <?= $activeHelpTab === 'rehber' ? 'var(--gold-primary)' : 'transparent' ?>; color:<?= $activeHelpTab === 'rehber' ? '#fff' : '#94a3b8' ?>; transition:all 0.2s;">
      <span>🏛️</span>
      <span>Müzayede Rehberi</span>
      <span style="font-size:0.7rem; background:#1e293b; color:#cbd5e1; padding:2px 8px; border-radius:10px;">6 Adım</span>
    </button>

    <button type="button" class="help-tab-btn <?= $activeHelpTab === 'sss' ? 'active' : '' ?>" onclick="switchHelpTab('sss', this)" style="display:flex; align-items:center; gap:8px; padding:12px 22px; font-size:0.92rem; font-weight:600; cursor:pointer; background:none; border:none; border-bottom:3px solid <?= $activeHelpTab === 'sss' ? 'var(--gold-primary)' : 'transparent' ?>; color:<?= $activeHelpTab === 'sss' ? '#fff' : '#94a3b8' ?>; transition:all 0.2s;">
      <span>❓</span>
      <span>Sıkça Sorulan Sorular (SSS)</span>
      <span style="font-size:0.7rem; background:#1e293b; color:#cbd5e1; padding:2px 8px; border-radius:10px;"><?= count($faqList) ?></span>
    </button>

    <button type="button" class="help-tab-btn <?= $activeHelpTab === 'destek' ? 'active' : '' ?>" onclick="switchHelpTab('destek', this)" style="display:flex; align-items:center; gap:8px; padding:12px 22px; font-size:0.92rem; font-weight:600; cursor:pointer; background:none; border:none; border-bottom:3px solid <?= $activeHelpTab === 'destek' ? 'var(--gold-primary)' : 'transparent' ?>; color:<?= $activeHelpTab === 'destek' ? '#fff' : '#94a3b8' ?>; transition:all 0.2s;">
      <span>🎫</span>
      <span>Canlı & Bilet Tabanlı Destek</span>
      <span style="font-size:0.7rem; background:#065f46; color:#a7f3d0; padding:2px 8px; border-radius:10px;">🟢 Canlı Aktif</span>
    </button>
  </div>

  <!-- =========================================================================
       1. SEKME: SİSTEM KURALLARI (SYSTEM RULES)
       ========================================================================= -->
  <div id="helpTab_kurallar" class="help-tab-content" style="display:<?= $activeHelpTab === 'kurallar' ? 'block' : 'none' ?>;">
    <div style="margin-bottom:24px;">
      <h2 style="font-size:1.4rem; color:#fff; font-weight:600; margin-bottom:6px;">MARTELLO Platform İşletim ve İhale Esasları</h2>
      <p style="color:#94a3b8; font-size:0.88rem; margin:0;">
        Tüm üyeler, satıcılar ve katılımcılar platformumuzda teklif verirken ve satış yaparken aşağıdaki noter ve tahkim standartlarına tabidir.
      </p>
    </div>

    <!-- Kurallar Grid Kartları -->
    <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(360px, 1fr)); gap:20px; margin-bottom:30px;">
      <?php foreach ($systemRules as $rule): ?>
        <div class="panel-card help-rule-item" style="border-left:4px solid var(--gold-primary); background:#11151f; padding:22px; display:flex; flex-direction:column; justify-content:space-between;">
          <div>
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:12px;">
              <span style="font-size:1.5rem;"><?= $rule['ikon'] ?></span>
              <span class="badge badge-gold" style="font-size:0.68rem; padding:3px 8px; letter-spacing:0.5px;"><?= htmlspecialchars($rule['rozet']) ?></span>
            </div>
            <div style="font-size:0.72rem; color:#d4af37; font-weight:700; text-transform:uppercase;"><?= htmlspecialchars($rule['kod']) ?></div>
            <h3 style="font-size:1.1rem; color:#fff; font-weight:600; margin:4px 0 10px 0;"><?= htmlspecialchars($rule['baslik']) ?></h3>
            <p style="font-size:0.82rem; color:#cbd5e1; font-weight:500; margin-bottom:10px; line-height:1.5;">
              <?= htmlspecialchars($rule['ozet']) ?>
            </p>
            <div style="font-size:0.8rem; color:#94a3b8; line-height:1.6; background:#0b0e14; border:1px solid #1e293b; border-radius:8px; padding:12px;">
              <?= htmlspecialchars($rule['detay']) ?>
            </div>
          </div>
        </div>
      <?php endforeach; ?>
    </div>

    <!-- İnteraktif Kademeli Asgari Pey Tablosu -->
    <div class="panel-card" style="background:#11151f; border:1px solid #243044; border-radius:12px; padding:24px; margin-bottom:24px;">
      <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:10px; margin-bottom:16px;">
        <div>
          <h3 style="font-size:1.15rem; color:#fff; margin-bottom:4px;">📊 Kademeli Asgari Artış ve Pey Adımları Cetveli</h3>
          <p style="font-size:0.82rem; color:#94a3b8; margin:0;">Teklif verilirken sistemin zorunlu tuttuğu asgari basamak oranları</p>
        </div>
        <span class="badge badge-info" style="font-size:0.72rem;">SRS Teklif Kuralı</span>
      </div>

      <div style="overflow-x:auto;">
        <table style="width:100%; border-collapse:collapse; font-size:0.85rem;">
          <thead>
            <tr style="border-bottom:1px solid #2d3748; background:#0b0e14; color:#d4af37;">
              <th style="padding:10px 14px; text-align:left;">Mevcut Teklif Aralığı</th>
              <th style="padding:10px 14px; text-align:left;">Zorunlu Asgari Pey Adımı</th>
              <th style="padding:10px 14px; text-align:left;">Örnek Senaryo</th>
              <th style="padding:10px 14px; text-align:center;">Anti-Sniping Süresi</th>
              <th style="padding:10px 14px; text-align:center;">Escrow Teminatı</th>
            </tr>
          </thead>
          <tbody>
            <tr style="border-bottom:1px solid #1e293b;">
              <td style="padding:12px 14px; color:#fff; font-weight:600;">₺0 — ₺25.000</td>
              <td style="padding:12px 14px; color:#34d399; font-weight:700;">+₺1.500</td>
              <td style="padding:12px 14px; color:#94a3b8;">₺15.000 pey üzerine bir sonraki teklif en az ₺16.500 olmalıdır.</td>
              <td style="padding:12px 14px; text-align:center; color:#38bdf8;">+60 Sn</td>
              <td style="padding:12px 14px; text-align:center; color:#fbbf24;">%10 Bloke</td>
            </tr>
            <tr style="border-bottom:1px solid #1e293b;">
              <td style="padding:12px 14px; color:#fff; font-weight:600;">₺25.001 — ₺100.000</td>
              <td style="padding:12px 14px; color:#34d399; font-weight:700;">+₺3.000</td>
              <td style="padding:12px 14px; color:#94a3b8;">₺50.000 pey üzerine bir sonraki teklif en az ₺53.000 olmalıdır.</td>
              <td style="padding:12px 14px; text-align:center; color:#38bdf8;">+60 Sn</td>
              <td style="padding:12px 14px; text-align:center; color:#fbbf24;">%10 Bloke</td>
            </tr>
            <tr style="border-bottom:1px solid #1e293b;">
              <td style="padding:12px 14px; color:#fff; font-weight:600;">₺100.001 — ₺500.000</td>
              <td style="padding:12px 14px; color:#34d399; font-weight:700;">+₺10.000</td>
              <td style="padding:12px 14px; color:#94a3b8;">₺340.000 pey üzerine bir sonraki teklif en az ₺350.000 olmalıdır.</td>
              <td style="padding:12px 14px; text-align:center; color:#38bdf8;">+60 Sn</td>
              <td style="padding:12px 14px; text-align:center; color:#fbbf24;">%15 Bloke</td>
            </tr>
            <tr>
              <td style="padding:12px 14px; color:#fff; font-weight:600;">₺500.001 ve Üzeri (VIP)</td>
              <td style="padding:12px 14px; color:#eab308; font-weight:700;">+₺25.000</td>
              <td style="padding:12px 14px; color:#94a3b8;">₺1.200.000 üzerine sonraki teklif en az ₺1.225.000 olmalıdır.</td>
              <td style="padding:12px 14px; text-align:center; color:#38bdf8;">+90 Sn (VIP)</td>
              <td style="padding:12px 14px; text-align:center; color:#fbbf24;">%20 Bloke</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>

  <!-- =========================================================================
       2. SEKME: MÜZAYEDE REHBERİ (AUCTION GUIDE)
       ========================================================================= -->
  <div id="helpTab_rehber" class="help-tab-content" style="display:<?= $activeHelpTab === 'rehber' ? 'block' : 'none' ?>;">
    <div style="margin-bottom:24px;">
      <h2 style="font-size:1.4rem; color:#fff; font-weight:600; margin-bottom:6px;">6 Adımda Seçkin Müzayede Deneyimi</h2>
      <p style="color:#94a3b8; font-size:0.88rem; margin:0;">
        Eser keşfinden pey vermeye, emanet ödemeden özel sigortalı teslimata kadar kusursuz ihale yol haritası.
      </p>
    </div>

    <!-- 6 Aşamalı İnteraktif Yol Haritası -->
    <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(320px, 1fr)); gap:18px; margin-bottom:30px;">
      <?php foreach ($auctionGuide as $step): ?>
        <div class="panel-card help-guide-item" style="background:#11151f; border:1px solid #243044; border-radius:12px; padding:22px; position:relative;">
          <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:12px;">
            <div style="display:flex; align-items:center; gap:8px;">
              <span style="display:inline-flex; align-items:center; justify-content:center; width:28px; height:28px; background:var(--gold-primary); color:#0c0f14; border-radius:50%; font-weight:700; font-size:0.85rem;">
                <?= $step['adim'] ?>
              </span>
              <span style="font-size:1.4rem;"><?= $step['ikon'] ?></span>
            </div>
            <span style="font-size:0.72rem; color:#d4af37; text-transform:uppercase; font-weight:700;">ADIM <?= $step['adim'] ?></span>
          </div>
          <h3 style="font-size:1.05rem; color:#fff; font-weight:600; margin:0 0 8px 0;"><?= htmlspecialchars($step['baslik']) ?></h3>
          <p style="font-size:0.82rem; color:#94a3b8; line-height:1.6; margin:0;">
            <?= htmlspecialchars($step['aciklama']) ?>
          </p>
        </div>
      <?php endforeach; ?>
    </div>

    <!-- Pey Verme Yöntemleri Karşılaştırma Paneli -->
    <div class="panel-card" style="background:#11151f; border:1px solid #243044; border-radius:12px; padding:24px;">
      <h3 style="font-size:1.15rem; color:#fff; margin-bottom:4px;">⚡ Hangi Teklif Türünü Kullanmalısınız?</h3>
      <p style="font-size:0.82rem; color:#94a3b8; margin-bottom:18px;">Stratejinize ve müzayede ritmine göre en uygun pey verme mekanizmasını seçin.</p>

      <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(240px, 1fr)); gap:16px;">
        <div style="background:#0b0e14; border:1px solid #1e293b; border-radius:8px; padding:16px;">
          <div style="font-size:1.3rem; margin-bottom:6px;">🖱️</div>
          <div style="font-size:0.92rem; color:#fff; font-weight:600; margin-bottom:4px;">1. Manuel Canlı Pey</div>
          <div style="font-size:0.78rem; color:#94a3b8; line-height:1.5;">
            Teklif ekranındaki "Pey Ver" butonuna basarak mevcut teklifin tam bir asgari artış adımı üzerine çıkarsınız. Canlı yayını takip ederken anlık kontrol sağlar.
          </div>
        </div>

        <div style="background:#0b0e14; border:1px solid #1e293b; border-radius:8px; padding:16px;">
          <div style="font-size:1.3rem; margin-bottom:6px;">🚀</div>
          <div style="font-size:0.92rem; color:#fff; font-weight:600; margin-bottom:4px;">2. Hızlı Artış Butonları</div>
          <div style="font-size:0.78rem; color:#94a3b8; line-height:1.5;">
            +1.500₺, +5.000₺ veya +10.000₺ butonlarına basarak rakiplerinizi psikolojik olarak saf dışı bırakacak süratli ve iddialı pey sürebilirsiniz.
          </div>
        </div>

        <div style="background:#0b0e14; border:1px solid #1e293b; border-radius:8px; padding:16px;">
          <div style="font-size:1.3rem; margin-bottom:6px;">🤖</div>
          <div style="font-size:0.92rem; color:#fff; font-weight:600; margin-bottom:4px;">3. Otomatik Pey (Proxy Bid)</div>
          <div style="font-size:0.78rem; color:#94a3b8; line-height:1.5;">
            Tavan bütçenizi girin. Sistem sizin adınıza yalnızca rakip pey geldiğinde bir asgari artış kadar yükselir. İnternetiniz kopsa dahi liderliği korur.
          </div>
        </div>

        <div style="background:#0b0e14; border:1px solid #1e293b; border-radius:8px; padding:16px;">
          <div style="font-size:1.3rem; margin-bottom:6px;">📞</div>
          <div style="font-size:0.92rem; color:#fff; font-weight:600; margin-bottom:4px;">4. VIP Telefonla Temsilci</div>
          <div style="font-size:0.78rem; color:#94a3b8; line-height:1.5;">
            Müzayede saatinde özel müşteri yöneticiniz sizi telefonla arar; salondan canlı aktarımla komutunuz üzerine fiziki ve dijital çekiçte pey sürer.
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- =========================================================================
       3. SEKME: SIKÇA SORULAN SORULAR (SSS / FAQ)
       ========================================================================= -->
  <div id="helpTab_sss" class="help-tab-content" style="display:<?= $activeHelpTab === 'sss' ? 'block' : 'none' ?>;">
    <div style="display:flex; justify-content:space-between; align-items:flex-end; flex-wrap:wrap; gap:16px; margin-bottom:20px;">
      <div>
        <h2 style="font-size:1.4rem; color:#fff; font-weight:600; margin-bottom:6px;">Sıkça Sorulan Sorular (SSS)</h2>
        <p style="color:#94a3b8; font-size:0.88rem; margin:0;">
          Müzayedeler, ödemeler, teminat iadeleri, ekspertiz ve kargo süreçleri hakkında en çok merak edilenler.
        </p>
      </div>

      <!-- Kategori Filtre Butonları -->
      <div style="display:flex; gap:6px; flex-wrap:wrap;">
        <button type="button" class="faq-filter-btn active btn-gold-pill" onclick="filterFaqCategory('ALL', this)" style="padding:6px 14px; font-size:0.78rem; cursor:pointer;">Tümü (<?= count($faqList) ?>)</button>
        <button type="button" class="faq-filter-btn btn-dark-pill" onclick="filterFaqCategory('PEY_MUZAYEDE', this)" style="padding:6px 14px; font-size:0.78rem; cursor:pointer;">Teklif & Müzayede</button>
        <button type="button" class="faq-filter-btn btn-dark-pill" onclick="filterFaqCategory('ODEME_EMANET', this)" style="padding:6px 14px; font-size:0.78rem; cursor:pointer;">Ödeme & Emanet</button>
        <button type="button" class="faq-filter-btn btn-dark-pill" onclick="filterFaqCategory('EKSPERTIZ_ORIJINALLIK', this)" style="padding:6px 14px; font-size:0.78rem; cursor:pointer;">Ekspertiz & Belge</button>
        <button type="button" class="faq-filter-btn btn-dark-pill" onclick="filterFaqCategory('KARGO_TESLIMAT', this)" style="padding:6px 14px; font-size:0.78rem; cursor:pointer;">Kargo & Teslimat</button>
        <button type="button" class="faq-filter-btn btn-dark-pill" onclick="filterFaqCategory('IPTAL_IHTILAF', this)" style="padding:6px 14px; font-size:0.78rem; cursor:pointer;">İptal & İhtilaf</button>
      </div>
    </div>

    <!-- SSS Akordeon Listesi -->
    <div id="faqAccordionContainer" style="display:flex; flex-direction:column; gap:12px;">
      <?php foreach ($faqList as $idx => $faq): ?>
        <div class="faq-card-item" data-cat="<?= htmlspecialchars($faq['kategori']) ?>" style="background:#11151f; border:1px solid #243044; border-radius:10px; overflow:hidden; transition:border-color 0.2s;">
          <button type="button" onclick="toggleFaqAccordion(<?= $idx ?>)" style="width:100%; text-align:left; background:none; border:none; padding:18px 20px; display:flex; justify-content:space-between; align-items:center; cursor:pointer; gap:12px;">
            <div style="display:flex; align-items:center; gap:10px;">
              <span class="badge badge-gold" style="font-size:0.68rem; padding:2px 8px;"><?= htmlspecialchars($faq['kategoriAd']) ?></span>
              <span class="faq-question-text" style="color:#fff; font-size:0.95rem; font-weight:600;"><?= htmlspecialchars($faq['soru']) ?></span>
            </div>
            <span id="faqIcon_<?= $idx ?>" style="color:var(--gold-primary); font-size:1.1rem; transition:transform 0.2s;">+</span>
          </button>
          <div id="faqBody_<?= $idx ?>" class="faq-body" style="display:none; padding:0 20px 18px 20px; color:#cbd5e1; font-size:0.86rem; line-height:1.6; border-top:1px solid rgba(255,255,255,0.04);">
            <div style="background:#0b0e14; border:1px solid #1e293b; border-radius:8px; padding:14px; margin-top:10px;">
              <?= htmlspecialchars($faq['cevap']) ?>
            </div>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  </div>

  <!-- =========================================================================
       4. SEKME: CANLI VE BİLET TABANLI DESTEK KANALI (LIVE & TICKET SUPPORT)
       ========================================================================= -->
  <div id="helpTab_destek" class="help-tab-content" style="display:<?= $activeHelpTab === 'destek' ? 'block' : 'none' ?>;">
    
    <div style="display:grid; grid-template-columns:1.35fr 1fr; gap:24px; align-items:flex-start;">
      
      <!-- SOL BÖLÜM: BİLET TABANLI DESTEK MASASI -->
      <div>
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:16px;">
          <div>
            <h2 style="font-size:1.25rem; color:#fff; font-weight:600; margin:0 0 4px 0;">🎫 Bilet Tabanlı Destek Masası</h2>
            <p style="font-size:0.8rem; color:#94a3b8; margin:0;">Teknik, finansal ve ekspertiz konularında kayıtlı destek talepleriniz</p>
          </div>
          <button type="button" onclick="toggleNewTicketForm()" class="btn-gold-pill" style="padding:8px 18px; font-size:0.82rem; cursor:pointer;">
            + Yeni Destek Talebi Aç
          </button>
        </div>

        <!-- Yeni Bilet Açma Formu (Varsayılan Açılır/Kapanır) -->
        <div id="newTicketFormContainer" class="panel-card" style="display:none; background:#11151f; border:1px solid var(--gold-primary); border-radius:12px; padding:22px; margin-bottom:20px; box-shadow:0 8px 24px rgba(0,0,0,0.5);">
          <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:14px;">
            <div style="color:#d4af37; font-weight:700; font-size:0.92rem;">Yeni Destek Bileti Oluştur</div>
            <button type="button" onclick="toggleNewTicketForm()" style="background:none; border:none; color:#888; font-size:1.2rem; cursor:pointer;">&times;</button>
          </div>

          <form method="POST">
            <input type="hidden" name="action" value="create_support_ticket">
            <input type="hidden" name="user_id" value="<?= $buyerId ?>">
            <input type="hidden" name="user_name" value="Emre K. (Alıcı)">
            <input type="hidden" name="user_email" value="alici@martello.com">

            <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px; margin-bottom:12px;">
              <div>
                <label class="form-label" style="font-size:0.8rem; color:#f0f3f6;">Talep Kategorisi *</label>
                <select name="category" class="form-control" style="background:#0b0e14; border:1px solid #2a3449; color:#fff; border-radius:8px;" required>
                  <option value="Sistem Kuralları & Pey">Sistem Kuralları & Pey</option>
                  <option value="Ödeme & Emanet Bloke">Ödeme & Emanet Bloke</option>
                  <option value="Ekspertiz & Orijinallik">Ekspertiz & Orijinallik</option>
                  <option value="Kargo & Sigortalı Teslimat">Kargo & Sigortalı Teslimat</option>
                  <option value="İptal & İhtilaf">İptal & İhtilaf</option>
                  <option value="Genel Bilgi">Genel Bilgi</option>
                </select>
              </div>

              <div>
                <label class="form-label" style="font-size:0.8rem; color:#f0f3f6;">Öncelik Derecesi *</label>
                <select name="priority" class="form-control" style="background:#0b0e14; border:1px solid #2a3449; color:#fff; border-radius:8px;" required>
                  <option value="NORMAL">Normal Öncelik</option>
                  <option value="YUKSEK">Yüksek Öncelik</option>
                  <option value="ACIL">Acil Durum (Pey/Ödeme)</option>
                </select>
              </div>
            </div>

            <div style="display:grid; grid-template-columns:2fr 1fr; gap:12px; margin-bottom:12px;">
              <div>
                <label class="form-label" style="font-size:0.8rem; color:#f0f3f6;">Konu Başlığı *</label>
                <input type="text" name="subject" class="form-control" placeholder="Örn: Pey teminatımın iade durumu..." style="background:#0b0e14; border:1px solid #2a3449; color:#fff; border-radius:8px;" required>
              </div>
              <div>
                <label class="form-label" style="font-size:0.8rem; color:#f0f3f6;">İlgili Lot No (Opsiyonel)</label>
                <input type="number" name="lot_id" class="form-control" placeholder="Örn: 1002" style="background:#0b0e14; border:1px solid #2a3449; color:#fff; border-radius:8px;">
              </div>
            </div>

            <div style="margin-bottom:16px;">
              <label class="form-label" style="font-size:0.8rem; color:#f0f3f6;">Detaylı Açıklama *</label>
              <textarea name="message" class="form-control" rows="4" placeholder="Lütfen sorununuzu veya talebinizi detaylı olarak açıklayınız..." style="background:#0b0e14; border:1px solid #2a3449; color:#fff; border-radius:8px;" required></textarea>
            </div>

            <div style="display:flex; justify-content:flex-end; gap:10px;">
              <button type="button" onclick="toggleNewTicketForm()" class="btn-dark-pill" style="padding:8px 18px; font-size:0.82rem; cursor:pointer;">Vazgeç</button>
              <button type="submit" class="btn-gold-pill" style="padding:8px 22px; font-size:0.85rem; cursor:pointer;">Talebi Gönder &rarr;</button>
            </div>
          </form>
        </div>

        <!-- Kullanıcının Mevcut Biletleri -->
        <div class="panel-card" style="background:#11151f; border:1px solid #243044; border-radius:12px; padding:20px;">
          <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:14px;">
            <div style="font-size:0.88rem; color:#fff; font-weight:600;">Destek Biletlerim (<?= count($userTickets) ?>)</div>
            <div style="font-size:0.75rem; color:#94a3b8;">Emre K. (#3) Hesabı</div>
          </div>

          <?php if (empty($userTickets)): ?>
            <div style="text-align:center; padding:30px; color:#64748b; font-size:0.85rem;">
              Henüz oluşturulmuş bir destek talebiniz bulunmuyor.
            </div>
          <?php else: ?>
            <div style="display:flex; flex-direction:column; gap:12px;">
              <?php foreach ($userTickets as $t): ?>
                <?php
                  $statusColor = '#38bdf8';
                  $statusLabel = 'AÇIK';
                  $s = strtoupper((string)$t['durum']);
                  if ($s === 'YANITLANDI') {
                      $statusColor = '#34d399';
                      $statusLabel = 'YANITLANDI';
                  } elseif ($s === 'ISLENIYOR') {
                      $statusColor = '#fbbf24';
                      $statusLabel = 'İŞLENİYOR';
                  } elseif ($s === 'COZULDU') {
                      $statusColor = '#94a3b8';
                      $statusLabel = 'ÇÖZÜLDÜ';
                  }
                ?>
                <div style="background:#0b0e14; border:1px solid #1e293b; border-radius:8px; padding:16px;">
                  <div style="display:flex; justify-content:space-between; align-items:flex-start; flex-wrap:wrap; gap:8px; margin-bottom:8px;">
                    <div>
                      <span style="font-family:monospace; color:#d4af37; font-weight:700; font-size:0.82rem;"><?= htmlspecialchars($t['biletNo']) ?></span>
                      <span style="font-size:0.75rem; color:#64748b; margin-left:8px;"><?= htmlspecialchars(substr($t['olusturmaTarihi'] ?? '', 0, 16)) ?></span>
                      <?php if (!empty($t['lotId'])): ?>
                        <span style="font-size:0.72rem; color:#cbd5e1; background:#1e293b; padding:1px 6px; border-radius:4px; margin-left:6px;">Lot #<?= (int)$t['lotId'] ?></span>
                      <?php endif; ?>
                    </div>
                    <div style="display:flex; align-items:center; gap:6px;">
                      <span class="badge" style="background:rgba(212,175,55,0.12); color:#d4af37; border:1px solid rgba(212,175,55,0.3); font-size:0.68rem; padding:2px 6px;">
                        <?= htmlspecialchars($t['kategori']) ?>
                      </span>
                      <span class="badge" style="background:rgba(0,0,0,0.4); color:<?= $statusColor ?>; border:1px solid <?= $statusColor ?>; font-size:0.68rem; padding:2px 6px;">
                        ● <?= $statusLabel ?>
                      </span>
                    </div>
                  </div>

                  <div style="font-size:0.92rem; color:#fff; font-weight:600; margin-bottom:6px;">
                    <?= htmlspecialchars($t['konu']) ?>
                  </div>
                  <div style="font-size:0.82rem; color:#94a3b8; line-height:1.5; margin-bottom:12px;">
                    <?= htmlspecialchars($t['mesaj']) ?>
                  </div>

                  <!-- Bilet Yanıtları Listesi -->
                  <?php if (!empty($t['cevaplar'])): ?>
                    <div style="border-top:1px solid #1e293b; padding-top:10px; margin-top:10px; display:flex; flex-direction:column; gap:8px;">
                      <div style="font-size:0.72rem; color:#d4af37; font-weight:700; text-transform:uppercase;">Görüşme Geçmişi (<?= count($t['cevaplar']) ?>)</div>
                      <?php foreach ($t['cevaplar'] as $ans): ?>
                        <?php $isStaff = in_array(strtoupper($ans['yazanRol'] ?? ''), ['DESTEK_UZMANI', 'ADMIN', 'CANLI DESTEK'], true); ?>
                        <div style="background:<?= $isStaff ? '#131924' : '#0e1219' ?>; border-left:3px solid <?= $isStaff ? '#d4af37' : '#38bdf8' ?>; border-radius:4px; padding:10px 12px;">
                          <div style="display:flex; justify-content:space-between; font-size:0.72rem; color:#848d9a; margin-bottom:4px;">
                            <strong style="color:<?= $isStaff ? '#d4af37' : '#38bdf8' ?>;"><?= htmlspecialchars($ans['yazanAd'] ?? '') ?> (<?= htmlspecialchars($ans['yazanRol'] ?? '') ?>)</strong>
                            <span><?= htmlspecialchars($ans['tarih'] ?? '') ?></span>
                          </div>
                          <div style="font-size:0.8rem; color:#e2e8f0; line-height:1.4;">
                            <?= htmlspecialchars($ans['mesaj'] ?? '') ?>
                          </div>
                        </div>
                      <?php endforeach; ?>
                    </div>
                  <?php endif; ?>

                  <!-- Yanıt Yazma Formu -->
                  <?php if ($s !== 'COZULDU'): ?>
                    <form method="POST" style="margin-top:12px; display:flex; gap:8px;">
                      <input type="hidden" name="action" value="reply_support_ticket">
                      <input type="hidden" name="ticket_id" value="<?= (int)$t['id'] ?>">
                      <input type="hidden" name="user_id" value="<?= $buyerId ?>">
                      <input type="hidden" name="user_name" value="Emre K.">
                      <input type="hidden" name="user_role" value="ALICI">
                      <input type="text" name="reply_text" class="form-control" placeholder="Bu bilete ek yanıt yazınız..." style="background:#161c28; border:1px solid #2a3449; font-size:0.8rem; color:#fff;" required>
                      <button type="submit" class="btn-outline-gold btn-sm" style="white-space:nowrap; padding:6px 14px; font-size:0.78rem;">Yanıtla</button>
                    </form>
                  <?php else: ?>
                    <div style="font-size:0.72rem; color:#64748b; margin-top:8px;">
                      ✓ Bu bilet çözümlendi olarak kapatılmıştır.
                    </div>
                  <?php endif; ?>
                </div>
              <?php endforeach; ?>
            </div>
          <?php endif; ?>
        </div>
      </div>

      <!-- SAĞ BÖLÜM: CANLI DESTEK SOHBET KANALI -->
      <div>
        <div class="panel-card" style="background:#11151f; border:1px solid #243044; border-radius:12px; padding:20px; display:flex; flex-direction:column; height:680px;">
          <!-- Canlı Destek Başlığı -->
          <div style="display:flex; justify-content:space-between; align-items:center; padding-bottom:14px; border-bottom:1px solid #1e293b; margin-bottom:14px;">
            <div style="display:flex; align-items:center; gap:10px;">
              <div style="position:relative;">
                <img src="public/assets/images/avatar.jpg" alt="Support" style="width:38px; height:38px; border-radius:50%; object-fit:cover; border:2px solid #10b981;">
                <span style="position:absolute; bottom:0; right:0; width:10px; height:10px; background:#10b981; border:2px solid #11151f; border-radius:50%;"></span>
              </div>
              <div>
                <div style="font-size:0.92rem; color:#fff; font-weight:600;">Elif Çelik</div>
                <div style="font-size:0.72rem; color:#10b981; font-weight:600;">● Canlı Destek Temsilcisi</div>
              </div>
            </div>
            <span class="badge badge-gold" style="font-size:0.68rem; padding:3px 8px;">VIP DANIŞMANLIK</span>
          </div>

          <!-- Canlı Sohbet Mesaj Akışı -->
          <div style="flex:1; overflow-y:auto; padding:12px; background:#0b0e14; border-radius:8px; border:1px solid #1e293b; display:flex; flex-direction:column; gap:10px; margin-bottom:12px;">
            <div style="text-align:center; margin:4px 0 10px 0;">
              <span style="font-size:0.68rem; color:#64748b; background:#161c28; padding:3px 10px; border-radius:10px;">
                Güvenli 256-bit SSL Canlı Destek Kanalı Aktif
              </span>
            </div>

            <?php foreach ($activeSession['mesajlar'] ?? [] as $msg): ?>
              <?php $isStaff = ((int)($msg['gonderenId'] ?? 0) === 5); ?>
              <div style="align-self:<?= $isStaff ? 'flex-start' : 'flex-end' ?>; max-width:85%;">
                <div style="font-size:0.7rem; color:#64748b; margin-bottom:2px; text-align:<?= $isStaff ? 'left' : 'right' ?>;">
                  <?= htmlspecialchars($msg['gonderenAd'] ?? '') ?> • <?= htmlspecialchars(substr($msg['zaman'] ?? '', 11, 5)) ?>
                </div>
                <div style="padding:10px 14px; border-radius:12px; font-size:0.85rem; line-height:1.45; background:<?= $isStaff ? '#1e293b' : 'linear-gradient(135deg, #c89e58 0%, #a47937 100%)' ?>; color:<?= $isStaff ? '#f1f5f9' : '#0c0f14' ?>; font-weight:<?= $isStaff ? '400' : '600' ?>; border:1px solid <?= $isStaff ? '#334155' : '#d4af37' ?>;">
                  <?= htmlspecialchars($msg['icerik'] ?? '') ?>
                </div>
              </div>
            <?php endforeach; ?>
          </div>

          <!-- Hızlı Soru Şablonları -->
          <div style="display:flex; gap:6px; flex-wrap:wrap; margin-bottom:10px;">
            <button type="button" class="btn-dark-pill" style="font-size:0.7rem; padding:3px 8px; cursor:pointer;" onclick="fillChatMessage('Lot #1002 Patek Philippe için ekspertiz raporu detaylarını öğrenebilir miyim?')">Ekspertiz Detayı</button>
            <button type="button" class="btn-dark-pill" style="font-size:0.7rem; padding:3px 8px; cursor:pointer;" onclick="fillChatMessage('Verdiğim pey geçildiğinde teminat blokesi ne kadar sürede açılır?')">Teminat Blokesi</button>
            <button type="button" class="btn-dark-pill" style="font-size:0.7rem; padding:3px 8px; cursor:pointer;" onclick="fillChatMessage('Özel sanat kuryesi teslimat randevusu nasıl planlanır?')">Kargo Randevusu</button>
          </div>

          <!-- Canlı Sohbet Formu -->
          <form method="POST" style="display:flex; gap:8px;">
            <input type="hidden" name="action" value="send_chat">
            <input type="hidden" name="session_id" value="<?= $activeSession['id'] ?? 951 ?>">
            <input type="hidden" name="sender_id" value="<?= $buyerId ?>">
            <input type="hidden" name="sender_name" value="Emre K. (Alıcı)">
            <input type="text" id="liveChatInput" name="message" class="form-control" placeholder="Canlı destek uzmanına yazın..." required style="background:#0b0e14; border:1px solid #2a3449; color:#fff; font-size:0.85rem; border-radius:8px;">
            <button type="submit" class="btn-gold" style="white-space:nowrap; padding:10px 18px; font-size:0.85rem; border-radius:8px;">
              Gönder &rarr;
            </button>
          </form>
        </div>
      </div>

    </div>
  </div>

</div>

<script>
function switchHelpTab(tabId, btn) {
  document.querySelectorAll('.help-tab-content').forEach(el => el.style.display = 'none');
  document.querySelectorAll('.help-tab-btn').forEach(b => {
    b.classList.remove('active');
    b.style.borderBottomColor = 'transparent';
    b.style.color = '#94a3b8';
  });

  const target = document.getElementById('helpTab_' + tabId);
  if (target) {
    target.style.display = 'block';
  }
  if (btn) {
    btn.classList.add('active');
    btn.style.borderBottomColor = 'var(--gold-primary)';
    btn.style.color = '#fff';
  }

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
