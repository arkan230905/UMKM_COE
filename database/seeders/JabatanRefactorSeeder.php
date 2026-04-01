<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Jabatan;
use App\Models\KategoriPegawai;

class JabatanRefactorSeeder extends Seeder
{
    public function run(): void
    {
        $kategoriBtkl = KategoriPegawai::where('nama', 'BTKL')->first();
        $kategoriBtktl = KategoriPegawai::where('nama', 'BTKTL')->first();

        // Update existing jabatans with kategori_id
        Jabatan::where('nama', 'like', '%Admin%')->update(['kategori_id' => $kategoriBtktl->id]);
        Jabatan::where('nama', 'like', '%Kasir%')->update(['kategori_id' => $kategoriBtkl->id]);
        Jabatan::where('nama', 'like', '%Stylist%')->update(['kategori_id' => $kategoriBtkl->id]);
        Jabatan::where('nama', 'like', '%Therapis%')->update(['kategori_id' => $kategoriBtkl->id]);
        Jabatan::where('nama', 'like', '%Manajer%')->update(['kategori_id' => $kategoriBtktl->id]);
        Jabatan::where('nama', 'like', '%Supervisor%')->update(['kategori_id' => $kategoriBtktl->id]);
    }
}
