<?php
declare(strict_types=1);

/**
 * MARTELLO Çevrimiçi Müzayede ve Ekspertiz Platformu — Ana Giriş ve Katman Bağlayıcı (index.php)
 * 2. Aşama: JSON Veri Deposu, Tasarım Desenleri (Observer & Strategy), İş Kuralları ve 5 Rol Ekranı
 */

require_once __DIR__ . '/vendor/autoload.php';

use Martello\Controllers\WebAndApiController;
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
use Martello\Patterns\Strategy\Payment\EscrowWalletStrategy;
use Martello\Repositories\AuctionRepository;
use Martello\Services\AuctionEngineService;
use Martello\Services\PlatformWorkflowService;

// 1. 4. Katman: JSON Veri Deposunu Başlat
$dataStore = new JsonDataStore(
    __DIR__ . '/storage/martello_datastore.json',
    __DIR__ . '/storage/locks'
);

// 2. 3. Katman: Observer (Gözlemci) Yayımcısını Oluştur ve 7 Gözlemciyi Abone Et
$auctionSubject = new AuctionSubject();
$auctionSubject->subscribe(new LiveBidScreenObserver());
$auctionSubject->subscribe(new TimerSyncObserver());
$auctionSubject->subscribe(new WatchlistObserver());
$auctionSubject->subscribe(new EscrowReleaseObserver());
$auctionSubject->subscribe(new NotificationObserver());
$auctionSubject->subscribe(new OrderAndReminderObserver());
$auctionSubject->subscribe(new AuditAndFraudObserver());

// 3. 3. Katman: Strategy Nesnelerini ve Servisleri Bağla
$auctionRepo     = new AuctionRepository($dataStore);
$biddingStrategy = new LiveAuctionStrategy();
$paymentStrategy = new EscrowWalletStrategy();

$auctionService  = new AuctionEngineService(
    $dataStore,
    $auctionRepo,
    $biddingStrategy,
    $paymentStrategy,
    $auctionSubject
);
$workflowService = new PlatformWorkflowService($dataStore, $auctionSubject);
$controller      = new WebAndApiController($dataStore, $auctionService, $workflowService);

// CLI üzerinden (örn: test betiği tarafından) çağrıldıysa görünüm render etme
if (PHP_SAPI === 'cli' && basename($_SERVER['SCRIPT_FILENAME'] ?? '') !== 'index.php') {
    return;
}

// 4. Web İsteklerini (POST / GET) İşle
$flashMessage = null;
if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    $flashMessage = $controller->handlePostAction($_POST);
}

$activeRole       = $_GET['role'] ?? 'buyer';
$activeCurrency   = $_GET['currency'] ?? 'TRY';
$currencyStrategy = $controller->resolveCurrencyStrategy($activeCurrency);
$db               = $dataStore->getAllData();
$rates            = $db['systemParameters']['exchangeRates']['rates'] ?? ['TRY' => 1.0, 'USD' => 34.50, 'EUR' => 37.80];

$formatPrice = static function (float $amountInTry) use ($currencyStrategy, $rates): string {
    $converted = $currencyStrategy->convertFromTry($amountInTry, $rates);
    return $currencyStrategy->getSymbol() . number_format($converted, 2, ',', '.');
};

require __DIR__ . '/views/layout/header.php';

switch ($activeRole) {
    case 'vitrin':
        require __DIR__ . '/views/home/vitrin.php';
        break;
    case 'seller':
        require __DIR__ . '/views/seller/workspace.php';
        break;
    case 'appraiser':
        require __DIR__ . '/views/appraiser/workspace.php';
        break;
    case 'support':
        require __DIR__ . '/views/support/workspace.php';
        break;
    case 'admin':
        require __DIR__ . '/views/admin/workspace.php';
        break;
    case 'buyer':
    default:
        require __DIR__ . '/views/buyer/workspace.php';
        break;
}

require __DIR__ . '/views/layout/footer.php';
