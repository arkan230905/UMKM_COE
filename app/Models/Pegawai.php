<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Pegawai extends Model
{
    use HasFactory;

    protected $table = 'pegawais';
    protected $primaryKey = 'nomor_induk_pegawai';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'nomor_induk_pegawai',
        'nama',
        'alamat',
        'no_telp',
        'jabatan',
        'email',
        'tanggal_masuk',
        'status_pegawai',
        'gaji_pokok',
        'tunjangan',
        'foto',
    ];

    protected $dates = [
        'tanggal_masuk',
        'created_at',
        'updated_at'
    ];

    public function presensis(): HasMany
    {
        return $this->hasMany(Presensi::class, 'pegawai_id', 'nomor_induk_pegawai');
    }

    // Scope untuk pencarian
    public function scopeSearch($query, $search)
    {
        return $query->where('nama', 'like', "%{$search}%")
                    ->orWhere('nomor_induk_pegawai', 'like', "%{$search}%");
    }
}