<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;
    use RefreshDatabase;

    /**
     * Track if Aimeos setup has been run for testing.
     *
     * @var bool
     */
    protected static bool $aimeosSetupRun = false;

    /**
     * Refresh a conventional cache_db database.
     *
     * @return void
     */
    protected function refreshDatabase()
    {
        if (! \Illuminate\Foundation\Testing\RefreshDatabaseState::$migrated) {
            $this->artisan('migrate:fresh', $this->migrateFreshUsing());

            $this->artisan('aimeos:setup');
            $this->artisan('db:seed');

            $this->app[\Illuminate\Contracts\Console\Kernel::class]->setArtisan(null);

            \Illuminate\Foundation\Testing\RefreshDatabaseState::$migrated = true;
        }

        $this->beginDatabaseTransaction();
    }
}
