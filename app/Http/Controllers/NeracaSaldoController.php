<?php

namespace App\Http\Controllers;

use App\Services\TrialBalanceService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class NeracaSaldoController extends Controller
{
    protected $trialBalanceService;

    public function __construct(TrialBalanceService $trialBalanceService)
    {
        $this->trialBalanceService = $trialBalanceService;
    }

    /**
     * Tampilkan Neraca Saldo berdasarkan data Buku Besar
     * 
     * Logika: Neraca saldo adalah ringkasan saldo akhir semua akun dari buku besar,
     * bukan input manual. Setiap transaksi sudah diposting ke journal_lines (buku besar).
     */
    public function index(Request $request)
    {
        // Debug log
        \Log::info('Neraca Saldo index method called', [
            'user_id' => auth()->id(),
            'user_role' => auth()->user()->role ?? 'no_role',
            'request_params' => $request->all()
        ]);

        // Validasi input dengan default values
        $bulan = $request->get('bulan', date('m'));
        $tahun = $request->get('tahun', date('Y'));

        // Validasi range
        if ($bulan < 1 || $bulan > 12) {
            $bulan = date('m');
        }
        if ($tahun < 2020 || $tahun > 2030) {
            $tahun = date('Y');
        }

        // Ensure bulan is zero-padded
        $bulan = str_pad($bulan, 2, '0', STR_PAD_LEFT);

        // Hitung periode
        $startDate = Carbon::create($tahun, $bulan, 1)->format('Y-m-d');
        $endDate = Carbon::create($tahun, $bulan, 1)->endOfMonth()->format('Y-m-d');

        try {
            // Ambil data neraca saldo dari service
            $neracaSaldoData = $this->trialBalanceService->calculateTrialBalance($startDate, $endDate);

            // Log untuk audit trail
            \Log::info('Neraca Saldo diakses', [
                'user_id' => auth()->id(),
                'periode' => "{$tahun}-{$bulan}",
                'total_accounts' => count($neracaSaldoData['accounts']),
                'is_balanced' => $neracaSaldoData['is_balanced']
            ]);

            return view('akuntansi.neraca-saldo-new', compact(
                'neracaSaldoData', 
                'bulan', 
                'tahun',
                'startDate',
                'endDate'
            ));

        } catch (\Exception $e) {
            \Log::error('Error calculating trial balance', [
                'user_id' => auth()->id(),
                'periode' => "{$tahun}-{$bulan}",
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            // Return to dashboard with error message instead of throwing exception
            return redirect()->route('dashboard')->with('error', 'Terjadi kesalahan saat menghitung neraca saldo: ' . $e->getMessage() . '. Silakan hubungi administrator.');
        }
    }

    /**
     * Export PDF Neraca Saldo
     */
    public function exportPdf(Request $request)
    {
        // Validasi input dengan default values
        $bulan = $request->get('bulan', date('m'));
        $tahun = $request->get('tahun', date('Y'));

        // Validasi range
        if ($bulan < 1 || $bulan > 12) {
            $bulan = date('m');
        }
        if ($tahun < 2020 || $tahun > 2030) {
            $tahun = date('Y');
        }

        $startDate = Carbon::create($tahun, $bulan, 1)->format('Y-m-d');
        $endDate = Carbon::create($tahun, $bulan, 1)->endOfMonth()->format('Y-m-d');

        try {
            // Ambil data neraca saldo dari service
            $neracaSaldoData = $this->trialBalanceService->calculateTrialBalance($startDate, $endDate);

            // Log untuk audit trail
            \Log::info('Neraca Saldo PDF diunduh', [
                'user_id' => auth()->id(),
                'periode' => "{$tahun}-{$bulan}",
                'total_accounts' => count($neracaSaldoData['accounts'])
            ]);

            // Generate PDF
            $pdf = \PDF::loadView('akuntansi.neraca-saldo-pdf-new', compact(
                'neracaSaldoData', 
                'bulan', 
                'tahun'
            ));

            $filename = "neraca-saldo-{$tahun}-{$bulan}.pdf";
            
            return $pdf->download($filename);

        } catch (\Exception $e) {
            \Log::error('Error generating trial balance PDF', [
                'user_id' => auth()->id(),
                'periode' => "{$tahun}-{$bulan}",
                'error' => $e->getMessage()
            ]);

            return back()->with('error', 'Terjadi kesalahan saat membuat PDF. Silakan coba lagi.');
        }
    }

    /**
     * API endpoint untuk mendapatkan data neraca saldo (untuk AJAX)
     */
    public function apiData(Request $request)
    {
        // Validasi input dengan default values
        $bulan = $request->get('bulan', date('m'));
        $tahun = $request->get('tahun', date('Y'));

        // Validasi range
        if ($bulan < 1 || $bulan > 12) {
            $bulan = date('m');
        }
        if ($tahun < 2020 || $tahun > 2030) {
            $tahun = date('Y');
        }

        $startDate = Carbon::create($tahun, $bulan, 1)->format('Y-m-d');
        $endDate = Carbon::create($tahun, $bulan, 1)->endOfMonth()->format('Y-m-d');

        try {
            $neracaSaldoData = $this->trialBalanceService->calculateTrialBalance($startDate, $endDate);
            
            return response()->json([
                'success' => true,
                'data' => $neracaSaldoData,
                'message' => 'Data berhasil diambil'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat mengambil data',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // REMOVED: createOpeningBalanceJournal method dihapus sesuai permintaan user
    // User tidak ingin jurnal penyeimbang otomatis

    /**
     * Posting saldo akhir bulan ini sebagai saldo awal bulan berikutnya
     * di tabel coa_period_balances
     */
    public function postingSaldo(Request $request)
    {
        $bulan = str_pad($request->get('bulan', date('m')), 2, '0', STR_PAD_LEFT);
        $tahun = $request->get('tahun', date('Y'));

        $startDate = Carbon::create($tahun, $bulan, 1)->format('Y-m-d');
        $endDate   = Carbon::create($tahun, $bulan, 1)->endOfMonth()->format('Y-m-d');

        // Bulan berikutnya
        $nextMonth = Carbon::create($tahun, $bulan, 1)->addMonth();
        $nextBulan = $nextMonth->format('m');
        $nextTahun = $nextMonth->format('Y');
        $nextPeriode = $nextTahun . '-' . $nextBulan;
        $currentPeriodeStr = "{$tahun}-{$bulan}";

        // 1. Guard anti-posting dobel (cek apakah periode berjalan sudah di-close)
        $currentPeriod = \App\Models\CoaPeriod::where('periode', $currentPeriodeStr)->first();
        if ($currentPeriod && $currentPeriod->is_closed) {
            return redirect()
                ->route('akuntansi.neraca-saldo-temp', ['bulan' => $bulan, 'tahun' => $tahun])
                ->with('error', 'Periode ini sudah pernah diposting sebelumnya.');
        }

        try {
            \DB::beginTransaction();

            // Hitung neraca saldo bulan ini
            $neracaSaldoData = $this->trialBalanceService->calculateTrialBalance($startDate, $endDate);

            // Pastikan periode bulan berikutnya ada di coa_periods
            $periode = \App\Models\CoaPeriod::firstOrCreate(
                ['periode' => $nextPeriode],
                [
                    'tanggal_mulai'   => $nextMonth->copy()->startOfMonth()->format('Y-m-d'),
                    'tanggal_selesai' => $nextMonth->copy()->endOfMonth()->format('Y-m-d'),
                    'is_closed'       => false,
                    'created_at'      => now(),
                    'updated_at'      => now(),
                ]
            );

            $posted = 0;
            foreach ($neracaSaldoData['accounts'] as $account) {
                if (empty($account['kode_akun'])) continue;

                $saldoAkhir = $account['saldo_akhir'] ?? 0;

                $updateData = [
                    'saldo_awal' => $saldoAkhir,
                    'saldo_akhir'=> $saldoAkhir,
                    'updated_at' => now(),
                    'created_at' => now(),
                ];

                // Simpan/update sebagai saldo_awal bulan berikutnya di coa_period_balances
                \App\Models\CoaPeriodBalance::updateOrCreate(
                    [
                        'user_id'    => auth()->id(),
                        'period_id'  => $periode->id,
                        'kode_akun'  => $account['kode_akun'],
                    ],
                    $updateData
                );

                // HAPUS UPDATE COA saldo_awal agar tidak menimpa saldo awal original
                $posted++;
            }

            // 2. Set is_closed = true pada record CoaPeriod untuk bulan yang sedang diposting
            if ($currentPeriod) {
                $currentPeriod->update(['is_closed' => true]);
            } else {
                \App\Models\CoaPeriod::where('periode', $currentPeriodeStr)->update(['is_closed' => true]);
            }

            \DB::commit();

            \Log::info('Posting saldo berhasil', [
                'user_id'    => auth()->id(),
                'dari_bulan' => "{$tahun}-{$bulan}",
                'ke_bulan'   => $nextPeriode,
                'total_akun' => $posted,
            ]);

            return redirect()
                ->route('akuntansi.neraca-saldo-temp', ['bulan' => $bulan, 'tahun' => $tahun])
                ->with('success', "✅ Posting berhasil! Saldo akhir {$bulan}/{$tahun} sudah menjadi saldo awal {$nextBulan}/{$nextTahun} untuk {$posted} akun.");

        } catch (\Exception $e) {
            \DB::rollBack();
            \Log::error('Posting saldo gagal: ' . $e->getMessage());
            return redirect()
                ->route('akuntansi.neraca-saldo-temp', ['bulan' => $bulan, 'tahun' => $tahun])
                ->with('error', 'Posting gagal: ' . $e->getMessage());
        }
    }
}