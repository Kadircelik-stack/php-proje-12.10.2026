<?php
declare(strict_types=1);

namespace Martello\Services;

use Martello\Core\JsonDataStore;
use Martello\Patterns\Observer\AuctionSubject;

/**
 * PlatformWorkflowService — İş Akışları, Moderasyon ve Yetkilendirme Servisi
 * SRS .., .., ..gereksinimlerini karşılar.
 */
class PlatformWorkflowService
{
    private JsonDataStore $dataStore;
    private AuctionSubject $auctionSubject;

    public function __construct(JsonDataStore $dataStore, AuctionSubject $auctionSubject)
    {
        $this->dataStore      = $dataStore;
        $this->auctionSubject = $auctionSubject;
    }

    /**
     * Kullanıcı Kaydı
     */
    public function registerUser(array $data): array
    {
        $email = trim($data['email'] ?? '');
        $name = trim($data['ad'] ?? '');
        $surname = trim($data['soyad'] ?? '');
        $phone = trim($data['telefon'] ?? '');
        $tc = trim($data['tc_kimlik'] ?? '');
        $role = $data['rol'] ?? 'ALICI';
        $password = $data['sifre'] ?? '123456';

        if ($email === '' || $name === '' || $surname === '') {
            return ['success' => false, 'message' => "Ad, soyad ve e-posta alanları zorunludur."];
        }

        // E-posta mükerrerlik kontrolü
        $existing = $this->dataStore->getItem('users', 'email', $email);
        if ($existing) {
            return ['success' => false, 'message' => "Bu e-posta adresiyle zaten kayıtlı bir hesap bulunmaktadır."];
        }

        $passwordHash = password_hash($password, PASSWORD_DEFAULT);
        $anonCode = 'Anonim-' . strtoupper(substr(md5($email . time()), 0, 4));

        $newUser = $this->dataStore->insertItem('users', [
            'ad'            => $name,
            'soyad'         => $surname,
            'email'         => $email,
            'telefon'       => $phone,
            'tcKimlikNo'    => $tc,
            'rol'           => $role,
            'durum'         => 'BEKLEMEDE', // KYC onayı bekliyor
            'sifreHash'     => $passwordHash,
            'anonimKod'     => $anonCode,
            'kycDogrulama'  => [
                'dogrulandiMi'   => false,
                'yuklenenBelge' => 'kimlik_belgesi_' . date('Ymd') . '.pdf',
                'basvuruTarihi' => date('c'),
            ],
            'aliciProfili'  => [
                'vipSeviyesi' => 'STANDARD',
                'harcamaHacmi' => 0.0,
            ],
            'kayitTarihi'   => date('c'),
        ]);

        // İlk Cüzdanını oluştur
        $this->dataStore->insertItem('wallets', [
            'kullaniciId'   => $newUser['id'],
            'paraBirimi'    => 'TRY',
            'bakiye'        => 0.0,
            'blokeBakiye'   => 0.0,
            'aktifBlokeler' => [],
            'sonGuncelleme' => date('c'),
        ]);

        return [
            'success' => true,
            'message' => "Hesabınız başarıyla oluşturuldu! Güvenlik gereği kimlik ve iletişim bilgileriniz (KYC) doğrulama sürecine aktarıldı.",
            'user'    => $newUser,
        ];
    }

    /**
     * Kullanıcı Girişi
     */
    public function loginUser(string $email, string $password): array
    {
        $user = $this->dataStore->getItem('users', 'email', trim($email));
        if (!$user) {
            return ['success' => false, 'message' => "Girdiğiniz e-posta veya şifre hatalı."];
        }

        // : Hesap Askıya Alınmış / Banlanmışsa engelle
        if (in_array($user['durum'] ?? '', ['ASKIDA', 'BANLI', 'ENGELENMIS'], true)) {
            return [
                'success' => false,
                'message' => "Hesabınız askıya alınmıştır. Platform güvenliği gereği giriş yetkiniz durdurulmuştur.",
            ];
        }

        // Parola doğrulaması
        $valid = false;
        if (!empty($user['sifreHash']) && password_verify($password, $user['sifreHash'])) {
            $valid = true;
        } elseif ($password === '123456' || $password === 'admin123') {
            $valid = true; // Prototip varsayılan şifre
        }

        if (!$valid) {
            return ['success' => false, 'message' => "Hatalı şifre girdiniz."];
        }

        return [
            'success' => true,
            'message' => "Giriş başarılı! Hoş geldiniz, {$user['ad']} {$user['soyad']}.",
            'user'    => $user,
        ];
    }

    /**
     * Kullanıcı Profili Güncelleme
     */
    public function updateProfile(int $userId, array $data): array
    {
        $user = $this->dataStore->getItem('users', 'id', $userId);
        if (!$user) {
            return ['success' => false, 'message' => "Kullanıcı bulunamadı."];
        }

        $updates = [
            'ad'            => trim($data['ad'] ?? $user['ad']),
            'soyad'         => trim($data['soyad'] ?? $user['soyad']),
            'telefon'       => trim($data['telefon'] ?? ($user['telefon'] ?? '')),
            'adres'         => trim($data['adres'] ?? ($user['adres'] ?? 'İstanbul, Türkiye')),
            'faturaBilgisi' => trim($data['fatura_bilgisi'] ?? ($user['faturaBilgisi'] ?? 'Bireysel Fatura')),
        ];

        if (!empty($data['yeni_sifre'])) {
            $updates['sifreHash'] = password_hash($data['yeni_sifre'], PASSWORD_DEFAULT);
        }

        $this->dataStore->updateItem('users', 'id', $userId, $updates);

        return [
            'success' => true,
            'message' => "Profil bilgileriniz başarıyla güncellendi.",
        ];
    }

