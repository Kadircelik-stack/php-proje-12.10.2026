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
    'yardim'    => ['name' => 'Ziyaretçi', 'role' => 'YARDIM MERKEZİ', 'balance' => 0.0],
    'help'      => ['name' => 'Ziyaretçi', 'role' => 'YARDIM MERKEZİ', 'balance' => 0.0],
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
    <a href="?role=vitrin&currency=<?= htmlspecialchars($activeCurrency) ?>" style="display:inline-flex; align-items:center; gap:10px; text-decoration:none;">
      <span class="brand-title" style="font-family:var(--font-brand); letter-spacing:2.5px; font-size:1.15rem; color:#f0f3f6; font-weight:600;">MARTELLO</span>
      <span style="color:rgba(255,255,255,0.25); font-weight:300; font-size:1.1rem; margin-top:-2px;">|</span>
      <span class="brand-gavel-icon" style="display:inline-flex; align-items:center;">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#d4af37" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <path d="m14 13-7.5 7.5c-.83.83-2.17.83-3 0 0 0 0 0 0 0a2.12 2.12 0 0 1 0-3L11 10"/>
          <path d="m16 16 6-6"/>
          <path d="m8 8 6-6"/>
          <path d="m9 7 8 8"/>
          <path d="m21 11-8-8"/>
        </svg>
      </span>
    </a>
  </div>

  <!-- Hızlı Eser & Marka Arama Çubuğu -->
  <div class="topbar-search-box" style="flex:1; max-width:360px; margin:0 20px;">
    <form method="GET" action="index.php" style="position:relative; width:100%; display:flex; align-items:center; margin:0;">
      <input type="hidden" name="role" value="<?= $activeRole === 'buyer' ? 'buyer' : 'vitrin' ?>">
      <?php if ($activeRole === 'buyer'): ?>
        <input type="hidden" name="tab" value="arama">
      <?php endif; ?>
      <input type="text" name="q" placeholder="Eser, marka veya anahtar kelime ara..." value="<?= htmlspecialchars((string)($_GET['q'] ?? '')) ?>" style="width:100%; padding:6px 34px 6px 14px; background:rgba(255,255,255,0.05); border:1px solid rgba(197,160,89,0.35); border-radius:20px; color:#f0f3f6; font-size:0.78rem; outline:none; transition:all 0.2s;" onfocus="this.style.borderColor='#d4af37'; this.style.background='rgba(0,0,0,0.5)';" onblur="this.style.borderColor='rgba(197,160,89,0.35)'; this.style.background='rgba(255,255,255,0.05)';">
      <button type="submit" style="position:absolute; right:10px; background:none; border:none; color:#c5a059; cursor:pointer; padding:0; display:flex; align-items:center;" title="Hızlı Ara">
        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
      </button>
    </form>
  </div>

  <!-- Müzayede & Satış Geçmişi Kısayolu -->
  <div style="display:flex; align-items:center; margin-right:8px;">
    <a href="index.php?role=buyer&tab=gecmis&currency=<?= htmlspecialchars($activeCurrency) ?>" style="color:#d4af37; font-size:0.78rem; font-weight:600; text-decoration:none; display:inline-flex; align-items:center; gap:6px; padding:6px 14px; background:rgba(212,175,55,0.08); border:1px solid rgba(212,175,55,0.3); border-radius:18px; white-space:nowrap; transition:all 0.2s;" onmouseover="this.style.background='rgba(212,175,55,0.18)'; this.style.borderColor='#d4af37';" onmouseout="this.style.background='rgba(212,175,55,0.08)'; this.style.borderColor='rgba(212,175,55,0.3)';">
      <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 8v4l3 3"/><circle cx="12" cy="12" r="10"/></svg>
      <span>Müzayede Geçmişi</span>
    </a>
  </div>

  <!-- Yardım Merkezi & Rehber Kısayolu -->
  <div style="display:flex; align-items:center; margin-right:12px;">
    <a href="index.php?role=yardim&currency=<?= htmlspecialchars($activeCurrency) ?>" style="color:#38bdf8; font-size:0.78rem; font-weight:600; text-decoration:none; display:inline-flex; align-items:center; gap:6px; padding:6px 14px; background:rgba(56,189,248,0.08); border:1px solid rgba(56,189,248,0.3); border-radius:18px; white-space:nowrap; transition:all 0.2s;" onmouseover="this.style.background='rgba(56,189,248,0.18)'; this.style.borderColor='#38bdf8';" onmouseout="this.style.background='rgba(56,189,248,0.08)'; this.style.borderColor='rgba(56,189,248,0.3)';">
      <span>❓</span>
      <span>Yardım Merkezi & Destek</span>
    </a>
  </div>

  <div class="topbar-nav">
    <?php if ($activeRole === 'buyer'): ?>
      <div class="topbar-balance" style="font-size:0.85rem; color:#f0f3f6; font-weight:500;">
        Bakiye: <strong style="color:#d4af37; font-weight:700;"><?= $formatPrice(2012100) ?></strong>
      </div>
      <div class="topbar-messages" style="font-size:0.85rem; color:#848d9a;">
        Mesajlar: <strong style="color:#f0f3f6;">0</strong>
      </div>
      <div class="user-avatar-pill" onclick="openProfileModal()" style="display:inline-flex; align-items:center; gap:8px; padding:3px 10px; background:#1b212c; border:1px solid #3d3525; border-radius:20px; cursor:pointer;" title="Kullanıcı Profili">
        <img src="public/assets/images/avatar.jpg" alt="User" style="width:24px; height:24px; border-radius:50%; object-fit:cover; border:1px solid #d4af37;">
        <span style="font-size:0.75rem; color:#d4af37; font-weight:700;">⌄</span>
      </div>
      <button type="button" onclick="openRoleModal()" class="btn-dark-pill" style="padding:7px 16px; font-size:0.82rem; cursor:pointer;">
        Rol değiştir
      </button>
      <select id="currencySelector" class="currency-select" title="Para Birimi Değiştir">
        <option value="TRY" <?= $activeCurrency === 'TRY' ? 'selected' : '' ?>>₺ TRY</option>
        <option value="USD" <?= $activeCurrency === 'USD' ? 'selected' : '' ?>>$ USD</option>
        <option value="EUR" <?= $activeCurrency === 'EUR' ? 'selected' : '' ?>>€ EUR</option>
      </select>
    <?php elseif ($activeRole === 'vitrin'): ?>
      <button type="button" onclick="openAuthModal('login')" class="btn-gold-pill" style="padding:8px 22px; font-size:0.84rem; font-weight:600; cursor:pointer; background:linear-gradient(135deg, #c89e58 0%, #a47937 100%); color:#0c0f14; border:1px solid #d4af37; border-radius:20px; box-shadow:0 2px 10px rgba(197, 160, 89, 0.25);">
        Kayıt Ol / Giriş Yap
      </button>
      <button type="button" onclick="openRoleModal()" class="btn-dark-pill" style="padding:7px 16px; font-size:0.82rem; cursor:pointer;" title="Rol Değiştir (Demo)">
        Rol değiştir
      </button>
      <select id="currencySelector" class="currency-select" title="Para Birimi Değiştir">
        <option value="TRY" <?= $activeCurrency === 'TRY' ? 'selected' : '' ?>>₺ TRY</option>
        <option value="USD" <?= $activeCurrency === 'USD' ? 'selected' : '' ?>>$ USD</option>
        <option value="EUR" <?= $activeCurrency === 'EUR' ? 'selected' : '' ?>>€ EUR</option>
      </select>
    <?php else: ?>
      <button type="button" onclick="openAuthModal('login')" class="btn-gold-pill" style="padding:7px 18px; font-size:0.82rem; font-weight:600; cursor:pointer;">
        Kayıt Ol / Giriş Yap
      </button>
      <button type="button" onclick="openRoleModal()" class="btn-dark-pill" style="padding:7px 18px; font-size:0.82rem; cursor:pointer;">
        Rol değiştir
      </button>
      <select id="currencySelector" class="currency-select" title="Para Birimi Değiştir">
        <option value="TRY" <?= $activeCurrency === 'TRY' ? 'selected' : '' ?>>₺ TRY</option>
        <option value="USD" <?= $activeCurrency === 'USD' ? 'selected' : '' ?>>$ USD</option>
        <option value="EUR" <?= $activeCurrency === 'EUR' ? 'selected' : '' ?>>€ EUR</option>
      </select>
    <?php endif; ?>
  </div>

