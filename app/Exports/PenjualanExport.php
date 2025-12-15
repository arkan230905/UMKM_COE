<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

class PenjualanExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithEvents
{
    protected Collection $penjualan;
    protected int $rowNumber = 0;

    public function __construct(Collection $penjualan)
    {
        $this->penjualan = $penjualan;
    }

    public function collection(): Collection
    {
        return $this->penjualan;
    }

    public function headings(): array
    {
        return ['No', 'No. Transaksi', 'Tanggal', 'Produk', 'Pembayaran', 'Total (Rp)'];
    }

    public function map($item): array
    {
        $this->rowNumber++;

        $produk = '-';
        if ($item->relationLoaded('details') && $item->details->count() > 0) {
            $produk = $item->details->map(function ($detail) {
                $qty = $detail->jumlah ?? 0;
                $qtyFormatted = rtrim(rtrim(number_format($qty, 2, ',', '.'), '0'), ',');
                $name = $detail->produk->nama_produk ?? 'Produk';

                return $qtyFormatted
                    ? sprintf('%s (x %s)', $name, $qtyFormatted)
                    : $name;
            })->implode(', ');
        } elseif ($item->relationLoaded('produk') && $item->produk) {
            $qty = $item->jumlah ?? 0;
            $qtyFormatted = rtrim(rtrim(number_format($qty, 2, ',', '.'), '0'), ',');
            $produk = $qty
                ? sprintf('%s (x %s)', $item->produk->nama_produk ?? '-', $qtyFormatted)
                : ($item->produk->nama_produk ?? '-');
        }

        $payment = match ($item->payment_method) {
            'cash' => 'Tunai',
            'transfer' => 'Transfer',
            'credit' => 'Kredit',
            default => ($item->payment_method ? ucfirst($item->payment_method) : '-')
        };

        $tanggal = $item->tanggal ? $item->tanggal->format('d/m/Y') : '-';

        return [
            $this->rowNumber,
            $item->nomor_penjualan ?? '-',
            $tanggal,
            $produk,
            $payment,
            (float) ($item->total ?? 0),
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $rowCount = $this->penjualan->count() + 1; // include header
                $totalRow = $rowCount + 1;

                $sheet = $event->sheet->getDelegate();

                // Header styling
                $sheet->getStyle('A1:F1')->getFont()->setBold(true);
                $sheet->getStyle('A1:F1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

                // Body styling
                $sheet->getStyle('D2:D' . $rowCount)->getAlignment()->setWrapText(true);
                $sheet->getStyle('A1:F' . $totalRow)->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);

                // Number formats for totals
                $sheet->getStyle('F2:F' . $rowCount)
                    ->getNumberFormat()
                    ->setFormatCode('#,##0');

                // Append total row
                $sheet->setCellValue('E' . $totalRow, 'TOTAL');
                $sheet->setCellValue('F' . $totalRow, $this->penjualan->sum('total'));

                $sheet->getStyle('E' . $totalRow . ':F' . $totalRow)->getFont()->setBold(true);
                $sheet->getStyle('E' . $totalRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
                $sheet->getStyle('F' . $totalRow)
                    ->getNumberFormat()
                    ->setFormatCode('#,##0');
            },
        ];
    }
}
