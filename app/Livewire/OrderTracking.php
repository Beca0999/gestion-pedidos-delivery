<?php
namespace App\Livewire;

use App\Models\Order;
use App\Services\BusinessRecommendationService;
use Livewire\Component;

class OrderTracking extends Component
{
    public $orderId;

    public function mount($id) {
        $this->orderId = $id;
    }

    public function refreshRiderLocation(): void
    {
        abort_unless(auth()->user()?->hasRole('customer'), 403);

        $order = Order::with('rider')
            ->where('customer_id', auth()->id())
            ->findOrFail($this->orderId);

        $rider = $order->rider;
        $isRecent = $order->status === 'En Camino'
            && ! is_null($rider?->latitude)
            && ! is_null($rider?->longitude)
            && ($rider?->location_updated_at?->greaterThan(now()->subMinutes(2)) ?? false);

        $this->dispatch(
            'rider-location-updated',
            latitude: $isRecent ? $rider->latitude : null,
            longitude: $isRecent ? $rider->longitude : null,
            updatedAt: $isRecent ? $rider->location_updated_at->diffForHumans() : null,
        );
    }

    public function render() {
        abort_unless(auth()->user()?->hasRole('customer'), 403);

        $order = Order::with(['business', 'rider'])
            ->where('customer_id', auth()->id())
            ->findOrFail($this->orderId);
        $recommendationService = app(BusinessRecommendationService::class);
        $recommendedBusinesses = $recommendationService->forBusiness($order->business);
        $recommendationTitle = 'También podrías pedir en';
        $riderLocationIsRecent = $order->status === 'En Camino'
            && ! is_null($order->rider?->latitude)
            && ! is_null($order->rider?->longitude)
            && ($order->rider?->location_updated_at?->greaterThan(now()->subMinutes(2)) ?? false);

        if ($recommendedBusinesses->isEmpty()) {
            $recommendedBusinesses = $recommendationService->popular(3, $order->business_id);
            $recommendationTitle = 'Otros negocios populares';
        }

        return view('livewire.order-tracking', [
            'order' => $order,
            'recommendedBusinesses' => $recommendedBusinesses,
            'recommendationTitle' => $recommendationTitle,
            'riderLocationIsRecent' => $riderLocationIsRecent,
        ])
            ->layout('layouts.app'); 
    }
}
