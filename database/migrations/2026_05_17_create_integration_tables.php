<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * This migration creates all necessary tables and columns for the 
     * customer website to owner dashboard integration to work properly.
     */
    public function up(): void
    {
        // Ensure orders table has all required columns
        if (Schema::hasTable('orders')) {
            Schema::table('orders', function (Blueprint $table) {
                // Add columns if they don't exist
                if (!Schema::hasColumn('orders', 'snap_token')) {
                    $table->string('snap_token')->nullable()->after('payment_status');
                }
                if (!Schema::hasColumn('orders', 'paid_at')) {
                    $table->timestamp('paid_at')->nullable()->after('snap_token');
                }
            });
        }

        // Ensure penjualans table has all required columns for integration
        if (Schema::hasTable('penjualans')) {
            Schema::table('penjualans', function (Blueprint $table) {
                // Add order_id foreign key if it doesn't exist
                if (!Schema::hasColumn('penjualans', 'order_id')) {
                    $table->unsignedBigInteger('order_id')->nullable()->after('id');
                    $table->foreign('order_id')->references('id')->on('orders')->onDelete('set null');
                }
                
                // Add payment_status if it doesn't exist
                if (!Schema::hasColumn('penjualans', 'payment_status')) {
                    $table->enum('payment_status', ['pending', 'paid', 'failed', 'expired'])
                        ->default('pending')
                        ->after('payment_method');
                }
                
                // Add payment_confirmed_at if it doesn't exist
                if (!Schema::hasColumn('penjualans', 'payment_confirmed_at')) {
                    $table->timestamp('payment_confirmed_at')->nullable()->after('payment_status');
                }
            });
        }

        // Ensure stock_movements table exists for audit trail
        if (!Schema::hasTable('stock_movements')) {
            Schema::create('stock_movements', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('user_id');
                $table->enum('item_type', ['product', 'material', 'support']);
                $table->unsignedBigInteger('item_id');
                $table->date('tanggal');
                $table->enum('direction', ['in', 'out']);
                $table->decimal('qty', 15, 4);
                $table->string('satuan')->nullable();
                $table->decimal('unit_cost', 15, 2)->nullable();
                $table->decimal('total_cost', 15, 2)->nullable();
                $table->string('ref_type')->nullable();
                $table->unsignedBigInteger('ref_id')->nullable();
                $table->text('keterangan')->nullable();
                $table->json('manual_conversion_data')->nullable();
                $table->timestamps();
                
                $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
                $table->index(['item_type', 'item_id', 'direction']);
                $table->index(['ref_type', 'ref_id']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Don't drop tables, just remove columns we added
        if (Schema::hasTable('orders')) {
            Schema::table('orders', function (Blueprint $table) {
                if (Schema::hasColumn('orders', 'snap_token')) {
                    $table->dropColumn('snap_token');
                }
                if (Schema::hasColumn('orders', 'paid_at')) {
                    $table->dropColumn('paid_at');
                }
            });
        }

        if (Schema::hasTable('penjualans')) {
            Schema::table('penjualans', function (Blueprint $table) {
                if (Schema::hasColumn('penjualans', 'order_id')) {
                    $table->dropForeign(['order_id']);
                    $table->dropColumn('order_id');
                }
                if (Schema::hasColumn('penjualans', 'payment_status')) {
                    $table->dropColumn('payment_status');
                }
                if (Schema::hasColumn('penjualans', 'payment_confirmed_at')) {
                    $table->dropColumn('payment_confirmed_at');
                }
            });
        }
    }
};
