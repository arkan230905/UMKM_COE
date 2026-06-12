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
        Schema::table('journal_lines', function (Blueprint $table) {
            // Add user_id if not exists
            if (!Schema::hasColumn('journal_lines', 'user_id')) {
                $table->unsignedBigInteger('user_id')->nullable()->after('id');
                $table->index('user_id');
            }
            
            // Add tipe_referensi if not exists
            if (!Schema::hasColumn('journal_lines', 'tipe_referensi')) {
                $table->string('tipe_referensi')->nullable()->after('credit');
                $table->index('tipe_referensi');
            }
            
            // Add referensi if not exists
            if (!Schema::hasColumn('journal_lines', 'referensi')) {
                $table->string('referensi')->nullable()->after('tipe_referensi');
                $table->index('referensi');
            }
            
            // Add tanggal if not exists
            if (!Schema::hasColumn('journal_lines', 'tanggal')) {
                $table->date('tanggal')->nullable()->after('referensi');
                $table->index('tanggal');
            }
            
            // Add memo if not exists (rename from keterangan)
            if (!Schema::hasColumn('journal_lines', 'memo')) {
                if (Schema::hasColumn('journal_lines', 'keterangan')) {
                    $table->renameColumn('keterangan', 'memo');
                } else {
                    $table->string('memo')->nullable()->after('credit');
                }
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('journal_lines', function (Blueprint $table) {
            if (Schema::hasColumn('journal_lines', 'user_id')) {
                $table->dropIndex(['user_id']);
                $table->dropColumn('user_id');
            }
            
            if (Schema::hasColumn('journal_lines', 'tipe_referensi')) {
                $table->dropIndex(['tipe_referensi']);
                $table->dropColumn('tipe_referensi');
            }
            
            if (Schema::hasColumn('journal_lines', 'referensi')) {
                $table->dropIndex(['referensi']);
                $table->dropColumn('referensi');
            }
            
            if (Schema::hasColumn('journal_lines', 'tanggal')) {
                $table->dropIndex(['tanggal']);
                $table->dropColumn('tanggal');
            }
            
            if (Schema::hasColumn('journal_lines', 'memo') && !Schema::hasColumn('journal_lines', 'keterangan')) {
                $table->renameColumn('memo', 'keterangan');
            }
        });
    }
};
