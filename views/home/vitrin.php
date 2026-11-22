<?php
declare(strict_types=1);

/** @var callable $formatPrice */
/** @var array $db */
/** @var \Martello\Controllers\WebAndApiController $controller */
/** @var \Martello\Services\PlatformWorkflowService $workflowService */

$filterOptions = $controller->getFilterOptions();

$filterParams = [
    'q'          => trim((string)($_GET['q'] ?? '')),
    'cat'        => trim((string)($_GET['cat'] ?? '')),
    'min_price'  => trim((string)($_GET['min_price'] ?? '')),
    'max_price'  => trim((string)($_GET['max_price'] ?? '')),
    'brand'      => trim((string)($_GET['brand'] ?? '')),
    'exp_status' => trim((string)($_GET['exp_status'] ?? '')),
];

$hasActiveFilters = (
    $filterParams['q'] !== '' ||
    $filterParams['cat'] !== '' ||
    $filterParams['min_price'] !== '' ||
    $filterParams['max_price'] !== '' ||
    $filterParams['brand'] !== '' ||
    ($filterParams['exp_status'] !== '' && $filterParams['exp_status'] !== 'TUMU')
);

$lots = $controller->searchCatalog($filterParams);
$categories = $filterOptions['categories'] ?? ($db['categories'] ?? []);

$vitrinCategoryCards = [
    [
        'title'    => 'Horoloji',
        'subtitle' => 'Vintage Mekanik Saatler',
        'filter'   => 'Horoloji',
        'image'    => 'public/assets/images/lot_swiss_watch_1968.jpg',
    ],
    [
        'title'    => 'Antika & Sanat',
        'subtitle' => 'Tablo, Seramik & Optik',
        'filter'   => 'Antika',
        'image'    => 'public/assets/images/lot_oil_painting.jpg',
    ],
    [
        'title'    => 'Osmanlı Dönemi',
        'subtitle' => 'Halı, Usturlap & Nadir Ciltler',
        'filter'   => 'Osmanlı',
        'image'    => 'public/assets/images/lot_ottoman_carpet.jpg',
    ],
    [
        'title'    => 'Filateli',
        'subtitle' => 'Nadir Posta Pulları',
        'filter'   => 'Filateli',
        'image'    => 'public/assets/images/lot_ottoman_stamps.jpg',
    ],
    [
        'title'    => 'Nümismatik',
        'subtitle' => 'Altın & Gümüş Sikkeler',
        'filter'   => 'Nümismatik',
        'image'    => 'public/assets/images/lot_ottoman_gold_coin.jpg',
    ],
];
?>

