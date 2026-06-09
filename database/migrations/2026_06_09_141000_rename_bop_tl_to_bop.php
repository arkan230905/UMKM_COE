<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Mengubah nama akun dari "BOP TL" menjadi "BOP" saja
     * Menyederhanakan penamaan akun Biaya Overhead Pabrik
     */
    public function up(): void
    {
        // Mapping akun lama ke akun baru
        $accountMapping = [
            '55'  => ['old' => 'BOP TL - BOP Tidak Langsung Lainnya', 'new' => 'BOP - Lainnya'],
            '550' => ['old' => 'BOP TL - Biaya Listrik', 'new' => 'BOP - Beban Listrik'],
            '551' => ['old' => 'BOP TL - Sewa Tempat', 'new' => 'BOP - Beban Sewa Tempat'],
            '552' => ['old' => 'BOP TL - Biaya Penyusutan Gedung', 'new' => 'BOP - Beban Penyusutan Gedung'],
            '553' => ['old' => 'BOP TL - Biaya Penyusutan Peralatan', 'new' => 'BOP - Beban Penyusutan Peralatan'],
            '554' => ['old' => 'BOP TL - Biaya Penyusutan Kendaraan', 'new' => 'BOP - Beban Penyusutan Kendaraan'],
            '555' => ['old' => 'BOP TL - Biaya Penyusutan Mesin', 'new' => 'BOP - Beban Penyusutan Mesin'],
            '556' => ['old' => 'BOP TL - Biaya Air', 'new' => 'BOP - Beban Air'],
            '557' => ['old' => 'BOP TL - Lainnya', 'new' => 'BOP - Lainnya'],
        ];

        $updatedCount = 0;
        
        foreach ($accountMapping as $kodeAkun => $names) {
            // Update nama akun di tabel coas
            $result = DB::table('coas')
                ->where('kode_akun', $kodeAkun)
                ->where('nama_akun', $names['old'])
                ->update([
                    'nama_akun' => $names['new'],
                    'updated_at' => now()
                ]);
            
            if ($result > 0) {
                $updatedCount += $result;
                echo "✓ Updated: {$kodeAkun} - {$names['old']} → {$names['new']} ({$result} record(s))\n";
            }
        }
        
        echo "\n";
        echo "========================================\n";
        echo "✅ MIGRATION SELESAI!\n";
        echo "Total akun diupdate: {$updatedCount}\n";
        echo "========================================\n";
        echo "\n";
        echo "CATATAN:\n";
        echo "- Kode akun TIDAK berubah\n";
        echo "- Tipe akun tetap 'Biaya'\n";
        echo "- Saldo normal tetap 'debit'\n";
        echo "- Data transaksi TIDAK terhapus\n";
        echo "- Hanya nama akun yang diperbarui\n";
        echo "========================================\n";
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Rollback: Kembalikan nama lama
        $accountMapping = [
            '55'  => ['new' => 'BOP - Lainnya', 'old' => 'BOP TL - BOP Tidak Langsung Lainnya'],
            '550' => ['new' => 'BOP - Beban Listrik', 'old' => 'BOP TL - Biaya Listrik'],
            '551' => ['new' => 'BOP - Beban Sewa Tempat', 'old' => 'BOP TL - Sewa Tempat'],
            '552' => ['new' => 'BOP - Beban Penyusutan Gedung', 'old' => 'BOP TL - Biaya Penyusutan Gedung'],
            '553' => ['new' => 'BOP - Beban Penyusutan Peralatan', 'old' => 'BOP TL - Biaya Penyusutan Peralatan'],
            '554' => ['new' => 'BOP - Beban Penyusutan Kendaraan', 'old' => 'BOP TL - Biaya Penyusutan Kendaraan'],
            '555' => ['new' => 'BOP - Beban Penyusutan Mesin', 'old' => 'BOP TL - Biaya Penyusutan Mesin'],
            '556' => ['new' => 'BOP - Beban Air', 'old' => 'BOP TL - Biaya Air'],
            '557' => ['new' => 'BOP - Lainnya', 'old' => 'BOP TL - Lainnya'],
        ];

        foreach ($accountMapping as $kodeAkun => $names) {
            DB::table('coas')
                ->where('kode_akun', $kodeAkun)
                ->where('nama_akun', $names['new'])
                ->update([
                    'nama_akun' => $names['old'],
                    'updated_at' => now()
                ]);
        }
        
        echo "Rollback selesai: Nama akun dikembalikan ke 'BOP TL'\n";
    }
};
