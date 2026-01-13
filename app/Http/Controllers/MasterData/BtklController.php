<?php

namespace App\Http\Controllers\MasterData;

use App\Http\Controllers\Controller;
use App\Models\Btkl;
use App\Models\Jabatan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BtklController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            $btkls = Btkl::with('jabatan')
                ->orderBy('kode_proses')
                ->get();

            return view('master-data.btkl.index', compact('btkls'));
            
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // Get Jabatan with category 'btkl'
        $jabatanBtkl = Jabatan::where('kategori', 'btkl')->orderBy('nama')->get();

        // Generate next process code
        $lastBtkl = Btkl::orderBy('kode_proses', 'desc')->first();
        if ($lastBtkl) {
            // Extract number from last code (e.g., PROC-001 -> 001)
            $lastNumber = (int) substr($lastBtkl->kode_proses, -3);
            $nextNumber = $lastNumber + 1;
        } else {
            $nextNumber = 1;
        }
        $nextKode = 'PROC-' . str_pad($nextNumber, 3, '0', STR_PAD_LEFT);

        $satuanOptions = ['Jam', 'Unit', 'Batch'];

        return view('master-data.btkl.create', compact('jabatanBtkl', 'nextKode', 'satuanOptions'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'kode_proses' => 'required|string|max:20|unique:btkls,kode_proses',
            'jabatan_id' => 'required|exists:jabatans,id',
            'tarif_per_jam' => 'required|numeric|min:0',
            'satuan' => 'required|in:Jam,Unit,Batch',
            'kapasitas_per_jam' => 'required|integer|min:0',
            'deskripsi_proses' => 'nullable|string',
        ]);

        try {
            DB::beginTransaction();

            // If kode_proses is empty, generate it
            if (empty($validated['kode_proses'])) {
                $lastBtkl = Btkl::orderBy('kode_proses', 'desc')->first();
                if ($lastBtkl) {
                    $lastNumber = (int) substr($lastBtkl->kode_proses, -3);
                    $nextNumber = $lastNumber + 1;
                } else {
                    $nextNumber = 1;
                }
                $validated['kode_proses'] = 'PROC-' . str_pad($nextNumber, 3, '0', STR_PAD_LEFT);
            }

            Btkl::create($validated);

            DB::commit();

            return redirect()
                ->route('master-data.btkl.index')
                ->with('success', 'Data BTKL berhasil ditambahkan');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()
                ->withInput()
                ->with('error', 'Gagal menyimpan data BTKL: ' . $e->getMessage());
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        try {
            $btkl = Btkl::with('jabatan')->findOrFail($id);
            $jabatanBtkl = Jabatan::where('kategori', 'btkl')->orderBy('nama')->get();
            $satuanOptions = ['Jam', 'Unit', 'Batch'];
                
            return view('master-data.btkl.edit', compact('btkl', 'jabatanBtkl', 'satuanOptions'));
            
        } catch (\Exception $e) {
            return redirect()
                ->route('master-data.btkl.index')
                ->with('error', 'Data BTKL tidak ditemukan: ' . $e->getMessage());
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'kode_proses' => 'required|string|max:20|unique:btkls,kode_proses,' . $id,
            'jabatan_id' => 'required|exists:jabatans,id',
            'tarif_per_jam' => 'required|numeric|min:0',
            'satuan' => 'required|in:Jam,Unit,Batch',
            'kapasitas_per_jam' => 'required|integer|min:0',
            'deskripsi_proses' => 'nullable|string',
        ]);

        DB::beginTransaction();
        
        try {
            $btkl = Btkl::findOrFail($id);
            $btkl->update($validated);

            DB::commit();

            return redirect()
                ->route('master-data.btkl.index')
                ->with('success', 'Data BTKL berhasil diperbarui');

        } catch (\Exception $e) {
            DB::rollBack();
            
            return back()
                ->withInput()
                ->with('error', 'Gagal memperbarui data BTKL: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        DB::beginTransaction();
        
        try {
            $btkl = Btkl::findOrFail($id);
            $btkl->delete();

            DB::commit();

            return redirect()
                ->route('master-data.btkl.index')
                ->with('success', 'Data BTKL berhasil dihapus');

        } catch (\Exception $e) {
            DB::rollBack();
            
            return back()
                ->with('error', 'Gagal menghapus data BTKL: ' . $e->getMessage());
        }
    }
}