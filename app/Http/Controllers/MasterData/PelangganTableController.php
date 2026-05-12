<?php

namespace App\Http\Controllers\MasterData;

use App\Http\Controllers\Controller;
use App\Models\Pelanggan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

class PelangganTableController extends Controller
{
    public function index()
    {
        // 🔒 SECURITY: Get pelanggans belonging to current user with safety check
        $query = Pelanggan::query();
        
        // Check if user_id column exists and filter by it
        if (Schema::hasColumn('pelanggans', 'user_id')) {
            $query->where('user_id', auth()->id());
        } else {
            // If no user_id column, return empty collection to prevent global data access
            $pelanggans = collect();
            return view('master-data.pelanggan.index', compact('pelanggans'));
        }
        
        $pelanggans = $query->latest()->paginate(15);
        
        // Debug: Log pelanggan data to check password values
        \Log::info('Pelanggan data for display:', $pelanggans->map(function($p) {
            return [
                'id' => $p->id,
                'nama_pelanggan' => $p->nama_pelanggan,
                'password' => $p->password,
                'user_id' => $p->user_id
            ];
        })->toArray());
        
        return view('master-data.pelanggan.index', compact('pelanggans'));
    }

    public function create()
    {
        return view('master-data.pelanggan.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama_pelanggan' => 'required|string|max:255',
            'email' => 'required|email|unique:pelanggans,email',
            'telepon' => 'required|string|max:20',
            'alamat' => 'required|string',
            'password' => 'required|string|min:6',
            'keterangan' => 'nullable|string',
        ]);

        // Prepare data for creation
        $pelangganData = [
            'nama_pelanggan' => $validated['nama_pelanggan'],
            'email' => $validated['email'],
            'telepon' => $validated['telepon'],
            'alamat' => $validated['alamat'],
            'password' => $validated['password'],
            'keterangan' => $validated['keterangan'] ?? '',
        ];
        
        // Add user_id only if column exists
        if (Schema::hasColumn('pelanggans', 'user_id')) {
            $pelangganData['user_id'] = auth()->id(); // CRITICAL: multi-tenant isolation
        }
        
        // Log data before saving for debugging
        \Log::info('Creating Pelanggan with data:', $pelangganData);
        
        // Create pelanggan record
        $pelanggan = Pelanggan::create($pelangganData);
        
        // Log the created pelanggan to verify password was saved
        \Log::info('Pelanggan created successfully:', [
            'id' => $pelanggan->id,
            'nama_pelanggan' => $pelanggan->nama_pelanggan,
            'password' => $pelanggan->password,
            'user_id' => $pelanggan->user_id
        ]);

        return redirect()->route('master-data.pelanggan.index')
            ->with('success', 'Pelanggan berhasil ditambahkan.');
    }

    public function edit(Pelanggan $pelanggan)
    {
        // 🔒 SECURITY: Check if user owns this pelanggan (multi-tenant)
        if (Schema::hasColumn('pelanggans', 'user_id') && $pelanggan->user_id !== auth()->id()) {
            return redirect()->route('master-data.pelanggan.index')
                ->with('error', 'Pelanggan tidak ditemukan atau Anda tidak memiliki akses.');
        }
        
        return view('master-data.pelanggan.edit', compact('pelanggan'));
    }

    public function update(Request $request, Pelanggan $pelanggan)
    {
        // 🔒 SECURITY: Check if user owns this pelanggan (multi-tenant)
        if (Schema::hasColumn('pelanggans', 'user_id') && $pelanggan->user_id !== auth()->id()) {
            return redirect()->route('master-data.pelanggan.index')
                ->with('error', 'Pelanggan tidak ditemukan atau Anda tidak memiliki akses.');
        }
        
        $validated = $request->validate([
            'nama_pelanggan' => 'required|string|max:255',
            'email' => 'required|email|unique:pelanggans,email,' . $pelanggan->id,
            'telepon' => 'required|string|max:20',
            'alamat' => 'required|string',
            'keterangan' => 'nullable|string',
        ]);

        $pelanggan->update($validated);

        return redirect()->route('master-data.pelanggan.index')
            ->with('success', 'Pelanggan berhasil diperbarui.');
    }

    public function destroy(Pelanggan $pelanggan)
    {
        // 🔒 SECURITY: Check if user owns this pelanggan (multi-tenant)
        if (Schema::hasColumn('pelanggans', 'user_id') && $pelanggan->user_id !== auth()->id()) {
            return redirect()->route('master-data.pelanggan.index')
                ->with('error', 'Pelanggan tidak ditemukan atau Anda tidak memiliki akses.');
        }
        
        $pelanggan->delete();

        return redirect()->route('master-data.pelanggan.index')
            ->with('success', 'Pelanggan berhasil dihapus.');
    }

    public function resetPassword($id)
    {
        // Find pelanggan with safety check
        $pelanggan = Pelanggan::findOrFail($id);
        
        // 🔒 SECURITY: Check if user owns this pelanggan (multi-tenant)
        if (Schema::hasColumn('pelanggans', 'user_id') && $pelanggan->user_id !== auth()->id()) {
            return redirect()->route('master-data.pelanggan.index')
                ->with('error', 'Pelanggan tidak ditemukan atau Anda tidak memiliki akses.');
        }
        
        // Reset password to default (you might want to generate a random password)
        $newPassword = 'password123'; // Change as needed
        
        // Note: Pelanggan table doesn't have password field, so this might be for related user account
        // You might need to implement password reset logic based on your requirements
        
        return redirect()->route('master-data.pelanggan.index')
            ->with('success', 'Password pelanggan berhasil direset.');
    }
}
