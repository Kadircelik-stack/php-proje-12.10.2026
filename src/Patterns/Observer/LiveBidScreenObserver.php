<?php
declare(strict_types=1);

namespace Martello\Patterns\Observer;

/**
 * LiveBidScreenObserver — Anlık Fiyat ve Ekran Yayını Gözlemcisi
 * SRS FR-34 (≤1 sn anlık fiyat yayını) ve NFR-1 (WebSocket gecikmesi) gereksinimlerini karşılar.
 */
class LiveBidScreenObserver implements IAuctionObserver
{
    /** @var array<int, array> */
    private array $broadcastQueue = [];

    public function update(string $event, array $payload): void
    {
        if (in_array($event, ['BID_PLACED', 'ANTI_SNIPING_TRIGGERED', 'LOT_CLOSED'], true)) {
            $lot = $payload['lot'] ?? [];
            $bid = $payload['bid'] ?? [];

            $nowMs = round(microtime(true) * 1000, 3);
            $bidStartMs = isset($payload['bidStartTimeMs']) ? (float)$payload['bidStartTimeMs'] : $nowMs;
            $processingLatencyMs = max(0.0, round($nowMs - $bidStartMs, 3));

            $broadcastPacket = [
                'type'                => 'LIVE_SCREEN_PUSH',
                'event'               => $event,
                'lotId'               => $lot['id'] ?? null,
                'lotKodu'             => $lot['lotKodu'] ?? null,
                'currentPrice'        => $lot['mevcutEnYuksekTeklif'] ?? ($bid['teklifTutari'] ?? 0),
                'leaderAnonCode'      => $lot['liderAnonimKod'] ?? ($bid['aliciAnonimKod'] ?? 'Anonim'),
                'timestamp'           => date('c'),
                'serverTimestampMs'   => $nowMs,
                'processingLatencyMs' => $processingLatencyMs,
                'latencyBudgetMs'     => 300,
            ];

            $this->broadcastQueue[] = $broadcastPacket;
        }
    }

    public function getBroadcastQueue(): array
    {
        return $this->broadcastQueue;
    }
}
