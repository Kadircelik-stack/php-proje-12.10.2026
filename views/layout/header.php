<?php
declare(strict_types=1);

/** @var array|null $flashMessage */
/** @var string $activeRole */
/** @var string $activeCurrency */
/** @var array $db */
/** @var array|null $loggedInUser */
/** @var bool $autoOpenAuthModal */

$loggedInUser      = $loggedInUser ?? ($_SESSION['auth_user'] ?? null);
$autoOpenAuthModal = $autoOpenAuthModal ?? false;

$userBalance = 0.0;
if (is_array($loggedInUser) && !empty($loggedInUser['id'])) {
    foreach (($db['wallets'] ?? []) as $w) {
        if ((int)($w['kullaniciId'] ?? 0) === (int)$loggedInUser['id']) {
            $userBalance = (float)($w['bakiye'] ?? 0.0);
            break;
        }
    }
}

$roleBadgeColors = [
    'ADMIN'        => ['bg' => 'rgba(200, 170, 110, 0.16)', 'border' => 'rgba(226, 201, 146, 0.45)', 'text' => '#E2C992'],
    'SATICI'       => ['bg' => 'rgba(200, 170, 110, 0.14)', 'border' => 'rgba(200, 170, 110, 0.4)',  'text' => '#E2C992'],
    'ALICI'        => ['bg' => 'rgba(200, 170, 110, 0.12)', 'border' => 'rgba(200, 170, 110, 0.36)', 'text' => '#DFC48D'],
    'EKSPERTIZ'    => ['bg' => 'rgba(183, 164, 132, 0.14)', 'border' => 'rgba(200, 170, 110, 0.38)', 'text' => '#DFC48D'],
    'CANLI_DESTEK' => ['bg' => 'rgba(183, 164, 132, 0.14)', 'border' => 'rgba(183, 164, 132, 0.38)', 'text' => '#DCD0BC'],
];
$activeRoleCode = is_array($loggedInUser) ? strtoupper((string)($loggedInUser['rol'] ?? 'ALICI')) : 'MISAFIR';
$activeRoleStyle = $roleBadgeColors[$activeRoleCode] ?? ['bg' => 'rgba(200, 170, 110, 0.12)', 'border' => 'rgba(200, 170, 110, 0.34)', 'text' => '#C8AA6E'];
$activeUserAvatar = is_array($loggedInUser) ? \Martello\Services\UserAuthStore::resolveAvatar($loggedInUser) : 'public/assets/images/avatar_zeynep.jpg';
?>
<!DOCTYPE html>
<html lang="tr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>MARTELLO — Çevrimiçi Müzayede ve Ekspertiz Platformu (ZeroDay)</title>
  <link rel="stylesheet" href="public/assets/css/martello.css?v=<?= time() ?>">
  <script>
    window.MARTELLO_CURRENCY = {
      code: <?= json_encode($activeCurrency) ?>,
      symbol: <?= json_encode($currencyStrategy->getSymbol()) ?>,
      rate: <?= (float)($rates[$activeCurrency] ?? 1.0) ?>,
      rates: <?= json_encode($rates) ?>
    };
    window.formatPriceJs = function(amountInTry) {
      var rate = window.MARTELLO_CURRENCY.rate || 1.0;
      var converted = parseFloat(amountInTry || 0) / rate;
      return window.MARTELLO_CURRENCY.symbol + converted.toLocaleString('tr-TR', {minimumFractionDigits: 2, maximumFractionDigits: 2});
    };
  </script>
</head>
<body>

