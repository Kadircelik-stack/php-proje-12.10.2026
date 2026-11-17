<?php
declare(strict_types=1);

namespace Martello\Patterns\Strategy\Currency;

/**
 * UsdCurrencyStrategy — Amerikan Doları Para Birimi Stratejisi
 */
class UsdCurrencyStrategy implements ICurrencyStrategy
{
    public function convertFromTry(float $amountInTry, array $rates): float
    {
        $rate = (float)($rates['USD'] ?? 34.50);
        return $rate > 0 ? round($amountInTry / $rate, 2) : $amountInTry;
    }

    public function convertToTry(float $foreignAmount, array $rates): float
    {
        $rate = (float)($rates['USD'] ?? 34.50);
        return round($foreignAmount * $rate, 2);
    }

    public function getSymbol(): string
    {
        return '$';
    }

    public function getCode(): string
    {
        return 'USD';
    }
}
