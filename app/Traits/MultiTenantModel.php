<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Builder;

/**
 * Trait MultiTenantModel
 * 
 * Otomatis filter query berdasarkan user_id untuk isolasi multi-tenant
 * Tambahkan ke model: use MultiTenantModel;
 * 
 * Model harus punya column 'user_id' di database
 */
trait MultiTenantModel
{
    /**
     * Boot the trait
     */
    protected static function bootMultiTenantModel()
    {
        /**
         * Otomatis tambah user_id saat creating
         */
        static::creating(function ($model) {
            if (empty($model->user_id) && auth()->check()) {
                $model->user_id = auth()->id();
            }
        });

        /**
         * Otomatis filter by user_id untuk queries
         * Semua query akan otomatis di-filter
         */
        static::addGlobalScope('user_id', function (Builder $builder) {
            if (auth()->check()) {
                $builder->where('user_id', auth()->id());
            }
        });
    }

    /**
     * Scope untuk bypass global scope jika perlu (admin only)
     * Usage: Model::withoutGlobalScopes()->get()
     */
}
