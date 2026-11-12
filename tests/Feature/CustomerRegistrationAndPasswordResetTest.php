<?php

namespace Tests\Feature;

use App\Models\User;
use App\Notifications\VerifyEmailNotification;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\URL;
use Symfony\Component\Mailer\Exception\TransportException;
use Tests\TestCase;

class CustomerRegistrationAndPasswordResetTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_can_register_but_cannot_choose_a_privileged_role(): void
    {
        Notification::fake();

        $this->get(route('customer.register'))
            ->assertOk()
            ->assertSee('Crear cuenta de cliente');

        $this->post(route('customer.register.store'), [
            'name' => 'Cliente nuevo',
            'email' => 'new-customer@example.test',
            'password' => 'a-secure-password',
            'password_confirmation' => 'a-secure-password',
            'role' => 'admin',
        ])->assertRedirect(route('verification.notice'));

        $user = User::where('email', 'new-customer@example.test')->firstOrFail();

        $this->assertSame('customer', $user->role);
        $this->assertAuthenticatedAs($user);
        $this->assertTrue(Hash::check('a-secure-password', $user->password));
        $this->assertFalse($user->hasVerifiedEmail());
        Notification::assertSentTo($user, VerifyEmailNotification::class);

        $mail = Notification::sent($user, VerifyEmailNotification::class)->first()->toMail($user);
        $this->assertSame('Verifica tu correo de Running', $mail->subject);
        $this->assertStringContainsString('signature=', $mail->actionUrl);
    }

    public function test_customer_can_verify_their_email_with_a_signed_link(): void
    {
        $user = User::factory()->unverified()->customer()->create();
        $this->actingAs($user);

        $url = URL::temporarySignedRoute('verification.verify', now()->addMinutes(60), [
            'id' => $user->id,
            'hash' => sha1($user->getEmailForVerification()),
        ]);

        $this->get($url)
            ->assertRedirect(route('home'))
            ->assertSessionHas('status');

        $this->assertTrue($user->fresh()->hasVerifiedEmail());
    }

    public function test_customer_stays_logged_in_if_verification_email_cannot_be_sent(): void
    {
        Notification::fake();
        Event::listen(Registered::class, function (): never {
            throw new TransportException('SMTP unavailable');
        });

        $this->post(route('customer.register.store'), [
            'name' => 'Cliente sin correo',
            'email' => 'customer-mail-failure@example.test',
            'password' => 'a-secure-password',
            'password_confirmation' => 'a-secure-password',
        ])->assertRedirect(route('verification.notice'))
            ->assertSessionHas('mail_error');

        $this->assertAuthenticated();
        $this->assertDatabaseHas('users', ['email' => 'customer-mail-failure@example.test']);
    }

    public function test_customer_can_request_and_complete_a_password_reset(): void
    {
        Notification::fake();
        $user = User::factory()->customer()->create(['email' => 'customer@example.test']);

        $this->get(route('password.request'))
            ->assertOk()
            ->assertSee('Recuperar contraseña');

        $this->post(route('password.email'), ['email' => $user->email])
            ->assertRedirect()
            ->assertSessionHas('status');

        Notification::assertSentTo($user, ResetPassword::class);

        $token = Password::createToken($user);
        $this->post(route('password.update'), [
            'token' => $token,
            'email' => $user->email,
            'password' => 'a-new-secure-password',
            'password_confirmation' => 'a-new-secure-password',
        ])->assertRedirect(route('login'))
            ->assertSessionHas('status');

        $this->assertTrue(Hash::check('a-new-secure-password', $user->fresh()->password));
    }
}
