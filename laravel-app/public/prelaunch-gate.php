<?php

function nb_prelaunch_env(string $key, ?string $default = null): ?string
{
    static $values = null;

    if ($values === null) {
        $values = [];
        $envPath = dirname(__DIR__).'/.env';

        if (is_readable($envPath)) {
            foreach (file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) ?: [] as $line) {
                $line = trim($line);

                if ($line === '' || str_starts_with($line, '#') || ! str_contains($line, '=')) {
                    continue;
                }

                [$name, $value] = explode('=', $line, 2);
                $value = trim($value);

                if (
                    strlen($value) >= 2
                    && (($value[0] === '"' && substr($value, -1) === '"') || ($value[0] === "'" && substr($value, -1) === "'"))
                ) {
                    $value = substr($value, 1, -1);
                }

                $values[trim($name)] = $value;
            }
        }
    }

    return $values[$key] ?? $default;
}

function nb_prelaunch_enabled(): bool
{
    return in_array(strtolower((string) nb_prelaunch_env('PRELAUNCH_LOCK_ENABLED', 'false')), [
        '1',
        'true',
        'yes',
        'on',
    ], true);
}

function nb_prelaunch_client_ip(): string
{
    $forwardedFor = (string) ($_SERVER['HTTP_X_FORWARDED_FOR'] ?? '');

    if ($forwardedFor !== '') {
        return trim(explode(',', $forwardedFor)[0]);
    }

    return (string) ($_SERVER['HTTP_CF_CONNECTING_IP'] ?? $_SERVER['REMOTE_ADDR'] ?? '');
}

function nb_prelaunch_allowed_ip(): bool
{
    $clientIp = nb_prelaunch_client_ip();

    if ($clientIp === '') {
        return false;
    }

    $allowedIps = array_filter(array_map('trim', explode(',', (string) nb_prelaunch_env('PRELAUNCH_ALLOWED_IPS', ''))));

    return in_array($clientIp, $allowedIps, true);
}

function nb_prelaunch_signature(string $token): string
{
    return hash_hmac('sha256', $token, (string) nb_prelaunch_env('APP_KEY', 'naijabuilders'));
}

function nb_prelaunch_has_preview_cookie(string $token): bool
{
    $cookieValue = (string) ($_COOKIE['naijabuilders_prelaunch_preview'] ?? '');

    return $cookieValue !== '' && hash_equals(nb_prelaunch_signature($token), $cookieValue);
}

function nb_prelaunch_has_preview_token(string $token): bool
{
    $candidate = (string) ($_GET['preview_token'] ?? $_SERVER['HTTP_X_PREVIEW_TOKEN'] ?? '');

    return $candidate !== '' && hash_equals($token, $candidate);
}

function nb_prelaunch_attach_cookie(string $token): void
{
    setcookie('naijabuilders_prelaunch_preview', nb_prelaunch_signature($token), [
        'expires' => time() + (7 * 24 * 60 * 60),
        'path' => '/',
        'secure' => (! empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off'),
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
}

function nb_prelaunch_expects_json(): bool
{
    $uri = (string) ($_SERVER['REQUEST_URI'] ?? '');
    $accept = strtolower((string) ($_SERVER['HTTP_ACCEPT'] ?? ''));
    $requestedWith = strtolower((string) ($_SERVER['HTTP_X_REQUESTED_WITH'] ?? ''));

    return str_starts_with($uri, '/api/') || str_contains($accept, 'application/json') || $requestedWith === 'xmlhttprequest';
}

function nb_prelaunch_locked_response(): never
{
    http_response_code(503);
    header('Retry-After: 3600');

    if (nb_prelaunch_expects_json()) {
        header('Content-Type: application/json; charset=UTF-8');
        echo json_encode([
            'message' => 'NaijaBuilders is not publicly available yet.',
            'status' => 'prelaunch',
        ], JSON_THROW_ON_ERROR);
        exit;
    }

    header('Content-Type: text/html; charset=UTF-8');
    $viewPath = dirname(__DIR__).'/resources/views/prelaunch/locked.blade.php';

    if (is_readable($viewPath)) {
        readfile($viewPath);
        exit;
    }

    echo '<!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>Coming Soon | NaijaBuilders</title></head><body><main><h1>Coming Soon</h1><p>NaijaBuilders is being tested privately before launch.</p></main></body></html>';
    exit;
}

if (nb_prelaunch_enabled()) {
    $token = (string) nb_prelaunch_env('PRELAUNCH_BYPASS_TOKEN', '');

    if (nb_prelaunch_allowed_ip() || ($token !== '' && nb_prelaunch_has_preview_cookie($token))) {
        return;
    }

    if ($token !== '' && nb_prelaunch_has_preview_token($token)) {
        nb_prelaunch_attach_cookie($token);
        return;
    }

    nb_prelaunch_locked_response();
}
