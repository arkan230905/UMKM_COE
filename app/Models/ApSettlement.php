<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ApSettlement extends Model
{
    use HasFactory;

    protected $fillable = [
        'tanggal','vendor_id','pembelian_id','total_tagihan','diskon','denda_bunga','dibayar_bersih','metode_bayar','coa_kasbank','keterangan','status','user_id'
    ];

    public function pembelian()
    {
        return $this->belongsTo(Pembelian::class);
    }

    public function vendor()
    {
        return $this->belongsTo(Vendor::class);
    }
}
