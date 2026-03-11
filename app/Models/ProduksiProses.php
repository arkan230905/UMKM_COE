<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProduksiProses extends Model
{
    use HasFactory;

    protected $table = 'produksi_proses';

    protected $fillable = [
        'produksi_id',
        'nama_proses',
        'urutan',
        'status',
        'biaya_btkl',
        'biaya_bop',
        'total_biaya_proses',
        'waktu_mulai',
        'waktu_selesai',
        'durasi_menit',
        'pegawai_ids',
        'catatan'
    ];

    protected $casts = [
        'biaya_btkl' => 'decimal:2',
        'biaya_bop' => 'decimal:2',
        'total_biaya_proses' => 'decimal:2',
        'waktu_mulai' => 'datetime',
        'waktu_selesai' => 'datetime',
        'pegawai_ids' => 'array',
    ];

    // Relationships
    public function produksi()
    {
        return $this->belongsTo(Produksi::class);
    }

    // Helper methods
    public function isPending()
    {
        return $this->status === 'pending';
    }

    public function isSedangDikerjakan()
    {
        return $this->status === 'sedang_dikerjakan';
    }

    public function isSelesai()
    {
        return $this->status === 'selesai';
    }

    public function mulaiProses()
    {
        $this->update([
            'status' => 'sedang_dikerjakan',
            'waktu_mulai' => now()
        ]);
    }

    public function selesaikanProses()
    {
        $waktuMulai = $this->waktu_mulai;
        $waktuSelesai = now();
        $durasi = $waktuMulai ? $waktuMulai->diffInMinutes($waktuSelesai) : 0;

        $this->update([
            'status' => 'selesai',
            'waktu_selesai' => $waktuSelesai,
            'durasi_menit' => $durasi
        ]);
    }

    public function getStatusBadgeAttribute()
    {
        return match($this->status) {
            'pending' => '<span class="badge bg-secondary">Menunggu</span>',
            'sedang_dikerjakan' => '<span class="badge bg-primary">Sedang Dikerjakan</span>',
            'selesai' => '<span class="badge bg-success">Selesai</span>',
            default => '<span class="badge bg-secondary">Unknown</span>',
        };
    }
}
