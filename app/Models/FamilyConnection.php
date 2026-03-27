<?php

namespace App\Models;

use App\Enums\ConnectionStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FamilyConnection extends Model
{
    use HasFactory, HasUlids;

    protected $fillable = [
        'requester_family_id',
        'receiver_family_id',
        'status',
        'requested_at',
        'responded_at',
    ];

    protected function casts(): array
    {
        return [
            'status' => ConnectionStatus::class,
            'requested_at' => 'datetime',
            'responded_at' => 'datetime',
        ];
    }

    public function requesterFamily(): BelongsTo
    {
        return $this->belongsTo(Family::class, 'requester_family_id');
    }

    public function receiverFamily(): BelongsTo
    {
        return $this->belongsTo(Family::class, 'receiver_family_id');
    }

    public function scopeAccepted(Builder $query): Builder
    {
        return $query->where('status', ConnectionStatus::Accepted);
    }

    public function scopeBetween(Builder $query, string $familyIdA, string $familyIdB): Builder
    {
        return $query->where(function (Builder $q) use ($familyIdA, $familyIdB) {
            $q->where('requester_family_id', $familyIdA)
                ->where('receiver_family_id', $familyIdB);
        })->orWhere(function (Builder $q) use ($familyIdA, $familyIdB) {
            $q->where('requester_family_id', $familyIdB)
                ->where('receiver_family_id', $familyIdA);
        });
    }
}
