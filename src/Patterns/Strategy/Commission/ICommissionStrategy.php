<?php
declare(strict_types=1);

namespace Martello\Patterns\Strategy\Commission;

/**
 * ICommissionStrategy — Kategori Bazlı Komisyon ve Hizmet Bedeli Stratejisi
 * SRS FR-08, FR-17, FR-22 ve Bölüm 1.7.2.1
 */
interface ICommissionStrategy
{
    /**
     * Kapanış fiyatı üzerinden platform komisyonunu hesaplar.
     */
    public function calculateCommission(float $closingPrice): float;

    /**
     * Kategoriye ait komisyon yüzdesini döner.
     */
    public function getCommissionRate(): float;

    /**
     * Sabit ekspertiz ve inceleme bedelini döner.
     */
    public function getAppraisalFee(): float;

    public function getName(): string;
}
