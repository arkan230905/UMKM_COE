<?php

namespace App\Http\Controllers;

use App\Services\NeracaService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Barryvdh\DomPDF\Facade\Pdf;

class NeracaController extends Controller
{
    protected $neracaService;

    public function __construct(NeracaService $neracaService)
    {
        $this->neracaService = $neracaService;
    }

    /**
     * Display the Balance Sheet report
     */
    public function index(Request $request)
    {
        // Gunakan format bulan/tahun seperti neraca saldo untuk konsistensi
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
        
        // Hitung periode - sama seperti neraca saldo
        $tanggalAwal = \Carbon\Carbon::create($tahun, $bulan, 1)->format('Y-m-d');
        $tanggalAkhir = \Carbon\Carbon::create($tahun, $bulan, 1)->endOfMonth()->format('Y-m-d');

        $neraca = $this->neracaService->generateLaporanPosisiKeuangan($tanggalAwal, $tanggalAkhir);

        return view('laporan.neraca.index', compact('neraca', 'bulan', 'tahun'));
    }

    /**
     * Export Balance Sheet to PDF
     */
    public function exportPdf(Request $request)
    {
        $bulan = $request->get('bulan', date('m'));
        $tahun = $request->get('tahun', date('Y'));
        
        if ($bulan < 1 || $bulan > 12) {
            $bulan = date('m');
        }
        if ($tahun < 2020 || $tahun > 2030) {
            $tahun = date('Y');
        }
        
        $bulan = str_pad($bulan, 2, '0', STR_PAD_LEFT);
        
        $tanggalAwal = \Carbon\Carbon::create($tahun, $bulan, 1)->format('Y-m-d');
        $tanggalAkhir = \Carbon\Carbon::create($tahun, $bulan, 1)->endOfMonth()->format('Y-m-d');

        $neraca = $this->neracaService->generateLaporanPosisiKeuangan($tanggalAwal, $tanggalAkhir);

        $pdf = Pdf::loadView('laporan.neraca.pdf', compact('neraca'));
        
        $filename = 'Laporan_Posisi_Keuangan_' . $tahun . '-' . $bulan . '.pdf';
        
        return $pdf->download($filename);
    }

    /**
     * Export Balance Sheet to Excel
     */
    public function exportExcel(Request $request)
    {
        $bulan = $request->get('bulan', date('m'));
        $tahun = $request->get('tahun', date('Y'));
        
        if ($bulan < 1 || $bulan > 12) {
            $bulan = date('m');
        }
        if ($tahun < 2020 || $tahun > 2030) {
            $tahun = date('Y');
        }
        
        $bulan = str_pad($bulan, 2, '0', STR_PAD_LEFT);
        
        $tanggalAwal = \Carbon\Carbon::create($tahun, $bulan, 1)->format('Y-m-d');
        $tanggalAkhir = \Carbon\Carbon::create($tahun, $bulan, 1)->endOfMonth()->format('Y-m-d');

        $neraca = $this->neracaService->generateLaporanPosisiKeuangan($tanggalAwal, $tanggalAkhir);

        // Create CSV content
        $csvContent = $this->generateCsvContent($neraca);
        
        $filename = 'Laporan_Posisi_Keuangan_' . $tahun . '-' . $bulan . '.csv';
        
        return Response::make($csvContent, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }

    /**
     * Generate CSV content for Excel export
     */
    private function generateCsvContent($neraca)
    {
        $csv = [];
        
        // Header
        $csv[] = ['LAPORAN POSISI KEUANGAN (NERACA)'];
        $csv[] = ['Periode: ' . date('d/m/Y', strtotime($neraca['periode']['tanggal_awal'])) . ' - ' . date('d/m/Y', strtotime($neraca['periode']['tanggal_akhir']))];
        $csv[] = [''];
        
        // ASET
        $csv[] = ['ASET'];
        $csv[] = [''];
        
        // Aset Lancar
        $csv[] = ['ASET LANCAR'];
        foreach ($neraca['aset']['lancar'] as $item) {
            $csv[] = [$item['nama_akun'], number_format($item['saldo'], 0, ',', '.')];
        }
        $csv[] = ['Total Aset Lancar', number_format($neraca['aset']['total_lancar'], 0, ',', '.')];
        $csv[] = [''];
        
        // Aset Tidak Lancar
        $csv[] = ['ASET TIDAK LANCAR'];
        foreach ($neraca['aset']['tidak_lancar'] as $item) {
            $csv[] = [$item['nama_akun'], number_format($item['saldo'], 0, ',', '.')];
        }
        $csv[] = ['Total Aset Tidak Lancar', number_format($neraca['aset']['total_tidak_lancar'], 0, ',', '.')];
        $csv[] = [''];
        
        $csv[] = ['TOTAL ASET', number_format($neraca['aset']['total_aset'], 0, ',', '.')];
        $csv[] = [''];
        
        // KEWAJIBAN DAN EKUITAS
        $csv[] = ['KEWAJIBAN DAN EKUITAS'];
        $csv[] = [''];
        
        // Kewajiban
        $csv[] = ['KEWAJIBAN'];
        foreach ($neraca['kewajiban']['detail'] as $item) {
            $csv[] = [$item['nama_akun'], number_format($item['saldo'], 0, ',', '.')];
        }
        $csv[] = ['Total Kewajiban', number_format($neraca['kewajiban']['total'], 0, ',', '.')];
        $csv[] = [''];
        
        // Ekuitas
        $csv[] = ['EKUITAS'];
        foreach ($neraca['ekuitas']['detail'] as $item) {
            $csv[] = [$item['nama_akun'], number_format($item['saldo'], 0, ',', '.')];
        }
        $csv[] = ['Total Ekuitas', number_format($neraca['ekuitas']['total'], 0, ',', '.')];
        $csv[] = [''];
        
        $csv[] = ['TOTAL KEWAJIBAN DAN EKUITAS', number_format($neraca['total_kewajiban_ekuitas'], 0, ',', '.')];
        $csv[] = [''];
        
        // Balance Check
        $csv[] = ['STATUS NERACA', $neraca['neraca_seimbang'] ? 'SEIMBANG' : 'TIDAK SEIMBANG'];
        if (!$neraca['neraca_seimbang']) {
            $csv[] = ['Selisih', number_format($neraca['selisih'], 0, ',', '.')];
        }
        
        // Convert to CSV string
        $output = '';
        foreach ($csv as $row) {
            $output .= implode(',', array_map(function($field) {
                return '"' . str_replace('"', '""', $field) . '"';
            }, $row)) . "\n";
        }
        
        return $output;
    }
}