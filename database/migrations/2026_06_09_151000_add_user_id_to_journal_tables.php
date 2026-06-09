<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Menambahkan user_id untuk multi-tenancy pada journal tables
     */
    public function up(): void
    {
        // Add user_id to journal_entries if not exists
        if (!Schema::hasColumn('journal_entries', 'user_id')) {
            Schema::table('journal_entries', function (Blueprint $table) {
                $table->unsignedBigInteger('user_id')->nullable()->after('id');
                $table->index('user_id');
            });
            
            echo "✓ Added user_id to journal_entries\n";
        }
        
        // Add user_id to journal_lines if not exists
        if (!Schema::hasColumn('journal_lines', 'user_id')) {
            Schema::table('journal_lines', function (Blueprint $table) {
                $table->unsignedBigInteger('user_id')->nullable()->after('id');
                $table->index('user_id');
            });
            
            echo "✓ Added user_id to journal_lines\n";
        }
        
        // Add tipe_referensi and referensi columns for tracking source
        if (!Schema::hasColumn('journal_lines', 'tipe_referensi')) {
            Schema::table('journal_lines', function (Blueprint $table) {
                $table->string('tipe_referensi')->nullable()->after('memo')->comment('e.g., aset_perolehan, aset_penyusutan');
                $table->string('referensi')->nullable()->after('tipe_referensi')->comment('e.g., ASET-{id}');
                $table->date('tanggal')->nullable()->after('referensi');
                
                $table->index(['tipe_referensi', 'referensi']);
                $table->index('tanggal');
            });
            
            echo "✓ Added tipe_referensi, referensi, tanggal to journal_lines\n";
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('journal_entries', 'user_id')) {
            Schema::table('journal_entries', function (Blueprint $table) {
                $table->dropIndex(['user_id']);
                $table->dropColumn('user_id');
            });
        }
        
        if (Schema::hasColumn('journal_lines', 'user_id')) {
            Schema::table('journal_lines', function (Blueprint $table) {
                $table->dropIndex(['user_id']);
                $table->dropColumn('user_id');
            });
        }
        
        if (Schema::hasColumn('journal_lines', 'tipe_referensi')) {
            Schema::table('journal_lines', function (Blueprint $table) {
                $table->dropIndex(['tipe_referensi', 'referensi']);
                $table->dropIndex(['tanggal']);
                $table->dropColumn(['tipe_referensi', 'referensi', 'tanggal']);
            });
        }
    }
};
