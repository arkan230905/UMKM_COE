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
        // Check agar tidak terjadi error "table already exists"
        if (!Schema::hasTable('coa_period_balances')) {
            Schema::create('coa_period_balances', function (Blueprint $table) {
                $table->id();
                
                // 1. Relasi Owner/Tenant agar saldo tidak bercampur antar UMKM
                $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('cascade');
                
                // 2. Relasi ke Periode (menggunakan coa_period_id agar sinkron dengan tabel coa_periods)
                $table->foreignId('coa_period_id')->constrained('coa_periods')->onDelete('cascade');
                
                // 3. Data Akun
                $table->string('kode_akun', 50);
                
                // 4. Komponen Saldo
                $table->decimal('saldo_awal', 15, 2)->default(0);
                $table->decimal('debit', 15, 2)->default(0); // Tambahan untuk tracking mutasi
                $table->decimal('credit', 15, 2)->default(0); // Tambahan untuk tracking mutasi
                $table->decimal('saldo_akhir', 15, 2)->default(0);
                
                // 5. Status Posting
                $table->boolean('is_posted')->default(false); 
                $table->timestamps();
                
                // Foreign Key manual untuk kode_akun (jika tabel coas menggunakan kode_akun sebagai primary/unique)
                $table->foreign('kode_akun')->references('kode_akun')->on('coas')->onDelete('cascade');
                
                // Indexes untuk performa laporan Neraca Saldo
                $table->index('user_id');
                $table->index('coa_period_id');
                $table->index('is_posted');
                
                /**
                 * Unique Constraint: 
                 * Satu akun hanya boleh punya satu catatan saldo per periode per user.
                 */
                $table->unique(['user_id', 'coa_period_id', 'kode_akun'], 'unique_user_period_balance');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('coa_period_balances');
    }
};