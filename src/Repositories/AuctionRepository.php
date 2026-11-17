<?php
declare(strict_types=1);

namespace Martello\Repositories;

use Martello\Core\JsonDataStore;

/**
 * AuctionRepository — Müzayede, Lot ve Pey Veri Erişim Katmanı
 */
class AuctionRepository
{
    private JsonDataStore $dataStore;

    public function __construct(JsonDataStore $dataStore)
    {
        $this->dataStore = $dataStore;
    }

    public function findLotById(int $lotId): ?array
    {
        return $this->dataStore->getItem('lots', 'id', $lotId);
    }

    public function findAllLots(): array
    {
        return $this->dataStore->getCollection('lots');
    }

    public function findActiveLots(): array
    {
        $all = $this->findAllLots();
        return array_values(array_filter($all, static fn(array $l) => ($l['durum'] ?? '') === 'ACIK_ARTIRMADA'));
    }

    public function findAuctionById(int $auctionId): ?array
    {
        return $this->dataStore->getItem('auctions', 'id', $auctionId);
    }

    public function findBidsByLotId(int $lotId): array
    {
        $bids = $this->dataStore->getCollection('bids');
        $filtered = array_filter($bids, static fn(array $b) => (int)($b['lotId'] ?? 0) === $lotId);
        // En yeni teklif en üstte
        usort($filtered, static fn(array $a, array $b) => ($b['id'] ?? 0) <=> ($a['id'] ?? 0));
        return array_values($filtered);
    }

    public function findProxyBidsByLotId(int $lotId): array
    {
        $proxies = $this->dataStore->getCollection('proxyBids');
        return array_values(array_filter($proxies, static fn(array $p) => (int)($p['lotId'] ?? 0) === $lotId && ($p['aktifMi'] ?? false)));
    }

    public function findActiveProxyBid(int $lotId): ?array
    {
        $proxies = $this->findProxyBidsByLotId($lotId);
        if (empty($proxies)) {
            return null;
        }
        // En yüksek bütçeli aktif proxy teklif
        usort($proxies, static fn(array $a, array $b) => (float)($b['maksimumButceTutari'] ?? 0) <=> (float)($a['maksimumButceTutari'] ?? 0));
        return $proxies[0];
    }

    public function saveLot(array $lot): void
    {
        $this->dataStore->updateItem('lots', 'id', $lot['id'], $lot);
    }

    public function saveBid(array $bid): array
    {
        return $this->dataStore->insertItem('bids', $bid);
    }

    public function saveProxyBid(array $proxyBid): array
    {
        return $this->dataStore->insertItem('proxyBids', $proxyBid);
    }

    public function getCategories(): array
    {
        return $this->dataStore->getCollection('categories');
    }

    public function getProducts(): array
    {
        return $this->dataStore->getCollection('products');
    }

    public function getUserWatchlist(int $userId): array
    {
        $watches = $this->dataStore->getCollection('watchlists');
        return array_values(array_filter($watches, static fn(array $w) => (int)($w['kullaniciId'] ?? 0) === $userId));
    }

    public function toggleWatchlist(int $userId, int $lotId, int $auctionId, float $currentPrice): bool
    {
        $watches = $this->dataStore->getCollection('watchlists');
        foreach ($watches as $w) {
            if ((int)($w['kullaniciId'] ?? 0) === $userId && (int)($w['lotId'] ?? 0) === $lotId) {
                $this->dataStore->deleteItem('watchlists', 'id', $w['id']);
                return false; // Çıkarıldı
            }
        }

        $this->dataStore->insertItem('watchlists', [
            'kullaniciId'     => $userId,
            'lotId'           => $lotId,
            'muzayedeId'      => $auctionId,
            'sonGorulenFiyat' => $currentPrice,
        ]);
        return true; // Eklendi
    }

    public function getUserWonOrders(int $userId): array
    {
        $orders = $this->dataStore->getCollection('orders');
        return array_values(array_filter($orders, static fn(array $o) => (int)($o['aliciId'] ?? 0) === $userId));
    }
}
