<?php
declare(strict_types=1);

namespace Martello\Patterns\Observer;

/**
 * AuditAndFraudObserver — Gerçek Zamanlı Denetim İzi ve Shill Bidding Tespiti
 * SRS FR-25, R-04, R-12 standartlarına uygun: WORM loglama ve SHA-256 blok zincirleme (Hash Chaining).
 */
class AuditAndFraudObserver implements IAuctionObserver
{
    private string $lastHash = '0000000000000000000000000000000000000000000000000000000000000000';
    /** @var array<int, array> */
    private array $loggedEntries = [];

    public function update(string $event, array $payload): void
    {
        $t0     = microtime(true);
        $lot    = $payload['lot'] ?? [];
        $bid    = $payload['bid'] ?? [];
        $userId = $bid['aliciId'] ?? ($payload['userId'] ?? 0);
        $userIp = $payload['ip'] ?? '127.0.0.1';

        // R-04: Satıcının kendi ürününe teklif vermesi kontrolü (Shill Bidding)
        $sellerId = $lot['saticiId'] ?? null;
        $shillScore = 0.0;
        $shillStatus = 'TEMIZ';

        if ($sellerId !== null && (int)$userId === (int)$sellerId) {
            $shillScore = 1.0;
            $shillStatus = 'ENGEL_SHILL_BIDDING_DETECTED';
        }

        $nowMs = round(microtime(true) * 1000, 3);
        $logData = [
            'logId'                    => 'WORM-AUDIT-' . uniqid('', true),
            'zamanDamgasi'             => date('c'),
            'timestampMs'              => $nowMs,
            'kullaniciId'              => $userId,
            'ipAdresi'                 => $userIp,
            'islemTipi'                => $event,
            'hedefVarlik'              => 'Lot:' . ($lot['id'] ?? 0) . ' / Tutar:' . ($bid['teklifTutari'] ?? 0),
            'shillBiddingAnomaliSkoru' => $shillScore,
            'shillBiddingDurumu'       => $shillStatus,
            'prevHash'                 => $this->lastHash,
        ];

        // SHA-256 Hash Chaining (NFR11 Değiştirilemezlik)
        $currentHash = hash('sha256', json_encode($logData, JSON_UNESCAPED_UNICODE) . $this->lastHash);
        $logData['currentHash'] = $currentHash;
        $logData['anomalyAlertMs'] = round((microtime(true) - $t0) * 1000, 4);
        $this->lastHash = $currentHash;

        $this->loggedEntries[] = $logData;
    }

