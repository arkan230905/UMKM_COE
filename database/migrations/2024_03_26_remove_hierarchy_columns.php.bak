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
        Schema::table('coas', function (Blueprint $table) {
            // Drop foreign key constraints if they exist
            try {
                $table->dropForeign(['kode_induk']);
            } catch (Exception $e) {
                // Foreign key might not exist, continue
            }
            
            // Remove hierarchy columns
            if (Schema::hasColumn('coas', 'kode_induk')) {
                $table->dropColumn('kode_induk');
            }
            
            if (Schema::hasColumn('coas', 'is_akun_header')) {
                $table->dropColumn('is_akun_header');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('coas', function (Blueprint $table) {
            $table->string('kode_induk')->nullable();
            $table->boolean('is_akun_header')->default(false);
        });
    }
};