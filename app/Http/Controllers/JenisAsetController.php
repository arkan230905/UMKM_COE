<?php

namespace App\Http\Controllers;

use App\Models\JenisAset;
use App\Models\Aset;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class JenisAsetController extends Controller
{
    /**
     * Store a newly created jenis aset via AJAX
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nama' => 'required|string|max:255|unique:jenis_asets,nama,NULL,id,user_id,' . auth()->id(),
            'deskripsi' => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $jenisAset = JenisAset::create([
                'nama' => $request->nama,
                'deskripsi' => $request->deskripsi ?? 'Jenis aset kustom',
                'user_id' => auth()->id(), // 🔒 SECURITY: Set user_id for multi-tenant
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Jenis aset berhasil ditambahkan',
                'data' => [
                    'id' => $jenisAset->id,
                    'nama' => $jenisAset->nama,
                    'slug' => Str::slug($jenisAset->nama),
                    'deskripsi' => $jenisAset->deskripsi,
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
     * Remove the specified jenis aset
     */
    public function destroy($id)
    {
        try {
            $jenisAset = JenisAset::where('user_id', auth()->id())
                ->findOrFail($id);
            
            // Cek apakah "Aset Tetap" (tidak boleh dihapus)
            if ($jenisAset->nama === 'Aset Tetap' || $jenisAset->user_id === null) {
                return response()->json([
                    'success' => false,
                    'message' => 'Aset Tetap tidak dapat dihapus'
                ], 403);
            }
            
            // Cek apakah sudah digunakan
            $used = Aset::where('jenis_aset', Str::slug($jenisAset->nama))
                ->where('user_id', auth()->id())
                ->exists();
                
            if ($used) {
                return response()->json([
                    'success' => false,
                    'message' => 'Jenis aset ini sudah digunakan dan tidak dapat dihapus'
                ], 400);
            }
            
            $jenisAset->delete();
            
            return response()->json([
                'success' => true,
                'message' => 'Jenis aset berhasil dihapus'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
}