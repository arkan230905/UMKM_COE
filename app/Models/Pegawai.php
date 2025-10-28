<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pegawai extends Model
{
    use HasFactory;

    protected $table = 'pegawais'; // pastikan nama tabel sama dengan database
    protected $fillable = [
        'nama',
        'email',
        'no_telp',
        'alamat',
        'jenis_kelamin',
        'jabatan',
        'kategori_tenaga_kerja',
        'gaji',
        'gaji_pokok',
        'tunjangan',
        'jenis_pegawai',
    ];
}
