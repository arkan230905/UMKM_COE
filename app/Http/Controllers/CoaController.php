<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Coa;
use App\Models\Bop;
use App\Models\CoaPeriod;
use App\Models\CoaPeriodBalance;

class CoaController extends Controller
{
    public function index(Request $request)
    {
        // Get periode yang dipilih atau periode saat ini
        $periodId = $request->get('period_id');
        $periode = null;
        
        if ($periodId) {
            $periode = CoaPeriod::find($periodId);
        }
        
        // Jika tidak ada periode dipilih, gunakan periode saat ini
        if (!$periode) {
            $periode = CoaPeriod::getCurrentPeriod();
        }
        
        // Get semua periode untuk dropdown
        $periods = CoaPeriod::orderBy('periode', 'desc')->get();
        
        // Get semua COA
        $coas = Coa::all();
        
        // Get saldo untuk setiap COA berdasarkan periode
        $saldoPeriode = [];
        foreach ($coas as $coa) {
            $saldoPeriode[$coa->kode_akun] = $this->getSaldoAwalPeriode($coa, $periode);
        }
        
        return view('master-data.coa.index', compact('coas', 'periode', 'periods', 'saldoPeriode'));
    }
    
    /**
     * Get saldo awal untuk periode tertentu
     */
    private function getSaldoAwalPeriode($coa, $periode)
    {
        // Cek apakah ada saldo periode
        $periodBalance = CoaPeriodBalance::where('kode_akun', $coa->kode_akun)
            ->where('period_id', $periode->id)
            ->first();
        
        if ($periodBalance) {
            return $periodBalance->saldo_awal;
        }
        
        // Jika tidak ada, cek periode sebelumnya
        $previousPeriod = $periode->getPreviousPeriod();
        if ($previousPeriod) {
            $previousBalance = CoaPeriodBalance::where('kode_akun', $coa->kode_akun)
                ->where('period_id', $previousPeriod->id)
                ->first();
            
            if ($previousBalance) {
                return $previousBalance->saldo_akhir;
            }
        }
        
        // Jika tidak ada periode sebelumnya, gunakan saldo awal dari COA
        return $coa->saldo_awal ?? 0;
    }

    public function create()
    {
        $coas = Coa::orderBy('kode_akun')->get(['kode_akun','nama_akun']);
        return view('master-data.coa.create', compact('coas'));
    }

    public function store(Request $request)
    {
        // Generate kode otomatis jika tipe akun diberikan
        if ($request->tipe_akun) {
            $maxKode = Coa::where('tipe_akun', $request->tipe_akun)->max('kode_akun');
            $request->merge([
                'kode_akun' => $maxKode ? $maxKode + 1 : $this->defaultKode($request->tipe_akun)
            ]);
        }

        $validated = $request->validate([
            'kode_akun' => 'required|unique:coas,kode_akun',
            'nama_akun' => 'required|string|max:255',
            'tipe_akun' => 'required|in:Asset,Liability,Equity,Revenue,Expense,Beban,Aset,Kewajiban,Ekuitas,Pendapatan',
            'kategori_akun' => 'nullable|string|max:255',
            'is_akun_header' => 'nullable|boolean',
            'kode_induk' => 'nullable|string|exists:coas,kode_akun',
            'saldo_normal' => 'nullable|in:debit,kredit',
            'saldo_awal' => 'nullable|numeric',
            'tanggal_saldo_awal' => 'nullable|date',
            'keterangan' => 'nullable|string',
            'posted_saldo_awal' => 'nullable|boolean',
        ]);

        // Pastikan semua nilai yang diperlukan ada
        $coaData = [
            'kode_akun' => $validated['kode_akun'],
            'nama_akun' => $validated['nama_akun'],
            'tipe_akun' => $validated['tipe_akun'],
            'kategori_akun' => $request->kategori_akun ?? $validated['tipe_akun'], // Default ke tipe_akun jika kategori_akun kosong
            'is_akun_header' => $request->boolean('is_akun_header') ? 1 : 0,
            'kode_induk' => $request->kode_induk,
            'saldo_normal' => $request->saldo_normal ?? 'debit', // Default ke debit jika kosong
            'saldo_awal' => $request->saldo_awal ?? 0,
            'keterangan' => $request->keterangan,
            'posted_saldo_awal' => $request->boolean('posted_saldo_awal') ? 1 : 0,
        ];
        
        // Hanya tambahkan tanggal_saldo_awal jika ada nilainya
        if ($request->has('tanggal_saldo_awal') && $request->tanggal_saldo_awal) {
            $coaData['tanggal_saldo_awal'] = $request->tanggal_saldo_awal;
        }
        
        // Buat COA dengan data yang sudah divalidasi
        $coa = Coa::create($coaData);

        // Otomatis tambahkan ke BOP jika tipe akun "Beban"
        if ($coa->tipe_akun === 'Beban') {
            Bop::create([
                'coa_id' => $coa->id,
                'keterangan' => 'Otomatis dari COA',
            ]);
        }

        return redirect()->route('master-data.coa.index')->with('success', 'COA berhasil ditambahkan.');
    }

