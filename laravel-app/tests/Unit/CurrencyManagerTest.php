<?php

namespace Tests\Unit;

use App\Support\CurrencyManager;
use Tests\TestCase;

class CurrencyManagerTest extends TestCase
{
    public function test_formats_converted_ngn_amounts_for_supported_currency(): void
    {
        config(['currency.ngn_per_unit.GBP' => 2000]);

        $formatted = app(CurrencyManager::class)->formatFromNgn(10000, 'GBP');

        $this->assertSame("\u{00A3}5.00", $formatted);
    }

    public function test_unsupported_or_unrated_currency_falls_back_to_base_currency(): void
    {
        config([
            'currency.base' => 'NGN',
            'currency.ngn_per_unit.EUR' => null,
        ]);

        $manager = app(CurrencyManager::class);

        $this->assertSame('NGN', $manager->effectiveCurrency('EUR'));
        $this->assertSame('NGN', $manager->effectiveCurrency('XYZ'));
    }
}
