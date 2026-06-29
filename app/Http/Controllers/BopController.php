<?php

namespace App\Http\Controllers;

use App\Models\Bop;
use App\Models\Coa;
use App\Models\Produk;
use App\Models\TargetProduksi;
use App\Models\TargetProduksiDetail;
use Illuminate\Http\Request;
use Carbon\Carbon;

class BopController extends Controller
{
    /**
     * Display BOP list with periode filter
     */
    public function index(Request $request)
    {
        // Default periode = bulan sekarang
        $periode = $request->get('periode', now()->format('Y-m'));
        
        // Get BOP data for selected periode
        $bops = Bop::with(['produk', 'coa'])
            ->where('user_id', auth()->id())
            ->where('periode', $periode)
            ->orderBy('created_at', 'desc')
            ->get();
        
        // Calculate totals
        $totalBudget = $bops->sum('budget');
        $totalAktual = $bops->sum('aktual');
        
        // Get available periodes (distinct from database)
        $availablePeriodes = Bop::where('user_id', auth()->id())
            ->select('periode')
            ->distinct()
            ->whereNotNull('periode')
            ->orderBy('periode', 'desc')
            ->pluck('periode');
        
        // Add current month if not exists
        if (!$availablePeriodes->contains($periode)) {
            $availablePeriodes->prepend($periode);
        }
        
        return view('master-data.bop.index', compact(
            'bops',
            'periode',
            'totalBudget',
            'totalAktual',
            'availablePeriodes'
        ));
    }

    /**
     * Show create form
     */
    public function create()
    {
        // Get products for selection
        $produks = Produk::where('user_id', auth()->id())
            ->orderBy('nama_produk')
            ->get();
        
        // Get COA for BOP (expense accounts)
        $akunBeban = Coa::where('user_id', auth()->id())
            ->where(function($q){
                $q->whereIn('tipe_akun', ['Beban', 'Biaya'])
                  ->orWhere('kode_akun', 'like', '5%')
                  ->orWhere('kode_akun', 'like', '6%');
            })
            ->orderBy('kode_akun')
            ->get();
        
        // Default periode = bulan sekarang
        $defaultPeriode = now()->format('Y-m');
        
        return view('master-data.bop.create', compact('produks', 'akunBeban', 'defaultPeriode'));
    }

    /**
     * Store new BOP
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'produk_id' => 'required|exists:produks,id',
            'periode' => 'required|date_format:Y-m',
            'coa_id' => 'required|exists:coas,id',
            'budget' => 'required|numeric|min:0',
            'keterangan' => 'nullable|string|max:500',
        ], [
            'produk_id.required' => 'Produk harus dipilih',
            'periode.required' => 'Periode harus diisi',
            'coa_id.required' => 'Akun beban harus dipilih',
            'budget.required' => 'Budget harus diisi',
        ]);
        
        // Get COA data
        $coa = Coa::where('id', $validated['coa_id'])
            ->where('user_id', auth()->id())
            ->firstOrFail();
        
        // Get jumlah produksi from target_produksi_detail
        $jumlahProduksi = $this->getJumlahProduksi($validated['produk_id'], $validated['periode']);
        
        if ($jumlahProduksi <= 0) {
            return back()->withInput()->with('warning', 
                'Peringatan: Tidak ada target produksi untuk produk ini di bulan ' . $validated['periode'] . 
                '. BOP tetap dibuat dengan jumlah produksi 0. Silakan set target produksi terlebih dahulu.'
            );
        }
        
        // Create BOP
        Bop::create([
            'user_id' => auth()->id(),
            'produk_id' => $validated['produk_id'],
            'periode' => $validated['periode'],
            'jumlah_produksi' => $jumlahProduksi,
            'coa_id' => $validated['coa_id'],
            'kode_akun' => $coa->kode_akun,
            'nama_akun' => $coa->nama_akun,
            'budget' => $validated['budget'],
            'aktual' => 0,
            'keterangan' => $validated['keterangan'],
            'is_active' => true,
        ]);
        
        return redirect()->route('master-data.bop.index', ['periode' => $validated['periode']])
            ->with('success', 'BOP berhasil ditambahkan');
    }

    /**
     * Show edit form
     */
    public function edit(Bop $bop)
    {
        // Check ownership
        if ($bop->user_id != auth()->id()) {
            abort(404);
        }
        
        // Get products for selection
        $produks = Produk::where('user_id', auth()->id())
            ->orderBy('nama_produk')
            ->get();
        
        // Get COA for BOP
        $akunBeban = Coa::where('user_id', auth()->id())
            ->where(function($q){
                $q->whereIn('tipe_akun', ['Beban', 'Biaya'])
                  ->orWhere('kode_akun', 'like', '5%')
                  ->orWhere('kode_akun', 'like', '6%');
            })
            ->orderBy('kode_akun')
            ->get();
        
        return view('master-data.bop.edit', compact('bop', 'produks', 'akunBeban'));
    }

