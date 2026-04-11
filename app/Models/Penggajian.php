<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\JurnalUmum;

class Penggajian extends Model
{
    use HasFactory;

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($penggajian) {
            if (empty($penggajian->status_pembayaran)) {
                $penggajian->status_pembayaran = 'belum_lunas';
            }
        });
    }

    protected $fillable = [
        'pegawai_id',
        'tanggal_penggajian',
        'coa_kasbank',
        'gaji_pokok',
        'tarif_per_jam',
        'tunjangan',
        'tunjangan_jabatan',
        'tunjangan_transport',
        'tunjangan_konsumsi',
        'total_tunjangan',
        'asuransi',
        'bonus',
        'potongan',
        'total_jam_kerja',
        'total_gaji',
        'status_pembayaran',
        'tanggal_dibayar',
        'metode_pembayaran',
        'status_posting',
        'tanggal_posting',
    ];

    protected $casts = [
        'tanggal_penggajian' => 'date',
        'gaji_pokok' => 'decimal:2',
        'tarif_per_jam' => 'decimal:2',
        'tunjangan' => 'decimal:2',
        'tunjangan_jabatan' => 'decimal:2',
        'tunjangan_transport' => 'decimal:2',
        'tunjangan_konsumsi' => 'decimal:2',
        'total_tunjangan' => 'decimal:2',
        'asuransi' => 'decimal:2',
        'bonus' => 'decimal:2',
        'potongan' => 'decimal:2',
        'total_jam_kerja' => 'decimal:2',
        'total_gaji' => 'decimal:2',
        'coa_kasbank' => 'string',
        'tanggal_dibayar' => 'date',
        'tanggal_posting' => 'date',
    ];

    public function pegawai()
    {
        return $this->belongsTo(Pegawai::class);
    }

    /**
     * Relasi ke jurnal umum (jurnal entries terkait penggajian ini)
     */
    public function jurnalEntries()
    {
        return $this->hasMany(JurnalUmum::class, 'referensi')
            ->where('tipe_referensi', 'penggajian')
            ->where('referensi', $this->id);
    }

    /**
     * Cek apakah penggajian sudah diposting ke jurnal
     */
    public function isPosted()
    {
        return $this->status_posting === 'posted';
    }

    /**
     * Cek apakah penggajian sudah dibayar
     */
    public function isPaid()
    {
        return $this->status_pembayaran === 'lunas';
    }

    /**
     * Generate no bukti jurnal otomatis
     */
    public function generateNoBukti()
    {
        $prefix = config('penggajian_journal.prefix_no_bukti', 'PGJ');
        $date = now()->format('Ymd');
        
        // Cari nomor urut terakhir hari ini
        $lastJournal = JurnalUmum::where('tipe_referensi', 'penggajian')
            ->where('keterangan', 'like', $prefix . '-' . $date . '%')
            ->orderBy('id', 'desc')
            ->first();

        if ($lastJournal) {
            // Extract nomor urut dari no_bukti terakhir
            $lastNoBukti = $lastJournal->keterangan;
            $lastNumber = (int) substr($lastNoBukti, -4);
            $nextNumber = $lastNumber + 1;
        } else {
            $nextNumber = 1;
        }

        return $prefix . '-' . $date . '-' . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);
    }
}
