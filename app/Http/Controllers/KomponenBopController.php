<?php

namespace App\Http\Controllers;

use App\Models\KomponenBop;
use Illuminate\Http\Request;

class KomponenBopController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $komponenBops = KomponenBop::orderBy('kode_komponen')->paginate(10);
        return view('master-data.komponen-bop.index', compact('komponenBops'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('master-data.komponen-bop.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama_komponen' => 'required|string|max:100',
            'satuan' => 'required|string|max:20',
            'tarif_per_satuan' => 'required|numeric|min:0',
        ]);

        KomponenBop::create($validated);

        return redirect()->route('master-data.komponen-bop.index')
            ->with('success', 'Komponen BOP berhasil ditambahkan');
    }

    /**
     * Display the specified resource.
     */
    public function show(KomponenBop $komponenBop)
    {
        $komponenBop->load('prosesBops.prosesProduksi');
        return view('master-data.komponen-bop.show', compact('komponenBop'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(KomponenBop $komponenBop)
    {
        return view('master-data.komponen-bop.edit', compact('komponenBop'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, KomponenBop $komponenBop)
    {
        $validated = $request->validate([
            'nama_komponen' => 'required|string|max:100',
            'satuan' => 'required|string|max:20',
            'tarif_per_satuan' => 'required|numeric|min:0',
            'is_active' => 'boolean',
        ]);

        $komponenBop->update([
            'nama_komponen' => $validated['nama_komponen'],
            'satuan' => $validated['satuan'],
            'tarif_per_satuan' => $validated['tarif_per_satuan'],
            'is_active' => $request->has('is_active'),
        ]);

        return redirect()->route('master-data.komponen-bop.index')
            ->with('success', 'Komponen BOP berhasil diperbarui');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(KomponenBop $komponenBop)
    {
        try {
            $komponenBop->delete();
            return redirect()->route('master-data.komponen-bop.index')
                ->with('success', 'Komponen BOP berhasil dihapus');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }
}
