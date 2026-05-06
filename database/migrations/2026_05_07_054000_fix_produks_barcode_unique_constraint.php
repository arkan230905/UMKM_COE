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
        // Drop old unique constraint on barcode only
        Schema::table('produks', function (Blueprint $table) {
            $table->dropUnique('produks_barcode_unique');
        });
        
        // Add new composite unique constraint on (barcode, user_id)
        Schema::table('produks', function (Blueprint $table) {
            $table->unique(['barcode', 'user_id'], 'produks_barcode_user_id_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop composite unique constraint
        Schema::table('produks', function (Blueprint $table) {
            $table->dropUnique('produks_barcode_user_id_unique');
        });
        
        // Restore old unique constraint (if needed)
        Schema::table('produks', function (Blueprint $table) {
            $table->unique('barcode', 'produks_barcode_unique');
        });
    }
};
