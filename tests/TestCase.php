<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\DB;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    protected function setUp(): void
    {
        parent::setUp();

        // Get IDs of test users to clean up their customer list relationships first
        $testUserIds = DB::table('users')
            ->where('email', 'like', '%@example.com')
            ->orWhere('email', 'like', '%@example.org')
            ->pluck('id');

        if ($testUserIds->isNotEmpty()) {
            DB::table('mshop_customer_list')
                ->whereIn('parentid', $testUserIds)
                ->delete();

            DB::table('users')
                ->whereIn('id', $testUserIds)
                ->delete();
        }

        // Clean up test sites and their associated data
        $sites = DB::table('mshop_locale_site')
            ->where('code', 'like', 'testsite%')
            ->orWhere('code', 'like', 'test-seller-%')
            ->get();

        foreach ($sites as $site) {
            // Clean products in this site
            DB::table('mshop_product')
                ->where('siteid', 'like', $site->siteid . '%')
                ->delete();

            // Clean locale-related entries using the site's numeric ID
            // (extracted from the path, e.g. "/1/5/" -> last segment = 5)
            $parts     = array_filter(explode('/', $site->siteid));
            $numericId = end($parts);

            DB::table('mshop_locale')
                ->where('site_id', (int) $numericId)
                ->delete();

            DB::table('mshop_customer_list')
                ->where('siteid', $site->siteid)
                ->delete();

            DB::table('mshop_group')
                ->where('siteid', $site->siteid)
                ->delete();

            DB::table('mshop_locale_site')
                ->where('siteid', $site->siteid)
                ->delete();
        }

        // Also clean orphaned mshop_locale entries whose site no longer exists
        DB::statement('
            DELETE ml FROM mshop_locale ml
            LEFT JOIN mshop_locale_site mls ON ml.site_id = mls.id
            WHERE mls.id IS NULL
        ');
        
        \Illuminate\Support\Facades\Cache::flush();
        try {
            if (app()->has('aimeos.context')) {
                app('aimeos.context')->get(false)->cache()->clear();
            }
            if (class_exists('\Aimeos\MShop')) {
                \Aimeos\MShop::cache(false);
                \Aimeos\MShop::cache(true);
            }
        } catch (\Exception $e) {}
    }
}
