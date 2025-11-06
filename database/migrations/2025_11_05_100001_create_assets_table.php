<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('assets', function (Blueprint $table) {
            $table->id();
            $table->string('nama_aset');
            $table->date('tanggal_perolehan');
            $table->decimal('harga_perolehan', 15, 2);
            $table->decimal('nilai_sisa', 15, 2)->default(0);
            $table->unsignedInteger('umur_ekonomis'); // years
            $table->foreignId('expense_coa_id')->nullable()->constrained('coas');
            $table->foreignId('accum_depr_coa_id')->nullable()->constrained('coas');
            $table->boolean('locked')->default(false); // prevent delete when used
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('assets');
    }
};
