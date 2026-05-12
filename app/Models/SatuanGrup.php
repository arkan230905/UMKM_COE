<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SatuanGrup extends Model
{
    use HasFactory;

    protected $fillable = [
        'nama',
        'kode',
        'keterangan',
    ];

    // Relasi ke satuan-satuan dalam grup ini
    public function satuans()
    {
        return $this->hasMany(Satuan::class);
    }

    // Ambil satuan dasar dari grup ini
    public function satuanDasar()
    {
        return $this->hasOne(Satuan::class)->where('is_dasar', true);
    }
}
