<?php

namespace App\Http\Controllers;

use App\Models\KategoriAset;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class KategoriAsetController extends Controller
{
    /**
     * Store a newly created kategori aset via AJAX
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'jenis_aset_id' => 'required|exists:jenis_asets,id',
            'nama' => 'required|string|max:255',
            'kode' => 'required|string|max:20|unique:kategori_asets,kode',
            'umur_ekonomis' => 'nullable|integer|min:0|max:100',
            'tarif_penyusutan' => 'nullable|numeric|min:0|max:100',
            'disusutkan' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            // Generate kode otomatis jika tidak diisi
            $kode = $request->kode;
            if (empty($kode)) {
                $jenisAset = \App\Models\JenisAset::find($request->jenis_aset_id);
                $prefix = '';
                if (str_contains($jenisAset->nama, 'Tetap')) {
                    $prefix = 'AT';
                } elseif (str_contains($jenisAset->nama, 'Tidak Tetap')) {
                    $prefix = 'ATT';
                } elseif (str_contains($jenisAset->nama, 'Tidak Berwujud')) {
                    $prefix = 'ATB';
                } else {
                    $prefix = 'A';
                }
                
                $lastKategori = KategoriAset::where('kode', 'LIKE', $prefix . '-%')
                    ->orderBy('kode', 'desc')
                    ->first();
                
                if ($lastKategori) {
                    $lastNumber = (int) substr($lastKategori->kode, strlen($prefix) + 1);
                    $newNumber = $lastNumber + 1;
                } else {
                    $newNumber = 1;
                }
                
                $kode = $prefix . '-' . str_pad($newNumber, 2, '0', STR_PAD_LEFT);
            }

            $kategoriAset = KategoriAset::create([
                'jenis_aset_id' => $request->jenis_aset_id,
                'kode' => $kode,
                'nama' => $request->nama,
                'umur_ekonomis' => $request->umur_ekonomis ?? 0,
                'tarif_penyusutan' => $request->tarif_penyusutan ?? 0,
                'disusutkan' => $request->disusutkan ?? true,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Kategori aset berhasil ditambahkan',
                'data' => [
                    'id' => $kategoriAset->id,
                    'jenis_aset_id' => $kategoriAset->jenis_aset_id,
                    'kode' => $kategoriAset->kode,
                    'nama' => $kategoriAset->nama,
                    'umur_ekonomis' => $kategoriAset->umur_ekonomis,
                    'tarif_penyusutan' => $kategoriAset->tarif_penyusutan,
                    'disusutkan' => $kategoriAset->disusutkan,
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