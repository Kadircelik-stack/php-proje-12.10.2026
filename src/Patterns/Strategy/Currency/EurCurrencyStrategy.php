<?php
declare(strict_types=1);

namespace Martello\Patterns\Strategy\Currency;

/**
 * EurCurrencyStrategy — Euro Para Birimi Stratejisi
 */
class EurCurrencyStrategy implements ICurrencyStrategy
{
    public function convertFromTry(float $amountInTry, array $rates): float
    {
        $rate = (float)($rates['EUR'] ?? 37.80);
        return $rate > 0 ? round($amountInTry / $rate, 2) : $amountInTry;
    }

    public function convertToTry(float $foreignAmount, array $rates): float
    {
        $rate = (float)($rates['EUR'] ?? 37.80);
        return round($foreignAmount * $rate, 2);
    }

    public function getSymbol(): string
    {
        return '€';
    }

    public function getCode(): string
    {
        return 'EUR';
    }
}