    /**
     * Update BOP
     */
    public function update(Request $request, Bop $bop)
    {
        // Check ownership
        if ($bop->user_id != auth()->id()) {
            abort(404);
        }
        
        $validated = $request->validate([
            'produk_id' => 'required|exists:produks,id',
            'periode' => 'required|date_format:Y-m',
            'coa_id' => 'required|exists:coas,id',
            'budget' => 'required|numeric|min:0',
            'keterangan' => 'nullable|string|max:500',
        ]);
        
        // Get COA data
        $coa = Coa::where('id', $validated['coa_id'])
            ->where('user_id', auth()->id())
            ->firstOrFail();
        
        // Get jumlah produksi from target_produksi_detail
        $jumlahProduksi = $this->getJumlahProduksi($validated['produk_id'], $validated['periode']);
        
        // Update BOP
        $bop->update([
            'produk_id' => $validated['produk_id'],
            'periode' => $validated['periode'],
            'jumlah_produksi' => $jumlahProduksi,
            'coa_id' => $validated['coa_id'],
            'kode_akun' => $coa->kode_akun,
            'nama_akun' => $coa->nama_akun,
            'budget' => $validated['budget'],
            'keterangan' => $validated['keterangan'],
        ]);
        
        return redirect()->route('master-data.bop.index', ['periode' => $validated['periode']])
            ->with('success', 'BOP berhasil diperbarui');
    }

    /**
     * Delete BOP
     */
    public function destroy(Bop $bop)
    {
        // Check ownership
        if ($bop->user_id != auth()->id()) {
            abort(404);
        }
        
        $periode = $bop->periode;
        $bop->delete();
        
        return redirect()->route('master-data.bop.index', ['periode' => $periode])
            ->with('success', 'BOP berhasil dihapus');
    }

    /**
     * API: Get jumlah produksi for selected product and periode
     */
    public function getJumlahProduksiApi(Request $request)
    {
        $produkId = $request->get('produk_id');
        $periode = $request->get('periode'); // Format: YYYY-MM
        
        if (!$produkId || !$periode) {
            return response()->json(['error' => 'produk_id dan periode harus diisi'], 400);
        }
        
        $jumlahProduksi = $this->getJumlahProduksi($produkId, $periode);
        
        return response()->json([
            'jumlah_produksi' => $jumlahProduksi,
            'jumlah_produksi_formatted' => number_format($jumlahProduksi, 0, ',', '.')
        ]);
    }

    /**
     * Helper: Get jumlah produksi from target_produksi_detail
     */
    private function getJumlahProduksi($produkId, $periode)
    {
        // Parse periode (YYYY-MM) to get year and month
        [$tahun, $bulan] = explode('-', $periode);
        $bulan = (int) $bulan;
        
        // Find target produksi for this product and year
        $targetProduksi = TargetProduksi::where('user_id', auth()->id())
            ->where('produk_id', $produkId)
            ->where('tahun', $tahun)
            ->first();
        
        if (!$targetProduksi) {
            return 0;
        }
        
        // Find detail for specific month
        $targetDetail = TargetProduksiDetail::where('target_produksi_id', $targetProduksi->id)
            ->where('bulan', $bulan)
            ->first();
        
        return $targetDetail ? $targetDetail->qty_produksi : 0;
    }
}
