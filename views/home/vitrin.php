<?php
declare(strict_types=1);

/** @var callable $formatPrice */
/** @var array $db */

$categories = $db['categories'] ?? [];
$lots = $db['lots'] ?? [];
?>

<!-- 1. VITRIN HERO BANNER (media_1795017299898_aa5174be.jpg ile Birebir) -->
<div style="max-width:1260px; margin:20px auto 0 auto; padding:0 32px; width:100%;">
  <div class="vitrin-hero-banner-exact" style="position:relative; border-radius:18px; overflow:hidden; background:#070a0f; min-height:360px; display:flex; align-items:center; padding:50px 60px; border:1px solid rgba(197, 160, 89, 0.35); box-shadow:0 12px 40px rgba(0,0,0,0.8), inset 0 0 60px rgba(0,0,0,0.7);">
    
    <!-- Astrolabe Saat Mekanizması Görsel Katmanı (Sağ Tarafta Yumuşak Geçişli) -->
    <div style="position:absolute; top:0; right:0; width:64%; height:100%; background:url('public/assets/images/astrolabe_clean.jpg') no-repeat right center / cover; mask-image:linear-gradient(to right, transparent 0%, rgba(0,0,0,0.35) 22%, rgba(0,0,0,1) 55%); -webkit-mask-image:linear-gradient(to right, transparent 0%, rgba(0,0,0,0.35) 22%, rgba(0,0,0,1) 55%); pointer-events:none; z-index:1;"></div>

    <!-- Zarif Çift Hatlı Altın Guilloche Çerçeve (Görseldeki Ornate Frame) -->
    <svg style="position:absolute; top:8px; left:8px; width:calc(100% - 16px); height:calc(100% - 16px); pointer-events:none; z-index:2;" preserveAspectRatio="none" viewBox="0 0 1000 360">
      <defs>
        <linearGradient id="goldFrameGrad" x1="0%" y1="0%" x2="100%" y2="100%">
          <stop offset="0%" stop-color="#b89355" stop-opacity="0.85"/>
          <stop offset="50%" stop-color="#dfbe7e" stop-opacity="0.95"/>
          <stop offset="100%" stop-color="#936d31" stop-opacity="0.8"/>
        </linearGradient>
      </defs>
      <!-- Dış Çerçeve -->
      <rect x="6" y="6" width="988" height="348" rx="22" fill="none" stroke="url(#goldFrameGrad)" stroke-width="1.6"/>
      <!-- İç Çerçeve -->
      <rect x="12" y="12" width="976" height="336" rx="16" fill="none" stroke="url(#goldFrameGrad)" stroke-width="0.9" stroke-opacity="0.55"/>
      <!-- Köşe Kıvrımları (Ornate Loops) -->
      <!-- Sol Üst -->
      <path d="M 6 32 C 6 18, 18 6, 32 6 C 20 10, 10 20, 6 32 Z" fill="url(#goldFrameGrad)" opacity="0.4"/>
      <path d="M 6 32 Q 20 20 32 6" fill="none" stroke="url(#goldFrameGrad)" stroke-width="1.2"/>
      <!-- Sağ Üst -->
      <path d="M 994 32 C 994 18, 982 6, 968 6 C 980 10, 990 20, 994 32 Z" fill="url(#goldFrameGrad)" opacity="0.4"/>
      <path d="M 994 32 Q 980 20 968 6" fill="none" stroke="url(#goldFrameGrad)" stroke-width="1.2"/>
      <!-- Sol Alt -->
      <path d="M 6 328 C 6 342, 18 354, 32 354 C 20 350, 10 340, 6 328 Z" fill="url(#goldFrameGrad)" opacity="0.4"/>
      <path d="M 6 328 Q 20 340 32 354" fill="none" stroke="url(#goldFrameGrad)" stroke-width="1.2"/>
      <!-- Sağ Alt -->
      <path d="M 994 328 C 994 342, 982 354, 968 354 C 980 350, 990 340, 994 328 Z" fill="url(#goldFrameGrad)" opacity="0.4"/>
      <path d="M 994 328 Q 980 340 968 354" fill="none" stroke="url(#goldFrameGrad)" stroke-width="1.2"/>
    </svg>

    <!-- Sol Metin ve Buton Kutusu -->
    <div style="position:relative; z-index:3; max-width:520px;">
      <div style="font-family:var(--font-brand); font-size:0.82rem; letter-spacing:2.5px; color:#c5a059; text-transform:uppercase; margin-bottom:18px;">
        MARTELLO - SEÇKİN KOLEKSİYONLAR
      </div>
      <h1 style="font-family:var(--font-serif); font-size:3.4rem; font-weight:500; line-height:1.15; color:#f0e9dc; margin-bottom:18px; text-shadow:0 3px 12px rgba(0,0,0,0.85);">
        Saatler vardır, anıları<br>hatırlatır.
      </h1>
      <p style="font-size:1.02rem; color:#9d9588; line-height:1.6; margin-bottom:28px; max-width:460px;">
        Seçkin koleksiyonları keşfedin, değerli parçalar için güvenle teklif verin.
      </p>
      <a href="?role=buyer" style="display:inline-flex; align-items:center; gap:8px; padding:12px 28px; font-size:0.92rem; font-weight:600; color:#0c0f14; background:linear-gradient(135deg, #d8b168 0%, #aa8037 100%); border:1px solid #e0be7d; border-radius:24px; text-decoration:none; box-shadow:0 4px 18px rgba(184, 147, 85, 0.35); transition:all 0.25s ease;" onmouseover="this.style.filter='brightness(1.1)'; this.style.transform='translateY(-2px)';" onmouseout="this.style.filter='none'; this.style.transform='none';">
        Müzayedeleri Keşfet ↗
      </a>
    </div>
  </div>
