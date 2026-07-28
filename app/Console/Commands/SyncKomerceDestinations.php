<?php

namespace App\Console\Commands;

use App\Models\KomerceDestination;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use RuntimeException;
use Throwable;

/**
 * Class SyncKomerceDestinations
 *
 * Handles sync komerce destinations operations for the application.
 */
class SyncKomerceDestinations extends Command
{
    protected $signature = 'rajaongkir:sync-komerce-destinations
        {--province= : Sinkron satu ID provinsi Komerce}
        {--delay=300 : Jeda request dalam milidetik}
        {--max-requests=95 : Maksimal request API dalam satu run, 0 tanpa batas}
        {--force : Sync ulang district walaupun datanya sudah ada}';

    protected $description = 'Sinkronisasi destination ID Komerce RajaOngkir ke database lokal.';

    private string $baseUrl;
    private string $apiKey;
    private int $delayMilliseconds;
    private int $maxRequests;
    private int $requestCount = 0;
    private bool $stoppedByRequestBudget = false;

    /**
     * Handle.
     */
    public function handle(): int
    {
        $this->baseUrl = rtrim((string) config('services.rajaongkir.base_url'), '/');
        $this->apiKey = (string) config('services.rajaongkir.key');
        $this->delayMilliseconds = max((int) $this->option('delay'), 0);
        $this->maxRequests = max((int) $this->option('max-requests'), 0);

        if ($this->apiKey === '') {
            $this->error('RAJAONGKIR_API_KEY belum diatur.');
            return self::FAILURE;
        }

        try {
            $provinces = $this->request('/destination/province');
            $selectedProvince = $this->option('province');

            if ($selectedProvince !== null) {
                $provinces = array_values(array_filter(
                    $provinces,
                    fn (array $province): bool => (string) ($province['id'] ?? '') === (string) $selectedProvince
                ));
            }

            foreach ($provinces as $province) {
                $this->syncProvince($province);
            }

            $this->newLine();
            $this->info("Sinkronisasi destination Komerce selesai. Request API: {$this->requestCount}");

            return self::SUCCESS;
        } catch (Throwable $exception) {
            if ($this->stoppedByRequestBudget) {
                $this->newLine();
                $this->warn($exception->getMessage());
                $this->info("Progress aman tersimpan. Lanjutkan besok dengan command yang sama. Request API: {$this->requestCount}");

                return self::SUCCESS;
            }

            report($exception);
            $this->error($exception->getMessage());

            return self::FAILURE;
        }
    }

    /**
     * Sync province.
     */
    private function syncProvince(array $province): void
    {
        $provinceId = (int) $province['id'];
        $provinceName = trim((string) ($province['name'] ?? $province['province_name'] ?? ''));

        $this->info("Provinsi: {$provinceName}");

        foreach ($this->request("/destination/city/{$provinceId}") as $city) {
            $this->syncCity($provinceId, $provinceName, $city);
        }
    }

    /**
     * Sync city.
     */
    private function syncCity(int $provinceId, string $provinceName, array $city): void
    {
        $cityId = (int) $city['id'];
        $cityName = trim((string) ($city['name'] ?? $city['city_name'] ?? ''));

        $this->line("  Kota/Kabupaten: {$cityName}");

        foreach ($this->request("/destination/district/{$cityId}") as $district) {
            $this->syncDistrict($provinceId, $provinceName, $cityId, $cityName, $district);
        }
    }

    /**
     * Sync district.
     */
    private function syncDistrict(int $provinceId, string $provinceName, int $cityId, string $cityName, array $district): void
    {
        $districtId = (int) $district['id'];
        $districtName = trim((string) ($district['name'] ?? $district['district_name'] ?? ''));
        $rows = [];

        if (!$this->option('force') && $this->districtAlreadySynced($districtId)) {
            $this->line("    Lewati: {$districtName} sudah ada");
            return;
        }

        $this->line("    Sync: {$districtName}");

        foreach ($this->request("/destination/sub-district/{$districtId}") as $subdistrict) {
            $subdistrictId = (int) $subdistrict['id'];
            $subdistrictName = trim((string) ($subdistrict['name'] ?? $subdistrict['subdistrict_name'] ?? ''));
            $zipCode = $subdistrict['zip_code'] ?? $subdistrict['zip'] ?? null;

            $rows[] = [
                'id' => $subdistrictId,
                'province_id' => $provinceId,
                'province_name' => $provinceName,
                'city_id' => $cityId,
                'city_name' => $cityName,
                'district_id' => $districtId,
                'district_name' => $districtName,
                'subdistrict_name' => $subdistrictName,
                'zip_code' => $zipCode ?: null,
                'label' => implode(', ', array_filter([$subdistrictName, $districtName, $cityName, $provinceName, $zipCode])),
                'synced_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        if ($rows === []) {
            return;
        }

        KomerceDestination::query()->upsert($rows, ['id'], [
            'province_id',
            'province_name',
            'city_id',
            'city_name',
            'district_id',
            'district_name',
            'subdistrict_name',
            'zip_code',
            'label',
            'synced_at',
            'updated_at',
        ]);

        $this->linkLegacySubdistricts($districtName, $cityName, $rows);
    }

    /**
     * District already synced.
     */
    private function districtAlreadySynced(int $districtId): bool
    {
        return KomerceDestination::query()
            ->where('district_id', $districtId)
            ->exists();
    }

    /**
     * Link legacy subdistricts.
     */
    private function linkLegacySubdistricts(string $districtName, string $cityName, array $rows): void
    {
        if (!DB::getSchemaBuilder()->hasColumn('tb_ro_subdistricts', 'komerce_destination_id')) {
            return;
        }

        foreach ($rows as $row) {
            DB::table('tb_ro_subdistricts')
                ->join('tb_ro_cities', 'tb_ro_subdistricts.city_id', '=', 'tb_ro_cities.city_id')
                ->whereNull('tb_ro_subdistricts.komerce_destination_id')
                ->whereRaw('lower(tb_ro_subdistricts.subdistrict_name) = ?', [mb_strtolower($districtName)])
                ->whereRaw('lower(tb_ro_cities.city_name) like ?', ['%' . mb_strtolower($cityName) . '%'])
                ->update(['tb_ro_subdistricts.komerce_destination_id' => $row['id']]);
        }
    }

    /**
     * Request.
     */
    private function request(string $endpoint): array
    {
        if ($this->maxRequests > 0 && $this->requestCount >= $this->maxRequests) {
            $this->stoppedByRequestBudget = true;
            throw new RuntimeException("Stop aman: batas --max-requests={$this->maxRequests} tercapai sebelum memanggil {$endpoint}.");
        }

        $this->requestCount++;

        $response = Http::withHeaders([
            'key' => $this->apiKey,
            'Accept' => 'application/json',
        ])
            ->retry(3, 1000, throw: false)
            ->timeout(30)
            ->get($this->baseUrl . $endpoint);

        if (!$response->successful()) {
            throw new RuntimeException("Request gagal [{$response->status()}]: {$endpoint} - " . $response->body());
        }

        $payload = $response->json();
        usleep($this->delayMilliseconds * 1000);

        return is_array($payload['data'] ?? null) ? $payload['data'] : [];
    }
}
