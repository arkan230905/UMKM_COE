<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;

class TargetProduksiDetail extends Model
{
    use HasFactory;

    protected $table = 'target_produksi_detail';

    protected $fillable = [
        'user_id',
        'target_produksi_id',
        'bulan',
        'target_bulanan',
    ];

    protected $casts = [
        'bulan' => 'integer',
        'target_bulanan' => 'integer',
    ];

    /**
     * Boot the model.
     */
    protected static function booted(): void
    {
        // Global scope untuk multi-tenant isolation
        static::addGlobalScope('user', function (Builder $builder) {
            if (auth()->check()) {
                $builder->where('target_produksi_detail.user_id', auth()->id());
            }
        });

        // Auto-fill user_id saat creating
        static::creating(function ($model) {
            if (!$model->user_id && auth()->check()) {
                $model->user_id = auth()->id();
            }
        });
    }

    /**
     * Relasi ke user
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relasi ke target produksi
     */
    public function targetProduksi(): BelongsTo
    {
        return $this->belongsTo(TargetProduksi::class, 'target_produksi_id');
    }

    /**
     * Get nama bulan
     */
    public function getNamaBulanAttribute(): string
    {
        return $this->getMonthName($this->bulan);
    }

    /**
     * Get month name
     */
    public static function getMonthName(int $month): string
    {
        return match($month) {
            1 => 'Januari',
            2 => 'Februari',
            3 => 'Maret',
            4 => 'April',
            5 => 'Mei',
            6 => 'Juni',
            7 => 'Juli',
            8 => 'Agustus',
            9 => 'September',
            10 => 'Oktober',
            11 => 'November',
            12 => 'Desember',
            default => 'Unknown',
        };
    }

    /**
     * Check apakah bulan ini terkunci (locked)
     */
    public function isLocked(): bool
    {
        return $this->checkLockStatus($this->targetProduksi->tahun, $this->bulan);
    }

    /**
     * Static method untuk check lock status
     */
    public static function checkLockStatus(int $targetYear, int $targetMonth): bool
    {
        $now = now();
        $currentYear = $now->year;
        $currentMonth = $now->month;

        // Jika tahun target lebih kecil dari tahun sekarang, locked
        if ($targetYear < $currentYear) {
            return true;
        }

        // Jika tahun target lebih besar dari tahun sekarang, editable
        if ($targetYear > $currentYear) {
            return false;
        }

        // Jika tahun sama, cek bulan
        // Bulan yang sudah lewat atau bulan berjalan = locked
        // Hanya bulan setelah bulan berjalan yang editable
        return $targetMonth <= $currentMonth;
    }

    /**
     * Get lock status attribute
     */
    public function getLockStatusAttribute(): string
    {
        return $this->isLocked() ? 'Locked' : 'Editable';
    }

    /**
     * Get realisasi produksi untuk bulan ini
     */
    public function getRealisasiAttribute(): int
    {
        if (!$this->targetProduksi) {
            return 0;
        }

        return Produksi::where('user_id', $this->targetProduksi->user_id)
            ->where('produk_id', $this->targetProduksi->produk_id)
            ->whereYear('tanggal_produksi', $this->targetProduksi->tahun)
            ->whereMonth('tanggal_produksi', $this->bulan)
            ->sum('jumlah_produksi') ?? 0;
    }

    /**
     * Get persentase pencapaian bulan ini
     */
    public function getPersentaseAttribute(): float
    {
        if ($this->target_bulanan == 0) {
            return 0;
        }

        return round(($this->realisasi / $this->target_bulanan) * 100, 2);
    }

    /**
     * Get selisih produksi bulan ini
     */
    public function getSelisihAttribute(): int
    {
        return $this->realisasi - $this->target_bulanan;
    }

    /**
     * Scope untuk filter berdasarkan bulan
     */
    public function scopeByMonth($query, int $month)
    {
        return $query->where('bulan', $month);
    }

    /**
     * Scope untuk bulan yang editable
     */
    public function scopeEditable($query, int $targetYear)
    {
        $currentYear = now()->year;
        $currentMonth = now()->month;

        if ($targetYear > $currentYear) {
            return $query; // All months editable
        }

        if ($targetYear < $currentYear) {
            return $query->whereRaw('1 = 0'); // No months editable
        }

        // Same year: only months after current month
        return $query->where('bulan', '>', $currentMonth);
    }

    /**
     * Scope untuk bulan yang locked
     */
    public function scopeLocked($query, int $targetYear)
    {
        $currentYear = now()->year;
        $currentMonth = now()->month;

        if ($targetYear < $currentYear) {
            return $query; // All months locked
        }

        if ($targetYear > $currentYear) {
            return $query->whereRaw('1 = 0'); // No months locked
        }

        // Same year: months up to and including current month
        return $query->where('bulan', '<=', $currentMonth);
    }
}
