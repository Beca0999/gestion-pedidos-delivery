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

        if (! Order::where('rider_id', $rider->id)->where('status', 'En Camino')->exists()) {
            $rider->update([
                'latitude' => null,
                'longitude' => null,
                'location_updated_at' => null,
            ]);
            $this->dispatch('rider-location-sharing-stopped');
        }

        session()->flash('message', '¡Entregado! El cliente ya no verá la burbuja. ✅');
    }

    public function updateLocation(mixed $latitude, mixed $longitude): void
    {
        $coordinates = validator(
            compact('latitude', 'longitude'),
            [
                'latitude' => ['required', 'numeric', 'between:-90,90'],
                'longitude' => ['required', 'numeric', 'between:-180,180'],
            ],
        )->validate();

        $rider = auth()->user()->rider;
        abort_unless($rider, 403);

        if (! Order::where('rider_id', $rider->id)->where('status', 'En Camino')->exists()) {
            $this->addError('location', 'Solo puedes compartir tu ubicación mientras tienes un pedido en camino.');

            return;
        }

        $rider->update([
            'latitude' => $coordinates['latitude'],
            'longitude' => $coordinates['longitude'],
            'location_updated_at' => now(),
        ]);
    }

    public function stopSharingLocation(): void
    {
        $rider = auth()->user()->rider;
        abort_unless($rider, 403);

        $rider->update([
            'latitude' => null,
            'longitude' => null,
            'location_updated_at' => null,
        ]);
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
