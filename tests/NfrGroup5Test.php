<?php
declare(strict_types=1);

namespace Martello\Tests;

require_once __DIR__ . '/../src/autoload.php';

use Martello\Core\JsonDataStore;
use Martello\Patterns\Observer\AuctionSubject;
use Martello\Patterns\Strategy\Bidding\LiveAuctionStrategy;
use Martello\Patterns\Strategy\Payment\EscrowWalletStrategy;
use Martello\Repositories\AuctionRepository;
use Martello\Services\AuctionEngineService;
use Martello\Services\ResilienceAndSecurityService;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;

/**
 * MARTELLO — Resmi PHPUnit 10 Birim Test Sınıfı (Grup 5: NFR21 - NFR25)
 * SRS Kaynağı: Rümeysa Şevval Kandilci (12/10/2026)
 *
 * Doğrudan test edilen gerçek ürün sınıfları:
 *  - NFR21: src/Services/ResilienceAndSecurityService.php (archiveExpiredAuctions, fetchArchiveReport)
 *  - NFR22: src/Services/ResilienceAndSecurityService.php (allocateIsolatedVipCluster) + JsonDataStore izolasyonu
 *  - NFR23: src/Patterns/Strategy/Bidding/LiveAuctionStrategy.php (evaluateAntiSniping) + AuctionEngineService::placeBid
 *  - NFR24: src/Services/AuctionEngineService.php (setProxyBid, evaluateProxyBidding)
 *  - NFR25: src/Services/ResilienceAndSecurityService.php (generateDynamicSessionAnonCode, maskBidFeedForRole, revealIdentityForDispute)
 */
#[Group('NFR-Grup5')]
final class NfrGroup5Test extends TestCase
{
    private string $projectRoot;
    private string $tempDir;

    protected function setUp(): void
    {
        parent::setUp();
        $this->projectRoot = dirname(__DIR__);
        $this->tempDir     = $this->projectRoot . '/storage/phpunit_g5_' . bin2hex(random_bytes(4));
        mkdir($this->tempDir . '/locks', 0777, true);
    }

    protected function tearDown(): void
    {
        $this->cleanupDir($this->tempDir);
        parent::tearDown();
    }

