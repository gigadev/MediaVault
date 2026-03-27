<?php

namespace App\Models;

use App\Enums\MediaCondition;
use App\Models\Concerns\BelongsToFamily;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class MediaItem extends Model
{
    use HasFactory, HasUlids, SoftDeletes, BelongsToFamily, LogsActivity;

    protected $fillable = [
        'family_id',
        'media_type_id',
        'added_by_user_id',
        'title',
        'barcode',
        'year',
        'description',
        'cover_image_path',
        'metadata',
        'condition',
        'location',
        'is_available',
        'is_shareable',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'metadata' => 'array',
            'condition' => MediaCondition::class,
            'is_available' => 'boolean',
            'is_shareable' => 'boolean',
            'year' => 'integer',
        ];
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty();
    }

    public function family(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Family::class);
    }

    public function mediaType(): BelongsTo
    {
        return $this->belongsTo(MediaType::class);
    }

    public function addedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'added_by_user_id');
    }

    public function checkouts(): HasMany
    {
        return $this->hasMany(Checkout::class);
    }

    public function collections(): BelongsToMany
    {
        return $this->belongsToMany(Collection::class, 'collection_media_item')
            ->withTimestamps();
    }

    public function borrowRequests(): HasMany
    {
        return $this->hasMany(BorrowRequest::class);
    }

    public function scopeAvailable(Builder $query): Builder
    {
        return $query->where('is_available', true);
    }

    public function scopeShareable(Builder $query): Builder
    {
        return $query->where('is_shareable', true);
    }

    public static function getGlobalSearchResultTitle(Model $record): string
    {
        return $record->title;
    }

    public static function getGlobalSearchResultDetails(Model $record): array
    {
        return [
            'Type' => $record->mediaType?->name,
            'Year' => $record->year,
        ];
    }

    public static function getGloballySearchableAttributes(): array
    {
        return ['title', 'barcode', 'description'];
    }
}
