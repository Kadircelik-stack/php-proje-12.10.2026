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
    <div style="display:flex; gap:12px; align-items:center;">
      <a href="?role=buyer" class="btn-gold" style="padding:12px 28px; font-size:0.95rem;">
        Müzayedeleri Keşfet ↗
      </a>
      <button type="button" onclick="document.getElementById('searchFilterSection').scrollIntoView({behavior:'smooth'})" class="btn-outline-gold" style="padding:12px 20px; font-size:0.95rem; display:inline-flex; align-items:center; gap:6px;">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
        <span>Eser Ara & Filtrele</span>
      </button>
    </div>
  </div>
</div>

<!-- Arama ve Filtreleme Bölümü -->
<section id="searchFilterSection" style="max-width:1240px; margin:0 auto; padding:24px 36px 0 36px; width:100%;">
  <div class="panel-card" style="margin-bottom:0; background:rgba(18,22,31,0.85); border:1px solid #2a3449;">
    <div style="font-size:0.85rem; color:var(--gold-primary); font-weight:700; text-transform:uppercase; letter-spacing:1px; margin-bottom:8px; display:flex; align-items:center; gap:6px;">
      <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
      <span>Ürün & Eser Arama Altyapısı</span>
    </div>
    <form method="GET" style="display:grid; grid-template-columns:2fr 1fr 1fr 1fr auto; gap:12px; align-items:flex-end;">
      <input type="hidden" name="role" value="vitrin">
      <div>
        <label class="form-label">Anahtar Kelime / Eser Adı</label>
        <input type="text" name="q" class="form-control" placeholder="Örn: İsviçre Vintage Saat, Seramik, Vazo..." value="<?= htmlspecialchars($_GET['q'] ?? '') ?>">
      </div>
      <div>
        <label class="form-label">Kategori</label>
        <select name="cat" class="form-control">
          <option value="">Tüm Kategoriler</option>
          <option value="Horoloji" <?= (($_GET['cat'] ?? '') === 'Horoloji') ? 'selected' : '' ?>>Horoloji & Saat</option>
          <option value="Sanat" <?= (($_GET['cat'] ?? '') === 'Sanat') ? 'selected' : '' ?>>Antika & Sanat</option>
          <option value="Osmanlı" <?= (($_GET['cat'] ?? '') === 'Osmanlı') ? 'selected' : '' ?>>Osmanlı Eserleri</option>
          <option value="Nümismatik" <?= (($_GET['cat'] ?? '') === 'Nümismatik') ? 'selected' : '' ?>>Nümismatik & Para</option>
        </select>
      </div>
      <div>
        <label class="form-label">Maksimum Fiyat</label>
        <input type="number" name="max_price" class="form-control" placeholder="₺ Tavan Fiyat" value="<?= htmlspecialchars($_GET['max_price'] ?? '') ?>">
      </div>
      <div>
        <label class="form-label">Ekspertiz Durumu</label>
        <select name="exp_status" class="form-control">
          <option value="">Tümü</option>
          <option value="ONAYLI" selected>✓ Ekspertiz Onaylı</option>
        </select>
      </div>
      <div>
        <button type="submit" class="btn-register-glow" style="padding:10px 20px; white-space:nowrap;">
          Filtrele
        </button>
      </div>
    </form>
  </div>
</section>

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

  <!-- 4 Adet Kategori Kartı (Müze Kalitesinde Fotoğrafik Seçki) -->
  <div class="category-cards-grid">
    <!-- 1. Efemera & Nadir Kitap -->
    <div class="category-item-card" onclick="window.location.href='?role=buyer'">
      <div class="category-image-wrap">
        <img src="public/assets/images/books.jpg" alt="Efemera" style="width:100%; height:100%; object-fit:cover; filter:brightness(0.85); transition:transform 0.4s ease;">
        <div style="position:absolute; bottom:8px; right:12px; font-size:0.75rem; color:#fff; background:rgba(0,0,0,0.65); padding:2px 8px; border-radius:4px; backdrop-filter:blur(4px);">Tarihi Yazma & Belgeler</div>
      </div>
      <div class="category-label-footer">Nadir Kitap & Efemera</div>
    </div>

    <!-- 2. Sanat & Seramik -->
    <div class="category-item-card" onclick="window.location.href='?role=buyer'">
      <div class="category-image-wrap">
        <img src="public/assets/images/vase.jpg" alt="Sanat" style="width:100%; height:100%; object-fit:cover; filter:brightness(0.85); transition:transform 0.4s ease;">
        <div style="position:absolute; bottom:8px; right:12px; font-size:0.75rem; color:#fff; background:rgba(0,0,0,0.65); padding:2px 8px; border-radius:4px; backdrop-filter:blur(4px);">Antik Seramik & Vazo</div>
      </div>
      <div class="category-label-footer">Antika & Güzel Sanatlar</div>
    </div>

    <!-- 3. Horoloji & Saat -->
    <div class="category-item-card" onclick="window.location.href='?role=buyer'">
      <div class="category-image-wrap">
        <img src="public/assets/images/watch.jpg" alt="Horoloji" style="width:100%; height:100%; object-fit:cover; filter:brightness(0.85); transition:transform 0.4s ease;">
        <div style="position:absolute; bottom:8px; right:12px; font-size:0.75rem; color:#fff; background:rgba(0,0,0,0.65); padding:2px 8px; border-radius:4px; backdrop-filter:blur(4px);">İsviçre Mekanik Kronograf</div>
      </div>
      <div class="category-label-footer">Horoloji & Zaman Eserleri</div>
    </div>

    <!-- 4. Koleksiyon & Klasik Cihazlar -->
    <div class="category-item-card" onclick="window.location.href='?role=buyer'">
      <div class="category-image-wrap">
        <img src="public/assets/images/camera.jpg" alt="Koleksiyon" style="width:100%; height:100%; object-fit:cover; filter:brightness(0.85); transition:transform 0.4s ease;">
        <div style="position:absolute; bottom:8px; right:12px; font-size:0.75rem; color:#fff; background:rgba(0,0,0,0.65); padding:2px 8px; border-radius:4px; backdrop-filter:blur(4px);">Klasik Analog & Tasarım</div>
      </div>
      <div class="category-label-footer">Koleksiyon & Nadir Cihazlar</div>
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
