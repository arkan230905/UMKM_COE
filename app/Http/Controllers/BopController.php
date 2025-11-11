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
        // Arahkan ke halaman BOP Budget (tampilan tabel sesuai yang diinginkan)
        $periode = request('periode', now()->format('Y-m'));
        return redirect()->route('master-data.bop-budget.index', ['periode' => $periode]);
    }

    // Form tambah BOP
    public function create()
    {
        // Ambil semua akun beban
        $akunBeban = Coa::where(function($q){
                    $q->whereIn('tipe_akun', ['Expense', 'Beban', 'Biaya'])
                      ->orWhere('kode_akun', 'like', '5%');
                })
                ->orderBy('kode_akun')
                ->get(['id','kode_akun','nama_akun']);

        return view('master-data.bop.create', compact('akunBeban'));
    }

    // Simpan BOP baru
    public function store(Request $request)
    {
        $validated = $request->validate([
            'kode_akun' => 'required|string',
            'budget' => 'required|numeric|min:0',
            'keterangan' => 'nullable|string',
        ]);

        // Cari COA berdasarkan kode_akun
        $coa = Coa::where('kode_akun', $validated['kode_akun'])->first();
        
        if (!$coa) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Akun dengan kode ' . $validated['kode_akun'] . ' tidak ditemukan');
        }

        // Cek apakah sudah ada budget untuk akun ini
        $existing = Bop::where('kode_akun', $validated['kode_akun'])->first();
        
        if ($existing) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Budget untuk akun ' . $coa->nama_akun . ' sudah ada. Silakan edit budget yang sudah ada.');
        }

        Bop::create([
            'coa_id' => $coa->id,
            'kode_akun' => $coa->kode_akun,
            'budget' => $validated['budget'],
            'aktual' => 0,
            'keterangan' => $validated['keterangan'] ?? null,
        ]);

        return redirect()->route('master-data.bop.index')
            ->with('success', 'Budget BOP untuk ' . $coa->nama_akun . ' berhasil ditambahkan');
    }

    // Rekalkulasi Beban Gaji (Perkiraan) untuk bulan tertentu
    public function recalc(Request $request)
    {
        $periodeInput = $request->input('periode'); // format expected: YYYY-MM
        $periode = $periodeInput ? Carbon::createFromFormat('Y-m', $periodeInput) : Carbon::now();

        // Hanya kumpulkan gaji BTKTL (tenaga kerja tidak langsung) untuk BOP
        $perkiraan = 0.0;
        foreach (Pegawai::all() as $p) {
            $jenis = strtolower($p->jenis_pegawai ?? '');
            if ($jenis !== 'btktl') { // selain BTKTL tidak masuk BOP
                continue;
            }
            $base = (float) ($p->gaji_pokok ?? 0);
            if ($base <= 0) { $base = (float) ($p->gaji ?? 0); }
            $tunj = (float) ($p->tunjangan ?? 0);
            $perkiraan += ($base + $tunj);
        }

        // COA sasaran: Beban Gaji BTKTL / BOP (prioritas kode 212)
        $coaBebanGaji = Coa::where('kode_akun', 212)
            ->orWhereRaw('LOWER(nama_akun) like ?', ['%btktl%'])
            ->orWhereRaw('LOWER(nama_akun) like ?', ['%tenaga kerja tidak langsung%'])
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
