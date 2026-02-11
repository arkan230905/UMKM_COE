<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SatuanConversion extends Model
{
    use HasFactory;

    protected $fillable = [
        'source_satuan_id',
        'target_satuan_id',
        'amount_source',
        'amount_target',
        'is_inverse',
    ];

    protected $casts = [
        'amount_source' => 'float',
        'amount_target' => 'float',
        'is_inverse' => 'boolean',
    ];

    public function source()
    {
        return $this->belongsTo(Satuan::class, 'source_satuan_id');
    }

    public function target()
    {
        return $this->belongsTo(Satuan::class, 'target_satuan_id');
    }
}
