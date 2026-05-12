<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        // Add status column to pembelians table if it doesn't exist
        if (!Schema::hasColumn('pembelians', 'status')) {
            Schema::table('pembelians', function (Blueprint $table) {
                $table->string('status', 20)->default('belum_lunas')->after('payment_method');
                $table->decimal('terbayar', 15, 2)->default(0)->after('total');
            });
        }

        // Create pelunasan_utang table
        Schema::create('pelunasan_utang', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pembelian_id')->constrained('pembelians')->onDelete('cascade');
            $table->date('tanggal');
            $table->decimal('jumlah', 15, 2);
            $table->string('keterangan')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pelunasan_utang');
        
        // Don't drop columns to prevent data loss
        // You can create a new migration if you need to rollback
    }
};
