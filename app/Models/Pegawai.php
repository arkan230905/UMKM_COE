<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Pegawai extends Model
{
    use HasFactory;

    protected $table = 'pegawais';
    protected $primaryKey = 'id';

    protected $fillable = [
        'kode_pegawai',
        'nama',
        'email',
        'no_telepon',
        'alamat',
        'jenis_kelamin',
        'jabatan_id',
        'kategori_id',
        'gaji',
        'gaji_pokok',
        'tarif_per_jam',
        'tunjangan',
        'asuransi',
        'jenis_pegawai',
        'bank',
        'nomor_rekening',
        'nama_rekening',
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
        'tarif_per_jam' => 'decimal:2',
        'tunjangan' => 'decimal:2',
        'asuransi' => 'decimal:2',
    ];

    protected $dates = [
        'created_at',
        'updated_at'
    ];

    /**
     * Relasi ke jabatan
     */
    public function jabatan(): BelongsTo
    {
        return $this->belongsTo(Jabatan::class, 'jabatan_id');
    }

    /**
     * Relasi ke kategori pegawai
     */
    public function kategori(): BelongsTo
    {
        return $this->belongsTo(KategoriPegawai::class, 'kategori_id');
    }

    public function getNoTeleponAttribute()
    {
        return $this->attributes['no_telepon'] ?? $this->attributes['no_telp'] ?? null;
    }

    public function presensis(): HasMany
    {
        return $this->hasMany(Presensi::class, 'pegawai_id');
    }

    // Scope untuk pencarian
    public function scopeSearch($query, $search)
    {
        return $query->where('nama', 'like', "%{$search}%")
                    ->orWhere('no_telepon', 'like', "%{$search}%")
                    ->orWhereHas('jabatan', function($q) use ($search) {
                        $q->where('nama', 'like', "%{$search}%");
                    });
    }

    /**
     * Accessor untuk backward compatibility
     */
    public function getJabatanNamaAttribute()
    {
        return $this->jabatan ? $this->jabatan->nama : $this->attributes['jabatan'] ?? null;
    }

    /**
     * Accessor untuk backward compatibility
     */
    public function getKategoriNamaAttribute()
    {
        return $this->kategori ? $this->kategori->nama : $this->attributes['jenis_pegawai'] ?? null;
    }

    // Use default 'id' for route model binding so views can pass numeric IDs
}