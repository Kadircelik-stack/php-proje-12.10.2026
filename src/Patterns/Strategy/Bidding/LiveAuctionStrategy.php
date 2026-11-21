<?php
declare(strict_types=1);

namespace Martello\Patterns\Strategy\Bidding;

/**
 * LiveAuctionStrategy — Canlı Müzayede Kural Stratejisi
 * Kademeli Anti-Sniping Kuralı:
 * Son 30 saniye içinde pey gelirse süre kademeli olarak uzatılır:
 * - İlk 2 uzatmada: +30 saniye
 * - 3. ve sonrakilerde: +10 saniye
 * Son 30 saniyede yeni pey gelmezse ihale sonlandırılır.
 */
class LiveAuctionStrategy implements IBiddingRuleStrategy
{
    public function validateBid(array $lot, float $bidAmount, array $user): array
    {
        $currentHighest = (float)($lot['mevcutEnYuksekTeklif'] ?? $lot['baslangicFiyati'] ?? 0);
        $minStep = (float)($lot['minimumArtisTutari'] ?? 500.0);
        $minRequired = $currentHighest + $minStep;

        if ($bidAmount < $minRequired) {
            return [
                'valid'       => false,
                'message'     => "Teklifiniz en az {$minRequired} " . ($lot['paraBirimi'] ?? 'TRY') . " olmalıdır (Asgari artış: {$minStep}).",
                'minRequired' => $minRequired,
            ];
        }

        // Satıcının kendi lotuna teklif vermesi engeli (Shill Bidding)
        if (isset($lot['saticiId']) && (int)$lot['saticiId'] === (int)($user['id'] ?? 0)) {
            return [
                'valid'       => false,
                'message'     => "Satıcılar kendi müzayede ilanlarına teklif veremezler (Shill Bidding engeli).",
                'minRequired' => $minRequired,
            ];
        }

        return [
            'valid'       => true,
            'message'     => "Teklif kurallara uygundur.",
            'minRequired' => $minRequired,
        ];
    }

    public function calculateMinimumNextBid(array $lot): float
    {
        $current = (float)($lot['mevcutEnYuksekTeklif'] ?? $lot['baslangicFiyati'] ?? 0);
        $step    = (float)($lot['minimumArtisTutari'] ?? 500.0);
        return $current + $step;
    }

    /**
     * Kademeli Anti-Sniping Kuralı:
     * - Son 30 saniye kritik eşiği
     * - İlk 2 uzatmada +30 sn
     * - 3. ve sonrakilerde +10 sn
     */
    public function evaluateAntiSniping(array $lot, int $remainingSeconds): array
    {
        $t0 = microtime(true);
        $threshold = 30; // Son 30 saniye kritik eşiği
        if ($remainingSeconds > $threshold || $remainingSeconds <= 0) {
            return [
                'extended'            => false,
                'additionalSeconds'   => 0,
                'newRemainingSeconds' => $remainingSeconds,
                'triggerLatencyMs'    => round((microtime(true) - $t0) * 1000, 4),
                'reason'              => "Kalan süre 30 saniyeden fazla ({$remainingSeconds} sn) veya süre bitmiş olduğundan süre uzatılmadı.",
            ];
        }

        // Mevcut uzatma sayacı (Kaçıncı kez uzatılıyor?)
        $extensionCount = (int)($lot['antiSnipingSayaci']['uzatmaSayisi'] ?? 0);

        // Kademeli kural: İlk 2 uzatmada +30 sn, 3. ve sonrakilerde +10 sn
        $additional = ($extensionCount < 2) ? 30 : 10;
        $newRemaining = $remainingSeconds + $additional;
        $extensionIndex = $extensionCount + 1;

        return [
            'extended'            => true,
            'additionalSeconds'   => $additional,
            'newRemainingSeconds' => $newRemaining,
            'extensionNumber'     => $extensionIndex,
            'triggerLatencyMs'    => round((microtime(true) - $t0) * 1000, 4),
            'reason'              => "Son 30 saniye içinde pey verildiğinden süre kademeli olarak +{$additional} sn uzatıldı ({$extensionIndex}. uzatma).",
        ];
    }

    public function getStrategyName(): string
    {
        return 'LiveAuctionStrategy';
    }
}
