<?php
declare(strict_types=1);

/** @var array|null $flashMessage */
/** @var string $activeRole */
/** @var string $activeCurrency */
/** @var array $db */

$userRoleMap = [
    'vitrin'    => ['name' => 'Misafir', 'role' => 'VİTRİN', 'balance' => 0.0],
    'buyer'     => ['name' => 'Emre K.', 'role' => 'ALICI', 'balance' => 120000.0],
    'seller'    => ['name' => 'Mehmet Kaya', 'role' => 'SATICI', 'balance' => 25000.0],
    'appraiser' => ['name' => 'Burak Şahin', 'role' => 'EKSPERTİZ', 'balance' => 12000.0],
    'support'   => ['name' => 'Elif Çelik', 'role' => 'CANLI DESTEK', 'balance' => 0.0],
    'admin'     => ['name' => 'Ahmet Yılmaz', 'role' => 'ADMIN', 'balance' => 0.0],
];
$currentUser = $userRoleMap[$activeRole] ?? $userRoleMap['buyer'];
?>
<!DOCTYPE html>
<html lang="tr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>MARTELLO — Çevrimiçi Müzayede ve Ekspertiz Platformu (ZeroDay)</title>
  <link rel="stylesheet" href="public/assets/css/martello.css">
</head>
<body>

<header class="martello-topbar">
  <div class="brand-wrap">
    <a href="?role=vitrin" class="brand-title">MARTELLO</a>
  </div>

  <div class="topbar-nav">
    <!-- Rol Değiştirici Piller (Test ve Sunum Geçişleri) -->
    <div class="role-pills">
      <a href="?role=vitrin&currency=<?= htmlspecialchars($activeCurrency) ?>" class="role-pill <?= $activeRole === 'vitrin' ? 'active' : '' ?>">Vitrin</a>
      <a href="?role=buyer&currency=<?= htmlspecialchars($activeCurrency) ?>" class="role-pill <?= $activeRole === 'buyer' ? 'active' : '' ?>">Alıcı</a>
      <a href="?role=seller&currency=<?= htmlspecialchars($activeCurrency) ?>" class="role-pill <?= $activeRole === 'seller' ? 'active' : '' ?>">Satıcı</a>
      <a href="?role=appraiser&currency=<?= htmlspecialchars($activeCurrency) ?>" class="role-pill <?= $activeRole === 'appraiser' ? 'active' : '' ?>">Ekspertiz</a>
      <a href="?role=support&currency=<?= htmlspecialchars($activeCurrency) ?>" class="role-pill <?= $activeRole === 'support' ? 'active' : '' ?>">Canlı Destek</a>
      <a href="?role=admin&currency=<?= htmlspecialchars($activeCurrency) ?>" class="role-pill <?= $activeRole === 'admin' ? 'active' : '' ?>">Admin</a>
    </div>

    <!-- Para Birimi Seçici (TRY / USD / EUR) -->
    <select id="currencySelector" class="currency-select" title="Para Birimi Değiştir">
      <option value="TRY" <?= $activeCurrency === 'TRY' ? 'selected' : '' ?>>₺ TRY</option>
      <option value="USD" <?= $activeCurrency === 'USD' ? 'selected' : '' ?>>$ USD</option>
      <option value="EUR" <?= $activeCurrency === 'EUR' ? 'selected' : '' ?>>€ EUR</option>
    </select>

    <?php if ($activeRole === 'vitrin'): ?>
      <button type="button" onclick="openAuthModal('login')" class="btn-gold btn-sm" style="padding:8px 18px; cursor:pointer;">Kayıt Ol / Giriş Yap</button>
    <?php else: ?>
      <!-- Sağ Kullanıcı Rozeti (Görsellerdeki 👤 Emre K. ⌄ Rozeti) -->
      <div class="user-profile-badge" onclick="openProfileModal()" style="cursor:pointer;" title="Profilinizi ve hesap ayarlarınızı görüntüleyin">
        <div class="user-avatar">
          <?= mb_substr($currentUser['name'], 0, 1) ?>
        </div>
        <span style="font-weight:600; font-size:0.85rem; color:#fff;"><?= htmlspecialchars($currentUser['name']) ?></span>
        <span style="font-size:0.75rem; color:var(--text-muted);">⌄</span>
      </div>
    <?php endif; ?>
  </div>
