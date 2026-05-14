<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bop_budgets', function (Blueprint $table) {
            $table->id();

            // 1. Multi-tenant: Relasi ke User/Owner
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('cascade');

            // 2. Multi-tenant: Relasi ke Perusahaan (Sesuai log: tabel 'perusahaan')
            $table->unsignedBigInteger('company_id')->nullable();
            $table->foreign('company_id')->references('id')->on('perusahaan')->onDelete('cascade');

            /**
             * 3. Relasi ke Akun (Sesuai log: tabel 'accounts')
             * Kita gunakan coa_id sebagai nama kolom, tapi merujuk ke tabel 'accounts'.
             */
            $table->unsignedBigInteger('coa_id');
            $table->foreign('coa_id')->references('id')->on('accounts')->onDelete('cascade');

            $table->decimal('jumlah_budget', 15, 2);
            $table->string('periode', 7); // Format: YYYY-MM
            $table->text('keterangan')->nullable();
            $table->timestamps();
            
            /**
             * 4. Multi-tenant Unique Constraint:
             * Memastikan satu akun hanya punya satu budget per periode di perusahaan yang sama.
             */
            $table->unique(['company_id', 'coa_id', 'periode'], 'unique_budget_per_company_period');

            // Indexing untuk performa laporan costing
            $table->index(['company_id', 'periode']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bop_budgets');
    }
};