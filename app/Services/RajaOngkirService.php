<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;

class RajaOngkirService
{
    private $baseUrl = 'https://api.rajaongkir.com/starter';
    private $apiKey = 'f6d56281e3d4a3d2d9c8b7a6f5e4d3c2'; // Free tier API key (public demo)

    /**
     * Get all provinces
     */
    public function getProvinces()
    {
        return Cache::remember('rajaongkir_provinces', 86400, function () {
            try {
                $response = Http::withHeaders([
                    'key' => $this->apiKey
                ])->timeout(10)->get($this->baseUrl . '/province');

                if ($response->successful()) {
                    $data = $response->json();
                    if ($data['status'] == 200 && isset($data['results'])) {
                        return $data['results'];
                    }
                }
            } catch (\Exception $e) {
                \Log::error('Error fetching provinces from RajaOngkir: ' . $e->getMessage());
            }
            return [];
        });
    }

    /**
     * Get cities by province ID
     */
    public function getCities($provinceId)
    {
        $cacheKey = 'rajaongkir_cities_' . $provinceId;
        return Cache::remember($cacheKey, 86400, function () use ($provinceId) {
            try {
                $response = Http::withHeaders([
                    'key' => $this->apiKey
                ])->timeout(10)->get($this->baseUrl . '/city', [
                    'province' => $provinceId
                ]);

                if ($response->successful()) {
                    $data = $response->json();
                    if ($data['status'] == 200 && isset($data['results'])) {
                        return $data['results'];
                    }
                }
            } catch (\Exception $e) {
                \Log::error('Error fetching cities from RajaOngkir: ' . $e->getMessage());
            }
            return [];
        });
    }

    /**
     * Get subdistricts by city ID
     */
    public function getSubDistricts($cityId)
    {
        $cacheKey = 'rajaongkir_subdistricts_' . $cityId;
        return Cache::remember($cacheKey, 86400, function () use ($cityId) {
            try {
                $response = Http::withHeaders([
                    'key' => $this->apiKey
                ])->timeout(10)->get($this->baseUrl . '/subdistrict', [
                    'city' => $cityId
                ]);

                if ($response->successful()) {
                    $data = $response->json();
                    if ($data['status'] == 200 && isset($data['results'])) {
                        return $data['results'];
                    }
                }
            } catch (\Exception $e) {
                \Log::error('Error fetching subdistricts from RajaOngkir: ' . $e->getMessage());
            }
            return [];
        });
    }

    /**
     * Calculate shipping cost
     * @param int $originCityId - Origin city ID (from RajaOngkir)
     * @param int $destinationCityId - Destination city ID (from RajaOngkir)
     * @param int $weight - Weight in grams
     * @param string $courier - Courier code (jne, tiki, pos)
     */
    public function calculateShipping($originCityId, $destinationCityId, $weight = 1000, $courier = 'jne')
    {
        try {
            $response = Http::withHeaders([
                'key' => $this->apiKey
            ])->timeout(10)->post($this->baseUrl . '/cost', [
                'origin' => $originCityId,
                'destination' => $destinationCityId,
                'weight' => $weight,
                'courier' => $courier
            ]);

            if ($response->successful()) {
                $data = $response->json();
                if ($data['status'] == 200 && isset($data['results'])) {
                    return [
                        'success' => true,
                        'data' => $data['results']
                    ];
                }
            }
        } catch (\Exception $e) {
            \Log::error('Error calculating shipping from RajaOngkir: ' . $e->getMessage());
        }

        return [
            'success' => false,
            'message' => 'Gagal menghitung ongkir'
        ];
    }
}
