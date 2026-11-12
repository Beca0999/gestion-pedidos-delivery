<x-layouts.app>
    <div class="mx-auto max-w-md px-4 py-12">
        <div class="rounded-3xl bg-white p-8 shadow-xl">
            <h1 class="mb-2 text-2xl font-black text-gray-900">Crear cuenta de cliente</h1>
            <p class="mb-6 text-sm text-gray-600">Regístrate para realizar y consultar tus pedidos.</p>

            <form method="POST" action="{{ route('customer.register.store') }}" class="space-y-4">
                @csrf
                <div>
                    <label for="name" class="mb-1 block text-sm font-bold text-gray-700">Nombre</label>
                    <input id="name" name="name" type="text" value="{{ old('name') }}" required autofocus
                        autocomplete="name" maxlength="255" class="w-full rounded-xl border-gray-300">
                    @error('name') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="email" class="mb-1 block text-sm font-bold text-gray-700">Correo</label>
                    <input id="email" name="email" type="email" value="{{ old('email') }}" required
                        autocomplete="email" class="w-full rounded-xl border-gray-300">
                    @error('email') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="password" class="mb-1 block text-sm font-bold text-gray-700">Contraseña</label>
                    <input id="password" name="password" type="password" required autocomplete="new-password"
                        minlength="8" class="w-full rounded-xl border-gray-300">
                    @error('password') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="password_confirmation" class="mb-1 block text-sm font-bold text-gray-700">
                        Confirmar contraseña
                    </label>
                    <input id="password_confirmation" name="password_confirmation" type="password" required
                        autocomplete="new-password" minlength="8" class="w-full rounded-xl border-gray-300">
                </div>
                <x-turnstile />
                <button type="submit"
                    class="w-full rounded-xl bg-orange-500 px-4 py-3 font-black text-white hover:bg-orange-600">
                    Crear cuenta
                </button>
            </form>

            <p class="mt-5 text-sm text-gray-600">
                ¿Ya tienes cuenta?
                <a href="{{ route('login') }}" class="font-bold text-orange-600 hover:underline">Inicia sesión</a>
            </p>
        </div>
    </div>
</x-layouts.app>
