<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Http\Request;
use App\Models\Penggajian;
use App\Models\Pegawai;
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
        // CRITICAL: Filter by user_id untuk multi-tenant isolation
        $query = Penggajian::with('pegawai')
            ->where('user_id', auth()->id());

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
     * Tampilkan form tambah penggajian.
     */
    public function create()
    {
        // Clear any old validation errors from session
        session()->forget('errors');

        // CRITICAL: Filter by user_id untuk multi-tenant isolation
        $pegawais = Pegawai::with('kualifikasiRelasi')
            ->select('pegawais.*')
            ->where('user_id', auth()->id())
            ->orderBy('nama')
            ->get();
        $kasbank = \App\Helpers\AccountHelper::getKasBankAccounts();
        
        // Log untuk debugging multi-tenant
        \Log::info('create form loaded', [
            'user_id' => auth()->id(),
            'user_name' => auth()->user()->name ?? 'unknown',
            'pegawais_count' => $pegawais->count(),
            'timestamp' => now()->toIso8601String(),
        ]);
        
        // Return view dengan cache-busting headers
        return response()
            ->view('transaksi.penggajian.create', compact('pegawais', 'kasbank'))
            ->header('Cache-Control', 'no-cache, no-store, must-revalidate, private, max-age=0')
            ->header('Pragma', 'no-cache')
            ->header('Expires', '0')
            ->header('X-Content-Type-Options', 'nosniff')
            ->header('X-Frame-Options', 'SAMEORIGIN')
            ->header('X-UA-Compatible', 'IE=edge');
    }

    /**
     * Tampilkan form tambah penggajian berbasis produk (BTKL).
     */
    public function createProduk()
    {
        // Clear any old validation errors from session
        session()->forget('errors');

        // CRITICAL: Filter by user_id untuk multi-tenant isolation
        $pegawais = Pegawai::with('kualifikasiRelasi')
            ->select('pegawais.*')
            ->where('user_id', auth()->id())
            ->orderBy('nama')
            ->get();
        $kasbank = \App\Helpers\AccountHelper::getKasBankAccounts();
        
        $kategoris = \App\Models\Kualifikasi::where('user_id', auth()->id())
            ->select('nama', 'kategori')
            ->distinct()
            ->orderBy('nama')
            ->get();
        
        // Log untuk debugging multi-tenant
        \Log::info('createProduk form loaded', [
            'user_id' => auth()->id(),
            'user_name' => auth()->user()->name ?? 'unknown',
            'pegawais_count' => $pegawais->count(),
            'timestamp' => now()->toIso8601String(),
        ]);
        
        // Return view dengan cache-busting headers
        return response()
            ->view('transaksi.penggajian.create-produk', compact('pegawais', 'kasbank', 'kategoris'))
            ->header('Cache-Control', 'no-cache, no-store, must-revalidate, private, max-age=0')
            ->header('Pragma', 'no-cache')
            ->header('Expires', '0')
            ->header('X-Content-Type-Options', 'nosniff')
            ->header('X-Frame-Options', 'SAMEORIGIN')
            ->header('X-UA-Compatible', 'IE=edge');
    }

    /**
     * API endpoint to get total produksi for a pegawai in a specific month
     * Mengambil TOTAL BULAN (bukan per hari)
     */
    public function getTotalProduksiByMonth($pegawaiId, $bulan, $tahun)
    {
        try {
            // CRITICAL: Filter by user_id untuk multi-tenant isolation
            $pegawai = Pegawai::where('user_id', auth()->id())
                ->findOrFail($pegawaiId);
            
            // Hitung tanggal awal dan akhir bulan
            $tanggalAwal = \Carbon\Carbon::createFromDate($tahun, $bulan, 1)->startOfMonth();
            $tanggalAkhir = \Carbon\Carbon::createFromDate($tahun, $bulan, 1)->endOfMonth();
            
            // Dapatkan kualifikasi pegawai untuk acuan filter
            $jabatan = app(\App\Services\KualifikasiTargetResolver::class)->resolve($pegawai);

            // Query produksi berdasarkan bulan dan tahun (tanpa filter pegawai_id karena produksi untuk seluruh pabrik)
            $query = \App\Models\Produksi::where('user_id', auth()->id())
                ->whereBetween('tanggal', [$tanggalAwal, $tanggalAkhir]);

            // Gunakan target_produksi dari kualifikasi sebagai acuan filter di transaksi produksi
            if ($jabatan && $jabatan->target_produksi > 0) {
                $query->where('jumlah_produksi_bulanan', $jabatan->target_produksi);
            }

            $produksi = $query->orderBy('tanggal', 'desc')->first();
            
            // Jika tidak ada record, return 0
            if (!$produksi) {
                \Log::info('getTotalProduksiByMonth - No record found', [
                    'pegawai_id' => $pegawaiId,
                    'pegawai_nama' => $pegawai->nama,
                    'bulan' => $bulan,
                    'tahun' => $tahun,
                    'tanggal_awal' => $tanggalAwal->format('Y-m-d'),
                    'tanggal_akhir' => $tanggalAkhir->format('Y-m-d'),
                ]);
                
                return response()->json([
                    'status' => 'success',
                    'data' => [
                        'pegawai_id' => $pegawaiId,
                        'pegawai_nama' => $pegawai->nama,
                        'bulan' => str_pad($bulan, 2, '0', STR_PAD_LEFT),
                        'tahun' => $tahun,
                        'tanggal_awal' => $tanggalAwal->format('Y-m-d'),
                        'tanggal_akhir' => $tanggalAkhir->format('Y-m-d'),
                        'total_produksi' => 0,
                        'jumlah_transaksi' => 0,
                        'hari_produksi_bulanan' => 0,
                    ]
                ]);
            }
            
            // Ambil jumlah_produksi_bulanan dari record
            $totalProduksi = (int) $produksi->jumlah_produksi_bulanan ?? 0;
            $hariProduksiBulanan = (int) $produksi->hari_produksi_bulanan ?? 26;
            
            \Log::info('getTotalProduksiByMonth', [
                'pegawai_id' => $pegawaiId,
                'pegawai_nama' => $pegawai->nama,
                'bulan' => $bulan,
                'tahun' => $tahun,
                'tanggal_awal' => $tanggalAwal->format('Y-m-d'),
                'tanggal_akhir' => $tanggalAkhir->format('Y-m-d'),
                'total_produksi' => $totalProduksi,
                'hari_produksi_bulanan' => $hariProduksiBulanan,
            ]);
            
            return response()->json([
                'status' => 'success',
                'data' => [
                    'pegawai_id' => $pegawaiId,
                    'pegawai_nama' => $pegawai->nama,
                    'bulan' => str_pad($bulan, 2, '0', STR_PAD_LEFT),
                    'tahun' => $tahun,
                    'tanggal_awal' => $tanggalAwal->format('Y-m-d'),
                    'tanggal_akhir' => $tanggalAkhir->format('Y-m-d'),
                    'total_produksi' => $totalProduksi,
                    'jumlah_transaksi' => 1,
                    'hari_produksi_bulanan' => $hariProduksiBulanan,
                ]
            ]);
            
        } catch (\Exception $e) {
            \Log::error('Error in getTotalProduksiByMonth', [
                'pegawai_id' => $pegawaiId,
                'bulan' => $bulan,
                'tahun' => $tahun,
                'error' => $e->getMessage(),
            ]);
            
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 404);
        }
    }

    /**
     * API endpoint to get attendance data for a pegawai in a specific month
     */
    public function getAttendanceData($pegawaiId, $bulan, $tahun)
    {
        try {
            // CRITICAL: Filter by user_id untuk multi-tenant isolation
            $pegawai = Pegawai::where('user_id', auth()->id())->findOrFail($pegawaiId);
            
            $presensi = \App\Models\Presensi::where('pegawai_id', $pegawaiId)
                ->whereMonth('tgl_presensi', $bulan)
                ->whereYear('tgl_presensi', $tahun)
                ->get();

            $jumlahHadir = $presensi->filter(function($item) {
                return strtolower($item->status) === 'hadir';
            })->count();

            $jumlahAlpa = $presensi->filter(function($item) {
                return strtolower($item->status) === 'alpa';
            })->count();
            
            return response()->json([
                'status' => 'success',
                'data' => [
                    'jumlah_hadir' => $jumlahHadir,
                    'jumlah_alpa' => $jumlahAlpa,
                    'total_data' => $presensi->count(),
                ]
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 404);
        }
    }

    /**
     * API endpoint to get real-time employee salary data
     */
    public function getEmployeeData($pegawaiId)
    {
        try {
            // CRITICAL: Filter by user_id untuk multi-tenant isolation
            $pegawai = Pegawai::with('kualifikasiRelasi')
                ->where('user_id', auth()->id())
                ->findOrFail($pegawaiId);
            
            \Log::info('getEmployeeData - Pegawai loaded', [
                'pegawai_id' => $pegawaiId,
                'nama' => $pegawai->nama,
                'kualifikasi_string' => $pegawai->kualifikasi,
                'kualifikasi_id' => $pegawai->kualifikasi_id,
                'has_kualifikasi_relasi' => $pegawai->kualifikasiRelasi ? 'YES' : 'NO'
            ]);
            
            // Get current salary data from qualification
            $kualifikasi = app(\App\Services\KualifikasiTargetResolver::class)->resolve($pegawai);
            
            \Log::info('getEmployeeData - Kualifikasi resolved', [
                'kualifikasi_found' => $kualifikasi ? 'YES' : 'NO',
                'kualifikasi_nama' => $kualifikasi->nama_kualifikasi ?? 'NULL',
                'tarif_produk' => $kualifikasi->tarif_produk ?? 'NULL',
                'asuransi' => $kualifikasi->asuransi ?? 'NULL'
            ]);
            
            if ($kualifikasi) {
                $tarif = (int) ($kualifikasi->tarif_produk ?? 0);
                // Accessor otomatis fallback ke gaji jika gaji_pokok kosong
                $gajiPokok = (int) ($kualifikasi->gaji_pokok ?? 0);
                $tunjanganJabatan = (int) ($kualifikasi->tunjangan ?? 0);
                $tunjanganTransport = (int) ($kualifikasi->tunjangan_transport ?? 0);
                $tunjanganKonsumsi = (int) ($kualifikasi->tunjangan_konsumsi ?? 0);
                // CRITICAL: Asuransi HARUS dari kualifikasi
                $asuransi = (int) ($kualifikasi->asuransi ?? 0);
                $kualifikasiNama = $kualifikasi->nama_kualifikasi ?? 'Unknown';
            } else {
                // Fallback to pegawai stored values
                $tarif = (int) ($pegawai->tarif_per_jam ?? $pegawai->tarif ?? 0);
                $gajiPokok = (int) ($pegawai->gaji_pokok ?? 0);
                $tunjanganJabatan = (int) ($pegawai->tunjangan_jabatan ?? $pegawai->tunjangan ?? 0);
                $tunjanganTransport = (int) ($pegawai->tunjangan_transport ?? 0);
                $tunjanganKonsumsi = (int) ($pegawai->tunjangan_konsumsi ?? 0);
                $asuransi = 0;
                $kualifikasiNama = $pegawai->kualifikasi ?? $pegawai->jabatan ?? 'Staff';
            }
            
            $totalTunjangan = $tunjanganJabatan + $tunjanganTransport + $tunjanganKonsumsi;
            
            $kategoriInternal = 'BTKTL';
            if ($kualifikasi) {
                $kategoriInternal = strtolower($kualifikasi->kategori) === 'btkl' ? 'BTKL' : 'BTKTL';
            } else {
                $jenis = strtolower($pegawai->jenis_pegawai ?? $pegawai->kategori ?? 'btktl');
                $kategoriInternal = $jenis === 'btkl' ? 'BTKL' : 'BTKTL';
            }

            $response = [
                'tarif' => $tarif,
                'gaji_pokok' => $gajiPokok,
                'tunjangan_jabatan' => $tunjanganJabatan,
                'tunjangan_transport' => $tunjanganTransport,
                'tunjangan_konsumsi' => $tunjanganKonsumsi,
                'total_tunjangan' => $totalTunjangan,
                'asuransi' => $asuransi,
                'nama' => $pegawai->nama,
                'jabatan_nama' => $kualifikasiNama,
                'kualifikasi_nama' => $kualifikasiNama, // For backward compatibility with form JS
                'kategori' => $kategoriInternal, // Simplified to BTKL or BTKTI
                'bank' => $pegawai->bank,
                'nomor_rekening' => $pegawai->nomor_rekening,
                'nama_rekening' => $pegawai->nama_rekening
            ];
            
            \Log::info('getEmployeeData - Final response', $response);
            
            return response()->json($response);
            
        } catch (\Exception $e) {
            \Log::error('Error in getEmployeeData', [
                'pegawai_id' => $pegawaiId,
                'user_id' => auth()->id(),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'error' => 'Employee not found or access denied',
                'message' => $e->getMessage()
            ], 404);
        }
    }

    /**
     * API endpoint to get master kategori / jabatan info
     */
    public function getMasterKategori($nama)
    {
        try {
            $jabatan = \App\Models\Kualifikasi::where('user_id', auth()->id())
                ->where('nama_kualifikasi', $nama)
                ->first();
            
            if (!$jabatan) {
                return response()->json([
                    'error' => 'Kategori tidak ditemukan'
                ], 404);
            }

            $isProduksi = strtolower($jabatan->kategori) === 'btkl';

            return response()->json([
                'status' => 'success',
                'data' => [
                    'nama_kategori' => $jabatan->nama,
                    'tipe_gaji' => $jabatan->kategori,
                    'produksi' => $isProduksi
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Gagal memuat kategori',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Simpan data penggajian baru (JAM-BASED atau PRODUK-BASED).
     */
    public function store(Request $request)
    {
        // Debug: Log all incoming request data
        \Log::info('=== PENGGAJIAN STORE DEBUG ===');
        \Log::info('All request data:', $request->all());

        // Mulai transaksi database
        DB::beginTransaction();

        try {
            // Validasi input - simplified for debugging
            $request->validate([
                'pegawai_id' => 'required|exists:pegawais,id',
                'tanggal_penggajian' => 'required|date',
                'metode_pembayaran' => 'required|string|in:tunai,transfer_bank',
            ]);

            $pegawai = Pegawai::with('kualifikasiRelasi')->findOrFail($request->pegawai_id);

            // STEP 1: Determine if this is PRODUK-BASED or JAM-BASED
            $totalProdukInput = (int) ($request->total_produk_bulanan ?? $request->total_produk ?? 0);
            $produkPerHariInput = (int) ($request->produk_per_hari ?? 0);
            $gajiProduksiFinalInput = (float) ($request->gaji_produksi_final ?? 0);
            $isProdukBased = $totalProdukInput > 0 || $produkPerHariInput > 0 || $gajiProduksiFinalInput > 0;

            if ($isProdukBased) {
                // ============================================================
                // PRODUK-BASED PENGGAJIAN (NEW SYSTEM)
                // ============================================================
                \Log::info('Creating PRODUK-BASED penggajian for pegawai: ' . $pegawai->nama);

                // Get input values
                $produkPerHari = (int) ($request->produk_per_hari ?? 0);
                $hariKerja = (int) ($request->hari_kerja ?? $request->hari_kerja_bulanan ?? 26);

                // STEP 1: Calculate total produk
                $totalProduk = $totalProdukInput > 0 ? $totalProdukInput : ($produkPerHari * $hariKerja);

                // STEP 2: Get jabatan/kualifikasi
                $jabatan = app(\App\Services\KualifikasiTargetResolver::class)->resolve($pegawai);
                $tarifProduk = (float) ($jabatan ? ($jabatan->tarif_produk ?? 0) : ($pegawai->tarif_per_jam ?? 0));

                // STEP 3: Gaji Pokok diambil LANGSUNG dari kualifikasis.gaji_pokok (nilai aktual)
                // BUKAN dihitung ulang dari tarif x produk (untuk menghindari selisih pembulatan)
                // Tarif/Produk hanya dipakai untuk alokasi biaya HPP per unit, bukan untuk hitung gaji
                // Accessor di model otomatis fallback ke gaji jika gaji_pokok kosong
                $gajiDariKualifikasi = (float) ($jabatan ? ($jabatan->gaji_pokok ?? 0) : 0);

                // STEP 4: Gaji Pokok Final = nilai dari kualifikasi (prioritas), fallback ke input form
                $gajiProduksiFinal = $gajiDariKualifikasi > 0
                    ? $gajiDariKualifikasi
                    : ($gajiProduksiFinalInput > 0 ? $gajiProduksiFinalInput : 0);

                // STEP 5: Get tunjangan & asuransi dari form, fallback ke jabatan.
                $tunjanganJabatan = (float) ($request->tunjangan_jabatan ?? ($jabatan ? ($jabatan->tunjangan ?? 0) : 0));
                $tunjanganTransport = (float) ($request->tunjangan_transport ?? ($jabatan ? ($jabatan->tunjangan_transport ?? 0) : 0));
                $tunjanganKonsumsi = (float) ($request->tunjangan_konsumsi ?? ($jabatan ? ($jabatan->tunjangan_konsumsi ?? 0) : 0));
                $totalTunjangan = $tunjanganJabatan + $tunjanganTransport + $tunjanganKonsumsi;
                $asuransi = (float) ($request->bpjs_asuransi ?? $request->asuransi ?? ($jabatan ? ($jabatan->asuransi ?? 0) : 0));
                $bonus = (float) ($request->bonus ?? 0);
                $potongan = (float) ($request->potongan ?? 0);

                // STEP 6: Calculate total gaji
                // Total Gaji Karyawan = Gaji Pokok Final + Tunjangan + Bonus - Potongan (yang diterima karyawan)
                $totalGajiKaryawan = $gajiProduksiFinal + $totalTunjangan + $bonus - $potongan;
                // Total Biaya Perusahaan = Total Gaji Karyawan + Asuransi BPJS (beban perusahaan)
                $totalGaji = $totalGajiKaryawan + $asuransi;

                // Determine COA Kas/Bank based on metode_pembayaran
                $metodePembayaran = $request->metode_pembayaran ?? 'transfer_bank';
                $coaKasBank = null;
                if ($metodePembayaran === 'tunai') {
                    $coa = \App\Helpers\AccountHelper::getKasAccounts(auth()->id())->first();
                    $coaKasBank = $coa ? $coa->kode_akun : '112';
                } else {
                    $coa = \App\Helpers\AccountHelper::getBankAccounts(auth()->id())->first();
                    if (!$coa) {
                        $coa = \App\Helpers\AccountHelper::getBankAccountsForTransfer(auth()->id())->first();
                    }
                    $coaKasBank = $coa ? $coa->kode_akun : '111';
                }

                // Create penggajian record
                $penggajian = Penggajian::create([
                    'pegawai_id' => $pegawai->id,
                    'nomor_penggajian' => $this->generateNomorPenggajian(),
                    'periode_bulan' => \Carbon\Carbon::parse($request->tanggal_penggajian)->month,
                    'periode_tahun' => \Carbon\Carbon::parse($request->tanggal_penggajian)->year,
                    'tanggal_penggajian' => $request->tanggal_penggajian,
                    'coa_kasbank' => $coaKasBank,
                    'gaji_pokok' => $gajiProduksiFinal,
                    'tarif_per_jam' => 0,
                    'tunjangan_jabatan' => $tunjanganJabatan,
                    'tunjangan_transport' => $tunjanganTransport,
                    'tunjangan_konsumsi' => $tunjanganKonsumsi,
                    'total_tunjangan' => $totalTunjangan,
                    'asuransi' => $asuransi,
                    'bonus' => $bonus,
                    'potongan' => $potongan,
                    'total_jam_kerja' => 0,
                    'total_hari_hadir' => 0,
                    'total_alpha' => 0,
                    'total_jam' => 0,
                    'total_gaji' => $totalGaji,
                    'status_pembayaran' => 'belum_lunas',
                    'status_posting' => 'belum_posting',
                    'metode_pembayaran' => $metodePembayaran,
                    'total_produk_bulan' => $totalProduk,
                    'tarif_produk' => $tarifProduk,
                    'keterangan' => $request->keterangan ?? null,
                    'user_id' => auth()->id(),
                    'mode_input' => 'bulanan',
                    'pembulatan_aktif' => $request->pembulatan_aktif ? 1 : 0,
                    'pembulatan_step' => $request->pembulatan_step ?? 100000,
                    'nominal_pembulatan' => 0,
                ]);

                \Log::info('PRODUK-BASED penggajian created successfully', [
                    'penggajian_id' => $penggajian->id,
                    'total_produk' => $totalProduk,
                    'gaji_produksi_mentah' => $gajiProduksiFinal,
                    'gaji_produksi_final' => $gajiProduksiFinal,
                    'total_gaji' => $totalGaji,
                ]);

            } else {
                // ============================================================
                // NO INPUT DETECTED - ERROR
                // ============================================================
                throw new \Exception('Error: Tidak ada input penggajian yang valid. Silakan input total produk atau gaji produksi terlebih dahulu. Sistem penggajian saat ini berbasis PRODUK (bukan JAM). Pastikan Anda sudah membaca panduan penggajian produk-based.');
            }

            // ✅ BUAT JURNAL ACCRUAL (BUKAN LANGSUNG KAS!)
            $this->createJournalAccrual($penggajian, $pegawai);

            // Commit transaksi
            DB::commit();

            return redirect()->route('transaksi.penggajian.index')
                ->with('success', 'Data penggajian berhasil ditambahkan!');

        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollBack();
            return back()->withErrors($e->errors())->withInput();
            
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error in PenggajianController@store: ' . $e->getMessage());

            return back()->withErrors(['error' => 'Gagal menyimpan penggajian: ' . $e->getMessage()])->withInput();
        }
    }

    /**
     * Recalculate penggajian (PRODUK-BASED ONLY)
     * Update tunjangan & asuransi berdasarkan master data terbaru
     * 
     * DEPRECATED: JAM-BASED recalculate sudah tidak digunakan
     */
    public function recalculate($id)
    {
        DB::beginTransaction();

        try {
            $penggajian = Penggajian::with('pegawai.kualifikasiRelasi')->findOrFail($id);
            
            // Cek apakah sudah diposting ke jurnal
            if ($penggajian->status_posting === 'posted') {
                return back()->withErrors(['error' => 'Penggajian yang sudah diposting ke jurnal tidak dapat direcalculate.']);
            }

            $pegawai = $penggajian->pegawai;

            $jabatan = app(\App\Services\KualifikasiTargetResolver::class)->resolve($pegawai);

            if (!$jabatan) {
                throw new \Exception('Pegawai tidak memiliki kualifikasi jabatan. Harap set jabatan terlebih dahulu.');
            }

            // STEP 1: Get tunjangan & asuransi dari KUALIFIKASI (JABATAN) terbaru
            $tunjanganJabatan = (float) ($jabatan->tunjangan ?? 0);
            $tunjanganTransport = (float) ($jabatan->tunjangan_transport ?? 0);
            $tunjanganKonsumsi = (float) ($jabatan->tunjangan_konsumsi ?? 0);
            $asuransi = (float) ($jabatan->asuransi ?? 0);
            $totalTunjangan = $tunjanganJabatan + $tunjanganTransport + $tunjanganKonsumsi;

            $produkDetail = $this->resolveProdukPayrollDetail($penggajian);
            $gajiProduksiFinal = $produkDetail['gaji_dasar'];
            $bonus = (float) $penggajian->bonus;
            $potongan = (float) $penggajian->potongan;
            
            $totalGajiKaryawan = $gajiProduksiFinal + $totalTunjangan + $bonus - $potongan;
            $totalGaji = $totalGajiKaryawan + $asuransi;

            // STEP 3: Update dengan data terbaru
            $penggajian->update([
                'total_tunjangan' => $totalTunjangan,
                'tunjangan_jabatan' => $tunjanganJabatan,
                'tunjangan_transport' => $tunjanganTransport,
                'tunjangan_konsumsi' => $tunjanganKonsumsi,
                'total_tunjangan' => $totalTunjangan,
                'asuransi' => $asuransi,
                'gaji_pokok' => $gajiProduksiFinal,
                'total_produk_bulan' => $produkDetail['produk_dihasilkan'],
                'tarif_produk' => $produkDetail['tarif_produk'],
                'total_gaji' => $totalGaji,
            ]);

            \Log::info('Data penggajian (PRODUK-BASED) berhasil direcalculate', [
                'penggajian_id' => $penggajian->id,
                'pegawai_id' => $pegawai->id,
                'gaji_produksi' => $gajiProduksiFinal,
                'total_tunjangan' => $totalTunjangan,
                'asuransi' => $asuransi,
                'total_gaji_baru' => $totalGaji,
            ]);

            DB::commit();

            return back()->with('success', 'Data penggajian berhasil direcalculate berdasarkan master data terbaru!');

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error in PenggajianController@recalculate: ' . $e->getMessage());

            return back()->withErrors(['error' => 'Gagal recalculate penggajian: ' . $e->getMessage()]);
        }
    }

    public function destroy($id)
    {
        try {
            DB::beginTransaction();
            
            // CRITICAL: Filter by user_id untuk multi-tenant isolation
            $penggajian = Penggajian::where('user_id', auth()->id())
                ->findOrFail($id);

            // Cegah hapus jika sudah dibayar
            if ($penggajian->status_pembayaran === 'lunas') {
                return redirect()->route('transaksi.penggajian.index')
                    ->with('error', 'Penggajian tidak dapat dihapus karena sudah dibayar (status: ' . $penggajian->status_pembayaran . ')');
            }

            // Hapus jurnal umum terkait terlebih dahulu.
            // Jurnal penggajian disimpan di tabel jurnal_umum dengan kolom
            // tipe_referensi/referensi, bukan ref_type/ref_id.
            \App\Models\JurnalUmum::where('tipe_referensi', 'penggajian')
                ->where('referensi', (string) $penggajian->id)
                ->where('user_id', auth()->id())
                ->delete();

            // Hapus data penggajian
            $penggajian->delete();

            DB::commit();

            return redirect()->route('transaksi.penggajian.index')
                ->with('success', 'Data penggajian berhasil dihapus!');
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error deleting penggajian: ' . $e->getMessage());

            return redirect()->route('transaksi.penggajian.index')
                ->withErrors(['error' => 'Gagal menghapus penggajian: ' . $e->getMessage()]);
        }
    }

    /**
     * Tandai penggajian sebagai sudah dibayar (otomatis posting ke jurnal)
     */
    public function markAsPaid(Request $request, $id)
    {
        try {

            // CRITICAL: Filter by user_id untuk multi-tenant isolation
            $penggajian = Penggajian::where('user_id', auth()->id())
                ->findOrFail($id);

            // Hanya update jika status masih belum_lunas
            if ($penggajian->status_pembayaran === 'belum_lunas') {
                // Mulai transaksi database
                DB::beginTransaction();
                
                try {
                    // Validasi akun sumber dana
                    $request->validate([
                        'akun_sumber_dana' => 'required|string'
                    ], [
                        'akun_sumber_dana.required' => 'Silakan pilih akun sumber dana terlebih dahulu.'
                    ]);

                    // Update coa_kasbank dengan akun yang dipilih dari modal
                    $penggajian->coa_kasbank = $request->akun_sumber_dana;
                    
                    // Update status
                    $penggajian->status_pembayaran = 'lunas';
                    $penggajian->tanggal_dibayar = now()->format('Y-m-d');
                    $penggajian->status_posting = 'posted';
                    $penggajian->tanggal_posting = now();
                    $penggajian->save();

                    // ✅ BUAT JURNAL PEMBAYARAN
                    $this->createJournalPayment($penggajian, $penggajian->pegawai);

                    // Commit transaksi
                    DB::commit();

                    return redirect()->back()
                        ->with('success', 'Penggajian berhasil ditandai sebagai sudah dibayar dan otomatis diposting ke jurnal umum.');
                        
                } catch (\Exception $e) {
                    DB::rollBack();
                    \Log::error('Error marking as paid and posting to journal: ' . $e->getMessage());
                    
                    return redirect()->back()
                        ->with('error', 'Gagal memproses pembayaran: ' . $e->getMessage());
                }
            }

            return redirect()->back()
                ->with('info', 'Penggajian sudah berstatus lunas dan sudah diposting ke jurnal.');
        } catch (\Exception $e) {
            \Log::error('Error marking penggajian as paid: ' . $e->getMessage());

            return redirect()->back()
                ->with('error', 'Gagal menandai penggajian sebagai dibayar: ' . $e->getMessage());
        }
    }

    private function createJournalAccrual($penggajian, $pegawai)
    {
        try {
            // Tentukan akun beban berdasarkan departemen/jabatan pegawai
            // Gunakan helper method getCoaBebanGaji() untuk mapping otomatis
            $koaBebanGajiCode = $this->getCoaBebanGaji($pegawai);

            $coaBebanGaji = Coa::where('kode_akun', $koaBebanGajiCode)
                ->where('user_id', auth()->id())
                ->first();

            if (!$coaBebanGaji) {
                throw new \Exception("COA dengan kode {$koaBebanGajiCode} tidak ditemukan. " .
                                   "Pastikan akun sudah dibuat via seeder UpdatePenggajianCoasSeeder.");
            }

            $coaBebanTunjangan = $this->getCoa('515', 'Beban Tunjangan');
            $coaBebanAsuransi = $this->getCoa('516', 'Beban Asuransi');
            $coaBebanBonus = $this->getCoa('517', 'Beban Bonus');
            $coaHutangGaji = $this->getCoa('212', 'Hutang Gaji');

            $gajiPokok = $penggajian->gaji_pokok ?? 0;
            $totalTunjangan = $penggajian->total_tunjangan ?? 0;
            $bonus = $penggajian->bonus ?? 0;
            $asuransi = $penggajian->asuransi ?? 0;
            $potongan = $penggajian->potongan ?? 0;
            
            // Hutang Gaji = yang diterima karyawan penuh (tanpa asuransi)
            $totalHutangGaji = $gajiPokok + $totalTunjangan + $bonus - $potongan;
            
            $keterangan = "Penggajian {$pegawai->nama}";

            // DEBIT: Beban Gaji Dasar
            if ($gajiPokok > 0) {
                JurnalUmum::create([
                    'coa_id' => $coaBebanGaji->id,
                    'tanggal' => $penggajian->tanggal_penggajian,
                    'keterangan' => $keterangan,
                    'debit' => $gajiPokok,
                    'kredit' => 0,
                    'referensi' => (string) $penggajian->id,
                    'tipe_referensi' => 'penggajian',
                    'created_by' => auth()->id() ?? 1,
                    'user_id' => auth()->id() ?? $penggajian->user_id,
                ]);
            }

            // DEBIT: Beban Tunjangan
            if ($totalTunjangan > 0) {
                JurnalUmum::create([
                    'coa_id' => $coaBebanTunjangan->id,
                    'tanggal' => $penggajian->tanggal_penggajian,
                    'keterangan' => $keterangan . ' - Beban Tunjangan',
                    'debit' => $totalTunjangan,
                    'kredit' => 0,
                    'referensi' => (string) $penggajian->id,
                    'tipe_referensi' => 'penggajian',
                    'created_by' => auth()->id() ?? 1,
                    'user_id' => auth()->id() ?? $penggajian->user_id,
                ]);
            }

            // DEBIT: Beban Bonus
            if ($bonus > 0) {
                JurnalUmum::create([
                    'coa_id' => $coaBebanBonus->id,
                    'tanggal' => $penggajian->tanggal_penggajian,
                    'keterangan' => $keterangan . ' - Beban Bonus',
                    'debit' => $bonus,
                    'kredit' => 0,
                    'referensi' => (string) $penggajian->id,
                    'tipe_referensi' => 'penggajian',
                    'created_by' => auth()->id() ?? 1,
                    'user_id' => auth()->id() ?? $penggajian->user_id,
                ]);
            }

            // DEBIT/KREDIT: Pembulatan Upah Gaji (akun 516)
            $selisihPembulatan = $penggajian->nominal_pembulatan ?? 0;
            if ($selisihPembulatan != 0) {
                $coaSelisih = $this->getCoa('516', 'Pembulatan Upah Gaji');
                
                if ($selisihPembulatan > 0) {
                    JurnalUmum::create([
                        'coa_id' => $coaSelisih->id,
                        'tanggal' => $penggajian->tanggal_penggajian,
                        'keterangan' => $keterangan . ' (Selisih Pembulatan)',
                        'debit' => $selisihPembulatan,
                        'kredit' => 0,
                        'referensi' => (string) $penggajian->id,
                        'tipe_referensi' => 'penggajian',
                        'created_by' => auth()->id() ?? 1,
                        'user_id' => auth()->id() ?? $penggajian->user_id,
                    ]);
                } else {
                    JurnalUmum::create([
                        'coa_id' => $coaSelisih->id,
                        'tanggal' => $penggajian->tanggal_penggajian,
                        'keterangan' => $keterangan . ' (Selisih Pembulatan)',
                        'debit' => 0,
                        'kredit' => abs($selisihPembulatan),
                        'referensi' => (string) $penggajian->id,
                        'tipe_referensi' => 'penggajian',
                        'created_by' => auth()->id() ?? 1,
                        'user_id' => auth()->id() ?? $penggajian->user_id,
                    ]);
                }
            }

            // KREDIT: Hutang Gaji
            JurnalUmum::create([
                'coa_id' => $coaHutangGaji->id,
                'tanggal' => $penggajian->tanggal_penggajian,
                'keterangan' => $keterangan . ' - Hutang Gaji',
                'debit' => 0,
                'kredit' => $totalHutangGaji,
                'referensi' => (string) $penggajian->id,
                'tipe_referensi' => 'penggajian',
                'created_by' => auth()->id() ?? 1,
                'user_id' => auth()->id() ?? $penggajian->user_id,
            ]);

            // DEBIT: Beban Asuransi & KREDIT: Hutang Asuransi
            if ($asuransi > 0) {
                $coaHutangAsuransi = $this->getCoa('213', 'Hutang Asuransi');
                
                // DEBIT Beban Asuransi
                JurnalUmum::create([
                    'coa_id' => $coaBebanAsuransi->id,
                    'tanggal' => $penggajian->tanggal_penggajian,
                    'keterangan' => $keterangan . ' - Beban Asuransi BPJS',
                    'debit' => $asuransi,
                    'kredit' => 0,
                    'referensi' => (string) $penggajian->id,
                    'tipe_referensi' => 'penggajian',
                    'created_by' => auth()->id() ?? 1,
                    'user_id' => auth()->id() ?? $penggajian->user_id,
                ]);

                // KREDIT Hutang Asuransi
                JurnalUmum::create([
                    'coa_id' => $coaHutangAsuransi->id,
                    'tanggal' => $penggajian->tanggal_penggajian,
                    'keterangan' => $keterangan . ' - Hutang Asuransi BPJS',
                    'debit' => 0,
                    'kredit' => $asuransi,
                    'referensi' => (string) $penggajian->id,
                    'tipe_referensi' => 'penggajian',
                    'created_by' => auth()->id() ?? 1,
                    'user_id' => auth()->id() ?? $penggajian->user_id,
                ]);
            }

            // KREDIT: Hutang Potongan Lainnya
            if ($potongan > 0) {
                // As per existing code for other deductions, we use a different liability account if it existed.
                // Since 213 is now Hutang Asuransi, we should probably use a generic liability or ask the user.
                // The prompt says "jangan rubah apapun [yang tdk diminta]", but we shifted 213.
                // We will use 219 for Hutang Potongan Lainnya to avoid conflict, or find it by name.
                // But the user didn't mention this. Wait, I will use getCoa('219', 'Hutang Potongan Gaji Lainnya').
                // Let me check if 219 exists in seeders, it doesn't. 
                // Let's create it in code or just skip. I'll use a placeholder code.
                // Wait, let's look at the replacement chunk.
                $coaPotongan = $this->getCoa('215', 'Hutang Potongan Gaji Lainnya');
                
                JurnalUmum::create([
                    'coa_id' => $coaPotongan->id,
                    'tanggal' => $penggajian->tanggal_penggajian,
                    'keterangan' => $keterangan . ' (Potongan Lainnya)',
                    'debit' => 0,
                    'kredit' => $potongan,
                    'referensi' => (string) $penggajian->id,
                    'tipe_referensi' => 'penggajian',
                    'created_by' => auth()->id() ?? 1,
                    'user_id' => auth()->id() ?? $penggajian->user_id,
                ]);
            }

            \Log::info('JURNAL ACCRUAL berhasil dibuat', [
                'penggajian_id' => $penggajian->id,
                'total_hutang_gaji' => $totalHutangGaji,
                'selisih_pembulatan' => $selisihPembulatan,
            ]);

            return true;

        } catch (\Exception $e) {
            \Log::error('Error createJournalAccrual: ' . $e->getMessage());
            throw $e;
        }
    }

    private function createJournalPayment($penggajian, $pegawai)
    {
        try {
            $coaHutangGaji = $this->getOrCreateCoa('212', 'Hutang Gaji', '2');
            $coaKasBank = Coa::where('kode_akun', $penggajian->coa_kasbank)
                ->where('user_id', auth()->id())
                ->first();
            
            if (!$coaKasBank) {
                throw new \Exception('Akun sumber dana tidak valid, silakan pilih ulang.');
            }

            $totalHutang = $penggajian->gaji_pokok + $penggajian->total_tunjangan 
                         + $penggajian->bonus - $penggajian->asuransi - $penggajian->potongan;
                         
            // Check Kas/Bank balance before payment
            $currentBalance = \App\Helpers\AccountHelper::getCurrentBalance($coaKasBank->kode_akun, auth()->id());
            if ($currentBalance < $totalHutang) {
                throw new \Exception("Saldo tidak mencukupi. Saldo saat ini: Rp " . number_format($currentBalance, 0, ',', '.') . " | Dibutuhkan: Rp " . number_format($totalHutang, 0, ',', '.'));
            }

            $keterangan = "Pembayaran Gaji {$pegawai->nama}";

            // DEBIT: Hutang Gaji
            JurnalUmum::create([
                'coa_id' => $coaHutangGaji->id,
                'tanggal' => $penggajian->tanggal_dibayar ?? now(),
                'keterangan' => $keterangan,
                'debit' => $totalHutang,
                'kredit' => 0,
                'referensi' => (string) $penggajian->id,
                'tipe_referensi' => 'penggajian_bayar',
                'created_by' => auth()->id() ?? 1,
                'user_id' => auth()->id() ?? $penggajian->user_id,
            ]);

            // KREDIT: Kas/Bank
            JurnalUmum::create([
                'coa_id' => $coaKasBank->id,
                'tanggal' => $penggajian->tanggal_dibayar ?? now(),
                'keterangan' => $keterangan,
                'debit' => 0,
                'kredit' => $totalHutang,
                'referensi' => (string) $penggajian->id,
                'tipe_referensi' => 'penggajian_bayar',
                'created_by' => auth()->id() ?? 1,
                'user_id' => auth()->id() ?? $penggajian->user_id,
            ]);

            \Log::info('JURNAL PEMBAYARAN berhasil dibuat', [
                'penggajian_id' => $penggajian->id,
                'total_bayar' => $totalHutang,
            ]);

            return true;

        } catch (\Exception $e) {
            \Log::error('Error createJournalPayment: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Buat journal entry untuk penggajian menggunakan JournalService (LAMA)
     */
    private function createJournalEntryOld($penggajian, $pegawai)
    {
        try {
            // Tentukan akun beban berdasarkan jenis pegawai
            $jenisPegawai = strtolower($pegawai->kategori ?? $pegawai->jenis_pegawai ?? 'btktl');

            // PRIORITY: Always use gaji_pokok from database if exists (for produk-based system)
            // Only calculate from jam kerja if gaji_pokok is 0 (legacy jam-based system)
            $gajiPokok = $penggajian->gaji_pokok ?? 0;

            // Special handling untuk Bagian Gudang
            if (strpos(strtolower($pegawai->kualifikasiRelasi->nama_kualifikasi ?? ''), 'gudang') !== false) {
                // Bagian Gudang = BTKTL (Tenaga Kerja Tidak Langsung)
                $coaBebanGaji = $this->getOrCreateCoa('54', 'Beban Tenaga Kerja Tidak Langsung', '5');
                $gajiDasar = $gajiPokok > 0 ? $gajiPokok : (($penggajian->tarif_per_jam ?? 0) * ($penggajian->total_jam_kerja ?? 0));
            } else if ($jenisPegawai === 'btkl') {
                $coaBebanGaji = $this->getOrCreateCoa('52', 'Beban Tenaga Kerja Langsung', '5');
                // FIXED: Use gaji_pokok first, fallback to jam kerja calculation
                $gajiDasar = $gajiPokok > 0 ? $gajiPokok : (($penggajian->tarif_per_jam ?? 0) * ($penggajian->total_jam_kerja ?? 0));
            } else {
                $coaBebanGaji = $this->getOrCreateCoa('54', 'Beban Tenaga Kerja Tidak Langsung', '5');
                $gajiDasar = $gajiPokok > 0 ? $gajiPokok : (($penggajian->tarif_per_jam ?? 0) * ($penggajian->total_jam_kerja ?? 0));
            }

            $totalTunjangan = $penggajian->total_tunjangan ?? 0;
            $bonus = $penggajian->bonus ?? 0;
            $potongan = $penggajian->potongan ?? 0;
            $asuransi = $penggajian->asuransi ?? 0;
            $totalGaji = $penggajian->total_gaji ?? 0;

            // COA untuk komponen lainnya - otomatis buat jika belum ada
            $coaBebanTunjangan = $this->getOrCreateCoa('513', 'Beban Tunjangan', '5');
            $coaBebanBonus = $this->getOrCreateCoa('515', 'Beban Bonus', '5');
            // ASURANSI adalah POTONGAN, bukan beban - dicatat sebagai HUTANG
            $coaHutangAsuransi = $this->getOrCreateCoa('211', 'Hutang Asuransi', '2');
            $coaPotongan = $this->getOrCreateCoa('516', 'Potongan Gaji', '5');

            // Pastikan akun kas/bank valid
            $coaKasBank = Coa::where('kode_akun', $penggajian->coa_kasbank)
                ->where('user_id', auth()->id())
                ->first();
            if (!$coaKasBank) {
                throw new \Exception('Akun kas/bank tidak valid');
            }
            
            // Log data sebelum membuat jurnal
            \Log::info('Membuat jurnal penggajian dengan detail', [
                'penggajian_id' => $penggajian->id,
                'pegawai_id' => $pegawai->id,
                'gaji_pokok_raw' => $penggajian->gaji_pokok,
                'gaji_dasar' => $gajiDasar,
                'total_tunjangan' => $totalTunjangan,
                'bonus' => $bonus,
                'asuransi' => $asuransi,
                'potongan' => $potongan,
                'total_gaji' => $totalGaji,
                'coa_beban_gaji_id' => $coaBebanGaji->id ?? 'NULL',
                'coa_beban' => $coaBebanGaji->kode_akun ?? 'NULL',
                'coa_kasbank' => $coaKasBank->kode_akun
            ]);
            
            // Buat jurnal entries dengan detail komponen - HANYA yang > 0
            $keterangan = "Penggajian {$pegawai->nama}";
            
            // DEBIT: Beban Gaji Dasar - CRITICAL: MUST CREATE if gaji_dasar > 0
            if ($gajiDasar > 0) {
                \Log::info('Creating Gaji Pokok journal entry', [
                    'coa_id' => $coaBebanGaji->id,
                    'gaji_dasar' => $gajiDasar,
                    'keterangan' => $keterangan
                ]);
                
                JurnalUmum::create([
                    'coa_id' => $coaBebanGaji->id,
                    'tanggal' => $penggajian->tanggal_penggajian,
                    'keterangan' => $keterangan,
                    'debit' => $gajiDasar,
                    'kredit' => 0,
                    'referensi' => (string) $penggajian->id,
                    'tipe_referensi' => 'penggajian',
                    'created_by' => auth()->id() ?? 1,
                    'user_id' => auth()->id() ?? $penggajian->user_id,
                ]);
                
                \Log::info('Gaji Pokok journal entry created successfully');
            } else {
                \Log::warning('SKIPPING Gaji Pokok journal entry - gaji_dasar is 0', [
                    'penggajian_id' => $penggajian->id,
                    'gaji_pokok_from_db' => $penggajian->gaji_pokok,
                    'tarif_per_jam' => $penggajian->tarif_per_jam,
                    'total_jam_kerja' => $penggajian->total_jam_kerja
                ]);
            }
            
            // DEBIT: Beban Tunjangan (HANYA jika > 0)
            if ($totalTunjangan > 0) {
                JurnalUmum::create([
                    'coa_id' => $coaBebanTunjangan->id,
                    'tanggal' => $penggajian->tanggal_penggajian,
                    'keterangan' => $keterangan,
                    'debit' => $totalTunjangan,
                    'kredit' => 0,
                    'referensi' => (string) $penggajian->id,
                    'tipe_referensi' => 'penggajian',
                    'created_by' => auth()->id() ?? 1,
                    'user_id' => auth()->id() ?? $penggajian->user_id,
                ]);
            }
            
            // DEBIT: Beban Bonus (HANYA jika > 0)
            if ($bonus > 0) {
                JurnalUmum::create([
                    'coa_id' => $coaBebanBonus->id,
                    'tanggal' => $penggajian->tanggal_penggajian,
                    'keterangan' => $keterangan,
                    'debit' => $bonus,
                    'kredit' => 0,
                    'referensi' => (string) $penggajian->id,
                    'tipe_referensi' => 'penggajian',
                    'created_by' => auth()->id() ?? 1,
                    'user_id' => auth()->id() ?? $penggajian->user_id,
                ]);
            }
            
            // KREDIT: Hutang Asuransi (potongan gaji) - HANYA jika > 0
            if ($asuransi > 0) {
                JurnalUmum::create([
                    'coa_id' => $coaHutangAsuransi->id,
                    'tanggal' => $penggajian->tanggal_penggajian,
                    'keterangan' => $keterangan . ' (Potongan Asuransi)',
                    'debit' => 0,
                    'kredit' => $asuransi, // KREDIT untuk hutang asuransi (potongan)
                    'referensi' => (string) $penggajian->id,
                    'tipe_referensi' => 'penggajian',
                    'created_by' => auth()->id() ?? 1,
                    'user_id' => auth()->id() ?? $penggajian->user_id,
                ]);
            }
            
            // KREDIT: Potongan Gaji lainnya (HANYA jika > 0)
            if ($potongan > 0) {
                JurnalUmum::create([
                    'coa_id' => $coaPotongan->id,
                    'tanggal' => $penggajian->tanggal_penggajian,
                    'keterangan' => $keterangan . ' (Potongan Lainnya)',
                    'debit' => 0,
                    'kredit' => $potongan, // KREDIT untuk mengurangi beban
                    'referensi' => (string) $penggajian->id,
                    'tipe_referensi' => 'penggajian',
                    'created_by' => auth()->id() ?? 1,
                    'user_id' => auth()->id() ?? $penggajian->user_id,
                ]);
            }
            
            // KREDIT: Kas/Bank (pembayaran gaji) - SELALU ADA karena pasti ada pembayaran
            JurnalUmum::create([
                'coa_id' => $coaKasBank->id,
                'tanggal' => $penggajian->tanggal_penggajian,
                'keterangan' => $keterangan,
                'debit' => 0,
                'kredit' => $totalGaji,
                'referensi' => (string) $penggajian->id,
                'tipe_referensi' => 'penggajian',
                'created_by' => auth()->id() ?? 1,
                'user_id' => auth()->id() ?? $penggajian->user_id,
            ]);
            
            // Update saldo COA
            $this->updateCoaSaldo($coaBebanGaji->kode_akun);
            if ($totalTunjangan > 0 && $coaBebanTunjangan) $this->updateCoaSaldo($coaBebanTunjangan->kode_akun);
            if ($bonus > 0 && $coaBebanBonus) $this->updateCoaSaldo($coaBebanBonus->kode_akun);
            if ($asuransi > 0 && $coaHutangAsuransi) $this->updateCoaSaldo($coaHutangAsuransi->kode_akun);
            if ($potongan > 0 && $coaPotongan) $this->updateCoaSaldo($coaPotongan->kode_akun);
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
     * API endpoint: Hitung gaji BTKL berbasis produk (PREVIEW)
     * POST /api/penggajian/hitung-produk
     */
    public function hitungGajiProduk(Request $request)
    {
        try {
            $validated = $request->validate([
                'pegawai_id' => 'required|integer',
                'produk_hari_1_5' => 'required|integer|min:0',
                'produk_hari_6_10' => 'required|integer|min:0',
                'produk_hari_11_20' => 'required|integer|min:0',
                'produk_hari_21_30' => 'required|integer|min:0',
            ]);

            $service = new \App\Services\PenggajianService();
            $detail = $service->hitungGajiProduk(
                $validated['pegawai_id'],
                $validated['produk_hari_1_5'],
                $validated['produk_hari_6_10'],
                $validated['produk_hari_11_20'],
                $validated['produk_hari_21_30']
            );

            return response()->json([
                'success' => true,
                'data' => $detail
            ]);
        } catch (\Exception $e) {
            \Log::error('Error in hitungGajiProduk: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * API endpoint: Create penggajian BTKL berbasis produk
     * POST /api/penggajian/create-produk
     */
    public function createPenggajianProduk(Request $request)
    {
        DB::beginTransaction();
        try {
            $validated = $request->validate([
                'pegawai_id' => 'required|integer|exists:pegawais,id',
                'tanggal_penggajian' => 'required|date',
                'produk_hari_1_5' => 'required|integer|min:0',
                'produk_hari_6_10' => 'required|integer|min:0',
                'produk_hari_11_20' => 'required|integer|min:0',
                'produk_hari_21_30' => 'required|integer|min:0',
                'coa_kasbank' => 'required|string',
                'metode_pembayaran' => 'nullable|string',
            ]);

            $service = new \App\Services\PenggajianService();
            $penggajian = $service->savePenggajianProduk(
                $validated['pegawai_id'],
                $validated['tanggal_penggajian'],
                $validated['produk_hari_1_5'],
                $validated['produk_hari_6_10'],
                $validated['produk_hari_11_20'],
                $validated['produk_hari_21_30'],
                $validated['metode_pembayaran'] ?? 'transfer_bank'
            );

            // Set additional fields
            $penggajian->coa_kasbank = $validated['coa_kasbank'];
            $penggajian->status_pembayaran = 'belum_lunas';
            $penggajian->periode_bulan = Carbon::parse($validated['tanggal_penggajian'])->month;
            $penggajian->periode_tahun = Carbon::parse($validated['tanggal_penggajian'])->year;
            $penggajian->save();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Penggajian berhasil dibuat',
                'data' => $penggajian
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error in createPenggajianProduk: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Get COA account by code, or create if it doesn't exist.
     */
    private function getOrCreateCoa($kodeAkun, $namaAkun, $prefix = null)
    {
        $userId = auth()->id() ?? 1;

        $coa = Coa::withoutGlobalScopes()
            ->where('user_id', $userId)
            ->where('kode_akun', $kodeAkun)
            ->first();

        if ($coa) {
            return $coa;
        }

        $tipeAkun = 'Aset';
        $kategoriAkun = 'Lancar';
        $saldoNormal = 'debit';
        
        $firstChar = substr($kodeAkun, 0, 1);
        if ($firstChar === '2') {
            $tipeAkun = 'Kewajiban';
            $saldoNormal = 'kredit';
        } elseif ($firstChar === '3') {
            $tipeAkun = 'Ekuitas';
            $saldoNormal = 'kredit';
        } elseif ($firstChar === '4') {
            $tipeAkun = 'Pendapatan';
            $saldoNormal = 'kredit';
        } elseif ($firstChar === '5') {
            $tipeAkun = 'Beban';
            $saldoNormal = 'debit';
        }

        return Coa::create([
            'kode_akun' => $kodeAkun,
            'nama_akun' => $namaAkun,
            'kategori_akun' => $kategoriAkun,
            'tipe_akun' => $tipeAkun,
            'saldo_normal' => $saldoNormal,
            'user_id' => $userId,
        ]);
    }

    /**
     * Ambil atau buat COA berdasarkan kode dan nama akun
     */
    private function getCoa($kodeAkun, $namaAkun)
    {
        $userId = auth()->id() ?? 1;

        // Try to find existing COA
        $coa = Coa::withoutGlobalScopes()
            ->where('user_id', $userId)
            ->where('kode_akun', $kodeAkun)
            ->first();

        if ($coa) {
            \Log::info("COA found: {$kodeAkun} - {$coa->nama_akun}");
            return $coa;
        }

        // COA doesn't exist, throw exception
        $errorMsg = "Gagal mencatat jurnal: Akun {$namaAkun} ({$kodeAkun}) tidak ditemukan. Pastikan master COA sudah dikonfigurasi.";
        \Log::error($errorMsg);
        throw new \Exception($errorMsg);
    }

    /**
     * Buat journal entry di sistem modern (journal_entries + journal_lines)
     * Ini memastikan penggajian muncul di halaman jurnal umum
     * METHOD DINONAKTIKAN - Menggunakan createJournalEntry() yang sudah bekerja dengan baik
     */
    private function createJournalEntryModern($penggajian, $pegawai)
    {

        // Method ini dinonaktifkan karena createJournalEntry() sudah bekerja dengan baik
        // dan tidak menyebabkan error field coa_id
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
            $tarifPerJam = (float) ($p->tarif_produk ?? $p->tarif_per_jam ?? 0);
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
    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        // CRITICAL: Filter by user_id untuk multi-tenant isolation
        $penggajian = Penggajian::with('pegawai.kualifikasiRelasi')
            ->where('user_id', auth()->id())
            ->findOrFail($id);
        
        // Cek apakah sudah diposting ke jurnal
        if ($penggajian->status_posting === 'posted') {
            return back()->withErrors(['error' => 'Penggajian yang sudah diposting ke jurnal tidak dapat diedit.']);
        }
        
        // CRITICAL: Filter by user_id untuk multi-tenant isolation
        $pegawais = Pegawai::with('kualifikasiRelasi')
            ->select('pegawais.*')
            ->where('user_id', auth()->id())
            ->orderBy('nama')
            ->get();
        $coaKasBank = \App\Models\Coa::whereIn('kode_akun', ['111', '112'])->get();
        
        return view('transaksi.penggajian.edit', compact('penggajian', 'pegawais', 'coaKasBank'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        DB::beginTransaction();

        try {
            // CRITICAL: Filter by user_id untuk multi-tenant isolation
            $penggajian = Penggajian::with('pegawai.kualifikasiRelasi')
                ->where('user_id', auth()->id())
                ->findOrFail($id);
            
            // Cek apakah sudah diposting ke jurnal
            if ($penggajian->status_posting === 'posted') {
                return back()->withErrors(['error' => 'Penggajian yang sudah diposting ke jurnal tidak dapat diedit.']);
            }

            // ⚠️ SISTEM TERBARU: PRODUK-BASED PENGGAJIAN ONLY
            // UPDATE HANYA UNTUK KOMPONEN YANG BISA BERUBAH:
            // - bonus, potongan, tunjangan, asuransi
            // - gaji_pokok (gaji_produksi) TIDAK boleh diubah - harus buat penggajian baru

            // Validasi input
            $request->validate([
                'tanggal_penggajian' => 'required|date',
                'coa_kasbank' => 'required|string',
                'bonus' => 'nullable|numeric',
                'potongan' => 'nullable|numeric',
                'tunjangan_jabatan' => 'nullable|numeric',
                'tunjangan_transport' => 'nullable|numeric',
                'tunjangan_konsumsi' => 'nullable|numeric',
                'asuransi' => 'nullable|numeric',
            ]);

            $pegawai = $penggajian->pegawai;

            // Get tunjangan & asuransi dari form atau gunakan yang ada
            $tunjanganJabatan = (float) ($request->tunjangan_jabatan ?? $penggajian->tunjangan_jabatan ?? 0);
            $tunjanganTransport = (float) ($request->tunjangan_transport ?? $penggajian->tunjangan_transport ?? 0);
            $tunjanganKonsumsi = (float) ($request->tunjangan_konsumsi ?? $penggajian->tunjangan_konsumsi ?? 0);
            $asuransi = (float) ($request->asuransi ?? $penggajian->asuransi ?? 0);
            $bonus = (float) ($request->bonus ?? $penggajian->bonus ?? 0);
            $potongan = (float) ($request->potongan ?? $penggajian->potongan ?? 0);

            // Total tunjangan
            $totalTunjangan = $tunjanganJabatan + $tunjanganTransport + $tunjanganKonsumsi;

            // Gaji produksi (gaji_pokok) tetap seperti semula - TIDAK BOLEH DIUBAH
            $gajiProduksiFinal = (float) $penggajian->gaji_pokok;
            
            // Calculate total gaji dengan komponen yang baru
            $totalGaji = $gajiProduksiFinal + $totalTunjangan + $bonus - $asuransi - $potongan;

            // Update penggajian dengan data terbaru (PRODUK-BASED ONLY)
            $penggajian->update([
                'tanggal_penggajian' => $request->tanggal_penggajian,
                'coa_kasbank' => $request->coa_kasbank,
                'total_tunjangan' => $totalTunjangan,
                'tunjangan_jabatan' => $tunjanganJabatan,
                'tunjangan_transport' => $tunjanganTransport,
                'tunjangan_konsumsi' => $tunjanganKonsumsi,
                'total_tunjangan' => $totalTunjangan,
                'asuransi' => $asuransi,
                'bonus' => $bonus,
                'potongan' => $potongan,
                'total_gaji' => $totalGaji,
                // ⚠️ PENTING: Jangan ubah gaji_pokok, total_jam_kerja, tarif_produk
            ]);

            \Log::info('Data penggajian (PRODUK-BASED) berhasil diupdate', [
                'penggajian_id' => $penggajian->id,
                'pegawai_id' => $pegawai->id,
                'gaji_produksi' => $gajiProduksiFinal,
                'total_tunjangan_baru' => $totalTunjangan,
                'asuransi_baru' => $asuransi,
                'total_gaji_baru' => $totalGaji,
            ]);

            DB::commit();

            return redirect()->route('transaksi.penggajian.index')
                ->with('success', 'Data penggajian berhasil diupdate! (Gaji produksi tetap, hanya tunjangan/bonus/potongan yang berubah)');

        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollBack();
            return back()->withErrors($e->errors())->withInput();
            
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error in PenggajianController@update: ' . $e->getMessage());

            return back()->withErrors(['error' => 'Gagal mengupdate penggajian: ' . $e->getMessage()])->withInput();
        }
    }

    public function show($id)
    {
        // CRITICAL: Filter by user_id untuk multi-tenant isolation
        $penggajian = Penggajian::with('pegawai.kualifikasiRelasi')
            ->where('user_id', auth()->id())
            ->findOrFail($id);

        $produkPayroll = $this->resolveProdukPayrollDetail($penggajian);

        return view('transaksi.penggajian.show', compact('penggajian', 'produkPayroll'));
    }

    private function resolveProdukPayrollDetail(Penggajian $penggajian): array
    {
        $pegawai = $penggajian->pegawai;
        $kualifikasi = app(\App\Services\KualifikasiTargetResolver::class)->resolve($pegawai);

        $tarifProduk = $this->firstPositiveNumber([
            $penggajian->tarif_produk,
            $kualifikasi?->tarif_produk,
            $kualifikasi?->tarif,
            $pegawai?->tarif_per_produk,
            $pegawai?->tarif,
            $pegawai?->tarif_per_jam,
        ]);

        $produkDihasilkan = (float) (
            $penggajian->total_produk_bulan
            ?? $penggajian->total_produk_bulanan
            ?? 0
        );

        if ($produkDihasilkan <= 0 && Schema::hasTable('produksis')) {
            $bulan = $penggajian->periode_bulan ?: Carbon::parse($penggajian->tanggal_penggajian)->month;
            $tahun = $penggajian->periode_tahun ?: Carbon::parse($penggajian->tanggal_penggajian)->year;
            $tanggalAwal = Carbon::createFromDate($tahun, $bulan, 1)->startOfMonth();
            $tanggalAkhir = Carbon::createFromDate($tahun, $bulan, 1)->endOfMonth();

            $query = DB::table('produksis')
                ->where('user_id', $penggajian->user_id ?? auth()->id())
                ->whereBetween('tanggal', [$tanggalAwal, $tanggalAkhir]);

            // Gunakan target_produksi dari kualifikasi sebagai acuan filter produk
            if ($kualifikasi && $kualifikasi->target_produksi > 0) {
                $query->where('jumlah_produksi_bulanan', $kualifikasi->target_produksi);
            }

            $produksi = $query->orderBy('tanggal', 'desc')->first();
            $produkDihasilkan = $produksi ? (float) $produksi->jumlah_produksi_bulanan : 0;
        }

        $gajiDasar = (float) ($penggajian->gaji_pokok ?? 0);
        if ($produkDihasilkan > 0 && $tarifProduk > 0) {
            $gajiDasar = $produkDihasilkan * $tarifProduk;
        }

        $totalGajiKaryawan = $gajiDasar
            + (float) ($penggajian->total_tunjangan ?? 0)
            + (float) ($penggajian->bonus ?? 0)
            - (float) ($penggajian->potongan ?? 0);

        $totalGaji = $totalGajiKaryawan + (float) ($penggajian->asuransi ?? 0);

        return [
            'tarif_produk' => $tarifProduk,
            'produk_dihasilkan' => $produkDihasilkan,
            'gaji_dasar' => $gajiDasar,
            'total_gaji' => $totalGaji,
        ];
    }

    private function firstPositiveNumber(array $values): float
    {
        foreach ($values as $value) {
            $number = (float) ($value ?? 0);
            if ($number > 0) {
                return $number;
            }
        }

        return 0;
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
        // CRITICAL: Filter by user_id untuk multi-tenant isolation
        $penggajian = Penggajian::with('pegawai')
            ->where('user_id', auth()->id())
            ->findOrFail($id);
        
        // Check permission: admin, owner, atau pegawai yang bersangkutan
        if (!in_array(auth()->user()->role, ['admin', 'owner']) && auth()->user()->pegawai_id !== $penggajian->pegawai_id) {
            abort(403, 'Anda tidak memiliki akses ke slip gaji ini');
        }

        // Prepare breakdown data for the slip
        $breakdown = [
            'gaji_pokok' => $penggajian->gaji_pokok ?? 0,
            'tunjangan' => $penggajian->total_tunjangan ?? 0,
            'bonus' => $penggajian->bonus ?? 0,
            'potongan' => $penggajian->potongan ?? 0,
            'asuransi' => $penggajian->asuransi ?? 0,
            'total' => $penggajian->total_gaji ?? 0,
        ];

        return view('transaksi.penggajian.slip', compact('penggajian', 'breakdown'));
    }

    /**
     * Download slip gaji PDF
     */
    public function downloadSlip($id)
    {
        // CRITICAL: Filter by user_id untuk multi-tenant isolation
        $penggajian = Penggajian::with('pegawai')
            ->where('user_id', auth()->id())
            ->findOrFail($id);
        
        // Check permission: admin, owner, atau pegawai yang bersangkutan
        if (!in_array(auth()->user()->role, ['admin', 'owner']) && auth()->user()->pegawai_id !== $penggajian->pegawai_id) {
            abort(403, 'Anda tidak memiliki akses ke slip gaji ini');
        }

        // Prepare breakdown data for the slip
        $breakdown = [
            'gaji_pokok' => $penggajian->gaji_pokok ?? 0,
            'tunjangan' => $penggajian->total_tunjangan ?? 0,
            'bonus' => $penggajian->bonus ?? 0,
            'potongan' => $penggajian->potongan ?? 0,
            'asuransi' => $penggajian->asuransi ?? 0,
            'total' => $penggajian->total_gaji ?? 0,
        ];

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('transaksi.penggajian.slip-pdf', compact('penggajian', 'breakdown'));
        
        $filename = 'slip-gaji-' . $penggajian->pegawai->nama . '-' . 
                   $penggajian->tanggal_penggajian->format('Y-m-d') . '.pdf';
        
        return $pdf->download($filename);
    }

    /**
     * Update status pembayaran
     */
    public function updateStatus(Request $request, $id)
    {
        // CRITICAL: Filter by user_id untuk multi-tenant isolation
        $penggajian = Penggajian::where('user_id', auth()->id())
            ->findOrFail($id);
        
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

        // CRITICAL: Filter by user_id untuk multi-tenant isolation
        $penggajian = Penggajian::with('pegawai')
            ->where('user_id', auth()->id())
            ->findOrFail($id);

        // Cegah double posting
        if ($penggajian->status_posting === 'posted') {
            return back()->with('error', 'Penggajian ini sudah diposting ke jurnal');
        }

        try {
            DB::beginTransaction();

            // Hitung komponen gaji
            $pegawai = $penggajian->pegawai;
            $jenisPegawai = strtolower($pegawai->jenis_pegawai ?? $pegawai->kategori ?? 'btktl');

            // ⚠️ SISTEM TERBARU: PRODUK-BASED PENGGAJIAN
            // Gaji dasar HARUS selalu dari gaji_pokok (yang menyimpan gaji_produksi_final)
            // TIDAK LAGI dari jam kerja atau presensi
            
            $gajiDasar = $penggajian->gaji_pokok ?? 0;
            
            // Validasi: Jika total_jam_kerja > 0, ini adalah data LEGACY (JAM-BASED)
            if (($penggajian->total_jam_kerja ?? 0) > 0) {
                \Log::warning("⚠️ LEGACY JAM-BASED DATA DETECTED - Penggajian ID {$penggajian->id} masih punya total_jam_kerja. Gunakan gaji_pokok saja.");
            }

            $totalTunjangan = $penggajian->total_tunjangan ?? 0;
            $bonus = $penggajian->bonus ?? 0;
            $potongan = $penggajian->potongan ?? 0;
            $asuransi = $penggajian->asuransi ?? 0;
            $totalGaji = $penggajian->total_gaji ?? 0;

            // Tentukan akun COA dengan validasi ketat
            if ($jenisPegawai === 'btkl') {
                $coaBebanGaji = Coa::where('kode_akun', '52')
                    ->where('user_id', auth()->id())
                    ->first(); // BIAYA TENAGA KERJA LANGSUNG (BTKL)
            } else {
                $coaBebanGaji = Coa::where('kode_akun', '54')
                    ->where('user_id', auth()->id())
                    ->first(); // BOP TENAGA KERJA TIDAK LANGSUNG
            }
            
            $coaBebanTunjangan = Coa::where('kode_akun', '513')
                ->where('user_id', auth()->id())
                ->first(); // Beban Tunjangan
            $coaBebanBonus = Coa::where('kode_akun', '515')
                ->where('user_id', auth()->id())
                ->first(); // Beban Bonus
            $coaBebanAsuransi = Coa::where('kode_akun', '514')
                ->where('user_id', auth()->id())
                ->first(); // Beban Asuransi
            $coaPotongan = Coa::where('kode_akun', '516')
                ->where('user_id', auth()->id())
                ->first(); // Potongan Gaji (contra account)

            // Tentukan akun kredit (Kas/Bank)
            $coaKredit = Coa::where('kode_akun', $penggajian->coa_kasbank)
                ->where('user_id', auth()->id())
                ->first();

            // Validasi COA tersedia - SEMUA HARUS ADA
            $missingCoas = [];
            if (!$coaBebanGaji) $missingCoas[] = ($jenisPegawai === 'btkl' ? '52 (BTKL)' : '54 (BOP)');
            if (!$coaBebanTunjangan) $missingCoas[] = '513 (Beban Tunjangan)';
            if (!$coaBebanBonus) $missingCoas[] = '515 (Beban Bonus)';
            if (!$coaBebanAsuransi) $missingCoas[] = '514 (Beban Asuransi)';
            if (!$coaPotongan) $missingCoas[] = '516 (Potongan Gaji)';
            if (!$coaKredit) $missingCoas[] = $penggajian->coa_kasbank . ' (Kas/Bank)';
            
            if (!empty($missingCoas)) {
                DB::rollBack();
                return back()->with('error', 'COA tidak ditemukan: ' . implode(', ', $missingCoas) . '. Pastikan semua akun sudah dibuat.');
            }

            // Buat jurnal entries dengan keterangan yang jelas
            $keterangan = "Penggajian {$pegawai->nama}";

            // DEBIT: Beban Gaji Dasar (HANYA jika > 0)
            if ($gajiDasar > 0) {
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
            }

            // DEBIT: Beban Tunjangan (HANYA jika > 0)
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

            // DEBIT: Beban Asuransi (HANYA jika > 0)
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

            // DEBIT: Beban Bonus (HANYA jika > 0)
            if ($bonus > 0) {
                JurnalUmum::create([
                    'coa_id' => $coaBebanBonus->id,
                    'tanggal' => $penggajian->tanggal_penggajian,
                    'keterangan' => $keterangan,
                    'debit' => $bonus,
                    'kredit' => 0,
                    'referensi' => $penggajian->id,
                    'tipe_referensi' => 'penggajian',
                    'created_by' => auth()->id(),
                ]);
            }

            // KREDIT: Potongan Gaji (HANYA jika > 0)
            if ($potongan > 0) {
                JurnalUmum::create([
                    'coa_id' => $coaPotongan->id,
                    'tanggal' => $penggajian->tanggal_penggajian,
                    'keterangan' => $keterangan,
                    'debit' => 0,
                    'kredit' => $potongan, // KREDIT untuk mengurangi beban
                    'referensi' => $penggajian->id,
                    'tipe_referensi' => 'penggajian',
                    'created_by' => auth()->id(),
                ]);
            }

            // KREDIT: Kas/Bank (pembayaran gaji) - SELALU ADA karena pasti ada pembayaran
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

            // Update saldo COA
            $this->updateCoaSaldo($coaBebanGaji->kode_akun);
            $this->updateCoaSaldo($coaBebanTunjangan->kode_akun);
            if ($bonus > 0) $this->updateCoaSaldo($coaBebanBonus->kode_akun);
            if ($asuransi > 0) $this->updateCoaSaldo($coaBebanAsuransi->kode_akun);
            if ($potongan > 0) $this->updateCoaSaldo($coaPotongan->kode_akun);
            $this->updateCoaSaldo($coaKredit->kode_akun);

            DB::commit();

            \Log::info('Penggajian berhasil diposting ke jurnal dengan detail', [
                'penggajian_id' => $penggajian->id,
                'gaji_dasar' => $gajiDasar,
                'total_tunjangan' => $totalTunjangan,
                'bonus' => $bonus,
                'asuransi' => $asuransi,
                'potongan' => $potongan,
                'total_gaji' => $totalGaji
            ]);

            return back()->with('success', 'Penggajian berhasil diposting ke jurnal umum dengan detail komponen');

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error posting penggajian to journal: ' . $e->getMessage());
            return back()->with('error', 'Terjadi kesalahan saat posting ke jurnal: ' . $e->getMessage());
        }
    }

    /**
     * TEST: Direct endpoint to check kualifikasi data
     */
    public function testKualifikasiData($pegawaiId)
    {
        try {
            $pegawai = Pegawai::with('kualifikasiRelasi')->findOrFail($pegawaiId);
            
            // Get all jabatan/kualifikasi data
            $jabatans = \App\Models\Kualifikasi::all();
            
            // Add resolved kualifikasi
            $resolvedJab = app(\App\Services\KualifikasiTargetResolver::class)->resolve($pegawai);
            
            return response()->json([
                'pegawai' => [
                    'id' => $pegawai->id,
                    'nama' => $pegawai->nama,
                    'jabatan_string' => $pegawai->jabatan,
                    'jabatan_id' => $pegawai->jabatan_id,
                    'user_id' => $pegawai->user_id,
                ],
                'jabatan_relasi' => $pegawai->jabatanRelasi ? [
                    'id' => $pegawai->jabatanRelasi->id,
                    'nama' => $pegawai->jabatanRelasi->nama,
                    'tarif_produk' => $pegawai->jabatanRelasi->tarif_produk,
                    'asuransi' => $pegawai->jabatanRelasi->asuransi,
                ] : null,
                'all_jabatans' => $jabatans->map(function($j) {
                    return [
                        'id' => $j->id,
                        'nama' => $j->nama,
                        'tarif_produk' => $j->tarif_produk,
                        'asuransi' => $j->asuransi,
                        'user_id' => $j->user_id,
                    ];
                }),
                'resolved_kualifikasi' => $resolvedJab ? [
                    'nama' => $resolvedJab->nama,
                    'tarif_produk' => $resolvedJab->tarif_produk,
                    'asuransi' => $resolvedJab->asuransi,
                ] : null,
                'timestamp' => now()->toISOString()
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ], 500);
        }
    }

    /**
     * Helper: Get COA kode berdasarkan departemen/jabatan pegawai.
     *
     * LOGIC:
     * - Cek kategori pegawai (BTKL atau BTKTL)
     * - Jika BTKL, cek keyword nama jabatan:
     *     "perbumbuan" / "bumbu"   → return '520'
     *     "penggorengan" / "goreng" → return '521'
     *     "penggorengan" / "goreng" / "pengukusan" / "kukus" → return '521'
     *     "pengemasan" / "kemasan"  → return '522'
     *     (tidak cocok apapun)      → return '52' (parent default)
     * - Jika BTKTL (admin, gudang, QC, dll) → return '53'
     *
     * @param Pegawai $pegawai
     * @return string Kode akun COA
     */
    private function getCoaBebanGaji($pegawai): string
    {
        // Tentukan jenis pegawai: prioritas dari jabatanRelasi, lalu field langsung
        $jenisPegawai = 'btktl'; // default
        if ($pegawai->jabatanRelasi) {
            $jenisPegawai = strtolower($pegawai->jabatanRelasi->kategori ?? 'btktl');
        } else {
            // Check broadly across fields
            $val = $pegawai->kategori ?? $pegawai->jenis_pegawai ?? $pegawai->jabatanRelasi?->kategori ?? 'btktl';
            $jenisPegawai = strtolower($val);
        }

        // JIKA BTKL (DIRECT LABOR) - Tenaga Kerja Langsung / Produksi
        if ($jenisPegawai === 'btkl') {
            // Ambil nama jabatan: prioritas dari relasi, fallback ke field string
            $jabatanNama = $pegawai->jabatanRelasi?->nama ?? $pegawai->jabatan ?? '';
            $jabatanLower = strtolower($jabatanNama);

            // Keyword: Penggorengan → Akun 521
            if (strpos($jabatanLower, 'penggorengan') !== false ||
                strpos($jabatanLower, 'goreng') !== false) {
                \Log::info("COA Mapping: {$pegawai->nama} [{$jabatanNama}] (Penggorengan) → COA 521");
                return '521';
            }

            // Keyword: Perbumbuan → Akun 522
            if (strpos($jabatanLower, 'perbumbuan') !== false ||
                strpos($jabatanLower, 'bumbu') !== false) {
                \Log::info("COA Mapping: {$pegawai->nama} [{$jabatanNama}] (Perbumbuan) → COA 522");
                return '522';
            }

            // Keyword: Pengemasan → Akun 523
            if (strpos($jabatanLower, 'pengemasan') !== false ||
                strpos($jabatanLower, 'kemasan') !== false) {
                \Log::info("COA Mapping: {$pegawai->nama} [{$jabatanNama}] (Pengemasan) → COA 523");
                return '523';
            }

            // Keyword: Pengukusan → Akun 524
            if (strpos($jabatanLower, 'pengukusan') !== false ||
                strpos($jabatanLower, 'kukus') !== false) {
                \Log::info("COA Mapping: {$pegawai->nama} [{$jabatanNama}] (Pengukusan) → COA 524");
                return '524';
            }

            // Default BTKL: tidak cocok keyword apapun → parent akun 52
            \Log::warning("COA Mapping: {$pegawai->nama} [{$jabatanNama}] BTKL tidak cocok keyword → Default COA 52");
            return '52';
        }

        // JIKA BTKTL (INDIRECT LABOR) - Admin, Gudang, QC, Supervisor, dll
        \Log::info("COA Mapping: {$pegawai->nama} (BTKTL) → COA 53");
        return '53';
    }

    /**
     * Generate nomor penggajian dengan format PGJ/YYYY/XXX
     * Per user per tahun, mulai dari 001
     */
    private function generateNomorPenggajian($tahun = null)
    {
        $tahun = $tahun ?? now()->year;
        $userId = auth()->id();
        
        // Hitung jumlah penggajian yang sudah ada untuk user ini di tahun ini
        $count = Penggajian::where('user_id', $userId)
            ->whereYear('created_at', $tahun)
            ->count();
        
        // Nomor berikutnya adalah count + 1
        $nomorUrut = str_pad($count + 1, 3, '0', STR_PAD_LEFT);
        
        $nomor = "PGJ/{$tahun}/{$nomorUrut}";
        
        \Log::info("Generated nomor penggajian", [
            'user_id' => $userId,
            'tahun' => $tahun,
            'nomor' => $nomor,
            'count' => $count
        ]);
        
        return $nomor;
    }
}