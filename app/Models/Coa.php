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
     * Nama tabel di database adalah 'coas'
     */
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
     * 
     * ATURAN KETAT:
     * - Child HARUS berawalan dengan EXACT parent code
     * - Child HARUS panjangnya = parent + 1 digit
     * 
     * Contoh:
     * - Parent "11" → Child "111", "112", "113" (NOT "114" jika 114 bukan child dari 11)
     * - Parent "111" → Child "1111", "1112", "1113" (4 digit, awalan 111)
     * - Parent "114" → Child "1141", "1142", "1143" (4 digit, awalan 114)
     */
    public static function generateChildCode(string $prefix): string
    {
        $prefixLen = strlen($prefix);
        $childLen = $prefixLen + 1;

        // Cari child yang BENAR-BENAR anak dari prefix ini
        // Harus: 1) Berawalan prefix, 2) Panjangnya exact childLen
        $maxChild = self::withoutGlobalScopes()
            ->where('kode_akun', 'LIKE', $prefix . '%')
            ->whereRaw('LENGTH(kode_akun) = ?', [$childLen])
            ->where('user_id', auth()->id())
            ->orderByRaw('CAST(kode_akun AS UNSIGNED) DESC')
            ->value('kode_akun');

        if ($maxChild) {
            // Sudah ada child, increment
            $nextNum = (int) $maxChild + 1;
            $candidate = (string) $nextNum;
        } else {
            // Belum ada child sama sekali
            // Mulai dari prefix + "1"
            // Contoh: "111" → "1111"
            $candidate = $prefix . '1';
        }

        // Validasi: candidate harus tetap berawalan prefix dan panjangnya benar
        while (true) {
            // Cek apakah candidate masih valid (berawalan prefix dan panjangnya benar)
            if (!str_starts_with($candidate, $prefix)) {
                throw new \Exception("Generate kode anak gagal: melewati batas prefix {$prefix}");
            }
            
            if (strlen($candidate) != $childLen) {
                throw new \Exception("Generate kode anak gagal: panjang tidak sesuai untuk prefix {$prefix}");
            }

            // Cek apakah sudah ada yang pakai
            $exists = self::withoutGlobalScopes()
                ->where('kode_akun', $candidate)
                ->where('user_id', auth()->id())
                ->exists();

            if (!$exists) {
                // Kandidat unik dan valid
                return $candidate;
            }

            // Sudah ada, coba increment
            $nextNum = (int) $candidate + 1;
            $candidate = (string) $nextNum;
        }
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