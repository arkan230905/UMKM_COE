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
        if (!Schema::hasTable('coa_period_balances')) {
            Schema::create('coa_period_balances', function (Blueprint $table) {
                $table->id();
                
                // 1. Relasi Owner & Perusahaan (Wajib ada agar saldo tidak tertukar antar UMKM)
                $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('cascade');
                $table->foreignId('company_id')->nullable()->constrained('companies')->onDelete('cascade');
                
                // 2. Relasi ke Periode
                $table->foreignId('period_id')->constrained('coa_periods')->onDelete('cascade');
                
                /**
                 * 3. Data Akun
                 * PENTING: Panjang karakter (default 255) harus sama dengan di tabel 'accounts'.
                 * Jika di coas tidak ditentukan panjangnya, hapus angka 191 atau 50 di bawah ini.
                 */
                $table->string('kode_akun'); 
                
                // 4. Komponen Saldo
                $table->decimal('saldo_awal', 15, 2)->default(0);
                $table->decimal('debit', 15, 2)->default(0); 
                $table->decimal('credit', 15, 2)->default(0); 
                $table->decimal('saldo_akhir', 15, 2)->default(0);
                
                // 5. Status Posting
                $table->boolean('is_posted')->default(false); 
                $table->timestamps();
                
                // Foreign Key ke tabel accounts (merujuk ke kolom kode_akun)
                $table->foreign('kode_akun')->references('kode_akun')->on('accounts')->onDelete('cascade');
                
                // Indexes untuk performa laporan
                $table->index('user_id');
                $table->index('company_id');
                $table->index('period_id');
                
                /**
                 * Unique Constraint: 
                 * Mencegah duplikasi saldo untuk akun yang sama di periode yang sama.
                 */
                $table->unique(['company_id', 'period_id', 'kode_akun'], 'unique_balance_per_company_period');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('coa_period_balances');
    }
};