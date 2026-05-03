<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pelanggan extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'kode_pelanggan',
        'nama_pelanggan',
        'alamat',
        'telepon',
        'email',
        'keterangan'
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($pelanggan) {
            // CRITICAL: Auto-fill user_id for multi-tenant isolation
            if (empty($pelanggan->user_id) && auth()->check()) {
                $pelanggan->user_id = auth()->id();
            }
            
            if (empty($pelanggan->kode_pelanggan)) {
                $pelanggan->kode_pelanggan = $pelanggan->generateKodePelanggan();
            }
        });
    }

    public function generateKodePelanggan()
    {
        $date = now()->format('ym');
        $lastPelanggan = self::whereYear('created_at', now()->year)
            ->whereMonth('created_at', now()->month)
            ->orderBy('id', 'desc')
            ->first();

        if ($lastPelanggan) {
            $lastNumber = (int) substr($lastPelanggan->kode_pelanggan, -4);
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }

        return 'CUS' . $date . str_pad($newNumber, 4, '0', STR_PAD_LEFT);
    }

    public function returPenjualans()
    {
        return $this->hasMany(ReturPenjualan::class);
    }
}
