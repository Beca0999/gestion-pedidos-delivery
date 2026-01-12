<!DOCTYPE html>
<html>
<head>
    <title>Ticket #{{ $order->tracking_code }}</title>
    <style>
        body { font-family: 'Courier New', Courier, monospace; width: 300px; margin: 0 auto; padding: 10px; font-size: 14px; }
        .text-center { text-align: center; }
        .divider { border-top: 1px dashed #000; margin: 10px 0; }
        .total { font-size: 18px; font-weight: bold; }
        .address { background: #eee; padding: 5px; margin-top: 10px; font-size: 16px; }
        @media print { .no-print { display: none; } }
    </style>
</head>
<body>
    <div class="no-print text-center">
        <button onclick="window.print()">🖨️ IMPRIMIR TICKET</button>
        <hr>
    </div>

    <div class="text-center">
        <h2 style="margin:0">{{ $order->business->name }}</h2>
        <p>Ticket: {{ $order->tracking_code }}</p>
    </div>

    <div class="divider"></div>
    <p><strong>Cliente:</strong> {{ $order->customer_name }}</p>
    <p><strong>Fecha:</strong> {{ $order->created_at->format('d/m/y H:i') }}</p>

    <div class="divider"></div>
    <div class="address">
        <strong>ENTREGAR EN:</strong><br>
        {{ $order->address }}
    </div>

    <div class="divider"></div>
    <div class="total text-center">
        TOTAL: ${{ number_format($order->total, 2) }}
    </div>
    
    <div class="divider"></div>
    <div class="text-center">
        <p>¡Gracias por tu compra!</p>
        <img src="https://api.qrserver.com/v1/create-qr-code/?size=100x100&data={{ urlencode(route('order.tracking', $order->tracking_code)) }}" />
        <p style="font-size:10px">Escanea para rastrear</p>
    </div>
</body>
</html>
