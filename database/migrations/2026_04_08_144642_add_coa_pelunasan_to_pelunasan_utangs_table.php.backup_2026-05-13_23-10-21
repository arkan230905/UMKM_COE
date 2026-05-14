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
        Schema::table('pelunasan_utangs', function (Blueprint $table) {
            // Add COA pelunasan column
            $table->unsignedBigInteger('coa_pelunasan_id')->nullable()->after('akun_kas_id');
            
            // Add foreign key constraint
            $table->foreign('coa_pelunasan_id')->references('id')->on('coas')->onDelete('set null');
            
            // Add index for better performance
            $table->index('coa_pelunasan_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pelunasan_utangs', function (Blueprint $table) {
            // Drop foreign key first
            $table->dropForeign(['coa_pelunasan_id']);
            
            // Drop column
            $table->dropColumn('coa_pelunasan_id');
        });
    }
};
