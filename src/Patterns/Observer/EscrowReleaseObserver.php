<?php
declare(strict_types=1);

namespace Martello\Patterns\Observer;

/**
 * EscrowReleaseObserver — Emanet Bloke Çözüm Gözlemcisi
 * SRS FR-23 ve R-02: Yeni en yüksek teklif geldiğinde bir önceki liderin bloke tutarını otomatik çözer.
 */
class EscrowReleaseObserver implements IAuctionObserver
{
    /** @var array<int, array> */
    private array $releasedBlocks = [];

    public function update(string $event, array $payload): void
    {
        if ($event === 'BID_PLACED') {
            $prevLeader = $payload['previousLeader'] ?? null;
            if ($prevLeader && !empty($prevLeader['aliciId'])) {
                $releaseRecord = [
                    'aliciId'        => $prevLeader['aliciId'],
                    'lotId'          => $payload['lot']['id'] ?? null,
                    'cozulenTutar'   => $prevLeader['teklifTutari'] ?? 0,
                    'paraBirimi'     => $payload['lot']['paraBirimi'] ?? 'TRY',
                    'durum'          => 'GECILDI_BLOKESI_COZULDU',
                    'islemZamani'    => date('c'),
                ];
                $this->releasedBlocks[] = $releaseRecord;
            }
        }
    }

    public function getReleasedBlocks(): array
    {
        return $this->releasedBlocks;
    }
}
