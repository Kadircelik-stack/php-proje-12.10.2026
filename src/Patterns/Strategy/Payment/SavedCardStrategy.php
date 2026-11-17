<?php
declare(strict_types=1);

namespace Martello\Patterns\Strategy\Payment;

/**
 * SavedCardStrategy — Kayıtlı Kart ile Ödeme Stratejisi
 * SRS FR-28 ve PCI-DSS kart tokenizasyonu.
 */
class SavedCardStrategy implements IPaymentStrategy
{
    public function processPayment(int $userId, float $amount, array $meta = []): array
    {
        $tokenId = $meta['tokenId'] ?? 'TOK_DEFAULT_CARD';
        return [
            'success'       => true,
            'transactionId' => 'SAVEDCARD-' . strtoupper(bin2hex(random_bytes(6))),
            'message'       => "Kayıtlı kart ({$tokenId}) üzerinden ₺" . number_format($amount, 2) . " tahsilat tamamlandı.",
        ];
    }

    public function refund(int $userId, float $amount, string $reason): array
    {
        return [
            'success'       => true,
            'transactionId' => 'REF-CARD-' . strtoupper(bin2hex(random_bytes(6))),
            'message'       => "₺" . number_format($amount, 2) . " kayıtlı karta iade edildi. Sebep: {$reason}",
        ];
    }

    public function getMethodName(): string
    {
        return 'SavedCardStrategy';
    }
}
