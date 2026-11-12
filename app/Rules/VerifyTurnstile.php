<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class VerifyTurnstile implements ValidationRule
{
    public static function validationRules(): array
    {
        return [
            'cf-turnstile-response' => [
                Rule::requiredIf(config('services.turnstile.enabled')),
                'nullable',
                'string',
                new self,
            ],
        ];
    }

    public static function validationMessages(): array
    {
        return [
            'cf-turnstile-response.required' => 'Completa el CAPTCHA. Si no aparece, revisa las claves de Cloudflare y que el dominio actual esté autorizado.',
        ];
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! config('services.turnstile.enabled')) {
            return;
        }

        $secret = config('services.turnstile.secret_key');
        if (! filled($secret)) {
            Log::error('Cloudflare Turnstile is enabled but TURNSTILE_SECRET_KEY is not configured.');
            $fail('La verificación anti-bots no está disponible. Inténtalo más tarde.');

            return;
        }

        try {
            $response = Http::asForm()
                ->timeout(5)
                ->post('https://challenges.cloudflare.com/turnstile/v0/siteverify', [
                    'secret' => $secret,
                    'response' => $value,
                    'remoteip' => request()->ip(),
                ]);
        } catch (ConnectionException) {
            Log::error('Cloudflare Turnstile verification could not connect to the verification service.');
            $fail('No se pudo completar la verificación anti-bots. Inténtalo de nuevo.');

            return;
        }

        if (! $response->successful()) {
            Log::warning('Cloudflare Turnstile verification returned an unsuccessful HTTP status.', [
                'status' => $response->status(),
            ]);
            $fail('No se pudo completar la verificación anti-bots. Inténtalo de nuevo.');

            return;
        }

        if ($response->json('success') !== true) {
            $fail('Confirma que no eres un robot e inténtalo de nuevo.');
        }
    }
}
