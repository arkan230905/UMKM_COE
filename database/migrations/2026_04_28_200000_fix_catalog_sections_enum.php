<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Fix enum to include cover and team
        DB::statement("ALTER TABLE catalog_sections MODIFY COLUMN section_type ENUM('hero','about','products','location','custom','cover','team') NOT NULL DEFAULT 'custom'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE catalog_sections MODIFY COLUMN section_type ENUM('hero','about','products','location','custom') NOT NULL DEFAULT 'custom'");
    }
};
