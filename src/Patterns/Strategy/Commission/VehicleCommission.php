<?php
declare(strict_types=1);

namespace Martello\Patterns\Strategy\Commission;

/**
 * VehicleCommission — Taşıt & Klasik Araç Komisyon Stratejisi
 * SRS Bölüm 1.7.2.2 & Tablo 1: %4 komisyon oranı, 3.000 TL ekspertiz bedeli.
 */
class VehicleCommission implements ICommissionStrategy
{
    private float $rate = 0.04;
    private float $appraisalFee = 3000.0;

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
        return 'VehicleCommission';
    }
}
