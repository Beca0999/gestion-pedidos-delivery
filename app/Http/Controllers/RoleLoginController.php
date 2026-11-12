<?php

namespace App\Http\Controllers;

use App\Rules\VerifyTurnstile;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class RoleLoginController extends Controller
{
    public function showGeneral(): View|RedirectResponse
    {
        if (Auth::check()) {
            return $this->redirectForRole(Auth::user()->role);
        }

        return view('auth.role-login', ['role' => null]);
    }

    public function authenticateGeneral(Request $request): RedirectResponse
    {
        $credentials = $request->validate(array_merge([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ], VerifyTurnstile::validationRules()), VerifyTurnstile::validationMessages());

        if (! Auth::attempt($credentials, $request->boolean('remember'))) {
            throw ValidationException::withMessages([
                'email' => 'El correo o la contraseña no son válidos.',
            ]);
        }

        $request->session()->regenerate();

        return $this->redirectForRole(Auth::user()->role);
    }

    public function authenticate(Request $request, string $role): RedirectResponse
    {
        abort_unless(in_array($role, ['rider', 'customer'], true), 404);

        $credentials = $request->validate(array_merge([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ], VerifyTurnstile::validationRules()), VerifyTurnstile::validationMessages());

        if (! Auth::attempt($credentials + ['role' => $role], $request->boolean('remember'))) {
            throw ValidationException::withMessages([
                'email' => 'Las credenciales no son válidas para este tipo de cuenta.',
            ]);
        }

        $request->session()->regenerate();

        return $this->redirectForRole($role);
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')
            ->with('status', 'Sesión cerrada. Inicia sesión con la cuenta que deseas usar.');
    }

    private function redirectForRole(string $role): RedirectResponse
    {
        return match ($role) {
            'admin', 'owner' => redirect('/admin'),
            'rider' => redirect()->route('rider.panel'),
            'customer' => redirect()->route('home'),
            default => abort(403),
        };
    }
}
