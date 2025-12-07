<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        // Add 'cash' to enum list for payment_method
        DB::statement("ALTER TABLE `orders` MODIFY COLUMN `payment_method` ENUM('qris','va_bca','va_bni','va_bri','va_mandiri','transfer','cash') NULL");
    }

    public function down(): void
    {
        // Revert by removing 'cash' (existing records with 'cash' should be handled before rollback)
        DB::statement("ALTER TABLE `orders` MODIFY COLUMN `payment_method` ENUM('qris','va_bca','va_bni','va_bri','va_mandiri','transfer') NULL");
    }
};
