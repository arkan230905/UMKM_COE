<?php

namespace App\Services;

use App\Models\BahanBaku;
use Illuminate\Support\Facades\DB;

class BahanBakuService
{
    /**
     * Hitung harga sub satuan berdasarkan konversi yang benar
     * Formula baru: untuk nilai desimal (< 1): (harga_utama * nilai * 100) / 100
     * Formula lama: untuk nilai >= 1: harga_utama / konversi
     */
    public function calculateSubSatuanPrices($bahanBakuId)
    {
        $bahan = BahanBaku::with(['satuan', 'subSatuan1', 'subSatuan2', 'subSatuan3'])->find($bahanBakuId);
        
        if (!$bahan || !($bahan->harga_satuan_display ?? $bahan->harga_satuan)) {
            return [];
        }

        $subSatuanData = [];

        // Sub Satuan 1
        if ($bahan->sub_satuan_1_id && $bahan->sub_satuan_1_konversi > 0) {
            $hargaPerUnit = $bahan->calculateSubUnitPrice(1);
            
            $subSatuanData[] = [
                'satuan_nama' => $bahan->subSatuan1->nama ?? 'Unknown',
                'konversi_nilai' => $bahan->sub_satuan_1_konversi,
                'harga_per_unit' => round($hargaPerUnit, 2),
                'formula_text' => $this->getFormulaText($bahan->harga_satuan_display ?? $bahan->harga_satuan, $bahan->sub_satuan_1_konversi, $bahan->sub_satuan_1_nilai, $hargaPerUnit),
                'konversi_text' => number_format($bahan->sub_satuan_1_konversi, 0, ',', '.') . " " . 
                                 ($bahan->satuan->nama ?? 'unit') . " = " . 
                                 $this->formatDecimal($bahan->sub_satuan_1_nilai) . " " . 
                                 ($bahan->subSatuan1->nama ?? 'unit')
            ];
        }

        // Sub Satuan 2
        if ($bahan->sub_satuan_2_id && $bahan->sub_satuan_2_konversi > 0) {
            $hargaPerUnit = $bahan->calculateSubUnitPrice(2);
            
            $subSatuanData[] = [
                'satuan_nama' => $bahan->subSatuan2->nama ?? 'Unknown',
                'konversi_nilai' => $bahan->sub_satuan_2_konversi,
                'harga_per_unit' => round($hargaPerUnit, 2),
                'formula_text' => $this->getFormulaText($bahan->harga_satuan_display ?? $bahan->harga_satuan, $bahan->sub_satuan_2_konversi, $bahan->sub_satuan_2_nilai, $hargaPerUnit),
                'konversi_text' => number_format($bahan->sub_satuan_2_konversi, 0, ',', '.') . " " . 
                                 ($bahan->satuan->nama ?? 'unit') . " = " . 
                                 $this->formatDecimal($bahan->sub_satuan_2_nilai) . " " . 
                                 ($bahan->subSatuan2->nama ?? 'unit')
            ];
        }

        // Sub Satuan 3
        if ($bahan->sub_satuan_3_id && $bahan->sub_satuan_3_konversi > 0) {
            $hargaPerUnit = $bahan->calculateSubUnitPrice(3);
            
            $subSatuanData[] = [
                'satuan_nama' => $bahan->subSatuan3->nama ?? 'Unknown',
                'konversi_nilai' => $bahan->sub_satuan_3_konversi,
                'harga_per_unit' => round($hargaPerUnit, 2),
                'formula_text' => $this->getFormulaText($bahan->harga_satuan_display ?? $bahan->harga_satuan, $bahan->sub_satuan_3_konversi, $bahan->sub_satuan_3_nilai, $hargaPerUnit),
                'konversi_text' => number_format($bahan->sub_satuan_3_konversi, 0, ',', '.') . " " . 
                                 ($bahan->satuan->nama ?? 'unit') . " = " . 
                                 $this->formatDecimal($bahan->sub_satuan_3_nilai) . " " . 
                                 ($bahan->subSatuan3->nama ?? 'unit')
            ];
        }

        return $subSatuanData;
    }

    /**
     * Generate formula text based on nilai value
     */
    private function getFormulaText($hargaUtama, $konversi, $nilai, $hargaPerUnit)
    {
        if ($nilai < 1) {
            // Untuk nilai desimal
            return "Rp " . number_format($hargaUtama, 0, ',', '.') . 
                   " × " . $this->formatDecimal($nilai * 100) . 
                   " ÷ 100 = Rp " . number_format($hargaPerUnit, 0, ',', '.') .
                   " (desimal)";
        } else {
            // Untuk nilai >= 1, gunakan pembagian dengan nilai
            return "Rp " . number_format($hargaUtama, 0, ',', '.') . 
                   " ÷ " . $this->formatDecimal($nilai) . 
                   " = Rp " . number_format($hargaPerUnit, 0, ',', '.');
        }
    }
    
    /**
     * Format decimal value for display, preserving decimal places
     */
    private function formatDecimal($value)
    {
        if ($value == floor($value)) {
            return number_format($value, 0, ',', '.');
        }
        return rtrim(rtrim(number_format($value, 4, ',', '.'), '0'), ',');
    }

    /**
     * Hitung harga sub satuan tunggal dengan validasi
     */
    public function calculateSingleSubSatuanPrice($hargaUtama, $nilaiKonversi)
    {
        // Validasi pembagian dengan nol
        if ($nilaiKonversi <= 0) {
            return 0;
        }

        // Formula yang benar: harga_sub = harga_utama / nilai_konversi
        return $hargaUtama / $nilaiKonversi;
    }

    /**
     * Update sub satuan prices dengan perhitungan yang benar
     */
    public function updateSubSatuanPrices($bahanBakuId)
    {
        $bahan = BahanBaku::find($bahanBakuId);
        
        if (!$bahan || !$bahan->harga_satuan) {
            return false;
        }

        $updateData = [];

        // Update Sub Satuan 1
        if ($bahan->sub_satuan_1_id && $bahan->sub_satuan_1_nilai > 0) {
            $updateData['sub_satuan_1_nilai'] = $bahan->sub_satuan_1_nilai;
        }

        // Update Sub Satuan 2
        if ($bahan->sub_satuan_2_id && $bahan->sub_satuan_2_nilai > 0) {
            $updateData['sub_satuan_2_nilai'] = $bahan->sub_satuan_2_nilai;
        }

        // Update Sub Satuan 3
        if ($bahan->sub_satuan_3_id && $bahan->sub_satuan_3_nilai > 0) {
            $updateData['sub_satuan_3_nilai'] = $bahan->sub_satuan_3_nilai;
        }

        if (!empty($updateData)) {
            $updateData['updated_at'] = now();
            
            DB::table('bahan_bakus')
                ->where('id', $bahanBakuId)
                ->update($updateData);
                
            return true;
        }

        return false;
    }
}