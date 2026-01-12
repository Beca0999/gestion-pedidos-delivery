<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class Order extends Model {
    protected $fillable = [
        'customer_name', 
        'phone', 
        'total', 
        'status', 
        'address', 
        'email', 
        'tracking_code',
        'business_id',
        'rider_id'
    ];

    protected static function booted() {
        static::creating(function ($order) {
            $order->tracking_code = 'DEL-' . strtoupper(Str::random(5));
        });
    }

    public function business(): BelongsTo {
        return $this->belongsTo(Business::class);
    }

    public function rider(): BelongsTo {
        return $this->belongsTo(Rider::class);
    }
}
