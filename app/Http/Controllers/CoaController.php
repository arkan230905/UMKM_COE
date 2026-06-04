<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Coa;
use App\Models\Bop;
use App\Models\CoaPeriod;
use App\Models\CoaPeriodBalance;

class CoaController extends Controller
{
    public function index(Request $request)
    {
        // Get periode yang dipilih atau periode saat ini
        $periodId = $request->get('period_id');
        $periode = null;
        
        if ($periodId) {
            $periode = CoaPeriod::find($periodId);
        }
        
        // Jika tidak ada periode dipilih, gunakan periode saat ini
        if (!$periode) {
            $periode = CoaPeriod::getCurrentPeriod();
        }
        
        // Get semua periode untuk dropdown
        $periods = CoaPeriod::orderBy('periode', 'desc')->get();

        // Get semua COA dengan urutan hierarkis (parent diikuti children)
        $coas = Coa::whereNotNull('nama_akun')
            ->where('nama_akun', '!=', '')
            ->where('user_id', auth()->id())
            ->orderBy('kode_akun')
            ->get();
        
        // Cek COA yang diperlukan untuk penggajian
        $requiredCoas = ['52', '54', '513', '514', '515', '516', '111', '112'];
        $coaNames = [
            '52' => 'Beban Gaji Pokok',
            '54' => 'Beban BOP',
            '513' => 'Beban Tunjangan',
            '514' => 'Beban Asuransi',
            '515' => 'Beban Bonus',
            '516' => 'Beban Lainnya',
            '111' => 'Kas/Bank',
            '112' => 'Kas Kecil'
        ];
        
        $existingCoas = $coas->pluck('kode_akun')->toArray();
        $missingCoas = array_diff($requiredCoas, $existingCoas);
        
        // Tampilkan warning jika ada COA yang hilang dengan nama akun yang lebih user-friendly
        // Gunakan key 'warning_coa' agar hanya tampil di COA page, bukan di page lain
        if (!empty($missingCoas)) {
            $missingNames = array_map(function($code) use ($coaNames) {
                return $coaNames[$code] ?? $code;
            }, $missingCoas);
            
            session()->flash('warning_coa', 'Akun yang belum ada: ' . implode(', ', $missingNames) . 
                '. Silakan tambahkan akun tersebut terlebih dahulu.');
        }
        
        // Get saldo untuk setiap COA berdasarkan periode
        $saldoPeriode = [];
        $posisiAkun = [];
        foreach ($coas as $coa) {
            // Untuk akun persediaan bahan baku dan bahan pendukung, ambil dari tabel bahan
            $saldoAwal = $this->getInventorySaldoAwalForCoa($coa->kode_akun);
            
            // Jika tidak ada di inventory, gunakan saldo_awal dari COA table
            if ($saldoAwal === null) {
                $saldoAwal = $coa->saldo_awal ?? 0;
            }
            
            $saldoPeriode[$coa->id] = $saldoAwal;
            
            // Hitung posisi akun berdasarkan digit pertama kode akun
            // Akun 1xx, 5xx, 6xx = debit normal
            // Akun 2xx, 3xx, 4xx = kredit normal
            $firstDigit = substr($coa->kode_akun, 0, 1);
            $isDebitNormal = !in_array($firstDigit, ['2', '3', '4']);
            
            $posisiAkun[$coa->id] = $isDebitNormal ? 'Debit' : 'Kredit';
        }
        
        return view('master-data.coa.index', compact('coas', 'periode', 'periods', 'saldoPeriode', 'posisiAkun'));
    }
    
