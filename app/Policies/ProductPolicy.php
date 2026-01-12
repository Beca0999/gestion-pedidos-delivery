<?php
namespace App\Policies;

use App\Models\User;
use App\Models\Product;
use App\Models\Rider;

class ProductPolicy {
    public function viewAny(User $user): bool {
        // Si es repartidor, no ve nada de productos
        return !Rider::where('user_id', $user->id)->exists();
    }

    public function create(User $user): bool {
        return !Rider::where('user_id', $user->id)->exists();
    }

    public function update(User $user, Product $product): bool {
        return $user->id === 1 || $product->business->user_id === $user->id;
    }

    public function delete(User $user, Product $product): bool {
        return $user->id === 1 || $product->business->user_id === $user->id;
    }
}
