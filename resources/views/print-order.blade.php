<!DOCTYPE html>
<html>
<head>
    <title>Ticket #{{ $order->id }}</title>
    <style>
        body { font-family: sans-serif; font-size: 14px; width: 300px; margin: auto; padding: 20px; }
        .text-center { text-align: center; }
        .border-b { border-bottom: 1px dashed #000; padding-bottom: 10px; margin-bottom: 10px; }
        .bold { font-weight: bold; }
        .flex { display: flex; justify-content: space-between; }
        @media print { .no-print { display: none; } }
    </style>
</head>
<body onload="window.print()">
    <div class="text-center">
        <h2 style="margin:0">DELIVERY EXPRESS</h2>
        <p>Ticket de Pedido #{{ $order->id }}</p>
    </div>
    <div class="border-b">
        <p><b>Cliente:</b> {{ $order->customer_name }}</p>
        <p><b>Tel:</b> {{ $order->phone }}</p>
        <p><b>Dirección:</b> {{ $order->address }}</p>
    </div>
    <div class="border-b">
        <p class="bold text-center">DETALLE DE COBRO</p>
        <div class="flex">
            <span>TOTAL A COBRAR:</span>
            <span class="bold">${{ number_format($order->total, 2) }}</span>
        </div>
    </div>
    <p class="text-center" style="font-size: 10px;">¡Gracias por su preferencia!</p>
    <button class="no-print" onclick="window.print()" style="width:100%; padding:10px; margin-top:20px;">Imprimir Manualmente</button>
</body>
</html>