</div>

<!-- 2. KOLEKSİYONLARI KEŞFEDİN BÖLÜMÜ (media_1795017299898_aa5174be.jpg ile Birebir) -->
<section style="max-width:1260px; margin:36px auto 48px auto; padding:0 32px; width:100%;">
  <div style="display:flex; justify-content:space-between; align-items:flex-end; margin-bottom:20px;">
    <div>
      <div style="font-family:var(--font-brand); font-size:0.8rem; letter-spacing:2px; color:#c5a059; text-transform:uppercase; margin-bottom:6px;">
        KOLEKSİYONLARI KEŞFEDİN
      </div>
      <h2 style="font-family:var(--font-serif); font-size:1.85rem; color:#f0e9dc; font-weight:500; margin:0;">
        Her parça bir hikâye.
      </h2>
    </div>
    <div style="display:flex; gap:8px;">
      <button type="button" style="width:36px; height:36px; display:inline-flex; align-items:center; justify-content:center; background:transparent; border:1px solid rgba(255,255,255,0.15); border-radius:8px; color:#8b949e; cursor:pointer; font-size:1rem; transition:all 0.2s;" onmouseover="this.style.borderColor='#d4af37'; this.style.color='#fff';" onmouseout="this.style.borderColor='rgba(255,255,255,0.15)'; this.style.color='#8b949e';">←</button>
      <button type="button" style="width:36px; height:36px; display:inline-flex; align-items:center; justify-content:center; background:transparent; border:1px solid rgba(255,255,255,0.15); border-radius:8px; color:#8b949e; cursor:pointer; font-size:1rem; transition:all 0.2s;" onmouseover="this.style.borderColor='#d4af37'; this.style.color='#fff';" onmouseout="this.style.borderColor='rgba(255,255,255,0.15)'; this.style.color='#8b949e';">→</button>
    </div>
  </div>

  <!-- 4 Adet Koleksiyon Kartı Grid (Birebir Tasarım) -->
  <div style="display:grid; grid-template-columns:repeat(4, 1fr); gap:16px;">
    <!-- 1. Efemera -->
    <div onclick="window.location.href='?role=buyer'" style="border-radius:14px; overflow:hidden; border:1px solid rgba(197, 160, 89, 0.25); background:#0c0f15; cursor:pointer; transition:all 0.25s ease; box-shadow:0 4px 16px rgba(0,0,0,0.5);" onmouseover="this.style.transform='translateY(-4px)'; this.style.borderColor='#d4af37'; this.style.boxShadow='0 8px 24px rgba(0,0,0,0.7)';" onmouseout="this.style.transform='none'; this.style.borderColor='rgba(197, 160, 89, 0.25)'; this.style.boxShadow='0 4px 16px rgba(0,0,0,0.5)';">
      <img src="public/assets/images/cat_efemera.jpg" alt="Efemera" style="width:100%; height:156px; object-fit:cover; display:block;">
    </div>

    <!-- 2. Filateli -->
    <div onclick="window.location.href='?role=buyer'" style="border-radius:14px; overflow:hidden; border:1px solid rgba(197, 160, 89, 0.25); background:#0c0f15; cursor:pointer; transition:all 0.25s ease; box-shadow:0 4px 16px rgba(0,0,0,0.5);" onmouseover="this.style.transform='translateY(-4px)'; this.style.borderColor='#d4af37'; this.style.boxShadow='0 8px 24px rgba(0,0,0,0.7)';" onmouseout="this.style.transform='none'; this.style.borderColor='rgba(197, 160, 89, 0.25)'; this.style.boxShadow='0 4px 16px rgba(0,0,0,0.5)';">
      <img src="public/assets/images/cat_filateli.jpg" alt="Filateli" style="width:100%; height:156px; object-fit:cover; display:block;">
    </div>

    <!-- 3. Osmanlı Eserleri -->
    <div onclick="window.location.href='?role=buyer'" style="border-radius:14px; overflow:hidden; border:1px solid rgba(197, 160, 89, 0.25); background:#0c0f15; cursor:pointer; transition:all 0.25s ease; box-shadow:0 4px 16px rgba(0,0,0,0.5);" onmouseover="this.style.transform='translateY(-4px)'; this.style.borderColor='#d4af37'; this.style.boxShadow='0 8px 24px rgba(0,0,0,0.7)';" onmouseout="this.style.transform='none'; this.style.borderColor='rgba(197, 160, 89, 0.25)'; this.style.boxShadow='0 4px 16px rgba(0,0,0,0.5)';">
      <img src="public/assets/images/cat_osmanli.jpg" alt="Osmanlı Eserleri" style="width:100%; height:156px; object-fit:cover; display:block;">
    </div>

    <!-- 4. Nümismatik -->
    <div onclick="window.location.href='?role=buyer'" style="border-radius:14px; overflow:hidden; border:1px solid rgba(197, 160, 89, 0.25); background:#0c0f15; cursor:pointer; transition:all 0.25s ease; box-shadow:0 4px 16px rgba(0,0,0,0.5);" onmouseover="this.style.transform='translateY(-4px)'; this.style.borderColor='#d4af37'; this.style.boxShadow='0 8px 24px rgba(0,0,0,0.7)';" onmouseout="this.style.transform='none'; this.style.borderColor='rgba(197, 160, 89, 0.25)'; this.style.boxShadow='0 4px 16px rgba(0,0,0,0.5)';">
      <img src="public/assets/images/cat_numismatik.jpg" alt="Nümismatik" style="width:100%; height:156px; object-fit:cover; display:block;">
    </div>
  </div>
