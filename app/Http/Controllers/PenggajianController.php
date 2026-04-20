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
     * Tampilkan form tambah penggajian.
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
     * API endpoint to get real-time employee salary data
     */
    public function getEmployeeData($pegawaiId)
    {
        try {
            $pegawai = Pegawai::with('jabatanRelasi')->findOrFail($pegawaiId);
            
            // Get current salary data from qualification (jabatan)
            $jabatan = $pegawai->jabatanRelasi;
            if ($jabatan) {
                $gajiPokok = $jabatan->gaji_pokok ?? $pegawai->gaji_pokok ?? 0;
                $tarif = $jabatan->tarif_per_jam ?? $pegawai->tarif_per_jam ?? 0;
                $tunjanganJabatan = $jabatan->tunjangan ?? 0;
                $tunjanganTransport = $jabatan->tunjangan_transport ?? 0;
                $tunjanganKonsumsi = $jabatan->tunjangan_konsumsi ?? 0;
                $asuransi = $jabatan->asuransi ?? 0;
            } else {
                // Fallback to pegawai stored values
                $gajiPokok = $pegawai->gaji_pokok ?? 0;
                $tarif = $pegawai->tarif_per_jam ?? 0;
                $tunjanganJabatan = $pegawai->tunjangan_jabatan ?? 0;
                $tunjanganTransport = $pegawai->tunjangan_transport ?? 0;
                $tunjanganKonsumsi = $pegawai->tunjangan_konsumsi ?? 0;
                $asuransi = $pegawai->asuransi ?? 0;
            }
            
            $totalTunjangan = $tunjanganJabatan + $tunjanganTransport + $tunjanganKonsumsi;
            
            return response()->json([
                'jenis' => strtolower($pegawai->jenis_pegawai ?? $pegawai->kategori ?? 'btktl'),
                'gaji_pokok' => $gajiPokok,
                'tarif' => $tarif,
                'total_tunjangan' => $totalTunjangan,
                'asuransi' => $asuransi,
                'nama' => $pegawai->nama,
                'jabatan_nama' => $pegawai->jabatan_nama ?? 'Staff'
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Employee not found',
                'message' => $e->getMessage()
            ], 404);
        }
    }

    /**
     * Simpan data penggajian baru.
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
                'coa_kasbank' => 'required|string',
            ]);

            $pegawai = Pegawai::with('jabatanRelasi')->findOrFail($request->pegawai_id);

            // STEP 1: Get data from KUALIFIKASI (JABATAN) - NOT from form
            $jenisPegawai = strtolower($pegawai->jenis_pegawai ?? $pegawai->kategori ?? 'btktl');
            
            if (!$pegawai->jabatanRelasi) {
                throw new \Exception('Pegawai tidak memiliki kualifikasi jabatan. Harap set jabatan terlebih dahulu.');
            }
            
            // Ambil data dari KUALIFIKASI (JABATAN) dengan validasi
            $gajiPokok = (float) ($pegawai->jabatanRelasi->gaji_pokok ?? 0);
            $tarifPerJam = (float) ($pegawai->jabatanRelasi->tarif_per_jam ?? 0);
            $tunjanganJabatan = (float) ($pegawai->jabatanRelasi->tunjangan ?? 0);
            $tunjanganTransport = (float) ($pegawai->jabatanRelasi->tunjangan_transport ?? 0);
            $tunjanganKonsumsi = (float) ($pegawai->jabatanRelasi->tunjangan_konsumsi ?? 0);
            $asuransi = (float) ($pegawai->jabatanRelasi->asuransi ?? 0);
            
            // Validasi data kualifikasi berdasarkan jenis pegawai
            if ($jenisPegawai === 'btkl') {
                if ($tarifPerJam <= 0) {
                    throw new \Exception("Tarif per jam untuk pegawai BTKL '{$pegawai->nama}' belum diset di kualifikasi jabatan '{$pegawai->jabatanRelasi->nama}'.");
                }
            } else { // BTKTL
                if ($gajiPokok <= 0) {
                    throw new \Exception("Gaji pokok untuk pegawai BTKTL '{$pegawai->nama}' belum diset di kualifikasi jabatan '{$pegawai->jabatanRelasi->nama}'.");
                }
            }
            
            // Warning jika asuransi 0 (opsional tapi sebaiknya ada)
            if ($asuransi <= 0) {
                \Log::warning("Asuransi untuk jabatan '{$pegawai->jabatanRelasi->nama}' adalah 0. Pastikan ini sudah benar.");
            }
            
            // STEP 2: Get jam kerja from PRESENSI - NOT from form
            $tanggalPenggajian = \Carbon\Carbon::parse($request->tanggal_penggajian);
            $month = $tanggalPenggajian->month;
            $year = $tanggalPenggajian->year;
            
            // Get total jam kerja from presensi
            $presensiData = \App\Models\Presensi::where('pegawai_id', $pegawai->id)
                ->whereMonth('tgl_presensi', $month)
                ->whereYear('tgl_presensi', $year)
                ->where('status', 'hadir')
                ->get();
            
            $totalJamKerja = 0;
            foreach ($presensiData as $presensi) {
                $totalJamKerja += $presensi->jumlah_jam;
            }
            
            // Validasi jam kerja untuk BTKL
            if ($jenisPegawai === 'btkl' && $totalJamKerja <= 0) {
                throw new \Exception("Tidak ada data presensi untuk pegawai BTKL '{$pegawai->nama}' pada periode {$month}/{$year}. Pastikan data presensi sudah diinput.");
            }
            
            // Log informasi presensi
            \Log::info("Data presensi pegawai {$pegawai->nama} periode {$month}/{$year}: {$presensiData->count()} hari hadir, total {$totalJamKerja} jam");
            
            // STEP 3: Get manual input from form (bonus, potongan)
            $bonus = (float) ($request->bonus ?? 0);
            $potongan = (float) ($request->potongan ?? 0);
            
            // STEP 4: Calculate totals
            $totalTunjangan = $tunjanganJabatan + $tunjanganTransport + $tunjanganKonsumsi;
            
            // Hitung gaji dasar berdasarkan jenis pegawai
            if ($jenisPegawai === 'btkl') {
                $gajiDasar = $tarifPerJam * $totalJamKerja;
            } else {
                $gajiDasar = $gajiPokok;
            }

            // Hitung total gaji
            $totalGaji = $gajiDasar + $totalTunjangan + $asuransi + $bonus - $potongan;

            // Log data sebelum menyimpan
            \Log::info('Data dari KUALIFIKASI dan PRESENSI:', [
                'pegawai_id' => $pegawai->id,
                'pegawai_nama' => $pegawai->nama,
                'jabatan_nama' => $pegawai->jabatanRelasi->nama_jabatan,
                'jenis_pegawai' => $jenisPegawai,
                'FROM_KUALIFIKASI' => [
                    'gaji_pokok' => $gajiPokok,
                    'tarif_per_jam' => $tarifPerJam,
                    'tunjangan_jabatan' => $tunjanganJabatan,
                    'tunjangan_transport' => $tunjanganTransport,
                    'tunjangan_konsumsi' => $tunjanganKonsumsi,
                    'asuransi' => $asuransi,
                ],
                'FROM_PRESENSI' => [
                    'total_jam_kerja' => $totalJamKerja,
                    'periode' => "{$year}-{$month}",
                ],
                'CALCULATED' => [
                    'gaji_dasar' => $gajiDasar,
                    'total_tunjangan' => $totalTunjangan,
                    'total_gaji' => $totalGaji,
                ],
            ]);

            // Simpan ke tabel penggajian
            $penggajian = new Penggajian([
                'pegawai_id' => $pegawai->id,
                'tanggal_penggajian' => $request->tanggal_penggajian,
                'coa_kasbank' => $request->coa_kasbank,
                'gaji_pokok' => $gajiPokok,
                'tarif_per_jam' => $tarifPerJam,
                'tunjangan' => $totalTunjangan, // For backward compatibility
                'tunjangan_jabatan' => $tunjanganJabatan,
                'tunjangan_transport' => $tunjanganTransport,
                'tunjangan_konsumsi' => $tunjanganKonsumsi,
                'total_tunjangan' => $totalTunjangan,
                'asuransi' => $asuransi,
                'bonus' => $bonus,
                'potongan' => $potongan,
                'total_jam_kerja' => $totalJamKerja,
                'total_gaji' => $totalGaji,
                'status_pembayaran' => 'belum_lunas',
            ]);

            if (!$penggajian->save()) {
                throw new \Exception('Gagal menyimpan data penggajian ke database');
            }

            \Log::info('Data penggajian berhasil disimpan', [
                'penggajian_id' => $penggajian->id,
                'total_gaji' => $totalGaji,
            ]);

            // STEP 5: Buat journal entry untuk mencatat pengeluaran kas/bank
            $this->createJournalEntry($penggajian, $pegawai);

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
     * Recalculate penggajian berdasarkan master data terbaru
     */
    public function recalculate($id)
    {
        DB::beginTransaction();

        try {
            $penggajian = Penggajian::with('pegawai.jabatanRelasi')->findOrFail($id);
            
            // Cek apakah sudah diposting ke jurnal
            if ($penggajian->isPosted()) {
                return back()->withErrors(['error' => 'Penggajian yang sudah diposting ke jurnal tidak dapat direcalculate.']);
            }

            $pegawai = $penggajian->pegawai;

            if (!$pegawai->jabatanRelasi) {
                throw new \Exception('Pegawai tidak memiliki kualifikasi jabatan. Harap set jabatan terlebih dahulu.');
            }

            // STEP 1: Get data from KUALIFIKASI (JABATAN) terbaru
            $jenisPegawai = strtolower($pegawai->jenis_pegawai ?? $pegawai->kategori ?? 'btktl');
            
            $gajiPokok = (float) ($pegawai->jabatanRelasi->gaji_pokok ?? 0);
            $tarifPerJam = (float) ($pegawai->jabatanRelasi->tarif_per_jam ?? 0);
            $tunjanganJabatan = (float) ($pegawai->jabatanRelasi->tunjangan ?? 0);
            $tunjanganTransport = (float) ($pegawai->jabatanRelasi->tunjangan_transport ?? 0);
            $tunjanganKonsumsi = (float) ($pegawai->jabatanRelasi->tunjangan_konsumsi ?? 0);
            $asuransi = (float) ($pegawai->jabatanRelasi->asuransi ?? 0);
            
            // STEP 2: Get jam kerja from PRESENSI terbaru
            $tanggalPenggajian = \Carbon\Carbon::parse($penggajian->tanggal_penggajian);
            $month = $tanggalPenggajian->month;
            $year = $tanggalPenggajian->year;
            
            $presensiData = \App\Models\Presensi::where('pegawai_id', $pegawai->id)
                ->whereMonth('tgl_presensi', $month)
                ->whereYear('tgl_presensi', $year)
                ->where('status', 'hadir')
                ->get();
            
            $totalJamKerja = 0;
            foreach ($presensiData as $presensi) {
                $totalJamKerja += $presensi->jumlah_jam;
            }
            
            // STEP 3: Calculate totals
            $totalTunjangan = $tunjanganJabatan + $tunjanganTransport + $tunjanganKonsumsi;
            
            if ($jenisPegawai === 'btkl') {
                $gajiDasar = $tarifPerJam * $totalJamKerja;
            } else {
                $gajiDasar = $gajiPokok;
            }

            $totalGaji = $gajiDasar + $totalTunjangan + $asuransi + $penggajian->bonus - $penggajian->potongan;

            // Update dengan data terbaru
            $penggajian->update([
                'gaji_pokok' => $gajiPokok,
                'tarif_per_jam' => $tarifPerJam,
                'tunjangan' => $totalTunjangan,
                'tunjangan_jabatan' => $tunjanganJabatan,
                'tunjangan_transport' => $tunjanganTransport,
                'tunjangan_konsumsi' => $tunjanganKonsumsi,
                'total_tunjangan' => $totalTunjangan,
                'asuransi' => $asuransi,
                'total_jam_kerja' => $totalJamKerja,
                'total_gaji' => $totalGaji,
            ]);

            \Log::info('Data penggajian berhasil direcalculate', [
                'penggajian_id' => $penggajian->id,
                'old_total' => $penggajian->getOriginal('total_gaji'),
                'new_total' => $totalGaji,
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
            
            $penggajian = Penggajian::findOrFail($id);

            // Cegah hapus jika sudah dibayar
            if ($penggajian->status_pembayaran === 'lunas') {
                return redirect()->route('transaksi.penggajian.index')
                    ->with('error', 'Penggajian tidak dapat dihapus karena sudah dibayar (status: ' . $penggajian->status_pembayaran . ')');
            }

            // Hapus journal entries terkait terlebih dahulu
            $journalEntries = \App\Models\JournalEntry::where('ref_type', 'penggajian')
                ->where('ref_id', $penggajian->id)
                ->get();

            foreach ($journalEntries as $entry) {
                // Hapus journal lines terlebih dahulu
                \App\Models\JournalLine::where('journal_entry_id', $entry->id)->delete();
                // Kemudian hapus journal entry
                $entry->delete();
            }

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
     * Tandai penggajian sebagai sudah dibayar
     */
    public function markAsPaid($id)
    {
        try {
            $penggajian = Penggajian::findOrFail($id);

            // Hanya update jika status masih belum_lunas
            if ($penggajian->status_pembayaran === 'belum_lunas') {
                // Mulai transaksi database
                DB::beginTransaction();
                
                try {
                    $penggajian->status_pembayaran = 'lunas';
                    $penggajian->tanggal_dibayar = now()->format('Y-m-d');
                    $penggajian->save();

                    // Buat journal entries untuk mencatat ke jurnal umum menggunakan postToJournal
                    $this->postToJournal($penggajian->id);

                    // Commit transaksi
                    DB::commit();

                    return redirect()->back()
                        ->with('success', 'Penggajian berhasil ditandai sebagai sudah dibayar dan jurnal umum telah dibuat.');
                        
                } catch (\Exception $e) {
                    DB::rollBack();
                    \Log::error('Error creating journal entry when marking as paid: ' . $e->getMessage());
                    
                    return redirect()->back()
                        ->withErrors(['error' => 'Gagal membuat jurnal umum: ' . $e->getMessage()]);
                }
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
            
            // Hitung komponen gaji
            if ($jenisPegawai === 'btkl') {
                $gajiDasar = ($penggajian->tarif_per_jam ?? 0) * ($penggajian->total_jam_kerja ?? 0);
                $coaBebanGaji = Coa::where('kode_akun', '52')->first(); // BIAYA TENAGA KERJA LANGSUNG (BTKL)
            } else {
                $gajiDasar = $penggajian->gaji_pokok ?? 0;
                $coaBebanGaji = Coa::where('kode_akun', '54')->first(); // BOP TENAGA KERJA TIDAK LANGSUNG
            }
            
            $totalTunjangan = $penggajian->total_tunjangan ?? 0;
            $bonus = $penggajian->bonus ?? 0;
            $potongan = $penggajian->potongan ?? 0;
            $asuransi = $penggajian->asuransi ?? 0;
            $totalGaji = $penggajian->total_gaji ?? 0;
            
            // COA untuk komponen lainnya dengan validasi ketat
            $coaBebanTunjangan = Coa::where('kode_akun', '513')->first(); // Beban Tunjangan
            $coaBebanBonus = Coa::where('kode_akun', '515')->first(); // Beban Bonus
            $coaBebanAsuransi = Coa::where('kode_akun', '514')->first(); // Beban Asuransi
            $coaPotongan = Coa::where('kode_akun', '516')->first(); // Potongan Gaji
            
            // Fallback: cari akun beban gaji umum
            if (!$coaBebanGaji) {
                $coaBebanGaji = Coa::whereRaw('LOWER(nama_akun) LIKE ?', ['%beban gaji%'])
                    ->orWhereRaw('LOWER(nama_akun) LIKE ?', ['%biaya tenaga kerja%'])
                    ->first();
            }
            
            // Validasi COA - SEMUA HARUS ADA
            $missingCoas = [];
            if (!$coaBebanGaji) $missingCoas[] = ($jenisPegawai === 'btkl' ? '52 (BTKL)' : '54 (BOP)');
            if (!$coaBebanTunjangan) $missingCoas[] = '513 (Beban Tunjangan)';
            if (!$coaBebanBonus) $missingCoas[] = '515 (Beban Bonus)';
            if (!$coaBebanAsuransi) $missingCoas[] = '514 (Beban Asuransi)';
            if (!$coaPotongan) $missingCoas[] = '516 (Potongan Gaji)';
            
            if (!empty($missingCoas)) {
                throw new \Exception('COA tidak ditemukan: ' . implode(', ', $missingCoas) . '. Pastikan semua akun sudah dibuat.');
            }

            // Pastikan akun kas/bank valid
            $coaKasBank = Coa::where('kode_akun', $penggajian->coa_kasbank)->first();
            if (!$coaKasBank) {
                throw new \Exception('Akun kas/bank tidak valid');
            }
            
            // Log data sebelum membuat jurnal
            \Log::info('Membuat jurnal penggajian dengan detail', [
                'penggajian_id' => $penggajian->id,
                'pegawai_id' => $pegawai->id,
                'gaji_dasar' => $gajiDasar,
                'tunjangan' => $totalTunjangan,
                'bonus' => $bonus,
                'asuransi' => $asuransi,
                'potongan' => $potongan,
                'total_gaji' => $totalGaji,
                'coa_beban' => $coaBebanGaji->kode_akun,
                'coa_kasbank' => $coaKasBank->kode_akun
            ]);
            
            // Buat jurnal entries dengan detail komponen - HANYA yang > 0
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
                    'created_by' => auth()->id() ?? 1,
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
                    'created_by' => auth()->id() ?? 1,
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
                    'created_by' => auth()->id() ?? 1,
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
                    'created_by' => auth()->id() ?? 1,
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
                    'created_by' => auth()->id() ?? 1,
                ]);
            }
            
            // KREDIT: Kas/Bank (pembayaran gaji) - SELALU ADA karena pasti ada pembayaran
            JurnalUmum::create([
                'coa_id' => $coaKasBank->id,
                'tanggal' => $penggajian->tanggal_penggajian,
                'keterangan' => $keterangan,
                'debit' => 0,
                'kredit' => $totalGaji,
                'referensi' => $penggajian->id,
                'tipe_referensi' => 'penggajian',
                'created_by' => auth()->id() ?? 1,
            ]);
            
            // Update saldo COA
            $this->updateCoaSaldo($coaBebanGaji->kode_akun);
            if ($totalTunjangan > 0 && $coaBebanTunjangan) $this->updateCoaSaldo($coaBebanTunjangan->kode_akun);
            if ($bonus > 0 && $coaBebanBonus) $this->updateCoaSaldo($coaBebanBonus->kode_akun);
            if ($asuransi > 0 && $coaBebanAsuransi) $this->updateCoaSaldo($coaBebanAsuransi->kode_akun);
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
    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $penggajian = Penggajian::with('pegawai.jabatanRelasi')->findOrFail($id);
        
        // Cek apakah sudah diposting ke jurnal
        if ($penggajian->isPosted()) {
            return back()->withErrors(['error' => 'Penggajian yang sudah diposting ke jurnal tidak dapat diedit.']);
        }
        
        $pegawais = Pegawai::with('jabatanRelasi')->get();
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
            $penggajian = Penggajian::with('pegawai.jabatanRelasi')->findOrFail($id);
            
            // Cek apakah sudah diposting ke jurnal
            if ($penggajian->isPosted()) {
                return back()->withErrors(['error' => 'Penggajian yang sudah diposting ke jurnal tidak dapat diedit.']);
            }

            // Validasi input
            $request->validate([
                'pegawai_id' => 'required|exists:pegawais,id',
                'tanggal_penggajian' => 'required|date',
                'coa_kasbank' => 'required|string',
            ]);

            $pegawai = Pegawai::with('jabatanRelasi')->findOrFail($request->pegawai_id);

            // STEP 1: Get data from KUALIFIKASI (JABATAN) - NOT from form
            $jenisPegawai = strtolower($pegawai->jenis_pegawai ?? $pegawai->kategori ?? 'btktl');
            
            if (!$pegawai->jabatanRelasi) {
                throw new \Exception('Pegawai tidak memiliki kualifikasi jabatan. Harap set jabatan terlebih dahulu.');
            }
            
            // Ambil data dari KUALIFIKASI (JABATAN) dengan validasi
            $gajiPokok = (float) ($pegawai->jabatanRelasi->gaji_pokok ?? 0);
            $tarifPerJam = (float) ($pegawai->jabatanRelasi->tarif_per_jam ?? 0);
            $tunjanganJabatan = (float) ($pegawai->jabatanRelasi->tunjangan ?? 0);
            $tunjanganTransport = (float) ($pegawai->jabatanRelasi->tunjangan_transport ?? 0);
            $tunjanganKonsumsi = (float) ($pegawai->jabatanRelasi->tunjangan_konsumsi ?? 0);
            $asuransi = (float) ($pegawai->jabatanRelasi->asuransi ?? 0);
            
            // Validasi data kualifikasi berdasarkan jenis pegawai
            if ($jenisPegawai === 'btkl') {
                if ($tarifPerJam <= 0) {
                    throw new \Exception("Tarif per jam untuk pegawai BTKL '{$pegawai->nama}' belum diset di kualifikasi jabatan '{$pegawai->jabatanRelasi->nama}'.");
                }
            } else { // BTKTL
                if ($gajiPokok <= 0) {
                    throw new \Exception("Gaji pokok untuk pegawai BTKTL '{$pegawai->nama}' belum diset di kualifikasi jabatan '{$pegawai->jabatanRelasi->nama}'.");
                }
            }
            
            // STEP 2: Get jam kerja from PRESENSI - NOT from form
            $tanggalPenggajian = \Carbon\Carbon::parse($request->tanggal_penggajian);
            $month = $tanggalPenggajian->month;
            $year = $tanggalPenggajian->year;
            
            // Get total jam kerja from presensi
            $presensiData = \App\Models\Presensi::where('pegawai_id', $pegawai->id)
                ->whereMonth('tgl_presensi', $month)
                ->whereYear('tgl_presensi', $year)
                ->where('status', 'hadir')
                ->get();
            
            $totalJamKerja = 0;
            foreach ($presensiData as $presensi) {
                $totalJamKerja += $presensi->jumlah_jam;
            }
            
            // Validasi jam kerja untuk BTKL
            if ($jenisPegawai === 'btkl' && $totalJamKerja <= 0) {
                throw new \Exception("Tidak ada data presensi untuk pegawai BTKL '{$pegawai->nama}' pada periode {$month}/{$year}. Pastikan data presensi sudah diinput.");
            }
            
            // STEP 3: Get manual input from form (bonus, potongan)
            $bonus = (float) ($request->bonus ?? 0);
            $potongan = (float) ($request->potongan ?? 0);
            
            // STEP 4: Calculate totals
            $totalTunjangan = $tunjanganJabatan + $tunjanganTransport + $tunjanganKonsumsi;
            
            // Hitung gaji dasar berdasarkan jenis pegawai
            if ($jenisPegawai === 'btkl') {
                $gajiDasar = $tarifPerJam * $totalJamKerja;
            } else {
                $gajiDasar = $gajiPokok;
            }

            // Hitung total gaji
            $totalGaji = $gajiDasar + $totalTunjangan + $asuransi + $bonus - $potongan;

            // Update penggajian
            $penggajian->update([
                'pegawai_id' => $pegawai->id,
                'tanggal_penggajian' => $request->tanggal_penggajian,
                'coa_kasbank' => $request->coa_kasbank,
                'gaji_pokok' => $gajiPokok,
                'tarif_per_jam' => $tarifPerJam,
                'tunjangan' => $totalTunjangan, // For backward compatibility
                'tunjangan_jabatan' => $tunjanganJabatan,
                'tunjangan_transport' => $tunjanganTransport,
                'tunjangan_konsumsi' => $tunjanganKonsumsi,
                'total_tunjangan' => $totalTunjangan,
                'asuransi' => $asuransi,
                'bonus' => $bonus,
                'potongan' => $potongan,
                'total_jam_kerja' => $totalJamKerja,
                'total_gaji' => $totalGaji,
            ]);

            \Log::info('Data penggajian berhasil diupdate', [
                'penggajian_id' => $penggajian->id,
                'total_gaji' => $totalGaji,
            ]);

            DB::commit();

            return redirect()->route('transaksi.penggajian.index')
                ->with('success', 'Data penggajian berhasil diupdate!');

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
            $jenisPegawai = strtolower($pegawai->jenis_pegawai ?? $pegawai->kategori ?? 'btktl');

            // Gaji dasar - gunakan data yang ada di penggajian
            if ($jenisPegawai === 'btkl') {
                // Untuk BTKL, gunakan tarif per jam x total jam kerja
                $gajiDasar = ($penggajian->tarif_per_jam ?? 0) * ($penggajian->total_jam_kerja ?? 0);
            } else {
                $gajiDasar = $penggajian->gaji_pokok ?? 0;
            }

            $totalTunjangan = $penggajian->total_tunjangan ?? 0;
            $bonus = $penggajian->bonus ?? 0;
            $potongan = $penggajian->potongan ?? 0;
            $asuransi = $penggajian->asuransi ?? 0;
            $totalGaji = $penggajian->total_gaji ?? 0;

            // Tentukan akun COA dengan validasi ketat
            if ($jenisPegawai === 'btkl') {
                $coaBebanGaji = Coa::where('kode_akun', '52')->first(); // BIAYA TENAGA KERJA LANGSUNG (BTKL)
            } else {
                $coaBebanGaji = Coa::where('kode_akun', '54')->first(); // BOP TENAGA KERJA TIDAK LANGSUNG
            }
            
            $coaBebanTunjangan = Coa::where('kode_akun', '513')->first(); // Beban Tunjangan
            $coaBebanBonus = Coa::where('kode_akun', '515')->first(); // Beban Bonus
            $coaBebanAsuransi = Coa::where('kode_akun', '514')->first(); // Beban Asuransi
            $coaPotongan = Coa::where('kode_akun', '516')->first(); // Potongan Gaji (contra account)

            // Tentukan akun kredit (Kas/Bank)
            $coaKredit = Coa::where('kode_akun', $penggajian->coa_kasbank)->first();

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
                'tunjangan' => $totalTunjangan,
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
}
