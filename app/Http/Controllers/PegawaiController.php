<?php

namespace App\Http\Controllers;

use App\Models\Pegawai;
use App\Models\Presensi;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class PegawaiController extends Controller
{
    // Menampilkan daftar pegawai dengan paginasi dan pencarian
    public function index()
    {
        $search = request('search');
        $jenis = request('jenis');
        
        $query = Pegawai::where('user_id', auth()->id());
        
        // Filter berdasarkan jenis pegawai (opsional)
        if ($jenis && in_array(strtolower((string)$jenis), ['btkl','btktl'])) {
            $query->where('jenis_pegawai', strtoupper($jenis));
        }
        
        // Pencarian
        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('nama', 'like', '%' . $search . '%')
                  ->orWhere('email', 'like', '%' . $search . '%')
                  ->orWhere('no_telepon', 'like', '%' . $search . '%')
                  ->orWhere('jabatan', 'like', '%' . $search . '%');
            });
        }
        
        // Paginasi dengan 10 item per halaman
        $pegawais = $query->orderBy('nama')->paginate(10);
        
        return view('master-data.pegawai.index', compact('pegawais', 'jenis', 'search'));
    }

    // Tampilkan form create
    public function create()
    {
        $jabatans = \App\Models\Jabatan::where('user_id', auth()->id())
            ->select('id','nama','kategori','tunjangan','asuransi','gaji_pokok','tarif')
            ->orderBy('nama')
            ->get();
        // Get distinct kategori values from jabatans table
        $kategoris = \App\Models\Jabatan::where('user_id', auth()->id())
            ->select('kategori')
            ->whereNotNull('kategori')
            ->where('kategori', '!=', '')
            ->distinct()
            ->orderBy('kategori')
            ->pluck('kategori');
        
        return view('master-data.pegawai.create', compact('jabatans', 'kategoris'));
    }

    // Simpan data baru
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama' => 'required|string|max:255|unique:pegawais,nama,NULL,id,user_id,'.auth()->id(),
            'email' => 'required|email|unique:pegawais,email,NULL,id,user_id,'.auth()->id(),
            'no_telepon' => 'required|string|max:20',
            'alamat' => 'required|string',
            'jabatan_id' => 'required|exists:jabatans,id',
            'kategori' => 'required|string',
            'jenis_kelamin' => 'required|in:L,P',
            'bank' => 'required|string|max:100',
            'nomor_rekening' => 'required|string|max:50',
            'nama_rekening' => 'required|string|max:100',
        ]);

        $jabatan = \App\Models\Jabatan::find($validated['jabatan_id']);
        
        if (!$jabatan) {
            return back()->withErrors(['error' => 'Jabatan tidak ditemukan'])->withInput();
        }

        $phoneColumn = Schema::hasColumn('pegawais', 'no_telephone') ? 'no_telephone' : 'no_telepon';

        // Prepare data for creation
        $pegawaiData = [
            'kode_pegawai' => 'PGW' . str_pad(Pegawai::where('user_id', auth()->id())->count() + 1, 4, '0', STR_PAD_LEFT),
            'nama' => $validated['nama'],
            'email' => $validated['email'],
            $phoneColumn => $validated['no_telepon'],
            'alamat' => $validated['alamat'],
            'jenis_kelamin' => $validated['jenis_kelamin'],
            'kategori' => $validated['kategori'],
            'jabatan' => $jabatan->nama,
            'jenis_pegawai' => strtolower($validated['kategori']),
            'gaji_pokok' => $jabatan->gaji_pokok ?? $jabatan->gaji,
            'tarif_per_jam' => $jabatan->tarif,
            'tunjangan' => $jabatan->tunjangan,
            'asuransi' => $jabatan->asuransi,
            'bank' => $validated['bank'],
            'nomor_rekening' => $validated['nomor_rekening'],
            'nama_rekening' => $validated['nama_rekening'],
            'user_id' => auth()->id(),
        ];
        
        // Log data being saved for debugging
        \Log::info('Creating new Pegawai:', $pegawaiData);
        
        // Create pegawai record
        $pegawai = Pegawai::create($pegawaiData);

        // Auto-create/update user account for this pegawai
        $user = User::where('pegawai_id', $pegawai->id)->first();
        if (!$user) {
            $user = User::where('email', $pegawai->email)->first();
        }
        if ($user) {
            $user->update([
                'name' => $pegawai->nama,
                'email' => $pegawai->email,
                'role' => User::ROLE_PEGAWAI,
                'pegawai_id' => $pegawai->id,
            ]);
        } else {
            User::create([
                'name' => $pegawai->nama,
                'email' => $pegawai->email,
                'password' => Hash::make(Str::random(32)),
                'role' => User::ROLE_PEGAWAI,
                'pegawai_id' => $pegawai->id,
                'email_verified_at' => now(),
            ]);
        }
        
        // Sync BOM when pegawai changes (affects BTKL calculations)
        if (strtolower($validated['kategori']) === 'btkl') {
            \App\Services\BomSyncService::syncBomFromJabatanChange($jabatan->id);
        }

        return redirect()->route('master-data.pegawai.index')->with('success', 'Pegawai berhasil ditambahkan.');
    }

    // Form edit pegawai
    public function edit(Pegawai $pegawai)
    {
        // Ensure user can only edit their own pegawai
        if ($pegawai->user_id !== auth()->id()) {
            abort(403, 'Unauthorized');
        }
        
        $jabatans = \App\Models\Jabatan::where('user_id', auth()->id())
            ->select('id','nama','kategori','tunjangan','asuransi','gaji_pokok','tarif')
            ->orderBy('nama')
            ->get();
        $kategoris = \App\Models\Jabatan::where('user_id', auth()->id())
            ->select('kategori')
            ->whereNotNull('kategori')
            ->where('kategori', '!=', '')
            ->distinct()
            ->orderBy('kategori')
            ->pluck('kategori');
        
        return view('master-data.pegawai.edit', compact('pegawai','jabatans', 'kategoris'));
    }

    // Update data pegawai
    public function update(Request $request, Pegawai $pegawai)
    {
        // Ensure user can only update their own pegawai
        if ($pegawai->user_id !== auth()->id()) {
            abort(403, 'Unauthorized');
        }
        
        $oldEmail = $pegawai->email;
        $validated = $request->validate([
            'nama' => 'required|string|max:255|unique:pegawais,nama,'.$pegawai->id.',id,user_id,'.auth()->id(),
            'email' => 'required|email|unique:pegawais,email,'.$pegawai->id.',id,user_id,'.auth()->id(),
            'no_telepon' => 'required|string|max:20',
            'alamat' => 'required|string',
            'jabatan_id' => 'required|exists:jabatans,id',
            'kategori' => 'required|string',
            'jenis_kelamin' => 'required|in:L,P',
            'bank' => 'nullable|string|max:100',
            'nomor_rekening' => 'nullable|string|max:50',
            'nama_rekening' => 'nullable|string|max:100',
        ]);

        $jabatan = \App\Models\Jabatan::find($validated['jabatan_id']);
        
        if (!$jabatan) {
            return back()->withErrors(['error' => 'Jabatan tidak ditemukan'])->withInput();
        }
        
        $phoneColumn = Schema::hasColumn('pegawais', 'no_telephone') ? 'no_telephone' : 'no_telepon';

        // Prepare data for update
        $updateData = [
            'nama' => $validated['nama'],
            'email' => $validated['email'],
            $phoneColumn => $validated['no_telepon'],
            'alamat' => $validated['alamat'],
            'jenis_kelamin' => $validated['jenis_kelamin'],
            'kategori' => $validated['kategori'],
            'jabatan' => $jabatan->nama,
            'jenis_pegawai' => strtolower($validated['kategori']),
            'gaji_pokok' => $jabatan->gaji_pokok ?? $jabatan->gaji,
            'tarif_per_jam' => $jabatan->tarif,
            'tunjangan' => $jabatan->tunjangan,
            'asuransi' => $jabatan->asuransi,
        ];
        
        // Add bank info if provided
        if (!empty($validated['bank'])) {
            $updateData['bank'] = $validated['bank'];
            $updateData['nomor_rekening'] = $validated['nomor_rekening'];
            $updateData['nama_rekening'] = $validated['nama_rekening'];
        }
        
        // Log the update data for debugging
        \Log::info('Updating Pegawai:', $updateData);
        
        // Update pegawai record
        $pegawai->update($updateData);

        // Auto-create/update user account for this pegawai (keep it linked by pegawai_id)
        $user = User::where('pegawai_id', $pegawai->id)->first();
        if (!$user && $oldEmail) {
            $user = User::where('email', $oldEmail)->first();
        }
        if (!$user) {
            $user = User::where('email', $pegawai->email)->first();
        }

        if ($user) {
            $user->update([
                'name' => $pegawai->nama,
                'email' => $pegawai->email,
                'role' => User::ROLE_PEGAWAI,
                'pegawai_id' => $pegawai->id,
            ]);
        } else {
            User::create([
                'name' => $pegawai->nama,
                'email' => $pegawai->email,
                'password' => Hash::make(Str::random(32)),
                'role' => User::ROLE_PEGAWAI,
                'pegawai_id' => $pegawai->id,
                'email_verified_at' => now(),
            ]);
        }
        
        // Sync BOM when pegawai changes (affects BTKL calculations)
        if (strtolower($validated['kategori']) === 'btkl') {
            \App\Services\BomSyncService::syncBomFromJabatanChange($jabatan->id);
        }

        return redirect()->route('master-data.pegawai.index')->with('success', 'Pegawai berhasil diperbarui.');
    }

    // Hapus pegawai
    public function destroy(Pegawai $pegawai)
    {
        // Ensure user can only delete their own pegawai
        if ($pegawai->user_id !== auth()->id()) {
            abort(403, 'Unauthorized');
        }
        
        try {
            // Delete associated user account
            $user = User::where('pegawai_id', $pegawai->id)->first();
            if ($user) {
                $user->delete();
            }
            
            // Delete pegawai
            $pegawai->delete();
            
            return redirect()->route('master-data.pegawai.index')->with('success', 'Pegawai berhasil dihapus.');
        } catch (\Exception $e) {
            return redirect()->route('master-data.pegawai.index')->with('error', 'Gagal menghapus pegawai: ' . $e->getMessage());
        }
    }
}
