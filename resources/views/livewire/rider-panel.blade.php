<div class="max-w-md mx-auto p-4 pb-20 bg-gray-100 min-h-screen">
    <div class="bg-black p-6 rounded-[2rem] mb-6 shadow-xl">
        <h1 class="text-white font-black italic text-2xl uppercase tracking-tighter">Running Rider 🛵</h1>
        <p class="text-orange-500 font-bold text-xs">PANEL DE ENTREGAS</p>
    </div>

    @if (session()->has('message'))
        <div class="bg-green-500 text-white p-4 rounded-2xl mb-4 font-black text-center animate-bounce">
            {{ session('message') }}
        </div>
    @endif

    <div class="space-y-4">
        @forelse($orders as $order)
            <div class="bg-white p-5 rounded-[2rem] shadow-md border-2 border-orange-100">
                <div class="flex justify-between items-start mb-4">
                    <span class="bg-orange-600 text-white px-3 py-1 rounded-full font-black text-[10px]">#{{ $order->id }}</span>
                    <span class="text-gray-400 font-bold text-xs">{{ $order->created_at->diffForHumans() }}</span>
                </div>
                
                <h3 class="font-black text-lg uppercase mb-1">{{ $order->customer_name }}</h3>
                <p class="text-gray-600 text-sm mb-4 font-medium italic">{{ $order->address }}</p>

                <button wire:click="markAsDelivered({{ $order->id }})"
                        class="w-full rounded-2xl bg-green-500 p-4 text-center text-xs font-black uppercase text-white">
                    ✅ Entregado
                </button>
            </div>
        @empty
            <div class="text-center py-20 opacity-30 italic font-black text-gray-400 uppercase">
                No tienes pedidos asignados por ahora.
            </div>
        @endforelse
    </div>
</div>
