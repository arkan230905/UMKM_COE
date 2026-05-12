<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KonversiProduksi extends Model
{
    use HasFactory;

    protected $fillable = [
        'bahan_baku_id',
        'produksi_id',
        'jumlah_bahan_asli',
        'satuan_asli',
        'jumlah_hasil_produksi',
        'satuan_hasil',
        'faktor_konversi',
        'harga_per_satuan_hasil',
        'tanggal_produksi',
        'is_active',
        'confidence_score',
    ];

    protected $casts = [
        'jumlah_bahan_asli' => 'float',
        'jumlah_hasil_produksi' => 'float',
        'faktor_konversi' => 'float',
        'harga_per_satuan_hasil' => 'float',
        'tanggal_produksi' => 'date',
        'is_active' => 'boolean',
        'confidence_score' => 'float',
    ];

    /**
     * Get the bahan baku that owns the konversi.
     */
    public function bahanBaku()
    {
        return $this->belongsTo(BahanBaku::class);
    }

    /**
     * Get the produksi that owns the konversi.
     */
    public function produksi()
    {
        return $this->belongsTo(Produksi::class);
    }

    /**
     * Scope untuk mendapatkan konversi yang aktif
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope untuk mendapatkan konversi terbaru
     */
    public function scopeTerbaru($query)
    {
        return $query->orderBy('tanggal_produksi', 'desc');
    }

    /**
     * Scope untuk mendapatkan konversi dengan confidence tinggi
     */
    public function scopeHighConfidence($query, $minScore = 0.8)
    {
        return $query->where('confidence_score', '>=', $minScore);
    }

    /**
     * Hitung faktor konversi otomatis
     * 1 satuan_asli = X satuan_hasil
     */
    public static function hitungFaktorKonversi($jumlahBahan, $jumlahHasil)
    {
        if ($jumlahBahan <= 0) {
            return 0;
        }
        
        return $jumlahHasil / $jumlahBahan;
    }

    /**
     * Hitung harga per satuan hasil berdasarkan konversi
     */
    public function hitungHargaPerSatuanHasil($hargaBahanPerSatuanAsli)
    {
        if ($this->faktor_konversi <= 0) {
            return 0;
        }
        
        // Jika 1 kg menghasilkan 8 potong dengan harga Rp40.000/kg
        // Maka harga per potong = Rp40.000 / 8 = Rp5.000/potong
        return $hargaBahanPerSatuanAsli / $this->faktor_konversi;
    }

    /**
     * Update confidence score berdasarkan konsistensi data
     */
    public function updateConfidenceScore()
    {
        // Ambil semua konversi untuk bahan baku yang sama
        $semuaKonversi = self::where('bahan_baku_id', $this->bahan_baku_id)
            ->where('satuan_asli', $this->satuan_asli)
            ->where('satuan_hasil', $this->satuan_hasil)
            ->where('id', '!=', $this->id)
            ->active()
            ->get();

        if ($semuaKonversi->isEmpty()) {
            $this->confidence_score = 1.0; // Konversi pertama, confidence tinggi
            return $this->confidence_score;
        }

        // Hitung deviasi dari konversi lain
        $deviations = [];
        foreach ($semuaKonversi as $konversi) {
            $deviasi = abs($this->faktor_konversi - $konversi->faktor_konversi);
            $deviations[] = $deviasi;
        }

        $rataRataDeviasi = array_sum($deviations) / count($deviations);
        
        // Confidence score berdasarkan konsistensi
        // Semakin konsisten, semakin tinggi confidence
        $this->confidence_score = max(0.1, 1.0 - ($rataRataDeviasi / $this->faktor_konversi));
        
        return $this->confidence_score;
    }

    /**
     * Get konversi aktif untuk bahan baku tertentu
     */
    public static function getKonversiAktif($bahanBakuId, $satuanAsli = null, $satuanHasil = null)
    {
        $query = self::where('bahan_baku_id', $bahanBakuId)
            ->active()
            ->highConfidence()
            ->terbaru();

        if ($satuanAsli) {
            $query->where('satuan_asli', $satuanAsli);
        }

        if ($satuanHasil) {
            $query->where('satuan_hasil', $satuanHasil);
        }

        return $query->first();
    }

    /**
     * Convert quantity using production-based conversion
     */
    public function konversi($jumlah, $dariSatuan, $keSatuan)
    {
        // Jika konversi dari satuan asli ke satuan hasil
        if ($dariSatuan === $this->satuan_asli && $keSatuan === $this->satuan_hasil) {
            return $jumlah * $this->faktor_konversi;
        }

        // Jika konversi dari satuan hasil ke satuan asli
        if ($dariSatuan === $this->satuan_hasil && $keSatuan === $this->satuan_asli) {
            if ($this->faktor_konversi <= 0) {
                return 0;
            }
            return $jumlah / $this->faktor_konversi;
        }

        // Jika satuan tidak cocok, return 0
        return 0;
    }

    /**
     * Format faktor konversi untuk display
     */
    public function getFaktorKonversiFormatAttribute()
    {
        return "1 {$this->satuan_asli} = " . number_format($this->faktor_konversi, 4) . " {$this->satuan_hasil}";
    }

    /**
     * Get harga per satuan hasil format
     */
    public function getHargaPerSatuanHasilFormatAttribute()
    {
        return "Rp " . number_format($this->harga_per_satuan_hasil, 2) . "/{$this->satuan_hasil}";
    }
}
