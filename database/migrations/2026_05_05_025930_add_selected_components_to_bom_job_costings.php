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
        Schema::table('bom_job_costings', function (Blueprint $table) {
            // Add columns to store selected component IDs
            $table->json('selected_bbb_ids')->nullable()->comment('Selected BBB component IDs');
            $table->json('selected_btkl_ids')->nullable()->comment('Selected BTKL process IDs');
            $table->json('selected_bop_ids')->nullable()->comment('Selected BOP component IDs');
            
            // Add flags for component selection
            $table->boolean('include_bbb')->default(true)->comment('Include Biaya Bahan Baku');
            $table->boolean('include_btkl')->default(true)->comment('Include Biaya Tenaga Kerja Langsung');
            $table->boolean('include_bop')->default(true)->comment('Include Biaya Overhead Pabrik');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bom_job_costings', function (Blueprint $table) {
            $table->dropColumn([
                'selected_bbb_ids',
                'selected_btkl_ids',
                'selected_bop_ids',
                'include_bbb',
                'include_btkl',
                'include_bop'
            ]);
        });
    }
};
