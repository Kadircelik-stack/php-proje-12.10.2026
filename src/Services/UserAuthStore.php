<?php
declare(strict_types=1);

namespace Martello\Services;

/**
 * MARTELLO — Bağımsız Sanal Kullanıcı Kimlik ve Rol Deposu (storage/users_auth.json)
 *
 * Veritabanı sunucusu eklenene kadar tüm kullanıcı kimlik bilgileri, Argon2id/bcrypt hash'li
 * parolaları ve yetki rolleri (ADMIN, SATICI, ALICI, EKSPERTIZ, CANLI_DESTEK) bu ayrı
 * dosyada saklanır. İleride MySQL/PostgreSQL geçişinde yalnızca bu katman değiştirilir.
 */
class UserAuthStore
{
    private string $authFilePath;

    /** @var array<string, string> Rol -> Çalışma Alanı (Panel) Eşlemesi */
    private const ROLE_TO_PANEL = [
        'ADMIN'        => 'admin',
        'SATICI'       => 'seller',
        'ALICI'        => 'buyer',
        'EKSPERTIZ'    => 'appraiser',
        'CANLI_DESTEK' => 'support',
    ];

    /** @var array<string, string> Rol -> Türkçe Etiket */
    private const ROLE_LABELS = [
        'ADMIN'        => 'ADMIN',
        'SATICI'       => 'SATICI',
        'ALICI'        => 'ALICI',
        'EKSPERTIZ'    => 'EKSPERTİZ',
        'CANLI_DESTEK' => 'CANLI DESTEK',
    ];

    public function __construct(string $authFilePath)
    {
        $this->authFilePath = $authFilePath;
        $this->ensureInitialized();
    }

    public static function mapRoleToPanel(string $role): string
    {
        return self::ROLE_TO_PANEL[strtoupper(trim($role))] ?? 'buyer';
    }

    public static function getRoleLabel(string $role): string
    {
        return self::ROLE_LABELS[strtoupper(trim($role))] ?? strtoupper(trim($role));
    }

    public static function resolveAvatar(array $user): string
    {
        $id = (int)($user['id'] ?? 0);
        $email = strtolower(trim((string)($user['email'] ?? '')));
        $name = mb_strtolower(trim((string)($user['ad'] ?? '') . ' ' . (string)($user['soyad'] ?? '') . ' ' . (string)($user['adSoyad'] ?? '')));

        if ($id === 8 || str_contains($email, 'sewal') || str_contains($email, 'kandilci') || str_contains($name, 'şevval') || str_contains($name, 'sevval') || str_contains($name, 'rümeysa')) {
            return 'public/assets/images/avatar_sevval.jpg?v=4';
        }
        if (!empty($user['avatar']) && is_string($user['avatar'])) {
            return $user['avatar'];
        }
        if ($id === 1 || str_contains($email, 'admin') || str_contains($name, 'ahmet')) {
            return 'public/assets/images/avatar_ahmet.jpg';
        }
        if ($id === 2 || str_contains($email, 'mehmet') || str_contains($name, 'mehmet')) {
            return 'public/assets/images/avatar_mehmet.jpg';
        }
        if ($id === 3 || str_contains($email, 'zeynep') || str_contains($name, 'zeynep')) {
            return 'public/assets/images/avatar_zeynep.jpg';
        }
        if ($id === 4 || str_contains($email, 'burak') || str_contains($name, 'burak')) {
            return 'public/assets/images/avatar_burak.jpg';
        }
        if ($id === 5 || str_contains($email, 'elif') || str_contains($name, 'elif')) {
            return 'public/assets/images/avatar_elif.jpg';
        }
        if ($id === 7 || str_contains($email, 'esra') || str_contains($name, 'esra')) {
            return 'public/assets/images/avatar_esra.jpg';
        }
        return 'public/assets/images/avatar_emre.jpg';
    }

