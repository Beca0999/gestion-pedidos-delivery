<?php

use App\Http\Controllers\Api\PizarronActivityController;
use App\Http\Controllers\BusinessRecommendationController;
use App\Http\Controllers\ProductRecommendationController;
use App\Models\Order;
use Illuminate\Support\Facades\Route;

Route::get('/orders/count', fn () => response()->json(['count' => Order::count()]));
Route::get('/pizarron/activity', [PizarronActivityController::class, 'index']);
Route::get('/products/{product}/recommendations', ProductRecommendationController::class)
    ->name('api.products.recommendations');
Route::get('/businesses/{business}/recommendations', BusinessRecommendationController::class)
    ->name('api.businesses.recommendations');
