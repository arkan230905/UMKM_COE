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
        // 🔒 SECURITY: Get all pelanggan users (user_id = null)
        // Pelanggan yang terdaftar melalui website akan memiliki user_id = null
        // Semua owner bisa melihat semua pelanggan yang terdaftar
        $pelanggans = User::where('role', 'pelanggan')
            ->whereNull('user_id') // Only show pelanggan with user_id = null (registered via website)
            ->withCount('orders')
            ->latest()
            ->paginate(15);

        return view('master-data.pelanggan.index', compact('pelanggans'));
    }

    /**
     * Pelanggan hanya bisa ditambah melalui registrasi di website pelanggan
     * Method create() dan store() dihapus untuk mencegah owner menambah pelanggan manual
     */
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

    public function show($id)
    {
        // 🔒 SECURITY: Get pelanggan with user_id = null (registered via website)
        $pelanggan = User::where('role', 'pelanggan')
            ->whereNull('user_id') // Only show pelanggan registered via website
            ->findOrFail($id);
        
        // Load orders jika ada
        $pelanggan->load(['orders' => function($query) {
            $query->latest()->take(10);
        }]);

        return view('master-data.pelanggan.show', compact('pelanggan'));
    }

    public function getPassword($id)
    {
        // 🔒 SECURITY: Get pelanggan with user_id = null
        $pelanggan = User::where('role', 'pelanggan')
            ->whereNull('user_id') // Only show pelanggan registered via website
            ->findOrFail($id);
        
        return response()->json([
            'password' => $pelanggan->password // Hashed password
        ]);
    }

    public function resetPassword(Request $request, $id)
    {
        // 🔒 SECURITY: Get pelanggan with user_id = null
        $pelanggan = User::where('role', 'pelanggan')
            ->whereNull('user_id') // Only show pelanggan registered via website
            ->findOrFail($id);

        $request->validate([
            'password' => 'required|min:6|confirmed',
        ]);

        $pelanggan->update([
            'password' => Hash::make($request->password),
            'plain_password' => $request->password, // Update plain password too
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Password pelanggan berhasil direset!'
        ]);
    }

    public function edit($id)
    {
        // 🔒 SECURITY: Get pelanggan with user_id = null
        $pelanggan = User::where('role', 'pelanggan')
            ->whereNull('user_id') // Only show pelanggan registered via website
            ->findOrFail($id);
        return view('master-data.pelanggan.edit', compact('pelanggan'));
    }

    public function update(Request $request, $id)
    {
        // 🔒 SECURITY: Get pelanggan with user_id = null
        $pelanggan = User::where('role', 'pelanggan')
            ->whereNull('user_id') // Only show pelanggan registered via website
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
            $data['plain_password'] = $request->password; // Update plain password too
        }

        $pelanggan->update($data);

        return redirect()->route('master-data.pelanggan.index')
            ->with('success', 'Data pelanggan berhasil diupdate!');
    }

    public function destroy($id)
    {
        // 🔒 SECURITY: Get pelanggan with user_id = null
        $pelanggan = User::where('role', 'pelanggan')
            ->whereNull('user_id') // Only show pelanggan registered via website
            ->findOrFail($id);
        
        // Cek apakah pelanggan punya order
        if ($pelanggan->orders()->count() > 0) {
            return back()->with('error', 'Tidak bisa menghapus pelanggan yang sudah memiliki pesanan!');
        }

        $pelanggan->delete();

        return redirect()->route('master-data.pelanggan.index')
            ->with('success', 'Data pelanggan berhasil dihapus!');
    }
}
