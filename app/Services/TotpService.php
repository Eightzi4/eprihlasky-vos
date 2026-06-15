<?php

namespace App\Services;

use App\Models\Admin;

class TotpService
{
    const RECOVERY_CODES_COUNT = 8;
    const TIME_STEP = 30;
    const CODE_DIGITS = 6;
    const WINDOW = 1;

    public function generateSecret(): string
    {
        $bytes = random_bytes(20);
        return $this->base32Encode($bytes);
    }

    public function generateQrCodeUrl(string $secret, string $email, string $issuer): string
    {
        $label = rawurlencode($issuer . ':' . $email);
        $params = http_build_query([
            'secret' => $secret,
            'issuer' => $issuer,
            'algorithm' => 'SHA1',
            'digits' => self::CODE_DIGITS,
            'period' => self::TIME_STEP,
        ], '', '&', PHP_QUERY_RFC3986);

        return 'otpauth://totp/' . $label . '?' . $params;
    }

    public function verify(string $secret, string $code): bool
    {
        $code = preg_replace('/\s+/', '', $code);

        if (! is_numeric($code) || strlen($code) !== self::CODE_DIGITS) {
            return false;
        }

        $secretBytes = $this->base32Decode($secret);

        if ($secretBytes === null) {
            return false;
        }

        $timeWindow = (int) floor(time() / self::TIME_STEP);

        for ($i = -self::WINDOW; $i <= self::WINDOW; $i++) {
            if (hash_equals($this->generateCode($secretBytes, $timeWindow + $i), $code)) {
                return true;
            }
        }

        return false;
    }

    public function generateRecoveryCodes(): array
    {
        $codes = [];
        for ($i = 0; $i < self::RECOVERY_CODES_COUNT; $i++) {
            $codes[] = $this->randomRecoveryCode();
        }
        return $codes;
    }

    public function generateRecoveryCodesWithHashes(): array
    {
        $plain = $this->generateRecoveryCodes();
        $hashed = array_map(fn(string $code) => password_hash($code, PASSWORD_BCRYPT), $plain);

        return [
            'plain' => $plain,
            'hashed' => $hashed,
        ];
    }

    public function verifyRecoveryCode(Admin $admin, string $code): array|false
    {
        $stored = $admin->recoveryCodes();

        if (empty($stored)) {
            return false;
        }

        $code = preg_replace('/\s+/', '', $code);

        foreach ($stored as $i => $hash) {
            if (password_verify($code, $hash)) {
                array_splice($stored, $i, 1);
                return $stored;
            }
        }

        return false;
    }

    private function randomRecoveryCode(): string
    {
        $chars = '23456789ABCDEFGHJKLMNPQRSTUVWXYZ';
        $length = strlen($chars);

        $segments = [];
        for ($s = 0; $s < 3; $s++) {
            $segment = '';
            for ($i = 0; $i < 4; $i++) {
                $segment .= $chars[random_int(0, $length - 1)];
            }
            $segments[] = $segment;
        }

        return implode('-', $segments);
    }

    private function generateCode(string $secretBytes, int $timeWindow): string
    {
        $time = pack('J', $timeWindow);
        $hash = hash_hmac('sha1', $time, $secretBytes, true);
        $offset = ord($hash[19]) & 0x0F;
        $binary = (
            ((ord($hash[$offset]) & 0x7F) << 24) |
            ((ord($hash[$offset + 1]) & 0xFF) << 16) |
            ((ord($hash[$offset + 2]) & 0xFF) << 8) |
            (ord($hash[$offset + 3]) & 0xFF)
        );

        $code = $binary % 10 ** self::CODE_DIGITS;

        return str_pad((string) $code, self::CODE_DIGITS, '0', STR_PAD_LEFT);
    }

    private function base32Encode(string $data): string
    {
        $alphabet = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';
        $binary = '';
        $length = strlen($data);

        for ($i = 0; $i < $length; $i++) {
            $binary .= str_pad(decbin(ord($data[$i])), 8, '0', STR_PAD_LEFT);
        }

        $result = '';
        foreach (str_split($binary, 5) as $chunk) {
            $chunk = str_pad($chunk, 5, '0');
            $result .= $alphabet[bindec($chunk)];
        }

        return $result;
    }

    private function base32Decode(string $data): ?string
    {
        $alphabet = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';
        $flip = array_flip(str_split($alphabet));

        $data = strtoupper(preg_replace('/\s+/', '', $data));

        $binary = '';
        $length = strlen($data);

        for ($i = 0; $i < $length; $i++) {
            $char = $data[$i];
            if (! isset($flip[$char])) {
                return null;
            }
            $binary .= str_pad(decbin($flip[$char]), 5, '0', STR_PAD_LEFT);
        }

        $result = '';
        $binaryLength = strlen($binary);

        for ($i = 0; $i + 8 <= $binaryLength; $i += 8) {
            $result .= chr(bindec(substr($binary, $i, 8)));
        }

        return $result;
    }
}
