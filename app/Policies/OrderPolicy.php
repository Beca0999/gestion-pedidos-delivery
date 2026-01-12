<?php
namespace App\Policies;

use App\Models\User;
use App\Models\Order;
use App\Models\Rider;

class OrderPolicy {
    public function viewAny(User $user): bool { return true; }

    public function create(User $user): bool {
        // Un repartidor no puede crear órdenes manualmente
        return !Rider::where('user_id', $user->id)->exists();
    }

    public function update(User $user, Order $order): bool {
        return true; // Permitimos update para que cambie el estado a 'Entregado'
    }

    public function delete(User $user, Order $order): bool {
        return $user->id === 1; // Solo el admin borra registros
    }
}
