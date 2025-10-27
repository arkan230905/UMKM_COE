<?php

namespace App\Http\Controllers;

use App\Models\Bop;
use App\Models\Coa;
use Illuminate\Http\Request;

class BopController extends Controller
{
    // ============================
    // 🧾 Tampilkan Data BOP
    // ============================
    public function index()
    {
        $bop = Bop::with('coa')->get();
        return view('master-data.bop.index', compact('bop'));
    }

    // ============================
    // ✏️ Edit Data BOP
    // ============================
    public function edit(Bop $bop)
    {
        // Ambil COA hanya untuk ditampilkan (tidak untuk diubah)
        $coa = Coa::where('tipe_akun', 'like', '%Beban%')->get();
        return view('master-data.bop.edit', compact('bop', 'coa'));
    }

    // ============================
    // 🔄 Update Data BOP
    // ============================
    public function update(Request $request, Bop $bop)
    {
        // Validasi hanya untuk nominal & tanggal
        $request->validate([
            'nominal' => 'nullable|numeric',
            'tanggal' => 'nullable|date',
        ]);

        // Update hanya kolom nominal dan tanggal
        $bop->update($request->only('nominal', 'tanggal'));

        return redirect()->route('master-data.bop.index')
                         ->with('success', 'Data BOP berhasil diperbarui');
    }

    // ============================
    // 🗑️ Hapus Data BOP
    // ============================
    public function destroy(Bop $bop)
    {
        $bop->delete();

        return redirect()->route('master-data.bop.index')
                         ->with('success', 'Data BOP berhasil dihapus');
    }
}
