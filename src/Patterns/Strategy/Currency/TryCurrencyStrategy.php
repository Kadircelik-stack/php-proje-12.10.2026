<?php
declare(strict_types=1);

namespace Martello\Patterns\Strategy\Currency;

/**
 * TryCurrencyStrategy — Türk Lirası Para Birimi Stratejisi
 */
class TryCurrencyStrategy implements ICurrencyStrategy
{
    public function convertFromTry(float $amountInTry, array $rates): float
    {
        return $amountInTry;
    }

    public function convertToTry(float $foreignAmount, array $rates): float
    {
        return $foreignAmount;
    }

    public function getSymbol(): string
    {
        return '₺';
    }

    public function getCode(): string
    {
        return 'TRY';
    }
}
