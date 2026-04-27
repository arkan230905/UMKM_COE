<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OngkirSetting extends Model
{
    protected $fillable = [
        'jarak_min', 'jarak_max', 'harga_ongkir', 'status',
    ];

    protected $casts = [
        'status' => 'boolean',
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
