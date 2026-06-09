<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class CheckPenggajianTable extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'check:penggajian-table {--fix : Automatically fix missing columns}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check if penggajians table has all required columns';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('=== CHECKING PENGGAJIANS TABLE ===');
        $this->newLine();

        // Daftar kolom yang harus ada
        $requiredColumns = [
            'id',
            'user_id',
            'pegawai_id',
            'periode_bulan',
            'periode_tahun',
            'total_hari_hadir',
            'total_alpha',
            'total_jam',
            'gaji_pokok',
            'tarif_per_jam',
            'tunjangan',
            'tunjangan_jabatan',
            'tunjangan_transport',
            'tunjangan_konsumsi',
            'total_tunjangan',
            'asuransi',
            'bonus',
            'potongan',
            'total_jam_kerja', // CRITICAL COLUMN
            'produk_hari_1_5',
            'produk_hari_6_10',
            'produk_hari_11_20',
            'produk_hari_21_30',
            'total_produk_bulan',
            'tarif_produk',
            'total_gaji',
            'status_pembayaran',
            'tanggal_dibayar',
            'metode_pembayaran',
            'keterangan',
            'status_posting',
            'tanggal_posting',
            'tanggal_penggajian',
            'coa_kasbank',
            'created_at',
            'updated_at',
            'mode_input',
            'pembulatan_aktif',
            'pembulatan_step',
            'nominal_pembulatan',
        ];

        $missingColumns = [];
        $existingColumns = [];

        foreach ($requiredColumns as $column) {
            if (Schema::hasColumn('penggajians', $column)) {
                $existingColumns[] = $column;
                $this->line("✅ {$column}");
            } else {
                $missingColumns[] = $column;
                $this->error("❌ {$column} - MISSING!");
            }
        }

        $this->newLine();
        
        if (empty($missingColumns)) {
            $this->info('✅ All required columns exist!');
            $this->info("Total columns: " . count($existingColumns));
            return Command::SUCCESS;
        } else {
            $this->error("❌ Found " . count($missingColumns) . " missing columns:");
            foreach ($missingColumns as $column) {
                $this->line("   - {$column}");
            }
            
            $this->newLine();
            
            if ($this->option('fix')) {
                $this->info('Attempting to fix missing columns...');
                $this->newLine();
                
                foreach ($missingColumns as $column) {
                    if ($column === 'total_jam_kerja') {
                        $this->fixTotalJamKerja();
                    }
                    // Add more fix methods for other columns if needed
                }
            } else {
                $this->warn('To automatically fix missing columns, run:');
                $this->line('php artisan check:penggajian-table --fix');
                $this->newLine();
                $this->warn('Or manually run migration:');
                $this->line('php artisan migrate');
            }
            
            return Command::FAILURE;
        }
    }

    /**
     * Fix missing total_jam_kerja column
     */
    private function fixTotalJamKerja()
    {
        $this->info('Fixing total_jam_kerja column...');
        
        try {
            DB::statement('ALTER TABLE `penggajians` ADD COLUMN `total_jam_kerja` DECIMAL(8, 2) NOT NULL DEFAULT 0.00 COMMENT "Total jam kerja (untuk sistem jam-based, 0 untuk produk-based)" AFTER `potongan`');
            
            $this->info('✅ total_jam_kerja column added successfully!');
        } catch (\Exception $e) {
            $this->error('❌ Failed to add total_jam_kerja column: ' . $e->getMessage());
        }
    }
}
