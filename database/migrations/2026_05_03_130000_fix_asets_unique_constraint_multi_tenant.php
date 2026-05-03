<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Fix unique constraint on asets table to include user_id for multi-tenant isolation
     */
    public function up(): void
    {
        // Drop old unique constraint if exists
        $indexes = DB::select("SHOW INDEX FROM asets WHERE Key_name = 'asets_kode_aset_unique'");
        if (!empty($indexes)) {
            DB::statement("ALTER TABLE asets DROP INDEX asets_kode_aset_unique");
        }
        
        // Add new unique constraint with user_id
        DB::statement("ALTER TABLE asets ADD UNIQUE KEY asets_kode_user_unique (kode_aset, user_id)");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop multi-tenant unique constraint
        $indexes = DB::select("SHOW INDEX FROM asets WHERE Key_name = 'asets_kode_user_unique'");
        if (!empty($indexes)) {
            DB::statement("ALTER TABLE asets DROP INDEX asets_kode_user_unique");
        }
        
        // Restore old unique constraint
        DB::statement("ALTER TABLE asets ADD UNIQUE KEY asets_kode_aset_unique (kode_aset)");
    }
};
