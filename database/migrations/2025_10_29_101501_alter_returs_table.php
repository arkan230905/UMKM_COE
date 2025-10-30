<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('returs', function (Blueprint $table) {
            if (!Schema::hasColumn('returs', 'type')) {
                $table->enum('type', ['sale','purchase'])->default('purchase')->after('id');
            }
            if (!Schema::hasColumn('returs', 'ref_id')) {
                $table->unsignedBigInteger('ref_id')->nullable()->after('type');
            }
            if (!Schema::hasColumn('returs', 'kompensasi')) {
                $table->enum('kompensasi', ['refund','credit'])->default('credit')->after('tanggal');
            }
            if (!Schema::hasColumn('returs', 'status')) {
                $table->enum('status', ['draft','approved','posted'])->default('draft')->after('kompensasi');
            }
            if (!Schema::hasColumn('returs', 'alasan')) {
                $table->text('alasan')->nullable()->after('status');
            }
            if (!Schema::hasColumn('returs', 'memo')) {
                $table->text('memo')->nullable()->after('alasan');
            }
            // indexes
            try {
                $table->index(['type','ref_id']);
            } catch (\Throwable $e) {}
        });
    }

    public function down(): void
    {
        Schema::table('returs', function (Blueprint $table) {
            // No-op safe down to avoid dropping existing columns unexpectedly
        });
    }
};
