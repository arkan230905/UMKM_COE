<?php

namespace App\Http\Controllers;

use App\Models\Presensi;
use App\Models\Pegawai;
use Illuminate\Http\Request;

class PresensiController extends Controller
{
    public function index()
    {
        $presensis = Presensi::with('pegawai')->get();
        return view('master-data.presensi.index', compact('presensis'));
    }

    public function create()
    {
        $pegawais = Pegawai::all();
        return view('master-data.presensi.create', compact('pegawais'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'pegawai_id' => 'required|exists:pegawais,id',
            'tgl_presensi' => 'required|date',
            'jam_masuk' => 'required',
            'status' => 'required|in:Hadir,Absen,Izin,Sakit'
        ]);

        Presensi::create($request->all());

        return redirect()->route('master-data.presensi.index')->with('success', 'Data presensi berhasil ditambahkan.');
    }

    public function edit(Presensi $presensi)
    {
        $pegawais = Pegawai::all();
        return view('master-data.presensi.edit', compact('presensi', 'pegawais'));
    }

    public function update(Request $request, Presensi $presensi)
    {
        $request->validate([
            'pegawai_id' => 'required|exists:pegawais,id',
            'tgl_presensi' => 'required|date',
            'jam_masuk' => 'required',
            'status' => 'required|in:Hadir,Absen,Izin,Sakit'
        ]);

        $presensi->update($request->all());

        return redirect()->route('master-data.presensi.index')->with('success', 'Data presensi berhasil diperbarui.');
    }

    public function destroy(Presensi $presensi)
    {
        $presensi->delete();
        return redirect()->route('master-data.presensi.index')->with('success', 'Data presensi berhasil dihapus.');
    }
}
