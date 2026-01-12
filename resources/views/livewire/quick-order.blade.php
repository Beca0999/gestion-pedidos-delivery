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
    @else
        <button wire:click="$set('selectedBusiness', null)" class="mb-6 bg-orange-100 text-orange-600 px-8 py-4 rounded-[1.5rem] font-black text-xs uppercase italic">← REGRESAR</button>
        
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
            </div>

            <div class="bg-orange-600 text-white p-8 rounded-[3rem] shadow-2xl h-fit sticky top-4 border-4 border-orange-500/50">
                <h2 class="text-3xl font-black mb-6 italic tracking-tighter uppercase">MI BOLSA 🛍️</h2>
                <div class="space-y-3 mb-8">
                    @forelse($selectedProducts as $idx => $item)
                        <div class="flex justify-between items-center bg-white/10 p-5 rounded-3xl backdrop-blur-md">
                            <span class="font-bold uppercase italic text-sm">{{ $item['name'] }}</span>
                            <div class="flex items-center gap-4">
                                <span class="font-black italic text-lg">${{ $item['price'] }}</span>
                                <button wire:click.stop="removeProduct({{ $idx }})" class="bg-white/20 hover:bg-white text-orange-600 w-10 h-10 rounded-full flex items-center justify-center transition-all font-black">✕</button>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-12 border-4 border-dashed border-white/20 rounded-[2.5rem] font-black italic opacity-40 uppercase tracking-widest text-sm italic text-white">¡Bolsa vacía!</div>
                    @endforelse
                </div>
                @if(count($selectedProducts) > 0)
                    <div class="border-t-4 border-white/10 pt-6 mb-8">
                        <div class="flex justify-between text-5xl font-black italic text-white tracking-tighter uppercase"><span>TOTAL</span><span>${{ $total }}</span></div>
                    </div>
                    <div class="space-y-4">
                        <input wire:model="customer_name" type="text" placeholder="¿QUIÉN RECIBE?" class="w-full p-5 rounded-2xl border-none font-black text-gray-900 shadow-xl">
                        <input wire:model="phone" type="tel" placeholder="WHATSAPP" class="w-full p-5 rounded-2xl border-none font-black text-gray-900 shadow-xl">
                        <input wire:model="address" type="text" placeholder="DIRECCIÓN" class="w-full p-5 rounded-2xl border-none font-black text-gray-900 shadow-xl">
                        <select wire:model="payment_method" class="w-full p-5 rounded-2xl font-black border-none text-white bg-black/30">
                            <option value="Efectivo" class="text-black uppercase">💵 EFECTIVO</option>
                            <option value="Tarjeta" class="text-black uppercase">💳 TARJETA</option>
                        </select>
                        <button wire:click="saveOrder" class="w-full bg-white text-orange-600 p-7 rounded-[2.5rem] font-black text-3xl shadow-2xl hover:scale-105 active:scale-95 transition-all uppercase italic tracking-tighter">¡DESPEGA YA! 🚀</button>
                    </div>
                @endif
            </div>
        </div>
    @endif
</div>
