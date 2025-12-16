<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Carbon\Carbon;
use App\Models\ExpensePayment;

class BopBudget extends Model
{
    protected $fillable = [
        'coa_id',
        'jumlah_budget',
        'periode',
        'keterangan',
        'kode_akun',
        'nama_akun'
    ];

    protected $casts = [
        'jumlah_budget' => 'decimal:2',
        'periode' => 'string',
        'actual_amount' => 'float',
        'variance' => 'float',
        'variance_percent' => 'float',
    ];

    protected $appends = [
        'nama_akun',
        'kode_akun',
        'actual_amount', 
        'variance', 
        'variance_percent',
        'periode_formatted'
    ];

    public function coa(): BelongsTo
    {
        return $this->belongsTo(Coa::class);
    }

    public function actualPayments(): HasMany
    {
        $startDate = Carbon::createFromFormat('Y-m', $this->periode)->startOfMonth();
        $endDate = Carbon::createFromFormat('Y-m', $this->periode)->endOfMonth();

        return $this->hasMany(ExpensePayment::class, 'coa_beban_id', 'kode_akun')
            ->whereBetween('tanggal', [$startDate, $endDate]);
    }

    public function getNamaAkunAttribute()
    {
        return $this->coa ? $this->coa->nama_akun : null;
    }

    public function getKodeAkunAttribute()
    {
        return $this->coa ? $this->coa->kode : null;
    }

    public function getPeriodeFormattedAttribute()
    {
        return Carbon::createFromFormat('Y-m', $this->periode)->isoFormat('MMMM YYYY');
    }

    public function getActualAmountAttribute()
    {
        return (float) $this->actualPayments()->sum('nominal');
    }

    public function getVarianceAttribute()
    {
        return $this->jumlah_budget - $this->actual_amount;
    }

    public function getVariancePercentAttribute()
    {
        return $this->jumlah_budget != 0 
            ? ($this->variance / $this->jumlah_budget) * 100 
            : 0;
    }
}
