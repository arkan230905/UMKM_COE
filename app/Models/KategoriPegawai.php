<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class KategoriPegawai extends Model
{
    use HasFactory;

    protected $table = 'kategori_pegawai';
    protected $fillable = ['nama', 'deskripsi'];

    protected $casts = [
        'nama' => 'string',
        'deskripsi' => 'string',
    ];

    /**
     * Relasi ke jabatan
     */
    public function jabatans(): HasMany
    {
        return $this->hasMany(Jabatan::class, 'kategori_id');
    }

    /**
     * Relasi ke pegawai
     */
    public function pegawais(): HasMany
    {
        return $this->hasMany(Pegawai::class, 'kategori_id');
    }

    /**
     * Scope untuk mencari kategori
     */
    public function scopeSearch($query, $search)
    {
        return $query->where('nama', 'like', "%{$search}%")
                    ->orWhere('deskripsi', 'like', "%{$search}%");
    }
}
