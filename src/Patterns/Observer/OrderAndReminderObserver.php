<?php
declare(strict_types=1);

namespace Martello\Patterns\Observer;

/**
 * OrderAndReminderObserver — Otomatik Sipariş ve Kademeli Ödeme Hatırlatma Gözlemcisi
 * SRS FR-29 (Açık Artırma Sonrası Otomatik Sipariş) ve FR-37 (Ödeme Hatırlatma) gereksinimlerini karşılar.
 */
class OrderAndReminderObserver implements IAuctionObserver
{
    /** @var array<int, array> */
    private array $generatedOrders = [];
    /** @var array<int, array> */
    private array $scheduledReminders = [];

    public function update(string $event, array $payload): void
    {
        if ($event === 'LOT_CLOSED') {
            $lot = $payload['lot'] ?? [];
            $winner = $payload['winner'] ?? [];

            if (!empty($winner['aliciId']) && !empty($lot['mevcutEnYuksekTeklif'])) {
                $closingPrice = (float)$lot['mevcutEnYuksekTeklif'];
                $commissionRate = 0.08; // Varsayılan antika/sanat %8
                $commissionAmount = $closingPrice * $commissionRate;
                $grandTotal = $closingPrice + $commissionAmount;

                $orderId = 'ORD-' . date('Y') . '-' . ($lot['id'] ?? mt_rand(1000, 9999)) . '-' . strtoupper(bin2hex(random_bytes(3)));

                $order = [
                    'siparisNo'         => $orderId,
                    'lotId'             => $lot['id'] ?? null,
                    'lotKodu'           => $lot['lotKodu'] ?? '',
                    'urunBaslik'        => $lot['baslik'] ?? '',
                    'kategoriAdi'       => $lot['kategoriAdi'] ?? 'Antika & Sanat',
                    'aliciId'           => $winner['aliciId'],
                    'saticiId'          => $lot['saticiId'] ?? 2,
                    'kapanisFiyati'     => $closingPrice,
                    'komisyonTutari'    => $commissionAmount,
                    'genelToplamTutar'  => $grandTotal,
                    'paraBirimi'        => $lot['paraBirimi'] ?? 'TRY',
                    'siparisDurumu'     => 'Odeme_Bekliyor',
                    'olusturmaZamani'   => date('c'),
                    'sonOdemeZamani'    => date('c', strtotime('+48 hours')),
                ];

                $this->generatedOrders[] = $order;

                // FR-37: 24, 12, 6, 1 saat kala kademeli hatırlatma kuyruğu
                $this->scheduledReminders[] = [
                    'orderId'        => $orderId,
                    'aliciId'        => $winner['aliciId'],
                    'reminderStages' => [24, 12, 6, 1],
                    'status'         => 'SCHEDULED',
                ];
            }
        }
    }

    public function getGeneratedOrders(): array
    {
        return $this->generatedOrders;
    }

    public function getScheduledReminders(): array
    {
        return $this->scheduledReminders;
    }
}
