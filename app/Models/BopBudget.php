<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Carbon\Carbon;

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

    public function actualPayments()
    {
        return $this->hasMany(ExpensePayment::class, 'coa_id', 'coa_id')
            ->whereYear('tanggal_pembayaran', '=', substr($this->periode, 0, 4))
            ->whereMonth('tanggal_pembayaran', '=', substr($this->periode, 5, 2));
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
        // Hitung dari ExpensePayment
        $amount = $this->actualPayments()->sum('jumlah');
        
        // Jika tidak ada data di ExpensePayment, coba hitung dari Expense
        if ($amount == 0 && $this->coa_id) {
            $startDate = Carbon::createFromFormat('Y-m', $this->periode)->startOfMonth();
            $endDate = Carbon::createFromFormat('Y-m', $this->periode)->endOfMonth();
            
            $amount = \App\Models\Expense::where('coa_id', $this->coa_id)
                ->whereBetween('tanggal', [$startDate, $endDate])
                ->sum('jumlah');
        }
        
        return $amount;
    }

    public function getVarianceAttribute()
    {
        return $this->actual_amount - $this->jumlah_budget;
    }

    public function getVariancePercentAttribute()
    {
        return $this->jumlah_budget != 0 
            ? ($this->variance / $this->jumlah_budget) * 100 
            : 0;
    }
}
