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
        // First, drop the unique constraint on kode_akun
        Schema::table('coas', function (Blueprint $table) {
            $table->dropUnique('coas_kode_akun_unique');
        });
        
        // Add a composite unique constraint on kode_akun and company_id
        Schema::table('coas', function (Blueprint $table) {
            $table->unique(['kode_akun', 'company_id'], 'coas_kode_akun_company_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('coas', function (Blueprint $table) {
            $table->dropUnique('coas_kode_akun_company_unique');
        });
        
        Schema::table('coas', function (Blueprint $table) {
            $table->unique('kode_akun', 'coas_kode_akun_unique');
        });
    }
};
