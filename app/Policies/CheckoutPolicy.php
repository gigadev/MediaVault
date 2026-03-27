<?php

namespace App\Policies;

use App\Models\Checkout;
use App\Models\User;

class CheckoutPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Checkout $checkout): bool
    {
        return $user->families()->where('families.id', $checkout->family_id)->exists();
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, Checkout $checkout): bool
    {
        return $user->families()->where('families.id', $checkout->family_id)->exists();
    }

    public function delete(User $user, Checkout $checkout): bool
    {
        return false; // Checkouts should not be deleted, only returned
    }
}
