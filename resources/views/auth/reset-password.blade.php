<x-layouts.app>
    <div class="mx-auto max-w-md px-4 py-12">
        <div class="rounded-3xl bg-white p-8 shadow-xl">
            <h1 class="mb-2 text-2xl font-black text-gray-900">Establecer contraseña nueva</h1>
            <p class="mb-6 text-sm text-gray-600">Elige una contraseña de al menos 8 caracteres.</p>

            <form method="POST" action="{{ route('password.update') }}" class="space-y-4">
                @csrf
                <input type="hidden" name="token" value="{{ $token }}">
                <div>
                    <label for="email" class="mb-1 block text-sm font-bold text-gray-700">Correo</label>
                    <input id="email" name="email" type="email" value="{{ old('email', $email) }}" required
                        autocomplete="email" class="w-full rounded-xl border-gray-300">
                    @error('email') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="password" class="mb-1 block text-sm font-bold text-gray-700">Contraseña nueva</label>
                    <input id="password" name="password" type="password" required minlength="8"
                        autocomplete="new-password" class="w-full rounded-xl border-gray-300">
                    @error('password') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="password_confirmation" class="mb-1 block text-sm font-bold text-gray-700">
                        Confirmar contraseña
                    </label>
                    <input id="password_confirmation" name="password_confirmation" type="password" required
                        minlength="8" autocomplete="new-password" class="w-full rounded-xl border-gray-300">
                </div>
                <x-turnstile />
                <button type="submit"
                    class="w-full rounded-xl bg-orange-500 px-4 py-3 font-black text-white hover:bg-orange-600">
                    Actualizar contraseña
                </button>
            </form>
        </div>
    </div>
</x-layouts.app>
