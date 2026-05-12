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
            $table->string('periode', 7)->unique(); // Format: YYYY-MM
            $table->date('tanggal_mulai');
            $table->date('tanggal_selesai');
            $table->boolean('is_closed')->default(false); // Status periode ditutup atau belum
            $table->timestamp('closed_at')->nullable();
            $table->unsignedBigInteger('closed_by')->nullable();
            $table->timestamps();
            
            $table->index('periode');
            $table->index('is_closed');
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
