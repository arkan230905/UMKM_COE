<?php

namespace App\Exports;

use App\Models\Account;
use App\Models\JournalLine;
use App\Models\Coa;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class BukuBesarExport implements WithMultipleSheets
{
    protected $from;
    protected $to;

    public function __construct($from = null, $to = null)
    {
        $this->from = $from;
        $this->to = $to;
    }

    public function sheets(): array
    {
        $sheets = [];
        
        // Get all accounts
        $accounts = Account::orderBy('code')->get();
        
        foreach ($accounts as $account) {
            // Ambil saldo awal dari COA
            $coa = Coa::where('kode_akun', $account->code)->first();
            $saldoAwalCoa = $coa ? (float)($coa->saldo_awal ?? 0) : 0;
            
            // Hitung mutasi sebelum periode
            $mutasiSebelumPeriode = 0.0;
            if ($this->from) {
                $mutasiSebelumPeriode = JournalLine::where('account_id', $account->id)
                    ->whereHas('entry', function($qq) {
                        $qq->whereDate('tanggal', '<', $this->from);
                    })
                    ->selectRaw('COALESCE(SUM(debit - credit),0) as sal')
                    ->value('sal') ?? 0;
            }
            
            // Saldo awal = saldo awal COA + mutasi sebelum periode
            $saldoAwal = $saldoAwalCoa + $mutasiSebelumPeriode;
            
            // Ambil transaksi dalam periode
            $q = JournalLine::with(['entry'])
                ->where('account_id', $account->id)
                ->orderBy('id', 'asc');
            
            if ($this->from) {
                $q->whereHas('entry', function($qq) {
                    $qq->whereDate('tanggal', '>=', $this->from);
                });
            }
            if ($this->to) {
                $q->whereHas('entry', function($qq) {
                    $qq->whereDate('tanggal', '<=', $this->to);
                });
            }
            
            $lines = $q->get();
            
            // Skip akun yang tidak ada transaksi dan saldo awal 0
            if ($lines->isEmpty() && $saldoAwal == 0) {
                continue;
            }
            
            // Tambahkan sheet untuk akun ini
            $sheets[] = new BukuBesarSheetExport($account, $lines, $saldoAwal, $this->from, $this->to);
        }
        
        return $sheets;
    }
}

// Class untuk setiap sheet (per akun)
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class BukuBesarSheetExport implements FromCollection, WithHeadings, WithStyles, WithTitle, WithColumnWidths
{
    protected $account;
    protected $lines;
    protected $saldoAwal;
    protected $from;
    protected $to;

    public function __construct($account, $lines, $saldoAwal, $from, $to)
    {
        $this->account = $account;
        $this->lines = $lines;
        $this->saldoAwal = $saldoAwal;
        $this->from = $from;
        $this->to = $to;
    }

    public function collection()
    {
        $data = collect();
        
        // Saldo awal
        $data->push([
            'tanggal' => '',
            'ref_type' => '',
            'ref_id' => '',
            'keterangan' => 'Saldo Awal',
            'debit' => '',
            'kredit' => '',
            'saldo' => $this->saldoAwal
        ]);
        
        // Transaksi
        $saldo = $this->saldoAwal;
        foreach ($this->lines as $line) {
            $saldo += $line->debit - $line->credit;
            
            $data->push([
                'tanggal' => date('d/m/Y', strtotime($line->entry->tanggal)),
                'ref_type' => $line->entry->ref_type ?? '',
                'ref_id' => $line->entry->ref_id ?? '',
                'keterangan' => $line->entry->memo ?? '',
                'debit' => $line->debit > 0 ? $line->debit : '',
                'kredit' => $line->credit > 0 ? $line->credit : '',
                'saldo' => $saldo
            ]);
        }
        
        // Saldo akhir
        $data->push([
            'tanggal' => '',
            'ref_type' => '',
            'ref_id' => '',
            'keterangan' => 'Saldo Akhir',
            'debit' => '',
            'kredit' => '',
            'saldo' => $saldo
        ]);
        
        return $data;
    }

    public function headings(): array
    {
        return [
            'Tanggal',
            'Ref Type',
            'Ref ID',
            'Keterangan',
            'Debit',
            'Kredit',
            'Saldo'
        ];
    }

    public function styles(Worksheet $sheet)
    {
        // Style header
        $sheet->getStyle('A1:G1')->applyFromArray([
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
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                ]
            ]
        ]);
        
        // Format angka untuk kolom Debit, Kredit, Saldo
        $rowCount = $this->collection()->count() + 1;
        $sheet->getStyle('E2:G' . $rowCount)->getNumberFormat()->setFormatCode('#,##0');
        
        // Bold untuk saldo awal dan akhir
        $sheet->getStyle('A2:G2')->getFont()->setBold(true);
        $sheet->getStyle('A' . $rowCount . ':G' . $rowCount)->getFont()->setBold(true);
        
        // Background untuk saldo akhir
        $sheet->getStyle('A' . $rowCount . ':G' . $rowCount)->applyFromArray([
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'E7E6E6'],
            ],
        ]);
        
        return $sheet;
    }

    public function title(): string
    {
        // Nama sheet (max 31 karakter)
        $title = $this->account->code . ' ' . $this->account->name;
        return substr($title, 0, 31);
    }

    public function columnWidths(): array
    {
        return [
            'A' => 12,
            'B' => 15,
            'C' => 10,
            'D' => 40,
            'E' => 15,
            'F' => 15,
            'G' => 15,
        ];
    }
}
