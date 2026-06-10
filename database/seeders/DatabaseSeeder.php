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
            CoaTemplateSeeder::class,
            JasukeCoaSeeder::class,                  // Memastikan COA Jasuke masuk terakhir
            CoaAyamSeeder::class,                    // COA untuk usaha Ayam Crispy (81 COA)
            CoaJagungSeeder::class,                  // COA untuk usaha Jagung (7 COA: Diskon, WIP, HPP, Banks)
            FixMissingWipCoasForUsers::class,        // Fix missing WIP COAs (1171-1173) untuk user yang punya 117
            AddBankAccountsToAllUsers::class,        // Add 3 bank accounts: BCA (1111), Mandiri (1112), BRI (1113)
            CopyCoaTemplateToExistingUsers::class,   // Copy all template COAs to existing users
        ]);
    }
}
