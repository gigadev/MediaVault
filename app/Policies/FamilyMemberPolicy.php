<?php

namespace App\Policies;

use App\Models\FamilyMember;
use App\Models\User;

class FamilyMemberPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, FamilyMember $member): bool
    {
        return $user->families()->where('families.id', $member->family_id)->exists();
    }

    public function create(User $user): bool
    {
        // Only owner/admin can invite members
        $tenant = filament()->getTenant();

        if (! $tenant) {
            return false;
        }

        $membership = $user->families()
            ->where('families.id', $tenant->id)
            ->first();

        return $membership && in_array($membership->pivot->role, ['owner', 'admin']);
    }

    public function update(User $user, FamilyMember $member): bool
    {
        // Cannot edit the owner
        if ($member->role->value === 'owner') {
            return false;
        }

        $membership = $user->families()
            ->where('families.id', $member->family_id)
            ->first();

        return $membership && in_array($membership->pivot->role, ['owner', 'admin']);
    }

    public function delete(User $user, FamilyMember $member): bool
    {
        // Cannot remove the owner
        if ($member->role->value === 'owner') {
            return false;
        }

        $membership = $user->families()
            ->where('families.id', $member->family_id)
            ->first();

        return $membership && $membership->pivot->role === 'owner';
    }
}
