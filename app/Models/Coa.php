<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Bop;

class Coa extends Model
{
    use HasFactory;

    protected $table = 'coas';
    protected $primaryKey = 'kode_akun';
    public $incrementing = false;
    protected $keyType = 'string';
    
    /**
     * Use 'kode_akun' for route model binding.
     */
    public function getRouteKeyName()
    {
        return 'kode_akun';
    }

    // Tipe akun untuk BOP
    const TIPE_BIAYA = 'Biaya';

    protected $fillable = [
        'kode_akun',
        'nama_akun',
        'kategori_akun',
        'tipe_akun',
        'kode_induk',
        'saldo_normal',
        'keterangan'
    ];

    protected $casts = [
        'saldo_normal' => 'decimal:2'
    ];

    /**
     * Relasi ke akun induk (hierarchical)
     */
    public function induk()
    {
        return $this->belongsTo(Coa::class, 'kode_induk', 'kode_akun');
    }

    /**
     * Relasi ke akun anak (hierarchical)
     */
    public function subAkun()
    {
        return $this->hasMany(Coa::class, 'kode_induk', 'kode_akun');
    }
    
    /**
     * Cek apakah akun termasuk BOP
     */
    public function isBop()
    {
        return $this->tipe_akun === self::TIPE_BIAYA;
    }
    
    /**
     * Relasi ke transaksi BOP
     */
    public function bops()
    {
        return $this->hasMany(Bop::class, 'kode_akun', 'kode_akun');
    }

    /**
     * The "booting" method of the model.
     *
     * @return void
     */
    protected static function booted()
    {
        parent::booted();
        
        // Generate kode akun otomatis jika kosong
        static::creating(function ($coa) {
            if (empty($coa->kode_akun)) {
                $coa->kode_akun = $coa->generateKodeAkun();
            }
        });
        
        // Default order by kode_akun
        static::addGlobalScope('orderByKode', function ($builder) {
            $builder->orderBy('kode_akun');
        });
    }
    
    /**
     * Generate kode akun otomatis
     */
    protected function generateKodeAkun()
    {
        // Logika generate kode akun disini
        // Contoh: Cari kode terakhir dan tambahkan 1
        $lastCoa = self::orderBy('kode_akun', 'desc')->first();
        $lastNumber = $lastCoa ? (int) substr($lastCoa->kode_akun, -3) : 0;
        return str_pad($lastNumber + 1, 3, '0', STR_PAD_LEFT);
    }
}
