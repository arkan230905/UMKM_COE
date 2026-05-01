<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('coa_periods')) {
            Schema::create('coa_periods', function (Blueprint $table) {
                $table->id();
                $table->string('periode', 7)->unique();
                $table->date('tanggal_mulai');
                $table->date('tanggal_selesai');
                $table->boolean('is_closed')->default(false);
                $table->timestamp('closed_at')->nullable();
                $table->unsignedBigInteger('closed_by')->nullable();
                $table->timestamps();
                $table->index('periode');
                $table->index('is_closed');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('coa_periods');
    }
};