    private function ensureInitialized(): void
    {
        if (file_exists($this->authFilePath)) {
            $raw = (string)file_get_contents($this->authFilePath);
            $decoded = json_decode($raw, true);
            if (is_array($decoded) && !empty($decoded['accounts'])) {
                return;
            }
        }

        $dir = dirname($this->authFilePath);
        if (!is_dir($dir)) {
            mkdir($dir, 0777, true);
        }

        $seedAccounts = [
            [
                'id'           => 1,
                'ad'           => 'Ahmet',
                'soyad'        => 'Yılmaz',
                'email'        => 'admin@muzayede.com',
                'telefon'      => '+905551112233',
                'rol'          => 'ADMIN',
                'durum'        => 'AKTIF',
                'parolaHash'   => $this->hashPassword('Admin123!'),
                'olusturma'    => '2026-01-01T10:00:00+03:00',
                'sonGiris'     => null,
                'yetkiKaynagi' => 'SISTEM_KURUCU',
            ],
            [
                'id'           => 2,
                'ad'           => 'Mehmet',
                'soyad'        => 'Kaya',
                'email'        => 'mehmet@antik.com',
                'telefon'      => '+905322223344',
                'rol'          => 'SATICI',
                'durum'        => 'AKTIF',
                'parolaHash'   => $this->hashPassword('Satici123!'),
                'olusturma'    => '2026-09-15T14:20:00+03:00',
                'sonGiris'     => null,
                'yetkiKaynagi' => 'KULLANICI_KAYDI',
            ],
            [
                'id'           => 3,
                'ad'           => 'Zeynep',
                'soyad'        => 'Demir',
                'email'        => 'zeynep@koleksiyon.com',
                'telefon'      => '+905443334455',
                'rol'          => 'ALICI',
                'durum'        => 'AKTIF',
                'parolaHash'   => $this->hashPassword('Alici123!'),
                'olusturma'    => '2026-09-20T11:30:00+03:00',
                'sonGiris'     => null,
                'yetkiKaynagi' => 'KULLANICI_KAYDI',
            ],
            [
                'id'           => 4,
                'ad'           => 'Burak',
                'soyad'        => 'Şahin',
                'email'        => 'burak@ekspertiz.com',
                'telefon'      => '+905334445566',
                'rol'          => 'EKSPERTIZ',
                'durum'        => 'AKTIF',
                'parolaHash'   => $this->hashPassword('Eksper123!'),
                'olusturma'    => '2026-09-10T09:00:00+03:00',
                'sonGiris'     => null,
                'yetkiKaynagi' => 'ADMIN_ATAMASI',
            ],
            [
                'id'           => 5,
                'ad'           => 'Elif',
                'soyad'        => 'Çelik',
                'email'        => 'elif@destek.com',
                'telefon'      => '+905556667788',
                'rol'          => 'CANLI_DESTEK',
                'durum'        => 'AKTIF',
                'parolaHash'   => $this->hashPassword('Destek123!'),
                'olusturma'    => '2026-09-10T09:15:00+03:00',
                'sonGiris'     => null,
                'yetkiKaynagi' => 'ADMIN_ATAMASI',
            ],
            [
                'id'           => 6,
                'ad'           => 'Emre',
                'soyad'        => 'Karahan',
                'email'        => 'emre.k@koleksiyon.com',
                'telefon'      => '+905327778899',
                'rol'          => 'ALICI',
                'durum'        => 'AKTIF',
                'parolaHash'   => $this->hashPassword('Alici123!'),
                'olusturma'    => '2026-09-25T16:10:00+03:00',
                'sonGiris'     => null,
                'yetkiKaynagi' => 'KULLANICI_KAYDI',
            ],
        ];

        $this->saveStore([
            'metadata' => [
                'storeName'     => 'Martello_Virtual_User_Auth_Store',
                'description'   => 'Ayrı sanal kullanıcı kimlik ve rol doğrulama deposu (Argon2id/bcrypt hash)',
                'schemaVersion' => '1.0.0',
                'updatedAt'     => date('c'),
            ],
            'accounts' => $seedAccounts,
        ]);
    }

    private function hashPassword(string $plain): string
    {
        if (defined('PASSWORD_ARGON2ID')) {
            return password_hash($plain, PASSWORD_ARGON2ID, [
                'memory_cost' => 16384,
                'time_cost'   => 2,
                'threads'     => 1,
            ]);
        }
        return password_hash($plain, PASSWORD_BCRYPT, ['cost' => 10]);
    }

    private function readStore(): array
    {
        $raw = file_exists($this->authFilePath) ? (string)file_get_contents($this->authFilePath) : '';
        $data = json_decode($raw, true);
        return is_array($data) ? $data : ['metadata' => [], 'accounts' => []];
    }

    private function saveStore(array $data): void
    {
        $data['metadata']['updatedAt'] = date('c');
        $json = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        $tmp = $this->authFilePath . '.tmp.' . bin2hex(random_bytes(4));
        file_put_contents($tmp, (string)$json, LOCK_EX);
        if (!@rename($tmp, $this->authFilePath)) {
            copy($tmp, $this->authFilePath);
            @unlink($tmp);
        }
    }

