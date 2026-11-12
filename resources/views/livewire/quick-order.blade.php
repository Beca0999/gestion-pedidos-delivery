<div class="max-w-4xl mx-auto p-4 pb-32 relative">
    
    {{-- SISTEMA DE ÓRDENES DESPLEGABLE --}}
    @if($activeOrders->count() > 0)
        <div x-data="{ open: false }" class="fixed bottom-6 right-6 z-50 flex flex-col items-end">
            <div x-show="open" x-transition class="mb-4 w-72 bg-white rounded-[2rem] shadow-2xl border-2 border-orange-500 overflow-hidden">
                <div class="bg-orange-500 p-4 flex justify-between items-center text-white font-black italic uppercase text-xs">
                    <span>Mis Pedidos Activos</span>
                    <button @click="open = false">✕</button>
                </div>
                <div class="max-h-80 overflow-y-auto p-2 space-y-2 bg-gray-50">
                    @foreach($activeOrders as $order)
                        <a href="{{ url('/rastreo/'.$order->id) }}" class="flex items-center gap-3 bg-white p-3 rounded-2xl border border-gray-100 hover:border-orange-500">
                            <div class="bg-orange-100 text-orange-600 p-2 rounded-xl">🛵</div>
                            <div class="flex flex-col">
                                <span class="text-[10px] font-black text-gray-400">#{{ $order->id }}</span>
                                <span class="text-xs font-bold uppercase italic text-orange-600">{{ $order->status }}</span>
                            </div>
                        </a>
                    @endforeach
                </div>
            </div>
            <button @click="open = !open" class="flex items-center gap-3 bg-black text-white p-3 pr-6 rounded-full shadow-2xl border-2 border-orange-500">
                <div class="relative bg-orange-500 p-2 rounded-full animate-bounce">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path></svg>
                    <span class="absolute -top-1 -right-1 bg-red-600 text-white text-[10px] font-black w-5 h-5 rounded-full flex items-center justify-center border-2 border-black">{{ $activeOrders->count() }}</span>
                </div>
                <span class="text-sm font-black italic uppercase">VER PEDIDOS</span>
            </button>
        </div>
    @endif

    @if(!$selectedBusiness)
        <div class="mb-8">
            <h1 class="text-4xl font-black text-orange-600 italic uppercase tracking-tighter">RUNNING 🚀</h1>
            <input wire:model.live="search" type="text" placeholder="¿Qué se te antoja?" class="w-full p-5 bg-gray-100 border-none rounded-[2rem] focus:ring-4 focus:ring-orange-500/20 font-bold">
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            @foreach($businesses as $biz)
                <div wire:click="$set('selectedBusiness', {{ $biz->id }})" class="group bg-white rounded-[2.5rem] border-2 border-orange-50 overflow-hidden cursor-pointer hover:shadow-2xl hover:border-orange-500 transition-all hover:-translate-y-1">
                    <div class="h-44 bg-gray-200 overflow-hidden relative">
                        @if($biz->image)
                            <img src="{{ asset('storage/' . $biz->image) }}" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-700">
                        @else
                            <div class="w-full h-full flex items-center justify-center bg-orange-100 text-orange-500 font-black italic text-2xl">RUNNING</div>
                        @endif
                    </div>
                    <div class="p-6">
                        <h3 class="text-2xl font-black text-gray-900 italic uppercase tracking-tighter">{{ $biz->name }}</h3>
                        <p class="text-orange-600 font-black italic">🚀 ENVÍO ${{ $biz->delivery_price }}</p>
                    </div>
                </div>
            @endforeach
        </div>
        @if($recommendedBusinesses->isNotEmpty())
            <section class="mt-8 rounded-3xl border border-orange-100 bg-white p-6 shadow-sm">
                <h2 class="mb-4 text-xl font-black uppercase italic text-orange-600">
                    {{ $businessRecommendationTitle }}
                </h2>
                <div class="grid gap-3 md:grid-cols-3">
                    @foreach($recommendedBusinesses as $recommendedBusiness)
                        <button type="button"
                                wire:click="browseRecommendedBusiness({{ $recommendedBusiness->id }})"
                                class="rounded-2xl bg-orange-50 p-4 text-left hover:bg-orange-100">
                            <span class="block font-black text-gray-900">{{ $recommendedBusiness->name }}</span>
                            <span class="mt-1 block text-sm text-orange-700">Ver menú</span>
                        </button>
                    @endforeach
                </div>
            </section>
        @endif
    @else
        <button wire:click="$set('selectedBusiness', null)" class="mb-6 bg-orange-100 text-orange-600 px-8 py-4 rounded-[1.5rem] font-black text-xs uppercase italic">← REGRESAR</button>
        @if($selectedBusinessRecord)
            <h1 class="mb-6 text-3xl font-black uppercase italic tracking-tight text-gray-900">
                {{ $selectedBusinessRecord->name }}
            </h1>
        @endif
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
            <div>
                <h2 class="text-3xl font-black mb-6 italic uppercase tracking-tighter text-gray-800">EL MENÚ 🔥</h2>
                <div class="space-y-4">
                    @foreach($products as $prod)
                        <div wire:click="toggleProduct({{ $prod->id }})" class="bg-white p-4 rounded-[2rem] border-2 border-transparent hover:border-orange-500 cursor-pointer shadow-sm flex justify-between items-center group">
                            <div class="flex items-center gap-4">
                                @if($prod->image)
                                    <img src="{{ asset('storage/' . $prod->image) }}" class="w-16 h-16 rounded-2xl object-cover shadow-sm">
                                @else
                                    <div class="w-16 h-16 bg-gray-100 rounded-2xl flex items-center justify-center text-xl">🍕</div>
                                @endif
                                <div class="flex flex-col">
                                    <span class="font-black text-gray-800 text-lg uppercase italic group-hover:text-orange-600 transition-colors">{{ $prod->name }}</span>
                                    <span class="text-[10px] text-gray-400 font-bold uppercase tracking-widest">Toca para agregar</span>
                                </div>
                            </div>
                            <span class="bg-orange-50 text-orange-600 px-5 py-2 rounded-2xl font-black text-xl shadow-sm">${{ $prod->price }}</span>
                        </div>
                    @endforeach
                </div>
                @if(count($selectedProducts) && count($businessProductRecommendations))
                    <section class="mt-6 rounded-3xl border border-orange-100 bg-white p-5 shadow-sm">
                        <h3 class="mb-3 font-black uppercase italic text-orange-600">
                            Sugerencias para ti
                        </h3>
                        <div class="space-y-2">
                            @foreach($businessProductRecommendations as $recommendedProduct)
                                <div class="flex items-center justify-between gap-3 rounded-xl bg-orange-50 p-3">
                                    <div>
                                        <p class="font-bold text-gray-800">
                                            Si ya agregaste {{ $recommendedProduct['source_name'] }},
                                            quizá también te guste {{ $recommendedProduct['name'] }}
                                        </p>
                                        <p class="text-sm text-gray-600">
                                            {{ $selectedBusinessRecord?->name }} · ${{ number_format($recommendedProduct['price'], 2) }}
                                        </p>
                                    </div>
                                    <button type="button" wire:click="toggleProduct({{ $recommendedProduct['id'] }})"
                                        class="shrink-0 rounded-lg bg-orange-600 px-3 py-2 text-xs font-black text-white hover:bg-orange-700">
                                        Agregar
                                    </button>
                                </div>
                            @endforeach
                        </div>
                    </section>
                @endif
                @if(count($businessRecommendations))
                    <section class="mt-6 rounded-3xl border border-orange-100 bg-white p-5 shadow-sm">
                        <h3 class="mb-3 font-black uppercase italic text-orange-600">También visitan estos restaurantes</h3>
                        <div class="space-y-2">
                            @foreach($businessRecommendations as $recommendedBusiness)
                                <button type="button" wire:click="browseRecommendedBusiness({{ $recommendedBusiness->id }})"
                                    @disabled(count($selectedProducts) > 0)
                                    class="flex w-full items-center justify-between gap-3 rounded-xl bg-orange-50 p-3 text-left hover:bg-orange-100 disabled:cursor-not-allowed disabled:opacity-50">
                                    <span class="font-bold text-gray-800">{{ $recommendedBusiness->name }}</span>
                                    <span class="text-xs font-black text-orange-600">Ver menú</span>
                                </button>
                            @endforeach
                        </div>
                    </section>
                @endif
            </div>

            <div class="bg-orange-600 text-white p-8 rounded-[3rem] shadow-2xl h-fit sticky top-4 border-4 border-orange-500/50">
                <h2 class="text-3xl font-black mb-6 italic tracking-tighter uppercase">MI BOLSA 🛍️</h2>
                <div class="space-y-3 mb-8">
                    @forelse($selectedProducts as $idx => $item)
                        <div class="flex justify-between items-center bg-white/10 p-5 rounded-3xl backdrop-blur-md">
                            <div class="flex flex-col gap-2">
                                <span class="font-bold uppercase italic text-sm">{{ $item['name'] }}</span>
                                <div class="flex items-center gap-3">
                                    <button type="button" wire:click.stop="changeQuantity({{ $idx }}, -1)"
                                        aria-label="Quitar una unidad de {{ $item['name'] }}"
                                        class="flex h-7 w-7 items-center justify-center rounded-full bg-white/20 font-black hover:bg-white hover:text-orange-600">−</button>
                                    <span class="text-sm font-black" aria-label="Cantidad">{{ $item['qty'] }} pza.</span>
                                    <button type="button" wire:click.stop="changeQuantity({{ $idx }}, 1)"
                                        aria-label="Agregar una unidad de {{ $item['name'] }}"
                                        class="flex h-7 w-7 items-center justify-center rounded-full bg-white/20 font-black hover:bg-white hover:text-orange-600">+</button>
                                </div>
                            </div>
                            <div class="flex items-center gap-4">
                                <span class="font-black italic text-lg">${{ number_format($item['price'] * $item['qty'], 2) }}</span>
                                <button wire:click.stop="removeProduct({{ $idx }})" class="bg-white/20 hover:bg-white text-orange-600 w-10 h-10 rounded-full flex items-center justify-center transition-all font-black">✕</button>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-12 border-4 border-dashed border-white/20 rounded-[2.5rem] font-black italic opacity-40 uppercase tracking-widest text-sm italic text-white">¡Bolsa vacía!</div>
                    @endforelse
                </div>
                @if(count($selectedProducts) > 0)
                    <div class="border-t-4 border-white/10 pt-6 mb-8">
                        <div class="mb-2 flex justify-between text-sm font-bold">
                            <span>Subtotal</span>
                            <span>${{ number_format($subtotal, 2) }}</span>
                        </div>
                        <div class="mb-4 flex justify-between text-sm font-bold">
                            <span>Envío</span>
                            <span>{{ $delivery_cost == 0 ? 'Gratis' : '$' . number_format($delivery_cost, 2) }}</span>
                        </div>
                        @if($subtotal < 300)
                            <p class="mb-4 text-sm font-bold text-white/90">
                                Agrega ${{ number_format(300 - $subtotal, 2) }} para obtener envío gratis en este restaurante.
                            </p>
                        @else
                            <p class="mb-4 text-sm font-black text-white">¡Envío gratis aplicado por compra de $300 o más!</p>
                        @endif
                        <div class="flex justify-between text-5xl font-black italic text-white tracking-tighter uppercase"><span>TOTAL</span><span>${{ $total }}</span></div>
                    </div>
                    @if(!auth()->check())
                        <div class="rounded-2xl border border-white/30 bg-black/15 p-5">
                            <h3 class="text-lg font-black">Para pedir, inicia sesión</h3>
                            <p class="mt-2 text-sm text-white/90">
                                Puedes armar tu bolsa, pero necesitas una cuenta de cliente para confirmar el pedido.
                            </p>
                            <div class="mt-4 grid gap-3">
                                <a href="{{ route('customer.register') }}"
                                   class="rounded-xl bg-white px-4 py-3 text-center font-black text-orange-700 hover:bg-orange-50">
                                    Crear cuenta de cliente
                                </a>
                                <a href="{{ route('login') }}"
                                   class="rounded-xl border border-white/70 px-4 py-3 text-center font-bold text-white hover:bg-white/10">
                                    Ya tengo cuenta — iniciar sesión
                                </a>
                            </div>
                        </div>
                    @elseif(!auth()->user()->hasRole('customer'))
                        <div class="rounded-2xl border border-white/30 bg-black/15 p-5">
                            <h3 class="text-lg font-black">Esta cuenta no puede realizar pedidos</h3>
                            <p class="mt-2 text-sm text-white/90">
                                Cierra esta sesión e inicia sesión con una cuenta de cliente.
                            </p>
                            <form method="POST" action="{{ route('logout') }}" class="mt-4">
                                @csrf
                                <button type="submit"
                                        class="w-full rounded-xl bg-white px-4 py-3 font-black text-orange-700 hover:bg-orange-50">
                                    Cambiar de cuenta
                                </button>
                            </form>
                        </div>
                    @else
                    <div class="space-y-4">
                        <input wire:model="customer_name" type="text" placeholder="¿QUIÉN RECIBE?" class="w-full p-5 rounded-2xl border-none font-black text-gray-900 shadow-xl">
                        <input wire:model="phone" type="tel" placeholder="WHATSAPP" class="w-full p-5 rounded-2xl border-none font-black text-gray-900 shadow-xl">
                        <input wire:model="address" type="text" placeholder="DIRECCIÓN" class="w-full p-5 rounded-2xl border-none font-black text-gray-900 shadow-xl">
                        <div class="rounded-2xl bg-white p-4 text-gray-900 shadow-xl">
                            <div class="flex flex-wrap items-center justify-between gap-3">
                                <div>
                                    <h3 class="font-black">Ubicación de entrega</h3>
                                    <p class="text-sm text-gray-600">Usa tu ubicación o toca el mapa para marcar el domicilio exacto.</p>
                                </div>
                                <button type="button" id="use-delivery-location"
                                        class="rounded-xl bg-orange-600 px-4 py-2 text-sm font-black text-white hover:bg-orange-700">
                                    Usar mi ubicación
                                </button>
                            </div>
                            <div id="delivery-map" wire:ignore class="mt-3 h-64 rounded-xl"></div>
                            <p id="delivery-map-status" class="mt-2 text-sm text-gray-600" aria-live="polite">
                                @if(!is_null($deliveryLatitude) && !is_null($deliveryLongitude))
                                    Destino marcado: {{ number_format((float) $deliveryLatitude, 6) }}, {{ number_format((float) $deliveryLongitude, 6) }}.
                                @else
                                    Marca el punto de entrega en el mapa para continuar.
                                @endif
                            </p>
                            @error('deliveryLatitude')
                                <p class="mt-2 text-sm font-bold text-red-600">Marca la ubicación de entrega en el mapa.</p>
                            @enderror
                            @error('deliveryLongitude')
                                <p class="mt-2 text-sm font-bold text-red-600">Marca la ubicación de entrega en el mapa.</p>
                            @enderror
                        </div>
                        <select wire:model="payment_method" class="w-full p-5 rounded-2xl font-black border-none text-white bg-black/30">
                            <option value="Efectivo" class="text-black uppercase">💵 EFECTIVO</option>
                            <option value="Tarjeta" class="text-black uppercase">💳 TARJETA</option>
                        </select>
                        <button wire:click="saveOrder" class="w-full bg-white text-orange-600 p-7 rounded-[2.5rem] font-black text-3xl shadow-2xl hover:scale-105 active:scale-95 transition-all uppercase italic tracking-tighter">¡DESPEGA YA! 🚀</button>
                        @if($errors->has('auth'))
                            <div class="mt-4 text-sm text-red-100 bg-red-600 p-3 rounded-lg font-bold">{{ $errors->first('auth') }}</div>
                        @endif
                        @if($errors->has('save'))
                            <div class="mt-4 text-sm text-red-100 bg-red-600 p-3 rounded-lg font-bold">{{ $errors->first('save') }}</div>
                        @endif
                        @if($errors->has('quantity'))
                            <div class="mt-4 text-sm text-red-100 bg-red-600 p-3 rounded-lg font-bold">{{ $errors->first('quantity') }}</div>
                        @endif
                        @if($errors->has('business-switch'))
                            <div class="mt-4 text-sm text-red-100 bg-red-600 p-3 rounded-lg font-bold">{{ $errors->first('business-switch') }}</div>
                        @endif
                        @if($errors->has('payment'))
                            <div class="mt-4 text-sm text-red-100 bg-red-600 p-3 rounded-lg font-bold">{{ $errors->first('payment') }}</div>
                        @endif
                        @if($errors->has('payment') && session('active_order_id'))
                            <button type="button" wire:click="retryCardPayment"
                                class="w-full rounded-xl bg-white px-4 py-3 font-black text-orange-700">
                                Reintentar pago del pedido #{{ session('active_order_id') }}
                            </button>
                        @endif
                    </div>

                    @script
                    <script>
                        const deliveryMapElement = document.getElementById('delivery-map');

                        if (deliveryMapElement && !deliveryMapElement.dataset.initialized) {
                            deliveryMapElement.dataset.initialized = 'true';
                            const status = document.getElementById('delivery-map-status');
                            const map = L.map(deliveryMapElement).setView([20, 0], 2);
                            let destinationMarker = null;

                            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                                maxZoom: 19,
                                attribution: '&copy; OpenStreetMap contributors',
                            }).addTo(map);

                            const setDeliveryPoint = (latitude, longitude, zoom = 16) => {
                                const point = [latitude, longitude];
                                map.setView(point, zoom);

                                if (destinationMarker) {
                                    destinationMarker.setLatLng(point);
                                } else {
                                    destinationMarker = L.marker(point, { draggable: true }).addTo(map);
                                    destinationMarker.on('dragend', (event) => {
                                        const position = event.target.getLatLng();
                                        $wire.set('deliveryLatitude', position.lat, false);
                                        $wire.set('deliveryLongitude', position.lng, false);
                                        status.textContent = `Destino marcado: ${position.lat.toFixed(6)}, ${position.lng.toFixed(6)}.`;
                                    });
                                }

                                $wire.set('deliveryLatitude', latitude, false);
                                $wire.set('deliveryLongitude', longitude, false);
                                status.textContent = `Destino marcado: ${latitude.toFixed(6)}, ${longitude.toFixed(6)}.`;
                            };

                            map.on('click', (event) => setDeliveryPoint(event.latlng.lat, event.latlng.lng));

                            const useLocationButton = document.getElementById('use-delivery-location');
                            useLocationButton?.addEventListener('click', () => {
                                if (!navigator.geolocation) {
                                    status.textContent = 'Este navegador no permite obtener la ubicación. Puedes marcarla tocando el mapa.';
                                    return;
                                }

                                status.textContent = 'Obteniendo ubicación del dispositivo…';
                                navigator.geolocation.getCurrentPosition(
                                    ({ coords }) => setDeliveryPoint(coords.latitude, coords.longitude),
                                    () => {
                                        status.textContent = 'No se pudo obtener la ubicación. Revisa el permiso del navegador o marca el punto en el mapa.';
                                    },
                                    { enableHighAccuracy: true, timeout: 10000, maximumAge: 30000 },
                                );
                            });

                            const hasInitialPoint = $wire.deliveryLatitude !== null
                                && $wire.deliveryLatitude !== ''
                                && $wire.deliveryLongitude !== null
                                && $wire.deliveryLongitude !== '';
                            const initialLatitude = Number($wire.deliveryLatitude);
                            const initialLongitude = Number($wire.deliveryLongitude);
                            if (hasInitialPoint && Number.isFinite(initialLatitude) && Number.isFinite(initialLongitude)) {
                                setDeliveryPoint(initialLatitude, initialLongitude);
                            }
                        }
                    </script>
                    @endscript
                    @endif
                @endif
            </div>
        </div>
    @endif
</div>
