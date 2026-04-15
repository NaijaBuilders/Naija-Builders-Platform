@echo off
cd /d "C:\wamp64\www\shop\legacy\Naijabuilders\laravel-app"
echo === Validating EnsureLegacyAuth.php ===
php -l app\Http\Middleware\EnsureLegacyAuth.php
echo.
echo === Validating AuthController.php ===
php -l app\Http\Controllers\AuthController.php
echo.
echo === Listing login routes ===
php artisan route:list --name=login --no-ansi
