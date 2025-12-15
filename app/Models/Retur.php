<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\Schema;

use App\Models\Order;
use App\Models\Penjualan;
use App\Models\Pembelian;

class Retur extends Model
{
    use HasFactory;

    protected $fillable = [
        'kode_retur',
        'tanggal',
        'referensi_kode',
        'referensi_id',
        'tipe_kompensasi',
        'total_nilai_retur',
        'nilai_kompensasi',
        'status',
        'keterangan',
        'created_by',
        // Legacy columns (masih dipakai modul lama)
        'type',
        'tipe_retur',
        'ref_id',
        'kompensasi',
        'jumlah',
        'memo',
        'alasan',
        'pembelian_id',
    ];

    protected $casts = [
        'tanggal' => 'date',
        'total_nilai_retur' => 'decimal:2',
        'nilai_kompensasi' => 'decimal:2',
        'jumlah' => 'decimal:2',
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
        return $this->belongsTo(Penjualan::class, $this->penjualanForeignKey())
                    ->withoutGlobalScopes();
    }

    public function pembelian()
    {
        return $this->belongsTo(Pembelian::class, $this->pembelianForeignKey())
                    ->withoutGlobalScopes();
    }

    public function order()
    {
        if (!Schema::hasColumn($this->getTable(), 'ref_id')) {
            return $this->belongsTo(Order::class, 'referensi_id');
        }

        return $this->belongsTo(Order::class, 'ref_id');
    }

    public function getJenisReturAttribute(): ?string
    {
        return $this->tipe_retur ?? $this->type ?? null;
    }

    public function getTanggalReturAttribute()
    {
        return $this->tanggal ?? $this->created_at;
    }

    public function getNomorReturAttribute(): string
    {
        return $this->kode_retur
            ?? $this->referensi_kode
            ?? $this->memo
            ?? ('RT-' . str_pad($this->id ?? 0, 4, '0', STR_PAD_LEFT));
    }

    public function calculateTotalNilai(): float
    {
        $candidates = [
            $this->total_nilai_retur,
            $this->jumlah,
            $this->nilai_kompensasi,
        ];

        foreach ($candidates as $value) {
            if (!is_null($value) && (float) $value > 0) {
                return (float) $value;
            }
        }

        $detailTotal = $this->details->sum(function ($detail) {
            $qty = $detail->qty ?? $detail->qty_retur ?? $detail->jumlah ?? 0;
            $price = $detail->harga_satuan_asal ?? $detail->harga_satuan ?? $detail->harga ?? 0;
            $subtotal = $detail->subtotal ?? null;
            if (!is_null($subtotal)) {
                return (float) $subtotal;
            }
            return (float) $qty * (float) $price;
        });

        return (float) $detailTotal;
    }

    public function resolveCustomerName(): string
    {
        if (($this->jenis_retur ?? '') === 'sale') {
            $order = $this->order;
            if ($order) {
                return $order->user->name
                    ?? $order->nama_penerima
                    ?? '-';
            }

            $penjualan = $this->penjualan;
            if ($penjualan) {
                return $penjualan->pelanggan->nama ?? '-';
            }

            return '-';
        }

        $pembelian = $this->pembelian;
        if ($pembelian) {
            return $pembelian->vendor->nama_vendor ?? '-';
        }

        return '-';
    }

    public function resolveReferensiNomor(): string
    {
        if (($this->jenis_retur ?? '') === 'sale') {
            $order = $this->order;
            if ($order) {
                return $order->nomor_order ?? '-';
            }
            $penjualan = $this->penjualan;
            return $penjualan->nomor_penjualan
                ?? $penjualan->no_penjualan
                ?? '-';
        }

        $pembelian = $this->pembelian;
        if ($pembelian) {
            return $pembelian->kode_pembelian ?? $pembelian->no_faktur ?? '-';
        }

        return '-';
    }

    protected function penjualanForeignKey(): string
    {
        if (Schema::hasColumn($this->getTable(), 'referensi_id')) {
            return 'referensi_id';
        }

        if (Schema::hasColumn($this->getTable(), 'penjualan_id')) {
            return 'penjualan_id';
        }

        return 'ref_id';
    }

    protected function pembelianForeignKey(): string
    {
        if (Schema::hasColumn($this->getTable(), 'referensi_id')) {
            return 'referensi_id';
        }

        if (Schema::hasColumn($this->getTable(), 'pembelian_id')) {
            return 'pembelian_id';
        }

        return 'ref_id';
    }

    // Generate kode retur otomatis
    public static function generateKodeRetur()
    {
        $tanggal = now()->format('Ymd');
        $lastRetur = self::whereDate('tanggal', now()->toDateString())
                         ->orderBy('id', 'desc')
                         ->first();
        
        $nomor = $lastRetur ? (int)substr($lastRetur->kode_retur ?? 'RTR-' . $tanggal . '-000', -3) + 1 : 1;
        
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