    public function getAllAccounts(): array
    {
        return $this->readStore()['accounts'] ?? [];
    }

    public function findByEmail(string $email): ?array
    {
        $needle = strtolower(trim($email));
        foreach ($this->getAllAccounts() as $acc) {
            if (strtolower(trim((string)($acc['email'] ?? ''))) === $needle) {
                return $acc;
            }
        }
        return null;
    }

    public function findById(int $id): ?array
    {
        foreach ($this->getAllAccounts() as $acc) {
            if ((int)($acc['id'] ?? 0) === $id) {
                return $acc;
            }
        }
        return null;
    }

    /**
     * Gerçek Kullanıcı Girişi (E-posta + Hash'li Şifre Doğrulama)
     */
    public function authenticate(string $email, string $password): array
    {
        $needle = strtolower(trim($email));
        if ($needle === '' || $password === '') {
            return ['success' => false, 'message' => 'Lütfen e-posta adresinizi ve şifrenizi giriniz.'];
        }

        $store = $this->readStore();
        $foundIdx = null;
        foreach ($store['accounts'] as $idx => $acc) {
            if (strtolower(trim((string)($acc['email'] ?? ''))) === $needle) {
                $foundIdx = $idx;
                break;
            }
        }

        if ($foundIdx === null) {
            // Timing-Attack & User Enumeration Koruması: Sabit zamanlı dummy hash doğrulaması
            password_verify($password, '$2y$10$usesomesillystringfore7hnbRJHxXVLeakoG8K30oukPsA.ztMG');
            return ['success' => false, 'message' => 'E-posta adresi veya şifre hatalı.'];
        }

        $acc = $store['accounts'][$foundIdx];
        if (in_array(strtoupper((string)($acc['durum'] ?? 'AKTIF')), ['ASKIDA', 'BANLI', 'ENGELENMIS'], true)) {
            return ['success' => false, 'message' => 'Hesabınız askıya alınmıştır. Giriş yetkiniz durdurulmuştur.'];
        }

        // Brute-Force (T1110) Geçici Hesap Kilitleme Kontrolü (5 hatalı denemede 15 dk kilit)
        $lockUntil = !empty($acc['kilitBitisZamani']) ? strtotime((string)$acc['kilitBitisZamani']) : 0;
        if ($lockUntil > time()) {
            $remMin = max(1, (int)ceil(($lockUntil - time()) / 60));
            return [
                'success' => false,
                'message' => "Çok sayıda başarısız giriş denemesi tespit edildi. Güvenliğiniz için hesabınız {$remMin} dakika süreyle geçici olarak kilitlenmiştir.",
            ];
        }

        $hash = (string)($acc['parolaHash'] ?? '');
        if ($hash === '' || !password_verify($password, $hash)) {
            $failCount = (int)($acc['hataliGirisSayisi'] ?? 0) + 1;
            $store['accounts'][$foundIdx]['hataliGirisSayisi'] = $failCount;
            if ($failCount >= 5) {
                $store['accounts'][$foundIdx]['kilitBitisZamani'] = date('c', time() + 900);
            }
            $this->saveStore($store);
            return ['success' => false, 'message' => 'E-posta adresi veya şifre hatalı.'];
        }

        $store['accounts'][$foundIdx]['hataliGirisSayisi'] = 0;
        $store['accounts'][$foundIdx]['kilitBitisZamani'] = null;
        $store['accounts'][$foundIdx]['sonGiris'] = date('c');
        $this->saveStore($store);

        $role = strtoupper((string)($acc['rol'] ?? 'ALICI'));
        $panel = self::mapRoleToPanel($role);

        return [
            'success' => true,
            'message' => "Hoş geldiniz, {$acc['ad']} {$acc['soyad']} (" . self::getRoleLabel($role) . "). Çalışma alanınıza yönlendirildiniz.",
            'panel'   => $panel,
            'account' => [
                'id'       => (int)$acc['id'],
                'ad'       => (string)$acc['ad'],
                'soyad'    => (string)$acc['soyad'],
                'adSoyad'  => trim(($acc['ad'] ?? '') . ' ' . ($acc['soyad'] ?? '')),
                'email'    => (string)$acc['email'],
                'telefon'  => (string)($acc['telefon'] ?? ''),
                'rol'      => $role,
                'rolEtiket'=> self::getRoleLabel($role),
                'panel'    => $panel,
                'avatar'   => self::resolveAvatar($acc),
            ],
        ];
    }