<header class="martello-topbar">
  <div class="brand-wrap">
    <a href="?role=vitrin&currency=<?= htmlspecialchars($activeCurrency) ?>" style="display:inline-flex; align-items:center; gap:11px; text-decoration:none;">
      <span class="brand-title">MARTELLO</span>
      <span style="color:rgba(200,170,110,0.32); font-weight:300; font-size:1.05rem; margin-top:-1px;">|</span>
      <span class="brand-gavel-icon" style="display:inline-flex; align-items:center;">
        <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="#C8AA6E" stroke-width="1.65" stroke-linecap="round" stroke-linejoin="round">
          <path d="m14 13-7.5 7.5c-.83.83-2.17.83-3 0a2.12 2.12 0 0 1 0-3L11 10"/>
          <path d="m16 16 6-6"/>
          <path d="m8 8 6-6"/>
          <path d="m9 7 8 8"/>
          <path d="m21 11-8-8"/>
        </svg>
      </span>
    </a>
  </div>

  <!-- Hızlı Eser & Marka Arama Çubuğu -->
  <div class="topbar-search-box" style="flex:1; max-width:340px; margin:0 16px;">
    <form method="GET" action="index.php" style="position:relative; width:100%; display:flex; align-items:center; margin:0;">
      <input type="hidden" name="role" value="<?= $activeRole === 'buyer' ? 'buyer' : 'vitrin' ?>">
      <?php if ($activeRole === 'buyer'): ?>
        <input type="hidden" name="tab" value="arama">
      <?php endif; ?>
      <input type="text" name="q" placeholder="Eser, koleksiyon veya sanatçı ara..." value="<?= htmlspecialchars((string)($_GET['q'] ?? '')) ?>" style="width:100%; padding:7px 36px 7px 15px; background:rgba(22,19,17,0.85); border:1px solid rgba(200,170,110,0.22); border-radius:999px; color:#F5F0E6; font-size:0.78rem; outline:none; transition:all 0.25s;" onfocus="this.style.borderColor='#E2C992'; this.style.background='rgba(14,12,10,0.98)';" onblur="this.style.borderColor='rgba(200,170,110,0.22)'; this.style.background='rgba(22,19,17,0.85)';">
      <button type="submit" style="position:absolute; right:11px; background:none; border:none; color:#C8AA6E; cursor:pointer; padding:0; display:flex; align-items:center;" title="Hızlı Ara">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
      </button>
    </form>
  </div>

  <div class="topbar-nav">
    <?php if (is_array($loggedInUser)): ?>
      <?php if (in_array($activeRoleCode, ['ALICI', 'SATICI'], true)): ?>
        <div class="topbar-balance" style="font-size:0.81rem; color:#A39B8F; font-weight:500; white-space:nowrap;">
          Bakiye <strong style="color:#E2C992; font-weight:600; margin-left:3px;"><?= $formatPrice($userBalance) ?></strong>
        </div>
      <?php endif; ?>

      <?php if ($activeRole !== ($loggedInUser['panel'] ?? '')): ?>
        <a href="index.php?role=<?= urlencode((string)$loggedInUser['panel']) ?>&currency=<?= htmlspecialchars($activeCurrency) ?>" class="btn-dark-pill" style="padding:6px 14px; font-size:0.77rem; text-decoration:none; white-space:nowrap; display:inline-flex; align-items:center; gap:6px;">
          <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="#C8AA6E" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg>
          <span>Panelim</span>
        </a>
      <?php endif; ?>

      <div class="user-avatar-pill" onclick="openProfileModal()" style="display:inline-flex; align-items:center; gap:8px; padding:4px 12px 4px 5px; background:rgba(22,19,17,0.92); border:1px solid rgba(200,170,110,0.26); border-radius:999px; cursor:pointer; white-space:nowrap; transition:border-color 0.2s;" onmouseover="this.style.borderColor='#E2C992'" onmouseout="this.style.borderColor='rgba(200,170,110,0.26)'" title="Profil ve Hesap Ayarları">
        <img src="<?= htmlspecialchars($activeUserAvatar) ?>" alt="User" style="width:26px; height:26px; border-radius:50%; object-fit:cover; border:1px solid rgba(226,201,146,0.75);">
        <span style="font-size:0.79rem; color:#F5F0E6; font-weight:500;"><?= htmlspecialchars((string)($loggedInUser['adSoyad'] ?? 'Kullanıcı')) ?></span>
        <span style="font-size:0.64rem; font-weight:600; letter-spacing:0.6px; padding:2px 8px; border-radius:999px; background:<?= $activeRoleStyle['bg'] ?>; border:1px solid <?= $activeRoleStyle['border'] ?>; color:<?= $activeRoleStyle['text'] ?>;">
          <?= htmlspecialchars((string)($loggedInUser['rolEtiket'] ?? $activeRoleCode)) ?>
        </span>
      </div>

      <form method="POST" action="index.php" style="margin:0; display:inline-flex;">
        <input type="hidden" name="action" value="logout">
        <button type="submit" class="btn-logout-luxury" style="padding:6px 13px; font-size:0.76rem; cursor:pointer; display:inline-flex; align-items:center; gap:6px; white-space:nowrap;" title="Oturumu Kapat">
          <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
          <span>Çıkış Yap</span>
        </button>
      </form>

      <select id="currencySelector" class="currency-select" title="Para Birimi Değiştir">
        <option value="TRY" <?= $activeCurrency === 'TRY' ? 'selected' : '' ?>>₺ TRY</option>
        <option value="USD" <?= $activeCurrency === 'USD' ? 'selected' : '' ?>>$ USD</option>
        <option value="EUR" <?= $activeCurrency === 'EUR' ? 'selected' : '' ?>>€ EUR</option>
      </select>
    <?php else: ?>
      <button type="button" onclick="openAuthModal('login')" class="btn-dark-pill" style="padding:7px 17px; font-size:0.79rem; cursor:pointer; white-space:nowrap;">
        Giriş Yap
      </button>
      <button type="button" onclick="openAuthModal('register')" class="btn-gold-pill" style="padding:7px 19px; font-size:0.79rem; cursor:pointer; white-space:nowrap;">
        Kayıt Ol
      </button>
      <select id="currencySelector" class="currency-select" title="Para Birimi Değiştir">
        <option value="TRY" <?= $activeCurrency === 'TRY' ? 'selected' : '' ?>>₺ TRY</option>
        <option value="USD" <?= $activeCurrency === 'USD' ? 'selected' : '' ?>>$ USD</option>
        <option value="EUR" <?= $activeCurrency === 'EUR' ? 'selected' : '' ?>>€ EUR</option>
      </select>
    <?php endif; ?>
  </div>
