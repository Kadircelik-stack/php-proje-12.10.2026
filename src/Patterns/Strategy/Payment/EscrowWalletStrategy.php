<?php
declare(strict_types=1);

namespace Martello\Patterns\Strategy\Payment;

/**
 * EscrowWalletStrategy — Cüzdan Emanet (Escrow) Blokaj ve Ödeme Stratejisi
 * SRS FR-23, FR-24 ve R-02 gereksinimlerini karşılar.
 */
class EscrowWalletStrategy implements IPaymentStrategy
{
    public function processPayment(int $userId, float $amount, array $meta = []): array
    {
        $wallet = $meta['wallet'] ?? null;
        if ($wallet !== null) {
            $available = (float)($wallet['bakiye'] ?? 0);
            if ($available < $amount) {
                return [
                    'success'       => false,
                    'transactionId' => '',
                    'message'       => "Yetersiz cüzdan bakiyesi. Mevcut: ₺" . number_format($available, 2) . ", Gerekli: ₺" . number_format($amount, 2),
                ];
            }
        }

        $txId = 'TX-ESCROW-' . strtoupper(bin2hex(random_bytes(6)));

        return [
            'success'       => true,
            'transactionId' => $txId,
            'message'       => "₺" . number_format($amount, 2) . " tutarındaki pey teminatı emanet havuzunda güvenle bloke edildi.",
            'blockedAmount' => $amount,
        ];
    }

    public function refund(int $userId, float $amount, string $reason): array
    {
        return [
            'success'       => true,
            'transactionId' => 'REF-' . strtoupper(bin2hex(random_bytes(6))),
            'message'       => "₺" . number_format($amount, 2) . " tutarındaki bloke çözülerek alıcı cüzdanına iade edildi. Sebep: {$reason}",
        ];
    }

    public function getMethodName(): string
    {
        return 'EscrowWalletStrategy';
    }
}
