<?php

namespace App\Support\Security;

class SensitiveData
{
    public static function fingerprint(?string $value): ?string
    {
        $normalized = self::normalize($value);

        if ($normalized === '') {
            return null;
        }

        $key = (string) config('app.key');
        if ($key === '') {
            $key = 'local-naijabuilders-risk-key';
        }

        return hash_hmac('sha256', $normalized, $key);
    }

    public static function maskDigits(?string $value, int $visibleEnd = 4): ?string
    {
        $digits = preg_replace('/\D+/', '', (string) $value) ?: '';

        if ($digits === '') {
            return null;
        }

        $visible = substr($digits, -$visibleEnd);
        $hiddenCount = max(0, strlen($digits) - strlen($visible));

        return str_repeat('*', $hiddenCount).$visible;
    }

    public static function maskIp(?string $ip): ?string
    {
        $ip = trim((string) $ip);

        if ($ip === '') {
            return null;
        }

        if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
            $parts = explode('.', $ip);
            $parts[3] = '0';

            return implode('.', $parts).'/24';
        }

        if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
            $parts = explode(':', $ip);

            return implode(':', array_slice($parts, 0, 4)).'::/64';
        }

        return 'masked';
    }

    public static function maskToken(?string $value): ?string
    {
        $normalized = self::normalize($value);

        if ($normalized === '') {
            return null;
        }

        return substr($normalized, 0, 3).'...'.substr($normalized, -3);
    }

    private static function normalize(?string $value): string
    {
        return strtoupper(trim((string) $value));
    }
}
