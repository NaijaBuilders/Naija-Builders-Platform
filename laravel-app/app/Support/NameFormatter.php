<?php

namespace App\Support;

class NameFormatter
{
    public static function title(?string $value, string $fallback = 'User'): string
    {
        $value = trim((string) $value);

        if ($value === '') {
            return $fallback;
        }

        return mb_convert_case(mb_strtolower($value), MB_CASE_TITLE, 'UTF-8');
    }
}
