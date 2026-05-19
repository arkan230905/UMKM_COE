<?php

namespace App\Http\Controllers\Transaksi;

use App\Http\Controllers\Controller;
use App\Models\PembayaranBeban;
use App\Models\Account;
use App\Models\Coa;
use App\Models\Jurnal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\Models\BopLainnya;

class PembayaranBebanController extends Controller
{
    public function index(Request $request)
    {
        // Load data with relationships - CRITICAL: Filter by user_id for multi-tenant isolation
        $query = PembayaranBeban::with(['coaBeban', 'coaKas', 'bebanOperasional'])
            ->where('user_id', auth()->id());
        
        // Apply filters
        if ($request->tanggal_mulai) {
            $query->whereDate('tanggal', '>=', $request->tanggal_mulai);
        }
        if ($request->tanggal_selesai) {
            $query->whereDate('tanggal', '<=', $request->tanggal_selesai);
        }
        if ($request->beban_operasional_id) {
            $query->where('beban_operasional_id', $request->beban_operasional_id);
        }
        if ($request->akun_beban_id) {
            $query->whereHas('coaBeban', function($q) use ($request) {
                $q->where('kode_akun', $request->akun_beban_id);
            });
        }
        if ($request->akun_kas_id) {
            $query->whereHas('coaKas', function($q) use ($request) {
                $q->where('kode_akun', $request->akun_kas_id);
            });
        }
        
        $pembayaranBeban = $query->latest()->paginate(15);
        
        // Load data for filters - CRITICAL: Filter by user_id
        $bebanOperasional = \App\Models\BebanOperasional::where('user_id', auth()->id())->get();
        $coaBebans = Coa::where('kode_akun', 'like', '5%')->orderBy('kode_akun')->get();
        $coaKas = \App\Helpers\AccountHelper::getKasBankAccounts();
        
        return view('transaksi.pembayaran-beban.index', compact('pembayaranBeban', 'bebanOperasional', 'coaBebans', 'coaKas'));
    }

    public function create()
    {
        try {
            // Load beban operasional data - CRITICAL: Filter by user_id for multi-tenant isolation
            $bebanOperasional = \App\Models\BebanOperasional::where('user_id', auth()->id())->with('coa')->get();
            
            // Ambil akun beban dari tabel Account (kode diawali angka 5) - CRITICAL: Use Account model
            $coaBebans = Account::where('kode_akun', 'like', '5%')
                ->where('user_id', auth()->id())
                ->orderBy('kode_akun')
                ->get();
                
            // Get akun kas/bank yang sama dengan laporan kas dan bank
            $akunKas = \App\Helpers\AccountHelper::getKasBankAccounts();
            
            if ($akunKas->isEmpty()) {
                // Warning only, not blocking
                \Log::warning('Tidak ada akun kas/bank yang aktif untuk pembayaran beban');
            }
            
            return view('transaksi.pembayaran-beban.create', compact('bebanOperasional', 'coaBebans', 'akunKas'));
            
        } catch (\Exception $e) {
            \Log::error('Error in PembayaranBebanController@create: ' . $e->getMessage());
            return back()->with('error', 'Terjadi kesalahan saat memuat form: ' . $e->getMessage());
        }
    }

