<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PembayaranBeban extends Model
{
    use SoftDeletes;
    
    protected $table = 'pembayaran_beban';
    protected $fillable = [
        'tanggal',
        'keterangan',
        'akun_beban_id',
        'akun_kas_id',
        'jumlah',
        'catatan',
        'user_id',
        'beban_operasional_id',
    ];

    protected $dates = ['tanggal'];

    public function coaBeban(): BelongsTo
    {
        return $this->belongsTo(Coa::class, 'akun_beban_id');
    }

    public function coaKas(): BelongsTo
    {
        return $this->belongsTo(Coa::class, 'akun_kas_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function bebanOperasional(): BelongsTo
    {
        return $this->belongsTo(\App\Models\BebanOperasional::class, 'beban_operasional_id');
    }

    /**
     * Get formatted nominal pembayaran
     */
    public function getNominalPembayaranFormattedAttribute()
    {
        return 'Rp ' . number_format($this->jumlah, 0, ',', '.');
    }

    /**
     * Get coaKasBank relationship (alias for coaKas)
     */
    public function coaKasBank(): BelongsTo
    {
        return $this->coaKas();
    }
}
