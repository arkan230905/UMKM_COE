<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BebanOperasional extends Model
{
    use HasFactory;

    protected $table = 'beban_operasional';

    protected $fillable = [
        'kode',
        'kategori',
        'nama_beban',
        'budget_bulanan',
        'keterangan',
        'status',
        'coa_id',
        'created_by'
    ];

    protected $casts = [
        'budget_bulanan' => 'decimal:2',
        'status' => 'string',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Relasi ke user yang membuat data
     */
    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Relasi ke COA
     */
    public function coa()
    {
        return $this->belongsTo(Coa::class, 'coa_id');
    }

    
    /**
     * Scope untuk beban operasional aktif
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'aktif');
    }

    /**
     * Scope filter berdasarkan kategori
     */
    public function scopeKategori($query, $kategori)
    {
        if ($kategori) {
            return $query->where('kategori', $kategori);
        }
        return $query;
    }

    /**
     * Scope filter berdasarkan nama beban
     */
    public function scopeSearch($query, $search)
    {
        if ($search) {
            return $query->where('nama_beban', 'like', '%' . $search . '%');
        }
        return $query;
    }

    /**
     * Generate kode otomatis
     */
    public static function generateKode()
    {
        $lastKode = self::orderBy('id', 'desc')->value('kode');
        if (!$lastKode) {
            return 'BO001';
        }
        
        $number = intval(substr($lastKode, 2)) + 1;
        return 'BO' . str_pad($number, 3, '0', STR_PAD_LEFT);
    }

    /**
     * Accessor untuk format budget bulanan
     */
    public function getBudgetBulananFormattedAttribute()
    {
        return 'Rp ' . number_format($this->budget_bulanan, 0, ',', '.');
    }

    
    /**
     * Accessor untuk status text
     */
    public function getStatusBadgeAttribute()
    {
        return $this->status === 'aktif' 
            ? '<span class="text-success fw-semibold">Aktif</span>'
            : '<span class="text-muted">Nonaktif</span>';
    }

    /**
     * Get kategori options
     */
    public static function getKategoriOptions(): array
    {
        return [
            'Administrasi' => 'Administrasi',
            'Marketing' => 'Marketing',
            'Utilitas' => 'Utilitas',
            'Distribusi' => 'Distribusi',
            'Lain-lain' => 'Lain-lain'
        ];
    }

    /**
     * Get status options
     */
    public static function getStatusOptions(): array
    {
        return [
            'aktif' => 'Aktif',
            'nonaktif' => 'Nonaktif'
        ];
    }

    /**
     * Boot method untuk auto-generate kode
     */
    protected static function booted()
    {
        static::creating(function ($bebanOperasional) {
            if (empty($bebanOperasional->kode)) {
                $bebanOperasional->kode = self::generateKode();
            }
        });
    }
}
