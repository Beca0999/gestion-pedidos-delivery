<?php

namespace App\Services;

use App\Models\Business;
use App\Models\Product;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class ProductRecommendationService
{
    public function forBusiness(Business $business, int $limit = 3): Collection
    {
        $rows = DB::table('product_recommendations as recommendation')
            ->join('products as source', 'source.id', '=', 'recommendation.product_id')
            ->join('products as recommended', 'recommended.id', '=', 'recommendation.recommended_product_id')
            ->where('source.business_id', $business->id)
            ->where('recommended.business_id', $business->id)
            ->whereColumn('source.id', '<>', 'recommended.id')
            ->orderByDesc('recommendation.confidence')
            ->orderByDesc('recommendation.lift')
            ->get([
                'source.id as source_product_id',
                'source.name as source_product_name',
                'recommendation.recommended_product_id',
                'recommendation.confidence',
            ])
            ->unique('recommended_product_id')
            ->take(max(1, min($limit, 10)));

        $products = Product::query()
            ->with('business')
            ->whereIn('id', $rows->pluck('recommended_product_id'))
            ->get()
            ->keyBy('id');

        return new Collection($rows
            ->map(function ($row) use ($products) {
                $product = $products->get($row->recommended_product_id);
                if ($product) {
                    $product->setAttribute('recommendation_source_id', (int) $row->source_product_id);
                    $product->setAttribute('recommendation_source_name', $row->source_product_name);
                    $product->setAttribute('recommendation_confidence', (float) $row->confidence);
                }

                return $product;
            })
            ->filter()
            ->values()
            ->all());
    }

    public function forProducts(Business $business, array $productIds, int $limit = 3): Collection
    {
        $productIds = array_values(array_unique(array_map('intval', $productIds)));
        if ($productIds === []) {
            return new Collection;
        }

        $rows = DB::table('product_recommendations as recommendation')
            ->join('products as source', 'source.id', '=', 'recommendation.product_id')
            ->join('products as recommended', 'recommended.id', '=', 'recommendation.recommended_product_id')
            ->where('source.business_id', $business->id)
            ->where('recommended.business_id', $business->id)
            ->whereIn('source.id', $productIds)
            ->whereNotIn('recommended.id', $productIds)
            ->whereColumn('source.id', '<>', 'recommended.id')
            ->orderByDesc('recommendation.confidence')
            ->orderByDesc('recommendation.lift')
            ->get([
                'source.id as source_product_id',
                'source.name as source_product_name',
                'recommendation.recommended_product_id',
                'recommendation.confidence',
            ])
            ->unique('recommended_product_id')
            ->take(max(1, min($limit, 10)));

        $products = Product::query()
            ->with('business')
            ->where('business_id', $business->id)
            ->whereIn('id', $rows->pluck('recommended_product_id'))
            ->get()
            ->keyBy('id');

        return new Collection($rows
            ->map(function ($row) use ($products) {
                $product = $products->get($row->recommended_product_id);
                if ($product) {
                    $product->setAttribute('recommendation_source_id', (int) $row->source_product_id);
                    $product->setAttribute('recommendation_source_name', $row->source_product_name);
                    $product->setAttribute('recommendation_confidence', (float) $row->confidence);
                }

                return $product;
            })
            ->filter()
            ->values()
            ->all());
    }

    public function forProduct(Product $product, int $limit = 3): Collection
    {
        $rows = DB::table('product_recommendations')
            ->where('product_id', $product->id)
            ->orderByDesc('confidence')
            ->limit(max(1, min($limit, 10)))
            ->get(['recommended_product_id']);

        $products = Product::query()
            ->with('business')
            ->whereIn('id', $rows->pluck('recommended_product_id'))
            ->get()
            ->keyBy('id');

        return new Collection($rows
            ->map(fn ($row) => $products->get($row->recommended_product_id))
            ->filter()
            ->values()
            ->all());
    }
}
