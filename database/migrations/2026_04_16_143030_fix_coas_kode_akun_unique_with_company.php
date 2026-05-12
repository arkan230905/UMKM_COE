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
        // Drop existing unique constraint if exists
        try {
            Schema::table('coas', function (Blueprint $table) {
                $table->dropUnique('coas_kode_akun_unique');
            });
        } catch (\Exception $e) {
            // Constraint might not exist, continue
        }

        // Drop composite unique if exists
        try {
            Schema::table('coas', function (Blueprint $table) {
                $table->dropUnique('coas_kode_akun_company_unique');
            });
        } catch (\Exception $e) {
            // Constraint might not exist, continue
        }

        // Add composite unique constraint: kode_akun + company_id
        // This allows same kode_akun for different companies
        Schema::table('coas', function (Blueprint $table) {
            $table->unique(['kode_akun', 'company_id'], 'coas_kode_company_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('coas', function (Blueprint $table) {
            $table->dropUnique('coas_kode_company_unique');
            $table->unique('kode_akun', 'coas_kode_akun_unique');
        });
    }
};
