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
            ->get();

        foreach ($sites as $site) {
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
    }
}