    /**
     * Get saldo awal inventory untuk COA tertentu
     * Mengambil dari bahan_bakus atau bahan_pendukungs berdasarkan coa_persediaan_id = kode_akun
     */
    private function getInventorySaldoAwalForCoa($kodeAkun)
    {
        // DISABLED - Logika ini dinonaktifkan untuk mencegah perhitungan saldo awal dari bahan
        // Bahan baku dan bahan pendukung tidak lagi berkontribusi ke saldo awal COA
        
        \Log::info("Skipping inventory saldo awal calculation for COA", [
            'kode_akun' => $kodeAkun,
            'reason' => 'Inventory saldo awal calculation disabled for bahan baku/pendukung'
        ]);
        
        return null; // Selalu return null agar menggunakan saldo_awal dari COA table
        
        // COMMENTED OUT - Logika lama yang menghitung dari bahan
        /*
        $userId = auth()->id();

        // Cari di bahan_bakus milik user ini
        $bahanBakus = \App\Models\BahanBaku::where('user_id', $userId)
            ->where('coa_persediaan_id', $kodeAkun)
            ->get();

        $total = 0;
        $found = false;

        foreach ($bahanBakus as $bahan) {
            $total += ($bahan->saldo_awal ?? 0) * ($bahan->harga_satuan ?? 0);
            $found = true;
        }

        // Cari di bahan_pendukungs milik user ini
        $bahanPendukungs = \App\Models\BahanPendukung::where('user_id', $userId)
            ->where('coa_persediaan_id', $kodeAkun)
            ->get();

        foreach ($bahanPendukungs as $bahan) {
            $total += ($bahan->saldo_awal ?? 0) * ($bahan->harga_satuan ?? 0);
            $found = true;
        }

        return $found ? $total : null;
        */
    }
    
    /**
     * Get saldo awal untuk periode tertentu
     */
    private function getSaldoAwalPeriode($coa, $periode)
    {
        // Cek apakah ada saldo periode
        $periodBalance = CoaPeriodBalance::where('coa_id', $coa->id)
            ->where('period_id', $periode->id)
            ->first();

        if (!$periodBalance) {
            // Fallback untuk data lama yang belum ter-backfill
            $periodBalance = CoaPeriodBalance::where('kode_akun', $coa->kode_akun)
                ->where('period_id', $periode->id)
                ->first();
        }
        
        if ($periodBalance) {
            return $periodBalance->saldo_awal;
        }
        
        // Jika tidak ada, cek periode sebelumnya
        $previousPeriod = $periode->getPreviousPeriod();
        if ($previousPeriod) {
            $previousBalance = CoaPeriodBalance::where('coa_id', $coa->id)
                ->where('period_id', $previousPeriod->id)
                ->first();

            if (!$previousBalance) {
                // Fallback untuk data lama yang belum ter-backfill
                $previousBalance = CoaPeriodBalance::where('kode_akun', $coa->kode_akun)
                    ->where('period_id', $previousPeriod->id)
                    ->first();
            }
            
            if ($previousBalance) {
                return $previousBalance->saldo_akhir;
            }
        }
        
        // Jika tidak ada periode sebelumnya, gunakan saldo awal dari COA
        return $coa->saldo_awal ?? 0;
    }

    public function create()
    {

        // Ambil semua COA sebagai pilihan akun induk, urut hierarkis
        // CRITICAL: Filter by user_id untuk multi-tenant isolation
        $parentCoas = Coa::withoutGlobalScopes()
            ->where('user_id', auth()->id())
            ->whereNotNull('nama_akun')
->where('nama_akun', '!=', '')
            ->orderByRaw("RPAD(kode_akun, 10, '0'), LENGTH(kode_akun)")
            ->get(['id', 'kode_akun', 'nama_akun', 'tipe_akun', 'kategori_akun', 'saldo_normal']);

        return view('master-data.coa.create', compact('parentCoas'));
    }

