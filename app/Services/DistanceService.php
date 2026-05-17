<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class DistanceService
{
    /**
     * Get address suggestions from Nominatim
     * 
     * @param string $query Search query
     * @return array Array of suggestions
     */
    public function getAddressSuggestions($query)
    {
        try {
            $response = Http::withoutVerifying()->timeout(10)->get('https://nominatim.openstreetmap.org/search', [
                'q' => $query . ', Indonesia',
                'format' => 'json',
                'limit' => 10,
                'addressdetails' => 1,
            ]);

            if ($response->successful()) {
                $data = $response->json();
                $suggestions = [];

                foreach ($data as $result) {
                    $suggestions[] = [
                        'display_name' => $result['display_name'] ?? $query,
                        'latitude' => (float)$result['lat'],
                        'longitude' => (float)$result['lon'],
                        'address' => $result['address'] ?? [],
                    ];
                }

                return $suggestions;
            }

            return [];
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * Calculate distance between two coordinates using Haversine formula
     * Returns distance in kilometers (straight line distance)
     * 
     * @param float $lat1 Origin latitude
     * @param float $lon1 Origin longitude
     * @param float $lat2 Destination latitude
     * @param float $lon2 Destination longitude
     * @return float Distance in kilometers
     */
    public function calculateHaversineDistance($lat1, $lon1, $lat2, $lon2)
    {
        $earthRadius = 6371; // Radius of the earth in km
        
        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);
        
        $a = sin($dLat / 2) * sin($dLat / 2) +
             cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
             sin($dLon / 2) * sin($dLon / 2);
        
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
        $distance = $earthRadius * $c;
        
        // Return straight line distance (most accurate for local delivery)
        return $distance;
    }

    /**
     * Geocode address using Nominatim (OpenStreetMap)
     * Mendukung alamat spesifik, bukan hanya kota
     * 
     * @param string $address Alamat lengkap
     * @return array|null Koordinat atau null jika tidak ditemukan
     */
    public function geocodeAddress($address)
    {
        try {
            $response = Http::withoutVerifying()->timeout(10)->get('https://nominatim.openstreetmap.org/search', [
                'q' => $address . ', Indonesia',
                'format' => 'json',
                'limit' => 1,
                'addressdetails' => 1,
            ]);

            if ($response->successful()) {
                $data = $response->json();

                if (!empty($data)) {
                    $result = $data[0];
                    return [
                        'success' => true,
                        'latitude' => (float)$result['lat'],
                        'longitude' => (float)$result['lon'],
                        'display_name' => $result['display_name'] ?? $address,
                        'address' => $result['address'] ?? [],
                    ];
                }
            }

            return [
                'success' => false,
                'message' => 'Alamat tidak ditemukan di OpenStreetMap',
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Error geocoding: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Calculate distance from store to customer address
     * Menggunakan Nominatim untuk geocode alamat spesifik
     * 
     * @param float $storeLat Store latitude
     * @param float $storeLon Store longitude
     * @param string $customerAddress Customer address (bisa alamat spesifik)
     * @return array Result dengan jarak dan info
     */
    public function calculateDistanceToAddress($storeLat, $storeLon, $customerAddress)
    {
        // Geocode customer address menggunakan Nominatim
        $geocodeResult = $this->geocodeAddress($customerAddress);
        
        if (!$geocodeResult['success']) {
            return [
                'success' => false,
                'message' => $geocodeResult['message'] ?? 'Alamat tidak ditemukan',
            ];
        }
        
        // Calculate distance using Haversine formula
        $distance = $this->calculateHaversineDistance(
            $storeLat,
            $storeLon,
            $geocodeResult['latitude'],
            $geocodeResult['longitude']
        );
        
        return [
            'success' => true,
            'distance_km' => round($distance, 2),
            'distance_text' => round($distance, 2) . ' km',
            'destination_address' => $geocodeResult['display_name'],
            'destination_lat' => $geocodeResult['latitude'],
            'destination_lon' => $geocodeResult['longitude'],
        ];
    }
}