</header>

<!-- Kimlik Doğrulama Modalı -->
<div id="authModal" class="martello-modal" style="display:none; position:fixed; z-index:9999; left:0; top:0; width:100%; height:100%; background:rgba(0,0,0,0.85); backdrop-filter:blur(6px); align-items:center; justify-content:center;">
  <div style="background:#12161f; border:1px solid #2a3449; border-radius:12px; max-width:480px; width:90%; padding:24px; box-shadow:0 10px 30px rgba(0,0,0,0.8); position:relative;">
    <button type="button" onclick="closeAuthModal()" style="position:absolute; right:16px; top:16px; background:none; border:none; color:#888; font-size:1.5rem; cursor:pointer;">&times;</button>
    
    <div style="display:flex; gap:10px; border-bottom:1px solid #2a3449; padding-bottom:12px; margin-bottom:16px;">
      <button type="button" id="tabBtnLogin" onclick="switchAuthTab('login')" class="role-pill active">Giriş Yap</button>
      <button type="button" id="tabBtnRegister" onclick="switchAuthTab('register')" class="role-pill">Kayıt Ol</button>
      <button type="button" id="tabBtnForgot" onclick="switchAuthTab('forgot')" class="role-pill">Şifremi Unuttum</button>
    </div>

    <!-- Giriş Formu -->
    <form id="formLogin" method="POST" style="display:block;">
      <input type="hidden" name="action" value="login">
      <div class="form-group" style="margin-bottom:12px;">
        <label class="form-label">E-Posta Adresi</label>
        <input type="email" name="email" class="form-control" placeholder="ornek@martello.com" value="alici@martello.com" required>
      </div>
      <div class="form-group" style="margin-bottom:16px;">
        <label class="form-label">Şifre</label>
        <input type="password" name="password" class="form-control" placeholder="••••••••" value="123456" required>
      </div>
      <button type="submit" class="btn-register-glow" style="width:100%;">Güvenli Giriş Yap</button>
      <div style="text-align:center; margin-top:10px; font-size:0.8rem; color:#888;">
        Hesabınız askıya alınmışsa sistem otomatik olarak erişimi engeller.
      </div>
    </form>

    <!-- Kayıt Formu -->
    <form id="formRegister" method="POST" style="display:none;">
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
      <div style="display:grid; grid-template-columns:1fr 1fr; gap:10px; margin-bottom:12px;">
        <div class="form-group">
          <label class="form-label">Rolünüz</label>
          <select name="rol" class="form-control">
            <option value="ALICI">Alıcı (Müzayede Katılımcısı)</option>
            <option value="SATICI">Satıcı (Eser Arz Eden)</option>
          </select>
        </div>
        <div class="form-group">
          <label class="form-label">Şifre</label>
          <input type="password" name="sifre" class="form-control" placeholder="En az 6 karakter" required>
        </div>
      </div>
      <div style="margin-bottom:14px; font-size:0.78rem; color:#aaa; display:flex; gap:8px;">
        <input type="checkbox" id="kvkkCheck" required style="accent-color:var(--gold-primary); margin-top:3px;">
        <label for="kvkkCheck">Mesafeli Satış Sözleşmesi ve KVKK Gizlilik Şartlarını kabul ediyorum.</label>
      </div>
      <button type="submit" class="btn-register-glow" style="width:100%;">Hesap Oluştur ve KYC'ye Aktar</button>
    </form>

    <!-- Şifremi Unuttum Formu -->
    <form id="formForgot" method="POST" style="display:none;">
      <input type="hidden" name="action" value="reset_password">
      <div class="form-group" style="margin-bottom:14px;">
        <label class="form-label">Kayıtlı E-Posta Adresiniz</label>
        <input type="email" name="email" class="form-control" placeholder="ornek@martello.com" required>
      </div>
      <div style="font-size:0.8rem; color:#94a3b8; margin-bottom:14px; line-height:1.4;">
        ℹ️ Şifre sıfırlama bağlantısı en geç 30 saniye içinde e-posta kutunuza ulaştırılır.
      </div>
      <button type="submit" class="btn-register-glow" style="width:100%;">Şifre Sıfırlama Bağlantısı Gönder</button>
    </form>
  </div>
