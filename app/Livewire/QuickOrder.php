<?php
namespace App\Livewire;

use App\Models\Business;
use App\Models\Product;
use App\Models\Order;
use Livewire\Component;

class QuickOrder extends Component
{
    public $businesses;
    public $selectedBusiness = null;
    public $products = [];
    public $selectedProducts = []; // IDs de productos seleccionados
    public $total = 0;
    
    public $customer_name;
    public $phone;

    public function mount() {
        $this->businesses = Business::all();
    }

    public function updatedSelectedBusiness($value) {
        $this->products = Product::where('business_id', $value)->get();
        $this->selectedProducts = [];
        $this->total = 0;
    }

    public function updatedSelectedProducts() {
        $this->total = Product::whereIn('id', $this->selectedProducts)->sum('price');
    }

    public function saveOrder() {
        $this->validate([
            'customer_name' => 'required|min:3',
            'phone' => 'required',
            'selectedBusiness' => 'required',
            'selectedProducts' => 'required|array|min:1',
        ]);

        Order::create([
            'customer_name' => $this->customer_name,
            'phone' => $this->phone,
            'total' => $this->total,
            'status' => 'Pendiente',
        ]);

        session()->flash('message', '¡Pedido recibido! Total a pagar: $' . $this->total);
        $this->reset(['customer_name', 'phone', 'selectedBusiness', 'selectedProducts', 'total', 'products']);
    }

    public function render() {
        return view('livewire.quick-order');
    }
}
