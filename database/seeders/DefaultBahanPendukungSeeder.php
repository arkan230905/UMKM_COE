<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\BahanPendukung;
use App\Models\Satuan;
use App\Models\Coa;

class DefaultBahanPendukungSeeder extends Seeder
{
    /**
     * Seed default bahan pendukung with COA mapping for new user
     */
    public function run(int $userId): void
    {
        // Get satuan IDs for this user
        $liter = Satuan::where('nama', 'Liter')->where('user_id', $userId)->first();
        $kg = Satuan::where('nama', 'Kilogram')->where('user_id', $userId)->first();
        $pcs = Satuan::where('nama', 'Pcs')->where('user_id', $userId)->first();
        
        if (!$liter || !$kg || !$pcs) {
            \Log::warning("Required satuan not found for user {$userId}, skipping bahan pendukung seeding");
            return;
        }

        // Get COA codes for this user
        $coaAir = Coa::where('kode_akun', '1150')->where('user_id', $userId)->first();
        $coaMinyak = Coa::where('kode_akun', '1151')->where('user_id', $userId)->first();
        $coaTepungTerigu = Coa::where('kode_akun', '1152')->where('user_id', $userId)->first();
        $coaTepungMaizena = Coa::where('kode_akun', '1153')->where('user_id', $userId)->first();
        $coaLada = Coa::where('kode_akun', '1154')->where('user_id', $userId)->first();
        $coaKaldu = Coa::where('kode_akun', '1155')->where('user_id', $userId)->first();
        $coaBawangPutih = Coa::where('kode_akun', '1156')->where('user_id', $userId)->first();
        $coaKemasan = Coa::where('kode_akun', '1157')->where('user_id', $userId)->first();

        $items = [
            [
                'nama' => 'Air Galon',
                'kode' => 'BP-AIR',
                'satuan' => $liter,
                'harga' => 500,
                'coa' => $coaAir ? $coaAir->kode_akun : '1150',
                'deskripsi' => 'Air untuk proses produksi',
            ],
            [
                'nama' => 'Minyak Goreng',
                'kode' => 'BP-MINYAK',
                'satuan' => $liter,
                'harga' => 15000,
                'coa' => $coaMinyak ? $coaMinyak->kode_akun : '1151',
                'deskripsi' => 'Minyak goreng untuk memasak',
            ],
            [
                'nama' => 'Tepung Terigu',
                'kode' => 'BP-TERIGU',
                'satuan' => $kg,
                'harga' => 12000,
                'coa' => $coaTepungTerigu ? $coaTepungTerigu->kode_akun : '1152',
                'deskripsi' => 'Tepung terigu untuk adonan',
            ],
            [
                'nama' => 'Tepung Maizena',
                'kode' => 'BP-MAIZENA',
                'satuan' => $kg,
                'harga' => 18000,
                'coa' => $coaTepungMaizena ? $coaTepungMaizena->kode_akun : '1153',
                'deskripsi' => 'Tepung maizena untuk pengental',
            ],
            [
                'nama' => 'Lada',
                'kode' => 'BP-LADA',
                'satuan' => $kg,
                'harga' => 80000,
                'coa' => $coaLada ? $coaLada->kode_akun : '1154',
                'deskripsi' => 'Lada bubuk untuk bumbu',
            ],
            [
                'nama' => 'Bubuk Kaldu Ayam',
                'kode' => 'BP-KALDU',
                'satuan' => $kg,
                'harga' => 50000,
                'coa' => $coaKaldu ? $coaKaldu->kode_akun : '1155',
                'deskripsi' => 'Bubuk kaldu untuk penyedap',
            ],
            [
                'nama' => 'Bubuk Bawang Putih',
                'kode' => 'BP-BAWANG',
                'satuan' => $kg,
                'harga' => 60000,
                'coa' => $coaBawangPutih ? $coaBawangPutih->kode_akun : '1156',
                'deskripsi' => 'Bubuk bawang putih untuk bumbu',
            ],
            [
                'nama' => 'Kemasan Makanan',
                'kode' => 'BP-KEMASAN',
                'satuan' => $pcs,
                'harga' => 1000,
                'coa' => $coaKemasan ? $coaKemasan->kode_akun : '1157',
                'deskripsi' => 'Kemasan untuk produk jadi',
            ],
        ];

        foreach ($items as $item) {
            BahanPendukung::create([
                'user_id' => $userId,
                'nama_bahan' => $item['nama'],
                'kode_bahan' => $item['kode'],
                'satuan_id' => $item['satuan']->id,
                'harga_satuan' => $item['harga'],
                'stok' => 0,
                'stok_minimum' => 10,
                'deskripsi' => $item['deskripsi'],
                'coa_persediaan_id' => $item['coa'],
            ]);
        }

        \Log::info("Default bahan pendukung seeded for user {$userId}", [
            'bahan_pendukung_count' => count($items),
            'with_coa' => true
        ]);
    }
}
