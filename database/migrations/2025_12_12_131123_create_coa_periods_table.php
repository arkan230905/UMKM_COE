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
        Schema::create('coa_periods', function (Blueprint $table) {
            $table->id();
            $table->string('periode')->unique(); // Format: Y-m (2025-12)
            $table->date('tanggal_mulai');
            $table->date('tanggal_selesai');
            $table->boolean('is_closed')->default(false);
            $table->datetime('closed_at')->nullable();
            $table->foreignId('closed_by')->nullable()->constrained('users');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('coa_periods');
    }
};
