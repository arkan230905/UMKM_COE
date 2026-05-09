<?php

namespace App\Console\Commands;

use App\Models\Pegawai;
use App\Models\Jabatan;
use Illuminate\Console\Command;

class AssignDefaultJabatanToPegawai extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'pegawai:assign-jabatan {--jabatan-id=33 : ID jabatan default}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Assign default jabatan to pegawai yang belum punya jabatan';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $jabatanId = $this->option('jabatan-id');
        
        // Check if jabatan exists
        $jabatan = Jabatan::find($jabatanId);
        if (!$jabatan) {
            $this->error("Jabatan dengan ID {$jabatanId} tidak ditemukan!");
            return 1;
        }

        // Find pegawai without jabatan
        $pegawaiTanpaJabatan = Pegawai::whereNull('jabatan_id')->get();
        
        if ($pegawaiTanpaJabatan->isEmpty()) {
            $this->info('Semua pegawai sudah memiliki jabatan.');
            return 0;
        }

        $this->info("Ditemukan {$pegawaiTanpaJabatan->count()} pegawai tanpa jabatan.");
        $this->info("Akan assign jabatan: {$jabatan->nama} (ID: {$jabatan->id})");

        if (!$this->confirm('Lanjutkan?')) {
            return 0;
        }

        $updated = 0;
        foreach ($pegawaiTanpaJabatan as $pegawai) {
            $pegawai->update(['jabatan_id' => $jabatanId]);
            $this->line("✓ {$pegawai->nama} -> {$jabatan->nama}");
            $updated++;
        }

        $this->info("\n✓ Berhasil assign jabatan ke {$updated} pegawai!");
        return 0;
    }
}
