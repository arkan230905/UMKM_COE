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
        Schema::table('asets', function (Blueprint $table) {
            // Add missing columns if they don't exist
            if (!Schema::hasColumn('asets', 'keterangan')) {
                $table->text('keterangan')->nullable();
            }
            if (!Schema::hasColumn('asets', 'created_by')) {
                $table->unsignedBigInteger('created_by')->nullable();
            }
            if (!Schema::hasColumn('asets', 'updated_by')) {
                $table->unsignedBigInteger('updated_by')->nullable();
            }
            if (!Schema::hasColumn('asets', 'locked')) {
                $table->boolean('locked')->default(false);
            }
        });
        
        // Add foreign key constraints in separate schema call
        Schema::table('asets', function (Blueprint $table) {
            if (Schema::hasColumn('asets', 'created_by')) {
                $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');
            }
            if (Schema::hasColumn('asets', 'updated_by')) {
                $table->foreign('updated_by')->references('id')->on('users')->onDelete('set null');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('asets', function (Blueprint $table) {
            // Drop foreign keys first
            $table->dropForeign(['created_by']);
            $table->dropForeign(['updated_by']);
            
            // Drop columns
            $table->dropColumn(['created_by', 'updated_by', 'locked']);
        });
    }
};
