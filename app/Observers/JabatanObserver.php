<?php

namespace App\Observers;

use App\Models\Jabatan;
use App\Models\Pegawai;
use Illuminate\Support\Facades\DB;

class JabatanObserver
{
    public function updated(Jabatan $jabatan): void
    {
        $originalNama = $jabatan->getOriginal('nama');
        $newNama = $jabatan->nama;
        $newKategori = strtoupper((string) $jabatan->kategori);
        $updates = [
            'jabatan' => $newNama,
            'kategori' => $newKategori,
            'tunjangan' => (float) ($jabatan->tunjangan ?? 0),
            'asuransi' => (float) ($jabatan->asuransi ?? 0),
            'gaji' => (float) ($jabatan->gaji ?? 0),
            'tarif' => (float) ($jabatan->tarif ?? 0),
        ];

        DB::transaction(function () use ($originalNama, $newNama, $updates) {
            // Update pegawai yang terhubung berdasarkan nama jabatan lama maupun baru
            Pegawai::where('jabatan', $originalNama)
                ->orWhere('jabatan', $newNama)
                ->update($updates);
        });
    }
}
