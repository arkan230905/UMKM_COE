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
        // Add unique constraint to prevent duplicate BTKL records
        Schema::table('btkls', function (Blueprint $table) {
            // Unique constraint on user_id + kode_proses to prevent duplicates
            $table->unique(['user_id', 'kode_proses'], 'unique_user_kode_proses');
        });

        // Add unique constraint to prevent duplicate ProsesProduksi records
        Schema::table('proses_produksis', function (Blueprint $table) {
            // Unique constraint on user_id + btkl_id to prevent duplicates
            $table->unique(['user_id', 'btkl_id'], 'unique_user_btkl_id');
        });

        // Clean up any existing duplicates before applying constraints
        $this->cleanupDuplicates();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('btkls', function (Blueprint $table) {
            $table->dropUnique('unique_user_kode_proses');
        });

        Schema::table('proses_produksis', function (Blueprint $table) {
            $table->dropUnique('unique_user_btkl_id');
        });
    }

    /**
     * Clean up existing duplicates
     */
    private function cleanupDuplicates()
    {
        // Clean up duplicate BTKL records
        DB::statement("
            DELETE t1 FROM btkls t1
            INNER JOIN btkls t2 
            WHERE t1.id > t2.id 
            AND t1.user_id = t2.user_id 
            AND t1.kode_proses = t2.kode_proses
        ");

        // Clean up duplicate ProsesProduksi records
        DB::statement("
            DELETE t1 FROM proses_produksis t1
            INNER JOIN proses_produksis t2 
            WHERE t1.id > t2.id 
            AND t1.user_id = t2.user_id 
            AND t1.btkl_id = t2.btkl_id
        ");
    }
};
