<?php

namespace App\Http\Controllers;

use App\Models\JenisAset;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class JenisAsetController extends Controller
{
    /**
     * Store a newly created jenis aset via AJAX
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nama' => 'required|string|max:255|unique:jenis_asets,nama',
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
                'deskripsi' => $request->deskripsi,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Jenis aset berhasil ditambahkan',
                'data' => [
                    'id' => $jenisAset->id,
                    'nama' => $jenisAset->nama,
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
}