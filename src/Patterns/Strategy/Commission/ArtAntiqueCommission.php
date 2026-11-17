<?php
declare(strict_types=1);

namespace Martello\Patterns\Strategy\Commission;

/**
 * ArtAntiqueCommission — Antika & Sanat Eseri Komisyon Stratejisi
 * SRS Bölüm 1.7.2.2 & Tablo 1: %8 komisyon oranı, 1.500 TL sabit ekspertiz bedeli.
 */
class ArtAntiqueCommission implements ICommissionStrategy
{
    private float $rate = 0.08;
    private float $appraisalFee = 1500.0;

    public function calculateCommission(float $closingPrice): float
    {
        return round($closingPrice * $this->rate, 2);
    }

    public function getCommissionRate(): float
    {
        return $this->rate * 100;
    }

    public function getAppraisalFee(): float
    {
        return $this->appraisalFee;
    }

    public function getName(): string
    {
        return 'ArtAntiqueCommission';
    }
}
