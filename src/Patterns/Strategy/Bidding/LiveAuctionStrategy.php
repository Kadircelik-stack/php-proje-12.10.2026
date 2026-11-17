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
     * NFR-23: İhalenin son 30 saniyesi içinde yeni bir teklif gelmesi durumunda sistemin
     * süreyi kademeli uzatması (ilk 2 uzamada +30 sn, 3. ve sonrakilerde +10 sn).
     */
    public function evaluateAntiSniping(array $lot, int $remainingSeconds): array
    {
        $threshold = 30; // Son 30 saniye kuralı
        if ($remainingSeconds > $threshold) {
            return [
                'extended'            => false,
                'additionalSeconds'   => 0,
                'newRemainingSeconds' => $remainingSeconds,
                'reason'              => "Kalan süre eşik değerin ({$threshold} sn) üzerinde olduğundan süre uzatılmadı.",
            ];
        }

        $extensionCount = (int)($lot['antiSnipingSayaci']['uzatmaSayisi'] ?? 0);
        $additional = ($extensionCount < 2) ? 30 : 10;
        $newRemaining = $remainingSeconds + $additional;

        return [
            'extended'            => true,
            'additionalSeconds'   => $additional,
            'newRemainingSeconds' => $newRemaining,
            'reason'              => "Anti-Sniping tetiklendi! Son saniye teklifi nedeniyle süreye +{$additional} saniye eklendi.",
        ];
    }

    public function getStrategyName(): string
    {
        return 'LiveAuctionStrategy';
    }
}