    private function createIsolatedStore(string $tag): array
    {
        $storeFile = $this->tempDir . "/datastore_{$tag}.json";
        $locksDir  = $this->tempDir . "/locks_{$tag}";
        if (!is_dir($locksDir)) {
            mkdir($locksDir, 0777, true);
        }

        $data = json_decode((string)file_get_contents($this->projectRoot . '/martello_datastore.json'), true);
        foreach ($data['wallets'] as &$w) {
            if (in_array((int)$w['kullaniciId'], [3, 6], true)) {
                $w['bakiye']      = 5000000.0;
                $w['blokeBakiye'] = 0.0;
            }
        }
        unset($w);
        file_put_contents($storeFile, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

        return [new JsonDataStore($storeFile, $locksDir), $storeFile, $locksDir];
    }

    private function cleanupDir(string $dir): void
    {
        if (!is_dir($dir)) {
            return;
        }
        foreach (scandir($dir) as $item) {
            if ($item === '.' || $item === '..') {
                continue;
            }
            $path = $dir . '/' . $item;
            is_dir($path) ? $this->cleanupDir($path) : @unlink($path);
        }
        @rmdir($dir);
    }

    #[TestDox('NFR21: Sona Eren Müzayedelerin Arşivlenmesi (Hot->Cold >1 Yıl, Must <= 30sn, Wish < 3sn Rapor)')]
    public function testNfr21AuctionArchiveRetentionAndFastColdReport(): void
    {
        [$store, $storeFile, $locksDir] = $this->createIsolatedStore('nfr21');
        $sec = new ResilienceAndSecurityService($store, $storeFile, $locksDir);

        // Referans zaman: 2026-11-22; 400 gün önce (2025-10-18) tamamlanmış eski bir müzayede lotu ekle
        $refTs = strtotime('2026-11-22 12:00:00');
        $store->insertItem('lots', [
            'id'                   => 1099,
            'lotKodu'              => 'AUC-OLD-2025',
            'baslik'               => '1890 Osmanlı Gümüş Şamdan (Arşiv Testi)',
            'mevcutEnYuksekTeklif' => 78000.0,
            'durum'                => 'SATILDI',
            'antiSnipingSayaci'    => ['uzatmaSayisi' => 0, 'sonBitisZamani' => '2025-10-15T20:00:00+03:00'],
        ]);

        // 1. Hot (>1 yıl dolanlar) -> Cold Archive taşıma
        $coldPath = $this->tempDir . '/cold_archive/martello_cold_archive.json';
        $archRes  = $sec->archiveExpiredAuctions($coldPath, 365, $refTs);

        $this->assertTrue($archRes['success']);
        $this->assertSame(1, $archRes['movedToCold'], '1 yıldan eski 1 lot soğuk arşive taşınmalıdır.');
        $this->assertSame(100.0, $archRes['integrityRate']);
        $this->assertNull($store->getItem('lots', 'id', 1099), 'Arşivlenen lot Hot Storage havuzundan ayrılmalıdır.');
        $this->assertNotNull($store->getItem('lots', 'id', 1003), '1 yıldan yeni sonuçlanan lot Hot Storage içinde kalmalıdır.');

        // 2. İndeksli soğuk arşivden geçmiş rapor sunumu (Must <= 30sn, Plan <= 10sn, Wish < 3sn)
        $report = $sec->fetchArchiveReport($coldPath, 1099);
        $this->assertTrue($report['found']);
        $this->assertTrue($report['intact']);
        $this->assertSame('COLD_ARCHIVE_STORAGE', $report['storageTier']);
        $this->assertSame('AUC-OLD-2025', $report['lot']['lotKodu']);
        $this->assertLessThanOrEqual(30.0, $report['reportSec']);
        $this->assertLessThan(3.0, $report['reportSec'], 'Wish ihlali: Arşiv raporu sunumu >= 3 sn');

        fwrite(STDOUT, sprintf(
            "\n  [NFR21 Olcum] Hot->Cold Tasinan: %d (>1 yil) | Butunluk: %%100 | Indeksli Arsiv Raporu: %.3f ms (%.5f sn < 3sn) (WISH)\n",
            $archRes['movedToCold'], $report['reportMs'], $report['reportSec']
        ));
    }

    #[TestDox('NFR22: VIP Müzayede Kaynak İzolasyonu (Dedicated Cluster, %0 Çakışma, HPA <= 5sn)')]
    public function testNfr22VipResourceIsolationZeroCollision(): void
    {
        [$stdStore, $stdStoreFile, $stdLocksDir] = $this->createIsolatedStore('nfr22_std');
        $sec = new ResilienceAndSecurityService($stdStore, $stdStoreFile, $stdLocksDir);

        // 1. İzole VIP Cluster tahsisi (Dedicated namespace, ayrı replika ve ayrı kilit havuzu)
        $vipClusterDir = $this->tempDir . '/vip_dedicated_cluster';
        $vipAlloc = $sec->allocateIsolatedVipCluster($vipClusterDir);

        $this->assertTrue($vipAlloc['isolated']);
        $this->assertSame(0, $vipAlloc['collisionCount']);
        $this->assertSame(0.0, $vipAlloc['collisionRatePct']);
        $this->assertLessThanOrEqual(5.0, $vipAlloc['hpaScaleOutSec'], 'Plan ihlali: HPA ölçekleme > 5sn');

        // 2. Gürültülü Komşu (Noisy Neighbor) Testi: Standart havuz kilitliyken VIP kümesi %0 bloklanmayla çalışmalı
        $this->assertTrue($stdStore->lock('lot_1002')); // Standart havuz yoğun kilit altında!
        try {
            /** @var JsonDataStore $vipStore */
            $vipStore = $vipAlloc['vipDataStore'];
            $tVip0 = microtime(true);
            $vipLockOk = $vipStore->lock('lot_1002');
            $vipStore->updateItem('lots', 'id', 1002, ['mevcutEnYuksekTeklif' => 150000.0]);
            $vipStore->unlock('lot_1002');
            $vipOpMs = round((microtime(true) - $tVip0) * 1000, 3);

            $this->assertTrue($vipLockOk, 'Standart havuz kilitliyken VIP kümesi etkilenmemelidir.');
            // Standart depo fiyatı değişmemiş olmalı (tam veri izolasyonu)
            $this->assertEquals(43500.0, (float)$stdStore->getItem('lots', 'id', 1002)['mevcutEnYuksekTeklif']);
            $this->assertEquals(150000.0, (float)$vipStore->getItem('lots', 'id', 1002)['mevcutEnYuksekTeklif']);
        } finally {
            $stdStore->unlock('lot_1002');
        }

        fwrite(STDOUT, sprintf(
            "\n  [NFR22 Olcum] Kaynak Cakismasi: %%0 (0/2) | HPA Tahsis: %.4f sn (<=5sn) | Standart Kilitliyken VIP Islem: %.3f ms (WISH)\n",
            $vipAlloc['hpaScaleOutSec'], $vipOpMs
        ));
    }

    #[TestDox('NFR23 (Çekirdek Problem): Kademeli Anti-Sniping (+30sn/+30sn/+10sn, Must <= 50ms, Plan < 20ms)')]
    public function testNfr23AntiSnipingSteppedExtensionCoreProblem(): void
    {
        [$store] = $this->createIsolatedStore('nfr23');
        $repo     = new AuctionRepository($store);
        $strategy = new LiveAuctionStrategy();
        $engine   = new AuctionEngineService(
            $store,
            $repo,
            $strategy,
            new EscrowWalletStrategy(),
            new AuctionSubject()
        );

        // 1. Normal aralık (>30sn): uzatma yapılmamalı
        $lot = $repo->findLotById(1002);
        $noExt = $strategy->evaluateAntiSniping($lot, 90);
        $this->assertFalse($noExt['extended']);
        $this->assertSame(0, $noExt['additionalSeconds']);

        // 2. Kademeli Anti-Sniping: Son 30sn içinde 1. (+30sn), 2. (+30sn), 3. (+10sn), 4. (+10sn)
        $expectedSteps = [1 => 30, 2 => 30, 3 => 10, 4 => 10];
        $triggerLatencies = [];
        $currentPrice = (float)$lot['mevcutEnYuksekTeklif'];

        foreach ($expectedSteps as $stepNum => $expectedAddSec) {
            $currentPrice += 1500.0;
            $bidderId = ($stepNum % 2 === 1) ? 6 : 3;
            $res = $engine->placeBid(1002, $bidderId, $currentPrice, 'MANUEL', 15);

            $this->assertTrue($res['success']);
            $this->assertTrue($res['antiSnip']['extended']);
            $this->assertSame($expectedAddSec, $res['antiSnip']['additionalSeconds'], "{$stepNum}. uzatmada +{$expectedAddSec}sn bekleniyordu.");
            $this->assertSame($stepNum, $res['antiSnip']['extensionNumber']);
            $this->assertLessThanOrEqual(50.0, $res['antiSnip']['triggerLatencyMs']);
            $this->assertLessThan(20.0, $res['antiSnip']['triggerLatencyMs']);
            $triggerLatencies[] = (float)$res['antiSnip']['triggerLatencyMs'];
        }

        // 3. Son 30 saniyede yeni pey gelmeyip süre 0 sn olduğunda ihale otomatik sonlandırılmalı ve sayaç sıfırlanmalı
        $expiredRes = $engine->placeBid(1002, 6, $currentPrice + 1500.0, 'MANUEL', 0);
        $this->assertFalse($expiredRes['success']);
        $closedLot = $repo->findLotById(1002);
        $this->assertSame('SATILDI', $closedLot['durum']);
        $this->assertSame(0, (int)$closedLot['antiSnipingSayaci']['uzatmaSayisi']);

        $maxTriggerMs = max($triggerLatencies);
        fwrite(STDOUT, sprintf(
            "\n  [NFR23 Olcum] Kademeler: +30sn/+30sn/+10sn/+10sn %%100 DOGRU | 0sn Kapanis & Sayac Sifirlama: OK | Tetikleme Maks: %.4f ms (WISH)\n",
            $maxTriggerMs
        ));
    }

    #[TestDox('NFR24 (Çekirdek Problem): Otomatik Teklif / Proxy Bidding (Must <= 100ms, Plan < 40ms, Tavan Koruma)')]
    public function testNfr24ProxyBiddingAutomaticCounterBidCoreProblem(): void
    {
        [$store] = $this->createIsolatedStore('nfr24');
        $repo   = new AuctionRepository($store);
        $engine = new AuctionEngineService(
            $store,
            $repo,
            new LiveAuctionStrategy(),
            new EscrowWalletStrategy(),
            new AuctionSubject()
        );

        // Lot #1002 başlangıç lideri: Kullanıcı #3 (₺43.500, min artış: ₺1.500)
        // Kullanıcı #3 için tavan bütçe ₺49.000 olacak şekilde Proxy Bid kur
        $setup = $engine->setProxyBid(1002, 3, 49000.0);
        $this->assertTrue($setup['success']);

        // 1. Rakip Kullanıcı #6 ₺45.000 teklif verince Proxy motoru otomatik ₺46.500 karşı teklif üretmeli
        $t0 = microtime(true);
        $compBid1 = $engine->placeBid(1002, 6, 45000.0, 'MANUEL', 180);
        $proxyReactMs = round((microtime(true) - $t0) * 1000, 3);

        $this->assertTrue($compBid1['success']);
        $lotAfter1 = $repo->findLotById(1002);
        $this->assertSame(3, (int)$lotAfter1['liderAliciId'], 'Proxy motoru Kullanıcı #3 adına otomatik liderliği geri almalıdır.');
        $this->assertEquals(46500.0, (float)$lotAfter1['mevcutEnYuksekTeklif'], 'Minimum artış (+1.500) kuralıyla ₺46.500 otomatik pey verilmelidir.');
        $this->assertLessThanOrEqual(100.0, $proxyReactMs, 'Must ihlali: Proxy tepki süresi > 100ms');

        // 2. Rakip Kullanıcı #6 ₺48.000 verince sıradaki teklif (₺49.500) tavanı (₺49.000) aşacağı için Proxy DURMALI!
        $compBid2 = $engine->placeBid(1002, 6, 48000.0, 'MANUEL', 180);
        $this->assertTrue($compBid2['success']);

        $lotAfter2 = $repo->findLotById(1002);
        $this->assertSame(6, (int)$lotAfter2['liderAliciId'], 'Tavan bütçe aşıldığında rakip lider kalmalıdır.');
        $this->assertEquals(48000.0, (float)$lotAfter2['mevcutEnYuksekTeklif']);
        $this->assertLessThanOrEqual(49000.0, (float)$lotAfter2['mevcutEnYuksekTeklif'], 'Tavan bütçe kesinlikle aşılmamalıdır.');

        $proxies = $store->getCollection('proxyBids');
        $user3Proxy = end($proxies);
        $this->assertFalse((bool)$user3Proxy['aktifMi']);
        $this->assertSame('BUTCE_ASILDI', $user3Proxy['kapanmaSebebi']);

        $tier = ($proxyReactMs < 15.0) ? 'WISH' : (($proxyReactMs < 40.0) ? 'PLAN' : 'MUST');
        fwrite(STDOUT, sprintf(
            "\n  [NFR24 Olcum] Rakip 45.000 -> Oto Karsi Pey: 46.500 (%.2f ms) | Tavan 49.000 Asilmadi (BUTCE_ASILDI) (%s)\n",
            $proxyReactMs, $tier
        ));
    }

    #[TestDox('NFR25: Anonim Katılımcı Kimlik Gizleme ve İhtilafta Admin RBAC Erişimi (Plan < 10ms, 0 PII Sızıntısı)')]
    public function testNfr25AnonymousBidderMaskingAndAdminDisputeReveal(): void
    {
        [$store, $storeFile, $locksDir] = $this->createIsolatedStore('nfr25');
        $sec = new ResilienceAndSecurityService($store, $storeFile, $locksDir);

        $rawBids = $store->getCollection('bids');
        $this->assertNotEmpty($rawBids);

        // 1. Alıcı ve Satıcı rolleri için teklif akışı maskelemesi (%100 PII gizleme, Plan < 10ms)
        $maskedForBuyer  = $sec->maskBidFeedForRole($rawBids, 'ALICI', 'OTURUM-A');
        $maskedForSeller = $sec->maskBidFeedForRole($rawBids, 'SATICI', 'OTURUM-B');

        $this->assertLessThan(10.0, $maskedForBuyer['maskingMs']);
        $this->assertLessThan(10.0, $maskedForSeller['maskingMs']);

        $serializedBuyer = json_encode($maskedForBuyer['bids'], JSON_UNESCAPED_UNICODE);
        $this->assertStringNotContainsString('Zeynep', $serializedBuyer);
        $this->assertStringNotContainsString('zeynep@koleksiyon.com', $serializedBuyer);
        $this->assertStringNotContainsString('+905443334455', $serializedBuyer);
        $this->assertStringNotContainsString('"aliciId"', $serializedBuyer);
        $this->assertStringStartsWith('Anonim-', $maskedForBuyer['bids'][0]['aliciAnonimKod']);

        // Wish: Farklı oturumlarda kriptografik dinamik anonim kod değişmeli (tahmin edilemezlik)
        $this->assertNotSame(
            $maskedForBuyer['bids'][0]['aliciAnonimKod'],
            $maskedForSeller['bids'][0]['aliciAnonimKod']
        );

        // 2. Gerçek kimlik sadece aktif ihtilafta RBAC yetkili ADMIN'e açılmalı
        $buyerUser  = $store->getItem('users', 'id', 6);
        $sellerUser = $store->getItem('users', 'id', 2);
        $adminUser  = $store->getItem('users', 'id', 1);

        $this->assertSame(403, $sec->revealIdentityForDispute(3, $buyerUser, true)['httpStatus']);
        $this->assertSame(403, $sec->revealIdentityForDispute(3, $sellerUser, true)['httpStatus']);
        $this->assertSame(403, $sec->revealIdentityForDispute(3, $adminUser, false)['httpStatus']);

        $adminReveal = $sec->revealIdentityForDispute(3, $adminUser, true);
        $this->assertTrue($adminReveal['allowed']);
        $this->assertSame(200, $adminReveal['httpStatus']);
        $this->assertSame('Zeynep Demir', $adminReveal['identity']['adSoyad']);

        fwrite(STDOUT, sprintf(
            "\n  [NFR25 Olcum] Maskeleme: %%100 (0 PII sizintisi) | Sure: %.4f ms (<10ms) | Dinamik Kod: %s | Admin Ihtilaf RBAC: OK (WISH)\n",
            $maskedForBuyer['maskingMs'], $maskedForBuyer['bids'][0]['aliciAnonimKod']
        ));
    }
}
