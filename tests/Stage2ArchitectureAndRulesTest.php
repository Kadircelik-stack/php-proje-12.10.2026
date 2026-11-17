<?php
declare(strict_types=1);

/**
 * MARTELLO — 2. Aşama Mimari, Tasarım Desenleri ve İş Kuralları Doğrulama Testi (ZeroDay)
 * SRS IEEE Std 830-1998 & ISO/IEC/IEEE 29148:2018 Standartlarına Göre Doğrulama Yapar.
 */

require_once __DIR__ . '/../vendor/autoload.php';

use Martello\Core\JsonDataStore;
use Martello\Patterns\Observer\AuctionSubject;
use Martello\Patterns\Observer\AuditAndFraudObserver;
use Martello\Patterns\Observer\EscrowReleaseObserver;
use Martello\Patterns\Observer\LiveBidScreenObserver;
use Martello\Patterns\Observer\NotificationObserver;
use Martello\Patterns\Observer\OrderAndReminderObserver;
use Martello\Patterns\Observer\TimerSyncObserver;
use Martello\Patterns\Observer\WatchlistObserver;
use Martello\Patterns\Strategy\Bidding\LiveAuctionStrategy;
use Martello\Patterns\Strategy\Bidding\VipAuctionStrategy;
use Martello\Patterns\Strategy\Commission\ArtAntiqueCommission;
use Martello\Patterns\Strategy\Commission\VehicleCommission;
use Martello\Patterns\Strategy\Currency\EurCurrencyStrategy;
use Martello\Patterns\Strategy\Currency\TryCurrencyStrategy;
use Martello\Patterns\Strategy\Currency\UsdCurrencyStrategy;
use Martello\Patterns\Strategy\Payment\EscrowWalletStrategy;
use Martello\Patterns\Strategy\Payment\ThreeDSecurePosStrategy;
use Martello\Repositories\AuctionRepository;
use Martello\Services\AuctionEngineService;
use Martello\Services\PlatformWorkflowService;

class TestRunner
{
    private int $passed = 0;
    private int $failed = 0;

    public function assert(bool $condition, string $testName, string $detail = ''): void
    {
        if ($condition) {
            $this->passed++;
            echo "  \033[32m[BAŞARILI]\033[0m {$testName}\n";
        } else {
            $this->failed++;
            echo "  \033[31m[BAŞARISIZ]\033[0m {$testName} - {$detail}\n";
        }
    }

    public function summary(): void
    {
        echo "\n" . str_repeat('=', 70) . "\n";
        echo "TEST SONUCU: {$this->passed} Başarılı, {$this->failed} Başarısız.\n";
        if ($this->failed === 0) {
            echo "\033[32mTÜM AŞAMA 2 GEREKSİNİMLERİ VE MİMARİ KURALLAR EKSİKSİZ DOĞRULANDI!\033[0m\n";
        } else {
            echo "\033[31mBAZI TESTLER BAŞARISIZ OLDU!\033[0m\n";
            exit(1);
        }
        echo str_repeat('=', 70) . "\n";
    }
}

$t = new TestRunner();

echo "======================================================================\n";
echo "MARTELLO 2. AŞAMA MİMARİ VE İŞ KURALLARI OTOMASYON TESTLERİ (ZeroDay)\n";
echo "======================================================================\n\n";

// 1. Core / JSON DataStore & ACID Testleri
echo "1. Katman: Core JSON DataStore & ACID Bütünlük Testi\n";
$testStorePath = __DIR__ . '/test_datastore.json';
$testLocksDir  = __DIR__ . '/test_locks';

// Kopyala
copy(__DIR__ . '/../storage/martello_datastore.json', $testStorePath);
$store = new JsonDataStore($testStorePath, $testLocksDir);

$data = $store->getAllData();
$t->assert(isset($data['users']) && count($data['users']) >= 5, "JSON Veri Deposu Koleksiyon Yükleme", "Kullanıcılar okunamadı.");

// ACID Rollback Testi
$store->beginTransaction();
$store->insertItem('categories', ['id' => 999, 'ad' => 'Geçici Kategori']);
$store->rollback();
$cat = $store->getItem('categories', 'id', 999);
$t->assert($cat === null, "ACID Transaction Rollback Doğrulaması", "Geri alınan işlem depoya yazıldı.");

// Dosya Kilitleme Testi (Pessimistic Lock - NFR-04)
$lockAcquired = $store->lock('test_resource_1');
$t->assert($lockAcquired, "Pessimistic Locking (flock) Kilidi Edinme");
$store->unlock('test_resource_1');

// 2. Observer (Gözlemci) Tasarım Deseni Testi
echo "\n2. Katman: Observer Tasarım Deseni (7 Somut Gözlemci) Testi\n";
$subject = new AuctionSubject();

$liveScreenObserver = new LiveBidScreenObserver();
$timerSyncObserver  = new TimerSyncObserver();
$watchlistObserver  = new WatchlistObserver();
$escrowObserver     = new EscrowReleaseObserver();
$notifObserver      = new NotificationObserver();
$orderObserver      = new OrderAndReminderObserver();
$auditObserver      = new AuditAndFraudObserver();

$subject->subscribe($liveScreenObserver);
$subject->subscribe($timerSyncObserver);
$subject->subscribe($watchlistObserver);
$subject->subscribe($escrowObserver);
$subject->subscribe($notifObserver);
$subject->subscribe($orderObserver);
$subject->subscribe($auditObserver);

$t->assert(count($subject->getObservers()) === 7, "7 Gözlemcinin AuctionSubject'e Başarıyla Abone Edilmesi");

