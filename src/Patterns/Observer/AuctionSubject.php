<?php
declare(strict_types=1);

namespace Martello\Patterns\Observer;

/**
 * MARTELLO — AuctionSubject (Müzayede Yayımcısı)
 * SRS Bölüm 1.7.1.1: Sistemde her aktif müzayede oturumu bir yayımcı (AuctionSubject) olarak modellenmiştir.
 */
class AuctionSubject
{
    /** @var array<int, IAuctionObserver> */
    private array $observers = [];

    /** @var array<int, array{event: string, payload: array, timestamp: string}> */
    private array $eventHistory = [];

    /**
     * Gözlemci abone et (Subscribe / Attach)
     */
    public function subscribe(IAuctionObserver $observer): void
    {
        if (!in_array($observer, $this->observers, true)) {
            $this->observers[] = $observer;
        }
    }

    /**
     * Gözlemci aboneliğini sonlandır (Unsubscribe / Detach)
     */
    public function unsubscribe(IAuctionObserver $observer): void
    {
        $this->observers = array_values(
            array_filter($this->observers, static fn(IAuctionObserver $obs) => $obs !== $observer)
        );
    }

    /**
     * Tüm abonelere eşzamanlı bildirim gönder (Publish / Notify)
     */
    public function notifyObservers(string $event, array $payload): void
    {
        $this->eventHistory[] = [
            'event'     => $event,
            'payload'   => $payload,
            'timestamp' => date('c'),
        ];

        foreach ($this->observers as $observer) {
            $observer->update($event, $payload);
        }
    }

    /**
     * Kayıtlı gözlemcileri listeler
     * @return array<int, IAuctionObserver>
     */
    public function getObservers(): array
    {
        return $this->observers;
    }

    /**
     * Yayınlanan olay geçmişini döner
     */
    public function getEventHistory(): array
    {
        return $this->eventHistory;
    }
}
