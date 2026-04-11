<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Http\Request;
use App\Models\Penggajian;
use App\Models\Pegawai;
use App\Models\Presensi;
use App\Models\Bop;
use App\Models\Coa;
use App\Models\JurnalUmum;
use Carbon\Carbon;

class PenggajianController extends Controller
{
    /**
     * Tampilkan daftar penggajian dengan filter.
     */
    public function index(Request $request)
    {
        $query = Penggajian::with('pegawai');

        // Filter nama pegawai
        if ($request->nama_pegawai) {
            $query->whereHas('pegawai', function ($q) use ($request) {
                $q->where('nama', 'like', '%' . $request->nama_pegawai . '%');
            });
        }

        // Filter tanggal
        if ($request->tanggal_mulai) {
            $query->whereDate('tanggal_penggajian', '>=', $request->tanggal_mulai);
        }
        if ($request->tanggal_selesai) {
            $query->whereDate('tanggal_penggajian', '<=', $request->tanggal_selesai);
        }

        // Filter jenis pegawai
        if ($request->jenis_pegawai) {
            $query->whereHas('pegawai', function ($q) use ($request) {
                $q->where('jenis_pegawai', $request->jenis_pegawai);
            });
        }

        // Filter status pembayaran
        if ($request->status_pembayaran) {
            $query->where('status_pembayaran', $request->status_pembayaran);
        }

        $penggajians = $query->latest()->get();
        return view('transaksi.penggajian.index', compact('penggajians'));
    }

    /**
     * Form tambah data penggajian.
     */
    public function create()
    {
        // Clear any old validation errors from session
        session()->forget('errors');

        $pegawais = Pegawai::with('jabatanRelasi')->get();
        $kasbank = \App\Helpers\AccountHelper::getKasBankAccounts();
        return view('transaksi.penggajian.create', compact('pegawais', 'kasbank'));
    }

