<x-layouts.app>
    <div class="mx-auto max-w-md px-4 py-12">
        <div class="rounded-3xl bg-white p-8 shadow-xl">
            <h1 class="mb-2 text-2xl font-black text-gray-900">Recuperar contraseña</h1>
            <p class="mb-6 text-sm text-gray-600">
                Escribe el correo de tu cuenta y te enviaremos un enlace si está registrado.
            </p>

            @if(session('status'))
                <p class="mb-4 rounded-xl bg-green-50 p-3 text-sm text-green-700">{{ session('status') }}</p>
            @endif

            <form method="POST" action="{{ route('password.email') }}" class="space-y-4">
                @csrf
                <div>
                    <label for="email" class="mb-1 block text-sm font-bold text-gray-700">Correo</label>
                    <input id="email" name="email" type="email" value="{{ old('email') }}" required autofocus
                        autocomplete="email" class="w-full rounded-xl border-gray-300">
                    @error('email') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>
                <x-turnstile />
                <button type="submit"
                    class="w-full rounded-xl bg-orange-500 px-4 py-3 font-black text-white hover:bg-orange-600">
                    Enviar enlace de recuperación
                </button>
            </form>

            <a href="{{ route('login') }}" class="mt-5 inline-block text-sm font-bold text-orange-600 hover:underline">
                Volver al inicio de sesión
            </a>
        </div>
    </div>
</x-layouts.app>
