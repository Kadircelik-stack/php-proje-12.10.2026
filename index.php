<?php
declare(strict_types=1);

// PHP yerleşik sunucusunda yalnızca public/ altındaki güvenli statik dosyaların sunulmasını sağla;
// storage/, src/, tests/ ve .json/.xml gibi hassas veri depolarına doğrudan erişimi (T1083 / T1552.001) engelle
if (php_sapi_name() === 'cli-server') {
    $urlPath = rawurldecode((string)parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH));
    $ext = strtolower((string)pathinfo($urlPath, PATHINFO_EXTENSION));
    $forbiddenPrefixes = ['/storage', '/src', '/tests', '/vs-tests', '/tools', '/vendor', '/.git'];
    $forbiddenExts = ['json', 'xml', 'ps1', 'bat', 'sln', 'csproj', 'md', 'log', 'lock', 'env', 'phar'];

    foreach ($forbiddenPrefixes as $pref) {
        if (str_starts_with(strtolower($urlPath), $pref)) {
            http_response_code(403);
            exit('403 Forbidden: Hassas sistem dizinine dogrudan erisim engellendi.');
        }
    }
    if ($ext !== '' && in_array($ext, $forbiddenExts, true)) {
        http_response_code(403);
        exit('403 Forbidden: Hassas veri dosyasina dogrudan erisim engellendi.');
    }

    $publicRoot = realpath(__DIR__ . '/public');
    $realTarget = ($urlPath !== '' && $urlPath !== '/') ? realpath(__DIR__ . $urlPath) : false;
    $allowedStaticExts = ['css', 'js', 'jpg', 'jpeg', 'png', 'webp', 'gif', 'svg', 'ico', 'woff', 'woff2', 'ttf'];

    if ($realTarget !== false && $publicRoot !== false && str_starts_with($realTarget, $publicRoot) && !is_dir($realTarget) && in_array($ext, $allowedStaticExts, true)) {
        return false;
    }
}

/**
 * MARTELLO Çevrimiçi Müzayede ve Ekspertiz Platformu — Ana Giriş ve Katman Bağlayıcı (index.php)
 * 2. Aşama: JSON Veri Deposu, Tasarım Desenleri (Observer & Strategy), İş Kuralları ve 5 Rol Ekranı
 */

if (file_exists(__DIR__ . '/vendor/autoload.php')) {
    require_once __DIR__ . '/vendor/autoload.php';
} else {
    require_once __DIR__ . '/src/autoload.php';
}

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
use Martello\Services\UserAuthStore;

// 1. 4. Katman: JSON Veri Deposunu ve Ayrı Kullanıcı Kimlik Deposunu Başlat
$dataStore = new JsonDataStore(
    __DIR__ . '/storage/martello_datastore.json',
    __DIR__ . '/storage/locks'
);
$authStore = new UserAuthStore(__DIR__ . '/storage/users_auth.json');

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

// 4. Savunma Başlıkları (HTTP Security Headers) ve Güvenli Oturum (Session) Başlatma
if (PHP_SAPI !== 'cli' && !headers_sent()) {
    header('X-Content-Type-Options: nosniff');
    header('X-Frame-Options: SAMEORIGIN');
    header('Referrer-Policy: strict-origin-when-cross-origin');
    header('X-XSS-Protection: 1; mode=block');
    header('Permissions-Policy: geolocation=(), microphone=(), camera=()');
}

if (session_status() === PHP_SESSION_NONE && !headers_sent()) {
    session_start([
        'cookie_httponly' => true,
        'cookie_samesite' => 'Lax',
        'use_strict_mode' => true,
    ]);
}
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$flashMessage = $_SESSION['flash_message'] ?? null;
unset($_SESSION['flash_message']);
$autoOpenAuthModal = false;

