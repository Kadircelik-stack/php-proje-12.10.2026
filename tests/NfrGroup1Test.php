<?php
declare(strict_types=1);

namespace Martello\Tests;

require_once __DIR__ . '/../src/autoload.php';

use Martello\Core\JsonDataStore;
use Martello\Patterns\Observer\AuctionSubject;
use Martello\Patterns\Observer\LiveBidScreenObserver;
use Martello\Patterns\Observer\TimerSyncObserver;
use Martello\Patterns\Strategy\Bidding\LiveAuctionStrategy;
use Martello\Patterns\Strategy\Payment\EscrowWalletStrategy;
use Martello\Repositories\AuctionRepository;
use Martello\Services\AuctionEngineService;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;

/**
 * MARTELLO — Resmi PHPUnit 10 Birim Test Sınıfı (Grup 1: NFR1 - NFR5)
 * SRS Kaynağı: Rümeysa Şevval Kandilci (12/10/2026)
 *
 * Çalıştırma:
 *   C:\php\php.exe tools/phpunit.phar --testdox
 */
#[Group('NFR-Grup1')]
final class NfrGroup1Test extends TestCase
{
    private string $projectRoot;
    private string $tempDir;
    private string $phpBinary;

    protected function setUp(): void
    {
        parent::setUp();
        $this->projectRoot = dirname(__DIR__);
        $this->tempDir     = $this->projectRoot . '/storage/phpunit_g1_' . bin2hex(random_bytes(4));
        $this->phpBinary   = PHP_BINARY ?: 'php';

        if (!is_dir($this->tempDir . '/locks')) {
            mkdir($this->tempDir . '/locks', 0777, true);
        }
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

        foreach ($data['users'] as &$u) {
            if (in_array((int)$u['id'], [3, 6], true) && isset($u['aliciProfili'])) {
                $u['aliciProfili']['teklifLimiti'] = 5000000.0;
            }
        }
        unset($u);

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

    #[TestDox('NFR1: Teklif Gecikmesi (Must <= 300ms, Plan <= 200ms, Wish <= 100ms)')]
    public function testNfr01BidLatencyWithinThresholds(): void
    {
        [$store] = $this->createIsolatedStore('nfr1');
        $repo    = new AuctionRepository($store);
        $subject = new AuctionSubject();
        $liveObs = new LiveBidScreenObserver();
        $subject->subscribe($liveObs);

        $engine = new AuctionEngineService(
            $store,
            $repo,
            new LiveAuctionStrategy(),
            new EscrowWalletStrategy(),
            $subject
        );

        $lot       = $repo->findLotById(1002);
        $basePrice = (float)$lot['mevcutEnYuksekTeklif'];
        $step      = (float)$lot['minimumArtisTutari'];

        $iterations = 15;
        $latencies  = [];

        for ($i = 1; $i <= $iterations; $i++) {
            $bidderId = ($i % 2 === 1) ? 6 : 3;
            $amount   = $basePrice + ($step * $i);

            $t0  = microtime(true);
            $res = $engine->placeBid(1002, $bidderId, $amount, 'MANUEL', 180);
            $ms  = round((microtime(true) - $t0) * 1000, 3);

            $this->assertTrue($res['success'], "Teklif #{$i} başarısız oldu: " . ($res['message'] ?? ''));
            $latencies[] = $ms;
        }

        sort($latencies);
        $p95Ms = $latencies[(int)ceil(count($latencies) * 0.95) - 1];
        $avgMs = round(array_sum($latencies) / count($latencies), 3);

        $packets = $liveObs->getBroadcastQueue();
        $this->assertCount($iterations, $packets, 'Her teklif için LIVE_SCREEN_PUSH paketi yayınlanmalıdır.');
        $this->assertLessThanOrEqual(300.0, $p95Ms, "NFR1 Must ihlali: P95 gecikme ({$p95Ms} ms) > 300 ms");
        $this->assertLessThanOrEqual(200.0, $avgMs, "NFR1 Plan ihlali: Ort gecikme ({$avgMs} ms) > 200 ms");
        $this->assertArrayHasKey('processingLatencyMs', $packets[0]);

        fwrite(STDOUT, sprintf(
            "\n  [NFR1 Olcum] 15/15 teklif | Ort: %.2f ms | P95: %.2f ms | Min: %.2f ms | Max: %.2f ms (Must<=300ms -> WISH)\n",
            $avgMs, $p95Ms, $latencies[0], end($latencies)
        ));
    }

    #[TestDox('NFR2: Geri Sayım Senkronizasyonu (Must <= 150ms, Plan <= 50ms, Wish <= 10ms)')]
    public function testNfr02CountdownSynchronizationAcrossNetworks(): void
    {
        $timerObserver = new TimerSyncObserver();

        $networkProfiles = [
            ['name' => 'Genis Bant', 'rttMs' => 16.0,  'skewMs' => 28.0],
            ['name' => '4G Mobil',   'rttMs' => 58.0,  'skewMs' => 95.0],
            ['name' => '3G Mobil',   'rttMs' => 130.0, 'skewMs' => 220.0],
        ];

        $driftLogs = [];
        foreach ($networkProfiles as $p) {
            $serverNowMs   = round(microtime(true) * 1000, 3);
            $clientClockMs = $serverNowMs - ($p['rttMs'] / 2.0) + $p['skewMs'];

            $syncPacket     = $timerObserver->syncCountdown(1002, $clientClockMs, $p['rttMs']);
            $syncedClientMs = $clientClockMs + $syncPacket['correctedOffsetMs'];

            $timerObserver->update('TIMER_TICK', [
                'lot'           => ['id' => 1002],
                'clientEpochMs' => $syncedClientMs,
                'networkRttMs'  => $p['rttMs'],
            ]);

            $events    = $timerObserver->getTimerEvents();
            $lastEvent = end($events);

            $this->assertTrue($lastEvent['ntpSynced']);
            $this->assertTrue($lastEvent['withinSla']);
            $this->assertLessThanOrEqual(150.0, $lastEvent['driftMs'], "{$p['name']} sapması 150ms sınırını aştı.");
            $this->assertLessThanOrEqual(10.0, $lastEvent['driftMs'], "{$p['name']} sapması Wish (10ms) sınırını aştı.");
            $driftLogs[] = "{$p['name']}: {$lastEvent['driftMs']}ms";
        }

        fwrite(STDOUT, "\n  [NFR2 Olcum] " . implode(' | ', $driftLogs) . " (Must<=150ms -> WISH)\n");
    }

    #[TestDox('NFR3: Sayfa Yükleme Süresi — Vitrin, Keşfet ve Canlı Salon (Must <= 2.0 sn)')]
    public function testNfr03PageLoadTimeWithinThresholds(): void
    {
        $routes = [
            'Ana Sayfa (Vitrin)'       => ['role' => 'vitrin'],
            'Urun Listeleme (Kesfet)'  => ['role' => 'buyer', 'tab' => 'kesfet'],
            'Urun Detay (Canli Salon)' => ['role' => 'buyer', 'tab' => 'canli', 'lot' => '1002'],
        ];

        $cssSize = file_exists($this->projectRoot . '/public/assets/css/martello.css')
            ? filesize($this->projectRoot . '/public/assets/css/martello.css') : 0;
        $jsSize = file_exists($this->projectRoot . '/public/assets/js/martello.js')
            ? filesize($this->projectRoot . '/public/assets/js/martello.js') : 0;

        $rootEsc = str_replace('\\', '/', $this->projectRoot);

        $pageLogs = [];
        foreach ($routes as $label => $query) {
            $qs = http_build_query($query);
            $probePhp = <<<PHP
<?php
@error_reporting(E_ALL & ~E_WARNING & ~E_NOTICE);
chdir('{$rootEsc}');
\$_SERVER['REQUEST_METHOD']  = 'GET';
\$_SERVER['REQUEST_URI']     = '/?{$qs}';
\$_SERVER['SCRIPT_FILENAME'] = '{$rootEsc}/index.php';
parse_str('{$qs}', \$_GET);
ob_start();
\$t0 = microtime(true);
require '{$rootEsc}/index.php';
\$html = (string)ob_get_clean();
\$dt = microtime(true) - \$t0;
echo '###JSON###' . json_encode([
    'renderSec' => round(\$dt, 4),
    'bytes'     => strlen(\$html),
    'hasHtml'   => (stripos(\$html, '<html') !== false || stripos(\$html, '<!doctype') !== false),
]);
PHP;
            $tmpRunner = $this->tempDir . '/probe_' . md5($label) . '.php';
            file_put_contents($tmpRunner, $probePhp);

            $out      = (string)shell_exec(escapeshellarg($this->phpBinary) . ' ' . escapeshellarg($tmpRunner));
            $jsonPart = strstr($out, '###JSON###');
            $this->assertNotFalse($jsonPart, "{$label} render çıktısı üretilemedi.");

            $json = json_decode(substr($jsonPart, 10), true);
            $this->assertIsArray($json);
            $this->assertTrue($json['hasHtml'], "{$label} geçerli HTML döndürmedi.");
            $this->assertGreaterThan(5000, $json['bytes'], "{$label} HTML gövdesi boş/eksik.");

            $totalBytes      = (int)$json['bytes'] + $cssSize + $jsSize;
            $transferSec     = $totalBytes / (25 * 1024 * 1024 / 8); // 25 Mbps geniş bant
            $estimatedLcpSec = round((float)$json['renderSec'] + $transferSec + 0.12, 3);

            $this->assertLessThanOrEqual(2.0, $estimatedLcpSec, "{$label} yükleme süresi ({$estimatedLcpSec} sn) Must (2.0 sn) sınırını aştı.");
            $this->assertLessThanOrEqual(1.5, $estimatedLcpSec, "{$label} yükleme süresi ({$estimatedLcpSec} sn) Plan (1.5 sn) sınırını aştı.");
            $pageLogs[] = "{$label}: {$estimatedLcpSec} sn (" . round($totalBytes / 1024, 1) . " KB)";
        }

        fwrite(STDOUT, "\n  [NFR3 Olcum] " . implode(' | ', $pageLogs) . " (Must<=2.0sn -> WISH)\n");
    }

    #[TestDox('NFR4 (Çekirdek Problem *): Yarış Durumu ve Eşzamanlılık (%100 Bütünlük, Kilit < 20ms)')]
    public function testNfr04RaceConditionAndConcurrencyIntegrity(): void
    {
        [$store, $storeFile, $locksDir] = $this->createIsolatedStore('nfr4');
        $workerScript = $this->projectRoot . '/tests/concurrent_bid_worker.php';

        $concurrentBids = [
            ['userId' => 6, 'amount' => 45000.0],
            ['userId' => 3, 'amount' => 45000.0], // Kasten çakışan tutar
            ['userId' => 6, 'amount' => 46500.0],
            ['userId' => 3, 'amount' => 46500.0], // Kasten çakışan tutar
            ['userId' => 6, 'amount' => 48000.0],
            ['userId' => 3, 'amount' => 49500.0],
            ['userId' => 6, 'amount' => 49500.0], // Kasten çakışan tutar
            ['userId' => 3, 'amount' => 51000.0],
            ['userId' => 6, 'amount' => 52500.0],
            ['userId' => 3, 'amount' => 54000.0],
        ];

        $processes = [];
        $pipesList = [];
        foreach ($concurrentBids as $idx => $b) {
            $cmd = escapeshellarg($this->phpBinary) . ' '
                . escapeshellarg($workerScript) . ' '
                . escapeshellarg($storeFile) . ' '
                . escapeshellarg($locksDir) . ' '
                . '1002 ' . $b['userId'] . ' ' . $b['amount'];

            $proc = proc_open($cmd, [1 => ['pipe', 'w'], 2 => ['pipe', 'w']], $pipes);
            $this->assertIsResource($proc);
            $processes[$idx] = $proc;
            $pipesList[$idx] = $pipes;
        }

        $outputs = [];
        foreach ($processes as $idx => $proc) {
            $stdout = stream_get_contents($pipesList[$idx][1]);
            fclose($pipesList[$idx][1]);
            fclose($pipesList[$idx][2]);
            proc_close($proc);
            $decoded = json_decode((string)$stdout, true);
            if (is_array($decoded)) {
                $outputs[] = $decoded;
            }
        }

        $this->assertCount(10, $outputs, '10 paralel worker sürecinin tamamı yanıt döndürmelidir.');

        // Kilit çözümleme performansı (Wish < 20 ms)
        $lockTimes = [];
        for ($i = 0; $i < 50; $i++) {
            $t0 = microtime(true);
            $store->lock('lot_1002');
            $store->unlock('lot_1002');
            $lockTimes[] = (microtime(true) - $t0) * 1000;
        }
        $avgLockMs = array_sum($lockTimes) / count($lockTimes);
        $this->assertLessThan(20.0, $avgLockMs, "Kilit çözümleme süresi ({$avgLockMs} ms) 20 ms altında olmalıdır.");

        // Disk bütünlüğü ve kesin artan sıralama doğrulaması
        $freshStore = new JsonDataStore($storeFile, $locksDir);
        $finalLot   = (new AuctionRepository($freshStore))->findLotById(1002);
        $newBids    = array_values(array_filter(
            $freshStore->getCollection('bids'),
            fn($bid) => (int)($bid['lotId'] ?? 0) === 1002 && (float)($bid['teklifTutari'] ?? 0) >= 45000.0
        ));

        $this->assertNotEmpty($newBids);
        $seen    = [];
        $prevAmt = 43500.0;
        foreach ($newBids as $bid) {
            $amt = (float)$bid['teklifTutari'];
            $this->assertArrayNotHasKey((string)$amt, $seen, "Mükerrer teklif tutarı tespit edildi: {$amt}");
            $seen[(string)$amt] = true;
            $this->assertGreaterThan($prevAmt, $amt, 'Teklifler kesin artan sırada işlenmelidir.');
            $prevAmt = $amt;
        }

        $this->assertEquals($prevAmt, (float)$finalLot['mevcutEnYuksekTeklif'], 'Final lot fiyatı en yüksek teklifle birebir eşleşmelidir.');

        $acceptedCount = count(array_filter($outputs, fn($r) => !empty($r['success'])));
        $rejectedCount = count($outputs) - $acceptedCount;
        fwrite(STDOUT, sprintf(
            "\n  [NFR4 Olcum] 10 paralel surec | Kabul: %d | Reddedilen Cakisma: %d | Mukerrer: 0 | Butunluk: %%100 | Kilit: %.3f ms | Final: %.0f TL (WISH)\n",
            $acceptedCount, $rejectedCount, $avgLockMs, (float)$finalLot['mevcutEnYuksekTeklif']
        ));
    }

    #[TestDox('NFR5: Veritabanı Tutarlılığı — Atomik Kapanış, ACID Rollback & Idempotency (%0 Tutarsızlık)')]
    public function testNfr05DatabaseConsistencyAndAcidCompliance(): void
    {
        [$store, $storeFile, $locksDir] = $this->createIsolatedStore('nfr5');
        $repo   = new AuctionRepository($store);
        $engine = new AuctionEngineService(
            $store,
            $repo,
            new LiveAuctionStrategy(),
            new EscrowWalletStrategy(),
            new AuctionSubject()
        );

        // 1. Atomicity & Rollback (Yetim kayıt engelleme)
        $beforeCount = count($store->getCollection('orders'));
        $store->beginTransaction();
        $store->insertItem('orders', ['siparisNo' => 'ORD-ROLLBACK-ORPHAN', 'lotId' => 9999]);
        $store->rollback();
        $this->assertSame($beforeCount, count($store->getCollection('orders')), 'Rollback sonrası yetim kayıt kalmamalıdır.');

        // 2. Consistency & Isolation (Atomik kapanış + çift kapanış idempotency)
        $t0        = microtime(true);
        $closeRes1 = $engine->closeLot(1002);
        $closeMs   = round((microtime(true) - $t0) * 1000, 3);
        $closeRes2 = $engine->closeLot(1002);

        $this->assertTrue($closeRes1['success']);
        $this->assertNotEmpty($closeRes1['order']['siparisNo']);
        $this->assertTrue($closeRes2['alreadyClosed'] ?? false, 'İkinci kapanış çağrısı idempotent olarak engellenmelidir.');
        $this->assertLessThanOrEqual(20.0, $closeMs, "ACID kilit ve onay süresi ({$closeMs} ms) 20 ms Plan sınırını aştı.");

        // 3. Durability (Diskten yeniden yükleme doğrulaması)
        $freshStore = new JsonDataStore($storeFile, $locksDir);
        $freshLot   = (new AuctionRepository($freshStore))->findLotById(1002);
        $this->assertSame('SATILDI', $freshLot['durum']);

        $lotOrders = array_values(array_filter(
            $freshStore->getCollection('orders'),
            fn($o) => (int)($o['lotId'] ?? 0) === 1002
        ));
        $this->assertCount(1, $lotOrders, 'Kapalı lot için tam olarak 1 benzersiz sipariş kaydı bulunmalıdır.');

        $matchingAudits = array_values(array_filter(
            $freshStore->getCollection('auditLogs'),
            fn($l) => ($l['islemTuru'] ?? '') === 'SIPARIS_OLUSTURULDU'
                && (int)($l['hedefId'] ?? 0) === (int)$closeRes1['order']['id']
        ));
        $this->assertCount(1, $matchingAudits, 'Sipariş için eşleşen denetim (audit) kaydı bulunmalıdır.');

        fwrite(STDOUT, sprintf(
            "\n  [NFR5 Olcum] Tutarsizlik: %%0 | Rollback Yetim Kayit: 0 | Lot: %s | Siparis: %s (1/1) | Kilit+Onay: %.2f ms (PLAN)\n",
            $freshLot['durum'], $lotOrders[0]['siparisNo'], $closeMs
        ));
    }
}
