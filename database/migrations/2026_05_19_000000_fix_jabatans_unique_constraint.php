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
        // Drop the old unique constraint on nama only
        DB::statement('ALTER TABLE jabatans DROP INDEX IF EXISTS jabatans_nama_unique');
        
        // Add new unique constraint that includes user_id
        // This allows different users to have jabatans with the same name
        DB::statement('ALTER TABLE jabatans ADD UNIQUE KEY jabatans_user_id_nama_unique (user_id, nama)');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop the new unique constraint
        DB::statement('ALTER TABLE jabatans DROP INDEX IF EXISTS jabatans_user_id_nama_unique');
        
        // Re-add the old unique constraint (if needed)
        DB::statement('ALTER TABLE jabatans ADD UNIQUE KEY jabatans_nama_unique (nama)');
    }
};