</header>

<!-- Bilgi ve Şartlar Modalı (Header Dışında: Her Zaman Ekranın Tam Ortasında Açılır) -->
<div id="infoModal" class="martello-modal" onclick="if(event.target===this) closeInfoModal();" style="display:none; position:fixed; z-index:10050; inset:0; width:100vw; height:100vh; background:rgba(6,5,4,0.82); backdrop-filter:blur(8px); align-items:center; justify-content:center; padding:20px;">
  <div style="background:#141210; border:1px solid rgba(200,170,110,0.32); border-radius:14px; max-width:520px; width:100%; max-height:82vh; overflow-y:auto; padding:26px 28px; box-shadow:0 24px 64px rgba(0,0,0,0.88); position:relative;">
    <button type="button" onclick="closeInfoModal()" style="position:absolute; right:16px; top:14px; background:none; border:none; color:#A39B8F; font-size:1.45rem; cursor:pointer; line-height:1;">&times;</button>
    <div id="infoModalBadge" style="font-family:var(--font-brand); color:var(--gold-primary); font-size:0.68rem; font-weight:600; letter-spacing:2px; text-transform:uppercase; margin-bottom:6px;">MARTELLO</div>
    <h3 id="infoModalTitle" style="font-family:var(--font-serif); color:#F5F0E6; font-size:1.45rem; font-weight:500; margin-bottom:12px;">Başlık</h3>
    <div id="infoModalBody" style="color:#C9C1B4; font-size:0.84rem; line-height:1.65;">İçerik</div>
  </div>
</div>

