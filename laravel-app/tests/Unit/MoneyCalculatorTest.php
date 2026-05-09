<?php

namespace Tests\Unit;

use App\Support\MoneyCalculator;
use PHPUnit\Framework\TestCase;

class MoneyCalculatorTest extends TestCase
{
    public function test_line_totals_are_rounded_to_money_precision(): void
    {
        $this->assertSame(3000.0, MoneyCalculator::lineTotal('1000.00', 3));
        $this->assertSame(0.3, MoneyCalculator::lineTotal('0.10', 3));
    }

    public function test_totals_use_minor_unit_accumulation(): void
    {
        $this->assertSame(0.3, MoneyCalculator::total([0.1, 0.2]));
        $this->assertSame(12999.99, MoneyCalculator::total(['9999.99', '3000']));
    }

    public function test_percentages_are_safe_for_empty_denominators(): void
    {
        $this->assertSame(33.3, MoneyCalculator::percentage(1, 3));
        $this->assertSame(0.0, MoneyCalculator::percentage(5, 0));
    }
}
