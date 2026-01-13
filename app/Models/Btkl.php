<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Btkl extends Model
{
    use HasFactory;

    protected $table = 'btkls';
    protected $primaryKey = 'id';
    public $incrementing = true;

    protected $fillable = [
        'kode_proses',
        'jabatan_id',
        'tarif_per_jam',
        'satuan',
        'kapasitas_per_jam',
        'deskripsi_proses',
        'is_active'
    ];

    protected $casts = [
        'tarif_per_jam' => 'decimal:2',
        'kapasitas_per_jam' => 'integer',
        'is_active' => 'boolean'
    ];

    /**
     * Relasi ke model Jabatan
     */
    public function jabatan()
    {
        return $this->belongsTo(Jabatan::class, 'jabatan_id');
    }

    /**
     * Scope untuk filter BTKL aktif
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Format tarif per jam
     *
     * @return string
     */
    public function getTarifPerJamFormattedAttribute()
    {
        return 'Rp ' . number_format($this->tarif_per_jam, 0, ',', '.');
    }

    /**
     * Get nama tenaga kerja (alias untuk jabatan nama)
     *
     * @return string
     */
    public function getNamaTenagaKerjaAttribute()
    {
        return $this->jabatan ? $this->jabatan->nama : '-';
    }

    /**
     * Get nama proses (alias untuk jabatan nama)
     *
     * @return string
     */
    public function getNamaProsesAttribute()
    {
        return $this->jabatan ? $this->jabatan->nama : '-';
    }
}