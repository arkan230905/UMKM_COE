<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        // Only for MySQL, not SQLite
        if (DB::getDriverName() === 'mysql') {
            DB::statement('SET FOREIGN_KEY_CHECKS=0;');
            DB::table('bop_budgets')->truncate();
            DB::table('bops')->truncate();
            DB::table('coas')->truncate();
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        }
    }

    public function down()
    {
        // No need to do anything on down
    }
};
