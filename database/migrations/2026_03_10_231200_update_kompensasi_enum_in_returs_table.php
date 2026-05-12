<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('returs', function (Blueprint $table) {
            // Drop the existing enum column
            $table->dropColumn('kompensasi');
        });
        
        Schema::table('returs', function (Blueprint $table) {
            // Add it back with the new enum values
            $table->enum('kompensasi', ['refund', 'credit', 'replace'])->default('credit');
        });
    }

    public function down(): void
    {
        Schema::table('returs', function (Blueprint $table) {
            // Drop the updated enum column
            $table->dropColumn('kompensasi');
        });
        
        Schema::table('returs', function (Blueprint $table) {
            // Add it back with the original enum values
            $table->enum('kompensasi', ['refund', 'credit'])->default('credit');
        });
    }
};
