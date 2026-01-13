<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Kasir extends Model
{
    use HasFactory;

    protected $table = 'kasirs';

    protected $fillable = ['nama', 'kode_kasir', 'perusahaan_id', 'is_active', 'last_login'];

    protected $casts = [
        'is_active' => 'boolean',
        'last_login' => 'datetime',
    ];

    public function perusahaan()
    {
        return $this->belongsTo(Perusahaan::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public static function generateKode($perusahaanId): string
    {
        $count = self::where('perusahaan_id', $perusahaanId)->count() + 1;
        return 'KSR-' . str_pad($count, 3, '0', STR_PAD_LEFT);
    }
}
