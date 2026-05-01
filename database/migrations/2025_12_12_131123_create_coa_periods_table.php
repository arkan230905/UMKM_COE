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
                $table->string('periode')->unique();
                $table->date('tanggal_mulai');
                $table->date('tanggal_selesai');
                $table->boolean('is_closed')->default(false);
                $table->datetime('closed_at')->nullable();
                $table->foreignId('closed_by')->nullable()->constrained('users');
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('coa_periods');
    }
};
