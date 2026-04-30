<?php

namespace App\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class CompanyScope implements Scope
{
    /**
     * Apply the scope to a given Eloquent query builder.
     */
    public function apply(Builder $builder, Model $model): void
    {
        // Get the current authenticated user
        $user = auth()->user();
        
        if ($user) {
            // Apply company filtering based on user's company
            if ($user->company_id) {
                $builder->where($model->getTable() . '.company_id', $user->company_id);
            } elseif ($user->perusahaan_id) {
                $builder->where($model->getTable() . '.user_id', $user->perusahaan_id);
            }
        }
    }
}
