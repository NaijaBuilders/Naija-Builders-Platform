<?php

namespace App\Providers;

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
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if (config('database.default') !== 'mysql') {
            throw new RuntimeException('This application is configured for MySQL only. Set DB_CONNECTION=mysql in your .env file.');
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

        view()->share('navbarMaterialSuggestions', $navbarMaterialSuggestions);
    }
}
