<?php
namespace App\Policies;

use App\Models\Business;
use App\Models\User;

class BusinessPolicy
{
    // Solo el Super Admin (ID 1) puede hacer cualquier cosa con los Negocios
    public function viewAny(User $user) { return $user->id === 1; }
    public function create(User $user) { return $user->id === 1; }
    public function update(User $user, Business $business) { return $user->id === 1; }
    public function delete(User $user, Business $business) { return $user->id === 1; }
}
