<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Bop;
use App\Models\CoaPeriodBalance;

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
        'saldo_normal',
        'keterangan',
        'saldo_awal',
        'tanggal_saldo_awal',
        'posted_saldo_awal',
    ];
    
    protected $appends = ['kode', 'nama'];

    protected $casts = [
        'posted_saldo_awal' => 'boolean',
        'saldo_awal' => 'decimal:2',
        'tanggal_saldo_awal' => 'date',
    ];

    // No casts: saldo_normal stores strings like 'debit'/'kredit'
    
    /**
     * Accessor untuk backward compatibility
     */
    public function getKodeAttribute()
    {
        return $this->kode_akun;
    }
    
    public function getNamaAttribute()
    {
        return $this->nama_akun;
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
     * Relasi ke saldo periode
     */
    public function periodBalances()
    {
        return $this->hasMany(CoaPeriodBalance::class, 'kode_akun', 'kode_akun');
    }

    /**
     * Get saldo untuk periode tertentu
     */
    public function getSaldoPeriode($periodId)
    {
        $balance = $this->periodBalances()->where('period_id', $periodId)->first();
        return $balance ? $balance->saldo_awal : ($this->saldo_awal ?? 0);
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
        
        // Default order by kode_akun untuk memastikan urutan yang konsisten
        static::addGlobalScope('orderByKodeAkun', function ($builder) {
            $builder->orderByRaw('CAST(kode_akun AS UNSIGNED) ASC, kode_akun ASC');
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
    
    /**
     * Scope untuk mencari akun berdasarkan prefix kode
     */
    public function scopeByPrefix($query, $prefix)
    {
        return $query->where('kode_akun', 'LIKE', $prefix . '%');
    }
    
    /**
     * Get akun dengan prefix yang sama (nomor kepala yang sama)
     */
    public function getSameGroupAccounts()
    {
        $prefix = substr($this->kode_akun, 0, 1); // Ambil digit pertama
        return self::byPrefix($prefix)->get();
    }
    
    /**
     * Validasi kode akun tidak duplikat
     */
    public static function validateUniqueKodeAkun($kodeAkun, $excludeId = null)
    {
        $query = self::where('kode_akun', $kodeAkun);
        
        if ($excludeId) {
            $query->where('kode_akun', '!=', $excludeId);
        }
        
        return $query->doesntExist();
    }
}
