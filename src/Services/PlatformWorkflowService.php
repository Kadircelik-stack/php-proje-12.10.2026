<?php
declare(strict_types=1);

namespace Martello\Services;

use Martello\Core\JsonDataStore;
use Martello\Patterns\Observer\AuctionSubject;

/**
 * PlatformWorkflowService — İş Akışları, Moderasyon ve Yetkilendirme Servisi
 * SRS FR-01..FR-09, FR-16..FR-20, FR-25..FR-31 gereksinimlerini karşılar.
 */
class PlatformWorkflowService
{
    private JsonDataStore $dataStore;
    private AuctionSubject $auctionSubject;

    public function __construct(JsonDataStore $dataStore, AuctionSubject $auctionSubject)
    {
        $this->dataStore      = $dataStore;
        $this->auctionSubject = $auctionSubject;
    }

    /**
     * Satıcı Ürün ve İlan Ekleme (FR-07, FR-31)
     */
    public function createProductAndListing(int $sellerId, array $data): array
    {
        $title = trim($data['baslik'] ?? '');
        $categoryId = (int)($data['kategoriId'] ?? 10);
        $startPrice = (float)($data['baslangicFiyati'] ?? 1000);
        $reservePrice = (float)($data['rezervFiyat'] ?? $startPrice);
        $minStep = (float)($data['minimumArtisTutari'] ?? 100);

        if ($title === '') {
            return ['success' => false, 'message' => "Ürün başlığı zorunludur."];
        }

        // Ürünü kaydet
        $product = $this->dataStore->insertItem('products', [
            'saticiId'          => $sellerId,
            'kategoriId'        => $categoryId,
            'baslik'            => $title,
            'aciklama'          => $data['aciklama'] ?? '',
            'fizikselKondisyon' => $data['fizikselKondisyon'] ?? 'İnceleme Bekliyor',
            'orijinallikDurumu' => 'Ekspertizde',
            'gorseller'         => ['varsayilan_lot.jpg'],
            'dinamikNitelikler' => $data['nitelikler'] ?? [],
        ]);

        // İlan oluştur
        $listing = $this->dataStore->insertItem('listings', [
            'ilanKodu'         => 'ILN-' . date('Y') . '-' . mt_rand(100, 999),
            'urunId'           => $product['id'],
            'saticiId'         => $sellerId,
            'ekspertizRaporId' => null,
            'durum'            => 'EKSPERTIZ_BEKLIYOR',
        ]);

        // Ekspertiz talebi aç (FR-17)
        $this->dataStore->insertItem('appraisalRequests', [
            'talepNo'        => 'EXP-REQ-' . mt_rand(500, 999),
            'lotKodu'        => 'AUC-' . mt_rand(1100, 1999),
            'urunId'         => $product['id'],
            'atananEksperId' => 4, // Varsayılan uzman Burak Şahin
            'durum'          => 'BEKLEYEN',
            'eskalasyon'     => false,
            'not'            => 'Yeni ürün eklendi, ekspertiz incelemesi bekliyor.',
        ]);

        return [
            'success' => true,
            'message' => "Ürün ve ilan taslağı oluşturuldu. Ekspertiz onayına yönlendirildi.",
            'product' => $product,
            'listing' => $listing,
        ];
    }

    /**
     * Ekspertiz Raporu Düzenleme ve Fiyat Önerisi Girme (FR-18)
     */
    public function submitAppraisalReport(int $expertId, int $requestId, float $suggestedValue, string $authenticityNote, array $points): array
    {
        $request = $this->dataStore->getItem('appraisalRequests', 'id', $requestId);
        if (!$request) {
            return ['success' => false, 'message' => "Ekspertiz talebi bulunamadı."];
        }

        $report = $this->dataStore->insertItem('appraisalReports', [
            'raporNo'              => 'REP-' . date('Y') . '-' . mt_rand(200, 899),
            'talepId'              => $requestId,
            'urunId'               => $request['urunId'] ?? 0,
            'eksperId'             => $expertId,
            'onerilenPiyasaDegeri' => $suggestedValue,
            'orijinallikNotu'      => $authenticityNote,
            'maddeler'             => $points,
            'denetimDurumu'        => 'ONAYLANDI',
            'raporTarihi'          => date('c'),
        ]);

        // Talebi tamamlandı yap
        $this->dataStore->updateItem('appraisalRequests', 'id', $requestId, [
            'durum' => 'TAMAMLANDI',
            'not'   => 'Ekspertiz raporu tamamlandı.',
        ]);

        // İlgili ilanı onaya hazırla
        $listings = $this->dataStore->getCollection('listings');
        foreach ($listings as $l) {
            if ((int)($l['urunId'] ?? 0) === (int)($request['urunId'] ?? 0)) {
                $this->dataStore->updateItem('listings', 'id', $l['id'], [
                    'durum'            => 'ONAYLANDI',
                    'ekspertizRaporId' => $report['id'],
                ]);
                break;
            }
        }

        return [
            'success' => true,
            'message' => "Ekspertiz raporu başarıyla sisteme kaydedildi ve ilan onay aşamasına iletildi.",
            'report'  => $report,
        ];
    }

