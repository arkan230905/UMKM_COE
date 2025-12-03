<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class FixAllMissingJournalsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        echo "=== FIXING ALL MISSING JOURNAL ENTRIES ===\n\n";
        
        // 1. Fix penjualan
        echo "1. Fixing Penjualan...\n";
        $this->call(FixMissingPenjualanJournalSeeder::class);
        
        echo "\n✓ All missing journal entries have been fixed!\n";
        echo "\nPlease refresh your browser to see the updated Laporan Kas dan Bank.\n";
    }
}
