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

            case 'simulate_vip_bid':
                $lotId = (int)($post['lot_id'] ?? 1005);
                $lot = $this->dataStore->getItem('lots', 'id', $lotId);
                if (!$lot) {
                    return ['type' => 'danger', 'text' => "VIP Lot bulunamadı."];
                }
                $step = (float)($lot['minimumArtisTutari'] ?? 10000);
                $competitorBid = (float)($lot['mevcutEnYuksekTeklif'] ?? 0) + $step;

                // Rakip VIP kullanıcı (ID 6 - damror0ki / GOLD Tier) pey versin
                $res = $this->auctionService->placeBid($lotId, 6, $competitorBid, 'MANUEL', 90);
                if ($res['success']) {
                    return [
                        'type' => 'info',
                        'text' => "👑 VIP Salonu: Akredite diğer VIP Koleksiyoner (damror0ki - GOLD) ₺" . number_format($competitorBid, 2, ',', '.') . " tutarında resmi VIP pey sürdü.",
                    ];
                }
                return ['type' => 'warning', 'text' => $res['message']];

            case 'vip_reserve_phone':
                $lotId = (int)($post['lot_id'] ?? 1005);
                $userId = (int)($post['user_id'] ?? 3);
                $phone = trim($post['phone'] ?? '+90 532 100 20 30');
                $lang = trim($post['preferred_language'] ?? 'Türkçe / İngilizce');
                
                $this->dataStore->insertItem('notifications', [
                    'kullaniciId' => $userId,
                    'baslik'      => "VIP Telefonla Pey Talebi Alındı",
                    'icerik'      => "Lot #{$lotId} için özel temsilciniz (Leyla Alptekin) müzayede günü {$phone} numarasından sizi canlı bağlayacaktır.",
                    'okundu'      => false,
                    'tarih'       => date('c'),
                    'oncelik'     => 'VIP',
                ]);
                return [
                    'type' => 'success',
                    'text' => "✓ VIP Telefonla Katılım Rezervasyonunuz onaylandı. Özel Temsilciniz (Leyla Alptekin) müzayededen 15 dakika önce {$phone} numarasından doğrudan şifreli hat kuracaktır.",
                ];

            case 'vip_request_limit':
                $userId = (int)($post['user_id'] ?? 3);
                $targetLimit = (float)($post['target_limit'] ?? 2000000);
                $user = $this->dataStore->getItem('users', 'id', $userId);
                if ($user) {
                    $prof = $user['aliciProfili'] ?? [];
                    $prof['teklifLimiti'] = $targetLimit;
                    $user['aliciProfili'] = $prof;
                    $this->dataStore->updateItem('users', 'id', $userId, ['aliciProfili' => $prof]);
                }
                return [
                    'type' => 'success',
                    'text' => "✓ VIP Konsiyerj: Özel Müşteri teklif limitiniz anında ₺" . number_format($targetLimit, 2, ',', '.') . " seviyesine yükseltildi.",
                ];

            case 'simulate_competitor_bid':
                $lotId = (int)($post['lot_id'] ?? 1002);
                $lot = $this->dataStore->getItem('lots', 'id', $lotId);
                $step = (float)($lot['minimumArtisTutari'] ?? 1500);
                $competitorBid = (float)($lot['mevcutEnYuksekTeklif'] ?? 0) + $step;

                // Rakip kullanıcı (ID 6 - damror0ki) teklif versin
                $res = $this->auctionService->placeBid($lotId, 6, $competitorBid, 'MANUEL', 120);
                if ($res['success']) {
                    return [
                        'type' => 'info',
                        'text' => "⚡ Rakip alıcı (damror0ki) ₺" . number_format($competitorBid, 2, ',', '.') . " tutarında pey verdi. Otomatik Teklif sisteminiz hemen devreye girerek adınıza otomatik karşı pey sürdü ve liderliği korudu!",
                    ];
                }
                return ['type' => 'warning', 'text' => $res['message']];

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

            case 'edit_lot':
                $lotId = (int)($post['lot_id'] ?? 0);
                $res = $this->workflowService->editLot($lotId, $post);
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

            case 'register':
                $res = $this->workflowService->registerUser($post);
                return [
                    'type' => $res['success'] ? 'success' : 'danger',
                    'text' => $res['message'],
                ];

            case 'login':
                $email = $post['email'] ?? '';
                $pass = $post['password'] ?? '';
                $res = $this->workflowService->loginUser($email, $pass);
                return [
                    'type' => $res['success'] ? 'success' : 'danger',
                    'text' => $res['message'],
                ];

            case 'update_profile':
                $userId = (int)($post['user_id'] ?? 3);
                $res = $this->workflowService->updateProfile($userId, $post);
                return [
                    'type' => $res['success'] ? 'success' : 'danger',
                    'text' => $res['message'],
                ];

            case 'verify_kyc':
                $userId = (int)($post['user_id'] ?? 0);
                $approve = ($post['decision'] ?? 'approve') === 'approve';
                $res = $this->workflowService->verifyUserKyc($userId, $approve);
                return [
                    'type' => $res['success'] ? 'success' : 'warning',
                    'text' => $res['message'],
                ];

            case 'assign_expert':
                $reqId = (int)($post['request_id'] ?? 0);
                $expertId = (int)($post['expert_id'] ?? 4);
                $res = $this->workflowService->assignExpertToLot($reqId, $expertId);
                return [
                    'type' => $res['success'] ? 'success' : 'danger',
                    'text' => $res['message'],
                ];

            case 'accredit_expert':
                $expertId = (int)($post['expert_id'] ?? 4);
                $approve = ($post['decision'] ?? 'approve') === 'approve';
                $res = $this->workflowService->accreditExpert($expertId, $approve);
                return [
                    'type' => $res['success'] ? 'success' : 'warning',
                    'text' => $res['message'],
                ];

            case 'submit_dispute_opinion':
                $dispId = (int)($post['dispute_id'] ?? 0);
                $expertId = (int)($post['expert_id'] ?? 4);
                $opinion = trim($post['expert_opinion'] ?? 'Ekspertiz teknik mütalaası sunuldu.');

                $disp = $this->dataStore->getItem('disputes', 'id', $dispId);
                if ($disp) {
                    $this->dataStore->updateItem('disputes', 'id', $dispId, [
                        'eksperGorus' => $opinion,
                        'eksperId'    => $expertId,
                        'gorusTarihi' => date('c'),
                        'durum'       => 'EKSPER_GORUSU_ALINDI',
                    ]);
                    return ['type' => 'success', 'text' => "✓ Ekspertiz teknik mütalaanız ihtilaf dosyasına başarıyla kaydedildi ve hakem kuruluna iletildi."];
                }
                return ['type' => 'danger', 'text' => "İhtilaf dosyası bulunamadı."];

            case 'resolve_dispute':
                $dispId = (int)($post['dispute_id'] ?? 0);
                $decision = $post['decision'] ?? 'ALICI_HAKLI';
                $res = $this->workflowService->resolveDispute($dispId, $decision);
                return [
                    'type' => $res['success'] ? 'success' : 'danger',
                    'text' => $res['message'],
                ];

            case 'cancel_bid':
                $lotId = (int)($post['lot_id'] ?? 1002);
                $bidId = isset($post['bid_id']) ? (int)$post['bid_id'] : null;
                $reason = $post['reason'] ?? 'Kural dışı teklif tespiti';
                $res = $this->workflowService->cancelBidOrLot($lotId, $bidId, $reason);
                return [
                    'type' => $res['success'] ? 'warning' : 'danger',
                    'text' => $res['message'],
                ];

            case 'reset_password':
                $email = $post['email'] ?? '';
                $res = $this->workflowService->requestPasswordReset($email);
                return [
                    'type' => $res['success'] ? 'info' : 'warning',
                    'text' => $res['message'],
                ];

            case 'add_category':
                $name = $post['kategori_adi'] ?? 'Yeni Kategori';
                $code = $post['kategori_kodu'] ?? 'CAT-' . mt_rand(10, 99);
                $attrs = array_filter(array_map('trim', explode(',', $post['nitelikler'] ?? 'Dönem, Menşei, Kondisyon')));
                $res = $this->workflowService->addCategoryAttribute($name, $code, $attrs);
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

            case 'cancel_listing':
                $listingId = (int)($post['listing_id'] ?? 0);
                $sellerId = (int)($post['seller_id'] ?? 2);
                $reason = trim($post['reason'] ?? 'Satıcı tarafından geri çekildi');
                $res = $this->workflowService->cancelListing($listingId, $sellerId, $reason);
                return [
                    'type' => $res['success'] ? 'info' : 'danger',
                    'text' => $res['message'],
                ];

            case 'edit_listing':
                $listingId = (int)($post['listing_id'] ?? 0);
                $sellerId = (int)($post['seller_id'] ?? 2);
                $res = $this->workflowService->editListing($listingId, $sellerId, $post);
                return [
                    'type' => $res['success'] ? 'success' : 'danger',
                    'text' => $res['message'],
                ];

            case 'request_payout':
                $sellerId = (int)($post['seller_id'] ?? 2);
                $amount = (float)($post['amount'] ?? 0);
                $iban = trim($post['iban'] ?? 'TR00 0000 0000 0000 0000 0000 00');
                $res = $this->workflowService->requestPayout($sellerId, $amount, $iban);
                return [
                    'type' => $res['success'] ? 'success' : 'warning',
                    'text' => $res['message'],
                ];

            case 'request_revision':
                $reqId = (int)($post['request_id'] ?? 0);
                $expertId = (int)($post['expert_id'] ?? 4);
                $reason = trim($post['reason'] ?? 'Kondisyon ve fatura görseli netleştirilmeli');
                $res = $this->workflowService->requestAppraisalRevision($expertId, $reqId, $reason);
                return [
                    'type' => $res['success'] ? 'info' : 'danger',
                    'text' => $res['message'],
                ];

            case 'escalate_appraisal':
                $reqId = (int)($post['request_id'] ?? 0);
                $expertId = (int)($post['expert_id'] ?? 4);
                $reason = trim($post['reason'] ?? 'Nadir eser statüsü - Üst ihtisas kurulu kararı gereklidir');
                $res = $this->workflowService->escalateAppraisal($expertId, $reqId, $reason);
                return [
                    'type' => $res['success'] ? 'warning' : 'danger',
                    'text' => $res['message'],
                ];

            case 'update_system_params':
                $res = $this->workflowService->updateSystemParameters($post);
                return [
                    'type' => $res['success'] ? 'success' : 'danger',
                    'text' => $res['message'],
                ];

            case 'create_staff':
                $res = $this->workflowService->createStaffAccount($post);
                return [
                    'type' => $res['success'] ? 'success' : 'danger',
                    'text' => $res['message'],
                ];

            case 'update_shipment':
                $shipmentId = (int)($post['shipment_id'] ?? 0);
                $status = $post['status'] ?? 'Teslim Edildi';
                $note = $post['note'] ?? 'Özel sanat taşımacılığı ile alıcıya teslim edildi.';
                $res = $this->workflowService->updateShipmentStatus($shipmentId, $status, $note);
                return [
                    'type' => $res['success'] ? 'success' : 'danger',
                    'text' => $res['message'],
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
