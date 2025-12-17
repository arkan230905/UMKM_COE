<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // Skip migration - pegawais table sudah ada dengan struktur yang tepat
        return;
    }

    public function down()
    {
        // No need to implement down as this is a one-time fix
    }
};