<script>
function openInfoModal(type, ev) {
  if (ev && typeof ev.preventDefault === 'function') {
    ev.preventDefault();
  }
  const titles = {
    'about': 'Hakkımızda',
    'terms': 'Müzayede Şartları & Pey Kuralları',
    'privacy': 'Gizlilik Politikası & KVK',
    'help': 'Yardım Merkezi',
    'categories': 'Ürün Kategorileri'
  };
  const contents = {
    'about': '<p>MARTELLO, seçkin antika, horoloji, Osmanlı dönemi koleksiyonları, filateli ve nümismatik eserleri için geliştirilmiş çevrimiçi müzayede ve ekspertiz platformudur.</p><p style="margin-top:10px; font-size:0.78rem; color:#8C8478;">ZeroDay Ekibi • Esra Yıldırım, Abdullah Kadir Çelik, Rümeysa Şevval Kandilci</p>',
    'terms': '<p><strong>Asgari Pey:</strong> Her lot için belirlenen artış adımının altında teklif kabul edilmez.</p><p style="margin-top:8px;"><strong>Anti-Sniping:</strong> Kapanışa son 60 saniye kala verilen peyler süreyi 60 saniye uzatır.</p><p style="margin-top:8px;"><strong>Emanet Koruması:</strong> Ödemeler teslimat onayına kadar emanet havuzunda tutulur.</p>',
    'privacy': '<p>6698 Sayılı KVKK kapsamında tüm veriler ve pey kayıtları şifreli denetim iziyle korunur. Canlı oturumlarda katılımcılar anonim rumuzlarla görüntülenir.</p>',
    'help': '<p>Bakiyenizi yükledikten sonra canlı müzayedelere manuel veya otomatik tavan teklif ile katılabilirsiniz.</p><p style="margin-top:10px;"><a href="?role=yardim" style="color:#E2C992; text-decoration:underline;">Yardım Merkezine Git →</a></p>',
    'categories': '<p style="margin-bottom:12px;">Platformumuz aşağıdaki 5 ana kategoride seçkin eserler sunmaktadır:</p><ul style="list-style:none; padding:0; display:grid; gap:7px;"><li style="padding:8px 12px; background:#0D0B0A; border:1px solid rgba(200,170,110,0.18); border-radius:8px;"><a href="?role=vitrin&cat=Horoloji#katalog" style="color:#E2C992; font-weight:500;">Horoloji (Vintage Mekanik Saatler) →</a></li><li style="padding:8px 12px; background:#0D0B0A; border:1px solid rgba(200,170,110,0.18); border-radius:8px;"><a href="?role=vitrin&cat=Antika#katalog" style="color:#E2C992; font-weight:500;">Antika &amp; Sanat →</a></li><li style="padding:8px 12px; background:#0D0B0A; border:1px solid rgba(200,170,110,0.18); border-radius:8px;"><a href="?role=vitrin&cat=Osmanl%C4%B1#katalog" style="color:#E2C992; font-weight:500;">Osmanlı Dönemi Koleksiyonları →</a></li><li style="padding:8px 12px; background:#0D0B0A; border:1px solid rgba(200,170,110,0.18); border-radius:8px;"><a href="?role=vitrin&cat=Filateli#katalog" style="color:#E2C992; font-weight:500;">Filateli →</a></li><li style="padding:8px 12px; background:#0D0B0A; border:1px solid rgba(200,170,110,0.18); border-radius:8px;"><a href="?role=vitrin&cat=N%C3%BCmismatik#katalog" style="color:#E2C992; font-weight:500;">Nümismatik →</a></li></ul>'
  };
  const t = document.getElementById('infoModalTitle');
  const b = document.getElementById('infoModalBody');
  if (t) t.innerText = titles[type] || 'Bilgilendirme';
  if (b) b.innerHTML = contents[type] || '';
  const m = document.getElementById('infoModal');
  if (m) m.style.display = 'flex';
  return false;
}
function closeInfoModal() {
  const m = document.getElementById('infoModal');
  if (m) m.style.display = 'none';
}
</script>

