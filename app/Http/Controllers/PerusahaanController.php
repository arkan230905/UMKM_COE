<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Perusahaan;

class PerusahaanController extends Controller
{
    // Menampilkan halaman tentang perusahaan
    public function index()
    {
        // Debug: Log yang dipanggil
        \Log::info('PerusahaanController::index() called');
        \Log::info('Current user: ' . auth()->user()->name . ' (' . auth()->user()->role . ')');
        \Log::info('Request URL: ' . request()->fullUrl());
        
        // Ambil data perusahaan dari database, tabel 'perusahaan'
        $dataPerusahaan = Perusahaan::first();

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

        \Log::info('Returning view: tentang-perusahaan.index');
        return view('tentang-perusahaan.index', compact('dataPerusahaan'));
    }

    // Menampilkan form edit perusahaan
    public function edit()
    {
        // Cek apakah user adalah owner
        if (auth()->user()->role !== 'owner') {
            abort(403, 'Hanya owner yang dapat mengedit data perusahaan.');
        }
        
        // Cek apakah ada data perusahaan, jika tidak ada redirect ke index
        $dataPerusahaan = Perusahaan::first();
        if (!$dataPerusahaan) {
            return redirect('/tentang-perusahaan')->with('info', 'Silakan buat data perusahaan terlebih dahulu.');
        }
        
        // Ambil data perusahaan dari database, tabel 'perusahaan'
        $dataPerusahaan = Perusahaan::first();

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

        return view('tentang-perusahaan.edit', compact('dataPerusahaan'));
    }

    // Update data perusahaan
    public function update(Request $request)
    {
        // Cek apakah user adalah owner
        if (auth()->user()->role !== 'owner') {
            abort(403, 'Hanya owner yang dapat mengedit data perusahaan.');
        }
        
        $request->validate([
            'nama' => 'required|string|max:255',
            'alamat' => 'required|string',
            'email' => 'required|email|max:255',
            'telepon' => 'required|string|max:20',
        ]);

        // Update data ke database
        $perusahaan = Perusahaan::first();
        
        if ($perusahaan) {
            $perusahaan->update([
                'nama' => $request->nama,
                'alamat' => $request->alamat,
                'email' => $request->email,
                'telepon' => $request->telepon,
            ]);
        } else {
            // Jika belum ada, buat baru
            Perusahaan::create([
                'nama' => $request->nama,
                'alamat' => $request->alamat,
                'email' => $request->email,
                'telepon' => $request->telepon,
                'kode' => Perusahaan::generateKode(),
            ]);
        }

        return redirect('/tentang-perusahaan/detail')->with('success', "Data perusahaan '{$perusahaan->nama}' berhasil diperbarui.");
    }
}