<!-- Bilgi ve Şartlar Modalı (Tüm Pazar Yeri & Gizlilik Seçenekleri) -->
<div id="infoModal" class="martello-modal" style="display:none; position:fixed; z-index:9999; left:0; top:0; width:100%; height:100%; background:rgba(0,0,0,0.85); backdrop-filter:blur(6px); align-items:center; justify-content:center;">
  <div style="background:#12161f; border:1px solid #2a3449; border-radius:12px; max-width:540px; width:90%; max-height:85vh; overflow-y:auto; padding:26px; box-shadow:0 12px 40px rgba(0,0,0,0.85); position:relative;">
    <button type="button" onclick="closeInfoModal()" style="position:absolute; right:16px; top:16px; background:none; border:none; color:#888; font-size:1.6rem; cursor:pointer; line-height:1;">&times;</button>
    <div id="infoModalBadge" style="color:var(--gold-primary); font-size:0.75rem; font-weight:700; letter-spacing:1px; text-transform:uppercase; margin-bottom:4px;">MARTELLO BİLGİ BANKASI</div>
    <h3 id="infoModalTitle" style="color:#fff; font-size:1.35rem; margin-bottom:12px;">Başlık</h3>
    <div id="infoModalBody" style="color:#abb2bf; font-size:0.88rem; line-height:1.6;">İçerik</div>
  </div>
