<div class="max-w-md mx-auto bg-white shadow-2xl rounded-[2rem] overflow-hidden mt-10 p-8 border border-gray-100">
    <div class="text-center mb-10">
        <span class="bg-orange-100 text-orange-600 text-xs font-black px-3 py-1 rounded-full uppercase tracking-widest">Estado del Pedido</span>
        <h2 class="text-3xl font-black text-gray-900 mt-3">#{{ $order->id }}</h2>
        <p class="text-gray-500 font-medium">{{ $order->business->name }}</p>
    </div>

    @php
        $steps = ['Pendiente', 'En Preparación', 'En Camino', 'Entregado'];
        $currentStatus = $order->status;
        $index = array_search($currentStatus, $steps);
        $currentStep = ($index === false) ? 0 : $index;
    @endphp

    <div class="relative flex justify-between items-center mb-20 px-2">
        <div class="absolute left-0 top-5 w-full h-1 bg-gray-100 rounded-full"></div>
        <div class="absolute left-0 top-5 h-1 bg-orange-500 rounded-full transition-all duration-1000 shadow-[0_0_10px_rgba(249,115,22,0.5)]" 
             style="width: {{ ($currentStep / 3) * 100 }}%"></div>
        
        @foreach($steps as $i => $step)
            <div class="relative z-10 flex flex-col items-center">
                <div class="w-10 h-10 rounded-full flex items-center justify-center transition-colors duration-500 {{ $i <= $currentStep ? 'bg-orange-500 text-white shadow-lg' : 'bg-white border-4 border-gray-100 text-gray-300' }}">
                    @if($i < $currentStep)
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path></svg>
                    @elseif($i == $currentStep)
                        <div class="w-3 h-3 bg-white rounded-full animate-ping"></div>
                    @else
                        <span class="text-sm font-bold">{{ $i + 1 }}</span>
                    @endif
                </div>
                <span class="absolute top-12 text-[10px] font-black uppercase tracking-tighter w-20 text-center {{ $i <= $currentStep ? 'text-orange-600' : 'text-gray-300' }}">
                    {{ $step }}
                </span>
            </div>
        @endforeach
    </div>

    <div class="bg-gray-50 rounded-3xl p-6 mb-6">
        <div class="flex items-center gap-4">
            <div class="w-12 h-12 bg-white rounded-2xl flex items-center justify-center text-2xl shadow-sm">
                {{ $order->rider ? '🛵' : '⏳' }}
            </div>
            <div>
                <p class="text-[10px] text-gray-400 font-bold uppercase">Repartidor asignado</p>
                <p class="font-bold text-gray-800">{{ $order->rider->name ?? 'Buscando repartidor...' }}</p>
            </div>
        </div>
    </div>

    <a href="/" class="flex items-center justify-center gap-2 w-full py-4 bg-gray-900 text-white rounded-2xl font-bold hover:bg-gray-800 transition-all active:scale-95 shadow-lg">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
        </svg>
        Volver a la Tienda
    </a>

    <div class="mt-8 text-center" wire:poll.5s>
        <p class="text-[10px] text-gray-400 font-medium animate-pulse italic">Actualizando estado en tiempo real...</p>
    </div>
</div>
