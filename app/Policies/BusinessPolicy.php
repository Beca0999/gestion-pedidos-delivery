<?php
namespace App\Policies;

use App\Models\Business;
use App\Models\User;

class BusinessPolicy
{
    public function viewAny(User $user): bool { return $user->hasRole(['admin', 'owner']); }
    public function create(User $user): bool { return $user->hasRole(['admin', 'owner']); }
    public function update(User $user, Business $business): bool
    {
        return $user->hasRole('admin') || ($user->hasRole('owner') && $business->user_id === $user->id);
    }
    public function delete(User $user, Business $business): bool
    {
        return $user->hasRole('admin') || ($user->hasRole('owner') && $business->user_id === $user->id);
    }
}
