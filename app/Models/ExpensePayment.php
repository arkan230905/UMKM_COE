<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExpensePayment extends Model
{
    use HasFactory;

    protected $fillable = [
        'tanggal','coa_beban_id','metode_bayar','coa_kasbank','nominal','deskripsi','user_id'
    ];

    protected $casts = [
        'tanggal' => 'date',
        'nominal' => 'decimal:2',
    ];

    /**
     * Relasi ke COA Beban
     * coa_beban_id menyimpan kode_akun (string seperti '5111')
     */
    public function coaBeban()
    {
        return $this->belongsTo(Coa::class, 'coa_beban_id', 'kode_akun')
            ->withDefault([
                'kode_akun' => '?',
                'nama_akun' => 'Akun tidak ditemukan'
            ]);
    }
    
    /**
     * Relasi ke COA Kas/Bank
     * coa_kasbank menyimpan kode_akun (string seperti '1101')
     */
    public function coaKasBank()
    {
        return $this->belongsTo(Coa::class, 'coa_kasbank', 'kode_akun')
            ->withDefault([
                'kode_akun' => '?',
                'nama_akun' => 'Akun tidak ditemukan'
            ]);
    }
    
    // Alias untuk kompatibilitas
    public function coa()
    {
        return $this->coaBeban();
    }
}