    public function store(Request $request)
    {
        // Jika user memilih akun induk dan mode auto-generate
        if ($request->filled('parent_coa_id') && $request->boolean('auto_generate_kode')) {

            // CRITICAL: Filter by user_id untuk multi-tenant isolation
            $parentCoa = Coa::withoutGlobalScopes()
                ->where('user_id', auth()->id())
                ->find($request->parent_coa_id);
                
if ($parentCoa) {
                $generatedKode = Coa::generateChildCode($parentCoa->kode_akun);
                $request->merge(['kode_akun' => $generatedKode]);
            }
        }

        // Normalize tipe_akun: map alias ke nilai Bahasa Indonesia yang konsisten
        $tipeAkunMap = [
            'ASET'       => 'Aset',
            'Aset'       => 'Aset',
            'Asset'      => 'Aset',
            'KEWAJIBAN'  => 'Kewajiban',
            'Kewajiban'  => 'Kewajiban',
            'Liability'  => 'Kewajiban',
            'MODAL'      => 'Modal',
            'Modal'      => 'Modal',
            'Equity'     => 'Modal',
            'Ekuitas'    => 'Modal',
            'PENDAPATAN' => 'Pendapatan',
            'Pendapatan' => 'Pendapatan',
            'Revenue'    => 'Pendapatan',
            'BEBAN'      => 'Beban',
            'Beban'      => 'Beban',
            'Biaya'      => 'Beban',
            'Expense'    => 'Beban',
        ];
        if (isset($tipeAkunMap[$request->tipe_akun])) {
            $request->merge(['tipe_akun' => $tipeAkunMap[$request->tipe_akun]]);
        }

        // CRITICAL: Add user_id to unique validation for multi-tenant isolation
        $validated = $request->validate([
            'kode_akun' => [
                'required',

                'unique:coas,kode_akun,NULL,id,user_id,' . auth()->id(),
'max:50'
            ],
            'nama_akun' => 'required|string|max:255',
            'tipe_akun' => 'required|in:Aset,Kewajiban,Modal,Pendapatan,Beban',
            'saldo_normal' => 'nullable|in:debit,kredit',
            'saldo_awal' => 'nullable|numeric',
            'tanggal_saldo_awal' => 'nullable|date',
            'keterangan' => 'nullable|string',
            'posted_saldo_awal' => 'nullable|boolean',
        ], [
            'kode_akun.unique' => 'Kode akun sudah ada. Silakan gunakan kode akun yang berbeda.',
            'kode_akun.required' => 'Kode akun wajib diisi.',
            'nama_akun.required' => 'Nama akun wajib diisi.',
            'tipe_akun.required' => 'Tipe akun wajib dipilih.',
            'tipe_akun.in' => 'Tipe akun harus salah satu dari: Aset, Kewajiban, Modal, Pendapatan, Beban',
        ]);

        $coaData = [
            'kode_akun'      => $validated['kode_akun'],
            'nama_akun'      => $validated['nama_akun'],
            'tipe_akun'      => $validated['tipe_akun'],
            'kategori_akun'  => $request->kategori_akun ?? '-',
            'saldo_normal'   => $request->saldo_normal ?? 'debit',
            'saldo_awal'     => $request->saldo_awal ?? 0,
            'keterangan'     => $request->keterangan,
            'posted_saldo_awal' => $request->boolean('posted_saldo_awal') ? 1 : 0,
            'company_id'     => auth()->user()->company_id ?? 1,
        ];

        if ($request->has('tanggal_saldo_awal') && $request->tanggal_saldo_awal) {
            $coaData['tanggal_saldo_awal'] = $request->tanggal_saldo_awal;
        }

        $coa = Coa::create($coaData);

        if ($coa->tipe_akun === 'Beban') {
            Bop::create([
                'coa_id' => $coa->id,
                'keterangan' => 'Otomatis dari COA',
            ]);
        }

        return redirect()->route('master-data.coa.index')->with('success',
            "COA berhasil ditambahkan: {$coa->kode_akun} - {$coa->nama_akun}");
    }

    public function edit(Coa $coa)
    {

        // CRITICAL: Filter by user_id untuk multi-tenant isolation
        $parentCoas = Coa::withoutGlobalScopes()
            ->where('user_id', auth()->id())
            ->whereNotNull('nama_akun')
->where('nama_akun', '!=', '')
            ->where('id', '!=', $coa->id)
            ->orderByRaw("RPAD(kode_akun, 10, '0'), LENGTH(kode_akun)")
            ->get(['id', 'kode_akun', 'nama_akun', 'tipe_akun', 'kategori_akun', 'saldo_normal']);

        return view('master-data.coa.edit', compact('coa', 'parentCoas'));
    }

