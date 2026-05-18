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
        Schema::table('kategori_bahan_pendukung', function (Blueprint $table) {
            $table->foreignId('user_id')->nullable()->after('id')->constrained('users')->onDelete('cascade');
            $table->index('user_id');
        });

        // Set existing records to user_id = 1 (default admin) ONLY if user exists
        $firstUser = DB::table('users')->first();
        if ($firstUser) {
            DB::table('kategori_bahan_pendukung')->whereNull('user_id')->update(['user_id' => $firstUser->id]);
        } else {
            // If no users exist, delete orphaned records
            DB::table('kategori_bahan_pendukung')->whereNull('user_id')->delete();
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('kategori_bahan_pendukung', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropIndex(['user_id']);
            $table->dropColumn('user_id');
        });
    }
};
