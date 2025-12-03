<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Retur extends Model
{
    use HasFactory;

    protected $fillable = [
        'kode_retur',
        'tanggal',
        'tipe_retur',
        'referensi_id',
        'referensi_kode',
        'tipe_kompensasi',
        'total_nilai_retur',
        'nilai_kompensasi',
        'status',
        'keterangan',
        'created_by'
    ];

    protected $casts = [
        'tanggal' => 'date',
        'total_nilai_retur' => 'decimal:2',
        'nilai_kompensasi' => 'decimal:2'
    ];

    // Relasi ke detail retur
    public function details()
    {
        return $this->hasMany(ReturDetail::class, 'retur_id');
    }

    // Relasi ke kompensasi
    public function kompensasis()
    {
        return $this->hasMany(ReturKompensasi::class, 'retur_id');
    }

    // Relasi ke jurnal entries
    public function jurnalEntries()
    {
        return $this->belongsToMany(JournalEntry::class, 'retur_jurnal_entries', 'retur_id', 'jurnal_entry_id')
                    ->withPivot('tipe_jurnal')
                    ->withTimestamps();
    }

    // Relasi ke user yang membuat
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // Relasi ke penjualan (jika retur penjualan)
    public function penjualan()
    {
        return $this->belongsTo(Penjualan::class, 'referensi_id')
                    ->where('tipe_retur', 'penjualan');
    }

    // Relasi ke pembelian (jika retur pembelian)
    public function pembelian()
    {
        return $this->belongsTo(Pembelian::class, 'referensi_id')
                    ->where('tipe_retur', 'pembelian');
    }

    // Generate kode retur otomatis
    public static function generateKodeRetur()
    {
        $tanggal = now()->format('Ymd');
        $lastRetur = self::whereDate('created_at', now()->toDateString())
                         ->orderBy('id', 'desc')
                         ->first();
        
        $nomor = $lastRetur ? (int)substr($lastRetur->kode_retur, -3) + 1 : 1;
        
        return 'RTR-' . $tanggal . '-' . str_pad($nomor, 3, '0', STR_PAD_LEFT);
    }

    // Scope untuk filter berdasarkan tipe
    public function scopeTipePenjualan($query)
    {
        return $query->where('tipe_retur', 'penjualan');
    }

    public function scopeTipePembelian($query)
    {
        return $query->where('tipe_retur', 'pembelian');
    }

    // Scope untuk filter berdasarkan status
    public function scopeStatus($query, $status)
    {
        return $query->where('status', $status);
    }
}
