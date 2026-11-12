@if(config('services.turnstile.enabled'))
    <div>
        @if(config('services.turnstile.site_key'))
            <div class="cf-turnstile" data-sitekey="{{ config('services.turnstile.site_key') }}"></div>
            <script src="https://challenges.cloudflare.com/turnstile/v0/api.js" async defer></script>
            <p class="mt-2 text-xs text-gray-500">
                Si el CAPTCHA no aparece, verifica las claves de Turnstile y que el dominio actual esté autorizado en Cloudflare.
            </p>
        @else
            <p class="text-sm font-bold text-red-600">El CAPTCHA está activado, pero falta configurar TURNSTILE_SITE_KEY.</p>
        @endif
        @error('cf-turnstile-response')
            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>
@endif
