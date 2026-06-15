<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\MultiTenantModel;

class OngkirSetting extends Model
{
    use MultiTenantModel;

    protected $fillable = [
        'user_id', 'jarak_min', 'jarak_max', 'harga_ongkir', 'status',
    ];

    protected $casts = [
        'status' => 'boolean',
        'jarak_min' => 'float',
        'jarak_max' => 'float',
        'harga_ongkir' => 'float',
    ];

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
