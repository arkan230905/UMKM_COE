<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('satuans', function (Blueprint $table) {
            // Add missing columns for the new conversion system
            if (!Schema::hasColumn('satuans', 'tipe')) {
                $table->enum('tipe', ['weight', 'volume', 'unit'])->default('unit')->after('nama');
            }
            if (!Schema::hasColumn('satuans', 'satuan_grup_id')) {
                $table->unsignedBigInteger('satuan_grup_id')->nullable()->after('tipe');
            }
            if (!Schema::hasColumn('satuans', 'satuan_dasar_id')) {
                $table->unsignedBigInteger('satuan_dasar_id')->nullable()->after('satuan_grup_id');
            }
            if (!Schema::hasColumn('satuans', 'nilai_konversi')) {
                $table->decimal('nilai_konversi', 24, 8)->default(1.000000)->after('satuan_dasar_id');
            }
            if (!Schema::hasColumn('satuans', 'is_dasar')) {
                $table->boolean('is_dasar')->default(false)->after('nilai_konversi');
            }
            if (!Schema::hasColumn('satuans', 'keterangan')) {
                $table->text('keterangan')->nullable()->after('is_dasar');
            }

            // Add foreign key constraints
            $table->foreign('satuan_grup_id')->references('id')->on('satuan_grups')->onDelete('set null');
            $table->foreign('satuan_dasar_id')->references('id')->on('satuans')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('satuans', function (Blueprint $table) {
            $table->dropForeign(['satuan_grup_id']);
            $table->dropForeign(['satuan_dasar_id']);
            $table->dropColumn(['tipe', 'satuan_grup_id', 'satuan_dasar_id', 'nilai_konversi', 'is_dasar', 'keterangan']);
        });
    }
};
