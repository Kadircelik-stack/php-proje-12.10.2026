<?php
declare(strict_types=1);

namespace Martello\Patterns\Observer;

/**
 * NotificationObserver — Çok Kanallı Bildirim Gözlemcisi
 * SRS FR-26 ve R-10: Teklifi geçilen (outbid) alıcıya veya kazanan kullanıcıya Push/E-posta uyarısı iletir.
 */
class NotificationObserver implements IAuctionObserver
{
    /** @var array<int, array> */
    private array $sentNotifications = [];

    public function update(string $event, array $payload): void
    {
        $lot = $payload['lot'] ?? [];
        $lotTitle = $lot['baslik'] ?? 'Müzayede Lotu';
        $lotCode  = $lot['lotKodu'] ?? '';

        if ($event === 'BID_PLACED') {
            $prevLeader = $payload['previousLeader'] ?? null;
            if ($prevLeader && !empty($prevLeader['aliciId'])) {
                $this->sentNotifications[] = [
                    'kullaniciId' => $prevLeader['aliciId'],
                    'olay'        => 'OUTBID_ALERT',
                    'baslik'      => "Teklifiniz Geçildi! ({$lotCode})",
                    'icerik'      => "{$lotTitle} için verdiğiniz teklif geçildi. Yeni lider teklif: " . number_format($lot['mevcutEnYuksekTeklif'] ?? 0, 2) . " " . ($lot['paraBirimi'] ?? 'TRY'),
                    'kanallar'    => ['PUSH', 'EMAIL'],
                    'zaman'       => date('c'),
                ];
            }

            // Yeni lidere onay bildirimi
            $newBid = $payload['bid'] ?? [];
            if (!empty($newBid['aliciId'])) {
                $this->sentNotifications[] = [
                    'kullaniciId' => $newBid['aliciId'],
                    'olay'        => 'LEADER_CONFIRMED',
                    'baslik'      => "En Yüksek Teklif Sizde! ({$lotCode})",
                    'icerik'      => "{$lotTitle} için " . number_format($newBid['teklifTutari'] ?? 0, 2) . " tutarındaki teklifiniz başarıyla alındı.",
                    'kanallar'    => ['PUSH'],
                    'zaman'       => date('c'),
                ];
            }
        } elseif ($event === 'LOT_CLOSED') {
            $winner = $payload['winner'] ?? null;
            if ($winner && !empty($winner['aliciId'])) {
                $this->sentNotifications[] = [
                    'kullaniciId' => $winner['aliciId'],
                    'olay'        => 'AUCTION_WON',
                    'baslik'      => "Tebrikler! Müzayedeyi Kazandınız ({$lotCode})",
                    'icerik'      => "{$lotTitle} ürününü " . number_format($lot['mevcutEnYuksekTeklif'] ?? 0, 2) . " TL ile kazandınız. Lütfen 48 saat içinde ödeme işlemini tamamlayınız.",
                    'kanallar'    => ['EMAIL', 'SMS', 'PUSH'],
                    'zaman'       => date('c'),
                ];
            }
        }
    }

    public function getSentNotifications(): array
    {
        return $this->sentNotifications;
    }
}
