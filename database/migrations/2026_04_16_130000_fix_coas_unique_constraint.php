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
        // Step 1: Drop foreign keys that reference coas.kode_akun
        $foreignKeys = [
            ['table' => 'bops', 'constraint' => 'bops_kode_akun_foreign'],
            ['table' => 'coa_period_balances', 'constraint' => 'coa_period_balances_kode_akun_foreign'],
        ];

        foreach ($foreignKeys as $fk) {
            // Check if foreign key exists before dropping
            $exists = DB::select("
                SELECT CONSTRAINT_NAME 
                FROM information_schema.KEY_COLUMN_USAGE 
                WHERE TABLE_SCHEMA = DATABASE() 
                    AND TABLE_NAME = '{$fk['table']}' 
                    AND CONSTRAINT_NAME = '{$fk['constraint']}'
            ");
            
            if (!empty($exists)) {
                DB::statement("ALTER TABLE {$fk['table']} DROP FOREIGN KEY {$fk['constraint']}");
            }
        }

        // Step 2: Drop the unique constraint on kode_akun if it exists
        $indexes = DB::select("SHOW INDEX FROM coas WHERE Key_name = 'coas_kode_akun_unique'");
        if (!empty($indexes)) {
            DB::statement("ALTER TABLE coas DROP INDEX coas_kode_akun_unique");
        }

        // Step 3: Add a composite unique constraint on kode_akun and company_id if it doesn't exist
        $compositeIndexes = DB::select("SHOW INDEX FROM coas WHERE Key_name = 'coas_kode_akun_company_unique'");
        if (empty($compositeIndexes)) {
            DB::statement("ALTER TABLE coas ADD UNIQUE KEY coas_kode_akun_company_unique (kode_akun, company_id)");
        }

        // Step 4: Recreate the foreign keys
        foreach ($foreignKeys as $fk) {
            // Check if foreign key doesn't exist before adding
            $exists = DB::select("
                SELECT CONSTRAINT_NAME 
                FROM information_schema.KEY_COLUMN_USAGE 
                WHERE TABLE_SCHEMA = DATABASE() 
                    AND TABLE_NAME = '{$fk['table']}' 
                    AND CONSTRAINT_NAME = '{$fk['constraint']}'
            ");
            
            if (empty($exists)) {
                DB::statement("
                    ALTER TABLE {$fk['table']} 
                    ADD CONSTRAINT {$fk['constraint']} 
                    FOREIGN KEY (kode_akun) 
                    REFERENCES coas(kode_akun) 
                    ON DELETE CASCADE 
                    ON UPDATE CASCADE
                ");
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Step 1: Drop foreign keys that reference coas.kode_akun
        $foreignKeys = [
            ['table' => 'bops', 'constraint' => 'bops_kode_akun_foreign'],
            ['table' => 'coa_period_balances', 'constraint' => 'coa_period_balances_kode_akun_foreign'],
        ];

        foreach ($foreignKeys as $fk) {
            $exists = DB::select("
                SELECT CONSTRAINT_NAME 
                FROM information_schema.KEY_COLUMN_USAGE 
                WHERE TABLE_SCHEMA = DATABASE() 
                    AND TABLE_NAME = '{$fk['table']}' 
                    AND CONSTRAINT_NAME = '{$fk['constraint']}'
            ");
            
            if (!empty($exists)) {
                DB::statement("ALTER TABLE {$fk['table']} DROP FOREIGN KEY {$fk['constraint']}");
            }
        }

        // Step 2: Drop the composite unique constraint if it exists
        $compositeIndexes = DB::select("SHOW INDEX FROM coas WHERE Key_name = 'coas_kode_akun_company_unique'");
        if (!empty($compositeIndexes)) {
            DB::statement("ALTER TABLE coas DROP INDEX coas_kode_akun_company_unique");
        }

        // Step 3: Recreate the original unique constraint if it doesn't exist
        $indexes = DB::select("SHOW INDEX FROM coas WHERE Key_name = 'coas_kode_akun_unique'");
        if (empty($indexes)) {
            DB::statement("ALTER TABLE coas ADD UNIQUE KEY coas_kode_akun_unique (kode_akun)");
        }

        // Step 4: Recreate the foreign keys
        foreach ($foreignKeys as $fk) {
            $exists = DB::select("
                SELECT CONSTRAINT_NAME 
                FROM information_schema.KEY_COLUMN_USAGE 
                WHERE TABLE_SCHEMA = DATABASE() 
                    AND TABLE_NAME = '{$fk['table']}' 
                    AND CONSTRAINT_NAME = '{$fk['constraint']}'
            ");
            
            if (empty($exists)) {
                DB::statement("
                    ALTER TABLE {$fk['table']} 
                    ADD CONSTRAINT {$fk['constraint']} 
                    FOREIGN KEY (kode_akun) 
                    REFERENCES coas(kode_akun) 
                    ON DELETE CASCADE 
                    ON UPDATE CASCADE
                ");
            }
        }
    }
};
