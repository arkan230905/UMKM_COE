<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Penggajian;
use App\Models\Pegawai;
use App\Models\Presensi;
use Carbon\Carbon;

class PenggajianController extends Controller
{
    /**
     * Tampilkan daftar penggajian.
     */
    public function index()
    {
        $penggajians = Penggajian::with('pegawai')->get();
        return view('transaksi.penggajian.index', compact('penggajians'));
    }

    /**
     * Form tambah penggajian baru.
     */
    public function create()
    {
        $pegawai = Pegawai::all();
        return view('transaksi.penggajian.create', compact('pegawai'));
    }

    /**
     * Simpan data penggajian baru.
     */
    public function store(Request $request)
{
    $validated = $request->validate([
        'pegawai_id' => 'required|exists:pegawais,id',
        'tunjangan' => 'nullable|numeric',
        'potongan' => 'nullable|numeric',
        'tanggal_penggajian' => 'required|date',
    ]);

    // Ambil data pegawai
    $pegawai = \App\Models\Pegawai::findOrFail($request->pegawai_id);

    // Tentukan bulan dan tahun dari tanggal penggajian
    $bulanIni = \Carbon\Carbon::parse($request->tanggal_penggajian)->format('m');
    $tahunIni = \Carbon\Carbon::parse($request->tanggal_penggajian)->format('Y');

    // Hitung total jam kerja dari presensi (selisih jam keluar - jam masuk)
    $presensiPegawai = Presensi::where('pegawai_id', $pegawai->id)
    ->whereYear('tgl_presensi', $tahunIni)
    ->whereMonth('tgl_presensi', $bulanIni)
    ->get();

    $totalJamKerja = 0;

    foreach ($presensiPegawai as $p) {
        if ($p->jam_masuk && $p->jam_keluar) {
        $jamMasuk = Carbon::createFromFormat('H:i:s', $p->jam_masuk);
        $jamKeluar = Carbon::createFromFormat('H:i:s', $p->jam_keluar);
        $totalJamKerja += $jamKeluar->diffInHours($jamMasuk);
        }
    }

    // Hitung gaji pokok berdasarkan jam kerja
    // Asumsinya: kolom 'gaji' di tabel pegawai = gaji per jam
    $gajiPokok = $pegawai->gaji;

    // Hitung total gaji keseluruhan
    $tunjangan = $request->tunjangan ?? 0;
    $potongan = $request->potongan ?? 0;
    $totalGaji = $gajiPokok + $tunjangan - $potongan;

    // Simpan ke database
    \App\Models\Penggajian::create([
        'pegawai_id' => $pegawai->id,
        'gaji_pokok' => $gajiPokok,
        'tunjangan' => $tunjangan,
        'potongan' => $potongan,
        'total_gaji' => $totalGaji,
        'tanggal_penggajian' => $request->tanggal_penggajian,
    ]);

    return redirect()->route('transaksi.penggajian.index')
        ->with('success', 'Data penggajian berhasil disimpan.');
    }

}