    /**
     * Simpan data penggajian baru.
     */
    public function store(Request $request)
    {
        // Mulai transaksi database
        DB::beginTransaction();

        try {
            // Validasi input
            $request->validate([
                'pegawai_id' => 'required|exists:pegawais,id',
                'tanggal_penggajian' => 'required|date',
                'coa_kasbank' => 'required|exists:coas,kode_akun',
                'bonus' => 'nullable|numeric|min:0',
                'potongan' => 'nullable|numeric|min:0',
                'gaji_pokok' => 'nullable|numeric|min:0',
                'tarif_per_jam' => 'nullable|numeric|min:0',
                'tunjangan' => 'nullable|numeric|min:0',
                'asuransi' => 'required|numeric|min:0',
                'total_jam_kerja' => 'nullable|numeric|min:0',
                'jenis_pegawai' => 'nullable|string|in:btkl,btktl',
            ]);

            $pegawai = Pegawai::findOrFail($request->pegawai_id);

            // Data dari form
            $gajiPokok = (float) ($request->gaji_pokok ?? 0);
            $tarifPerJam = (float) ($request->tarif_per_jam ?? 0);
            // Get jenis_pegawai from form or from pegawai
            $jenisPegawai = $request->jenis_pegawai ?? strtolower($pegawai->jenis_pegawai ?? $pegawai->kategori ?? 'btktl');

            // Get tunjangan from pegawai's jabatan (kualifikasi)
            $jabatan = $pegawai->jabatanRelasi;
            if ($jabatan) {
                $tunjanganJabatan = (float) ($jabatan->tunjangan ?? 0);
                $tunjanganTransport = (float) ($jabatan->tunjangan_transport ?? 0);
                $tunjanganKonsumsi = (float) ($jabatan->tunjangan_konsumsi ?? 0);
            } else {
                // Fallback to pegawai stored values or accessor
                $tunjanganJabatan = (float) ($pegawai->tunjangan_jabatan ?? 0);
                $tunjanganTransport = (float) ($pegawai->tunjangan_transport ?? 0);
                $tunjanganKonsumsi = (float) ($pegawai->tunjangan_konsumsi ?? 0);
            }

            // Calculate total tunjangan
            $totalTunjangan = $tunjanganJabatan + $tunjanganTransport + $tunjanganKonsumsi;
            $asuransi = (float) $request->asuransi;

            // Untuk BTKL: hitung total jam kerja otomatis dari presensi (bulan dari tanggal_penggajian)
            if ($jenisPegawai === 'btkl') {
                $tanggal = Carbon::parse($request->tanggal_penggajian);
                $presensis = Presensi::where('pegawai_id', $pegawai->id)
                    ->whereMonth('tgl_presensi', $tanggal->month)
                    ->whereYear('tgl_presensi', $tanggal->year)
                    ->get();

                // Sum via accessor (handles cases where DB jumlah_jam is 0/null but can be calculated from jam_masuk/jam_keluar)
                $totalJamKerja = (float) $presensis->sum(function ($p) {
                    return (float) ($p->jumlah_jam ?? 0);
                });
            } else {
                $totalJamKerja = (float) ($request->total_jam_kerja ?? 0);
            }
            
            // Input manual dari user
            $bonus = (float) ($request->bonus ?? 0);
            $potongan = (float) ($request->potongan ?? 0);

            // Debug log
            \Log::info('Data penggajian yang akan disimpan:', [
                'pegawai_id' => $pegawai->id,
                'jenis_pegawai' => $jenisPegawai,
                'gaji_pokok' => $gajiPokok,
                'tarif_per_jam' => $tarifPerJam,
                'tunjangan_jabatan' => $tunjanganJabatan,
                'tunjangan_transport' => $tunjanganTransport,
                'tunjangan_konsumsi' => $tunjanganKonsumsi,
                'total_tunjangan' => $totalTunjangan,
                'asuransi' => $asuransi,
                'total_jam_kerja' => $totalJamKerja,
                'bonus' => $bonus,
                'potongan' => $potongan,
            ]);

            // Hitung gaji dasar berdasarkan jenis pegawai
            if ($jenisPegawai === 'btkl') {
                // BTKL = (Tarif × Jam Kerja)
                $gajiDasar = $tarifPerJam * $totalJamKerja;
            } else {
                // BTKTL = Gaji Pokok
                $gajiDasar = $gajiPokok;
            }

            // Total gaji = gaji dasar + total_tunjangan + asuransi + bonus - potongan
            $totalGaji = $gajiDasar + $totalTunjangan + $asuransi + $bonus - $potongan;

            // Dapatkan akun kas/bank
            $coaKasBank = Coa::where('kode_akun', $request->coa_kasbank)->first();
            if (!$coaKasBank) {
                throw new \Exception('Akun kas/bank tidak ditemukan');
            }

            // Hitung saldo kas/bank dari saldo_awal + jurnal
            $saldoAwal = (float) ($coaKasBank->saldo_awal ?? 0);
            $jurnalSaldo = \DB::table('journal_entries as je')
                ->join('journal_lines as jl', 'je.id', '=', 'jl.journal_entry_id')
                ->where('jl.coa_id', $coaKasBank->id)
                ->selectRaw('COALESCE(SUM(jl.debit), 0) as total_debit, COALESCE(SUM(jl.credit), 0) as total_credit')
                ->first();
            $saldoAkhir = $saldoAwal + ($jurnalSaldo->total_debit ?? 0) - ($jurnalSaldo->total_credit ?? 0);

            // Validasi saldo cukup
            if ($saldoAkhir < $totalGaji) {
                return back()->withErrors([
                    'kas' => 'Saldo kas/bank tidak mencukupi. Saldo tersedia: ' . number_format($saldoAkhir, 0, ',', '.') . ' ; Kebutuhan: ' . number_format($totalGaji, 0, ',', '.')
                ])->withInput();
            }

            // Simpan ke tabel penggajian
            $penggajian = new Penggajian([
                'pegawai_id' => $pegawai->id,
                'tanggal_penggajian' => $request->tanggal_penggajian,
                'coa_kasbank' => $coaKasBank->kode_akun,
                'gaji_pokok' => $gajiPokok,
                'tarif_per_jam' => $tarifPerJam,
                'tunjangan' => $totalTunjangan, // Backward compatibility
                'tunjangan_jabatan' => $tunjanganJabatan,
                'tunjangan_transport' => $tunjanganTransport,
                'tunjangan_konsumsi' => $tunjanganKonsumsi,
                'total_tunjangan' => $totalTunjangan,
                'asuransi' => $asuransi,
                'bonus' => $bonus,
                'potongan' => $potongan,
                'total_jam_kerja' => $totalJamKerja,
                'total_gaji' => $totalGaji,
                'status_pembayaran' => 'lunas', // Status default: Lunas
            ]);

            if (!$penggajian->save()) {
                throw new \Exception('Gagal menyimpan data penggajian ke database');
            }

            \Log::info('Data penggajian berhasil disimpan', [
                'penggajian_id' => $penggajian->id,
                'total_gaji' => $totalGaji,
                'bonus' => $bonus,
                'potongan' => $potongan,
                'status_pembayaran' => 'lunas'
            ]);

            // Buat jurnal umum otomatis (Debit: Beban Gaji, Kredit: Kas/Bank)
            $this->createJournalEntry($penggajian, $pegawai);

            // Commit transaksi
            DB::commit();
            \Log::info('Transaksi penggajian berhasil disimpan');

            return redirect()->route('transaksi.penggajian.index')
                ->with('success', 'Data penggajian berhasil ditambahkan!');

        } catch (\Illuminate\Validation\ValidationException $e) {
            // Rollback transaksi jika terjadi error validasi
            DB::rollBack();
            \Log::error('Validation Error in PenggajianController@store: ' . $e->getMessage());
            
            return back()->withErrors($e->errors())->withInput();
            
        } catch (\Exception $e) {
            // Rollback transaksi jika terjadi error
            DB::rollBack();
            \Log::error('Error in PenggajianController@store: ' . $e->getMessage());
            \Log::error($e->getTraceAsString());

            return back()->withErrors(['error' => 'Gagal menyimpan penggajian: ' . $e->getMessage()])->withInput();
        }
    }

