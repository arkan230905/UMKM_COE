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
                                " ÷ " . number_format($konversi->konversi_nilai, 0, ',', '.') . 
                                " = Rp " . number_format($hargaSubSatuan, 0, ',', '.'),
                'konversi_text' => "1 {$bahan->satuan_utama_nama} = " . 
                                 number_format($konversi->konversi_nilai, 0, ',', '.') . 
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
     */
    public function getDirectSubSatuanPrices($bahanPendukungId)
    {
        $bahan = DB::table('bahan_pendukungs as bp')
            ->leftJoin('satuans as s_utama', 'bp.satuan_id', '=', 's_utama.id')
            ->leftJoin('satuans as s1', 'bp.sub_satuan_1_id', '=', 's1.id')
            ->leftJoin('satuans as s2', 'bp.sub_satuan_2_id', '=', 's2.id')
            ->leftJoin('satuans as s3', 'bp.sub_satuan_3_id', '=', 's3.id')
            ->where('bp.id', $bahanPendukungId)
            ->select(
                'bp.*',
                's_utama.nama as satuan_utama_nama',
                's1.nama as sub_satuan_1_nama',
                's2.nama as sub_satuan_2_nama',
                's3.nama as sub_satuan_3_nama'
            )
            ->first();

        if (!$bahan) {
            return null;
        }

        $subSatuanData = [];

        // Sub Satuan 1
        if ($bahan->sub_satuan_1_id && $bahan->sub_satuan_1_konversi > 0) {
            $subSatuanData[] = [
                'satuan_nama' => $bahan->sub_satuan_1_nama,
                'konversi_nilai' => $bahan->sub_satuan_1_konversi,
                'harga_per_unit' => $bahan->sub_satuan_1_nilai,
                'formula_text' => "Rp " . number_format($bahan->harga_satuan, 0, ',', '.') . 
                                " ÷ " . number_format($bahan->sub_satuan_1_konversi, 0, ',', '.') . 
                                " = Rp " . number_format($bahan->sub_satuan_1_nilai, 0, ',', '.'),
                'konversi_text' => "1 {$bahan->satuan_utama_nama} = " . 
                                 number_format($bahan->sub_satuan_1_konversi, 0, ',', '.') . 
                                 " {$bahan->sub_satuan_1_nama}"
            ];
        }

        // Sub Satuan 2
        if ($bahan->sub_satuan_2_id && $bahan->sub_satuan_2_konversi > 0) {
            $subSatuanData[] = [
                'satuan_nama' => $bahan->sub_satuan_2_nama,
                'konversi_nilai' => $bahan->sub_satuan_2_konversi,
                'harga_per_unit' => $bahan->sub_satuan_2_nilai,
                'formula_text' => "Rp " . number_format($bahan->harga_satuan, 0, ',', '.') . 
                                " ÷ " . number_format($bahan->sub_satuan_2_konversi, 0, ',', '.') . 
                                " = Rp " . number_format($bahan->sub_satuan_2_nilai, 0, ',', '.'),
                'konversi_text' => "1 {$bahan->satuan_utama_nama} = " . 
                                 number_format($bahan->sub_satuan_2_konversi, 0, ',', '.') . 
                                 " {$bahan->sub_satuan_2_nama}"
            ];
        }

        // Sub Satuan 3
        if ($bahan->sub_satuan_3_id && $bahan->sub_satuan_3_konversi > 0) {
            $subSatuanData[] = [
                'satuan_nama' => $bahan->sub_satuan_3_nama,
                'konversi_nilai' => $bahan->sub_satuan_3_konversi,
                'harga_per_unit' => $bahan->sub_satuan_3_nilai,
                'formula_text' => "Rp " . number_format($bahan->harga_satuan, 0, ',', '.') . 
                                " ÷ " . number_format($bahan->sub_satuan_3_konversi, 0, ',', '.') . 
                                " = Rp " . number_format($bahan->sub_satuan_3_nilai, 0, ',', '.'),
                'konversi_text' => "1 {$bahan->satuan_utama_nama} = " . 
                                 number_format($bahan->sub_satuan_3_konversi, 0, ',', '.') . 
                                 " {$bahan->sub_satuan_3_nama}"
            ];
        }

        return [
            'bahan' => $bahan,
            'sub_satuan_prices' => $subSatuanData
        ];
    }
}