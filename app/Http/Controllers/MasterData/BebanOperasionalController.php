<?php

namespace App\Http\Controllers\MasterData;

use App\Http\Controllers\Controller;
use App\Models\BebanOperasional;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BebanOperasionalController extends Controller
{
    /**
     * Display a listing of beban operasional
     */
    public function index()
    {
        try {
            $bebanOperasional = BebanOperasional::query()
                ->orderBy('kode', 'asc')
                ->get();

            return view('master-data.beban-operasional.index', compact('bebanOperasional'));
            
        } catch (\Exception $e) {
            \Log::error('Error in BebanOperasionalController@index: ' . $e->getMessage());
            return back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    /**
     * Store a newly created beban operasional
     */
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'nama_beban' => 'required|string|max:255',
                'budget_bulanan' => 'required|numeric|min:0',
            ]);

            // Explicitly create the data array with all required fields
            $data = [
                'nama_beban' => $validated['nama_beban'],
                'budget_bulanan' => $validated['budget_bulanan'],
                'created_by' => auth()->id(),
                'status' => 'aktif',
                'kategori' => 'Lain-lain', // Set default kategori instead of null
            ];
            
            $bebanOperasional = BebanOperasional::create($data);
            $bebanOperasional->refresh();

            return response()->json([
                'success' => true,
                'message' => 'Beban Operasional berhasil ditambahkan',
                'data' => [
                    'id' => $bebanOperasional->id,
                    'kode' => $bebanOperasional->kode,
                    'nama_beban' => $bebanOperasional->nama_beban,
                    'budget_bulanan' => $bebanOperasional->budget_bulanan,
                    'budget_bulanan_formatted' => $bebanOperasional->budget_bulanan_formatted,
                    'status' => $bebanOperasional->status,
                    'status_badge' => $bebanOperasional->status_badge,
                ]
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $e->errors()
            ], 422);

        } catch (\Exception $e) {
            \Log::error('BebanOperasional Store Error: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Gagal menambahkan: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get beban operasional for editing
     */
    public function show($id)
    {
        try {
            $bebanOperasional = BebanOperasional::findOrFail($id);
            
            return response()->json([
                'success' => true,
                'data' => $bebanOperasional
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Data tidak ditemukan'
            ], 404);
        }
    }

    /**
     * Update beban operasional
     */
    public function update(Request $request, $id)
    {
        try {
            $bebanOperasional = BebanOperasional::findOrFail($id);

            $validated = $request->validate([
                'nama_beban' => 'required|string|max:255',
                'budget_bulanan' => 'required|numeric|min:0',
            ]);

            $bebanOperasional->update($validated);

            return response()->json([
                'success' => true,
                'message' => 'Beban Operasional berhasil diperbarui',
                'data' => [
                    'id' => $bebanOperasional->id,
                    'kode' => $bebanOperasional->kode,
                    'nama_beban' => $bebanOperasional->nama_beban,
                    'budget_bulanan' => $bebanOperasional->budget_bulanan,
                    'budget_bulanan_formatted' => $bebanOperasional->budget_bulanan_formatted,
                    'status' => $bebanOperasional->status,
                    'status_badge' => $bebanOperasional->status_badge,
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal memperbarui: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete beban operasional
     */
    public function destroy($id)
    {
        try {
            $bebanOperasional = BebanOperasional::findOrFail($id);
            
            // Check if used in transactions
            $usageCount = \App\Models\PembayaranBeban::where('beban_operasional_id', $id)->count();
            if ($usageCount > 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data tidak bisa dihapus karena sudah digunakan pada ' . $usageCount . ' transaksi.'
                ], 422);
            }
            
            $bebanOperasional->delete();

            return response()->json([
                'success' => true,
                'message' => 'Beban Operasional berhasil dihapus'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus: ' . $e->getMessage()
            ], 500);
        }
    }
}
