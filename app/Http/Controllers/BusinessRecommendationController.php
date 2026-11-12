<?php

namespace App\Http\Controllers;

use App\Models\Business;
use App\Services\BusinessRecommendationService;
use Illuminate\Http\JsonResponse;

class BusinessRecommendationController extends Controller
{
    public function __invoke(Business $business, BusinessRecommendationService $recommendations): JsonResponse
    {
        return response()->json([
            'business_id' => $business->id,
            'recommendations' => $recommendations->forBusiness($business)->map(fn (Business $recommended) => [
                'id' => $recommended->id,
                'name' => $recommended->name,
                'delivery_price' => $recommended->delivery_price,
            ])->values(),
        ]);
    }
}
