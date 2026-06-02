<?php

namespace App\Http\Controllers;

use App\Models\Produk;
// ✅ PERBAIKAN: Disable import BomJobCosting karena tabel bom_job_costings tidak ada
// use App\Models\BomJobCosting;
use App\Models\BiayaBahanBaku;
use App\Models\Btkl;
use App\Models\Bop;
use App\Models\Produksi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class HppController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Dashboard perhitungan HPP
     */
    public function index()
    {
        $userId = auth()->id();
        
        // Get semua produk user dengan data HPP
        $produks = Produk::where('user_id', $userId)
            ->with(['bomJobCosting', 'biayaBahanBaku'])
            ->orderBy('nama_produk')
            ->get();

        $produkStats = [];
        foreach ($produks as $produk) {
            $bom = $produk->bomJobCosting;
            $biayaBahan = $produk->biayaBahanBaku->sum('subtotal');
            
            $produkStats[] = [
                'produk' => $produk,
                'bom' => $bom,
                'total_biaya_bahan' => $biayaBahan,
                'harga_pokok' => $bom ? $bom->hpp_per_unit : 0,
                'margin' => $produk->harga_jual > 0 ? (($produk->harga_jual - ($bom ? $bom->hpp_per_unit : 0)) / $produk->harga_jual) * 100 : 0
            ];
        }

        return view('hpp.index', compact('produkStats'));
    }

    /**
     * Halaman detail perhitungan HPP untuk produk tertentu
    public function show($produkId)
    {
        $produk = Produk::where('id', $produkId)
            ->where('user_id', auth()->id())
            ->with(['bomJobCosting', 'biayaBahanBaku.bahanBaku.satuan'])
            ->firstOrFail();

        // Get komponen biaya bahan baku
        $biayaBahanBaku = $produk->biayaBahanBaku()
            ->with(['bahanBaku.satuan'])
            ->get();

        // Get komponen BTKL
        $btklComponents = [];
        if ($produk->bomJobCosting) {
            $btklComponents = $produk->bomJobCosting->btklSelections()
                ->with(['btkl'])
                ->get();
        }

        // Get komponen BOP
        $bopComponents = [];
        if ($produk->bomJobCosting) {
            $bopComponents = $produk->bomJobCosting->bopSelections()
                ->with(['bopProses'])
                ->get();
        }

        // Hitung total per komponen
        $totalBBB = $biayaBahanBaku->sum('subtotal');
        $totalBTKL = $btklComponents->sum(function($item) {
            return $item->jumlah * $item->tarif;
        });
        // Fixed: BomJobBopSelection has 'subtotal' field, not 'nominal'
        $totalBOP = $bopComponents->sum('subtotal');
        $totalHPP = $totalBBB + $totalBTKL + $totalBOP;

        return view('hpp.show', compact(
            'produk', 
            'biayaBahanBaku', 
            'btklComponents', 
            'bopComponents',
            'totalBBB',
            'totalBTKL', 
            'totalBOP',
            'totalHPP'
        ));
    }

    /**
     * Form perhitungan HPP untuk produk
     */
    public function create($produkId)
    {
        $produk = Produk::where('id', $produkId)
            ->where('user_id', auth()->id())
            ->firstOrFail();

        // Check if already has BOM
        if ($produk->bomJobCosting) {
            return redirect()->route('hpp.show', $produk->id)
                ->with('info', 'HPP untuk produk ini sudah ada');
        }

        // Get data yang dibutuhkan
        $biayaBahanBaku = $produk->biayaBahanBaku()
            ->with(['bahanBaku.satuan'])
            ->get();

        $btkls = Btkl::where('user_id', auth()->id())
            ->with(['jabatan'])
            ->get();

        $bops = Bop::where('user_id', auth()->id())
            ->get();

        return view('hpp.create', compact(
            'produk', 
            'biayaBahanBaku', 
            'btkls', 
            'bops'
        ));
    }

    /**
     * Simpan perhitungan HPP
     */
    public function store(Request $request, $produkId)
    {
        $produk = Produk::where('id', $produkId)
            ->where('user_id', auth()->id())
            ->firstOrFail();

        $request->validate([
            'jumlah_produk' => 'required|integer|min:1',
            'btkl_selected' => 'nullable|array',
            'btkl_selected.*' => 'exists:btkls,id',
            'bop_selected' => 'nullable|array',
            'bop_selected.*' => 'exists:bops,id',
        ]);

        DB::beginTransaction();
        try {
            // Create BOM Job Costing
            $bom = BomJobCosting::create([
                'user_id' => auth()->id(),
                'produk_id' => $produk->id,
                'jumlah_produk' => $request->jumlah_produk,
                'total_bbb' => 0,
                'total_btkl' => 0,
                'total_bop' => 0,
                'total_hpp' => 0,
                'keterangan' => $request->keterangan ?? 'Perhitungan HPP ' . Carbon::now()->format('d/m/Y')
            ]);

            // Calculate BBB from existing biaya bahan
            $totalBBB = $produk->biayaBahanBaku()->sum('subtotal');

            // Calculate BTKL
            $totalBTKL = 0;
            if ($request->btkl_selected) {
                foreach ($request->btkl_selected as $btklId) {
                    $btkl = Btkl::where('id', $btklId)
                        ->where('user_id', auth()->id())
                        ->first();
                    
                    if ($btkl) {
                        $subtotal = $btkl->biaya_per_produk * $request->jumlah_produk;
                        $totalBTKL += $subtotal;

                        // Save BTKL selection
                        $bom->btklSelections()->create([
                            'btkl_id' => $btklId,
                            'jumlah' => $request->jumlah_produk,
                            'tarif' => $btkl->biaya_per_produk,
                            'subtotal' => $subtotal
                        ]);
                    }
                }
            }

            // Calculate BOP
            $totalBOP = 0;
            if ($request->bop_selected) {
                foreach ($request->bop_selected as $bopId) {
                    $bop = Bop::where('id', $bopId)
                        ->where('user_id', auth()->id())
                        ->first();
                    
                    if ($bop) {
                        $nominal = $bop->nominal ?? 0;
                        $totalBOP += $nominal;

                        // Save BOP selection
                        $bom->bopSelections()->create([
                            'bop_id' => $bopId,
                            'nominal' => $nominal
                        ]);
                    }
                }
            }

            // Update totals
            $totalHPP = $totalBBB + $totalBTKL + $totalBOP;
            $hppPerUnit = $request->jumlah_produk > 0 ? $totalHPP / $request->jumlah_produk : 0;

            $bom->update([
                'total_bbb' => $totalBBB,
                'total_btkl' => $totalBTKL,
                'total_bop' => $totalBOP,
                'total_hpp' => $totalHPP
            ]);

            // Update product harga pokok
            $produk->update(['harga_pokok' => $hppPerUnit]);

            DB::commit();

            return redirect()->route('hpp.show', $produk->id)
                ->with('success', 'Perhitungan HPP berhasil disimpan. HPP per unit: Rp ' . number_format($hppPerUnit, 0, ',', '.'));

        } catch (\Exception $e) {
            DB::rollback();
            return back()->withInput()
                ->with('error', 'Gagal menyimpan perhitungan HPP: ' . $e->getMessage());
        }
    }

    /**
     * Rehitung HPP berdasarkan data terbaru
     */
    public function recalculate($produkId)
    {
        $produk = Produk::where('id', $produkId)
            ->where('user_id', auth()->id())
            ->with(['bomJobCosting'])
            ->firstOrFail();

        if (!$produk->bomJobCosting) {
            return back()->with('error', 'Belum ada data HPP untuk produk ini');
        }

        DB::beginTransaction();
        try {
            $bom = $produk->bomJobCosting;

            // Recalculate BBB
            $totalBBB = $produk->biayaBahanBaku()->sum('subtotal');

            // Recalculate BTKL
            $totalBTKL = $bom->btklSelections()->sum('subtotal');

            // Recalculate BOP
            $totalBOP = $bom->bopSelections()->sum('nominal');

            // Update totals
            $totalHPP = $totalBBB + $totalBTKL + $totalBOP;
            $hppPerUnit = $bom->jumlah_produk > 0 ? $totalHPP / $bom->jumlah_produk : 0;

            $bom->update([
                'total_bbb' => $totalBBB,
                'total_btkl' => $totalBTKL,
                'total_bop' => $totalBOP,
                'total_hpp' => $totalHPP
            ]);

            // Update product harga pokok
            $produk->update(['harga_pokok' => $hppPerUnit]);

            DB::commit();

            return back()->with('success', 'HPP berhasil dihitung ulang. HPP per unit: Rp ' . number_format($hppPerUnit, 0, ',', '.'));

        } catch (\Exception $e) {
            DB::rollback();
            return back()->with('error', 'Gagal menghitung ulang HPP: ' . $e->getMessage());
        }
    }

    /**
     * API untuk get data HPP produk
     */
    public function apiGetHpp($produkId)
    {
        $produk = Produk::where('id', $produkId)
            ->where('user_id', auth()->id())
            ->with(['bomJobCosting'])
            ->first();

        if (!$produk) {
            return response()->json(['error' => 'Produk tidak ditemukan'], 404);
        }

        $bom = $produk->bomJobCosting;
        
        return response()->json([
            'produk_id' => $produk->id,
            'nama_produk' => $produk->nama_produk,
            'harga_jual' => $produk->harga_jual,
            'harga_pokok' => $produk->harga_pokok,
            'has_bom' => $bom ? true : false,
            'bom' => $bom ? [
                'total_bbb' => $bom->total_bbb,
                'total_btkl' => $bom->total_btkl,
                'total_bop' => $bom->total_bop,
                'total_hpp' => $bom->total_hpp,
                'hpp_per_unit' => $bom->jumlah_produk > 0 ? $bom->total_hpp / $bom->jumlah_produk : 0,
                'jumlah_produk' => $bom->jumlah_produk
            ] : null
        ]);
    }
}
