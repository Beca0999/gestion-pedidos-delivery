<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RAPIDIN - Delivery</title>
    <script src="https://cdn.tailwindcss.com"></script>
    @livewireStyles
</head>
<body class="bg-gray-50 font-sans antialiased">
    <nav class="bg-white shadow-sm py-4 sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 flex justify-between items-center">
            <a href="/" class="text-2xl font-black text-orange-500 tracking-tighter flex items-center gap-2">
                <span>🚀</span> RAPIDIN
            </a>
            
            @if(session('active_order_id'))
                <a href="{{ route('order.tracking', session('active_order_id')) }}" 
                   class="flex items-center gap-2 bg-orange-100 text-orange-600 px-4 py-2 rounded-full text-xs font-black hover:bg-orange-200 transition-colors">
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
</body>
</html>
