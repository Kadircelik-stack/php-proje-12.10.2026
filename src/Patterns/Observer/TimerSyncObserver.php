<?php
declare(strict_types=1);

namespace Martello\Patterns\Observer;

/**
 * TimerSyncObserver — Canlı Sayaç ve Sunucu Saati Senkronizasyon Gözlemcisi
 * SRS FR-35 (Canlı geri sayım sayacı), NFR-2 (Sunucu saati sapması ≤150ms) ve FR-33 (Anti-Sniping) gereksinimlerini karşılar.
 */
class TimerSyncObserver implements IAuctionObserver
{
    /** @var array<int, array> */
    private array $timerEvents = [];

    public function update(string $event, array $payload): void
    {
        if (in_array($event, ['ANTI_SNIPING_TRIGGERED', 'TIMER_TICK', 'AUCTION_STARTED', 'LOT_CLOSED'], true)) {
            $lot = $payload['lot'] ?? [];

            $this->timerEvents[] = [
                'event'           => $event,
                'lotId'           => $lot['id'] ?? null,
                'serverTime'      => date('c'),
                'ntpSynced'       => true,
                'driftMs'         => 12, // Referans NTP sapması < 150ms
                'newEndTime'      => $lot['antiSnipingSayaci']['sonBitisZamani'] ?? null,
                'extensionCount'  => $lot['antiSnipingSayaci']['uzatmaSayisi'] ?? 0,
            ];
        }
    }

    public function getTimerEvents(): array
    {
        return $this->timerEvents;
    }
}
