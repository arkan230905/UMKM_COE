<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CatalogSection extends Model
{
    use HasFactory;

    protected $fillable = [
        'perusahaan_id',
        'section_type',
        'title',
        'content',
        'image',
        'order',
        'is_active',
    ];

    protected $casts = [
        'content' => 'array',
        'is_active' => 'boolean',
    ];

    /**
     * Get the company that owns the catalog section
     */
    public function perusahaan()
    {
        return $this->belongsTo(Perusahaan::class);
    }
}
