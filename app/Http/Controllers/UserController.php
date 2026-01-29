<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;

class UserController extends Controller
{
    public function index()
    {
        $users = User::orderBy('role')->orderBy('name')->paginate(10);
        return view('master-data.user.index', compact('users'));
    }

    public function create()
    {
        return view('master-data.user.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
            'role' => 'required|in:admin,owner,pelanggan,pegawai_pembelian,kasir',
            'perusahaan_id' => 'nullable|exists:perusahaan,id',
        ]);

        $validated['password'] = bcrypt($validated['password']);

        User::create($validated);

        return redirect()->route('master-data.user.index')
            ->with('success', 'User berhasil ditambahkan');
    }

    public function edit(User $user)
    {
        return view('master-data.user.edit', compact('user'));
    }

    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'role' => 'required|in:admin,owner,pelanggan,pegawai_pembelian,kasir',
            'perusahaan_id' => 'nullable|exists:perusahaan,id',
        ]);

        if ($request->filled('password')) {
            $validated['password'] = bcrypt($request->password);
        }

        $user->update($validated);

        return redirect()->route('master-data.user.index')
            ->with('success', 'User berhasil diperbarui');
    }

    public function destroy(User $user)
    {
        // Log untuk debugging
        \Log::info('Attempting to delete user', [
            'user_id' => $user->id,
            'user_name' => $user->name,
            'user_email' => $user->email,
            'user_role' => $user->role,
            'request_ip' => request()->ip(),
            'user_agent' => request()->userAgent()
        ]);

        // Cek apakah user adalah owner
        if ($user->role === 'owner') {
            \Log::warning('Attempt to delete owner user blocked', ['user_id' => $user->id]);
            return back()->with('error', 'Owner tidak dapat dihapus!');
        }

        // Cek apakah user adalah admin dan ini admin terakhir
        if ($user->role === 'admin') {
            $adminCount = User::where('role', 'admin')->count();
            \Log::info('Admin delete attempt', [
                'user_id' => $user->id,
                'total_admins' => $adminCount,
                'can_delete' => $adminCount > 1
            ]);
            
            if ($adminCount <= 1) {
                \Log::warning('Last admin cannot be deleted', ['user_id' => $user->id]);
                return back()->with('error', 'Admin terakhir tidak dapat dihapus!');
            }
        }

        // Cek apakah user memiliki data terkait sebelum dihapus
        $relatedData = [];
        
        // Check pegawai dengan email yang sama
        $pegawaiCount = \App\Models\Pegawai::where('email', $user->email)->count();
        if ($pegawaiCount > 0) {
            $relatedData[] = "Pegawai: {$pegawaiCount} records dengan email {$user->email}";
        }
        
        // Check jika user memiliki perusahaan dan ada pegawai di perusahaan tersebut
        if ($user->perusahaan_id) {
            $pegawaiInCompany = \App\Models\Pegawai::where('perusahaan_id', $user->perusahaan_id)->count();
            if ($pegawaiInCompany > 0) {
                $relatedData[] = "Pegawai di perusahaan: {$pegawaiInCompany} records";
            }
        }
        
        if (!empty($relatedData)) {
            \Log::warning('User has related data', [
                'user_id' => $user->id,
                'related_data' => $relatedData
            ]);
            
            return back()->with('error', 'User memiliki data terkait yang tidak dapat dihapus: ' . implode(', ', $relatedData));
        }

        try {
            // Hapus user
            $userId = $user->id;
            $userName = $user->name;
            $user->delete();
            
            \Log::info('User deleted successfully', [
                'deleted_user_id' => $userId,
                'deleted_user_name' => $userName
            ]);
            
            return redirect()->route('master-data.user.index')
                ->with('success', "User '{$userName}' berhasil dihapus");
                
        } catch (\Exception $e) {
            \Log::error('Error deleting user', [
                'user_id' => $user->id,
                'error' => $e->getMessage()
            ]);
            
            return back()->with('error', 'Terjadi kesalahan saat menghapus user: ' . $e->getMessage());
        }
    }
}
