<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

class BahanPendukungService
{
    /**
     * Hitung harga sub satuan berdasarkan konversi
     * Formula: harga_sub = harga_utama / nilai_konversi
     */
    public function calculateSubSatuanPrices($bahanPendukungId)
    {
        // Ambil data bahan pendukung
        $bahan = DB::table('bahan_pendukungs as bp')
            ->leftJoin('satuans as s_utama', 'bp.satuan_id', '=', 's_utama.id')
            ->where('bp.id', $bahanPendukungId)
            ->select(
                'bp.*',
                's_utama.nama as satuan_utama_nama'
            )
            ->first();

        if (!$bahan) {
            return null;
        }

        // Ambil konversi dari tabel bahan_konversi
        $konversiList = DB::table('bahan_konversi as bk')
            ->join('satuans as s', 'bk.satuan_id', '=', 's.id')
            ->where('bk.bahan_id', $bahanPendukungId)
            ->select(
                's.nama as satuan_nama',
                'bk.nilai as konversi_nilai',
                'bk.satuan_id'
            )
            ->get();

        // Hitung harga untuk setiap sub satuan
        $subSatuanPrices = [];
        foreach ($konversiList as $konversi) {
            // Validasi pembagian dengan nol
            if ($konversi->konversi_nilai <= 0) {
                continue;
            }

            // Formula yang benar: harga_sub = harga_utama / nilai_konversi
            $hargaSubSatuan = $bahan->harga_satuan / $konversi->konversi_nilai;

            $subSatuanPrices[] = [
                'satuan_nama' => $konversi->satuan_nama,
                'konversi_nilai' => $konversi->konversi_nilai,
                'harga_per_unit' => round($hargaSubSatuan, 2),
                'formula_text' => "Rp " . number_format($bahan->harga_satuan, 0, ',', '.') . 
                                " ÷ " . $this->formatDecimal($konversi->konversi_nilai) . 
                                " = Rp " . number_format($hargaSubSatuan, 0, ',', '.'),
                'konversi_text' => "1 {$bahan->satuan_utama_nama} = " . 
                                 $this->formatDecimal($konversi->konversi_nilai) . 
                                 " {$konversi->satuan_nama}"
            ];
        }

        return [
            'bahan' => $bahan,
            'sub_satuan_prices' => $subSatuanPrices
        ];
    }

    /**
     * Ambil data sub satuan dari field langsung di tabel bahan_pendukungs
     * Updated to use new decimal calculation logic from model
     */
    public function getDirectSubSatuanPrices($bahanPendukungId)
    {
        $bahan = \App\Models\BahanPendukung::with(['satuan', 'subSatuan1', 'subSatuan2', 'subSatuan3'])->find($bahanPendukungId);

        if (!$bahan || !$bahan->harga_satuan) {
            return [
                'bahan' => $bahan,
                'sub_satuan_prices' => []
            ];
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

        return [
            'bahan' => $bahan,
            'sub_satuan_prices' => $subSatuanData
        ];
    }

    /**
     * Alternative method that accepts the bahan object directly
     * This allows using harga_satuan_display set by the controller
     */
    public function getDirectSubSatuanPricesFromObject($bahan)
    {
        if (!$bahan || !($bahan->harga_satuan_display ?? $bahan->harga_satuan)) {
            return [
                'bahan' => $bahan,
                'sub_satuan_prices' => []
            ];
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

        return [
            'bahan' => $bahan,
            'sub_satuan_prices' => $subSatuanData
        ];
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
}