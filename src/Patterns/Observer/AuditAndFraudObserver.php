<?php
declare(strict_types=1);

namespace Martello\Patterns\Observer;

/**
 * AuditAndFraudObserver — Gerçek Zamanlı Denetim İzi ve Shill Bidding Tespiti
 * SRS FR-25, R-04, R-12 standartlarına uygun: WORM loglama ve SHA-256 blok zincirleme (Hash Chaining).
 */
class AuditAndFraudObserver implements IAuctionObserver
{
    private string $lastHash = '0000000000000000000000000000000000000000000000000000000000000000';
    /** @var array<int, array> */
    private array $loggedEntries = [];

    public function update(string $event, array $payload): void
    {
        $lot    = $payload['lot'] ?? [];
        $bid    = $payload['bid'] ?? [];
        $userId = $bid['aliciId'] ?? ($payload['userId'] ?? 0);
        $userIp = $payload['ip'] ?? '127.0.0.1';

        // R-04: Satıcının kendi ürününe teklif vermesi kontrolü (Shill Bidding)
        $sellerId = $lot['saticiId'] ?? null;
        $shillScore = 0.0;
        $shillStatus = 'TEMIZ';

        if ($sellerId !== null && (int)$userId === (int)$sellerId) {
            $shillScore = 1.0;
            $shillStatus = 'ENGEL_SHILL_BIDDING_DETECTED';
        }

        $logData = [
            'logId'                   => 'WORM-AUDIT-' . uniqid('', true),
            'zamanDamgasi'            => date('c'),
            'kullaniciId'             => $userId,
            'ipAdresi'                => $userIp,
            'islemTipi'               => $event,
            'hedefVarlik'             => 'Lot:' . ($lot['id'] ?? 0) . ' / Tutar:' . ($bid['teklifTutari'] ?? 0),
            'shillBiddingAnomaliSkoru'=> $shillScore,
            'shillBiddingDurumu'      => $shillStatus,
            'prevHash'                => $this->lastHash,
        ];

        // SHA-256 Hash Chaining
        $currentHash = hash('sha256', json_encode($logData) . $this->lastHash);
        $logData['currentHash'] = $currentHash;
        $this->lastHash = $currentHash;

        $this->loggedEntries[] = $logData;
    }

    public function getLoggedEntries(): array
    {
        return $this->loggedEntries;
    }

    public function getLastHash(): string
    {
        return $this->lastHash;
    }
}
