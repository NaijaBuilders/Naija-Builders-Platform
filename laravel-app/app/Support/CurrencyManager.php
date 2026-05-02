<?php

namespace App\Support;

use Illuminate\Http\Request;

class CurrencyManager
{
    /**
     * Major buyer-facing currencies supported by the platform.
     *
     * @return array<string, array{name: string, symbol: string, locale: string}>
     */
    public function currencies(): array
    {
        return [
            'NGN' => ['name' => 'Nigerian Naira', 'symbol' => '₦', 'locale' => 'en-NG'],
            'GBP' => ['name' => 'British Pound', 'symbol' => '£', 'locale' => 'en-GB'],
            'USD' => ['name' => 'US Dollar', 'symbol' => '$', 'locale' => 'en-US'],
            'EUR' => ['name' => 'Euro', 'symbol' => '€', 'locale' => 'en-IE'],
            'CAD' => ['name' => 'Canadian Dollar', 'symbol' => 'C$', 'locale' => 'en-CA'],
            'AUD' => ['name' => 'Australian Dollar', 'symbol' => 'A$', 'locale' => 'en-AU'],
            'ZAR' => ['name' => 'South African Rand', 'symbol' => 'R', 'locale' => 'en-ZA'],
            'GHS' => ['name' => 'Ghanaian Cedi', 'symbol' => 'GH₵', 'locale' => 'en-GH'],
            'KES' => ['name' => 'Kenyan Shilling', 'symbol' => 'KSh', 'locale' => 'en-KE'],
            'CNY' => ['name' => 'Chinese Yuan', 'symbol' => '¥', 'locale' => 'zh-CN'],
            'JPY' => ['name' => 'Japanese Yen', 'symbol' => '¥', 'locale' => 'ja-JP'],
            'INR' => ['name' => 'Indian Rupee', 'symbol' => '₹', 'locale' => 'en-IN'],
            'AED' => ['name' => 'UAE Dirham', 'symbol' => 'د.إ', 'locale' => 'en-AE'],
            'SAR' => ['name' => 'Saudi Riyal', 'symbol' => '﷼', 'locale' => 'en-SA'],
            'CHF' => ['name' => 'Swiss Franc', 'symbol' => 'CHF', 'locale' => 'de-CH'],
            'SEK' => ['name' => 'Swedish Krona', 'symbol' => 'kr', 'locale' => 'sv-SE'],
            'NOK' => ['name' => 'Norwegian Krone', 'symbol' => 'kr', 'locale' => 'nb-NO'],
            'DKK' => ['name' => 'Danish Krone', 'symbol' => 'kr', 'locale' => 'da-DK'],
            'NZD' => ['name' => 'New Zealand Dollar', 'symbol' => 'NZ$', 'locale' => 'en-NZ'],
            'SGD' => ['name' => 'Singapore Dollar', 'symbol' => 'S$', 'locale' => 'en-SG'],
            'HKD' => ['name' => 'Hong Kong Dollar', 'symbol' => 'HK$', 'locale' => 'en-HK'],
            'MXN' => ['name' => 'Mexican Peso', 'symbol' => 'Mex$', 'locale' => 'es-MX'],
            'BRL' => ['name' => 'Brazilian Real', 'symbol' => 'R$', 'locale' => 'pt-BR'],
        ];
    }

    public function resolveForRequest(Request $request): string
    {
        $settings = $request->hasSession()
            ? (array) $request->session()->get('app_settings', [])
            : [];
        $mode = (string) ($settings['currency_mode'] ?? 'auto');
        $manualCurrency = (string) ($settings['currency'] ?? '');

        if ($mode === 'manual' && $this->isSupported($manualCurrency)) {
            return $manualCurrency;
        }

        $detectedCurrency = $this->detectFromRequest($request);
        if ($this->isSupported($detectedCurrency)) {
            return $detectedCurrency;
        }

        if ($this->isSupported($manualCurrency)) {
            return $manualCurrency;
        }

        return (string) config('currency.base', 'NGN');
    }

    public function formatFromNgn(float|int|string|null $amountInNgn, ?string $currency = null): string
    {
        $currencyCode = $this->effectiveCurrency((string) $currency);

        $amount = $this->convertFromNgn((float) ($amountInNgn ?? 0), $currencyCode);
        $details = $this->currencies()[$currencyCode];
        $decimals = in_array($currencyCode, ['JPY'], true) ? 0 : 2;

        return $details['symbol'] . number_format($amount, $decimals);
    }

    public function convertFromNgn(float $amountInNgn, string $currency): float
    {
        if ($currency === 'NGN') {
            return $amountInNgn;
        }

        $rate = $this->rateNgnPerUnit($currency);
        if ($rate <= 0) {
            return $amountInNgn;
        }

        return $amountInNgn / $rate;
    }

    public function effectiveCurrency(string $currency): string
    {
        if (!$this->isSupported($currency)) {
            return (string) config('currency.base', 'NGN');
        }

        if ($currency === 'NGN' || $this->rateNgnPerUnit($currency) > 0) {
            return $currency;
        }

        return (string) config('currency.base', 'NGN');
    }

    public function rateNgnPerUnit(string $currency): float
    {
        $rawRate = config('currency.ngn_per_unit.' . $currency);

        return is_numeric($rawRate) ? (float) $rawRate : 0.0;
    }

