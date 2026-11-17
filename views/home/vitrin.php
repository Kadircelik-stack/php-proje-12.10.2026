<?php
declare(strict_types=1);

/** @var callable $formatPrice */
/** @var array $db */

$categories = $db['categories'] ?? [];
$lots = $db['lots'] ?? [];
?>

<!-- Vitrin Hero Bölümü (SRS Sayfa 32 — media_1794924544624_e0391dd8.jpg ile Birebir) -->
<div class="vitrin-hero-container">
  <div class="astrolabe-bg"></div>
  
  <div class="hero-ornate-box">
    <div class="hero-tag">MARTELLO - SEÇKİN KOLEKSİYONLAR</div>
    <h1 class="hero-title">Saatler vardır, anıları<br>hatırlatır.</h1>
    <p class="hero-desc">
      Seçkin koleksiyonları keşfedin, değerli parçalar için güvenle teklif verin.
    </p>
    <a href="?role=buyer" class="btn-gold" style="padding:12px 28px; font-size:0.95rem;">
      Müzayedeleri Keşfet ↗
    </a>
  </div>
</div>

<!-- Koleksiyonları Keşfedin Bölümü -->
<main style="max-width:1240px; margin:0 auto; padding:48px 36px; width:100%;">
  <div style="display:flex; justify-content:space-between; align-items:flex-end;">
    <div>
      <div class="category-section-title">KOLEKSİYONLARI KEŞFEDİN</div>
      <h2 class="category-section-heading">Her parça bir hikâye.</h2>
    </div>
    <div style="display:flex; gap:8px;">
      <button type="button" class="btn-outline-gold btn-sm" style="padding:8px 14px;" onclick="alert('Önceki koleksiyonlar')">←</button>
      <button type="button" class="btn-outline-gold btn-sm" style="padding:8px 14px;" onclick="alert('Sonraki koleksiyonlar')">→</button>
    </div>
  </div>

  <!-- 4 Adet Kategori Kartı (Görseldeki Gibi) -->
  <div class="category-cards-grid">
    <!-- 1. Efemera -->
    <div class="category-item-card" onclick="window.location.href='?role=buyer'">
      <div class="category-image-wrap">
        <div style="font-size:3.5rem;">📜</div>
        <div style="position:absolute; bottom:8px; right:12px; font-size:0.75rem; color:var(--text-muted);">Tarihi Harita & Yazışmalar</div>
      </div>
      <div class="category-label-footer">Efemera</div>
    </div>

    <!-- 2. Filateli -->
    <div class="category-item-card" onclick="window.location.href='?role=buyer'">
      <div class="category-image-wrap">
        <div style="font-size:3.5rem;">📮</div>
        <div style="position:absolute; bottom:8px; right:12px; font-size:0.75rem; color:var(--text-muted);">Nadir Posta Pulları</div>
      </div>
      <div class="category-label-footer">Filateli</div>
    </div>

    <!-- 3. Osmanlı Eserleri -->
    <div class="category-item-card" onclick="window.location.href='?role=buyer'">
      <div class="category-image-wrap">
        <div style="font-size:3.5rem;">⚔️</div>
        <div style="position:absolute; bottom:8px; right:12px; font-size:0.75rem; color:var(--text-muted);">Saray İşi Kılıç & Tombak</div>
      </div>
      <div class="category-label-footer">Osmanlı Eserleri</div>
    </div>

    <!-- 4. Nümismatik -->
    <div class="category-item-card" onclick="window.location.href='?role=buyer'">
      <div class="category-image-wrap">
        <div style="font-size:3.5rem;">🪙</div>
        <div style="position:absolute; bottom:8px; right:12px; font-size:0.75rem; color:var(--text-muted);">Altın & Gümüş Sikkeler</div>
      </div>
      <div class="category-label-footer">Nümismatik</div>
    </div>
  </div>

  <!-- Canlı Müzayede Önizleme Listesi -->
  <div style="margin-top:60px;">
    <div class="panel-header">
      <div>
        <div class="category-section-title">CANLI MÜZAYEDELER</div>
        <h3 class="panel-title" style="margin-top:4px;">Açık Artırmadaki Seçkin Eserler</h3>
      </div>
      <a href="?role=buyer" class="btn-outline-gold btn-sm">Tüm Lotları Gör →</a>
    </div>

    <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(320px, 1fr)); gap:24px;">
      <?php foreach ($lots as $lot): ?>
        <div class="panel-card" style="margin-bottom:0; display:flex; flex-direction:column; justify-content:space-between;">
          <div>
            <div style="display:flex; justify-content:space-between; margin-bottom:12px;">
              <span class="badge badge-gold"><?= htmlspecialchars($lot['lotKodu']) ?></span>
              <span class="badge <?= $lot['durum'] === 'ACIK_ARTIRMADA' ? 'badge-success' : 'badge-warning' ?>">
                <?= htmlspecialchars($lot['durum']) ?>
              </span>
            </div>
            <h4 style="font-family:var(--font-serif); font-size:1.25rem; color:#fff; margin-bottom:6px;">
              <?= htmlspecialchars($lot['baslik']) ?>
            </h4>
            <div style="color:var(--text-muted); font-size:0.85rem; margin-bottom:16px;">
              Kategori: <?= htmlspecialchars($lot['kategoriAdi'] ?? 'Antika') ?>
            </div>
          </div>

          <div style="border-top:1px solid var(--border-color); padding-top:14px; display:flex; justify-content:space-between; align-items:center;">
            <div>
              <div style="font-size:0.75rem; color:var(--text-muted);">Güncel Teklif</div>
              <div style="font-family:var(--font-serif); font-size:1.4rem; font-weight:700; color:var(--gold-bright);">
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
