<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('penjualans', function (Blueprint $table) {
            // Add order_id foreign key to link to orders table
            if (!Schema::hasColumn('penjualans', 'order_id')) {
                $table->foreignId('order_id')
                    ->nullable()
                    ->after('id')
                    ->constrained('orders')
                    ->onDelete('cascade');
            }

            // Add payment_status to track payment confirmation
            if (!Schema::hasColumn('penjualans', 'payment_status')) {
                $table->enum('payment_status', ['pending', 'paid', 'failed', 'expired'])
                    ->default('pending')
                    ->after('payment_method');
            }

            // Add payment_confirmed_at timestamp
            if (!Schema::hasColumn('penjualans', 'payment_confirmed_at')) {
                $table->timestamp('payment_confirmed_at')
                    ->nullable()
                    ->after('payment_status');
            }

            // Add user_id if not exists (for multi-tenant isolation)
            if (!Schema::hasColumn('penjualans', 'user_id')) {
                $table->foreignId('user_id')
                    ->nullable()
                    ->after('order_id')
                    ->constrained('users')
                    ->onDelete('cascade');
            }
        });
    }

    public function down(): void
    {
        Schema::table('penjualans', function (Blueprint $table) {
            if (Schema::hasColumn('penjualans', 'payment_confirmed_at')) {
                $table->dropColumn('payment_confirmed_at');
            }
            if (Schema::hasColumn('penjualans', 'payment_status')) {
                $table->dropColumn('payment_status');
            }
            if (Schema::hasColumn('penjualans', 'user_id')) {
                $table->dropForeign(['user_id']);
                $table->dropColumn('user_id');
            }
            if (Schema::hasColumn('penjualans', 'order_id')) {
                $table->dropForeign(['order_id']);
                $table->dropColumn('order_id');
            }
        });
    }
};
