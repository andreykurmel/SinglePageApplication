<?php

namespace Database\Seeders;

use Vanguard\Role;
use Vanguard\Support\Enum\UserStatus;
use Vanguard\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        if (!DB::table('users')->count()) {
            $admin = Role::where('name', 'Admin')->first();

            User::create([
                'first_name' => 'Vanguard',
                'email' => 'admin@example.com',
                'username' => 'admin',
                'password' => 'admin123',
                'avatar' => null,
                'country_id' => null,
                'role_id' => $admin->id,
                'status' => UserStatus::ACTIVE
            ]);

            $user = Role::where('name', 'User')->first();

            User::create([
                'first_name' => 'User',
                'email' => 'user@example.com',
                'username' => 'user',
                'password' => 'user123',
                'avatar' => null,
                'country_id' => null,
                'role_id' => $user->id,
                'status' => UserStatus::ACTIVE
            ]);
        }
    }
}