// Çıkış (Logout) İşlemi
if (($_GET['action'] ?? '') === 'logout' || (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST' && ($_POST['action'] ?? '') === 'logout')) {
    unset($_SESSION['auth_user']);
    if (session_status() === PHP_SESSION_ACTIVE && !headers_sent()) {
        @session_regenerate_id(true);
    }
    $_SESSION['flash_message'] = [
        'type' => 'info',
        'text' => 'Oturumunuz güvenli bir şekilde kapatıldı.',
    ];
    if (PHP_SAPI !== 'cli' && !headers_sent()) {
        header('Location: index.php?role=vitrin');
        exit;
    }
    $flashMessage = $_SESSION['flash_message'];
    unset($_SESSION['flash_message']);
}

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    // CSRF & Cross-Origin İstek Sahteciliği (T1189) Doğrulaması
    $csrfOriginOk = true;
    if (PHP_SAPI !== 'cli' && !empty($_SERVER['HTTP_HOST'])) {
        $reqHost = strtolower((string)parse_url('http://' . $_SERVER['HTTP_HOST'], PHP_URL_HOST));
        $originHeader = $_SERVER['HTTP_ORIGIN'] ?? ($_SERVER['HTTP_REFERER'] ?? '');
        if ($originHeader !== '') {
            $originHost = strtolower((string)parse_url((string)$originHeader, PHP_URL_HOST));
            if ($originHost !== '' && $reqHost !== '' && $originHost !== $reqHost) {
                $csrfOriginOk = false;
            }
        }
        if (!empty($_POST['csrf_token']) && !hash_equals((string)$_SESSION['csrf_token'], (string)$_POST['csrf_token'])) {
            $csrfOriginOk = false;
        }
    }

    $postAction = (string)($_POST['action'] ?? '');

    if (!$csrfOriginOk) {
        $flashMessage = [
            'type' => 'danger',
            'text' => 'Güvenlik Uyarısı (CSRF / Cross-Origin): İstek kaynağı doğrulanamadığı için işlem reddedildi.',
        ];
    } elseif ($postAction === 'login') {
        $loginRes = $authStore->authenticate((string)($_POST['email'] ?? ''), (string)($_POST['password'] ?? ''));
        if ($loginRes['success']) {
            if (session_status() === PHP_SESSION_ACTIVE && !headers_sent()) {
                @session_regenerate_id(true);
            }
            $_SESSION['auth_user'] = $loginRes['account'];
            $_SESSION['flash_message'] = ['type' => 'success', 'text' => $loginRes['message']];
            if (PHP_SAPI !== 'cli' && !headers_sent()) {
                header('Location: index.php?role=' . urlencode($loginRes['panel']));
                exit;
            }
            $flashMessage = $_SESSION['flash_message'];
            unset($_SESSION['flash_message']);
        } else {
            $flashMessage = ['type' => 'danger', 'text' => $loginRes['message']];
            $autoOpenAuthModal = true;
        }
    } elseif ($postAction === 'register') {
        $reqRole = strtoupper(trim((string)($_POST['rol'] ?? 'ALICI')));
        if (!in_array($reqRole, ['ALICI', 'SATICI'], true)) {
            $flashMessage = [
                'type' => 'danger',
                'text' => 'Kayıt formundan yalnızca Alıcı veya Satıcı hesabı açılabilir. Yetkili personel rolleri Admin tarafından atanır.',
            ];
            $autoOpenAuthModal = true;
        } else {
            $_POST['rol'] = $reqRole;
            $regRes = $workflowService->registerUser($_POST);
            if ($regRes['success'] && !empty($regRes['user'])) {
                $createdUser = $regRes['user'];
                $authReg = $authStore->registerPublicAccount(
                    (int)$createdUser['id'],
                    (string)($createdUser['ad'] ?? ''),
                    (string)($createdUser['soyad'] ?? ''),
                    (string)($createdUser['email'] ?? ''),
                    (string)($createdUser['telefon'] ?? ''),
                    $reqRole,
                    (string)($_POST['sifre'] ?? '')
                );

                if ($authReg['success']) {
                    if (session_status() === PHP_SESSION_ACTIVE && !headers_sent()) {
                        @session_regenerate_id(true);
                    }
                    $_SESSION['auth_user'] = $authReg['account'];
                    $_SESSION['flash_message'] = [
                        'type' => 'success',
                        'text' => "Hoş geldiniz {$authReg['account']['adSoyad']}! Hesabınız ({$authReg['account']['rolEtiket']}) oluşturuldu ve oturumunuz açıldı.",
                    ];
                    if (PHP_SAPI !== 'cli' && !headers_sent()) {
                        header('Location: index.php?role=' . urlencode($authReg['panel']));
                        exit;
                    }
                    $flashMessage = $_SESSION['flash_message'];
                    unset($_SESSION['flash_message']);
                } else {
                    $flashMessage = ['type' => 'danger', 'text' => $authReg['message']];
                    $autoOpenAuthModal = true;
                }
            } else {
                $flashMessage = ['type' => 'danger', 'text' => $regRes['message'] ?? 'Kayıt işlemi başarısız.'];
                $autoOpenAuthModal = true;
            }
        }
    } else {
        $flashMessage = $controller->handlePostAction($_POST);

        // Admin panelinden yeni personel oluşturulduysa veya rol değiştirildiyse users_auth.json ile senkronize et
        if ($postAction === 'create_staff' && ($flashMessage['type'] ?? '') === 'success') {
            $staffEmail = trim((string)($_POST['email'] ?? ''));
            $staffRec = $dataStore->getItem('users', 'email', $staffEmail);
            if ($staffRec) {
                $authStore->upsertStaffOrRole(
                    (int)$staffRec['id'],
                    (string)($staffRec['ad'] ?? ''),
                    (string)($staffRec['soyad'] ?? '(Yetkili)'),
                    $staffEmail,
                    (string)($staffRec['rol'] ?? 'CANLI_DESTEK'),
                    trim((string)($_POST['sifre'] ?? '')) ?: 'staff123'
                );
            }
        } elseif ($postAction === 'update_user_role_and_permissions' && ($flashMessage['type'] ?? '') === 'success') {
            $targetUid = (int)($_POST['user_id'] ?? 0);
            $updatedUser = $dataStore->getItem('users', 'id', $targetUid);
            if ($updatedUser) {
                $authStore->upsertStaffOrRole(
                    $targetUid,
                    (string)($updatedUser['ad'] ?? ''),
                    (string)($updatedUser['soyad'] ?? ''),
                    (string)($updatedUser['email'] ?? ''),
                    (string)($updatedUser['rol'] ?? 'ALICI')
                );
            }
        } elseif ($postAction === 'update_profile' && ($flashMessage['type'] ?? '') === 'success') {
            $targetUid = (int)($_POST['user_id'] ?? ($_SESSION['auth_user']['id'] ?? 0));
            $newPass = trim((string)($_POST['yeni_sifre'] ?? ''));
            if ($targetUid > 0 && $newPass !== '') {
                $authStore->updatePassword($targetUid, $newPass);
            }
            if (!empty($_SESSION['auth_user']) && (int)$_SESSION['auth_user']['id'] === $targetUid) {
                $freshU = $dataStore->getItem('users', 'id', $targetUid);
                if ($freshU) {
                    $_SESSION['auth_user']['ad']      = (string)($freshU['ad'] ?? $_SESSION['auth_user']['ad']);
                    $_SESSION['auth_user']['soyad']   = (string)($freshU['soyad'] ?? $_SESSION['auth_user']['soyad']);
                    $_SESSION['auth_user']['adSoyad'] = trim($_SESSION['auth_user']['ad'] . ' ' . $_SESSION['auth_user']['soyad']);
                    $_SESSION['auth_user']['telefon'] = (string)($freshU['telefon'] ?? '');
                }
            }
        }
    }
}

