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
        Schema::table('purchase_return_items', function (Blueprint $table) {
            // Add bahan_pendukung_id column after bahan_baku_id
            $table->unsignedBigInteger('bahan_pendukung_id')->nullable()->after('bahan_baku_id');
            
            // Add foreign key constraint
            $table->foreign('bahan_pendukung_id')->references('id')->on('bahan_pendukungs')->onDelete('cascade');
            
            // Add index for better performance
            $table->index('bahan_pendukung_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('purchase_return_items', function (Blueprint $table) {
            // Drop foreign key constraint first
            $table->dropForeign(['bahan_pendukung_id']);
            
            // Drop the column
            $table->dropColumn('bahan_pendukung_id');
        });
    }
};