    public function store(Request $request)
    {
        // Debug log - log all incoming request data
        \Log::info('PembayaranBeban store method called', [
            'request_data' => $request->all(),
        ]);
        
        // Validasi input
        try {
            $validated = $request->validate([
                'tanggal' => 'required|date',
                'beban_operasional_id' => 'required|exists:beban_operasional,id',
                'kode_akun_beban' => 'required|exists:coas,kode_akun',
                'metode_pembayaran' => 'required|in:kas,transfer',
                'nominal_pembayaran' => 'required|numeric|min:1',
                'catatan' => 'nullable|string|max:255',
            ], [
                'beban_operasional_id.exists' => 'Beban operasional tidak valid',
                'kode_akun_beban.exists' => 'Akun beban tidak valid',
                'metode_pembayaran.required' => 'Metode pembayaran harus dipilih',
                'metode_pembayaran.in' => 'Metode pembayaran tidak valid',
                'nominal_pembayaran.min' => 'Nominal pembayaran minimal adalah 1',
            ]);
            \Log::info('Validation passed successfully');
        } catch (\Illuminate\Validation\ValidationException $e) {
            \Log::error('Validation failed: ' . json_encode($e->errors()));
            return back()->withErrors($e->errors())->withInput();
        }

        DB::beginTransaction();
        
        try {
            // Dapatkan data Account dengan pengecekan yang lebih ketat
            $beban = Account::where('kode_akun', $request->kode_akun_beban)
                ->where('user_id', auth()->id())
                ->first();
            
            // Pilih akun kas berdasarkan metode pembayaran
            if ($request->metode_pembayaran === 'kas') {
                $kas = Account::where('kode_akun', '112')
                    ->where('user_id', auth()->id())
                    ->first(); // Kas Tunai
            } else {
                $kas = Account::where('kode_akun', '111')
                    ->where('user_id', auth()->id())
                    ->first(); // Kas Bank (Transfer)
            }
            
            // Validasi Account
            if (!$beban) {
                throw new \Exception('Akun beban dengan kode ' . $request->kode_akun_beban . ' tidak ditemukan di database accounts.');
            }
            
            if (!$kas) {
                $kodeKas = $request->metode_pembayaran === 'kas' ? '112 (Kas Tunai)' : '111 (Kas Bank)';
                throw new \Exception("Akun kas {$kodeKas} tidak ditemukan. Pastikan akun kas sudah dibuat di master COA.");
            }
            
            // Simpan pembayaran beban
            $pembayaran = new PembayaranBeban([
                'tanggal' => $request->tanggal,
                'keterangan' => $request->catatan ?: 'Pembayaran Beban',
                'akun_beban_id' => $beban->id,
                'akun_kas_id' => $kas->id,
                'jumlah' => $request->nominal_pembayaran,
                'catatan' => $request->catatan,
                'user_id' => auth()->id() ?? 1,
                'beban_operasional_id' => $request->beban_operasional_id,
            ]);
            
            if (!$pembayaran->save()) {
                throw new \Exception('Gagal menyimpan data pembayaran beban');
            }

            // Data jurnal untuk beban (debit)
            $jurnalBeban = [
                'tanggal' => $request->tanggal,
                'coa_id' => $beban->id,
                'keterangan' => 'Pembayaran Beban: ' . ($request->catatan ?: 'Tanpa catatan'),
                'debit' => $request->nominal_pembayaran,
                'kredit' => 0,
                'referensi' => $pembayaran->id,
                'tipe_referensi' => 'pembayaran_beban',
                'user_id' => auth()->id(), // MULTI-TENANT: Filter by user_id
                'created_by' => auth()->id() ?? 1,
                'created_at' => now(),
                'updated_at' => now(),
            ];
            
            // Data jurnal untuk kas (kredit)
            $jurnalKas = [
                'tanggal' => $request->tanggal,
                'coa_id' => $kas->id,
                'keterangan' => 'Pembayaran Beban: ' . ($request->catatan ?: 'Tanpa catatan'),
                'debit' => 0,
                'kredit' => $request->nominal_pembayaran,
                'referensi' => $pembayaran->id,
                'tipe_referensi' => 'pembayaran_beban',
                'user_id' => auth()->id(), // MULTI-TENANT: Filter by user_id
                'created_by' => auth()->id() ?? 1,
                'created_at' => now(),
                'updated_at' => now(),
            ];
            
            // Simpan jurnal ke jurnal_umum (bukan jurnal)
            \App\Models\JurnalUmum::insert([$jurnalBeban, $jurnalKas]);
            
            // Jurnal sudah dibuat di jurnal_umum di atas, tidak perlu sistem modern
            // $this->createJournalEntryModern($pembayaran, $beban, $kas, $request->nominal_pembayaran);

            DB::commit();
            
            return redirect()
                ->route('transaksi.pembayaran-beban.index')
                ->with('success', 'Pembayaran beban berhasil disimpan');
                
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error in PembayaranBebanController@store: ' . $e->getMessage());
            \Log::error($e->getTraceAsString());
            
            $errorMessage = 'Gagal menyimpan pembayaran beban: ' . $e->getMessage();
            
            return back()
                ->with('error', $errorMessage)
                ->withInput();
        }
    }

