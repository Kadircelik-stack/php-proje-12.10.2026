<?php
declare(strict_types=1);

namespace Martello\Tests;

require_once __DIR__ . '/../src/autoload.php';

use Martello\Core\JsonDataStore;
use Martello\Patterns\Observer\AuctionSubject;
use Martello\Patterns\Observer\AuditAndFraudObserver;
use Martello\Patterns\Strategy\Bidding\LiveAuctionStrategy;
use Martello\Patterns\Strategy\Payment\EscrowWalletStrategy;
use Martello\Patterns\Strategy\Payment\ThreeDSecurePosStrategy;
use Martello\Repositories\AuctionRepository;
use Martello\Services\AuctionEngineService;
use Martello\Services\PlatformWorkflowService;
use Martello\Services\ResilienceAndSecurityService;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;

/**
 * MARTELLO — Resmi PHPUnit 10 Birim Test Sınıfı (Grup 2: NFR6 - NFR10)
 * SRS Kaynağı: Rümeysa Şevval Kandilci (12/10/2026)
 *
 * Doğrudan test edilen gerçek ürün sınıfları:
 *  - NFR6 : src/Services/AuctionEngineService.php (checkAndAdmitParticipant)
 *  - NFR7 : src/Services/ResilienceAndSecurityService.php (performHealthCheck, evaluateAvailabilitySla)
 *  - NFR8 : src/Core/JsonDataStore.php (createReplicaSnapshot, failoverRestoreFromReplica)
 *  - NFR9 : src/Services/PlatformWorkflowService.php (saveUserCard) & ThreeDSecurePosStrategy.php
 *  - NFR10: src/Patterns/Observer/AuditAndFraudObserver.php (evaluateRequestTraffic)
 */
#[Group('NFR-Grup2')]
final class NfrGroup2Test extends TestCase
{
    private string $projectRoot;
    private string $tempDir;

