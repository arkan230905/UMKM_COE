<?php

namespace App\Http\Controllers;

use App\Models\Presensi;
use App\Models\Pegawai;
use Illuminate\Http\Request;
use Carbon\Carbon;

class PresensiController extends Controller
{
    /**
     * Tampilkan daftar presensi
     */
    public function index()
    {
        // Ambil semua presensi beserta relasi pegawai
        $presensis = Presensi::with('pegawai')->orderBy('tgl_presensi', 'desc')->get();
        return view('master-data.presensi.index', compact('presensis'));
    }

    /**
     * Form tambah presensi
     */
    public function create()
    {
        $pegawais = Pegawai::all(); // untuk select pegawai
        return view('master-data.presensi.create', compact('pegawais'));
    }

    /**
     * Simpan presensi baru
     */
    public function store(Request $request)
    {
        // Validasi input
        $validated = $request->validate([
            'pegawai_id' => 'required|exists:pegawais,id',
            'tgl_presensi' => 'required|date',
            'jam_masuk' => 'required|date_format:H:i',
            'jam_keluar' => 'required|date_format:H:i|after:jam_masuk',
        ]);

        // Hitung total jam kerja
        $jamMasuk = Carbon::parse($validated['jam_masuk']);
        $jamKeluar = Carbon::parse($validated['jam_keluar']);
        $validated['total_jam'] = $jamKeluar->floatDiffInHours($jamMasuk);

        // Simpan ke database
        Presensi::create($validated);

        return redirect()->route('master-data.presensi.index')
                         ->with('success', 'Presensi berhasil disimpan.');
    }

    /**
     * Form edit presensi
     */
    public function edit(Presensi $presensi)
    {
        $pegawais = Pegawai::all();
        return view('master-data.presensi.edit', compact('presensi', 'pegawais'));
    }

    /**
     * Update presensi
     */
    public function update(Request $request, Presensi $presensi)
    {
        $validated = $request->validate([
            'pegawai_id' => 'required|exists:pegawais,id',
            'tgl_presensi' => 'required|date',
            'jam_masuk' => 'required|date_format:H:i',
            'jam_keluar' => 'required|date_format:H:i|after:jam_masuk',
            'status' => 'required|in:Hadir,Absen,Izin,Sakit',
        ]);

        // Hitung total jam kerja
        $jamMasuk = Carbon::parse($validated['jam_masuk']);
        $jamKeluar = Carbon::parse($validated['jam_keluar']);
        $validated['total_jam'] = $jamKeluar->floatDiffInHours($jamMasuk);

        // Update presensi
        $presensi->update($validated);

        return redirect()->route('master-data.presensi.index')
                         ->with('success', 'Presensi berhasil diperbarui.');
    }

    /**
     * Hapus presensi
     */
    public function destroy(Presensi $presensi)
    {
        $presensi->delete();
        return redirect()->route('master-data.presensi.index')
                         ->with('success', 'Presensi berhasil dihapus.');
    }
}
