<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Builder;

trait HasUserScope
{
    protected static function bootHasUserScope()
    {
        static::addGlobalScope('user', function (Builder $builder) {
            if (auth()->check()) {
                // Allow both user-specific data AND global data (user_id = NULL)
                $builder->where(function($query) {
                    $query->where(app(static::class)->getTable() . '.user_id', auth()->id())
                          ->orWhereNull(app(static::class)->getTable() . '.user_id');
                });
            }
        });

        static::creating(function ($model) {
            if (empty($model->user_id) && auth()->check()) {
                $model->user_id = auth()->id();
            }
        });
    }
}
