<?php
declare(strict_types=1);

/** @var callable $formatPrice */
/** @var array $db */

$buyerId = 3; // Zeynep Demir (Alıcı)
$lots = $db['lots'] ?? [];
$selectedLotId = (int)($_GET['lot_id'] ?? 1002); // 1002: AUC-1642 (1968 İsviçre Vintage Saat)

$currentLot = null;
foreach ($lots as $l) {
    if ((int)$l['id'] === $selectedLotId) {
        $currentLot = $l;
        break;
    }
}
if (!$currentLot && !empty($lots)) {
    $currentLot = $lots[0];
}

// Seçili lotun pey geçmişi
$allBids = $db['bids'] ?? [];
$lotBids = array_filter($allBids, static fn($b) => (int)($b['lotId'] ?? 0) === (int)$currentLot['id']);
usort($lotBids, static fn($a, $b) => ($b['id'] ?? 0) <=> ($a['id'] ?? 0));

// Alıcının cüzdanı
$wallet = null;
foreach ($db['wallets'] ?? [] as $w) {
    if ((int)($w['kullaniciId'] ?? 0) === $buyerId) {
        $wallet = $w;
        break;
    }
}

// Alıcının kazandığı siparişler
$orders = $db['orders'] ?? [];
$wonOrders = array_filter($orders, static fn($o) => (int)($o['aliciId'] ?? 0) === $buyerId || in_array($o['lotKodu'] ?? '', ['AUC-0991', 'AUC-0984'], true));

// Takip listesi
$watchlists = $db['watchlists'] ?? [];
$isWatched = false;
foreach ($watchlists as $wl) {
    if ((int)($wl['kullaniciId'] ?? 0) === $buyerId && (int)($wl['lotId'] ?? 0) === (int)$currentLot['id']) {
        $isWatched = true;
        break;
    }
}

$minNextBid = (float)($currentLot['mevcutEnYuksekTeklif'] ?? 0) + (float)($currentLot['minimumArtisTutari'] ?? 1500);
?>

