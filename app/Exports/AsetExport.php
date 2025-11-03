<?php

namespace App\Exports;

use App\Models\Aset;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class AsetExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize
{
    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        return Aset::all();
    }

    public function headings(): array
    {
        return [
            'Kode Aset',
            'Nama Aset',
            'Kategori',
            'Tanggal Perolehan',
            'Harga Perolehan',
            'Nilai Sisa',
            'Umur Ekonomis (Tahun)',
            'Metode Penyusutan',
            'Lokasi',
            'Nomor Serial',
            'Status',
            'Nilai Buku',
            'Akumulasi Penyusutan',
            'Keterangan',
        ];
    }

    public function map($aset): array
    {
        return [
            $aset->kode_aset,
            $aset->nama_aset,
            $aset->kategori,
            $aset->tanggal_perolehan->format('Y-m-d'),
            $aset->harga_perolehan,
            $aset->nilai_sisa,
            $aset->umur_ekonomis_tahun,
            $aset->metode_penyusutan,
            $aset->lokasi,
            $aset->nomor_serial,
            $aset->status,
            $aset->nilai_buku,
            $aset->akumulasi_penyusutan,
            $aset->keterangan,
        ];
    }
}
