<?php

namespace App\Models\Concerns;

trait BelongsToFamily
{
    public static function bootBelongsToFamily(): void
    {
        static::creating(function ($model) {
            if (!$model->family_id && filament()->getTenant()) {
                $model->family_id = filament()->getTenant()->id;
            }
        });
    }

    public function family(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\App\Models\Family::class);
    }
}
