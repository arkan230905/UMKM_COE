<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BahanBaku extends Model
{
    use HasFactory;

    protected $fillable = [
        'nama_bahan',
        'stok',
        'satuan',
        'harga_satuan'
    ];

    // Accessor untuk detail harga
    public function getDetailHargaAttribute()
    {
        $detail = [];
        switch($this->satuan){
            case 'Kg':
                $detail['g'] = $this->harga_satuan / 1000;
                $detail['mg'] = $this->harga_satuan / 1000000;
                break;
            case 'Liter':
                $detail['ml'] = $this->harga_satuan / 1000;
                $detail['cl'] = $this->harga_satuan / 100;
                break;
        }
        return $detail;
    }
}
