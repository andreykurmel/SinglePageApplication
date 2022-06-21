<?php

namespace Tests;

use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\DB;

trait CreatesDatabase
{
    /**
     * Creates the application.
     *
     * @return \Illuminate\Foundation\Application
     */
    public function createDatabase()
    {
        try {
            DB::connection()->getPdo();
        } catch (\Exception $e) {
            $def = config('database.default');
            $dbname = config('database.connections.'.$def.'.database');
            DB::connection($def.'_schema')->statement('CREATE DATABASE '. $dbname);
        }
    }
}
