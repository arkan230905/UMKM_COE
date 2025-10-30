<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class AsetController extends Controller
{
    public function getKategoriByJenis(Request $request)
    {
        $jenisAset = $request->query('jenis_aset');
        
        $kategoriOptions = [
            'Aset Tetap' => [
                'Kendaraan Operasional',
                'Peralatan Kantor',
                'Peralatan Produksi',
                'Peralatan Medis',
                'Peralatan Laboratorium',
                'Peralatan Konstruksi',
                'Peralatan IT & Elektronik',
                'Furniture & Perlengkapan',
                'Peralatan Listrik',
                'Peralatan Mekanik',
                'Alat Berat',
                'Mesin Pabrik',
                'Gedung & Bangunan',
                'Tanah',
                'Rumah Dinas',
                'Kapal',
                'Pesawat Terbang',
                'Kereta Api',
                'Peralatan Telekomunikasi',
                'Peralatan Keamanan',
                'Peralatan Dapur',
                'Peralatan Kebersihan',
                'Peralatan Olahraga',
                'Peralatan Musik',
                'Peralatan Fotografi',
                'Peralatan Studio',
                'Peralatan Bengkel',
                'Peralatan Pertanian',
                'Peralatan Perkebunan',
                'Peralatan Peternakan',
                'Peralatan Perikanan',
                'Peralatan Kehutanan',
                'Peralatan Tambang',
                'Peralatan Makanan & Minuman',
                'Peralatan Kesehatan & Keselamatan'
            ],
            'Aset Tidak Tetap' => [
                'Persediaan Barang Dagang',
                'Bahan Baku',
                'Barang Dalam Proses',
                'Barang Jadi',
                'Konsinyasi',
                'Barang Promosi',
                'Perlengkapan Kantor',
                'Perlengkapan Kebersihan',
                'Perlengkapan Dapur',
                'Perlengkapan Maintenance',
                'Bahan Habis Pakai',
                'Bahan Kimia',
                'Suku Cadang',
                'Kemasan',
                'Barang Cetakan',
                'Alat Tulis Kantor',
                'Bahan Bakar & Pelumas',
                'Barang Konsinyasi',
                'Barang Sampel',
                'Barang Lain-lain'
            ],
            'Aset Tak Berwujud' => [
                'Hak Cipta (Copyright)',
                'Merek Dagang (Trademark)',
                'Paten',
                'Hak Desain Industri',
                'Rahasia Dagang',
                'Lisensi',
                'Franchise',
                'Hak Guna Bangunan',
                'Hak Pengusahaan Hutan',
                'Hak Pengusahaan Pertambangan',
                'Hak Pengusahaan Perairan',
                'Hak Pengusahaan Perkebunan',
                'Hak Pengusahaan Perikanan',
                'Hak Pengusahaan Peternakan',
                'Hak Pengusahaan Pariwisata',
                'Hak Pengusahaan Jasa',
                'Hak Pengusahaan Lainnya',
                'Goodwill',
                'Biaya Pendirian',
                'Biaya Penelitian & Pengembangan',
                'Biaya Pengembangan Software',
                'Biaya Lisensi Software',
                'Biaya Iklan & Promosi',
                'Biaya Pelatihan',
                'Biaya Perizinan',
                'Biaya Operasional Yang Ditangguhkan',
                'Biaya Pra Operasi',
                'Biaya Pendahuluan',
                'Biaya Pengalihan Hak'
            ]
        ];

        if ($jenisAset && array_key_exists($jenisAset, $kategoriOptions)) {
            return response()->json($kategoriOptions[$jenisAset]);
        }

        return response()->json([]);
    }
}
