<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Perusahaan;
use App\Models\User;

class CompanySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('Creating default companies for multi-tenant setup...');

        // Create default company if not exists
        $defaultCompany = Perusahaan::firstOrCreate([
            'kode' => 'UMKM001'
        ], [
            'nama' => 'UMKM COE',
            'email' => 'info@umkmcoe.com',
            'telepon' => '08123456789',
            'alamat' => 'Jl. Contoh No. 123, Jakarta, Indonesia',
            'catalog_description' => 'Kami adalah perusahaan UMKM yang berdedikasi untuk menyediakan produk berkualitas tinggi dengan harga terjangkau. Kami mengutamakan kepuasan pelanggan dan selalu berinovasi dalam mengembangkan produk kami.',
            'maps_link' => 'https://maps.google.com/?q=Jakarta',
            'latitude' => -6.2088,
            'longitude' => 106.8456,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->command->info("Default company created: {$defaultCompany->nama} (ID: {$defaultCompany->id})");

        // Create admin user if not exists and link to default company
        $adminUser = User::find(1);
        if (!$adminUser) {
            $adminUser = User::create([
                'name' => 'Admin UMKM COE',
                'email' => 'admin@umkmcoe.com',
                'password' => bcrypt('password'),
                'email_verified_at' => now(),
                'perusahaan_id' => $defaultCompany->id,
                'company_id' => $defaultCompany->id,
                'role' => 'owner',
            ]);
            $this->command->info("Admin user created: {$adminUser->name}");
        } elseif (!$adminUser->perusahaan_id) {
            $adminUser->update([
                'perusahaan_id' => $defaultCompany->id,
                'company_id' => $defaultCompany->id,
            ]);
            $this->command->info("Admin user linked to company: {$adminUser->name}");
        }

        // Create sample companies for testing multi-tenant
        $sampleCompanies = [
            [
                'kode' => 'DEMO001',
                'nama' => 'Demo Company A',
                'email' => 'info@demo-a.com',
                'telepon' => '08123456780',
                'alamat' => 'Jl. Demo A No. 456, Jakarta',
                'catalog_description' => 'Demo Company A for multi-tenant testing',
            ],
            [
                'kode' => 'DEMO002', 
                'nama' => 'Demo Company B',
                'email' => 'info@demo-b.com',
                'telepon' => '08123456781',
                'alamat' => 'Jl. Demo B No. 789, Jakarta',
                'catalog_description' => 'Demo Company B for multi-tenant testing',
            ]
        ];

        foreach ($sampleCompanies as $companyData) {
            $company = Perusahaan::firstOrCreate([
                'kode' => $companyData['kode']
            ], array_merge($companyData, [
                'maps_link' => 'https://maps.google.com/?q=Jakarta',
                'latitude' => -6.2088,
                'longitude' => 106.8456,
                'created_at' => now(),
                'updated_at' => now(),
            ]));

            $this->command->info("Sample company created: {$company->nama} (ID: {$company->id})");
            
            // Create a user for each sample company
            $user = User::firstOrCreate([
                'email' => 'admin-' . strtolower(str_replace(' ', '', $company->nama)) . '@demo.com'
            ], [
                'name' => 'Admin ' . $company->nama,
                'password' => bcrypt('password'),
                'email_verified_at' => now(),
                'perusahaan_id' => $company->id,
                'company_id' => $company->id,
                'role' => 'owner',
            ]);
            
            $this->command->info("User created for company: {$user->name}");
        }

        $this->command->info('Company seeder completed successfully!');
    }
}
