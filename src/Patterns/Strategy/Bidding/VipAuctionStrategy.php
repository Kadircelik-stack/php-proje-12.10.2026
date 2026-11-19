<?php
declare(strict_types=1);

namespace Martello\Patterns\Strategy\Bidding;

/**
 * VipAuctionStrategy — VIP Müzayede Kural Stratejisi
 * Kademeli Anti-Sniping ve VIP Tier Denetim Kurallarını uygular.
 */
class VipAuctionStrategy implements IBiddingRuleStrategy
{
    public function validateBid(array $lot, float $bidAmount, array $user): array
    {
        // NFR-14: VIP Müzayede Yetki ve Tier Kontrolü (Gold veya onaylı VIP)
        $vipLevel = $user['aliciProfili']['vipSeviyesi'] ?? 'STANDARD';
        $isKycApproved = $user['kycDogrulama']['dogrulandiMi'] ?? false;

        if (!$isKycApproved || !in_array($vipLevel, ['GOLD', 'PLATINUM', 'DIAMOND'], true)) {
            return [
                'valid'       => false,
                'message'     => "Bu VIP müzayedeye sadece onaylı GOLD veya PLATINUM üyeler teklif verebilir.",
                'minRequired' => 0.0,
            ];
        }

        $currentHighest = (float)($lot['mevcutEnYuksekTeklif'] ?? $lot['baslangicFiyati'] ?? 0);
        $minStep = (float)($lot['minimumArtisTutari'] ?? 5000.0);
        $minRequired = $currentHighest + $minStep;

        if ($bidAmount < $minRequired) {
            return [
                'valid'       => false,
                'message'     => "VIP Teklif tutarı en az {$minRequired} olmalıdır (VIP asgari artış: {$minStep}).",
                'minRequired' => $minRequired,
            ];
        }

        return [
            'valid'       => true,
            'message'     => "VIP Teklifi kabul edildi.",
            'minRequired' => $minRequired,
        ];
    }

    public function calculateMinimumNextBid(array $lot): float
    {
        $current = (float)($lot['mevcutEnYuksekTeklif'] ?? $lot['baslangicFiyati'] ?? 0);
        $step    = (float)($lot['minimumArtisTutari'] ?? 5000.0);
        return $current + $step;
    }

    /**
     * VIP Kademeli Anti-Sniping Kuralı:
     * Son 30 saniye içinde pey gelirse kademeli uzatma:
     * - İlk 2 uzatmada: +30 sn
     * - 3. ve sonrakilerde: +10 sn
     */
    public function evaluateAntiSniping(array $lot, int $remainingSeconds): array
    {
        $threshold = 30; // Son 30 saniye kritik eşiği
        if ($remainingSeconds > $threshold || $remainingSeconds <= 0) {
            return [
                'extended'            => false,
                'additionalSeconds'   => 0,
                'newRemainingSeconds' => $remainingSeconds,
                'reason'              => "Süre normal aralıkta veya süre bitmiş ({$remainingSeconds} sn).",
            ];
        }

        $extensionCount = (int)($lot['antiSnipingSayaci']['uzatmaSayisi'] ?? 0);
        $additional = ($extensionCount < 2) ? 30 : 10;
        $newRemaining = $remainingSeconds + $additional;
        $extensionIndex = $extensionCount + 1;

        return [
            'extended'            => true,
            'additionalSeconds'   => $additional,
            'newRemainingSeconds' => $newRemaining,
            'extensionNumber'     => $extensionIndex,
            'reason'              => "VIP Kademeli Anti-Sniping tetiklendi (+{$additional} sn, {$extensionIndex}. uzatma).",
        ];
    }

    public function getStrategyName(): string
    {
        return 'VipAuctionStrategy';
    }
}
