<?php

namespace App\Exports;

use App\Models\JournalEntry;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class JurnalUmumExport
{
    protected $from;
    protected $to;
    protected $refType;
    protected $refId;

    public function __construct($from = null, $to = null, $refType = null, $refId = null)
    {
        $this->from = $from;
        $this->to = $to;
        $this->refType = $refType;
        $this->refId = $refId;
    }

    public function download($filename = 'jurnal-umum.csv')
    {
        // Generate CSV instead of Excel (no PhpSpreadsheet needed)
        $this->downloadCSV($filename);
        return;
    }
    
    private function downloadCSV($filename)
    {
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Jurnal Umum');
        
        // Set judul
        $sheet->setCellValue('A1', 'JURNAL UMUM');
        $sheet->mergeCells('A1:I1');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        
        // Set periode jika ada
        $startRow = 3;
        if ($this->from || $this->to) {
            $periode = 'Periode: ';
            if ($this->from) $periode .= date('d/m/Y', strtotime($this->from));
            if ($this->from && $this->to) $periode .= ' s/d ';
            if ($this->to) $periode .= date('d/m/Y', strtotime($this->to));
            
            $sheet->setCellValue('A2', $periode);
            $sheet->mergeCells('A2:I2');
            $sheet->getStyle('A2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $startRow = 4;
        }
        
        // Set header
        $headers = ['Tanggal', 'Ref Type', 'Ref ID', 'Memo', 'Kode Akun', 'Nama Akun', 'Tipe Akun', 'Debit', 'Kredit'];
        $col = 'A';
        foreach ($headers as $header) {
            $sheet->setCellValue($col . $startRow, $header);
            $col++;
        }
        
        // Style header
        $sheet->getStyle('A' . $startRow . ':I' . $startRow)->applyFromArray([
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
        
        // Get data
        $query = JournalEntry::with(['lines.account'])->orderBy('tanggal','asc')->orderBy('id','asc');
        
        if ($this->from) { 
            $query->whereDate('tanggal', '>=', $this->from); 
        }
        if ($this->to) { 
            $query->whereDate('tanggal', '<=', $this->to); 
        }
        if ($this->refType) { 
            $query->where('ref_type', $this->refType); 
        }
        if ($this->refId) { 
            $query->where('ref_id', $this->refId); 
        }
        
        $entries = $query->get();
        
        // Fill data
        $row = $startRow + 1;
        $totalDebit = 0;
        $totalCredit = 0;
        
        foreach ($entries as $entry) {
            foreach ($entry->lines as $line) {
                $sheet->setCellValue('A' . $row, date('d/m/Y', strtotime($entry->tanggal)));
                $sheet->setCellValue('B' . $row, $entry->ref_type);
                $sheet->setCellValue('C' . $row, $entry->ref_id);
                $sheet->setCellValue('D' . $row, $entry->memo);
                $sheet->setCellValue('E' . $row, $line->account->code ?? '-');
                $sheet->setCellValue('F' . $row, $line->account->name ?? 'Akun tidak ditemukan');
                $sheet->setCellValue('G' . $row, $line->account->type ?? '-');
                $sheet->setCellValue('H' . $row, $line->debit > 0 ? $line->debit : 0);
                $sheet->setCellValue('I' . $row, $line->credit > 0 ? $line->credit : 0);
                
                $totalDebit += $line->debit;
                $totalCredit += $line->credit;
                
                // Format angka
                $sheet->getStyle('H' . $row)->getNumberFormat()->setFormatCode('#,##0');
                $sheet->getStyle('I' . $row)->getNumberFormat()->setFormatCode('#,##0');
                
                $row++;
            }
        }
        
        // Add total row
        if ($row > $startRow + 1) {
            $totalRow = $row;
            $sheet->setCellValue('A' . $totalRow, 'TOTAL');
            $sheet->mergeCells('A' . $totalRow . ':G' . $totalRow);
            $sheet->setCellValue('H' . $totalRow, $totalDebit);
            $sheet->setCellValue('I' . $totalRow, $totalCredit);
            
            $sheet->getStyle('A' . $totalRow . ':I' . $totalRow)->applyFromArray([
                'font' => ['bold' => true],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'E7E6E6'],
                ],
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_THIN,
                    ]
                ]
            ]);
            
            $sheet->getStyle('H' . $totalRow . ':I' . $totalRow)->getNumberFormat()->setFormatCode('#,##0');
        }
        
        // Set column widths
        $sheet->getColumnDimension('A')->setWidth(12);
        $sheet->getColumnDimension('B')->setWidth(15);
        $sheet->getColumnDimension('C')->setWidth(10);
        $sheet->getColumnDimension('D')->setWidth(40);
        $sheet->getColumnDimension('E')->setWidth(12);
        $sheet->getColumnDimension('F')->setWidth(30);
        $sheet->getColumnDimension('G')->setWidth(12);
        $sheet->getColumnDimension('H')->setWidth(15);
        $sheet->getColumnDimension('I')->setWidth(15);
        
        // Set borders untuk data
        if ($row > $startRow + 1) {
            $sheet->getStyle('A' . $startRow . ':I' . ($row - 1))->applyFromArray([
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_THIN,
                    ]
                ]
            ]);
        }
        
        // Create writer and download
        $writer = new Xlsx($spreadsheet);
        
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');
        
        $writer->save('php://output');
        exit;
    }
}
