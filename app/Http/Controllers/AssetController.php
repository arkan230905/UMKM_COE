<?php
namespace App\Http\Controllers;

use App\Models\Asset;
use Illuminate\Http\Request;

class AssetController extends Controller
{
    public function index()
    {
        $assets = Asset::when(auth()->check(), function ($q) {
                return $q->where('id_perusahaan', auth()->user()->id_perusahaan);
            })
            ->latest()->paginate(10);

        return view('aset.index', compact('assets'));
    }

    public function create()
    {
        return view('aset.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama_asset' => 'required|string|max:255',
            'tanggal_beli' => 'required|date',
            'harga_perolehan' => 'required|numeric|min:0',
            'nilai_sisa' => 'required|numeric|min:0',
            'umur_ekonomis' => 'required|integer|min:1',
        ]);

        if (auth()->check()) {
            $request->merge(['id_perusahaan' => auth()->user()->id_perusahaan]);
        }

        Asset::create($request->all());

        return redirect()->route('aset.index')->with('success', 'Asset created successfully.');
    }

    public function show(Asset $asset)
    {
        if (auth()->check() && $asset->id_perusahaan != auth()->user()->id_perusahaan) {
            return redirect()->route('aset.index')->with('error', 'Unauthorized access to this asset.');
        }

        $penyusutan_per_tahun = $asset->depreciation_per_year;
        $schedule = $asset->calculateDepreciationSchedule();

        $total_depreciation = collect($schedule)->sum('biaya_penyusutan');
        $current_book_value = collect($schedule)->last()['nilai_buku'] ?? $asset->harga_perolehan;

        return view('aset.show', [
            'asset' => $asset,
            'penyusutan_per_tahun' => $penyusutan_per_tahun,
            'total_depreciation' => $total_depreciation,
            'current_book_value' => $current_book_value,
            'schedule' => $schedule,
        ]);
    }

    public function edit($id)
    {
        $asset = Asset::findOrFail($id);
        if (auth()->check() && $asset->id_perusahaan != auth()->user()->id_perusahaan) {
            return redirect()->route('aset.index')->with('error', 'Unauthorized access to edit this asset.');
        }
        return view('aset.edit', compact('asset'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'nama_asset' => 'required|string|max:255',
            'tanggal_beli' => 'required|date',
            'harga_perolehan' => 'required|numeric|min:0',
            'nilai_sisa' => 'required|numeric|min:0',
            'umur_ekonomis' => 'required|integer|min:1',
        ]);

        $asset = Asset::findOrFail($id);
        if (auth()->check() && $asset->id_perusahaan != auth()->user()->id_perusahaan) {
            return redirect()->route('aset.index')->with('error', 'Unauthorized access to update this asset.');
        }

        $asset->update($request->all());

        return redirect()->route('aset.index')->with('success', 'Asset updated successfully.');
    }

    public function destroy(Asset $asset)
    {
        if (auth()->check() && $asset->id_perusahaan != auth()->user()->id_perusahaan) {
            return redirect()->route('aset.index')->with('error', 'Unauthorized access to delete this asset.');
        }

        $asset->delete();
        return redirect()->route('aset.index')->with('success', 'Asset deleted successfully.');
    }

    public function calculateDepreciation(Asset $asset)
    {
        if (auth()->check() && $asset->id_perusahaan != auth()->user()->id_perusahaan) {
            return redirect()->route('aset.index')->with('error', 'Unauthorized access to this asset.');
        }

        $schedule = $asset->calculateDepreciationSchedule();

        return view('aset.depreciation', [
            'asset' => $asset,
            'depreciation_schedule' => $schedule,
        ]);
    }
}
