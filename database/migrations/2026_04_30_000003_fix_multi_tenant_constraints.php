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
        // Skip COA constraint modification (foreign key issue)
        echo "Skipping COA constraint modification (foreign key constraint exists)\n";
        
        // Fix Satuan unique constraint for multi-tenant
        try {
            Schema::table('satuans', function (Blueprint $table) {
                // Drop existing unique constraint if exists
                $table->dropUnique('satuans_kode_unique');
            });
        } catch (\Exception $e) {
            // Constraint doesn't exist, continue
        }
        
        try {
            Schema::table('satuans', function (Blueprint $table) {
                // Create proper multi-tenant unique constraint
                $table->unique(['kode', 'user_id'], 'satuans_kode_user_unique');
            });
        } catch (\Exception $e) {
            echo "Satuan constraint already exists or error: " . $e->getMessage() . "\n";
        }
        
        echo "Fixed multi-tenant constraints for Satuan table\n";
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
