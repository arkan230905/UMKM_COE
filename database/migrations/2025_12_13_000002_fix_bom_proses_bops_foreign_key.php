<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bom_proses_bops', function (Blueprint $table) {
            // Drop foreign key constraint if exists
            $table->dropForeign(['komponen_bop_id']);
            
            // Drop the column
            $table->dropColumn('komponen_bop_id');
            
            // Add new column with proper foreign key to bops table
            $table->foreignId('bop_id')->nullable()->after('bom_proses_id')->constrained('bops')->onDelete('restrict');
        });
    }

    public function down(): void
    {
        Schema::table('bom_proses_bops', function (Blueprint $table) {
            // Drop the new foreign key
            $table->dropForeign(['bop_id']);
            
            // Drop the new column
            $table->dropColumn('bop_id');
            
            // Recreate the old column (this won't work if komponen_bops table doesn't exist)
            // For now, we'll just add the column without foreign key
            $table->foreignId('komponen_bop_id')->nullable()->after('bom_proses_id');
        });
    }
};
