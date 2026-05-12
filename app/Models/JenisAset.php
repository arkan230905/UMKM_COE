<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class JenisAset extends Model
{
    protected $table = 'jenis_asets';
    
    protected $fillable = [
        'nama',
        'deskripsi',
    ];

    /**
     * Get the kategories for the jenis aset.
     */
    public function kategories(): HasMany
    {
        return $this->hasMany(KategoriAset::class, 'jenis_aset_id');
    }

    /**
     * Get the asets for the jenis aset.
     */
    public function asets(): HasMany
    {
        return $this->hasMany(Aset::class, 'jenis_aset_id');
    }
}
