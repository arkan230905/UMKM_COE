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
        
        // CRITICAL: Always filter by user_id for multi-tenant
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
        // CRITICAL: Filter jabatans by user_id for multi-tenant
        $jabatans = \App\Models\Jabatan::select('id','nama','kategori','tunjangan','asuransi','gaji_pokok','tarif_produk as tarif')
            ->where('user_id', auth()->id())
            ->orderBy('nama')
            ->get();
        
        // Get unique kategori values from Jabatan table
        $kategoris = \App\Models\Jabatan::where('user_id', auth()->id())
            ->whereNotNull('kategori')
            ->where('kategori', '!=', '')
            ->distinct()
            ->pluck('kategori')
            ->map(function($k) {
                return strtolower($k);
            })
            ->unique()
            ->values();
        
        return view('master-data.pegawai.create', compact('jabatans', 'kategoris'));
    }

    // Simpan data baru
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama' => 'required|string|max:255',

            // MULTI-TENANT: email unique dengan safety check
            'email' => [
                'required',
                'email',
                'unique:pegawais,email'
            ],
'no_telepon' => 'required|string|max:20',
            'alamat' => 'required|string',
            'jabatan_id' => 'required|exists:jabatans,id',
            'kategori' => 'required|string',
            'jenis_kelamin' => 'required|in:L,P',
            'bank' => 'required|string|max:100',
            'nomor_rekening' => 'required|string|max:50',
            'nama_rekening' => 'required|string|max:100',
        ], [
            'email.required' => 'Email harus diisi',
            'email.email' => 'Format email tidak valid',
        ]);

        // Manual check for duplicate email (karena global scope)
        $existingPegawai = Pegawai::withoutGlobalScopes()
            ->where('email', $validated['email'])
            ->first();
        
        if ($existingPegawai) {
            return back()
                ->withErrors(['email' => 'Email sudah digunakan oleh pegawai lain: ' . $existingPegawai->nama])
                ->withInput();
        }

        // Manual check for duplicate nama
        $existingNama = Pegawai::where('user_id', auth()->id())
            ->where('nama', $validated['nama'])
            ->first();
        
        if ($existingNama) {
            return back()
                ->withErrors(['nama' => 'Nama pegawai sudah ada'])
                ->withInput();
        }

        $jabatan = \App\Models\Jabatan::find($validated['jabatan_id']);
        
        if (!$jabatan) {
            return back()->withErrors(['error' => 'Jabatan tidak ditemukan'])->withInput();
        }

        $phoneColumn = Schema::hasColumn('pegawais', 'no_telephone') ? 'no_telephone' : 'no_telepon';

        // Generate unique kode_pegawai
        $lastPegawai = Pegawai::withoutGlobalScopes()
            ->orderBy('id', 'desc')
            ->first();
        
        $nextNumber = $lastPegawai ? ($lastPegawai->id + 1) : 1;
        $kodePegawai = 'PGW' . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);
        
        // Pastikan kode unik (jika ada yang dihapus)
        while (Pegawai::withoutGlobalScopes()->where('kode_pegawai', $kodePegawai)->exists()) {
            $nextNumber++;
            $kodePegawai = 'PGW' . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);
        }

        // Prepare data for creation
        $pegawaiData = [
            'kode_pegawai' => $kodePegawai,
            'nama' => $validated['nama'],
            'email' => $validated['email'],
            $phoneColumn => $validated['no_telepon'],
            'alamat' => $validated['alamat'],
            'jenis_kelamin' => $validated['jenis_kelamin'],
            'kategori' => $validated['kategori'],
            'jabatan' => $jabatan->nama,
            'jenis_pegawai' => strtolower($validated['kategori']),

            'gaji_pokok' => $jabatan->gaji_pokok ?? 0,
            'tarif_per_produk' => $jabatan->tarif_produk ?? 0,
            'tunjangan' => $jabatan->tunjangan ?? 0,
            'asuransi' => $jabatan->asuransi ?? 0,
'bank' => $validated['bank'],
            'nomor_rekening' => $validated['nomor_rekening'],
            'nama_rekening' => $validated['nama_rekening'],
            'user_id' => auth()->id(),
        ];
        
        // Add user_id only if column exists
        if (Schema::hasColumn('pegawais', 'user_id')) {
            $pegawaiData['user_id'] = auth()->id(); // CRITICAL: multi-tenant isolation
        }
        
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
        // CRITICAL: Filter jabatans by user_id for multi-tenant
        $jabatans = \App\Models\Jabatan::select('id','nama','kategori','tunjangan','tunjangan_transport','tunjangan_konsumsi','asuransi','gaji_pokok','tarif_produk')
            ->where('user_id', auth()->id())
            ->orderBy('nama')
            ->get();
        
        // Get unique kategori values from Jabatan table
        $kategoris = \App\Models\Jabatan::where('user_id', auth()->id())
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

            'nama' => 'required|string|max:255',
            // MULTI-TENANT: email unique hanya dalam scope user yang sama, kecuali record ini sendiri
            'email' => [
                'required',
                'email',
                \Illuminate\Validation\Rule::unique('pegawais', 'email')
                    ->where('user_id', auth()->id())
                    ->ignore($pegawai->id),
            ],
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

            'gaji_pokok' => $jabatan->gaji_pokok ?? 0,
            'tarif_per_produk' => $jabatan->tarif_produk ?? 0,
            'tunjangan' => $jabatan->tunjangan ?? 0,
            'asuransi' => $jabatan->asuransi ?? 0,
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
