<?php

namespace App\Livewire;

use App\Models\Business;
use App\Models\Product;
use App\Models\Order;
use Livewire\Component;
use MercadoPago\SDK;
use MercadoPago\Preference;
use MercadoPago\Item;

class QuickOrder extends Component
{
    public $search = '', $selectedBusiness = null, $selectedProducts = [], $total = 0, $delivery_cost = 0;
    public $customer_name, $phone, $address, $email, $payment_method = 'Efectivo';

    public function updatedSelectedBusiness($value) {
        $this->selectedProducts = []; 
        $biz = Business::find($value);
        $this->delivery_cost = $biz ? $biz->delivery_price : 0;
        $this->calculateTotal();
    }

    public function toggleProduct($productId) {
        $product = Product::find($productId);
        $this->selectedProducts[] = array_merge($product->toArray(), ['qty' => 1]);
        $this->calculateTotal();
    }

    public function removeProduct($index) {
        unset($this->selectedProducts[$index]);
        $this->selectedProducts = array_values($this->selectedProducts);
        $this->calculateTotal();
    }

    public function calculateTotal() {
        $subtotal = collect($this->selectedProducts)->sum(fn($i) => $i['price'] * $i['qty']);
        $this->total = $subtotal + $this->delivery_cost;
    }

    public function saveOrder() {
        $this->validate(['customer_name'=>'required','phone'=>'required','address'=>'required']);
        
        $order = Order::create([
            'customer_name' => $this->customer_name, 'phone' => $this->phone,
            'address' => $this->address, 'total' => $this->total,
            'business_id' => $this->selectedBusiness, 'status' => 'Pendiente',
            'payment_method' => $this->payment_method,
        ]);

        // Guardamos el teléfono en la sesión para rastrear sus múltiples pedidos
        session(['customer_phone' => $this->phone]);
        session(['active_order_id' => $order->id]);

        if ($this->payment_method === 'Tarjeta') {
            SDK::setAccessToken("APP_USR-4989168451157947-011121-a6123eaafaaa1fb98b919e7b9f88bb02-3127805417");
            $preference = new Preference();
            $item = new Item();
            $item->title = "Pedido #" . $order->id . " en Running";
            $item->quantity = 1;
            $item->unit_price = (float)$this->total;
            $preference->items = array($item);
            $preference->back_urls = ["success" => url("/"), "failure" => url("/")];
            $preference->save();
            return redirect($preference->init_point);
        }

        return redirect()->to('/rastreo/' . $order->id);
    }

    public function render() {
        // BUSCAMOS TODOS LOS PEDIDOS QUE NO ESTÉN ENTREGADOS DE ESTE USUARIO
        $phone = session('customer_phone');
        $allActiveOrders = Order::where('status', '!=', 'Entregado')
            ->when($phone, fn($q) => $q->where('phone', $phone))
            ->orderBy('created_at', 'desc')
            ->get();

        return view('livewire.quick-order', [
            'businesses' => Business::where('name', 'like', '%'.$this->search.'%')->get(),
            'products' => $this->selectedBusiness ? Product::where('business_id', $this->selectedBusiness)->get() : [],
            'activeOrders' => $allActiveOrders
        ]);
    }
}
