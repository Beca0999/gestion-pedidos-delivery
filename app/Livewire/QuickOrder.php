<?php

namespace App\Livewire;

use App\Exceptions\MercadoPagoCheckoutConfigurationException;
use App\Models\Business;
use App\Models\Order;
use App\Models\Product;
use App\Services\BusinessRecommendationService;
use App\Services\MercadoPagoCheckoutService;
use App\Services\ProductRecommendationService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Livewire\Component;

class QuickOrder extends Component
{
    public $search = '';

    public $selectedBusiness = null;

    public $selectedProducts = [];

    public $subtotal = 0;

    public $total = 0;

    public $delivery_cost = 0;

    public $businessProductRecommendations = [];

    public $businessRecommendations = [];

    public $customer_name;

    public $phone;

    public $address;

    public $deliveryLatitude;

    public $deliveryLongitude;

    public $email;

    public $payment_method = 'Efectivo';

    public function mount(?Business $business = null): void
    {
        $customer = auth()->user();

        if ($customer?->hasRole('customer')) {
            $this->customer_name = $customer->name;
            $this->email = $customer->email;
        }

        if ($business === null && request()->filled('business')) {
            $business = Business::find(request()->query('business'));
        }

        $selectedBusiness = $business;
        if ($selectedBusiness) {
            $this->selectedBusiness = $selectedBusiness->id;
            $this->updatedSelectedBusiness($selectedBusiness->id);
        }
    }

    public function updatedSelectedBusiness($value)
    {
        $this->selectedProducts = [];
        $this->businessProductRecommendations = [];
        $this->businessRecommendations = [];
        $biz = Business::find($value);
        $this->delivery_cost = $biz ? $biz->delivery_price : 0;
        if ($biz) {
            $this->businessRecommendations = app(BusinessRecommendationService::class)
                ->forBusiness($biz)
                ->all();
        }
        $this->calculateTotal();
    }

    public function browseRecommendedBusiness(int $businessId): void
    {
        $business = Business::findOrFail($businessId);

        if ((int) $this->selectedBusiness === $business->id) {
            return;
        }

        if ($this->selectedProducts !== []) {
            $this->addError('business-switch', 'Vacía tu bolsa actual antes de cambiar de restaurante; cada pedido se prepara en un solo negocio.');

            return;
        }

        $this->resetValidation('business-switch');
        $this->selectedBusiness = $business->id;
        $this->updatedSelectedBusiness($business->id);
    }

    public function toggleProduct($productId)
    {
        $product = Product::where('business_id', $this->selectedBusiness)->findOrFail($productId);
        $existingIndex = collect($this->selectedProducts)->search(
            fn ($item) => (int) ($item['id'] ?? 0) === $product->id,
        );

        if ($existingIndex !== false) {
            if ((int) ($this->selectedProducts[$existingIndex]['qty'] ?? 1) >= 99) {
                $this->addError('quantity', 'El máximo por producto es 99 piezas.');

                return;
            }

            $this->selectedProducts[$existingIndex]['qty'] =
                (int) ($this->selectedProducts[$existingIndex]['qty'] ?? 1) + 1;
        } else {
            $this->selectedProducts[] = array_merge($product->toArray(), ['qty' => 1]);
        }
        $this->resetValidation('quantity');
        $this->resetValidation('quantity');
        $this->refreshBusinessProductRecommendations();
        $this->calculateTotal();
    }

    public function changeQuantity(int $index, int $change): void
    {
        if (! in_array($change, [-1, 1], true) || ! isset($this->selectedProducts[$index])) {
            return;
        }

        $quantity = (int) ($this->selectedProducts[$index]['qty'] ?? 1) + $change;

        if ($quantity < 1) {
            $this->removeProduct($index);

            return;
        }

        if ($quantity > 99) {
            $this->addError('quantity', 'El máximo por producto es 99 piezas.');

            return;
        }

        $this->selectedProducts[$index]['qty'] = $quantity;
        $this->resetValidation('quantity');
        $this->calculateTotal();
    }

