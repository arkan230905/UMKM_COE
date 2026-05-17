<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;

class IndonesiaAddressService
{
    private $baseUrl = 'https://emsifa.github.io/api-wilayah-indonesia/api';

    /**
     * Get all provinces
     */
    public function getProvinces()
    {
        return Cache::remember('indonesia_provinces', 86400, function () {
            try {
                $response = Http::timeout(10)->get($this->baseUrl . '/provinces.json');
                if ($response->successful()) {
                    return $response->json();
                }
            } catch (\Exception $e) {
                \Log::error('Error fetching provinces: ' . $e->getMessage());
            }
            return [];
        });
    }

    /**
     * Get cities by province ID
     */
    public function getCities($provinceId)
    {
        $cacheKey = 'indonesia_cities_' . $provinceId;
        return Cache::remember($cacheKey, 86400, function () use ($provinceId) {
            try {
                $response = Http::timeout(10)->get($this->baseUrl . '/regencies/' . $provinceId . '.json');
                if ($response->successful()) {
                    return $response->json();
                }
            } catch (\Exception $e) {
                \Log::error('Error fetching cities: ' . $e->getMessage());
            }
            return [];
        });
    }

    /**
     * Get districts by city ID
     */
    public function getDistricts($cityId)
    {
        $cacheKey = 'indonesia_districts_' . $cityId;
        return Cache::remember($cacheKey, 86400, function () use ($cityId) {
            try {
                $response = Http::timeout(10)->get($this->baseUrl . '/districts/' . $cityId . '.json');
                if ($response->successful()) {
                    return $response->json();
                }
            } catch (\Exception $e) {
                \Log::error('Error fetching districts: ' . $e->getMessage());
            }
            return [];
        });
    }

    /**
     * Get sub-districts by district ID
     */
    public function getSubDistricts($districtId)
    {
        $cacheKey = 'indonesia_sub_districts_' . $districtId;
        return Cache::remember($cacheKey, 86400, function () use ($districtId) {
            try {
                $response = Http::timeout(10)->get($this->baseUrl . '/villages/' . $districtId . '.json');
                if ($response->successful()) {
                    return $response->json();
                }
            } catch (\Exception $e) {
                \Log::error('Error fetching sub-districts: ' . $e->getMessage());
            }
            return [];
        });
    }

    /**
     * Get province name by ID
     */
    public function getProvinceName($provinceId)
    {
        $provinces = $this->getProvinces();
        foreach ($provinces as $province) {
            if ($province['id'] == $provinceId) {
                return $province['name'];
            }
        }
        return null;
    }

    /**
     * Get city name by ID
     */
    public function getCityName($cityId)
    {
        // Get all provinces first
        $provinces = $this->getProvinces();
        foreach ($provinces as $province) {
            $cities = $this->getCities($province['id']);
            foreach ($cities as $city) {
                if ($city['id'] == $cityId) {
                    return $city['name'];
                }
            }
        }
        return null;
    }

    /**
     * Get district name by ID
     */
    public function getDistrictName($districtId)
    {
        // This would require iterating through all cities
        // For now, we'll return null and rely on frontend caching
        return null;
    }

    /**
     * Get sub-district name by ID
     */
    public function getSubDistrictName($subDistrictId)
    {
        // This would require iterating through all districts
        // For now, we'll return null and rely on frontend caching
        return null;
    }
}
