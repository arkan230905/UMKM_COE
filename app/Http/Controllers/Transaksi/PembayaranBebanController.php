<?php

namespace App\Http\Controllers\Transaksi;

use App\Http\Controllers\Controller;
use App\Models\PembayaranBeban;
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
        // Load data with relationships
        $query = PembayaranBeban::with(['coaBeban', 'coaKas', 'bebanOperasional']);
        
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
        
        // Load data for filters
        $bebanOperasional = \App\Models\BebanOperasional::all();
        $coaBebans = Coa::where('kode_akun', 'like', '5%')->orderBy('kode_akun')->get();
        $coaKas = \App\Helpers\AccountHelper::getKasBankAccounts();
        
        return view('transaksi.pembayaran-beban.index', compact('pembayaranBeban', 'bebanOperasional', 'coaBebans', 'coaKas'));
    }

    public function create()
    {
        try {
            // Load beban operasional data
            $bebanOperasional = \App\Models\BebanOperasional::with('coa')->get();
            
            // Ambil akun beban langsung dari tabel COA (kode diawali angka 5)
            $coaBebans = Coa::where('kode_akun', 'like', '5%')
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
                'nominal_pembayaran' => 'required|numeric|min:1',
                'catatan' => 'nullable|string|max:255',
            ], [
                'beban_operasional_id.exists' => 'Beban operasional tidak valid',
                'kode_akun_beban.exists' => 'Akun beban tidak valid',
                'nominal_pembayaran.min' => 'Nominal pembayaran minimal adalah 1',
            ]);
            \Log::info('Validation passed successfully');
        } catch (\Illuminate\Validation\ValidationException $e) {
            \Log::error('Validation failed: ' . json_encode($e->errors()));
            return back()->withErrors($e->errors())->withInput();
        }

        DB::beginTransaction();
        
        try {
            // Dapatkan data COA dengan pengecekan yang lebih ketat
            $beban = Coa::where('kode_akun', $request->kode_akun_beban)->first();
            
            // Otomatis pilih Kas Bank (111) untuk pembayaran beban
            $kas = Coa::where('kode_akun', '111')->first(); // Kas Bank
            
            // Validasi COA
            if (!$beban) {
                throw new \Exception('Akun beban tidak ditemukan');
            }
            
            if (!$kas) {
                throw new \Exception('Akun kas (111) tidak ditemukan. Pastikan akun kas sudah dibuat di master COA.');
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
                'created_by' => auth()->id() ?? 1,
                'created_at' => now(),
                'updated_at' => now(),
            ];
            
            // Simpan jurnal ke jurnal_umum (bukan jurnal)
            \App\Models\JurnalUmum::insert([$jurnalBeban, $jurnalKas]);
            
            // JUGA buat journal entry di sistem modern (journal_entries + journal_lines)
            $this->createJournalEntryModern($pembayaran, $beban, $kas, $request->nominal_pembayaran);

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
        $pembayaran = PembayaranBeban::with(['coaBeban', 'coaKas', 'user', 'bebanOperasional'])
            ->findOrFail($id);
            
        $jurnals = \App\Models\JurnalUmum::where('referensi', $pembayaran->id)
            ->where('tipe_referensi', 'pembayaran_beban')
            ->with('coa')
            ->get();
            
        return view('transaksi.pembayaran-beban.show', compact('pembayaran', 'jurnals'));
    }
    
    public function print($id)
    {
        $pembayaran = PembayaranBeban::with(['coaBeban', 'coaKas', 'user', 'bebanOperasional'])
            ->findOrFail($id);
            
        return view('transaksi.pembayaran-beban.print', compact('pembayaran'));
    }

    public function edit($id)
    {
        $pembayaran = PembayaranBeban::findOrFail($id);
        $bebanOperasional = \App\Models\BebanOperasional::with('coa')->get();
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
            
            $pembayaran = PembayaranBeban::findOrFail($id);
            
            // Reverse journal entries dari jurnal_umum
            \App\Models\JurnalUmum::where('referensi', $pembayaran->id)
                ->where('tipe_referensi', 'pembayaran_beban')
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

    /**
     * Buat journal entry di sistem modern (journal_entries + journal_lines)
     * Ini memastikan pembayaran beban muncul di halaman jurnal umum
     */
    private function createJournalEntryModern($pembayaran, $beban, $kas, $nominal)
    {
        try {
            // Create journal entry
            $journalEntry = \App\Models\JournalEntry::create([
                'tanggal' => $pembayaran->tanggal,
                'ref_type' => 'pembayaran_beban',
                'ref_id' => $pembayaran->id,
                'memo' => 'Pembayaran Beban: ' . ($pembayaran->keterangan ?: 'Tanpa catatan'),
            ]);
            
            // Create journal lines
            // DEBIT: Beban
            \App\Models\JournalLine::create([
                'journal_entry_id' => $journalEntry->id,
                'coa_id' => $beban->id,
                'debit' => $nominal,
                'credit' => 0,
                'memo' => 'Pembayaran Beban',
            ]);
            
            // CREDIT: Kas
            \App\Models\JournalLine::create([
                'journal_entry_id' => $journalEntry->id,
                'coa_id' => $kas->id,
                'debit' => 0,
                'credit' => $nominal,
                'memo' => 'Pembayaran Beban',
            ]);
            
            \Log::info('Journal entry modern created for pembayaran beban', [
                'pembayaran_id' => $pembayaran->id,
                'journal_entry_id' => $journalEntry->id,
            ]);
            
            return true;
            
        } catch (\Exception $e) {
            \Log::error('Failed to create modern journal entry for pembayaran beban: ' . $e->getMessage());
            throw $e;
        }
    }
}