    public function show($id)
    {
        // CRITICAL: Filter by user_id for multi-tenant isolation
        $pembayaran = PembayaranBeban::with(['coaBeban', 'coaKas', 'user', 'bebanOperasional'])
            ->where('user_id', auth()->id())
            ->findOrFail($id);
            
        $jurnals = \App\Models\JurnalUmum::where('referensi', $pembayaran->id)
            ->where('tipe_referensi', 'pembayaran_beban')
            ->where('user_id', auth()->id()) // MULTI-TENANT: Filter by user_id
            ->with('coa')
            ->get();
            
        return view('transaksi.pembayaran-beban.show', compact('pembayaran', 'jurnals'));
    }
    
    public function print($id)
    {
        // CRITICAL: Filter by user_id for multi-tenant isolation
        $pembayaran = PembayaranBeban::with(['coaBeban', 'coaKas', 'user', 'bebanOperasional'])
            ->where('user_id', auth()->id())
            ->findOrFail($id);
            
        return view('transaksi.pembayaran-beban.print', compact('pembayaran'));
    }

    public function edit($id)
    {
        $pembayaran = PembayaranBeban::where('user_id', auth()->id())->findOrFail($id);
        // CRITICAL: Filter by user_id for multi-tenant isolation
        $bebanOperasional = \App\Models\BebanOperasional::where('user_id', auth()->id())->with('coa')->get();
        $coaBebans = Coa::where('kode_akun', 'like', '5%')->orderBy('kode_akun')->get();
        $akunKas = \App\Helpers\AccountHelper::getKasBankAccounts();
        
        return view('transaksi.pembayaran-beban.edit', compact('pembayaran', 'bebanOperasional', 'coaBebans', 'akunKas'));
    }

    public function update(Request $request, $id)
    {
        // For now, just redirect back with message
        return back()->with('info', 'Fitur edit pembayaran beban belum tersedia');
    }

    public function destroy($id)
    {
        try {
            DB::beginTransaction();
            
            // CRITICAL: Filter by user_id for multi-tenant isolation
            $pembayaran = PembayaranBeban::where('user_id', auth()->id())->findOrFail($id);
            
            // Reverse journal entries dari jurnal_umum
            \App\Models\JurnalUmum::where('referensi', $pembayaran->id)
                ->where('tipe_referensi', 'pembayaran_beban')
                ->where('user_id', auth()->id()) // MULTI-TENANT: Filter by user_id
                ->delete();
            
            // Saldo akun tidak perlu diupdate langsung di tabel COA
            // Saldo akan dihitung ulang real-time dari journal entries
            
            // Delete payment record
            $pembayaran->delete();
            
            DB::commit();
            
            return redirect()
                ->route('transaksi.pembayaran-beban.index')
                ->with('success', 'Pembayaran beban berhasil dihapus');
                
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error in PembayaranBebanController@destroy: ' . $e->getMessage());
            return back()->with('error', 'Terjadi kesalahan saat menghapus pembayaran beban');
        }
    }
    