    /**
     * Dış Kullanıcı Kaydı (Sadece ALICI ve SATICI — Yetkili roller sadece Admin tarafından atanır)
     */
    public function registerPublicAccount(int $userId, string $ad, string $soyad, string $email, string $telefon, string $requestedRole, string $plainPassword): array
    {
        $role = strtoupper(trim($requestedRole));
        if (!in_array($role, ['ALICI', 'SATICI'], true)) {
            return [
                'success' => false,
                'message' => 'Kayıt formundan yalnızca Alıcı veya Satıcı hesabı açılabilir. Yetkili personel rolleri Admin tarafından atanır.',
            ];
        }

        if (strlen($plainPassword) < 6) {
            return ['success' => false, 'message' => 'Şifreniz en az 6 karakter uzunluğunda olmalıdır.'];
        }

        if ($this->findByEmail($email) !== null) {
            return ['success' => false, 'message' => 'Bu e-posta adresi ile zaten kayıtlı bir hesap bulunmaktadır.'];
        }

        $store = $this->readStore();
        $newRecord = [
            'id'           => $userId,
            'ad'           => trim($ad),
            'soyad'        => trim($soyad),
            'email'        => strtolower(trim($email)),
            'telefon'      => trim($telefon),
            'rol'          => $role,
            'durum'        => 'AKTIF',
            'parolaHash'   => $this->hashPassword($plainPassword),
            'olusturma'    => date('c'),
            'sonGiris'     => date('c'),
            'yetkiKaynagi' => 'KULLANICI_KAYDI',
        ];

        $store['accounts'][] = $newRecord;
        $this->saveStore($store);

        $panel = self::mapRoleToPanel($role);
        return [
            'success' => true,
            'panel'   => $panel,
            'account' => [
                'id'       => $userId,
                'ad'       => $newRecord['ad'],
                'soyad'    => $newRecord['soyad'],
                'adSoyad'  => trim($newRecord['ad'] . ' ' . $newRecord['soyad']),
                'email'    => $newRecord['email'],
                'telefon'  => $newRecord['telefon'],
                'rol'      => $role,
                'rolEtiket'=> self::getRoleLabel($role),
                'panel'    => $panel,
                'avatar'   => self::resolveAvatar($acc),
            ],
        ];
    }

    /**
     * Admin Ataması ile Yetkili Hesap Ekleme veya Rol/Şifre Güncelleme
     */
    public function upsertStaffOrRole(int $userId, string $ad, string $soyad, string $email, string $role, ?string $plainPassword = null): void
    {
        $role = strtoupper(trim($role));
        $store = $this->readStore();
        $updated = false;

        foreach ($store['accounts'] as &$acc) {
            if ((int)($acc['id'] ?? 0) === $userId || strtolower((string)($acc['email'] ?? '')) === strtolower(trim($email))) {
                $acc['ad']           = trim($ad) !== '' ? trim($ad) : $acc['ad'];
                $acc['soyad']        = trim($soyad) !== '' ? trim($soyad) : $acc['soyad'];
                $acc['rol']          = $role;
                $acc['yetkiKaynagi'] = 'ADMIN_ATAMASI';
                if ($plainPassword !== null && $plainPassword !== '') {
                    $acc['parolaHash'] = $this->hashPassword($plainPassword);
                }
                $updated = true;
                break;
            }
        }
        unset($acc);

        if (!$updated) {
            $store['accounts'][] = [
                'id'           => $userId,
                'ad'           => trim($ad),
                'soyad'        => trim($soyad),
                'email'        => strtolower(trim($email)),
                'telefon'      => '',
                'rol'          => $role,
                'durum'        => 'AKTIF',
                'parolaHash'   => $this->hashPassword($plainPassword ?: 'Staff123!'),
                'olusturma'    => date('c'),
                'sonGiris'     => null,
                'yetkiKaynagi' => 'ADMIN_ATAMASI',
            ];
        }

        $this->saveStore($store);
    }

    public function updatePassword(int $userId, string $newPlainPassword): void
    {
        if (strlen($newPlainPassword) < 6) {
            return;
        }
        $store = $this->readStore();
        foreach ($store['accounts'] as &$acc) {
            if ((int)($acc['id'] ?? 0) === $userId) {
                $acc['parolaHash'] = $this->hashPassword($newPlainPassword);
                break;
            }
        }
        unset($acc);
        $this->saveStore($store);
    }
}
