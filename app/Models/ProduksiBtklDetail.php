<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProduksiBtklDetail extends Model
{
    protected $table = 'produksi_btkl_details';
    protected $fillable = [
        'produksi_id', 'nama_proses', 'harga_per_unit', 'total',
        'coa_debit_kode', 'coa_debit_nama', 'coa_kredit_kode', 'coa_kredit_nama',
    ];
    public function produksi() { return $this->belongsTo(Produksi::class); }
}
