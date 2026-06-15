<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BomJobBtklSelection extends Model
{
    use \App\Traits\HasUserScope;
    use HasFactory;

    protected $table = 'bom_job_btkl_selections';

    protected $fillable = [
        'user_id', 'bom_job_costing_id', 'proses_produksis_id',
        'tarif_per_jam', 'jumlah_pegawai', 'kapasitas_per_jam', 'biaya_per_produk',
        'keterangan'
    ];

    protected $casts = [
        'tarif_per_jam' => 'decimal:2',
        'jumlah_pegawai' => 'decimal:2',
        'kapasitas_per_jam' => 'decimal:2',
        'biaya_per_produk' => 'decimal:2',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function bomJobCosting()
    {
        return $this->belongsTo(BomJobCosting::class);
    }

    public function prosesProduksi()
    {
        return $this->belongsTo(ProsesProduksi::class, 'proses_produksis_id');
    }

    public function scopeByUser($query)
    {
        return $query->where('user_id', auth()->id());
    }
}