    public function update(Request $request, Coa $coa)
    {
        // Normalize tipe_akun: map uppercase/alias ke nilai Bahasa Indonesia yang konsisten
        $tipeAkunMap = [
            'ASET'       => 'Aset',
            'Aset'       => 'Aset',
            'Asset'      => 'Aset',
            'KEWAJIBAN'  => 'Kewajiban',
            'Kewajiban'  => 'Kewajiban',
            'Liability'  => 'Kewajiban',
            'MODAL'      => 'Modal',
            'Modal'      => 'Modal',
            'Equity'     => 'Modal',
            'Ekuitas'    => 'Modal',
            'PENDAPATAN' => 'Pendapatan',
            'Pendapatan' => 'Pendapatan',
            'Revenue'    => 'Pendapatan',
            'BEBAN'      => 'Beban',
            'Beban'      => 'Beban',
            'Biaya'      => 'Beban',
            'Expense'    => 'Beban',
        ];
        if (isset($tipeAkunMap[$request->tipe_akun])) {
            $request->merge(['tipe_akun' => $tipeAkunMap[$request->tipe_akun]]);
        }

        // CRITICAL: Add user_id to unique validation for multi-tenant isolation
        $validated = $request->validate([
            'kode_akun' => [
                'required',

                'unique:coas,kode_akun,' . $coa->id . ',id,user_id,' . auth()->id(),
'max:50'
            ],
            'nama_akun' => 'required|string|max:255',
            'tipe_akun' => 'required|in:Aset,Kewajiban,Modal,Pendapatan,Beban',
            'saldo_normal' => 'nullable|in:debit,kredit',
            'saldo_awal' => 'nullable|numeric',
            'tanggal_saldo_awal' => 'nullable|date',
            'keterangan' => 'nullable|string',
            'posted_saldo_awal' => 'nullable|boolean',
        ], [
            'kode_akun.unique' => 'Kode akun sudah ada. Silakan gunakan kode akun yang berbeda.',
            'kode_akun.required' => 'Kode akun wajib diisi.',
            'nama_akun.required' => 'Nama akun wajib diisi.',
            'tipe_akun.required' => 'Tipe akun wajib dipilih.',
            'tipe_akun.in' => 'Tipe akun harus salah satu dari: Asset, Liability, Equity, Revenue, Expense',
        ]);

        $coa->update([
            'kode_akun' => $validated['kode_akun'],
            'nama_akun' => $validated['nama_akun'],
            'tipe_akun' => $validated['tipe_akun'],
            'saldo_normal' => $request->saldo_normal,
            'saldo_awal' => $request->saldo_awal,
            'tanggal_saldo_awal' => $request->tanggal_saldo_awal,
            'keterangan' => $request->keterangan,
            'posted_saldo_awal' => $request->boolean('posted_saldo_awal'),
        ]);

        return redirect()->route('master-data.coa.index')->with('success', 'COA berhasil diperbarui.');
    }

