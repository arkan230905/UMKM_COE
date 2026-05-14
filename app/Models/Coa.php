<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Bop;
use App\Models\CoaPeriodBalance;

class Coa extends Model
{
    use HasFactory;

    /**
     * Nama tabel di database diarahkan ke 'accounts' 
     * agar sesuai dengan file migrasi terbaru.
     */
    protected $table = 'accounts'; 

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
        'company_id',
        'nomor_rekening',
        'atas_nama',
        'user_id',
        'kode_induk', // Menambahkan fillable agar sinkron dengan kolom migrasi
    ];
    
    protected $appends = ['kode', 'nama'];

    protected $casts = [
        'posted_saldo_awal' => 'boolean',
        'saldo_awal' => 'decimal:2',
        'tanggal_saldo_awal' => 'date',
    ];

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
     * Generate kode akun anak otomatis berdasarkan prefix induk.
     */
    public static function generateChildCode(string $prefix): string
    {
        $prefixLen = strlen($prefix);
        $childLen = $prefixLen + 1;

        $maxChild = self::withoutGlobalScopes()
            ->where('kode_akun', 'LIKE', $prefix . '%')
            ->whereRaw('LENGTH(kode_akun) = ?', [$childLen])
            ->orderByRaw('CAST(kode_akun AS UNSIGNED) DESC')
            ->value('kode_akun');

        if ($maxChild) {
            $nextNum = (int) $maxChild + 1;
        } else {
            $parentPrefix = substr($prefix, 0, $prefixLen - 1);
            $startDigit = '0';

            if ($parentPrefix !== '') {
                $firstSibling = self::withoutGlobalScopes()
                    ->where('kode_akun', 'LIKE', $parentPrefix . '%')
                    ->whereRaw('LENGTH(kode_akun) = ?', [$childLen])
                    ->orderByRaw('CAST(kode_akun AS UNSIGNED) ASC')
                    ->value('kode_akun');

                if ($firstSibling) {
                    $lastDigit = substr($firstSibling, -1);
                    $startDigit = ($lastDigit === '0') ? '0' : '1';
                }
            }

            $nextNum = (int) ($prefix . $startDigit);
        }

        $candidate = (string) $nextNum;
        while (self::withoutGlobalScopes()->where('kode_akun', $candidate)->exists()) {
            $nextNum++;
            $candidate = (string) $nextNum;
        }

        return $candidate;
    }

    /**
     * Generate kode akun otomatis (legacy)
     */
    protected function generateKodeAkun()
    {
        $typeToGroup = [
            'Asset' => '1', 'Aset' => '1', 'ASET' => '1',
            'Liability' => '2', 'Kewajiban' => '2', 'KEWAJIBAN' => '2',
            'Equity' => '3', 'Ekuitas' => '3', 'Modal' => '3', 'MODAL' => '3',
            'Revenue' => '4', 'Pendapatan' => '4', 'PENDAPATAN' => '4',
            'Expense' => '5', 'Beban' => '5', 'BEBAN' => '5',
        ];

        $group = $typeToGroup[$this->tipe_akun] ?? '1';
        return self::generateChildCode($group);
    }
    
    public function scopeByPrefix($query, $prefix)
    {
        return $query->where('kode_akun', 'LIKE', $prefix . '%');
    }
    
    public function getSameGroupAccounts()
    {
        $prefix = substr($this->kode_akun, 0, 1);
        return self::byPrefix($prefix)->get();
    }
    
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public static function validateUniqueKodeAkun($kodeAkun, $excludeId = null)
    {
        $query = self::where('kode_akun', $kodeAkun);
        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }
        return $query->doesntExist();
    }

    protected static function booted()
    {
        parent::booted();
        
        static::creating(function ($coa) {
            if (empty($coa->user_id) && auth()->check()) {
                $coa->user_id = auth()->id();
            }
        });
        
        static::addGlobalScope('user', function ($builder) {
            if (auth()->check()) {
                $builder->where('user_id', auth()->id());
            }
        });
        
        static::creating(function ($coa) {
            if (empty($coa->kode_akun)) {
                $coa->kode_akun = $coa->generateKodeAkun();
            }
        });
        
        static::addGlobalScope('orderByKodeAkun', function ($builder) {
            $builder->orderByRaw("RPAD(kode_akun, 10, '0'), LENGTH(kode_akun)");
        });
    }
}