<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class FixCoasTableDirectly extends Migration
{
    public function up()
    {
        // Skip migration - kolom sudah ditambahkan di migration sebelumnya
        return;
    }

    public function down()
    {
        // This is a one-way migration
    }
}
