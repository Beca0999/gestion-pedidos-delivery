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

    public function render() {
        abort_unless(auth()->user()?->hasRole('customer'), 403);

        $order = Order::with(['business', 'rider'])
            ->where('customer_id', auth()->id())
            ->findOrFail($this->orderId);
        $recommendationService = app(BusinessRecommendationService::class);
        $recommendedBusinesses = $recommendationService->forBusiness($order->business);
        $recommendationTitle = 'También podrías pedir en';
        if ($recommendedBusinesses->isEmpty()) {
            $recommendedBusinesses = $recommendationService->popular(3, $order->business_id);
            $recommendationTitle = 'Otros negocios populares';
        }

        return view('livewire.order-tracking', [
            'order' => $order,
            'recommendedBusinesses' => $recommendedBusinesses,
            'recommendationTitle' => $recommendationTitle,
        ])
            ->layout('layouts.app'); 
    }
}
