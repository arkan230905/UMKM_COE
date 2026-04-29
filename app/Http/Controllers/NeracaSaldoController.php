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
}