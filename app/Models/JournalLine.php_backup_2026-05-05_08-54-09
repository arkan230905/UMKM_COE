<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JournalLine extends Model
{
    use HasFactory;

    protected $fillable = ['journal_entry_id','coa_id','debit','credit','memo'];

    protected $appends = ['kode_akun'];

    public function entry()
    {
        return $this->belongsTo(JournalEntry::class, 'journal_entry_id');
    }

    public function coa()
    {
        return $this->belongsTo(Coa::class, 'coa_id', 'id');
    }
    
    /**
     * Get kode akun from related COA
     */
    public function getKodeAkunAttribute()
    {
        return $this->coa ? $this->coa->kode_akun : null;
    }
}