<!-- 1. VİTRİN HERO BANNER -->
<div style="max-width:1240px; margin:20px auto 0 auto; padding:0 28px; width:100%;">
  <div style="position:relative; border-radius:16px; overflow:hidden; background:#0A0908; min-height:310px; display:flex; align-items:center; padding:42px 52px; border:1px solid rgba(200,170,110,0.28); box-shadow:0 16px 44px rgba(0,0,0,0.72);">
    
    <div style="position:absolute; top:0; right:0; width:62%; height:100%; background:url('public/assets/images/astrolabe_clean.jpg') no-repeat right center / cover; mask-image:linear-gradient(to right, transparent 0%, rgba(0,0,0,0.3) 26%, rgba(0,0,0,0.92) 60%); -webkit-mask-image:linear-gradient(to right, transparent 0%, rgba(0,0,0,0.3) 26%, rgba(0,0,0,0.92) 60%); pointer-events:none; z-index:1;"></div>

    <svg style="position:absolute; top:8px; left:8px; width:calc(100% - 16px); height:calc(100% - 16px); pointer-events:none; z-index:2;" preserveAspectRatio="none" viewBox="0 0 1000 320">
      <defs>
        <linearGradient id="goldFrameGrad" x1="0%" y1="0%" x2="100%" y2="100%">
          <stop offset="0%" stop-color="#C8AA6E" stop-opacity="0.55"/>
          <stop offset="50%" stop-color="#E2C992" stop-opacity="0.72"/>
          <stop offset="100%" stop-color="#967740" stop-opacity="0.45"/>
        </linearGradient>
      </defs>
      <rect x="6" y="6" width="988" height="308" rx="18" fill="none" stroke="url(#goldFrameGrad)" stroke-width="1.2"/>
      <rect x="12" y="12" width="976" height="296" rx="14" fill="none" stroke="url(#goldFrameGrad)" stroke-width="0.7" stroke-opacity="0.45"/>
    </svg>

    <div style="position:relative; z-index:3; max-width:500px;">
      <div style="font-family:var(--font-brand); font-size:0.72rem; letter-spacing:2.4px; color:#C8AA6E; text-transform:uppercase; margin-bottom:14px;">
        MARTELLO • SEÇKİN KOLEKSİYONLAR
      </div>
      <h1 style="font-family:var(--font-serif); font-size:2.75rem; font-weight:500; line-height:1.14; color:#F5F0E6; margin-bottom:14px;">
        Saatler vardır, anıları<br>hatırlatır.
      </h1>
      <p style="font-size:0.9rem; color:#A39B8F; line-height:1.58; margin-bottom:24px; max-width:420px;">
        Ekspertiz onaylı nadir eserleri keşfedin, güvenle teklif verin.
      </p>
      <div style="display:flex; gap:10px; align-items:center; flex-wrap:wrap;">
        <a href="#katalog" class="btn-gold-pill" style="display:inline-flex; align-items:center; gap:7px; padding:10px 24px; font-size:0.84rem; text-decoration:none;">
          Seçkiyi İncele &rarr;
        </a>
        <a href="?role=buyer&tab=canli" class="btn-dark-pill" style="display:inline-flex; align-items:center; gap:7px; padding:10px 20px; font-size:0.82rem; text-decoration:none;">
          Canlı Salon
        </a>
      </div>
    </div>
  </div>
</div>

<!-- 2. KATEGORİLER BÖLÜMÜ (5 ANA KATEGORİ) -->
<section style="max-width:1240px; margin:34px auto 38px auto; padding:0 28px; width:100%;">
  <div style="display:flex; justify-content:space-between; align-items:flex-end; margin-bottom:18px; flex-wrap:wrap; gap:10px;">
    <div>
      <div style="font-family:var(--font-brand); font-size:0.7rem; letter-spacing:2px; color:#C8AA6E; text-transform:uppercase; margin-bottom:4px;">
        KATEGORİLER
      </div>
      <h2 style="font-family:var(--font-serif); font-size:1.65rem; color:#F5F0E6; font-weight:500; margin:0;">
        Koleksiyonları keşfedin
      </h2>
    </div>
    <?php if ($filterParams['cat'] !== ''): ?>
      <a href="?role=vitrin#katalog" class="btn-outline-gold btn-sm">Tüm Kategoriler</a>
    <?php endif; ?>
  </div>

  <div style="display:grid; grid-template-columns:repeat(5, 1fr); gap:14px;">
    <?php foreach ($vitrinCategoryCards as $cc):
      $isSelectedCat = ($filterParams['cat'] !== '' && mb_stripos($filterParams['cat'], $cc['filter']) !== false);
    ?>
      <a href="?role=vitrin&cat=<?= urlencode($cc['filter']) ?>#katalog"
         style="position:relative; display:block; border-radius:12px; overflow:hidden; height:168px; border:1px solid <?= $isSelectedCat ? '#E2C992' : 'rgba(200,170,110,0.22)' ?>; background:#110F0D; text-decoration:none; transition:all 0.25s ease; box-shadow:0 6px 20px rgba(0,0,0,0.45);"
         onmouseover="this.style.transform='translateY(-3px)'; this.style.borderColor='#E2C992';"
         onmouseout="this.style.transform='none'; this.style.borderColor='<?= $isSelectedCat ? '#E2C992' : 'rgba(200,170,110,0.22)' ?>';">
        <img src="<?= htmlspecialchars($cc['image']) ?>" alt="<?= htmlspecialchars($cc['title']) ?>" style="width:100%; height:100%; object-fit:cover; display:block; opacity:0.86; transition:transform 0.35s ease;">
        <div style="position:absolute; inset:0; background:linear-gradient(180deg, rgba(10,9,8,0.08) 25%, rgba(10,9,8,0.88) 100%);"></div>
        <div style="position:absolute; left:14px; right:14px; bottom:13px;">
          <div style="font-family:var(--font-serif); font-size:1.08rem; font-weight:600; color:#F5F0E6; line-height:1.2;">
            <?= htmlspecialchars($cc['title']) ?>
          </div>
          <div style="font-size:0.71rem; color:#C8AA6E; margin-top:2px;">
            <?= htmlspecialchars($cc['subtitle']) ?>
          </div>
        </div>
      </a>
    <?php endforeach; ?>
  </div>
