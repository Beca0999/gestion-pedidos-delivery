<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CaptchaDisabledTest extends TestCase
{
    use RefreshDatabase;

    public function test_authentication_and_password_pages_do_not_render_captcha(): void
    {
        $pages = [
            route('login'),
            route('customer.register'),
            route('password.request'),
            route('password.reset', ['token' => 'temporary-token']).'?email=customer%40example.test',
        ];

        foreach ($pages as $page) {
            $this->get($page)
                ->assertOk()
                ->assertDontSee('cf-turnstile')
                ->assertDontSee('challenges.cloudflare.com');
        }
    }

    public function test_login_does_not_require_a_captcha_response(): void
    {
        $this->from(route('login'))
            ->post(route('login.store'), [
                'email' => 'customer@example.test',
                'password' => 'incorrect-password',
            ])
            ->assertRedirect(route('login'))
            ->assertSessionHasErrors('email')
            ->assertSessionDoesntHaveErrors('cf-turnstile-response');
    }
}
