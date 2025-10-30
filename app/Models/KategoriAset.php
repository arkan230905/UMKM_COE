<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class KategoriAset extends Model
{
    protected $table = 'kategori_asets';
    
    protected $fillable = [
        'jenis_aset_id',
        'kode',
        'nama',
        'deskripsi',
        'umur_ekonomis',
        'tarif_penyusutan',
    ];

    protected $casts = [
        'umur_ekonomis' => 'integer',
        'tarif_penyusutan' => 'decimal:2',
    ];

    /**
     * Get the jenis aset that owns the kategori aset.
     */
    public function jenisAset(): BelongsTo
    {
        return $this->belongsTo(JenisAset::class, 'jenis_aset_id');
    }

    /**
     * Get the asets for the kategori aset.
     */
    public function asets(): HasMany
    {
        return $this->hasMany(Aset::class, 'kategori_aset_id');
    }

    /**
     * Get the umur ekonomis in months.
     */
    public function getUmurEkonomisBulanAttribute(): int
    {
        return $this->umur_ekonomis * 12;
    }
}
