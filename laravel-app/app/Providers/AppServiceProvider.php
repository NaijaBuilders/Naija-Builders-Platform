<?php

namespace App\Providers;

use App\Services\Kyc\Contracts\KycProvider;
use App\Services\Kyc\Providers\DojahKycProvider;
use App\Services\Kyc\Providers\FakeKycProvider;
use App\Services\Kyc\Providers\PremblyKycProvider;
use App\Support\CurrencyManager;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\ServiceProvider;
use RuntimeException;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(KycProvider::class, function ($app): KycProvider {
            return match ((string) config('services.kyc.provider', 'fake')) {
                'prembly' => $app->make(PremblyKycProvider::class),
                'dojah' => $app->make(DojahKycProvider::class),
                default => $app->make(FakeKycProvider::class),
            };
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if (config('database.default') !== 'mysql' && ! $this->app->environment('testing')) {
            throw new RuntimeException('This application is configured for MySQL only. Set DB_CONNECTION=mysql in your .env file.');
        }

        if ($this->app->runningInConsole() || request()->is('api/*')) {
            return;
        }

        // Fail fast if MySQL is not reachable so local/prod behavior stays consistent.
        DB::connection('mysql')->getPdo();

        $navbarMaterialSuggestions = DB::table('materials')
            ->where('status', 'active')
            ->whereNotNull('name')
            ->where('name', '<>', '')
            ->select('name')
            ->distinct()
            ->orderBy('name')
            ->limit(20)
            ->pluck('name')
            ->all();

        $currencyManager = app(CurrencyManager::class);

        view()->share('navbarMaterialSuggestions', $navbarMaterialSuggestions);
        view()->share('supportedCurrencies', $currencyManager->currencies());
        view()->share('formatMoney', fn ($amount, ?string $currency = null): string => $currencyManager->formatFromNgn(
            $amount,
            $currency ?: $currencyManager->effectiveCurrency($currencyManager->resolveForRequest(request()))
        ));

        view()->composer('*', function ($view) use ($currencyManager): void {
            $view->with('displayCurrency', $currencyManager->effectiveCurrency($currencyManager->resolveForRequest(request())));
        });
    }
}
