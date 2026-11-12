<?php
namespace App\Policies;

use App\Models\User;
use App\Models\Product;

class ProductPolicy {
    public function viewAny(User $user): bool {
        return $user->hasRole(['admin', 'owner']);
    }

    public function create(User $user): bool {
        return $user->hasRole(['admin', 'owner']);
    }

    public function update(User $user, Product $product): bool {
        return $user->hasRole('admin')
            || ($user->hasRole('owner') && $product->business?->user_id === $user->id);
    }

    public function delete(User $user, Product $product): bool {
        return $user->hasRole('admin')
            || ($user->hasRole('owner') && $product->business?->user_id === $user->id);
    }
}
