<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Vanguard\Models\Table\Table;
use Vanguard\Models\Table\TableField;
use Vanguard\User;

class LinkSysTablesSeeder extends Seeder
{
    /**
     * Constructor
     */
    public function __construct()
    {
    }

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $repo = new \Vanguard\Repositories\Tablda\FolderRepository();

        DB::select('DELETE FROM `folders` WHERE `for_shared_user_id` IS NOT NULL;');
        DB::select('DELETE FROM `folders` WHERE `is_system` = 1;');

        $repo->insertFolder(null, null, 'UNCATEGORIZED', 'public', ['is_system' => 1]);

        DB::select('DELETE FROM `folders_2_tables` WHERE `structure` = \'account\';');
        $repo->addSysFolderWithSysTables('System');
        $repo->addSysFolderWithSysTables('Support');
        $repo->addSysFolderWithSysTables('Info');
        $repo->addSysFolderWithSysTables('myAccount');
        $repo->addSysFolderWithSysTables('Correspondence');
        DB::select('UPDATE `folders` SET user_id =1 WHERE `user_id` IS NULL AND `name` LIKE \'System\' AND `is_system` = 1');

        $users = User::all();
        foreach ($users as $user) {
            $repo->insertSystems($user->id);
        }
    }
}
