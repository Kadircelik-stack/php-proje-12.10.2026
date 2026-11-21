<?php
declare(strict_types=1);

namespace Martello\Patterns\Strategy\Bidding;

/**
 * VipAuctionStrategy — VIP Müzayede Kural Stratejisi
 * Kademeli Anti-Sniping ve VIP Tier Denetim Kurallarını uygular.
 */
class VipAuctionStrategy implements IBiddingRuleStrategy
{
    /** @var array<int, array{authorized: bool, tier: string, reason: string}> Bellek içi önbellek (NFR14 Plan/Wish) */
    private array $tierCache = [];

    /**
     * NFR14: VIP Müzayede API Yetki ve Tier Kontrolü (Bellek İçi Önbellekli)
     * Must: <= 20ms & %100 doğruluk | Plan: <= 10ms cache | Wish: < 3ms & anlık dinamik güncelleme
     */
    public function authorizeVipApiRequest(array $user, int $minWonLots = 1): array
    {
        $t0 = microtime(true);
        $userId = (int)($user['id'] ?? 0);

        if ($userId > 0 && isset($this->tierCache[$userId])) {
            $cached = $this->tierCache[$userId];
            return [
                'authorized' => $cached['authorized'],
                'httpStatus' => $cached['authorized'] ? 200 : 403,
                'tier'       => $cached['tier'],
                'cacheHit'   => true,
                'latencyMs'  => round((microtime(true) - $t0) * 1000, 4),
                'reason'     => $cached['reason'],
            ];
        }

        $isVipBlocked  = !empty($user['kisitlamalar']['vipEngeli']);
        $isKycApproved = !empty($user['kycDogrulama']['dogrulandiMi']);
        $tier          = strtoupper((string)($user['aliciProfili']['vipSeviyesi'] ?? 'STANDARD'));
        $wonLots       = (int)($user['aliciProfili']['toplamKazanilanLot'] ?? 0);
        $allowedTiers  = ['GOLD', 'PLATINUM', 'DIAMOND'];

        $authorized = !$isVipBlocked && $isKycApproved && in_array($tier, $allowedTiers, true) && ($wonLots >= $minWonLots);
        $reason = $authorized
            ? "VIP API yetkisi onaylandı (Tier: {$tier}, Kazanılan Lot: {$wonLots})."
            : "VIP API yetkisi reddedildi (Tier: {$tier}, KYC: " . ($isKycApproved ? 'OK' : 'YOK') . ", Kazanılan Lot: {$wonLots}).";

        if ($userId > 0) {
            $this->tierCache[$userId] = [
                'authorized' => $authorized,
                'tier'       => $tier,
                'reason'     => $reason,
            ];
        }

        return [
            'authorized' => $authorized,
            'httpStatus' => $authorized ? 200 : 403,
            'tier'       => $tier,
            'cacheHit'   => false,
            'latencyMs'  => round((microtime(true) - $t0) * 1000, 4),
            'reason'     => $reason,
        ];
    }

    /**
     * Dinamik tier ataması değiştiğinde önbelleği anında temizler (NFR14 Wish).
     */
    public function invalidateTierCache(int $userId): void
    {
        unset($this->tierCache[$userId]);
    }

    public function validateBid(array $lot, float $bidAmount, array $user): array
    {
        // NFR-14: VIP Müzayede Yetki ve Tier Kontrolü (Gold veya onaylı VIP)
        if (!empty($user['kisitlamalar']['vipEngeli'])) {
            return [
                'valid'       => false,
                'message'     => "VIP özel salon katılım yetkiniz yönetici tarafından kısıtlanmıştır.",
                'minRequired' => 0.0,
            ];
        }

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
