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
            'request_method' => $request->method(),
            'request_headers' => $request->headers->all()
        ]);
        
        // Log untuk debugging lebih detail
        \Log::info('Request details:', [
            'tanggal' => $request->tanggal,
            'keterangan' => $request->keterangan,
            'beban_operasional_id' => $request->beban_operasional_id,
            'kode_akun_beban' => $request->kode_akun_beban,
            'kode_akun_kas' => $request->kode_akun_kas,
            'nominal_pembayaran' => $request->nominal_pembayaran,
            'metode_bayar' => $request->metode_bayar,
            'catatan' => $request->catatan,
        ]);
        
        // Validasi input
        try {
            \Log::info('Starting validation...');
            $validated = $request->validate([
                'tanggal' => 'required|date',
                'beban_operasional_id' => 'required|exists:beban_operasional,id',
                'kode_akun_beban' => 'required|exists:coas,kode_akun',
                'kode_akun_kas' => 'required|exists:coas,kode_akun|different:kode_akun_beban',
                'nominal_pembayaran' => 'required|numeric|min:1',
                'metode_bayar' => 'required|in:cash,bank',
                'catatan' => 'nullable|string|max:255',
            ], [
                'kode_akun_kas.different' => 'Akun Kas dan Akun Beban tidak boleh sama',
                'beban_operasional_id.exists' => 'Beban operasional tidak valid',
                'kode_akun_beban.exists' => 'Akun beban tidak valid',
                'kode_akun_kas.exists' => 'Akun kas tidak valid',
                'nominal_pembayaran.min' => 'Nominal pembayaran minimal adalah 1',
            ]);
            \Log::info('Validation passed successfully');
        } catch (\Illuminate\Validation\ValidationException $e) {
            \Log::error('Validation failed: ' . json_encode($e->errors()));
            throw $e;
        }

        DB::beginTransaction();
        
        try {
            // Dapatkan data COA dengan pengecekan yang lebih ketat
            $beban = Coa::where('kode_akun', $request->kode_akun_beban)->first();
            $kas = Coa::where('kode_akun', $request->kode_akun_kas)->first();
            
            // Validasi COA
            if (!$beban) {
                throw new \Exception('Akun beban tidak ditemukan');
            }
            
            if (!$kas) {
                throw new \Exception('Akun kas tidak ditemukan');
            }
            
            // Hitung saldo real-time seperti di laporan kas dan bank
            $saldoRealtime = $this->getSaldoRealtime($kas);
            
            // Validasi saldo kas
            if ($saldoRealtime < $request->nominal_pembayaran) {
                return back()
                    ->with('error', 'Saldo kas tidak mencukupi. Saldo tersedia: ' . format_rupiah($saldoRealtime))
                    ->withInput();
            }
            
            // Simpan pembayaran beban
            $pembayaran = new PembayaranBeban([
                'tanggal' => $request->tanggal,
                'keterangan' => $request->catatan ?: 'Pembayaran Beban',
                'akun_beban_id' => $beban->id,
                'akun_kas_id' => $kas->id,
                'jumlah' => $request->nominal_pembayaran,
                'catatan' => $request->catatan,
                'user_id' => auth()->id(),
                'beban_operasional_id' => $request->beban_operasional_id,
            ]);
            
            if (!$pembayaran->save()) {
                throw new \Exception('Gagal menyimpan data pembayaran beban');
            }

            // Saldo akun tidak perlu diupdate langsung di tabel COA
            // Saldo akan dihitung real-time dari journal entries seperti di laporan kas dan bank

            // Data jurnal untuk beban (debit)
            $jurnalBeban = [
                'tanggal' => $request->tanggal,
                'coa_id' => $beban->id,
                'keterangan' => 'Pembayaran Beban: ' . ($request->catatan ?: 'Tanpa catatan'),
                'debit' => $request->nominal_pembayaran,
                'kredit' => 0,
                'referensi' => 'PB-' . $pembayaran->id,
                'tipe_referensi' => 'pembayaran_beban',
                'created_by' => auth()->id(),
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
                'referensi' => 'PB-' . $pembayaran->id,
                'tipe_referensi' => 'pembayaran_beban',
                'created_by' => auth()->id(),
                'created_at' => now(),
                'updated_at' => now(),
            ];
            
            // Simpan jurnal dalam batch
            if (!Jurnal::insert([$jurnalBeban, $jurnalKas])) {
                throw new \Exception('Gagal menyimpan jurnal transaksi');
            }

            DB::commit();
            
            return redirect()
                ->route('transaksi.pembayaran-beban.index')
                ->with('success', 'Pembayaran beban berhasil disimpan');
                
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error in PembayaranBebanController@store: ' . $e->getMessage());
            \Log::error($e->getTraceAsString());
            
            $errorMessage = 'Gagal menyimpan pembayaran beban';
            if (strpos($e->getMessage(), 'No query results for model') !== false) {
                $errorMessage = 'Data COA tidak valid. Pastikan akun beban dan kas sudah diatur dengan benar.';
            }
            
            return back()
                ->with('error', $errorMessage)
                ->withInput();
        }
    }

    public function show($id)
    {
        $pembayaran = PembayaranBeban::with(['coaBeban', 'coaKas', 'user', 'bebanOperasional'])
            ->findOrFail($id);
            
        $jurnals = Jurnal::where('referensi', 'PB-' . $pembayaran->id)
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
            
            // Reverse journal entries
            Jurnal::where('referensi', 'PB-' . $pembayaran->id)
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
}
