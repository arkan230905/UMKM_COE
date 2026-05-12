<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DepreciationSchedule extends Model
{
    protected $table = 'depreciation_schedules';

    protected $fillable = [
        'aset_id',
        'periode_mulai',
        'periode_akhir',
        'periode_bulan',
        'nilai_awal',
        'beban_penyusutan',
        'akumulasi_penyusutan',
        'nilai_buku',
        'status',
        'jurnal_id',
        'posted_by',
        'posted_at',
        'reversed_by',
        'reversed_at',
        'keterangan'
    ];

    protected $casts = [
        'periode_mulai' => 'date',
        'periode_akhir' => 'date',
        'nilai_awal' => 'decimal:2',
        'beban_penyusutan' => 'decimal:2',
        'akumulasi_penyusutan' => 'decimal:2',
        'nilai_buku' => 'decimal:2',
        'posted_at' => 'datetime',
        'reversed_at' => 'datetime',
    ];

    /**
     * Relationship ke Aset
     */
    public function aset(): BelongsTo
    {
        return $this->belongsTo(Aset::class);
    }

    /**
     * Relationship ke Jurnal
     */
    public function jurnal(): BelongsTo
    {
        return $this->belongsTo(Jurnal::class);
    }

    /**
     * Relationship ke User (posted_by)
     */
    public function postedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'posted_by');
    }

    /**
     * Relationship ke User (reversed_by)
     */
    public function reversedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reversed_by');
    }
}
