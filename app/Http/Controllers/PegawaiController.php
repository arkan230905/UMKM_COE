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
        
        $query = Pegawai::query();
        
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
        $jabatans = \App\Models\Jabatan::select('id','nama','kategori','tunjangan','asuransi','gaji','tarif')
            ->orderBy('nama')
            ->get();
        // Get distinct kategori values from jabatans table
        $kategoris = \App\Models\Jabatan::select('kategori')
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
            'nama' => 'required|string|max:255',
            'email' => 'required|email|unique:pegawais,email',
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
            'nama' => $validated['nama'],
            'email' => $validated['email'],
            $phoneColumn => $validated['no_telepon'],
            'alamat' => $validated['alamat'],
            'jenis_kelamin' => $validated['jenis_kelamin'],
            'kategori' => $validated['kategori'],
            'jabatan' => $jabatan->nama,
            'jenis_pegawai' => strtolower($validated['kategori']),
            'gaji_pokok' => $jabatan->gaji,
            'tarif_per_jam' => $jabatan->tarif,
            'tunjangan' => $jabatan->tunjangan,
            'asuransi' => $jabatan->asuransi,
            'bank' => $validated['bank'],
            'nomor_rekening' => $validated['nomor_rekening'],
            'nama_rekening' => $validated['nama_rekening'],
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
        $jabatans = \App\Models\Jabatan::select('id','nama','kategori','tunjangan','asuransi','gaji','tarif')
            ->orderBy('nama')
            ->get();
        $kategoris = \App\Models\Jabatan::select('kategori')
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
        $oldEmail = $pegawai->email;
        $validated = $request->validate([
            'nama' => 'required|string|max:255',
            'email' => 'required|email|unique:pegawais,email,'.$pegawai->id,
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
            'gaji_pokok' => $jabatan->gaji,
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
        // Disabled to prevent timeout - will be synced in background or manually
        // if (strtolower($jenisPegawai) === 'btkl') {
        //     try {
        //         \App\Services\BomSyncService::syncBomFromJabatanChange($jab->id);
        //     } catch (\Exception $e) {
        //         \Log::warning('BOM sync failed during pegawai update: ' . $e->getMessage());
        //     }
        // }

        return redirect()->route('master-data.pegawai.index')->with('success', 'Pegawai berhasil diperbarui.');
    }

    // Hapus pegawai
    public function destroy(Request $request, Pegawai $pegawai)
    {
        // Get jabatan before deleting
        $jabatanNama = $pegawai->jabatan;
        $jabatan = \App\Models\Jabatan::where('nama', $jabatanNama)->first();

        \Log::info('Pegawai destroy started', [
            'pegawai_key' => $pegawai->getKey(),
            'pegawai_id' => $pegawai->id ?? null,
            'kode_pegawai' => $pegawai->kode_pegawai ?? null,
            'nomor_induk_pegawai' => $pegawai->nomor_induk_pegawai ?? null,
            'expects_json' => $request->expectsJson(),
        ]);

        $pegawaiIdCandidates = [];
        if (!empty($pegawai->kode_pegawai)) {
            $pegawaiIdCandidates[] = (string) $pegawai->kode_pegawai;
        }
        if (!empty($pegawai->id)) {
            $pegawaiIdCandidates[] = (string) $pegawai->id;
        }
        if (Schema::hasColumn('pegawais', 'nomor_induk_pegawai') && !empty($pegawai->nomor_induk_pegawai)) {
            $pegawaiIdCandidates[] = (string) $pegawai->nomor_induk_pegawai;
        }
        $pegawaiIdCandidates = array_values(array_unique(array_filter($pegawaiIdCandidates)));

        $blockedBy = [];
        $blockedMessage = null;

        try {
            if (!empty($pegawaiIdCandidates)) {
                if (Schema::hasTable('presensis') && Schema::hasColumn('presensis', 'pegawai_id')) {
                    $count = Presensi::whereIn('pegawai_id', $pegawaiIdCandidates)->count();
                    if ($count > 0) {
                        $blockedBy['presensis'] = $count;
                    }
                }

                if (Schema::hasTable('penggajians') && Schema::hasColumn('penggajians', 'pegawai_id')) {
                    $count = DB::table('penggajians')->whereIn('pegawai_id', $pegawaiIdCandidates)->count();
                    if ($count > 0) {
                        $blockedBy['penggajians'] = $count;
                    }
                }

                if (Schema::hasTable('pegawai_produk_allocations') && Schema::hasColumn('pegawai_produk_allocations', 'pegawai_id')) {
                    $count = DB::table('pegawai_produk_allocations')->whereIn('pegawai_id', $pegawaiIdCandidates)->count();
                    if ($count > 0) {
                        $blockedBy['pegawai_produk_allocations'] = $count;
                    }
                }

                if (Schema::hasTable('users') && Schema::hasColumn('users', 'pegawai_id')) {
                    $count = DB::table('users')->whereIn('pegawai_id', $pegawaiIdCandidates)->count();
                    if ($count > 0) {
                        $blockedBy['users'] = $count;
                    }
                }
            }

            if (Schema::hasTable('verifikasi_wajah') && Schema::hasColumn('verifikasi_wajah', 'kode_pegawai') && !empty($pegawai->kode_pegawai)) {
                $count = DB::table('verifikasi_wajah')->where('kode_pegawai', $pegawai->kode_pegawai)->count();
                if ($count > 0) {
                    $blockedBy['verifikasi_wajah'] = $count;
                }
            }

            if (Schema::hasTable('produksi_proses') && Schema::hasColumn('produksi_proses', 'pegawai_ids') && !empty($pegawai->id)) {
                $pidStr = (string) $pegawai->id;
                $pidInt = is_numeric($pegawai->id) ? (int) $pegawai->id : null;

                $query = DB::table('produksi_proses');
                $count = 0;
                try {
                    $q = clone $query;
                    $q->whereJsonContains('pegawai_ids', $pidStr);
                    if ($pidInt !== null) {
                        $q->orWhereJsonContains('pegawai_ids', $pidInt);
                    }
                    $count = $q->count();
                } catch (\Throwable $e) {
                    $count = 0;
                }

                if ($count > 0) {
                    $blockedBy['produksi_proses'] = $count;
                }
            }

            // Hapus user dari blockedBy karena akan dihapus otomatis
            if (isset($blockedBy['users'])) {
                unset($blockedBy['users']);
            }
            
            if (!empty($blockedBy)) {
                $tableNames = [
                    'presensis' => 'Data Presensi',
                    'penggajians' => 'Data Penggajian',
                    'pegawai_produk_allocations' => 'Alokasi Produk',
                    'verifikasi_wajah' => 'Verifikasi Wajah',
                    'produksi_proses' => 'Proses Produksi',
                ];
                
                $blockedList = [];
                foreach ($blockedBy as $table => $count) {
                    $tableName = $tableNames[$table] ?? $table;
                    $blockedList[] = "$tableName ($count data)";
                }
                
                $blockedMessage = 'Pegawai tidak dapat dihapus karena masih terhubung dengan: ' . implode(', ', $blockedList) . '. Silakan hapus data terkait terlebih dahulu atau nonaktifkan pegawai.';

                \Log::warning('Pegawai destroy blocked', [
                    'pegawai_key' => $pegawai->getKey(),
                    'blocked_by' => $blockedBy,
                ]);

                if ($request->expectsJson()) {
                    return response()->json([
                        'success' => false,
                        'message' => $blockedMessage,
                        'blocked_by' => $blockedBy,
                    ], 409);
                }

                return redirect()
                    ->route('master-data.pegawai.index')
                    ->with('error', $blockedMessage);
            }

            $deleted = DB::transaction(function () use ($pegawai, $pegawaiIdCandidates) {
                // Hapus user terkait terlebih dahulu
                if (!empty($pegawaiIdCandidates)) {
                    DB::table('users')->whereIn('pegawai_id', $pegawaiIdCandidates)->delete();
                }
                
                // Hapus pegawai
                $ok = $pegawai->delete();
                return $ok ? 1 : 0;
            });

            if ($deleted < 1) {
                throw new \RuntimeException('Pegawai tidak terhapus (0 rows affected).');
            }
        } catch (\Throwable $e) {
            \Log::error('Gagal menghapus pegawai', [
                'pegawai_key' => $pegawai->getKey(),
                'message' => $e->getMessage(),
            ]);

            $message = 'Gagal menghapus pegawai: ' . $e->getMessage();
            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'message' => $message], 500);
            }

            return redirect()
                ->route('master-data.pegawai.index')
                ->with('error', $message);
        }

        // Sync BOM when pegawai changes (affects BTKL calculations)
        if ($jabatan && strtolower($jabatan->kategori) === 'btkl') {
            \App\Services\BomSyncService::syncBomFromJabatanChange($jabatan->id);
        }

        \Log::info('Pegawai destroy finished', [
            'pegawai_key' => $pegawai->getKey(),
            'result' => ['pegawais' => 1],
        ]);

        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'message' => 'Pegawai berhasil dihapus.', 'result' => ['pegawais' => 1]]);
        }

        return redirect()->route('master-data.pegawai.index')->with('success', 'Pegawai berhasil dihapus.');
    }
}
