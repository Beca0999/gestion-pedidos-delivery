<div class="max-w-md mx-auto bg-white shadow-xl rounded-2xl p-6 mt-10 border border-gray-100">
    <div class="text-center mb-6">
        <h2 class="text-3xl font-extrabold text-gray-900">Menú Digital</h2>
        <p class="text-gray-500">Selecciona tus productos favoritos</p>
    </div>

    @if (session()->has('message'))
        <div class="bg-green-50 border-l-4 border-green-500 text-green-700 p-4 mb-6 rounded shadow-sm">
            {{ session('message') }}
        </div>
    @endif

    <form wire:submit.prevent="saveOrder" class="space-y-5">
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Negocio:</label>
            <select wire:model.live="selectedBusiness" class="w-full border-gray-300 rounded-lg shadow-sm">
                <option value="">¿Dónde quieres pedir?</option>
                @foreach($businesses as $biz)
                    <option value="{{ $biz->id }}">{{ $biz->name }}</option>
                @endforeach
            </select>
        </div>

        @if($selectedBusiness)
            <div class="grid grid-cols-1 gap-3">
                @foreach($products as $prod)
                    <label class="relative flex items-center p-3 bg-white rounded-xl border hover:border-orange-500 cursor-pointer transition shadow-sm">
                        <input type="checkbox" wire:model.live="selectedProducts" value="{{ $prod->id }}" class="hidden">
                        
                        @if($prod->image)
                            <img src="{{ asset('storage/' . $prod->image) }}" class="w-16 h-16 rounded-lg object-cover mr-4">
                        @else
                            <div class="w-16 h-16 bg-gray-200 rounded-lg mr-4 flex items-center justify-center text-xs text-gray-400">Sin foto</div>
                        @endif

                        <div class="flex-1">
                            <h3 class="font-bold text-gray-800">{{ $prod->name }}</h3>
                            <p class="text-orange-600 font-bold">${{ number_format($prod->price, 2) }}</p>
                        </div>

                        @if(in_array($prod->id, $selectedProducts))
                            <div class="text-orange-600">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" />
                                </svg>
                            </div>
                        @endif
                    </label>
                @endforeach
            </div>
        @endif

        @if($total > 0)
            <div class="p-4 bg-gray-900 rounded-xl text-white flex justify-between items-center shadow-lg">
                <span class="text-sm">Total acumulado:</span>
                <span class="text-xl font-black">${{ number_format($total, 2) }}</span>
            </div>
        @endif

        <div class="space-y-3 pt-4 border-t">
            <input type="text" wire:model="customer_name" class="w-full border-gray-300 rounded-lg shadow-sm" placeholder="Tu nombre">
            <input type="text" wire:model="phone" class="w-full border-gray-300 rounded-lg shadow-sm" placeholder="Teléfono de contacto">
        </div>

        <button type="submit" class="w-full bg-orange-600 text-white font-bold py-4 rounded-2xl hover:bg-orange-700 transition shadow-xl">
            Confirmar Pedido
        </button>
    </form>
</div>