    /**
     * Kullanıcı Doğrulama / KYC İnceleme
     */
    public function verifyUserKyc(int $userId, bool $approve): array
    {
        $user = $this->dataStore->getItem('users', 'id', $userId);
        if (!$user) {
            return ['success' => false, 'message' => "Kullanıcı bulunamadı."];
        }

        if ($approve) {
            $this->dataStore->updateItem('users', 'id', $userId, [
                'durum'        => 'AKTIF',
                'kycDogrulama' => [
                    'dogrulandiMi'  => true,
                    'onaylayan'     => 'Sistem Yöneticisi',
                    'onayTarihi'    => date('c'),
                ],
            ]);
            return [
                'success' => true,
                'message' => "Kullanıcı (#{$userId} {$user['ad']}) KYC kimlik doğrulaması ONAYLANDI. Kullanıcıya tam yetki verildi.",
            ];
        }

        $this->dataStore->updateItem('users', 'id', $userId, [
            'durum'        => 'REDDEDILDI',
            'kycDogrulama' => [
                'dogrulandiMi'  => false,
                'onayTarihi'    => date('c'),
                'redGerekcesi'  => 'Eksik veya okunamayan kimlik belgesi',
            ],
        ]);
        return [
            'success' => true,
            'message' => "Kullanıcı (#{$userId} {$user['ad']}) KYC doğrulaması reddedildi.",
        ];
    }

    /**
     * Doğrulanan kullanıcıların rolüne göre (Alıcı, Satıcı, Eksper, Admin)
     * sistem içi erişim izinlerinin ve yetki seviyelerinin belirlenmesi (SRS RBAC)
     */
    public function updateUserRoleAndPermissions(int $userId, string $newRole, string $authTier, array $permissions): array
    {
        $user = $this->dataStore->getItem('users', 'id', $userId);
        if (!$user) {
            return ['success' => false, 'message' => "Kullanıcı bulunamadı."];
        }

        $allowedRoles = ['ALICI', 'SATICI', 'EKSPERTIZ', 'ADMIN', 'CANLI_DESTEK'];
        if (str_contains($newRole, ':')) {
            [$parsedRole, $parsedTier] = explode(':', $newRole, 2);
            $newRole = $parsedRole;
            if (empty($authTier) || $authTier === 'STANDART') {
                $authTier = $parsedTier;
            }
        }
        $newRole = strtoupper(trim($newRole));
        if (!in_array($newRole, $allowedRoles, true)) {
            return ['success' => false, 'message' => "Geçersiz kullanıcı rolü ({$newRole})."];
        }

        // Otomatik varsayılan izin presetleri
        if (empty($permissions)) {
            if ($newRole === 'ALICI') {
                $permissions = ['BID_LIVE', 'WALLET_ESCROW', 'ORDER_TRACK'];
                if (in_array($authTier, ['GOLD', 'PLATINUM', 'DIAMOND'], true)) {
                    $permissions[] = 'VIP_SALON';
                }
            } elseif ($newRole === 'SATICI') {
                $permissions = ['LISTING_CREATE', 'APPRAISAL_REQUEST', 'PAYOUT_REQUEST'];
            } elseif ($newRole === 'EKSPERTIZ') {
                $permissions = ['APPRAISAL_INSPECT', 'REPORT_CREATE', 'CERTIFICATE_SIGN'];
            } elseif ($newRole === 'ADMIN') {
                $permissions = ['ALL_ACCESS', 'KYC_APPROVE', 'ROLE_MANAGE', 'LOT_CLOSE', 'AUDIT_LOG'];
            } else {
                $permissions = ['SUPPORT_CHAT'];
            }
        }

        // Rol ve Yetki Seviyesi Yapılandırması
        $updates = [
            'rol' => $newRole,
            'yetkiSeviyesi' => $authTier,
            'erisimIzinleri' => $permissions,
            'yetkiGuncellemeZamani' => date('c'),
        ];

        // Role göre ilgili alt profilleri de senkronize et
        if ($newRole === 'ALICI') {
            $aliciProfili = $user['aliciProfili'] ?? [];
            $aliciProfili['vipSeviyesi'] = in_array($authTier, ['GOLD', 'PLATINUM', 'DIAMOND'], true) ? $authTier : 'STANDARD';
            $aliciProfili['dogrulandiMi'] = true;
            $updates['aliciProfili'] = $aliciProfili;
        } elseif ($newRole === 'SATICI') {
            $saticiProfili = $user['saticiProfili'] ?? [];
            $saticiProfili['saticiTipi'] = $authTier; // BIREYSEL / KURUMSAL / PREMIUM
            $updates['saticiProfili'] = $saticiProfili;
        } elseif ($newRole === 'EKSPERTIZ') {
            $ekspertizProfili = $user['ekspertizProfili'] ?? [];
            $ekspertizProfili['yetkiSeviyesi'] = $authTier; // SAHA_EKSPERI / KIDEMLI / BAS_EKSPER
            $ekspertizProfili['yetkiDurumu'] = 'ONAYLI';
            $updates['ekspertizProfili'] = $ekspertizProfili;
        } elseif ($newRole === 'ADMIN') {
            $adminProfili = $user['adminProfili'] ?? [];
            $adminProfili['yetkiDerecesi'] = ($authTier === 'SUPER_ADMIN') ? 3 : (($authTier === 'DENETCI') ? 2 : 1);
            $updates['adminProfili'] = $adminProfili;
        }

        $this->dataStore->updateItem('users', 'id', $userId, $updates);
        $this->dataStore->commit();

        return [
            'success' => true,
            'message' => "Kullanıcı (#{$userId} " . ($user['ad'] ?? '') . ") rolü '{$newRole}', yetki seviyesi '{$authTier}' olarak güncellendi ve erişim izinleri tanımlandı.",
            'user' => array_merge($user, $updates)
        ];
    }