    public function removeProduct($index)
    {
        unset($this->selectedProducts[$index]);
        $this->selectedProducts = array_values($this->selectedProducts);
        $this->refreshBusinessProductRecommendations();
        $this->calculateTotal();
    }

    private function refreshBusinessProductRecommendations(): void
    {
        $business = $this->selectedBusiness
            ? Business::find($this->selectedBusiness)
            : null;
        $productIds = collect($this->selectedProducts)
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->all();

        $this->businessProductRecommendations = $business && $productIds !== []
            ? app(ProductRecommendationService::class)
                ->forProducts($business, $productIds)
                ->map(fn (Product $product) => [
                    'id' => $product->id,
                    'name' => $product->name,
                    'price' => (float) $product->price,
                    'source_id' => $product->recommendation_source_id,
                    'source_name' => $product->recommendation_source_name,
                    'confidence' => $product->recommendation_confidence,
                ])
                ->all()
            : [];
    }

    public function calculateTotal()
    {
        $this->subtotal = collect($this->selectedProducts)->sum(fn ($i) => $i['price'] * $i['qty']);
        $business = $this->selectedBusiness ? Business::find($this->selectedBusiness) : null;
        $baseDeliveryCost = (float) ($business?->delivery_price ?? 0);
        $this->delivery_cost = $this->subtotal >= 300 ? 0 : $baseDeliveryCost;
        $this->total = $this->subtotal + $this->delivery_cost;
    }

    public function saveOrder()
    {
        if (! auth()->user()?->hasRole('customer')) {
            $this->addError('auth', 'Para realizar un pedido, inicia sesión o crea una cuenta de cliente.');

            return;
        }

        $this->validate([
            'customer_name' => 'required|string|max:255',
            'phone' => 'required|string|max:30',
            'address' => 'required|string|max:1000',
            'deliveryLatitude' => 'required|numeric|between:-90,90',
            'deliveryLongitude' => 'required|numeric|between:-180,180',
            'selectedBusiness' => 'required|integer|exists:businesses,id',
            'selectedProducts' => 'required|array|min:1',
            'payment_method' => 'required|in:Efectivo,Tarjeta',
        ]);

        if ($this->payment_method === 'Tarjeta' && blank(config('services.mercadopago.access_token'))) {
            $this->addError('payment', 'El pago con tarjeta no está configurado. Selecciona efectivo o inténtalo más tarde.');

            return;
        }

        $business = Business::findOrFail($this->selectedBusiness);
        $quantities = collect($this->selectedProducts)
            ->groupBy(fn ($item) => (int) ($item['id'] ?? 0))
            ->map(fn ($items) => $items->sum(fn ($item) => (int) ($item['qty'] ?? 1)));
        $products = Product::where('business_id', $business->id)
            ->whereIn('id', $quantities->keys())
            ->get()
            ->keyBy('id');

        if ($products->count() !== $quantities->count() || $quantities->contains(fn ($qty) => $qty < 1 || $qty > 99)) {
            $this->addError('save', 'El menú cambió. Revisa los productos e inténtalo de nuevo.');

            return;
        }

        $subtotal = $products->sum(fn (Product $product) => (float) $product->price * $quantities->get($product->id));
        $this->subtotal = $subtotal;
        $this->delivery_cost = $subtotal >= 300 ? 0 : (float) $business->delivery_price;
        $this->total = $subtotal + $this->delivery_cost;

        try {
            $order = DB::transaction(function () use ($business, $products, $quantities) {
                $order = Order::create([
                    'customer_id' => auth()->id(),
                    'customer_name' => $this->customer_name,
                    'email' => auth()->user()->email,
                    'phone' => $this->phone,
                    'address' => $this->address,
                    'delivery_latitude' => $this->deliveryLatitude,
                    'delivery_longitude' => $this->deliveryLongitude,
                    'total' => $this->total,
                    'business_id' => $business->id,
                    'status' => 'Pendiente',
                    'payment_method' => $this->payment_method,
                ]);

                $order->products()->sync($products->mapWithKeys(fn (Product $product) => [
                    $product->id => [
                        'quantity' => $quantities->get($product->id),
                        'unit_price' => $product->price,
                    ],
                ])->all());

                return $order;
            });
        } catch (\Illuminate\Database\QueryException $e) {
            Log::error('No se pudo guardar el pedido.', [
                'exception' => $e::class,
            ]);
            $this->addError('save', 'No se pudo crear el pedido: datos incompletos o inválidos.');

            return;
        }

        // Guardamos el teléfono en la sesión para rastrear sus múltiples pedidos
        session(['customer_phone' => $this->phone]);
        session(['active_order_id' => $order->id]);

        if ($this->payment_method === 'Tarjeta') {
            return $this->redirectToCardCheckout($order);
        }

        return $this->redirectRoute('order.tracking', ['id' => $order->id], navigate: true);
    }

