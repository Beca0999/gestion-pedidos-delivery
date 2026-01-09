<?php
use App\Livewire\QuickOrder;
use Illuminate\Support\Facades\Route;
Route::get('/', QuickOrder::class);

use App\Models\Order;

Route::get('/order/{order}/print', function (Order $order) {
    return view('print-order', compact('order'));
})->name('order.print');

Route::get('/track/{code}', function ($code) {
    $order = Order::where('tracking_code', $code)->firstOrFail();
    return view('track-order', compact('order'));
})->name('order.track');
