<?php

namespace App\Http\Controllers\MasterData;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class PelangganController extends Controller
{
    public function index(Request $request)
    {
        // 🔒 SECURITY: Get pelanggan yang belong ke current user (owner)
        $userId = auth()->id();
        $query = \App\Models\Pelanggan::where('user_id', $userId);

        // Pencarian
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('nama_pelanggan', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('telepon', 'like', "%{$search}%");
            });
        }

        // Pengurutan
        $sort = $request->get('sort', 'terbaru');
        switch ($sort) {
            case 'terlama':
                $query->oldest();
                break;
            case 'nama_az':
                $query->orderBy('nama_pelanggan', 'asc');
                break;
            case 'nama_za':
                $query->orderBy('nama_pelanggan', 'desc');
                break;
            case 'terbaru':
            default:
                $query->latest();
                break;
        }

        $pelanggans = $query->paginate(15)->withQueryString();

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
        // 🔒 SECURITY: Get pelanggan yang belong ke current user
        $pelanggan = \App\Models\Pelanggan::where('user_id', auth()->id())
            ->findOrFail($id);
        
        return view('master-data.pelanggan.show', compact('pelanggan'));
    }

    public function getPassword($id)
    {
        // 🔒 SECURITY: Get pelanggan yang belong ke current user
        $pelanggan = \App\Models\Pelanggan::where('user_id', auth()->id())
            ->findOrFail($id);
        
        return response()->json([
            'password' => $pelanggan->password // Hashed password
        ]);
    }

    public function resetPassword(Request $request, $id)
    {
        // 🔒 SECURITY: Get pelanggan yang belong ke current user
        $pelanggan = \App\Models\Pelanggan::where('user_id', auth()->id())
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
        // 🔒 SECURITY: Get pelanggan yang belong ke current user
        $pelanggan = \App\Models\Pelanggan::where('user_id', auth()->id())
            ->findOrFail($id);
        return view('master-data.pelanggan.edit', compact('pelanggan'));
    }

    public function update(Request $request, $id)
    {
        // 🔒 SECURITY: Get pelanggan yang belong ke current user
        $pelanggan = \App\Models\Pelanggan::where('user_id', auth()->id())
            ->findOrFail($id);

        $request->validate([
            'nama_pelanggan' => 'required|string|max:255',
            'email'          => 'required|email|unique:pelanggans,email,' . $id,
            'telepon'        => 'required|string|max:20',
            'alamat'         => 'nullable|string',
        ]);

        $pelanggan->update([
            'nama_pelanggan' => $request->nama_pelanggan,
            'email'          => $request->email,
            'telepon'        => $request->telepon,
            'alamat'         => $request->alamat,
        ]);

        return redirect()->route('master-data.pelanggan.index')
            ->with('success', 'Data pelanggan berhasil diupdate!');
    }

    public function destroy($id)
    {
        // 🔒 SECURITY: Get pelanggan yang belong ke current user
        $pelanggan = \App\Models\Pelanggan::where('user_id', auth()->id())
            ->findOrFail($id);

        $pelanggan->delete();

        return redirect()->route('master-data.pelanggan.index')
            ->with('success', 'Data pelanggan berhasil dihapus!');
    }
}
