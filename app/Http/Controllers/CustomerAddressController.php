<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CustomerAddressController extends Controller
{
    private function getContext()
    {
        $context = app('aimeos.context')->get(false);
        $localeManager = \Aimeos\MShop::create($context, 'locale');
        $localeItem = $localeManager->bootstrap('default', '', '', false);
        
        $siteManager = \Aimeos\MShop::create($context, 'locale/site');
        $siteItem = $siteManager->create();
        $siteItem->setId('1.');
        $siteItem->setCode('default');
        
        $ref = new \ReflectionClass($localeItem);
        if ($ref->hasProperty('siteItem')) {
            $prop = $ref->getProperty('siteItem');
            $prop->setAccessible(true);
            $prop->setValue($localeItem, $siteItem);
        }
        
        $context->setLocale($localeItem);
        return $context;
    }

    private function getAddressManager()
    {
        return \Aimeos\MShop::create($this->getContext(), 'customer/address');
    }

    public function index(Request $request)
    {
        $manager = $this->getAddressManager();
        $user = $request->user();

        $filter = $manager->filter();
        $filter->add($filter->compare('==', 'customer.address.parentid', $user->id));
        
        $items = $manager->search($filter);
        
        $addressIds = [];
        foreach ($items as $item) {
            $addressIds[] = $item->getId();
        }

        $locationRows = collect();
        if (Schema::hasColumn('mshop_customer_address', 'ro_city_id')) {
            $locationRows = DB::table('mshop_customer_address')
                ->whereIn('id', $addressIds)
                ->get(['id', 'ro_city_id', 'ro_subdistrict_id', 'komerce_destination_id'])
                ->keyBy('id');
        }

        $addresses = [];
        foreach ($items as $item) {
            $row = $locationRows->get($item->getId());
            $data = $item->toArray();
            $data['customer.address.ro_city_id'] = $row->ro_city_id ?? null;
            $data['customer.address.ro_subdistrict_id'] = $row->ro_subdistrict_id ?? null;
            $data['customer.address.komerce_destination_id'] = $row->komerce_destination_id ?? null;
            $addresses[] = $data;
        }

        return response()->json([
            'data' => $addresses
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'firstname' => 'required|string|max:255',
            'lastname' => 'nullable|string|max:255',
            'telephone' => 'required|string|max:32',
            'address1' => 'required|string|max:255', // Street address
            'address2' => 'nullable|string|max:255', // Apartment, suite, etc.
            'city' => 'required_without:ro_city_id|nullable|string|max:255',
            'ro_city_id' => 'required_without:city|nullable|integer|exists:tb_ro_cities,city_id',
            'ro_subdistrict_id' => 'nullable|integer|exists:tb_ro_subdistricts,subdistrict_id',
            'komerce_destination_id' => 'nullable|integer|exists:komerce_destinations,id',
            'state' => 'nullable|string|max:255', // Province / State
            'postal' => 'nullable|string|max:32',
        ]);

        $location = $this->resolveRajaOngkirLocation($request);

        $manager = $this->getAddressManager();
        $item = $manager->create();
        
        $item->setParentId($request->user()->id);
        $item->setFirstname($request->firstname);
        $item->setLastname($request->lastname ?: '');
        $item->setTelephone($request->telephone);
        $item->setAddress1($request->address1);
        $item->setAddress2($request->address2 ?: '');
        if (method_exists($item, 'setAddress3')) {
            $item->setAddress3($location['subdistrict_name'] ?? '');
        }
        $item->setCity($location['city_name']);
        $item->setState($location['province_name']);
        $item->setPostal($request->postal ?: ($location['postal_code'] ?? ''));
        
        $manager->save($item);
        $this->syncAddressLocationIds($item->getId(), $location);

        return response()->json([
            'message' => 'Alamat berhasil ditambahkan.',
            'data' => $item->toArray()
        ], 201);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'firstname' => 'required|string|max:255',
            'lastname' => 'nullable|string|max:255',
            'telephone' => 'required|string|max:32',
            'address1' => 'required|string|max:255',
            'address2' => 'nullable|string|max:255',
            'city' => 'required_without:ro_city_id|nullable|string|max:255',
            'ro_city_id' => 'required_without:city|nullable|integer|exists:tb_ro_cities,city_id',
            'ro_subdistrict_id' => 'nullable|integer|exists:tb_ro_subdistricts,subdistrict_id',
            'komerce_destination_id' => 'nullable|integer|exists:komerce_destinations,id',
            'state' => 'nullable|string|max:255',
            'postal' => 'nullable|string|max:32',
        ]);

        $location = $this->resolveRajaOngkirLocation($request);

        $manager = $this->getAddressManager();
        
        try {
            $item = $manager->get($id);
            
            // Security check
            if ($item->getParentId() != $request->user()->id) {
                return response()->json(['message' => 'Unauthorized'], 403);
            }

            $item->setFirstname($request->firstname);
            $item->setLastname($request->lastname ?: '');
            $item->setTelephone($request->telephone);
            $item->setAddress1($request->address1);
            $item->setAddress2($request->address2 ?: '');
            if (method_exists($item, 'setAddress3')) {
                $item->setAddress3($location['subdistrict_name'] ?? '');
            }
            $item->setCity($location['city_name']);
            $item->setState($location['province_name']);
            $item->setPostal($request->postal ?: ($location['postal_code'] ?? ''));
            
            $manager->save($item);
            $this->syncAddressLocationIds($item->getId(), $location);

            return response()->json([
                'message' => 'Alamat berhasil diperbarui.',
                'data' => $item->toArray()
            ]);
        } catch (\Aimeos\MShop\Exception $e) {
            return response()->json(['message' => 'Alamat tidak ditemukan.'], 404);
        }
    }

    public function destroy(Request $request, $id)
    {
        $manager = $this->getAddressManager();
        
        try {
            $item = $manager->get($id);
            
            // Security check
            if ($item->getParentId() != $request->user()->id) {
                return response()->json(['message' => 'Unauthorized'], 403);
            }

            $manager->delete($id);

            return response()->json([
                'message' => 'Alamat berhasil dihapus.'
            ]);
        } catch (\Aimeos\MShop\Exception $e) {
            return response()->json(['message' => 'Alamat tidak ditemukan.'], 404);
        }
    }

    private function resolveRajaOngkirLocation(Request $request): array
    {
        $komerce = $this->resolveKomerceDestination($request);

        if (!Schema::hasTable('tb_ro_cities') || !Schema::hasTable('tb_ro_provinces')) {
            return [
                'city_id' => null,
                'city_name' => $komerce['city_name'] ?? (string) $request->city,
                'province_name' => $komerce['province_name'] ?? (string) $request->state,
                'postal_code' => $komerce['zip_code'] ?? (string) $request->postal,
                'subdistrict_id' => null,
                'subdistrict_name' => $komerce['subdistrict_name'] ?? null,
                'komerce_destination_id' => $komerce['id'] ?? null,
            ];
        }

        $cityQuery = DB::table('tb_ro_cities')
            ->join('tb_ro_provinces', 'tb_ro_cities.province_id', '=', 'tb_ro_provinces.province_id')
            ->select([
                'tb_ro_cities.city_id',
                'tb_ro_cities.city_name',
                'tb_ro_cities.postal_code',
                'tb_ro_provinces.province_name',
            ]);

        if ($request->filled('ro_city_id')) {
            $cityQuery->where('tb_ro_cities.city_id', (int) $request->ro_city_id);
        } else {
            $cityName = trim((string) $request->city);
            $cityQuery->where('tb_ro_cities.city_name', 'like', '%' . $cityName . '%')
                ->orderBy('tb_ro_cities.city_id');
        }

        $city = $cityQuery->first();
        if (!$city) {
            abort(422, 'Pilih kota/kabupaten dari daftar lokasi RajaOngkir.');
        }

        $subdistrict = null;
        if ($request->filled('ro_subdistrict_id')) {
            $subdistrict = DB::table('tb_ro_subdistricts')
                ->where('city_id', $city->city_id)
                ->where('subdistrict_id', (int) $request->ro_subdistrict_id)
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
        if (!$request->filled('komerce_destination_id') || !Schema::hasTable('komerce_destinations')) {
            return null;
        }

        $destination = DB::table('komerce_destinations')
            ->where('id', (int) $request->komerce_destination_id)
            ->first(['id', 'province_name', 'city_name', 'district_name', 'subdistrict_name', 'zip_code']);

        return $destination ? (array) $destination : null;
    }

    private function syncAddressLocationIds($addressId, array $location): void
    {
        if (!Schema::hasColumn('mshop_customer_address', 'ro_city_id')) {
            return;
        }

        DB::table('mshop_customer_address')
            ->where('id', $addressId)
            ->update([
                'ro_city_id' => $location['city_id'] ?: null,
                'ro_subdistrict_id' => $location['subdistrict_id'],
                'komerce_destination_id' => $location['komerce_destination_id'] ?? null,
            ]);
    }
}