<!-- Kimlik Doğrulama Modalı (Gerçek users_auth.json Oturum & Rol Doğrulaması) -->
<div id="authModal" class="martello-modal" style="display:<?= $autoOpenAuthModal ? 'flex' : 'none' ?>; position:fixed; z-index:9999; left:0; top:0; width:100%; height:100%; background:rgba(6,5,4,0.86); backdrop-filter:blur(10px); align-items:center; justify-content:center;">
  <div style="background:#12100E; border:1px solid rgba(200,170,110,0.28); border-radius:16px; max-width:520px; width:92%; max-height:92vh; overflow-y:auto; padding:28px; box-shadow:0 24px 60px rgba(0,0,0,0.85); position:relative;">
    <button type="button" onclick="closeAuthModal()" style="position:absolute; right:18px; top:16px; background:none; border:none; color:#9A9284; font-size:1.5rem; cursor:pointer; transition:color 0.2s;" onmouseover="this.style.color='#E2C992'" onmouseout="this.style.color='#9A9284'">&times;</button>

    <div style="font-family:var(--font-brand); font-size:0.7rem; letter-spacing:2.8px; color:#C8AA6E; text-transform:uppercase; margin-bottom:4px;">MARTELLO PRIVATE ACCESS</div>
    <div style="font-family:var(--font-serif); font-size:1.55rem; font-weight:500; color:#F5F0E6; margin-bottom:16px;">Üye Girişi &amp; Hesap Yönetimi</div>
    
    <div style="display:flex; gap:8px; border-bottom:1px solid rgba(200,170,110,0.16); padding-bottom:14px; margin-bottom:18px;">
      <button type="button" id="tabBtnLogin" onclick="switchAuthTab('login')" class="role-pill active">Giriş Yap</button>
      <button type="button" id="tabBtnRegister" onclick="switchAuthTab('register')" class="role-pill">Kayıt Ol</button>
      <button type="button" id="tabBtnForgot" onclick="switchAuthTab('forgot')" class="role-pill">Şifremi Unuttum</button>
    </div>

    <!-- Giriş Formu -->
    <form id="formLogin" method="POST" action="index.php" style="display:block;">
      <input type="hidden" name="action" value="login">
      <div class="form-group" style="margin-bottom:12px;">
        <label class="form-label">E-Posta Adresi</label>
        <input type="email" id="loginEmailInput" name="email" class="form-control" placeholder="eposta@martello.com" required>
      </div>
      <div class="form-group" style="margin-bottom:16px;">
        <label class="form-label">Şifre</label>
        <input type="password" id="loginPasswordInput" name="password" class="form-control" placeholder="••••••••" required>
      </div>
      <button type="submit" class="btn-gold-pill" style="width:100%; padding:11px 18px; font-size:0.86rem; cursor:pointer;">Güvenli Giriş Yap</button>
      <div style="text-align:center; margin-top:9px; font-size:0.74rem; color:#8C8478;">
        Sistem rolünüzü <code>storage/users_auth.json</code> deposundan tanır ve sizi otomatik kendi çalışma alanınıza yönlendirir.
      </div>

      <!-- Test & Demo Hesapları Bilgi Kutusu (5 Rol — Gerçekçi Kullanıcı Portreleriyle) -->
      <div style="margin-top:16px; padding:14px; background:#0B0A08; border:1px solid rgba(200,170,110,0.2); border-radius:12px;">
        <div style="font-family:var(--font-brand); font-size:0.68rem; font-weight:600; color:#C8AA6E; letter-spacing:1.6px; text-transform:uppercase; margin-bottom:10px;">
          Test Hesapları · 5 Rol (Doldurmak için tıklayın)
        </div>
        <div style="display:grid; grid-template-columns:1fr; gap:6px; font-size:0.74rem;">
          <button type="button" onclick="fillDemoLogin('zeynep@koleksiyon.com','Alici123!')" style="display:flex; justify-content:space-between; align-items:center; width:100%; text-align:left; padding:6px 11px; background:#151310; border:1px solid rgba(200,170,110,0.16); border-radius:8px; color:#EAE2D5; cursor:pointer; transition:all 0.2s;" onmouseover="this.style.borderColor='#C8AA6E'" onmouseout="this.style.borderColor='rgba(200,170,110,0.16)'">
            <span style="display:inline-flex; align-items:center; gap:9px;">
              <img src="public/assets/images/avatar_zeynep.jpg" alt="Zeynep Demir" style="width:26px; height:26px; border-radius:50%; object-fit:cover; border:1px solid rgba(200,170,110,0.5);">
              <span><strong style="color:#E2C992;">ALICI</strong> · Zeynep Demir</span>
            </span>
            <span style="color:#9A9284; font-family:monospace; font-size:0.71rem;">zeynep@koleksiyon.com</span>
          </button>
          <button type="button" onclick="fillDemoLogin('mehmet@antik.com','Satici123!')" style="display:flex; justify-content:space-between; align-items:center; width:100%; text-align:left; padding:6px 11px; background:#151310; border:1px solid rgba(200,170,110,0.16); border-radius:8px; color:#EAE2D5; cursor:pointer; transition:all 0.2s;" onmouseover="this.style.borderColor='#C8AA6E'" onmouseout="this.style.borderColor='rgba(200,170,110,0.16)'">
            <span style="display:inline-flex; align-items:center; gap:9px;">
              <img src="public/assets/images/avatar_mehmet.jpg" alt="Mehmet Kaya" style="width:26px; height:26px; border-radius:50%; object-fit:cover; border:1px solid rgba(200,170,110,0.5);">
              <span><strong style="color:#E2C992;">SATICI</strong> · Mehmet Kaya</span>
            </span>
            <span style="color:#9A9284; font-family:monospace; font-size:0.71rem;">mehmet@antik.com</span>
          </button>
          <button type="button" onclick="fillDemoLogin('burak@ekspertiz.com','Eksper123!')" style="display:flex; justify-content:space-between; align-items:center; width:100%; text-align:left; padding:6px 11px; background:#151310; border:1px solid rgba(200,170,110,0.16); border-radius:8px; color:#EAE2D5; cursor:pointer; transition:all 0.2s;" onmouseover="this.style.borderColor='#C8AA6E'" onmouseout="this.style.borderColor='rgba(200,170,110,0.16)'">
            <span style="display:inline-flex; align-items:center; gap:9px;">
              <img src="public/assets/images/avatar_burak.jpg" alt="Burak Şahin" style="width:26px; height:26px; border-radius:50%; object-fit:cover; border:1px solid rgba(200,170,110,0.5);">
              <span><strong style="color:#E2C992;">EKSPERTİZ</strong> · Burak Şahin</span>
            </span>
            <span style="color:#9A9284; font-family:monospace; font-size:0.71rem;">burak@ekspertiz.com</span>
          </button>
          <button type="button" onclick="fillDemoLogin('elif@destek.com','Destek123!')" style="display:flex; justify-content:space-between; align-items:center; width:100%; text-align:left; padding:6px 11px; background:#151310; border:1px solid rgba(200,170,110,0.16); border-radius:8px; color:#EAE2D5; cursor:pointer; transition:all 0.2s;" onmouseover="this.style.borderColor='#C8AA6E'" onmouseout="this.style.borderColor='rgba(200,170,110,0.16)'">
            <span style="display:inline-flex; align-items:center; gap:9px;">
              <img src="public/assets/images/avatar_elif.jpg" alt="Elif Çelik" style="width:26px; height:26px; border-radius:50%; object-fit:cover; border:1px solid rgba(200,170,110,0.5);">
              <span><strong style="color:#E2C992;">CANLI DESTEK</strong> · Elif Çelik</span>
            </span>
            <span style="color:#9A9284; font-family:monospace; font-size:0.71rem;">elif@destek.com</span>
          </button>
          <button type="button" onclick="fillDemoLogin('admin@muzayede.com','Admin123!')" style="display:flex; justify-content:space-between; align-items:center; width:100%; text-align:left; padding:6px 11px; background:#151310; border:1px solid rgba(200,170,110,0.16); border-radius:8px; color:#EAE2D5; cursor:pointer; transition:all 0.2s;" onmouseover="this.style.borderColor='#C8AA6E'" onmouseout="this.style.borderColor='rgba(200,170,110,0.16)'">
            <span style="display:inline-flex; align-items:center; gap:9px;">
              <img src="public/assets/images/avatar_ahmet.jpg" alt="Ahmet Yılmaz" style="width:26px; height:26px; border-radius:50%; object-fit:cover; border:1px solid rgba(200,170,110,0.5);">
              <span><strong style="color:#E2C992;">ADMIN</strong> · Ahmet Yılmaz</span>
            </span>
            <span style="color:#9A9284; font-family:monospace; font-size:0.71rem;">admin@muzayede.com</span>
          </button>
        </div>
      </div>
    </form>

    <!-- Kayıt Formu (Gerçek Hayat Kuralı: Yalnızca Alıcı ve Satıcı) -->
    <form id="formRegister" method="POST" action="index.php" style="display:none;">
      <input type="hidden" name="action" value="register">
      <div style="display:grid; grid-template-columns:1fr 1fr; gap:10px; margin-bottom:10px;">
        <div class="form-group">
          <label class="form-label">Ad</label>
          <input type="text" name="ad" class="form-control" placeholder="Adınız" required>
        </div>
        <div class="form-group">
          <label class="form-label">Soyad</label>
          <input type="text" name="soyad" class="form-control" placeholder="Soyadınız" required>
        </div>
      </div>
      <div class="form-group" style="margin-bottom:10px;">
        <label class="form-label">E-Posta Adresi</label>
        <input type="email" name="email" class="form-control" placeholder="eposta@adresi.com" required>
      </div>
      <div style="display:grid; grid-template-columns:1fr 1fr; gap:10px; margin-bottom:10px;">
        <div class="form-group">
          <label class="form-label">Telefon</label>
          <input type="tel" name="telefon" class="form-control" placeholder="+90 5xx xxx xx xx">
        </div>
        <div class="form-group">
          <label class="form-label">T.C. / Kimlik No</label>
          <input type="text" name="tc_kimlik" class="form-control" placeholder="11 haneli T.C.">
        </div>
      </div>
      <div style="display:grid; grid-template-columns:1fr 1fr; gap:10px; margin-bottom:10px;">
        <div class="form-group">
          <label class="form-label">Hesap Türünüz</label>
          <select name="rol" class="form-control">
            <option value="ALICI">Alıcı (Müzayede Katılımcısı)</option>
            <option value="SATICI">Satıcı (Eser Arz Eden)</option>
          </select>
        </div>
        <div class="form-group">
          <label class="form-label">Şifre</label>
          <input type="password" name="sifre" class="form-control" placeholder="En az 6 karakter" minlength="6" required>
        </div>
      </div>
      <div style="margin-bottom:12px; padding:9px 12px; background:rgba(200,170,110,0.07); border:1px solid rgba(200,170,110,0.24); border-radius:8px; font-size:0.74rem; color:#D7CEBE;">
        Yetkili personel hesapları (<strong style="color:#E2C992;">Ekspertiz</strong>, <strong style="color:#E2C992;">Canlı Destek</strong>, <strong style="color:#E2C992;">Admin</strong>) güvenlik politikası gereği yalnızca <strong style="color:#E2C992;">Admin Paneli</strong> üzerinden atanır.
      </div>
      <div style="margin-bottom:14px; font-size:0.78rem; color:#aaa; display:flex; gap:8px;">
        <input type="checkbox" id="kvkkCheck" required style="accent-color:var(--gold-primary); margin-top:3px;">
        <label for="kvkkCheck">Mesafeli Satış Sözleşmesi ve KVKK Gizlilik Şartlarını kabul ediyorum.</label>
      </div>
      <button type="submit" class="btn-register-glow" style="width:100%;">Hesap Oluştur ve Giriş Yap</button>
    </form>

    <!-- Şifremi Unuttum Formu -->
    <form id="formForgot" method="POST" action="index.php" style="display:none;">
      <input type="hidden" name="action" value="reset_password">
      <div class="form-group" style="margin-bottom:14px;">
        <label class="form-label">Kayıtlı E-Posta Adresiniz</label>
        <input type="email" name="email" class="form-control" placeholder="ornek@martello.com" required>
      </div>
      <div style="font-size:0.8rem; color:#94a3b8; margin-bottom:14px; line-height:1.4; display:flex; align-items:center; gap:6px;">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#d4af37" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/></svg>
        <span>Şifre sıfırlama bağlantısı en geç 30 saniye içinde e-posta kutunuza ulaştırılır.</span>
      </div>
      <button type="submit" class="btn-register-glow" style="width:100%;">Şifre Sıfırlama Bağlantısı Gönder</button>
    </form>
  </div>
