<?php

namespace App\Support;

class MoneyCalculator
{
    public static function amount(float|int|string|null $amount): float
    {
        return round((float) ($amount ?? 0), 2);
    }

    public static function lineTotal(float|int|string|null $unitPrice, int $quantity): float
    {
        return self::amount((float) ($unitPrice ?? 0) * max(1, $quantity));
    }

    /**
     * @param iterable<float|int|string|null> $amounts
     */
    public static function total(iterable $amounts): float
    {
        $minorUnits = 0;

        foreach ($amounts as $amount) {
            $minorUnits += (int) round((float) ($amount ?? 0) * 100);
        }

        return $minorUnits / 100;
    }

    public static function percentage(float|int $part, float|int $whole, int $precision = 1): float
    {
        if ((float) $whole <= 0.0) {
            return 0.0;
        }

        return round((((float) $part / (float) $whole) * 100), $precision);
    }
}
