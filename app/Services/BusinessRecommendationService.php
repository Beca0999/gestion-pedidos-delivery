<?php

namespace App\Services;

use App\Models\Business;
use App\Models\Order;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class BusinessRecommendationService
{
    public function forCustomer(User $customer, int $limit = 3): Collection
    {
        $lastBusinessId = Order::query()
            ->where('customer_id', $customer->id)
            ->where('status', 'Entregado')
            ->whereNotNull('business_id')
            ->latest('id')
            ->value('business_id');

        if ($lastBusinessId === null) {
            return new Collection();
        }

        $business = Business::find($lastBusinessId);

        return $business
            ? $this->forBusiness($business, $limit)
            : new Collection();
    }

    public function forBusiness(Business $business, int $limit = 3): Collection
    {
        $rows = DB::table('business_recommendations')
            ->where('business_id', $business->id)
            ->orderByDesc('confidence')
            ->orderByDesc('lift')
            ->limit(max(1, min($limit, 10)))
            ->get(['recommended_business_id']);

        $businesses = Business::query()
            ->whereIn('id', $rows->pluck('recommended_business_id'))
            ->get()
            ->keyBy('id');

        return new Collection($rows
            ->map(fn ($row) => $businesses->get($row->recommended_business_id))
            ->filter()
            ->values()
            ->all());
    }

    public function popular(int $limit = 3, ?int $exceptBusinessId = null): Collection
    {
        return Business::query()
            ->when(
                $exceptBusinessId !== null,
                fn ($query) => $query->whereKeyNot($exceptBusinessId),
            )
            ->withCount([
                'orders as delivered_orders_count' => fn ($query) => $query
                    ->where('status', 'Entregado'),
            ])
            ->orderByDesc('delivered_orders_count')
            ->orderBy('name')
            ->limit(max(1, min($limit, 10)))
            ->get();
    }
}
