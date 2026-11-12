<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class TurnstileTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'services.turnstile.enabled' => true,
            'services.turnstile.site_key' => 'test-site-key',
            'services.turnstile.secret_key' => 'test-secret-key',
        ]);
    }

    public function test_login_rejects_a_turnstile_token_that_cloudflare_does_not_accept(): void
    {
        Http::fake([
            'challenges.cloudflare.com/turnstile/v0/siteverify' => Http::response(['success' => false]),
        ]);

        $this->from(route('login'))
            ->post(route('login.store'), [
                'email' => 'customer@example.test',
                'password' => 'password',
                'cf-turnstile-response' => 'invalid-token',
            ])
            ->assertRedirect(route('login'))
            ->assertSessionHasErrors('cf-turnstile-response');

        Http::assertSent(fn ($request) => $request->url() === 'https://challenges.cloudflare.com/turnstile/v0/siteverify'
            && $request['secret'] === 'test-secret-key'
            && $request['response'] === 'invalid-token');
    }

    public function test_forgot_password_page_renders_turnstile_widget(): void
    {
        $this->get(route('password.request'))
            ->assertOk()
            ->assertSee('cf-turnstile', false)
            ->assertSee('data-sitekey="test-site-key"', false)
            ->assertSee('challenges.cloudflare.com/turnstile/v0/api.js', false);
    }

    public function test_missing_turnstile_token_has_a_helpful_error_message(): void
    {
        $this->from(route('password.request'))
            ->post(route('password.email'), [
                'email' => 'customer@example.test',
            ])
            ->assertRedirect(route('password.request'))
            ->assertSessionHasErrors([
                'cf-turnstile-response' => 'Completa el CAPTCHA. Si no aparece, revisa las claves de Cloudflare y que el dominio actual esté autorizado.',
            ]);
    }

    public function test_login_accepts_a_turnstile_token_verified_by_cloudflare(): void
    {
        Http::fake([
            'challenges.cloudflare.com/turnstile/v0/siteverify' => Http::response(['success' => true]),
        ]);

        $this->from(route('login'))
            ->post(route('login.store'), [
                'email' => 'customer@example.test',
                'password' => 'wrong-password',
                'cf-turnstile-response' => 'valid-token',
            ])
            ->assertSessionHasErrors('email')
            ->assertSessionDoesntHaveErrors('cf-turnstile-response');
    }

    public function test_turnstile_service_connection_failure_does_not_return_a_server_error(): void
    {
        Http::fake(fn () => throw new ConnectionException('Connection failed'));

        $this->from(route('login'))
            ->post(route('login.store'), [
                'email' => 'customer@example.test',
                'password' => 'password',
                'cf-turnstile-response' => 'valid-token',
            ])
            ->assertRedirect(route('login'))
            ->assertSessionHasErrors('cf-turnstile-response');
    }
}
