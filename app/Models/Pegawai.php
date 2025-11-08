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
        'email',
        'no_telp',
        'alamat',
        'jenis_kelamin',
        'jabatan',
        'gaji',
        'gaji_pokok',
        'tunjangan',
        'jenis_pegawai',
    ];
    
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->kode_pegawai)) {
                $lastId = static::max('id') ?? 0;
                $model->kode_pegawai = 'PGW' . str_pad($lastId + 1, 4, '0', STR_PAD_LEFT);
            }
        });
    }
    
    protected $casts = [
        'gaji' => 'decimal:2',
        'gaji_pokok' => 'decimal:2',
        'tunjangan' => 'decimal:2',
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