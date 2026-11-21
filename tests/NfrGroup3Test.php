<?php
declare(strict_types=1);

namespace Martello\Tests;

require_once __DIR__ . '/../src/autoload.php';

use Martello\Core\JsonDataStore;
use Martello\Patterns\Observer\AuditAndFraudObserver;
use Martello\Patterns\Strategy\Bidding\VipAuctionStrategy;
use Martello\Services\ResilienceAndSecurityService;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;

/**
 * MARTELLO — Resmi PHPUnit 10 Birim Test Sınıfı (Grup 3: NFR11 - NFR15)
 * SRS Kaynağı: Rümeysa Şevval Kandilci (12/10/2026)
 *
 * Doğrudan test edilen gerçek ürün sınıfları:
 *  - NFR11: src/Patterns/Observer/AuditAndFraudObserver.php (update, syncToWormFile, verifyChainIntegrity)
 *  - NFR12: src/Services/ResilienceAndSecurityService.php (encryptPersonalField, processKvkkErasureRequest)
 *  - NFR13: src/Services/ResilienceAndSecurityService.php (verifyContractCompliance, recordContractAcceptance)
 *  - NFR14: src/Patterns/Strategy/Bidding/VipAuctionStrategy.php (authorizeVipApiRequest, invalidateTierCache)
 *  - NFR15: src/Patterns/Observer/AuditAndFraudObserver.php (evaluateVipWafRequest)
 */
#[Group('NFR-Grup3')]
final class NfrGroup3Test extends TestCase
{
    private string $projectRoot;
    private string $tempDir;

