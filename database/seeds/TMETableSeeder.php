<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Vanguard\User;
use Vanguard\Models\Table\Table;
use Vanguard\Models\Table\TableField;
use Vanguard\Models\Folder\Folder2Table;

class TMETableSeeder extends Seeder
{
    private $permissionsService;
    private $viewRepository;

    /**
     * TMETableSeeder constructor.
     *
     * @param \Vanguard\Services\Tablda\Permissions\TablePermissionService $permissionsService
     */
    public function __construct(
        \Vanguard\Services\Tablda\Permissions\TablePermissionService $permissionsService,
        \Vanguard\Repositories\Tablda\TableViewRepository $tableViewRepository
    )
    {
        $this->permissionsService = $permissionsService;
        $this->viewRepository = $tableViewRepository;
    }

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        if (Table::where('db_name', 'tme')->first()) {
            return;
        }

        $admin = User::where('role_id', '=', '1')->first();

        //create table in the tablda_data
        DB::connection('mysql_data')->unprepared(file_get_contents('database/seeds/tme_2k.sql'));

        //add to 'tables' and ['ddls', 'ddl_items', 'ddl_references']
        $table = Table::create([
            'is_system' => 0,
            'db_name' => 'tme',
            'name' => 'TME',
            'user_id' => $admin->id,
            'rows_per_page' => 100,
            'source' => 'scratch',
            'created_by' => $admin->id,
            'created_name' => $admin->first_name.' '.$admin->last_name,
            'created_on' => now(),
            'modified_by' => $admin->id,
            'modified_name' => $admin->first_name.' '.$admin->last_name,
            'modified_on' => now()
        ]);
        $table_id = $table->id;

        //add to 'table_fields' and 'user_headers'
        $this->createHeaders('type','TYPE', $table_id, $admin);
        $this->createHeaders('mftr','MANUFACTURER', $table_id, $admin);
        $this->createHeaders('model','MODEL', $table_id, $admin);
        $this->createHeaders('wt','WT', $table_id, $admin);

        $this->createHeaders('created_on','Created On', $table_id, $admin, 'Date Time');
        $this->createHeaders('created_by','Created By', $table_id, $admin, 'User');
        $this->createHeaders('modified_on','Modified On', $table_id, $admin, 'Date Time');
        $this->createHeaders('modified_by','Modified By', $table_id, $admin, 'User');

        //add right to Visitor
        $this->permissionsService->addSystemRights($table_id);
        $this->viewRepository->addSys($table);

        Folder2Table::create([
            'table_id' => $table_id,
            'user_id' => $admin->id,
            'folder_id' => null,
            'type' => 'table',
            'structure' => 'private',
            'created_by' => $admin->id,
            'created_name' => $admin->first_name.' '.$admin->last_name,
            'created_on' => now(),
            'modified_by' => $admin->id,
            'modified_name' => $admin->first_name.' '.$admin->last_name,
            'modified_on' => now()
        ]);
    }

    private function createHeaders($field, $name, $table_id, $admin, $type = 'String') {
        TableField::create([
            'table_id' => $table_id,
            'field' => $field,
            'name' => $name,
            'f_type' => $type,
            'filter' => (in_array($name, ['TYPE','MANUFACTURER','MODEL']) ? 1 : 0),
            'created_by' => $admin->id,
            'created_name' => $admin->first_name.' '.$admin->last_name,
            'created_on' => now(),
            'modified_by' => $admin->id,
            'modified_name' => $admin->first_name.' '.$admin->last_name,
            'modified_on' => now()
        ]);
    }
}
