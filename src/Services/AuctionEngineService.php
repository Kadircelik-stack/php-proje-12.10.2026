<?php
declare(strict_types=1);

namespace Martello\Services;

use Martello\Core\JsonDataStore;
use Martello\Patterns\Observer\AuctionSubject;
use Martello\Patterns\Strategy\Bidding\IBiddingRuleStrategy;
use Martello\Patterns\Strategy\Payment\IPaymentStrategy;
use Martello\Repositories\AuctionRepository;
use Throwable;

/**
 * AuctionEngineService — Çekirdek Açık Artırma ve Pey İşleme Motoru
 * IEEE Std 830 / SRS Bölüm 1.7, Bölüm 2.6 (Çekirdek Problemler) ve FR-10..FR-15, FR-32..FR-35, NFR-04, NFR-23, NFR-24
 */
class AuctionEngineService
{
    private JsonDataStore $dataStore;
    private AuctionRepository $auctionRepo;
    private IBiddingRuleStrategy $biddingStrategy;
    private IPaymentStrategy $paymentStrategy;
    private AuctionSubject $auctionSubject;

    public function __construct(
        JsonDataStore $dataStore,
        AuctionRepository $auctionRepo,
        IBiddingRuleStrategy $biddingStrategy,
        IPaymentStrategy $paymentStrategy,
        AuctionSubject $auctionSubject
    ) {
        $this->dataStore       = $dataStore;
        $this->auctionRepo     = $auctionRepo;
        $this->biddingStrategy = $biddingStrategy;
        $this->paymentStrategy = $paymentStrategy;
        $this->auctionSubject  = $auctionSubject;
    }

    /**
     * Canlı Açık Artırmada Pey (Teklif) Ver
     * Pessimistic Locking, ACID Transaction, Escrow Bloke, Anti-Sniping ve Proxy Bidding tetiklemesi içerir.
     */
    
    /**
     * Eserin müzayede türüne göre (VIP vs CANLI) uygun BiddingRuleStrategy nesnesini dinamik çözer.
     */
    public function getStrategyForLot(array $lot): IBiddingRuleStrategy
    {
        if (($lot['muzyedeTuru'] ?? '') === 'VIP' || ($lot['tur'] ?? '') === 'VIP' || (int)($lot['muzayedeId'] ?? 0) === 802) {
            return new \Martello\Patterns\Strategy\Bidding\VipAuctionStrategy();
        }
        return $this->biddingStrategy;
    }

