<?php

namespace App\Models;

use App\Enums\MediaApiSource;
use App\Models\Concerns\BelongsToFamily;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MediaType extends Model
{
    use HasFactory, HasUlids, BelongsToFamily;

    protected $fillable = [
        'family_id',
        'name',
        'slug',
        'icon',
        'metadata_schema',
        'api_source',
    ];

    protected function casts(): array
    {
        return [
            'metadata_schema' => 'array',
            'api_source' => MediaApiSource::class,
        ];
    }

    public function family(): BelongsTo
    {
        return $this->belongsTo(Family::class);
    }

    public function mediaItems(): HasMany
    {
        return $this->hasMany(MediaItem::class);
    }

    /**
     * Convert the metadata_schema JSON into Filament form component arrays.
     */
    public function buildFormSchema(): array
    {
        $schema = $this->metadata_schema ?? [];
        $components = [];

        foreach ($schema as $field) {
            $components[] = $field;
        }

        return $components;
    }

    public function scopeSystem(Builder $query): Builder
    {
        return $query->whereNull('family_id');
    }

    public function scopeCustom(Builder $query): Builder
    {
        return $query->whereNotNull('family_id');
    }
}
