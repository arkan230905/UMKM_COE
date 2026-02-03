<?php

namespace App\Http\Controllers;

use App\Models\Vendor;
use Illuminate\Http\Request;

class VendorController extends Controller
{
    // Tampilkan semua vendor
    public function index()
    {
        $vendors = Vendor::orderBy('id', 'asc')->get(); // urut ID kecil ke besar
        return view('master-data.vendor.index', compact('vendors'));
    }

    // Form tambah vendor
    public function create()
    {
        return view('master-data.vendor.create');
    }

    // Simpan vendor baru
    public function store(Request $request)
    {
        $request->validate([
            'nama_vendor' => 'required|string|max:255',
            'kategori' => 'required|string|in:Bahan Baku,Bahan Pendukung,Aset',
            'alamat' => 'nullable|string',
            'no_telp' => 'nullable|string',
            'email' => 'nullable|email',
        ]);

        $vendor = Vendor::create($request->all());
        
        // Log for debugging
        \Log::info('Vendor created successfully', [
            'id' => $vendor->id,
            'nama_vendor' => $vendor->nama_vendor,
            'redirecting_to' => 'master-data.vendor.index'
        ]);

        // Add session debugging
        session()->flash('success', 'Vendor berhasil ditambahkan.');
        \Log::info('Session flash data', session()->all());

        return redirect()->route('master-data.vendor.index');
    }

    // Form edit vendor
    public function edit(Vendor $vendor)
    {
        return view('master-data.vendor.edit', compact('vendor'));
    }

    // Update vendor
    public function update(Request $request, Vendor $vendor)
    {
        $request->validate([
            'nama_vendor' => 'required|string|max:255',
            'kategori' => 'required|string|in:Bahan Baku,Bahan Pendukung,Aset',
            'alamat' => 'nullable|string',
            'no_telp' => 'nullable|string',
            'email' => 'nullable|email',
        ]);

        $vendor->update($request->all());

        return redirect()->route('master-data.vendor.index')
            ->with('success', 'Vendor berhasil diperbarui.');
    }

    // Hapus vendor
    public function destroy(Vendor $vendor)
    {
        $vendor->delete();
        return redirect()->route('master-data.vendor.index')
            ->with('success', 'Vendor berhasil dihapus.');
    }
}
