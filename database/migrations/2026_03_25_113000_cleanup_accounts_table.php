<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Clean up duplicate accounts table in eadt_umkm database
     * The system uses coas table instead of accounts table
     */
    public function up(): void
    {
        // Only drop if table exists and we're in production environment
        if (Schema::hasTable('accounts') && app()->environment() !== 'testing') {
            echo "Dropping foreign key from journal_lines...\n";
            Schema::table('journal_lines', function (Blueprint $table) {
                $table->dropForeign(['account_id']);
                $table->dropColumn('account_id');
            });
            
            echo "Dropping accounts table...\n";
            Schema::dropIfExists('accounts');
            echo "Accounts table dropped successfully.\n";
        }
    }

    public function down(): void
    {
        // Recreate accounts table for rollback
        Schema::create('accounts', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('name');
            $table->enum('type', ['asset','liability','equity','revenue','expense']);
            $table->timestamps();
        });
        echo "Accounts table recreated for rollback.\n";
    }
};
