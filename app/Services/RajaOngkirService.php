<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class RajaOngkirService
{
    protected $apiKey;
    protected $baseUrl;

    public function __construct()
    {
        $this->apiKey = config('services.rajaongkir.key');
        $this->baseUrl = rtrim(config('services.rajaongkir.base_url', 'https://rajaongkir.komerce.id/api/v1'), '/');
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
    public function calculateCost($destinationId, $weightGrams, $courier = 'jne', ?string $originId = null, string $price = 'lowest')
    {
        $weightKg = max(1, ceil($weightGrams / 1000));
        if (!$originId) {
            Log::warning('RajaOngkir calculateCost skipped: origin city is empty.');
            return [];
        }

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

            $rates = $baseRates[$destinationId] ?? ['jne' => 35000, 'jnt' => 33000, 'sicepat' => 32000];
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
                'key' => $this->apiKey,
                'Content-Type' => 'application/x-www-form-urlencoded',
            ])->asForm()->post($this->baseUrl . '/calculate/domestic-cost', [
                'origin' => $originId,
                'destination' => $destinationId,
                'weight' => $weightGrams,
                'courier' => strtolower($courier),
                'price' => $price,
            ]);

            if ($response->ok()) {
                return $this->normalizeKomerceCostResponse($response->json(), $courier);
            }
        } catch (\Exception $e) {
            Log::error('RajaOngkir calculateCost error: ' . $e->getMessage());
        }

        return [];
    }

    public function calculateDomesticCost(string $originId, string $destinationId, int $weightGrams, string $courier = 'jne', string $price = 'lowest'): array
    {
        return $this->calculateCost($destinationId, $weightGrams, $courier, $originId, $price);
    }

    public function searchDomesticDestinations(string $search, int $limit = 10, int $offset = 0): array
    {
        if (empty($this->apiKey)) {
            Log::warning('RajaOngkir destination search skipped: API key is empty.');
            return [];
        }

        try {
            $response = Http::withHeaders([
                'key' => $this->apiKey,
                'Accept' => 'application/json',
            ])->get($this->baseUrl . '/destination/domestic-destination', [
                'search' => $search,
                'limit' => $limit,
                'offset' => $offset,
            ]);

            if ($response->ok()) {
                return $response->json('data') ?? [];
            }

            Log::warning('RajaOngkir destination search failed: ' . $response->body());
        } catch (\Exception $e) {
            Log::error('RajaOngkir destination search error: ' . $e->getMessage());
        }

        return [];
    }

    private function normalizeKomerceCostResponse(array $payload, string $courier): array
    {
        $services = $payload['data'] ?? [];
        if (!is_array($services) || $services === []) {
            return [];
        }

        $first = $services[0];

        return [
            'code' => $first['code'] ?? $courier,
            'name' => $first['name'] ?? strtoupper($courier),
            'costs' => array_map(function ($item) {
                return [
                    'service' => $item['service'] ?? 'REG',
                    'description' => $item['description'] ?? '',
                    'cost' => [
                        [
                            'value' => (float) ($item['cost'] ?? 0),
                            'etd' => $item['etd'] ?? '',
                            'note' => '',
                        ],
                    ],
                ];
            }, $services),
        ];
    }
}
