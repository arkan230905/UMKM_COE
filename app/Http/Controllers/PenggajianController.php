<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
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
     * Tampilkan daftar penggajian.
     */
    public function index()
    {
        $penggajians = Penggajian::with('pegawai')->latest()->get();
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
            $request->validate([
                'pegawai_id' => 'required|exists:pegawais,id',
                'tanggal_penggajian' => 'required|date',
                'coa_kasbank' => 'required|in:' . implode(',', \App\Helpers\AccountHelper::KAS_BANK_CODES),
                'bonus' => 'nullable|numeric|min:0',
                'potongan' => 'nullable|numeric|min:0',
            ]);

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

            // Debug data sebelum disimpan
            \Log::info('Menyimpan data penggajian', [
                'pegawai_id' => $pegawai->id,
                'total_gaji' => $totalGaji,
                'coa_kasbank' => $coaKasBank->kode_akun
            ]);

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
            ]);

            if (!$penggajian->save()) {
                throw new \Exception('Gagal menyimpan data penggajian ke database');
            }

            \Log::info('Data penggajian berhasil disimpan', ['penggajian_id' => $penggajian->id]);

            // Buat jurnal entry untuk penggajian
            $journalCreated = $this->createJournalEntry($penggajian, $pegawai);
            if (!$journalCreated) {
                throw new \Exception('Gagal membuat jurnal penggajian');
            }

            \Log::info('Jurnal penggajian berhasil dibuat');

            // Dapatkan akun beban gaji untuk update saldo
            $coaBebanGaji = Coa::whereRaw('LOWER(nama_akun) LIKE ?', ['%beban gaji%'])
                ->orWhere('kode_akun', '501')
                ->first();

            if ($coaBebanGaji) {
                // Perbarui saldo COA untuk kas/bank dan beban gaji
                $this->updateCoaSaldo($coaKasBank->kode_akun);
                $this->updateCoaSaldo($coaBebanGaji->kode_akun);
                \Log::info('Saldo COA berhasil diperbarui');
            } else {
                \Log::warning('Akun beban gaji tidak ditemukan untuk update saldo');
            }
            
            // Perbarui BOP untuk Beban Gaji
            $this->updateBopBebanGaji($request->tanggal_penggajian);

            // Commit transaksi jika semua berhasil
            DB::commit();
            \Log::info('Transaksi penggajian berhasil disimpan');

            return redirect()->route('transaksi.penggajian.index')
                ->with('success', 'Data penggajian berhasil ditambahkan!');

        } catch (\Exception $e) {
            // Rollback transaksi jika terjadi error
            DB::rollBack();
            \Log::error('Error in PenggajianController@store: ' . $e->getMessage());
            \Log::error($e->getTraceAsString());

            return back()->with('error', 'Gagal menyimpan penggajian: ' . $e->getMessage())->withInput();
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
}
