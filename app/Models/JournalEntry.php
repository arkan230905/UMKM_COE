<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JournalEntry extends Model
{
    use \App\Traits\HasUserScope;
    use HasFactory;

    protected $table = 'journal_entries';

    protected $fillable = [
        'user_id',
        'tanggal',
        'ref_type',
        'ref_id',
        'memo',
    ];

    protected $casts = [
        'tanggal' => 'date',
    ];

    /**
     * Relasi ke JournalLine (detail jurnal)
     */
    public function journalLines()
    {
        return $this->hasMany(JournalLine::class, 'journal_entry_id');
    }

    /**
     * Relasi polymorphic ke referensi (Pembelian, Penjualan, dll)
     */
    public function reference()
    {
        return $this->morphTo('ref');
    }

    /**
     * Get total debit
     */
    public function getTotalDebitAttribute()
    {
        return $this->journalLines()->sum('debit');
    }

    /**
     * Get total kredit
     */
    public function getTotalKreditAttribute()
    {
        return $this->journalLines()->sum('credit');
    }

    /**
     * Check if journal is balanced
     */
    public function isBalanced()
    {
        return $this->total_debit == $this->total_kredit;
    }

    /**
     * Scope untuk filter berdasarkan tanggal
     */
    public function scopeByDate($query, $date)
    {
        return $query->whereDate('tanggal', $date);
    }

    /**
     * Scope untuk filter berdasarkan periode
     */
    public function scopeByPeriod($query, $startDate, $endDate)
    {
        return $query->whereBetween('tanggal', [$startDate, $endDate]);
    }

    /**
     * Scope untuk filter berdasarkan ref_type
     */
    public function scopeByRefType($query, $refType)
    {
        return $query->where('ref_type', $refType);
    }
}
