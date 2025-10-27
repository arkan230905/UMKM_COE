<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pegawai extends Model
{
    use HasFactory;

    protected $table = 'pegawais';

    protected $fillable = [
        'nama',
        'email',
        'no_telp',
        'alamat',
        'jenis_kelamin',
        'jabatan',
        'kategori_tenaga_kerja',
        'gaji',
    ];
    
    public function penggajian()
    {
        return $this->hasMany(Penggajian::class);
    }

    public function presensi()
    {
        return $this->hasMany(Presensi::class);
    }

}
