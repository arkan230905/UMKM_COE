<?php

namespace App\Http\Controllers\MasterData;

use App\Http\Controllers\Controller;
use App\Models\TargetProduksi;
use App\Models\Produk;
use App\Services\TargetProduksiService;
use Illuminate\Http\Request;

class TargetProduksiController extends Controller
{
    protected $service;

    public function __construct(TargetProduksiService $service)
    {
        $this->service = $service;
    }

    /**
     * Display a listing of target produksi
     */
    public function index(Request $request)
    {
        $tahun = $request->get('tahun', now()->year);
        
        $targets = TargetProduksi::with(['produk', 'details'])
            ->where('user_id', auth()->id())
            ->when($tahun, fn($q) => $q->where('tahun', $tahun))
            ->orderBy('tahun', 'desc')
            ->orderBy('created_at', 'desc')
            ->get();

        $years = $this->getYearOptions();
        
        return view('master-data.target-produksi.index', compact('targets', 'years', 'tahun'));
    }

    /**
     * Show the form for creating new target produksi
     */
    public function create()
    {
        $produks = Produk::where('user_id', auth()->id())
            ->orderBy('nama_produk')
            ->get();
        
        $years = $this->getYearOptions();
        
        return view('master-data.target-produksi.create', compact('produks', 'years'));
    }

    /**
     * Store a newly created target produksi
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'tahun' => 'required|integer|min:2020|max:2100',
            'produk_id' => 'required|exists:produks,id',
            'total_target_tahunan' => 'required|integer|min:1',
            'details' => 'required|array|size:12',
            'details.*.bulan' => 'required|integer|between:1,12',
            'details.*.target_bulanan' => 'required|integer|min:0',
        ]);

        try {
            $target = $this->service->createTarget($validated);
            
            return redirect()
                ->route('master-data.target-produksi.index')
                ->with('success', 'Target produksi berhasil dibuat');
        } catch (\Exception $e) {
            return back()
                ->withInput()
                ->with('error', $e->getMessage());
        }
    }

    /**
     * Display the specified target produksi
     */
    public function show($id)
    {
        $target = TargetProduksi::with(['produk', 'details', 'logs'])
            ->where('user_id', auth()->id())
            ->findOrFail($id);
        
        $comparison = $this->service->getComparison($target);
        
        return view('master-data.target-produksi.show', compact('target', 'comparison'));
    }

    /**
     * Show the form for editing target produksi
     */
    public function edit($id)
    {
        $target = TargetProduksi::with(['produk', 'details'])
            ->where('user_id', auth()->id())
            ->findOrFail($id);
        
        $produks = Produk::where('user_id', auth()->id())
            ->orderBy('nama_produk')
            ->get();
        
        $years = $this->getYearOptions();
        
        return view('master-data.target-produksi.edit', compact('target', 'produks', 'years'));
    }

    /**
     * Update the specified target produksi
     */
    public function update(Request $request, $id)
    {
        $target = TargetProduksi::where('user_id', auth()->id())->findOrFail($id);
        
        $validated = $request->validate([
            'tahun' => 'required|integer|min:2020|max:2100',
            'produk_id' => 'required|exists:produks,id',
            'total_target_tahunan' => 'required|integer|min:1',
            'details' => 'required|array|size:12',
            'details.*.bulan' => 'required|integer|between:1,12',
            'details.*.target_bulanan' => 'required|integer|min:0',
        ]);

        try {
            $this->service->updateTarget($target, $validated);
            
            return redirect()
                ->route('master-data.target-produksi.index')
                ->with('success', 'Target produksi berhasil diperbarui');
        } catch (\Exception $e) {
            return back()
                ->withInput()
                ->with('error', $e->getMessage());
        }
    }

    /**
     * Remove the specified target produksi
     */
    public function destroy($id)
    {
        $target = TargetProduksi::where('user_id', auth()->id())->findOrFail($id);
        
        try {
            $this->service->deleteTarget($target);
            
            return redirect()
                ->route('master-data.target-produksi.index')
                ->with('success', 'Target produksi berhasil dihapus');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Get year options for dropdown
     */
    private function getYearOptions(): array
    {
        $currentYear = now()->year;
        $years = [];
        
        for ($i = $currentYear - 5; $i <= $currentYear + 5; $i++) {
            $years[$i] = $i;
        }
        
        return $years;
    }
}
