<?php

namespace App\Services\Kyc;

use Illuminate\Contracts\Foundation\Application;
use RuntimeException;

class KycConfigurationValidator
{
    private const PROVIDERS = ['fake', 'prembly', 'dojah'];

    private const MODES = ['local', 'sandbox', 'production'];

    public function __construct(private readonly Application $app) {}

    public function validate(): void
    {
        $provider = strtolower((string) config('services.kyc.provider', 'fake'));
        $mode = strtolower((string) config('services.kyc.mode', 'local'));

        if (! in_array($provider, self::PROVIDERS, true)) {
            throw new RuntimeException('Invalid KYC provider configured.');
        }

        if (! in_array($mode, self::MODES, true)) {
            throw new RuntimeException('Invalid KYC mode configured.');
        }

        if (($this->app->environment('production') || $mode === 'production') && $provider === 'fake') {
            throw new RuntimeException('Production cannot run with the fake KYC provider.');
        }

        if ($provider === 'prembly' && trim((string) config('services.kyc.prembly.api_key', '')) === '') {
            throw new RuntimeException('Prembly KYC provider requires PREMBLY_API_KEY.');
        }
    }
}
