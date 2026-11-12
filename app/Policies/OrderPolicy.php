<?php
namespace App\Policies;

use App\Models\User;
use App\Models\Order;

class OrderPolicy {
    public function viewAny(User $user): bool { return $user->hasRole(['admin', 'owner']); }

    public function create(User $user): bool {
        return $user->hasRole(['admin', 'owner']);
    }

    public function update(User $user, Order $order): bool {
        return $user->hasRole('admin')
            || ($user->hasRole('owner') && $order->business?->user_id === $user->id)
            || ($user->hasRole('rider') && $order->rider?->user_id === $user->id);
    }

    public function delete(User $user, Order $order): bool {
        return $user->hasRole('admin');
    }
}
