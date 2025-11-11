<?php

namespace App\Exports;

use App\Models\Coa;
use App\Models\JournalLine;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class LaporanKasBankExport implements FromCollection, WithHeadings, WithStyles, WithTitle, WithColumnWidths
{
    protected $startDate;
    protected $endDate;

    public function __construct($startDate, $endDate)
    {
        $this->startDate = $startDate;
        $this->endDate = $endDate;
    }

    public function collection()
    {
        // Get HANYA akun Kas dan Bank menggunakan helper untuk konsistensi
        $akunKasBank = \App\Helpers\AccountHelper::getKasBankAccounts();
        
        $data = collect();
        
        foreach ($akunKasBank as $akun) {
            $saldoAwal = $this->getSaldoAwal($akun);
            $transaksiMasuk = $this->getTransaksiMasuk($akun);
            $transaksiKeluar = $this->getTransaksiKeluar($akun);
            $saldoAkhir = $saldoAwal + $transaksiMasuk - $transaksiKeluar;
            
            $data->push([
                'kode_akun' => $akun->kode_akun,
                'nama_akun' => $akun->nama_akun,
                'saldo_awal' => $saldoAwal,
                'transaksi_masuk' => $transaksiMasuk,
                'transaksi_keluar' => $transaksiKeluar,
                'saldo_akhir' => $saldoAkhir,
            ]);
        }
        
        return $data;
    }

    public function headings(): array
    {
        return [
            'Kode Akun',
            'Nama Akun',
            'Saldo Awal',
            'Transaksi Masuk',
            'Transaksi Keluar',
            'Saldo Akhir'
        ];
    }

    public function styles(Worksheet $sheet)
    {
        // Style header row
        $sheet->getStyle('A1:F1')->applyFromArray([
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF'],
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '4472C4']
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                ]
            ]
        ]);
        
        // Style data rows
        $rowCount = $this->collection()->count() + 1;
        $sheet->getStyle('A2:F' . $rowCount)->applyFromArray([
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                ]
            ]
        ]);
        
        // Format number columns
        $sheet->getStyle('C2:F' . $rowCount)->getNumberFormat()->setFormatCode('#,##0');
        
        return $sheet;
    }

    public function title(): string
    {
        return 'Laporan Kas Bank';
    }

    public function columnWidths(): array
    {
        return [
            'A' => 12,
            'B' => 30,
            'C' => 15,
            'D' => 15,
            'E' => 15,
            'F' => 15,
        ];
    }

    private function getSaldoAwal($akun)
    {
        $saldoAwalCoa = $akun->saldo_awal ?? 0;
        $account = DB::table('accounts')->where('code', $akun->kode_akun)->first();
        
        if (!$account) {
            return $saldoAwalCoa;
        }
        
        $mutasiSebelumPeriode = JournalLine::where('account_id', $account->id)
            ->whereHas('entry', function($query) {
                $query->where('tanggal', '<', $this->startDate);
            })
            ->selectRaw('SUM(debit) as total_debit, SUM(credit) as total_credit')
            ->first();
        
        $totalDebit = $mutasiSebelumPeriode->total_debit ?? 0;
        $totalCredit = $mutasiSebelumPeriode->total_credit ?? 0;
        
        return $saldoAwalCoa + $totalDebit - $totalCredit;
    }

    private function getTransaksiMasuk($akun)
    {
        $account = DB::table('accounts')->where('code', $akun->kode_akun)->first();
        
        if (!$account) {
            return 0;
        }
        
        return JournalLine::where('account_id', $account->id)
            ->whereHas('entry', function($query) {
                $query->whereBetween('tanggal', [$this->startDate, $this->endDate]);
            })
            ->sum('debit') ?? 0;
    }

    private function getTransaksiKeluar($akun)
    {
        $account = DB::table('accounts')->where('code', $akun->kode_akun)->first();
        
        if (!$account) {
            return 0;
        }
        
        return JournalLine::where('account_id', $account->id)
            ->whereHas('entry', function($query) {
                $query->whereBetween('tanggal', [$this->startDate, $this->endDate]);
            })
            ->sum('credit') ?? 0;
    }
}
