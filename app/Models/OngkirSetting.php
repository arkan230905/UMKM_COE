<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\MultiTenantModel;

class OngkirSetting extends Model
{
    use MultiTenantModel;

    protected $fillable = [
        'user_id',  // 🔒 SECURITY: Add user_id for multi-tenant isolation
        'jarak_min', 'jarak_max', 'harga_ongkir', 'status',
    ];

    protected $casts = [
        'status' => 'boolean',
        'jarak_min' => 'float',
        'jarak_max' => 'float',
        'harga_ongkir' => 'float',
    ];

    /**
     * Boot the model and add global scope for multi-tenant isolation
     */
    protected static function booted()
    {
        // 🔒 SECURITY: Apply global scope untuk multi-tenant isolation
        static::addGlobalScope(new \App\Scopes\UserScope);

        static::creating(function ($ongkir) {
            // 🔒 SECURITY: Auto-fill user_id for multi-tenant isolation
            if (empty($ongkir->user_id) && auth()->check()) {
                $ongkir->user_id = auth()->id();
            }
        });
    }

    public function getJarakLabel(): string
    {
        $min = $this->jarak_min;
        $max = $this->jarak_max;
        if (is_null($max)) {
            return "> {$min} km";
        }
        if ($min == 0) {
            return "0 - {$max} km";
        }
        return "{$min} - {$max} km";
    }
}
