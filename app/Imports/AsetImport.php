<?php

namespace App\Imports;

use App\Models\Aset;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;

class AsetImport implements ToModel, WithHeadingRow, WithValidation
{
    /**
     * @param array $row
     *
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function model(array $row)
    {
        return new Aset([
            'kode_aset' => $row['kode_aset'] ?? Aset::generateKodeAset(),
            'nama_aset' => $row['nama_aset'],
            'kategori' => $row['kategori'],
            'tanggal_perolehan' => $row['tanggal_perolehan'],
            'harga_perolehan' => $row['harga_perolehan'],
            'nilai_sisa' => $row['nilai_sisa'],
            'umur_ekonomis_tahun' => $row['umur_ekonomis_tahun'],
            'metode_penyusutan' => $row['metode_penyusutan'] ?? 'garis_lurus',
            'lokasi' => $row['lokasi'] ?? null,
            'nomor_serial' => $row['nomor_serial'] ?? null,
            'status' => $row['status'] ?? 'aktif',
            'keterangan' => $row['keterangan'] ?? null,
        ]);
    }

    public function rules(): array
    {
        return [
            'nama_aset' => 'required|string',
            'kategori' => 'required|string',
            'tanggal_perolehan' => 'required|date',
            'harga_perolehan' => 'required|numeric|min:0',
            'nilai_sisa' => 'required|numeric|min:0',
            'umur_ekonomis_tahun' => 'required|integer|min:1',
            'metode_penyusutan' => 'in:garis_lurus,saldo_menurun,sum_of_years_digits',
            'status' => 'in:aktif,tidak_aktif,dihapus',
        ];
    }
}
