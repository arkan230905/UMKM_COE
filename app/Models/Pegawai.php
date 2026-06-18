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
        'kualifikasi',
        'kualifikasi_id',
        'jabatan',         // Backward compatibility alias for kualifikasi
        'jabatan_id',      // Backward compatibility alias for kualifikasi_id
        'kategori_id',
        'kategori',
        'gaji_pokok',
        'tarif_per_jam',
        'tarif',
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
            
            // Auto-generate kode_pegawai jika kosong — per tenant (user_id)
            if (empty($model->kode_pegawai)) {
                $userId = $model->user_id ?? (auth()->check() ? auth()->id() : null);

                // Ambil nomor urut tertinggi khusus untuk user/tenant ini
                $lastCode = static::withoutGlobalScopes()
                    ->where('user_id', $userId)
                    ->where('kode_pegawai', 'LIKE', 'PGW%')
                    ->orderByRaw('CAST(SUBSTRING(kode_pegawai, 4) AS UNSIGNED) DESC')
                    ->value('kode_pegawai');

                $lastNumber = 0;
                if ($lastCode && preg_match('/^PGW(\d+)$/', $lastCode, $m)) {
                    $lastNumber = (int) $m[1];
                }

                $nextNumber = $lastNumber + 1;
                $newCode    = 'PGW' . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);

                // Pastikan tidak duplikat (untuk tenant yang sama)
                while (static::withoutGlobalScopes()->where('user_id', $userId)->where('kode_pegawai', $newCode)->exists()) {
                    $nextNumber++;
                    $newCode = 'PGW' . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);
                }

                $model->kode_pegawai = $newCode;
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
        'tarif' => 'decimal:2', // PERBAIKAN: Cast tarif
        'tunjangan' => 'decimal:2',
        'asuransi' => 'decimal:2',
    ];

    /**
     * Relasi ke kualifikasi
     */
    public function kualifikasiRelasi(): BelongsTo
    {
        return $this->belongsTo(Kualifikasi::class, 'kualifikasi_id');
    }

    /**
     * Relasi ke jabatan (alias untuk kualifikasi via kualifikasi_id)
     * Digunakan untuk backward compatibility dengan PenggajianController
     */
    public function jabatanRelasi(): BelongsTo
    {
        return $this->belongsTo(Jabatan::class, 'kualifikasi_id');
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
                    ->orWhereHas('kualifikasiRelasi', function($q) use ($search) {
                        $q->where('nama_kualifikasi', 'like', "%{$search}%");
                    });
    }

    /**
     * Accessor: Ambil Tarif Per Produk dari kualifikasi jika di model kosong
     */
    public function getTarifPerProdukAttribute($value)
    {
        if (isset($this->attributes['tarif']) && $this->attributes['tarif'] > 0) return $this->attributes['tarif'];
        
        return $this->kualifikasiRelasi->tarif_produk ?? 0;
    }

    /**
     * Get total tunjangan dari kualifikasi
     */
    public function getTotalTunjanganAttribute()
    {
        if ($this->kualifikasiRelasi) {
            return ($this->kualifikasiRelasi->tunjangan ?? 0) + 
                   ($this->kualifikasiRelasi->tunjangan_transport ?? 0) + 
                   ($this->kualifikasiRelasi->tunjangan_konsumsi ?? 0);
        }
        return $this->tunjangan ?? 0;
    }

    /**
     * Get tarif produk from related kualifikasi
     */
    public function getTarifProdukFromKualifikasiAttribute()
    {
        if (is_object($this->kualifikasiRelasi)) {
            return $this->kualifikasiRelasi->tarif_produk ?? 0;
        }
        return $this->attributes['tarif'] ?? 0;
    }

    /**
     * Get asuransi from related kualifikasi
     */
    public function getAsuransiFromKualifikasiAttribute()
    {
        if (is_object($this->kualifikasiRelasi)) {
            return $this->kualifikasiRelasi->asuransi ?? 0;
        }
        return $this->attributes['asuransi'] ?? 0;
    }

    /**
     * Komponen Gaji Lengkap (Untuk Slip Gaji/Costing)
     */
    public function getKomponenGajiAttribute()
    {
        $kualifikasi = $this->kualifikasiRelasi;

        if (!is_object($kualifikasi)) {
            return [
                'gaji_pokok' => $this->gaji_pokok ?? 0,
                'tarif_per_produk' => $this->tarif ?? 0,
                'tunjangan_jabatan' => $this->tunjangan ?? 0,
                'tunjangan_transport' => 0,
                'tunjangan_konsumsi' => 0,
                'total_tunjangan' => $this->tunjangan ?? 0,
                'asuransi' => $this->asuransi ?? 0,
            ];
        }

        return [
            'gaji_pokok' => $kualifikasi->gaji_pokok ?? $this->gaji_pokok ?? 0,
            'tarif_per_produk' => $this->tarif ?? 0,
            'tunjangan_jabatan' => $kualifikasi->tunjangan ?? $this->tunjangan ?? 0,
            'tunjangan_transport' => $kualifikasi->tunjangan_transport ?? 0,
            'tunjangan_konsumsi' => $kualifikasi->tunjangan_konsumsi ?? 0,
            'total_tunjangan' => ($kualifikasi->tunjangan ?? 0) + ($kualifikasi->tunjangan_transport ?? 0) + ($kualifikasi->tunjangan_konsumsi ?? 0),
            'asuransi' => $kualifikasi->asuransi ?? $this->asuransi ?? 0,
        ];
    }
}