<!DOCTYPE html>
<html>
<body style="font-family: Arial, sans-serif; color: #333;">
    <div style="max-width: 600px; margin: auto; border: 1px solid #eee; padding: 20px; border-radius: 15px;">
        <h2 style="color: #ea580c; text-align: center;">¡Pedido Confirmado! 🚲</h2>
        <p>Hola <strong>{{ $order->customer_name }}</strong>,</p>
        <p>Gracias por pedir en <strong>Delivery Express</strong>. Aquí tienes los detalles de tu compra:</p>
        <hr style="border: 0; border-top: 1px dashed #eee;">
        <p><strong>Código de Rastreo:</strong> {{ $order->tracking_code }}</p>
        <p><strong>Dirección:</strong> {{ $order->address }}</p>
        <p><strong>Total a Pagar:</strong> <span style="font-size: 20px; font-weight: bold; color: #000;">${{ number_format($order->total, 2) }}</span></p>
        <hr style="border: 0; border-top: 1px dashed #eee;">
        <p style="text-align: center; font-size: 12px; color: #999;">Tu repartidor se pondrá en contacto contigo pronto.</p>
    </div>
</body>
</html>
