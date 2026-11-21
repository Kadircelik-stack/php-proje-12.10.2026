<?php
declare(strict_types=1);

/**
 * NFR4 Multi-Process Race Condition Worker
 * Paralel olarak çalıştırılarak aynı lota milisaniyelik farkla eşzamanlı teklif gönderir.
 */
require_once __DIR__ . '/../vendor/autoload.php';

use Martello\Core\JsonDataStore;
use Martello\Patterns\Observer\AuctionSubject;
use Martello\Patterns\Observer\LiveBidScreenObserver;
use Martello\Patterns\Strategy\Bidding\LiveAuctionStrategy;
use Martello\Patterns\Strategy\Payment\EscrowWalletStrategy;
use Martello\Repositories\AuctionRepository;
use Martello\Services\AuctionEngineService;

if ($argc < 6) {
    echo json_encode(['error' => 'Eksik parametre']);
    exit(1);
}

$storePath = $argv[1];
$locksDir  = $argv[2];
$lotId     = (int)$argv[3];
$userId    = (int)$argv[4];
$amount    = (float)$argv[5];

$store   = new JsonDataStore($storePath, $locksDir);
$repo    = new AuctionRepository($store);
$subject = new AuctionSubject();
$subject->subscribe(new LiveBidScreenObserver());

$engine = new AuctionEngineService(
    $store,
    $repo,
    new LiveAuctionStrategy(),
    new EscrowWalletStrategy(),
    $subject
);

$t0 = microtime(true);
$result = $engine->placeBid($lotId, $userId, $amount, 'MANUEL', 120);
$elapsedMs = round((microtime(true) - $t0) * 1000, 3);

echo json_encode([
    'userId'    => $userId,
    'amount'    => $amount,
    'success'   => (bool)($result['success'] ?? false),
    'elapsedMs' => $elapsedMs,
    'message'   => $result['message'] ?? '',
], JSON_UNESCAPED_UNICODE);
