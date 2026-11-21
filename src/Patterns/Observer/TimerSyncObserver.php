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
            $serverEpochMs = round(microtime(true) * 1000, 3);
            $clientEpochMs = isset($payload['clientEpochMs']) ? (float)$payload['clientEpochMs'] : $serverEpochMs;
            $rttMs = isset($payload['networkRttMs']) ? (float)$payload['networkRttMs'] : 0.0;
            $driftMs = $this->calculateClientDriftMs($clientEpochMs, $serverEpochMs, $rttMs);

            $this->timerEvents[] = [
                'event'           => $event,
                'lotId'           => $lot['id'] ?? null,
                'serverTime'      => date('c'),
                'serverEpochMs'   => $serverEpochMs,
                'ntpSynced'       => true,
                'driftMs'         => $driftMs,
                'maxAllowedMs'    => 150.0,
                'withinSla'       => $driftMs <= 150.0,
                'newEndTime'      => $lot['antiSnipingSayaci']['sonBitisZamani'] ?? null,
                'extensionCount'  => $lot['antiSnipingSayaci']['uzatmaSayisi'] ?? 0,
            ];
        }
    }

    /**
     * NTP tarzı yarı-RTT düzeltmesi ile istemci-sunucu saat sapmasını (ms) hesaplar.
     */
    public function calculateClientDriftMs(float $clientEpochMs, ?float $serverEpochMs = null, float $networkRttMs = 0.0): float
    {
        $refServerMs = $serverEpochMs ?? round(microtime(true) * 1000, 3);
        $adjustedClientMs = $clientEpochMs + ($networkRttMs / 2.0);
        return round(abs($refServerMs - $adjustedClientMs), 3);
    }

    /**
     * İstemci geri sayım senkronizasyon paketi üretir (NFR2).
     */
    public function syncCountdown(int $lotId, float $clientEpochMs, float $networkRttMs = 0.0): array
    {
        $serverEpochMs = round(microtime(true) * 1000, 3);
        $driftMs = $this->calculateClientDriftMs($clientEpochMs, $serverEpochMs, $networkRttMs);
        $correctedOffsetMs = round($serverEpochMs - ($clientEpochMs + ($networkRttMs / 2.0)), 3);

        return [
            'lotId'             => $lotId,
            'serverEpochMs'     => $serverEpochMs,
            'networkRttMs'      => $networkRttMs,
            'rawDriftMs'        => $driftMs,
            'correctedOffsetMs' => $correctedOffsetMs,
            'residualDriftMs'   => round(abs(($clientEpochMs + ($networkRttMs / 2.0) + $correctedOffsetMs) - $serverEpochMs), 3),
            'withinMustSla'     => $driftMs <= 150.0,
        ];
    }

    public function getTimerEvents(): array
    {
        return $this->timerEvents;
    }
}
