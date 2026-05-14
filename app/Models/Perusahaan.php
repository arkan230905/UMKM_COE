<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Perusahaan extends Model
{
    use HasFactory;

    protected $table = 'perusahaan';

    /**
     * Boot method untuk auto-fill user_id untuk multi-tenant isolation
     */
    protected static function booted()
    {
        static::creating(function ($model) {
            // CRITICAL: Auto-fill user_id for multi-tenant isolation
            if (empty($model->user_id) && auth()->check()) {
                $model->user_id = auth()->id();
            }
        });
        
        static::updating(function ($model) {
            // Ensure user_id tidak berubah saat update
            if (empty($model->user_id) && auth()->check()) {
                $model->user_id = auth()->id();
            }
        });
    }

    protected $fillable = [
        'user_id',
        'nama', 
        'alamat', 
        'email', 
        'telepon', 
        'kode', 
        'foto', 
        'catalog_description', 
        'maps_link', 
        'latitude', 
        'longitude',
        'background_type',
        'background_color',
        'gradient_color_1',
        'gradient_color_2',
        'gradient_direction',
        'background_image',
        'background_opacity'
    ];

    public function kasirs()
    {
        return $this->hasMany(Kasir::class);
    }

    public function users()
    {
        return $this->hasMany(User::class);
    }

    /**
     * Get pegawai yang bekerja di perusahaan ini
     */
    public function pegawais()
    {
        return $this->hasMany(Pegawai::class, 'perusahaan_id');
    }

    /**
     * Get catalog photos for the company
     */
    public function catalogPhotos()
    {
        return $this->hasMany(CatalogPhoto::class)->ordered();
    }

    /**
     * Get catalog sections for the company
     */
    public function catalogSections()
    {
        return $this->hasMany(CatalogSection::class)->orderBy('order', 'asc');
    }

    /**
     * Generate kode perusahaan unik
     */
    public static function generateKode(): string
    {
        do {
            $kode = strtoupper(substr(md5(uniqid()), 0, 6));
        } while (self::where('kode', $kode)->exists());
        
        return $kode;
    }
}