    /**
     * Şifremi Unuttum Kurtarma Akışı
     */
    public function requestPasswordReset(string $email): array
    {
        $user = $this->dataStore->getItem('users', 'email', trim($email));
        if (!$user) {
            // Güvenlik gereği kullanıcı var/yok belli edilmez, ama simülasyonda bilgi veririz
            return [
                'success' => true,
                'message' => "Eğer sistemde kayıtlı ise, şifre sıfırlama bağlantısı 30 saniye içinde e-posta adresinize iletilecektir.",
            ];
        }

        $resetToken = bin2hex(random_bytes(16));
        $this->dataStore->updateItem('users', 'id', $user['id'], [
            'sifreSifirlamaToken' => $resetToken,
            'sifreTokenZamani'   => date('c'),
        ]);

        return [
            'success' => true,
            'message' => "Şifre sıfırlama bağlantısı {$email} adresine 30 saniye içinde iletildi (simülasyonu aktif).",
        ];
    }

    /**
     * Eksper ve Kurum Atama
     */
    public function assignExpertToLot(int $requestId, int $expertId): array
    {
        $req = $this->dataStore->getItem('appraisalRequests', 'id', $requestId);
        if (!$req) {
            return ['success' => false, 'message' => "Ekspertiz talebi bulunamadı."];
        }

        $expert = $this->dataStore->getItem('users', 'id', $expertId);
        $expertName = $expert ? ($expert['ad'] . ' ' . $expert['soyad']) : "Eksper #{$expertId}";

        $this->dataStore->updateItem('appraisalRequests', 'id', $requestId, [
            'atananEksperId' => $expertId,
            'durum'          => 'ATANDI',
            'not'            => "Görev {$expertName} uzmanına atandı.",
        ]);

        return [
            'success' => true,
            'message' => "İlan başarıyla {$expertName} uzmanına atandı ve eksper paneline iletildi.",
        ];
    }

    /**
     * Ekspertiz Akreditasyon Yönetimi
     */
    public function accreditExpert(int $expertId, bool $approve): array
    {
        $user = $this->dataStore->getItem('users', 'id', $expertId);
        if (!$user) {
            return ['success' => false, 'message' => "Eksper bulunamadı."];
        }

        $this->dataStore->updateItem('users', 'id', $expertId, [
            'akreditasyon' => [
                'onaylandiMi'   => $approve,
                'akreditasyonNo' => $approve ? ('AKR-2026-' . mt_rand(100, 999)) : null,
                'onayTarihi'    => date('c'),
                'durum'         => $approve ? 'AKTIF_EKSPER' : 'REDDEDILDI',
            ],
        ]);

        $statusStr = $approve ? "onaylandı ve 'Aktif Eksper' statüsü kazandı" : "reddedildi";
        return [
            'success' => true,
            'message' => "{$user['ad']} {$user['soyad']} mesleki akreditasyonu {$statusStr}.",
        ];
    }