</section>

<!-- 3. VIP SALONU BANNER -->
<section style="max-width:1240px; margin:0 auto 36px auto; padding:0 28px; width:100%;">
  <div style="background:linear-gradient(135deg, #13110F 0%, #191612 60%, #100E0C 100%); border:1px solid rgba(200,170,110,0.28); border-radius:14px; padding:24px 32px; display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:18px;">
    <div style="max-width:660px;">
      <div style="font-family:var(--font-brand); font-size:0.68rem; letter-spacing:2px; color:#C8AA6E; text-transform:uppercase; margin-bottom:5px;">
        SALON D'HONNEUR • VIP MÜZAYEDE
      </div>
      <h3 style="font-family:var(--font-serif); font-size:1.65rem; color:#F5F0E6; margin:0 0 6px 0; font-weight:500;">
        Özel Müşteriler VIP Salonu
      </h3>
      <p style="font-size:0.83rem; color:#A39B8F; line-height:1.55; margin:0;">
        Müze niteliğindeki nadir başyapıtlar, tam kıymet sigortası ve zırhlı teslimat güvencesiyle akredite koleksiyonerlere sunulur.
      </p>
    </div>
    <a href="?role=buyer&tab=vip" class="btn-outline-gold" style="padding:10px 22px; font-size:0.82rem; text-decoration:none; white-space:nowrap;">
      VIP Salona Geç &rarr;
    </a>
  </div>
</section>

