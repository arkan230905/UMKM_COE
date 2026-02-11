<?php

namespace App\Services;

use App\Models\BahanBaku;
use App\Models\BahanPendukung;
use App\Support\UnitConverter;

class BiayaBahanConversionService
{
    private UnitConverter $converter;

    public function __construct()
    {
        $this->converter = new UnitConverter();
    }

    /**
     * Konversi jumlah bahan baku ke satuan base dengan menggunakan sub_satuan dari database
     */
    public function convertBahanBakuToBase(BahanBaku $bahanBaku, float $jumlah, string $satuanInput): array
    {
        $harga = (float)$bahanBaku->harga_satuan;
        $satuanBaseObj = $bahanBaku->satuan;
        $satuanBase = is_object($satuanBaseObj) ? ($satuanBaseObj->nama ?? 'unit') : ($satuanBaseObj ?: 'unit');

        // PENTING: Cek apakah satuan input sama dengan satuan base
        if (strtolower($satuanInput) === strtolower($satuanBase)) {
            // Tidak perlu konversi
            $qtyBase = $jumlah;
        } else {
            // Cek apakah ada konversi di sub_satuan
            $qtyBase = $jumlah;
            $konversiDitemukan = false;
            
            // Cek sub_satuan_1
            if ($bahanBaku->subSatuan1 && strtolower($bahanBaku->subSatuan1->nama) === strtolower($satuanInput)) {
                if ($bahanBaku->sub_satuan_1_nilai > 0) {
                    $qtyBase = $jumlah / $bahanBaku->sub_satuan_1_nilai;
                    $konversiDitemukan = true;
                }
            }
            // Cek sub_satuan_2
            if (!$konversiDitemukan && $bahanBaku->subSatuan2 && strtolower($bahanBaku->subSatuan2->nama) === strtolower($satuanInput)) {
                if ($bahanBaku->sub_satuan_2_nilai > 0) {
                    $qtyBase = $jumlah / $bahanBaku->sub_satuan_2_nilai;
                    $konversiDitemukan = true;
                }
            }
            // Cek sub_satuan_3
            if (!$konversiDitemukan && $bahanBaku->subSatuan3 && strtolower($bahanBaku->subSatuan3->nama) === strtolower($satuanInput)) {
                if ($bahanBaku->sub_satuan_3_nilai > 0) {
                    $qtyBase = $jumlah / $bahanBaku->sub_satuan_3_nilai;
                    $konversiDitemukan = true;
                }
            }
            
            // Jika tidak ada konversi di sub_satuan, coba UnitConverter
            if (!$konversiDitemukan) {
                $desc = $this->converter->describe($satuanInput, $satuanBase);
                if ($desc !== 'konversi tidak dikenal' && !str_contains($desc, 'volume↔massa')) {
                    $qtyBase = $this->converter->convert($jumlah, $satuanInput, $satuanBase);
                }
            }
        }

        $subtotal = $harga * $qtyBase;
        
        // Hitung harga per satuan yang digunakan (harga konversi)
        $hargaPerSatuanDipakai = $jumlah > 0 ? ($subtotal / $jumlah) : 0;

        return [
            'qty_base' => $qtyBase,
            'subtotal' => $subtotal,
            'harga_per_satuan' => $hargaPerSatuanDipakai
        ];
    }

    /**
     * Konversi jumlah bahan pendukung ke satuan base dengan menggunakan sub_satuan dari database
     */
    public function convertBahanPendukungToBase(BahanPendukung $bahanPendukung, float $jumlah, string $satuanInput): array
    {
        $harga = (float)$bahanPendukung->harga_satuan;
        $satuanBaseObj = $bahanPendukung->satuan;
        $satuanBase = is_object($satuanBaseObj) ? ($satuanBaseObj->nama ?? 'unit') : ($satuanBaseObj ?: 'unit');

        // PENTING: Cek apakah satuan input sama dengan satuan base
        if (strtolower($satuanInput) === strtolower($satuanBase)) {
            // Tidak perlu konversi
            $qtyBase = $jumlah;
        } else {
            // Cek apakah ada konversi di sub_satuan
            $qtyBase = $jumlah;
            $konversiDitemukan = false;
            
            // Cek sub_satuan_1
            if ($bahanPendukung->subSatuan1 && strtolower($bahanPendukung->subSatuan1->nama) === strtolower($satuanInput)) {
                if ($bahanPendukung->sub_satuan_1_nilai > 0) {
                    $qtyBase = $jumlah / $bahanPendukung->sub_satuan_1_nilai;
                    $konversiDitemukan = true;
                }
            }
            // Cek sub_satuan_2
            if (!$konversiDitemukan && $bahanPendukung->subSatuan2 && strtolower($bahanPendukung->subSatuan2->nama) === strtolower($satuanInput)) {
                if ($bahanPendukung->sub_satuan_2_nilai > 0) {
                    $qtyBase = $jumlah / $bahanPendukung->sub_satuan_2_nilai;
                    $konversiDitemukan = true;
                }
            }
            // Cek sub_satuan_3
            if (!$konversiDitemukan && $bahanPendukung->subSatuan3 && strtolower($bahanPendukung->subSatuan3->nama) === strtolower($satuanInput)) {
                if ($bahanPendukung->sub_satuan_3_nilai > 0) {
                    $qtyBase = $jumlah / $bahanPendukung->sub_satuan_3_nilai;
                    $konversiDitemukan = true;
                }
            }
            
            // Jika tidak ada konversi di sub_satuan, coba UnitConverter
            if (!$konversiDitemukan) {
                $desc = $this->converter->describe($satuanInput, $satuanBase);
                if ($desc !== 'konversi tidak dikenal' && !str_contains($desc, 'volume↔massa')) {
                    $qtyBase = $this->converter->convert($jumlah, $satuanInput, $satuanBase);
                }
            }
        }

        $subtotal = $harga * $qtyBase;
        
        // Hitung harga per satuan yang digunakan (harga konversi)
        $hargaPerSatuanDipakai = $jumlah > 0 ? ($subtotal / $jumlah) : 0;

        return [
            'qty_base' => $qtyBase,
            'subtotal' => $subtotal,
            'harga_per_satuan' => $hargaPerSatuanDipakai
        ];
    }
}
