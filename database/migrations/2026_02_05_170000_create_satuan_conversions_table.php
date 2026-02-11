<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('satuan_conversions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('source_satuan_id');
            $table->unsignedBigInteger('target_satuan_id');
            $table->decimal('amount_source', 24, 8);
            $table->decimal('amount_target', 24, 8);
            $table->boolean('is_inverse')->default(false);
            $table->timestamps();

            $table->foreign('source_satuan_id')->references('id')->on('satuans')->onDelete('cascade');
            $table->foreign('target_satuan_id')->references('id')->on('satuans')->onDelete('cascade');
            $table->unique(['source_satuan_id', 'target_satuan_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('satuan_conversions');
    }
};
