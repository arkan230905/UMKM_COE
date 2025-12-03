<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PerusahaanController extends Controller
{
    // Menampilkan halaman tentang perusahaan
    public function edit()
    {
        // Ambil data perusahaan dari database, tabel 'perusahaan'
        $dataPerusahaan = DB::table('perusahaan')->first();

        // Jika belum ada data, buat default dummy
        if (!$dataPerusahaan) {
            $dataPerusahaan = (object)[
                'nama' => 'PT Pangalengan Sejahtera',
                'alamat' => 'Jl. Raya Pangalengan No.123, Bandung',
                'email' => 'info@ptps.co.id',
                'telepon' => '022-1234567',
                'kode' => null,
            ];
        }

        return view('tentang-perusahaan', compact('dataPerusahaan'));
    }

    // Update data perusahaan
    public function update(Request $request)
    {
        $request->validate([
            'nama' => 'required|string|max:255',
            'alamat' => 'required|string',
            'email' => 'required|email|max:255',
            'telepon' => 'required|string|max:20',
        ]);

        // Update data ke database
        $updated = DB::table('perusahaan')->update([
            'nama' => $request->nama,
            'alamat' => $request->alamat,
            'email' => $request->email,
            'telepon' => $request->telepon,
        ]);

        return redirect()->route('tentang-perusahaan')->with('success', 'Data perusahaan berhasil diperbarui.');
    }
}
