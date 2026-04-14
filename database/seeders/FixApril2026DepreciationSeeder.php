<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class FixApril2026DepreciationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        echo "=== MEMPERBAIKI JURNAL PENYUSUTAN APRIL 2026 ===\n\n";

        // Backup data lama
        try {
            DB::statement("
                CREATE TABLE IF NOT EXISTS jurnal_umum_backup_april_2026 AS
                SELECT * FROM jurnal_umum 
                WHERE tanggal = '2026-04-30' 
                  AND keterangan LIKE '%Penyusutan%'
            ");
            echo "✓ Backup jurnal lama berhasil dibuat\n";
        } catch (\Exception $e) {
            echo "⚠ Backup mungkin sudah ada: " . $e->getMessage() . "\n";
        }

        // Data koreksi
        $corrections = [
            ['old' => 1416667.00, 'new' => 1333333.00, 'asset' => 'Mesin'],
            ['old' => 2833333.00, 'new' => 659474.00, 'asset' => 'Peralatan'],
            ['old' => 2361111.00, 'new' => 888889.00, 'asset' => 'Kendaraan']
        ];

        $totalUpdated = 0;

        foreach ($corrections as $correction) {
            echo "Memperbaiki {$correction['asset']}:\n";

            // Update debit
            $updated1 = DB::update("
                UPDATE jurnal_umum 
                SET debit = ? 
                WHERE tanggal = '2026-04-30' 
                  AND keterangan LIKE '%Penyusutan%'
                  AND keterangan LIKE ? 
                  AND debit = ?
            ", [$correction['new'], "%{$correction['asset']}%", $correction['old']]);

            // Update kredit
            $updated2 = DB::update("
                UPDATE jurnal_umum 
                SET kredit = ? 
                WHERE tanggal = '2026-04-30' 
                  AND keterangan LIKE '%Penyusutan%'
                  AND keterangan LIKE ? 
                  AND kredit = ?
            ", [$correction['new'], "%{$correction['asset']}%", $correction['old']]);

            echo "  Debit updated: {$updated1} rows\n";
            echo "  Kredit updated: {$updated2} rows\n";
            echo "  Rp " . number_format($correction['old'], 0, ',', '.') . " → Rp " . number_format($correction['new'], 0, ',', '.') . "\n\n";

            $totalUpdated += $updated1 + $updated2;
        }

        if ($totalUpdated > 0) {
            echo "✓ Total {$totalUpdated} baris berhasil diupdate!\n\n";

            // Update data aset
            echo "Mengupdate data aset...\n";

            $assetUpdates = [
                ['amount' => 1333333, 'yearly' => 16000000, 'keyword' => '%Mesin%'],
                ['amount' => 659474, 'yearly' => 7913688, 'keyword' => '%Peralatan%'],
                ['amount' => 888889, 'yearly' => 10666668, 'keyword' => '%Kendaraan%']
            ];

            foreach ($assetUpdates as $update) {
                $updated = DB::update("
                    UPDATE asets 
                    SET penyusutan_per_bulan = ?,
                        penyusutan_per_tahun = ?
                    WHERE nama_aset LIKE ?
                ", [$update['amount'], $update['yearly'], $update['keyword']]);

                $keyword = str_replace('%', '', $update['keyword']);
                echo "  {$keyword}: {$updated} aset diupdate\n";
            }

            // Validasi hasil
            echo "\nValidasi hasil:\n";
            $results = DB::select("
                SELECT keterangan, debit, kredit 
                FROM jurnal_umum 
                WHERE tanggal = '2026-04-30' 
                  AND keterangan LIKE '%Penyusutan%'
                ORDER BY debit DESC
            ");

            foreach ($results as $result) {
                $amount = max($result->debit, $result->kredit);
                echo "  " . substr($result->keterangan, 0, 30) . "... : Rp " . number_format($amount, 0, ',', '.') . "\n";
            }

            echo "\n✓ PERBAIKAN SELESAI!\n";

        } else {
            echo "✗ Tidak ada data yang diupdate. Kemungkinan sudah benar atau tidak ditemukan.\n";
        }
    }
}