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

        // Auto-create journal entries when penggajian is marked as paid
        static::updated(function ($penggajian) {
            // Check if status changed to 'lunas' and no journal entries exist yet
            if ($penggajian->status_pembayaran === 'lunas' && 
                $penggajian->getOriginal('status_pembayaran') !== 'lunas') {
                
                // Check if journal entries already exist
                $existingJournal = \App\Models\JurnalUmum::where('tipe_referensi', 'penggajian')
                    ->where('referensi', $penggajian->id)
                    ->exists();
                    
                if (!$existingJournal) {
                    static::createJournalEntries($penggajian);
                }
            }
        });
    }

    /**
     * Create journal entries for penggajian
     */
    public static function createJournalEntries($penggajian)
    {
        try {
            $pegawai = $penggajian->pegawai;
            if (!$pegawai) {
                throw new \Exception('Pegawai not found for penggajian ID ' . $penggajian->id);
            }

            // Get required COA accounts
            $coaBebanGaji = \App\Models\Coa::where('kode_akun', '52')->first(); // BTKL
            if (!$coaBebanGaji) {
                $coaBebanGaji = \App\Models\Coa::where('kode_akun', '54')->first(); // BOP TENAGA KERJA TIDAK LANGSUNG
            }
            
            $coaKasBank = \App\Models\Coa::where('kode_akun', $penggajian->coa_kasbank)->first();
            
            if (!$coaBebanGaji) {
                throw new \Exception('COA Beban Gaji not found');
            }
            
            if (!$coaKasBank) {
                throw new \Exception('COA Kas/Bank not found for code: ' . $penggajian->coa_kasbank);
            }
            
            // Create journal entries
            $keterangan = "Penggajian {$pegawai->nama}";
            
            // DEBIT: Beban Gaji
            \App\Models\JurnalUmum::create([
                'coa_id' => $coaBebanGaji->id,
                'tanggal' => $penggajian->tanggal_penggajian,
                'keterangan' => $keterangan,
                'debit' => $penggajian->total_gaji,
                'kredit' => 0,
                'referensi' => $penggajian->id,
                'tipe_referensi' => 'penggajian',
                'created_by' => auth()->id() ?? 1,
            ]);
            
            // CREDIT: Kas/Bank
            \App\Models\JurnalUmum::create([
                'coa_id' => $coaKasBank->id,
                'tanggal' => $penggajian->tanggal_penggajian,
                'keterangan' => $keterangan,
                'debit' => 0,
                'kredit' => $penggajian->total_gaji,
                'referensi' => $penggajian->id,
                'tipe_referensi' => 'penggajian',
                'created_by' => auth()->id() ?? 1,
            ]);
            
            \Log::info('Journal entries created for penggajian', [
                'penggajian_id' => $penggajian->id,
                'pegawai' => $pegawai->nama,
                'total_gaji' => $penggajian->total_gaji
            ]);
            
        } catch (\Exception $e) {
            \Log::error('Failed to create journal entries for penggajian', [
                'penggajian_id' => $penggajian->id,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
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
        return $this->jurnalEntries()->exists();
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