    /**
     * İhtilaf Çözümü ve Tahkim Kararı
     */
    public function resolveDispute(int $disputeId, string $decision): array
    {
        $dispute = $this->dataStore->getItem('disputes', 'id', $disputeId);
        if (!$dispute) {
            return ['success' => false, 'message' => "İhtilaf kaydı bulunamadı."];
        }

        $lotId = (int)($dispute['lotId'] ?? 0);
        $buyerId = (int)($dispute['olusturanKullaniciId'] ?? 0);

        if ($decision === 'ALICI_HAKLI') {
            $this->dataStore->updateItem('disputes', 'id', $disputeId, [
                'durum'       => 'COZULDU',
                'karar'       => 'ALICI_HAKLI',
                'kararTarihi' => date('c'),
                'aciklama'    => 'Hakem heyeti alıcıyı haklı buldu. Emanet bloke çözülerek alıcı cüzdanına iade yapıldı.',
            ]);

            // Para iadesi simülasyonu
            $wallet = $this->dataStore->getItem('wallets', 'kullaniciId', $buyerId);
            if ($wallet) {
                $refundAmount = 12800.0; // Lot bedeli
                $this->dataStore->updateItem('wallets', 'id', $wallet['id'], [
                    'bakiye'        => ((float)($wallet['bakiye'] ?? 0)) + $refundAmount,
                    'sonGuncelleme' => date('c'),
                ]);
            }

            return [
                'success' => true,
                'message' => "İhtilaf #{$disputeId} için ALICI HAKLI kararı verildi. Bedel alıcıya iade edildi.",
            ];
        }

        $this->dataStore->updateItem('disputes', 'id', $disputeId, [
            'durum'       => 'COZULDU',
            'karar'       => 'SATICI_HAKLI',
            'kararTarihi' => date('c'),
            'aciklama'    => 'Hakem heyeti satıcıyı haklı buldu. Emanet bloke bedeli satıcı hesabına aktarılacak.',
        ]);

        return [
            'success' => true,
            'message' => "İhtilaf #{$disputeId} için SATICI HAKLI kararı verildi. Satış süreci devam ettiriliyor.",
        ];
    }

    /**
     * Kural Dışı Teklif veya İlan İptal Etme
     */
    public function cancelBidOrLot(int $lotId, ?int $bidId, string $reason): array
    {
        $lot = $this->dataStore->getItem('lots', 'id', $lotId);
        if (!$lot) {
            return ['success' => false, 'message' => "Lot bulunamadı."];
        }

        // Teklif iptali: En son teklifi bulup iptal et, bir öncekine dön
        $bids = $this->dataStore->getCollection('bids');
        $lotBids = [];
        foreach ($bids as $b) {
            if ((int)($b['lotId'] ?? 0) === $lotId && ($b['durum'] ?? '') === 'GECERLI') {
                $lotBids[] = $b;
            }
        }

        if (!empty($lotBids)) {
            // Son teklifi iptal yap
            $lastBid = end($lotBids);
            $this->dataStore->updateItem('bids', 'id', $lastBid['id'], [
                'durum'        => 'IPTAL_EDILDI',
                'iptalSebebi'  => $reason,
                'iptalZamani'  => date('c'),
            ]);

            // Önceki teklife dön
            $prevBid = count($lotBids) > 1 ? $lotBids[count($lotBids) - 2] : null;
            $newHighest = $prevBid ? (float)$prevBid['teklifTutari'] : (float)$lot['baslangicFiyati'];
            $newLeader = $prevBid ? (int)$prevBid['aliciId'] : null;
            $newAnon = $prevBid ? ($prevBid['aliciAnonimKod'] ?? 'Anonim') : 'Henüz Teklif Yok';

            $this->dataStore->updateItem('lots', 'id', $lotId, [
                'mevcutEnYuksekTeklif' => $newHighest,
                'liderAliciId'         => $newLeader,
                'liderAnonimKod'       => $newAnon,
            ]);

            // İptal edilen alıcının blokesini iade et
            $refundUserId = (int)$lastBid['aliciId'];
            $refundAmount = (float)$lastBid['teklifTutari'];
            $wallet = $this->dataStore->getItem('wallets', 'kullaniciId', $refundUserId);
            if ($wallet) {
                $curBlocked = (float)($wallet['blokeBakiye'] ?? 0);
                $this->dataStore->updateItem('wallets', 'id', $wallet['id'], [
                    'blokeBakiye'   => max(0.0, $curBlocked - $refundAmount),
                    'sonGuncelleme' => date('c'),
                ]);
            }

            return [
                'success' => true,
                'message' => "Kural dışı teklif (#{$lastBid['id']}) iptal edildi. Süreç bir önceki peye döndürüldü ve bloke tutarı iade edildi.",
            ];
        }

        return ['success' => false, 'message' => "İptal edilecek geçerli pey bulunamadı."];
    }

    /**
     * Kategori ve Dinamik Nitelik Şablonu Ekleme
     */
    public function addCategoryAttribute(string $name, string $code, array $attributes): array
    {
        $newCat = $this->dataStore->insertItem('categories', [
            'kategoriAdi'        => $name,
            'kategoriKodu'       => $code,
            'aktifMi'            => true,
            'zorunluNitelikler' => $attributes,
            'olusturmaTarihi'    => date('c'),
        ]);

        return [
            'success' => true,
            'message' => "'{$name}' kategorisi ve özellik şablonu başarıyla tanımlandı.",
            'category' => $newCat,
        ];
    }

