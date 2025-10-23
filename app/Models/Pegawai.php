<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pegawai extends Model
{
    use HasFactory;

    protected $fillable = [
        'nama',
        'email',
        'no_telp',
        'alamat',
        'jenis_kelamin',
        'jabatan',
        'gaji',
    ];
}
