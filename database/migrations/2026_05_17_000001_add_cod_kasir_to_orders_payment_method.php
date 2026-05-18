<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Modify the payment_method enum to include 'cod' and 'kasir'
        DB::statement("ALTER TABLE orders MODIFY COLUMN payment_method ENUM('qris', 'va_bca', 'va_bni', 'va_bri', 'va_mandiri', 'transfer', 'cod', 'kasir') NULL");
    }

    public function down(): void
    {
        // Revert to original enum values
        DB::statement("ALTER TABLE orders MODIFY COLUMN payment_method ENUM('qris', 'va_bca', 'va_bni', 'va_bri', 'va_mandiri', 'transfer') NULL");
    }
};
