/**
 * MARTELLO — İstemci Tarafı Gerçek Zamanlı Müzayede ve Sayaç Betiği (ZeroDay)
 * Kademeli Anti-Sniping Kuralı:
 * - Son 30 saniye içinde pey gelirse ilk 2 uzatmada +30 sn, 3. ve sonrakilerde +10 sn uzatılır.
 * - Son 30 saniyede yeni pey gelmezse ihale sonlandırılır.
 */
document.addEventListener("DOMContentLoaded", function () {
  // Canlı Geri Sayım Sayacı (Auction Countdown Timer)
  const timerElements = document.querySelectorAll(".live-countdown");

  timerElements.forEach((el) => {
    let remaining = parseInt(el.getAttribute("data-seconds") || "300", 10);
    const box = el.closest(".countdown-box");

    const updateDisplay = () => {
      // İhale süresi bittiğinde (Son 30 saniyede pey gelmediğinde sonlandırma)
      if (remaining <= 0) {
        el.textContent = "İhale Sonlandı (Satıldı)";
        if (box) box.classList.remove("critical");
        const warningEl = document.getElementById("antiSnipingWarning");
        if (warningEl) {
          warningEl.style.display = "flex";
          warningEl.style.borderColor = "rgba(63, 185, 80, 0.4)";
          warningEl.style.background = "rgba(63, 185, 80, 0.12)";
          warningEl.style.color = "#3fb950";
          warningEl.innerHTML = `
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="#3fb950" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg>
            <span>İhale son 30 saniyede yeni pey gelmediği için başarıyla sonlandırılmıştır. En yüksek teklif sahibi eseri kazandı.</span>
          `;
        }
        
        // Teklif butonunu pasifleştir
        const bidButtons = document.querySelectorAll('button[type="submit"].btn-register-glow');
        bidButtons.forEach(b => {
          if (!b.disabled) {
            b.textContent = "İhale Tamamlandı";
            b.disabled = true;
            b.style.opacity = "0.6";
            b.style.cursor = "not-allowed";
          }
        });
        return;
      }

      const h = Math.floor(remaining / 3600);
      const m = Math.floor((remaining % 3600) / 60);
      const s = remaining % 60;

      el.textContent =
        (h > 0 ? String(h).padStart(2, "0") + ":" : "") +
        String(m).padStart(2, "0") +
        ":" +
        String(s).padStart(2, "0");

      // Son 30 saniye kuralı (Kademeli Anti-Sniping Eşiği)
      if (remaining <= 30) {
        if (box) box.classList.add("critical");
        const warningEl = document.getElementById("antiSnipingWarning");
        if (warningEl) {
          warningEl.style.display = "flex";
          warningEl.style.borderColor = "rgba(239, 68, 68, 0.35)";
          warningEl.style.background = "rgba(239, 68, 68, 0.12)";
          warningEl.style.color = "#f87171";
          warningEl.innerHTML = `
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="#f87171" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m21.73 18-8-14a2 2 0 0 0-3.48 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3Z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
            <span>Kritik Son 30 Saniye! Pey verilirse süre kademeli uzatılacaktır (İlk 2 uzatma: +30 sn, 3. ve sonrakiler: +10 sn). Pey gelmezse ihale sonlandırılacaktır.</span>
          `;
        }
      } else {
        if (box) box.classList.remove("critical");
        const warningEl = document.getElementById("antiSnipingWarning");
        if (warningEl) warningEl.style.display = "none";
      }

      // Kalan süreyi gizli inputa yaz (POST edildiğinde doğru Anti-Sniping hesaplanır)
      const secInputs = document.querySelectorAll("#remainingSecondsInput");
      secInputs.forEach(input => {
        input.value = remaining;
      });

      remaining--;
    };

    // Jüriye / test amaçlı sayacı ayarlama fonksiyonu (25 sn = son 30 sn kritik eşiği)
    window.setDemoTimer = function (seconds) {
      remaining = parseInt(seconds, 10);
      updateDisplay();
    };

    updateDisplay();
    setInterval(updateDisplay, 1000);
  });

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
