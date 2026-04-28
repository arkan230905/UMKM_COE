<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Perusahaan extends Model
{
    use HasFactory;

    protected $table = 'perusahaan';

    protected $fillable = [
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
