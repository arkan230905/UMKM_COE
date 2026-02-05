<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Satuan extends Model
{
    use HasFactory;

    // Pastikan sesuai nama tabel di DB
    protected $table = 'satuans';

    // Kolom yang boleh di-mass assign
    protected $fillable = [
        'kode',
        'nama',
        'faktor', // Add faktor field if it exists
    ];

    /**
     * Get the bahan bakus for the satuan.
     */
    public function bahanBakus()
    {
        return $this->hasMany(BahanBaku::class, 'satuan_id');
    }

    /**
     * Get the bahan pendukungs for the satuan.
     */
    public function bahanPendukungs()
    {
        return $this->hasMany(BahanPendukung::class, 'satuan_id');
    }
}
