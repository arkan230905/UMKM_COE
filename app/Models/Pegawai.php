<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Pegawai extends Model
{
    use HasFactory;

    protected $table = 'pegawais';

    protected $fillable = [
        'nomor_induk_pegawai',
        'nama',
        'email',
        'no_telp',
        'alamat',
        'jenis_kelamin',
        'jabatan',
        'kategori_tenaga_kerja',
        'tanggal_masuk',
        'status_aktif',
        'gaji_pokok',
        'tarif_per_jam',
        'gaji',
        'tunjangan',
    ];

    protected $dates = [
        'tanggal_masuk',
        'created_at',
        'updated_at'
    ];

    public function presensis(): HasMany
    {
        return $this->hasMany(Presensi::class, 'pegawai_id');
    }

    // Scope untuk pencarian
    public function scopeSearch($query, $search)
    {
        return $query->where('nama', 'like', "%{$search}%")
                    ->orWhere('nomor_induk_pegawai', 'like', "%{$search}%");
    }

    /**
     * Use nomor_induk_pegawai as the route key for implicit model binding.
     */
    public function getRouteKeyName(): string
    {
        return 'nomor_induk_pegawai';
    }
}