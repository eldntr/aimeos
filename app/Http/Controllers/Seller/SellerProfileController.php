<?php

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SellerProfileController extends Controller
{
    /**
     * Re-upload KTP for rejected sellers.
     */
    public function reuploadKtp(Request $request)
    {
        $request->validate([
            'ktp_image' => ['required', 'image', 'max:5120'], // Max 5MB
        ]);

        $user = $request->user();

        if ($user->seller_status === 'approved') {
            return response()->json(['message' => 'Your seller account is already approved.'], 400);
        }

        $fileService = new \App\Services\FileServerService();
        $ktpUrl = $fileService->uploadFile($request->file('ktp_image'));
        $fileService->triggerCompression();

        $user->ktp_url = $ktpUrl;
        $user->seller_status = 'pending';
        $user->rejection_reason = null;
        $user->save();

        return response()->json(['message' => 'KTP re-uploaded successfully. Your status is now pending review.']);
    }
    protected function getNumericSiteId($siteid)
    {
        $parts = array_filter(explode('.', trim($siteid, '.')));
        return end($parts);
    }

    protected function getSellerContext()
    {
        $context = app('aimeos.context')->get(false);
        $user = auth()->user();
        if ($user && $user->siteid) {
            $siteManager = \Aimeos\MShop::create($context, 'locale/site');
            $numericId = $this->getNumericSiteId($user->siteid);
            $siteItem = $siteManager->get($numericId);
            $context->setLocale(app('aimeos.locale')->get($context, $siteItem->getCode()));
        }
        return $context;
    }

    public function getShop(Request $request)
    {
        $user = $request->user();
        if (!$user->siteid) {
            return response()->json(['message' => 'Seller shop not initialized.'], 404);
        }
        
        $context = app('aimeos.context')->get(false);
        $siteManager = \Aimeos\MShop::create($context, 'locale/site');
        $numericId = $this->getNumericSiteId($user->siteid);
        $site = $siteManager->get($numericId);
        
        return response()->json([
            'data' => [
                'name' => $site->getLabel(),
                'logo' => $site->getLogo(),
                'config' => $site->getConfig(),
                'shipping_couriers' => $this->enabledCourierCodes($user->siteid),
            ]
        ]);
    }

    public function updateShop(Request $request)
    {
        $rules = [
            'name' => 'nullable|string|max:255',
            'logo' => 'nullable|image|max:5120',
            'banner' => 'nullable|image|max:5120',
            'address' => 'nullable|string',
            'shipping_subdistrict_id' => 'nullable|integer|exists:tb_ro_subdistricts,subdistrict_id',
            'shipping_komerce_destination_id' => 'nullable|integer|exists:komerce_destinations,id',
            'shipping_postal' => 'nullable|string|max:20',
            'shipping_couriers' => 'nullable|array',
            'shipping_couriers.*' => 'string|exists:shipping_couriers,code',
        ];
        
        if ($request->filled('address') && !$request->filled('shipping_komerce_destination_id')) {
            $rules['shipping_city_id'] = 'required|integer|exists:tb_ro_cities,city_id';
        } else {
            $rules['shipping_city_id'] = 'nullable|integer|exists:tb_ro_cities,city_id';
        }

        $request->validate($rules);

        $user = $request->user();
        if (!$user->siteid) {
            return response()->json(['message' => 'Seller shop not initialized.'], 404);
        }

        $context = app('aimeos.context')->get(false);
        $siteManager = \Aimeos\MShop::create($context, 'locale/site');
        $numericId = $this->getNumericSiteId($user->siteid);
        
        $siteManager->begin();
        try {
            $site = $siteManager->get($numericId);
            
            if ($request->has('name')) {
                $site->setLabel($request->name);
            }
            
            if ($request->hasFile('logo')) {
                $fileService = new \App\Services\FileServerService();
                $logoUrl = $fileService->uploadFile($request->file('logo'));
                $fileService->triggerCompression();
                $site->setLogo($logoUrl);
            }

            if ($request->hasFile('banner')) {
                $fileService = new \App\Services\FileServerService();
                $bannerUrl = $fileService->uploadFile($request->file('banner'));
                $fileService->triggerCompression();
                $config = $site->getConfig();
                $config['banner'] = $bannerUrl;
                $site->setConfig($config);
            }

            if ($request->has('address')) {
                $shippingLocation = $this->resolveShippingLocation($request);
                $config = $site->getConfig();
                $config['address'] = $request->address;
                $config['shipping.origin_id'] = $shippingLocation['komerce_destination_id'] ? (string) $shippingLocation['komerce_destination_id'] : '';
                $config['shipping.komerce_destination_id'] = $shippingLocation['komerce_destination_id'] ? (string) $shippingLocation['komerce_destination_id'] : '';
                $config['shipping.city_id'] = (string) $shippingLocation['city_id'];
                $config['shipping.subdistrict_id'] = $shippingLocation['subdistrict_id'] ? (string) $shippingLocation['subdistrict_id'] : '';
                $config['shipping.city'] = $shippingLocation['city_name'];
                $config['shipping.province'] = $shippingLocation['province_name'];
                $config['shipping.subdistrict'] = $shippingLocation['subdistrict_name'] ?? '';
                $config['shipping.postal'] = $request->shipping_postal ?: ($shippingLocation['postal_code'] ?? '');
                $site->setConfig($config);
            }

            if ($request->has('shipping_couriers')) {
                $this->syncSellerCouriers($user->siteid, $request->shipping_couriers ?? []);
            }
            
            $siteManager->save($site);
            $siteManager->commit();

            return response()->json([
                'message' => 'Profil toko berhasil diperbarui.',
                'data' => [
                    'name' => $site->getLabel(),
                    'logo' => $site->getLogo(),
                    'config' => $site->getConfig(),
                    'shipping_couriers' => $this->enabledCourierCodes($user->siteid),
                ]
            ]);
        } catch (\Exception $e) {
            $siteManager->rollback();
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    public function updateBank(Request $request)
    {
        $request->validate([
            'bank_name' => 'required|string|max:255',
            'account_number' => 'required|string|max:255',
            'account_name' => 'required|string|max:255',
        ]);

        $user = $request->user();
        if (!$user->siteid) {
            return response()->json(['message' => 'Seller shop not initialized.'], 404);
        }

        $context = app('aimeos.context')->get(false);
        $siteManager = \Aimeos\MShop::create($context, 'locale/site');
        $numericId = $this->getNumericSiteId($user->siteid);
        
        $siteManager->begin();
        try {
            $site = $siteManager->get($numericId);
            
            $config = $site->getConfig();
            $config['bank.name'] = $request->bank_name;
            $config['bank.account_number'] = $request->account_number;
            $config['bank.account_name'] = $request->account_name;
            
            $site->setConfig($config);
            $siteManager->save($site);
            $siteManager->commit();

            return response()->json([
                'message' => 'Informasi bank berhasil diperbarui.',
                'data' => [
                    'config' => $site->getConfig(),
                ]
            ]);
        } catch (\Exception $e) {
            $siteManager->rollback();
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    private function resolveShippingLocation(Request $request): array
    {
        $komerce = $this->resolveKomerceDestination($request);

        if ($komerce) {
            $subdistrictRecord = DB::table('tb_ro_subdistricts')
                ->where('komerce_destination_id', $komerce['id'])
                ->first();
            if ($subdistrictRecord) {
                $cityRecord = DB::table('tb_ro_cities')
                    ->join('tb_ro_provinces', 'tb_ro_cities.province_id', '=', 'tb_ro_provinces.province_id')
                    ->where('tb_ro_cities.city_id', $subdistrictRecord->city_id)
                    ->first([
                        'tb_ro_cities.city_id',
                        'tb_ro_cities.city_name',
                        'tb_ro_cities.postal_code',
                        'tb_ro_provinces.province_name'
                    ]);
                if ($cityRecord) {
                    return [
                        'city_id' => (int) $cityRecord->city_id,
                        'city_name' => $komerce['city_name'] ?: $cityRecord->city_name,
                        'province_name' => $komerce['province_name'] ?: $cityRecord->province_name,
                        'postal_code' => $komerce['zip_code'] ?: $cityRecord->postal_code,
                        'subdistrict_id' => (int) $subdistrictRecord->subdistrict_id,
                        'subdistrict_name' => $komerce['subdistrict_name'] ?: $subdistrictRecord->subdistrict_name,
                        'komerce_destination_id' => (int) $komerce['id'],
                    ];
                }
            }
            
            return [
                'city_id' => $komerce['city_id'] ?? null,
                'city_name' => $komerce['city_name'],
                'province_name' => $komerce['province_name'],
                'postal_code' => $komerce['zip_code'],
                'subdistrict_id' => $komerce['district_id'] ?? null,
                'subdistrict_name' => $komerce['subdistrict_name'] ?? $komerce['district_name'],
                'komerce_destination_id' => (int) $komerce['id'],
            ];
        }

        $city = DB::table('tb_ro_cities')
            ->join('tb_ro_provinces', 'tb_ro_cities.province_id', '=', 'tb_ro_provinces.province_id')
            ->where('tb_ro_cities.city_id', (int) $request->shipping_city_id)
            ->first([
                'tb_ro_cities.city_id',
                'tb_ro_cities.city_name',
                'tb_ro_cities.postal_code',
                'tb_ro_provinces.province_name',
            ]);

        if (!$city) {
            abort(422, 'Pilih kota/kabupaten asal dari daftar lokasi RajaOngkir.');
        }

        $subdistrict = null;
        if ($request->filled('shipping_subdistrict_id')) {
            $subdistrict = DB::table('tb_ro_subdistricts')
                ->where('city_id', $city->city_id)
                ->where('subdistrict_id', (int) $request->shipping_subdistrict_id)
                ->first(['subdistrict_id', 'subdistrict_name', 'komerce_destination_id']);
        }

        return [
            'city_id' => (int) $city->city_id,
            'city_name' => $komerce['city_name'] ?? $city->city_name,
            'province_name' => $komerce['province_name'] ?? $city->province_name,
            'postal_code' => $komerce['zip_code'] ?? $city->postal_code,
            'subdistrict_id' => $subdistrict?->subdistrict_id,
            'subdistrict_name' => $komerce['subdistrict_name'] ?? $subdistrict?->subdistrict_name,
            'komerce_destination_id' => $komerce['id'] ?? $subdistrict?->komerce_destination_id ?? null,
        ];
    }

    private function resolveKomerceDestination(Request $request): ?array
    {
        if (!$request->filled('shipping_komerce_destination_id') || !\Illuminate\Support\Facades\Schema::hasTable('komerce_destinations')) {
            return null;
        }

        $destination = DB::table('komerce_destinations')
            ->where('id', (int) $request->shipping_komerce_destination_id)
            ->first(['id', 'province_name', 'city_name', 'district_name', 'subdistrict_name', 'zip_code']);

        return $destination ? (array) $destination : null;
    }

    private function enabledCourierCodes(string $siteid): array
    {
        if (!\Illuminate\Support\Facades\Schema::hasTable('seller_shipping_couriers')) {
            return [];
        }

        return DB::table('seller_shipping_couriers')
            ->where('siteid', $siteid)
            ->pluck('courier_code')
            ->map(fn ($code) => (string) $code)
            ->all();
    }

    private function syncSellerCouriers(string $siteid, array $codes): void
    {
        if (!\Illuminate\Support\Facades\Schema::hasTable('seller_shipping_couriers')) {
            return;
        }

        $codes = DB::table('shipping_couriers')
            ->whereIn('code', array_values(array_unique($codes)))
            ->where('active', true)
            ->where('supports_domestic_cost', true)
            ->pluck('code')
            ->all();

        DB::table('seller_shipping_couriers')->where('siteid', $siteid)->delete();

        if ($codes === []) {
            return;
        }

        $now = now();
        DB::table('seller_shipping_couriers')->insert(array_map(fn ($code) => [
            'siteid' => $siteid,
            'courier_code' => $code,
            'created_at' => $now,
            'updated_at' => $now,
        ], $codes));
    }
}
