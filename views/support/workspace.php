<?php
declare(strict_types=1);

/** @var array $db */
/** @var callable $formatPrice */
/** @var \Martello\Controllers\WebAndApiController $controller */

$supportId = (!empty($_SESSION['auth_user']['id']) && strtoupper((string)($_SESSION['auth_user']['rol'] ?? '')) === 'CANLI_DESTEK')
    ? (int)$_SESSION['auth_user']['id']
    : 5; // Elif Çelik (Canlı Destek)
$chatSessions = $db['liveChatSessions'] ?? [];
$activeSession = !empty($chatSessions) ? $chatSessions[0] : null;
$shipments = $db['shipments'] ?? [];
$disputes = $db['disputes'] ?? [];
$allTickets = $controller->getSupportTickets();
$ticketStats = $controller->getSupportTicketStatistics();
$activeTab = $_GET['tab'] ?? 'sohbet';
?>

<div class="app-container">
  <aside class="sidebar">
    <div>
      <div class="sidebar-section-title">Canlı Destek Masası</div>
      <ul class="nav-menu">
        <li class="nav-menu-item <?= $activeTab === 'sohbet' ? 'active' : '' ?>">
          <button type="button" data-tab="sohbet" onclick="switchSupportTab('sohbet', this)">Canlı Sohbet &amp; Genel</button>
        </li>
        <li class="nav-menu-item <?= $activeTab === 'biletler' ? 'active' : '' ?>">
          <button type="button" data-tab="biletler" onclick="switchSupportTab('biletler', this)">Destek Biletleri (<?= (int)$ticketStats['open'] ?>)</button>
        </li>
        <li class="nav-menu-item <?= $activeTab === 'kargo' ? 'active' : '' ?>">
          <button type="button" data-tab="kargo" onclick="switchSupportTab('kargo', this)">Kargo &amp; Lojistik Takibi</button>
        </li>
        <li class="nav-menu-item <?= $activeTab === 'ihtilaf' ? 'active' : '' ?>">
          <button type="button" data-tab="ihtilaf" onclick="switchSupportTab('ihtilaf', this)">İhtilaf Eskalasyonu (<?= count($disputes) ?>)</button>
        </li>
      </ul>
    </div>

    <div style="margin-top:16px;">
      <div class="sidebar-section-title">Müzayede Salonları</div>
      <ul class="nav-menu">
        <li class="nav-menu-item"><a href="?role=buyer&tab=canli">Canlı Müzayede Salonları</a></li>
        <li class="nav-menu-item"><a href="?role=buyer&tab=kesfet">Müzayede Keşfet</a></li>
        <li class="nav-menu-item"><a href="?role=buyer&tab=gecmis">Müzayede &amp; Satış Geçmişi</a></li>
        <li class="nav-menu-item"><a href="?role=vitrin">Vitrin &amp; Koleksiyonlar</a></li>
        <li class="nav-menu-item"><a href="?role=yardim" style="color:#C8AA6E; font-weight:500;">Yardım Merkezi</a></li>
      </ul>
    </div>

    <form method="POST" action="index.php" style="margin:12px 0 8px 0;">
      <input type="hidden" name="action" value="logout">
      <button type="submit" class="btn-logout-luxury" style="width:100%; display:flex; align-items:center; justify-content:center; gap:8px; padding:10px 14px; font-size:0.82rem; cursor:pointer;">
        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
        <span>Çıkış Yap</span>
      </button>
    </form>
  </aside>

  <main class="main-content">

    <!-- 1. TAB: CANLI SOHBET & GENEL -->
    <div id="supTab_sohbet" class="tab-content <?= $activeTab === 'sohbet' ? 'active' : '' ?>">
      <div class="page-breadcrumb">CANLI DESTEK / OPERASYON PANELİ</div>
      <h1 class="page-heading">Canlı Destek ve Müşteri İletişimi</h1>
      <p class="page-subheading">Koleksiyoner görüşmelerini, kargo durumlarını ve destek biletlerini yönetin.</p>

      <div class="kpi-grid" style="grid-template-columns:repeat(4, 1fr);">
        <div class="kpi-card highlight" style="cursor:pointer;" onclick="switchSupportTab('sohbet')">
          <div class="kpi-label">Aktif Görüşme</div>
          <div class="kpi-value"><?= count($chatSessions) ?></div>
        </div>
        <div class="kpi-card" style="cursor:pointer;" onclick="switchSupportTab('biletler')">
          <div class="kpi-label">Açık Destek Bileti</div>
          <div class="kpi-value"><?= (int)$ticketStats['open'] ?></div>
        </div>
        <div class="kpi-card" style="cursor:pointer;" onclick="switchSupportTab('biletler')">
          <div class="kpi-label">Çözülen Bilet</div>
          <div class="kpi-value"><?= (int)$ticketStats['resolved'] ?></div>
        </div>
        <div class="kpi-card" style="cursor:pointer;" onclick="switchSupportTab('ihtilaf')">
          <div class="kpi-label">Açık İhtilaf</div>
          <div class="kpi-value" style="color:#E2C992;"><?= count($disputes) ?></div>
        </div>
      </div>

      <div style="display:grid; grid-template-columns:1.45fr 1fr; gap:20px; align-items:start;">
        <!-- Canlı Sohbet Kutusu -->
        <div class="panel-card" style="margin-bottom:0; display:flex; flex-direction:column; height:520px;">
          <div class="panel-header">
            <div>
              <h3 class="panel-title">Canlı Sohbet: <?= htmlspecialchars($activeSession['kullaniciAd'] ?? 'Zeynep Demir') ?></h3>
              <span style="font-size:0.74rem; color:#A39B8F;">Oturum Kodu: <?= htmlspecialchars($activeSession['oturumId'] ?? 'CHAT-1') ?></span>
            </div>
            <span class="badge badge-success">Görüşme Aktif</span>
          </div>

          <div style="flex:1; overflow-y:auto; padding:14px; background:#0D0B0A; border-radius:8px; border:1px solid rgba(200,170,110,0.16); display:flex; flex-direction:column; gap:12px; margin-bottom:14px;">
            <?php foreach ($activeSession['mesajlar'] ?? [] as $msg): ?>
              <?php $isSupport = (int)($msg['gonderenId'] ?? 0) === $supportId; ?>
              <div style="align-self:<?= $isSupport ? 'flex-end' : 'flex-start' ?>; max-width:80%;">
                <div style="font-size:0.72rem; color:#A39B8F; margin-bottom:3px; text-align:<?= $isSupport ? 'right' : 'left' ?>;">
                  <?= htmlspecialchars($msg['gonderenAd'] ?? '') ?> · <?= htmlspecialchars($msg['zaman'] ?? '') ?>
                </div>
                <div style="padding:10px 14px; border-radius:10px; font-size:0.85rem; background:<?= $isSupport ? 'rgba(200,170,110,0.16)' : '#141210' ?>; color:#F5F0E6; border:1px solid <?= $isSupport ? '#C8AA6E' : 'rgba(200,170,110,0.18)' ?>;">
                  <?= htmlspecialchars($msg['icerik'] ?? '') ?>
                </div>
              </div>
            <?php endforeach; ?>
          </div>

          <!-- Hızlı Yanıtlar -->
          <div style="display:flex; gap:6px; flex-wrap:wrap; margin-bottom:10px;">
            <button type="button" class="btn-outline-gold btn-sm" style="font-size:0.72rem;" onclick="document.getElementById('supChatMsgInput').value='Eserlerimiz T.C. Kültür Bakanlığı lisanslı eksper onayından geçmiştir.'">Ekspertiz Bilgisi</button>
            <button type="button" class="btn-outline-gold btn-sm" style="font-size:0.72rem;" onclick="document.getElementById('supChatMsgInput').value='Pey verirken asgari artış kuralı geçerlidir; teminatınız emanet hesabında güvendedir.'">Pey Kuralları</button>
            <button type="button" class="btn-outline-gold btn-sm" style="font-size:0.72rem;" onclick="document.getElementById('supChatMsgInput').value='Kargonuz özel sigortalı sanat kuryesi ile yola çıkmıştır.'">Kargo Durumu</button>
          </div>

          <form method="POST" style="display:flex; gap:8px;">
            <input type="hidden" name="action" value="send_chat">
            <input type="hidden" name="session_id" value="<?= $activeSession['id'] ?? 951 ?>">
            <input type="hidden" name="sender_id" value="<?= $supportId ?>">
            <input type="hidden" name="sender_name" value="Elif Çelik (Canlı Destek)">
            <input type="text" id="supChatMsgInput" name="message" class="form-control" placeholder="Müşteriye yanıtınızı yazınız..." required>
            <button type="submit" class="btn-gold" style="white-space:nowrap;">Gönder &rarr;</button>
          </form>
        </div>

        <!-- Sağ: Müşteri Bilgileri & Hızlı Erişim -->
        <div style="display:flex; flex-direction:column; gap:16px;">
          <div class="panel-card" style="margin-bottom:0;">
            <div class="panel-header">
              <h3 class="panel-title">Görüşülen Koleksiyoner</h3>
              <span class="badge badge-gold">Onaylı KYC</span>
            </div>
            <div style="display:flex; align-items:center; gap:12px; margin-bottom:12px;">
              <img src="public/assets/images/avatar_zeynep.jpg" alt="Zeynep Demir" style="width:44px; height:44px; border-radius:50%; object-fit:cover; border:1px solid rgba(200,170,110,0.45);">
              <div>
                <div style="font-size:0.9rem; color:#F5F0E6; font-weight:600;">Zeynep Demir (#3)</div>
                <div style="font-size:0.75rem; color:#C8AA6E;">GOLD VIP · Limit: <?= $formatPrice(120000) ?></div>
              </div>
            </div>
            <div style="font-size:0.79rem; color:#A39B8F; line-height:1.6; padding:10px 12px; background:#0D0B0A; border:1px solid rgba(200,170,110,0.14); border-radius:8px;">
              <div>E-Posta: <strong style="color:#EAE2D5;">zeynep@koleksiyon.com</strong></div>
              <div>Telefon: <strong style="color:#EAE2D5;">+90 544 333 44 55</strong></div>
            </div>
          </div>

          <div class="panel-card" style="margin-bottom:0;">
            <div class="panel-header">
              <h3 class="panel-title">Hızlı Operasyon Masaları</h3>
            </div>
            <div style="display:flex; flex-direction:column; gap:8px;">
              <button type="button" class="btn-gold" style="width:100%; justify-content:space-between;" onclick="switchSupportTab('biletler')">
                <span>Müşteri Destek Biletlerini Yönet (<?= count($allTickets) ?>)</span><span>&rarr;</span>
              </button>
              <button type="button" class="btn-outline-gold" style="width:100%; display:flex; justify-content:space-between;" onclick="switchSupportTab('kargo')">
                <span>Kargo &amp; Teslimat Durumu Güncelle</span><span>&rarr;</span>
              </button>
              <button type="button" class="btn-outline-gold" style="width:100%; display:flex; justify-content:space-between;" onclick="switchSupportTab('ihtilaf')">
                <span>Acil İhtilaf Eskalasyonu Aç</span><span>&rarr;</span>
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- 2. TAB: DESTEK BİLETLERİ -->
    <div id="supTab_biletler" class="tab-content <?= $activeTab === 'biletler' ? 'active' : '' ?>">
      <div class="page-breadcrumb">CANLI DESTEK / DESTEK BİLETLERİ</div>
      <div style="display:flex; justify-content:space-between; align-items:flex-start; flex-wrap:wrap; gap:12px; margin-bottom:20px;">
        <div>
          <h1 class="page-heading">Müşteri Destek Biletleri</h1>
          <p class="page-subheading" style="margin-bottom:0;">Yardım Merkezi üzerinden oluşturulan talepleri yanıtlayın ve çözüme kavuşturun.</p>
        </div>
        <div style="display:flex; gap:8px;">
          <span class="badge badge-warning"><?= (int)$ticketStats['open'] ?> Açık</span>
          <span class="badge badge-success"><?= (int)$ticketStats['resolved'] ?> Çözüldü</span>
        </div>
      </div>

      <div style="display:grid; grid-template-columns:repeat(auto-fill, minmax(400px, 1fr)); gap:16px;">
        <?php foreach ($allTickets as $t): ?>
          <?php $durum = strtoupper((string)($t['durum'] ?? 'ACIK')); ?>
          <div class="panel-card" style="margin-bottom:0; display:flex; flex-direction:column; justify-content:space-between;">
            <div>
              <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:10px;">
                <div>
                  <span class="badge badge-gold"><?= htmlspecialchars((string)$t['biletNo']) ?></span>
                  <span style="color:#A39B8F; font-size:0.78rem; margin-left:8px;"><?= htmlspecialchars((string)$t['kullaniciAd']) ?></span>
                </div>
                <span class="badge <?= $durum === 'COZULDU' ? 'badge-success' : 'badge-warning' ?>"><?= htmlspecialchars($durum) ?></span>
              </div>
              <h3 style="font-family:var(--font-serif); font-size:1.15rem; color:#F5F0E6; margin:0 0 8px 0;">
                <?= htmlspecialchars((string)$t['konu']) ?>
              </h3>
              <p style="font-size:0.82rem; color:#A39B8F; line-height:1.55; margin:0 0 14px 0;">
                <?= htmlspecialchars((string)$t['mesaj']) ?>
              </p>
            </div>

            <?php if ($durum !== 'COZULDU'): ?>
              <div style="border-top:1px solid rgba(200,170,110,0.12); padding-top:12px;">
                <form method="POST" style="display:flex; gap:8px; margin-bottom:8px;">
                  <input type="hidden" name="action" value="reply_support_ticket">
                  <input type="hidden" name="ticket_id" value="<?= (int)$t['id'] ?>">
                  <input type="hidden" name="user_id" value="<?= $supportId ?>">
                  <input type="hidden" name="user_name" value="Elif Çelik (Canlı Destek)">
                  <input type="hidden" name="user_role" value="DESTEK_UZMANI">
                  <input type="hidden" name="new_status" value="YANITLANDI">
                  <input type="text" name="reply_text" class="form-control" placeholder="Müşteriye yanıt yaz..." style="padding:7px 10px; font-size:0.8rem;" required>
                  <button type="submit" class="btn-gold btn-sm">Yanıtla</button>
                </form>
                <form method="POST" style="margin:0;">
                  <input type="hidden" name="action" value="resolve_support_ticket">
                  <input type="hidden" name="ticket_id" value="<?= (int)$t['id'] ?>">
                  <input type="hidden" name="user_id" value="<?= $supportId ?>">
                  <input type="hidden" name="user_name" value="Elif Çelik (Canlı Destek)">
                  <input type="hidden" name="resolution_note" value="Talep temsilci tarafından incelenip bilgilendirme sağlandı.">
                  <button type="submit" class="btn-outline-gold btn-sm" style="width:100%; justify-content:center;">Çözümlendi Olarak Kapat</button>
                </form>
              </div>
            <?php endif; ?>
          </div>
        <?php endforeach; ?>
      </div>
    </div>

    <!-- 3. TAB: KARGO & LOJİSTİK TAKİBİ -->
    <div id="supTab_kargo" class="tab-content <?= $activeTab === 'kargo' ? 'active' : '' ?>">
      <div class="page-breadcrumb">CANLI DESTEK / KARGO VE LOJİSTİK</div>
      <h1 class="page-heading">Kargo &amp; Özel Sanat Taşıma Takibi</h1>
      <p class="page-subheading">Sigortalı eser sevkiyatlarını sorgulayın ve teslimat durumlarını güncelleyin.</p>

      <div class="panel-card" style="max-width:640px;">
        <div class="panel-header">
          <h3 class="panel-title">Kargo Teslimat Sorgulama</h3>
          <span class="badge badge-gold">Zırhlı Kurye</span>
        </div>
        <div class="form-group">
          <label class="form-label">Kargo veya Lot Takip Numarası</label>
          <input type="text" id="kargoInput" class="form-control" value="KRG-99887766">
        </div>
        <div style="background:#0D0B0A; border:1px solid rgba(200,170,110,0.16); border-radius:8px; padding:14px; margin-top:12px;">
          <div style="font-size:0.7rem; color:#A39B8F; text-transform:uppercase; letter-spacing:0.6px;">GÜNCEL SEVKİYAT DURUMU</div>
          <div style="color:#E2C992; font-family:var(--font-serif); font-size:1.15rem; font-weight:600; margin:4px 0;">Dağıtımda / Özel Sanat Taşıma</div>
          <div style="font-size:0.82rem; color:#A39B8F; margin-bottom:12px;">
            Özel sigortalı ahşap sandıklama tamamlandı, kurye teslimat adresine doğru yola çıktı.
          </div>
          <form method="POST" style="margin:0;">
            <input type="hidden" name="action" value="update_shipment">
            <input type="hidden" name="shipment_id" value="701">
            <input type="hidden" name="status" value="Teslim Edildi">
            <button type="submit" class="btn-gold btn-sm">'Teslim Edildi' Olarak İşaretle</button>
          </form>
        </div>
      </div>
    </div>

    <!-- 4. TAB: İHTİLAF ESKALASYONU -->
    <div id="supTab_ihtilaf" class="tab-content <?= $activeTab === 'ihtilaf' ? 'active' : '' ?>">
      <div class="page-breadcrumb">CANLI DESTEK / İHTİLAF ESKALASYONU</div>
      <h1 class="page-heading">Acil İhtilaf ve Tahkim Eskalasyonu</h1>
      <p class="page-subheading">Canlı görüşme sırasında tespit edilen uyuşmazlıkları doğrudan Hakem Heyetine iletin.</p>

      <div style="display:grid; grid-template-columns:1fr 1.2fr; gap:20px; align-items:start;">
        <div class="panel-card" style="margin-bottom:0;">
          <div class="panel-header">
            <h3 class="panel-title">Canlı Destekten Acil İhtilaf Aç</h3>
            <span class="badge badge-gold">Emanet Korumalı</span>
          </div>
          <form method="POST">
            <input type="hidden" name="action" value="create_dispute">
            <input type="hidden" name="user_id" value="3">
            <input type="hidden" name="lot_id" value="1002">
            <div class="form-group">
              <label class="form-label">Uyuşmazlık Konusu</label>
              <input type="text" name="topic" class="form-control" value="Müşteri Görüşme Talebi / Ödeme İtirazı" required>
            </div>
            <div class="form-group">
              <label class="form-label">Acil Durum Notu &amp; Bulgular</label>
              <textarea name="description" class="form-control" rows="3" placeholder="Görüşme özeti ve eskalasyon gerekçesi..." required></textarea>
            </div>
            <button type="submit" class="btn-gold" style="width:100%; justify-content:center;">İhtilaf Kuruluna İlet</button>
          </form>
        </div>

        <div class="panel-card" style="margin-bottom:0;">
          <div class="panel-header">
            <h3 class="panel-title">Aktif Uyuşmazlık Dosyaları</h3>
            <span class="badge badge-warning"><?= count($disputes) ?> Dosya</span>
          </div>
          <div style="display:flex; flex-direction:column; gap:10px;">
            <?php foreach ($disputes as $disp): ?>
              <div style="padding:12px 14px; background:#0D0B0A; border:1px solid rgba(200,170,110,0.14); border-radius:8px;">
                <div style="display:flex; justify-content:space-between; margin-bottom:6px;">
                  <span class="badge badge-gold"><?= htmlspecialchars((string)$disp['talepNo']) ?></span>
                  <span class="badge badge-info"><?= htmlspecialchars((string)$disp['durum']) ?></span>
                </div>
                <div style="font-size:0.86rem; color:#F5F0E6; font-weight:600; margin-bottom:4px;">
                  <?= htmlspecialchars((string)$disp['konuTipi']) ?>
                </div>
                <div style="font-size:0.79rem; color:#A39B8F;">
                  <?= htmlspecialchars((string)$disp['aciklama']) ?>
                </div>
              </div>
            <?php endforeach; ?>
          </div>
        </div>
      </div>
    </div>

  </main>
</div>

<script>
function switchSupportTab(tabId, btn) {
  document.querySelectorAll('.tab-content').forEach(el => el.classList.remove('active'));
  document.querySelectorAll('.sidebar .nav-menu-item').forEach(el => el.classList.remove('active'));
  const target = document.getElementById('supTab_' + tabId);
  if (target) target.classList.add('active');
  let navItem = btn && btn.closest ? btn.closest('.nav-menu-item') : null;
  if (!navItem) {
    const match = document.querySelector('.sidebar button[data-tab="' + tabId + '"]');
    if (match) navItem = match.closest('.nav-menu-item');
  }
  if (navItem) navItem.classList.add('active');
  if (window.history && window.history.replaceState) {
    try {
      const url = new URL(window.location.href);
      url.searchParams.set('tab', tabId);
      window.history.replaceState({}, '', url);
    } catch(e) {}
  }
}
</script>