<div class="app-container">
  <!-- Sol Menü (Sidebar) — SRS Sayfa 33 ile Birebir Uyumlu -->
  <aside class="sidebar">
    <div>
      <div class="sidebar-section-title">Çalışma Alanı</div>
      <ul class="nav-menu">
        <li class="nav-menu-item"><a href="?role=buyer&tab=genel">🏠 Genel Bakış</a></li>
        <li class="nav-menu-item"><a href="?role=vitrin">🔍 Müzayede Keşfet</a></li>
        <li class="nav-menu-item active"><a href="?role=buyer&tab=canli">📡 Canlı Müzayede</a></li>
        <li class="nav-menu-item"><a href="#kazanilanlar">🏆 Kazanılan Lotlar</a></li>
        <li class="nav-menu-item"><a href="#takip-edilenler">⭐ Takip Edilenler</a></li>
        <li class="nav-menu-item"><a href="#bakiye-paneli">💳 Bakiye ve Faturalar</a></li>
        <li class="nav-menu-item"><a href="#itiraz-paneli">⚖️ İtirazlar</a></li>
      </ul>
    </div>

    <!-- Cüzdan Durumu Kutusu -->
    <div style="background:#11161f; border:1px solid var(--border-color); border-radius:8px; padding:14px;">
      <div style="font-size:0.75rem; color:var(--text-muted);">CÜZDAN BAKİYENİZ</div>
      <div style="font-size:1.4rem; font-weight:700; color:var(--gold-bright); margin:4px 0;">
        <?= $formatPrice((float)($wallet['bakiye'] ?? 120000)) ?>
      </div>
      <div style="font-size:0.75rem; color:var(--text-muted); display:flex; justify-content:space-between;">
        <span>Bloke Teminat:</span>
        <span style="color:#f85149; font-weight:600;"><?= $formatPrice((float)($wallet['blokeBakiye'] ?? 65000)) ?></span>
      </div>
    </div>
  </aside>

  <!-- Ana İçerik Alanı -->
  <main class="main-content">
    <!-- 1. BÖLÜM: LOT DETAYI (SRS Sayfa 33 Üst Görsel) -->
    <div class="panel-card" style="border-color:var(--border-gold);">
      <div class="panel-header">
        <div>
          <span style="font-size:0.8rem; color:var(--gold-primary); font-weight:600; text-transform:uppercase;">Lot Detayı</span>
          <h1 style="font-size:1.6rem; color:#fff; font-weight:600;">
            <?= htmlspecialchars($currentLot['baslik']) ?> — Müzayede No: <?= htmlspecialchars($currentLot['lotKodu']) ?>
          </h1>
        </div>
        <div style="display:flex; gap:10px;">
          <form method="POST" style="margin:0;">
            <input type="hidden" name="action" value="toggle_watchlist">
            <input type="hidden" name="user_id" value="<?= $buyerId ?>">
            <input type="hidden" name="lot_id" value="<?= $currentLot['id'] ?>">
            <input type="hidden" name="auction_id" value="<?= $currentLot['muzayedeId'] ?? 801 ?>">
            <input type="hidden" name="current_price" value="<?= $currentLot['mevcutEnYuksekTeklif'] ?>">
            <button type="submit" class="<?= $isWatched ? 'btn-gold' : 'btn-outline-gold' ?> btn-sm">
              <?= $isWatched ? '★ Takip Ediliyor' : '☆ Takip Et' ?>
            </button>
          </form>
        </div>
      </div>

      <div class="auction-detail-grid">
        <!-- Sol: Görsel ve Açıklama & Ekspertiz Raporu -->
        <div>
          <div class="auction-image-box">
            <div class="live-badge">
              <span class="live-pulse"></span> CANLI YAYIN
            </div>
            <!-- Antika / Saat Temsili Görsel Alanı -->
            <div style="text-align:center;">
              <div style="font-size:5rem;">⏱️</div>
              <div style="color:var(--text-muted); font-size:0.85rem; margin-top:8px;">
                <?= htmlspecialchars($currentLot['baslik']) ?> (Orijinal Kronograf Kalibre)
              </div>
            </div>
          </div>

          <div style="margin-top:20px; line-height:1.7; color:#c9d1d9;">
            <p>
              1968 İsviçre Vintage Saat: Nadir mekanik kalibre, kusursuz dönem orijinal kadranı, tepe ve tokasıyla eksiksiz koleksiyon niteliğindedir. Bağımsız ekspertiz heyetimizce fiziksel ve optik mikroskop değerlendirmelerinden geçirilmiştir.
            </p>
          </div>

          <!-- Ekspertiz Raporu Kutusu (SRS Sayfa 33'teki Damgalı Rapor Alanı) -->
          <div class="appraisal-box">
            <div>
              <div style="color:var(--gold-bright); font-weight:700; font-size:1rem; margin-bottom:6px;">
                📋 Ekspertiz Raporu (Akredite Uzman Heyeti)
              </div>
              <ul style="padding-left:18px; color:var(--text-muted); font-size:0.85rem; line-height:1.6;">
                <li>Mekanizma ve kronograf fonksiyonları laboratuvar testlerinde kusursuz çalışmaktadır.</li>
                <li>Kasa, kadran ve ibreler %100 dönem fabrikasyon orijinali olup revizyon görmemiştir.</li>
                <li>E-imzalı blokzincir QR kodu ile orijinalliği platform garantisi altındadır.</li>
              </ul>
              <div style="font-size:0.75rem; color:var(--text-muted); margin-top:8px;">
                Ekspertiz Damgası: <strong>EXP-2026-112</strong> • Uzman: Burak Şahin
              </div>
            </div>
            <div class="expert-stamp">
              MARTELLO<br>EKSPERTİZ<br>SEÇKİ<br>★ ★ ★
            </div>
          </div>
        </div>

        <!-- Sağ: Teklif Verme, Canlı Sayaç ve Teklif Geçmişi -->
        <div>
          <!-- Canlı Sayaç (SRS FR-35, NFR-2) -->
          <div class="countdown-box">
            <span style="font-size:0.85rem; color:var(--text-muted);">Kalan Süre:</span>
            <span class="countdown-timer live-countdown" data-seconds="185">
              <?= htmlspecialchars($currentLot['kalanSureMetni'] ?? '00:03:05') ?>
            </span>
          </div>

          <div id="antiSnipingWarning" style="display:none; background:rgba(248,81,73,0.15); border:1px solid #f85149; border-radius:6px; padding:8px 12px; margin-bottom:14px; font-size:0.75rem; color:#ff7b72;">
            ⚠️ <strong>Anti-Sniping devrede:</strong> Son 30 saniye içinde pey verilirse süre otomatik +30 sn uzatılacaktır.
          </div>

          <!-- Güncel Fiyat -->
          <div style="background:#0e1218; border:1px solid var(--border-color); border-radius:8px; padding:16px; margin-bottom:18px; text-align:center;">
            <div style="font-size:0.8rem; color:var(--text-muted); text-transform:uppercase;">Güncel En Yüksek Teklif</div>
            <div style="font-size:2.2rem; font-weight:800; color:var(--gold-bright); margin:6px 0;">
              <?= $formatPrice((float)$currentLot['mevcutEnYuksekTeklif']) ?>
            </div>
            <div style="font-size:0.8rem; color:var(--text-muted);">
              Lider Alıcı: <strong style="color:#58a6ff;"><?= htmlspecialchars($currentLot['liderAnonimKod'] ?? 'yortunan...') ?></strong>
            </div>
          </div>

          <!-- Manuel Pey Formu (FR-13) -->
          <form method="POST" style="margin-bottom:20px;">
            <input type="hidden" name="action" value="place_bid">
            <input type="hidden" name="lot_id" value="<?= $currentLot['id'] ?>">
            <input type="hidden" name="user_id" value="<?= $buyerId ?>">
            <input type="hidden" id="remainingSecondsInput" name="remaining_seconds" value="185">

            <div class="form-group">
              <label class="form-label">Teklifiniz (Asgari: <?= $formatPrice($minNextBid) ?>)</label>
              <input type="number" step="100" id="bidAmountInput" name="bid_amount" class="form-control" value="<?= $minNextBid ?>" required>
            </div>

            <div style="display:grid; grid-template-columns:1fr 1fr 1fr; gap:6px; margin-bottom:12px;">
              <button type="button" class="btn-outline-gold btn-sm btn-quick-bid" data-add="1500">+1.500</button>
              <button type="button" class="btn-outline-gold btn-sm btn-quick-bid" data-add="3000">+3.000</button>
              <button type="button" class="btn-outline-gold btn-sm btn-quick-bid" data-add="5000">+5.000</button>
            </div>

            <button type="submit" class="btn-gold" style="width:100%; justify-content:center; padding:12px;">
              🔨 Pey Ver (Teklif Gönder)
            </button>
          </form>

          <!-- Otomatik / Temsili Teklif Formu (Bölüm 2.6.1 & NFR-24) -->
          <div style="background:#11161e; border:1px solid var(--border-color); border-radius:8px; padding:14px; margin-bottom:20px;">
            <div style="font-weight:600; font-size:0.85rem; color:#fff; margin-bottom:6px;">🤖 Otomatik Teklif (Proxy Bidding)</div>
            <p style="font-size:0.75rem; color:var(--text-muted); margin-bottom:10px;">
              Maksimum bütçenizi girin; sistem rakip pey geldikçe sizin adınıza en düşük adımla otomatik teklif versin.
            </p>
            <form method="POST">
              <input type="hidden" name="action" value="set_proxy_bid">
              <input type="hidden" name="lot_id" value="<?= $currentLot['id'] ?>">
              <input type="hidden" name="user_id" value="<?= $buyerId ?>">
              <div style="display:flex; gap:8px;">
                <input type="number" step="1000" name="max_budget" class="form-control" placeholder="Tavan Bütçe (örn: 60000)" required>
                <button type="submit" class="btn-outline-gold btn-sm" style="white-space:nowrap;">Kur</button>
              </div>
            </form>
          </div>

          <!-- Diğer Teklifler (Pey Geçmişi — Sayfa 33 Sağ Sütun) -->
          <div>
            <div style="font-weight:600; font-size:0.85rem; color:var(--text-muted); margin-bottom:8px;">
              📜 Teklif Geçmişi (Anonim İstemciler)
            </div>
            <ul class="bid-history-list">
              <?php if (empty($lotBids)): ?>
                <li class="bid-history-item" style="color:var(--text-muted);">Henüz teklif girilmedi.</li>
              <?php else: ?>
                <?php foreach ($lotBids as $idx => $b): ?>
                  <li class="bid-history-item <?= $idx === 0 ? 'leader' : '' ?>">
                    <div>
                      <span style="font-weight:600; color:<?= $idx === 0 ? 'var(--gold-bright)' : '#fff' ?>;">
                        👤 <?= htmlspecialchars($b['aliciAnonimKod'] ?? 'Anonim') ?>
                      </span>
                      <?php if ($idx === 0): ?>
                        <span class="badge badge-gold" style="font-size:0.65rem; margin-left:4px;">LİDER</span>
                      <?php endif; ?>
                    </div>
                    <div style="text-align:right;">
                      <div style="font-weight:700; color:#fff;"><?= $formatPrice((float)$b['teklifTutari']) ?></div>
                      <div style="font-size:0.7rem; color:var(--text-muted);"><?= htmlspecialchars($b['saatEtiketi'] ?? 'Yeni') ?></div>
                    </div>
                  </li>
                <?php endforeach; ?>
              <?php endif; ?>
            </ul>
          </div>
        </div>
      </div>
    </div>

    <!-- 2. BÖLÜM: KAZANILAN LOTLAR (SRS Sayfa 33 Alt Görsel ile Birebir Uyumlu) -->
    <div id="kazanilanlar" class="panel-card">
      <div class="panel-header">
        <div>
          <span style="font-size:0.75rem; color:var(--gold-primary); text-transform:uppercase;">Alıcı / Çalışma Alanı</span>
          <h2 class="panel-title" style="font-size:1.5rem; margin-top:4px;">Kazanılan Lotlar</h2>
          <p style="font-size:0.85rem; color:var(--text-muted);">Müzayede deneyiminizi profesyonelce yönetin.</p>
        </div>
        <span class="badge badge-success"><?= count($wonOrders) ?> Adet Sipariş</span>
      </div>

      <table class="data-table">
        <thead>
          <tr>
            <th>Lot</th>
            <th>Ürün</th>
            <th>Kategori</th>
            <th>Son Tarih</th>
            <th>Son Fiyat</th>
            <th>Ödeme Durumu</th>
            <th>İşlemler</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($wonOrders as $ord): ?>
            <tr>
              <td>
                <span class="badge badge-gold"><?= htmlspecialchars($ord['lotKodu'] ?? 'AUC-000') ?></span>
              </td>
              <td style="font-weight:600; color:#fff;">
                <?= htmlspecialchars($ord['urunBaslik'] ?? 'Koleksiyon Eseri') ?>
              </td>
              <td style="color:var(--text-muted);"><?= htmlspecialchars($ord['kategoriAdi'] ?? 'Sanat') ?></td>
              <td><?= htmlspecialchars($ord['sonTarih'] ?? '12-Ekim-2026') ?></td>
              <td style="font-weight:700; color:var(--gold-bright);">
                <?= $formatPrice((float)$ord['kapanisFiyati']) ?>
              </td>
              <td>
                <span class="badge <?= ($ord['siparisDurumu'] ?? '') === 'Ödendi' ? 'badge-success' : 'badge-warning' ?>">
                  <?= htmlspecialchars($ord['siparisDurumu'] ?? 'Ödendi') ?>
                </span>
              </td>
              <td>
                <div style="display:flex; gap:8px;">
                  <button type="button" class="btn-outline-gold btn-sm" onclick="alert('Fatura No: FAT-2026-1088\nToplam Tutar: <?= $formatPrice((float)$ord['kapanisFiyati']) ?>\nDurum: Ödendi')">
                    Faturayı İndir
                  </td>
                  <td>
                  <button type="button" class="btn-gold btn-sm" onclick="alert('Kargo Takip No: <?= htmlspecialchars($ord['kargoTakipNo'] ?? 'KRG-99887766') ?>\nDurum: Özel Sanat Lojistiği / Dağıtımda')">
                    Kargoyu Takip Et
                  </button>
                </div>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>

      <!-- Ödeme Detayları Kartı (Sayfa 33 Altındaki Kutu) -->
      <div style="margin-top:24px; background:#0e1218; border:1px solid var(--border-color); border-radius:8px; padding:18px; max-width:400px;">
        <div style="font-weight:600; font-size:0.9rem; color:var(--gold-bright); margin-bottom:12px; border-bottom:1px solid rgba(255,255,255,0.06); padding-bottom:6px;">
          Ödeme Detayları
        </div>
        <div style="display:flex; justify-content:space-between; margin-bottom:6px; font-size:0.85rem;">
          <span style="color:var(--text-muted);">Lot:</span>
          <span>AUC-0991</span>
        </div>
        <div style="display:flex; justify-content:space-between; margin-bottom:6px; font-size:0.85rem;">
          <span style="color:var(--text-muted);">Son Fiyat:</span>
          <span style="font-weight:600;"><?= $formatPrice(12800) ?></span>
        </div>
        <div style="display:flex; justify-content:space-between; margin-bottom:6px; font-size:0.85rem;">
          <span style="color:var(--text-muted);">Ödeme Durumu:</span>
          <span style="color:#3fb950; font-weight:600;">Ödendi (3D Secure)</span>
        </div>
        <div style="display:flex; justify-content:space-between; font-size:0.95rem; font-weight:700; color:var(--gold-bright); border-top:1px solid var(--border-color); padding-top:8px; margin-top:8px;">
          <span>Ödeme Toplam:</span>
          <span><?= $formatPrice(12800) ?></span>
        </div>
      </div>
    </div>

    <!-- 3. BÖLÜM: CÜZDANA BAKİYE YÜKLEME VE İHTİLAF / İTİRAZ TALEBİ -->
    <div style="display:grid; grid-template-columns:1fr 1fr; gap:24px;">
      <!-- Bakiye Yükleme (FR-07/Alıcı) -->
      <div id="bakiye-paneli" class="panel-card">
        <div class="panel-header">
          <h3 class="panel-title">💳 Bakiye Yükle (3D Secure POS)</h3>
        </div>
        <form method="POST">
          <input type="hidden" name="action" value="deposit_wallet">
          <input type="hidden" name="user_id" value="<?= $buyerId ?>">
          <div class="form-group">
            <label class="form-label">Yüklenecek Tutar (TRY)</label>
            <input type="number" name="amount" class="form-control" placeholder="Örn: 25000" min="500" required>
          </div>
          <div class="form-group">
            <label class="form-label">Ödeme Yöntemi</label>
            <select class="form-control">
              <option>Kayıtlı Kart (Garanti BBVA **** 8821 - 3D Secure)</option>
              <option>Yeni Kredi / Banka Kartı</option>
            </select>
          </div>
          <button type="submit" class="btn-gold" style="width:100%; justify-content:center;">
            Güvenli Ödeme Yap (≤3sn Onay)
          </button>
        </form>
      </div>

      <!-- İhtilaf / İtiraz Talebi Oluşturma (FR-20) -->
      <div id="itiraz-paneli" class="panel-card">
        <div class="panel-header">
          <h3 class="panel-title">⚖️ İhtilaf / İtiraz Bildirimi (Tahkim)</h3>
        </div>
        <form method="POST">
          <input type="hidden" name="action" value="create_dispute">
          <input type="hidden" name="user_id" value="<?= $buyerId ?>">
          <input type="hidden" name="lot_id" value="<?= $currentLot['id'] ?>">
          <div class="form-group">
            <label class="form-label">Uyuşmazlık Konusu</label>
            <select name="topic" class="form-control">
              <option value="KONDİSYON_UYUŞMAZLIĞI">Kondisyon Uyuşmazlığı (Katalog Farkı)</option>
              <option value="KARGO_HASAR">Kargo Hasar / Teslimat Gecikmesi</option>
              <option value="ORIJINALLIK_ITIRAZ">Orijinallik Şüphesi / Ek Ekspertiz Talebi</option>
            </select>
          </div>
          <div class="form-group">
            <label class="form-label">Açıklama ve Kanıt Detayı</label>
            <textarea name="description" class="form-control" rows="3" placeholder="Uyuşmazlık gerekçenizi yazınız..." required></textarea>
          </div>
          <button type="submit" class="btn-outline-gold" style="width:100%; justify-content:center;">
            İhtilaf Dosyası Aç (Admin Tahkimine Gönder)
          </button>
        </form>
      </div>
    </div>
  </main>
</div>
