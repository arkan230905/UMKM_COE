<?php

namespace App\Observers;

use App\Models\Jabatan;
use App\Models\Pegawai;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class JabatanObserver
{
    public function updated(Jabatan $jabatan): void
    {
        $originalNama = $jabatan->getOriginal('nama');
        $newNama = $jabatan->nama;
        
        // Cek kolom yang ada di tabel pegawais
        $pegawaiColumns = Schema::getColumnListing('pegawais');
        
        // Hanya update kolom yang benar-benar ada di tabel pegawais
        $updates = ['jabatan' => $newNama];
        
        if (in_array('tunjangan', $pegawaiColumns)) {
            $updates['tunjangan'] = (float) ($jabatan->tunjangan ?? 0);
        }
        if (in_array('asuransi', $pegawaiColumns)) {
            $updates['asuransi'] = (float) ($jabatan->asuransi ?? 0);
        }
        if (in_array('gaji', $pegawaiColumns)) {
            $updates['gaji'] = (float) ($jabatan->gaji_pokok ?? 0);
        }
        if (in_array('gaji_pokok', $pegawaiColumns)) {
            $updates['gaji_pokok'] = (float) ($jabatan->gaji_pokok ?? 0);
        }
        if (in_array('tarif_per_jam', $pegawaiColumns)) {
            $updates['tarif_per_jam'] = (float) ($jabatan->tarif_lembur ?? 0);
        }

        DB::transaction(function () use ($originalNama, $newNama, $updates) {
            // Update pegawai yang terhubung berdasarkan nama jabatan lama maupun baru
            Pegawai::where('jabatan', $originalNama)
                ->orWhere('jabatan', $newNama)
                ->update($updates);
        });
    }
}
