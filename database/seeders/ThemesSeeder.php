<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Vanguard\Models\AppTheme;
use Vanguard\Models\Table\Table;
use Vanguard\User;

class ThemesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return  void
     */
    public function run()
    {
        $theme_count = AppTheme::USERS_THEMES_COUNT;

        //create themes
        while (AppTheme::where('obj_type', 'system')->count() < $theme_count) {
            AppTheme::create([
                'obj_type' => 'system',
                'obj_id' => null,
            ]);
        }

        $tables = Table::with('_theme')->get();
        foreach ($tables as $tb) {
            if (!$tb->_theme) {
                AppTheme::create([
                    'obj_type' => 'table',
                    'obj_id' => $tb->id,
                ]);
            }
        }

        $users = User::with('_themes')->get();
        foreach ($users as $user) {
            while ($user->_themes()->count() < $theme_count) {
                AppTheme::create([
                    'obj_type' => 'user',
                    'obj_id' => $user->id,
                ]);
            }
        }
    }
}
