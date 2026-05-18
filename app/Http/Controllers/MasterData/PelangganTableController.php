<?php

namespace App\Http\Controllers\MasterData;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

class PelangganTableController extends Controller
{
    public function index()
    {
        // 🔒 SECURITY: Get pelanggan users (registered via website)
        // Pelanggan yang terdaftar melalui website memiliki role='pelanggan'
        // Semua owner bisa melihat semua pelanggan yang terdaftar
        $pelanggans = User::where('role', 'pelanggan')
            ->withCount('orders')
            ->latest()
            ->paginate(15);
        
        return view('master-data.pelanggan.index', compact('pelanggans'));
    }

    public function create()
    {
        // Redirect ke index dengan pesan bahwa pelanggan hanya bisa ditambah melalui registrasi
        return redirect()->route('master-data.pelanggan.index')
            ->with('info', 'Pelanggan hanya bisa ditambahkan melalui registrasi di website pelanggan (/pelanggan/login)');
    }

    public function store(Request $request)
    {
        // Prevent manual creation - pelanggan hanya bisa ditambah melalui registrasi
        return redirect()->route('master-data.pelanggan.index')
            ->with('error', 'Tidak bisa menambah pelanggan secara manual. Pelanggan hanya bisa ditambahkan melalui registrasi di website pelanggan.');
    }

    public function edit($id)
    {
        // 🔒 SECURITY: Get pelanggan
        $pelanggan = User::where('role', 'pelanggan')
            ->findOrFail($id);
        
        return view('master-data.pelanggan.edit', compact('pelanggan'));
    }

    public function update(Request $request, $id)
    {
        // 🔒 SECURITY: Get pelanggan
        $pelanggan = User::where('role', 'pelanggan')
            ->findOrFail($id);

        $request->validate([
            'name'    => 'required|string|max:255',
            'email'   => 'required|email|unique:users,email,' . $id,
            'phone'   => 'required|string|max:20',
            'address' => 'nullable|string',
            'password' => 'nullable|min:6|confirmed',
        ]);

        $data = [
            'name'    => $request->name,
            'email'   => $request->email,
            'phone'   => $request->phone,
            'address' => $request->address,
        ];

        // Update password jika diisi
        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
            $data['plain_password'] = $request->password; // CRITICAL: Also update plain password
        }

        $pelanggan->update($data);

        return redirect()->route('master-data.pelanggan.index')
            ->with('success', 'Data pelanggan berhasil diupdate!');
    }

    public function destroy($id)
    {
        // 🔒 SECURITY: Get pelanggan
        $pelanggan = User::where('role', 'pelanggan')
            ->findOrFail($id);
        
        // Cek apakah pelanggan punya order
        if ($pelanggan->orders()->count() > 0) {
            return back()->with('error', 'Tidak bisa menghapus pelanggan yang sudah memiliki pesanan!');
        }

        $pelanggan->delete();

        return redirect()->route('master-data.pelanggan.index')
            ->with('success', 'Data pelanggan berhasil dihapus!');
    }

    public function resetPassword($id)
    {
        // 🔒 SECURITY: Get pelanggan
        $pelanggan = User::where('role', 'pelanggan')
            ->findOrFail($id);
        
        // Reset password to default
        $newPassword = 'password123';
        $pelanggan->update([
            'password' => Hash::make($newPassword),
            'plain_password' => $newPassword, // CRITICAL: Also update plain password
        ]);
        
        return redirect()->route('master-data.pelanggan.index')
            ->with('success', 'Password pelanggan berhasil direset ke: ' . $newPassword);
    }
}
