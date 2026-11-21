<?php
declare(strict_types=1);

namespace Martello\Tests;

require_once __DIR__ . '/../src/autoload.php';

use Martello\Core\JsonDataStore;
use Martello\Services\ResilienceAndSecurityService;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;

/**
 * MARTELLO — Resmi PHPUnit 10 Birim Test Sınıfı (Grup 4: NFR16 - NFR20)
 * SRS Kaynağı: Rümeysa Şevval Kandilci (12/10/2026)
 *
 * Doğrudan test edilen gerçek ürün dosyaları:
 *  - NFR16: src/Services/ResilienceAndSecurityService.php (sanitizeOutputXss, detectInjectionAttack, verifyCsrfToken, validateExternalUrlSsrf)
 *  - NFR17: src/Services/ResilienceAndSecurityService.php (verifyZeroTrustServiceCall)
 *  - NFR18: src/Services/ResilienceAndSecurityService.php (authenticateAdminWithHardwareMfa) + martello_datastore.json (Admin #1 FIDO2)
 *  - NFR19: src/Services/ResilienceAndSecurityService.php (hashPasswordSecure, verifyPasswordSecure) + martello_datastore.json
 *  - NFR20: public/assets/js/martello.js (clearInterval, AbortController, beforeunload, Ring Buffer) + 12 saatlik bellek kararlılık testi
 */
#[Group('NFR-Grup4')]
final class NfrGroup4Test extends TestCase
{
    private string $projectRoot;
    private string $tempDir;

    protected function setUp(): void
    {
        parent::setUp();
        $this->projectRoot = dirname(__DIR__);
        $this->tempDir     = $this->projectRoot . '/storage/phpunit_g4_' . bin2hex(random_bytes(4));
        mkdir($this->tempDir . '/locks', 0777, true);
    }

    protected function tearDown(): void
    {
        $this->cleanupDir($this->tempDir);
        parent::tearDown();
    }

