<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Builder;

trait HasUserScope
{
    protected static function bootHasUserScope()
    {
        static::addGlobalScope('user', function (Builder $builder) {
            if (auth()->check()) {
                $builder->where(app(static::class)->getTable() . '.user_id', auth()->id());
            }
        });

        static::creating(function ($model) {
            if (empty($model->user_id) && auth()->check()) {
                $model->user_id = auth()->id();
            }
        });
    }
}
