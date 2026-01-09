<?php

namespace App\Livewire;

use App\Models\Business;
use App\Models\Product;
use App\Models\Order;
use App\Mail\OrderConfirmed;
use Illuminate\Support\Facades\Mail;
use Livewire\Component;

class QuickOrder extends Component
{
    public $businesses = [];
    public $selectedBusiness = null;
    public $products = [];
    public $selectedProducts = []; 
    public $total = 0;
    
    public $customer_name = '';
    public $phone = '';
    public $address = '';
    public $email = '';
    
    public $orderFinished = false;

    public function mount() {
        $this->businesses = Business::all();
    }

    public function updatedSelectedBusiness($value) {
        $this->products = Product::where('business_id', $value)->get();
        $this->clearCart();
    }

    public function toggleProduct($productId) {
        $product = Product::find($productId);
        $collection = collect($this->selectedProducts);
        
        if ($collection->contains('id', $productId)) {
            $this->selectedProducts = $collection->map(function($item) use ($productId) {
                if ($item['id'] === $productId) $item['qty']++;
                return $item;
            })->toArray();
        } else {
            $this->selectedProducts[] = array_merge($product->toArray(), ['qty' => 1]);
        }
        $this->calculateTotal();
    }

    public function removeOne($productId) {
        $collection = collect($this->selectedProducts);
        $this->selectedProducts = $collection->map(function($item) use ($productId) {
            if ($item['id'] === $productId && $item['qty'] > 0) $item['qty']--;
            return $item;
        })->filter(fn($item) => $item['qty'] > 0)->toArray();
        $this->calculateTotal();
    }

    public function calculateTotal() {
        $this->total = collect($this->selectedProducts)->reduce(function ($carry, $item) {
            return $carry + ($item['price'] * $item['qty']);
        }, 0);
    }

    public function clearCart() {
        $this->reset(['selectedProducts', 'total', 'customer_name', 'phone', 'address', 'email', 'orderFinished']);
    }

    public function saveOrder() {
        $this->validate([
            'customer_name' => 'required',
            'phone' => 'required',
            'address' => 'required',
            'email' => 'required|email',
            'selectedProducts' => 'required|array|min:1'
        ]);

        $order = Order::create([
            'customer_name' => $this->customer_name,
            'phone' => $this->phone,
            'address' => $this->address,
            'email' => $this->email,
            'total' => $this->total,
            'status' => 'Pendiente',
        ]);

        // Enviar Correo
        try {
            Mail::to($this->email)->send(new OrderConfirmed($order));
        } catch (\Exception $e) {
            // Si el correo falla (por config de SMTP), el pedido se guarda igual
        }

        $this->orderFinished = true;
    }

    public function render() {
        return view('livewire.quick-order');
    }
}
