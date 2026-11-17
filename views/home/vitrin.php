<?php
declare(strict_types=1);

/** @var callable $formatPrice */
/** @var array $db */

$categories = $db['categories'] ?? [];
$lots = $db['lots'] ?? [];
?>

<div class="vitrin-hero">
  <div class="vitrin-badge">MARTELLO • SEÇKİN KOLEKSİYONLAR</div>
  <h1 class="vitrin-title">Saatler vardır, anıları<br>hatırlatır.</h1>
  <p class="vitrin-desc">
    Yüksek değerli antika, saat ve sanat eserlerinin bağımsız akredite eksperlerce incelendiği, şeffaf ve güvenli çevrimiçi açık artırma platformu.
  </p>
  <div style="display:flex; justify-content:center; gap:16px;">
    <a href="?role=buyer" class="btn-gold" style="font-size:1rem; padding:12px 28px;">
      Müzayedeleri Keşfet &rarr;
    </a>
    <a href="?role=seller" class="btn-outline-gold" style="font-size:1rem; padding:12px 28px;">
      Eserini Satışa Çıkar
    </a>
  </div>
</div>

<main style="max-width:1200px; margin:0 auto; padding:40px 24px; width:100%;">
  <div style="display:flex; justify-content:space-between; align-items:flex-end; margin-bottom:24px;">
    <div>
      <div style="color:var(--gold-primary); font-size:0.8rem; font-weight:700; text-transform:uppercase; letter-spacing:1px;">Koleksiyonları Keşfedin</div>
      <h2 style="font-size:1.8rem; color:#fff; font-weight:400;">Her parça bir hikâye.</h2>
    </div>
    <div style="color:var(--text-muted); font-size:0.9rem;">
      Tüm Kategoriler (<?= count($categories) ?>)
    </div>
  </div>

  <div class="category-tiles">
    <div class="category-card" onclick="window.location.href='?role=buyer'">
      <div class="category-card-icon">📜</div>
      <div class="category-card-name">Efemera</div>
      <p style="color:var(--text-muted); font-size:0.8rem; margin-top:6px;">Tarihi belgeler, nadir harita ve gravürler</p>
    </div>
    <div class="category-card" onclick="window.location.href='?role=buyer'">
      <div class="category-card-icon">📮</div>
      <div class="category-card-name">Filateli</div>
      <p style="color:var(--text-muted); font-size:0.8rem; margin-top:6px;">Nadir damgalı/damgasız posta pulları</p>
    </div>
    <div class="category-card" onclick="window.location.href='?role=buyer'">
      <div class="category-card-icon">⚔️</div>
      <div class="category-card-name">Osmanlı Eserleri</div>
      <p style="color:var(--text-muted); font-size:0.8rem; margin-top:6px;">Saray işi tombaklar, halılar ve kılıçlar</p>
    </div>
    <div class="category-card" onclick="window.location.href='?role=buyer'">
      <div class="category-card-icon">🪙</div>
      <div class="category-card-name">Nümismatik</div>
      <p style="color:var(--text-muted); font-size:0.8rem; margin-top:6px;">Antik sikkeler ve altın/gümüş madalyonlar</p>
    </div>
  </div>

  <div style="margin-top:54px;">
    <div class="panel-header">
      <h3 class="panel-title">🔥 Öne Çıkan Canlı Müzayede Lotları</h3>
      <a href="?role=buyer" class="btn-outline-gold btn-sm">Tümünü İncele</a>
    </div>

    <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(280px, 1fr)); gap:24px;">
      <?php foreach ($lots as $lot): ?>
        <div class="panel-card" style="margin-bottom:0; display:flex; flex-direction:column; justify-content:space-between;">
          <div>
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:12px;">
              <span class="badge badge-gold"><?= htmlspecialchars($lot['lotKodu']) ?></span>
              <span class="badge <?= $lot['durum'] === 'ACIK_ARTIRMADA' ? 'badge-success' : 'badge-warning' ?>">
                <?= htmlspecialchars($lot['durum']) ?>
              </span>
            </div>
            <h4 style="font-size:1.15rem; color:#fff; margin-bottom:8px;"><?= htmlspecialchars($lot['baslik']) ?></h4>
            <div style="color:var(--text-muted); font-size:0.85rem; margin-bottom:14px;"><?= htmlspecialchars($lot['kategoriAdi'] ?? 'Antika') ?></div>
          </div>

          <div style="border-top:1px solid rgba(255,255,255,0.06); padding-top:14px; display:flex; justify-content:space-between; align-items:center;">
            <div>
              <div style="font-size:0.75rem; color:var(--text-muted);">Güncel Teklif</div>
              <div style="font-size:1.3rem; font-weight:700; color:var(--gold-bright);">
                <?= $formatPrice((float)$lot['mevcutEnYuksekTeklif']) ?>
              </div>
            </div>
            <a href="?role=buyer&lot_id=<?= $lot['id'] ?>" class="btn-gold btn-sm">
              Pey Ver &rarr;
            </a>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</main>
