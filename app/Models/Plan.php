<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Plan extends Model
{
    use HasFactory, HasUlids;

    protected $fillable = [
        'name',
        'slug',
        'stripe_price_id',
        'max_media_items',
        'max_family_members',
        'can_cross_family_share',
        'can_use_api_lookup',
        'can_create_custom_media_types',
        'price_monthly',
        'price_yearly',
        'features',
        'sort_order',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'features' => 'array',
            'max_media_items' => 'integer',
            'max_family_members' => 'integer',
            'sort_order' => 'integer',
            'can_cross_family_share' => 'boolean',
            'can_use_api_lookup' => 'boolean',
            'can_create_custom_media_types' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }
}
