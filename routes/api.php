<?php
use App\Models\Order;
use Illuminate\Support\Facades\Route;
Route::get('/api/orders/count', fn() => response()->json(['count' => Order::count()]));

