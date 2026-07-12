<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Tambah kolom timestamps untuk flow "Ambil di Toko"
     */
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            // Add timestamp columns if they don't exist
            if (!Schema::hasColumn('orders', 'completed_at')) {
                $table->timestamp('completed_at')->nullable()->after('paid_at');
            }
            
            if (!Schema::hasColumn('orders', 'payment_success_at')) {
                $table->timestamp('payment_success_at')->nullable()->after('completed_at');
            }
            
            if (!Schema::hasColumn('orders', 'ready_pickup_at')) {
                $table->timestamp('ready_pickup_at')->nullable()->after('payment_success_at');
            }
            
            if (!Schema::hasColumn('orders', 'picked_up_at')) {
                $table->timestamp('picked_up_at')->nullable()->after('ready_pickup_at');
            }
            
            if (!Schema::hasColumn('orders', 'shipped_at')) {
                $table->timestamp('shipped_at')->nullable()->after('picked_up_at');
            }
            
            if (!Schema::hasColumn('orders', 'approved_at')) {
                $table->timestamp('approved_at')->nullable()->after('shipped_at');
            }
            
            if (!Schema::hasColumn('orders', 'rejected_at')) {
                $table->timestamp('rejected_at')->nullable()->after('approved_at');
            }
            
            if (!Schema::hasColumn('orders', 'perusahaan_id')) {
                $table->foreignId('perusahaan_id')->nullable()->after('user_id')->constrained('perusahaans')->onDelete('cascade');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumnIfExists([
                'completed_at',
                'payment_success_at',
                'ready_pickup_at',
                'picked_up_at',
                'shipped_at',
                'approved_at',
                'rejected_at',
                'perusahaan_id',
            ]);
        });
    }
};