    /**
     * Admin: Müzayede / Lot Bilgilerini Düzenleme (Admin Düzenleme Yetkisi)
     */
    public function editLot(int $lotId, array $data): array
    {
        $lot = $this->dataStore->getItem('lots', 'id', $lotId);
        if (!$lot) {
            return ['success' => false, 'message' => "Düzenlenecek lot (#{$lotId}) bulunamadı."];
        }

        $updates = [];
        if (!empty($data['baslik'])) {
            $updates['baslik'] = trim((string)$data['baslik']);
        }
        if (isset($data['baslangicFiyati']) && is_numeric($data['baslangicFiyati'])) {
            $updates['baslangicFiyati'] = (float)$data['baslangicFiyati'];
        }
        if (isset($data['rezervFiyat']) && is_numeric($data['rezervFiyat'])) {
            $updates['rezervFiyat'] = (float)$data['rezervFiyat'];
        }
        if (isset($data['minimumArtisTutari']) && is_numeric($data['minimumArtisTutari'])) {
            $updates['minimumArtisTutari'] = (float)$data['minimumArtisTutari'];
        }
        if (!empty($data['durum'])) {
            $updates['durum'] = trim((string)$data['durum']);
        }
        if (!empty($data['kalanSureMetni'])) {
            $updates['kalanSureMetni'] = trim((string)$data['kalanSureMetni']);
        }
        if (!empty($data['kategoriAdi'])) {
            $updates['kategoriAdi'] = trim((string)$data['kategoriAdi']);
        }

        $this->dataStore->updateItem('lots', 'id', $lotId, $updates);

        // Denetim günlüğüne kaydet (WORM Log)
        $this->dataStore->insertItem('auditLogs', [
            'logId'              => 'LOG-MOD-' . mt_rand(1000, 9999),
            'zamanDamgasi'       => date('c'),
            'kullaniciId'        => 1, // Admin
            'islemTipi'          => 'MÜZAYEDE_DÜZENLEME',
            'shillBiddingDurumu' => 'TEMİZ',
            'detay'              => "Lot #{$lotId} ({$lot['lotKodu']}) admin tarafından güncellendi.",
            'currentHash'        => hash('sha256', date('c') . $lotId . json_encode($updates)),
        ]);

        return [
            'success' => true,
            'message' => "Müzayede Lotu ({$lot['lotKodu']} - {$lot['baslik']}) bilgileri başarıyla güncellendi.",
            'lot'     => array_merge($lot, $updates),
        ];
    }

    /**
     * Satıcı Ürün ve İlan Ekleme
     */
    public function createProductAndListing(int $sellerId, array $data): array
    {
        $title = trim($data['baslik'] ?? '');
        $categoryId = (int)($data['kategoriId'] ?? 10);
        $startPrice = (float)($data['baslangicFiyati'] ?? 1000);
        $reservePrice = (float)($data['rezervFiyat'] ?? $startPrice);
        $minStep = (float)($data['minimumArtisTutari'] ?? 100);

        if ($title === '') {
            return ['success' => false, 'message' => "Ürün başlığı zorunludur."];
        }

        // Ürünü kaydet
        $product = $this->dataStore->insertItem('products', [
            'saticiId'          => $sellerId,
            'kategoriId'        => $categoryId,
            'baslik'            => $title,
            'aciklama'          => $data['aciklama'] ?? '',
            'fizikselKondisyon' => $data['fizikselKondisyon'] ?? 'İnceleme Bekliyor',
            'orijinallikDurumu' => 'Ekspertizde',
            'gorseller'         => ['varsayilan_lot.jpg'],
            'dinamikNitelikler' => $data['nitelikler'] ?? [],
        ]);

        // İlan oluştur
        $listing = $this->dataStore->insertItem('listings', [
            'ilanKodu'         => 'ILN-' . date('Y') . '-' . mt_rand(100, 999),
            'urunId'           => $product['id'],
            'saticiId'         => $sellerId,
            'ekspertizRaporId' => null,
            'durum'            => 'EKSPERTIZ_BEKLIYOR',
        ]);

        // Ekspertiz talebi aç
        $this->dataStore->insertItem('appraisalRequests', [
            'talepNo'        => 'EXP-REQ-' . mt_rand(500, 999),
            'lotKodu'        => 'AUC-' . mt_rand(1100, 1999),
            'urunId'         => $product['id'],
            'atananEksperId' => 4, // Varsayılan uzman Burak Şahin
            'durum'          => 'BEKLEYEN',
            'eskalasyon'     => false,
            'not'            => 'Yeni ürün eklendi, ekspertiz incelemesi bekliyor.',
        ]);

        return [
            'success' => true,
            'message' => "Ürün ve ilan taslağı oluşturuldu. Ekspertiz onayına yönlendirildi.",
            'product' => $product,
            'listing' => $listing,
        ];
    }

    /**
     * Ekspertiz Raporu Düzenleme ve Fiyat Önerisi Girme
     */
    public function submitAppraisalReport(int $expertId, int $requestId, float $suggestedValue, string $authenticityNote, array $points): array
    {
        $request = $this->dataStore->getItem('appraisalRequests', 'id', $requestId);
        if (!$request) {
            return ['success' => false, 'message' => "Ekspertiz talebi bulunamadı."];
        }

        $report = $this->dataStore->insertItem('appraisalReports', [
            'raporNo'              => 'REP-' . date('Y') . '-' . mt_rand(200, 899),
            'talepId'              => $requestId,
            'urunId'               => $request['urunId'] ?? 0,
            'eksperId'             => $expertId,
            'onerilenPiyasaDegeri' => $suggestedValue,
            'orijinallikNotu'      => $authenticityNote,
            'maddeler'             => $points,
            'denetimDurumu'        => 'ONAYLANDI',
            'raporTarihi'          => date('c'),
        ]);

        // Talebi tamamlandı yap
        $this->dataStore->updateItem('appraisalRequests', 'id', $requestId, [
            'durum' => 'TAMAMLANDI',
            'not'   => 'Ekspertiz raporu tamamlandı.',
        ]);

        // İlgili ilanı onaya hazırla
        $listings = $this->dataStore->getCollection('listings');
        foreach ($listings as $l) {
            if ((int)($l['urunId'] ?? 0) === (int)($request['urunId'] ?? 0)) {
                $this->dataStore->updateItem('listings', 'id', $l['id'], [
                    'durum'            => 'ONAYLANDI',
                    'ekspertizRaporId' => $report['id'],
                ]);
                break;
            }
        }

        return [
            'success' => true,
            'message' => "Ekspertiz raporu başarıyla sisteme kaydedildi ve ilan onay aşamasına iletildi.",
            'report'  => $report,
        ];
    }

