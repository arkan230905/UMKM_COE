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
        // Step 1: Remove duplicates first
        echo "Removing duplicate COAs...\n";
        
        $duplicates = DB::select("
            SELECT kode_akun, company_id, MIN(id) as keep_id, GROUP_CONCAT(id) as all_ids
            FROM coas
            GROUP BY kode_akun, company_id
            HAVING COUNT(*) > 1
        ");
        
        foreach ($duplicates as $dup) {
            $ids = explode(',', $dup->all_ids);
            $deleteIds = array_diff($ids, [$dup->keep_id]);
            
            if (!empty($deleteIds)) {
                DB::table('coas')->whereIn('id', $deleteIds)->delete();
                echo "Deleted duplicate COAs: " . implode(', ', $deleteIds) . "\n";
            }
        }
        
        // Step 2: Drop old constraint
        echo "Dropping old constraint...\n";
        
        $constraints = [
            'coas_kode_akun_company_unique',
            'coas_kode_company_unique',
            'coas_kode_akun_unique'
        ];
        
        foreach ($constraints as $constraint) {
            try {
                DB::statement("ALTER TABLE coas DROP INDEX {$constraint}");
                echo "Dropped constraint: {$constraint}\n";
            } catch (\Exception $e) {
                // Constraint doesn't exist, skip
            }
        }
        
        // Step 3: Add new constraint on (kode_akun, user_id)
        echo "Adding new constraint on (kode_akun, user_id)...\n";
        
        Schema::table('coas', function (Blueprint $table) {
            $table->unique(['kode_akun', 'user_id'], 'coas_kode_akun_user_unique');
        });
        
        echo "✅ Migration completed!\n";
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop new constraint
        Schema::table('coas', function (Blueprint $table) {
            $table->dropUnique('coas_kode_akun_user_unique');
        });
        
        // Add back old constraint
        Schema::table('coas', function (Blueprint $table) {
            $table->unique(['kode_akun', 'company_id'], 'coas_kode_akun_company_unique');
        });
    }
};
