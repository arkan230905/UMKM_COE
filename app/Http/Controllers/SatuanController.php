<?php

namespace App\Http\Controllers;

use App\Models\Satuan;
use App\Models\SatuanGrup;
use Illuminate\Http\Request;

class SatuanController extends Controller
{
    public function index(Request $request)
    {
        $sortBy = $request->get('sort_by', 'kode');
        $sortDir = $request->get('sort_dir', 'asc');

        if (!in_array($sortBy, ['kode', 'nama'])) {
            $sortBy = 'kode';
        }
        if (!in_array($sortDir, ['asc', 'desc'])) {
            $sortDir = 'asc';
        }

        $satuans = Satuan::where('user_id', auth()->id())
            ->orderBy($sortBy, $sortDir)
            ->get();
        return view('master-data.satuan.index', compact('satuans', 'sortBy', 'sortDir'));
    }

    /**
     * Display satuan management page with tabs
     */
    public function dashboard(Request $request)
    {
        $sortBy = $request->get('sort_by', 'kode');
        $sortDir = $request->get('sort_dir', 'asc');

        if (!in_array($sortBy, ['kode', 'nama'])) {
            $sortBy = 'kode';
        }
        if (!in_array($sortDir, ['asc', 'desc'])) {
            $sortDir = 'asc';
        }

        $satuans = Satuan::where('user_id', auth()->id())
            ->orderBy($sortBy, $sortDir)
            ->get();
        return view('master-data.satuan.dashboard', compact('satuans', 'sortBy', 'sortDir'));
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
            
            // Cek apakah satuan sedang digunakan oleh tabel lain
            $blockers = [];
            $bb = \App\Models\BahanBaku::where('satuan_id', $satuan->id)->count();
            if ($bb > 0) $blockers[] = "Bahan Baku ($bb)";
            
            $bp = \App\Models\BahanPendukung::where('satuan_id', $satuan->id)->count();
            if ($bp > 0) $blockers[] = "Bahan Pendukung ($bp)";
            
            $pr = \App\Models\Produk::where('satuan_id', $satuan->id)->count();
            if ($pr > 0) $blockers[] = "Produk ($pr)";

            if (!empty($blockers)) {
                $msg = 'Satuan "' . $satuanName . '" tidak dapat dihapus karena masih digunakan pada data: ' . implode(', ', $blockers) . '.';
                
                if ($isAjax) {
                    return response()->json([
                        'success' => false,
                        'message' => $msg
                    ], 400); // 400 Bad Request
                }

                return redirect()->route('master-data.satuan.index')
                    ->with('error', $msg);
            }

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
                
        } catch (\Illuminate\Database\QueryException $e) {
            // Check for foreign key constraint violation
            if ($e->getCode() == '23000') {
                $message = 'Gagal menghapus data: Satuan sedang digunakan oleh data lain dan tidak dapat dihapus.';
            } else {
                $message = 'Gagal menghapus data: ' . $e->getMessage();
            }
            
            if ($isAjax) {
                return response()->json([
                    'success' => false,
                    'message' => $message
                ], 500);
            }

            return redirect()->back()
                ->with('error', $message);
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
