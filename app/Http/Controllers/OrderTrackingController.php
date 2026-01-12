<?php
namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;

class OrderTrackingController extends Controller {
    public function show($code) {
        $order = Order::where('tracking_code', $code)->firstOrFail();
        return view('orders.tracking', compact('order'));
    }
}