    public function placeBid(
        int $lotId,
        int $userId,
        float $amount,
        string $bidType = 'MANUEL',
        ?int $remainingSeconds = null
    ): array {
        $lockKey = "lot_{$lotId}";
        $this->dataStore->lock($lockKey);

        try {
            $this->dataStore->beginTransaction();

            $lot = $this->auctionRepo->findLotById($lotId);
            if (!$lot) {
                $this->dataStore->rollback();
                $this->dataStore->unlock($lockKey);
                return ['success' => false, 'message' => "Lot #{$lotId} bulunamadı."];
            }

            if (($lot['durum'] ?? '') !== 'ACIK_ARTIRMADA') {
                $this->dataStore->rollback();
                $this->dataStore->unlock($lockKey);
                return ['success' => false, 'message' => "Bu lot şu anda pey kabulüne kapalıdır ({$lot['durum']})."];
            }

            $user = $this->dataStore->getItem('users', 'id', $userId);
            if (!$user) {
                $this->dataStore->rollback();
                $this->dataStore->unlock($lockKey);
                return ['success' => false, 'message' => "Kullanıcı kaydı bulunamadı."];
            }

            // FR-06: Hesap askıya alınmışsa veya pey verme kısıtlanmışsa işlem engellenir
            if (($user['durum'] ?? '') === 'ASKIDA' || ($user['durum'] ?? '') === 'BANLI') {
                $this->dataStore->rollback();
                $this->dataStore->unlock($lockKey);
                return ['success' => false, 'message' => "Hesabınız askıya alınmıştır. Teklif veremezsiniz."];
            }

            if (!empty($user['kisitlamalar']['peyEngeli'])) {
                $this->dataStore->rollback();
                $this->dataStore->unlock($lockKey);
                return ['success' => false, 'message' => "Hesabınızın açık artırmada pey verme yetkisi yönetici tarafından kısıtlanmıştır."];
            }

            // Süre dolmuşsa pey kabul edilmez (Son 30 saniyede yeni pey gelmediğinde sonlandırma)
            if ($remainingSeconds !== null && $remainingSeconds <= 0) {
                $this->dataStore->rollback();
                $this->dataStore->unlock($lockKey);
                // İhaleyi kapat ve pey verme sayacını sıfırla
                $this->closeLot($lotId);
                return [
                    'success' => false,
                    'message' => "İhale süresi dolmuştur (0 sn). Son 30 saniyede yeni teklif gelmediği için müzayede tamamlanmış ve pey sayacı sıfırlanmıştır.",
                ];
            }

            // 1. Kural Denetimi (Strategy)
            $activeStrategy = $this->getStrategyForLot($lot);
            $ruleValidation = $activeStrategy->validateBid($lot, $amount, $user);
            if (!$ruleValidation['valid']) {
                $this->dataStore->rollback();
                $this->dataStore->unlock($lockKey);
                return ['success' => false, 'message' => $ruleValidation['message']];
            }

            // 2. Cüzdan ve Teminat Denetimi (Strategy)
            $wallet = $this->dataStore->getItem('wallets', 'kullaniciId', $userId);
            $payResult = $this->paymentStrategy->processPayment($userId, $amount, [
                'wallet' => $wallet,
                'lotId'  => $lotId,
            ]);

            if (!$payResult['success']) {
                $this->dataStore->rollback();
                $this->dataStore->unlock($lockKey);
                return ['success' => false, 'message' => $payResult['message']];
            }

            // Önceki lider alıcı bilgisi
            $prevLeader = [
                'aliciId'      => $lot['liderAliciId'] ?? null,
                'aliciAnonKod' => $lot['liderAnonimKod'] ?? null,
                'teklifTutari' => $lot['mevcutEnYuksekTeklif'] ?? 0.0,
            ];

            // Önceki liderin cüzdan blokesini çöz (FR-23) ve emanet kaydını güncelle
            if (!empty($prevLeader['aliciId']) && (int)$prevLeader['aliciId'] !== $userId) {
                $prevWallet = $this->dataStore->getItem('wallets', 'kullaniciId', $prevLeader['aliciId']);
                if ($prevWallet) {
                    $prevBlocked = (float)($prevWallet['blokeBakiye'] ?? 0);
                    $newBlocked = max(0.0, $prevBlocked - (float)$prevLeader['teklifTutari']);
                    $this->dataStore->updateItem('wallets', 'id', $prevWallet['id'], [
                        'blokeBakiye'   => $newBlocked,
                        'sonGuncelleme' => date('c'),
                    ]);
                }

                $activeEscrows = $this->dataStore->getCollection('escrowTransactions');
                foreach ($activeEscrows as $ae) {
                    if ((int)($ae['lotId'] ?? 0) === $lotId && (int)($ae['aliciId'] ?? 0) === (int)$prevLeader['aliciId'] && ($ae['durum'] ?? '') === 'EMANETTE_BLOKE' && ($ae['tur'] ?? '') === 'PEY_TEMINATI') {
                        $this->dataStore->updateItem('escrowTransactions', 'id', $ae['id'], [
                            'durum'          => 'BLOKE_IADE_EDILDI',
                            'iadeZamani'     => date('c'),
                            'islemGerekcesi' => 'Yeni daha yüksek pey verilmesi nedeniyle teminat blokesi çözüldü ve alıcıya iade edildi.',
                        ]);
                    }
                }
            }

            // Yeni liderin cüzdanında pey tutarını bloke et
            if ($wallet) {
                $curBlocked = (float)($wallet['blokeBakiye'] ?? 0);
                $this->dataStore->updateItem('wallets', 'id', $wallet['id'], [
                    'blokeBakiye'   => $curBlocked + $amount,
                    'sonGuncelleme' => date('c'),
                ]);
            }

            // SRS: Teklif verme anında pey teminatının emanet hesapta bloke edilmesi
            $buyerName = trim(($user['ad'] ?? '') . ' ' . ($user['soyad'] ?? ''));
            $sellerId = (int)($lot['saticiId'] ?? 2);
            $seller = $this->dataStore->getItem('users', 'id', $sellerId);
            $sellerName = $seller ? trim(($seller['ad'] ?? '') . ' ' . ($seller['soyad'] ?? '')) : 'Mehmet Kaya';
            $commissionRate = 8.0;
            $commAmount = $amount * ($commissionRate / 100);
            $escrowFixedFee = 750.0;
            $netSeller = max(0.0, $amount - $commAmount - $escrowFixedFee);

            $this->dataStore->insertItem('escrowTransactions', [
                'emanetNo'       => 'ESC-PEY-' . $lotId . '-' . time(),
                'tur'            => 'PEY_TEMINATI',
                'lotId'          => $lotId,
                'lotKodu'        => $lot['kod'] ?? ('AUC-' . $lotId),
                'urunBaslik'     => $lot['baslik'] ?? 'Müzayede Eseri',
                'aliciId'        => $userId,
                'aliciAd'        => $buyerName ?: 'Alıcı Koleksiyoner',
                'saticiId'       => $sellerId,
                'saticiAd'       => $sellerName,
                'blokeTutari'    => $amount,
                'paraBirimi'     => $lot['paraBirimi'] ?? 'TRY',
                'kesintiOrani'   => $commissionRate,
                'kesintiTutari'  => $commAmount,
                'sabitHarc'      => $escrowFixedFee,
                'netSaticiHakedis' => $netSeller,
                'durum'          => 'EMANETTE_BLOKE',
                'blokeZamani'    => date('c'),
                'aktarimZamani'  => null,
                'islemKaniti'    => $payResult['transactionId'] ?? ('TX-ESCROW-' . strtoupper(bin2hex(random_bytes(5)))),
                'islemGerekcesi' => 'Teklif verme anında pey teminatı emanet hesapta bloke edildi.',
            ]);

            // Teklif nesnesi oluştur
            $anonKod = $user['anonimKod'] ?? ('Anonim-' . substr(md5((string)$userId), 0, 4));
            $bidRecord = [
                'lotId'          => $lotId,
                'muzayedeId'     => $lot['muzayedeId'] ?? 801,
                'aliciId'        => $userId,
                'aliciAnonimKod' => $anonKod,
                'teklifTutari'   => $amount,
                'paraBirimi'     => $lot['paraBirimi'] ?? 'TRY',
                'teklifTipi'     => $bidType,
                'teklifZamani'   => date('c'),
                'saatEtiketi'    => date('H:i:s'),
                'durum'          => 'GECERLI',
                'blokeIslemId'   => $payResult['transactionId'] ?? ('BLK-' . uniqid()),
            ];
            $savedBid = $this->auctionRepo->saveBid($bidRecord);

            // Lot bilgilerini güncelle
            $lot['mevcutEnYuksekTeklif'] = $amount;
            $lot['liderAliciId']         = $userId;
            $lot['liderAnonimKod']       = $anonKod;

            // Anti-Sniping Değerlendirmesi (FR-33, NFR-23)
            $antiSnipInfo = null;
            if ($remainingSeconds !== null) {
                $activeStrategy = $this->getStrategyForLot($lot);
                $antiSnipInfo = $activeStrategy->evaluateAntiSniping($lot, $remainingSeconds);
                if ($antiSnipInfo['extended']) {
                    $sayac = $lot['antiSnipingSayaci'] ?? ['uzatmaSayisi' => 0];
                    $sayac['uzatmaSayisi'] = ((int)($sayac['uzatmaSayisi'] ?? 0)) + 1;
                    $sayac['sonBitisZamani'] = date('c', time() + $antiSnipInfo['newRemainingSeconds']);
                    $lot['antiSnipingSayaci'] = $sayac;
                    $lot['kalanSureMetni'] = gmdate('H:i:s', $antiSnipInfo['newRemainingSeconds']);
                }
            }

            $this->auctionRepo->saveLot($lot);
            $this->dataStore->commit();

            // 3. Observer (Gözlemci) Bildirimleri
            $payload = [
                'lot'            => $lot,
                'bid'            => $savedBid,
                'user'           => $user,
                'previousLeader' => $prevLeader,
                'antiSnip'       => $antiSnipInfo,
            ];

            if ($antiSnipInfo && $antiSnipInfo['extended']) {
                $this->auctionSubject->notifyObservers('ANTI_SNIPING_TRIGGERED', $payload);
            }

            $this->auctionSubject->notifyObservers('BID_PLACED', $payload);

            $this->dataStore->unlock($lockKey);

            // 4. Çekirdek Problem 1: Otomatik Teklif (Proxy Bidding) Zincirleme Tetikleme
            $this->evaluateProxyBidding($lotId, $userId, $amount);

            $snipNotice = "";
            if ($antiSnipInfo && $antiSnipInfo['extended']) {
                $extNum = $antiSnipInfo['extensionNumber'] ?? ($lot['antiSnipingSayaci']['uzatmaSayisi'] ?? 1);
                $nextInfo = ($extNum < 2) ? " (Sıradaki 2. basım da +30 sn)" : (($extNum === 2) ? " (Sıradaki 3. basım +10 sn olacak)" : " (Sonraki basımlar +10 sn)");
                $snipNotice = " ⏳ (Kademeli Anti-Sniping: Son 30 saniyede pey verildi! {$extNum}. basım: Süre +{$antiSnipInfo['additionalSeconds']} saniye uzatıldı!{$nextInfo})";
            }

            return [
                'success'      => true,
                'message'      => "₺" . number_format($amount, 2, ',', '.') . " tutarındaki teklifiniz başarıyla alındı." . $snipNotice,
                'lot'          => $lot,
                'bid'          => $savedBid,
                'antiSnip'     => $antiSnipInfo,
            ];

        } catch (Throwable $e) {
            $this->dataStore->rollback();
            $this->dataStore->unlock($lockKey);
            return ['success' => false, 'message' => "Pey işlemi sırasında hata: " . $e->getMessage()];
        }
    }

