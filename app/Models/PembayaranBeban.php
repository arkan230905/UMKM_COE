<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PembayaranBeban extends Model
{
    protected $table = 'pembayaran_beban';
    protected $fillable = [
        'tanggal',
        'keterangan',
        'akun_beban_id',
        'akun_kas_id',
        'jumlah',
        'user_id',
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
}
