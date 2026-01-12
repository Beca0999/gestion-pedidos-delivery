<div class="font-mono text-black">
    <div class="text-center font-black text-2xl italic text-orange-600 mb-1">RUNNING 🏃‍♂️</div>
    <div class="text-center text-[10px] text-gray-400 uppercase tracking-[0.2em] mb-4">Comprobante Oficial</div>
    
    <div class="border-y-2 border-dashed border-gray-200 py-3 my-3 space-y-1">
        <div class="flex justify-between text-xs font-bold">
            <span class="text-gray-500 uppercase">Orden:</span>
            <span>#{{ $order->id }}</span>
        </div>
        <div class="flex justify-between text-xs font-bold">
            <span class="text-gray-500 uppercase">Fecha:</span>
            <span>{{ $order->created_at->format('d/m/y H:i') }}</span>
        </div>
        <div class="flex justify-between text-xs font-bold">
            <span class="text-gray-500 uppercase">Pago:</span>
            <span class="bg-black text-white px-2 py-0.5 rounded text-[10px] uppercase">{{ $order->payment_method }}</span>
        </div>
    </div>

    <div class="py-2">
        <p class="text-[9px] text-gray-400 uppercase font-black mb-1">Dirección de entrega:</p>
        <p class="text-xs font-bold leading-tight uppercase">{{ $order->address }}</p>
    </div>

    <div class="mt-4 pt-4 border-t-4 border-double border-gray-100">
        <div class="flex justify-between items-end">
            <span class="text-xs font-black italic uppercase text-gray-400">Total Pagado:</span>
            <span class="text-3xl font-black italic tracking-tighter text-orange-600">${{ number_format($order->total, 2) }}</span>
        </div>
    </div>

    <div class="text-center mt-6">
        <div class="inline-block border-2 border-black p-1">
            <div class="text-[8px] font-black uppercase tracking-widest px-2">Gracias por confiar en nosotros</div>
        </div>
    </div>
</div>
