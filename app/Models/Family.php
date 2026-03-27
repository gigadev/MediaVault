<?php

namespace App\Models;

use App\Enums\FamilyVisibility;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Laravel\Cashier\Billable;

class Family extends \Illuminate\Database\Eloquent\Model
{
    use HasFactory, SoftDeletes, Billable, HasUlids;

    protected $fillable = [
        'name',
        'slug',
        'visibility',
        'allow_open_borrow_requests',
        'settings',
    ];

    protected function casts(): array
    {
        return [
            'visibility' => FamilyVisibility::class,
            'allow_open_borrow_requests' => 'boolean',
            'settings' => 'array',
        ];
    }

    public function members(): HasMany
    {
        return $this->hasMany(FamilyMember::class);
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'family_members')
            ->withPivot('role', 'joined_at')
            ->withTimestamps();
    }

    public function mediaItems(): HasMany
    {
        return $this->hasMany(MediaItem::class);
    }

    public function mediaTypes(): HasMany
    {
        return $this->hasMany(MediaType::class);
    }

    public function collections(): HasMany
    {
        return $this->hasMany(Collection::class);
    }

    public function checkouts(): HasMany
    {
        return $this->hasMany(Checkout::class);
    }

    public function sentConnections(): HasMany
    {
        return $this->hasMany(FamilyConnection::class, 'requester_family_id');
    }

    public function receivedConnections(): HasMany
    {
        return $this->hasMany(FamilyConnection::class, 'receiver_family_id');
    }

    public function outgoingBorrowRequests(): HasMany
    {
        return $this->hasMany(BorrowRequest::class, 'requesting_family_id');
    }

    public function incomingBorrowRequests(): HasMany
    {
        return $this->hasMany(BorrowRequest::class, 'owning_family_id');
    }
}