    /**
     * Otomatik Teklif / Temsili Teklif Tanımla (Proxy Bidding)
     * SRS Bölüm 2.6.1 ve NFR-24
     */
    public function setProxyBid(int $lotId, int $userId, float $maxBudget): array
    {
        $lot = $this->auctionRepo->findLotById($lotId);
        if (!$lot) {
            return ['success' => false, 'message' => 'Lot bulunamadı.'];
        }

        $activeStrategy = $this->getStrategyForLot($lot);
        $minNext = $activeStrategy->calculateMinimumNextBid($lot);
        if ($maxBudget < $minNext) {
            return [
                'success' => false,
                'message' => "Maksimum bütçeniz asgari verilebilecek sonraki tekliften (₺" . number_format($minNext, 2) . ") düşük olamaz.",
            ];
        }

        // Mevcut proxy teklif var mı kontrol et
        $existingProxies = $this->dataStore->getCollection('proxyBids');
        $updated = false;
        foreach ($existingProxies as &$pb) {
            if ((int)($pb['lotId'] ?? 0) === $lotId && (int)($pb['aliciId'] ?? 0) === $userId) {
                $pb['maksimumButceTutari'] = $maxBudget;
                $pb['aktifMi']              = true;
                $pb['guncellemeZamani']     = date('c');
                $updated = true;
                break;
            }
        }
        unset($pb);

        if (!$updated) {
            $newProxy = [
                'lotId'               => $lotId,
                'aliciId'             => $userId,
                'maksimumButceTutari' => $maxBudget,
                'paraBirimi'          => $lot['paraBirimi'] ?? 'TRY',
                'aktifMi'             => true,
                'olusturmaZamani'     => date('c'),
            ];
            $this->auctionRepo->saveProxyBid($newProxy);
        } else {
            $this->dataStore->saveCollection('proxyBids', $existingProxies);
        }

        // Eğer kullanıcı şu an lider değilse, bütçesi dahilinde asgari teklifi otomatik ver
        if ((int)($lot['liderAliciId'] ?? 0) !== $userId) {
            $this->placeBid($lotId, $userId, $minNext, 'OTOMATIK_PROXIED');
        }

        return [
            'success'   => true,
            'message'   => "₺" . number_format($maxBudget, 2, ',', '.') . " tavan bütçeli otomatik teklifiniz başarıyla kuruldu.",
            'maxBudget' => $maxBudget,
        ];
    }

