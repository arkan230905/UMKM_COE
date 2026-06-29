<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class TargetProduksi extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'target_produksi';

    protected $fillable = [
        'user_id',
        'tahun',
        'produk_id',
        'total_target_tahunan',
        'created_by',
    ];

    protected $casts = [
        'tahun' => 'integer',
        'total_target_tahunan' => 'integer',
    ];

    protected static function boot()
    {
        parent::boot();

        // Apply global scope for multi-tenant isolation
        static::addGlobalScope(new \App\Scopes\UserScope);

        static::creating(function ($model) {
            // Auto-fill user_id for multi-tenant isolation
            if (empty($model->user_id) && auth()->check()) {
                $model->user_id = auth()->id();
            }

            // Set created_by
            if (empty($model->created_by) && auth()->check()) {
                $model->created_by = auth()->id();
            }
        });

        static::created(function ($model) {
            $model->logActivity('created', null, $model->toArray());
        });

        static::updated(function ($model) {
            if ($model->wasChanged()) {
                $model->logActivity('updated', $model->getOriginal(), $model->getChanges());
            }
        });

        static::deleted(function ($model) {
            $model->logActivity('deleted', $model->toArray(), null);
        });
    }

    /**
     * Relasi ke produk
     */
    public function produk(): BelongsTo
    {
        return $this->belongsTo(Produk::class, 'produk_id');
    }

    /**
     * Relasi ke user yang membuat
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Relasi ke detail target bulanan
     */
    public function details(): HasMany
    {
        return $this->hasMany(TargetProduksiDetail::class, 'target_produksi_id')
            ->orderBy('bulan');
    }

    /**
     * Relasi ke audit log
     */
    public function logs(): HasMany
    {
        return $this->hasMany(TargetProduksiLog::class, 'target_produksi_id')
            ->orderByDesc('created_at');
    }

    /**
     * Get status target (Aktif/Selesai)
     */
    public function getStatusAttribute(): string
    {
        $currentYear = now()->year;
        
        if ($this->tahun > $currentYear) {
            return 'Belum Dimulai';
        } elseif ($this->tahun < $currentYear) {
            return 'Selesai';
        } else {
            return 'Aktif';
        }
    }

    /**
     * Get status badge color
     */
    public function getStatusColorAttribute(): string
    {
        return match($this->status) {
            'Belum Dimulai' => 'info',
            'Aktif' => 'success',
            'Selesai' => 'gray',
            default => 'gray',
        };
    }

    /**
     * Get target untuk bulan tertentu
     */
    public function getTargetBulan(int $bulan): int
    {
        return $this->details()
            ->where('bulan', $bulan)
            ->value('target_bulanan') ?? 0;
    }

    /**
     * Get total realisasi produksi
     */
    public function getTotalRealisasiAttribute(): int
    {
        return Produksi::where('user_id', $this->user_id)
            ->where('produk_id', $this->produk_id)
            ->whereYear('tanggal_produksi', $this->tahun)
            ->sum('jumlah_produksi') ?? 0;
    }

    /**
     * Get persentase pencapaian
     */
    public function getPersentasePencapaianAttribute(): float
    {
        if ($this->total_target_tahunan == 0) {
            return 0;
        }

        return round(($this->total_realisasi / $this->total_target_tahunan) * 100, 2);
    }

    /**
     * Get selisih produksi
     */
    public function getSelisihAttribute(): int
    {
        return $this->total_realisasi - $this->total_target_tahunan;
    }

    /**
     * Check apakah target sudah digunakan dalam produksi
     */
    public function hasProductions(): bool
    {
        return Produksi::where('user_id', $this->user_id)
            ->where('produk_id', $this->produk_id)
            ->whereYear('tanggal_produksi', $this->tahun)
            ->exists();
    }

    /**
     * Check apakah bisa dihapus
     */
    public function canBeDeleted(): bool
    {
        return !$this->hasProductions();
    }

    /**
     * Validasi total target bulanan sama dengan tahunan
     */
    public function validateMonthlyTotal(): bool
    {
        $totalBulanan = $this->details()->sum('target_bulanan');
        return $totalBulanan === $this->total_target_tahunan;
    }

    /**
     * Log activity
     */
    public function logActivity(string $action, ?array $oldData, ?array $newData): void
    {
        TargetProduksiLog::create([
            'target_produksi_id' => $this->id,
            'user_id' => auth()->id() ?? $this->created_by ?? $this->user_id,
            'action' => $action,
            'old_data' => $oldData ? json_encode($oldData) : null,
            'new_data' => $newData ? json_encode($newData) : null,
            'description' => $this->generateLogDescription($action),
        ]);
    }

    /**
     * Generate log description
     */
    private function generateLogDescription(string $action): string
    {
        $userName = auth()->user()->name ?? $this->creator->name ?? 'System';
        $productName = $this->produk->nama_produk ?? 'Unknown';

        return match($action) {
            'created' => "{$userName} membuat target produksi untuk {$productName} tahun {$this->tahun}",
            'updated' => "{$userName} mengubah target produksi untuk {$productName} tahun {$this->tahun}",
            'deleted' => "{$userName} menghapus target produksi untuk {$productName} tahun {$this->tahun}",
            default => "{$userName} melakukan aksi {$action}",
        };
    }

    /**
     * Scope untuk filter berdasarkan tahun
     */
    public function scopeByYear($query, int $year)
    {
        return $query->where('tahun', $year);
    }

    /**
     * Scope untuk filter berdasarkan produk
     */
    public function scopeByProduct($query, int $productId)
    {
        return $query->where('produk_id', $productId);
    }

    /**
     * Scope untuk target aktif (tahun berjalan)
     */
    public function scopeActive($query)
    {
        return $query->where('tahun', now()->year);
    }
}
