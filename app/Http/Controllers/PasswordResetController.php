<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Rules\VerifyTurnstile;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\View\View;

class PasswordResetController extends Controller
{
    public function createLinkRequest(): View
    {
        return view('auth.forgot-password');
    }

    public function sendResetLink(Request $request): RedirectResponse
    {
        $request->validate(array_merge([
            'email' => ['required', 'email'],
        ], VerifyTurnstile::validationRules()), VerifyTurnstile::validationMessages());

        Password::sendResetLink($request->only('email'));

        return back()->with(
            'status',
            'Si existe una cuenta con ese correo, recibirás instrucciones para recuperar tu contraseña.',
        );
    }

    public function createResetForm(Request $request, string $token): View
    {
        return view('auth.reset-password', [
            'token' => $token,
            'email' => $request->query('email'),
        ]);
    }

    public function reset(Request $request): RedirectResponse
    {
        $credentials = $request->validate(array_merge([
            'token' => ['required', 'string'],
            'email' => ['required', 'email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ], VerifyTurnstile::validationRules()), VerifyTurnstile::validationMessages());

        $status = Password::reset($credentials, function (User $user, string $password): void {
            $user->forceFill([
                'password' => $password,
                'remember_token' => Str::random(60),
            ])->save();

            event(new PasswordReset($user));
        });

        if ($status === Password::PASSWORD_RESET) {
            return redirect()->route('login')
                ->with('status', 'Tu contraseña se actualizó. Ya puedes iniciar sesión.');
        }

        return back()->withErrors([
            'email' => 'El enlace de recuperación no es válido o ya venció. Solicita uno nuevo.',
        ]);
    }
}