    protected function setUp(): void
    {
        parent::setUp();
        $this->projectRoot = dirname(__DIR__);
        $this->tempDir     = $this->projectRoot . '/storage/phpunit_g2_' . bin2hex(random_bytes(4));
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

    #[TestDox('NFR6: Katılımcı Kapasitesi 50.000 Sınırı ve Kontenjan Dolu Yönlendirmesi (Must <= 200ms, Plan <= 100ms)')]
    public function testNfr06ParticipantCapacityLimit50000(): void
    {
        [$store] = $this->createIsolatedStore('nfr6');
        $repo    = new AuctionRepository($store);
        $engine  = new AuctionEngineService(
            $store,
            $repo,
            new LiveAuctionStrategy(),
            new EscrowWalletStrategy(),
            new AuctionSubject()
        );

        // Müzayede #801'i 49.999 aktif katılımcı durumuna getir
        $store->updateItem('auctions', 'id', 801, [
            'katilimciSayisi'   => 49999,
            'maksimumKatilimci' => 50000,
        ]);

        // 50.000. kullanıcı güvenle kabul edilmeli
        $admit50000 = $engine->checkAndAdmitParticipant(801, 50000);
        $this->assertTrue($admit50000['admitted']);
        $this->assertSame(200, $admit50000['httpStatus']);
        $this->assertSame(50000, $admit50000['currentCount']);

        // 50.001. kullanıcı <= 100ms (Plan) / <= 200ms (Must) içinde engellenip "Kontenjan Dolu" ekranına yönlendirilmeli
        $reject50001 = $engine->checkAndAdmitParticipant(801, 50001);
        $this->assertFalse($reject50001['admitted']);
        $this->assertSame(429, $reject50001['httpStatus']);
        $this->assertSame('/kontenjan-dolu', $reject50001['redirect']);
        $this->assertSame('Kontenjan Dolu', $reject50001['screenTitle']);
        $this->assertLessThanOrEqual(200.0, $reject50001['decisionMs'], 'Must ihlali: 50.001. istek reddi > 200ms');
        $this->assertLessThanOrEqual(100.0, $reject50001['decisionMs'], 'Plan ihlali: Kontenjan Dolu yönlendirmesi > 100ms');

        // Wish: Sınır aşımı anında mevcut aktif kullanıcı (Kullanıcı #6) gecikmesiz teklif verebilmeli
        $tBid0 = microtime(true);
        $activeUserBid = $engine->placeBid(1002, 6, 45000.0, 'MANUEL', 180);
        $activeBidMs   = round((microtime(true) - $tBid0) * 1000, 2);
        $this->assertTrue($activeUserBid['success'], 'Sınır aşımı anında aktif kullanıcı teklif verebilmelidir.');
        $this->assertLessThanOrEqual(100.0, $activeBidMs);

        fwrite(STDOUT, sprintf(
            "\n  [NFR6 Olcum] 50.000. kabul: HTTP 200 | 50.001. red: HTTP 429 -> %s (%.3f ms) | Aktif kullanici pey: %.2f ms (WISH)\n",
            $reject50001['redirect'], $reject50001['decisionMs'], $activeBidMs
        ));
    }

    #[TestDox('NFR7: Kesintisiz Çalışma ve Yüksek Erişilebilirlik (Must >= %99.9, Plan >= %99.95, Wish >= %99.99)')]
    public function testNfr07HighAvailabilityAndHealthCheck(): void
    {
        [$store, $storeFile, $locksDir] = $this->createIsolatedStore('nfr7');
        $resilience = new ResilienceAndSecurityService($store, $storeFile, $locksDir);

        // 50 ardışık canlı sağlık kontrolü (APM / Prometheus Health Probe)
        $probes = 50;
        $upCount = 0;
        $probeLatencies = [];
        for ($i = 0; $i < $probes; $i++) {
            $health = $resilience->performHealthCheck();
            $this->assertTrue($health['healthy']);
            $this->assertSame('UP', $health['status']);
            $this->assertSame(200, $health['httpStatus']);
            $upCount++;
            $probeLatencies[] = (float)$health['probeLatencyMs'];
        }
        $avgProbeMs = round(array_sum($probeLatencies) / count($probeLatencies), 3);
        $liveUptime = ($upCount / $probes) * 100.0;
        $this->assertSame(100.0, $liveUptime);

        // Yıllık erişilebilirlik SLA doğrulaması (525.600 dk / yıl; 35 dk kesinti => %99.9933 -> WISH)
        $slaWish = $resilience->evaluateAvailabilitySla(525600.0, 35.0);
        $this->assertTrue($slaWish['meetsMust']);
        $this->assertTrue($slaWish['meetsPlan']);
        $this->assertTrue($slaWish['meetsWish']);
        $this->assertGreaterThanOrEqual(99.99, $slaWish['uptimePercentage']);

        // Negatif sınır testi: 600 dk (10 saat) kesinti Must (%99.9 = 8.76 saat) sınırını ihlal etmeli
        $slaFail = $resilience->evaluateAvailabilitySla(525600.0, 600.0);
        $this->assertFalse($slaFail['meetsMust']);

        fwrite(STDOUT, sprintf(
            "\n  [NFR7 Olcum] 50/50 Health-Check UP (Ort: %.3f ms) | Canli Uptime: %%100 | Yillik SLA: %%%.4f -> %s\n",
            $avgProbeMs, $slaWish['uptimePercentage'], $slaWish['slaTier']
        ));
    }

    #[TestDox('NFR8: Felaket Kurtarma ve Veri Kurtarma Hedefleri (RPO = 0, Must RTO <= 30dk, Wish RTO <= 5dk)')]
    public function testNfr08DisasterRecoveryZeroRpoAndRto(): void
    {
        [$store, $storeFile, $locksDir] = $this->createIsolatedStore('nfr8');
        $repo   = new AuctionRepository($store);
        $engine = new AuctionEngineService(
            $store,
            $repo,
            new LiveAuctionStrategy(),
            new EscrowWalletStrategy(),
            new AuctionSubject()
        );

        // Kritik finansal işlem / canlı teklif oluştur
        $bidRes = $engine->placeBid(1002, 6, 45000.0, 'MANUEL', 180);
        $this->assertTrue($bidRes['success']);
        $expectedBidCount = count($store->getCollection('bids'));

        // 1. İkincil yedeğe (Active-Passive Replica) SHA-256 imzalı anlık kopya al (RPO = 0)
        $replicaPath = $this->tempDir . '/dr_secondary/martello_replica.json';
        $snap = $store->createReplicaSnapshot($replicaPath);
        $this->assertTrue($snap['success']);
        $this->assertSame(0.0, $snap['rpoSeconds']);
        $this->assertNotEmpty($snap['checksum']);

        // 2. FELAKET SİMÜLASYONU: Birincil veri deposunun disk arızasıyla tamamen bozulması
        file_put_contents($storeFile, '{DISK_CRASH_CORRUPTED_BINARY_DATA');
        $this->assertNull(json_decode((string)file_get_contents($storeFile), true));

        // 3. Otomatik Failover Kurtarma (SHA-256 doğrulamalı)
        $recovery = $store->failoverRestoreFromReplica($replicaPath);
        $this->assertTrue($recovery['success']);
        $this->assertTrue($recovery['checksumVerified']);
        $this->assertSame(0, $recovery['rpoRecordsLost']);
        $this->assertLessThanOrEqual(30.0, $recovery['rtoMinutes'], 'Must ihlali: RTO > 30 dk');
        $this->assertLessThanOrEqual(5.0, $recovery['rtoMinutes'], 'Wish ihlali: RTO > 5 dk');

        // 4. Kurtarılan birincil veride son teklifin ve tüm kayıtların eksiksiz olduğunu doğrula
        $recoveredStore = new JsonDataStore($storeFile, $locksDir);
        $recoveredLot   = (new AuctionRepository($recoveredStore))->findLotById(1002);
        $this->assertSame($expectedBidCount, count($recoveredStore->getCollection('bids')));
        $this->assertEquals(45000.0, (float)$recoveredLot['mevcutEnYuksekTeklif']);

        fwrite(STDOUT, sprintf(
            "\n  [NFR8 Olcum] Felaket Simulasyonu: Birincil Disk Bozuldu -> Failover RTO: %.3f ms (%.6f dk) | RPO: 0 kayip | SHA-256 Dogrulandi (WISH)\n",
            $recovery['rtoMs'], $recovery['rtoMinutes']
        ));
    }

    #[TestDox('NFR9: Ödeme Güvenliği — PCI-DSS Tokenizasyon, Sıfır Düz Metin Kart & 3D Secure (Wish < 1.5sn)')]
    public function testNfr09PaymentSecurityPciDssAnd3DSecure(): void
    {
        [$store, $storeFile] = $this->createIsolatedStore('nfr9');
        $workflow = new PlatformWorkflowService($store, new AuctionSubject());

        $rawCardNumber = '5400112233448899';
        $saveRes = $workflow->saveUserCard(3, [
            'card_holder'  => 'Zeynep Demir',
            'card_number'  => $rawCardNumber,
            'expire_month' => '11',
            'expire_year'  => '2029',
            'card_type'    => 'KREDI_KARTI',
            'card_name'    => 'Garanti Bonus Test',
        ]);

        // 1. Tokenizasyon ve 3D Secure zorunluluğu kontrolü
        $this->assertTrue($saveRes['success']);
        $this->assertStringStartsWith('TOK_PCI_DSS_', $saveRes['tokenId']);
        $this->assertSame('5400 **** **** 8899', $saveRes['card']['maskeliKartNo']);
        $this->assertTrue($saveRes['card']['threeDSecureZorunlu']);

        // 2. PCI-DSS Seviye 1: Düz metin kart numarası veri deposunun HİÇBİR yerinde geçmemeli
        $rawDatastoreContent = (string)file_get_contents($storeFile);
        $this->assertStringNotContainsString($rawCardNumber, $rawDatastoreContent, 'KRİTİK İHLAL: Düz metin kart numarası veritabanında bulundu!');

        // 3. 3D Secure Negatif Test: Geçersiz/boş SMS kodu kesinlikle reddedilmeli
        $posStrategy = new ThreeDSecurePosStrategy();
        $bad3ds = $posStrategy->processPayment(3, 45000.0, [
            'maskedCard' => '5400 **** **** 8899',
            'smsCode'    => '123', // Eksik/geçersiz OTP
        ]);
        $this->assertFalse($bad3ds['success']);
        $this->assertFalse($bad3ds['threeDSecureValid']);

        // 4. 3D Secure Pozitif Test: Geçerli 6 haneli OTP + HSM anahtarı + onay süresi < 1.5 sn (Wish)
        $ok3ds = $posStrategy->processPayment(3, 45000.0, [
            'maskedCard' => '5400 **** **** 8899',
            'smsCode'    => '482910',
        ]);
        $this->assertTrue($ok3ds['success']);
        $this->assertTrue($ok3ds['pciDssCompliant']);
        $this->assertTrue($ok3ds['threeDSecureValid']);
        $this->assertStringStartsWith('HSM-AES256-', $ok3ds['hsmKeyRef']);
        $this->assertLessThan(1.5, (float)$ok3ds['approvalTimeSec'], 'Wish ihlali: 3D Secure onayı >= 1.5 sn');

        fwrite(STDOUT, sprintf(
            "\n  [NFR9 Olcum] Token: %s | Duz Metin Kart Sizintisi: 0 | 3DS Gecersiz Kod: RED | 3DS Onay: %.3f sn | HSM: %s (WISH)\n",
            $saveRes['tokenId'], $ok3ds['approvalTimeSec'], $ok3ds['hsmKeyRef']
        ));
    }

    #[TestDox('NFR10: Sahtecilik ve Bot Önleme (Must >= %99 Engel, < %0.5 FP, Plan CAPTCHA < 50ms)')]
    public function testNfr10AntiFraudBotPreventionAndLowFalsePositive(): void
    {
        $fraudObserver = new AuditAndFraudObserver();

        // 1. 1.000 kötü niyetli bot / spam / shill-bidding isteği simülasyonu
        $botTotal   = 1000;
        $botBlocked = 0;
        $botPatterns = [
            ['requestsPerSecond' => 25.0, 'clickIntervalMs' => 20.0, 'userAgent' => 'python-requests/2.31', 'mouseEntropy' => 0.02, 'isSellerSelfBid' => false],
            ['requestsPerSecond' => 18.0, 'clickIntervalMs' => 35.0, 'userAgent' => 'HeadlessChrome/120.0', 'mouseEntropy' => 0.05, 'isSellerSelfBid' => false],
            ['requestsPerSecond' => 40.0, 'clickIntervalMs' => 15.0, 'userAgent' => 'Mozilla/5.0',          'mouseEntropy' => 0.01, 'isSellerSelfBid' => false],
            ['requestsPerSecond' => 2.0,  'clickIntervalMs' => 900.0,'userAgent' => 'Mozilla/5.0',          'mouseEntropy' => 0.85, 'isSellerSelfBid' => true],
        ];

        for ($i = 0; $i < $botTotal; $i++) {
            $eval = $fraudObserver->evaluateRequestTraffic($botPatterns[$i % count($botPatterns)]);
            if ($eval['blocked']) {
                $botBlocked++;
            }
        }
        $botBlockRate = round(($botBlocked / $botTotal) * 100.0, 2);

        // 2. 1.000 gerçek koleksiyoner kullanıcı isteği simülasyonu (False Positive ölçümü)
        $humanTotal         = 1000;
        $humanFalsePositive = 0;
        for ($i = 0; $i < $humanTotal; $i++) {
            $eval = $fraudObserver->evaluateRequestTraffic([
                'requestsPerSecond' => 1.0 + ($i % 4),
                'clickIntervalMs'   => 350.0 + ($i % 900),
                'userAgent'         => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) Chrome/128.0',
                'mouseEntropy'      => 0.65 + (($i % 30) / 100.0),
                'isSellerSelfBid'   => false,
            ]);
            if ($eval['blocked']) {
                $humanFalsePositive++;
            }
        }
        $falsePositiveRate = round(($humanFalsePositive / $humanTotal) * 100.0, 3);

        // 3. Plan: Şüpheli ara trafikte dinamik CAPTCHA tetikleme süresi < 50 ms
        $suspiciousEval = $fraudObserver->evaluateRequestTraffic([
            'requestsPerSecond' => 12.0,
            'clickIntervalMs'   => 250.0,
            'userAgent'         => 'Mozilla/5.0 (Windows NT 10.0)',
            'mouseEntropy'      => 0.55,
            'isSellerSelfBid'   => false,
        ]);

        $this->assertGreaterThanOrEqual(99.0, $botBlockRate, 'Must ihlali: Bot engelleme < %99');
        $this->assertGreaterThanOrEqual(99.9, $botBlockRate, 'Plan ihlali: Bot engelleme < %99.9');
        $this->assertLessThan(0.5, $falsePositiveRate, 'Must ihlali: Yanlış pozitif >= %0.5');
        $this->assertTrue($suspiciousEval['captchaIssued'], 'Şüpheli trafikte dinamik CAPTCHA tetiklenmelidir.');
        $this->assertLessThan(50.0, $suspiciousEval['decisionMs'], 'Plan ihlali: Dinamik CAPTCHA tetiklemesi >= 50ms');

        fwrite(STDOUT, sprintf(
            "\n  [NFR10 Olcum] Bot Engelleme: %%%.1f (%d/%d) | Gercek Kullanici FP: %%%.2f (%d/%d) | Dinamik CAPTCHA: %.4f ms (WISH)\n",
            $botBlockRate, $botBlocked, $botTotal, $falsePositiveRate, $humanFalsePositive, $humanTotal, $suspiciousEval['decisionMs']
        ));
    }
}
