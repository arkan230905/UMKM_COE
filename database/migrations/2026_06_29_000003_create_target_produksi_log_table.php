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
        Schema::create('target_produksi_log', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('target_produksi_id')->index();
            $table->unsignedBigInteger('user_id')->index();
            $table->string('action', 50); // created, updated, deleted
            $table->json('old_data')->nullable();
            $table->json('new_data')->nullable();
            $table->text('description')->nullable();
            $table->timestamp('created_at');
            
            // Foreign keys
            $table->foreign('target_produksi_id')
                ->references('id')
                ->on('target_produksi')
                ->onDelete('cascade');
            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('target_produksi_log');
    }
};
