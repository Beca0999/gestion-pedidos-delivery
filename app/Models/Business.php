<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Business extends Model
{
    protected $fillable = ['name', 'image', 'user_id', 'delivery_price', 'phone', 'is_open', 'open_at', 'close_at'];

    // Esta función revisa la hora actual vs el horario del negocio
    public function getIsOpenAttribute()
    {
        $now = now()->format('H:i:s');
        if (!$this->attributes['is_open']) return false;
        return $now >= $this->open_at && $now <= $this->close_at;
    }

    public function owner(): BelongsTo { return $this->belongsTo(User::class, 'user_id'); }
    public function products(): HasMany { return $this->hasMany(Product::class); }
}
