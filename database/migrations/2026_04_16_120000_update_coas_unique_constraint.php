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
        // First, drop foreign keys that depend on kode_akun
        Schema::disableForeignKeyConstraints();

        Schema::table('coas', function (Blueprint $table) {
            // Drop the unique constraint on kode_akun
            $table->dropUnique('coas_kode_akun_unique');

            // Add a composite unique constraint on kode_akun and company_id
            $table->unique(['kode_akun', 'company_id'], 'coas_kode_akun_company_unique');
        });

        Schema::enableForeignKeyConstraints();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('coas', function (Blueprint $table) {
            // Drop the composite unique constraint
            $table->dropUnique('coas_kode_akun_company_unique');
            
            // Add back the original unique constraint on kode_akun
            $table->unique('kode_akun', 'coas_kode_akun_unique');
        });
    }
};
