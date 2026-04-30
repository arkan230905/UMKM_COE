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
        // Fix COA unique constraint for multi-tenant
        Schema::table('coas', function (Blueprint $table) {
            // Drop existing unique constraint
            $table->dropUnique('coas_kode_company_unique');
            
            // Create proper multi-tenant unique constraint
            $table->unique(['kode_akun', 'company_id'], 'coas_kode_akun_company_unique');
        });
        
        // Fix Satuan unique constraint for multi-tenant
        Schema::table('satuans', function (Blueprint $table) {
            // Drop existing unique constraint if exists
            $table->dropUnique('satuans_kode_unique');
            
            // Create proper multi-tenant unique constraint
            $table->unique(['kode', 'user_id'], 'satuans_kode_user_unique');
        });
        
        echo "Fixed multi-tenant constraints for COA and Satuan tables\n";
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('coas', function (Blueprint $table) {
            $table->dropUnique('coas_kode_akun_company_unique');
            $table->unique(['kode_akun'], 'coas_kode_akun_unique');
        });
        
        Schema::table('satuans', function (Blueprint $table) {
            $table->dropUnique('satuans_kode_user_unique');
            $table->unique(['kode'], 'satuans_kode_unique');
        });
    }
};
