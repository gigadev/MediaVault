<?php

namespace App\Policies;

use App\Models\MediaItem;
use App\Models\User;

class MediaItemPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, MediaItem $mediaItem): bool
    {
        return $user->families()->where('families.id', $mediaItem->family_id)->exists();
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, MediaItem $mediaItem): bool
    {
        return $user->families()->where('families.id', $mediaItem->family_id)->exists();
    }

    public function delete(User $user, MediaItem $mediaItem): bool
    {
        $membership = $user->families()
            ->where('families.id', $mediaItem->family_id)
            ->first();

        return $membership && in_array($membership->pivot->role, ['owner', 'admin']);
    }
}
