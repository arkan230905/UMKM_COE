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
        Schema::table('bahan_bakus', function (Blueprint $table) {
            // Add satuan_id column
            $table->unsignedBigInteger('satuan_id')->nullable()->after('id');
            
            // Add foreign key constraint
            $table->foreign('satuan_id')
                  ->references('id')
                  ->on('satuans')
                  ->onDelete('set null');
            
            // Drop the old satuan column after migration
            $table->dropColumn('satuan');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bahan_bakus', function (Blueprint $table) {
            // Drop foreign key constraint
            $table->dropForeign(['satuan_id']);
            
            // Add back the old satuan column
            $table->string('satuan')->after('id');
            
            // Drop satuan_id column
            $table->dropColumn('satuan_id');
        });
    }
};
