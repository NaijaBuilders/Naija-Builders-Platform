<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\Response;

class EnsurePrelaunchAccess
{
    private const PREVIEW_COOKIE = 'naijabuilders_prelaunch_preview';

    /**
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (! config('prelaunch.lock_enabled')) {
            return $next($request);
        }

        if ($this->hasAllowedIp($request) || $this->hasPreviewCookie($request)) {
            return $next($request);
        }

        if ($this->hasPreviewToken($request)) {
            $response = $next($request);
            $this->attachPreviewCookie($request, $response);

            return $response;
        }

        if ($request->is('api/*') || $request->expectsJson()) {
            return response()
                ->json([
                    'message' => 'NaijaBuilders is not publicly available yet.',
                    'status' => 'prelaunch',
                ], 503)
                ->header('Retry-After', '3600');
        }

        return response()
            ->view('prelaunch.locked', [], 503)
            ->header('Retry-After', '3600');
    }

    private function hasPreviewToken(Request $request): bool
    {
        $token = (string) config('prelaunch.bypass_token', '');

        if ($token === '') {
            return false;
        }

        $candidate = (string) ($request->header('X-Preview-Token') ?: $request->query('preview_token', ''));

        return $candidate !== '' && hash_equals($token, $candidate);
    }

    private function hasPreviewCookie(Request $request): bool
    {
        $token = (string) config('prelaunch.bypass_token', '');

        if ($token === '') {
            return false;
        }

        $cookieValue = (string) $request->cookies->get(self::PREVIEW_COOKIE, '');

        return $cookieValue !== '' && hash_equals($this->previewSignature($token), $cookieValue);
    }

    private function attachPreviewCookie(Request $request, Response $response): void
    {
        $token = (string) config('prelaunch.bypass_token', '');

        if ($token === '') {
            return;
        }

        $response->headers->setCookie(new Cookie(
            self::PREVIEW_COOKIE,
            $this->previewSignature($token),
            now()->addDays(7),
            '/',
            null,
            $request->isSecure(),
            true,
            false,
            'lax',
        ));
    }

    private function previewSignature(string $token): string
    {
        $appKey = (string) config('app.key', 'naijabuilders');

        return hash_hmac('sha256', $token, $appKey);
    }

    private function hasAllowedIp(Request $request): bool
    {
        $requestIp = (string) $request->ip();

        if ($requestIp === '') {
            return false;
        }

        foreach ((array) config('prelaunch.allowed_ips', []) as $allowedIp) {
            if ($requestIp === $allowedIp) {
                return true;
            }
        }

        return false;
    }
}