    public function destroy(Coa $coa)
    {
        $coaColumn = \Illuminate\Support\Facades\Schema::hasColumn('journal_lines', 'coa_id') ? 'coa_id' : 'account_id';

        // Cek apakah punya child accounts
        $childCount = \Illuminate\Support\Facades\DB::table('coas')
            ->where('kode_induk', $coa->kode_akun)
            ->where('company_id', $coa->company_id)
            ->count();
        if ($childCount > 0) {
            return redirect()->route('master-data.coa.index')
                ->with('error', "Tidak dapat menghapus COA {$coa->kode_akun} karena masih memiliki {$childCount} sub-akun. Hapus sub-akun terlebih dahulu.");
        }

        // Cek jurnal_umum
        if (\App\Models\JurnalUmum::where($coaColumn, $coa->id)->count() > 0) {
            return redirect()->route('master-data.coa.index')
                ->with('error', "Tidak dapat menghapus COA {$coa->kode_akun} karena sudah digunakan dalam transaksi jurnal.");
        }

        // Cek pembayaran_beban
        $pembayaranCount = \Illuminate\Support\Facades\DB::table('pembayaran_beban')
            ->where('akun_kas_id', $coa->id)
            ->orWhere('akun_beban_id', $coa->id)
            ->count();
        if ($pembayaranCount > 0) {
            return redirect()->route('master-data.coa.index')
                ->with('error', "Tidak dapat menghapus COA {$coa->kode_akun} karena masih digunakan di {$pembayaranCount} data pembayaran beban.");
        }

        // Cek bahan_bakus
        $bahanBakuCount = \Illuminate\Support\Facades\DB::table('bahan_bakus')
            ->where('coa_persediaan_id', $coa->kode_akun)
            ->orWhere('coa_hpp_id', $coa->kode_akun)
            ->orWhere('coa_pembelian_id', $coa->kode_akun)
            ->count();
        if ($bahanBakuCount > 0) {
            return redirect()->route('master-data.coa.index')
                ->with('error', "Tidak dapat menghapus COA {$coa->kode_akun} karena masih digunakan oleh {$bahanBakuCount} bahan baku.");
        }

        // Cek bahan_pendukungs
        $bahanPendukungCount = \Illuminate\Support\Facades\DB::table('bahan_pendukungs')
            ->where('coa_persediaan_id', $coa->kode_akun)
            ->orWhere('coa_hpp_id', $coa->kode_akun)
            ->orWhere('coa_pembelian_id', $coa->kode_akun)
            ->count();
        if ($bahanPendukungCount > 0) {
            return redirect()->route('master-data.coa.index')
                ->with('error', "Tidak dapat menghapus COA {$coa->kode_akun} karena masih digunakan oleh {$bahanPendukungCount} bahan pendukung.");
        }

        // Cek produks
        $produkCount = \Illuminate\Support\Facades\DB::table('produks')
            ->where('coa_persediaan_id', $coa->id)
            ->count();
        if ($produkCount > 0) {
            return redirect()->route('master-data.coa.index')
                ->with('error', "Tidak dapat menghapus COA {$coa->kode_akun} karena masih digunakan oleh {$produkCount} produk.");
        }

        // Hapus dengan try-catch untuk tangkap FK violation yang tidak terduga
        try {
            $coa->delete();
            return redirect()->route('master-data.coa.index')
                ->with('success', "COA {$coa->kode_akun} - {$coa->nama_akun} berhasil dihapus.");
        } catch (\Illuminate\Database\QueryException $e) {
            return redirect()->route('master-data.coa.index')
                ->with('error', "Tidak dapat menghapus COA {$coa->kode_akun} karena masih digunakan di data lain.");
        }
    }

    /**
     * AJAX: Generate kode akun anak berdasarkan parent_coa_id
     */
    public function generateChildKode(Request $request)
    {
        $parentId = $request->get('parent_coa_id');

        if (!$parentId) {
            return response()->json(['error' => 'Parent COA ID diperlukan'], 400);
        }

        // CRITICAL: Filter by user_id untuk multi-tenant isolation
        $parent = Coa::withoutGlobalScopes()
            ->where('user_id', auth()->id())
            ->find($parentId);
            
        if (!$parent) {
            return response()->json(['error' => 'Akun induk tidak ditemukan'], 404);
        }

        $nextKode = Coa::generateChildCode($parent->kode_akun);

        return response()->json([
            'kode_akun' => $nextKode,
            'parent_kode' => $parent->kode_akun,
            'parent_nama' => $parent->nama_akun,
            'parent_tipe' => $parent->tipe_akun,
            'parent_kategori' => $parent->kategori_akun,
            'parent_saldo_normal' => $parent->saldo_normal,
        ]);
    }

    /**
     * AJAX: Legacy generate kode (backward compat)
     */
    public function generateKode(Request $request)
    {
        $tipe = $request->tipe;
        $maxKode = Coa::where('tipe_akun', $tipe)->max('kode_akun');
        $kode = $maxKode ? $maxKode + 1 : $this->defaultKode($tipe);

        return response()->json(['kode_akun' => $kode]);
    }

    private function defaultKode($tipe)
    {
        return match($tipe) {
            'Asset' => 101,
            'Liability' => 201,
            'Equity' => 301,
            'Revenue' => 401,
            'Expense', 'Beban' => 501,
            default => 100,
        };
    }
}
