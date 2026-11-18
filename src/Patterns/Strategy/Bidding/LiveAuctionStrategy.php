<?php
declare(strict_types=1);

namespace Martello\Patterns\Strategy\Bidding;

/**
 * LiveAuctionStrategy — Canlı Müzayede Kural Stratejisi
 * SRS FR-13, FR-32, FR-33, NFR-23 kurallarını uygular.
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
     * Anti-Sniping Kuralı (FR-33 & NFR-23):
     * Pey başına süre uzatılmaz.
     * SADECE son 1 dakika (60 saniye) içinde verilen peyler süreyi 1 dakika (+60 saniye) uzatır.
     */
    public function evaluateAntiSniping(array $lot, int $remainingSeconds): array
    {
        $threshold = 60; // Son 1 dakika (60 saniye) eşiği
        if ($remainingSeconds > $threshold) {
            return [
                'extended'            => false,
                'additionalSeconds'   => 0,
                'newRemainingSeconds' => $remainingSeconds,
                'reason'              => "Kalan süre 1 dakikadan fazla ({$remainingSeconds} sn) olduğundan süre uzatılmadı (Pey başına süre uzamaz).",
            ];
        }

        $additional = 60; // Son 1 dakika kala pey verilirse süreyi 1 dakika (+60 sn) uzat
        $newRemaining = $remainingSeconds + $additional;

        return [
            'extended'            => true,
            'additionalSeconds'   => $additional,
            'newRemainingSeconds' => $newRemaining,
            'reason'              => "Son 1 dakika içinde pey verildiğinden süre 1 dakika (+60 sn) uzatıldı.",
        ];
    }

    public function getStrategyName(): string
    {
        return 'LiveAuctionStrategy';
    }
}
