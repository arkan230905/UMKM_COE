<?php

namespace App\Console\Commands;

use App\Models\Province;
use App\Models\City;
use App\Models\District;
use App\Models\SubDistrict;
use Illuminate\Console\Command;

class SeedIndonesianAddresses extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:seed-indonesian-addresses';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Seed Indonesian provinces, cities, districts, and sub-districts';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Seeding Indonesian addresses...');

        // Seed Jawa Barat
        $jawaBarat = Province::firstOrCreate(
            ['code' => '32'],
            ['name' => 'Jawa Barat']
        );

        // Bandung City
        $bandung = City::firstOrCreate(
            ['code' => '3273'],
            ['province_id' => $jawaBarat->id, 'name' => 'Kota Bandung']
        );

        // Bandung Districts
        $sumurBandung = District::firstOrCreate(
            ['code' => '3273010'],
            ['city_id' => $bandung->id, 'name' => 'Sumur Bandung']
        );

        $braga = District::firstOrCreate(
            ['code' => '3273020'],
            ['city_id' => $bandung->id, 'name' => 'Braga']
        );

        // Sumur Bandung Sub-districts
        SubDistrict::firstOrCreate(
            ['code' => '3273010001'],
            ['district_id' => $sumurBandung->id, 'name' => 'Braga', 'latitude' => -6.9170, 'longitude' => 107.6085]
        );
        SubDistrict::firstOrCreate(
            ['code' => '3273010002'],
            ['district_id' => $sumurBandung->id, 'name' => 'Jalan Braga', 'latitude' => -6.9175, 'longitude' => 107.6090]
        );
        SubDistrict::firstOrCreate(
            ['code' => '3273010003'],
            ['district_id' => $sumurBandung->id, 'name' => 'Kompleks Asrama Putri Telkom', 'latitude' => -6.9180, 'longitude' => 107.6095]
        );

        // Braga Sub-districts
        SubDistrict::firstOrCreate(
            ['code' => '3273020001'],
            ['district_id' => $braga->id, 'name' => 'Jalan Braga Utama', 'latitude' => -6.9165, 'longitude' => 107.6080]
        );
        SubDistrict::firstOrCreate(
            ['code' => '3273020002'],
            ['district_id' => $braga->id, 'name' => 'Jalan Braga Selatan', 'latitude' => -6.9185, 'longitude' => 107.6100]
        );

        // Jakarta
        $dkiJakarta = Province::firstOrCreate(
            ['code' => '31'],
            ['name' => 'DKI Jakarta']
        );

        $jakartaPusat = City::firstOrCreate(
            ['code' => '3171'],
            ['province_id' => $dkiJakarta->id, 'name' => 'Jakarta Pusat']
        );

        $tanahAbang = District::firstOrCreate(
            ['code' => '3171010'],
            ['city_id' => $jakartaPusat->id, 'name' => 'Tanah Abang']
        );

        SubDistrict::firstOrCreate(
            ['code' => '3171010001'],
            ['district_id' => $tanahAbang->id, 'name' => 'Jalan Sudirman', 'latitude' => -6.2088, 'longitude' => 106.8456]
        );
        SubDistrict::firstOrCreate(
            ['code' => '3171010002'],
            ['district_id' => $tanahAbang->id, 'name' => 'Bendungan Hilir', 'latitude' => -6.2100, 'longitude' => 106.8470]
        );

        // Jawa Timur
        $jawaTimur = Province::firstOrCreate(
            ['code' => '35'],
            ['name' => 'Jawa Timur']
        );

        $surabaya = City::firstOrCreate(
            ['code' => '3578'],
            ['province_id' => $jawaTimur->id, 'name' => 'Kota Surabaya']
        );

        $tegalsari = District::firstOrCreate(
            ['code' => '3578010'],
            ['city_id' => $surabaya->id, 'name' => 'Tegalsari']
        );

        SubDistrict::firstOrCreate(
            ['code' => '3578010001'],
            ['district_id' => $tegalsari->id, 'name' => 'Jalan Kaliasin', 'latitude' => -7.2575, 'longitude' => 112.7521]
        );
        SubDistrict::firstOrCreate(
            ['code' => '3578010002'],
            ['district_id' => $tegalsari->id, 'name' => 'Tunjungan Plaza', 'latitude' => -7.2500, 'longitude' => 112.7500]
        );

        // DI Yogyakarta
        $yogyakarta = Province::firstOrCreate(
            ['code' => '34'],
            ['name' => 'DI Yogyakarta']
        );

        $kotaYogyakarta = City::firstOrCreate(
            ['code' => '3471'],
            ['province_id' => $yogyakarta->id, 'name' => 'Kota Yogyakarta']
        );

        $gedongtengen = District::firstOrCreate(
            ['code' => '3471010'],
            ['city_id' => $kotaYogyakarta->id, 'name' => 'Gedongtengen']
        );

        SubDistrict::firstOrCreate(
            ['code' => '3471010001'],
            ['district_id' => $gedongtengen->id, 'name' => 'Malioboro', 'latitude' => -7.7956, 'longitude' => 110.3695]
        );
        SubDistrict::firstOrCreate(
            ['code' => '3471010002'],
            ['district_id' => $gedongtengen->id, 'name' => 'Jalan Malioboro', 'latitude' => -7.7960, 'longitude' => 110.3700]
        );

        $this->info('✓ Indonesian addresses seeded successfully!');
    }
}
