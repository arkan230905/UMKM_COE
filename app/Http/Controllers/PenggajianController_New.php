<?php

namespace App\Http\Controllers;

use App\Models\Pegawai;
use App\Models\Penggajian;
use App\Models\Presensi;
use App\Models\Coa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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
     * AJAX: Get data pegawai untuk form penggajian
     */
    public function getPegawaiData($pegawaiId, $tanggal)
    {
        $pegawai = Pegawai::findOrFail($pegawaiId);
        
        // Hitung total jam kerja bulan berjarkan
        $totalJamKerja = $this->hitungTotalJamKerja($pegawaiId, $tanggal);
        
        // Tentukan data berdasarkan jenis pegawai
        $data = [
            'pegawai' => $pegawai,
            'total_jam_kerja' => $totalJamKerja,
        ];
        
        if (strtolower($pegawai->jenis_pegawai) === 'btkl') {
            // BTKL: Tarif per jam, tunjangan, asuransi
            $data['tarif_per_jam'] = (float) ($pegawai->tarif_per_jam ?? 0);
            $data['gaji_pokok'] = 0; // Tidak dipakai untuk BTKL
            $data['tunjangan'] = (float) ($pegawai->tunjangan ?? 0);
            $data['asuransi'] = (float) ($pegawai->asuransi ?? 0);
            $data['gaji_dasar'] = $data['tarif_per_jam'] * $totalJamKerja;
        } else {
            // BTKTL: Gaji pokok, tunjangan, asuransi
            $data['gaji_pokok'] = (float) ($pegawai->gaji_pokok ?? 0);
            $data['tarif_per_jam'] = 0; // Tidak dipakai untuk BTKTL
            $data['tunjangan'] = (float) ($pegawai->tunjangan ?? 0);
            $data['asuransi'] = (float) ($pegawai->asuransi ?? 0);
            $data['gaji_dasar'] = $data['gaji_pokok'];
        }
        
        return response()->json($data);
    }

    /**
     * Hitung total jam kerja dari tabel presensi
     */
    private function hitungTotalJamKerja($pegawaiId, $tanggal)
    {
        $carbonDate = Carbon::parse($tanggal);
        
        return Presensi::where('pegawai_id', $pegawaiId)
            ->whereMonth('tgl_presensi', $carbonDate->month)
            ->whereYear('tgl_presensi', $carbonDate->year)
            ->sum('jumlah_jam');
    }

    /**
     * Simpan data penggajian baru.
     */
    public function store(Request $request)
    {
        // Validasi input
        $validated = $request->validate([
            'pegawai_id' => 'required|exists:pegawais,id',
            'tanggal_penggajian' => 'required|date',
            'coa_kasbank' => 'required',
            'bonus' => 'nullable|numeric|min:0',
            'potongan' => 'nullable|numeric|min:0',
        ], [
            'pegawai_id.required' => 'Pegawai harus dipilih',
            'pegawai_id.exists' => 'Pegawai tidak ditemukan',
            'tanggal_penggajian.required' => 'Tanggal penggajian harus diisi',
            'tanggal_penggajian.date' => 'Format tanggal tidak valid',
            'coa_kasbank.required' => 'Akun kas/bank harus dipilih',
        ]);

        DB::beginTransaction();
        try {
            $pegawai = Pegawai::findOrFail($validated['pegawai_id']);
            $totalJamKerja = $this->hitungTotalJamKerja($pegawai->id, $validated['tanggal_penggajian']);
            
            // Input manual
            $bonus = (float) ($request->bonus ?? 0);
            $potongan = (float) ($request->potongan ?? 0);
            
            // Data dari pegawai
            $tunjangan = (float) ($pegawai->tunjangan ?? 0);
            $asuransi = (float) ($pegawai->asuransi ?? 0);
            
            // Tentukan perhitungan berdasarkan jenis pegawai
            $jenis = strtolower($pegawai->jenis_pegawai ?? 'btktl');
            
            if ($jenis === 'btkl') {
                // BTKL: Gaji dasar = tarif × jam kerja
                $tarifPerJam = (float) ($pegawai->tarif_per_jam ?? 0);
                $gajiPokok = 0; // Tidak dipakai
                $gajiDasar = $tarifPerJam * $totalJamKerja;
                
                // Validasi: BTKL harus punya tarif
                if ($tarifPerJam <= 0) {
                    return back()->withErrors([
                        'pegawai_id' => 'Pegawai BTKL harus memiliki tarif per jam yang valid'
                    ])->withInput();
                }
                
                $totalGaji = $gajiDasar + $tunjangan + $asuransi + $bonus - $potongan;
            } else {
                // BTKTL: Gaji pokok langsung
                $gajiPokok = (float) ($pegawai->gaji_pokok ?? 0);
                $tarifPerJam = 0; // Tidak dipakai
                $gajiDasar = $gajiPokok;
                
                // Validasi: BTKTL harus punya gaji pokok
                if ($gajiPokok <= 0) {
                    return back()->withErrors([
                        'pegawai_id' => 'Pegawai BTKTL harus memiliki gaji pokok yang valid'
                    ])->withInput();
                }
                
                $totalGaji = $gajiPokok + $tunjangan + $asuransi + $bonus - $potongan;
            }

            // Validasi saldo kas/bank
            $coaKasBank = Coa::where('kode_akun', $validated['coa_kasbank'])->first();
            if (!$coaKasBank) {
                return back()->withErrors(['coa_kasbank' => 'Akun kas/bank tidak ditemukan'])->withInput();
            }

            $saldoAkhir = ($coaKasBank->saldo_awal ?? 0) + ($coaKasBank->saldo_debit ?? 0) - ($coaKasBank->saldo_kredit ?? 0);
            if ($saldoAkhir < $totalGaji) {
                return back()->withErrors([
                    'kas' => 'Saldo kas/bank tidak mencukupi. Saldo tersedia: Rp ' . number_format($saldoAkhir, 0, ',', '.') . 
                              '; Kebutuhan: Rp ' . number_format($totalGaji, 0, ',', '.')
                ])->withInput();
            }

            // Simpan ke tabel penggajian
            $penggajian = new Penggajian([
                'pegawai_id' => $pegawai->id,
                'tanggal_penggajian' => $validated['tanggal_penggajian'],
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
                throw new \Exception('Gagal menyimpan data penggajian');
            }

            // Simpan detail bonus/potongan tambahan jika ada
            $this->saveDetailKomponen($penggajian, $request);

            // Update saldo COA (optional)
            // $this->updateSaldoCoa($coaKasBank, $totalGaji);

            DB::commit();
            return redirect()->route('transaksi.penggajian.index')
                ->with('success', 'Data penggajian berhasil ditambahkan!');

        } catch (\Exception $e) {
            DB::rollback();
            \Log::error('Error in PenggajianController@store: ' . $e->getMessage());
            return back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Tampilkan detail penggajian.
     */
    public function show($id)
    {
        $penggajian = Penggajian::with(['pegawai', 'bonusTambahans', 'tunjanganTambahans', 'potonganTambahans'])
            ->findOrFail($id);
        
        // Hitung ulang untuk konsistensi
        $pegawai = $penggajian->pegawai;
        $jenis = strtolower($pegawai->jenis_pegawai ?? 'btktl');
        
        if ($jenis === 'btkl') {
            $gajiDasar = $penggajian->tarif_per_jam * $penggajian->total_jam_kerja;
            $gajiDasarLabel = 'Gaji Dasar (Tarif × Jam Kerja)';
        } else {
            $gajiDasar = $penggajian->gaji_pokok;
            $gajiDasarLabel = 'Gaji Pokok';
        }
        
        $totalPendapatan = $gajiDasar + $penggajian->tunjangan + $penggajian->asuransi + $penggajian->bonus;
        $totalPotongan = $penggajian->potongan;
        $gajiBersih = $totalPendapatan - $totalPotongan;
        
        return view('transaksi.penggajian.show', compact('penggajian', 'gajiDasar', 'gajiDasarLabel', 'totalPendapatan', 'totalPotongan', 'gajiBersih'));
    }

    /**
     * Form edit data penggajian.
     */
    public function edit($id)
    {
        $penggajian = Penggajian::findOrFail($id);
        $pegawais = Pegawai::all();
        $kasbank = \App\Helpers\AccountHelper::getKasBankAccounts();
        return view('transaksi.penggajian.edit', compact('penggajian', 'pegawais', 'kasbank'));
    }

    /**
     * Update data penggajian.
     */
    public function update(Request $request, $id)
    {
        // Validasi (sama dengan store)
        $validated = $request->validate([
            'pegawai_id' => 'required|exists:pegawais,id',
            'tanggal_penggajian' => 'required|date',
            'coa_kasbank' => 'required',
            'bonus' => 'nullable|numeric|min:0',
            'potongan' => 'nullable|numeric|min:0',
        ]);

        DB::beginTransaction();
        try {
            $penggajian = Penggajian::findOrFail($id);
            $pegawai = Pegawai::findOrFail($validated['pegawai_id']);
            $totalJamKerja = $this->hitungTotalJamKerja($pegawai->id, $validated['tanggal_penggajian']);
            
            // Hitung ulang dengan logika yang sama dengan store()
            $jenis = strtolower($pegawai->jenis_pegawai ?? 'btktl');
            $bonus = (float) ($request->bonus ?? 0);
            $potongan = (float) ($request->potongan ?? 0);
            $tunjangan = (float) ($pegawai->tunjangan ?? 0);
            $asuransi = (float) ($pegawai->asuransi ?? 0);
            
            if ($jenis === 'btkl') {
                $tarifPerJam = (float) ($pegawai->tarif_per_jam ?? 0);
                $gajiPokok = 0;
                $gajiDasar = $tarifPerJam * $totalJamKerja;
                $totalGaji = $gajiDasar + $tunjangan + $asuransi + $bonus - $potongan;
            } else {
                $gajiPokok = (float) ($pegawai->gaji_pokok ?? 0);
                $tarifPerJam = 0;
                $gajiDasar = $gajiPokok;
                $totalGaji = $gajiPokok + $tunjangan + $asuransi + $bonus - $potongan;
            }

            // Update penggajian
            $penggajian->update([
                'pegawai_id' => $pegawai->id,
                'tanggal_penggajian' => $validated['tanggal_penggajian'],
                'coa_kasbank' => $validated['coa_kasbank'],
                'gaji_pokok' => $gajiPokok,
                'tarif_per_jam' => $tarifPerJam,
                'tunjangan' => $tunjangan,
                'asuransi' => $asuransi,
                'bonus' => $bonus,
                'potongan' => $potongan,
                'total_jam_kerja' => $totalJamKerja,
                'total_gaji' => $totalGaji,
            ]);

            // Update detail komponen
            $this->saveDetailKomponen($penggajian, $request);

            DB::commit();
            return redirect()->route('transaksi.penggajian.index')
                ->with('success', 'Data penggajian berhasil diperbarui!');

        } catch (\Exception $e) {
            DB::rollback();
            return back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Hapus data penggajian.
     */
    public function destroy($id)
    {
        $penggajian = Penggajian::findOrFail($id);
        $penggajian->delete();
        
        return redirect()->route('transaksi.penggajian.index')
            ->with('success', 'Data penggajian berhasil dihapus');
    }

    /**
     * Simpan detail komponen tambahan (bonus, tunjangan, potongan)
     */
    private function saveDetailKomponen($penggajian, $request)
    {
        // Hapus detail lama
        $penggajian->bonusTambahans()->delete();
        $penggajian->tunjanganTambahans()->delete();
        $penggajian->potonganTambahans()->delete();

        // Simpan bonus tambahan
        if ($request->has('bonus_tambahan_names')) {
            foreach ($request->bonus_tambahan_names as $index => $nama) {
                if (!empty($nama) && isset($request->bonus_tambahan_values[$index])) {
                    $penggajian->bonusTambahans()->create([
                        'nama' => $nama,
                        'jumlah' => (float) $request->bonus_tambahan_values[$index],
                    ]);
                }
            }
        }

        // Simpan tunjangan tambahan
        if ($request->has('tunjangan_tambahan_names')) {
            foreach ($request->tunjangan_tambahan_names as $index => $nama) {
                if (!empty($nama) && isset($request->tunjangan_tambahan_values[$index])) {
                    $penggajian->tunjanganTambahans()->create([
                        'nama' => $nama,
                        'jumlah' => (float) $request->tunjangan_tambahan_values[$index],
                    ]);
                }
            }
        }

        // Simpan potongan tambahan
        if ($request->has('potongan_tambahan_names')) {
            foreach ($request->potongan_tambahan_names as $index => $nama) {
                if (!empty($nama) && isset($request->potongan_tambahan_values[$index])) {
                    $penggajian->potonganTambahans()->create([
                        'nama' => $nama,
                        'jumlah' => (float) $request->potongan_tambahan_values[$index],
                    ]);
                }
            }
        }
    }

    /**
     * Cetak slip gaji (PDF)
     */
    public function slip($id)
    {
        $penggajian = Penggajian::with(['pegawai', 'bonusTambahans', 'tunjanganTambahans', 'potonganTambahans'])
            ->findOrFail($id);
        
        // Hitung ulang untuk konsistensi (sama dengan method show)
        $pegawai = $penggajian->pegawai;
        $jenis = strtolower($pegawai->jenis_pegawai ?? 'btktl');
        
        if ($jenis === 'btkl') {
            $gajiDasar = $penggajian->tarif_per_jam * $penggajian->total_jam_kerja;
            $gajiDasarLabel = 'Gaji Dasar (Tarif × Jam Kerja)';
        } else {
            $gajiDasar = $penggajian->gaji_pokok;
            $gajiDasarLabel = 'Gaji Pokok';
        }
        
        $totalPendapatan = $gajiDasar + $penggajian->tunjangan + $penggajian->asuransi + $penggajian->bonus;
        $totalPotongan = $penggajian->potongan;
        $gajiBersih = $totalPendapatan - $totalPotongan;

        return view('transaksi.penggajian.slip', compact('penggajian', 'gajiDasar', 'gajiDasarLabel', 'totalPendapatan', 'totalPotongan', 'gajiBersih'));
    }
}
