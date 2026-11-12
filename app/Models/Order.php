<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Str;

class Order extends Model {
    use HasFactory;

    protected $fillable = [
        'customer_name', 
        'customer_id',
        'phone', 
        'total', 
        'status', 
        'address', 
        'email', 
        'tracking_code',
        'business_id',
        'rider_id',
        'payment_method',
        'payment_status',
        'payment_id',
        'delivery_latitude',
        'delivery_longitude',
    ];

    protected static function booted() {
        static::creating(function ($order) {
            $order->tracking_code = 'DEL-' . strtoupper(Str::random(5));
        });
    }

    public function business(): BelongsTo {
        return $this->belongsTo(Business::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    public function rider(): BelongsTo {
        return $this->belongsTo(Rider::class);
    }

    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class)
            ->withPivot(['quantity', 'unit_price'])
            ->withTimestamps();
    }
}
