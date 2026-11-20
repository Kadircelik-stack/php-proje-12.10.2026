<?php
declare(strict_types=1);

/** @var array $db */
/** @var \Martello\Controllers\WebAndApiController $controller */

$supportId = 5; // Elif Çelik (Canlı Destek)
$chatSessions = $db['liveChatSessions'] ?? [];
$activeSession = !empty($chatSessions) ? $chatSessions[0] : null;
$shipments = $db['shipments'] ?? [];
$disputes = $db['disputes'] ?? [];
$allTickets = $controller->getSupportTickets();
$ticketStats = $controller->getSupportTicketStatistics();
?>

<div class="app-container">
  <aside class="sidebar">
    <div>
      <div class="sidebar-section-title">Çalışma Alanı</div>
      <ul class="nav-menu">
        <li class="nav-menu-item active"><a href="?role=support">Canlı Sohbet</a></li>
        <li class="nav-menu-item"><a href="#kargo-takip">Kargo Takibi</a></li>
        <li class="nav-menu-item"><a href="#destek-talepleri">Destek Biletleri (<?= (int)$ticketStats['open'] ?>)</a></li>
        <li class="nav-menu-item"><a href="#ihtilaf-yonetimi">İhtilaf Eskalasyonu</a></li>
        <li class="nav-menu-item"><a href="?role=yardim" style="color:#38bdf8; font-weight:600;">Yardım Merkezi (Müşteri) ↗</a></li>
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
            <h3 class="panel-title">Canlı Sohbet: <?= htmlspecialchars($activeSession['kullaniciAd'] ?? 'Müşteri') ?></h3>
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

        <!-- Hızlı Yanıtlar (Canned Responses) -->
        <div style="display:flex; gap:6px; flex-wrap:wrap; margin-bottom:8px;">
          <button type="button" class="btn-outline-gold btn-sm" style="font-size:0.72rem; padding:2px 8px;" onclick="document.querySelector('input[name=message]').value='Eserlerimiz T.C. Kültür Bakanlığı lisanslı eksper onayından geçmiştir.'">Ekspertiz Bilgisi</button>
          <button type="button" class="btn-outline-gold btn-sm" style="font-size:0.72rem; padding:2px 8px;" onclick="document.querySelector('input[name=message]').value='Pey verirken asgari artış kuralı geçerlidir; teminatınız emanet hesabında güvendedir.'">Pey Kuralları</button>
          <button type="button" class="btn-outline-gold btn-sm" style="font-size:0.72rem; padding:2px 8px;" onclick="document.querySelector('input[name=message]').value='Kargonuz özel sigortalı sanat kuryesi ile yola çıkmıştır.'">Kargo Durumu</button>
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

      <!-- Sağ: Kullanıcı Bilgisi, Kargo Takibi & Acil Durum / İhtilaf -->
      <div>
        <!-- Kullanıcı Profili ve KYC Bilgileri Kartı (Use Case) -->
        <div class="panel-card" style="margin-bottom:16px;">
          <div class="panel-header">
            <h3 class="panel-title">Müşteri Bilgileri & KYC</h3>
            <span class="badge badge-success">✓ Onaylı KYC</span>
          </div>
          <div style="font-size:0.85rem; color:#fff; font-weight:600; margin-bottom:4px;">
            <?= htmlspecialchars($activeSession['kullaniciAd'] ?? 'Zeynep Demir') ?> (#3)
          </div>
          <div style="font-size:0.8rem; color:var(--text-muted); line-height:1.5;">
            VIP Seviyesi: <strong style="color:#d4af37;">PLATINUM</strong> • Bakiye: <strong><?= $formatPrice(120000) ?></strong><br>
            Kayıtlı E-Posta: zeynep.demir@ornek.com<br>
            Kayıtlı Telefon: +90 532 999 8877
          </div>
        </div>

        <!-- Kargo Takibi -->
        <div id="kargo-takip" class="panel-card" style="margin-bottom:16px;">
          <div class="panel-header">
            <h3 class="panel-title">Kargo Teslimat Sorgulama</h3>
          </div>
          <div class="form-group">
            <label class="form-label">Kargo veya Lot Takip No</label>
            <input type="text" id="kargoInput" class="form-control" value="KRG-99887766">
          </div>
          <div style="background:#0e1218; border:1px solid var(--border-color); border-radius:8px; padding:12px; margin-top:10px;">
            <div style="font-size:0.75rem; color:var(--text-muted);">GÜNCEL DURUM</div>
            <div style="color:#3fb950; font-weight:700; margin:4px 0;">Dağıtımda / Özel Sanat Taşıma</div>
            <div style="font-size:0.8rem; color:#8b949e; margin-bottom:8px;">
              Özel sigortalı ahşap sandıklama tamamlandı, kurye teslimat adresine doğru yola çıktı.
            </div>
            <!-- Kargo Durumu Güncelleme Butonu -->
            <form method="POST" style="margin:0;">
              <input type="hidden" name="action" value="update_shipment">
              <input type="hidden" name="shipment_id" value="701">
              <input type="hidden" name="status" value="Teslim Edildi">
              <button type="submit" class="btn-outline-gold btn-sm" style="font-size:0.75rem;">'Teslim Edildi' Olarak İşaretle</button>
            </form>
          </div>
        </div>

        <!-- Müşteri Destek Biletleri Masası (SRS Canlı/Bilet Destek Kanalı) -->
        <div id="destek-talepleri" class="panel-card" style="margin-bottom:16px;">
          <div class="panel-header" style="display:flex; justify-content:space-between; align-items:center; margin-bottom:12px;">
            <div>
              <h3 class="panel-title" style="margin-bottom:2px;">Müşteri Destek Biletleri</h3>
              <span style="font-size:0.75rem; color:var(--text-muted);">Yardım Merkezinden gelen biletler</span>
            </div>
            <div style="display:flex; gap:6px;">
              <span class="badge badge-warning"><?= (int)$ticketStats['open'] ?> Açık</span>
              <span class="badge badge-success"><?= (int)$ticketStats['resolved'] ?> Çözüldü</span>
            </div>
          </div>

          <div style="display:flex; flex-direction:column; gap:10px; max-height:380px; overflow-y:auto;">
            <?php foreach ($allTickets as $t): ?>
              <?php
                $durum = strtoupper((string)($t['durum'] ?? 'ACIK'));
                $bColor = ($durum === 'COZULDU') ? '#64748b' : (($durum === 'YANITLANDI') ? '#34d399' : '#fbbf24');
              ?>
              <div style="background:#0e1218; border:1px solid #1e293b; border-left:3px solid <?= $bColor ?>; border-radius:6px; padding:10px 12px;">
                <div style="display:flex; justify-content:space-between; align-items:center; font-size:0.72rem; margin-bottom:4px;">
                  <div>
                    <strong style="color:#d4af37;"><?= htmlspecialchars($t['biletNo']) ?></strong>
                    <span style="color:#848d9a; margin-left:4px;"><?= htmlspecialchars($t['kullaniciAd']) ?></span>
                  </div>
                  <span class="badge" style="font-size:0.65rem; padding:1px 5px; color:<?= $bColor ?>; border:1px solid <?= $bColor ?>;">
                    <?= $durum ?>
                  </span>
                </div>
                <div style="font-size:0.82rem; color:#fff; font-weight:600; margin-bottom:4px;">
                  <?= htmlspecialchars($t['konu']) ?>
                </div>
                <div style="font-size:0.75rem; color:#94a3b8; margin-bottom:8px; line-height:1.4;">
                  <?= htmlspecialchars(mb_strimwidth((string)$t['mesaj'], 0, 110, '...')) ?>
                </div>

                <!-- Temsilci Hızlı Yanıt & Çözüm Formu -->
                <?php if ($durum !== 'COZULDU'): ?>
                  <form method="POST" style="display:flex; gap:6px; margin-top:6px;">
                    <input type="hidden" name="action" value="reply_support_ticket">
                    <input type="hidden" name="ticket_id" value="<?= (int)$t['id'] ?>">
                    <input type="hidden" name="user_id" value="<?= $supportId ?>">
                    <input type="hidden" name="user_name" value="Elif Çelik (Canlı Destek)">
                    <input type="hidden" name="user_role" value="DESTEK_UZMANI">
                    <input type="hidden" name="new_status" value="YANITLANDI">
                    <input type="text" name="reply_text" class="form-control" placeholder="Müşteriye yanıt yaz..." style="font-size:0.75rem; padding:4px 8px; background:#161c28;" required>
                    <button type="submit" class="btn-gold-pill btn-sm" style="font-size:0.7rem; padding:3px 10px; white-space:nowrap;">Yanıtla</button>
                  </form>
                  <form method="POST" style="margin-top:4px;">
                    <input type="hidden" name="action" value="resolve_support_ticket">
                    <input type="hidden" name="ticket_id" value="<?= (int)$t['id'] ?>">
                    <input type="hidden" name="user_id" value="<?= $supportId ?>">
                    <input type="hidden" name="user_name" value="Elif Çelik (Canlı Destek)">
                    <input type="hidden" name="resolution_note" value="Talep temsilci tarafından incelenip bilgilendirme sağlandı.">
                    <button type="submit" class="btn-outline-gold btn-sm" style="font-size:0.68rem; padding:2px 8px; width:100%;">✓ Çözümlendi Olarak Kapat</button>
                  </form>
                <?php endif; ?>
              </div>
            <?php endforeach; ?>
          </div>
        </div>

        <!-- Acil Durum / İhtilaf Oluşturma (Use Case) -->
        <div id="ihtilaf-yonetimi" class="panel-card">
          <div class="panel-header">
            <h3 class="panel-title">Aktif Uyuşmazlıklar</h3>
            <span class="badge badge-warning"><?= count($disputes) ?></span>
          </div>
          <form method="POST" style="margin-bottom:14px; background:#0e1218; padding:10px; border-radius:8px; border:1px solid rgba(255,255,255,0.06);">
            <input type="hidden" name="action" value="create_dispute">
            <input type="hidden" name="user_id" value="3">
            <input type="hidden" name="lot_id" value="1002">
            <div style="font-size:0.78rem; color:#d4af37; font-weight:700; margin-bottom:6px;">Canlı Destekten Acil İhtilaf Aç</div>
            <input type="text" name="topic" class="form-control" value="Müşteri Görüşme Talebi / Ödeme İtirazı" style="margin-bottom:6px; font-size:0.8rem;" required>
            <input type="text" name="description" class="form-control" placeholder="Acil durum notu..." style="margin-bottom:8px; font-size:0.8rem;" required>
            <button type="submit" class="btn-gold btn-sm" style="width:100%; font-size:0.78rem;">İhtilaf Kuruluna İlet</button>
          </form>
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
