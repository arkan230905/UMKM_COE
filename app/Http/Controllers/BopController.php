<?php

namespace App\Http\Controllers;

use App\Models\Bop;
use App\Models\Coa;
use App\Models\Pegawai;
use Illuminate\Http\Request;
use Carbon\Carbon;

class BopController extends Controller
{
    // Tampilkan semua data BOP
    public function index()
    {
        // Ambil data BOP dan relasi COA
        $bop = Bop::with('coa')->get();

        // Jika ada COA yang belum punya BOP, tambahkan otomatis
        // Kriteria: tipe_akun beban/expense/biaya ATAU kode_akun dimulai dengan '5'
        $coaTanpaBop = Coa::where(function($q){
                                $q->whereIn('tipe_akun', ['Expense', 'Beban', 'Biaya'])
                                  ->orWhere('kode_akun', 'like', '5%');
                            })
                            ->whereDoesntHave('bop')
                            ->get();

        foreach ($coaTanpaBop as $coa) {
            Bop::create([
                'coa_id' => $coa->id,
                'keterangan' => 'Sinkron otomatis dari COA',
            ]);
        }

        // Ambil ulang data terbaru
        $bop = Bop::with('coa')->get();

        return view('master-data.bop.index', compact('bop'));
    }

    // Rekalkulasi Beban Gaji (Perkiraan) untuk bulan tertentu
    public function recalc(Request $request)
    {
        $periodeInput = $request->input('periode'); // format expected: YYYY-MM
        $periode = $periodeInput ? Carbon::createFromFormat('Y-m', $periodeInput) : Carbon::now();

        $hoursPerDay = (int) (config('app.btkl_hours_per_day') ?? 8);
        $workingDays = (int) (config('app.working_days_per_month') ?? 26);

        $perkiraan = 0.0;
        foreach (Pegawai::all() as $p) {
            $jenis = strtolower($p->jenis_pegawai ?? '');
            $base = (float) ($p->gaji_pokok ?? 0);
            if ($base <= 0) { $base = (float) ($p->gaji ?? 0); }
            $tunj = (float) ($p->tunjangan ?? 0);

            if ($jenis === 'btkl') {
                $perkiraan += ($base * $hoursPerDay * $workingDays) + $tunj;
            } else {
                $perkiraan += $base + $tunj;
            }
        }

        $coaBebanGaji = Coa::whereRaw('LOWER(nama_akun) = ?', ['beban gaji'])
            ->orWhere('kode_akun', 501)
            ->first();

        if ($coaBebanGaji) {
            $tanggal = $periode->copy()->endOfMonth()->toDateString();
            Bop::updateOrCreate(
                [
                    'coa_id' => $coaBebanGaji->id,
                    'tanggal' => $tanggal,
                ],
                [
                    'keterangan' => 'Beban Gaji (Perkiraan) ' . $periode->format('F Y'),
                    'nominal' => $perkiraan,
                ]
            );
        }

        return redirect()->route('master-data.bop.index')
            ->with('success', 'Rekalkulasi Beban Gaji (Perkiraan) berhasil untuk ' . $periode->format('F Y'));
    }

    // Edit data BOP
    public function edit(Bop $bop)
    {
        $coa = Coa::whereIn('tipe_akun', ['Expense', 'Beban', 'Biaya'])->get();
        return view('master-data.bop.edit', compact('bop', 'coa'));
    }

    // Update data BOP
    public function update(Request $request, Bop $bop)
    {
        $request->validate([
            'nominal' => 'nullable|numeric',
            'tanggal' => 'nullable|date',
        ]);

        $bop->update($request->only('nominal', 'tanggal'));

        return redirect()->route('master-data.bop.index')
                         ->with('success', 'Data BOP berhasil diperbarui');
    }

    // Hapus data BOP
    public function destroy(Bop $bop)
    {
        $bop->delete();

        return redirect()->route('master-data.bop.index')
                         ->with('success', 'Data BOP berhasil dihapus');
    }
}
