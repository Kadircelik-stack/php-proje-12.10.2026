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
          <button type="button" data-tab="konsol" onclick="switchAdminTab('konsol', this)">
            Yönetim konsolu
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
        <li class="nav-menu-item <?= $activeTab === 'moderasyon' ? 'active' : '' ?>">
          <button type="button" data-tab="moderasyon" onclick="switchAdminTab('moderasyon', this)">
            Müzayede & Lot Düzenleme
          </button>
        </li>
        <li class="nav-menu-item <?= $activeTab === 'ekspertiz' ? 'active' : '' ?>">
          <button type="button" data-tab="ekspertiz" onclick="switchAdminTab('ekspertiz', this)">
            Ekspertiz atama
          </button>
        </li>
        <li class="nav-menu-item <?= $activeTab === 'kategoriler' ? 'active' : '' ?>">
          <button type="button" data-tab="kategoriler" onclick="switchAdminTab('kategoriler', this)">
            Kategoriler
          </button>
        </li>
        <li class="nav-menu-item <?= $activeTab === 'komisyon' ? 'active' : '' ?>">
          <button type="button" data-tab="komisyon" onclick="switchAdminTab('komisyon', this)">
            Komisyon oranları
          </button>
        </li>
        <li class="nav-menu-item <?= $activeTab === 'raporlar' ? 'active' : '' ?>">
          <button type="button" data-tab="raporlar" onclick="switchAdminTab('raporlar', this)">
            Raporlar
          </button>
        </li>
        <li class="nav-menu-item <?= $activeTab === 'ihtilaflar' ? 'active' : '' ?>">
          <button type="button" data-tab="ihtilaflar" onclick="switchAdminTab('ihtilaflar', this)">
            İhtilaflar
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

      <!-- 4 Adet KPI Kartı -->
      <div class="kpi-grid">
        <div class="kpi-card">
          <div class="kpi-label">KULLANICILAR</div>
          <div class="kpi-value">3</div>
        </div>
        <div class="kpi-card highlight">
          <div class="kpi-label">AKTİF LOTLAR</div>
          <div class="kpi-value">6</div>
        </div>
        <div class="kpi-card">
          <div class="kpi-label">RAPORLAR</div>
          <div class="kpi-value">1</div>
        </div>
        <div class="kpi-card">
          <div class="kpi-label">AÇIK İHTİLAFLAR</div>
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

    <!-- ROL & YETKİ YÖNETİMİ (RBAC) TABI -->
    <div id="admTab_yetkiler" class="tab-content <?= $activeTab === 'yetkiler' ? 'active' : '' ?>">
      <div class="page-breadcrumb">ADMIN / ÇALIŞMA ALANI / ROL VE YETKİ YÖNETİMİ</div>
      <div style="display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:16px;">
        <div>
          <h1 class="page-heading">Rol ve Yetki Seviyeleri Yönetimi (RBAC)</h1>
          <p class="page-subheading" style="color:var(--gold-primary); font-weight:500;">
            ⚡ "Doğrulanan kullanıcıların rolüne göre (Alıcı, Satıcı, Eksper, Admin) sistem içi erişim izinlerinin ve yetki seviyelerinin belirlenmesidir."
          </p>
        </div>
        <span class="badge badge-gold" style="font-size:0.8rem; padding:6px 12px;">SRS Güvenlik & Yetkilendirme Standardı</span>
      </div>

      <!-- Bilgilendirme Kartı -->
      <div style="background:rgba(212,175,55,0.06); border:1px solid rgba(212,175,55,0.25); border-radius:8px; padding:12px 16px; margin-bottom:24px; display:flex; align-items:center; gap:12px;">
        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#d4af37" stroke-width="2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
        <div style="font-size:0.83rem; color:#c5cbd3; line-height:1.45;">
          Platformda kimliği doğrulanan (KYC onaylı) her kullanıcı, üstlendiği role (<strong style="color:#fff;">Alıcı, Satıcı, Eksper, Admin</strong>) göre katı bir <strong>Rol Tabanlı Erişim Kontrolü (RBAC)</strong> politikasına tabi tutulur. Çıkar çatışmasını önlemek (Shill Bidding engeli) ve işlem güvenliğini sağlamak için her rolün yetki sınırları ve izin matrisi aşağıda dinamik olarak yönetilmektedir.
        </div>
      </div>

      <!-- 4 Temel Rol Yetki Seviyeleri Kartları (Grid) -->
      <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(260px, 1fr)); gap:16px; margin-bottom:28px;">
        <!-- 1. ALICI KARTI -->
        <div class="panel-card" style="border-top:3px solid #3fb950; background:#0b0f17;">
          <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:8px;">
            <h3 style="color:#3fb950; margin:0; font-size:1.05rem;">Alıcı (Buyer)</h3>
            <span class="badge" style="background:rgba(63,185,80,0.15); color:#3fb950;">Pey & Koleksiyon</span>
          </div>
          <p style="font-size:0.78rem; color:#8b949e; margin-bottom:12px;">Açık artırmalarda pey sürme, emanet teminat ve eser kazanımı.</p>
          <div style="font-size:0.76rem; color:#ddd; line-height:1.6;">
            <div><strong>Yetki Kademeleri:</strong></div>
            <ul style="margin:4px 0 10px 16px; padding:0; color:#8b949e;">
              <li><strong style="color:#c5cbd3;">Standart (Lv1):</strong> Temel açık artırma katılımı</li>
              <li><strong style="color:#c5cbd3;">Onaylı (Lv2 - KYC):</strong> Otomatik teklif, limitsiz teminat</li>
              <li><strong style="color:#d4af37;">VIP / Gold (Lv3):</strong> VIP Özel Salon & Telefon Masası</li>
            </ul>
            <div><strong>İzinler:</strong> Canlı Pey, Proxy Bid, Bloke Cüzdan, Sipariş Takibi</div>
            <div style="color:#f87171; margin-top:4px;"><strong>Kısıt:</strong> İlan açamaz, ekspertiz yapamaz.</div>
          </div>
        </div>

        <!-- 2. SATICI KARTI -->
        <div class="panel-card" style="border-top:3px solid #60a5fa; background:#0b0f17;">
          <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:8px;">
            <h3 style="color:#60a5fa; margin:0; font-size:1.05rem;">Satıcı (Seller)</h3>
            <span class="badge" style="background:rgba(96,165,250,0.15); color:#60a5fa;">Eser & Konsinye</span>
          </div>
          <p style="font-size:0.78rem; color:#8b949e; margin-bottom:12px;">Eser listeleme, ekspertiz başvurusu ve satış geliri tahsilatı.</p>
          <div style="font-size:0.76rem; color:#ddd; line-height:1.6;">
            <div><strong>Yetki Kademeleri:</strong></div>
            <ul style="margin:4px 0 10px 16px; padding:0; color:#8b949e;">
              <li><strong style="color:#c5cbd3;">Bireysel (Lv1):</strong> 3 aktif eser ilanı sınırı</li>
              <li><strong style="color:#c5cbd3;">Kurumsal (Lv2):</strong> Sınırsız ilan, %6 komisyon</li>
              <li><strong style="color:#60a5fa;">Premium Galeri (Lv3):</strong> Öncelikli katalog yerleşimi</li>
            </ul>
            <div><strong>İzinler:</strong> İlan Taslağı, Ekspertiz Talebi, Hakediş Çekme</div>
            <div style="color:#f87171; margin-top:4px;"><strong>Kısıt:</strong> Kendi lotuna pey veremez (Shill Bidding engeli).</div>
          </div>
        </div>

        <!-- 3. EKSPERTİZ KARTI -->
        <div class="panel-card" style="border-top:3px solid #e3b341; background:#0b0f17;">
          <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:8px;">
            <h3 style="color:#e3b341; margin:0; font-size:1.05rem;">Ekspertiz (Appraiser)</h3>
            <span class="badge" style="background:rgba(227,179,65,0.15); color:#e3b341;">Orijinallik & Değerleme</span>
          </div>
          <p style="font-size:0.78rem; color:#8b949e; margin-bottom:12px;">Eser orijinalliği, 8 kriterli kondisyon incelemesi ve değer takdiri.</p>
          <div style="font-size:0.76rem; color:#ddd; line-height:1.6;">
            <div><strong>Yetki Kademeleri:</strong></div>
            <ul style="margin:4px 0 10px 16px; padding:0; color:#8b949e;">
              <li><strong style="color:#c5cbd3;">Saha Eksperi (Lv1):</strong> Ön kondisyon ve fiziksel tespit</li>
              <li><strong style="color:#c5cbd3;">Kıdemli Uzman (Lv2):</strong> Spektrometre, sertifika onayı</li>
              <li><strong style="color:#e3b341;">Baş Eksper (Lv3):</strong> İtiraz tahkimi, heyet başkanlığı</li>
            </ul>
            <div><strong>İzinler:</strong> Rapor Hazırlama, Değer Takdiri, Tahkim Görüşü</div>
            <div style="color:#f87171; margin-top:4px;"><strong>Kısıt:</strong> Eser alım-satımı yapamaz (Çıkar çatışması yasağı).</div>
          </div>
        </div>

        <!-- 4. ADMIN KARTI -->
        <div class="panel-card" style="border-top:3px solid #d4af37; background:#0b0f17;">
          <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:8px;">
            <h3 style="color:#d4af37; margin:0; font-size:1.05rem;">Yönetici (Admin)</h3>
            <span class="badge badge-gold">Platform Otoritesi</span>
          </div>
          <p style="font-size:0.78rem; color:#8b949e; margin-bottom:12px;">Sistem yönetimi, doğrulama onayları, finansal ve hukuki denetim.</p>
          <div style="font-size:0.76rem; color:#ddd; line-height:1.6;">
            <div><strong>Yetki Kademeleri:</strong></div>
            <ul style="margin:4px 0 10px 16px; padding:0; color:#8b949e;">
              <li><strong style="color:#c5cbd3;">Operatör (Lv1):</strong> Destek ve müzayede izleme</li>
              <li><strong style="color:#c5cbd3;">Denetçi (Lv2):</strong> KYC onayı, askıya alma, tahkim kararı</li>
              <li><strong style="color:#d4af37;">Süper Admin (Lv3):</strong> Kök yetki, kur/komisyon, kilitler</li>
            </ul>
            <div><strong>İzinler:</strong> KYC Onayı, Rol/Yetki Atama, İhale Kapatma, Audit</div>
            <div style="color:#3fb950; margin-top:4px;"><strong>Kısıt:</strong> Yok (Tüm işlemler Audit Log ile kriptolanır).</div>
          </div>
        </div>
      </div>

      <!-- Kullanıcıların Yetki Seviyelerini ve İzinlerini Yönetme Masası -->
      <div class="panel-card" style="margin-bottom:28px;">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:16px;">
          <div>
            <h3 class="panel-title" style="margin-bottom:4px;">Doğrulanan Kullanıcı Yetki & Erişim İzni Masası</h3>
            <p style="font-size:0.8rem; color:var(--text-muted); margin:0;">
              Sistemdeki kayıtlı kullanıcıların rolleri, KYC doğrulama durumları ve rollere özel yetki seviyeleri doğrudan buradan güncellenir.
            </p>
          </div>
          <span class="badge" style="background:#161b22; border:1px solid #30363d; color:#c5cbd3;"><?= count($users) ?> Kayıtlı Kullanıcı</span>
        </div>

        <div style="overflow-x:auto;">
          <table class="data-table">
            <thead>
              <tr>
                <th>ID</th>
                <th>Kullanıcı / E-posta</th>
                <th>KYC Doğrulama</th>
                <th>Mevcut Rol</th>
                <th>Yetki Seviyesi</th>
                <th>Özel İzinler</th>
                <th style="text-align:right;">Yetkiyi Güncelle</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($users as $u): 
                $uid = (int)($u['id'] ?? 0);
                $isKyc = !empty($u['kycDogrulama']['dogrulandiMi']);
                $urole = strtoupper((string)($u['rol'] ?? 'ALICI'));
                $tier = htmlspecialchars($u['yetkiSeviyesi'] ?? ($u['aliciProfili']['vipSeviyesi'] ?? ($u['adminProfili']['yetkiDerecesi'] ? 'LV-' . $u['adminProfili']['yetkiDerecesi'] : 'STANDART')));
              ?>
                <tr>
                  <td><strong style="color:#8b949e;">#<?= $uid ?></strong></td>
                  <td>
                    <div style="font-weight:600; color:#fff;"><?= htmlspecialchars(($u['ad'] ?? '') . ' ' . ($u['soyad'] ?? '')) ?></div>
                    <div style="font-size:0.75rem; color:#8b949e;"><?= htmlspecialchars($u['email'] ?? '') ?></div>
                  </td>
                  <td>
                    <?php if ($isKyc): ?>
                      <span class="badge badge-success" style="font-size:0.72rem;">✓ DOĞRULANDI</span>
                    <?php else: ?>
                      <span class="badge badge-warning" style="font-size:0.72rem;">BEKLEMEDE</span>
                    <?php endif; ?>
                  </td>
                  <td>
                    <span class="badge <?php 
                      echo match($urole) {
                        'ADMIN' => 'badge-gold',
                        'EKSPERTIZ' => 'badge-info',
                        'SATICI' => 'badge-primary',
                        default => 'badge-success'
                      };
                    ?>">
                      <?= $urole ?>
                    </span>
                  </td>
                  <td>
                    <span style="font-weight:700; font-size:0.8rem; color:<?= in_array($tier, ['GOLD', 'PLATINUM', 'DIAMOND', 'SUPER_ADMIN', 'BAS_EKSPER', 'LV-3'], true) ? '#d4af37' : '#c5cbd3' ?>;">
                      <?= $tier ?>
                    </span>
                  </td>
                  <td style="font-size:0.75rem; color:#8b949e;">
                    <?php 
                      if ($urole === 'ADMIN') {
                        echo '<span style="color:#d4af37;">✓ Tam Kök Yetki (All Access)</span>';
                      } elseif ($urole === 'ALICI') {
                        echo '<span>Canlı Pey' . (in_array($tier, ['GOLD', 'PLATINUM'], true) ? ', <strong style="color:#d4af37;">VIP Özel Salon</strong>' : '') . ', Proxy Bid</span>';
                      } elseif ($urole === 'SATICI') {
                        echo '<span>İlan Açma, Ekspertiz Talebi, Hakediş</span>';
                      } elseif ($urole === 'EKSPERTIZ') {
                        echo '<span>Rapor Düzenleme, Kondisyon, Sertifika</span>';
                      } else {
                        echo '<span>Standart Destek</span>';
                      }
                    ?>
                  </td>
                  <td style="text-align:right;">
                    <!-- Yetki Düzenleme Açılır Butonu -->
                    <button type="button" class="btn-outline-gold btn-sm" onclick="toggleRbacForm(<?= $uid ?>)" style="padding:3px 10px; font-size:0.75rem;">
                      ⚙ Yetki Ata
                    </button>
                  </td>
                </tr>
                <!-- Hızlı Düzenleme Satırı (Gizli/Açılır) -->
                <tr id="rbac_row_<?= $uid ?>" style="display:none; background:rgba(212,175,55,0.03);">
                  <td colspan="7" style="padding:14px 20px; border-left:3px solid var(--gold-primary);">
                    <form method="POST" style="margin:0; display:flex; flex-wrap:wrap; gap:16px; align-items:flex-end;">
                      <input type="hidden" name="action" value="update_user_role_and_permissions">
                      <input type="hidden" name="user_id" value="<?= $uid ?>">

                      <div style="flex:1; min-width:140px;">
                        <label style="display:block; font-size:0.72rem; color:#8b949e; margin-bottom:4px; font-weight:600;">SİSTEM ROLÜ</label>
                        <select name="new_role" class="form-control" style="font-size:0.8rem; padding:6px 10px; height:auto;" onchange="updateTierOptions(<?= $uid ?>, this.value)">
                          <option value="ALICI" <?= $urole === 'ALICI' ? 'selected' : '' ?>>ALICI (Buyer)</option>
                          <option value="SATICI" <?= $urole === 'SATICI' ? 'selected' : '' ?>>SATICI (Seller)</option>
                          <option value="EKSPERTIZ" <?= $urole === 'EKSPERTIZ' ? 'selected' : '' ?>>EKSPERTİZ (Appraiser)</option>
                          <option value="ADMIN" <?= $urole === 'ADMIN' ? 'selected' : '' ?>>ADMIN (Sistem Yöneticisi)</option>
                          <option value="CANLI_DESTEK" <?= $urole === 'CANLI_DESTEK' ? 'selected' : '' ?>>CANLI DESTEK (Support)</option>
                        </select>
                      </div>

                      <div style="flex:1; min-width:160px;">
                        <label style="display:block; font-size:0.72rem; color:#8b949e; margin-bottom:4px; font-weight:600;">YETKİ SEVİYESİ (TIER)</label>
                        <select name="auth_tier" id="tier_select_<?= $uid ?>" class="form-control" style="font-size:0.8rem; padding:6px 10px; height:auto;">
                          <?php if ($urole === 'ADMIN'): ?>
                            <option value="OPERATOR">OPERATOR (Seviye 1)</option>
                            <option value="DENETCI">DENETCI (Seviye 2)</option>
                            <option value="SUPER_ADMIN" selected>SUPER_ADMIN (Seviye 3 - Kök)</option>
                          <?php elseif ($urole === 'SATICI'): ?>
                            <option value="BIREYSEL">BIREYSEL (Standart Satıcı)</option>
                            <option value="KURUMSAL" <?= $tier === 'KURUMSAL' ? 'selected' : '' ?>>KURUMSAL (Antikacı / Galeri)</option>
                            <option value="PREMIUM" <?= $tier === 'PREMIUM' ? 'selected' : '' ?>>PREMIUM (Konsinye Ortağı)</option>
                          <?php elseif ($urole === 'EKSPERTIZ'): ?>
                            <option value="SAHA_EKSPERI">SAHA_EKSPERI (Fiziksel İnceleme)</option>
                            <option value="KIDEMLI_EKSPER">KIDEMLI_EKSPER (Laboratuvar & Sertifika)</option>
                            <option value="BAS_EKSPER" selected>BAS_EKSPER (Kurul Başkanı / Tahkim)</option>
                          <?php else: ?>
                            <option value="STANDART">STANDART (Genel Alıcı)</option>
                            <option value="GOLD" <?= $tier === 'GOLD' ? 'selected' : '' ?>>GOLD (VIP Salon & ₺150K Limit)</option>
                            <option value="PLATINUM" <?= $tier === 'PLATINUM' ? 'selected' : '' ?>>PLATINUM (VIP Yüksek Portföy)</option>
                            <option value="DIAMOND" <?= $tier === 'DIAMOND' ? 'selected' : '' ?>>DIAMOND (Müze / Vakıf)</option>
                          <?php endif; ?>
                        </select>
                      </div>

                      <div style="flex:2; min-width:240px;">
                        <label style="display:block; font-size:0.72rem; color:#8b949e; margin-bottom:4px; font-weight:600;">ERİŞİM VE İŞLEM İZİNLERİ</label>
                        <div style="display:flex; flex-wrap:wrap; gap:8px; font-size:0.75rem; color:#c5cbd3;">
                          <label style="display:flex; align-items:center; gap:4px; cursor:pointer;">
                            <input type="checkbox" name="permissions[]" value="BID_LIVE" checked> Canlı Pey
                          </label>
                          <label style="display:flex; align-items:center; gap:4px; cursor:pointer;">
                            <input type="checkbox" name="permissions[]" value="VIP_SALON" <?= in_array($tier, ['GOLD', 'PLATINUM', 'DIAMOND', 'SUPER_ADMIN'], true) ? 'checked' : '' ?>> VIP Salon
                          </label>
                          <label style="display:flex; align-items:center; gap:4px; cursor:pointer;">
                            <input type="checkbox" name="permissions[]" value="LISTING_CREATE" <?= in_array($urole, ['SATICI', 'ADMIN'], true) ? 'checked' : '' ?>> İlan Yükleme
                          </label>
                          <label style="display:flex; align-items:center; gap:4px; cursor:pointer;">
                            <input type="checkbox" name="permissions[]" value="APPRAISAL_APPROVE" <?= in_array($urole, ['EKSPERTIZ', 'ADMIN'], true) ? 'checked' : '' ?>> Ekspertiz Onay
                          </label>
                        </div>
                      </div>

                      <div style="display:flex; gap:8px;">
                        <button type="submit" class="btn-register-glow" style="padding:6px 14px; font-size:0.78rem; font-weight:600;">Kaydet ve Uygula</button>
                        <button type="button" class="btn-outline-gold" style="padding:6px 10px; font-size:0.78rem;" onclick="toggleRbacForm(<?= $uid ?>)">İptal</button>
                      </div>
                    </form>
                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Rol Bazlı Sistem İçi Erişim İzinleri Matrisi (RBAC Matrix) -->
      <div class="panel-card">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:14px;">
          <div>
            <h3 class="panel-title" style="margin-bottom:2px;">Sistem İçi Erişim İzinleri Matrisi (RBAC Policy)</h3>
            <p style="font-size:0.8rem; color:var(--text-muted); margin:0;">Modüller ve eylemler bazında rollerin erişim hakları tablosu</p>
          </div>
          <span class="badge badge-gold">Aktif Politika v2026.4</span>
        </div>

        <div style="overflow-x:auto;">
          <table class="data-table" style="font-size:0.82rem;">
            <thead>
              <tr>
                <th>Platform Modülü / İşlem Yetkisi</th>
                <th style="text-align:center; color:#3fb950;">Alıcı (Buyer)</th>
                <th style="text-align:center; color:#60a5fa;">Satıcı (Seller)</th>
                <th style="text-align:center; color:#e3b341;">Eksper (Appraiser)</th>
                <th style="text-align:center; color:#d4af37;">Yönetici (Admin)</th>
              </tr>
            </thead>
            <tbody>
              <tr>
                <td><strong>Canlı Açık Artırma & Normal Pey Sürme</strong></td>
                <td style="text-align:center; color:#3fb950;">✓ İzinli (Onaylı)</td>
                <td style="text-align:center; color:#f87171;">✕ Yasak (Kendi lotu)</td>
                <td style="text-align:center; color:#f87171;">✕ Yasak (Çıkar Çatışması)</td>
                <td style="text-align:center; color:#3fb950;">✓ İdari İzleme</td>
              </tr>
              <tr>
                <td><strong>VIP Özel Salon Katılımı & Telefon Masası</strong></td>
                <td style="text-align:center; color:#d4af37;">⚡ Yalnızca GOLD/PLATINUM</td>
                <td style="text-align:center; color:#8b949e;">— İzleme</td>
                <td style="text-align:center; color:#8b949e;">— Rapor Kaydı</td>
                <td style="text-align:center; color:#3fb950;">✓ Tam Yetki</td>
              </tr>
              <tr>
                <td><strong>Eser Listeleme ve İlan Yükleme</strong></td>
                <td style="text-align:center; color:#f87171;">✕ Yetkisiz</td>
                <td style="text-align:center; color:#3fb950;">✓ İzinli (Taslak)</td>
                <td style="text-align:center; color:#f87171;">✕ Yetkisiz</td>
                <td style="text-align:center; color:#3fb950;">✓ Doğrudan Düzenleme</td>
              </tr>
              <tr>
                <td><strong>Ekspertiz Raporu Hazırlama ve Değerleme</strong></td>
                <td style="text-align:center; color:#f87171;">✕ Yetkisiz</td>
                <td style="text-align:center; color:#8b949e;">Talep Edebilir</td>
                <td style="text-align:center; color:#3fb950;">✓ Resmi İmza & Onay</td>
                <td style="text-align:center; color:#3fb950;">✓ Denetim & İptal</td>
              </tr>
              <tr>
                <td><strong>Emanet Kasa (Escrow) Bloke ve Bakiye</strong></td>
                <td style="text-align:center; color:#3fb950;">✓ Kendi Cüzdanı</td>
                <td style="text-align:center; color:#3fb950;">✓ Hakediş Tahsilatı</td>
                <td style="text-align:center; color:#8b949e;">Ücret Hakedişi</td>
                <td style="text-align:center; color:#3fb950;">✓ Kasa Denetimi / Çözme</td>
              </tr>
              <tr>
                <td><strong>İhtilaf ve Tahkim Kararı Verme</strong></td>
                <td style="text-align:center; color:#8b949e;">Şikayet Açabilir</td>
                <td style="text-align:center; color:#8b949e;">Savunma Sunabilir</td>
                <td style="text-align:center; color:#e3b341;">Bilirkişi Görüşü</td>
                <td style="text-align:center; color:#3fb950;">✓ Nihai Bağlayıcı Karar</td>
              </tr>
              <tr>
                <td><strong>Kullanıcı Doğrulama (KYC) ve Banlama</strong></td>
                <td style="text-align:center; color:#f87171;">✕ Yetkisiz</td>
                <td style="text-align:center; color:#f87171;">✕ Yetkisiz</td>
                <td style="text-align:center; color:#f87171;">✕ Yetkisiz</td>
                <td style="text-align:center; color:#3fb950;">✓ Tam Yetkili</td>
              </tr>
              <tr>
                <td><strong>Denetim İzi (Audit Log) & Güvenlik İzleme</strong></td>
                <td style="text-align:center; color:#f87171;">✕ Yetkisiz</td>
                <td style="text-align:center; color:#f87171;">✕ Yetkisiz</td>
                <td style="text-align:center; color:#f87171;">✕ Yetkisiz</td>
                <td style="text-align:center; color:#3fb950;">✓ Kriptografik Loglar</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- 3. TAB: MÜZAYEDE & LOT DÜZENLEME (ADMİN DÜZENLEME YETKİSİ) -->
    <div id="admTab_moderasyon" class="tab-content <?= $activeTab === 'moderasyon' ? 'active' : '' ?>">
      <div class="page-breadcrumb">ADMIN / ÇALIŞMA ALANI</div>
      <h1 class="page-heading">Müzayede ve Lot Düzenleme</h1>
      <p class="page-subheading">Açık artırmadaki lotların fiyatlarını, pey adımlarını, sürelerini ve durumlarını yönetin</p>

      <!-- Aktif Lotlar Düzenleme Tablosu -->
      <div class="panel-card" style="margin-bottom:24px;">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:14px;">
          <div>
            <h3 class="panel-title" style="margin-bottom:2px;">Katalogdaki Müzayede Lotları</h3>
            <p style="font-size:0.8rem; color:var(--text-muted);">Tüm canlı lotların başlangıç/rezerv fiyatını, artırım adımını ve durumunu buradan düzenleyebilirsiniz.</p>
          </div>
          <span class="badge badge-gold"><?= count($lots) ?> Adet Eser</span>
        </div>

        <table class="data-table">
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
                    <button type="submit" class="btn-outline-gold btn-sm" style="padding:4px 8px; font-size:0.75rem; color:#f87171; border-color:rgba(239,68,68,0.35);" title="Son peyi iptal et ve önceki fiyata dön">Pey İptal</button>
                  </form>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>

      <!-- Onay Bekleyen Taslak İlanlar -->
      <div class="panel-card">
        <h3 class="panel-title" style="margin-bottom:12px;">Yeni Taslak İlanlar (Onay ve Yayına Alma)</h3>
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
                    <form method="POST" style="display:inline-flex; gap:6px; margin:0;">
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
    </script>

    <!-- 4. TAB: EKSPERTİZ ATAMA (media_1795014776298_9cbae966.jpg ile Birebir) -->
    <div id="admTab_ekspertiz" class="tab-content <?= $activeTab === 'ekspertiz' ? 'active' : '' ?>">
      <div class="page-breadcrumb">ADMIN / ÇALIŞMA ALANI</div>
      <h1 class="page-heading">Ekspertiz atama</h1>
      <p class="page-subheading" style="margin-bottom:24px;">Seçkin müzayede deneyiminizi yönetin</p>

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

    <!-- 5. TAB: KATEGORİLER (media_1795014776291_4276223f.jpg ile Birebir) -->
    <div id="admTab_kategoriler" class="tab-content <?= $activeTab === 'kategoriler' ? 'active' : '' ?>">
      <div class="page-breadcrumb">ADMIN / ÇALIŞMA ALANI</div>
      <h1 class="page-heading">Kategoriler</h1>
      <p class="page-subheading" style="margin-bottom:24px;">Seçkin müzayede deneyiminizi yönetin</p>

      <div class="panel-card">
        <h3 class="panel-title" style="margin-bottom:12px;">Kategoriler</h3>
        <p style="font-size:0.95rem; color:#848d9a; margin-bottom:24px;">
          Saat • Kamera • Sanat • Otomobil • Nümismatik • Efemera
        </p>

        <form method="POST" style="max-width:400px;">
          <input type="hidden" name="action" value="add_category">
          <input type="hidden" name="kategori_kodu" value="GENEL">
          <input type="hidden" name="nitelikler" value="Dönem, Kondisyon, Menşei">
          
          <div class="form-group" style="margin-bottom:14px;">
            <label class="form-label" style="font-size:0.85rem; color:#f0f3f6;">Yeni kategori *</label>
            <input type="text" name="kategori_adi" class="form-control" style="background:#0e1118; border:1px solid #283244; border-radius:8px; padding:10px 14px;" required>
          </div>

          <button type="submit" class="btn-gold-pill" style="padding:8px 24px; cursor:pointer;">
            Ekle
          </button>
        </form>
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

