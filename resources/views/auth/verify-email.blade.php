<x-layouts.app>
    <div class="mx-auto max-w-md px-4 py-12">
        <div class="rounded-3xl bg-white p-8 text-center shadow-xl">
            <h1 class="mb-2 text-2xl font-black text-gray-900">Verifica tu correo</h1>
            <p class="mb-6 text-sm text-gray-600">
                Te enviamos un enlace de confirmación. Ábrelo para verificar tu cuenta y poder realizar pedidos.
            </p>

            @if(session('status'))
                <p class="mb-4 rounded-xl bg-green-50 p-3 text-sm text-green-700">{{ session('status') }}</p>
            @endif

            @if(session('mail_error'))
                <p class="mb-4 rounded-xl bg-amber-50 p-3 text-sm text-amber-800">{{ session('mail_error') }}</p>
            @endif

            <form method="POST" action="{{ route('verification.send') }}">
                @csrf
                <button type="submit"
                    class="w-full rounded-xl bg-orange-500 px-4 py-3 font-black text-white hover:bg-orange-600">
                    Enviar otro enlace
                </button>
            </form>

            <form method="POST" action="{{ route('logout') }}" class="mt-4">
                @csrf
                <button type="submit" class="text-sm font-bold text-gray-600 hover:text-orange-600">
                    Cerrar sesión y volver al inicio de sesión
                </button>
            </form>
        </div>
    </div>
</x-layouts.app>
