<?php

namespace App\Http\Controllers;

use App\Models\Vendor;
use Illuminate\Http\Request;

class VendorController extends Controller
{
    // Tampilkan semua vendor
    public function index()
    {
        // CRITICAL: Filter by user_id untuk multi-tenant isolation
        $vendors = Vendor::where('user_id', auth()->id())
            ->orderBy('id', 'asc')
            ->get();
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
            'nama_vendor' => [
                'required',
                'string',
                'max:255',
                // Unique: kombinasi (user_id, nama_vendor, kategori)
                // Vendor dengan nama sama boleh ada jika kategorinya berbeda
                function ($attribute, $value, $fail) use ($request) {
                    $exists = Vendor::where('user_id', auth()->id())
                        ->where('nama_vendor', $value)
                        ->where('kategori', $request->kategori)
                        ->exists();
                    
                    if ($exists) {
                        $fail("Vendor dengan nama '{$value}' dan kategori '{$request->kategori}' sudah ada.");
                    }
                },
            ],
            'kategori' => 'required|string|in:Bahan Baku,Bahan Pendukung,Aset',
            'alamat' => 'required|string',
            'no_telp' => 'required|string',
            'email' => 'required|email',
        ]);

        $vendor = Vendor::create(array_merge($request->all(), ['user_id' => auth()->id()])); // 🔒 SECURITY: Add user_id
        
        // Log for debugging
        \Log::info('Vendor created successfully', [
            'id' => $vendor->id,
            'nama_vendor' => $vendor->nama_vendor,
            'kategori' => $vendor->kategori,
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
            'nama_vendor' => [
                'required',
                'string',
                'max:255',
                // Unique: kombinasi (user_id, nama_vendor, kategori) kecuali vendor yang sedang diedit
                function ($attribute, $value, $fail) use ($request, $vendor) {
                    $exists = Vendor::where('user_id', auth()->id())
                        ->where('nama_vendor', $value)
                        ->where('kategori', $request->kategori)
                        ->where('id', '!=', $vendor->id) // Exclude current vendor
                        ->exists();
                    
                    if ($exists) {
                        $fail("Vendor dengan nama '{$value}' dan kategori '{$request->kategori}' sudah ada.");
                    }
                },
            ],
            'kategori' => 'required|string|in:Bahan Baku,Bahan Pendukung,Aset',
            'alamat' => 'required|string',
            'no_telp' => 'required|string',
            'email' => 'required|email',
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
