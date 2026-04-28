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
        'jabatan',
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
        'user_id',
    ];
    
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->kode_pegawai)) {
                $userId = auth()->id();
                $lastId = static::where('user_id', $userId)->max('id') ?? 0;
                $model->kode_pegawai = 'PGW' . str_pad($lastId + 1, 4, '0', STR_PAD_LEFT);
            }
        });
        
        // Global scope for multi-tenant isolation
        static::addGlobalScope('user_id', function ($builder) {
            if (auth()->check()) {
                $builder->where('user_id', auth()->id());
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
    public function jabatanRelasi(): BelongsTo
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
                    ->orWhereHas('jabatanRelasi', function($q) use ($search) {
                        $q->where('nama', 'like', "%{$search}%");
                    });
    }

    /**
     * Accessor untuk backward compatibility
     */
    public function getJabatanNamaAttribute()
    {
        if (is_object($this->jabatanRelasi)) {
            return $this->jabatanRelasi->nama;
        }
        return $this->attributes['jabatan'] ?? null;
    }

    /**
     * Accessor untuk backward compatibility
     */
    public function getKategoriNamaAttribute()
    {
        if (is_object($this->kategori)) {
            return $this->kategori->nama;
        }
        return $this->attributes['jenis_pegawai'] ?? null;
    }

    /**
     * Get tunjangan jabatan from related jabatan (kualifikasi)
     */
    public function getTunjanganJabatanAttribute()
    {
        if (is_object($this->jabatanRelasi)) {
            return $this->jabatanRelasi->tunjangan ?? 0;
        }
        return $this->attributes['tunjangan'] ?? 0;
    }

    /**
     * Get tunjangan transport from related jabatan (kualifikasi)
     */
    public function getTunjanganTransportAttribute()
    {
        if (is_object($this->jabatanRelasi)) {
            return $this->jabatanRelasi->tunjangan_transport ?? 0;
        }
        return 0;
    }

    /**
     * Get tunjangan konsumsi from related jabatan (kualifikasi)
     */
    public function getTunjanganKonsumsiAttribute()
    {
        if (is_object($this->jabatanRelasi)) {
            return $this->jabatanRelasi->tunjangan_konsumsi ?? 0;
        }
        return 0;
    }

    /**
     * Get total tunjangan (sum of all tunjangan types)
     */
    public function getTotalTunjanganAttribute()
    {
        return $this->tunjangan_jabatan + $this->tunjangan_transport + $this->tunjangan_konsumsi;
    }

    /**
     * Get gaji pokok from related jabatan (kualifikasi)
     */
    public function getGajiPokokFromJabatanAttribute()
    {
        if (is_object($this->jabatanRelasi)) {
            return $this->jabatanRelasi->gaji_pokok ?? 0;
        }
        return $this->attributes['gaji_pokok'] ?? 0;
    }

    /**
     * Get tarif per jam from related jabatan (kualifikasi)
     */
    public function getTarifPerJamFromJabatanAttribute()
    {
        if (is_object($this->jabatanRelasi)) {
            return $this->jabatanRelasi->tarif_per_jam ?? 0;
        }
        return $this->attributes['tarif_per_jam'] ?? 0;
    }

    /**
     * Get asuransi from related jabatan (kualifikasi)
     */
    public function getAsuransiFromJabatanAttribute()
    {
        if (is_object($this->jabatanRelasi)) {
            return $this->jabatanRelasi->asuransi ?? 0;
        }
        return $this->attributes['asuransi'] ?? 0;
    }

    /**
     * Get all komponen gaji from jabatan as array
     */
    public function getKomponenGajiAttribute()
    {
        if (!is_object($this->jabatanRelasi)) {
            return [
                'gaji_pokok' => $this->gaji_pokok ?? 0,
                'tarif_per_jam' => $this->tarif_per_jam ?? 0,
                'tunjangan_jabatan' => $this->tunjangan ?? 0,
                'tunjangan_transport' => 0,
                'tunjangan_konsumsi' => 0,
                'total_tunjangan' => $this->tunjangan ?? 0,
                'asuransi' => $this->asuransi ?? 0,
            ];
        }

        return [
            'gaji_pokok' => $this->jabatanRelasi->gaji_pokok ?? 0,
            'tarif_per_jam' => $this->jabatanRelasi->tarif_per_jam ?? 0,
            'tunjangan_jabatan' => $this->jabatanRelasi->tunjangan ?? 0,
            'tunjangan_transport' => $this->jabatanRelasi->tunjangan_transport ?? 0,
            'tunjangan_konsumsi' => $this->jabatanRelasi->tunjangan_konsumsi ?? 0,
            'total_tunjangan' => ($this->jabatanRelasi->tunjangan ?? 0) + ($this->jabatanRelasi->tunjangan_transport ?? 0) + ($this->jabatanRelasi->tunjangan_konsumsi ?? 0),
            'asuransi' => $this->jabatanRelasi->asuransi ?? 0,
        ];
    }

    // Use default 'id' for route model binding so views can pass numeric IDs
}