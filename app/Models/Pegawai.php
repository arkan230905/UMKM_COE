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
        'user_id',         // 🔒 Multi-tenant isolation
        'kode_pegawai',
        'nama',
        'email',
        'no_telepon',
        'alamat',
        'jenis_kelamin',
        'jabatan',
        'jabatan_id',
        'kategori_id',
        'kategori',
        'gaji_pokok',
        'tarif_per_jam',
        'tarif_per_produk',
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
            // 🔒 SECURITY: Auto-fill user_id untuk Multi-tenancy
            if (empty($model->user_id) && auth()->check()) {
                $model->user_id = auth()->id();
            }
            
            // Auto-generate kode_pegawai jika kosong
            if (empty($model->kode_pegawai)) {
                $lastId = static::max('id') ?? 0;
                $model->kode_pegawai = 'PGW' . str_pad($lastId + 1, 4, '0', STR_PAD_LEFT);
            }
        });

        // 🔒 SECURITY: Global Scope untuk Multi-tenancy
        // Memastikan Owner hanya melihat data pegawainya sendiri
        if (auth()->check()) {
            static::addGlobalScope('user_id', function ($builder) {
                $builder->where('user_id', auth()->id());
            });
        }
    }
    
    protected $casts = [
        'gaji_pokok' => 'decimal:2',
        'tarif_per_produk' => 'decimal:2', // PERBAIKAN: Cast tarif_per_produk
        'tunjangan' => 'decimal:2',
        'asuransi' => 'decimal:2',
    ];

    /**
     * Relasi ke jabatan
     */
    public function jabatanRelasi(): BelongsTo
    {
        return $this->belongsTo(Jabatan::class, 'jabatan_id');
    }

    /**
     * Relasi ke presensi
     */
    public function presensis(): HasMany
    {
        return $this->hasMany(Presensi::class, 'pegawai_id');
    }

    // Scope untuk pencarian
    public function scopeSearch($query, $search)
    {
        return $query->where('nama', 'like', "%{$search}%")
                    ->orWhere('kode_pegawai', 'like', "%{$search}%")
                    ->orWhereHas('jabatanRelasi', function($q) use ($search) {
                        $q->where('nama', 'like', "%{$search}%");
                    });
    }

    /**
     * Accessor: Ambil Tarif Per Produk dari jabatan jika di model kosong
     */
    public function getTarifPerProdukAttribute($value)
    {
        if ($value > 0) return $value;
        
        return $this->jabatanRelasi->tarif_per_produk ?? 0;
    }

    /**
     * Get total tunjangan dari jabatan
     */
    public function getTotalTunjanganAttribute()
    {
        if ($this->jabatanRelasi) {
            return ($this->jabatanRelasi->tunjangan ?? 0) + 
                   ($this->jabatanRelasi->tunjangan_transport ?? 0) + 
                   ($this->jabatanRelasi->tunjangan_konsumsi ?? 0);
        }
        return $this->tunjangan ?? 0;
    }

    /**
     * Get tarif produk from related jabatan (kualifikasi)
     */
    public function getTarifProdukFromJabatanAttribute()
    {
        if (is_object($this->jabatanRelasi)) {
            return $this->jabatanRelasi->tarif_produk ?? 0;
        }
        return $this->attributes['tarif_per_produk'] ?? 0;
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
     * Komponen Gaji Lengkap (Untuk Slip Gaji/Costing)
     */
    public function getKomponenGajiAttribute()
    {
        $jabatan = $this->jabatanRelasi;

        if (!is_object($jabatan)) {
            return [
                'gaji_pokok' => $this->gaji_pokok ?? 0,
                'tarif_per_produk' => $this->tarif_per_produk ?? 0,
                'tunjangan_jabatan' => $this->tunjangan ?? 0,
                'tunjangan_transport' => 0,
                'tunjangan_konsumsi' => 0,
                'total_tunjangan' => $this->tunjangan ?? 0,
                'asuransi' => $this->asuransi ?? 0,
            ];
        }

        return [
            'gaji_pokok' => $jabatan->gaji_pokok ?? $this->gaji_pokok ?? 0,
            'tarif_per_produk' => $this->tarif_per_produk ?? 0,
            'tunjangan_jabatan' => $jabatan->tunjangan ?? $this->tunjangan ?? 0,
            'tunjangan_transport' => $jabatan->tunjangan_transport ?? 0,
            'tunjangan_konsumsi' => $jabatan->tunjangan_konsumsi ?? 0,
            'total_tunjangan' => ($jabatan->tunjangan ?? 0) + ($jabatan->tunjangan_transport ?? 0) + ($jabatan->tunjangan_konsumsi ?? 0),
            'asuransi' => $jabatan->asuransi ?? $this->asuransi ?? 0,
        ];
    }
}