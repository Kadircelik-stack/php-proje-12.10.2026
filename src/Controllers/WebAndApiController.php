<?php
declare(strict_types=1);

namespace Martello\Controllers;

use Martello\Core\JsonDataStore;
use Martello\Patterns\Strategy\Currency\EurCurrencyStrategy;
use Martello\Patterns\Strategy\Currency\ICurrencyStrategy;
use Martello\Patterns\Strategy\Currency\TryCurrencyStrategy;
use Martello\Patterns\Strategy\Currency\UsdCurrencyStrategy;
use Martello\Services\AuctionEngineService;
use Martello\Services\PlatformWorkflowService;

/**
 * WebAndApiController — İstek Yönlendirme ve Eylem Denetleyicisi
 */
class WebAndApiController
{
    private JsonDataStore $dataStore;
    private AuctionEngineService $auctionService;
    private PlatformWorkflowService $workflowService;

    public function __construct(
        JsonDataStore $dataStore,
        AuctionEngineService $auctionService,
        PlatformWorkflowService $workflowService
    ) {
        $this->dataStore       = $dataStore;
        $this->auctionService = $auctionService;
        $this->workflowService = $workflowService;
    }

    /**
     * Web arayüzünden gelen POST işlemlerini işler ve flash mesajı üretir.
     */
    public function handlePostAction(array $post): array
    {
        $action = $post['action'] ?? '';

        switch ($action) {
            case 'place_bid':
                $lotId = (int)($post['lot_id'] ?? 0);
                $userId = (int)($post['user_id'] ?? 3); // Varsayılan Alıcı Zeynep Demir
                $amount = (float)($post['bid_amount'] ?? 0);
                $remainingSec = isset($post['remaining_seconds']) ? (int)$post['remaining_seconds'] : null;

                $res = $this->auctionService->placeBid($lotId, $userId, $amount, 'MANUEL', $remainingSec);
                return [
                    'type'    => $res['success'] ? 'success' : 'danger',
                    'text'    => $res['message'],
                    'details' => $res,
                ];

            case 'set_proxy_bid':
                $lotId = (int)($post['lot_id'] ?? 0);
                $userId = (int)($post['user_id'] ?? 3);
                $maxBudget = (float)($post['max_budget'] ?? 0);

                $res = $this->auctionService->setProxyBid($lotId, $userId, $maxBudget);
                return [
                    'type' => $res['success'] ? 'success' : 'warning',
                    'text' => $res['message'],
                ];

            case 'close_lot':
                $lotId = (int)($post['lot_id'] ?? 0);
                $res = $this->auctionService->closeLot($lotId);
                return [
                    'type' => $res['success'] ? 'success' : 'warning',
                    'text' => $res['message'],
                ];

            case 'toggle_watchlist':
                $userId = (int)($post['user_id'] ?? 3);
                $lotId = (int)($post['lot_id'] ?? 0);
                $auctionId = (int)($post['auction_id'] ?? 801);
                $curPrice = (float)($post['current_price'] ?? 0);

                $watches = $this->dataStore->getCollection('watchlists');
                $removed = false;
                $filtered = [];
                foreach ($watches as $w) {
                    if ((int)($w['kullaniciId'] ?? 0) === $userId && (int)($w['lotId'] ?? 0) === $lotId) {
                        $removed = true;
                        continue;
                    }
                    $filtered[] = $w;
                }

                if ($removed) {
                    $this->dataStore->saveCollection('watchlists', $filtered);
                    return ['type' => 'info', 'text' => "Ürün 'Takip Ettiklerim' listenizden çıkarıldı."];
                }

                $this->dataStore->insertItem('watchlists', [
                    'kullaniciId'     => $userId,
                    'lotId'           => $lotId,
                    'muzayedeId'      => $auctionId,
                    'sonGorulenFiyat' => $curPrice,
                ]);
                return ['type' => 'success', 'text' => "Ürün 'Takip Ettiklerim' listenize başarıyla eklendi."];

            case 'create_listing':
                $sellerId = (int)($post['seller_id'] ?? 2);
                $res = $this->workflowService->createProductAndListing($sellerId, $post);
                return [
                    'type' => $res['success'] ? 'success' : 'danger',
                    'text' => $res['message'],
                ];

            case 'submit_appraisal':
                $expertId = (int)($post['expert_id'] ?? 4);
                $reqId = (int)($post['request_id'] ?? 0);
                $val = (float)($post['suggested_value'] ?? 50000);
                $note = $post['authenticity_note'] ?? 'Orijinal - Ekspertiz Seçki Damgalı';
                $points = array_filter(array_map('trim', explode("\n", $post['notes'] ?? 'Fiziksel kondisyon kusursuz')));
                if (empty($points)) {
                    $points = ['Orijinallik ve mekanizma testleri yapılmıştır.'];
                }

                $res = $this->workflowService->submitAppraisalReport($expertId, $reqId, $val, $note, $points);
                return [
                    'type' => $res['success'] ? 'success' : 'danger',
                    'text' => $res['message'],
                ];

            case 'approve_listing':
                $listingId = (int)($post['listing_id'] ?? 0);
                $start = (float)($post['start_price'] ?? 10000);
                $reserve = (float)($post['reserve_price'] ?? $start);
                $minStep = (float)($post['min_step'] ?? 500);

                $res = $this->workflowService->approveListingAndPublish($listingId, $start, $reserve, $minStep);
                return [
                    'type' => $res['success'] ? 'success' : 'danger',
                    'text' => $res['message'],
                ];

            case 'toggle_ban':
                $userId = (int)($post['user_id'] ?? 0);
                $status = $post['new_status'] ?? 'ASKIDA';
                $res = $this->workflowService->toggleUserBan($userId, $status);
                return [
                    'type' => $res['success'] ? 'warning' : 'danger',
                    'text' => $res['message'],
                ];

            case 'deposit_wallet':
                $userId = (int)($post['user_id'] ?? 3);
                $amount = (float)($post['amount'] ?? 0);
                $res = $this->workflowService->depositToWallet($userId, $amount);
                return [
                    'type' => $res['success'] ? 'success' : 'danger',
                    'text' => $res['message'],
                ];

            case 'create_dispute':
                $userId = (int)($post['user_id'] ?? 3);
                $lotId = (int)($post['lot_id'] ?? 1001);
                $topic = $post['topic'] ?? 'KONDİSYON_UYUŞMAZLIĞI';
                $desc = $post['description'] ?? 'Eksik veya hasarlı parça bildirimi';
                $res = $this->workflowService->createDispute($userId, $lotId, $topic, $desc);
                return [
                    'type' => $res['success'] ? 'success' : 'danger',
                    'text' => $res['message'],
                ];

            case 'send_chat':
                $sessionId = (int)($post['session_id'] ?? 951);
                $senderId = (int)($post['sender_id'] ?? 3);
                $senderName = $post['sender_name'] ?? 'Kullanıcı';
                $msg = trim($post['message'] ?? '');
                if ($msg === '') {
                    return ['type' => 'warning', 'text' => "Boş mesaj gönderilemez."];
                }
                $res = $this->workflowService->sendChatMessage($sessionId, $senderId, $senderName, $msg);
                return [
                    'type' => $res['success'] ? 'success' : 'danger',
                    'text' => $res['message'],
                ];

            case 'pay_order':
                $orderId = (int)($post['order_id'] ?? 0);
                $orders = $this->dataStore->getCollection('orders');
                foreach ($orders as &$ord) {
                    if ((int)($ord['id'] ?? 0) === $orderId) {
                        $ord['siparisDurumu'] = 'Ödendi';
                        break;
                    }
                }
                unset($ord);
                $this->dataStore->saveCollection('orders', $orders);
                return [
                    'type' => 'success',
                    'text' => "3D Secure Sanal POS onayı alındı (≤3sn). Sipariş başarıyla ödendi.",
                ];

            default:
                return ['type' => 'info', 'text' => "İşlem alındı."];
        }
    }

    /**
     * Aktif döviz kuruna göre uygun Strategy nesnesini çözer.
     */
    public function resolveCurrencyStrategy(string $currencyCode): ICurrencyStrategy
    {
        return match (strtoupper($currencyCode)) {
            'USD'   => new UsdCurrencyStrategy(),
            'EUR'   => new EurCurrencyStrategy(),
            default => new TryCurrencyStrategy(),
        };
    }
}
