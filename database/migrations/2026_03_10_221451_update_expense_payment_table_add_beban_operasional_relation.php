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
        Schema::table('expense_payments', function (Blueprint $table) {
            // Add beban_operasional_id column
            $table->unsignedBigInteger('beban_operasional_id')->nullable()->after('id');
            
            // Rename nominal to nominal_pembayaran for clarity
            $table->renameColumn('nominal', 'nominal_pembayaran');
            
            // Rename deskripsi to keterangan for consistency
            $table->renameColumn('deskripsi', 'keterangan');
            
            // Add foreign key constraint
            $table->foreign('beban_operasional_id')->references('id')->on('beban_operasional')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('expense_payments', function (Blueprint $table) {
            // Drop foreign key
            $table->dropForeign(['beban_operasional_id']);
            
            // Drop column
            $table->dropColumn('beban_operasional_id');
            
            // Reverse column names
            $table->renameColumn('nominal_pembayaran', 'nominal');
            $table->renameColumn('keterangan', 'deskripsi');
        });
    }
};
