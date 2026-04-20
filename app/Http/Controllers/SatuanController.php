<?php

namespace App\Http\Controllers;

use App\Models\SatuanGrup;
use Illuminate\Http\Request;

class SatuanController extends Controller
{
    public function index()
    {
        $satuans = SatuanGrup::orderBy('id', 'asc')->get();
        return view('master-data.satuan.index', compact('satuans'));
    }

    /**
     * Display satuan management page with tabs
     */
    public function dashboard()
    {
        $satuans = SatuanGrup::orderBy('kode', 'asc')->get();
        return view('master-data.satuan.dashboard', compact('satuans'));
    }

    public function create()
    {
        return view('master-data.satuan.create');
    }

    public function store(Request $request)
    {
        // Check if this is an AJAX request
        $isAjax = $request->ajax() || $request->wantsJson();
        
        $validated = $request->validate([
            'kode' => 'required|string|max:10|unique:satuan_grups,kode',
            'nama' => 'required|string|max:50|unique:satuan_grups,nama',
        ], [
            'kode.required' => 'Kode satuan harus diisi',
            'kode.max' => 'Kode maksimal 10 karakter',
            'kode.unique' => 'Kode satuan sudah digunakan',
            'nama.required' => 'Nama satuan harus diisi',
            'nama.max' => 'Nama maksimal 50 karakter',
            'nama.unique' => 'Nama satuan sudah digunakan',
        ]);

        try {
            // Simpan data
            $satuan = SatuanGrup::create([
                'kode' => strtoupper($validated['kode']),
                'nama' => $validated['nama'],
                'keterangan' => $request->keterangan ?? '',
            ]);

            // Check if request is AJAX
            if ($isAjax) {
                return response()->json([
                    'success' => true,
                    'id' => $satuan->id,
                    'message' => 'Data satuan berhasil ditambahkan!'
                ]);
            }

            return redirect()->route('master-data.satuan.index')
                ->with('success', 'Data satuan berhasil ditambahkan!');
                
        } catch (\Exception $e) {
            // Check if request is AJAX
            if ($isAjax) {
                return response()->json([
                    'success' => false,
                    'message' => 'Gagal menambahkan data: ' . $e->getMessage()
                ], 500);
            }

            return redirect()->back()
                ->withInput()
                ->with('error', 'Gagal menambahkan data: ' . $e->getMessage());
        }
    }


    public function edit(SatuanGrup $satuan)
    {
        return view('master-data.satuan.edit', compact('satuan'));
    }

    public function update(Request $request, SatuanGrup $satuan)
    {
        // Check if this is an AJAX request
        $isAjax = $request->ajax() || $request->wantsJson();
        
        $validated = $request->validate([
            'kode' => 'required|string|max:10|unique:satuan_grups,kode,' . $satuan->id,
            'nama' => 'required|string|max:50|unique:satuan_grups,nama,' . $satuan->id,
        ], [
            'kode.required' => 'Kode satuan harus diisi',
            'kode.max' => 'Kode maksimal 10 karakter',
            'kode.unique' => 'Kode satuan sudah digunakan',
            'nama.required' => 'Nama satuan harus diisi',
            'nama.max' => 'Nama maksimal 50 karakter',
            'nama.unique' => 'Nama satuan sudah digunakan',
        ]);

        try {
            // Update data
            $satuan->update([
                'kode' => strtoupper($validated['kode']),
                'nama' => $validated['nama'],
                'keterangan' => $request->keterangan ?? $satuan->keterangan,
            ]);

            // Check if request is AJAX
            if ($isAjax) {
                return response()->json([
                    'success' => true,
                    'message' => 'Data satuan berhasil diperbarui!'
                ]);
            }

            return redirect()->route('master-data.satuan.index')
                ->with('success', 'Data satuan berhasil diperbarui!');
                
        } catch (\Exception $e) {
            // Check if request is AJAX
            if ($isAjax) {
                return response()->json([
                    'success' => false,
                    'message' => 'Gagal memperbarui data: ' . $e->getMessage()
                ], 500);
            }

            return redirect()->back()
                ->withInput()
                ->with('error', 'Gagal memperbarui data: ' . $e->getMessage());
        }
    }

    public function destroy(Request $request, SatuanGrup $satuan)
    {
        // Check if this is an AJAX request
        $isAjax = $request->ajax() || $request->wantsJson();
        
        try {
            $satuanName = $satuan->nama;
            $satuan->delete();

            // Check if request is AJAX
            if ($isAjax) {
                return response()->json([
                    'success' => true,
                    'message' => 'Satuan berhasil dihapus!'
                ]);
            }

            return redirect()->route('master-data.satuan.index')
                ->with('success', 'Satuan berhasil dihapus!');
                
        } catch (\Exception $e) {
            // Check if request is AJAX
            if ($isAjax) {
                return response()->json([
                    'success' => false,
                    'message' => 'Gagal menghapus data: ' . $e->getMessage()
                ], 500);
            }

            return redirect()->back()
                ->with('error', 'Gagal menghapus data: ' . $e->getMessage());
        }
    }
}
