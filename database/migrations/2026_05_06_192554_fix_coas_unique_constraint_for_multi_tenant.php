<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * FIX: COA unique constraint untuk multi-tenant
     * Setiap user harus bisa punya COA dengan kode_akun yang sama
     * Constraint harus COMPOSITE: (kode_akun + user_id)
     */
    public function up(): void
    {
        // Step 1: Drop existing unique constraint on kode_akun (if exists)
        try {
            $indexes = DB::select("SHOW INDEX FROM coas WHERE Key_name = 'coas_kode_akun_unique'");
            if (!empty($indexes)) {
                DB::statement("ALTER TABLE coas DROP INDEX coas_kode_akun_unique");
                echo "✅ Dropped old unique constraint: coas_kode_akun_unique\n";
            }
        } catch (\Exception $e) {
            echo "⚠️  Could not drop coas_kode_akun_unique: " . $e->getMessage() . "\n";
        }
        
        // Step 2: Drop any other conflicting unique constraints
        try {
            $indexes = DB::select("SHOW INDEX FROM coas WHERE Key_name = 'coas_kode_company_unique'");
            if (!empty($indexes)) {
                DB::statement("ALTER TABLE coas DROP INDEX coas_kode_company_unique");
                echo "✅ Dropped old unique constraint: coas_kode_company_unique\n";
            }
        } catch (\Exception $e) {
            // Ignore if doesn't exist
        }
        
        // Step 3: Create COMPOSITE unique constraint (kode_akun + user_id)
        try {
            // Check if composite unique already exists
            $indexes = DB::select("SHOW INDEX FROM coas WHERE Key_name = 'coas_kode_akun_user_id_unique'");
            
            if (empty($indexes)) {
                Schema::table('coas', function (Blueprint $table) {
                    // CRITICAL: Multi-tenant unique constraint
                    // Setiap user bisa punya COA dengan kode_akun yang sama
                    $table->unique(['kode_akun', 'user_id'], 'coas_kode_akun_user_id_unique');
                });
                echo "✅ Created composite unique constraint: coas_kode_akun_user_id_unique\n";
            } else {
                echo "✅ Composite unique constraint already exists\n";
            }
        } catch (\Exception $e) {
            echo "❌ Error creating composite unique constraint: " . $e->getMessage() . "\n";
            throw $e;
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop composite unique constraint
        try {
            Schema::table('coas', function (Blueprint $table) {
                $table->dropUnique('coas_kode_akun_user_id_unique');
            });
        } catch (\Exception $e) {
            // Ignore if doesn't exist
        }
        
        // Recreate single column unique constraint (for rollback)
        try {
            Schema::table('coas', function (Blueprint $table) {
                $table->unique('kode_akun', 'coas_kode_akun_unique');
            });
        } catch (\Exception $e) {
            // Ignore if already exists
        }
    }
};
