<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Drop unused assets table (using Asset model)
     * The system uses Aset model with 'asets' table instead
     */
    public function up(): void
    {
        // Check if table exists before dropping
        if (Schema::hasTable('assets')) {
            // Drop foreign key constraints first if they exist
            Schema::table('assets', function (Blueprint $table) {
                $table->dropForeign(['expense_coa_id']);
                $table->dropForeign(['accum_depr_coa_id']);
            });
            
            // Drop the table
            Schema::dropIfExists('assets');
        }
    }

    public function down(): void
    {
        // Recreate the table if rollback is needed
        Schema::create('assets', function (Blueprint $table) {
            $table->id();
            $table->string('nama_aset');
            $table->date('tanggal_perolehan');
            $table->decimal('harga_perolehan', 15, 2);
            $table->decimal('nilai_sisa', 15, 2)->default(0);
            $table->unsignedInteger('umur_ekonomis'); // years
            $table->foreignId('expense_coa_id')->nullable()->constrained('coas');
            $table->foreignId('accum_depr_coa_id')->nullable()->constrained('coas');
            $table->boolean('locked')->default(false); // prevent delete when used
            $table->timestamps();
        });
    }
};
