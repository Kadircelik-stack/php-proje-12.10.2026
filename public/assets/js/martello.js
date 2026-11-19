/**
 * MARTELLO — İstemci Tarafı Gerçek Zamanlı Müzayede ve Sayaç Betiği (ZeroDay)
 * Kademeli Anti-Sniping Kuralı:
 * - Son 30 saniye içinde pey verilirse: ilk 2 basımda +30 sn, 3. ve sonraki tüm basımlarda +10 sn uzatılır.
 * - Son 30 saniyede yeni pey gelmezse ihale otomatik sonlandırılır ve tutulan pey verme sayısı sıfırlanır.
 */
document.addEventListener("DOMContentLoaded", function () {
  // Timer yapılandırma listesi
  const timerConfigs = [
    {
      key: "standard",
      el: document.getElementById("standardCountdown") || document.querySelector(".live-countdown:not(#vipCountdown)"),
      warning: document.getElementById("standardAntiSnipingWarning") || document.getElementById("antiSnipingWarning"),
      input: document.getElementById("standardRemainingSecondsInput") || document.getElementById("remainingSecondsInput"),
      button: document.getElementById("standardBidSubmitBtn") || document.querySelector('#standardBidForm button[type="submit"]'),
      lotId: document.querySelector('#standardBidForm input[name="lot_id"]')?.value || '1002',
      badge: document.getElementById("standardExtCountBadge"),
      nextBadge: document.getElementById("standardNextExtBadge"),
      closedSent: false
    },
    {
      key: "vip",
      el: document.getElementById("vipCountdown"),
      warning: document.getElementById("vipAntiSnipingWarning"),
      input: document.getElementById("vipRemainingSecondsInput"),
      button: document.getElementById("vipBidSubmitBtn") || document.querySelector('#vipBidForm button[type="submit"]'),
      lotId: document.querySelector('#vipBidForm input[name="lot_id"]')?.value || '1005',
      badge: document.getElementById("vipExtCountBadge"),
      nextBadge: document.getElementById("vipNextExtBadge"),
      closedSent: false
    }
  ];

  const activeTimers = {};

  timerConfigs.forEach((cfg) => {
    if (!cfg.el) return;

    let remaining = parseInt(cfg.el.getAttribute("data-seconds") || "300", 10);
    const box = cfg.el.closest(".countdown-box");

    const updateDisplay = () => {
      // İhale süresi bittiğinde (Son 30 saniyede yeni pey gelmediğinde sonlandırma)
      if (remaining <= 0) {
        remaining = 0;
        cfg.el.textContent = "İhale Sonlandı (Satıldı)";
        if (box) box.classList.remove("critical");
        if (cfg.input) cfg.input.value = "0";

        // İhale bittiğinde tutulan pey verme sayısını sıfırla
        if (cfg.badge) {
          cfg.badge.textContent = "0";
        }
        if (cfg.nextBadge) {
          cfg.nextBadge.textContent = "+30 sn";
          cfg.nextBadge.style.color = "#3fb950";
        }

        if (cfg.warning) {
          cfg.warning.style.display = "flex";
          cfg.warning.style.borderColor = "rgba(63, 185, 80, 0.4)";
          cfg.warning.style.background = "rgba(63, 185, 80, 0.12)";
          cfg.warning.style.color = "#3fb950";
          cfg.warning.innerHTML = `
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="#3fb950" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg>
            <span>İhale son 30 saniyede yeni pey gelmediği için başarıyla sonlandırılmıştır. Tutulan pey verme ve uzatma sayısı sıfırlanmıştır.</span>
          `;
        }

        // Teklif butonunu pasifleştir
        if (cfg.button && !cfg.button.disabled) {
          cfg.button.textContent = "İhale Tamamlandı";
          cfg.button.disabled = true;
          cfg.button.style.opacity = "0.5";
          cfg.button.style.cursor = "not-allowed";
        }

        // Sunucuda lotu kapat ve veritabanında pey sayacını sıfırla
        if (cfg.lotId && !cfg.closedSent) {
          cfg.closedSent = true;
          const formData = new FormData();
          formData.append("action", "close_lot");
          formData.append("lot_id", cfg.lotId);
          fetch("index.php", {
            method: "POST",
            body: formData
          }).catch(() => {});
        }
        return;
      }

      const h = Math.floor(remaining / 3600);
      const m = Math.floor((remaining % 3600) / 60);
      const s = remaining % 60;

      cfg.el.textContent =
        (h > 0 ? String(h).padStart(2, "0") + ":" : "") +
        String(m).padStart(2, "0") +
        ":" +
        String(s).padStart(2, "0");

      // Son 30 saniye kuralı (Kademeli Anti-Sniping Eşiği)
      if (remaining <= 30) {
        if (box) box.classList.add("critical");
        cfg.el.style.color = "#f87171";
        if (cfg.warning) {
          cfg.warning.style.display = "flex";
          cfg.warning.style.borderColor = "rgba(239, 68, 68, 0.35)";
          cfg.warning.style.background = "rgba(239, 68, 68, 0.12)";
          cfg.warning.style.color = "#f87171";
          cfg.warning.innerHTML = `
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="#f87171" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m21.73 18-8-14a2 2 0 0 0-3.48 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3Z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
            <span>Kritik Son 30 Saniye! Pey verilirse süre kademeli uzatılacaktır (İlk 2 basımda +30 sn, 3. ve sonrakilerde +10 sn). Pey gelmezse ihale sonlandırılacaktır.</span>
          `;
        }
      } else {
        if (box) box.classList.remove("critical");
        cfg.el.style.color = "";
        if (cfg.warning) cfg.warning.style.display = "none";
      }

      // Kalan süreyi ilgili gizli inputa yaz
      if (cfg.input) {
        cfg.input.value = remaining;
      }

      remaining--;
    };

    updateDisplay();
    const intervalId = setInterval(updateDisplay, 1000);

    activeTimers[cfg.key] = {
      set: (sec) => {
        remaining = parseInt(sec, 10);
        if (cfg.input) cfg.input.value = remaining;
        cfg.closedSent = false;
        updateDisplay();
      },
      getRemaining: () => remaining
    };
  });

  // Jüriye / test amaçlı sayacı ayarlama fonksiyonu
  window.setDemoTimer = function (seconds, target) {
    const sec = parseInt(seconds, 10);
    if (target && activeTimers[target]) {
      activeTimers[target].set(sec);
    } else {
      // Hedef belirtilmediyse var olan tüm sayaçları veya standardı ayarla
      if (activeTimers["standard"]) activeTimers["standard"].set(sec);
      if (activeTimers["vip"] && target === "all") activeTimers["vip"].set(sec);
    }
  };

  // Hızlı Bakiye Artırma Butonları (Pey Önizleme)
  const quickBidButtons = document.querySelectorAll(".btn-quick-bid");
  const bidInput = document.getElementById("bidAmountInput");

  quickBidButtons.forEach((btn) => {
    btn.addEventListener("click", function () {
      const addAmount = parseFloat(this.getAttribute("data-add") || "0");
      if (bidInput) {
        const cur = parseFloat(bidInput.value || "0");
        bidInput.value = (cur + addAmount).toFixed(0);
      }
    });
  });

  // Otomatik Kur Değişimi Yönlendirmesi
  const currencySelect = document.getElementById("currencySelector");
  if (currencySelect) {
    currencySelect.addEventListener("change", function () {
      const url = new URL(window.location.href);
      url.searchParams.set("currency", this.value);
      window.location.href = url.toString();
    });
  }
});
