<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class RajaOngkirService
{
    protected $apiKey;
    protected $baseUrl;
    protected $originCityId;

    public function __construct()
    {
        $this->apiKey = env('RAJAONGKIR_API_KEY');
        $this->baseUrl = env('RAJAONGKIR_BASE_URL', 'https://api.rajaongkir.com/starter');
        $this->originCityId = env('RAJAONGKIR_ORIGIN_CITY_ID', '152'); // Default: Jakarta Pusat
    }

    /**
     * Get list of cities
     */
    public function getCities()
    {
        // Fallback simulation if no API Key is provided
        if (empty($this->apiKey)) {
            return [
                ['city_id' => '152', 'city_name' => 'Jakarta Pusat', 'province' => 'DKI Jakarta'],
                ['city_id' => '151', 'city_name' => 'Jakarta Barat', 'province' => 'DKI Jakarta'],
                ['city_id' => '23', 'city_name' => 'Bandung', 'province' => 'Jawa Barat'],
                ['city_id' => '444', 'city_name' => 'Surabaya', 'province' => 'Jawa Timur'],
                ['city_id' => '501', 'city_name' => 'Yogyakarta', 'province' => 'DI Yogyakarta'],
                ['city_id' => '256', 'city_name' => 'Medan', 'province' => 'Sumatera Utara'],
                ['city_id' => '399', 'city_name' => 'Semarang', 'province' => 'Jawa Tengah'],
                ['city_id' => '115', 'city_name' => 'Depok', 'province' => 'Jawa Barat'],
                ['city_id' => '55', 'city_name' => 'Bekasi', 'province' => 'Jawa Barat']
            ];
        }

        try {
            $response = Http::withHeaders([
                'key' => $this->apiKey
            ])->get($this->baseUrl . '/city');

            if ($response->ok()) {
                return $response->json()['rajaongkir']['results'] ?? [];
            }
        } catch (\Exception $e) {
            Log::error('RajaOngkir getCities error: ' . $e->getMessage());
        }

        return [];
    }

    /**
     * Calculate Shipping Cost
     */
    public function calculateCost($destinationCityId, $weightGrams, $courier = 'jne')
    {
        $weightKg = max(1, ceil($weightGrams / 1000));

        // Fallback realistic simulation if API Key is not set
        if (empty($this->apiKey)) {
            $baseRates = [
                '152' => ['jne' => 9000, 'jnt' => 8500, 'sicepat' => 8000],  // Jakarta Pusat (Same city)
                '151' => ['jne' => 10000, 'jnt' => 9500, 'sicepat' => 9000], // Jakarta Barat
                '23'  => ['jne' => 18000, 'jnt' => 17000, 'sicepat' => 16000], // Bandung
                '444' => ['jne' => 28000, 'jnt' => 26000, 'sicepat' => 25000], // Surabaya
                '501' => ['jne' => 22000, 'jnt' => 21000, 'sicepat' => 20000], // Yogyakarta
                '256' => ['jne' => 38000, 'jnt' => 36500, 'sicepat' => 35000], // Medan
                '399' => ['jne' => 24000, 'jnt' => 23000, 'sicepat' => 22000], // Semarang
                '115' => ['jne' => 11000, 'jnt' => 10000, 'sicepat' => 9500],  // Depok
                '55'  => ['jne' => 12000, 'jnt' => 11000, 'sicepat' => 10500],  // Bekasi
            ];

            $rates = $baseRates[$destinationCityId] ?? ['jne' => 35000, 'jnt' => 33000, 'sicepat' => 32000];
            $cost = ($rates[strtolower($courier)] ?? 25000) * $weightKg;

            $courierNames = ['jne' => 'Jalur Nugraha Ekakurir (JNE)', 'jnt' => 'J&T Express', 'sicepat' => 'SiCepat Ekspres'];

            return [
                'code' => $courier,
                'name' => $courierNames[strtolower($courier)] ?? strtoupper($courier),
                'costs' => [
                    [
                        'service' => 'REG',
                        'description' => 'Layanan Reguler',
                        'cost' => [
                            [
                                'value' => $cost,
                                'etd' => '2-3 Hari',
                                'note' => ''
                            ]
                        ]
                    ]
                ]
            ];
        }

        try {
            $response = Http::withHeaders([
                'key' => $this->apiKey
            ])->post($this->baseUrl . '/cost', [
                'origin' => $this->originCityId,
                'destination' => $destinationCityId,
                'weight' => $weightGrams,
                'courier' => strtolower($courier)
            ]);

            if ($response->ok()) {
                return $response->json()['rajaongkir']['results'][0] ?? [];
            }
        } catch (\Exception $e) {
            Log::error('RajaOngkir calculateCost error: ' . $e->getMessage());
        }

        return [];
    }
}
