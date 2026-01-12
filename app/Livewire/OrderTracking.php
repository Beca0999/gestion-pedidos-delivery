<?php
namespace App\Livewire;

use App\Models\Order;
use Livewire\Component;

class OrderTracking extends Component
{
    public $orderId;

    public function mount($id) {
        $this->orderId = $id;
    }

    public function render() {
        $order = Order::with(['business', 'rider'])->findOrFail($this->orderId);
        return view('livewire.order-tracking', ['order' => $order])
            ->layout('layouts.app'); 
    }
}
