<?php
declare(strict_types=1);

/** @var callable $formatPrice */
/** @var array $db */

$sellerId = 2; // Mehmet Kaya (Satıcı)
$products = array_filter($db['products'] ?? [], static fn($p) => (int)($p['saticiId'] ?? 0) === $sellerId);
$listings = array_filter($db['listings'] ?? [], static fn($l) => (int)($l['saticiId'] ?? 0) === $sellerId);
$categories = $db['categories'] ?? [];
?>

<div class="app-container">
  <!-- Sol Menü (Sidebar) — SRS Sayfa 34 Üst Görsel ile Birebir Uyumlu -->
  <aside class="sidebar">
    <div>
      <div class="sidebar-section-title">Çalışma Alanı</div>
      <ul class="nav-menu">
        <li class="nav-menu-item active"><a href="?role=seller">🏠 Genel Bakış</a></li>
        <li class="nav-menu-item"><a href="#yeni-urun">➕ Yeni Ürün Ekle</a></li>
        <li class="nav-menu-item"><a href="#ilanlarim">📋 İlanlarım</a></li>
        <li class="nav-menu-item"><a href="#teklif-gecmisi">📈 Teklif Geçmişi</a></li>
        <li class="nav-menu-item"><a href="#bakiye-komisyon">💰 Bakiye ve Komisyon</a></li>
        <li class="nav-menu-item"><a href="#itirazlar">⚖️ İtirazlar</a></li>
      </ul>
    </div>
  </aside>

  <!-- Ana İçerik Alanı -->
  <main class="main-content">
    <div style="font-size:0.8rem; color:var(--gold-primary); text-transform:uppercase; margin-bottom:4px;">
      Satıcı / Çalışma Alanı
    </div>
    <h1 style="font-size:2rem; color:#fff; font-weight:600; margin-bottom:6px;">Genel bakış</h1>
    <p style="color:var(--text-muted); font-size:0.9rem; margin-bottom:28px;">
      Seçkin müzayede deneyiminizi yönetin.
    </p>

    <!-- 4 Adet KPI Kartı (Sayfa 34 Üstteki Tasarım) -->
    <div class="kpi-grid">
      <div class="kpi-card">
        <div class="kpi-label">Aktif İlanlar</div>
        <div class="kpi-value">1</div>
      </div>
      <div class="kpi-card highlight">
        <div class="kpi-label">Toplam Satış</div>
        <div class="kpi-value"><?= $formatPrice(142800) ?></div>
      </div>
      <div class="kpi-card">
        <div class="kpi-label">Taslaklar</div>
        <div class="kpi-value">0</div>
      </div>
      <div class="kpi-card">
        <div class="kpi-label">Teklifler</div>
        <div class="kpi-value">48</div>
      </div>
    </div>

    <!-- Satış Performansı Kutusu -->
    <div class="panel-card">
      <div class="panel-header">
        <div>
          <h2 class="panel-title">Satış performansı</h2>
          <p style="font-size:0.85rem; color:var(--text-muted); margin-top:4px;">
            İlanlarınızı ve yeni teklifleri tek yerden yönetin.
          </p>
        </div>
        <a href="#yeni-urun" class="btn-gold btn-sm">Yeni İlan Oluştur</a>
      </div>
      <p style="color:#8b949e; font-size:0.9rem;">
        Katalogdaki eserleriniz bağımsız akredite eksperlerimizce incelendikten sonra Canlı Müzayede listesine dahil edilmektedir.
      </p>
    </div>

    <!-- Yeni Ürün ve İlan Ekleme Formu (FR-07, FR-31) -->
    <div id="yeni-urun" class="panel-card">
      <div class="panel-header">
        <h3 class="panel-title">➕ Yeni Ürün / İlan Kaydı (FR-07, FR-31)</h3>
      </div>
      <form method="POST">
        <input type="hidden" name="action" value="create_listing">
        <input type="hidden" name="seller_id" value="<?= $sellerId ?>">

        <div style="display:grid; grid-template-columns:1fr 1fr; gap:16px;">
          <div class="form-group">
            <label class="form-label">Eser / Ürün Başlığı</label>
            <input type="text" name="baslik" class="form-control" placeholder="Örn: 19. Yüzyıl Osmanlı Gümüş Buhurdan" required>
          </div>
          <div class="form-group">
            <label class="form-label">Kategori</label>
            <select name="kategoriId" class="form-control">
              <?php foreach ($categories as $cat): ?>
                <option value="<?= $cat['id'] ?>">
                  <?= htmlspecialchars($cat['ad']) ?> (Komisyon: %<?= $cat['komisyonOraniYuzde'] ?>)
                </option>
              <?php endforeach; ?>
            </select>
          </div>
        </div>

        <div class="form-group">
          <label class="form-label">Detaylı Açıklama ve Provenans</label>
          <textarea name="aciklama" class="form-control" rows="3" placeholder="Eserin dönemi, kökeni ve özellikleri..."></textarea>
        </div>

        <div style="display:grid; grid-template-columns:1fr 1fr 1fr; gap:16px;">
          <div class="form-group">
            <label class="form-label">Başlangıç Fiyatı (TRY)</label>
            <input type="number" step="500" name="baslangicFiyati" class="form-control" placeholder="10000" required>
          </div>
          <div class="form-group">
            <label class="form-label">Rezerv Taban Fiyat (TRY)</label>
            <input type="number" step="500" name="rezervFiyat" class="form-control" placeholder="15000" required>
          </div>
          <div class="form-group">
            <label class="form-label">Asgari Pey Adımı (TRY)</label>
            <input type="number" step="100" name="minimumArtisTutari" class="form-control" placeholder="1000" required>
          </div>
        </div>

        <button type="submit" class="btn-gold" style="width:100%; justify-content:center; padding:12px;">
          Ekspertiz İncelemesine Gönder
        </button>
      </form>
    </div>

    <!-- İlanlarım Listesi -->
    <div id="ilanlarim" class="panel-card">
      <div class="panel-header">
        <h3 class="panel-title">📋 İlanlarım ve Müzayede Durumları</h3>
        <span class="badge badge-gold"><?= count($listings) ?> Kayıt</span>
      </div>
      <table class="data-table">
        <thead>
          <tr>
            <th>İlan Kodu</th>
            <th>Ürün Adı</th>
            <th>Ekspertiz Raporu</th>
            <th>Durum</th>
            <th>İşlem</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($listings as $l): ?>
            <?php
              $prod = null;
              foreach ($products as $p) {
                  if ((int)$p['id'] === (int)$l['urunId']) {
                      $prod = $p;
                      break;
                  }
              }
            ?>
            <tr>
              <td><span class="badge badge-gold"><?= htmlspecialchars($l['ilanKodu']) ?></span></td>
              <td style="font-weight:600; color:#fff;"><?= htmlspecialchars($prod['baslik'] ?? 'Müzayede Lotu') ?></td>
              <td>
                <?= $l['ekspertizRaporId'] ? '<span class="badge badge-success">REP-' . $l['ekspertizRaporId'] . ' Onaylandı</span>' : '<span class="badge badge-warning">İnceleme Sürüyor</span>' ?>
              </td>
              <td>
                <span class="badge <?= $l['durum'] === 'ONAYLANDI' ? 'badge-success' : 'badge-info' ?>">
                  <?= htmlspecialchars($l['durum']) ?>
                </span>
              </td>
              <td>
                <a href="?role=buyer&lot_id=1002" class="btn-outline-gold btn-sm">Müzayedeyi Gör</a>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </main>
</div>
