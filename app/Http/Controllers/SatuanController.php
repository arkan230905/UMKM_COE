<?php

namespace App\Http\Controllers;

use App\Models\Satuan;
use App\Models\SatuanGrup;
use Illuminate\Http\Request;

class SatuanController extends Controller
{
    public function index()
    {
        $satuans = Satuan::where('user_id', auth()->id())
            ->orderBy('kode', 'asc')
            ->get();
        return view('master-data.satuan.index', compact('satuans'));
    }

    /**
     * Display satuan management page with tabs
     */
    public function dashboard()
    {
        $satuans = Satuan::where('user_id', auth()->id())
            ->orderBy('kode', 'asc')
            ->get();
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
            'kode' => 'required|string|max:10|unique:satuans,kode,NULL,id,user_id,'.auth()->id(),
            'nama' => 'required|string|max:50|unique:satuans,nama,NULL,id,user_id,'.auth()->id(),
        ], [
            'kode.required' => 'Kode satuan harus diisi',
            'kode.max' => 'Kode maksimal 10 karakter',
            'kode.unique' => 'Kode satuan sudah digunakan',
            'nama.required' => 'Nama satuan harus diisi',
            'nama.max' => 'Nama maksimal 50 karakter',
            'nama.unique' => 'Nama satuan sudah digunakan',
        ]);

        try {
            // Simpan data dengan user_id
            $satuan = Satuan::create([
                'user_id' => auth()->id(),
                'kode' => strtoupper($validated['kode']),
                'nama' => $validated['nama'],
                'tipe' => $request->tipe ?? 'unit',
                'kategori' => $request->kategori ?? 'jumlah',
                'is_active' => true,
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


    public function edit($id)
    {
        $satuan = Satuan::where('user_id', auth()->id())->findOrFail($id);
        return view('master-data.satuan.edit', compact('satuan'));
    }

    public function update(Request $request, $id)
    {
        // Check if this is an AJAX request
        $isAjax = $request->ajax() || $request->wantsJson();
        
        $satuan = Satuan::where('user_id', auth()->id())->findOrFail($id);
        
        $validated = $request->validate([
            'kode' => 'required|string|max:10|unique:satuans,kode,' . $id . ',id,user_id,'.auth()->id(),
            'nama' => 'required|string|max:50|unique:satuans,nama,' . $id . ',id,user_id,'.auth()->id(),
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
                'tipe' => $request->tipe ?? $satuan->tipe,
                'kategori' => $request->kategori ?? $satuan->kategori,
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

    public function destroy(Request $request, $id)
    {
        // Check if this is an AJAX request
        $isAjax = $request->ajax() || $request->wantsJson();
        
        try {
            $satuan = Satuan::where('user_id', auth()->id())->findOrFail($id);
            $satuanName = $satuan->nama;
            
            // Hapus data
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