    protected function setUp(): void
    {
        parent::setUp();
        $this->projectRoot = dirname(__DIR__);
        $this->tempDir     = $this->projectRoot . '/storage/phpunit_g3_' . bin2hex(random_bytes(4));
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
        copy($this->projectRoot . '/martello_datastore.json', $storeFile);
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

    #[TestDox('NFR11: Denetim İzi, İzole WORM Senkronizasyonu ve SHA-256 Değiştirilemezlik (Plan <= 100ms, Wish < 10ms)')]
    public function testNfr11AuditTrailWormAndHashChainIntegrity(): void
    {
        $audit = new AuditAndFraudObserver();

        $criticalEvents = [
            ['event' => 'USER_LOGIN',     'payload' => ['userId' => 3, 'ip' => '88.241.110.54', 'lot' => ['id' => 0]]],
            ['event' => 'WALLET_DEPOSIT', 'payload' => ['userId' => 3, 'ip' => '88.241.110.54', 'bid' => ['aliciId' => 3, 'teklifTutari' => 50000]]],
            ['event' => 'BID_PLACED',     'payload' => ['ip' => '88.241.110.54', 'lot' => ['id' => 1002, 'saticiId' => 2], 'bid' => ['aliciId' => 3, 'teklifTutari' => 45000]]],
            ['event' => 'BID_PLACED',     'payload' => ['ip' => '176.88.42.19',  'lot' => ['id' => 1002, 'saticiId' => 2], 'bid' => ['aliciId' => 2, 'teklifTutari' => 46500]]], // Shill bidding!
            ['event' => 'ADMIN_ACTION',   'payload' => ['userId' => 1, 'ip' => '10.0.0.1', 'lot' => ['id' => 1002]]],
        ];

        foreach ($criticalEvents as $ev) {
            $audit->update($ev['event'], $ev['payload']);
        }

        $entries = $audit->getLoggedEntries();
        $this->assertCount(5, $entries, 'Kritik işlemlerin %100ü denetim izine alınmalıdır.');

        foreach ($entries as $e) {
            $this->assertNotEmpty($e['ipAdresi']);
            $this->assertGreaterThan(0, $e['timestampMs']);
            $this->assertSame(64, strlen($e['currentHash']));
            $this->assertLessThan(10.0, $e['anomalyAlertMs'], 'Wish ihlali: Anomali bildirimi >= 10ms');
        }
        $this->assertSame('ENGEL_SHILL_BIDDING_DETECTED', $entries[3]['shillBiddingDurumu']);

        // Plan: İzole WORM dosyasına senkronizasyon <= 100 ms
        $wormPath = $this->tempDir . '/worm_isolated/audit_worm.jsonl';
        $wormSync = $audit->syncToWormFile($wormPath);
        $this->assertTrue($wormSync['success']);
        $this->assertSame(5, $wormSync['syncedCount']);
        $this->assertLessThanOrEqual(100.0, $wormSync['syncMs']);

        // Değiştirilemezlik doğrulaması + Adli Bilişim Kurcalama (Tamper) Testi
        $cleanCheck = $audit->verifyChainIntegrity();
        $this->assertTrue($cleanCheck['intact']);

        $tamperedEntries = $entries;
        $tamperedEntries[2]['hedefVarlik'] = 'Lot:1002 / Tutar:999999'; // Kasten kurcalama
        $tamperCheck = $audit->verifyChainIntegrity($tamperedEntries);
        $this->assertFalse($tamperCheck['intact']);
        $this->assertSame(2, $tamperCheck['tamperedIndex']);

        fwrite(STDOUT, sprintf(
            "\n  [NFR11 Olcum] Kapsam: %%100 (5/5) | WORM Senkron: %.3f ms | Anomali Uyari: %.4f ms | Kurcalama Tespiti: BASARILI (WISH)\n",
            $wormSync['syncMs'], $entries[3]['anomalyAlertMs']
        ));
    }

    #[TestDox('NFR12: KVKK / GDPR AES-256-GCM Sütun Şifreleme ve Unutulma Hakkı Anonimleştirme (Plan <= 24sa)')]
    public function testNfr12KvkkGdprEncryptionAndRightToErasure(): void
    {
        [$store, $storeFile, $locksDir] = $this->createIsolatedStore('nfr12');
        $sec = new ResilienceAndSecurityService($store, $storeFile, $locksDir);

        // 1. Plan: Sütun düzeyinde AES-256-GCM şifreleme / şifre çözme
        $secretKey = 'MARTELLO_HSM_MASTER_KEY_2026';
        $piiPlain  = 'TC:12345678901 | zeynep@koleksiyon.com';
        $encrypted = $sec->encryptPersonalField($piiPlain, $secretKey);

        $this->assertSame('AES-256-GCM', $encrypted['algorithm']);
        $this->assertStringNotContainsString('12345678901', $encrypted['ciphertext']);
        $this->assertSame($piiPlain, $sec->decryptPersonalField($encrypted, $secretKey));

        // 2. Must/Plan: KVKK Unutulma Hakkı (Right to Erasure) otomatik silme & anonimleştirme
        $erasure = $sec->processKvkkErasureRequest(3);
        $this->assertTrue($erasure['success']);
        $this->assertLessThanOrEqual(24.0, $erasure['completedHrs'], 'Plan ihlali: KVKK silme > 24 saat');

        $freshStore = new JsonDataStore($storeFile, $locksDir);
        $erasedUser = $freshStore->getItem('users', 'id', 3);
        $this->assertSame('Anonim', $erasedUser['ad']);
        $this->assertSame('SILINDI', $erasedUser['telefon']);
        $this->assertSame('KVKK_ANONIMLESTIRILDI', $erasedUser['durum']);
        $this->assertEmpty($erasedUser['aliciProfili']['kayitliKartTokenlari']);
        $this->assertFalse($erasedUser['kvkkRizaDurumu']['aktifMi']);

        $rawAfter = (string)file_get_contents($storeFile);
        $this->assertStringNotContainsString('zeynep@koleksiyon.com', $rawAfter);
        $this->assertStringNotContainsString('+905443334455', $rawAfter);

        fwrite(STDOUT, sprintf(
            "\n  [NFR12 Olcum] Sifreleme: AES-256-GCM OK | KVKK Unutulma Hakki: %.3f ms (<< 24sa) | Kisisel Veri Kalintisi: 0 (WISH)\n",
            $erasure['completedMs']
        ));
    }

    #[TestDox('NFR13: Sözleşme Sürüm Zorunluluğu ve Hash Zincirli İnkar Edilemezlik (Plan <= 100ms)')]
    public function testNfr13ContractComplianceVersionEnforcementAndNonRepudiation(): void
    {
        [$store, $storeFile, $locksDir] = $this->createIsolatedStore('nfr13');
        $sec = new ResilienceAndSecurityService($store, $storeFile, $locksDir);

        // 1. Mevcut güncel sürüm (v2026.4) için kullanıcı #3 uyumlu olmalı
        $check1 = $sec->verifyContractCompliance(3);
        $this->assertTrue($check1['compliant']);
        $this->assertFalse($check1['requiresReAcceptance']);
        $this->assertLessThanOrEqual(100.0, $check1['verifyMs']);

        // 2. Sözleşme sürümü güncellendiğinde yeni onay zorunlu kılınmalı (Plan)
        $all = $store->getAllData();
        $all['systemParameters']['activeContractVersion'] = 'v2026.5-KVKK-MESAFELI-SATIS';
        $store->saveAllData($all);

        $check2 = $sec->verifyContractCompliance(3);
        $this->assertFalse($check2['compliant']);
        $this->assertTrue($check2['requiresReAcceptance']);
        $this->assertLessThanOrEqual(100.0, $check2['verifyMs']);

        // 3. Wish: Kriptografik SHA-256 hash zinciriyle inkar edilemez (non-repudiation) yeni onay kaydı
        $acc = $sec->recordContractAcceptance(3, 'v2026.5-KVKK-MESAFELI-SATIS', '88.241.110.54');
        $this->assertTrue($acc['success']);
        $this->assertSame(64, strlen($acc['record']['nonRepudiationHash']));
        $this->assertSame('88.241.110.54', $acc['record']['ipAdresi']);

        $check3 = $sec->verifyContractCompliance(3);
        $this->assertTrue($check3['compliant']);
        $this->assertLessThanOrEqual(100.0, $check3['verifyMs']);

        fwrite(STDOUT, sprintf(
            "\n  [NFR13 Olcum] Surum Zorunlulugu: AKTIF | Dogrulama: %.3f ms (Plan<=100ms) | SHA-256 Non-Repudiation: %s... (WISH)\n",
            $check2['verifyMs'], substr($acc['record']['nonRepudiationHash'], 0, 12)
        ));
    }

    #[TestDox('NFR14: VIP Müzayede Yetki ve Tier Kontrolü — Önbellekli %100 Doğruluk (Must <= 20ms, Wish < 3ms)')]
    public function testNfr14VipTierAccessControlAndInMemoryCache(): void
    {
        [$store] = $this->createIsolatedStore('nfr14');
        $vipStrategy = new VipAuctionStrategy();

        $goldUser = $store->getItem('users', 'id', 3); // GOLD + KYC Onaylı + 3 kazanılan lot

        $standardUser = [
            'id'           => 91,
            'kycDogrulama' => ['dogrulandiMi' => true],
            'aliciProfili' => ['vipSeviyesi' => 'STANDARD', 'toplamKazanilanLot' => 0],
        ];
        $unverifiedGold = [
            'id'           => 92,
            'kycDogrulama' => ['dogrulandiMi' => false],
            'aliciProfili' => ['vipSeviyesi' => 'GOLD', 'toplamKazanilanLot' => 5],
        ];
        $restrictedVip = [
            'id'           => 93,
            'kycDogrulama' => ['dogrulandiMi' => true],
            'aliciProfili' => ['vipSeviyesi' => 'PLATINUM', 'toplamKazanilanLot' => 10],
            'kisitlamalar' => ['vipEngeli' => true],
        ];

        // %100 yetki doğruluğu kontrolü (4 farklı profil)
        $rGold = $vipStrategy->authorizeVipApiRequest($goldUser);
        $rStd  = $vipStrategy->authorizeVipApiRequest($standardUser);
        $rNoKyc = $vipStrategy->authorizeVipApiRequest($unverifiedGold);
        $rRestr = $vipStrategy->authorizeVipApiRequest($restrictedVip);

        $this->assertTrue($rGold['authorized']);
        $this->assertSame(200, $rGold['httpStatus']);
        $this->assertFalse($rStd['authorized']);
        $this->assertSame(403, $rStd['httpStatus']);
        $this->assertFalse($rNoKyc['authorized']);
        $this->assertFalse($rRestr['authorized']);

        // Plan/Wish: Bellek içi önbellek isabeti (cache hit) ve < 3 ms sorgu süresi
        $rStdCached = $vipStrategy->authorizeVipApiRequest($standardUser);
        $this->assertTrue($rStdCached['cacheHit']);
        $this->assertLessThanOrEqual(20.0, $rStd['latencyMs']);
        $this->assertLessThan(3.0, $rStdCached['latencyMs'], 'Wish ihlali: Önbellekli VIP tier sorgusu >= 3ms');

        // Wish: Dinamik tier yükseltmesi anında sisteme yansımalı
        $standardUser['aliciProfili']['vipSeviyesi'] = 'PLATINUM';
        $standardUser['aliciProfili']['toplamKazanilanLot'] = 2;
        $vipStrategy->invalidateTierCache(91);
        $rUpgraded = $vipStrategy->authorizeVipApiRequest($standardUser);
        $this->assertTrue($rUpgraded['authorized']);

        fwrite(STDOUT, sprintf(
            "\n  [NFR14 Olcum] Dogruluk: %%100 (4/4 profil) | Ilk Sorgu: %.4f ms | In-Memory Cache: %.4f ms | Dinamik Yukseltme: OK (WISH)\n",
            $rStd['latencyMs'], $rStdCached['latencyMs']
        ));
    }

    #[TestDox('NFR15: VIP WAF, DDoS ve Bot Koruması (Must >= %99.9 Engel, Plan < 5ms, Wish %100 & %0 FP)')]
    public function testNfr15VipWafDdosAndBotProtection(): void
    {
        $waf = new AuditAndFraudObserver();

        // 1. 2.000 VIP bot / DDoS / SQLi-XSS saldırı isteği
        $attackTotal   = 2000;
        $attackBlocked = 0;
        $filterTimes   = [];

        $attackVectors = [
            ['requestsPerSecond' => 15.0, 'burstCountLastSec' => 30, 'userAgent' => 'Mozilla/5.0', 'validTlsFingerprint' => true],
            ['requestsPerSecond' => 2.0,  'burstCountLastSec' => 2,  'userAgent' => 'HeadlessChrome/122', 'validTlsFingerprint' => false],
            ['requestsPerSecond' => 1.0,  'burstCountLastSec' => 1,  'userAgent' => 'Mozilla/5.0', 'payload' => "1' OR 1=1 --"],
            ['requestsPerSecond' => 2.0,  'burstCountLastSec' => 2,  'userAgent' => 'Mozilla/5.0', 'clickIntervalMs' => 25.0, 'mouseEntropy' => 0.04],
            ['requestsPerSecond' => 1.0,  'burstCountLastSec' => 1,  'userAgent' => 'sqlmap/1.8',  'payload' => '<script>alert(1)</script>'],
        ];

        for ($i = 0; $i < $attackTotal; $i++) {
            $res = $waf->evaluateVipWafRequest($attackVectors[$i % count($attackVectors)]);
            if ($res['blocked']) {
                $attackBlocked++;
            }
            $filterTimes[] = (float)$res['filterMs'];
        }

        // 2. 1.000 gerçek VIP koleksiyoner isteği (False Positive = 0 hedefi)
        $vipHumanTotal = 1000;
        $vipHumanFp    = 0;
        for ($i = 0; $i < $vipHumanTotal; $i++) {
            $res = $waf->evaluateVipWafRequest([
                'requestsPerSecond'   => 1.0 + ($i % 3),
                'burstCountLastSec'   => 1 + ($i % 4),
                'userAgent'           => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 Chrome/128.0',
                'payload'             => 'lot=1005&amount=1850000',
                'clickIntervalMs'     => 400.0 + ($i % 700),
                'mouseEntropy'        => 0.72 + (($i % 25) / 100.0),
                'validTlsFingerprint' => true,
            ]);
            if ($res['blocked']) {
                $vipHumanFp++;
            }
            $filterTimes[] = (float)$res['filterMs'];
        }

        $blockRate = round(($attackBlocked / $attackTotal) * 100.0, 2);
        $fpRate    = round(($vipHumanFp / $vipHumanTotal) * 100.0, 3);
        $maxMs     = max($filterTimes);
        $avgMs     = round(array_sum($filterTimes) / count($filterTimes), 4);

        $this->assertGreaterThanOrEqual(99.9, $blockRate, 'Must ihlali: VIP bot/DDoS engelleme < %99.9');
        $this->assertGreaterThanOrEqual(99.95, $blockRate, 'Plan ihlali: VIP bot/DDoS engelleme < %99.95');
        $this->assertSame(0.0, $fpRate, 'Wish ihlali: VIP gerçek kullanıcı yanlış pozitif > %0');
        $this->assertLessThan(5.0, $maxMs, "Plan ihlali: VIP WAF filtreleme süresi ({$maxMs} ms) >= 5 ms");

        fwrite(STDOUT, sprintf(
            "\n  [NFR15 Olcum] VIP Bot/DDoS Engel: %%%.2f (%d/%d) | VIP FP: %%%.2f | WAF Filtreleme Ort: %.4f ms (Maks: %.4f ms < 5ms) (WISH)\n",
            $blockRate, $attackBlocked, $attackTotal, $fpRate, $avgMs, $maxMs
        ));
    }
}
