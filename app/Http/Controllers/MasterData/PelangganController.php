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
        // 🔒 SECURITY: Filter by perusahaan_id untuk multi-tenant isolation
        $query = User::where('role', 'pelanggan')
            ->where('perusahaan_id', auth()->user()->perusahaan_id)
            ->withCount(['orders' => function($q) {
                $q->withoutGlobalScopes()
                  ->where('perusahaan_id', auth()->id());
            }]);

        // Pencarian
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        // Pengurutan
        $sort = $request->get('sort', 'terbaru');
        switch ($sort) {
            case 'terlama':
                $query->oldest();
                break;
            case 'nama_az':
                $query->orderBy('name', 'asc');
                break;
            case 'nama_za':
                $query->orderBy('name', 'desc');
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
        // 🔒 SECURITY: Get pelanggan dengan filter perusahaan_id
        $pelanggan = User::where('role', 'pelanggan')
            ->where('perusahaan_id', auth()->user()->perusahaan_id)
            ->findOrFail($id);
        
        return view('master-data.pelanggan.show', compact('pelanggan'));
    }

    public function getPassword($id)
    {
        // 🔒 SECURITY: Get pelanggan dengan filter perusahaan_id
        $pelanggan = User::where('role', 'pelanggan')
            ->where('perusahaan_id', auth()->user()->perusahaan_id)
            ->findOrFail($id);
        
        return response()->json([
            'password' => $pelanggan->password // Hashed password
        ]);
    }

    public function resetPassword(Request $request, $id)
    {
        // 🔒 SECURITY: Get pelanggan dengan filter perusahaan_id
        $pelanggan = User::where('role', 'pelanggan')
            ->where('perusahaan_id', auth()->user()->perusahaan_id)
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
        // 🔒 SECURITY: Get pelanggan dengan filter perusahaan_id
        $pelanggan = User::where('role', 'pelanggan')
            ->where('perusahaan_id', auth()->user()->perusahaan_id)
            ->findOrFail($id);
        return view('master-data.pelanggan.edit', compact('pelanggan'));
    }

    public function update(Request $request, $id)
    {
        // 🔒 SECURITY: Get pelanggan dengan filter perusahaan_id
        $pelanggan = User::where('role', 'pelanggan')
            ->where('perusahaan_id', auth()->user()->perusahaan_id)
            ->findOrFail($id);

        $request->validate([
            'name'    => 'required|string|max:255',
            'email'   => 'required|email|unique:users,email,' . $id,
            'phone'   => 'required|string|max:20',
            'address' => 'nullable|string',
        ]);

        $pelanggan->update([
            'name'    => $request->name,
            'email'   => $request->email,
            'phone'   => $request->phone,
            'address' => $request->address,
        ]);

        return redirect()->route('master-data.pelanggan.index')
            ->with('success', 'Data pelanggan berhasil diupdate!');
    }

    public function destroy($id)
    {
        // 🔒 SECURITY: Get pelanggan dengan filter perusahaan_id
        $pelanggan = User::where('role', 'pelanggan')
            ->where('perusahaan_id', auth()->user()->perusahaan_id)
            ->findOrFail($id);

        $pelanggan->delete();

        return redirect()->route('master-data.pelanggan.index')
            ->with('success', 'Data pelanggan berhasil dihapus!');
    }
}
