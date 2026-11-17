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
      <a href="?role=buyer" class="btn-gold btn-sm" style="padding:8px 18px;">Kayıt Ol / Giriş Yap</a>
    <?php else: ?>
      <!-- Sağ Kullanıcı Rozeti (Görsellerdeki 👤 Emre K. ⌄ Rozeti) -->
      <div class="user-profile-badge" onclick="alert('Kullanıcı: <?= htmlspecialchars($currentUser['name']) ?>\nRol: <?= htmlspecialchars($currentUser['role']) ?>\nDurum: Aktif Oturum')">
        <div class="user-avatar">
          <?= mb_substr($currentUser['name'], 0, 1) ?>
        </div>
        <span style="font-weight:600; font-size:0.85rem; color:#fff;"><?= htmlspecialchars($currentUser['name']) ?></span>
        <span style="font-size:0.75rem; color:var(--text-muted);">⌄</span>
      </div>
    <?php endif; ?>
  </div>
</header>

<?php if (!empty($flashMessage)): ?>
  <div class="flash-alert <?= htmlspecialchars($flashMessage['type'] ?? 'info') ?>">
    <div>
      <strong>[Sistem Bildirimi]</strong> <?= htmlspecialchars($flashMessage['text'] ?? '') ?>
    </div>
    <span style="cursor:pointer; font-size:1.2rem;" onclick="this.parentElement.style.display='none';">&times;</span>
  </div>
<?php endif; ?>
