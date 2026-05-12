<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Remove margin_percent column and fix harga_pokok
     */
    public function up(): void
    {
        Schema::table('produks', function (Blueprint $table) {
            // Make margin_percent nullable first
            $table->decimal('margin_percent', 5, 2)->nullable()->change();
        });
        
        // Update all records to set harga_pokok to NULL and margin_percent to 0
        DB::table('produks')->update([
            'harga_pokok' => null,
            'margin_percent' => 0
        ]);
        
        Schema::table('produks', function (Blueprint $table) {
            // Drop margin_percent column completely
            $table->dropColumn('margin_percent');
        });
    }

    public function down(): void
    {
        Schema::table('produks', function (Blueprint $table) {
            // Recreate margin_percent column
            $table->decimal('margin_percent', 5, 2)->default(0)->after('hpp');
        });
    }
};
