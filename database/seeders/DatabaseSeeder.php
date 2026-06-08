<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            UserSeeder::class,
            CompanySeeder::class,
            CoaSeeder::class, // COA untuk Ayam Goreng Bundo (84 COA standar)
        ]);
    }
}
