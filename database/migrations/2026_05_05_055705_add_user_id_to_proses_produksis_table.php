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
        Schema::table('proses_produksis', function (Blueprint $table) {
            // Check if user_id column doesn't exist before adding it
            if (!Schema::hasColumn('proses_produksis', 'user_id')) {
                $table->foreignId('user_id')->nullable()->after('id')->constrained('users')->onDelete('cascade');
                
                // Add index for better performance
                $table->index('user_id');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('proses_produksis', function (Blueprint $table) {
            // Drop foreign key and index first if they exist
            if (Schema::hasColumn('proses_produksis', 'user_id')) {
                $table->dropForeign(['user_id']);
                $table->dropIndex(['user_id']);
                $table->dropColumn('user_id');
            }
        });
    }
};