    /**
     * Admin: İlan Onaylama / Reddetme ve Müzayedeye Lot Olarak Ekleme (FR-09, FR-10)
     */
    public function approveListingAndPublish(int $listingId, float $startPrice, float $reservePrice, float $minStep): array
    {
        $listing = $this->dataStore->getItem('listings', 'id', $listingId);
        if (!$listing) {
            return ['success' => false, 'message' => "İlan bulunamadı."];
        }

        $this->dataStore->updateItem('listings', 'id', $listingId, ['durum' => 'YAYINDA']);

        $product = $this->dataStore->getItem('products', 'id', $listing['urunId']);

        // Müzayedeye lot olarak ekle
        $newLot = $this->dataStore->insertItem('lots', [
            'lotKodu'              => 'AUC-' . mt_rand(2000, 2999),
            'lotNumarasi'          => mt_rand(20, 50),
            'muzayedeId'           => 801,
            'urunId'               => $listing['urunId'],
            'ilanId'               => $listingId,
            'ekspertizRaporId'     => $listing['ekspertizRaporId'] ?? 301,
            'baslik'               => $product['baslik'] ?? 'Müzayede Lotu',
            'kategoriAdi'          => 'Antika & Sanat',
            'paraBirimi'           => 'TRY',
            'baslangicFiyati'      => $startPrice,
            'rezervFiyat'          => $reservePrice,
            'mevcutEnYuksekTeklif' => $startPrice,
            'minimumArtisTutari'   => $minStep,
            'liderAliciId'         => null,
            'liderAnonimKod'       => 'Henüz Teklif Yok',
            'durum'                => 'ACIK_ARTIRMADA',
            'kalanSureMetni'       => '2 gün 04 saat',
            'antiSnipingSayaci'    => [
                'uzatmaSayisi'   => 0,
                'sonBitisZamani' => date('c', strtotime('+2 days')),
            ],
        ]);

        return [
            'success' => true,
            'message' => "İlan onaylandı ve açık artırma kataloğuna eklendi.",
            'lot'     => $newLot,
        ];
    }

    /**
     * Admin: Kullanıcı Durumu Değiştirme (Askıya Alma / Banlama / Aktif Etme) (FR-06)
     */
    public function toggleUserBan(int $userId, string $newStatus): array
    {
        $user = $this->dataStore->getItem('users', 'id', $userId);
        if (!$user) {
            return ['success' => false, 'message' => "Kullanıcı bulunamadı."];
        }

        $this->dataStore->updateItem('users', 'id', $userId, ['durum' => $newStatus]);
        return [
            'success' => true,
            'message' => "Kullanıcı (#{$userId} {$user['ad']} {$user['soyad']}) durumu '{$newStatus}' olarak güncellendi.",
        ];
    }

    /**
     * Cüzdana Bakiye Yükleme (FR-07/Alıcı)
     */
    public function depositToWallet(int $userId, float $amount): array
    {
        if ($amount <= 0) {
            return ['success' => false, 'message' => "Geçerli bir yükleme tutarı giriniz."];
        }

        $wallet = $this->dataStore->getItem('wallets', 'kullaniciId', $userId);
        if (!$wallet) {
            $wallet = $this->dataStore->insertItem('wallets', [
                'kullaniciId'   => $userId,
                'paraBirimi'    => 'TRY',
                'bakiye'        => $amount,
                'blokeBakiye'   => 0.0,
                'aktifBlokeler' => [],
                'sonGuncelleme' => date('c'),
            ]);
        } else {
            $newBal = (float)($wallet['bakiye'] ?? 0) + $amount;
            $this->dataStore->updateItem('wallets', 'id', $wallet['id'], [
                'bakiye'        => $newBal,
                'sonGuncelleme' => date('c'),
            ]);
        }

        return [
            'success' => true,
            'message' => "₺" . number_format($amount, 2, ',', '.') . " tutarındaki bakiye cüzdanınıza başarıyla yüklendi.",
        ];
    }

    /**
     * İhtilaf / İtiraz Talebi Oluşturma (FR-20)
     */
    public function createDispute(int $userId, int $lotId, string $topic, string $description): array
    {
        $lot = $this->dataStore->getItem('lots', 'id', $lotId);
        $dispute = $this->dataStore->insertItem('disputes', [
            'talepNo'              => 'DISP-' . date('Y') . '-' . mt_rand(10, 99),
            'lotId'                => $lotId,
            'lotKodu'              => $lot['lotKodu'] ?? "LOT-{$lotId}",
            'siparisId'            => null,
            'olusturanKullaniciId' => $userId,
            'konuTipi'             => $topic,
            'aciklama'             => $description,
            'durum'                => 'INCELENIYOR',
            'olusturmaTarihi'      => date('c'),
        ]);

        return [
            'success' => true,
            'message' => "İhtilaf/itiraz talebiniz alındı ve hukuk/denetim kuruluna iletildi.",
            'dispute' => $dispute,
        ];
    }

    /**
     * Canlı Sohbet Mesajı Gönderme (FR-27)
     */
    public function sendChatMessage(int $sessionId, int $senderId, string $senderName, string $text): array
    {
        $session = $this->dataStore->getItem('liveChatSessions', 'id', $sessionId);
        if (!$session) {
            return ['success' => false, 'message' => "Sohbet oturumu bulunamadı."];
        }

        $messages = $session['mesajlar'] ?? [];
        $messages[] = [
            'gonderenId' => $senderId,
            'gonderenAd' => $senderName,
            'icerik'     => $text,
            'zaman'      => date('Y-m-d H:i:s'),
        ];

        $this->dataStore->updateItem('liveChatSessions', 'id', $sessionId, [
            'mesajlar' => $messages,
        ]);

        return ['success' => true, 'message' => "Mesaj iletildi."];
    }
}
