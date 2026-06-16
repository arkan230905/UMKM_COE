<?php

namespace App\Http\Controllers\MasterData;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

class PelangganTableController extends Controller
{
    public function index(Request $request)
    {
        // 🔒 SECURITY: Filter by perusahaan_id for multi-tenant isolation
        $query = User::where('role', 'pelanggan')
            ->where('perusahaan_id', auth()->user()->perusahaan_id)
            ->withCount('orders');

        // Filter Pencarian
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', '%' . $search . '%')
                  ->orWhere('email', 'like', '%' . $search . '%')
                  ->orWhere('phone', 'like', '%' . $search . '%');
            });
        }

        // Filter Rentang Tanggal Pendaftaran
        if ($request->filled('date_start') && $request->filled('date_end')) {
            $query->whereBetween('created_at', [
                $request->date_start . ' 00:00:00',
                $request->date_end . ' 23:59:59'
            ]);
        } elseif ($request->filled('date_start')) {
            $query->where('created_at', '>=', $request->date_start . ' 00:00:00');
        } elseif ($request->filled('date_end')) {
            $query->where('created_at', '<=', $request->date_end . ' 23:59:59');
        }

        $pelanggans = $query->latest()->paginate(15)->withQueryString();
        
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
        // 🔒 SECURITY: Filter by perusahaan_id for multi-tenant isolation
        $pelanggan = User::where('role', 'pelanggan')
            ->where('perusahaan_id', auth()->user()->perusahaan_id)
            ->findOrFail($id);
        
        return view('master-data.pelanggan.edit', compact('pelanggan'));
    }

    public function update(Request $request, $id)
    {
        // 🔒 SECURITY: Filter by perusahaan_id for multi-tenant isolation
        $pelanggan = User::where('role', 'pelanggan')
            ->where('perusahaan_id', auth()->user()->perusahaan_id)
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
        // 🔒 SECURITY: Filter by perusahaan_id for multi-tenant isolation
        $pelanggan = User::where('role', 'pelanggan')
            ->where('perusahaan_id', auth()->user()->perusahaan_id)
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
        // 🔒 SECURITY: Filter by perusahaan_id for multi-tenant isolation
        $pelanggan = User::where('role', 'pelanggan')
            ->where('perusahaan_id', auth()->user()->perusahaan_id)
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
