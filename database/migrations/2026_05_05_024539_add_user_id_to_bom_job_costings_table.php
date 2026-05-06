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
            // Add user_id column for multi-tenant isolation
            $table->unsignedBigInteger('user_id')->nullable()->after('produk_id');
            
            // Add foreign key constraint
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            
            // Add index for performance
            $table->index('user_id');
        });
        
        // Update existing records with user_id from produk table
        DB::statement('
            UPDATE bom_job_costings bjc 
            SET bjc.user_id = (
                SELECT p.user_id 
                FROM produks p 
                WHERE p.id = bjc.produk_id 
                LIMIT 1
            )
        ');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bom_job_costings', function (Blueprint $table) {
            // Drop foreign key and index
            $table->dropForeign(['user_id']);
            $table->dropIndex(['user_id']);
            
            // Drop column
            $table->dropColumn('user_id');
        });
    }
};
