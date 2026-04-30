<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Perusahaan;

class SatuanDefaultSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get all companies
        $companies = Perusahaan::all();
        
        if ($companies->isEmpty()) {
            $this->command->info('No companies found. Please create companies first.');
            return;
        }

        // Default Satuan data provided by user
        $defaultSatuanData = [
            ['kode' => 'ONS', 'nama' => 'Ons'],
            ['kode' => 'KG', 'nama' => 'Kilogram'],
            ['kode' => 'ML', 'nama' => 'Mililiter'],
            ['kode' => 'G', 'nama' => 'Gram'],
            ['kode' => 'LTR', 'nama' => 'Liter'],
            ['kode' => 'PTG', 'nama' => 'Potong'],
            ['kode' => 'EKOR', 'nama' => 'Ekor'],
            ['kode' => 'SDT', 'nama' => 'Sendok Teh'],
            ['kode' => 'SDM', 'nama' => 'Sendok Makan'],
            ['kode' => 'PCS', 'nama' => 'Pieces'],
            ['kode' => 'BNGKS', 'nama' => 'Bungkus'],
            ['kode' => 'CUP', 'nama' => 'Cup'],
            ['kode' => 'GL', 'nama' => 'Galon'],
            ['kode' => 'TBG', 'nama' => 'Tabung'],
            ['kode' => 'SNG', 'nama' => 'Siung'],
            ['kode' => 'KLG', 'nama' => 'Kaleng'],
        ];

        foreach ($companies as $company) {
            $this->command->info("Creating Satuan for company: {$company->nama} (ID: {$company->id})");
            
            foreach ($defaultSatuanData as $satuan) {
                // Check if Satuan already exists for this company
                $existingSatuan = DB::table('satuans')
                    ->where('user_id', $company->id)
                    ->where('kode', $satuan['kode'])
                    ->first();
                
                if (!$existingSatuan) {
                    DB::table('satuans')->insert([
                        'kode' => $satuan['kode'],
                        'nama' => $satuan['nama'],
                        'user_id' => $company->id,
                        'is_active' => true,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }
        }

        $this->command->info('Default Satuan seeder completed successfully!');
    }
}
