<?php
namespace App\Livewire;
use App\Models\Order;
use App\Models\Rider;
use Livewire\Component;

class RiderPanel extends Component
{
    public $access_code;
    public $is_authorized = false;
    public $rider_id;
    public $rider_name;

    public function checkAccess() {
        // El código de acceso es el teléfono que registraste en la tabla Riders
        $rider = Rider::where('phone', $this->access_code)->first();
        
        if ($rider) {
            $this->is_authorized = true;
            $this->rider_id = $rider->id;
            $this->rider_name = $rider->name;
        } else {
            session()->flash('error', 'Teléfono no registrado como Repartidor');
        }
    }

    public function markAsDelivered($orderId) {
        $order = Order::where('id', $orderId)->where('rider_id', $this->rider_id)->first();
        if ($order) {
            $order->update(['status' => 'Entregado']);
            session()->flash('message', '¡Entregado! El cliente ya no verá la burbuja. ✅');
        }
    }

    public function render() {
        $orders = [];
        if ($this->is_authorized) {
            // FILTRO CRUCIAL: Solo estatus 'En Camino' y que me pertenezcan a MÍ
            $orders = Order::where('status', 'En Camino')
                ->where('rider_id', $this->rider_id)
                ->orderBy('created_at', 'desc')
                ->get();
        }
        return view('livewire.rider-panel', ['orders' => $orders])->layout('layouts.app');
    }
}
