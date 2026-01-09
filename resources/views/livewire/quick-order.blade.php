<div class="max-w-6xl mx-auto p-6 bg-gray-50 min-h-screen font-sans text-gray-900">
    @if($orderFinished)
        <div class="flex flex-col items-center justify-center py-20 text-center animate-fade-in">
            <div class="w-24 h-24 bg-green-100 text-green-600 rounded-full flex items-center justify-center text-5xl mb-6 shadow-sm">✓</div>
            <h1 class="text-4xl font-black mb-2">¡Pedido Recibido!</h1>
            <p class="text-gray-500 mb-8 text-lg">Revisa tu correo para ver tu ticket digital.</p>
            <button wire:click="clearCart" class="bg-orange-500 text-white px-8 py-4 rounded-2xl font-bold shadow-lg hover:bg-orange-600 transition-all">Hacer otro pedido</button>
        </div>
    @else
        <div class="flex justify-between items-center mb-10">
            <h1 class="text-3xl font-black italic">Delivery <span class="text-orange-600">Express</span></h1>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            <div class="md:col-span-2 space-y-6">
                <div class="bg-white p-6 rounded-3xl shadow-sm border border-gray-100">
                    <select wire:model.live="selectedBusiness" class="w-full p-4 bg-gray-50 border-none rounded-2xl text-lg font-bold">
                        <option value="">¿Dónde pedimos hoy?</option>
                        @foreach($businesses as $biz)
                            <option value="{{ $biz->id }}">{{ $biz->name }}</option>
                        @endforeach
                    </select>
                </div>

                @if($selectedBusiness)
                    <div class="grid grid-cols-1 gap-4">
                        @foreach($products as $prod)
                            <div class="bg-white p-4 rounded-3xl shadow-sm border border-gray-50 flex items-center justify-between">
                                <div class="flex items-center">
                                    <div class="w-20 h-20 bg-gray-100 rounded-2xl overflow-hidden mr-4 italic text-xs flex items-center justify-center text-gray-400">Foto</div>
                                    <div>
                                        <h4 class="font-bold text-xl">{{ $prod->name }}</h4>
                                        <p class="text-orange-600 font-black text-2xl">${{ number_format($prod->price, 2) }}</p>
                                    </div>
                                </div>
                                <div class="flex items-center bg-gray-100 rounded-2xl p-1">
                                    <button wire:click="removeOne({{ $prod->id }})" class="w-10 h-10 flex items-center justify-center font-bold text-gray-500">-</button>
                                    <span class="px-4 font-bold text-lg">{{ collect($selectedProducts)->firstWhere('id', $prod->id)['qty'] ?? 0 }}</span>
                                    <button wire:click="toggleProduct({{ $prod->id }})" class="w-10 h-10 bg-white rounded-xl shadow-sm flex items-center justify-center font-bold text-orange-600">+</button>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

            <div class="md:col-span-1">
                <div class="bg-white p-8 rounded-3xl shadow-xl border border-gray-100 sticky top-10">
                    <h3 class="font-black text-2xl mb-6">🛒 Mi Bolsa</h3>
                    <div class="space-y-4 mb-8">
                        @forelse($selectedProducts as $item)
                            <div class="flex justify-between items-center text-sm">
                                <span><span class="font-bold text-orange-600">{{ $item['qty'] }}x</span> {{ $item['name'] }}</span>
                                <span class="font-bold">${{ number_format($item['price'] * $item['qty'], 2) }}</span>
                            </div>
                        @empty
                            <p class="text-gray-300 italic text-center py-10">Bolsa vacía</p>
                        @endforelse
                    </div>

                    @if($total > 0)
                        <div class="space-y-4 pt-6 border-t-2 border-gray-50">
                            <div class="flex justify-between items-center mb-6">
                                <span class="text-4xl font-black">${{ number_format($total, 2) }}</span>
                            </div>
                            <input type="text" wire:model="customer_name" placeholder="Tu nombre" class="w-full p-4 bg-gray-50 rounded-2xl outline-none">
                            <input type="text" wire:model="phone" placeholder="WhatsApp" class="w-full p-4 bg-gray-50 rounded-2xl outline-none">
                            <input type="email" wire:model="email" placeholder="Email para el ticket" class="w-full p-4 bg-gray-50 rounded-2xl outline-none border-2 border-orange-100">
                            <textarea wire:model="address" placeholder="Dirección de entrega..." class="w-full p-4 bg-gray-50 rounded-2xl outline-none h-24"></textarea>
                            <button wire:click="saveOrder" class="w-full bg-orange-600 text-white font-black py-5 rounded-2xl shadow-xl hover:bg-orange-700 transition-all">Confirmar Pedido</button>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    @endif
</div>
