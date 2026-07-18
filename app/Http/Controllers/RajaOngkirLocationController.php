<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RajaOngkirLocationController extends Controller
{
    public function provinces()
    {
        return response()->json([
            'data' => DB::table('tb_ro_provinces')
                ->orderBy('province_name')
                ->get(['province_id', 'province_name']),
        ]);
    }

    public function cities(Request $request)
    {
        $query = DB::table('tb_ro_cities')
            ->leftJoin('tb_ro_provinces', 'tb_ro_cities.province_id', '=', 'tb_ro_provinces.province_id')
            ->orderBy('tb_ro_cities.city_name');

        if ($request->filled('province_id')) {
            $query->where('tb_ro_cities.province_id', (int) $request->province_id);
        }

        if ($request->filled('city_id')) {
            $query->where('tb_ro_cities.city_id', (int) $request->city_id);
        }

        if ($request->filled('q')) {
            $term = '%' . $request->q . '%';
            $query->where(function ($q) use ($term) {
                $q->where('tb_ro_cities.city_name', 'like', $term)
                    ->orWhere('tb_ro_cities.postal_code', 'like', $term);
            });
        }

        return response()->json([
            'data' => $query->limit(200)->get([
                'tb_ro_cities.city_id',
                'tb_ro_cities.province_id',
                'tb_ro_cities.city_name',
                'tb_ro_cities.postal_code',
                'tb_ro_provinces.province_name',
            ]),
        ]);
    }

    public function subdistricts(Request $request)
    {
        $request->validate([
            'city_id' => ['required', 'integer'],
        ]);

        return response()->json([
            'data' => DB::table('tb_ro_subdistricts')
                ->where('city_id', (int) $request->city_id)
                ->orderBy('subdistrict_name')
                ->get(['subdistrict_id', 'city_id', 'subdistrict_name', 'komerce_destination_id']),
        ]);
    }

    public function komerceDestinations(Request $request)
    {
        $request->validate([
            'search' => ['required', 'string', 'min:2', 'max:100'],
        ]);

        $keyword = trim($request->search);

        return response()->json([
            'data' => DB::table('komerce_destinations')
                ->select([
                    'id',
                    'label',
                    'province_name',
                    'city_name',
                    'district_name',
                    'subdistrict_name',
                    'zip_code',
                ])
                ->where(function ($query) use ($keyword) {
                    $query->where('subdistrict_name', 'like', "%{$keyword}%")
                        ->orWhere('district_name', 'like', "%{$keyword}%")
                        ->orWhere('city_name', 'like', "%{$keyword}%")
                        ->orWhere('zip_code', 'like', "{$keyword}%")
                        ->orWhere('label', 'like', "%{$keyword}%");
                })
                ->orderBy('province_name')
                ->orderBy('city_name')
                ->orderBy('district_name')
                ->orderBy('subdistrict_name')
                ->limit(20)
                ->get(),
        ]);
    }

    public function couriers()
    {
        return response()->json([
            'data' => DB::table('shipping_couriers')
                ->where('active', true)
                ->where('supports_domestic_cost', true)
                ->orderBy('name')
                ->get([
                    'code',
                    'name',
                    'supports_domestic_cost',
                    'supports_international_cost',
                    'supports_awb',
                ]),
        ]);
    }
}