    public function destroy($id)
    {
        try {
            $penggajian = Penggajian::findOrFail($id);

            // Cegah hapus jika sudah dibayar
            if ($penggajian->isPaid()) {
                return redirect()->route('transaksi.penggajian.index')
                    ->with('error', 'Penggajian tidak dapat dihapus karena sudah dibayar (status: ' . $penggajian->status_pembayaran . ')');
            }

            $penggajian->delete();

            return redirect()->route('transaksi.penggajian.index')
                ->with('success', 'Data penggajian berhasil dihapus!');
        } catch (\Exception $e) {
            \Log::error('Error deleting penggajian: ' . $e->getMessage());

            return redirect()->route('transaksi.penggajian.index')
                ->withErrors(['error' => 'Gagal menghapus penggajian: ' . $e->getMessage()]);
        }
    }

    /**
     * Tandai penggajian sebagai sudah dibayar
     */
    public function markAsPaid($id)
    {
        try {
            $penggajian = Penggajian::findOrFail($id);

            // Hanya update jika status masih belum_lunas
            if ($penggajian->status_pembayaran === 'belum_lunas') {
                $penggajian->status_pembayaran = 'lunas';
                $penggajian->tanggal_dibayar = now()->format('Y-m-d');
                $penggajian->save();

                return redirect()->back()
                    ->with('success', 'Penggajian berhasil ditandai sebagai sudah dibayar.');
            }

            return redirect()->back()
                ->with('info', 'Penggajian sudah berstatus ' . $penggajian->status_pembayaran);
        } catch (\Exception $e) {
            \Log::error('Error marking penggajian as paid: ' . $e->getMessage());

            return redirect()->back()
                ->withErrors(['error' => 'Gagal menandai penggajian sebagai dibayar: ' . $e->getMessage()]);
        }
    }