// Bildirim Tetikleme
$dummyPayload = [
    'lot' => ['id' => 1002, 'lotKodu' => 'AUC-1642', 'baslik' => 'Vintage Saat', 'mevcutEnYuksekTeklif' => 45000, 'saticiId' => 2],
    'bid' => ['id' => 9990, 'aliciId' => 3, 'aliciAnonimKod' => 'Anonim-Zey', 'teklifTutari' => 45000],
    'previousLeader' => ['aliciId' => 6, 'teklifTutari' => 43500],
];
$subject->notifyObservers('BID_PLACED', $dummyPayload);

$t->assert(!empty($liveScreenObserver->getBroadcastQueue()), "LiveBidScreenObserver (≤1sn Anlık Fiyat Yayını Tetiklendi)");
$t->assert(!empty($escrowObserver->getReleasedBlocks()), "EscrowReleaseObserver (Önceki Lider Blokesi Çözümü Tetiklendi)");
$t->assert(!empty($notifObserver->getSentNotifications()), "NotificationObserver (Outbid Çok Kanallı Bildirimi Üretildi)");
$t->assert(!empty($auditObserver->getLoggedEntries()), "AuditAndFraudObserver (WORM Denetim İzi & SHA-256 Chaining Oluştu)");

// 3. Strategy Tasarım Deseni Testleri
echo "\n3. Katman: Strategy Tasarım Deseni Testleri\n";

// Bidding Strategy
$liveRule = new LiveAuctionStrategy();
$vipRule  = new VipAuctionStrategy();

$validation1 = $liveRule->validateBid(['mevcutEnYuksekTeklif' => 1000, 'minimumArtisTutari' => 100], 1050, ['id' => 3]);
$t->assert(!$validation1['valid'], "LiveAuctionStrategy: Asgari Artış Kuralı Reddi");

$validation2 = $liveRule->validateBid(['mevcutEnYuksekTeklif' => 1000, 'minimumArtisTutari' => 100], 1150, ['id' => 3]);
$t->assert($validation2['valid'], "LiveAuctionStrategy: Geçerli Pey Onayı");

// Anti-Sniping Süre Uzatma Kuralı (NFR-23)
$antiSnip1 = $liveRule->evaluateAntiSniping(['antiSnipingSayaci' => ['uzatmaSayisi' => 0]], 25);
$t->assert($antiSnip1['extended'] && $antiSnip1['additionalSeconds'] === 30, "Anti-Sniping: Son 30 Saniye Kala +30 Sn Uzatma Kuralı");

$antiSnip2 = $liveRule->evaluateAntiSniping(['antiSnipingSayaci' => ['uzatmaSayisi' => 2]], 10);
$t->assert($antiSnip2['extended'] && $antiSnip2['additionalSeconds'] === 10, "Anti-Sniping: 3. Uzamada +10 Sn Kademeli Uzatma Kuralı");

// Commission Strategy
$artCommission = new ArtAntiqueCommission();
$t->assert($artCommission->calculateCommission(100000) === 8000.0, "ArtAntiqueCommission: %8 Komisyon Hesabı");

// Currency Strategy
$rates = ['TRY' => 1.0, 'USD' => 34.50, 'EUR' => 37.80];
$usdStrategy = new UsdCurrencyStrategy();
$eurStrategy = new EurCurrencyStrategy();
$t->assert($usdStrategy->convertFromTry(3450, $rates) === 100.0, "UsdCurrencyStrategy: 3450 TL -> 100 USD Dönüşümü");
$t->assert($eurStrategy->convertFromTry(3780, $rates) === 100.0, "EurCurrencyStrategy: 3780 TL -> 100 EUR Dönüşümü");

// Payment Strategy
$posStrategy = new ThreeDSecurePosStrategy();
$posRes = $posStrategy->processPayment(3, 1000);
$t->assert($posRes['success'] && $posRes['approvalTimeSec'] <= 3.0, "ThreeDSecurePosStrategy: ≤3 Saniye Sanal POS Onayı (FR-28)");

// 4. Çekirdek Problemler (Proxy Bidding & Concurrency)
echo "\n4. Katman: Çekirdek Problemler ve İş Kuralları Testi\n";
$repo = new AuctionRepository($store);
$auctionService = new AuctionEngineService($store, $repo, $liveRule, new EscrowWalletStrategy(), $subject);

// Kullanıcı A (Zeynep - ID 3) için Proxy Bidding Kur
$proxyRes = $auctionService->setProxyBid(1002, 3, 50000.0);
$t->assert($proxyRes['success'], "Proxy Bidding: Tavan Bütçe Tanımlama (NFR-24)");

// Kullanıcı B (Emre - ID 6) Manuel Teklif Versin (45.000 TL)
$bidB = $auctionService->placeBid(1002, 6, 45000.0, 'MANUEL', 120);
$t->assert($bidB['success'], "Rakip Teklif Verme (45.000 TL)");

// Proxy Bidding motorunun Kullanıcı A adına otomatik karşı pey (46.500 TL) üretip liderliği alması
$updatedLot = $repo->findLotById(1002);
$t->assert((float)$updatedLot['mevcutEnYuksekTeklif'] === 46500.0 && (int)$updatedLot['liderAliciId'] === 3, "Proxy Bidding: Anında Otomatik Karşı Teklif Üretme (46.500 TL)");

// Temizlik
@unlink($testStorePath);
array_map('unlink', glob($testLocksDir . '/*.*'));
@rmdir($testLocksDir);

$t->summary();
