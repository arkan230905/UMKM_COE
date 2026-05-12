<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Btkl;
use App\Models\ProsesProduksi;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class BtklApiController extends Controller
{
    /**
     * Get BTKL data by jabatan_id
     */
    public function getByJabatan($jabatanId): JsonResponse
    {
        try {
            $btkl = Btkl::where('jabatan_id', $jabatanId)
                ->where('is_active', true)
                ->first();

            if (!$btkl) {
                return response()->json([
                    'success' => false,
                    'message' => 'BTKL not found'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'id' => $btkl->id,
                    'nama_btkl' => $btkl->nama_btkl,
                    'tarif_per_jam' => $btkl->tarif_per_jam,
                    'kapasitas_per_jam' => $btkl->kapasitas_per_jam,
                    'satuan' => $btkl->satuan,
                    'deskripsi_proses' => $btkl->deskripsi_proses,
                    'is_active' => $btkl->is_active,
                    'updated_at' => $btkl->updated_at->toISOString(),
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get BTKL data: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get BTKL data by proses_produksi_id (using ProsesProduksi data)
     */
    public function getByProses($prosesId): JsonResponse
    {
        try {
            $proses = ProsesProduksi::find($prosesId);
            
            if (!$proses) {
                return response()->json([
                    'success' => false,
                    'message' => 'Proses not found'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'id' => $proses->id,
                    'nama_btkl' => $proses->nama_proses,
                    'tarif_per_jam' => $proses->tarif_btkl,
                    'kapasitas_per_jam' => $proses->kapasitas_per_jam,
                    'satuan' => $proses->satuan_btkl ?? 'Jam',
                    'deskripsi_proses' => $proses->deskripsi,
                    'jabatan_id' => $proses->jabatan_id,
                    'updated_at' => $proses->updated_at->toISOString(),
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get BTKL data: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get all active BTKL data
     */
    public function index(): JsonResponse
    {
        try {
            $btkl = Btkl::with('jabatan')
                ->where('is_active', true)
                ->get();

            return response()->json([
                'success' => true,
                'data' => $btkl->map(function ($item) {
                    return [
                        'id' => $item->id,
                        'nama_btkl' => $item->nama_btkl,
                        'tarif_per_jam' => $item->tarif_per_jam,
                        'kapasitas_per_jam' => $item->kapasitas_per_jam,
                        'satuan' => $item->satuan,
                        'deskripsi_proses' => $item->deskripsi_proses,
                        'jabatan_nama' => $item->jabatan ? $item->jabatan->nama : null,
                        'updated_at' => $item->updated_at->toISOString(),
                    ];
                })
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get BTKL data: ' . $e->getMessage()
            ], 500);
        }
    }
}
