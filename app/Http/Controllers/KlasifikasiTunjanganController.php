<?php

namespace App\Http\Controllers;

use App\Models\Jabatan;
use App\Models\KlasifikasiTunjangan;
use Illuminate\Http\Request;

class KlasifikasiTunjanganController extends Controller
{
    /**
     * Store tunjangan baru
     */
    public function store(Request $request, Jabatan $jabatan)
    {
        $validated = $request->validate([
            'nama_tunjangan' => 'required|string|max:255',
            'nilai_tunjangan' => 'required|numeric|min:0',
            'keterangan' => 'nullable|string',
        ]);

        $jabatan->tunjangans()->create($validated);

        return back()->with('success', 'Tunjangan berhasil ditambahkan');
    }

    /**
     * Update tunjangan
     */
    public function update(Request $request, KlasifikasiTunjangan $tunjangan)
    {
        $validated = $request->validate([
            'nama_tunjangan' => 'required|string|max:255',
            'nilai_tunjangan' => 'required|numeric|min:0',
            'keterangan' => 'nullable|string',
        ]);

        $tunjangan->update($validated);

        return back()->with('success', 'Tunjangan berhasil diperbarui');
    }

    /**
     * Delete tunjangan
     */
    public function destroy(KlasifikasiTunjangan $tunjangan)
    {
        $tunjangan->delete();
        return back()->with('success', 'Tunjangan berhasil dihapus');
    }

    /**
     * Toggle status tunjangan
     */
    public function toggleStatus(KlasifikasiTunjangan $tunjangan)
    {
        $tunjangan->update(['is_active' => !$tunjangan->is_active]);
        return back()->with('success', 'Status tunjangan berhasil diubah');
    }
}
