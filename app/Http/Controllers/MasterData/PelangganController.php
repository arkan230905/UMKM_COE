<?php

namespace App\Http\Controllers\MasterData;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class PelangganController extends Controller
{
    public function index()
    {
        $pelanggans = User::where('role', 'pelanggan')
            ->withCount('orders')
            ->latest()
            ->paginate(15);

        return view('master-data.pelanggan.index', compact('pelanggans'));
    }

    public function show($id)
    {
        $pelanggan = User::where('role', 'pelanggan')->findOrFail($id);
        
        // Load orders jika ada
        $pelanggan->load(['orders' => function($query) {
            $query->latest()->take(10);
        }]);

        return view('master-data.pelanggan.show', compact('pelanggan'));
    }

    public function edit($id)
    {
        $pelanggan = User::where('role', 'pelanggan')->findOrFail($id);
        return view('master-data.pelanggan.edit', compact('pelanggan'));
    }

    public function update(Request $request, $id)
    {
        $pelanggan = User::where('role', 'pelanggan')->findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $id,
            'username' => 'required|string|unique:users,username,' . $id,
            'phone' => 'nullable|string|max:20',
            'password' => 'nullable|min:8|confirmed',
        ]);

        $data = [
            'name' => $request->name,
            'email' => $request->email,
            'username' => $request->username,
            'phone' => $request->phone,
        ];

        // Update password jika diisi
        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        $pelanggan->update($data);

        return redirect()->route('master-data.pelanggan.index')
            ->with('success', 'Data pelanggan berhasil diupdate!');
    }

    public function destroy($id)
    {
        $pelanggan = User::where('role', 'pelanggan')->findOrFail($id);
        
        // Cek apakah pelanggan punya order
        if ($pelanggan->orders()->count() > 0) {
            return back()->with('error', 'Tidak bisa menghapus pelanggan yang sudah memiliki pesanan!');
        }

        $pelanggan->delete();

        return redirect()->route('master-data.pelanggan.index')
            ->with('success', 'Data pelanggan berhasil dihapus!');
    }
}
