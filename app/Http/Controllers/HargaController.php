<?php

namespace App\Http\Controllers;

use App\Models\BahanBaku;
use App\Models\BahanPendukung;
use App\Services\HargaService;
use Illuminate\Http\Request;

class HargaController extends Controller
{
    protected $hargaService;
    
    public function __construct(HargaService $hargaService)
    {
        $this->middleware('auth');
        $this->hargaService = $hargaService;
    }
    
    /**
     * Display harga validation dashboard
     */
    public function index()
    {
        $bahanBakus = BahanBaku::all();
        $bahanPendukungs = BahanPendukung::all();
        
        return view('harga.index', compact('bahanBakus', 'bahanPendukungs'));
    }
    
    /**
     * Recalculate harga rata-rata for all bahan baku
     */
    public function recalculateAll()
    {
        try {
            $results = [];
            $errors = [];
            
            // Process bahan baku
            $bahanBakus = BahanBaku::all();
            foreach ($bahanBakus as $bahanBaku) {
                try {
                    $hargaBaru = $this->hargaService->recalculateHargaRataRata($bahanBaku->id);
                    $results[] = [
                        'id' => $bahanBaku->id,
                        'tipe' => 'bahan_baku',
                        'nama' => $bahanBaku->nama_bahan,
                        'harga_lama' => $bahanBaku->harga_rata_rata,
                        'harga_baru' => $hargaBaru,
                        'status' => 'success'
                    ];
                } catch (\Exception $e) {
                    $errors[] = [
                        'id' => $bahanBaku->id,
                        'tipe' => 'bahan_baku',
                        'nama' => $bahanBaku->nama_bahan,
                        'error' => $e->getMessage()
                    ];
                }
            }
            
            // Process bahan pendukung
            $bahanPendukungs = BahanPendukung::all();
            foreach ($bahanPendukungs as $bahanPendukung) {
                try {
                    $hargaBaru = $this->hargaService->recalculateHargaRataRataBahanPendukung($bahanPendukung->id);
                    $results[] = [
                        'id' => $bahanPendukung->id,
                        'tipe' => 'bahan_pendukung',
                        'nama' => $bahanPendukung->nama_bahan,
                        'harga_lama' => $bahanPendukung->harga_satuan,
                        'harga_baru' => $hargaBaru,
                        'status' => 'success'
                    ];
                } catch (\Exception $e) {
                    $errors[] = [
                        'id' => $bahanPendukung->id,
                        'tipe' => 'bahan_pendukung',
                        'nama' => $bahanPendukung->nama_bahan,
                        'error' => $e->getMessage()
                    ];
                }
            }
            
            return response()->json([
                'success' => true,
                'message' => 'Perhitungan ulang harga rata-rata selesai',
                'results' => $results,
                'errors' => $errors,
                'total_processed' => count($bahanBakus) + count($bahanPendukungs),
                'success_count' => count($results),
                'error_count' => count($errors),
                'summary' => [
                    'bahan_baku_count' => count($bahanBakus),
                    'bahan_pendukung_count' => count($bahanPendukungs),
                    'total_items' => count($bahanBakus) + count($bahanPendukungs),
                    'consistent_count' => count(array_filter($results, fn($r) => $r['status'] === 'success')),
                    'inconsistent_count' => count(array_filter($results, fn($r) => $r['status'] !== 'success')),
                    'error_count' => count($errors)
                ]
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Validate harga rata-rata for all bahan baku
     */
    public function validateAll()
    {
        try {
            $results = [];
            
            // Process bahan baku
            $bahanBakus = BahanBaku::all();
            foreach ($bahanBakus as $bahanBaku) {
                $validation = $this->hargaService->validateHargaRataRata($bahanBaku->id);
                $results[] = array_merge($validation, [
                    'tipe' => 'bahan_baku'
                ]);
            }
            
            // Process bahan pendukung
            $bahanPendukungs = BahanPendukung::all();
            foreach ($bahanPendukungs as $bahanPendukung) {
                $validation = $this->hargaService->validateHargaRataRataBahanPendukung($bahanPendukung->id);
                $results[] = array_merge($validation, [
                    'tipe' => 'bahan_pendukung'
                ]);
            }
            
            return response()->json([
                'success' => true,
                'message' => 'Validasi harga rata-rata selesai',
                'results' => $results,
                'summary' => [
                    'total_items' => count($results),
                    'consistent_count' => count(array_filter($results, fn($r) => $r['konsisten'])),
                    'inconsistent_count' => count(array_filter($results, fn($r) => !$r['konsisten'])),
                    'bahan_baku_consistent' => count(array_filter($results, fn($r) => $r['tipe'] === 'bahan_baku' && $r['konsisten'])),
                    'bahan_pendukung_consistent' => count(array_filter($results, fn($r) => $r['tipe'] === 'bahan_pendukung' && $r['konsisten']))
                ]
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Get purchase history for specific bahan baku
     */
    public function purchaseHistory($bahanBakuId)
    {
        try {
            $bahanBaku = BahanBaku::find($bahanBakuId);
            if (!$bahanBaku) {
                return response()->json([
                    'success' => false,
                    'message' => 'Bahan baku tidak ditemukan'
                ], 404);
            }
            
            $history = $this->hargaService->getPurchaseHistory($bahanBakuId);
            
            return response()->json([
                'success' => true,
                'message' => 'Riwayat pembelian berhasil diambil',
                'data' => [
                    'bahan_baku' => $bahanBaku,
                    'history' => $history
                ]
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Get purchase history for specific bahan pendukung
     */
    public function purchaseHistoryBahanPendukung($bahanPendukungId)
    {
        try {
            $bahanPendukung = BahanPendukung::find($bahanPendukungId);
            if (!$bahanPendukung) {
                return response()->json([
                    'success' => false,
                    'message' => 'Bahan pendukung tidak ditemukan'
                ], 404);
            }
            
            $history = $this->hargaService->getPurchaseHistoryBahanPendukung($bahanPendukungId);
            
            return response()->json([
                'success' => true,
                'message' => 'Riwayat pembelian berhasil diambil',
                'data' => [
                    'bahan_pendukung' => $bahanPendukung,
                    'history' => $history
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
