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
        'company_id',
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
        
        // Filter COA berdasarkan company_id user yang login (kecuali untuk template)
        static::addGlobalScope('filterByCompany', function ($builder) {
            $user = auth()->user();
            if ($user && $user->perusahaan_id) {
                // Hanya tampilkan COA milik company user
                $builder->where('company_id', $user->perusahaan_id);
            } else {
                // Jika tidak ada user atau company_id, hanya tampilkan template
                $builder->whereNull('company_id');
            }
        });
        
        // Default order hierarkis: parent diikuti children (11 → 111 → 1131 → 112 → ...)
        static::addGlobalScope('orderByKodeAkun', function ($builder) {
            $builder->orderByRaw("RPAD(kode_akun, 10, '0'), LENGTH(kode_akun)");
        });
    }
    
    /**
     * Generate kode akun anak otomatis berdasarkan prefix induk.
     *
     * Contoh:
     *   prefix '114', existing: 114,1141,1142,1143,1144 → return '1145'
     *   prefix '51',  existing: 51,510,511,512           → return '513'
     *   prefix '11',  existing: 11 (belum ada anak)      → return '111'
     *
     * @param string $prefix  Kode akun induk (misal '114', '51', '21')
     * @return string         Kode akun anak berikutnya
     */
    public static function generateChildCode(string $prefix): string
    {
        $prefixLen = strlen($prefix);
        $childLen = $prefixLen + 1;

        // Cari kode_akun anak langsung (panjang = prefix + 1 digit) yang diawali prefix
        $maxChild = self::withoutGlobalScopes()
            ->where('kode_akun', 'LIKE', $prefix . '%')
            ->whereRaw('LENGTH(kode_akun) = ?', [$childLen])
            ->orderByRaw('CAST(kode_akun AS UNSIGNED) DESC')
            ->value('kode_akun');

        if ($maxChild) {
            $nextNum = (int) $maxChild + 1;
        } else {
            // Belum ada anak → deteksi pola awal (0 atau 1) dari sibling
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

        // Pastikan kode yang dihasilkan belum ada (hindari konflik)
        $candidate = (string) $nextNum;
        while (self::withoutGlobalScopes()->where('kode_akun', $candidate)->exists()) {
            $nextNum++;
            $candidate = (string) $nextNum;
        }

        return $candidate;
    }

    /**
     * Generate kode akun otomatis (legacy, untuk backward compatibility)
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

        // Gunakan generateChildCode dengan group sebagai prefix
        return self::generateChildCode($group);
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