<!-- 4. AÇIK ARTIRMADAKİ SEÇKİN ESERLER & FİLTRELEME -->
<main id="katalog" style="max-width:1240px; margin:0 auto 54px auto; padding:0 28px; width:100%;">
  <!-- Sade Filtre Barı -->
  <div class="panel-card" style="margin-bottom:24px; padding:18px 20px;">
    <form method="GET" action="index.php#katalog" style="display:grid; grid-template-columns:1.8fr 1.3fr 1.4fr 1.1fr 1.1fr auto; gap:10px; align-items:flex-end;">
      <input type="hidden" name="role" value="vitrin">

      <div>
        <label class="form-label">Arama</label>
        <input type="text" name="q" class="form-control" placeholder="Eser adı, lot no, dönem..." value="<?= htmlspecialchars($filterParams['q']) ?>">
      </div>

      <div>
        <label class="form-label">Kategori</label>
        <select name="cat" class="form-control">
          <option value="">Tüm Kategoriler</option>
          <?php foreach ($categories as $cat): ?>
            <?php $catName = (string)($cat['ad'] ?? ''); ?>
            <option value="<?= htmlspecialchars($catName) ?>" <?= ($filterParams['cat'] !== '' && (mb_stripos($catName, $filterParams['cat']) !== false || $filterParams['cat'] === (string)($cat['id'] ?? ''))) ? 'selected' : '' ?>>
              <?= htmlspecialchars($catName) ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>

      <div>
        <label class="form-label">Fiyat Aralığı (<?= $currencyStrategy->getSymbol() ?>)</label>
        <div style="display:grid; grid-template-columns:1fr 1fr; gap:6px;">
          <input type="number" name="min_price" class="form-control" placeholder="Min" value="<?= htmlspecialchars($filterParams['min_price']) ?>">
          <input type="number" name="max_price" class="form-control" placeholder="Max" value="<?= htmlspecialchars($filterParams['max_price']) ?>">
        </div>
      </div>

      <div>
        <label class="form-label">Atölye / Marka</label>
        <select name="brand" class="form-control">
          <option value="">Tümü</option>
          <?php foreach ($filterOptions['brands'] ?? [] as $br): ?>
            <option value="<?= htmlspecialchars($br) ?>" <?= ($filterParams['brand'] === $br) ? 'selected' : '' ?>>
              <?= htmlspecialchars($br) ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>

      <div>
        <label class="form-label">Ekspertiz</label>
        <select name="exp_status" class="form-control">
          <option value="">Tümü</option>
          <option value="ONAYLI" <?= ($filterParams['exp_status'] === 'ONAYLI') ? 'selected' : '' ?>>Onaylı</option>
          <option value="BEKLEMEDE" <?= ($filterParams['exp_status'] === 'BEKLEMEDE') ? 'selected' : '' ?>>İncelemede</option>
        </select>
      </div>

      <div style="display:flex; gap:6px;">
        <button type="submit" class="btn-gold" style="padding:8px 16px; white-space:nowrap;">Filtrele</button>
        <?php if ($hasActiveFilters): ?>
          <a href="?role=vitrin#katalog" class="btn-outline-gold" style="padding:8px 12px; white-space:nowrap;">Sıfırla</a>
        <?php endif; ?>
      </div>
    </form>

    <?php if ($hasActiveFilters): ?>
      <div style="margin-top:12px; padding-top:10px; border-top:1px solid var(--border-color); display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:8px; font-size:0.76rem;">
        <div style="display:flex; align-items:center; gap:6px; flex-wrap:wrap;">
          <span style="color:#A39B8F;">Filtre:</span>
          <?php if ($filterParams['q'] !== ''): ?><span class="badge badge-gold">"<?= htmlspecialchars($filterParams['q']) ?>"</span><?php endif; ?>
          <?php if ($filterParams['cat'] !== ''): ?><span class="badge badge-gold"><?= htmlspecialchars($filterParams['cat']) ?></span><?php endif; ?>
          <?php if ($filterParams['brand'] !== ''): ?><span class="badge badge-gold"><?= htmlspecialchars($filterParams['brand']) ?></span><?php endif; ?>
          <?php if ($filterParams['exp_status'] !== ''): ?><span class="badge badge-gold"><?= htmlspecialchars($filterParams['exp_status']) ?></span><?php endif; ?>
        </div>
        <span style="color:#C8AA6E;"><?= count($lots) ?> eser listeleniyor</span>
      </div>
    <?php endif; ?>
  </div>

  <div class="panel-header" style="margin-bottom:20px;">
    <div>
      <div style="font-family:var(--font-brand); font-size:0.68rem; letter-spacing:2px; color:#C8AA6E; text-transform:uppercase;">
        CANLI MÜZAYEDELER
      </div>
      <h3 style="font-family:var(--font-serif); font-size:1.55rem; font-weight:500; color:#F5F0E6; margin-top:2px;">
        <?= $hasActiveFilters ? 'Filtrelenmiş Eser Kataloğu' : 'Açık Artırmadaki Seçkin Eserler' ?>
      </h3>
    </div>
    <div style="display:flex; align-items:center; gap:12px;">
      <span style="font-size:0.78rem; color:#A39B8F;"><?= count($lots) ?> eser</span>
      <a href="?role=buyer&tab=kesfet" class="btn-outline-gold btn-sm">Tüm Müzayedeler &rarr;</a>
    </div>
  </div>

  <?php if (empty($lots)): ?>
    <div class="panel-card" style="text-align:center; padding:42px 20px;">
      <h4 style="font-family:var(--font-serif); color:#F5F0E6; font-size:1.25rem; margin-bottom:8px;">Eşleşen eser bulunamadı</h4>
      <p style="color:#A39B8F; font-size:0.82rem; margin-bottom:18px;">Filtre kriterlerini temizleyerek tüm kataloğu görüntüleyebilirsiniz.</p>
      <a href="?role=vitrin#katalog" class="btn-gold">Tüm Kataloğu Göster</a>
    </div>
  <?php else: ?>
    <div style="display:grid; grid-template-columns:repeat(3, 1fr); gap:20px;">
      <?php foreach ($lots as $lot):
        $imgUrl = !empty($lot['gorselUrl']) ? (string)$lot['gorselUrl'] : 'public/assets/images/lot_ceramic_vase.jpg';
        $isLive = (($lot['durum'] ?? '') === 'ACIK_ARTIRMADA');
      ?>
        <div class="panel-card" style="margin-bottom:0; padding:16px; display:flex; flex-direction:column; justify-content:space-between; transition:all 0.25s ease;"
             onmouseover="this.style.borderColor='rgba(200,170,110,0.42)'; this.style.transform='translateY(-2px)';"
             onmouseout="this.style.borderColor='var(--border-card)'; this.style.transform='none';">
          <div>
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:10px;">
              <span class="badge badge-gold"><?= htmlspecialchars((string)($lot['lotKodu'] ?? '')) ?></span>
              <span class="badge <?= $isLive ? 'badge-success' : 'badge-warning' ?>">
                <?= $isLive ? 'Açık Artırmada' : htmlspecialchars((string)($lot['durum'] ?? '')) ?>
              </span>
            </div>

            <div style="height:196px; background:#0A0908; border-radius:8px; overflow:hidden; margin-bottom:12px; border:1px solid rgba(200,170,110,0.14);">
              <img src="<?= htmlspecialchars($imgUrl) ?>" alt="<?= htmlspecialchars((string)($lot['baslik'] ?? '')) ?>" style="width:100%; height:100%; object-fit:cover; display:block;">
            </div>

            <div style="display:flex; justify-content:space-between; align-items:center; gap:8px; margin-bottom:4px;">
              <span style="font-size:0.7rem; color:#C8AA6E; font-weight:600; text-transform:uppercase; letter-spacing:0.5px;">
                <?= htmlspecialchars((string)($lot['markaGosterim'] ?? ($lot['marka'] ?? 'Özel Koleksiyon'))) ?>
              </span>
              <span style="font-size:0.7rem; color:#8C8478; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">
                <?= htmlspecialchars((string)($lot['kategoriAdi'] ?? 'Antika & Sanat')) ?>
              </span>
            </div>

            <h4 style="font-family:var(--font-serif); font-size:1.12rem; font-weight:500; color:#F5F0E6; margin-bottom:12px; line-height:1.32;">
              <?= htmlspecialchars((string)($lot['baslik'] ?? '')) ?>
            </h4>
          </div>

          <div style="border-top:1px solid var(--border-color); padding-top:12px; display:flex; justify-content:space-between; align-items:center;">
            <div>
              <div style="font-size:0.67rem; color:#8C8478; text-transform:uppercase; letter-spacing:0.6px;">Güncel Teklif</div>
              <div style="font-family:var(--font-serif); font-size:1.22rem; font-weight:600; color:#E2C992;">
                <?= $formatPrice((float)($lot['mevcutEnYuksekTeklif'] ?: ($lot['baslangicFiyati'] ?? 0))) ?>
              </div>
            </div>
            <a href="?role=buyer&tab=canli&lot_id=<?= (int)$lot['id'] ?>" class="btn-outline-gold btn-sm">
              İncele &rarr;
            </a>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</main>