function openDemoSupportModal() {
  document.getElementById('demoSupportModal').style.display = 'flex';
}

function closeDemoSupportModal() {
  document.getElementById('demoSupportModal').style.display = 'none';
}

function toggleRbacForm(userId) {
  const row = document.getElementById('rbac_row_' + userId);
  if (row) {
    row.style.display = (row.style.display === 'none' || row.style.display === '') ? 'table-row' : 'none';
  }
}

function updateTierOptions(userId, role) {
  const sel = document.getElementById('tier_select_' + userId);
  if (!sel) return;
  sel.innerHTML = '';
  
  if (role === 'ADMIN') {
    sel.innerHTML = `
      <option value="OPERATOR">OPERATOR (Seviye 1)</option>
      <option value="DENETCI">DENETCI (Seviye 2)</option>
      <option value="SUPER_ADMIN" selected>SUPER_ADMIN (Seviye 3 - Kök)</option>
    `;
  } else if (role === 'SATICI') {
    sel.innerHTML = `
      <option value="BIREYSEL">BIREYSEL (Standart Satıcı)</option>
      <option value="KURUMSAL" selected>KURUMSAL (Antikacı / Galeri)</option>
      <option value="PREMIUM">PREMIUM (Konsinye Ortağı)</option>
    `;
  } else if (role === 'EKSPERTIZ') {
    sel.innerHTML = `
      <option value="SAHA_EKSPERI">SAHA_EKSPERI (Fiziksel İnceleme)</option>
      <option value="KIDEMLI_EKSPER" selected>KIDEMLI_EKSPER (Laboratuvar & Sertifika)</option>
      <option value="BAS_EKSPER">BAS_EKSPER (Kurul Başkanı / Tahkim)</option>
    `;
  } else if (role === 'ALICI') {
    sel.innerHTML = `
      <option value="STANDART">STANDART (Genel Alıcı)</option>
      <option value="GOLD" selected>GOLD (VIP Salon & ₺150K Limit)</option>
      <option value="PLATINUM">PLATINUM (VIP Yüksek Portföy)</option>
      <option value="DIAMOND">DIAMOND (Müze / Vakıf)</option>
    `;
  } else {
    sel.innerHTML = `
      <option value="STANDART_DESTEK" selected>STANDART_DESTEK</option>
      <option value="KIDEMLI_DESTEK">KIDEMLI_DESTEK</option>
    `;
  }
}
</script>