    /**
     * NFR11: İzole WORM (Write Once, Read Many) Depolama Senkronizasyonu (Plan <= 100ms)
     */
    public function syncToWormFile(string $wormFilePath): array
    {
        $t0 = microtime(true);
        $dir = dirname($wormFilePath);
        if (!is_dir($dir)) {
            mkdir($dir, 0777, true);
        }

        $lines = '';
        foreach ($this->loggedEntries as $entry) {
            $lines .= json_encode($entry, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . PHP_EOL;
        }

        file_put_contents($wormFilePath, $lines, FILE_APPEND | LOCK_EX);
        $syncMs = round((microtime(true) - $t0) * 1000, 3);

        return [
            'success'      => true,
            'syncedCount'  => count($this->loggedEntries),
            'wormFilePath' => $wormFilePath,
            'syncMs'       => $syncMs,
            'lastHash'     => $this->lastHash,
        ];
    }

    /**
     * NFR11: Adli Bilişim SHA-256 Hash Zinciri Bütünlük Doğrulaması (Değiştirilemezlik Denetimi)
     */
    public function verifyChainIntegrity(?array $entries = null): array
    {
        $list = $entries ?? $this->loggedEntries;
        $expectedPrev = '0000000000000000000000000000000000000000000000000000000000000000';
        $tamperedIndex = null;

        foreach ($list as $idx => $item) {
            if (($item['prevHash'] ?? '') !== $expectedPrev) {
                $tamperedIndex = $idx;
                break;
            }
            $copy = $item;
            $storedHash = (string)($copy['currentHash'] ?? '');
            unset($copy['currentHash'], $copy['anomalyAlertMs']);
            $recomputed = hash('sha256', json_encode($copy, JSON_UNESCAPED_UNICODE) . $expectedPrev);
            if (!hash_equals($recomputed, $storedHash)) {
                $tamperedIndex = $idx;
                break;
            }
            $expectedPrev = $storedHash;
        }

        return [
            'intact'        => ($tamperedIndex === null),
            'checkedCount'  => count($list),
            'tamperedIndex' => $tamperedIndex,
        ];
    }

    public function getLoggedEntries(): array
    {
        return $this->loggedEntries;
    }

    public function getLastHash(): string
    {
        return $this->lastHash;
    }

    /**
     * NFR10: Gerçek Zamanlı WAF, Oran Sınırlama (Rate Limit) ve Davranışsal Bot Analitiği
     * Must: >= %99 bot engelleme, < %0.5 yanlış pozitif (false positive)
     * Plan: >= %99.9 bot engelleme, şüpheli isteklerde dinamik CAPTCHA < 50ms
     */
    public function evaluateRequestTraffic(array $req): array
    {
        $t0 = microtime(true);

        $rps           = (float)($req['requestsPerSecond'] ?? 1.0);
        $clickInterval = (float)($req['clickIntervalMs'] ?? 800.0);
        $userAgent     = strtolower((string)($req['userAgent'] ?? 'mozilla/5.0'));
        $mouseEntropy  = (float)($req['mouseEntropy'] ?? 0.85);
        $isSelfBid     = !empty($req['isSellerSelfBid']);

        $riskScore = 0.0;
        $reasons   = [];

        // 1. Bilinen otomasyon/bot User-Agent imzaları (WAF)
        if ($userAgent === '' || preg_match('/(bot|crawler|spider|curl|python-requests|scrapy|selenium|headless|puppeteer|wget)/i', $userAgent)) {
            $riskScore += 0.65;
            $reasons[] = 'WAF_BOT_SIGNATURE';
        }

        // 2. Oran sınırlama (Rate Limit > 10 istek/sn)
        if ($rps > 10.0) {
            $riskScore += 0.50;
            $reasons[] = 'RATE_LIMIT_EXCEEDED';
        }

        // 3. İnsan dışı tıklama hızı (< 80 ms) ve sıfır fare entropisi
        if ($clickInterval < 80.0) {
            $riskScore += 0.45;
            $reasons[] = 'SUBHUMAN_CLICK_INTERVAL';
        }
        if ($mouseEntropy < 0.15) {
            $riskScore += 0.35;
            $reasons[] = 'LOW_BEHAVIORAL_ENTROPY';
        }

        // 4. Shill Bidding (Satıcı kendi lotuna teklif veriyor)
        if ($isSelfBid) {
            $riskScore += 1.0;
            $reasons[] = 'SHILL_BIDDING_SELF_BID';
        }

        $riskScore = min(1.0, round($riskScore, 3));

        $action = 'ALLOW';
        if ($riskScore >= 0.70) {
            $action = 'BLOCKED';
        } elseif ($riskScore >= 0.40) {
            $action = 'CHALLENGE_CAPTCHA';
        }

        $decisionMs = round((microtime(true) - $t0) * 1000, 4);

        return [
            'action'         => $action,
            'blocked'        => ($action !== 'ALLOW'),
            'captchaIssued'  => ($action === 'CHALLENGE_CAPTCHA'),
            'riskScore'      => $riskScore,
            'reasons'        => $reasons,
            'decisionMs'     => $decisionMs,
        ];
    }

    /**
     * NFR15: VIP Müzayede Özel Katmanlı WAF, DDoS ve Bot Koruması
     * Must: >= %99.9 engel | Plan: < 5 ms filtreleme & >= %99.95 | Wish: %100 engel & %0 FP
     */
    public function evaluateVipWafRequest(array $req): array
    {
        $t0 = microtime(true);

        $rps           = (float)($req['requestsPerSecond'] ?? 1.0);
        $burstCount    = (int)($req['burstCountLastSec'] ?? 1);
        $userAgent     = strtolower((string)($req['userAgent'] ?? 'mozilla/5.0'));
        $payloadStr    = (string)($req['payload'] ?? '');
        $clickInterval = (float)($req['clickIntervalMs'] ?? 500.0);
        $mouseEntropy  = (float)($req['mouseEntropy'] ?? 0.80);
        $hasValidTlsFp = (bool)($req['validTlsFingerprint'] ?? true);

        $blockedReasons = [];

        // 1. VIP DDoS Burst & Sıkı Oran Sınırlama (VIP eşiği: > 5 RPS veya > 12 burst/sn)
        if ($rps > 5.0 || $burstCount > 12) {
            $blockedReasons[] = 'VIP_DDOS_RATE_LIMIT';
        }

        // 2. WAF Bot / Otomasyon İmzası & Geçersiz TLS Parmak İzi
        if (!$hasValidTlsFp || $userAgent === '' || preg_match('/(bot|crawler|spider|curl|python|scrapy|selenium|headless|puppeteer|phantom|go-http|wget|nikto|sqlmap)/i', $userAgent)) {
            $blockedReasons[] = 'VIP_WAF_BOT_SIGNATURE';
        }

        // 3. WAF Enjeksiyon / Saldırı Deseni Kontrolü (SQLi / XSS / Path Traversal)
        if ($payloadStr !== '' && preg_match('/(<script|union\s+select|or\s+1=1|\.\.\/|javascript:|onerror=)/i', $payloadStr)) {
            $blockedReasons[] = 'VIP_WAF_ATTACK_PAYLOAD';
        }

        // 4. Davranışsal Bot Analitiği (VIP seansında insan dışı hız + düşük entropi)
        if ($clickInterval < 90.0 || $mouseEntropy < 0.20) {
            $blockedReasons[] = 'VIP_BEHAVIORAL_ANOMALY';
        }

        $blocked = !empty($blockedReasons);
        $filterMs = round((microtime(true) - $t0) * 1000, 4);

        return [
            'allowed'       => !$blocked,
            'blocked'       => $blocked,
            'httpStatus'    => $blocked ? 403 : 200,
            'reasons'       => $blockedReasons,
            'filterMs'      => $filterMs,
            'vipWafLayer'   => 'STRICT_L7_BEHAVIORAL',
        ];
    }
}
