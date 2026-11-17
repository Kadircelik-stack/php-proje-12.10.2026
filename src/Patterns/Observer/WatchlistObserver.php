<?php
declare(strict_types=1);

namespace Martello\Patterns\Observer;

/**
 * WatchlistObserver — Takip Listesi Gözlemcisi
 * SRS FR-36 ('Takip Ettiklerim' izleme listesi anlık fiyat güncellemesi).
 */
class WatchlistObserver implements IAuctionObserver
{
    /** @var array<int, array> */
    private array $updatedWatchlists = [];

    public function update(string $event, array $payload): void
    {
        if ($event === 'BID_PLACED') {
            $lot = $payload['lot'] ?? [];
            $lotId = $lot['id'] ?? null;
            $newPrice = $lot['mevcutEnYuksekTeklif'] ?? 0;

            $this->updatedWatchlists[] = [
                'lotId'        => $lotId,
                'lotKodu'      => $lot['lotKodu'] ?? '',
                'currentPrice' => $newPrice,
                'updatedAt'    => date('c'),
            ];
        }
    }

    public function getUpdatedWatchlists(): array
    {
        return $this->updatedWatchlists;
    }
}