    /**
     * Buat jurnal entry untuk penggajian menggunakan JournalService
     */
    private function createJournalEntry($penggajian, $pegawai)
    {
        try {
            // Tentukan akun beban berdasarkan jenis pegawai
            $jenisPegawai = strtolower($pegawai->kategori ?? $pegawai->jenis_pegawai ?? 'btktl');
            
            if ($jenisPegawai === 'btkl') {
                // BTKL → 52 (Biaya Tenaga Kerja Langsung)
                $coaBebanGaji = Coa::where('kode_akun', '52')->first();
            } else {
                // BTKTL → 54 (Biaya Tenaga Kerja Tidak Langsung)
                $coaBebanGaji = Coa::where('kode_akun', '54')->first();
            }
            
            // Fallback: cari akun beban gaji umum
            if (!$coaBebanGaji) {
                $coaBebanGaji = Coa::whereRaw('LOWER(nama_akun) LIKE ?', ['%beban gaji%'])
                    ->orWhereRaw('LOWER(nama_akun) LIKE ?', ['%biaya tenaga kerja%'])
                    ->first();
            }
            
            if (!$coaBebanGaji) {
                throw new \Exception('Akun beban gaji tidak ditemukan. Pastikan COA kode 52 (BTKL) atau 54 (BTKTL) sudah ada.');
            }

            // Pastikan akun kas/bank valid
            $coaKasBank = Coa::where('kode_akun', $penggajian->coa_kasbank)->first();
            if (!$coaKasBank) {
                throw new \Exception('Akun kas/bank tidak valid');
            }
            
            // Log data sebelum membuat jurnal
            \Log::info('Membuat jurnal penggajian', [
                'penggajian_id' => $penggajian->id,
                'pegawai_id' => $pegawai->id,
                'total_gaji' => $penggajian->total_gaji,
                'coa_beban' => $coaBebanGaji->kode_akun,
                'coa_kasbank' => $coaKasBank->kode_akun
            ]);
            
            // Gunakan JournalService untuk konsistensi
            $journalService = app(\App\Services\JournalService::class);
            
            $result = $journalService->post(
                $penggajian->tanggal_penggajian->format('Y-m-d'),
                'penggajian',
                (int)$penggajian->id,
                'Penggajian - ' . $pegawai->nama,
                [
                    ['code' => $coaBebanGaji->kode_akun, 'debit' => (float)$penggajian->total_gaji, 'credit' => 0],
                    ['code' => $coaKasBank->kode_akun, 'debit' => 0, 'credit' => (float)$penggajian->total_gaji],
                ]
            );
            
            if (!$result) {
                throw new \Exception('Gagal memproses jurnal penggajian');
            }
            
            // Update saldo COA
            $this->updateCoaSaldo($coaBebanGaji->kode_akun);
            $this->updateCoaSaldo($coaKasBank->kode_akun);
            
            return true;
            
        } catch (\Exception $e) {
            \Log::error('Gagal membuat jurnal penggajian: ' . $e->getMessage());
            \Log::error($e->getTraceAsString());
            throw $e; // Re-throw agar DB transaction di store() ikut rollback
        }
    }

    /**
     * Update saldo COA berdasarkan jurnal
     */
    protected function updateCoaSaldo($kodeAkun)
    {
        // Saldo dihitung langsung dari saldo_awal + journal_lines.
        // Tabel coas tidak memiliki kolom saldo_akhir, jadi tidak perlu di-update.
        // Saldo aktual selalu dihitung on-the-fly dari jurnal.
        \Log::info('Saldo COA ' . $kodeAkun . ' akan dihitung dari jurnal saat dibutuhkan.');
        return true;
    }
    
    /**
     * Update BOP untuk beban gaji
     */
    private function updateBopBebanGaji($tanggal)
    {
        $periode = Carbon::parse($tanggal);
        $perkiraanBebanGaji = 0.0;

        $hoursPerDay = (int) (config('app.btkl_hours_per_day') ?? 8);
        $workingDays = (int) (config('app.working_days_per_month') ?? 26);

        $semuaPegawai = Pegawai::all();
        foreach ($semuaPegawai as $p) {
            $jenisP = strtolower($p->jenis_pegawai ?? 'btktl');
            $gajiPokok = (float) ($p->gaji_pokok ?? 0);
            $tarifPerJam = (float) ($p->tarif_per_jam ?? 0);
            $tunjangan = (float) ($p->tunjangan ?? 0);
            $asuransi = (float) ($p->asuransi ?? 0);

            if ($jenisP === 'btkl') {
                // BTKL = (Tarif × Jam Kerja estimasi) + Asuransi + Tunjangan
                $perkiraanBebanGaji += ($tarifPerJam * $hoursPerDay * $workingDays) + $asuransi + $tunjangan;
            } else {
                // BTKTL = Gaji Pokok + Asuransi + Tunjangan
                $perkiraanBebanGaji += $gajiPokok + $asuransi + $tunjangan;
            }
        }

        // Cari COA Beban Gaji
        $coaBebanGaji = Coa::whereRaw('LOWER(nama_akun) LIKE ?', ['%beban gaji%'])
            ->orWhere('kode_akun', '501')
            ->first();

        if ($coaBebanGaji) {
            // Update atau buat BOP untuk beban gaji
            $bop = Bop::firstOrNew(['kode_akun' => $coaBebanGaji->kode_akun]);
            
            // Increment aktual value
            $bop->nama_akun = $coaBebanGaji->nama_akun;
            $bop->keterangan = 'Beban Gaji';
            $bop->aktual = ($bop->aktual ?? 0) + $perkiraanBebanGaji;
            $bop->is_active = true;
            $bop->save();
        }
    }

