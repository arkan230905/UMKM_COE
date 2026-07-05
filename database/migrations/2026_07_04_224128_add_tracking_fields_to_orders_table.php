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
        Schema::table('orders', function (Blueprint $table) {
            $table->unsignedBigInteger('perusahaan_id')->nullable()->after('user_id');
            $table->text('alasan_penolakan')->nullable()->after('status');
            $table->timestamp('approved_at')->nullable()->after('alasan_penolakan');
            $table->timestamp('rejected_at')->nullable()->after('approved_at');
            $table->timestamp('ready_pickup_at')->nullable()->after('rejected_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn([
                'perusahaan_id',
                'alasan_penolakan',
                'approved_at',
                'rejected_at',
                'ready_pickup_at'
            ]);
        });
    }
};