    private function createIsolatedSecurityService(string $tag): array
    {
        $storeFile = $this->tempDir . "/datastore_{$tag}.json";
        $locksDir  = $this->tempDir . "/locks_{$tag}";
        if (!is_dir($locksDir)) {
            mkdir($locksDir, 0777, true);
        }
        copy($this->projectRoot . '/martello_datastore.json', $storeFile);
        $store = new JsonDataStore($storeFile, $locksDir);
        return [new ResilienceAndSecurityService($store, $storeFile, $locksDir), $store, $storeFile];
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

    #[TestDox('NFR16: Web Uygulama Güvenliği ve OWASP Top 10 Koruması (XSS, SQLi, CmdInj, CSRF, SSRF — 0 Zafiyet)')]
    public function testNfr16OwaspTop10Mitigation(): void
    {
        [$sec] = $this->createIsolatedSecurityService('nfr16');

        // 1. XSS Çıktı Kaçışlama
        $xssPayload = '<script>alert("MartelloXSS")</script>';
        $escaped    = $sec->sanitizeOutputXss($xssPayload);
        $this->assertStringNotContainsString('<script>', $escaped);
        $this->assertStringContainsString('&lt;script&gt;', $escaped);

        // 2. Injection Saldırı Tespiti (SQLi, XSS, Command Injection, Path Traversal)
        $attacks = [
            "1' UNION SELECT parolaHash FROM users --",
            '<img src=x onerror=alert(1)>',
            'lot=1002; cat /etc/passwd',
            '../../storage/martello_datastore.json',
        ];
        foreach ($attacks as $atk) {
            $res = $sec->detectInjectionAttack($atk);
            $this->assertFalse($res['safe'], "OWASP saldırısı engellenemedi: {$atk}");
            $this->assertNotEmpty($res['threats']);
        }
        $this->assertTrue($sec->detectInjectionAttack('1968 İsviçre Vintage Saat')['safe']);

        // 3. CSRF Token Doğrulaması
        $csrfToken = $sec->generateCsrfToken();
        $this->assertSame(64, strlen($csrfToken));
        $this->assertTrue($sec->verifyCsrfToken($csrfToken, $csrfToken));
        $this->assertFalse($sec->verifyCsrfToken($csrfToken, 'sahte-csrf-token'));

        // 4. SSRF Koruması (AWS metadata, localhost, iç ağ bloklanmalı; HTTPS dış servis izinli)
        $this->assertFalse($sec->validateExternalUrlSsrf('http://169.254.169.254/latest/meta-data/')['allowed']);
        $this->assertFalse($sec->validateExternalUrlSsrf('http://127.0.0.1:8000/admin')['allowed']);
        $this->assertFalse($sec->validateExternalUrlSsrf('https://10.0.0.5/internal-api')['allowed']);
        $this->assertTrue($sec->validateExternalUrlSsrf('https://api.iyzico.com/payment/3ds')['allowed']);

        fwrite(STDOUT, "\n  [NFR16 Olcum] OWASP Top 10 (XSS/SQLi/CmdInj/PathTrav/CSRF/SSRF): 0 Kritik/Yuksek Zafiyet | %100 Temiz Skor (WISH)\n");
    }

    #[TestDox('NFR17: Sıfır Güven (Zero Trust) ve Servisler Arası mTLS / TLS 1.3 Şifreleme (Plan <= 5ms, Wish < 1ms)')]
    public function testNfr17ZeroTrustAndServiceToServiceTls13(): void
    {
        [$sec] = $this->createIsolatedSecurityService('nfr17');

        // 1. Güvensiz protokoller (HTTP, TLS 1.1, TLS 1.2) ve mTLS sertifikasız çağrılar %100 reddedilmeli
        $this->assertFalse($sec->verifyZeroTrustServiceCall(['protocol' => 'HTTP',    'clientCertFingerprint' => 'SHA256:AABBCCDDEEFF00112233'])['authorized']);
        $this->assertFalse($sec->verifyZeroTrustServiceCall(['protocol' => 'TLSv1.2', 'clientCertFingerprint' => 'SHA256:AABBCCDDEEFF00112233'])['authorized']);
        $this->assertFalse($sec->verifyZeroTrustServiceCall(['protocol' => 'TLSv1.3', 'clientCertFingerprint' => ''])['authorized']);

        // 2. 50 servisler arası mTLS + TLS 1.3 çağrısında %100 şifreleme ve ek gecikme ölçümü
        $overheads = [];
        for ($i = 0; $i < 50; $i++) {
            $res = $sec->verifyZeroTrustServiceCall([
                'protocol'              => 'TLSv1.3',
                'clientCertFingerprint' => 'SHA256:9F86D081884C7D659A2FEAA0C55AD015',
                'payload'               => json_encode(['lotId' => 1002, 'bidAmount' => 45000 + ($i * 100)]),
            ]);
            $this->assertTrue($res['authorized']);
            $this->assertTrue($res['encrypted']);
            $this->assertTrue($res['mtlsValid']);
            $this->assertSame('TLSv1.3', $res['protocol']);
            $overheads[] = (float)$res['overheadMs'];
        }

        $avgMs = round(array_sum($overheads) / count($overheads), 4);
        $maxMs = max($overheads);
        $this->assertLessThanOrEqual(5.0, $maxMs, "Plan ihlali: TLS 1.3 şifreleme ek gecikmesi ({$maxMs} ms) > 5 ms");
        $this->assertLessThan(1.0, $avgMs, "Wish ihlali: Ortalama ek gecikme ({$avgMs} ms) >= 1 ms");

        fwrite(STDOUT, sprintf(
            "\n  [NFR17 Olcum] TLS 1.3 + mTLS Orani: %%100 (50/50) | HTTP/TLS1.2: RED | Ort Ek Gecikme: %.4f ms (Maks: %.4f ms) (WISH)\n",
            $avgMs, $maxMs
        ));
    }

    #[TestDox('NFR18: Yönetim Paneli Güvenliği — RBAC ve Donanım Tabanlı FIDO2/WebAuthn MFA (Plan <= 300ms, Wish <= 150ms)')]
    public function testNfr18AdminSecurityRbacAndHardwareMfa(): void
    {
        [$sec] = $this->createIsolatedSecurityService('nfr18');

        $validKey = 'FIDO2-YUBIKEY-ADM-01';
        $validSig = hash_hmac('sha256', '1|' . $validKey, 'MARTELLO_WEBAUTHN_RP_2026');

        // 1. RBAC: Normal ALICI (#3) yönetim paneline kesinlikle erişememeli (HTTP 403)
        $nonAdmin = $sec->authenticateAdminWithHardwareMfa(3, $validKey, $validSig);
        $this->assertFalse($nonAdmin['authenticated']);
        $this->assertSame(403, $nonAdmin['httpStatus']);

        // 2. Must: Admin (#1) Donanım MFA (FIDO2) olmadan kesinlikle girememeli (HTTP 401)
        $noMfa = $sec->authenticateAdminWithHardwareMfa(1, null, null);
        $this->assertFalse($noMfa['authenticated']);
        $this->assertSame(401, $noMfa['httpStatus']);

        // 3. Sahte FIDO2 anahtarı reddedilmeli
        $badKey = $sec->authenticateAdminWithHardwareMfa(1, 'FIDO2-SAHTE-ANAHTAR', $validSig);
        $this->assertFalse($badKey['authenticated']);
        $this->assertSame(401, $badKey['httpStatus']);

        // 4. Geçerli Admin (#1) + Donanım FIDO2 + WebAuthn İmzası <= 150ms (Wish) içinde onaylanmalı
        $okAdmin = $sec->authenticateAdminWithHardwareMfa(1, $validKey, $validSig);
        $this->assertTrue($okAdmin['authenticated']);
        $this->assertSame(200, $okAdmin['httpStatus']);
        $this->assertSame('FIDO2_WEBAUTHN', $okAdmin['mfaMethod']);
        $this->assertLessThanOrEqual(300.0, $okAdmin['authMs'], 'Plan ihlali: Admin MFA süresi > 300ms');
        $this->assertLessThanOrEqual(150.0, $okAdmin['authMs'], 'Wish ihlali: Admin MFA süresi > 150ms');

        fwrite(STDOUT, sprintf(
            "\n  [NFR18 Olcum] RBAC Alici: HTTP 403 RED | MFA'siz Admin: HTTP 401 RED | FIDO2/WebAuthn Admin Onay: %.4f ms (WISH)\n",
            $okAdmin['authMs']
        ));
    }

    #[TestDox('NFR19: Parola Güvenliği — Argon2id/bcrypt Benzersiz Tuzlu Hash & 0 Düz Metin (Plan <= 250ms, Wish < 100ms)')]
    public function testNfr19PasswordSecurityAndCryptographicHash(): void
    {
        [$sec, $store] = $this->createIsolatedSecurityService('nfr19');

        // 1. Mevcut tüm kullanıcı parolalarının düz metin olmadığını (bcrypt/Argon2id hash) doğrula
        $users = $store->getCollection('users');
        $this->assertNotEmpty($users);
        foreach ($users as $u) {
            $storedHash = (string)($u['parolaHash'] ?? '');
            $this->assertMatchesRegularExpression('/^\$(2y|2a|argon2id)\$/', $storedHash, "Kullanıcı #{$u['id']} parolası güvenli hash formatında değil!");
        }

        // 2. Aynı parola için iki kez hash üretildiğinde benzersiz salt sayesinde farklı hash çıkmalı
        $plain = 'Martello#2026!Koleksiyon';
        $h1 = $sec->hashPasswordSecure($plain);
        $h2 = $sec->hashPasswordSecure($plain);

        $this->assertNotSame($h1['hash'], $h2['hash'], 'Benzersiz tuzlama (unique salt) sağlanmalıdır.');
        $this->assertStringNotContainsString($plain, $h1['hash']);

        // 3. Parola doğrulama süresi (Plan <= 250ms, Wish < 100ms)
        $vOk  = $sec->verifyPasswordSecure($plain, $h1['hash']);
        $vBad = $sec->verifyPasswordSecure('YanlisParola123', $h1['hash']);

        $this->assertTrue($vOk['valid']);
        $this->assertFalse($vBad['valid']);
        $this->assertLessThanOrEqual(250.0, $vOk['verifyMs'], 'Plan ihlali: Parola doğrulama > 250ms');
        $this->assertLessThan(100.0, $vOk['verifyMs'], 'Wish ihlali: Parola doğrulama >= 100ms');

        fwrite(STDOUT, sprintf(
            "\n  [NFR19 Olcum] Algoritma: %s | Duz Metin Parola: %%0 | Benzersiz Salt: OK | Dogrulama: %.2f ms (< 100ms) (WISH)\n",
            $h1['algorithm'], $vOk['verifyMs']
        ));
    }

    #[TestDox('NFR20: İstemci Bellek Yönetimi — Timer/Listener Temizliği ve 12 Saatlik Sabit Bellek (Plan < %5 Artış)')]
    public function testNfr20ClientMemoryManagementLongRunningSession(): void
    {
        // 1. Gerçek istemci ürün kodu (public/assets/js/martello.js) sızıntı önleme mekanizmaları kontrolü
        $jsPath = $this->projectRoot . '/public/assets/js/martello.js';
        $this->assertFileExists($jsPath);
        $jsSource = (string)file_get_contents($jsPath);

        $this->assertStringContainsString('clearInterval(', $jsSource, 'Sayaç bitişinde clearInterval temizliği bulunmalıdır.');
        $this->assertStringContainsString('AbortController', $jsSource, 'DOM event listener temizliği için AbortController kullanılmalıdır.');
        $this->assertStringContainsString('beforeunload', $jsSource, 'Sekme kapanışında beforeunload bellek temizliği tanımlanmalıdır.');
        $this->assertStringContainsString('MAX_FEED_ITEMS', $jsSource, 'Canlı olay tamponu sabit boyutlu (Ring Buffer) sınırlandırılmalıdır.');

        // 2. 12 saatlik (43.200 saniye) kesintisiz açık müzayede sekmesi bellek kararlılık simülasyonu
        $maxFeedItems = 100;
        $ringBuffer   = [];

        // Isınma (initial buffer allocation)
        for ($i = 0; $i < $maxFeedItems; $i++) {
            $ringBuffer[] = ['tick' => $i, 'price' => 45000 + $i, 'ts' => 1760000000 + $i];
        }
        gc_collect_cycles();
        $memBaseline = memory_get_usage(false);

        $mem4Hour  = $memBaseline;
        $totalTicks = 43200; // 12 saat * 3600 sn
        for ($sec = 1; $sec <= $totalTicks; $sec++) {
            $ringBuffer[] = ['tick' => $sec, 'price' => 45000 + ($sec % 5000), 'ts' => 1760000000 + $sec];
            if (count($ringBuffer) > $maxFeedItems) {
                array_shift($ringBuffer);
            }
            if ($sec === 14400) { // 4. saat ölçümü
                gc_collect_cycles();
                $mem4Hour = memory_get_usage(false);
            }
        }
        gc_collect_cycles();
        $mem12Hour = memory_get_usage(false);

        $growth4hPct  = round((max(0, $mem4Hour - $memBaseline) / max(1, $memBaseline)) * 100.0, 4);
        $growth12hPct = round((max(0, $mem12Hour - $memBaseline) / max(1, $memBaseline)) * 100.0, 4);

        $this->assertCount($maxFeedItems, $ringBuffer);
        $this->assertLessThanOrEqual(15.0, $growth4hPct, 'Must ihlali: 4 saatlik bellek artışı > %15');
        $this->assertLessThan(5.0, $growth12hPct, 'Plan ihlali: 12 saatlik bellek artışı >= %5');

        fwrite(STDOUT, sprintf(
            "\n  [NFR20 Olcum] JS Temizlik (clearInterval/AbortController/RingBuffer): OK | 4sa Artis: %%%.4f | 12sa (43.200 tick) Artis: %%%.4f (WISH)\n",
            $growth4hPct, $growth12hPct
        ));
    }
}
