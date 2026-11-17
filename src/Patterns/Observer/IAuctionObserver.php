<?php
declare(strict_types=1);

namespace Martello\Patterns\Observer;

/**
 * MARTELLO — Observer (Gözlemci) Tasarım Deseni Arayüzü
 * SRS Bölüm 1.7.1, Tablo 1 ve Tablo 2 standartlarına uygundur.
 */
interface IAuctionObserver
{
    /**
     * Müzayedede gerçekleşen bir durum/olay değişikliğinde tetiklenir.
     *
     * @param string $event Olay tipi (örn: BID_PLACED, ANTI_SNIPING_TRIGGERED, LOT_CLOSED, ESCROW_LOCKED)
     * @param array $payload Olayla ilgili zengin veri paketi (lot, teklif, alıcı, bakiye, vb.)
     */
    public function update(string $event, array $payload): void;
}
