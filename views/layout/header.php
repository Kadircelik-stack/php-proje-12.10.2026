<?php
declare(strict_types=1);

/** @var array|null $flashMessage */
/** @var string $activeRole */
/** @var string $activeCurrency */
/** @var array $db */

$userRoleMap = [
    'vitrin'    => ['name' => 'Misafir Ziyaretçi', 'role' => 'VİTRİN', 'balance' => 0.0],
    'buyer'     => ['name' => 'Zeynep Demir', 'role' => 'ALICI (GOLD VIP)', 'balance' => 120000.0],
    'seller'    => ['name' => 'Mehmet Kaya', 'role' => 'SATICI', 'balance' => 25000.0],
    'appraiser' => ['name' => 'Burak Şahin', 'role' => 'EKSPERTİZ UZMANI', 'balance' => 12000.0],
    'support'   => ['name' => 'Elif Çelik', 'role' => 'CANLI DESTEK', 'balance' => 0.0],
    'admin'     => ['name' => 'Ahmet Yılmaz', 'role' => 'SİSTEM YÖNETİCİSİ (ADMIN)', 'balance' => 0.0],
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
    <span class="brand-hammer">🔨</span>
    <span style="font-size:0.75rem; color:var(--text-muted); border-left:1px solid var(--border-color); padding-left:10px;">
      ZeroDay • SRS IEEE 830
    </span>
  </div>

  <div class="topbar-nav">
    <div class="role-pills">
      <a href="?role=vitrin&currency=<?= htmlspecialchars($activeCurrency) ?>" class="role-pill <?= $activeRole === 'vitrin' ? 'active' : '' ?>">Vitrin</a>
      <a href="?role=buyer&currency=<?= htmlspecialchars($activeCurrency) ?>" class="role-pill <?= $activeRole === 'buyer' ? 'active' : '' ?>">Alıcı</a>
      <a href="?role=seller&currency=<?= htmlspecialchars($activeCurrency) ?>" class="role-pill <?= $activeRole === 'seller' ? 'active' : '' ?>">Satıcı</a>
      <a href="?role=appraiser&currency=<?= htmlspecialchars($activeCurrency) ?>" class="role-pill <?= $activeRole === 'appraiser' ? 'active' : '' ?>">Ekspertiz</a>
      <a href="?role=support&currency=<?= htmlspecialchars($activeCurrency) ?>" class="role-pill <?= $activeRole === 'support' ? 'active' : '' ?>">Canlı Destek</a>
      <a href="?role=admin&currency=<?= htmlspecialchars($activeCurrency) ?>" class="role-pill <?= $activeRole === 'admin' ? 'active' : '' ?>">Admin</a>
    </div>

    <select id="currencySelector" class="currency-select" title="Para Birimi Değiştir">
      <option value="TRY" <?= $activeCurrency === 'TRY' ? 'selected' : '' ?>>₺ TRY</option>
      <option value="USD" <?= $activeCurrency === 'USD' ? 'selected' : '' ?>>$ USD</option>
      <option value="EUR" <?= $activeCurrency === 'EUR' ? 'selected' : '' ?>>€ EUR</option>
    </select>

    <div style="display:flex; align-items:center; gap:10px; border-left:1px solid var(--border-color); padding-left:14px;">
      <div style="text-align:right;">
        <div style="font-weight:600; font-size:0.85rem; color:#fff;"><?= htmlspecialchars($currentUser['name']) ?></div>
        <div style="font-size:0.75rem; color:var(--gold-bright);"><?= htmlspecialchars($currentUser['role']) ?></div>
      </div>
      <div style="width:36px; height:36px; border-radius:50%; background:var(--bg-card); border:1px solid var(--border-gold); display:flex; align-items:center; justify-content:center; color:var(--gold-primary); font-weight:700;">
        <?= mb_substr($currentUser['name'], 0, 1) ?>
      </div>
    </div>
  </div>
</header>

<?php if (!empty($flashMessage)): ?>
  <div class="flash-alert <?= htmlspecialchars($flashMessage['type'] ?? 'info') ?>">
    <div>
      <strong>[Bildirim]</strong> <?= htmlspecialchars($flashMessage['text'] ?? '') ?>
    </div>
    <span style="cursor:pointer;" onclick="this.parentElement.style.display='none';">&times;</span>
  </div>
<?php endif; ?>
