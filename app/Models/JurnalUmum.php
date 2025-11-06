<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class JurnalUmum extends Model
{
    protected $table = 'jurnal_umum';
    
    protected $fillable = [
        'coa_id',
        'tanggal',
        'keterangan',
        'debit',
        'kredit',
        'referensi',
        'tipe_referensi',
        'created_by',
    ];

    protected $casts = [
        'tanggal' => 'date',
        'debit' => 'decimal:2',
        'kredit' => 'decimal:2',
    ];

    public function coa(): BelongsTo
    {
        return $this->belongsTo(Coa::class, 'coa_id', 'id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function scopeDebit($query)
    {
        return $query->where('debit', '>', 0);
    }

    public function scopeKredit($query)
    {
        return $query->where('kredit', '>', 0);
    }

    public function scopeBetweenDates($query, $startDate, $endDate)
    {
        return $query->whereBetween('tanggal', [$startDate, $endDate]);
    }
}
