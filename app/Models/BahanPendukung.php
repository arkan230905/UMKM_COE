<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BahanPendukung extends Model
{
    use HasFactory;

    protected $table = 'bahan_pendukungs';

    protected $fillable = [
        'kode_bahan',
        'nama_bahan',
        'deskripsi',
        'satuan_id',
        'harga_satuan',
        'stok',
        'stok_minimum',
        'kategori_id',
        'is_active',
        'sub_satuan_1_id',
        'sub_satuan_1_konversi',
        'sub_satuan_1_nilai',
        'sub_satuan_2_id',
        'sub_satuan_2_konversi',
        'sub_satuan_2_nilai',
        'sub_satuan_3_id',
        'sub_satuan_3_konversi',
        'sub_satuan_3_nilai',
        'coa_pembelian_id',    // COA untuk pembelian
        'coa_persediaan_id',    // COA untuk persediaan
        'coa_hpp_id'           // COA untuk HPP
    ];

    protected $casts = [
        'harga_satuan' => 'decimal:2',
        'stok' => 'decimal:4',
        'stok_minimum' => 'decimal:4',
        'is_active' => 'boolean'
    ];

    protected $appends = ['stok_aman', 'status_stok'];

    /**
     * Boot method untuk auto-generate kode
     */
    protected static function booted()
    {
        static::creating(function ($model) {
            if (empty($model->kode_bahan)) {
                $model->kode_bahan = self::generateKode();
            }
        });
    }

    /**
     * Generate kode bahan otomatis
     */
    public static function generateKode(): string
    {
        $lastBahan = self::orderBy('id', 'desc')->first();
        $nextNumber = $lastBahan ? ((int) substr($lastBahan->kode_bahan, 4)) + 1 : 1;
        return 'BPD-' . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Relasi ke Satuan
     */
    public function satuan()
    {
        return $this->belongsTo(Satuan::class, 'satuan_id')
            ->withDefault([
                'nama' => 'Tidak Diketahui',
                'kode_satuan' => 'N/A'
            ]);
    }

    /**
     * Relasi ke Satuan (alias untuk backward compatibility)
     */
    public function satuanRelation()
    {
        return $this->belongsTo(Satuan::class, 'satuan_id');
    }

    /**
     * Relasi ke Kategori
     */
    public function kategoriBahanPendukung()
    {
        return $this->belongsTo(KategoriBahanPendukung::class, 'kategori_id');
    }

    /**
     * Get the sub satuan 1
     */
    public function subSatuan1()
    {
        return $this->belongsTo(Satuan::class, 'sub_satuan_1_id');
    }

    /**
     * Get the sub satuan 2
     */
    public function subSatuan2()
    {
        return $this->belongsTo(Satuan::class, 'sub_satuan_2_id');
    }

    /**
     * Get the sub satuan 3
     */
    public function subSatuan3()
    {
        return $this->belongsTo(Satuan::class, 'sub_satuan_3_id');
    }

    /**
     * Get the COA for pembelian
     */
    public function coaPembelian()
    {
        return $this->belongsTo(Coa::class, 'coa_pembelian_id');
    }

    /**
     * Get the COA for persediaan
     */
    public function coaPersediaan()
    {
        return $this->belongsTo(Coa::class, 'coa_persediaan_id');
    }

    /**
     * Get the COA for HPP
     */
    public function coaHpp()
    {
        return $this->belongsTo(Coa::class, 'coa_hpp_id');
    }

    /**
     * Check apakah stok aman
     */
    public function getStokAmanAttribute(): bool
    {
        return $this->stok >= $this->stok_minimum;
    }

    /**
     * Get status stok
     */
    public function getStatusStokAttribute(): string
    {
        if ($this->stok <= 0) {
            return 'Habis';
        } elseif ($this->stok < $this->stok_minimum) {
            return 'Menipis';
        }
        return 'Aman';
    }

    /**
     * Scope untuk bahan aktif
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope untuk filter kategori by ID
     */
    public function scopeKategori($query, $kategoriId)
    {
        return $query->where('kategori_id', $kategoriId);
    }

    /**
     * Convert quantity based on production requirements (similar to BahanBaku)
     */
    public function konversiBerdasarkanProduksi($jumlah, $dariSatuan, $keSatuan)
    {
        // PRIORITAS 1: Gunakan konversi dari master data bahan pendukung (sub_satuan)
        $masterConversion = $this->convertToSubUnit($jumlah, $dariSatuan, $keSatuan);
        if ($masterConversion !== $jumlah) {
            // Konversi berhasil menggunakan master data
            return $masterConversion;
        }
        
        // PRIORITAS 2: Fallback ke UnitConverter untuk konversi standar
        $converter = new \App\Support\UnitConverter();
        $convertedQty = $converter->convert($jumlah, $dariSatuan, $keSatuan);
        
        // Jika UnitConverter berhasil konversi (hasil berbeda dari input), gunakan hasilnya
        if (abs($convertedQty - $jumlah) > 0.001) {
            return $convertedQty;
        }
        
        // PRIORITAS 3: Return original jika tidak ada konversi yang cocok
        return $jumlah;
    }

    /**
     * Convert to sub unit using master data conversion factors
     */
    public function convertToSubUnit($jumlah, $dariSatuan, $keSatuan)
    {
        // Jika satuan sama, tidak perlu konversi
        if (strtolower($dariSatuan) === strtolower($keSatuan)) {
            return $jumlah;
        }
        
        $dariSatuanLower = strtolower($dariSatuan);
        $keSatuanLower = strtolower($keSatuan);
        $satuanUtama = strtolower($this->satuan->nama ?? '');
        
        // Konversi dari satuan utama ke sub satuan
        if ($dariSatuanLower === $satuanUtama || str_contains($satuanUtama, $dariSatuanLower)) {
            // Cek sub_satuan_1
            if ($this->sub_satuan_1_id && $this->sub_satuan_1_konversi > 0) {
                $subSatuan1 = \App\Models\Satuan::find($this->sub_satuan_1_id);
                if ($subSatuan1 && str_contains(strtolower($subSatuan1->nama), $keSatuanLower)) {
                    return $jumlah * $this->sub_satuan_1_konversi;
                }
            }
            
            // Cek sub_satuan_2
            if ($this->sub_satuan_2_id && $this->sub_satuan_2_konversi > 0) {
                $subSatuan2 = \App\Models\Satuan::find($this->sub_satuan_2_id);
                if ($subSatuan2 && str_contains(strtolower($subSatuan2->nama), $keSatuanLower)) {
                    return $jumlah * $this->sub_satuan_2_konversi;
                }
            }
            
            // Cek sub_satuan_3
            if ($this->sub_satuan_3_id && $this->sub_satuan_3_konversi > 0) {
                $subSatuan3 = \App\Models\Satuan::find($this->sub_satuan_3_id);
                if ($subSatuan3 && str_contains(strtolower($subSatuan3->nama), $keSatuanLower)) {
                    return $jumlah * $this->sub_satuan_3_konversi;
                }
            }
        }
        
        // Konversi dari sub satuan ke satuan utama (kebalikan)
        // Cek sub_satuan_1
        if ($this->sub_satuan_1_id && $this->sub_satuan_1_konversi > 0) {
            $subSatuan1 = \App\Models\Satuan::find($this->sub_satuan_1_id);
            if ($subSatuan1 && str_contains(strtolower($subSatuan1->nama), $dariSatuanLower) && 
                ($keSatuanLower === $satuanUtama || str_contains($satuanUtama, $keSatuanLower))) {
                return $jumlah / $this->sub_satuan_1_konversi;
            }
        }
        
        // Cek sub_satuan_2
        if ($this->sub_satuan_2_id && $this->sub_satuan_2_konversi > 0) {
            $subSatuan2 = \App\Models\Satuan::find($this->sub_satuan_2_id);
            if ($subSatuan2 && str_contains(strtolower($subSatuan2->nama), $dariSatuanLower) && 
                ($keSatuanLower === $satuanUtama || str_contains($satuanUtama, $keSatuanLower))) {
                return $jumlah / $this->sub_satuan_2_konversi;
            }
        }
        
        // Cek sub_satuan_3
        if ($this->sub_satuan_3_id && $this->sub_satuan_3_konversi > 0) {
            $subSatuan3 = \App\Models\Satuan::find($this->sub_satuan_3_id);
            if ($subSatuan3 && str_contains(strtolower($subSatuan3->nama), $dariSatuanLower) && 
                ($keSatuanLower === $satuanUtama || str_contains($satuanUtama, $keSatuanLower))) {
                return $jumlah / $this->sub_satuan_3_konversi;
            }
        }
        
        // Tidak ada konversi yang cocok, return jumlah asli
        return $jumlah;
    }
    
    /**
     * Get stok dalam berbagai satuan berdasarkan konversi manual dari pembelian
     */
    public function getStokDalamBerbagaiSatuan()
    {
        $stokSatuan = [];
        
        // Stok dalam satuan utama
        $stokSatuan[$this->satuanRelation->nama ?? 'unit'] = $this->stok;
        
        // Ambil konversi manual dari pembelian terbaru
        $konversiManual = \App\Models\PembelianDetailKonversi::whereHas('pembelianDetail', function($query) {
                $query->where('bahan_pendukung_id', $this->id);
            })
            ->with('satuan')
            ->get()
            ->groupBy('satuan_id');
            
        foreach ($konversiManual as $satuanId => $konversiList) {
            $satuan = $konversiList->first()->satuan;
            if ($satuan) {
                // Hitung rata-rata konversi dari semua pembelian
                $totalKonversi = $konversiList->sum('jumlah_konversi');
                $totalSatuanUtama = $konversiList->sum(function($konversi) {
                    return $konversi->pembelianDetail->jumlah_satuan_utama ?? 0;
                });
                
                if ($totalSatuanUtama > 0) {
                    $rataRataKonversi = $totalKonversi / $totalSatuanUtama;
                    $stokSatuan[$satuan->nama] = $this->stok * $rataRataKonversi;
                }
            }
        }
        
        // Fallback ke konversi otomatis jika tidak ada konversi manual
        if (count($stokSatuan) === 1) {
            if ($this->sub_satuan_1_konversi && $this->subSatuan1) {
                $stokSatuan[$this->subSatuan1->nama] = $this->stok * $this->sub_satuan_1_konversi;
            }
            if ($this->sub_satuan_2_konversi && $this->subSatuan2) {
                $stokSatuan[$this->subSatuan2->nama] = $this->stok * $this->sub_satuan_2_konversi;
            }
            if ($this->sub_satuan_3_konversi && $this->subSatuan3) {
                $stokSatuan[$this->subSatuan3->nama] = $this->stok * $this->sub_satuan_3_konversi;
            }
        }
        
        return $stokSatuan;
    }
}