    /**
     * Admin: İlan Onaylama / Reddetme ve Müzayedeye Lot Olarak Ekleme
     */
    public function approveListingAndPublish(int $listingId, float $startPrice, float $reservePrice, float $minStep): array
    {
        $listing = $this->dataStore->getItem('listings', 'id', $listingId);
        if (!$listing) {
            return ['success' => false, 'message' => "İlan bulunamadı."];
        }

        $this->dataStore->updateItem('listings', 'id', $listingId, ['durum' => 'YAYINDA']);

        $product = $this->dataStore->getItem('products', 'id', $listing['urunId']);

        // Müzayedeye lot olarak ekle
        $newLot = $this->dataStore->insertItem('lots', [
            'lotKodu'              => 'AUC-' . mt_rand(2000, 2999),
            'lotNumarasi'          => mt_rand(20, 50),
            'muzayedeId'           => 801,
            'urunId'               => $listing['urunId'],
            'ilanId'               => $listingId,
            'ekspertizRaporId'     => $listing['ekspertizRaporId'] ?? 301,
            'baslik'               => $product['baslik'] ?? 'Müzayede Lotu',
            'kategoriAdi'          => 'Antika & Sanat',
            'paraBirimi'           => 'TRY',
            'baslangicFiyati'      => $startPrice,
            'rezervFiyat'          => $reservePrice,
            'mevcutEnYuksekTeklif' => $startPrice,
            'minimumArtisTutari'   => $minStep,
            'liderAliciId'         => null,
            'liderAnonimKod'       => 'Henüz Teklif Yok',
            'durum'                => 'ACIK_ARTIRMADA',
            'kalanSureMetni'       => '2 gün 04 saat',
            'antiSnipingSayaci'    => [
                'uzatmaSayisi'   => 0,
                'sonBitisZamani' => date('c', strtotime('+2 days')),
            ],
        ]);

        return [
            'success' => true,
            'message' => "İlan onaylandı ve açık artırma kataloğuna eklendi.",
            'lot'     => $newLot,
        ];
    }

    /**
     * Admin: Kullanıcı Durumu Değiştirme (Askıya Alma / Banlama / Aktif Etme)
     */
    public function toggleUserBan(int $userId, string $newStatus): array
    {
        $user = $this->dataStore->getItem('users', 'id', $userId);
        if (!$user) {
            return ['success' => false, 'message' => "Kullanıcı bulunamadı."];
        }

        $this->dataStore->updateItem('users', 'id', $userId, ['durum' => $newStatus]);
        return [
            'success' => true,
            'message' => "Kullanıcı (#{$userId} {$user['ad']} {$user['soyad']}) durumu '{$newStatus}' olarak güncellendi.",
        ];
    }

    /**
     * Cüzdana Bakiye Yükleme (/Alıcı)
     */
    public function depositToWallet(int $userId, float $amount): array
    {
        if ($amount <= 0) {
            return ['success' => false, 'message' => "Geçerli bir yükleme tutarı giriniz."];
        }

        $wallet = $this->dataStore->getItem('wallets', 'kullaniciId', $userId);
        if (!$wallet) {
            $wallet = $this->dataStore->insertItem('wallets', [
                'kullaniciId'   => $userId,
                'paraBirimi'    => 'TRY',
                'bakiye'        => $amount,
                'blokeBakiye'   => 0.0,
                'aktifBlokeler' => [],
                'sonGuncelleme' => date('c'),
            ]);
        } else {
            $newBal = (float)($wallet['bakiye'] ?? 0) + $amount;
            $this->dataStore->updateItem('wallets', 'id', $wallet['id'], [
                'bakiye'        => $newBal,
                'sonGuncelleme' => date('c'),
            ]);
        }

        return [
            'success' => true,
            'message' => "₺" . number_format($amount, 2, ',', '.') . " tutarındaki bakiye cüzdanınıza başarıyla yüklendi.",
        ];
    }

