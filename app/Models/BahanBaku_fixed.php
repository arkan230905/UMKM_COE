<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BahanBaku extends Model
{
    use HasFactory;

    protected $table = 'bahan_bakus';
    protected $guarded = [];
    
    protected $fillable = [
        'nama_bahan',
        'kode_bahan',
        'satuan',
        'satuan_dasar',
        'faktor_konversi',
        'harga_satuan',
        'harga_per_satuan_dasar',
        'harga_rata_rata',
        'stok',
        'stok_minimum',
        'keterangan',
        'satuan_id'
    ];

    protected $casts = [
        'harga_satuan' => 'float',
        'harga_per_satuan_dasar' => 'float',
        'harga_rata_rata' => 'float',
        'stok' => 'float',
        'stok_minimum' => 'float',
    ];
    
    protected $appends = ['stok_aman', 'status_stok', 'harga_per_gram', 'stok_dalam_gram'];

    /**
     * Get the harga per gram attribute.
     *
     * @return float
     */
    public function getHargaPerGramAttribute()
    {
        // Jika harga per satuan dasar tidak ada, return 0
        if (empty($this->harga_per_satuan_dasar)) {
            return 0;
        }
        
        // Jika satuan dasar adalah gram, return harga per satuan dasar
        if ($this->satuan_dasar === 'gram') {
            return (float) $this->harga_per_satuan_dasar;
        }
        
        // Pastikan faktor_konversi valid (tidak nol)
        if (empty($this->faktor_konversi) || $this->faktor_konversi <= 0) {
            return 0;
        }
        
        // Hitung berdasarkan konversi
        return (float) $this->harga_per_satuan_dasar / (float) $this->faktor_konversi;
    }

    /**
     * Konversi harga dari satuan apapun ke satuan utama
     * 
     * @param float $harga Harga dalam satuan yang dibeli
     * @param string $fromUnit Satuan pembelian
     * @param float $quantity Jumlah yang dibeli
     * @return array [convertedQuantity, hargaPerSatuanUtama]
     */
    public function konversiKeSatuanUtama($harga, $fromUnit, $quantity)
    {
        // Default values jika tidak ada
        $satuanUtama = $this->satuan ?? 'KG';
        
        // Normalisasi satuan
        $fromUnit = strtoupper(trim($fromUnit));
        $satuanUtama = strtoupper(trim($satuanUtama));
        
        // Jika satuan sama, tidak perlu konversi
        if ($fromUnit === $satuanUtama) {
            return [
                'quantity' => $quantity,
                'harga_per_satuan_utama' => $harga
            ];
        }
        
        // Konversi faktor ke KG (satuan utama default)
        $konversiFaktor = 1;
        
        switch ($fromUnit) {
            case 'KG':
            case 'KILOGRAM':
            case 'K':
                $konversiFaktor = 1;
                break;
            case 'G':
            case 'GR':
                $konversiFaktor = 0.001;
                break;
            case 'MG':
                $konversiFaktor = 0.000001;
                break;
            case 'LITER':
            case 'L':
                $konversiFaktor = 1; // Asumsi 1L = 1kg untuk air
                break;
            case 'ML':
                $konversiFaktor = 0.001;
                break;
            case 'PCS':
            case 'BUAH':
            case 'BOTOL':
            case 'PAK':
            case 'BUNGKUS':
            case 'DUS':
            case 'KALENG':
            case 'SACHET':
            case 'TABLET':
            case 'KAPSUL':
            case 'TUBE':
            case 'POTONG':
            case 'LEMBAR':
            case 'ROLL':
            case 'METER':
            case 'CM':
            case 'MM':
            case 'INCH':
            case 'KODI':
            case 'LUSIN':
            case 'GROSS':
                $konversiFaktor = 1;
                break;
        }
        
        $convertedQuantity = $quantity * $konversiFaktor;
        $hargaPerSatuanUtama = $harga / $konversiFaktor;
        
        return [
            'quantity' => $convertedQuantity,
            'harga_per_satuan_utama' => $hargaPerSatuanUtama
        ];
    }
    
    /**
     * Get the satuan relationship
     */
    public function satuan()
    {
        return $this->belongsTo(Satuan::class, 'satuan_id');
    }
    
    /**
     * Get the satuan relationship
     */
    public function satuanRelation()
    {
        return $this->belongsTo(Satuan::class, 'satuan_id');
    }
    
    /**
     * Get the pembelian details for this bahan baku
     */
    public function pembelianDetails()
    {
        return $this->hasMany(PembelianDetail::class, 'bahan_baku_id');
    }
}