    public function isSupported(string $currency): bool
    {
        return array_key_exists($currency, $this->currencies());
    }

    private function detectFromRequest(Request $request): string
    {
        $country = strtoupper((string) (
            $request->headers->get('CF-IPCountry')
            ?: $request->headers->get('X-App-Country')
            ?: $request->headers->get('X-Country-Code')
        ));

        $countryCurrency = $this->currencyForCountry($country);
        if ($countryCurrency !== '') {
            return $countryCurrency;
        }

        $settings = $request->hasSession()
            ? (array) $request->session()->get('app_settings', [])
            : [];
        $timezone = (string) ($settings['detected_timezone'] ?? $settings['timezone'] ?? '');
        $timezoneCurrency = $this->currencyForTimezone($timezone);
        if ($timezoneCurrency !== '') {
            return $timezoneCurrency;
        }

        $legacyUser = $request->hasSession()
            ? (array) $request->session()->get('legacy_user', [])
            : [];
        $location = strtolower((string) ($legacyUser['location'] ?? ''));

        return $this->currencyForLocation($location);
    }

    private function currencyForCountry(string $country): string
    {
        return match ($country) {
            'NG' => 'NGN',
            'GB', 'UK' => 'GBP',
            'US' => 'USD',
            'IE', 'FR', 'DE', 'ES', 'IT', 'NL', 'BE', 'PT', 'FI', 'AT', 'GR', 'LU', 'MT', 'CY', 'EE', 'LV', 'LT', 'SI', 'SK' => 'EUR',
            'CA' => 'CAD',
            'AU' => 'AUD',
            'ZA' => 'ZAR',
            'GH' => 'GHS',
            'KE' => 'KES',
            'CN' => 'CNY',
            'JP' => 'JPY',
            'IN' => 'INR',
            'AE' => 'AED',
            'SA' => 'SAR',
            'CH', 'LI' => 'CHF',
            'SE' => 'SEK',
            'NO' => 'NOK',
            'DK' => 'DKK',
            'NZ' => 'NZD',
            'SG' => 'SGD',
            'HK' => 'HKD',
            'MX' => 'MXN',
            'BR' => 'BRL',
            default => '',
        };
    }

    private function currencyForTimezone(string $timezone): string
    {
        if (str_starts_with($timezone, 'Africa/Lagos')) {
            return 'NGN';
        }

        if (str_starts_with($timezone, 'Europe/London')) {
            return 'GBP';
        }

        if (str_starts_with($timezone, 'America/')) {
            return 'USD';
        }

        if (str_starts_with($timezone, 'Europe/')) {
            return 'EUR';
        }

        return '';
    }

    private function currencyForLocation(string $location): string
    {
        if ($location === '') {
            return '';
        }

        if (str_contains($location, 'nigeria') || str_contains($location, 'lagos') || str_contains($location, 'abuja')) {
            return 'NGN';
        }

        if (str_contains($location, 'united kingdom') || str_contains($location, 'uk') || str_contains($location, 'london')) {
            return 'GBP';
        }

        if (str_contains($location, 'united states') || str_contains($location, 'usa') || str_contains($location, 'america')) {
            return 'USD';
        }

        if (str_contains($location, 'canada')) {
            return 'CAD';
        }

        if (str_contains($location, 'australia')) {
            return 'AUD';
        }

        if (str_contains($location, 'south africa')) {
            return 'ZAR';
        }

        if (str_contains($location, 'ghana')) {
            return 'GHS';
        }

        if (str_contains($location, 'kenya')) {
            return 'KES';
        }

        if (str_contains($location, 'china')) {
            return 'CNY';
        }

        if (str_contains($location, 'japan')) {
            return 'JPY';
        }

        if (str_contains($location, 'india')) {
            return 'INR';
        }

        if (str_contains($location, 'uae') || str_contains($location, 'united arab emirates') || str_contains($location, 'dubai')) {
            return 'AED';
        }

        if (str_contains($location, 'saudi')) {
            return 'SAR';
        }

        if (str_contains($location, 'switzerland')) {
            return 'CHF';
        }

        if (str_contains($location, 'sweden')) {
            return 'SEK';
        }

        if (str_contains($location, 'norway')) {
            return 'NOK';
        }

        if (str_contains($location, 'denmark')) {
            return 'DKK';
        }

        if (str_contains($location, 'new zealand')) {
            return 'NZD';
        }

        if (str_contains($location, 'singapore')) {
            return 'SGD';
        }

        if (str_contains($location, 'hong kong')) {
            return 'HKD';
        }

        if (str_contains($location, 'mexico')) {
            return 'MXN';
        }

        if (str_contains($location, 'brazil')) {
            return 'BRL';
        }

        if (
            str_contains($location, 'ireland')
            || str_contains($location, 'france')
            || str_contains($location, 'germany')
            || str_contains($location, 'spain')
            || str_contains($location, 'italy')
            || str_contains($location, 'netherlands')
            || str_contains($location, 'belgium')
            || str_contains($location, 'portugal')
            || str_contains($location, 'finland')
            || str_contains($location, 'austria')
            || str_contains($location, 'greece')
        ) {
            return 'EUR';
        }

        return '';
    }
}
