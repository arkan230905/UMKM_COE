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
        Schema::table('biaya_bahan_baku', function (Blueprint $table) {
            // Add coa_id column for specific COA per material per product
            $table->unsignedBigInteger('coa_id')->nullable()->after('bahan_baku_id');
            
            // Add foreign key constraint
            $table->foreign('coa_id')
                ->references('id')
                ->on('coas')
                ->onDelete('set null');
            
            // Add index for faster queries
            $table->index('coa_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('biaya_bahan_baku', function (Blueprint $table) {
            $table->dropForeign(['coa_id']);
            $table->dropIndex(['coa_id']);
            $table->dropColumn('coa_id');
        });
    }
};
