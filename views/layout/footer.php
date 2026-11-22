<?php
declare(strict_types=1);
?>
<footer class="martello-footer" style="background:#0A0908; border-top:1px solid rgba(200,170,110,0.14);">
  <div>
    <div class="footer-brand" style="font-family:var(--font-brand); letter-spacing:3px; font-weight:600; color:#F5F0E6; font-size:1.05rem; margin-bottom:6px;">MARTELLO</div>
    <p class="footer-text" style="color:#A39B8F; font-size:0.8rem; line-height:1.55;">Seçkin antika, horoloji ve koleksiyon eserlerine açılan güvenli kapı.</p>
    <div style="margin-top:12px; font-size:0.72rem; color:#6E675D;">
      ZeroDay • Esra Yıldırım • Abdullah Kadir Çelik • Rümeysa Şevval Kandilci
    </div>
  </div>

  <div>
    <div class="footer-col-title" style="font-family:var(--font-brand); font-size:0.7rem; font-weight:600; color:#C8AA6E; letter-spacing:1.6px; text-transform:uppercase; margin-bottom:10px;">PAZAR YERİ</div>
    <ul class="footer-links-list" style="list-style:none; padding:0; display:flex; flex-direction:column; gap:6px;">
      <li><a href="javascript:void(0)" onclick="return openInfoModal('about', event)" style="color:#A39B8F; font-size:0.79rem;">Hakkımızda</a></li>
      <li><a href="?role=buyer&tab=canli" style="color:#A39B8F; font-size:0.79rem;">Canlı Müzayedeler</a></li>
      <li><a href="?role=buyer&tab=kesfet" style="color:#A39B8F; font-size:0.79rem;">Müzayede Keşfet</a></li>
      <li><a href="?role=buyer&tab=gecmis" style="color:#C8AA6E; font-size:0.79rem; font-weight:500;">Müzayede &amp; Satış Geçmişi</a></li>
      <li><a href="javascript:void(0)" onclick="return openInfoModal('categories', event)" style="color:#A39B8F; font-size:0.79rem;">Ürün Kategorileri</a></li>
    </ul>
  </div>

  <div>
    <div class="footer-col-title" style="font-family:var(--font-brand); font-size:0.7rem; font-weight:600; color:#C8AA6E; letter-spacing:1.6px; text-transform:uppercase; margin-bottom:10px;">ŞARTLAR &amp; DESTEK</div>
    <p style="font-size:0.77rem; color:#8C8478; line-height:1.55; margin-bottom:10px;">
      MARTELLO prototip uygulamasıdır; işlemler sunum amaçlıdır.
    </p>
    <ul class="footer-links-list" style="list-style:none; padding:0; display:grid; grid-template-columns:1fr 1fr; gap:6px;">
      <li><a href="javascript:void(0)" onclick="return openInfoModal('terms', event)" style="color:#A39B8F; font-size:0.78rem;">Müzayede Şartları</a></li>
      <li><a href="javascript:void(0)" onclick="return openInfoModal('privacy', event)" style="color:#A39B8F; font-size:0.78rem;">Gizlilik &amp; KVKK</a></li>
      <li><a href="?role=yardim" style="color:#C8AA6E; font-size:0.78rem; font-weight:500;">Yardım Merkezi</a></li>
      <li><a href="?role=yardim&htab=destek" style="color:#A39B8F; font-size:0.78rem;">Destek Talebi</a></li>
    </ul>
  </div>
</footer>

<script src="public/assets/js/martello.js?v=<?= time() ?>"></script>
</body>
</html>
