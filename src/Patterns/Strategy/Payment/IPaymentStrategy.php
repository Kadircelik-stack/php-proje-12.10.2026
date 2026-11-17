<?php
declare(strict_types=1);

namespace Martello\Patterns\Strategy\Payment;

/**
 * IPaymentStrategy — Ödeme ve Emanet (Escrow) Yöntemleri Stratejisi
 * SRS FR-13, FR-23, FR-24, FR-28 ve NFR-10 standartları.
 */
interface IPaymentStrategy
{
    /**
     * Ödeme veya teminat blokajını yürütür.
     *
     * @param int $userId Alıcı kullanıcı ID
     * @param float $amount İşlem tutarı
     * @param array $meta Ekstra parametreler (lotId, cardToken, cvv, vb.)
     * @return array{success: bool, transactionId: string, message: string, newBalance?: float, blockedAmount?: float}
     */
    public function processPayment(int $userId, float $amount, array $meta = []): array;

    /**
     * Emanet blokajını çözer veya iade gerçekleştirir.
     */
    public function refund(int $userId, float $amount, string $reason): array;

    public function getMethodName(): string;
}
