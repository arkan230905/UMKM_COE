<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// FIXED: Migration ini sebelumnya membuat tabel bernama 'false' (bug).
// Sekarang dijadikan no-op karena barcode sudah ditangani migration lain.
return new class extends Migration
{
    public function up(): void
    {
        // No-op: barcode column handled by 2025_12_08_155454 and 2025_12_08_160000
    }

    public function down(): void
    {
        // No-op
    }
};
