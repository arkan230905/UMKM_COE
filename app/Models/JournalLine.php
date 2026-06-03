<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JournalLine extends Model
{
    use HasFactory;

    protected $table = 'journal_lines';

    protected $fillable = [
        'journal_entry_id',
        'coa_id',
        'debit',
        'credit',
        'memo',
    ];

    protected $casts = [
        'debit' => 'decimal:2',
        'credit' => 'decimal:2',
    ];

    /**
     * Relasi ke JournalEntry
     */
    public function journalEntry()
    {
        return $this->belongsTo(JournalEntry::class, 'journal_entry_id');
    }

    /**
     * Alias untuk journalEntry() - untuk backward compatibility
     */
    public function entry()
    {
        return $this->journalEntry();
    }

    /**
     * Relasi ke COA
     */
    public function coa()
    {
        return $this->belongsTo(Coa::class, 'coa_id');
    }

    /**
     * Scope untuk filter berdasarkan COA
     */
    public function scopeByCoa($query, $coaId)
    {
        return $query->where('coa_id', $coaId);
    }

    /**
     * Scope untuk filter berdasarkan journal entry
     */
    public function scopeByJournalEntry($query, $journalEntryId)
    {
        return $query->where('journal_entry_id', $journalEntryId);
    }

    /**
     * Scope untuk debit saja
     */
    public function scopeDebitOnly($query)
    {
        return $query->where('debit', '>', 0);
    }

    /**
     * Scope untuk kredit saja
     */
    public function scopeCreditOnly($query)
    {
        return $query->where('credit', '>', 0);
    }
}