</section>

<!-- VIP MÜZAYEDE SALONU VITRIN BANNER -->
<section style="max-width:1260px; margin:0 auto 40px auto; padding:0 32px; width:100%;">
  <div style="background:linear-gradient(135deg, #090c12 0%, #161c28 50%, #0c0f16 100%); border:1px solid rgba(212,175,55,0.4); border-radius:16px; padding:32px 40px; display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:24px; box-shadow:0 12px 40px rgba(0,0,0,0.8), inset 0 0 50px rgba(212,175,55,0.06); position:relative; overflow:hidden;">
    <div style="position:absolute; right:0; top:0; width:50%; height:100%; background:radial-gradient(circle at 80% 50%, rgba(212,175,55,0.12) 0%, transparent 70%); pointer-events:none;"></div>
    
    <div style="max-width:680px; position:relative; z-index:2;">
      <div style="display:flex; align-items:center; gap:8px; margin-bottom:8px;">
        <span style="color:#d4af37;">
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m2 4 3 12h14l3-12-6 7-4-7-4 7-6-7zm3 16h14"/></svg>
        </span>
        <span style="font-family:var(--font-brand); font-size:0.75rem; letter-spacing:2px; color:#d4af37; text-transform:uppercase; font-weight:700;">
          MARTELLO PRIVATE CLIENTS • SALON D'HONNEUR
        </span>
      </div>
      <h3 style="font-family:var(--font-serif); font-size:2.1rem; color:#f5f0e6; margin:0 0 10px 0; font-weight:500;">
        Özel Müşteriler VIP Müzayede Salonu
      </h3>
      <p style="font-size:0.92rem; color:#a0aab8; line-height:1.6; margin:0 0 18px 0;">
        Nadir astronomik aletler, imparatorluk saatleri ve başyapıt niteliğindeki saray koleksiyonları. Yalnızca akredite VIP koleksiyonerlerin katılımına açık özel müzayede salonu.
      </p>
      <div style="display:flex; gap:20px; font-size:0.82rem; color:#d4af37; flex-wrap:wrap;">
        <span>★ <strong>Gold, Platinum & Diamond</strong> Giriş</span>
        <span>•</span>
        <span><strong>Lloyds of London</strong> %100 Sigorta</span>
        <span>•</span>
        <span><strong>Zırhlı Kurye</strong> Noter Teslimatı</span>
      </div>
    </div>

    <div style="position:relative; z-index:2;">
      <a href="?role=buyer&tab=vip" class="btn-register-glow" style="padding:14px 32px; font-size:0.95rem; font-weight:600; text-decoration:none; display:inline-flex; align-items:center; gap:8px;">
        VIP Salona Giriş Yap &rarr;
      </a>
    </div>
  </div>
</section>

<!-- 3. CANLI MÜZAYEDELER & ARAMA ALTYAPISI (Aşağı Kaydırıldığında Erişilebilir) -->
<main style="max-width:1260px; margin:20px auto 60px auto; padding:0 32px; width:100%;">
  <!-- Arama ve Filtreleme Bölümü -->
  <div class="panel-card" style="margin-bottom:28px; background:rgba(18,22,31,0.85); border:1px solid #2a3449;">
    <div style="font-size:0.82rem; color:var(--gold-primary); font-weight:700; text-transform:uppercase; letter-spacing:1px; margin-bottom:12px; display:flex; align-items:center; gap:6px;">
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
</main>
