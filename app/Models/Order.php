<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Order extends Model {
    protected $fillable = ['customer_name', 'phone', 'total', 'status', 'address', 'tracking_code'];

    protected static function booted() {
        static::creating(function ($order) {
            $order->tracking_code = 'DEL-' . strtoupper(Str::random(5));
        });
    }
}
