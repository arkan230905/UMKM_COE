<?php

namespace App\Http\Controllers;

use App\Models\ProsesProduksi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProsesProduksiController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $prosesProduksis = ProsesProduksi::with(['jabatan.pegawais'])
            ->where('user_id', auth()->id())
            ->orderBy('kode_proses')
            ->paginate(10);
        
        $totalProses = $prosesProduksis->count();
        $totalBiayaPerProduk = $prosesProduksis->sum('biaya_per_produk');
        $rataRataTarif = $totalProses > 0 ? $prosesProduksis->sum('tarif_btkl') / $totalProses : 0;
        $rataRataBiayaPerUnit = $totalProses > 0 ? $totalBiayaPerProduk / $totalProses : 0;

        $statistics = [
            'total_proses' => $totalProses,
            'total_biaya_per_produk' => $totalBiayaPerProduk,
            'rata_rata_tarif' => $rataRataTarif,
            'rata_rata_biaya_per_unit' => $rataRataBiayaPerUnit,
        ];
        
        return view('master-data.proses-produksi.index', compact('prosesProduksis', 'statistics'));
    }

    public function create()
    {
        $jabatans = \App\Models\Jabatan::where('user_id', auth()->id())->orderBy('nama')->get();
        return view('master-data.proses-produksi.create', compact('jabatans'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama_proses' => 'required|string|max:100',
            'jabatan_id' => 'required|exists:jabatans,id',
            'deskripsi' => 'nullable|string',
            'tarif_btkl' => 'required|numeric|min:0',
        ]);

        try {
            $jabatan = \App\Models\Jabatan::where('user_id', auth()->id())->findOrFail($validated['jabatan_id']);
            $jumlahPegawai = \App\Models\Pegawai::where('jabatan', $jabatan->nama)->count();
            $expectedTarifBTKL = $jabatan->tarif * $jumlahPegawai;
            
            if ($jumlahPegawai === 0) {
                return back()->withInput()->with('error', 'Jabatan "' . $jabatan->nama . '" belum memiliki pegawai.');
            }
            
            // Hapus 'satuan_btkl' dan 'kapasitas_per_jam' karena kolom tidak ada di DB
            $createData = [
                'user_id'               => auth()->id(),
                'nama_proses'           => $validated['nama_proses'],
                'deskripsi'             => $validated['deskripsi'] ?? null,
                'tarif_btkl'            => $expectedTarifBTKL,
                'jabatan_id'            => $validated['jabatan_id'],
                'biaya_btkl_per_produk' => 0,        
            ];

            $btkl = ProsesProduksi::create($createData);

            return redirect()->route('master-data.btkl.index')
                ->with('success', 'BTKL berhasil ditambahkan! Tarif: Rp ' . number_format($expectedTarifBTKL, 0, ',', '.') . '/produk');

        } catch (\Exception $e) {
            return back()->withInput()->with('error', 'Gagal menyimpan: ' . $e->getMessage());
        }
    }

    public function update(Request $request, ProsesProduksi $prosesProduksi)
    {
        if ($prosesProduksi->user_id != auth()->id()) { abort(404); }

        $validated = $request->validate([
            'nama_proses' => 'required|string|max:100',
            'jabatan_id' => 'required|exists:jabatans,id',
            'deskripsi' => 'nullable|string',
            'tarif_btkl' => 'required|numeric|min:0',
        ]);

        try {
            $jabatan = \App\Models\Jabatan::where('user_id', auth()->id())->findOrFail($validated['jabatan_id']);
            $jumlahPegawai = \App\Models\Pegawai::where('jabatan', $jabatan->nama)->count();
            $expectedTarifBTKL = $jabatan->tarif * $jumlahPegawai;
            
            $updateData = [
                'nama_proses'           => $validated['nama_proses'],
                'deskripsi'             => $validated['deskripsi'] ?? null,
                'tarif_btkl'            => $expectedTarifBTKL,
                'jabatan_id'            => $validated['jabatan_id'],
            ];

            $prosesProduksi->update($updateData);

            return redirect()->route('master-data.btkl.index')
                ->with('success', 'BTKL diperbarui! Tarif: Rp ' . number_format($expectedTarifBTKL, 0, ',', '.') . '/produk');
        } catch (\Exception $e) {
            return back()->withInput()->with('error', 'Gagal memperbarui: ' . $e->getMessage());
        }
    }

    // Fungsi lainnya (show, edit, destroy) tetap sama seperti sebelumnya...
    public function edit(ProsesProduksi $prosesProduksi)
    {
        if ($prosesProduksi->user_id != auth()->id()) { abort(404); }
        $jabatans = \App\Models\Jabatan::where('user_id', auth()->id())->orderBy('nama')->get();
        return view('master-data.proses-produksi.edit', compact('prosesProduksi', 'jabatans'));
    }

    public function show(ProsesProduksi $prosesProduksi)
    {
        if ($prosesProduksi->user_id != auth()->id()) { abort(404); }
        $prosesProduksi->load('bomProses.bom.produk');
        return view('master-data.proses-produksi.show', compact('prosesProduksi'));
    }

    public function destroy(ProsesProduksi $prosesProduksi)
    {
        if ($prosesProduksi->user_id != auth()->id()) { abort(404); }
        try {
            $prosesProduksi->delete();
            return redirect()->route('master-data.btkl.index')->with('success', 'BTKL berhasil dihapus');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }
}