</div>

<!-- Profil Güncelleme Modalı -->
<div id="profileModal" class="martello-modal" onclick="if(event.target===this) closeProfileModal();" style="display:none; position:fixed; z-index:10050; inset:0; width:100vw; height:100vh; background:rgba(6,5,4,0.84); backdrop-filter:blur(8px); align-items:center; justify-content:center; padding:20px;">
  <div style="background:#141210; border:1px solid rgba(200,170,110,0.3); border-radius:14px; max-width:460px; width:100%; padding:24px 26px; box-shadow:0 24px 60px rgba(0,0,0,0.85); position:relative;">
    <button type="button" onclick="closeProfileModal()" style="position:absolute; right:16px; top:14px; background:none; border:none; color:#A39B8F; font-size:1.45rem; cursor:pointer;">&times;</button>
    
    <div style="font-family:var(--font-serif); color:#F5F0E6; font-size:1.35rem; font-weight:500; margin-bottom:4px;">Profil ve Hesap Ayarları</div>
    <div style="color:#A39B8F; font-size:0.78rem; margin-bottom:16px;">İletişim, adres ve şifre bilgilerinizi güncelleyebilirsiniz.</div>

    <form method="POST" action="index.php">
      <input type="hidden" name="action" value="update_profile">
      <input type="hidden" name="user_id" value="<?= (int)($loggedInUser['id'] ?? 0) ?>">
      <div style="display:grid; grid-template-columns:1fr 1fr; gap:10px; margin-bottom:10px;">
        <div class="form-group">
          <label class="form-label">Ad</label>
          <input type="text" name="ad" class="form-control" value="<?= htmlspecialchars((string)($loggedInUser['ad'] ?? '')) ?>" required>
        </div>
        <div class="form-group">
          <label class="form-label">Soyad</label>
          <input type="text" name="soyad" class="form-control" value="<?= htmlspecialchars((string)($loggedInUser['soyad'] ?? '')) ?>" required>
        </div>
      </div>
      <div class="form-group" style="margin-bottom:10px;">
        <label class="form-label">Telefon Numarası</label>
        <input type="tel" name="telefon" class="form-control" value="<?= htmlspecialchars((string)($loggedInUser['telefon'] ?? '')) ?>">
      </div>
      <div class="form-group" style="margin-bottom:10px;">
        <label class="form-label">Teslimat Adresi</label>
        <input type="text" name="adres" class="form-control" value="Levent Mah. Cömert Sok. No:12 Beşiktaş / İstanbul">
      </div>
      <div class="form-group" style="margin-bottom:10px;">
        <label class="form-label">Fatura Bilgisi</label>
        <input type="text" name="fatura_bilgisi" class="form-control" value="<?= htmlspecialchars((string)($loggedInUser['adSoyad'] ?? '')) ?> — Bireysel Fatura">
      </div>
      <div class="form-group" style="margin-bottom:16px;">
        <label class="form-label">Yeni Şifre (Değiştirmek İstemiyorsanız Boş Bırakın)</label>
        <input type="password" name="yeni_sifre" class="form-control" placeholder="••••••••">
      </div>
      <button type="submit" class="btn-register-glow" style="width:100%;">Bilgileri Güncelle</button>
    </form>
  </div>
