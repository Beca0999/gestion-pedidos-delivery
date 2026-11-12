<x-layouts.app>
    <div class="mx-auto max-w-md px-4 py-12">
        <div class="rounded-3xl bg-white p-8 shadow-xl">
            <h1 class="mb-2 text-2xl font-black text-gray-900">Iniciar sesión</h1>
            <p class="mb-6 text-sm text-gray-600">
                Ingresa tus datos; te llevaremos al área correspondiente a tu cuenta.
            </p>
            @if(session('status'))
                <p class="mb-4 rounded-xl bg-green-50 p-3 text-sm text-green-700">{{ session('status') }}</p>
            @endif

            <form method="POST" action="{{ route('login.store') }}" class="space-y-4">
                @csrf
                <div>
                    <label for="email" class="mb-1 block text-sm font-bold text-gray-700">Correo</label>
                    <input id="email" name="email" type="email" value="{{ old('email') }}" required autofocus
                        autocomplete="username" class="w-full rounded-xl border-gray-300">
                    @error('email') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="password" class="mb-1 block text-sm font-bold text-gray-700">Contraseña</label>
                    <input id="password" name="password" type="password" required autocomplete="current-password"
                        class="w-full rounded-xl border-gray-300">
                    @error('password') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>
                <label class="flex items-center gap-2 text-sm text-gray-600">
                    <input type="checkbox" name="remember" value="1" class="rounded border-gray-300">
                    Mantener sesión iniciada
                </label>
                <x-turnstile />
                <button type="submit"
                    class="w-full rounded-xl bg-orange-500 px-4 py-3 font-black text-white hover:bg-orange-600">
                    Iniciar sesión
                </button>
            </form>

            <div class="mt-5 flex flex-col gap-3 text-sm">
                <a href="{{ route('password.request') }}" class="font-bold text-orange-600 hover:underline">
                    ¿Olvidaste tu contraseña?
                </a>
                <a href="{{ route('customer.register') }}" class="font-bold text-gray-700 hover:text-orange-600">
                    ¿No tienes cuenta de cliente? Regístrate
                </a>
            </div>
        </div>
    </div>
</x-layouts.app>
