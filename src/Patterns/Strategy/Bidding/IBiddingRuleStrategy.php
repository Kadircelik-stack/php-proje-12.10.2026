<?php
declare(strict_types=1);

namespace Martello\Patterns\Strategy\Bidding;

/**
 * IBiddingRuleStrategy — Pey ve Açık Artırma Kural Stratejisi Arayüzü
 * SRS Bölüm 1.7.2.1 & Tablo 1
 */
interface IBiddingRuleStrategy
{
    /**
     * Verilmek istenen teklifin geçerliliğini denetler.
     *
     * @return array{valid: bool, message: string, minRequired: float}
     */
    public function validateBid(array $lot, float $bidAmount, array $user): array;

    /**
     * Mevcut duruma göre verilebilecek asgari yeni teklif tutarını hesaplar.
     */
    public function calculateMinimumNextBid(array $lot): float;

    /**
     * Anti-Sniping kuralını değerlendirir.
     *
     * @param int $remainingSeconds Müzayede bitimine kalan saniye
     * @return array{extended: bool, additionalSeconds: int, newRemainingSeconds: int, reason: string}
     */
    public function evaluateAntiSniping(array $lot, int $remainingSeconds): array;

    public function getStrategyName(): string;
}