</div>

<script>
function openInfoModal(type) {
  const titles = {
    'about': 'Hakkımızda',
    'terms': 'Müzayede Şartları & Pey Kuralları',
    'privacy': 'Gizlilik Politikası & KVK',
    'help': 'Yardım Merkezi & Sıkça Sorulan Sorular',
    'categories': 'Eser Kategorileri & Seçki Rehberi'
  };
  const contents = {
    'about': '<p>MARTELLO, Türkiye\'nin seçkin antika, horoloji, filateli ve sanat koleksiyonları için geliştirilmiş bağımsız çevrimiçi müzayede platformudur. Tüm eserler lisanslı eksper onayından ve seçki damgasından geçirilmektedir.</p><p style="margin-top:10px; font-size:0.8rem; color:#888;">Geliştirici Ekip: ZeroDay (Esra Yıldırım, Abdullah Kadir Çelik, Rümeysa Şevval Kandilci)</p>',
    'terms': '<p><strong>1. Asgari Pey Kuralı:</strong> Her lot için minimum artış tutarı tanımlıdır; altında pey kabul edilmez.</p><p style="margin-top:8px;"><strong>2. 60 Saniye Anti-Sniping Kuralı:</strong> Kapanışa son 60 saniye kala verilen peyler süreyi 60 saniye uzatır.</p><p style="margin-top:8px;"><strong>3. Emanet & Teminat:</strong> Ödemeler teslimat ve alıcı onayı gerçekleşene kadar emanet havuzunda bloke tutulur.</p>',
    'privacy': '<p>6698 Sayılı Kişisel Verilerin Korunması Kanunu (KVKK) uyarınca verileriniz ve pey loglarınız 256-bit SSL ve SHA-256 denetim iziyle korunmaktadır.</p><p style="margin-top:8px;">Canlı açık artırmalarda alıcı gizliliği gereği tüm katılımcılar anonim rumuzlarla listelenir.</p>',
    'help': '<p><strong>Nasıl Pey Veririm?</strong> Cüzdanınıza bakiye yükledikten sonra canlı müzayede ekranından dilediğiniz tutarda veya hızlı butonlarla (+1.500, +3.000) pey verebilirsiniz.</p><p style="margin-top:8px;"><strong>Otomatik Teklif Nedir?</strong> Tavan bütçenizi girerek sistemin siz çevrimdışıyken bile rakip peylerin bir adım üzerine çıkmasını sağlayabilirsiniz.</p>',
    'categories': '<p>Platformumuz Horoloji (Vintage Mekanik Saatler), Antika & Sanat, Osmanlı Dönemi Koleksiyonları, Filateli ve Nümismatik ana kategorilerinde hizmet vermektedir.</p>'
  };
  const t = document.getElementById('infoModalTitle');
  const b = document.getElementById('infoModalBody');
  if (t) t.innerText = titles[type] || 'Bilgilendirme';
  if (b) b.innerHTML = contents[type] || '';
  const m = document.getElementById('infoModal');
  if (m) m.style.display = 'flex';
}
function closeInfoModal() {
  const m = document.getElementById('infoModal');
  if (m) m.style.display = 'none';
}
</script>

