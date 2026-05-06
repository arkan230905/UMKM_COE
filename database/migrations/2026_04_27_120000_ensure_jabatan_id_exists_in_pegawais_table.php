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
        // Check if jabatan_id column exists, if not add it
        if (!Schema::hasColumn('pegawais', 'jabatan_id')) {
            Schema::table('pegawais', function (Blueprint $table) {
                $table->unsignedBigInteger('jabatan_id')->nullable()->after('jenis_kelamin');
                
                // Add foreign key constraint if jabatans table exists
                if (Schema::hasTable('jabatans')) {
                    $table->foreign('jabatan_id')->references('id')->on('jabatans')->onDelete('set null');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('pegawais', 'jabatan_id')) {
            Schema::table('pegawais', function (Blueprint $table) {
                $table->dropForeign(['jabatan_id']);
                $table->dropColumn('jabatan_id');
            });
        }
    }
};