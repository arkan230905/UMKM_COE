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
        $userId = auth()->id();
        $jabatans = \App\Models\Jabatan::where('user_id', $userId)
            ->where('kategori', 'btkl')
            ->orderBy('nama')
            ->get();
        
        // Get employee data for JavaScript
        $employeeData = collect();
        foreach ($jabatans as $jabatan) {
            $pegawaiCount = \DB::table('pegawais')
                ->where('user_id', $userId)
                ->where('jabatan_id', $jabatan->id)
                ->count();
                
            $employeeData->push([
                'id'            => $jabatan->id,
                'nama'          => $jabatan->nama,
                'pegawai_count' => $pegawaiCount,
                'tarif'         => $jabatan->tarif ?? 0,
                'tarif_produk'  => $jabatan->tarif_produk ?? 0,
            ]);
        }
        
        // Generate next code from proses_produksis table (which has the unique constraint)
        $lastProses = ProsesProduksi::where('user_id', $userId)
            ->where('kode_proses', 'LIKE', 'PRO-%')
            ->orderByRaw('CAST(SUBSTRING(kode_proses, 5) AS UNSIGNED) DESC')
            ->first();
        
        if ($lastProses && $lastProses->kode_proses) {
            $lastNumber = (int) substr($lastProses->kode_proses, 4);
            $nextNumber = $lastNumber + 1;
        } else {
            $nextNumber = 1;
        }
        
        $nextKode = 'PRO-' . str_pad($nextNumber, 3, '0', STR_PAD_LEFT);
        $jabatanBtkl = $jabatans;
        $satuanOptions = ['Jam', 'Unit', 'Batch'];
        
        return view('master-data.btkl.create', compact('jabatans', 'jabatanBtkl', 'nextKode', 'satuanOptions', 'employeeData'));
    }

    public function store(Request $request)
    {
        $userId = auth()->id();
        
        $validated = $request->validate([
            'nama_btkl' => 'required|string|max:100',
            'jabatan_id' => 'required|exists:jabatans,id',
            'deskripsi_proses' => 'nullable|string',
            'tarif_per_produk' => 'required|numeric|min:0',
            'jumlah_pegawai' => 'nullable|integer|min:0',
            'satuan' => 'required|in:Jam,Unit,Batch',
        ]);

        try {
            DB::beginTransaction();
            
            $jabatan = \App\Models\Jabatan::where('user_id', $userId)->findOrFail($validated['jabatan_id']);
            
            // Gunakan tarif_produk dari jabatan sebagai tarif per produk
            $tarifPerProduk = $jabatan->tarif_produk ?? $validated['tarif_per_produk'];
            $jumlahPegawai = $validated['jumlah_pegawai'] ?? 1;
            
            // Generate unique kode_proses
            $lastProses = ProsesProduksi::where('user_id', $userId)
                ->where('kode_proses', 'LIKE', 'PRO-%')
                ->orderByRaw('CAST(SUBSTRING(kode_proses, 5) AS UNSIGNED) DESC')
                ->first();
            
            if ($lastProses && $lastProses->kode_proses) {
                $lastNumber = (int) substr($lastProses->kode_proses, 4);
                $nextNumber = $lastNumber + 1;
            } else {
                $nextNumber = 1;
            }
            
            $kodeProses = 'PRO-' . str_pad($nextNumber, 3, '0', STR_PAD_LEFT);
            
            $createData = [
                'user_id'               => $userId,
                'kode_proses'           => $kodeProses,
                'nama_proses'           => $validated['nama_btkl'],
                'deskripsi'             => $validated['deskripsi_proses'] ?? null,
                'tarif_per_produk'      => $tarifPerProduk,
                'jabatan_id'            => $validated['jabatan_id'],
                'jumlah_pegawai'        => $jumlahPegawai,
            ];

            $prosesProduksi = ProsesProduksi::create($createData);
            
            // Create corresponding Btkl record
            \App\Models\Btkl::create([
                'user_id'           => $userId,
                'kode_proses'       => $kodeProses,
                'nama_btkl'         => $validated['nama_btkl'],
                'jabatan_id'        => $validated['jabatan_id'],
                'satuan'            => $validated['satuan'],
                'deskripsi_proses'  => $validated['deskripsi_proses'] ?? null,
                'tarif_per_jam'     => $tarifPerProduk * $jumlahPegawai, // Total BTKL
            ]);
            
            DB::commit();
            
            $totalBtkl = $tarifPerProduk * $jumlahPegawai;

            return redirect()->route('master-data.btkl.index')
                ->with('success', 'BTKL berhasil ditambahkan! Tarif per produk: Rp ' . number_format($tarifPerProduk, 0, ',', '.') . ' × ' . $jumlahPegawai . ' pegawai = Rp ' . number_format($totalBtkl, 0, ',', '.'));

        } catch (\Exception $e) {
            DB::rollBack();
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
            'tarif_per_produk' => 'required|numeric|min:0',
            'jumlah_pegawai' => 'nullable|integer|min:0',
        ]);

        try {
            $jabatan = \App\Models\Jabatan::where('user_id', auth()->id())->findOrFail($validated['jabatan_id']);
            
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