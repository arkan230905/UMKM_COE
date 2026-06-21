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
        $totalBiayaPerProduk = $prosesProduksis->sum(function($proses) {
            return $proses->biaya_per_produk;
        });
        $rataRataTarif = $totalProses > 0 ? $prosesProduksis->sum('tarif_per_produk') / $totalProses : 0;
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
        $jabatans = \App\Models\Kualifikasi::where('user_id', auth()->id())->orderBy('nama_kualifikasi')->get();
        return view('master-data.proses-produksi.create', compact('jabatans'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama_proses' => 'required|string|max:100',
            'jabatan_id' => 'required|exists:kualifikasis,id',
            'deskripsi' => 'nullable|string',
            'tarif_per_produk' => 'required|numeric|min:0',
            'jumlah_pegawai' => 'nullable|integer|min:0',
        ]);

        try {
            $jabatan = \App\Models\Kualifikasi::where('user_id', auth()->id())->findOrFail($validated['jabatan_id']);
            
            // Gunakan tarif_produk dari jabatan sebagai tarif per produk
            $tarifPerProduk = $jabatan->tarif_produk ?? $validated['tarif_per_produk'];
            $jumlahPegawai = $validated['jumlah_pegawai'] ?? 1;
            
            $createData = [
                'user_id'               => auth()->id(),
                'nama_proses'           => $validated['nama_proses'],
                'deskripsi'             => $validated['deskripsi'] ?? null,
                'tarif_per_produk'      => $tarifPerProduk,
                'jabatan_id'            => $validated['jabatan_id'],
                'jumlah_pegawai'        => $jumlahPegawai,
            ];

            $btkl = ProsesProduksi::create($createData);
            
            $totalBtkl = $tarifPerProduk * $jumlahPegawai;

            return redirect()->route('master-data.btkl.index')
                ->with('success', 'BTKL berhasil ditambahkan! Tarif per produk: Rp ' . number_format($tarifPerProduk, 0, ',', '.') . ' × ' . $jumlahPegawai . ' pegawai = Rp ' . number_format($totalBtkl, 0, ',', '.'));

        } catch (\Exception $e) {
            return back()->withInput()->with('error', 'Gagal menyimpan: ' . $e->getMessage());
        }
    }

    public function update(Request $request, ProsesProduksi $prosesProduksi)
    {
        if ($prosesProduksi->user_id != auth()->id()) { abort(404); }

        $validated = $request->validate([
            'nama_proses' => 'required|string|max:100',
            'jabatan_id' => 'required|exists:kualifikasis,id',
            'deskripsi' => 'nullable|string',
            'tarif_per_produk' => 'required|numeric|min:0',
            'jumlah_pegawai' => 'nullable|integer|min:0',
        ]);

        try {
            $jabatan = \App\Models\Kualifikasi::where('user_id', auth()->id())->findOrFail($validated['jabatan_id']);
            
            // Gunakan tarif_produk dari jabatan sebagai tarif per produk
            $tarifPerProduk = $jabatan->tarif_produk ?? $validated['tarif_per_produk'];
            $jumlahPegawai = $validated['jumlah_pegawai'] ?? $prosesProduksi->jumlah_pegawai ?? 1;
            
            $updateData = [
                'nama_proses'           => $validated['nama_proses'],
                'deskripsi'             => $validated['deskripsi'] ?? null,
                'tarif_per_produk'      => $tarifPerProduk,
                'jabatan_id'            => $validated['jabatan_id'],
                'jumlah_pegawai'        => $jumlahPegawai,
            ];

            $prosesProduksi->update($updateData);
            
            $totalBtkl = $tarifPerProduk * $jumlahPegawai;

            return redirect()->route('master-data.btkl.index')
                ->with('success', 'BTKL diperbarui! Tarif per produk: Rp ' . number_format($tarifPerProduk, 0, ',', '.') . ' × ' . $jumlahPegawai . ' pegawai = Rp ' . number_format($totalBtkl, 0, ',', '.'));
        } catch (\Exception $e) {
            return back()->withInput()->with('error', 'Gagal memperbarui: ' . $e->getMessage());
        }
    }

    // Fungsi lainnya (show, edit, destroy) tetap sama seperti sebelumnya...
    public function edit(ProsesProduksi $prosesProduksi)
    {
        if ($prosesProduksi->user_id != auth()->id()) { abort(404); }
        $jabatans = \App\Models\Kualifikasi::where('user_id', auth()->id())->orderBy('nama_kualifikasi')->get();
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