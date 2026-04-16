<?php

namespace App\Services;

use App\Models\BahanBaku;
use Illuminate\Support\Facades\DB;

class BahanBakuService
{
    /**
     * Hitung harga sub satuan berdasarkan konversi yang benar
     * Formula: harga_sub = harga_utama / nilai_konversi
     */
    public function calculateSubSatuanPrices($bahanBakuId)
    {
        $bahan = BahanBaku::with(['satuan', 'subSatuan1', 'subSatuan2', 'subSatuan3'])->find($bahanBakuId);
        
        if (!$bahan || !$bahan->harga_satuan) {
            return [];
        }

        $subSatuanData = [];

        // Sub Satuan 1
        if ($bahan->sub_satuan_1_id && $bahan->sub_satuan_1_konversi > 0) {
            $hargaPerUnit = $bahan->harga_satuan / $bahan->sub_satuan_1_konversi;
            
            $subSatuanData[] = [
                'satuan_nama' => $bahan->subSatuan1->nama ?? 'Unknown',
                'konversi_nilai' => $bahan->sub_satuan_1_konversi,
                'harga_per_unit' => round($hargaPerUnit, 2),
                'formula_text' => "Rp " . number_format($bahan->harga_satuan, 0, ',', '.') . 
                                " ÷ " . number_format($bahan->sub_satuan_1_konversi, 0, ',', '.') . 
                                " = Rp " . number_format($hargaPerUnit, 0, ',', '.'),
                'konversi_text' => "1 " . ($bahan->satuan->nama ?? 'unit') . " = " . 
                                 number_format($bahan->sub_satuan_1_konversi, 0, ',', '.') . " " . 
                                 ($bahan->subSatuan1->nama ?? 'unit')
            ];
        }

        // Sub Satuan 2
        if ($bahan->sub_satuan_2_id && $bahan->sub_satuan_2_konversi > 0) {
            $hargaPerUnit = $bahan->harga_satuan / $bahan->sub_satuan_2_konversi;
            
            $subSatuanData[] = [
                'satuan_nama' => $bahan->subSatuan2->nama ?? 'Unknown',
                'konversi_nilai' => $bahan->sub_satuan_2_konversi,
                'harga_per_unit' => round($hargaPerUnit, 2),
                'formula_text' => "Rp " . number_format($bahan->harga_satuan, 0, ',', '.') . 
                                " ÷ " . number_format($bahan->sub_satuan_2_konversi, 0, ',', '.') . 
                                " = Rp " . number_format($hargaPerUnit, 0, ',', '.'),
                'konversi_text' => "1 " . ($bahan->satuan->nama ?? 'unit') . " = " . 
                                 number_format($bahan->sub_satuan_2_konversi, 0, ',', '.') . " " . 
                                 ($bahan->subSatuan2->nama ?? 'unit')
            ];
        }

        // Sub Satuan 3
        if ($bahan->sub_satuan_3_id && $bahan->sub_satuan_3_konversi > 0) {
            $hargaPerUnit = $bahan->harga_satuan / $bahan->sub_satuan_3_konversi;
            
            $subSatuanData[] = [
                'satuan_nama' => $bahan->subSatuan3->nama ?? 'Unknown',
                'konversi_nilai' => $bahan->sub_satuan_3_konversi,
                'harga_per_unit' => round($hargaPerUnit, 2),
                'formula_text' => "Rp " . number_format($bahan->harga_satuan, 0, ',', '.') . 
                                " ÷ " . number_format($bahan->sub_satuan_3_konversi, 0, ',', '.') . 
                                " = Rp " . number_format($hargaPerUnit, 0, ',', '.'),
                'konversi_text' => "1 " . ($bahan->satuan->nama ?? 'unit') . " = " . 
                                 number_format($bahan->sub_satuan_3_konversi, 0, ',', '.') . " " . 
                                 ($bahan->subSatuan3->nama ?? 'unit')
            ];
        }

        return $subSatuanData;
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
        if ($bahan->sub_satuan_1_id && $bahan->sub_satuan_1_konversi > 0) {
            $updateData['sub_satuan_1_nilai'] = $this->calculateSingleSubSatuanPrice(
                $bahan->harga_satuan, 
                $bahan->sub_satuan_1_konversi
            );
        }

        // Update Sub Satuan 2
        if ($bahan->sub_satuan_2_id && $bahan->sub_satuan_2_konversi > 0) {
            $updateData['sub_satuan_2_nilai'] = $this->calculateSingleSubSatuanPrice(
                $bahan->harga_satuan, 
                $bahan->sub_satuan_2_konversi
            );
        }

        // Update Sub Satuan 3
        if ($bahan->sub_satuan_3_id && $bahan->sub_satuan_3_konversi > 0) {
            $updateData['sub_satuan_3_nilai'] = $this->calculateSingleSubSatuanPrice(
                $bahan->harga_satuan, 
                $bahan->sub_satuan_3_konversi
            );
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