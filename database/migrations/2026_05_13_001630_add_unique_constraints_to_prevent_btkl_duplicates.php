<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Bersihkan data duplikat terlebih dahulu
        $this->cleanupDuplicates();

        // 2. Handle tabel 'btkls'
        if (Schema::hasTable('btkls')) {
            Schema::table('btkls', function (Blueprint $table) {
                // Hapus jika sudah ada untuk menghindari 'Duplicate key name'
                if ($this->hasIndex('btkls', 'unique_user_kode_proses')) {
                    $table->dropUnique('unique_user_kode_proses');
                }
                
                if (Schema::hasColumns('btkls', ['user_id', 'kode_proses'])) {
                    $table->unique(['user_id', 'kode_proses'], 'unique_user_kode_proses');
                }
            });
        }

        // 3. Handle tabel 'proses_produksis'
        if (Schema::hasTable('proses_produksis')) {
            Schema::table('proses_produksis', function (Blueprint $table) {
                // Hapus jika sudah ada
                if ($this->hasIndex('proses_produksis', 'unique_user_btkl_id')) {
                    $table->dropUnique('unique_user_btkl_id');
                }

                if (Schema::hasColumns('proses_produksis', ['user_id', 'btkl_id'])) {
                    $table->unique(['user_id', 'btkl_id'], 'unique_user_btkl_id');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('btkls', function (Blueprint $table) {
            if ($this->hasIndex('btkls', 'unique_user_kode_proses')) {
                $table->dropUnique('unique_user_kode_proses');
            }
        });

        Schema::table('proses_produksis', function (Blueprint $table) {
            if ($this->hasIndex('proses_produksis', 'unique_user_btkl_id')) {
                $table->dropUnique('unique_user_btkl_id');
            }
        });
    }

    /**
     * Helper untuk mengecek keberadaan index
     */
    private function hasIndex($table, $name): bool
    {
        $conn = Schema::getConnection();
        $dbName = $conn->getDatabaseName();
        
        $results = DB::select("
            SELECT INDEX_NAME FROM INFORMATION_SCHEMA.STATISTICS 
            WHERE TABLE_SCHEMA = ? 
            AND TABLE_NAME = ? 
            AND INDEX_NAME = ?
        ", [$dbName, $table, $name]);

        return count($results) > 0;
    }

    /**
     * Clean up existing duplicates
     */
    private function cleanupDuplicates()
    {
        if (Schema::hasColumns('btkls', ['user_id', 'kode_proses'])) {
            DB::statement("
                DELETE t1 FROM btkls t1
                INNER JOIN btkls t2 
                WHERE t1.id > t2.id 
                AND t1.user_id = t2.user_id 
                AND t1.kode_proses = t2.kode_proses
            ");
        }

        if (Schema::hasColumns('proses_produksis', ['user_id', 'btkl_id'])) {
            DB::statement("
                DELETE t1 FROM proses_produksis t1
                INNER JOIN proses_produksis t2 
                WHERE t1.id > t2.id 
                AND t1.user_id = t2.user_id 
                AND t1.btkl_id = t2.btkl_id
            ");
        }
    }
};