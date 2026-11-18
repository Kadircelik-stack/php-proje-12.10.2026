<?php
declare(strict_types=1);

/** @var array $db */

$supportId = 5; // Elif Çelik (Canlı Destek)
$chatSessions = $db['liveChatSessions'] ?? [];
$activeSession = !empty($chatSessions) ? $chatSessions[0] : null;
$shipments = $db['shipments'] ?? [];
$disputes = $db['disputes'] ?? [];
?>

<div class="app-container">
  <aside class="sidebar">
    <div>
      <div class="sidebar-section-title">Çalışma Alanı</div>
      <ul class="nav-menu">
        <li class="nav-menu-item active"><a href="?role=support">💬 Canlı Sohbet</a></li>
        <li class="nav-menu-item"><a href="#kargo-takip">📦 Kargo Takibi</a></li>
        <li class="nav-menu-item"><a href="#destek-talepleri">🎫 Destek Talepleri</a></li>
        <li class="nav-menu-item"><a href="#ihtilaf-yonetimi">⚖️ İhtilaf Eskalasyonu</a></li>
      </ul>
    </div>

    <div class="sidebar-demo-badge" style="margin-top:auto;">
      <div style="font-weight:700; font-size:0.75rem; color:#d4af37; letter-spacing:0.5px;">MARTELLO / DEMO</div>
      <div style="font-size:0.7rem; color:#888; margin-top:2px;">İşlemler sunum amaçlıdır.</div>
    </div>
  </aside>

  <main class="main-content">
    <div style="font-size:0.8rem; color:var(--gold-primary); text-transform:uppercase; margin-bottom:4px;">
      Canlı Destek / Operasyon Paneli
    </div>
    <h1 style="font-size:2rem; color:#fff; font-weight:600; margin-bottom:24px;">Canlı Destek ve Kargo Takip</h1>

    <div style="display:grid; grid-template-columns:1.4fr 1fr; gap:24px;">
      <!-- Canlı Sohbet Kutusu -->
      <div class="panel-card" style="display:flex; flex-direction:column; height:560px;">
        <div class="panel-header">
          <div>
            <h3 class="panel-title">💬 Canlı Sohbet: <?= htmlspecialchars($activeSession['kullaniciAd'] ?? 'Müşteri') ?></h3>
            <span style="font-size:0.75rem; color:var(--text-muted);">Oturum ID: <?= htmlspecialchars($activeSession['oturumId'] ?? 'CHAT-1') ?></span>
          </div>
          <span class="badge badge-success">Görüşme Aktif</span>
        </div>

        <!-- Mesaj Akışı -->
        <div style="flex:1; overflow-y:auto; padding:14px; background:#0e1218; border-radius:8px; border:1px solid var(--border-color); display:flex; flex-direction:column; gap:12px; margin-bottom:14px;">
          <?php foreach ($activeSession['mesajlar'] ?? [] as $msg): ?>
            <?php $isSupport = (int)($msg['gonderenId'] ?? 0) === $supportId; ?>
            <div style="align-self:<?= $isSupport ? 'flex-end' : 'flex-start' ?>; max-width:80%;">
              <div style="font-size:0.75rem; color:var(--text-muted); margin-bottom:2px; text-align:<?= $isSupport ? 'right' : 'left' ?>;">
                <?= htmlspecialchars($msg['gonderenAd'] ?? '') ?> • <?= htmlspecialchars($msg['zaman'] ?? '') ?>
              </div>
              <div style="padding:10px 14px; border-radius:10px; font-size:0.9rem; background:<?= $isSupport ? 'var(--gold-primary)' : 'var(--bg-card)' ?>; color:<?= $isSupport ? '#0c0f14' : '#fff' ?>; border:1px solid <?= $isSupport ? 'var(--gold-bright)' : 'var(--border-color)' ?>;">
                <?= htmlspecialchars($msg['icerik'] ?? '') ?>
              </div>
            </div>
          <?php endforeach; ?>
        </div>

        <!-- Yanıt Gönder Formu -->
        <form method="POST" style="display:flex; gap:8px;">
          <input type="hidden" name="action" value="send_chat">
          <input type="hidden" name="session_id" value="<?= $activeSession['id'] ?? 951 ?>">
          <input type="hidden" name="sender_id" value="<?= $supportId ?>">
          <input type="hidden" name="sender_name" value="Elif Ç. (Canlı Destek)">
          <input type="text" name="message" class="form-control" placeholder="Müşteriye yanıtınızı yazınız..." required>
          <button type="submit" class="btn-gold" style="white-space:nowrap;">Gönder &rarr;</button>
        </form>
      </div>

      <!-- Sağ: Kargo Takibi & Acil Durum / İhtilaf -->
      <div>
        <div id="kargo-takip" class="panel-card" style="margin-bottom:24px;">
          <div class="panel-header">
            <h3 class="panel-title">📦 Kargo Teslimat Sorgulama</h3>
          </div>
          <div class="form-group">
            <label class="form-label">Kargo veya Lot Takip No</label>
            <input type="text" id="kargoInput" class="form-control" value="KRG-99887766">
          </div>
          <div style="background:#0e1218; border:1px solid var(--border-color); border-radius:8px; padding:12px; margin-top:12px;">
            <div style="font-size:0.75rem; color:var(--text-muted);">GÜNCEL DURUM</div>
            <div style="color:#3fb950; font-weight:700; margin:4px 0;">Dağıtımda / Özel Sanat Taşıma</div>
            <div style="font-size:0.8rem; color:#8b949e;">
              Özel sigortalı ahşap sandıklama tamamlandı, kurye teslimat adresine doğru yola çıktı.
            </div>
          </div>
        </div>

        <div id="ihtilaf-yonetimi" class="panel-card">
          <div class="panel-header">
            <h3 class="panel-title">⚖️ Aktif Uyuşmazlıklar</h3>
            <span class="badge badge-warning"><?= count($disputes) ?></span>
          </div>
          <?php foreach ($disputes as $disp): ?>
            <div style="border-bottom:1px solid rgba(255,255,255,0.06); padding-bottom:10px; margin-bottom:10px;">
              <div style="display:flex; justify-content:space-between; font-size:0.8rem;">
                <span class="badge badge-gold"><?= $disp['talepNo'] ?></span>
                <span class="badge badge-info"><?= $disp['durum'] ?></span>
              </div>
              <div style="font-size:0.85rem; color:#fff; font-weight:600; margin-top:6px;">
                <?= htmlspecialchars($disp['konuTipi']) ?>
              </div>
              <div style="font-size:0.8rem; color:var(--text-muted); margin-top:2px;">
                <?= htmlspecialchars($disp['aciklama']) ?>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      </div>
    </div>
  </main>
</div>
