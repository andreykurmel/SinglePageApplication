<?php

namespace Vanguard\Console\Commands;

use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;

class CreateDatabaseCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'make:database {dbname?} {connection?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create all needed databases';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Create
     */
    public function handle()
    {
        $connection = $this->hasArgument('connection') && $this->argument('connection')
            ? $this->argument('connection')
            : config('database.default');

        $dbname = $this->hasArgument('dbname') && $this->argument('dbname')
            ? $this->argument('dbname')
            : config('database.connections.' . $connection . '.database');

        try {

            $this->setDatabaseForConnection($connection, $dbname);
            DB::connection($connection)->getPdo();
            $this->info("Database $dbname already exists for $connection connection");

        } catch (Exception $e) {

            $this->setDatabaseForConnection($connection, '');
            DB::connection($connection)->statement('CREATE DATABASE ' . $dbname);
            $this->info("Database '$dbname' created for '$connection' connection");

        }
    }

    /**
     * @param string $connection
     * @param string $database
     */
    protected function setDatabaseForConnection(string $connection, string $database = '')
    {
        DB::purge($connection);
        Config::set('database.connections.' . $connection . '.database', $database);
        DB::reconnect($connection);
    }
}
