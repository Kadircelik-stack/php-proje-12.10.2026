<?php
declare(strict_types=1);

namespace Martello\Patterns\Strategy\Bidding;

/**
 * VipAuctionStrategy — VIP Müzayede Kural Stratejisi
 * SRS NFR-14 (VIP Yetki ve Tier Kontrolü) ve Özel VIP Artırım Kurallarını uygular.
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

    public function evaluateAntiSniping(array $lot, int $remainingSeconds): array
    {
        $threshold = 60; // VIP oturumlarda son 60 saniye
        if ($remainingSeconds > $threshold) {
            return [
                'extended'            => false,
                'additionalSeconds'   => 0,
                'newRemainingSeconds' => $remainingSeconds,
                'reason'              => "Süre normal aralıkta.",
            ];
        }

        $additional = 60; // VIP oturumlarda her seferinde +60 sn
        return [
            'extended'            => true,
            'additionalSeconds'   => $additional,
            'newRemainingSeconds' => $remainingSeconds + $additional,
            'reason'              => "VIP Anti-Sniping tetiklendi (+60 sn).",
        ];
    }

    public function getStrategyName(): string
    {
        return 'VipAuctionStrategy';
    }
}
