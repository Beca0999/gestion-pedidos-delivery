<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ config('app.name', 'Running') }} - Delivery</title>
    <script src="https://cdn.tailwindcss.com"></script>
    @livewireStyles
</head>
<body class="bg-gray-50 font-sans antialiased">
    <nav class="bg-white shadow-sm py-4 sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 flex flex-wrap justify-between items-center gap-3">
            <a href="/" class="text-2xl font-black text-orange-500 tracking-tighter flex items-center gap-2">
                <span>🚀</span> {{ config('app.name', 'Running') }}
            </a>

            <div class="flex flex-wrap items-center justify-end gap-3">
                @auth
                    <div class="text-right">
                        <p class="text-sm font-black text-gray-900">{{ auth()->user()->name }}</p>
                        <p class="text-xs font-bold text-gray-500">
                            {{ match (auth()->user()->role) {
                                'admin' => 'Administrador',
                                'owner' => 'Propietario',
                                'rider' => 'Repartidor',
                                default => 'Cliente',
                            } }}
                        </p>
                    </div>
                    @if(auth()->user()->hasRole('rider'))
                        <a href="{{ route('rider.panel') }}"
                           class="rounded-xl border border-orange-200 px-3 py-2 text-sm font-bold text-orange-700 hover:bg-orange-50">
                            Mis entregas
                        </a>
                    @elseif(auth()->user()->hasRole(['admin', 'owner']))
                        <a href="{{ url('/admin') }}"
                           class="rounded-xl border border-orange-200 px-3 py-2 text-sm font-bold text-orange-700 hover:bg-orange-50">
                            Administración
                        </a>
                    @endif
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit"
                                aria-label="Cerrar sesión de {{ auth()->user()->name }}"
                                class="rounded-xl bg-orange-600 px-4 py-2 text-sm font-black text-white shadow-sm hover:bg-orange-700 focus:outline-none focus:ring-2 focus:ring-orange-500 focus:ring-offset-2">
                            Cerrar sesión
                        </button>
                    </form>
                @else
                    <a href="{{ route('login') }}"
                       class="rounded-full bg-orange-500 px-5 py-2 text-sm font-black text-white shadow hover:bg-orange-600">
                        Iniciar sesión
                    </a>
                    <a href="{{ route('customer.register') }}"
                       class="rounded-full border border-orange-300 px-4 py-2 text-sm font-black text-orange-700 hover:bg-orange-50">
                        Crear cuenta
                    </a>
                @endauth
            </div>

            @if(session('active_order_id'))
                <a href="{{ route('order.tracking', session('active_order_id')) }}" 
                   class="order-3 flex w-full items-center justify-center gap-2 rounded-full bg-orange-100 px-4 py-2 text-xs font-black text-orange-600 transition-colors hover:bg-orange-200 sm:order-none sm:w-auto">
                    <span class="relative flex h-2 w-2">
                      <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-orange-400 opacity-75"></span>
                      <span class="relative inline-flex rounded-full h-2 w-2 bg-orange-500"></span>
                    </span>
                    RASTREAR MI PEDIDO #{{ session('active_order_id') }}
                </a>
            @endif
        </div>
    </nav>

    <main>
        {{ $slot }}
    </main>

    @livewireScripts
    @stack('scripts')
</body>
</html>
