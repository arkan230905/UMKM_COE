<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Carbon\Carbon;
use App\Models\KategoriAset;

class Aset extends Model
{
    protected $table = 'asets';
    
    protected $fillable = [
        'kode_aset',
        'nama_aset',
        'kategori_aset_id',
        'harga_perolehan',
        'biaya_perolehan',
        'nilai_residu',
        'umur_manfaat',
        'penyusutan_per_tahun',
        'penyusutan_per_bulan',
        'nilai_buku',
        'tanggal_beli',
        'tanggal_akuisisi',
        'status',
        'metode_penyusutan',
        'nilai_sisa',
        'akumulasi_penyusutan',
        'keterangan'
    ];

    protected $casts = [
        'tanggal_perolehan' => 'date',
        'tanggal_beli' => 'date',
        'tanggal_akuisisi' => 'date',
        'harga_perolehan' => 'decimal:2',
        'biaya_perolehan' => 'decimal:2',
        'nilai_residu' => 'decimal:2',
        'nilai_sisa' => 'decimal:2',
        'nilai_buku' => 'decimal:2',
        'akumulasi_penyusutan' => 'decimal:2',
        'persentase_penyusutan' => 'decimal:2',
        'umur_manfaat' => 'integer',
        'umur_ekonomis_tahun' => 'integer',
    ];

    /**
     * Relationship ke COA
     */
    public function coa(): BelongsTo
    {
        return $this->belongsTo(Coa::class);
    }

    /**
     * Relationship ke KategoriAset
     */
    public function kategori(): BelongsTo
    {
        return $this->belongsTo(KategoriAset::class, 'kategori_aset_id');
    }

    /**
     * Relationship ke User (created_by)
     */
    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Relationship ke User (updated_by)
     */
    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Relationship ke Depreciation Schedules
     */
    public function depreciationSchedules(): HasMany
    {
        return $this->hasMany(DepreciationSchedule::class);
    }

    /**
     * Generate kode aset otomatis
     * Format: AST-YYYYMM-XXXX
     */
    public static function generateKodeAset()
    {
        $prefix = 'AST-' . date('Ym') . '-';
        $lastAsset = self::where('kode_aset', 'like', $prefix . '%')
            ->orderBy('kode_aset', 'desc')
            ->first();

        $number = $lastAsset ? (int) str_replace($prefix, '', $lastAsset->kode_aset) + 1 : 1;

        return $prefix . str_pad($number, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Boot method untuk menangani event model
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (!$model->kode_aset) {
                $model->kode_aset = self::generateKodeAset();
            }
            $model->metode_penyusutan = $model->metode_penyusutan ?? 'garis_lurus';
            $model->nilai_sisa = $model->nilai_sisa ?? 0;
            $model->nilai_buku = $model->harga_perolehan ?? 0;
            $model->akumulasi_penyusutan = $model->akumulasi_penyusutan ?? 0;
            $model->status = $model->status ?? 'aktif';
            if (Schema::hasColumn('asets', 'created_by')) {
                $model->created_by = $model->created_by ?? Auth::id();
            }
        });

        static::updating(function ($model) {
            $model->updated_by = Auth::id();
        });
    }

    /**
     * Hitung penyusutan (untuk kompatibilitas dengan AsetController)
     */
    public function hitungPenyusutan()
    {
        // Calculate nilai_buku based on harga_perolehan and nilai_residu
        $this->nilai_buku = $this->harga_perolehan - $this->nilai_residu;
    }

    /**
     * Hitung beban penyusutan bulanan
     */
    public function hitungBebanPenyusutanBulanan(): float
    {
        $nilaiTerdepresiasi = $this->harga_perolehan - $this->nilai_sisa;
        $bulanEkonomis = $this->umur_ekonomis_tahun * 12;

        return $nilaiTerdepresiasi / $bulanEkonomis;
    }

    /**
     * Hitung beban penyusutan tahunan
     */
    public function hitungBebanPenyusutanTahunan(): float
    {
        $nilaiTerdepresiasi = $this->harga_perolehan - $this->nilai_sisa;

        return $nilaiTerdepresiasi / $this->umur_ekonomis_tahun;
    }

    /**
     * Cek apakah aset bisa dihapus
     */
    public function bisaDihapus(): bool
    {
        return $this->akumulasi_penyusutan == 0;
    }

    /**
     * Update nilai buku berdasarkan akumulasi penyusutan
     */
    public function updateNilaiBuku(): void
    {
        $this->nilai_buku = $this->harga_perolehan - $this->akumulasi_penyusutan;
        $this->save();
    }
}
