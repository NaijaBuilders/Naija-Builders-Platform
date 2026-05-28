<?php

namespace Tests\Feature;

use Tests\TestCase;

class PrelaunchAccessTest extends TestCase
{
    public function test_public_web_requests_show_coming_soon_when_prelaunch_lock_is_enabled(): void
    {
        config(['prelaunch.lock_enabled' => true]);

        $response = $this
            ->withServerVariables(['REMOTE_ADDR' => '203.0.113.10'])
            ->get('/login.php');

        $response->assertStatus(503);
        $response->assertSee('Coming Soon');
    }

    public function test_public_api_requests_receive_prelaunch_response_when_lock_is_enabled(): void
    {
        config(['prelaunch.lock_enabled' => true]);

        $response = $this
            ->withServerVariables(['REMOTE_ADDR' => '203.0.113.10'])
            ->getJson('/api/mobile/materials');

        $response->assertStatus(503);
        $response->assertJson([
            'status' => 'prelaunch',
        ]);
    }

    public function test_preview_token_allows_private_access_when_lock_is_enabled(): void
    {
        config([
            'prelaunch.lock_enabled' => true,
            'prelaunch.bypass_token' => 'preview-secret',
        ]);

        $response = $this
            ->withServerVariables(['REMOTE_ADDR' => '203.0.113.10'])
            ->get('/login.php?preview_token=preview-secret');

        $response->assertStatus(200);
    }

    public function test_preview_token_sets_cookie_for_follow_up_browser_requests(): void
    {
        config([
            'prelaunch.lock_enabled' => true,
            'prelaunch.bypass_token' => 'preview-secret',
        ]);

        $response = $this
            ->withServerVariables(['REMOTE_ADDR' => '203.0.113.10'])
            ->get('/login.php?preview_token=preview-secret');

        $response->assertStatus(200);
        $response->assertCookie('naijabuilders_prelaunch_preview');

        $previewCookie = collect($response->headers->getCookies())
            ->first(fn ($cookie) => $cookie->getName() === 'naijabuilders_prelaunch_preview');

        $followUp = $this
            ->withUnencryptedCookie('naijabuilders_prelaunch_preview', $previewCookie->getValue())
            ->withServerVariables(['REMOTE_ADDR' => '203.0.113.10'])
            ->get('/login.php');

        $followUp->assertStatus(200);
    }
}
