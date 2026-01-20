<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BahanPendukung extends Model
{
    use HasFactory;

    protected $table = 'bahan_pendukungs';

    protected $fillable = [
        'kode_bahan',
        'nama_bahan',
        'deskripsi',
        'satuan_id',
        'harga_satuan',
        'stok',
        'stok_minimum',
        'kategori',
        'kategori_id',
        'is_active'
    ];

    protected $casts = [
        'harga_satuan' => 'decimal:2',
        'stok' => 'decimal:4',
        'stok_minimum' => 'decimal:4',
        'is_active' => 'boolean'
    ];

    protected $appends = ['stok_aman', 'status_stok'];

    /**
     * Boot method untuk auto-generate kode
     */
    protected static function booted()
    {
        static::creating(function ($model) {
            if (empty($model->kode_bahan)) {
                $model->kode_bahan = self::generateKode();
            }
        });
    }

    /**
     * Generate kode bahan otomatis
     */
    public static function generateKode(): string
    {
        $lastBahan = self::orderBy('id', 'desc')->first();
        $nextNumber = $lastBahan ? ((int) substr($lastBahan->kode_bahan, 4)) + 1 : 1;
        return 'BPD-' . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Relasi ke Satuan
     */
    public function satuan()
    {
        return $this->belongsTo(Satuan::class, 'satuan_id')
            ->withDefault([
                'nama' => 'Tidak Diketahui',
                'kode_satuan' => 'N/A'
            ]);
    }

    /**
     * Relasi ke Satuan (alias untuk backward compatibility)
     */
    public function satuanRelation()
    {
        return $this->belongsTo(Satuan::class, 'satuan_id');
    }

    /**
     * Relasi ke Kategori
     */
    public function kategoriBahanPendukung()
    {
        return $this->belongsTo(KategoriBahanPendukung::class, 'kategori_id');
    }

    /**
     * Check apakah stok aman
     */
    public function getStokAmanAttribute(): bool
    {
        return $this->stok >= $this->stok_minimum;
    }

    /**
     * Get status stok
     */
    public function getStatusStokAttribute(): string
    {
        if ($this->stok <= 0) {
            return 'Habis';
        } elseif ($this->stok < $this->stok_minimum) {
            return 'Menipis';
        }
        return 'Aman';
    }

    /**
     * Scope untuk bahan aktif
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope untuk filter kategori
     */
    public function scopeKategori($query, $kategori)
    {
        return $query->where('kategori', $kategori);
    }
}
