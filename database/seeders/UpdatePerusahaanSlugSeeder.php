<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Perusahaan;

class UpdatePerusahaanSlugSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Update all perusahaan that don't have a slug
        $perusahaans = Perusahaan::whereNull('slug')->get();
        
        foreach ($perusahaans as $perusahaan) {
            // Generate slug from kode or nama
            $identifier = $perusahaan->kode ?: $perusahaan->nama;
            $slug = strtolower(str_replace(' ', '-', trim($identifier)));
            
            // Ensure slug is unique
            $originalSlug = $slug;
            $counter = 1;
            while (Perusahaan::where('slug', $slug)->where('id', '!=', $perusahaan->id)->exists()) {
                $slug = $originalSlug . '-' . $counter;
                $counter++;
            }
            
            $perusahaan->update(['slug' => $slug]);
            $this->command->info("Updated perusahaan '{$perusahaan->nama}' with slug '{$slug}'");
        }
        
        $this->command->info('All perusahaan slugs updated successfully!');
    }
}