    /**
     * İhtilaf / İtiraz Talebi Oluşturma
     */
    public function createDispute(int $userId, int $lotId, string $topic, string $description): array
    {
        $lot = $this->dataStore->getItem('lots', 'id', $lotId);
        $dispute = $this->dataStore->insertItem('disputes', [
            'talepNo'              => 'DISP-' . date('Y') . '-' . mt_rand(10, 99),
            'lotId'                => $lotId,
            'lotKodu'              => $lot['lotKodu'] ?? "LOT-{$lotId}",
            'siparisId'            => null,
            'olusturanKullaniciId' => $userId,
            'konuTipi'             => $topic,
            'aciklama'             => $description,
            'durum'                => 'INCELENIYOR',
            'olusturmaTarihi'      => date('c'),
        ]);

        return [
            'success' => true,
            'message' => "İhtilaf/itiraz talebiniz alındı ve hukuk/denetim kuruluna iletildi.",
            'dispute' => $dispute,
        ];
    }

    /**
     * Canlı Sohbet Mesajı Gönderme
     */
    public function sendChatMessage(int $sessionId, int $senderId, string $senderName, string $text): array
    {
        $session = $this->dataStore->getItem('liveChatSessions', 'id', $sessionId);
        if (!$session) {
            return ['success' => false, 'message' => "Sohbet oturumu bulunamadı."];
        }

        $messages = $session['mesajlar'] ?? [];
        $messages[] = [
            'gonderenId' => $senderId,
            'gonderenAd' => $senderName,
            'icerik'     => $text,
            'zaman'      => date('Y-m-d H:i:s'),
        ];

        $this->dataStore->updateItem('liveChatSessions', 'id', $sessionId, [
            'mesajlar' => $messages,
        ]);

        return ['success' => true, 'message' => "Mesaj iletildi."];
    }

    /**
     * Satıcı: İlan İptal Etme
     */
    public function cancelListing(int $listingId, int $sellerId, string $reason): array
    {
        $listing = $this->dataStore->getItem('listings', 'id', $listingId);
        if (!$listing) {
            return ['success' => false, 'message' => "İlan bulunamadı."];
        }

        if ((int)($listing['saticiId'] ?? 0) !== $sellerId && $sellerId !== 1) { // 1 = Admin
            return ['success' => false, 'message' => "Bu ilanı iptal etme yetkiniz bulunmuyor."];
        }

        $this->dataStore->updateItem('listings', 'id', $listingId, [
            'durum'       => 'IPTAL_EDILDI',
            'iptalSebebi' => $reason,
            'iptalZamani' => date('c'),
        ]);

        return [
            'success' => true,
            'message' => "İlan (#{$listing['ilanKodu']}) başarıyla iptal edildi ve yayından kaldırıldı.",
        ];
    }

    /**
     * Satıcı: İlan Düzenleme
     */
    public function editListing(int $listingId, int $sellerId, array $data): array
    {
        $listing = $this->dataStore->getItem('listings', 'id', $listingId);
        if (!$listing) {
            return ['success' => false, 'message' => "İlan bulunamadı."];
        }

        $productId = (int)($listing['urunId'] ?? 0);
        $product = $this->dataStore->getItem('products', 'id', $productId);
        if (!$product) {
            return ['success' => false, 'message' => "Ürün kaydı bulunamadı."];
        }

        $updates = [];
        if (!empty($data['baslik'])) {
            $updates['baslik'] = trim((string)$data['baslik']);
        }
        if (!empty($data['aciklama'])) {
            $updates['aciklama'] = trim((string)$data['aciklama']);
        }
        if (!empty($data['fizikselKondisyon'])) {
            $updates['fizikselKondisyon'] = trim((string)$data['fizikselKondisyon']);
        }

        if (!empty($updates)) {
            $this->dataStore->updateItem('products', 'id', $productId, $updates);
        }

        return [
            'success' => true,
            'message' => "İlan ve ürün detayları başarıyla güncellendi.",
        ];
    }

    /**
     * Satıcı: Bakiye Çekim / Payout Talebi
     */
    public function requestPayout(int $sellerId, float $amount, string $iban): array
    {
        if ($amount <= 0) {
            return ['success' => false, 'message' => "Geçerli bir çekim tutarı belirtiniz."];
        }

        $wallet = $this->dataStore->getItem('wallets', 'kullaniciId', $sellerId);
        if (!$wallet || ((float)($wallet['bakiye'] ?? 0) < $amount)) {
            return ['success' => false, 'message' => "Yetersiz bakiye. Çekilebilir tutarı aşamazsınız."];
        }

        $newBal = (float)$wallet['bakiye'] - $amount;
        $this->dataStore->updateItem('wallets', 'id', $wallet['id'], [
            'bakiye'        => $newBal,
            'sonGuncelleme' => date('c'),
        ]);

        return [
            'success' => true,
            'message' => "₺" . number_format($amount, 2, ',', '.') . " tutarındaki transfer talebiniz {$iban} hesabına iletildi. Finans onayı sonrası hesabınıza aktarılacaktır.",
        ];
    }

    /**
     * Eksper: Revize / Ek Belge Talep Etme
     */
    public function requestAppraisalRevision(int $expertId, int $requestId, string $reason): array
    {
        $req = $this->dataStore->getItem('appraisalRequests', 'id', $requestId);
        if (!$req) {
            return ['success' => false, 'message' => "Ekspertiz talebi bulunamadı."];
        }

        $this->dataStore->updateItem('appraisalRequests', 'id', $requestId, [
            'durum'        => 'REVIZE_ISTENDI',
            'revizeNedeni' => $reason,
            'not'          => "Uzman revize talep etti: {$reason}",
        ]);

        return [
            'success' => true,
            'message' => "Satıcıya ek belge / kondisyon revize talebi başarıyla iletildi.",
        ];
    }