    public function retryCardPayment()
    {
        if (! auth()->user()?->hasRole('customer')) {
            $this->addError('payment', 'Inicia sesión con tu cuenta de cliente para reintentar el pago.');

            return null;
        }

        $order = Order::query()
            ->whereKey(session('active_order_id'))
            ->where('customer_id', auth()->id())
            ->where('payment_method', 'Tarjeta')
            ->where('payment_status', 'Pendiente')
            ->first();

        if (! $order) {
            $this->addError('payment', 'No encontramos un pedido pendiente de pago en esta sesión.');

            return null;
        }

        return $this->redirectToCardCheckout($order);
    }

    private function redirectToCardCheckout(Order $order)
    {
        try {
            $checkoutUrl = app(MercadoPagoCheckoutService::class)->createPreference($order);
        } catch (MercadoPagoCheckoutConfigurationException $exception) {
            Log::warning('La URL de regreso de Mercado Pago no está configurada como pública y HTTPS.', [
                'order_id' => $order->id,
            ]);
            $message = config('app.env') === 'local'
                ? $exception->getMessage()
                : 'No pudimos iniciar el pago en este momento. Tu pedido quedó guardado; intenta más tarde o elige efectivo.';
            $this->addError('payment', $message);

            return null;
        } catch (\Throwable $exception) {
            Log::error('No se pudo iniciar el checkout de MercadoPago.', [
                'order_id' => $order->id,
                'exception' => $exception::class,
            ]);

            $this->addError('payment', 'No se pudo iniciar el pago. Tu pedido quedó guardado; puedes reintentar o pagar en efectivo.');

            return null;
        }

        return redirect()->away($checkoutUrl);
    }

    public function render()
    {
        $customer = auth()->user();
        $allActiveOrders = $customer?->hasRole('customer')
            ? Order::where('customer_id', $customer->id)
                ->where('status', '!=', 'Entregado')
                ->orderByDesc('created_at')
                ->get()
            : collect();
        $recommendedBusinesses = collect();
        $businessRecommendationTitle = 'Negocios populares';

        if (! $this->selectedBusiness) {
            $recommendationService = app(BusinessRecommendationService::class);

            if ($customer?->hasRole('customer')) {
                $recommendedBusinesses = $recommendationService->forCustomer($customer);
                if ($recommendedBusinesses->isNotEmpty()) {
                    $businessRecommendationTitle = 'Recomendados para ti';
                }
            }

            if ($recommendedBusinesses->isEmpty()) {
                $recommendedBusinesses = $recommendationService->popular();
            }
        }

        return view('livewire.quick-order', [
            'businesses' => Business::where('name', 'like', '%'.$this->search.'%')->get(),
            'products' => $this->selectedBusiness ? Product::where('business_id', $this->selectedBusiness)->get() : [],
            'activeOrders' => $allActiveOrders,
            'recommendedBusinesses' => $recommendedBusinesses,
            'businessRecommendationTitle' => $businessRecommendationTitle,
            'selectedBusinessRecord' => $this->selectedBusiness
                ? Business::find($this->selectedBusiness)
                : null,
        ])->layout('layouts.app');
    }
}
