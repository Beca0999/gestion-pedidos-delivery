<div class="mx-auto mt-10 max-w-3xl overflow-hidden rounded-[2rem] border border-gray-100 bg-white p-8 shadow-2xl">
    <div class="mb-10 text-center">
        <span class="rounded-full bg-orange-100 px-3 py-1 text-xs font-black uppercase tracking-widest text-orange-600">Estado del Pedido</span>
        <h2 class="mt-3 text-3xl font-black text-gray-900">#{{ $order->id }}</h2>
        <p class="font-medium text-gray-500">{{ $order->business->name }}</p>
    </div>

    @php
        $steps = ['Pendiente', 'En Preparación', 'En Camino', 'Entregado'];
        $index = array_search($order->status, $steps);
        $currentStep = $index === false ? 0 : $index;
    @endphp

    <div class="relative mb-20 flex items-center justify-between px-2">
        <div class="absolute left-0 top-5 h-1 w-full rounded-full bg-gray-100"></div>
        <div class="absolute left-0 top-5 h-1 rounded-full bg-orange-500 shadow-[0_0_10px_rgba(249,115,22,0.5)] transition-all duration-1000"
             style="width: {{ ($currentStep / 3) * 100 }}%"></div>

        @foreach($steps as $stepIndex => $step)
            <div class="relative z-10 flex flex-col items-center">
                <div class="flex h-10 w-10 items-center justify-center rounded-full transition-colors duration-500 {{ $stepIndex <= $currentStep ? 'bg-orange-500 text-white shadow-lg' : 'border-4 border-gray-100 bg-white text-gray-300' }}">
                    @if($stepIndex < $currentStep)
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path>
                        </svg>
                    @elseif($stepIndex === $currentStep)
                        <div class="h-3 w-3 animate-ping rounded-full bg-white"></div>
                    @else
                        <span class="text-sm font-bold">{{ $stepIndex + 1 }}</span>
                    @endif
                </div>
                <span class="absolute top-12 w-20 text-center text-[10px] font-black uppercase tracking-tighter {{ $stepIndex <= $currentStep ? 'text-orange-600' : 'text-gray-300' }}">
                    {{ $step }}
                </span>
            </div>
        @endforeach
    </div>

    <div class="mb-6 rounded-3xl bg-gray-50 p-6">
        <div class="flex items-center gap-4">
            <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-white text-2xl shadow-sm">
                {{ $order->rider ? '🛵' : '⏳' }}
            </div>
            <div>
                <p class="text-[10px] font-bold uppercase text-gray-400">Repartidor asignado</p>
                <p class="font-bold text-gray-800">{{ $order->rider->name ?? 'Buscando repartidor...' }}</p>
            </div>
        </div>
        <div class="mt-5 border-t border-gray-200 pt-4">
            <p class="text-[10px] font-bold uppercase text-gray-400">Dirección de entrega</p>
            <p class="mt-1 whitespace-pre-line font-medium text-gray-800">{{ $order->address }}</p>
        </div>
    </div>

    @if($recommendedBusinesses->isNotEmpty())
        <section class="mb-6 rounded-3xl border border-orange-100 bg-orange-50 p-6">
            <h3 class="mb-3 text-lg font-black uppercase italic text-orange-700">
                {{ $recommendationTitle }}
            </h3>
            <div class="space-y-2">
                @foreach($recommendedBusinesses as $recommendedBusiness)
                    <a href="{{ route('business.menu', $recommendedBusiness) }}"
                       class="flex items-center justify-between rounded-xl bg-white p-3 font-bold text-gray-800 hover:bg-orange-100">
                        <span>{{ $recommendedBusiness->name }}</span>
                        <span class="text-sm font-black text-orange-700">Ver menú</span>
                    </a>
                @endforeach
            </div>
        </section>
    @endif

    <a href="/" class="flex w-full items-center justify-center gap-2 rounded-2xl bg-gray-900 py-4 font-bold text-white shadow-lg transition-all hover:bg-gray-800 active:scale-95">
        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
        </svg>
        Volver a la Tienda
    </a>
</div>