</div>

<!-- Site Temasıyla Birebir Uyumlu Özel Bildirim, Sertifika & Resmi Cetvel Modalı -->
<div id="martelloNoticeModal" class="martello-modal" onclick="if(event.target===this) closeMartelloNotice();" style="display:none; position:fixed; z-index:10090; inset:0; width:100vw; height:100vh; background:rgba(6,5,4,0.84); backdrop-filter:blur(8px); align-items:center; justify-content:center; padding:20px;">
  <div style="background:#141210; border:1px solid rgba(200,170,110,0.38); border-radius:14px; max-width:460px; width:100%; padding:24px; box-shadow:0 24px 60px rgba(0,0,0,0.9); position:relative;">
    <button type="button" onclick="closeMartelloNotice()" style="position:absolute; right:16px; top:14px; background:none; border:none; color:#A39B8F; font-size:1.35rem; cursor:pointer;">&times;</button>
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:10px; padding-right:20px;">
      <div style="font-family:var(--font-brand); color:#C8AA6E; font-size:0.68rem; font-weight:600; letter-spacing:1.8px; text-transform:uppercase;">MARTELLO · RESMİ BİLDİRİM &amp; BELGE</div>
      <span class="badge badge-gold" style="font-size:0.62rem; padding:2px 7px;">SHA-256 TESCİLLİ</span>
    </div>
    <div id="martelloNoticeText" style="color:#F5F0E6; font-size:0.86rem; line-height:1.65; white-space:pre-line; margin-bottom:18px; padding:12px 14px; background:#0D0B0A; border:1px solid rgba(200,170,110,0.16); border-radius:8px;"></div>
    <div style="display:flex; justify-content:space-between; align-items:center; gap:10px;">
      <button type="button" onclick="window.print()" class="btn-outline-gold btn-sm" style="padding:7px 14px; font-size:0.76rem; cursor:pointer;">
        Yazdır / PDF
      </button>
      <button type="button" onclick="closeMartelloNotice()" class="btn-gold-pill" style="padding:7px 22px; font-size:0.79rem; cursor:pointer;">Tamam</button>
    </div>
  </div>
