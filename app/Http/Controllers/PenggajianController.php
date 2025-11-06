<?php

namespace App\Http\Controllers;

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
        return view('transaksi.penggajian.create', compact('pegawais'));
    }

    /**
     * Simpan data penggajian baru.
     */
    public function store(Request $request)
    {
        $request->validate([
            'pegawai_id' => 'required|exists:pegawais,id',
            'tanggal_penggajian' => 'required|date',
        ]);

        $pegawai = Pegawai::findOrFail($request->pegawai_id);

        // Ambil total jam kerja pegawai dari presensi bulan ini
        $totalJamKerja = Presensi::where('pegawai_id', $pegawai->id)
            ->whereMonth('tgl_presensi', Carbon::parse($request->tanggal_penggajian)->month)
            ->whereYear('tgl_presensi', Carbon::parse($request->tanggal_penggajian)->year)
            ->sum('jumlah_jam');

        // Tentukan gaji berdasarkan jenis pegawai
        $jenis = strtolower($pegawai->jenis_pegawai ?? '');
        $potongan = 0; // bisa disesuaikan jika ada logika potongan

        if ($jenis === 'btkl') {
            // BTKL: gaji dihitung per jam kerja (anggap gaji_pokok sebagai tarif per jam)
            $ratePerJam = (float) ($pegawai->gaji_pokok ?? 0);
            if ($ratePerJam <= 0) {
                $ratePerJam = (float) ($pegawai->gaji ?? 0);
            }
            $gajiPokok = $ratePerJam; // disimpan sebagai rate yang dipakai
            $tunjangan = 0;
            $totalGaji = ($ratePerJam * (float) $totalJamKerja) - (float) $potongan;
        } else {
            // BTKTL: gaji pokok bulanan + tunjangan (tidak dikali jam)
            $gajiPokok = (float) ($pegawai->gaji_pokok ?? 0);
            if ($gajiPokok <= 0) {
                $gajiPokok = (float) ($pegawai->gaji ?? 0);
            }
            $tunjangan = (float) ($pegawai->tunjangan ?? 0);
            $totalGaji = ($gajiPokok + $tunjangan) - (float) $potongan;
        }

        // Cek saldo kas (101) cukup untuk membayar total gaji
        $cashCode = '101';
        $saldoAwal = (float) (\App\Models\Coa::where('kode_akun', $cashCode)->value('saldo_awal') ?? 0);
        $acc = \App\Models\Account::where('code', $cashCode)->first();
        $journalBalance = 0.0;
        if ($acc) {
            $journalBalance = (float) (\App\Models\JournalLine::where('account_id', $acc->id)
                ->selectRaw('COALESCE(SUM(debit - credit),0) as bal')->value('bal') ?? 0);
        }
        $cashBalance = $saldoAwal + $journalBalance;
        if ($cashBalance + 1e-6 < (float)$totalGaji) {
            return back()->withErrors([
                'kas' => 'Nominal kas tidak cukup untuk melakukan transaksi. Saldo kas saat ini: Rp '.number_format($cashBalance,0,',','.').' ; Nominal transaksi: Rp '.number_format((float)$totalGaji,0,',','.'),
            ])->withInput();
        }

        // Simpan ke tabel penggajian
        $created = Penggajian::create([
            'pegawai_id' => $pegawai->id,
            'tanggal_penggajian' => $request->tanggal_penggajian,
            'gaji_pokok' => $gajiPokok,
            'tunjangan' => $tunjangan,
            'potongan' => $potongan,
            'total_jam_kerja' => $totalJamKerja,
            'total_gaji' => $totalGaji,
        ]);

        // Perbarui BOP untuk Beban Gaji (perkiraan):
        // BTKTL  => gaji_pokok (fallback gaji) + tunjangan (per bulan)
        // BTKL   => (rate per jam dari gaji_pokok fallback gaji) * total jam bulan itu + tunjangan
        $periode = Carbon::parse($request->tanggal_penggajian);
        $perkiraanBebanGaji = 0.0;

        $hoursPerDay = (int) (config('app.btkl_hours_per_day') ?? 8);
        $workingDays = (int) (config('app.working_days_per_month') ?? 26);

        $semuaPegawai = Pegawai::all();
        foreach ($semuaPegawai as $p) {
            $jenisP = strtolower($p->jenis_pegawai ?? '');
            $base = (float) ($p->gaji_pokok ?? 0);
            if ($base <= 0) { $base = (float) ($p->gaji ?? 0); }
            $tunj = (float) ($p->tunjangan ?? 0);

            if ($jenisP === 'btkl') {
                $perkiraanBebanGaji += ($base * $hoursPerDay * $workingDays) + $tunj;
            } else {
                $perkiraanBebanGaji += $base + $tunj;
            }
        }

        // Cari COA Beban Gaji (prioritas berdasarkan nama_akun), fallback kode_akun 501
        $coaBebanGaji = Coa::whereRaw('LOWER(nama_akun) = ?', ['beban gaji'])
            ->orWhere('kode_akun', 501)
            ->first();

        if ($coaBebanGaji) {
            $tanggalBop = $periode->copy()->startOfMonth()->toDateString();
            Bop::updateOrCreate(
                [
                    'coa_id' => $coaBebanGaji->id,
                    'tanggal' => $tanggalBop,
                ],
                [
                    'keterangan' => 'Beban Gaji (Perkiraan) ' . $periode->format('F Y'),
                    'nominal' => $perkiraanBebanGaji,
                ]
            );
        }

        return redirect()->route('transaksi.penggajian.index')
            ->with('success', 'Data penggajian berhasil ditambahkan!');
    }

    /**
     * Hapus data penggajian.
     */
    public function destroy($id)
    {
        $penggajian = Penggajian::findOrFail($id);
        $penggajian->delete();

        return redirect()->route('transaksi.penggajian.index')
            ->with('success', 'Data penggajian berhasil dihapus.');
    }
}