</header>

<!-- Rol Değiştirme Modalı -->
<div id="roleModal" class="martello-modal" style="display:none; position:fixed; z-index:9999; left:0; top:0; width:100%; height:100%; background:rgba(0,0,0,0.85); backdrop-filter:blur(6px); align-items:center; justify-content:center;">
  <div style="background:#12161f; border:1px solid #2a3449; border-radius:12px; max-width:540px; width:92%; padding:24px; box-shadow:0 10px 30px rgba(0,0,0,0.8); position:relative;">
    <button type="button" onclick="closeRoleModal()" style="position:absolute; right:16px; top:16px; background:none; border:none; color:#888; font-size:1.5rem; cursor:pointer;">&times;</button>
    
    <div style="color:#d4af37; font-size:0.75rem; font-weight:700; letter-spacing:1px; text-transform:uppercase; margin-bottom:4px;">MARTELLO • DEMO İŞLEM</div>
    <div style="font-family:var(--font-serif); font-size:1.45rem; font-weight:600; color:#fff; margin-bottom:6px;">Rol Değiştir</div>
    <div style="color:#888; font-size:0.82rem; margin-bottom:20px;">Geçmek istediğiniz kullanıcı rolünü ve çalışma alanını seçin:</div>

    <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px;">
      <a href="?role=vitrin&currency=<?= htmlspecialchars($activeCurrency) ?>" style="display:block; padding:14px; background:#181d28; border:1px solid <?= $activeRole === 'vitrin' ? '#d4af37' : '#263042' ?>; border-radius:8px; text-decoration:none;">
        <div style="color:#d4af37; margin-bottom:6px;">
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="2" x2="22" y1="12" y2="12"/><path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/></svg>
        </div>
        <div style="font-weight:600; color:#fff; font-size:0.9rem;">Vitrin (Keşfet)</div>
        <div style="font-size:0.75rem; color:#848d9a;">Müzayede kataloğu ve lot listesi</div>
      </a>

      <a href="?role=buyer&currency=<?= htmlspecialchars($activeCurrency) ?>" style="display:block; padding:14px; background:#181d28; border:1px solid <?= $activeRole === 'buyer' ? '#d4af37' : '#263042' ?>; border-radius:8px; text-decoration:none;">
        <div style="color:#d4af37; margin-bottom:6px;">
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M6 9H4.5a2.5 2.5 0 0 1 0-5H6"/><path d="M18 9h1.5a2.5 2.5 0 0 0 0-5H18"/><path d="M4 22h16"/><path d="M10 14.66V17c0 .55-.47.98-.97 1.21C7.85 18.75 7 20.24 7 22"/><path d="M14 14.66V17c0 .55.47.98.97 1.21C16.15 18.75 17 20.24 17 22"/><path d="M18 2H6v7a6 6 0 0 0 12 0V2Z"/></svg>
        </div>
        <div style="font-weight:600; color:#fff; font-size:0.9rem;">Alıcı Paneli</div>
        <div style="font-size:0.75rem; color:#848d9a;">Kazanılan lotlar, pey verme, bakiye</div>
      </a>

      <a href="?role=seller&currency=<?= htmlspecialchars($activeCurrency) ?>" style="display:block; padding:14px; background:#181d28; border:1px solid <?= $activeRole === 'seller' ? '#d4af37' : '#263042' ?>; border-radius:8px; text-decoration:none;">
        <div style="color:#d4af37; margin-bottom:6px;">
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect width="20" height="14" x="2" y="7" rx="2" ry="2"/><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/></svg>
        </div>
        <div style="font-weight:600; color:#fff; font-size:0.9rem;">Satıcı Paneli</div>
        <div style="font-size:0.75rem; color:#848d9a;">İlanlarım, yeni ürün ekle, satışlar</div>
      </a>

      <a href="?role=appraiser&currency=<?= htmlspecialchars($activeCurrency) ?>" style="display:block; padding:14px; background:#181d28; border:1px solid <?= $activeRole === 'appraiser' ? '#d4af37' : '#263042' ?>; border-radius:8px; text-decoration:none;">
        <div style="color:#d4af37; margin-bottom:6px;">
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><line x1="21" x2="16.65" y1="21" y2="16.65"/></svg>
        </div>
        <div style="font-weight:600; color:#fff; font-size:0.9rem;">Ekspertiz Uzmanı</div>
        <div style="font-size:0.75rem; color:#848d9a;">Görev paneli, rapor oluşturma</div>
      </a>

      <a href="?role=support&currency=<?= htmlspecialchars($activeCurrency) ?>" style="display:block; padding:14px; background:#181d28; border:1px solid <?= $activeRole === 'support' ? '#d4af37' : '#263042' ?>; border-radius:8px; text-decoration:none;">
        <div style="color:#d4af37; margin-bottom:6px;">
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
        </div>
        <div style="font-weight:600; color:#fff; font-size:0.9rem;">Canlı Destek</div>
        <div style="font-size:0.75rem; color:#848d9a;">Müşteri mesajları ve canlı yardım</div>
      </a>

      <a href="?role=admin&currency=<?= htmlspecialchars($activeCurrency) ?>" style="display:block; padding:14px; background:#181d28; border:1px solid <?= $activeRole === 'admin' ? '#d4af37' : '#263042' ?>; border-radius:8px; text-decoration:none;">
        <div style="color:#d4af37; margin-bottom:6px;">
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"/></svg>
        </div>
        <div style="font-weight:600; color:#fff; font-size:0.9rem;">Admin Konsolu</div>
        <div style="font-size:0.75rem; color:#848d9a;">Yönetim konsolu, moderasyon, denetim</div>
      </a>

      <a href="?role=yardim&currency=<?= htmlspecialchars($activeCurrency) ?>" style="display:block; padding:14px; background:#181d28; border:1px solid <?= $activeRole === 'yardim' ? '#d4af37' : '#263042' ?>; border-radius:8px; text-decoration:none; grid-column:span 2;">
        <div style="color:#38bdf8; margin-bottom:6px; display:flex; align-items:center; gap:8px;">
          <span style="font-size:1.2rem;">❓</span>
          <span style="font-weight:600; color:#fff; font-size:0.92rem;">Yardım Merkezi & Destek Masası</span>
          <span class="badge badge-gold" style="font-size:0.65rem; margin-left:auto;">Rehber • Kurallar • SSS • Canlı Destek</span>
        </div>
        <div style="font-size:0.75rem; color:#848d9a;">Sistem kuralları, açık artırma kılavuzu, SSS arama ve canlı/bilet destek kanalı</div>
      </a>
    </div>
  </div>