// Aktif oturum kullanıcısını doğrula (users_auth.json ile güncel rol kontrolü)
$loggedInUser = $_SESSION['auth_user'] ?? null;
if (is_array($loggedInUser) && !empty($loggedInUser['id'])) {
    $freshAuth = $authStore->findById((int)$loggedInUser['id']);
    if ($freshAuth) {
        $freshRole = strtoupper((string)($freshAuth['rol'] ?? 'ALICI'));
        $loggedInUser['rol']       = $freshRole;
        $loggedInUser['rolEtiket'] = UserAuthStore::getRoleLabel($freshRole);
        $loggedInUser['panel']     = UserAuthStore::mapRoleToPanel($freshRole);
        $loggedInUser['avatar']    = UserAuthStore::resolveAvatar($freshAuth);
        $_SESSION['auth_user']     = $loggedInUser;
    }
}

if (isset($_GET['currency'])) {
    $activeCurrency = strtoupper(trim((string)$_GET['currency']));
    if (!in_array($activeCurrency, ['TRY', 'USD', 'EUR'], true)) {
        $activeCurrency = 'TRY';
    }
    $_SESSION['active_currency'] = $activeCurrency;
    @setcookie('active_currency', $activeCurrency, [
        'expires'  => time() + 86400 * 30,
        'path'     => '/',
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
} elseif (isset($_SESSION['active_currency'])) {
    $activeCurrency = $_SESSION['active_currency'];
} elseif (isset($_COOKIE['active_currency'])) {
    $activeCurrency = $_COOKIE['active_currency'];
} else {
    $activeCurrency = 'TRY';
}

if (!in_array($activeCurrency, ['TRY', 'USD', 'EUR'], true)) {
    $activeCurrency = 'TRY';
}

// 5. Rol Tabanlı Erişim Denetimi (RBAC) + Müzayede Salonu İnceleme ve VIP Akreditasyon Kuralları
$publicRoles     = ['vitrin', 'yardim', 'help'];
$auctionHallTabs = ['canli', 'kesfet', 'arama', 'gecmis'];
$requestedRole   = isset($_GET['role']) ? strtolower(trim((string)$_GET['role'])) : null;
$requestedTab    = isset($_GET['tab']) ? strtolower(trim((string)$_GET['tab'])) : '';

// Yardımcı: VIP Salonu erişim kontrolü (Admin her zaman erişir; Alıcı GOLD/PLATINUM/DIAMOND olmalı)
$canAccessVipSalon = static function (?array $authUser) use ($dataStore): bool {
    if (!is_array($authUser)) {
        return false;
    }
    $roleCode = strtoupper((string)($authUser['rol'] ?? ''));
    if ($roleCode === 'ADMIN') {
        return true;
    }
    if ($roleCode !== 'ALICI') {
        return false;
    }
    $uRec = $dataStore->getItem('users', 'id', (int)($authUser['id'] ?? 0));
    $vipLevel = strtoupper((string)($uRec['aliciProfili']['vipSeviyesi'] ?? 'STANDARD'));
    return in_array($vipLevel, ['GOLD', 'PLATINUM', 'DIAMOND'], true);
};

if ($loggedInUser === null) {
    // Giriş yapmamış ziyaretçi: Vitrin, Yardım Merkezi ve açık müzayede salonlarını inceleyebilir
    if ($requestedRole === null || $requestedRole === '') {
        $activeRole = 'vitrin';
    } elseif (in_array($requestedRole, $publicRoles, true)) {
        $activeRole = $requestedRole;
    } elseif ($requestedRole === 'buyer' && in_array($requestedTab, $auctionHallTabs, true)) {
        $activeRole = 'buyer';
    } elseif ($requestedRole === 'buyer' && $requestedTab === 'vip') {
        $activeRole = 'vitrin';
        $autoOpenAuthModal = true;
        if ($flashMessage === null) {
            $flashMessage = [
                'type' => 'warning',
                'text' => 'VIP Müzayede Salonu yalnızca akredite VIP koleksiyonerler (Gold/Platinum/Diamond) ve yöneticilere açıktır. Lütfen giriş yapın.',
            ];
        }
    } else {
        $activeRole = 'vitrin';
        $autoOpenAuthModal = true;
        if ($flashMessage === null) {
            $flashMessage = [
                'type' => 'warning',
                'text' => 'Bu çalışma alanına erişmek için lütfen hesabınızla giriş yapın veya kayıt olun.',
            ];
        }
    }
} else {
    $ownPanel = (string)$loggedInUser['panel'];
    $roleCode = strtoupper((string)($loggedInUser['rol'] ?? 'ALICI'));

    if ($requestedRole === null || $requestedRole === '') {
        $activeRole = $ownPanel;
    } elseif ($roleCode === 'ADMIN') {
        // Admin tüm salonlara (VIP dahil) ve tüm çalışma alanlarına sınırsız erişebilir
        $allowedAdminTargets = ['admin', 'vitrin', 'yardim', 'help', 'buyer', 'seller', 'appraiser', 'support'];
        $activeRole = in_array($requestedRole, $allowedAdminTargets, true) ? $requestedRole : 'admin';
    } elseif (in_array($requestedRole, $publicRoles, true)) {
        $activeRole = $requestedRole;
    } elseif ($requestedRole === 'buyer' && $requestedTab === 'vip') {
        // VIP Salonu kıstası: yalnızca akredite VIP Alıcılar (ve yukarıdaki Admin) girebilir
        if ($canAccessVipSalon($loggedInUser)) {
            $activeRole = 'buyer';
        } else {
            $activeRole = ($roleCode === 'ALICI') ? 'buyer' : $ownPanel;
            if ($roleCode === 'ALICI') {
                $_GET['tab'] = 'kesfet';
            }
            if ($flashMessage === null) {
                $flashMessage = [
                    'type' => 'warning',
                    'text' => 'VIP Salonu yalnızca Gold / Platinum / Diamond akreditasyonuna sahip koleksiyonerlere ve yöneticilere açıktır.',
                ];
            }
        }
    } elseif ($requestedRole === 'buyer' && in_array($requestedTab, $auctionHallTabs, true)) {
        // Destek, Satıcı, Ekspertiz ve Alıcı tüm standart müzayede salonlarını serbestçe inceleyebilir
        $activeRole = 'buyer';
    } elseif ($requestedRole === $ownPanel) {
        $activeRole = $ownPanel;
    } else {
        $activeRole = $ownPanel;
        if ($flashMessage === null) {
            $flashMessage = [
                'type' => 'danger',
                'text' => "Yetkisiz Erişim Engellendi: {$loggedInUser['rolEtiket']} hesabı yalnızca kendi yönetim paneline ve açık müzayede salonlarına erişebilir.",
            ];
        }
    }
}

$currencyStrategy = $controller->resolveCurrencyStrategy($activeCurrency);
$db               = $dataStore->getAllData();
$rates            = $db['systemParameters']['exchangeRates']['rates'] ?? ['TRY' => 1.0, 'USD' => 34.50, 'EUR' => 37.80];

$formatPrice = static function (float $amountInTry) use ($currencyStrategy, $rates): string {
    $converted = $currencyStrategy->convertFromTry($amountInTry, $rates);
    return $currencyStrategy->getSymbol() . number_format($converted, 2, ',', '.');
};

if (($_GET['ajax'] ?? '') === 'lot_status') {
    header('Content-Type: application/json; charset=UTF-8');
    $ajaxLotId = (int)($_GET['lot_id'] ?? 1002);
    $foundLot = null;
    foreach ($db['lots'] ?? [] as $lt) {
        if ((int)($lt['id'] ?? 0) === $ajaxLotId) {
            $foundLot = $lt;
            break;
        }
    }
    if ($foundLot === null) {
        http_response_code(404);
        echo json_encode(['ok' => false, 'error' => 'LOT_BULUNAMADI'], JSON_UNESCAPED_UNICODE);
        exit;
    }

    $currentPriceRaw = (float)($foundLot['mevcutEnYuksekTeklif'] ?? 0);
    $minStepRaw      = (float)($foundLot['minimumArtisTutari'] ?? 1500);
    $minNextRaw      = $currentPriceRaw + $minStepRaw;
    $extCount        = (int)($foundLot['antiSnipingSayaci']['uzatmaSayisi'] ?? 0);
    $nextExtSec      = ($extCount < 2) ? 30 : 10;

    $matchingBids = array_values(array_filter(
        $db['bids'] ?? [],
        static fn($b) => (int)($b['lotId'] ?? 0) === $ajaxLotId
    ));
    usort($matchingBids, static fn($a, $b) => ((int)($b['id'] ?? 0)) <=> ((int)($a['id'] ?? 0)));
    $recentBids = [];
    foreach (array_slice($matchingBids, 0, 5) as $rb) {
        $amt = (float)($rb['tutar'] ?? $rb['teklifTutari'] ?? 0);
        $recentBids[] = [
            'id' => (int)($rb['id'] ?? 0),
            'bidder' => (string)($rb['anonimKod'] ?? ('Bidder #' . ($rb['aliciId'] ?? '100'))),
            'amountRaw' => $amt,
            'amountFormatted' => $formatPrice($amt),
            'time' => !empty($rb['zamanDamgasi']) ? date('H:i:s', strtotime((string)$rb['zamanDamgasi'])) : date('H:i:s'),
        ];
    }

    echo json_encode([
        'ok' => true,
        'lotId' => $ajaxLotId,
        'lotKodu' => (string)($foundLot['lotKodu'] ?? ''),
        'durum' => (string)($foundLot['durum'] ?? 'ACIK_ARTIRMADA'),
        'currentPriceRaw' => $currentPriceRaw,
        'currentPriceFormatted' => $formatPrice($currentPriceRaw),
        'minNextBidRaw' => $minNextRaw,
        'minNextBidFormatted' => $formatPrice($minNextRaw),
        'extCount' => $extCount,
        'nextExtSec' => $nextExtSec,
        'recentBids' => $recentBids,
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

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
    case 'yardim':
    case 'help':
        require __DIR__ . '/views/support/help_center.php';
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