    /**
     * Rakip pey geldiğinde otomatik teklif motorunun anında karşı pey sürmesi
     * SRS Bölüm 2.6.1 Mantığı: "Yeni teklif geldiğinde otomatik olarak yeni_teklif + min_artis basar"
     */
    private function evaluateProxyBidding(int $lotId, int $lastBidderId, float $currentPrice): void
    {
        $proxy = $this->auctionRepo->findActiveProxyBid($lotId);
        if (!$proxy) {
            return;
        }

        $proxyUserId = (int)$proxy['aliciId'];
        if ($proxyUserId === $lastBidderId) {
            return; // Zaten kendisi teklif verdi
        }

        $lot = $this->auctionRepo->findLotById($lotId);
        if (!$lot) {
            return;
        }

        $minStep = (float)($lot['minimumArtisTutari'] ?? 500.0);
        $nextRequired = $currentPrice + $minStep;
        $maxBudget = (float)($proxy['maksimumButceTutari'] ?? 0.0);

        if ($maxBudget >= $nextRequired) {
            // Otomatik olarak yeni teklif bas
            $this->placeBid($lotId, $proxyUserId, $nextRequired, 'OTOMATIK_PROXIED');
        } else {
            // Bütçe aşıldı, proxy'yi pasife al
            $this->dataStore->updateItem('proxyBids', 'id', $proxy['id'], [
                'aktifMi'       => false,
                'kapanmaSebebi' => 'BUTCE_ASILDI',
            ]);
        }
    }

