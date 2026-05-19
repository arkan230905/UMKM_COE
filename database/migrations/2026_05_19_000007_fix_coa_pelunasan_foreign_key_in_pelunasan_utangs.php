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
        // Drop the incorrect foreign key constraint
        Schema::table('pelunasan_utangs', function (Blueprint $table) {
            $table->dropForeign(['coa_pelunasan_id']);
        });
        
        // Add the correct foreign key constraint to coas table
        Schema::table('pelunasan_utangs', function (Blueprint $table) {
            $table->foreign('coa_pelunasan_id')->references('id')->on('coas')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop the correct foreign key
        Schema::table('pelunasan_utangs', function (Blueprint $table) {
            $table->dropForeign(['coa_pelunasan_id']);
        });
        
        // Restore the incorrect foreign key (for rollback purposes)
        Schema::table('pelunasan_utangs', function (Blueprint $table) {
            $table->foreign('coa_pelunasan_id')->references('id')->on('accounts')->onDelete('set null');
        });
    }
};
