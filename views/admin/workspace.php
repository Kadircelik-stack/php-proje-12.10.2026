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

    <!-- 3. TAB: İLAN MODERASYONU -->
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

    <!-- 4. TAB: EKSPERTİZ ATAMA & AKREDİTASYON -->
    <div id="admTab_ekspertiz" class="tab-content <?= $activeTab === 'ekspertiz' ? 'active' : '' ?>">
      <div class="page-breadcrumb">ADMIN / ÇALIŞMA ALANI</div>
      <h1 class="page-heading">Ekspertiz ve Kurum Atama</h1>
      <p class="page-subheading">Yeni ürünlere alanında uzman akredite eksper veya bağımsız kurum atayın</p>

      <div class="panel-card" style="margin-bottom:20px;">
        <h3 class="panel-title" style="margin-bottom:12px;">Yeni Görev / Eksper Atama Formu</h3>
        <form method="POST" style="display:grid; grid-template-columns:1fr 1fr auto; gap:14px; align-items:flex-end;">
          <input type="hidden" name="action" value="assign_expert">
          <input type="hidden" name="request_id" value="301">
          <div>
            <label class="form-label">İncelenecek Eser / Talep</label>
            <input type="text" class="form-control" value="EXP-REQ-772 • 1968 İsviçre Vintage Saat (AUC-1642)" readonly>
          </div>
          <div>
            <label class="form-label">Atanacak Akredite Uzman / Kurum</label>
            <select name="expert_id" class="form-control">
              <option value="4" selected>Burak Şahin (Marmara Ekspertiz A.Ş. — Sicil: OD-4491)</option>
              <option value="4">Dr. Haluk Aydın (Osmanlı & İslam Sanatları Uzmanı)</option>
            </select>
          </div>
          <div>
            <button type="submit" class="btn-register-glow">Görevi Ata</button>
          </div>
        </form>
      </div>

      <div class="panel-card">
        <h3 class="panel-title" style="margin-bottom:12px;">Eksper Akreditasyon Onay Paneli</h3>
        <p style="font-size:0.85rem; color:var(--text-muted); margin-bottom:14px;">
          Ekspertiz yapacak uzmanların lisans ve yeterlilik belgeleri admin heyeti tarafından onaylanır.
        </p>
        <form method="POST" style="display:inline-block;">
          <input type="hidden" name="action" value="accredit_expert">
          <input type="hidden" name="expert_id" value="4">
          <input type="hidden" name="decision" value="approve">
          <button type="submit" class="btn-gold btn-sm">✓ Burak Şahin Akreditasyonunu Yenile / Onayla</button>
        </form>
      </div>
    </div>

    <!-- 5. TAB: KATEGORİ ÖZELLİK YÖNETİMİ -->
    <div id="admTab_kategoriler" class="tab-content <?= $activeTab === 'kategoriler' ? 'active' : '' ?>">
      <div class="page-breadcrumb">ADMIN / ÇALIŞMA ALANI</div>
      <h1 class="page-heading">Kategori Özellik Yönetimi</h1>
      <p class="page-subheading">Ürün kategorileri, komisyon ve dinamik zorunlu nitelik şablonları tanımlama</p>

      <div class="panel-card" style="margin-bottom:20px;">
        <h3 class="panel-title" style="margin-bottom:12px;">Yeni Kategori & Özellik Şablonu Tanımla</h3>
        <form method="POST" style="display:grid; grid-template-columns:1.5fr 1fr 2fr auto; gap:12px; align-items:flex-end;">
          <input type="hidden" name="action" value="add_category">
          <div>
            <label class="form-label">Kategori Adı</label>
            <input type="text" name="kategori_adi" class="form-control" placeholder="Örn: Klasik Otomobil" required>
          </div>
          <div>
            <label class="form-label">Kategori Kodu</label>
            <input type="text" name="kategori_kodu" class="form-control" placeholder="Örn: VEHICLE" required>
          </div>
          <div>
            <label class="form-label">Zorunlu Nitelikler (Virgülle Ayırın)</label>
            <input type="text" name="nitelikler" class="form-control" placeholder="Model Yılı, Motor No, Şasi No, Kilometre" required>
          </div>
          <div>
            <button type="submit" class="btn-register-glow">Kategori Ekle</button>
          </div>
        </form>
      </div>

      <div class="panel-card">
        <table class="data-table">
          <thead>
            <tr><th>Kategori Adı</th><th>Kod</th><th>Komisyon</th><th>Ekspertiz Ücreti</th><th>Zorunlu Nitelikler</th></tr>
          </thead>
          <tbody>
            <?php foreach ($categories as $c): ?>
              <tr>
                <td style="font-weight:600; color:#fff;"><?= htmlspecialchars($c['ad'] ?? $c['kategoriAdi'] ?? 'Kategori') ?></td>
                <td><code><?= htmlspecialchars($c['kod'] ?? $c['kategoriKodu'] ?? 'CAT') ?></code></td>
                <td><span class="badge badge-gold">%<?= $c['komisyonOraniYuzde'] ?? 8 ?></span></td>
                <td><?= $formatPrice((float)($c['sabitEkspertizUcreti'] ?? 1500)) ?></td>
                <td style="font-size:0.8rem; color:var(--text-muted);"><?= is_array($c['zorunluNitelikler'] ?? null) ? implode(', ', $c['zorunluNitelikler']) : 'Dönem, Menşei, Kondisyon' ?></td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
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

    <!-- 7. TAB: RAPORLAR -->
    <div id="admTab_raporlar" class="tab-content <?= $activeTab === 'raporlar' ? 'active' : '' ?>">
      <div class="page-breadcrumb">ADMIN / ÇALIŞMA ALANI</div>
      <h1 class="page-heading">Ekspertiz ve Müzayede Raporları</h1>
      <p class="page-subheading">Onaylı değerlendirmeler ve 1 yıldan eski verilerin arşivleme politikası (Cold Storage)</p>

      <div class="panel-card">
        <table class="data-table">
          <thead><tr><th>Rapor No</th><th>Lot Kodu</th><th>Piyasa Değeri</th><th>Durum</th><th>Arşiv Durumu</th></tr></thead>
          <tbody>
            <?php foreach ($reports as $r): ?>
              <tr>
                <td><code><?= $r['raporNo'] ?></code></td>
                <td>AUC-1001</td>
                <td style="color:var(--gold-bright); font-weight:700;"><?= $formatPrice((float)$r['onerilenPiyasaDegeri']) ?></td>
                <td><span class="badge badge-success">✓ Onaylandı</span></td>
                <td><span class="badge badge-gold">Hot Storage (&lt;1 Yıl)</span></td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>

    <!-- 8. TAB: İHTİLAF ÇÖZÜMÜ -->
    <div id="admTab_ihtilaflar" class="tab-content <?= $activeTab === 'ihtilaflar' ? 'active' : '' ?>">
      <div class="page-breadcrumb">ADMIN / ÇALIŞMA ALANI</div>
      <h1 class="page-heading">İhtilaf ve Tahkim Çözümü</h1>
      <p class="page-subheading">Alıcı ve satıcı uyuşmazlıklarında admin tahkim kararı ve bloke iade süreçleri</p>

      <div class="panel-card">
        <table class="data-table">
          <thead>
            <tr><th>Talep No</th><th>Lot Kodu</th><th>Konu</th><th>Açıklama</th><th>Tahkim Kararı</th></tr>
          </thead>
          <tbody>
            <tr>
              <td><code>DISP-2026-01</code></td>
              <td><span class="badge badge-gold">AUC-1001</span></td>
              <td>Kondisyon Uyuşmazlığı</td>
              <td>Teslim edilen eserin çerçevesinde mikro çizik tespit edildi.</td>
              <td>
                <div style="display:flex; gap:8px;">
                  <form method="POST" style="margin:0;">
                    <input type="hidden" name="action" value="resolve_dispute">
                    <input type="hidden" name="dispute_id" value="1">
                    <input type="hidden" name="decision" value="ALICI_HAKLI">
                    <button type="submit" class="btn-gold btn-sm" style="font-size:0.75rem;">Alıcı Haklı (İade Et)</button>
                  </form>
                  <form method="POST" style="margin:0;">
                    <input type="hidden" name="action" value="resolve_dispute">
                    <input type="hidden" name="dispute_id" value="1">
                    <input type="hidden" name="decision" value="SATICI_HAKLI">
                    <button type="submit" class="btn-outline-gold btn-sm" style="font-size:0.75rem;">Satıcı Haklı (Bloke Çöz)</button>
                  </form>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- 9. TAB: WORM DENETİM İZİ & KURAL DIŞI PEY İPTALİ -->
    <div id="admTab_denetim" class="tab-content <?= $activeTab === 'denetim' ? 'active' : '' ?>">
      <div class="page-breadcrumb">ADMIN / ÇALIŞMA ALANI</div>
      <h1 class="page-heading">Değiştirilemez Denetim İzi (WORM) & Teklif İptali</h1>
      <p class="page-subheading">SHA-256 Kriptografik Hash Zincirleme ve kural dışı teklifleri iptal etme</p>

      <div class="panel-card" style="margin-bottom:16px;">
        <div style="display:flex; justify-content:space-between; align-items:center;">
          <div>
            <div style="color:var(--gold-primary); font-weight:700;">Kural Dışı / Manipülatif Teklif İptal Paneli</div>
            <div style="font-size:0.8rem; color:var(--text-muted);">İptal edilen teklif listeden düşer, önceki lider geçerli kılınır ve bloke iade edilir.</div>
          </div>
          <form method="POST" style="display:flex; gap:8px; margin:0;">
            <input type="hidden" name="action" value="cancel_bid">
            <input type="hidden" name="lot_id" value="1002">
            <input type="hidden" name="reason" value="Shill Bidding veya kural dışı pey artışı tespiti">
            <button type="submit" class="btn-outline-gold btn-sm" style="color:#f87171 !important; border-color:#f87171;">⚠️ Son Teklifi İptal Et</button>
          </form>
        </div>
      </div>

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
      <h1 class="page-heading">Sistem Parametreleri ve NFR Uyumluluğu</h1>
      <p class="page-subheading">Anti-Sniping, Eşzamanlılık (flock), Katılımcı Kapasitesi ve Bot Koruması</p>

      <div class="panel-card">
        <div style="display:grid; grid-template-columns:1fr 1fr; gap:20px;">
          <div>
            <div style="font-weight:600; color:#fff; margin-bottom:8px;">⏱️ Anti-Sniping Kuralı</div>
            <p style="font-size:0.82rem; color:var(--text-muted);">Eşik: <strong>Son 1 dakika (60 sn)</strong> • Kural: <strong>Son 1 dk içinde pey gelirse süre +1 dk uzar. 1 dk'dan önce pey başına süre uzamaz.</strong></p>
          </div>
          <div>
            <div style="font-weight:600; color:#fff; margin-bottom:8px;">👥 Katılımcı Kapasitesi</div>
            <p style="font-size:0.82rem; color:var(--text-muted);">Maksimum Sınır: <strong>50.000 eşzamanlı kullanıcı</strong> • Kota Aşımı: <strong>Otomatik bekleme odası yönlendirmesi</strong></p>
          </div>
          <div>
            <div style="font-weight:600; color:#fff; margin-bottom:8px;">🔒 Eşzamanlılık & ACID</div>
            <p style="font-size:0.82rem; color:var(--text-muted);">Kilitleme: <strong>Pessimistic Locking (flock EXCLUSIVE)</strong> • Bütünlük: <strong>Atomik tmp rename ve transaction rollback</strong></p>
          </div>
          <div>
            <div style="font-weight:600; color:#fff; margin-bottom:8px;">🛡️ Sahtecilik & Bot Koruması</div>
            <p style="font-size:0.82rem; color:var(--text-muted);">Shill Bidding: <strong>Satıcının kendi ilanına teklif vermesi %100 engellenir.</strong></p>
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
