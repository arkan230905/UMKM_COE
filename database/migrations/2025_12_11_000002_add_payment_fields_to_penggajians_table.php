<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('penggajians', function (Blueprint $table) {
            if (!Schema::hasColumn('penggajians', 'status')) {
                $table->enum('status', ['draft', 'siap_dibayar', 'dibayar'])->default('draft')->after('total_gaji');
            }
            if (!Schema::hasColumn('penggajians', 'tanggal_pembayaran')) {
                $table->date('tanggal_pembayaran')->nullable()->after('status');
            }
            if (!Schema::hasColumn('penggajians', 'catatan')) {
                $table->text('catatan')->nullable()->after('tanggal_pembayaran');
            }
            if (!Schema::hasColumn('penggajians', 'jam_lembur')) {
                $table->decimal('jam_lembur', 8, 2)->default(0)->after('catatan');
            }
        });
    }

    public function down(): void
    {
        Schema::table('penggajians', function (Blueprint $table) {
            $table->dropColumn(['status', 'tanggal_pembayaran', 'catatan', 'jam_lembur']);
        });
    }
};
