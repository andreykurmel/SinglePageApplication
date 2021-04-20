<?php

use Vanguard\Role;
use Illuminate\Database\Seeder;

class RolesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        if (!DB::table('roles')->count()) {
            Role::create([
                'name' => 'Admin',
                'display_name' => 'Admin',
                'description' => 'System administrator.',
                'removable' => false
            ]);

            Role::create([
                'name' => 'User',
                'display_name' => 'User',
                'description' => 'Default system user.',
                'removable' => false
            ]);

            Role::create([
                'name' => 'Get Started Editor',
                'display_name' => 'Get Started Editor',
                'description' => 'Get Started Editor.',
                'removable' => false
            ]);
        }
    }
}
