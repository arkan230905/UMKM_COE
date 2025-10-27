<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BahanBaku extends Model
{
    use HasFactory;

    protected $table = 'bahan_bakus'; // <--- PENTING: samakan dengan nama tabel di migration
    protected $fillable = [
        'nama_bahan',
        'satuan',
        'stok',
        'harga',
    ];
}
