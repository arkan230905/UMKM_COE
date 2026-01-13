<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('bom_job_btkl', function (Blueprint $table) {
            // Add btkl_id column to reference the new BTKL master data
            $table->foreignId('btkl_id')->nullable()->after('proses_produksi_id')->constrained('btkls')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bom_job_btkl', function (Blueprint $table) {
            $table->dropForeign(['btkl_id']);
            $table->dropColumn('btkl_id');
        });
    }
};
