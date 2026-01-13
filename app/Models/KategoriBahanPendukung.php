<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KategoriBahanPendukung extends Model
{
    use HasFactory;

    protected $table = 'kategori_bahan_pendukung';

    protected $fillable = ['nama', 'keterangan', 'is_active'];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function bahanPendukungs()
    {
        return $this->hasMany(BahanPendukung::class, 'kategori_id');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
