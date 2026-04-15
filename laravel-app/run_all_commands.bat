@echo off
setlocal enabledelayedexpansion

REM Set up PHP path
set PHP_PATH=C:\wamp64\bin\php\php8.2.29\php.exe
set WORK_DIR=C:\wamp64\www\shop\legacy\Naijabuilders\laravel-app

REM Check if PHP exists in PATH first
for %%X in (php.exe) do (set PHP_CHECK=%%~$PATH:X)
if not "!PHP_CHECK!"=="" (
    set PHP_PATH=php
)

REM Change to working directory
cd /d "!WORK_DIR!"

echo === COMMAND 1: php -l app\Http\Middleware\EnsureLegacyAuth.php ===
"!PHP_PATH!" -l app\Http\Middleware\EnsureLegacyAuth.php
set "EXIT_CODE_1=!errorlevel!"
echo.
echo EXIT CODE: !EXIT_CODE_1!
echo.
echo.

echo === COMMAND 2: php -l app\Http\Controllers\AuthController.php ===
"!PHP_PATH!" -l app\Http\Controllers\AuthController.php
set "EXIT_CODE_2=!errorlevel!"
echo.
echo EXIT CODE: !EXIT_CODE_2!
echo.
echo.

echo === COMMAND 3: php artisan route:list --name=login --no-ansi ===
"!PHP_PATH!" artisan route:list --name=login --no-ansi
set "EXIT_CODE_3=!errorlevel!"
echo.
echo EXIT CODE: !EXIT_CODE_3!
echo.

endlocal
