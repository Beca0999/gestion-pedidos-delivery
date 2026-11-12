<?php
namespace App\Livewire;

use App\Models\Order;
use Livewire\Component;

class RiderPanel extends Component
{
    public function mount(): void
    {
        abort_unless(auth()->user()?->hasRole('rider'), 403);
        abort_unless(auth()->user()->rider, 403);
    }

    public function markAsDelivered(int $orderId): void
    {
        $rider = auth()->user()->rider;
        $order = Order::where('id', $orderId)->where('rider_id', $rider->id)->firstOrFail();
        $order->update(['status' => 'Entregado']);

        session()->flash('message', '¡Entregado! El cliente ya no verá la burbuja. ✅');
    }

    public function render()
    {
        $orders = Order::where('status', 'En Camino')
            ->where('rider_id', auth()->user()->rider->id)
            ->orderBy('created_at', 'desc')
            ->get();

        return view('livewire.rider-panel', ['orders' => $orders])->layout('layouts.app');
    }
}
