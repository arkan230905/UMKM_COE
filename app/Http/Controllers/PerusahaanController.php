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

    // Update informasi bank
    public function updateBankInfo(Request $request)
    {
        // Cek apakah user adalah owner
        if (auth()->user()->role !== 'owner') {
            abort(403, 'Hanya owner yang dapat mengedit informasi bank.');
        }

        try {
            DB::transaction(function () use ($request) {
                $banks = $request->input('banks', []);
                
                foreach ($banks as $bankData) {
                    if (isset($bankData['coa_id'])) {
                        $coa = \App\Models\Coa::find($bankData['coa_id']);
                        if ($coa) {
                            $coa->update([
                                'nomor_rekening' => $bankData['nomor_rekening'] ?? null,
                                'atas_nama' => $bankData['atas_nama'] ?? null,
                            ]);
                        }
                    }
                }
            });

            return redirect()->back()->with('success', 'Informasi bank berhasil diperbarui.');
        } catch (\Exception $e) {
            \Log::error('Error updating bank info: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Terjadi kesalahan saat memperbarui informasi bank.');
        }
    }

    // Update single bank field via AJAX
    public function updateBankField(Request $request)
    {
        // Cek apakah user adalah owner
        if (auth()->user()->role !== 'owner') {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $request->validate([
            'coa_id' => 'required|exists:coas,id',
            'field' => 'required|in:nomor_rekening,atas_nama',
            'value' => 'nullable|string|max:255',
        ]);

        try {
            $coa = \App\Models\Coa::find($request->coa_id);
            if ($coa) {
                $coa->update([
                    $request->field => $request->value
                ]);

                return response()->json([
                    'success' => true,
                    'message' => 'Data berhasil diperbarui',
                    'value' => $request->value
                ]);
            }

            return response()->json(['success' => false, 'message' => 'COA tidak ditemukan'], 404);
        } catch (\Exception $e) {
            \Log::error('Error updating bank field: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Terjadi kesalahan'], 500);
        }
    }
}