    /**
     * Müzayedeyi / Lotu Tamamla ve Kapat
     * SRS FR-29, FR-37: Otomatik sipariş ve hatırlatma tetikler.
     */
    public function closeLot(int $lotId): array
    {
        $lot = $this->auctionRepo->findLotById($lotId);
        if (!$lot) {
            return ['success' => false, 'message' => 'Lot bulunamadı.'];
        }

        $winnerId = $lot['liderAliciId'] ?? null;
        $closingPrice = (float)($lot['mevcutEnYuksekTeklif'] ?? 0);
        $reservePrice = (float)($lot['rezervFiyat'] ?? 0);

        if ($winnerId && $closingPrice >= $reservePrice) {
            $lot['durum'] = 'SATILDI';
            $lot['kalanSureMetni'] = 'Tamamlandı';

            // İhale bittiğinde tutulan pey verme sayısını sıfırla
            if (!isset($lot['antiSnipingSayaci']) || !is_array($lot['antiSnipingSayaci'])) {
                $lot['antiSnipingSayaci'] = [];
            }
            $lot['antiSnipingSayaci']['uzatmaSayisi'] = 0;
            $lot['antiSnipingSayaci']['sonBitisZamani'] = null;
            if (isset($lot['antiSnipingSayaci']['uzatildiMi'])) {
                $lot['antiSnipingSayaci']['uzatildiMi'] = false;
            }

            $this->auctionRepo->saveLot($lot);
            $this->dataStore->commit();

            // Gözlemcileri tetikle (OrderAndReminderObserver siparişi oluşturacak)
            $this->auctionSubject->notifyObservers('LOT_CLOSED', [
                'lot'    => $lot,
                'winner' => ['aliciId' => $winnerId],
            ]);

            // SRS: Kazanan alıcı, satıcı, ürün ve kapanış fiyatı bilgilerini kullanarak benzersiz bir sipariş kaydı oluştur
            $orders = $this->dataStore->getCollection('orders');
            $existingOrder = null;
            $maxId = 5000;
            $existingNos = [];
            foreach ($orders as $o) {
                if ((int)($o['lotId'] ?? 0) === $lotId && !in_array($o['siparisDurumu'] ?? '', ['IPTAL_EDILDI', 'GECERSIZ'], true)) {
                    $existingOrder = $o;
                }
                if ((int)($o['id'] ?? 0) > $maxId) {
                    $maxId = (int)$o['id'];
                }
                if (!empty($o['siparisNo'])) {
                    $existingNos[$o['siparisNo']] = true;
                }
            }

            if ($existingOrder !== null) {
                return [
                    'success' => true,
                    'message' => "Lot başarıyla satıldı. Mevcut benzersiz sipariş kaydı: {$existingOrder['siparisNo']}.",
                    'order'   => $existingOrder,
                ];
            }

            // Alıcı, Satıcı ve Ürün Detayları
            $winner = $this->dataStore->getItem('users', 'id', (int)$winnerId);
            $winnerName = $winner ? trim(($winner['ad'] ?? '') . ' ' . ($winner['soyad'] ?? '')) : 'Koleksiyoner Alıcı';
            $winnerEmail = $winner['email'] ?? 'alici@martello.com';
            $winnerPhone = $winner['telefon'] ?? '+90 532 000 00 00';
            $winnerAddress = $winner['adres'] ?? 'Nişantaşı, Şişli, İstanbul';

            $sellerId = (int)($lot['saticiId'] ?? 2);
            $seller = $this->dataStore->getItem('users', 'id', $sellerId);
            $sellerName = $seller ? trim(($seller['ad'] ?? '') . ' ' . ($seller['soyad'] ?? '')) : 'Koleksiyoner Satıcı';
            $sellerEmail = $seller['email'] ?? 'satici@martello.com';
            $sellerPhone = $seller['telefon'] ?? '+90 532 000 00 00';
            $sellerAddress = $seller['adres'] ?? 'Kadıköy, İstanbul';

            $productId = (int)($lot['urunId'] ?? 501);
            $product = $this->dataStore->getItem('products', 'id', $productId);
            $productTitle = $lot['baslik'] ?? ($product['baslik'] ?? "Müzayede Eseri #{$lotId}");
            $categoryName = $lot['kategoriAdi'] ?? ($product['kategori'] ?? 'Antika & Sanat');
            $productImage = $lot['gorselUrl'] ?? ($product['gorselUrl'] ?? 'public/assets/images/carpet.jpg');

            $commissionRate = 0.08;
            $commission = round($closingPrice * $commissionRate, 2);
            $fixedFee = 750.0;
            $grandTotal = round($closingPrice + $commission, 2);
            $currency = $lot['paraBirimi'] ?? 'TRY';

            // Benzersiz Sipariş Numarası ve ID (Strictly Unique Order Record)
            $newOrderId = $maxId + 1;
            do {
                $uniqueSuffix = strtoupper(bin2hex(random_bytes(3)));
                $newOrderNo = 'ORD-' . date('Y') . '-' . sprintf('%04d', $lotId) . '-' . $uniqueSuffix;
            } while (isset($existingNos[$newOrderNo]));

            $trackingNo = 'KRG-' . date('Y') . '-' . mt_rand(100000, 999999);
            $nowIso = date('c');

            $orderRecord = [
                'id'                => $newOrderId,
                'siparisNo'         => $newOrderNo,
                'lotId'             => $lotId,
                'lotKodu'           => $lot['lotKodu'] ?? ('AUC-' . $lotId),
                'urunId'            => $productId,
                'urunBaslik'        => $productTitle,
                'kategoriAdi'       => $categoryName,
                'urunGorsel'        => $productImage,
                'muzayedeId'        => $lot['muzayedeId'] ?? 801,
                
                // Kazanan Alıcı
                'aliciId'           => (int)$winnerId,
                'aliciAdi'          => $winnerName,
                'aliciEmail'        => $winnerEmail,
                'aliciTelefon'      => $winnerPhone,
                'aliciAdres'        => $winnerAddress,
                'aliciAnonimKod'    => $lot['liderAnonimKod'] ?? ($winner['anonimKod'] ?? ''),

                // Satıcı
                'saticiId'          => $sellerId,
                'saticiAdi'         => $sellerName,
                'saticiEmail'       => $sellerEmail,
                'saticiTelefon'     => $sellerPhone,
                'saticiAdres'       => $sellerAddress,

                // Kapanış Fiyatı ve Finansal Kalemler
                'kapanisFiyati'     => $closingPrice,
                'komisyonOrani'     => $commissionRate * 100,
                'komisyonTutari'    => $commission,
                'sabitHizmetBedeli' => $fixedFee,
                'genelToplamTutar'  => $grandTotal,
                'paraBirimi'        => $currency,

                // Durum ve Zaman Takibi
                'siparisDurumu'     => 'Emanet_Bloke',
                'emanetDurumu'      => 'EMANETTE_BLOKE',
                'kargoTakipNo'      => $trackingNo,
                'olusturmaZamani'   => $nowIso,
                'sonOdemeZamani'    => date('c', strtotime('+48 hours')),
                'sonTarih'          => date('d-M-Y'),
                'kapanisGerekcesi'  => 'Açık artırma süresi doldu / En yüksek pey ile tamamlandı',
            ];

            $this->dataStore->insertItem('orders', $orderRecord);

            // Audit Log
            $allLogs = $this->dataStore->getCollection('auditLogs');
            $newLogId = count($allLogs) + 1;
            $this->dataStore->insertItem('auditLogs', [
                'id'            => $newLogId,
                'logKodu'       => 'LOG-' . date('Y') . '-' . sprintf('%04d', $newLogId),
                'kategori'      => 'FINANSAL',
                'islemTuru'     => 'SIPARIS_OLUSTURULDU',
                'kullaniciId'   => (int)$winnerId,
                'kullaniciAd'   => $winnerName,
                'rol'           => 'ALICI',
                'hedefTur'      => 'ORDER',
                'hedefId'       => $newOrderId,
                'aciklama'      => "Açık artırma süresi doldu. Lot #{$orderRecord['lotKodu']} ('{$productTitle}') için kazanan alıcı {$winnerName} (#{$winnerId}), satıcı {$sellerName} (#{$sellerId}) ve ₺" . number_format($closingPrice, 2, ',', '.') . " kapanış fiyatıyla benzersiz sipariş kaydı ({$newOrderNo}) oluşturuldu.",
                'oncekiDeger'   => 'ACIK_ARTIRMADA',
                'yeniDeger'     => 'SIPARIS_OLUSTURULDU',
                'tarih'         => $nowIso,
                'saatEtiketi'   => date('d.m.Y H:i:s'),
                'ipAdresi'      => $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1',
                'seviye'        => 'INFO',
                'guvenlikOzeti' => strtoupper(substr(md5($newOrderNo . $nowIso), 0, 16)),
            ]);

            // Kazanan alıcı ve satıcıya bildirim
            $allNotifs = $this->dataStore->getCollection('notifications');
            $notifId = count($allNotifs) + 6001;
            $this->dataStore->insertItem('notifications', [
                'id'          => $notifId,
                'kullaniciId' => (int)$winnerId,
                'baslik'      => "🎉 Tebrikler! Müzayedeyi Kazandınız",
                'icerik'      => "Lot #{$orderRecord['lotKodu']} ('{$productTitle}') açık artırmasını ₺" . number_format($closingPrice, 2, ',', '.') . " kapanış fiyatıyla kazandınız. Benzersiz sipariş kaydınız ({$newOrderNo}) oluşturuldu.",
                'okundu'      => false,
                'tarih'       => $nowIso,
                'oncelik'     => 'YUKSEK',
            ]);
            $this->dataStore->insertItem('notifications', [
                'id'          => $notifId + 1,
                'kullaniciId' => $sellerId,
                'baslik'      => "🏷️ Eseriniz Açık Artırmada Satıldı",
                'icerik'      => "Lot #{$orderRecord['lotKodu']} ('{$productTitle}') açık artırmada ₺" . number_format($closingPrice, 2, ',', '.') . " kapanış fiyatıyla satıldı. Sipariş no: {$newOrderNo}.",
                'okundu'      => false,
                'tarih'       => $nowIso,
                'oncelik'     => 'YUKSEK',
            ]);

            $this->dataStore->commit();

            return [
                'success' => true,
                'message' => "Lot başarıyla satıldı ve kazanan alıcı ({$winnerName}), satıcı ({$sellerName}) ve ₺" . number_format($closingPrice, 2, ',', '.') . " kapanış fiyatıyla benzersiz sipariş ({$newOrderNo}) oluşturuldu.",
                'order'   => $orderRecord,
            ];
        }

        $lot['durum'] = 'REZERVE_ULASILMADI';
        $lot['kalanSureMetni'] = 'Tamamlandı';

        // İhale bittiğinde tutulan pey verme sayısını sıfırla
        if (!isset($lot['antiSnipingSayaci']) || !is_array($lot['antiSnipingSayaci'])) {
            $lot['antiSnipingSayaci'] = [];
        }
        $lot['antiSnipingSayaci']['uzatmaSayisi'] = 0;
        $lot['antiSnipingSayaci']['sonBitisZamani'] = null;
        if (isset($lot['antiSnipingSayaci']['uzatildiMi'])) {
            $lot['antiSnipingSayaci']['uzatildiMi'] = false;
        }

        $this->auctionRepo->saveLot($lot);
        $this->dataStore->commit();

        return [
            'success' => false,
            'message' => "Rezerv fiyata (₺" . number_format($reservePrice, 2) . ") ulaşılamadığı için satış gerçekleşmedi.",
        ];
    }
}
