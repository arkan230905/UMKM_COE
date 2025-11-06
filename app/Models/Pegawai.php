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
        'kode_pegawai',
        'nama',
        'no_telepon',
        'email',
        'alamat',
        'nama_bank',
        'no_rekening',
        'jabatan',
        'kategori',
        'asuransi',
        'tarif',
        'tunjangan',
        'gaji',
    ];

    protected $dates = [
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
                    ->orWhere('no_telepon', 'like', "%{$search}%");
    }

    // Use default 'id' for route model binding so views can pass numeric IDs
}