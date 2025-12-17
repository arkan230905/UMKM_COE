<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Jabatan;
use Illuminate\Http\Request;

class JabatanController extends Controller
{
    public function show($id)
    {
        $jabatan = Jabatan::find($id);
        
        if (!$jabatan) {
            return response()->json(['error' => 'Jabatan tidak ditemukan'], 404);
        }
        
        return response()->json([
            'id' => $jabatan->id,
            'nama' => $jabatan->nama,
            'kategori' => $jabatan->kategori,
            'tunjangan' => $jabatan->tunjangan ?? 0,
            'asuransi' => $jabatan->asuransi ?? 0,
            'gaji_pokok' => $jabatan->gaji_pokok ?? $jabatan->gaji ?? 0,
            'tarif' => $jabatan->tarif ?? 0,
        ]);
    }
}
