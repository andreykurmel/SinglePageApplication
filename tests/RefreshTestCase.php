<?php

namespace Tests;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class RefreshTestCase extends BaseTestCase
{
    use CreatesApplication, CreatesDatabase, RefreshDatabase;

    /**
     * @var bool
     */
    protected $seed = true;

    /**
     * Database migrated for each test
     */
    public function setUp(): void
    {
        $this->afterApplicationCreated(function () {
            $this->createDatabase();
        });

        parent::setUp();
    }

}
