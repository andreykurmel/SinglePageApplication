<?php

use Illuminate\Database\Seeder;

class MemLeakTestSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        for ($i = 0; $i < 100; $i++) {
            \Vanguard\UserActivity::create([
                'description' => 'test',
                'user_id' => 1,
                'ip_address' => '127.0.0.1',
                'user_agent' => 'data',
            ]);
            echo $i . ' - ' .memory_get_usage() . "\n";
        }
    }
}
