<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Coa extends Model
{
    use HasFactory;

    // Pastikan tabel yang digunakan benar
    protected $table = 'coas';

    protected $fillable = [
        'kode_akun',
        'nama_akun',
        'tipe_akun',
    ];
}