</div>

<!-- Profil Güncelleme Modalı -->
<div id="profileModal" class="martello-modal" style="display:none; position:fixed; z-index:9999; left:0; top:0; width:100%; height:100%; background:rgba(0,0,0,0.85); backdrop-filter:blur(6px); align-items:center; justify-content:center;">
  <div style="background:#12161f; border:1px solid #2a3449; border-radius:12px; max-width:480px; width:90%; padding:24px; box-shadow:0 10px 30px rgba(0,0,0,0.8); position:relative;">
    <button type="button" onclick="closeProfileModal()" style="position:absolute; right:16px; top:16px; background:none; border:none; color:#888; font-size:1.5rem; cursor:pointer;">&times;</button>
    
    <div style="color:var(--gold-primary); font-size:1.15rem; font-weight:700; margin-bottom:6px;">👤 Profil ve Hesap Ayarları</div>
    <div style="color:#888; font-size:0.8rem; margin-bottom:16px;">İletişim, adres ve faturalandırma bilgilerinizi buradan güncelleyebilirsiniz.</div>

    <form method="POST">
      <input type="hidden" name="action" value="update_profile">
      <input type="hidden" name="user_id" value="3">
      <div style="display:grid; grid-template-columns:1fr 1fr; gap:10px; margin-bottom:10px;">
        <div class="form-group">
          <label class="form-label">Ad</label>
          <input type="text" name="ad" class="form-control" value="Emre" required>
        </div>
        <div class="form-group">
          <label class="form-label">Soyad</label>
          <input type="text" name="soyad" class="form-control" value="K." required>
        </div>
      </div>
      <div class="form-group" style="margin-bottom:10px;">
        <label class="form-label">Telefon Numarası</label>
        <input type="tel" name="telefon" class="form-control" value="+90 532 100 20 30">
      </div>
      <div class="form-group" style="margin-bottom:10px;">
        <label class="form-label">Teslimat Adresi</label>
        <input type="text" name="adres" class="form-control" value="Levent Mah. Cömert Sok. No:12 Beşiktaş / İstanbul">
      </div>
      <div class="form-group" style="margin-bottom:10px;">
        <label class="form-label">Fatura Bilgisi</label>
        <input type="text" name="fatura_bilgisi" class="form-control" value="Emre K. — Bireysel Fatura">
      </div>
      <div class="form-group" style="margin-bottom:16px;">
        <label class="form-label">Yeni Şifre (Değiştirmek İstemiyorsanız Boş Bırakın)</label>
        <input type="password" name="yeni_sifre" class="form-control" placeholder="••••••••">
      </div>
      <button type="submit" class="btn-register-glow" style="width:100%;">Bilgileri Güncelle</button>
    </form>
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
function openProfileModal() {
  const p = document.getElementById('profileModal');
  if (p) p.style.display = 'flex';
}
function closeProfileModal() {
  const p = document.getElementById('profileModal');
  if (p) p.style.display = 'none';
}
</script>

<?php if (!empty($flashMessage)): ?>
  <div class="flash-alert <?= htmlspecialchars($flashMessage['type'] ?? 'info') ?>">
    <div>
      <strong>[Sistem Bildirimi]</strong> <?= htmlspecialchars($flashMessage['text'] ?? '') ?>
    </div>
    <span style="cursor:pointer; font-size:1.2rem;" onclick="this.parentElement.style.display='none';">&times;</span>
  </div>
<?php endif; ?>
