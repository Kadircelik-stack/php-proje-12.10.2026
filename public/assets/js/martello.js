/**
 * MARTELLO — İstemci Tarafı Gerçek Zamanlı Müzayede ve Sayaç Betiği (ZeroDay)
 * SRS FR-33 (Anti-Sniping), FR-34 (≤1sn Anlık Fiyat Yayını), FR-35 (Geri Sayım Sayacı)
 */
document.addEventListener("DOMContentLoaded", function () {
  // Canlı Geri Sayım Sayacı (Auction Countdown Timer)
  const timerElements = document.querySelectorAll(".live-countdown");

  timerElements.forEach((el) => {
    let remaining = parseInt(el.getAttribute("data-seconds") || "300", 10);
    const box = el.closest(".countdown-box");

    const updateDisplay = () => {
      if (remaining <= 0) {
        el.textContent = "Tamamlandı";
        if (box) box.classList.remove("critical");
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

      // Son 1 dakika (60 saniye) kuralı (Anti-Sniping Eşiği - FR-33 & NFR-23)
      if (remaining <= 60) {
        if (box) box.classList.add("critical");
        const warningEl = document.getElementById("antiSnipingWarning");
        if (warningEl) {
          warningEl.style.display = "block";
          warningEl.textContent = "⚠️ Son 1 dakika! Bu süre içinde pey verilirse süre 1 dakika uzatılacaktır.";
        }
      } else {
        if (box) box.classList.remove("critical");
        const warningEl = document.getElementById("antiSnipingWarning");
        if (warningEl) warningEl.style.display = "none";
      }

      // Kalan süreyi gizli inputa yaz (Böylece POST edildiğinde doğru Anti-Sniping hesaplanır)
      const secInput = document.getElementById("remainingSecondsInput");
      if (secInput) {
        secInput.value = remaining;
      }

      remaining--;
    };

    // Hocaya veya jüriye canlı test/demo göstermek için yardımcı fonksiyon
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