</div>

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
      <div style="font-size:0.8rem; color:#94a3b8; margin-bottom:14px; line-height:1.4; display:flex; align-items:center; gap:6px;">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#d4af37" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/></svg>
        <span>Şifre sıfırlama bağlantısı en geç 30 saniye içinde e-posta kutunuza ulaştırılır.</span>
      </div>
      <button type="submit" class="btn-register-glow" style="width:100%;">Şifre Sıfırlama Bağlantısı Gönder</button>
    </form>
  </div>
</div>

<!-- Profil Güncelleme Modalı -->
<div id="profileModal" class="martello-modal" style="display:none; position:fixed; z-index:9999; left:0; top:0; width:100%; height:100%; background:rgba(0,0,0,0.85); backdrop-filter:blur(6px); align-items:center; justify-content:center;">
  <div style="background:#12161f; border:1px solid #2a3449; border-radius:12px; max-width:480px; width:90%; padding:24px; box-shadow:0 10px 30px rgba(0,0,0,0.8); position:relative;">
    <button type="button" onclick="closeProfileModal()" style="position:absolute; right:16px; top:16px; background:none; border:none; color:#888; font-size:1.5rem; cursor:pointer;">&times;</button>
    
    <div style="color:var(--gold-primary); font-size:1.15rem; font-weight:700; margin-bottom:6px;">Profil ve Hesap Ayarları</div>
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
function openRoleModal() {
  const r = document.getElementById('roleModal');
  if (r) r.style.display = 'flex';
}
function closeRoleModal() {
  const r = document.getElementById('roleModal');
  if (r) r.style.display = 'none';
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