    /**
     * Get saldo real-time untuk akun kas (sama seperti logic di LaporanKasBankController)
     */
    private function getSaldoRealtime($akun)
    {
        // Ambil saldo awal (sama seperti di LaporanKasBankController)
        $startDate = now()->startOfMonth()->format('Y-m-d');
        $saldoAwal = $this->getSaldoAwal($akun, $startDate);
        
        // Ambil transaksi masuk dan keluar dalam periode ini
        $transaksiMasuk = $this->getTransaksiMasuk($akun, $startDate, now()->format('Y-m-d'));
        $transaksiKeluar = $this->getTransaksiKeluar($akun, $startDate, now()->format('Y-m-d'));
        
        // Untuk akun Kas & Bank (Aset), saldo normal adalah Debit
        // Saldo Akhir = Saldo Awal + Debit (Masuk) - Kredit (Keluar)
        return $saldoAwal + $transaksiMasuk - $transaksiKeluar;
    }
    
    /**
     * Get saldo awal sebelum periode (sama seperti di LaporanKasBankController)
     */
    private function getSaldoAwal($akun, $startDate)
    {
        // 1. Cari periode yang sesuai dengan start date
        $periode = \App\Models\CoaPeriod::where('periode', date('Y-m', strtotime($startDate)))->first();
        
        if ($periode) {
            // 2. Cek apakah ada saldo periode
            $periodBalance = \App\Models\CoaPeriodBalance::where('kode_akun', $akun->kode_akun)
                ->where('period_id', $periode->id)
                ->first();
            
            if ($periodBalance) {
                return is_numeric($periodBalance->saldo_awal) ? (float) $periodBalance->saldo_awal : 0;
            }
            
            // 3. Jika tidak ada, cek periode sebelumnya
            $previousPeriod = $periode->getPreviousPeriod();
            if ($previousPeriod) {
                $previousBalance = \App\Models\CoaPeriodBalance::where('kode_akun', $akun->kode_akun)
                    ->where('period_id', $previousPeriod->id)
                    ->first();
                
                if ($previousBalance) {
                    return is_numeric($previousBalance->saldo_akhir) ? (float) $previousBalance->saldo_akhir : 0;
                }
            }
        }
        
        // 4. Jika tidak ada periode atau saldo, gunakan saldo awal dari COA atau 0 untuk virtual accounts
        if (isset($akun->saldo_awal)) {
            return is_numeric($akun->saldo_awal) ? (float) ($akun->saldo_awal ?? 0) : 0;
        }
        
        return 0;
    }
    
    /**
     * Get total transaksi masuk dalam periode (Debit) - sama seperti di LaporanKasBankController
     */
    private function getTransaksiMasuk($akun, $startDate, $endDate)
    {
        // Gunakan JournalLine dengan coa_id langsung untuk akurasi
        $journalMasuk = \App\Models\JournalLine::join('journal_entries', 'journal_lines.journal_entry_id', '=', 'journal_entries.id')
            ->where('journal_lines.coa_id', $akun->id)
            ->whereBetween('journal_entries.tanggal', [$startDate, $endDate])
            ->sum('journal_lines.debit') ?? 0;
        
        return (float) $journalMasuk;
    }
    
    /**
     * Get total transaksi keluar dalam periode (Kredit) - sama seperti di LaporanKasBankController
     */
    private function getTransaksiKeluar($akun, $startDate, $endDate)
    {
        // Gunakan JournalLine dengan coa_id langsung untuk akurasi
        $journalKeluar = \App\Models\JournalLine::join('journal_entries', 'journal_lines.journal_entry_id', '=', 'journal_entries.id')
            ->where('journal_lines.coa_id', $akun->id)
            ->whereBetween('journal_entries.tanggal', [$startDate, $endDate])
            ->sum('journal_lines.credit') ?? 0;
        
        return (float) $journalKeluar;
    }

    // Method createJournalEntryModern dihapus karena class JournalEntry tidak tersedia
    // Jurnal pembayaran beban sudah dibuat di jurnal_umum di method store()
}
