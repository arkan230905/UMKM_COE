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
    protected $primaryKey = 'id';
    public $incrementing = true;
    protected $keyType = 'int';

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
        return $this->hasMany(Bop::class, 'coa_id', 'id');
    }

    /**
     * Relasi ke saldo periode
     */
    public function periodBalances()
    {
        return $this->hasMany(CoaPeriodBalance::class, 'coa_id', 'id');
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
        $typeToGroup = [
            'Asset' => '1',
            'Aset' => '1',
            'Liability' => '2',
            'Kewajiban' => '2',
            'Equity' => '3',
            'Ekuitas' => '3',
            'Revenue' => '4',
            'Pendapatan' => '4',
            'Expense' => '5',
            'Beban' => '5',
        ];

        $group = $typeToGroup[$this->tipe_akun] ?? null;
        if (!$group) {
            $group = '1';
        }

        $prefix = $group;
        $last = self::where('kode_akun', 'like', $prefix . '%')
            ->whereRaw('LENGTH(kode_akun) = 4')
            ->orderByRaw('CAST(kode_akun AS UNSIGNED) DESC')
            ->value('kode_akun');

        $next = $last ? ((int) $last + 1) : ((int) ($prefix . '101'));
        return (string) $next;
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
        $prefix = substr($this->kode_akun, 0, 1);
        return self::byPrefix($prefix)->get();
    }
    
    /**
     * Validasi kode akun tidak duplikat
     */
    public static function validateUniqueKodeAkun($kodeAkun, $excludeId = null)
    {
        $query = self::where('kode_akun', $kodeAkun);
        
        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }
        
        return $query->doesntExist();
    }
}
