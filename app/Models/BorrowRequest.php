<?php

namespace App\Models;

use App\Enums\BorrowRequestStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BorrowRequest extends Model
{
    use HasFactory, HasUlids;

    protected $fillable = [
        'media_item_id',
        'requesting_user_id',
        'requesting_family_id',
        'owning_family_id',
        'status',
        'requested_at',
        'responded_at',
        'responded_by_user_id',
        'checked_out_at',
        'due_at',
        'returned_at',
        'message',
        'response_message',
    ];

    protected function casts(): array
    {
        return [
            'status' => BorrowRequestStatus::class,
            'requested_at' => 'datetime',
            'responded_at' => 'datetime',
            'checked_out_at' => 'datetime',
            'due_at' => 'datetime',
            'returned_at' => 'datetime',
        ];
    }

    public function mediaItem(): BelongsTo
    {
        return $this->belongsTo(MediaItem::class);
    }

    public function requestingUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requesting_user_id');
    }

    public function requestingFamily(): BelongsTo
    {
        return $this->belongsTo(Family::class, 'requesting_family_id');
    }

    public function owningFamily(): BelongsTo
    {
        return $this->belongsTo(Family::class, 'owning_family_id');
    }

    public function respondedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'responded_by_user_id');
    }

    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', BorrowRequestStatus::Pending);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->whereIn('status', [
            BorrowRequestStatus::Approved,
            BorrowRequestStatus::CheckedOut,
        ]);
    }
}
