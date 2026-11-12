<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;

class CustomerRegistrationController extends Controller
{
    public function create(): View
    {
        return view('auth.customer-register');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => $validated['password'],
            'role' => 'customer',
        ]);

        Auth::login($user);
        $request->session()->regenerate();

        try {
            event(new Registered($user));
        } catch (TransportExceptionInterface $exception) {
            Log::error('Could not send the customer email verification message.', [
                'user_id' => $user->id,
                'exception' => $exception::class,
            ]);

            return redirect()->route('verification.notice')
                ->with('mail_error', 'Tu cuenta se creó y ya iniciaste sesión, pero no se pudo enviar el correo. Revisa la configuración del correo e intenta reenviarlo.');
        }

        return redirect()->route('verification.notice')
            ->with('status', 'Tu cuenta se creó. Revisa tu correo para verificarla.');
    }
}
