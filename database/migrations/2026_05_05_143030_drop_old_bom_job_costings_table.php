<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Disable foreign key checks temporarily
        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        
        // Drop old tables in correct order to avoid constraint issues
        Schema::dropIfExists('bom_job_bbb');
        Schema::dropIfExists('bom_job_bahan_pendukung');
        Schema::dropIfExists('bom_job_btkl');
        Schema::dropIfExists('bom_job_bop');
        Schema::dropIfExists('bom_job_costings');
        
        // Re-enable foreign key checks
        DB::statement('SET FOREIGN_KEY_CHECKS=1');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