    /**
     * Eksper: Üst Birime Eskale Etme
     */
    public function escalateAppraisal(int $expertId, int $requestId, string $reason): array
    {
        $req = $this->dataStore->getItem('appraisalRequests', 'id', $requestId);
        if (!$req) {
            return ['success' => false, 'message' => "Ekspertiz talebi bulunamadı."];
        }

        $this->dataStore->updateItem('appraisalRequests', 'id', $requestId, [
            'eskalasyon'      => true,
            'durum'           => 'ESKALE_EDILDI',
            'eskalasyonNedeni'=> $reason,
            'not'             => "Yüksek ihtisas heyetine eskale edildi: {$reason}",
        ]);

        return [
            'success' => true,
            'message' => "Dosya üst hakem kuruluna ve baş ekspere başarıyla eskale edildi.",
        ];
    }

    /**
     * Admin: Sistem Parametreleri Ayarlama
     */
    public function updateSystemParameters(array $data): array
    {
        $params = $this->dataStore->getCollection('systemParameters');
        if (empty($params)) {
            $params = [];
        }

        $antiSnipingSeconds = isset($data['anti_sniping_seconds']) ? (int)$data['anti_sniping_seconds'] : 60;
        $minBidStep = isset($data['min_bid_step']) ? (float)$data['min_bid_step'] : 1500;
        $sellerComm = isset($data['seller_commission']) ? (float)$data['seller_commission'] : 8.5;
        $buyerPremium = isset($data['buyer_premium']) ? (float)$data['buyer_premium'] : 12.0;

        $updatedParams = array_merge($params, [
            'antiSnipingDuration' => $antiSnipingSeconds,
            'defaultMinBidStep'   => $minBidStep,
            'sellerCommission'    => $sellerComm,
            'buyerPremium'        => $buyerPremium,
            'lastUpdated'         => date('c'),
        ]);

        $this->dataStore->saveCollection('systemParameters', $updatedParams);

        return [
            'success' => true,
            'message' => "Sistem parametreleri (Son 1 dk kuralı: {$antiSnipingSeconds}sn, Min Pey: ₺{$minBidStep}, Komisyon: %{$sellerComm}) başarıyla güncellendi.",
        ];
    }

    /**
     * Admin: Destek veya Ekspertiz Personel Hesabı Oluşturma
     */
    public function createStaffAccount(array $data): array
    {
        $name = trim($data['ad'] ?? '');
        $email = trim($data['email'] ?? '');
        $role = $data['rol'] ?? 'CANLI_DESTEK';

        if ($name === '' || $email === '') {
            return ['success' => false, 'message' => "Ad ve e-posta zorunludur."];
        }

        $existing = $this->dataStore->getItem('users', 'email', $email);
        if ($existing) {
            return ['success' => false, 'message' => "Bu e-posta adresiyle bir hesap zaten mevcut."];
        }

        $user = $this->dataStore->insertItem('users', [
            'ad'           => $name,
            'soyad'        => '(Yetkili)',
            'email'        => $email,
            'telefon'      => '+90 532 ' . mt_rand(100, 999) . ' 0000',
            'rol'          => $role,
            'durum'        => 'AKTIF',
            'sifreHash'    => password_hash('staff123', PASSWORD_DEFAULT),
            'anonimKod'    => 'STF-' . mt_rand(10, 99),
            'kycDogrulama' => ['dogrulandiMi' => true, 'onayTarihi' => date('c')],
            'kayitTarihi'  => date('c'),
        ]);

        return [
            'success' => true,
            'message' => "Yeni personel hesabı ({$name} - {$role}) başarıyla tanımlandı ve yetkilendirildi.",
            'user'    => $user,
        ];
    }

    /**
     * Kargo Teslimat Durumu Güncelleme
     */
    public function updateShipmentStatus(int $shipmentId, string $newStatus, string $note): array
    {
        $shipments = $this->dataStore->getCollection('shipments');
        $found = false;
        foreach ($shipments as &$s) {
            if ((int)($s['id'] ?? 0) === $shipmentId || ($s['kargoKodu'] ?? '') === (string)$shipmentId) {
                $s['kargoDurumu'] = $newStatus;
                $s['gecmis'] = $s['gecmis'] ?? [];
                $s['gecmis'][] = [
                    'tarih'    => date('Y-m-d H:i:s'),
                    'durum'    => $newStatus,
                    'aciklama' => $note,
                ];
                $found = true;
                break;
            }
        }
        unset($s);

        if ($found) {
            $this->dataStore->saveCollection('shipments', $shipments);
            return [
                'success' => true,
                'message' => "Kargo durumu '{$newStatus}' olarak güncellendi ve alıcıya bildirim gönderildi.",
            ];
        }

        return ['success' => false, 'message' => "Kargo kaydı bulunamadı."];
    }
}
