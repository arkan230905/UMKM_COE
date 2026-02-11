<?php

namespace App\Services;

use App\Models\BahanBaku;
use App\Models\KonversiProduksi;
use App\Models\Produksi;
use App\Models\ProduksiDetail;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class KonversiProduksiService
{
    /**
     * Simpan konversi otomatis dari data produksi
     * 
     * @param Produksi $produksi
     * @param array $produksiDetails
     * @return void
     */
    public function simpanKonversiDariProduksi(Produksi $produksi, $produksiDetails = null)
    {
        try {
            DB::beginTransaction();

            // Ambil detail produksi jika tidak disediakan
            if (!$produksiDetails) {
                $produksiDetails = $produksi->details;
            }

            // Kelompokkan berdasarkan bahan baku untuk menghindari duplikasi
            $bahanGroups = [];
            
            foreach ($produksiDetails as $detail) {
                $bahanId = $detail->bahan_baku_id;
                
                if (!isset($bahanGroups[$bahanId])) {
                    $bahanGroups[$bahanId] = [
                        'bahan_baku_id' => $bahanId,
                        'total_jumlah_bahan' => 0,
                        'satuan_bahan' => $detail->satuan_resep,
                        'details' => []
                    ];
                }
                
                $bahanGroups[$bahanId]['total_jumlah_bahan'] += $detail->qty_resep;
                $bahanGroups[$bahanId]['details'][] = $detail;
            }

            // Simpan konversi untuk setiap bahan baku
            foreach ($bahanGroups as $group) {
                $this->createKonversiUntukBahan($produksi, $group);
            }

            DB::commit();
            
            Log::info('Konversi produksi berhasil disimpan', [
                'produksi_id' => $produksi->id,
                'total_bahan' => count($bahanGroups)
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Gagal menyimpan konversi produksi', [
                'produksi_id' => $produksi->id,
                'error' => $e->getMessage()
            ]);
            
            throw $e;
        }
    }

    /**
     * Buat konversi untuk satu bahan baku
     */
    private function createKonversiUntukBahan(Produksi $produksi, array $bahanGroup)
    {
        $bahanBaku = BahanBaku::find($bahanGroup['bahan_baku_id']);
        
        if (!$bahanBaku) {
            return;
        }

        // Data konversi
        $jumlahBahanAsli = $bahanGroup['total_jumlah_bahan'];
        $satuanAsli = $bahanGroup['satuan_bahan'];
        $jumlahHasilProduksi = $produksi->qty_produksi;
        $satuanHasil = $this->getSatuanHasilDefault($bahanBaku);

        // Hitung faktor konversi
        $faktorKonversi = KonversiProduksi::hitungFaktorKonversi($jumlahBahanAsli, $jumlahHasilProduksi);
        
        if ($faktorKonversi <= 0) {
            return;
        }

        // Hitung harga per satuan hasil
        $hargaBahanPerSatuanAsli = $bahanBaku->getHargaRataRataSaatIni();
        $hargaPerSatuanHasil = $faktorKonversi > 0 ? $hargaBahanPerSatuanAsli / $faktorKonversi : 0;

        // Cek apakah konversi serupa sudah ada
        $konversiExist = KonversiProduksi::where('bahan_baku_id', $bahanBaku->id)
            ->where('satuan_asli', $satuanAsli)
            ->where('satuan_hasil', $satuanHasil)
            ->active()
            ->first();

        if ($konversiExist) {
            // Update konversi yang ada dengan data baru (weighted average)
            $this->updateKonversiExist($konversiExist, $faktorKonversi, $hargaPerSatuanHasil, $produksi->tanggal);
        } else {
            // Buat konversi baru
            $konversi = new KonversiProduksi([
                'bahan_baku_id' => $bahanBaku->id,
                'produksi_id' => $produksi->id,
                'jumlah_bahan_asli' => $jumlahBahanAsli,
                'satuan_asli' => $satuanAsli,
                'jumlah_hasil_produksi' => $jumlahHasilProduksi,
                'satuan_hasil' => $satuanHasil,
                'faktor_konversi' => $faktorKonversi,
                'harga_per_satuan_hasil' => $hargaPerSatuanHasil,
                'tanggal_produksi' => $produksi->tanggal,
                'is_active' => true,
                'confidence_score' => 1.0,
            ]);

            $konversi->save();
        }
    }

    /**
     * Update konversi yang sudah ada dengan weighted average
     */
    private function updateKonversiExist(KonversiProduksi $konversi, $faktorBaru, $hargaBaru, $tanggal)
    {
        // Hitung weighted average untuk faktor konversi
        $totalProduksi = KonversiProduksi::where('bahan_baku_id', $konversi->bahan_baku_id)
            ->where('satuan_asli', $konversi->satuan_asli)
            ->where('satuan_hasil', $konversi->satuan_hasil)
            ->active()
            ->count();

        // Weighted average: (faktor_lama * total_lama + faktor_baru) / (total_lama + 1)
        $faktorRataRata = ($konversi->faktor_konversi * $totalProduksi + $faktorBaru) / ($totalProduksi + 1);
        $hargaRataRata = ($konversi->harga_per_satuan_hasil * $totalProduksi + $hargaBaru) / ($totalProduksi + 1);

        $konversi->update([
            'faktor_konversi' => $faktorRataRata,
            'harga_per_satuan_hasil' => $hargaRataRata,
            'tanggal_produksi' => $tanggal, // Update ke tanggal terbaru
        ]);

        // Update confidence score
        $konversi->updateConfidenceScore();
        $konversi->save();
    }

    /**
     * Get satuan hasil default berdasarkan jenis bahan baku
     */
    private function getSatuanHasilDefault(BahanBaku $bahanBaku)
    {
        $namaBahan = strtolower($bahanBaku->nama_bahan);
        
        // Mapping berdasarkan nama bahan
        if (strpos($namaBahan, 'ayam') !== false) {
            return 'potong';
        }
        
        if (strpos($namaBahan, 'telur') !== false) {
            return 'butir';
        }
        
        if (strpos($namaBahan, 'beras') !== false) {
            return 'porsi';
        }
        
        if (strpos($namaBahan, 'minyak') !== false || strpos($namaBahan, 'saus') !== false) {
            return 'ml';
        }
        
        if (strpos($namaBahan, 'gula') !== false || strpos($namaBahan, 'garam') !== false || strpos($namaBahan, 'tepung') !== false) {
            return 'gram';
        }
        
        // Default ke 'pcs' jika tidak ada yang cocok
        return 'pcs';
    }

    /**
     * Hitung biaya bahan baku untuk resep berdasarkan konversi produksi
     * 
     * @param int $bahanBakuId
     * @param float $jumlahResep
     * @param string $satuanResep
     * @return float
     */
    public function hitungBiayaBahanResep($bahanBakuId, $jumlahResep, $satuanResep)
    {
        $bahanBaku = BahanBaku::find($bahanBakuId);
        
        if (!$bahanBaku) {
            return 0;
        }

        // Coba dapatkan konversi aktif
        $konversi = $bahanBaku->getActiveKonversi($bahanBaku->satuan, $satuanResep);
        
        if ($konversi) {
            // Gunakan harga berdasarkan konversi produksi
            return $jumlahResep * $konversi->harga_per_satuan_hasil;
        }

        // Fallback ke harga satuan biasa
        return $jumlahResep * $bahanBaku->getHargaRataRataSaatIni();
    }

    /**
     * Konversi jumlah bahan untuk resep
     * 
     * @param int $bahanBakuId
     * @param float $jumlah
     * @param string $dariSatuan
     * @param string $keSatuan
     * @return float
     */
    public function konversiJumlahResep($bahanBakuId, $jumlah, $dariSatuan, $keSatuan)
    {
        $bahanBaku = BahanBaku::find($bahanBakuId);
        
        if (!$bahanBaku) {
            return 0;
        }

        return $bahanBaku->konversiBerdasarkanProduksi($jumlah, $dariSatuan, $keSatuan);
    }

    /**
     * Get ringkasan konversi untuk bahan baku
     */
    public function getRingkasanKonversi($bahanBakuId)
    {
        $bahanBaku = BahanBaku::find($bahanBakuId);
        
        if (!$bahanBaku) {
            return [];
        }

        $konversiAktif = KonversiProduksi::where('bahan_baku_id', $bahanBakuId)
            ->active()
            ->highConfidence()
            ->get()
            ->groupBy('satuan_hasil');

        $ringkasan = [];
        
        foreach ($konversiAktif as $satuanHasil => $konversis) {
            $konversiTerbaik = $konversis->first();
            
            $ringkasan[] = [
                'satuan_asli' => $konversiTerbaik->satuan_asli,
                'satuan_hasil' => $satuanHasil,
                'faktor_konversi' => $konversiTerbaik->faktor_konversi,
                'harga_per_satuan_hasil' => $konversiTerbaik->harga_per_satuan_hasil,
                'confidence_score' => $konversiTerbaik->confidence_score,
                'format' => $konversiTerbaik->faktor_konversi_format,
            ];
        }

        return $ringkasan;
    }

    /**
     * Reset konversi untuk bahan baku (jika ada perubahan drastis)
     */
    public function resetKonversiBahan($bahanBakuId)
    {
        KonversiProduksi::where('bahan_baku_id', $bahanBakuId)
            ->update(['is_active' => false]);
            
        Log::info('Konversi bahan baku direset', ['bahan_baku_id' => $bahanBakuId]);
    }
}
