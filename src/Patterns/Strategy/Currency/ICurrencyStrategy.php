<?php
declare(strict_types=1);

namespace Martello\Patterns\Strategy\Currency;

/**
 * ICurrencyStrategy — Çoklu Döviz Kuru Dönüşüm Stratejisi
 * SRS FR-21 ve R-05 (Kur riski koruması: teklif ve blokaj orijinal para biriminde sabitlenir).
 */
interface ICurrencyStrategy
{
    /**
     * Verilen TRY tutarını aktif para birimine dönüştürür.
     */
    public function convertFromTry(float $amountInTry, array $rates): float;

    /**
     * Yabancı para birimini TRY karşılığına dönüştürür.
     */
    public function convertToTry(float $foreignAmount, array $rates): float;

    public function getSymbol(): string;

    public function getCode(): string;
}
