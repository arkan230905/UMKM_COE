<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Jabatan extends Model
{
    protected $fillable = [
        'kode_jabatan', 
        'nama', 
        'kategori',
        'kategori_id',
        'gaji_pokok', 
        'tunjangan', 
        'asuransi', 
        'tarif',
        'tarif_per_jam', 
        'deskripsi'
    ];

    protected $casts = [
        'gaji' => 'decimal:2',
        'tunjangan' => 'decimal:2',
        'asuransi' => 'decimal:2',
        'tarif' => 'decimal:2',
    ];

    /**
     * Get kategori name (accessor for backward compat)
     */
    public function getKategoriIdAttribute()
    {
        return $this->kategori;
    }

    /**
     * Relasi ke pegawai
     */
    public function pegawais(): HasMany
    {
        return $this->hasMany(Pegawai::class, 'jabatan', 'nama');
    }

    /**
     * Get count of active employees for this position
     */
    public function getJumlahPegawaiAttribute()
    {
        return $this->pegawais()->count();
    }
    
    /**
     * Calculate automatic BTKL rate based on tariff and employee count
     */
    /**
     * Get gaji_pokok (alias for gaji column)
     */
    public function getGajiPokokAttribute()
    {
        return $this->gaji;
    }

    public function getTarifBtklAttribute()
    {
        $jumlahPegawai = $this->pegawais()->count();
        return $this->tarif * $jumlahPegawai;
    }

    /**
     * Get tarif per jam (alias for tarif column)
     */
    public function getTarifPerJamAttribute()
    {
        return $this->tarif;
    }

    /**
     * Scope untuk mencari jabatan
     */
    public function scopeSearch($query, $search)
    {
        return $query->where('nama', 'like', "%{$search}%")
                    ->orWhere('kategori', 'like', "%{$search}%");
    }

    /**
     * Scope untuk filter by kategori
     */
    public function scopeByKategori($query, $kategori)
    {
        return $query->where('kategori', $kategori);
    }
}
