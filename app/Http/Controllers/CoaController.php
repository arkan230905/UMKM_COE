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
        // Ambil COA milik user yang login sebagai pilihan akun induk
        $parentCoas = Coa::whereNotNull('nama_akun')
            ->where('nama_akun', '!=', '')
            ->orderByRaw("RPAD(kode_akun, 10, '0'), LENGTH(kode_akun)")
            ->get(['id', 'kode_akun', 'nama_akun', 'tipe_akun', 'kategori_akun', 'saldo_normal']);

        return view('master-data.coa.create', compact('parentCoas'));
    }

    public function store(Request $request)
    {
        // Jika user memilih akun induk dan mode auto-generate
        if ($request->filled('parent_coa_id') && $request->boolean('auto_generate_kode')) {
            $parentCoa = Coa::find($request->parent_coa_id);
            if ($parentCoa) {
                $generatedKode = Coa::generateChildCode($parentCoa->kode_akun);
                $request->merge(['kode_akun' => $generatedKode]);
            }
        }

        // Normalize tipe_akun: map alias ke nilai enum DB
        $tipeAkunMap = [
            'ASET'       => 'Asset',
            'Aset'       => 'Asset',
            'KEWAJIBAN'  => 'Liability',
            'Kewajiban'  => 'Liability',
            'MODAL'      => 'Equity',
            'Modal'      => 'Equity',
            'Ekuitas'    => 'Equity',
            'PENDAPATAN' => 'Revenue',
            'Pendapatan' => 'Revenue',
            'BEBAN'      => 'Expense',
            'Beban'      => 'Expense',
            'Biaya'      => 'Expense',
        ];
        if (isset($tipeAkunMap[$request->tipe_akun])) {
            $request->merge(['tipe_akun' => $tipeAkunMap[$request->tipe_akun]]);
        }

        $validated = $request->validate([
            'kode_akun' => [
                'required',
                \Illuminate\Validation\Rule::unique('coas', 'kode_akun')
                    ->where('user_id', auth()->id()),
                'max:50'
            ],
            'nama_akun' => 'required|string|max:255',
            'tipe_akun' => 'required|in:Asset,Liability,Equity,Revenue,Expense,Biaya Bahan Baku,Biaya Tenaga Kerja Langsung,Biaya Overhead Pabrik,Biaya Tenaga Kerja Tidak Langsung,BOP Tidak Langsung Lainnya',
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
        // Ambil COA milik user yang login (kecuali dirinya sendiri)
        $parentCoas = Coa::whereNotNull('nama_akun')
            ->where('nama_akun', '!=', '')
            ->where('id', '!=', $coa->id)
            ->orderByRaw("RPAD(kode_akun, 10, '0'), LENGTH(kode_akun)")
            ->get(['id', 'kode_akun', 'nama_akun', 'tipe_akun', 'kategori_akun', 'saldo_normal']);

        return view('master-data.coa.edit', compact('coa', 'parentCoas'));
    }

    public function update(Request $request, Coa $coa)
    {
        // Normalize tipe_akun: map uppercase/alias ke nilai enum DB
        $tipeAkunMap = [
            'ASET'       => 'Asset',
            'Aset'       => 'Asset',
            'KEWAJIBAN'  => 'Liability',
            'Kewajiban'  => 'Liability',
            'MODAL'      => 'Equity',
            'Modal'      => 'Equity',
            'Ekuitas'    => 'Equity',
            'PENDAPATAN' => 'Revenue',
            'Pendapatan' => 'Revenue',
            'BEBAN'      => 'Expense',
            'Beban'      => 'Expense',
            'Biaya'      => 'Expense',
        ];
        if (isset($tipeAkunMap[$request->tipe_akun])) {
            $request->merge(['tipe_akun' => $tipeAkunMap[$request->tipe_akun]]);
        }

        $validated = $request->validate([
            'kode_akun' => [
                'required',
                \Illuminate\Validation\Rule::unique('coas', 'kode_akun')
                    ->where('user_id', auth()->id())
                    ->ignore($coa->id),
                'max:50'
            ],
            'nama_akun' => 'required|string|max:255',
            'tipe_akun' => 'required|in:Asset,Liability,Equity,Revenue,Expense,Biaya Bahan Baku,Biaya Tenaga Kerja Langsung,Biaya Overhead Pabrik,Biaya Tenaga Kerja Tidak Langsung,BOP Tidak Langsung Lainnya',
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

        // Cek journal_lines
        if (\App\Models\JournalLine::where($coaColumn, $coa->id)->count() > 0) {
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
            ->orWhere('coa_hpp_id', $coa->id)
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

        $parent = Coa::withoutGlobalScopes()->find($parentId);
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
