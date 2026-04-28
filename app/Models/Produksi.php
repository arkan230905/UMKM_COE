<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Produksi extends Model
{
    use HasFactory;

    protected $fillable = [
        'produk_id',
        'coa_persediaan_barang_jadi_id',
        'tanggal',
        'qty_produksi',
        'jumlah_produksi_bulanan',
        'hari_produksi_bulanan',
        'total_bahan',
        'total_btkl',
        'total_bop',
        'total_biaya',
        'catatan',
        'status',
        'proses_saat_ini',
        'proses_selesai',
        'total_proses',
        'waktu_mulai_produksi',
        'waktu_selesai_produksi'
    ];

    protected $casts = [
        'tanggal' => 'date',
        'qty_produksi' => 'decimal:4',
        'jumlah_produksi_bulanan' => 'decimal:4',
        'hari_produksi_bulanan' => 'integer',
        'total_bahan' => 'decimal:2',
        'total_btkl' => 'decimal:2',
        'total_bop' => 'decimal:2',
        'total_biaya' => 'decimal:2',
        'waktu_mulai_produksi' => 'datetime',
        'waktu_selesai_produksi' => 'datetime',
    ];

    public function produk() 
    { 
        return $this->belongsTo(Produk::class); 
    }
    
    public function details() 
    { 
        return $this->hasMany(ProduksiDetail::class); 
    }
    
    public function btklDetails()
    {
        return $this->hasMany(ProduksiBtklDetail::class);
    }

    public function bopDetails()
    {
        return $this->hasMany(ProduksiBopDetail::class);
    }

    public function proses() 
    { 
        return $this->hasMany(ProduksiProses::class)->orderBy('urutan'); 
    }
    
    /**
     * Get COA persediaan barang jadi for this production
     */
    public function coaPersediaanBarangJadi()
    {
        return $this->belongsTo(Coa::class, 'coa_persediaan_barang_jadi_id');
    }

    // Helper methods for status
    public function isDraft()
    {
        return $this->status === 'draft';
    }
    
    public function isSiapProduksi()
    {
        return $this->status === 'draft'; // Draft means ready for production in process costing
    }

    public function isDalamProses()
    {
        return $this->status === 'dalam_proses';
    }

    public function isSelesai()
    {
        return $this->status === 'selesai';
    }

    public function getStatusBadgeAttribute()
    {
        return match($this->status) {
            'draft' => '<span class="badge bg-info">Siap Produksi</span>',
            'dalam_proses' => '<span class="badge bg-primary">Dalam Proses</span>',
            'selesai' => '<span class="badge bg-success">Selesai</span>',
            default => '<span class="badge bg-secondary">Unknown</span>',
        };
    }

    public function getProgressPercentageAttribute()
    {
        if ($this->total_proses == 0) return 0;
        
        // Hitung ulang proses selesai berdasarkan data aktual
        $actualProsesSelesai = $this->proses()->where('status', 'selesai')->count();
        
        return round(($actualProsesSelesai / $this->total_proses) * 100);
    }
    
    /**
     * Get actual count of completed processes
     */
    public function getActualProsesSelesaiAttribute()
    {
        return $this->proses()->where('status', 'selesai')->count();
    }
}
