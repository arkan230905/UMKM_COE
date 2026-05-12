<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ReturKompensasi extends Model
{
    use HasFactory;

    protected $fillable = [
        'retur_id',
        'tipe_kompensasi',
        'item_type',
        'item_id',
        'item_nama',
        'qty',
        'satuan',
        'nilai_kompensasi',
        'metode_pembayaran',
        'akun_id',
        'tanggal_kompensasi',
        'status',
        'keterangan'
    ];

    protected $casts = [
        'qty' => 'decimal:2',
        'nilai_kompensasi' => 'decimal:2',
        'tanggal_kompensasi' => 'date'
    ];

    // Relasi ke retur
    public function retur()
    {
        return $this->belongsTo(Retur::class);
    }

    // Relasi ke akun COA
    public function akun()
    {
        return $this->belongsTo(Coa::class, 'akun_id');
    }

    // Accessor untuk mendapatkan item kompensasi
    public function getItemAttribute()
    {
        if ($this->tipe_kompensasi === 'barang') {
            if ($this->item_type === 'produk') {
                return Produk::find($this->item_id);
            } elseif ($this->item_type === 'bahan_baku') {
                return BahanBaku::find($this->item_id);
            }
        }
        return null;
    }

    // Scope untuk filter berdasarkan status
    public function scopeStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeSelesai($query)
    {
        return $query->where('status', 'selesai');
    }
}