</div>

<script>
function openAuthModal(tab) {
  const m = document.getElementById('authModal');
  if (m) m.style.display = 'flex';
  if (tab) switchAuthTab(tab);
}
function closeAuthModal() {
  const m = document.getElementById('authModal');
  if (m) m.style.display = 'none';
}
function switchAuthTab(tab) {
  document.getElementById('formLogin').style.display = tab === 'login' ? 'block' : 'none';
  document.getElementById('formRegister').style.display = tab === 'register' ? 'block' : 'none';
  document.getElementById('formForgot').style.display = tab === 'forgot' ? 'block' : 'none';

  document.getElementById('tabBtnLogin').classList.toggle('active', tab === 'login');
  document.getElementById('tabBtnRegister').classList.toggle('active', tab === 'register');
  document.getElementById('tabBtnForgot').classList.toggle('active', tab === 'forgot');
}
function fillDemoLogin(email, pass) {
  const e = document.getElementById('loginEmailInput');
  const p = document.getElementById('loginPasswordInput');
  if (e) e.value = email;
  if (p) p.value = pass;
}
function openProfileModal() {
  const p = document.getElementById('profileModal');
  if (p) p.style.display = 'flex';
}
function closeProfileModal() {
  const p = document.getElementById('profileModal');
  if (p) p.style.display = 'none';
}
function closeMartelloNotice() {
  const n = document.getElementById('martelloNoticeModal');
  if (n) n.style.display = 'none';
}
window.alert = function(msg) {
  const n = document.getElementById('martelloNoticeModal');
  const t = document.getElementById('martelloNoticeText');
  if (n && t) {
    t.innerText = String(msg || '');
    n.style.display = 'flex';
  }
};
</script>

<?php if (!empty($flashMessage)): ?>
  <div class="flash-alert <?= htmlspecialchars($flashMessage['type'] ?? 'info') ?>">
    <div style="display:flex; align-items:center; gap:10px;">
      <span style="color:#E2C992; font-size:0.9rem;">◆</span>
      <div>
        <strong style="color:#E2C992; font-family:var(--font-brand); font-size:0.72rem; letter-spacing:1.2px; text-transform:uppercase; margin-right:6px;">BİLDİRİM</strong>
        <span style="color:#F5F0E6;"><?= htmlspecialchars($flashMessage['text'] ?? '') ?></span>
      </div>
    </div>
    <span style="cursor:pointer; font-size:1.15rem; color:#C8AA6E;" onclick="this.parentElement.style.display='none';">&times;</span>
  </div>
<?php endif; ?>
