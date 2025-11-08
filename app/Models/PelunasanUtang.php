<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\Pembelian;
use App\Models\Coa;
use App\Models\User;
use App\Models\Jurnal;

class PelunasanUtang extends Model
{
    use SoftDeletes;

    protected $table = 'pelunasan_utang';
    
    protected $fillable = [
        'pembelian_id',
        'tanggal',
        'jumlah',
        'keterangan'
    ];

    protected $dates = ['tanggal'];

    public function pembelian()
    {
        return $this->belongsTo(Pembelian::class);
    }
}
