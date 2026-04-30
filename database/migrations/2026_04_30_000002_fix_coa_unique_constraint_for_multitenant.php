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
        // Drop existing unique constraints and create new one that includes company_id
        Schema::table('coas', function (Blueprint $table) {
            // Drop existing unique constraints if they exist
            try {
                $table->dropUnique('coas_kode_akun_company_unique');
            } catch (\Exception $e) {
                // Constraint doesn't exist, continue
            }
            
            try {
                $table->dropUnique('coas_kode_company_unique');
            } catch (\Exception $e) {
                // Constraint doesn't exist, continue
            }
            
            try {
                $table->dropUnique('coas_kode_akun_unique');
            } catch (\Exception $e) {
                // Constraint doesn't exist, continue
            }
            
            // Create new unique constraint that includes company_id for multi-tenant
            $table->unique(['kode_akun', 'company_id'], 'coas_kode_akun_company_unique');
        });
        
        echo "Fixed COA unique constraint for multi-tenant (kode_akun + company_id)\n";
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('coas', function (Blueprint $table) {
            // Drop the multi-tenant unique constraint
            $table->dropUnique(['kode_akun', 'company_id']);
            
            // Restore the original unique constraint
            $table->unique(['kode_akun'], 'coas_kode_akun_unique');
        });
    }
};
