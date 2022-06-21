<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\Schema;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication, CreatesDatabase;

    /**
     * @var bool
     */
    protected $seed = true;

    /**
     * Database migrated just once if needed
     */
    public function setUp(): void
    {
        $this->afterApplicationCreated(function () {
            $this->createDatabase();

            if (!Schema::hasTable('migrations')) {
                $this->artisan('migrate');
                $this->artisan('db:seed');
            }
        });

        parent::setUp();
    }

}
