<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Produk;
use App\Models\Bom;
use App\Models\BahanBaku;
use App\Services\KonversiProduksiService;
use Illuminate\Http\Request;

class ProdukController extends Controller
{
    protected $konversiService;

    public function __construct(KonversiProduksiService $konversiService)
    {
        $this->konversiService = $konversiService;
    }

    /**
     * Get BOM details for a product
     */
    public function getBomDetails($produkId)
    {
        try {
            $produk = Produk::find($produkId);
            if (!$produk) {
                return response()->json([
                    'success' => false,
                    'message' => 'Produk tidak ditemukan'
                ], 404);
            }

            $bom = Bom::where('produk_id', $produkId)->first();
            if (!$bom) {
                return response()->json([
                    'success' => false,
                    'message' => 'BOM tidak ditemukan untuk produk ini'
                ], 404);
            }

            $bahanBakuDetails = [];
            $konversiInfo = [];

            foreach ($bom->details as $detail) {
                $bahanBaku = $detail->bahanBaku;
                if (!$bahanBaku) continue;

                // Get stok tersedia
                $stokTersedia = $bahanBaku->stok ?? 0;
                $satuanStok = $bahanBaku->satuan ?? 'pcs';

                // Check apakah stok cukup untuk 1 unit produksi
                $stokCukup = $bahanBaku->hasEnoughStock($detail->jumlah, $detail->satuan ?: $satuanStok);

                $bahanBakuDetails[] = [
                    'bahan_baku_id' => $bahanBaku->id,
                    'nama_bahan' => $bahanBaku->nama_bahan,
                    'jumlah_resep' => $detail->jumlah,
                    'satuan_resep' => $detail->satuan ?: $satuanStok,
                    'stok_tersedia' => $stokTersedia,
                    'satuan_stok' => $satuanStok,
                    'stok_cukup' => $stokCukup,
                    'harga_satuan' => $bahanBaku->getHargaRataRataSaatIni()
                ];

                // Get konversi info untuk bahan baku ini
                $ringkasanKonversi = $this->konversiService->getRingkasanKonversi($bahanBaku->id);
                foreach ($ringkasanKonversi as $konversi) {
                    $key = $konversi['satuan_asli'] . '_to_' . $konversi['satuan_hasil'];
                    if (!isset($konversiInfo[$key])) {
                        $konversiInfo[$key] = $konversi;
                    }
                }
            }

            return response()->json([
                'success' => true,
                'produk' => [
                    'id' => $produk->id,
                    'nama_produk' => $produk->nama_produk,
                    'harga_pokok' => $produk->harga_pokok ?? 0
                ],
                'bahan_baku' => $bahanBakuDetails,
                'konversi' => array_values($konversiInfo)
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get BOM calculations for specific quantity
     */
    public function getBomCalculations($produkId, Request $request)
    {
        try {
            $qty = $request->input('qty', 1);
            $qty = floatval($qty);

            if ($qty <= 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Quantity harus lebih dari 0'
                ], 400);
            }

            $produk = Produk::find($produkId);
            if (!$produk) {
                return response()->json([
                    'success' => false,
                    'message' => 'Produk tidak ditemukan'
                ], 404);
            }

            $bom = Bom::where('produk_id', $produkId)->first();
            if (!$bom) {
                return response()->json([
                    'success' => false,
                    'message' => 'BOM tidak ditemukan untuk produk ini'
                ], 404);
            }

            $bahanBakuDetails = [];
            $totalBiayaBahan = 0;

            foreach ($bom->details as $detail) {
                $bahanBaku = $detail->bahanBaku;
                if (!$bahanBaku) continue;

                $jumlahResep = $detail->jumlah * $qty;
                $satuanResep = $detail->satuan ?: $bahanBaku->satuan;

                // Hitung total kebutuhan dalam satuan stok
                $satuanStok = $bahanBaku->satuan ?? 'pcs';
                $totalKebutuhan = $this->konversiService->konversiJumlahResep(
                    $bahanBaku->id,
                    $jumlahResep,
                    $satuanResep,
                    $satuanStok
                );

                // Get stok tersedia
                $stokTersedia = $bahanBaku->stok ?? 0;

                // Check apakah stok cukup
                $stokCukup = $bahanBaku->hasEnoughStock($totalKebutuhan, $satuanStok);

                // Hitung biaya menggunakan konversi produksi
                $biayaBahan = $this->konversiService->hitungBiayaBahanResep(
                    $bahanBaku->id,
                    $jumlahResep,
                    $satuanResep
                );

                $totalBiayaBahan += $biayaBahan;

                $bahanBakuDetails[] = [
                    'bahan_baku_id' => $bahanBaku->id,
                    'nama_bahan' => $bahanBaku->nama_bahan,
                    'jumlah_resep' => $detail->jumlah,
                    'satuan_resep' => $satuanResep,
                    'total_kebutuhan' => $totalKebutuhan,
                    'satuan_kebutuhan' => $satuanStok,
                    'stok_tersedia' => $stokTersedia,
                    'satuan_stok' => $satuanStok,
                    'stok_cukup' => $stokCukup,
                    'biaya' => $biayaBahan,
                    'harga_satuan' => $bahanBaku->getHargaRataRataSaatIni()
                ];
            }

            // Hitung total biaya produksi
            $hargaService = new \App\Services\HargaService();
            $totalBiayaProduksi = $hargaService->calculateHPPBasedOnProduction($produkId, $qty);

            return response()->json([
                'success' => true,
                'produk' => [
                    'id' => $produk->id,
                    'nama_produk' => $produk->nama_produk,
                    'qty' => $qty
                ],
                'bahan_baku' => $bahanBakuDetails,
                'ringkasan_biaya' => [
                    'total_biaya_bahan' => $totalBiayaBahan,
                    'total_biaya_produksi' => $totalBiayaProduksi,
                    'hpp_per_unit' => $qty > 0 ? $totalBiayaProduksi / $qty : 0
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }
}
