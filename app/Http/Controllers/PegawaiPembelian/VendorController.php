<?php

namespace App\Http\Controllers\PegawaiPembelian;

use App\Http\Controllers\Controller;
use App\Models\Vendor;
use Illuminate\Http\Request;

class VendorController extends Controller
{
    public function index()
    {
        $vendors = Vendor::latest()->paginate(15);
        return view('pegawai-pembelian.vendor.index', compact('vendors'));
    }

    public function create()
    {
        return view('pegawai-pembelian.vendor.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama_vendor' => 'required|string|max:255',
            'kategori' => 'nullable|string|max:100',
            'alamat' => 'nullable|string',
            'no_telp' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
        ]);

        Vendor::create($validated);

        return redirect()->route('pegawai-pembelian.vendor.index')
            ->with('success', 'Vendor berhasil ditambahkan!');
    }

    public function show($id)
    {
        $vendor = Vendor::with('pembelians')->findOrFail($id);
        return view('pegawai-pembelian.vendor.show', compact('vendor'));
    }

    public function edit($id)
    {
        $vendor = Vendor::findOrFail($id);
        return view('pegawai-pembelian.vendor.edit', compact('vendor'));
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'nama_vendor' => 'required|string|max:255',
            'kategori' => 'nullable|string|max:100',
            'alamat' => 'nullable|string',
            'no_telp' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
        ]);

        $vendor = Vendor::findOrFail($id);
        $vendor->update($validated);

        return redirect()->route('pegawai-pembelian.vendor.index')
            ->with('success', 'Vendor berhasil diupdate!');
    }

    public function destroy($id)
    {
        $vendor = Vendor::findOrFail($id);
        $vendor->delete();

        return redirect()->route('pegawai-pembelian.vendor.index')
            ->with('success', 'Vendor berhasil dihapus!');
    }
}
