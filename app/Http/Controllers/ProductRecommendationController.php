<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Services\ProductRecommendationService;
use Illuminate\Http\JsonResponse;

class ProductRecommendationController extends Controller
{
    public function __invoke(Product $product, ProductRecommendationService $recommendations): JsonResponse
    {
        return response()->json([
            'product_id' => $product->id,
            'recommendations' => $recommendations->forProduct($product)->map(fn (Product $recommended) => [
                'id' => $recommended->id,
                'name' => $recommended->name,
                'price' => $recommended->price,
                'business_id' => $recommended->business_id,
                'business_name' => $recommended->business?->name,
            ])->values(),
        ]);
    }
}
