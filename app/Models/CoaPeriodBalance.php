<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CoaPeriodBalance extends Model
{
    use HasFactory;

    protected $fillable = [
        'coa_id',
        'kode_akun',
        'period_id',
        'saldo_awal',
        'saldo_akhir',
        'is_posted',
    ];

    protected $casts = [
        'saldo_awal' => 'decimal:2',
        'saldo_akhir' => 'decimal:2',
        'is_posted' => 'boolean',
    ];

    /**
     * Relasi ke COA
     */
    public function coa()
    {
        return $this->belongsTo(Coa::class, 'coa_id', 'id');
    }

    /**
     * Relasi ke periode
     */
    public function period()
    {
        return $this->belongsTo(CoaPeriod::class, 'period_id');
    }
}
