<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExpensePayment extends Model
{
    use HasFactory;

    protected $fillable = [
        'tanggal',
        'beban_operasional_id',
        'coa_beban_id', 
        'metode_bayar',
        'coa_kasbank',
        'nominal_pembayaran',
        'keterangan',
        'user_id'
    ];

    protected $casts = [
        'tanggal' => 'date',
        'nominal_pembayaran' => 'decimal:2',
    ];

    /**
     * Relasi ke Beban Operasional
     */
    public function bebanOperasional()
    {
        return $this->belongsTo(BebanOperasional::class);
    }

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

    /**
     * Accessor untuk format nominal pembayaran
     */
    public function getNominalPembayaranFormattedAttribute()
    {
        return 'Rp ' . number_format($this->nominal_pembayaran, 0, ',', '.');
    }

    /**
     * Accessor untuk nama beban operasional
     */
    public function getNamaBebanOperasionalAttribute()
    {
        return $this->bebanOperasional ? $this->bebanOperasional->nama_beban : '-';
    }

    /**
     * Accessor untuk kategori beban
     */
    public function getKategoriBebanAttribute()
    {
        return $this->bebanOperasional ? $this->bebanOperasional->kategori : '-';
    }
}
