<?php
declare(strict_types=1);

/** @var callable $formatPrice */
/** @var array $db */
/** @var \Martello\Controllers\WebAndApiController $controller */
/** @var string $activeCurrency */

$helpData    = $controller->getHelpCenterData();
$systemRules = $helpData['systemRules'] ?? [];
$faqList     = $helpData['faqList'] ?? [];

$loggedUser  = $_SESSION['auth_user'] ?? null;
$currentUid  = (int)($loggedUser['id'] ?? 3);
$currentName = (string)($loggedUser['adSoyad'] ?? 'Zeynep Demir');
$currentMail = (string)($loggedUser['email'] ?? 'zeynep@koleksiyon.com');

$userTickets = $controller->getSupportTickets($currentUid);
$chatSessions = $db['liveChatSessions'] ?? [];
$activeSession = !empty($chatSessions) ? $chatSessions[0] : null;

$activeHelpTab = $_GET['htab'] ?? 'sss';
if ($activeHelpTab === 'destek') {
    $activeHelpTab = 'biletler';
}
if (!in_array($activeHelpTab, ['sss', 'kurallar', 'biletler', 'canli_sohbet'], true)) {
    $activeHelpTab = 'sss';
}
?>

<main style="max-width:960px; margin:28px auto 64px auto; padding:0 24px; width:100%;">
  <!-- Sade Üst Başlık ve Arama -->
  <div style="display:flex; justify-content:space-between; align-items:flex-end; flex-wrap:wrap; gap:16px; margin-bottom:22px; padding-bottom:18px; border-bottom:1px solid var(--border-color);">
    <div>
      <div class="page-breadcrumb">MARTELLO / YARDIM</div>
      <h1 class="page-heading" style="margin-bottom:4px;">Yardım Merkezi</h1>
      <p style="font-size:0.82rem; color:#A39B8F; margin:0;">
        Müzayede kuralları, sıkça sorulan sorular ve destek talepleri.
      </p>
    </div>
    <div style="width:280px;">
      <input type="text" id="helpSearchInput" onkeyup="filterHelpItems(this.value)" class="form-control" placeholder="Konu veya soru ara...">
    </div>
  </div>

  <!-- Sade Sekme Çubuğu -->
  <div style="display:flex; gap:8px; flex-wrap:wrap; margin-bottom:22px;">
    <button type="button" data-htab="sss" onclick="switchHelpTab('sss', this)" class="help-tab-btn <?= $activeHelpTab === 'sss' ? 'btn-gold-pill' : 'btn-dark-pill' ?>" style="padding:7px 16px; font-size:0.79rem; cursor:pointer;">
      Sıkça Sorulanlar
    </button>
    <button type="button" data-htab="kurallar" onclick="switchHelpTab('kurallar', this)" class="help-tab-btn <?= $activeHelpTab === 'kurallar' ? 'btn-gold-pill' : 'btn-dark-pill' ?>" style="padding:7px 16px; font-size:0.79rem; cursor:pointer;">
      Temel Kurallar
    </button>
    <button type="button" data-htab="biletler" onclick="switchHelpTab('biletler', this)" class="help-tab-btn <?= $activeHelpTab === 'biletler' ? 'btn-gold-pill' : 'btn-dark-pill' ?>" style="padding:7px 16px; font-size:0.79rem; cursor:pointer;">
      Destek Taleplerim (<?= count($userTickets) ?>)
    </button>
    <button type="button" data-htab="canli_sohbet" onclick="switchHelpTab('canli_sohbet', this)" class="help-tab-btn <?= $activeHelpTab === 'canli_sohbet' ? 'btn-gold-pill' : 'btn-dark-pill' ?>" style="padding:7px 16px; font-size:0.79rem; cursor:pointer;">
      Canlı Destek
    </button>
  </div>

  <!-- 1. SIKÇA SORULAN SORULAR -->
  <div id="helpTab_sss" class="tab-content <?= $activeHelpTab === 'sss' ? 'active' : '' ?>">
    <div style="display:flex; flex-direction:column; gap:8px;">
      <?php foreach ($faqList as $idx => $faq): ?>
        <div class="help-searchable" style="background:#141210; border:1px solid rgba(200,170,110,0.16); border-radius:10px; overflow:hidden;">
          <button type="button" onclick="toggleFaqRow(<?= $idx ?>)" style="width:100%; text-align:left; background:none; border:none; padding:14px 18px; display:flex; justify-content:space-between; align-items:center; gap:12px; cursor:pointer;">
            <span style="color:#F5F0E6; font-size:0.85rem; font-weight:500;">
              <?= htmlspecialchars((string)$faq['soru']) ?>
            </span>
            <span id="faqSign_<?= $idx ?>" style="color:#C8AA6E; font-size:1rem; font-weight:600;">+</span>
          </button>
          <div id="faqAns_<?= $idx ?>" style="display:none; padding:0 18px 14px 18px; color:#A39B8F; font-size:0.81rem; line-height:1.6; border-top:1px solid rgba(200,170,110,0.08);">
            <div style="padding-top:10px;"><?= htmlspecialchars((string)$faq['cevap']) ?></div>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  </div>

  <!-- 2. TEMEL KURALLAR -->
  <div id="helpTab_kurallar" class="tab-content <?= $activeHelpTab === 'kurallar' ? 'active' : '' ?>">
    <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(280px, 1fr)); gap:14px;">
      <?php foreach ($systemRules as $rule): ?>
        <div class="panel-card help-searchable" style="margin-bottom:0; padding:18px;">
          <div style="font-size:0.68rem; color:#C8AA6E; font-weight:600; letter-spacing:1px; text-transform:uppercase; margin-bottom:4px;">
            <?= htmlspecialchars((string)($rule['rozet'] ?? 'KURAL')) ?>
          </div>
          <h3 style="font-family:var(--font-serif); font-size:1.08rem; color:#F5F0E6; font-weight:500; margin-bottom:6px;">
            <?= htmlspecialchars((string)$rule['baslik']) ?>
          </h3>
          <p style="font-size:0.8rem; color:#A39B8F; line-height:1.55; margin:0;">
            <?= htmlspecialchars((string)$rule['ozet']) ?>
          </p>
        </div>
      <?php endforeach; ?>
    </div>
  </div>

  <!-- 3. DESTEK TALEPLERİ -->
  <div id="helpTab_biletler" class="tab-content <?= $activeHelpTab === 'biletler' ? 'active' : '' ?>">
    <div class="panel-card" style="margin-bottom:18px;">
      <div style="font-family:var(--font-serif); font-size:1.15rem; color:#F5F0E6; margin-bottom:12px;">Yeni Destek Talebi</div>
      <form method="POST">
        <input type="hidden" name="action" value="create_support_ticket">
        <input type="hidden" name="user_id" value="<?= $currentUid ?>">
        <input type="hidden" name="user_name" value="<?= htmlspecialchars($currentName) ?>">
        <input type="hidden" name="user_email" value="<?= htmlspecialchars($currentMail) ?>">

        <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px; margin-bottom:12px;">
          <div>
            <label class="form-label">Konu Kategorisi</label>
            <select name="category" class="form-control">
              <option value="Sistem Kuralları & Pey">Teklif &amp; Müzayede</option>
              <option value="Ödeme & Emanet Bloke">Ödeme &amp; Bakiye</option>
              <option value="Ekspertiz & Orijinallik">Ekspertiz</option>
              <option value="Kargo & Sigortalı Teslimat">Kargo &amp; Teslimat</option>
              <option value="Genel Bilgi">Genel</option>
            </select>
          </div>
          <div>
            <label class="form-label">Öncelik</label>
            <select name="priority" class="form-control">
              <option value="NORMAL">Normal</option>
              <option value="YUKSEK">Yüksek</option>
              <option value="ACIL">Acil</option>
            </select>
          </div>
        </div>

        <div style="margin-bottom:12px;">
          <label class="form-label">Başlık</label>
          <input type="text" name="subject" class="form-control" placeholder="Kısa başlık yazın..." required>
        </div>

        <div style="margin-bottom:14px;">
          <label class="form-label">Açıklama</label>
          <textarea name="message" class="form-control" rows="3" placeholder="Talebinizi kısaca açıklayın..." required></textarea>
        </div>

        <button type="submit" class="btn-gold">Talebi Gönder</button>
      </form>
    </div>

    <div class="panel-card" style="margin-bottom:0;">
      <div style="font-family:var(--font-serif); font-size:1.12rem; color:#F5F0E6; margin-bottom:12px;">Geçmiş Taleplerim</div>
      <?php if (empty($userTickets)): ?>
        <div style="color:#8C8478; font-size:0.82rem; padding:12px 0;">Henüz açılmış bir destek talebiniz bulunmuyor.</div>
      <?php else: ?>
        <div style="display:flex; flex-direction:column; gap:10px;">
          <?php foreach ($userTickets as $t):
            $durum = strtoupper((string)($t['durum'] ?? 'ACIK'));
          ?>
            <div style="background:#0D0B0A; border:1px solid rgba(200,170,110,0.14); border-radius:8px; padding:14px;">
              <div style="display:flex; justify-content:space-between; align-items:center; gap:8px; margin-bottom:6px;">
                <span style="font-family:monospace; color:#C8AA6E; font-size:0.78rem; font-weight:600;"><?= htmlspecialchars((string)$t['biletNo']) ?></span>
                <span class="badge <?= $durum === 'COZULDU' ? 'badge-success' : 'badge-gold' ?>"><?= htmlspecialchars($durum) ?></span>
              </div>
              <div style="color:#F5F0E6; font-size:0.85rem; font-weight:500; margin-bottom:4px;"><?= htmlspecialchars((string)$t['konu']) ?></div>
              <div style="color:#A39B8F; font-size:0.79rem; line-height:1.5;"><?= htmlspecialchars((string)$t['mesaj']) ?></div>

              <?php if ($durum !== 'COZULDU'): ?>
                <form method="POST" style="margin-top:10px; display:flex; gap:8px;">
                  <input type="hidden" name="action" value="reply_support_ticket">
                  <input type="hidden" name="ticket_id" value="<?= (int)$t['id'] ?>">
                  <input type="hidden" name="user_id" value="<?= $currentUid ?>">
                  <input type="hidden" name="user_name" value="<?= htmlspecialchars($currentName) ?>">
                  <input type="hidden" name="user_role" value="ALICI">
                  <input type="text" name="reply_text" class="form-control" placeholder="Yanıt yazın..." required>
                  <button type="submit" class="btn-outline-gold btn-sm" style="white-space:nowrap;">Gönder</button>
                </form>
              <?php endif; ?>
            </div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </div>
  </div>

  <!-- 4. CANLI DESTEK -->
  <div id="helpTab_canli_sohbet" class="tab-content <?= $activeHelpTab === 'canli_sohbet' ? 'active' : '' ?>">
    <div class="panel-card" style="display:flex; flex-direction:column; height:460px; margin-bottom:0;">
      <div style="display:flex; justify-content:space-between; align-items:center; padding-bottom:12px; border-bottom:1px solid var(--border-color); margin-bottom:12px;">
        <div>
          <div style="color:#F5F0E6; font-size:0.88rem; font-weight:500;">Elif Çelik — Canlı Destek</div>
          <div style="color:#A39B8F; font-size:0.74rem;">Ortalama yanıt süresi ~2 dk</div>
        </div>
        <span class="badge badge-success">Çevrimiçi</span>
      </div>

      <div style="flex:1; overflow-y:auto; padding:12px; background:#0D0B0A; border-radius:8px; border:1px solid var(--border-color); display:flex; flex-direction:column; gap:10px; margin-bottom:12px;">
        <?php foreach ($activeSession['mesajlar'] ?? [] as $msg):
          $isStaff = ((int)($msg['gonderenId'] ?? 0) === 5);
        ?>
          <div style="align-self:<?= $isStaff ? 'flex-start' : 'flex-end' ?>; max-width:78%;">
            <div style="font-size:0.68rem; color:#8C8478; margin-bottom:2px; text-align:<?= $isStaff ? 'left' : 'right' ?>;">
              <?= htmlspecialchars((string)($msg['gonderenAd'] ?? '')) ?>
            </div>
            <div style="padding:8px 12px; border-radius:8px; font-size:0.81rem; line-height:1.45; background:<?= $isStaff ? '#1A1714' : 'rgba(200,170,110,0.16)' ?>; color:#F5F0E6; border:1px solid <?= $isStaff ? 'rgba(200,170,110,0.12)' : 'rgba(200,170,110,0.34)' ?>;">
              <?= htmlspecialchars((string)($msg['icerik'] ?? '')) ?>
            </div>
          </div>
        <?php endforeach; ?>
      </div>

      <form method="POST" style="display:flex; gap:8px;">
        <input type="hidden" name="action" value="send_chat">
        <input type="hidden" name="session_id" value="<?= (int)($activeSession['id'] ?? 951) ?>">
        <input type="hidden" name="sender_id" value="<?= $currentUid ?>">
        <input type="hidden" name="sender_name" value="<?= htmlspecialchars($currentName) ?>">
        <input type="text" name="message" class="form-control" placeholder="Mesajınızı yazın..." required>
        <button type="submit" class="btn-gold" style="white-space:nowrap;">Gönder</button>
      </form>
    </div>
  </div>
</main>

<script>
function switchHelpTab(tabId, btn) {
  document.querySelectorAll('.tab-content').forEach(el => el.classList.remove('active'));
  document.querySelectorAll('.help-tab-btn').forEach(b => {
    b.classList.remove('btn-gold-pill');
    b.classList.add('btn-dark-pill');
  });
  const target = document.getElementById('helpTab_' + tabId);
  if (target) target.classList.add('active');
  if (btn) {
    btn.classList.remove('btn-dark-pill');
    btn.classList.add('btn-gold-pill');
  }
}
function toggleFaqRow(idx) {
  const ans = document.getElementById('faqAns_' + idx);
  const sign = document.getElementById('faqSign_' + idx);
  if (!ans) return;
  const open = (ans.style.display === 'block');
  ans.style.display = open ? 'none' : 'block';
  if (sign) sign.innerText = open ? '+' : '−';
}
function filterHelpItems(q) {
  q = (q || '').toLowerCase().trim();
  document.querySelectorAll('.help-searchable').forEach(el => {
    el.style.display = (!q || el.innerText.toLowerCase().indexOf(q) !== -1) ? '' : 'none';
  });
}
</script>