    /**
     * Tampilkan detail penggajian.
     */
    public function show($id)
    {
        $penggajian = Penggajian::with('pegawai')->findOrFail($id);
        return view('transaksi.penggajian.show', compact('penggajian'));
    }

    /**
     * EDIT DAN UPDATE DIHAPUS - Transaksi penggajian tidak boleh diedit setelah disimpan
     * Untuk koreksi, buat transaksi baru
     */

    /**
     * HAPUS DIHAPUS - Transaksi penggajian tidak boleh dihapus (audit trail)
     */


    /**
     * Generate slip gaji HTML
     */
    public function generateSlip($id)
    {
        $penggajian = Penggajian::with('pegawai')->findOrFail($id);
        
        // Check permission: admin, owner, atau pegawai yang bersangkutan
        if (!in_array(auth()->user()->role, ['admin', 'owner']) && auth()->user()->pegawai_id !== $penggajian->pegawai_id) {
            abort(403, 'Anda tidak memiliki akses ke slip gaji ini');
        }

        return view('transaksi.penggajian.slip', compact('penggajian'));
    }

    /**
     * Download slip gaji PDF
     */
    public function downloadSlip($id)
    {
        $penggajian = Penggajian::with('pegawai')->findOrFail($id);
        
        // Check permission: admin, owner, atau pegawai yang bersangkutan
        if (!in_array(auth()->user()->role, ['admin', 'owner']) && auth()->user()->pegawai_id !== $penggajian->pegawai_id) {
            abort(403, 'Anda tidak memiliki akses ke slip gaji ini');
        }

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('transaksi.penggajian.slip-pdf', compact('penggajian'));
        
        $filename = 'slip-gaji-' . $penggajian->pegawai->nama . '-' . 
                   $penggajian->tanggal_penggajian->format('Y-m-d') . '.pdf';
        
        return $pdf->download($filename);
    }

    /**
     * Update status pembayaran
     */
    public function updateStatus(Request $request, $id)
    {
        $penggajian = Penggajian::findOrFail($id);
        
        $request->validate([
            'action' => 'required|in:pay,cancel',
            'metode_pembayaran' => 'required_if:action,pay|in:transfer,tunai,cek'
        ]);

        if ($request->action === 'pay') {
            $penggajian->status_pembayaran = 'lunas';
            $penggajian->tanggal_dibayar = now();
            $penggajian->metode_pembayaran = $request->metode_pembayaran;
            $penggajian->save();
            
            return back()->with('success', 'Transaksi berhasil ditandai sebagai dibayar');
        } 
        elseif ($request->action === 'cancel') {
            $penggajian->status_pembayaran = 'dibatalkan';
            $penggajian->save();
            
            return back()->with('success', 'Transaksi berhasil dibatalkan');
        }
    }

