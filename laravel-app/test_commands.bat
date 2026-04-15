@echo off
cd /d "C:\wamp64\www\shop\legacy\Naijabuilders\laravel-app"

echo === COMMAND 1 ===
php -l app\Http\Middleware\EnsureLegacyAuth.php
echo EXIT CODE: %ERRORLEVEL%
echo.

echo === COMMAND 2 ===
php -l app\Http\Controllers\AuthController.php
echo EXIT CODE: %ERRORLEVEL%
echo.

echo === COMMAND 3 ===
php artisan route:list --name=login --no-ansi
echo EXIT CODE: %ERRORLEVEL%
