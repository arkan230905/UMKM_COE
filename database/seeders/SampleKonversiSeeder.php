<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Satuan;
use App\Models\SatuanConversion;

class SampleKonversiSeeder extends Seeder
{
    public function run()
    {
        // Get or create sample units
        $sdm = Satuan::firstOrCreate(['kode' => 'SDM'], [
            'nama' => 'Sendok Makan',
            'tipe' => 'volume'
        ]);
        
        $ml = Satuan::firstOrCreate(['kode' => 'ML'], [
            'nama' => 'Mililiter',
            'tipe' => 'volume'
        ]);
        
        $gram = Satuan::firstOrCreate(['kode' => 'GR'], [
            'nama' => 'Gram',
            'tipe' => 'weight'
        ]);
        
        $kg = Satuan::firstOrCreate(['kode' => 'KG'], [
            'nama' => 'Kilogram',
            'tipe' => 'weight'
        ]);

        // Clear existing conversions
        SatuanConversion::query()->delete();

        // Create conversions
        $timestamp = now();
        $conversions = [
            // SDM to ML
            [
                'source_satuan_id' => $sdm->id,
                'target_satuan_id' => $ml->id,
                'amount_source' => 1,
                'amount_target' => 15,
                'is_inverse' => false,
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
            ],
            [
                'source_satuan_id' => $ml->id,
                'target_satuan_id' => $sdm->id,
                'amount_source' => 15,
                'amount_target' => 1,
                'is_inverse' => true,
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
            ],
            
            // ML to Gram (assuming 1 ml = 1 gram for water)
            [
                'source_satuan_id' => $ml->id,
                'target_satuan_id' => $gram->id,
                'amount_source' => 1,
                'amount_target' => 1,
                'is_inverse' => false,
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
            ],
            [
                'source_satuan_id' => $gram->id,
                'target_satuan_id' => $ml->id,
                'amount_source' => 1,
                'amount_target' => 1,
                'is_inverse' => true,
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
            ],
            
            // Gram to KG
            [
                'source_satuan_id' => $gram->id,
                'target_satuan_id' => $kg->id,
                'amount_source' => 1000,
                'amount_target' => 1,
                'is_inverse' => false,
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
            ],
            [
                'source_satuan_id' => $kg->id,
                'target_satuan_id' => $gram->id,
                'amount_source' => 1,
                'amount_target' => 1000,
                'is_inverse' => true,
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
            ],
        ];

        SatuanConversion::insert($conversions);
        
        $this->command->info('Sample konversi created successfully!');
    }
}
