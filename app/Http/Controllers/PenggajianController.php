<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Models\Penggajian;
use App\Models\Pegawai;
use App\Models\Presensi;
use App\Models\Bop;
use App\Models\Coa;
use App\Models\PenggajianTunjanganTambahan;
use App\Models\PenggajianPotonganTambahan;
use App\Models\PenggajianBonusTambahan;
use App\Services\PayrollService;
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
     * Form test tambah data penggajian (minimal).
     */
    public function testCreate()
    {
        $pegawais = Pegawai::all();
        $kasbank = \App\Helpers\AccountHelper::getKasBankAccounts();
        return view('transaksi.penggajian.test-create', compact('pegawais', 'kasbank'));
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
        // Debug logging
        \Log::info('Penggajian store method called');
        \Log::info('Request data', ['request' => $request->all()]);
        
        // Validasi input
        $validated = $request->validate([
            'pegawai_id' => 'required|exists:pegawais,id',
            'tanggal_penggajian' => 'required|date',
            'coa_kasbank' => 'required|exists:coas,kode_akun',
            'bonus' => 'nullable|numeric|min:0',
            'potongan' => 'nullable|numeric|min:0',
            'nama_bonus' => 'nullable|array',
            'nama_bonus.*' => 'nullable|string|max:255',
            'nominal_bonus' => 'nullable|array',
            'nominal_bonus.*' => 'nullable|numeric|min:0',
            'nama_tunjangan' => 'nullable|array',
            'nama_tunjangan.*' => 'nullable|string|max:255',
            'nominal_tunjangan' => 'nullable|array',
            'nominal_tunjangan.*' => 'nullable|numeric|min:0',
            'nama_potongan' => 'nullable|array',
            'nama_potongan.*' => 'nullable|string|max:255',
            'nominal_potongan' => 'nullable|array',
            'nominal_potongan.*' => 'nullable|numeric|min:0',
        ], [
            'pegawai_id.required' => 'Pegawai harus dipilih',
            'pegawai_id.exists' => 'Pegawai tidak ditemukan',
            'tanggal_penggajian.required' => 'Tanggal penggajian harus diisi',
            'tanggal_penggajian.date' => 'Format tanggal tidak valid',
            'coa_kasbank.required' => 'Akun kas/bank harus dipilih',
            'coa_kasbank.exists' => 'Akun kas/bank tidak valid',
        ]);
        
        \Log::info('Validation passed', ['validated' => $validated]);

        DB::beginTransaction();
        try {
            $pegawai = Pegawai::findOrFail($validated['pegawai_id']);
            \Log::info('Pegawai found', ['pegawai' => $pegawai->toArray()]);
            
            // Hitung total jam kerja dari presensi
            $totalJamKerja = Presensi::where('pegawai_id', $pegawai->id)
                ->whereMonth('tgl_presensi', Carbon::parse($validated['tanggal_penggajian'])->month)
                ->whereYear('tgl_presensi', Carbon::parse($validated['tanggal_penggajian'])->year)
                ->sum('jumlah_jam');
            
            \Log::info('Total jam kerja', ['total' => $totalJamKerja]);
            
            // Data dari pegawai
            $tunjanganJabatan = (float) ($pegawai->tunjangan ?? 0);
            $asuransi = (float) ($pegawai->asuransi ?? 0);
            
            // Hitung total dari input dinamis
            $totalBonusTambahan = collect($request->nominal_bonus ?? [])->sum();
            $totalTunjanganTambahan = collect($request->nominal_tunjangan ?? [])->sum();
            $totalPotonganTambahan = collect($request->nominal_potongan ?? [])->sum();
            
            \Log::info('Total tambahan:', [
                'bonus' => $totalBonusTambahan,
                'tunjangan' => $totalTunjanganTambahan,
                'potongan' => $totalPotonganTambahan
            ]);
            
            // Tentukan perhitungan berdasarkan jenis pegawai
            $jenis = strtolower($pegawai->jenis_pegawai ?? 'btktl');
            \Log::info('Jenis pegawai', ['jenis' => $jenis]);
            
            if ($jenis === 'btkl') {
                // BTKL: Gaji dasar = tarif × jam kerja
                $tarifPerJam = (float) ($pegawai->tarif_per_jam ?? 0);
                $gajiPokok = 0; // Tidak dipakai untuk BTKL
                $gajiDasar = $tarifPerJam * $totalJamKerja;
                
                \Log::info('BTKL calculation', [
                    'tarif' => $tarifPerJam,
                    'jam_kerja' => $totalJamKerja,
                    'gaji_dasar' => $gajiDasar
                ]);
                
                // Validasi: BTKL harus punya tarif
                if ($tarifPerJam <= 0) {
                    return back()->withErrors([
                        'pegawai_id' => 'Pegawai BTKL harus memiliki tarif per jam yang valid'
                    ])->withInput();
                }
                
                $totalGaji = $gajiDasar + $tunjanganJabatan + $totalTunjanganTambahan + $totalBonusTambahan + $asuransi - $totalPotonganTambahan;
            } else {
                // BTKTL: Gaji pokok langsung
                $gajiPokok = (float) ($pegawai->gaji_pokok ?? 0);
                $tarifPerJam = 0; // Tidak dipakai untuk BTKTL
                $gajiDasar = $gajiPokok;
                
                \Log::info('BTKTL calculation', [
                    'gaji_pokok' => $gajiPokok,
                    'gaji_dasar' => $gajiDasar
                ]);
                
                // Validasi: BTKTL harus punya gaji pokok
                if ($gajiPokok <= 0) {
                    return back()->withErrors([
                        'pegawai_id' => 'Pegawai BTKTL harus memiliki gaji pokok yang valid'
                    ])->withInput();
                }
                
                $totalGaji = $gajiPokok + $tunjanganJabatan + $totalTunjanganTambahan + $totalBonusTambahan + $asuransi - $totalPotonganTambahan;
            }
            
            \Log::info('Final calculation', [
                'gaji_dasar' => $gajiDasar,
                'tunjangan_jabatan' => $tunjanganJabatan,
                'tunjangan_tambahan' => $totalTunjanganTambahan,
                'bonus_tambahan' => $totalBonusTambahan,
                'asuransi' => $asuransi,
                'potongan_tambahan' => $totalPotonganTambahan,
                'total_gaji' => $totalGaji
            ]);

            // Simpan ke tabel penggajian
            $penggajianData = [
                'pegawai_id' => $pegawai->id,
                'tanggal_penggajian' => $validated['tanggal_penggajian'],
                'coa_kasbank' => $validated['coa_kasbank'],
                'gaji_pokok' => $gajiPokok,
                'tarif_per_jam' => $tarifPerJam,
                'tunjangan' => $tunjanganJabatan,
                'asuransi' => $asuransi,
                'bonus' => $totalBonusTambahan, // Total bonus tambahan
                'potongan' => $totalPotonganTambahan, // Total potongan tambahan
                'total_jam_kerja' => $totalJamKerja,
                'total_gaji' => $totalGaji,
            ];
            
            \Log::info('Data to save', ['data' => $penggajianData]);
            
            $penggajian = Penggajian::create($penggajianData);
            
            \Log::info('Penggajian saved successfully', ['id' => $penggajian->id]);
            
            // Simpan detail bonus tambahan
            $this->saveDetailBonus($penggajian, $request);
            
            // Simpan detail tunjangan tambahan
            $this->saveDetailTunjangan($penggajian, $request);
            
            // Simpan detail potongan tambahan
            $this->saveDetailPotongan($penggajian, $request);
            
            // Buat jurnal otomatis untuk penggajian
            $this->createPayrollJournal($penggajian, $pegawai);
            
            DB::commit();
            return redirect()->route('transaksi.penggajian.index')
                ->with('success', 'Data penggajian berhasil ditambahkan!');

        } catch (\Exception $e) {
            DB::rollback();
            \Log::error('Error in PenggajianController@store', [
                'exception' => $e,
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Buat jurnal otomatis untuk transaksi penggajian
     */
    private function createPayrollJournal($penggajian, $pegawai)
    {
        try {
            // Cari akun beban gaji (biasanya kode 501)
            $coaBebanGaji = Coa::where('kode_akun', '501')
                ->orWhereRaw('LOWER(nama_akun) LIKE ?', ['%beban gaji%'])
                ->first();
            
            if (!$coaBebanGaji) {
                \Log::warning('Akun beban gaji tidak ditemukan, menggunakan default 501');
                $coaBebanGaji = Coa::where('kode_akun', '501')->first();
            }

            // Pastikan akun kas/bank valid
            $coaKasBank = Coa::where('kode_akun', $penggajian->coa_kasbank)->first();
            if (!$coaKasBank) {
                throw new \Exception('Akun kas/bank tidak valid: ' . $penggajian->coa_kasbank);
            }
            
            \Log::info('Membuat jurnal penggajian', [
                'penggajian_id' => $penggajian->id,
                'pegawai' => $pegawai->nama,
                'total_gaji' => $penggajian->total_gaji,
                'coa_beban' => $coaBebanGaji->kode_akun,
                'coa_kasbank' => $coaKasBank->kode_akun
            ]);
            
            // Gunakan JournalService untuk konsistensi
            $journalService = app(\App\Services\JournalService::class);
            
            $journalEntry = $journalService->post(
                $penggajian->tanggal_penggajian,
                'penggajian',
                (int)$penggajian->id,
                'Pembayaran Gaji - ' . $pegawai->nama,
                [
                    ['code' => $coaBebanGaji->kode_akun, 'debit' => (float)$penggajian->total_gaji, 'credit' => 0],
                    ['code' => $coaKasBank->kode_akun, 'debit' => 0, 'credit' => (float)$penggajian->total_gaji],
                ]
            );
            
            \Log::info('Jurnal penggajian berhasil dibuat', ['journal_entry_id' => $journalEntry->id]);
            
            return $journalEntry;
            
        } catch (\Exception $e) {
            \Log::error('Gagal membuat jurnal penggajian', [
                'exception' => $e,
                'message' => $e->getMessage(),
                'penggajian_id' => $penggajian->id
            ]);
            throw $e;
        }
    }

    /**
     * Simpan detail bonus tambahan
     */
    private function saveDetailBonus($penggajian, $request)
    {
        if ($request->has('nama_bonus') && is_array($request->nama_bonus)) {
            foreach ($request->nama_bonus as $index => $nama) {
                if (!empty($nama) && isset($request->nominal_bonus[$index])) {
                    $nominal = (float) $request->nominal_bonus[$index];
                    if ($nominal > 0) {
                        $penggajian->bonus()->create([
                            'nama' => $nama,
                            'nominal' => $nominal,
                        ]);
                    }
                }
            }
        }
    }

    /**
     * Simpan detail tunjangan tambahan
     */
    private function saveDetailTunjangan($penggajian, $request)
    {
        if ($request->has('nama_tunjangan') && is_array($request->nama_tunjangan)) {
            foreach ($request->nama_tunjangan as $index => $nama) {
                if (!empty($nama) && isset($request->nominal_tunjangan[$index])) {
                    $nominal = (float) $request->nominal_tunjangan[$index];
                    if ($nominal > 0) {
                        $penggajian->tunjangan()->create([
                            'nama' => $nama,
                            'nominal' => $nominal,
                        ]);
                    }
                }
            }
        }
    }

    /**
     * Simpan detail potongan tambahan
     */
    private function saveDetailPotongan($penggajian, $request)
    {
        if ($request->has('nama_potongan') && is_array($request->nama_potongan)) {
            foreach ($request->nama_potongan as $index => $nama) {
                if (!empty($nama) && isset($request->nominal_potongan[$index])) {
                    $nominal = (float) $request->nominal_potongan[$index];
                    if ($nominal > 0) {
                        $penggajian->potongan()->create([
                            'nama' => $nama,
                            'nominal' => $nominal,
                        ]);
                    }
                }
            }
        }
    }

    /**
     * AJAX: Get data pegawai untuk form penggajian
     */
    public function getPegawaiData($pegawaiId, $tanggal)
    {
        try {
            $pegawai = Pegawai::findOrFail($pegawaiId);
            
            // Hitung total jam kerja bulan berjarkan
            $totalJamKerja = Presensi::where('pegawai_id', $pegawaiId)
                ->whereMonth('tgl_presensi', Carbon::parse($tanggal)->month)
                ->whereYear('tgl_presensi', Carbon::parse($tanggal)->year)
                ->sum('jumlah_jam');
            
            // Tentukan data berdasarkan jenis pegawai
            $jenis = strtolower($pegawai->jenis_pegawai ?? 'btktl');
            
            if ($jenis === 'btkl') {
                $data = [
                    'jenis' => 'btkl',
                    'tarif_per_jam' => (float) ($pegawai->tarif_per_jam ?? 0),
                    'gaji_pokok' => 0,
                    'tunjangan' => (float) ($pegawai->tunjangan ?? 0),
                    'asuransi' => (float) ($pegawai->asuransi ?? 0),
                    'total_jam_kerja' => $totalJamKerja,
                    'gaji_dasar' => ((float) ($pegawai->tarif_per_jam ?? 0)) * $totalJamKerja,
                ];
            } else {
                $data = [
                    'jenis' => 'btktl',
                    'gaji_pokok' => (float) ($pegawai->gaji_pokok ?? 0),
                    'tarif_per_jam' => 0,
                    'tunjangan' => (float) ($pegawai->tunjangan ?? 0),
                    'asuransi' => (float) ($pegawai->asuransi ?? 0),
                    'total_jam_kerja' => $totalJamKerja, // Tetap kirim untuk info
                    'gaji_dasar' => (float) ($pegawai->gaji_pokok ?? 0),
                ];
            }
            
            return response()->json($data);
            
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Tampilkan detail penggajian.
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
            \Log::error('Gagal membuat jurnal penggajian', [
                'exception' => $e,
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
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
                \Log::warning('COA tidak ditemukan', ['kode_akun' => $kodeAkun]);
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
            \Log::error('Gagal update saldo COA', [
                'exception' => $e,
                'message' => $e->getMessage()
            ]);
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
     * Tampilkan slip gaji
     */
    public function showSlip($id)
    {
        try {
            \Log::info('=== DEBUG showSlip START ===');
            \Log::info('Accessing showSlip', ['id' => $id]);
            
            $penggajian = Penggajian::with(['pegawai', 'bonus', 'tunjangan', 'potongan'])->findOrFail($id);
            \Log::info('Penggajian found', ['id' => $penggajian->id]);
            \Log::info('Pegawai', ['nama' => $penggajian->pegawai->nama]);
            \Log::info('Jenis Pegawai', ['jenis' => $penggajian->pegawai->jenis_pegawai ?? 'null']);
            
            $slipData = $this->calculateSlipData($penggajian);
            \Log::info('SlipData calculated successfully');
            \Log::info('Total Pendapatan', ['amount' => $slipData['total_pendapatan']]);
            \Log::info('Total Potongan', ['amount' => $slipData['total_potongan']]);
            \Log::info('Gaji Bersih', ['amount' => $slipData['gaji_bersih']]);
            
            \Log::info('Returning view...');
            return view('transaksi.penggajian.slip', compact('penggajian', 'slipData'));
        } catch (\Exception $e) {
            \Log::error('Error in showSlip', [
                'exception' => $e,
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return back()->with('error', 'Gagal menampilkan slip gaji: ' . $e->getMessage());
        }
    }

    /**
     * Export slip gaji sebagai PDF
     */
    public function exportSlipPdf($id)
    {
        $penggajian = Penggajian::with('pegawai')->findOrFail($id);
        $slipData = $this->calculateSlipData($penggajian);
        
        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('transaksi.penggajian.slip-pdf', compact('penggajian', 'slipData'));
        return $pdf->download('slip-gaji-' . $penggajian->pegawai->nama . '-' . $penggajian->tanggal_penggajian->format('Y-m-d') . '.pdf');
    }

    /**
     * Hitung data slip gaji
     */
    private function calculateSlipData($penggajian)
    {
        $pegawai = $penggajian->pegawai;
        $jenis = strtolower($pegawai->jenis_pegawai ?? 'btktl');
        
        // Rincian Pendapatan
        $pendapatan = [];
        $totalPendapatan = 0;

        if ($jenis === 'btkl') {
            // BTKL: Tarif × Jam Kerja
            $gajiDasar = (float)$penggajian->tarif_per_jam * (float)$penggajian->total_jam_kerja;
            $pendapatan['gaji_dasar'] = [
                'label' => 'Gaji Dasar (Tarif × Jam Kerja)',
                'tarif' => (float)$penggajian->tarif_per_jam,
                'unit' => (float)$penggajian->total_jam_kerja . ' jam',
                'nilai' => $gajiDasar
            ];
            $totalPendapatan += $gajiDasar;
        } else {
            // BTKTL: Gaji Pokok
            $gajiDasar = (float)$penggajian->gaji_pokok;
            $pendapatan['gaji_pokok'] = [
                'label' => 'Gaji Pokok',
                'nilai' => $gajiDasar
            ];
            $totalPendapatan += $gajiDasar;
        }

        // Tunjangan Jabatan
        if ((float)$penggajian->tunjangan > 0) {
            $pendapatan['tunjangan'] = [
                'label' => 'Tunjangan Jabatan',
                'nilai' => (float)$penggajian->tunjangan
            ];
            $totalPendapatan += (float)$penggajian->tunjangan;
        }

        // Tunjangan Tambahan (Detail)
        foreach ($penggajian->tunjanganTambahans as $tunjangan) {
            if ($tunjangan->nominal > 0) {
                $pendapatan['tunjangan_tambahan_' . $tunjangan->id] = [
                    'label' => $tunjangan->nama,
                    'nilai' => (float)$tunjangan->nominal
                ];
                $totalPendapatan += (float)$tunjangan->nominal;
            }
        }

        // Bonus Tambahan (Detail)
        foreach ($penggajian->bonusTambahans as $bonus) {
            if ($bonus->nominal > 0) {
                $pendapatan['bonus_tambahan_' . $bonus->id] = [
                    'label' => $bonus->nama,
                    'nilai' => (float)$bonus->nominal
                ];
                $totalPendapatan += (float)$bonus->nominal;
            }
        }

        // Rincian Potongan
        $potongan = [];
        $totalPotongan = 0;

        // Asuransi
        if ((float)$penggajian->asuransi > 0) {
            $potongan['asuransi'] = [
                'label' => 'Asuransi',
                'nilai' => (float)$penggajian->asuransi
            ];
            $totalPotongan += (float)$penggajian->asuransi;
        }

        // Potongan Tambahan (Detail)
        foreach ($penggajian->potonganTambahans as $potonganTambahan) {
            if ($potonganTambahan->nominal > 0) {
                $potongan['potongan_tambahan_' . $potonganTambahan->id] = [
                    'label' => $potonganTambahan->nama,
                    'nilai' => (float)$potonganTambahan->nominal
                ];
                $totalPotongan += (float)$potonganTambahan->nominal;
            }
        }

        // Total Akhir
        $totalAkhir = $totalPendapatan - $totalPotongan;

        return [
            'pendapatan' => $pendapatan,
            'total_pendapatan' => $totalPendapatan,
            'potongan' => $potongan,
            'total_potongan' => $totalPotongan,
            'gaji_bersih' => $totalPendapatan - $totalPotongan,
            'total_akhir' => $totalPendapatan - $totalPotongan,
            'jenis_pegawai' => $jenis
        ];
    }

    /**
     * Form edit penggajian
     */
    public function edit($id)
    {
        $penggajian = Penggajian::with('pegawai')->findOrFail($id);
        $kasbank = \App\Helpers\AccountHelper::getKasBankAccounts();
        return view('transaksi.penggajian.edit', compact('penggajian', 'kasbank'));
    }

    /**
     * Update penggajian
     */
    public function update(Request $request, $id)
    {
        DB::beginTransaction();

        try {
            $penggajian = Penggajian::findOrFail($id);
            $pegawai = $penggajian->pegawai;
            $payrollService = app(PayrollService::class);

            $request->validate([
                'bonus' => 'nullable|numeric|min:0',
                'potongan' => 'nullable|numeric|min:0',
                'catatan' => 'nullable|string',
                'jam_lembur' => 'nullable|numeric|min:0',
            ]);

            $bonus = (float)($request->bonus ?? 0);
            $potongan = (float)($request->potongan ?? 0);

            // Hitung gaji ulang dengan PayrollService
            $gajiData = $payrollService->hitungGajiPegawai(
                $pegawai,
                $bonus,
                $potongan,
                $penggajian->tanggal_penggajian->month,
                $penggajian->tanggal_penggajian->year
            );

            // Update penggajian
            $penggajian->update([
                'bonus' => $bonus,
                'potongan' => $potongan,
                'total_gaji' => $gajiData['total_gaji'],
                'catatan' => $request->catatan,
                'jam_lembur' => $request->jam_lembur ?? 0,
            ]);

            // Simpan bonus tambahan
            $this->saveBonusTambahan($penggajian, $request);
            
            // Simpan tunjangan tambahan
            $this->saveTunjanganTambahan($penggajian, $request);
            
            // Simpan potongan tambahan
            $this->savePotonganTambahan($penggajian, $request);

            DB::commit();

            return redirect()->route('transaksi.penggajian.index')
                ->with('success', 'Penggajian berhasil diperbarui');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal memperbarui penggajian: ' . $e->getMessage());
        }
    }

    /**
     * Setujui penggajian (ubah status menjadi siap_dibayar)
     */
    public function approve($id)
    {
        $penggajian = Penggajian::findOrFail($id);

        if ($penggajian->status !== 'draft') {
            return back()->with('error', 'Hanya penggajian draft yang bisa disetujui');
        }

        $penggajian->update(['status' => 'siap_dibayar']);

        return back()->with('success', 'Penggajian berhasil disetujui');
    }

    /**
     * Bayar penggajian (ubah status menjadi dibayar dan buat jurnal)
     */
    public function pay($id)
    {
        DB::beginTransaction();

        try {
            $penggajian = Penggajian::with('pegawai')->findOrFail($id);

            if ($penggajian->status !== 'siap_dibayar') {
                return back()->with('error', 'Hanya penggajian yang siap dibayar yang bisa dibayarkan');
            }

            // Validasi saldo kas
            $coaKasBank = Coa::where('kode_akun', $penggajian->coa_kasbank)->first();
            if (!$coaKasBank) {
                throw new \Exception('Akun kas/bank tidak ditemukan');
            }

            $saldoAwal = (float)($coaKasBank->saldo_awal ?? 0);
            $saldoDebit = (float)($coaKasBank->saldo_debit ?? 0);
            $saldoKredit = (float)($coaKasBank->saldo_kredit ?? 0);
            $saldoAkhir = $saldoAwal + $saldoDebit - $saldoKredit;

            if ($saldoAkhir < $penggajian->total_gaji) {
                throw new \Exception('Saldo kas/bank tidak mencukupi');
            }

            // Buat jurnal pembayaran gaji
            $this->createPaymentJournal($penggajian, $coaKasBank);

            // Update status penggajian
            $penggajian->update([
                'status' => 'dibayar',
                'tanggal_pembayaran' => now(),
            ]);

            DB::commit();

            return back()->with('success', 'Gaji berhasil dibayarkan');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal membayarkan gaji: ' . $e->getMessage());
        }
    }

    /**
     * Buat jurnal pembayaran gaji
     */
    private function createPaymentJournal($penggajian, $coaKasBank)
    {
        try {
            // Cari akun utang gaji (biasanya 201 atau sesuai COA)
            $coaUtangGaji = Coa::where('kode_akun', '201')
                ->orWhereRaw('LOWER(nama_akun) LIKE ?', ['%utang gaji%'])
                ->first();

            if (!$coaUtangGaji) {
                // Jika tidak ada, gunakan akun beban gaji
                $coaUtangGaji = Coa::where('kode_akun', '501')
                    ->orWhereRaw('LOWER(nama_akun) LIKE ?', ['%beban gaji%'])
                    ->first();
            }

            if (!$coaUtangGaji) {
                throw new \Exception('Akun utang gaji atau beban gaji tidak ditemukan');
            }

            // Gunakan JournalService jika ada
            if (class_exists(\App\Services\JournalService::class)) {
                $journalService = app(\App\Services\JournalService::class);

                $journalService->post(
                    $penggajian->tanggal_pembayaran ?? now(),
                    'penggajian_pembayaran',
                    (int)$penggajian->id,
                    'Pembayaran Gaji - ' . $penggajian->pegawai->nama,
                    [
                        ['code' => $coaUtangGaji->kode_akun, 'debit' => (float)$penggajian->total_gaji, 'credit' => 0],
                        ['code' => $coaKasBank->kode_akun, 'debit' => 0, 'credit' => (float)$penggajian->total_gaji],
                    ]
                );
            }

        } catch (\Exception $e) {
            \Log::error('Gagal membuat jurnal pembayaran gaji', [
                'exception' => $e,
                'message' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Simpan bonus tambahan dari form
     */
    private function saveBonusTambahan($penggajian, Request $request)
    {
        $names = $request->input('bonus_tambahan_names', []);
        $values = $request->input('bonus_tambahan_values', []);

        // Hapus bonus tambahan lama
        $penggajian->bonusTambahans()->delete();

        // Simpan bonus tambahan baru
        foreach ($names as $index => $name) {
            if (!empty($name) && !empty($values[$index])) {
                $penggajian->bonusTambahans()->create([
                    'nama' => $name,
                    'nominal' => (float)$values[$index],
                ]);
            }
        }
    }

    /**
     * Simpan tunjangan tambahan dari form
     */
    private function saveTunjanganTambahan($penggajian, Request $request)
    {
        $names = $request->input('tunjangan_tambahan_names', []);
        $values = $request->input('tunjangan_tambahan_values', []);

        // Hapus tunjangan tambahan lama
        $penggajian->tunjanganTambahans()->delete();

        // Simpan tunjangan tambahan baru
        foreach ($names as $index => $name) {
            if (!empty($name) && !empty($values[$index])) {
                $penggajian->tunjanganTambahans()->create([
                    'nama' => $name,
                    'nominal' => (float)$values[$index],
                ]);
            }
        }
    }

    /**
     * Simpan potongan tambahan dari form
     */
    private function savePotonganTambahan($penggajian, Request $request)
    {
        $names = $request->input('potongan_tambahan_names', []);
        $values = $request->input('potongan_tambahan_values', []);

        // Hapus potongan tambahan lama
        $penggajian->potonganTambahans()->delete();

        // Simpan potongan tambahan baru
        foreach ($names as $index => $name) {
            if (!empty($name) && !empty($values[$index])) {
                $penggajian->potonganTambahans()->create([
                    'nama' => $name,
                    'nominal' => (float)$values[$index],
                ]);
            }
        }
    }
}
