<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class DatabaseFixer
{
    public function run()
    {
        echo "Memulai perbaikan struktur database...\n\n";
        
        // Nonaktifkan foreign key check
        $this->disableForeignKeyChecks();
        
        // Perbaiki tabel bops
        $this->fixBopsTable();
        
        // Perbaiki tabel coas
        $this->fixCoasTable();
        
        // Perbaiki tabel pegawais
        $this->fixPegawaisTable();
        
        // Tandai semua migrasi sebagai selesai
        $this->markAllMigrationsAsRun();
        
        // Aktifkan kembali foreign key check
        $this->enableForeignKeyChecks();
        
        echo "\nPerbaikan struktur database selesai!\n";
        echo "Sekarang coba akses aplikasi Anda di http://127.0.0.1:8000\n";
    }
    
    private function disableForeignKeyChecks()
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        echo "✓ Menonaktifkan pengecekan foreign key\n";
    }
    
    private function enableForeignKeyChecks()
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=1');
        echo "✓ Mengaktifkan kembali pengecekan foreign key\n";
    }
    
    private function fixBopsTable()
    {
        echo "\nMemeriksa tabel bops...\n";
        
        if (!Schema::hasTable('bops')) {
            echo "- Tabel bops tidak ditemukan, akan dibuat...\n";
            
            Schema::create('bops', function($table) {
                $table->increments('id');
                $table->string('kode_akun', 20);
                $table->decimal('budget', 15, 2)->default(0);
                $table->timestamps();
                
                $table->foreign('kode_akun')
                      ->references('kode_akun')
                      ->on('coas')
                      ->onDelete('cascade');
            });
            
            echo "✓ Tabel bops berhasil dibuat\n";
        } else {
            // Periksa kolom budget
            if (!Schema::hasColumn('bops', 'budget')) {
                echo "- Menambahkan kolom budget ke tabel bops...\n";
                Schema::table('bops', function($table) {
                    $table->decimal('budget', 15, 2)->default(0)->after('kode_akun');
                });
                echo "✓ Kolom budget berhasil ditambahkan\n";
            } else {
                echo "- Kolom budget sudah ada di tabel bops\n";
            }
        }
    }
    
    private function fixCoasTable()
    {
        echo "\nMemeriksa tabel coas...\n";
        
        if (!Schema::hasTable('coas')) {
            echo "- Tabel coas tidak ditemukan, akan dibuat...\n";
            
            Schema::create('coas', function($table) {
                $table->string('kode_akun', 20)->primary();
                $table->string('nama_akun');
                $table->string('tipe_akun');
                $table->timestamps();
            });
            
            echo "✓ Tabel coas berhasil dibuat\n";
        } else {
            echo "- Tabel coas sudah ada\n";
        }
    }
    
    private function fixPegawaisTable()
    {
        echo "\nMemeriksa tabel pegawais...\n";
        
        if (!Schema::hasTable('pegawais')) {
            echo "- Tabel pegawais tidak ditemukan, akan dibuat...\n";
            
            Schema::create('pegawais', function($table) {
                $table->id();
                $table->string('nama');
                $table->string('jabatan')->nullable();
                $table->decimal('gaji_pokok', 15, 2)->default(0);
                $table->decimal('tunjangan', 15, 2)->default(0);
                $table->timestamps();
            });
            
            echo "✓ Tabel pegawais berhasil dibuat\n";
        } else {
            echo "- Tabel pegawais sudah ada\n";
            
            // Periksa kolom yang diperlukan
            $columnsToCheck = ['gaji_pokok', 'tunjangan'];
            
            foreach ($columnsToCheck as $column) {
                if (!Schema::hasColumn('pegawais', $column)) {
                    echo "- Menambahkan kolom $column ke tabel pegawais...\n";
                    Schema::table('pegawais', function($table) use ($column) {
                        $table->decimal($column, 15, 2)->default(0);
                    });
                    echo "✓ Kolom $column berhasil ditambahkan\n";
                }
            }
        }
    }
    
    private function markAllMigrationsAsRun()
    {
        echo "\nMenandai semua migrasi sebagai selesai...\n";
        
        // Dapatkan semua file migrasi
        $migrationFiles = glob(database_path('migrations/*.php'));
        $migrations = [];
        
        foreach ($migrationFiles as $file) {
            $fileName = basename($file);
            if (preg_match('/^(\d{4}_\d{2}_\d{2}_\d{6}_.+)\.php$/', $fileName, $matches)) {
                $migrations[] = $matches[1];
            }
        }
        
        // Dapatkan migrasi yang sudah dijalankan
        $executedMigrations = DB::table('migrations')->pluck('migration')->toArray();
        $pendingMigrations = array_diff($migrations, $executedMigrations);
        
        if (empty($pendingMigrations)) {
            echo "- Tidak ada migrasi yang tertunda\n";
            return;
        }
        
        // Dapatkan batch terbaru
        $latestBatch = DB::table('migrations')->max('batch') ?: 0;
        $newBatch = $latestBatch + 1;
        
        // Tambahkan semua migrasi yang tertunda
        $data = array_map(function ($migration) use ($newBatch) {
            return [
                'migration' => $migration,
                'batch' => $newBatch
            ];
        }, $pendingMigrations);
        
        DB::table('migrations')->insert($data);
        
        echo "✓ Berhasil menandai " . count($pendingMigrations) . " migrasi sebagai selesai\n";
    }
}

// Jalankan perbaikan
$fixer = new DatabaseFixer();
$fixer->run();
