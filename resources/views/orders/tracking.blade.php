<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rastreo de Pedido - {{ $order->tracking_code }}</title>
    <meta http-equiv="refresh" content="15">
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen flex items-center justify-center p-4">
    <div class="bg-white p-8 rounded-3xl shadow-xl max-w-md w-full text-center">
        <h1 class="text-2xl font-black mb-2">Estatus de tu pedido</h1>
        <p class="text-orange-600 font-bold mb-8">#{{ $order->tracking_code }}</p>

        <div class="space-y-8 relative">
            @php
                $steps = [
                    'Pendiente' => ['label' => 'Recibido', 'color' => 'bg-gray-300'],
                    'En Camino' => ['label' => 'En Camino', 'color' => 'bg-orange-500'],
                    'Entregado' => ['label' => '¡Entregado!', 'color' => 'bg-green-500']
                ];
                $currentStatus = $order->status;
            @endphp

            @foreach(['Pendiente', 'En Camino', 'Entregado'] as $status)
                <div class="flex items-center space-x-4">
                    <div class="w-10 h-10 rounded-full flex items-center justify-center {{ $order->status == $status ? 'bg-orange-600 text-white animate-bounce' : 'bg-gray-200' }}">
                        {{ $loop->iteration }}
                    </div>
                    <div class="text-left">
                        <p class="font-bold {{ $order->status == $status ? 'text-orange-600' : 'text-gray-400' }}">
                            {{ $steps[$status]['label'] }}
                        </p>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="mt-10 pt-6 border-t">
            <p class="text-sm text-gray-500">Negocio: <strong>{{ $order->business->name }}</strong></p>
            <p class="text-sm text-gray-500">Destino: {{ $order->address }}</p>
        </div>
        
        <a href="/" class="mt-8 inline-block text-orange-600 font-bold underline">Hacer otro pedido</a>
    </div>
</body>
</html>
