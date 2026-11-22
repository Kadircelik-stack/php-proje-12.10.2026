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
  const activeIntervalIds = [];
  const lifecycleController = typeof AbortController !== "undefined" ? new AbortController() : null;
  const MAX_FEED_ITEMS = 100; // NFR20: Sabit boyutlu halka tampon (Ring Buffer) ile bellek sızıntısı önleme
  const liveEventRingBuffer = [];

  function pushBoundedEvent(evt) {
    liveEventRingBuffer.push(evt);
    if (liveEventRingBuffer.length > MAX_FEED_ITEMS) {
      liveEventRingBuffer.splice(0, liveEventRingBuffer.length - MAX_FEED_ITEMS);
    }
  }

  timerConfigs.forEach((cfg) => {
    if (!cfg.el) return;

    let remaining = parseInt(cfg.el.getAttribute("data-seconds") || "300", 10);
    let intervalId = null;
    const box = cfg.el.closest(".countdown-box");

    const updateDisplay = () => {
      // İhale süresi bittiğinde (Son 30 saniyede yeni pey gelmediğinde sonlandırma)
      if (remaining <= 0) {
        remaining = 0;
        // NFR20: İhale tamamlandığında zamanlayıcıyı serbest bırak (Memory Leak önleme)
        if (intervalId !== null) {
          clearInterval(intervalId);
          intervalId = null;
        }
        cfg.el.textContent = "İhale Sonlandı (Satıldı)";
        if (box) box.classList.remove("critical");
        if (cfg.input) cfg.input.value = "0";

        // İhale bittiğinde tutulan pey verme sayısını sıfırla
        if (cfg.badge) {
          cfg.badge.textContent = "0";
        }
        if (cfg.nextBadge) {
          cfg.nextBadge.textContent = "+30 sn";
          cfg.nextBadge.style.color = "#E2C992";
        }

        if (cfg.warning) {
          cfg.warning.style.display = "flex";
          cfg.warning.style.borderColor = "rgba(200, 170, 110, 0.36)";
          cfg.warning.style.background = "rgba(200, 170, 110, 0.09)";
          cfg.warning.style.color = "#EAE2D5";
          cfg.warning.innerHTML = `
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="#C8AA6E" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg>
            <span>İhale son 30 saniyede yeni pey gelmediği için tamamlanmıştır. Uzatma sayacı sıfırlandı.</span>
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
        cfg.el.style.color = "#E2C992";
        if (cfg.warning) {
          cfg.warning.style.display = "flex";
          cfg.warning.style.borderColor = "rgba(200, 170, 110, 0.38)";
          cfg.warning.style.background = "rgba(200, 170, 110, 0.1)";
          cfg.warning.style.color = "#EAE2D5";
          cfg.warning.innerHTML = `
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="#E2C992" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
            <span>Son 30 Saniye: Pey verilirse süre kademeli uzatılır (+30 sn / +10 sn).</span>
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
    intervalId = setInterval(updateDisplay, 1000);
    activeIntervalIds.push(intervalId);

    activeTimers[cfg.key] = {
      set: (sec) => {
        remaining = parseInt(sec, 10);
        if (cfg.input) cfg.input.value = remaining;
        cfg.closedSent = false;
        if (intervalId === null && remaining > 0) {
          intervalId = setInterval(updateDisplay, 1000);
          activeIntervalIds.push(intervalId);
        }
        updateDisplay();
      },
      getRemaining: () => remaining,
      destroy: () => {
        if (intervalId !== null) {
          clearInterval(intervalId);
          intervalId = null;
        }
      }
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

  // Hızlı Bakiye Artırma Butonları (Pey Önizleme) — AbortController ile sızıntısız dinleyici
  const quickBidButtons = document.querySelectorAll(".btn-quick-bid");
  const bidInput = document.getElementById("bidAmountInput");
  const listenerOpts = lifecycleController ? { signal: lifecycleController.signal } : false;

  quickBidButtons.forEach((btn) => {
    btn.addEventListener("click", function () {
      const addAmount = parseFloat(this.getAttribute("data-add") || "0");
      if (bidInput) {
        const cur = parseFloat(bidInput.value || "0");
        bidInput.value = (cur + addAmount).toFixed(0);
      }
      pushBoundedEvent({ type: "QUICK_BID_CLICK", ts: Date.now() });
    }, listenerOpts);
  });

  // NFR20: Sayfa kapanırken tüm zamanlayıcıları, olay dinleyicilerini ve tamponları temizle
  window.MartelloMemoryCleanup = function () {
    activeIntervalIds.forEach((id) => clearInterval(id));
    activeIntervalIds.length = 0;
    Object.values(activeTimers).forEach((t) => t.destroy && t.destroy());
    if (lifecycleController) {
      lifecycleController.abort();
    }
    liveEventRingBuffer.length = 0;
  };
  window.addEventListener("beforeunload", window.MartelloMemoryCleanup);

  // Otomatik Kur Değişimi Yönlendirmesi
  document.querySelectorAll(".currency-select, #currencySelector").forEach(function (sel) {
    sel.addEventListener("change", function () {
      const url = new URL(window.location.href);
      url.searchParams.set("currency", this.value);
      window.location.href = url.toString();
    });
  });

  // Gerçek Zamanlı AJAX Pey & Lot Durumu Yoklama (Polling - Sayfa Yenilemesiz Güncelleme)
  const stdLotInput = document.querySelector('#standardBidForm input[name="lot_id"]');
  if (stdLotInput && document.getElementById("standardCountdown")) {
    let lastKnownPrice = null;
    const pollLotStatus = () => {
      const lotId = stdLotInput.value;
      const curParam = new URL(window.location.href).searchParams.get("currency") || "";
      const pollUrl = "index.php?ajax=lot_status&lot_id=" + encodeURIComponent(lotId) + (curParam ? "&currency=" + encodeURIComponent(curParam) : "");
      fetch(pollUrl, { headers: { "Accept": "application/json" } })
        .then((res) => (res.ok ? res.json() : null))
        .then((data) => {
          if (!data || !data.ok) return;
          pushBoundedEvent({ type: "AJAX_LOT_POLL", lotId: data.lotId, price: data.currentPriceRaw, ts: Date.now() });

          const priceEl = document.querySelector("#buyerTab_canli .bid-price-large");
          if (priceEl && data.currentPriceFormatted) {
            priceEl.textContent = data.currentPriceFormatted;
          }

          if (lastKnownPrice !== null && data.currentPriceRaw > lastKnownPrice) {
            const bidAmountEl = document.getElementById("bidAmountInput");
            if (bidAmountEl && parseFloat(bidAmountEl.value || "0") < data.minNextBidRaw) {
              bidAmountEl.value = data.minNextBidRaw;
            }
          }
          lastKnownPrice = data.currentPriceRaw;

          const extBadge = document.getElementById("standardExtCountBadge");
          if (extBadge && typeof data.extCount !== "undefined") {
            extBadge.textContent = String(data.extCount);
          }
          const nextExtBadge = document.getElementById("standardNextExtBadge");
          if (nextExtBadge && typeof data.nextExtSec !== "undefined") {
            nextExtBadge.textContent = "+" + String(data.nextExtSec) + " sn";
          }
        })
        .catch(() => {});
    };
    const pollIntervalId = setInterval(pollLotStatus, 4000);
    activeIntervalIds.push(pollIntervalId);
  }
});
