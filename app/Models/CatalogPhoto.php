<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CatalogPhoto extends Model
{
    use HasFactory;

    protected $fillable = [
        'perusahaan_id',
        'judul',
        'foto',
        'deskripsi',
        'urutan',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'urutan' => 'integer',
    ];

    /**
     * Get the perusahaan that owns the catalog photo
     */
    public function perusahaan()
    {
        return $this->belongsTo(Perusahaan::class);
    }

    /**
     * Scope to get only active photos
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to order by urutan
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('urutan', 'asc')->orderBy('created_at', 'asc');
    }

    /**
     * Get full URL for the photo
     */
    public function getFotoUrlAttribute()
    {
        return asset('storage/' . $this->foto);
    }
}