    /**
     * Posting penggajian ke jurnal umum
     * 
     * Skema Jurnal:
     * DEBIT:
     * - Beban Gaji (BTKL/BTKTL) = gaji_dasar
     * - Beban Tunjangan = total_tunjangan
     * - Beban Asuransi = asuransi
     * 
     * KREDIT:
     * - Kas/Bank (jika sudah dibayar) atau Utang Gaji (jika belum dibayar) = total_gaji
     */
    public function postToJournal($id)
    {
        // Check permission: hanya owner/admin
        if (!in_array(auth()->user()->role, ['owner', 'admin'])) {
            abort(403, 'Anda tidak memiliki akses untuk posting ke jurnal');
        }

        $penggajian = Penggajian::with('pegawai')->findOrFail($id);

        // Cegah double posting
        if ($penggajian->isPosted()) {
            return back()->with('error', 'Penggajian ini sudah diposting ke jurnal');
        }

        try {
            DB::beginTransaction();

            // Hitung komponen gaji
            $pegawai = $penggajian->pegawai;
            $jenisPegawai = strtolower($pegawai->jenis_pegawai ?? 'btktl');

            // Gaji dasar
            if ($jenisPegawai === 'btkl') {
                $gajiDasar = ($penggajian->tarif_per_jam ?? 0) * ($penggajian->total_jam_kerja ?? 0);
            } else {
                $gajiDasar = $penggajian->gaji_pokok ?? 0;
            }

            $totalTunjangan = $penggajian->total_tunjangan ?? 0;
            $asuransi = $penggajian->asuransi ?? 0;
            $totalGaji = $penggajian->total_gaji ?? 0;

            // Generate no bukti jurnal
            $noBukti = $penggajian->generateNoBukti();

            // Tentukan akun COA dari config
            $config = config('penggajian_journal');
            
            // Pilih akun beban gaji berdasarkan jenis pegawai
            $coaBebanGaji = Coa::where('kode_akun', $jenisPegawai === 'btkl' 
                ? $config['beban_gaji_btkl'] 
                : $config['beban_gaji_btklt'])->first();
            
            $coaBebanTunjangan = Coa::where('kode_akun', $config['beban_tunjangan'])->first();
            $coaBebanAsuransi = Coa::where('kode_akun', $config['beban_asuransi'])->first();

            // Tentukan akun kredit (Kas/Bank atau Utang Gaji)
            $isPaid = $penggajian->status_pembayaran === 'lunas';
            if ($isPaid) {
                // Gunakan COA kasbank dari penggajian atau default
                $coaKredit = Coa::where('kode_akun', $penggajian->coa_kasbank ?? $config['kas_bank_default'])->first();
            } else {
                $coaKredit = Coa::where('kode_akun', $config['utang_gaji'])->first();
            }

            // Validasi COA tersedia
            if (!$coaBebanGaji || !$coaBebanTunjangan || !$coaBebanAsuransi || !$coaKredit) {
                DB::rollBack();
                return back()->with('error', 'COA untuk jurnal penggajian belum dikonfigurasi dengan benar');
            }

            // Buat jurnal entries (DEBIT)
            $keterangan = "Penggajian {$pegawai->nama} ({$pegawai->kode_pegawai}) - " . 
                         ($isPaid ? 'Dibayar' : 'Belum Dibayar');

            // DEBIT: Beban Gaji
            JurnalUmum::create([
                'coa_id' => $coaBebanGaji->id,
                'tanggal' => $penggajian->tanggal_penggajian,
                'keterangan' => $keterangan,
                'debit' => $gajiDasar,
                'kredit' => 0,
                'referensi' => $penggajian->id,
                'tipe_referensi' => 'penggajian',
                'created_by' => auth()->id(),
            ]);

            // DEBIT: Beban Tunjangan
            if ($totalTunjangan > 0) {
                JurnalUmum::create([
                    'coa_id' => $coaBebanTunjangan->id,
                    'tanggal' => $penggajian->tanggal_penggajian,
                    'keterangan' => $keterangan,
                    'debit' => $totalTunjangan,
                    'kredit' => 0,
                    'referensi' => $penggajian->id,
                    'tipe_referensi' => 'penggajian',
                    'created_by' => auth()->id(),
                ]);
            }

            // DEBIT: Beban Asuransi
            if ($asuransi > 0) {
                JurnalUmum::create([
                    'coa_id' => $coaBebanAsuransi->id,
                    'tanggal' => $penggajian->tanggal_penggajian,
                    'keterangan' => $keterangan,
                    'debit' => $asuransi,
                    'kredit' => 0,
                    'referensi' => $penggajian->id,
                    'tipe_referensi' => 'penggajian',
                    'created_by' => auth()->id(),
                ]);
            }

            // KREDIT: Kas/Bank atau Utang Gaji
            JurnalUmum::create([
                'coa_id' => $coaKredit->id,
                'tanggal' => $penggajian->tanggal_penggajian,
                'keterangan' => $keterangan,
                'debit' => 0,
                'kredit' => $totalGaji,
                'referensi' => $penggajian->id,
                'tipe_referensi' => 'penggajian',
                'created_by' => auth()->id(),
            ]);

            // Update status posting penggajian
            $penggajian->status_posting = 'posted';
            $penggajian->tanggal_posting = now();
            $penggajian->save();

            DB::commit();

            return back()->with('success', 'Penggajian berhasil diposting ke jurnal umum');

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error posting penggajian to journal: ' . $e->getMessage());
            return back()->with('error', 'Terjadi kesalahan saat posting ke jurnal: ' . $e->getMessage());
        }
    }
}