    public function edit(Coa $coa)
    {
        $coas = Coa::orderBy('kode_akun')->get(['kode_akun','nama_akun']);
        return view('master-data.coa.edit', compact('coa','coas'));
    }

    public function update(Request $request, Coa $coa)
    {
        $validated = $request->validate([
            'kode_akun' => 'required|unique:coas,kode_akun,' . $coa->kode_akun . ',kode_akun',
            'nama_akun' => 'required|string|max:255',
            'tipe_akun' => 'required|in:Asset,Liability,Equity,Revenue,Expense,Beban,Aset,Kewajiban,Ekuitas,Pendapatan',
            'kategori_akun' => 'nullable|string|max:255',
            'is_akun_header' => 'nullable|boolean',
            'kode_induk' => 'nullable|string|exists:coas,kode_akun',
            'saldo_normal' => 'nullable|in:debit,kredit',
            'saldo_awal' => 'nullable|numeric',
            'tanggal_saldo_awal' => 'nullable|date',
            'keterangan' => 'nullable|string',
            'posted_saldo_awal' => 'nullable|boolean',
        ]);

        $coa->update([
            'kode_akun' => $validated['kode_akun'],
            'nama_akun' => $validated['nama_akun'],
            'tipe_akun' => $validated['tipe_akun'],
            'kategori_akun' => $request->kategori_akun,
            'is_akun_header' => $request->boolean('is_akun_header'),
            'kode_induk' => $request->kode_induk,
            'saldo_normal' => $request->saldo_normal,
            'saldo_awal' => $request->saldo_awal,
            'tanggal_saldo_awal' => $request->tanggal_saldo_awal,
            'keterangan' => $request->keterangan,
            'posted_saldo_awal' => $request->boolean('posted_saldo_awal'),
        ]);

        return redirect()->route('master-data.coa.index')->with('success', 'COA berhasil diperbarui.');
    }

    public function destroy(Coa $coa)
    {
        $coa->delete();
        return redirect()->route('master-data.coa.index')->with('success', 'COA berhasil dihapus.');
    }

    public function generateKode(Request $request)
    {
        $tipe = $request->tipe;
        $maxKode = Coa::where('tipe_akun', $tipe)->max('kode_akun');
        $kode = $maxKode ? $maxKode + 1 : $this->defaultKode($tipe);

        return response()->json(['kode_akun' => $kode]);
    }

    private function defaultKode($tipe)
    {
        return match($tipe) {
            'Asset' => 101,
            'Liability' => 201,
            'Equity' => 301,
            'Revenue' => 401,
            'Expense', 'Beban' => 501,
            default => 100,
        };
    }
}
