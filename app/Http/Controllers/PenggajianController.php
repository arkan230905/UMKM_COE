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

        $penggajians = $query->latest()->get();
        return view('transaksi.penggajian.index', compact('penggajians'));
    }

    /**
     * Form tambah data penggajian.
     */
    public function create()
    {
        $pegawais = Pegawai::all();
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
                'coa_kasbank' => 'required|in:' . implode(',', \App\Helpers\AccountHelper::KAS_BANK_CODES),
                'bonus' => 'nullable|numeric|min:0',
                'potongan' => 'nullable|numeric|min:0',
                'gaji_pokok' => 'required|numeric|min:0',
                'tarif_per_jam' => 'required|numeric|min:0',
                'tunjangan' => 'required|numeric|min:0',
                'asuransi' => 'required|numeric|min:0',
                'total_jam_kerja' => 'required|numeric|min:0',
                'jenis_pegawai' => 'required|string|in:btkl,btktl',
            ]);

            $pegawai = Pegawai::findOrFail($request->pegawai_id);

            // Data dari form
            $gajiPokok = (float) $request->gaji_pokok;
            $tarifPerJam = (float) $request->tarif_per_jam;
            $tunjangan = (float) $request->tunjangan;
            $asuransi = (float) $request->asuransi;
            $totalJamKerja = (float) $request->total_jam_kerja;
            $jenisPegawai = $request->jenis_pegawai;
            
            // Input manual dari user
            $bonus = (float) ($request->bonus ?? 0);
            $potongan = (float) ($request->potongan ?? 0);

            // Debug log
            \Log::info('Data penggajian yang akan disimpan:', [
                'pegawai_id' => $pegawai->id,
                'jenis_pegawai' => $jenisPegawai,
                'gaji_pokok' => $gajiPokok,
                'tarif_per_jam' => $tarifPerJam,
                'tunjangan' => $tunjangan,
                'asuransi' => $asuransi,
                'total_jam_kerja' => $totalJamKerja,
                'bonus' => $bonus,
                'potongan' => $potongan,
            ]);

            // Hitung gaji dasar berdasarkan jenis pegawai
            if ($jenisPegawai === 'btkl') {
                // BTKL = (Tarif × Jam Kerja)
                $gajiDasar = ($tarifPerJam * $totalJamKerja);
            } else {
                // BTKTL = Gaji Pokok
                $gajiDasar = $gajiPokok;
            }

            // Total gaji = gaji dasar + tunjangan + asuransi + bonus - potongan
            $totalGaji = $gajiDasar + $tunjangan + $asuransi + $bonus - $potongan;

            // Dapatkan akun kas/bank
            $coaKasBank = Coa::where('kode_akun', $request->coa_kasbank)->first();
            if (!$coaKasBank) {
                throw new \Exception('Akun kas/bank tidak ditemukan');
            }

            // Hitung saldo kas/bank
            $saldoAwal = (float) ($coaKasBank->saldo_awal ?? 0);
            $saldoDebit = (float) ($coaKasBank->saldo_debit ?? 0);
            $saldoKredit = (float) ($coaKasBank->saldo_kredit ?? 0);
            $saldoAkhir = $saldoAwal + $saldoDebit - $saldoKredit;

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
                'tunjangan' => $tunjangan,
                'asuransi' => $asuransi,
                'bonus' => $bonus,
                'potongan' => $potongan,
                'total_jam_kerja' => $totalJamKerja,
                'total_gaji' => $totalGaji,
                'status_pembayaran' => 'belum_lunas', // Default status
            ]);

            if (!$penggajian->save()) {
                throw new \Exception('Gagal menyimpan data penggajian ke database');
            }

            \Log::info('Data penggajian berhasil disimpan', [
                'penggajian_id' => $penggajian->id,
                'total_gaji' => $totalGaji,
                'bonus' => $bonus,
                'potongan' => $potongan,
                'status_pembayaran' => 'belum_lunas'
            ]);

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

    /**
     * Buat jurnal entry untuk penggajian menggunakan JournalService
     */
    private function createJournalEntry($penggajian, $pegawai)
    {
        try {
            // Cari akun beban gaji
            $coaBebanGaji = Coa::whereRaw('LOWER(nama_akun) LIKE ?', ['%beban gaji%'])
                ->orWhere('kode_akun', '501')
                ->first();
            
            if (!$coaBebanGaji) {
                throw new \Exception('Akun beban gaji tidak ditemukan');
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
                $penggajian->tanggal_penggajian,
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
            return false;
        }
    }

    /**
     * Update saldo COA berdasarkan jurnal
     */
    protected function updateCoaSaldo($kodeAkun)
    {
        try {
            $coa = Coa::where('kode_akun', $kodeAkun)->first();
            if (!$coa) {
                \Log::warning('COA tidak ditemukan: ' . $kodeAkun);
                return false;
            }
            
            // Hitung saldo dari jurnal
            $saldo = \DB::table('journal_entries as je')
                ->join('journal_lines as jl', 'je.id', '=', 'jl.journal_entry_id')
                ->where('jl.account_id', $coa->id)
                ->selectRaw('COALESCE(SUM(jl.debit - jl.credit), 0) as saldo')
                ->first();
                
            // Update saldo di COA
            $coa->saldo_akhir = $coa->saldo_awal + $saldo->saldo;
            $coa->save();
            
            return true;
            
        } catch (\Exception $e) {
            \Log::error('Gagal update saldo COA: ' . $e->getMessage());
            return false;
        }
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
     * Form edit data penggajian.
     */
    public function edit($id)
    {
        $penggajian = Penggajian::with('pegawai')->findOrFail($id);
        $pegawais = Pegawai::all();
        return view('transaksi.penggajian.edit', compact('penggajian', 'pegawais'));
    }

    /**
     * Update data penggajian.
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'pegawai_id' => 'required|exists:pegawais,id',
            'tanggal_penggajian' => 'required|date',
            'bonus' => 'nullable|numeric|min:0',
            'potongan' => 'nullable|numeric|min:0',
        ]);

        $penggajian = Penggajian::findOrFail($id);
        $pegawai = Pegawai::findOrFail($request->pegawai_id);

        // Ambil total jam kerja pegawai dari presensi bulan ini
        $totalJamKerja = Presensi::where('pegawai_id', $pegawai->id)
            ->whereMonth('tgl_presensi', Carbon::parse($request->tanggal_penggajian)->month)
            ->whereYear('tgl_presensi', Carbon::parse($request->tanggal_penggajian)->year)
            ->sum('jumlah_jam');

        // Data dari pegawai
        $gajiPokok = (float) ($pegawai->gaji_pokok ?? 0);
        $tarifPerJam = (float) ($pegawai->tarif_per_jam ?? 0);
        $tunjangan = (float) ($pegawai->tunjangan ?? 0);
        $asuransi = (float) ($pegawai->asuransi ?? 0);
        
        // Input manual
        $bonus = (float) ($request->bonus ?? 0);
        $potongan = (float) ($request->potongan ?? 0);

        // Tentukan gaji berdasarkan jenis pegawai
        $jenis = strtolower($pegawai->jenis_pegawai ?? 'btktl');
        
        if ($jenis === 'btkl') {
            // BTKL = (Tarif × Jam Kerja) + Asuransi + Tunjangan + Bonus - Potongan
            $gajiDasar = ($tarifPerJam * (float) $totalJamKerja);
            $totalGaji = $gajiDasar + $asuransi + $tunjangan + $bonus - $potongan;
        } else {
            // BTKTL = Gaji Pokok + Asuransi + Tunjangan + Bonus - Potongan
            $totalGaji = $gajiPokok + $asuransi + $tunjangan + $bonus - $potongan;
        }

        // Cek saldo kas cukup (hitung selisih)
        $selisih = $totalGaji - ($penggajian->total_gaji ?? 0);
        if ($selisih > 0) {
            $cashCode = '101';
            $saldoAwal = (float) (\App\Models\Coa::where('kode_akun', $cashCode)->value('saldo_awal') ?? 0);
            $acc = \App\Models\Account::where('code', $cashCode)->first();
            $journalBalance = 0.0;
            if ($acc) {
                $journalBalance = (float) (\App\Models\JournalLine::where('account_id', $acc->id)
                    ->selectRaw('COALESCE(SUM(debit - credit),0) as bal')->value('bal') ?? 0);
            }
            $cashBalance = $saldoAwal + $journalBalance;
            if ($cashBalance + 1e-6 < $selisih) {
                return back()->withErrors([
                    'kas' => 'Nominal kas tidak cukup untuk melakukan transaksi. Saldo kas saat ini: Rp '.number_format($cashBalance,0,',','.').' ; Selisih nominal: Rp '.number_format($selisih,0,',','.'),
                ])->withInput();
            }
        }

        // Hapus jurnal lama
        \App\Models\JournalEntry::where('ref_type', 'penggajian')
            ->where('ref_id', $penggajian->id)
            ->delete();

        $penggajian->update([
            'pegawai_id' => $request->pegawai_id,
            'tanggal_penggajian' => $request->tanggal_penggajian,
            'gaji_pokok' => $gajiPokok,
            'tarif_per_jam' => $tarifPerJam,
            'tunjangan' => $tunjangan,
            'asuransi' => $asuransi,
            'bonus' => $bonus,
            'potongan' => $potongan,
            'total_jam_kerja' => $totalJamKerja,
            'total_gaji' => $totalGaji,
        ]);

        // Buat jurnal entry baru
        $this->createJournalEntry($penggajian, $pegawai);

        // Update BOP
        $this->updateBopBebanGaji($request->tanggal_penggajian);

        return redirect()->route('transaksi.penggajian.index')
            ->with('success', 'Data penggajian berhasil diperbarui!');
    }

    /**
     * Hapus data penggajian.
     */
    public function destroy($id)
    {
        $penggajian = Penggajian::findOrFail($id);
        
        // Hapus jurnal terkait jika ada
        \App\Models\JournalEntry::where('ref_type', 'penggajian')
            ->where('ref_id', $penggajian->id)
            ->delete();
        
        $penggajian->delete();

        return redirect()->route('transaksi.penggajian.index')
            ->with('success', 'Data penggajian berhasil dihapus.');
    }

    /**
     * Generate slip gaji HTML
     */
    public function generateSlip($id)
    {
        $penggajian = Penggajian::with('pegawai')->findOrFail($id);
        
        // Check permission: admin atau pegawai yang bersangkutan
        if (auth()->user()->role !== 'admin' && auth()->user()->pegawai_id !== $penggajian->pegawai_id) {
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
        
        // Check permission
        if (auth()->user()->role !== 'admin' && auth()->user()->pegawai_id !== $penggajian->pegawai_id) {
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
}
