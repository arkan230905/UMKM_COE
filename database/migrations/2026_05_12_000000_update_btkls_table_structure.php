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
        Schema::table('btkls', function (Blueprint $table) {
            // Add user_id for multi-tenant
            if (!Schema::hasColumn('btkls', 'user_id')) {
                $table->foreignId('user_id')->constrained()->onDelete('cascade');
            }

            // Rename tarif_per_jam to tarif_btkl for clarity
            if (Schema::hasColumn('btkls', 'tarif_per_jam')) {
                $table->renameColumn('tarif_per_jam', 'tarif_btkl');
            }
            
            // Remove satuan and kapasitas_per_jam columns as they are no longer needed
            if (Schema::hasColumn('btkls', 'satuan')) {
                $table->dropColumn('satuan');
            }
            if (Schema::hasColumn('btkls', 'kapasitas_per_jam')) {
                $table->dropColumn('kapasitas_per_jam');
            }

            // Add indexes for performance
            $table->index(['user_id', 'jabatan_id']);
            $table->index(['user_id', 'kode_proses']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('btkls', function (Blueprint $table) {
            // Drop indexes
            $table->dropIndex(['user_id', 'jabatan_id']);
            $table->dropIndex(['user_id', 'kode_proses']);

            // Drop user_id column
            $table->dropForeign(['user_id']);
            $table->dropColumn('user_id');

            // Rename back to original
            if (Schema::hasColumn('btkls', 'tarif_btkl')) {
                $table->renameColumn('tarif_btkl', 'tarif_per_jam');
            }

            // Add back satuan and kapasitas_per_jam columns
            $table->enum('satuan', ['Jam', 'Unit', 'Batch'])->default('Jam');
            $table->integer('kapasitas_per_jam')->default(0);
        });
    }
};
