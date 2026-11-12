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
        @if($orders->isNotEmpty())
            <div wire:ignore class="rounded-2xl bg-white p-4 shadow-md">
                <p id="rider-location-status" class="mb-3 text-sm text-gray-700" aria-live="polite">
                    Tu ubicación solo se comparte mientras tienes pedidos en camino.
                </p>
                <div class="flex gap-2">
                    <button type="button" id="start-rider-location"
                            class="flex-1 rounded-xl bg-orange-600 px-4 py-3 text-sm font-black text-white">
                        Compartir GPS
                    </button>
                    <button type="button" id="stop-rider-location"
                            class="rounded-xl border border-gray-300 px-4 py-3 text-sm font-bold text-gray-700">
                        Detener
                    </button>
                </div>
            </div>
        @endif

        @error('location')
            <div class="rounded-xl bg-red-100 p-3 text-sm font-bold text-red-700">{{ $message }}</div>
        @enderror

        @forelse($orders as $order)
            <div class="bg-white p-5 rounded-[2rem] shadow-md border-2 border-orange-100">
                <div class="flex justify-between items-start mb-4">
                    <span class="bg-orange-600 text-white px-3 py-1 rounded-full font-black text-[10px]">#{{ $order->id }}</span>
                    <span class="text-gray-400 font-bold text-xs">{{ $order->created_at->diffForHumans() }}</span>
                </div>
                
                <h3 class="font-black text-lg uppercase mb-1">{{ $order->customer_name }}</h3>
                <p class="text-gray-600 text-sm mb-4 font-medium italic">{{ $order->address }}</p>

                <div class="grid grid-cols-2 gap-2">
                    @if($order->latitud)
                    <a href="https://www.google.com/maps?q={{ $order->latitud }},{{ $order->longitud }}" target="_blank" 
                       class="bg-blue-500 text-white p-4 rounded-2xl font-black text-center text-xs uppercase">
                        🗺️ Ver Mapa
                    </a>
                    @endif
                    <button wire:click="markAsDelivered({{ $order->id }})" 
                            class="bg-green-500 text-white p-4 rounded-2xl font-black text-center text-xs uppercase {{ !$order->latitud ? 'col-span-2' : '' }}">
                        ✅ Entregado
                    </button>
                </div>
            </div>

            @script
            <script>
                let riderLocationWatchId = null;
                let lastLocationSentAt = 0;
                const riderLocationStatus = document.getElementById('rider-location-status');
                const stopRiderLocationWatch = () => {
                    if (riderLocationWatchId !== null && navigator.geolocation) {
                        navigator.geolocation.clearWatch(riderLocationWatchId);
                        riderLocationWatchId = null;
                    }
                };

                Livewire.on('rider-location-sharing-stopped', () => {
                    stopRiderLocationWatch();
                    if (riderLocationStatus) {
                        riderLocationStatus.textContent = 'No hay pedidos en camino; se detuvo el uso del GPS.';
                    }
                });

                document.addEventListener('click', (event) => {
                    if (event.target.closest('#start-rider-location')) {
                        if (!navigator.geolocation) {
                            riderLocationStatus.textContent = 'Este navegador no permite compartir GPS.';
                            return;
                        }

                        if (riderLocationWatchId !== null) {
                            return;
                        }

                        riderLocationStatus.textContent = 'Solicitando permiso de ubicación…';
                        riderLocationWatchId = navigator.geolocation.watchPosition(
                            ({ coords }) => {
                                const now = Date.now();
                                if (now - lastLocationSentAt < 10000) {
                                    return;
                                }

                                lastLocationSentAt = now;
                                $wire.updateLocation(coords.latitude, coords.longitude);
                                riderLocationStatus.textContent = `GPS compartido: ${coords.latitude.toFixed(5)}, ${coords.longitude.toFixed(5)}.`;
                            },
                            () => {
                                riderLocationStatus.textContent = 'No se pudo acceder al GPS. Revisa el permiso de ubicación del navegador.';
                                if (riderLocationWatchId !== null) {
                                    stopRiderLocationWatch();
                                }
                                $wire.stopSharingLocation();
                            },
                            { enableHighAccuracy: true, maximumAge: 10000, timeout: 15000 },
                        );
                    }

                    if (event.target.closest('#stop-rider-location')) {
                        stopRiderLocationWatch();

                        $wire.stopSharingLocation();
                        if (riderLocationStatus) {
                            riderLocationStatus.textContent = 'Dejaste de compartir tu ubicación.';
                        }
                    }
                });
            </script>
            @endscript
        @empty
            <div class="text-center py-20 opacity-30 italic font-black text-gray-400 uppercase">
                No tienes pedidos asignados por ahora.
            </div>
        @endforelse
    </div>
</div>
