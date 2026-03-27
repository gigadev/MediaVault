<?php

namespace App\Models;

use App\Enums\MediaCondition;
use App\Models\Concerns\BelongsToFamily;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Checkout extends Model
{
    use HasFactory, HasUlids, BelongsToFamily;

    protected $fillable = [
        'family_id',
        'media_item_id',
        'checked_out_to_user_id',
        'checked_out_by_user_id',
        'checked_out_at',
        'due_at',
        'returned_at',
        'condition_on_checkout',
        'condition_on_return',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'checked_out_at' => 'datetime',
            'due_at' => 'datetime',
            'returned_at' => 'datetime',
            'condition_on_checkout' => MediaCondition::class,
            'condition_on_return' => MediaCondition::class,
        ];
    }

    public function family(): BelongsTo
    {
        return $this->belongsTo(Family::class);
    }

    public function mediaItem(): BelongsTo
    {
        return $this->belongsTo(MediaItem::class);
    }

    public function checkedOutTo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'checked_out_to_user_id');
    }

    public function checkedOutBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'checked_out_by_user_id');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->whereNull('returned_at');
    }

    public function scopeOverdue(Builder $query): Builder
    {
        return $query->whereNull('returned_at')
            ->where('due_at', '<', now());
    }

    public function getIsOverdueAttribute(): bool
    {
        return $this->due_at !== null
            && $this->returned_at === null
            && $this->due_at->isPast();
    }
}
