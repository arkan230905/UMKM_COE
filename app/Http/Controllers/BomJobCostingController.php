<?php

namespace App\Http\Controllers;

use App\Models\BomJobCosting;
use App\Models\Produk;
use App\Models\BahanBaku;
use App\Models\BahanPendukung;
use App\Models\ProsesProduksi;
use App\Models\Bop;
use App\Models\Satuan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BomJobCostingController extends Controller
{
    public function __construct() { $this->middleware('auth'); }

    /**
     * Halaman Hitung HPP untuk produk tertentu
     */
    public function create($produkId)
    {
        $produk = Produk::findOrFail($produkId);
        
        // Cek apakah sudah ada BOM untuk produk ini
        $bom = $produk->bomJobCosting;
        if ($bom) {
            return redirect()->route('master-data.bom-job-costing.edit', $produk->id);
        }
        
        $bahanBakus = BahanBaku::with('satuanRelation')->orderBy('nama_bahan')->get();
        $bahanPendukungs = BahanPendukung::active()->with('satuanRelation')->orderBy('nama_bahan')->get();
        $prosesProduksis = ProsesProduksi::orderBy('nama_proses')->get();
        $bops = Bop::active()->orderBy('nama_akun')->get();
        $satuans = Satuan::orderBy('kode')->get();
        
        // Group units by category for conversion
        $satuansByCategory = $satuans->groupBy('kategori');
        
        return view('master-data.bom-job-costing.create', compact('produk', 'bahanBakus', 'bahanPendukungs', 'prosesProduksis', 'bops', 'satuans', 'satuansByCategory'));
    }

    public function store(Request $request, $produkId)
    {
        $produk = Produk::findOrFail($produkId);
        
        // Debug logging
        \Log::info('=== BOM STORE DEBUG ===');
        \Log::info('Request data:', $request->all());
        
        // Cek apakah sudah ada BOM
        if ($produk->bomJobCosting) {
            return redirect()->route('master-data.bom-job-costing.edit', $produk->id)
                ->with('error', 'BOM untuk produk ini sudah ada');
        }
        
        $request->validate([
            'jumlah_produk' => 'required|integer|min:1',
        ]);

        // Validasi: Pastikan ada minimal satu komponen biaya yang diisi
        $hasBBB = !empty($request->bbb_id) && array_filter($request->bbb_id);
        $hasBP = !empty($request->bp_id) && array_filter($request->bp_id);
        $hasBOP = !empty($request->bop_nominal) && array_sum(array_filter($request->bop_nominal));
        
        if (!$hasBBB && !$hasBP && !$hasBOP) {
            return back()->withInput()->with('error', 'Tidak dapat menyimpan BOM! Anda belum menghitung biaya bahan apapun. Silakan isi minimal salah satu dari: Biaya Bahan Baku (BBB), Bahan Penolong/Pendukung, atau Biaya Overhead Pabrik (BOP).');
        }

        DB::beginTransaction();
        try {
            $bom = BomJobCosting::create([
                'produk_id' => $produk->id,
                'jumlah_produk' => $request->jumlah_produk,
            ]);
            
            \Log::info('BOM created with ID: ' . $bom->id);

            $this->saveDetails($bom, $request);
            \Log::info('saveDetails completed');
            
            $bom->recalculate();
            \Log::info('recalculate completed');
            
            DB::commit();
            return redirect()->route('master-data.bom-job-costing.show', $produk->id)
                ->with('success', 'BOM untuk ' . $produk->nama_produk . ' berhasil disimpan dengan total HPP Rp ' . number_format($bom->fresh()->hpp_per_unit, 0, ',', '.'));
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('BOM Store Error: ' . $e->getMessage());
            return back()->withInput()->with('error', 'Gagal menyimpan: ' . $e->getMessage());
        }
    }

    public function show($produkId)
    {
        $produk = Produk::findOrFail($produkId);
        $bom = $produk->bomJobCosting;
        
        if (!$bom) {
            return redirect()->route('master-data.bom-job-costing.create', $produk->id);
        }
        
        $bom->load([
            'detailBBB.bahanBaku.satuanRelation',
            'detailBTKL.prosesProduksi',
            'detailBahanPendukung.bahanPendukung.satuanRelation',
            'detailBOP.bop'
        ]);
        
        return view('master-data.bom-job-costing.show', compact('produk', 'bom'));
    }

    public function edit($produkId)
    {
        $produk = Produk::findOrFail($produkId);
        $bom = $produk->bomJobCosting;
        
        if (!$bom) {
            return redirect()->route('master-data.bom-job-costing.create', $produk->id);
        }
        
        $bom->load(['detailBBB', 'detailBTKL', 'detailBahanPendukung', 'detailBOP']);
        $bahanBakus = BahanBaku::with('satuanRelation')->orderBy('nama_bahan')->get();
        $bahanPendukungs = BahanPendukung::active()->with('satuanRelation')->orderBy('nama_bahan')->get();
        $btkls = \App\Models\Btkl::with('jabatan')->active()->orderBy('kode_proses')->get();
        $bops = Bop::active()->orderBy('nama_akun')->get();
        $satuans = Satuan::orderBy('kode')->get();
        
        return view('master-data.bom-job-costing.edit', compact('produk', 'bom', 'bahanBakus', 'bahanPendukungs', 'btkls', 'bops', 'satuans'));
    }

    public function update(Request $request, $produkId)
    {
        $produk = Produk::findOrFail($produkId);
        $bom = $produk->bomJobCosting;
        
        if (!$bom) {
            return redirect()->route('master-data.bom-job-costing.create', $produk->id);
        }
        
        $request->validate([
            'jumlah_produk' => 'required|integer|min:1',
        ]);

        DB::beginTransaction();
        try {
            $bom->update(['jumlah_produk' => $request->jumlah_produk]);

            // Clear old details
            $bom->detailBBB()->delete();
            $bom->detailBTKL()->delete();
            $bom->detailBahanPendukung()->delete();
            $bom->detailBOP()->delete();

            $this->saveDetails($bom, $request);
            $bom->recalculate();
            
            DB::commit();
            return redirect()->route('master-data.bom-job-costing.show', $produk->id)
                ->with('success', 'BOM untuk ' . $produk->nama_produk . ' berhasil diperbarui');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Gagal memperbarui: ' . $e->getMessage());
        }
    }

    public function destroy($produkId)
    {
        $produk = Produk::findOrFail($produkId);
        $bom = $produk->bomJobCosting;
        
  \Log::info('=== BOM DESTROY DEBUG ===');
        \Log::info('Produk ID: ' . $produk->id);
        \Log::info('Produk Nama: ' . $produk->nama_produk);
        \Log::info('BOM exists: ' . ($bom ? 'Yes' : 'No'));
        
        if ($bom) {
            \Log::info('Current biaya_bahan: ' . $produk->biaya_bahan);
            \Log::info('Current harga_bom: ' . $produk->harga_bom);
            
            DB::beginTransaction();
            try {
                // Hapus semua detail BOM terlebih dahulu
                $bom->detailBBB()->delete();
                $bom->detailBTKL()->delete();
                $bom->detailBahanPendukung()->delete();
                $bom->detailBOP()->delete();
                
                // Hapus BOM utama
                $bom->delete();
                
                // Reset biaya_bahan dan harga_bom di produk
                $produk->update([
                    'biaya_bahan' => 0,
                    'harga_bom' => 0
                ]);
                
                \Log::info('After delete - biaya_bahan: ' . $produk->fresh()->biaya_bahan);
                \Log::info('After delete - harga_bom: ' . $produk->fresh()->harga_bom);
                
                DB::commit();
                
                return redirect()->route('master-data.produk.index')
                    ->with('success', 'Biaya bahan untuk ' . $produk->nama_produk . ' berhasil dihapus. Semua data BOM telah direset.');
                    
            } catch (\Exception $e) {
                DB::rollBack();
                \Log::error('Error deleting BOM: ' . $e->getMessage());
                
                return back()->with('error', 'Gagal menghapus biaya bahan: ' . $e->getMessage());
            }
        }
        
        return redirect()->route('master-data.produk.index')
            ->with('info', 'Tidak ada data biaya bahan untuk produk ' . $produk->nama_produk);
    }

    public function print($produkId)
    {
        $produk = Produk::findOrFail($produkId);
        $bom = $produk->bomJobCosting;
        
        if (!$bom) {
            return redirect()->route('master-data.bom-job-costing.create', $produk->id);
        }
        
        $bom->load([
            'detailBBB.bahanBaku.satuanRelation',
            'detailBTKL.prosesProduksi',
            'detailBahanPendukung.bahanPendukung.satuanRelation',
            'detailBOP.bop'
        ]);
        
        return view('master-data.bom-job-costing.print', compact('produk', 'bom'));
    }
    
    /**
     * Helper untuk menyimpan detail BOM
     */
    private function saveDetails($bom, $request)
    {
        // Debug logging
        \Log::info('=== SAVE DETAILS DEBUG ===');
        \Log::info('BBB IDs: ' . json_encode($request->bbb_id ?? []));
        \Log::info('BBB Jumlah: ' . json_encode($request->bbb_jumlah ?? []));
        \Log::info('BP IDs: ' . json_encode($request->bp_id ?? []));
        \Log::info('BP Jumlah: ' . json_encode($request->bp_jumlah ?? []));
        
        // BBB
        if ($request->bbb_id) {
            foreach ($request->bbb_id as $i => $id) {
                if (!$id) continue;
                $bb = BahanBaku::find($id);
                \Log::info('Creating BBB detail for bahan: ' . $bb->nama_bahan ?? 'Unknown');
                
                $jumlah = $request->bbb_jumlah[$i] ?? 0;
                $selectedSatuan = $request->bbb_satuan[$i] ?? ($bb->satuanRelation->kode ?? 'KG');
                $baseHarga = $bb->harga_satuan ?? 0;
                $baseSatuan = $bb->satuanRelation->kode ?? 'KG';
                
                // Konversi harga ke satuan yang dipilih (auto-detect category)
                $convertedHarga = $this->convertPrice($baseHarga, $baseSatuan, $selectedSatuan);
                
                $bom->detailBBB()->create([
                    'bahan_baku_id' => $id,
                    'jumlah' => $jumlah,
                    'satuan' => $selectedSatuan,
                    'harga_satuan' => $convertedHarga, // Gunakan harga yang sudah dikonversi
                ]);
            }
        }

        // BTKL
        if ($request->btkl_id) {
            foreach ($request->btkl_id as $i => $id) {
                if (!$id) continue;
                $btkl = \App\Models\Btkl::with('jabatan')->find($id);
                \Log::info('Creating BTKL detail for: ' . ($btkl->jabatan->nama ?? 'Unknown'));
                
                $bom->detailBTKL()->create([
                    'btkl_id' => $id,
                    'nama_proses' => $btkl->jabatan->nama ?? $btkl->kode_proses,
                    'durasi_jam' => $request->btkl_durasi[$i] ?? 0,
                    'tarif_per_jam' => $btkl->tarif_per_jam ?? 0,
                ]);
            }
        }

        // Bahan Pendukung
        if ($request->bp_id) {
            foreach ($request->bp_id as $i => $id) {
                if (!$id) continue;
                $bp = BahanPendukung::find($id);
                \Log::info('Creating Bahan Pendukung detail for: ' . $bp->nama_bahan ?? 'Unknown');
                
                $jumlah = $request->bp_jumlah[$i] ?? 0;
                $selectedSatuan = $request->bp_satuan[$i] ?? ($bp->satuanRelation->kode ?? 'PCS');
                $baseHarga = $bp->harga_satuan ?? 0;
                $baseSatuan = $bp->satuanRelation->kode ?? 'PCS';
                
                // Konversi harga ke satuan yang dipilih (auto-detect category)
                $convertedHarga = $this->convertPrice($baseHarga, $baseSatuan, $selectedSatuan);
                
                $bom->detailBahanPendukung()->create([
                    'bahan_pendukung_id' => $id,
                    'jumlah' => $jumlah,
                    'satuan' => $selectedSatuan,
                    'harga_satuan' => $convertedHarga, // Gunakan harga yang sudah dikonversi
                ]);
            }
        }

        // BOP
        if ($request->bop_id) {
            foreach ($request->bop_id as $i => $id) {
                if (!$id) continue;
                $bop = Bop::find($id);
                $bom->detailBOP()->create([
                    'bop_id' => $id,
                    'nama_bop' => $bop->nama_akun ?? '',
                    'jumlah' => $request->bop_jumlah[$i] ?? 1,
                    'tarif' => $request->bop_tarif[$i] ?? ($bop->budget ?? 0),
                ]);
            }
        }
        
        \Log::info('SAVE DETAILS COMPLETED');
    }
    
    /**
     * Convert price from base unit to selected unit
     */
    private function convertPrice($basePrice, $baseUnit, $targetUnit, $category = null)
    {
        if ($baseUnit === $targetUnit) return $basePrice;
        
        // Define conversion factors (how many smaller units in one larger unit)
        $conversions = [
            'Berat' => ['KG' => 1000, 'G' => 1, 'MG' => 0.001],
            'Volume' => ['LTR' => 1000, 'ML' => 1],
            'Unit' => ['PCS' => 1, 'PACK' => 10, 'BOX' => 100],
            'Energi' => ['WTT' => 1],
            'Ukuran' => ['SDT' => 1]
        ];
        
        // Auto-detect category if not provided
        if ($category === null) {
            foreach ($conversions as $cat => $units) {
                if (isset($units[$baseUnit])) {
                    $category = $cat;
                    break;
                }
            }
        }
        
        // If still no category found, default to 'Unit'
        if ($category === null || !isset($conversions[$category])) {
            $category = 'Unit';
        }
        
        $categoryConversions = $conversions[$category] ?? [];
        
        // Convert to smallest unit first, then to target unit
        $baseToSmallest = $categoryConversions[$baseUnit] ?? 1;
        $targetToSmallest = $categoryConversions[$targetUnit] ?? 1;
        
        // Convert base price to smallest unit, then to target unit
        $priceInSmallestUnit = $basePrice / $baseToSmallest;
        $convertedPrice = $priceInSmallestUnit * $targetToSmallest;
        
        return $convertedPrice;
    }
}
