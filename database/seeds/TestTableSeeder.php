<?php

use Illuminate\Database\Seeder;
use Vanguard\Models\Table\TableData;
use Vanguard\Models\Table\Table;
use Vanguard\Models\Table\TableField;
use Vanguard\Models\Table\UserHeaders;
use Vanguard\Models\DDL;
use Vanguard\Models\DDLItem;
use Vanguard\Models\DDLReference;
use Vanguard\Models\FavoriteRow;
use Vanguard\Models\Folder\Folder;
use Vanguard\Models\Folder\Folder2Table;
use Vanguard\User;
use Illuminate\Database\Schema\Blueprint;

class TestTableSeeder extends Seeder
{
    private $permissionsService;
    private $viewRepository;

    /**
     * TestTableSeeder constructor.
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
        if (Table::where('db_name', 'test_admin')->first()) {
            return;
        }

        $admin = User::where('role_id', '=', '1')->first();
        $user = User::where('username', '=', 'user')->first();


        //create table in the tablda_data
        Schema::connection('mysql_data')->create('test_admin', function (Blueprint $table) {
            $table->increments('id');
            $table->string('col1')->nullable();
            $table->integer('col2')->nullable();
            $table->string('col3')->nullable();

            $table->unsignedInteger('created_by')->nullable();
            $table->string('created_name')->nullable();
            $table->dateTime('created_on');
            $table->unsignedInteger('modified_by')->nullable();
            $table->string('modified_name')->nullable();
            $table->dateTime('modified_on');
        });

        $tableData = new TableData();
        $tableData->setTable('test_admin');
        $tableData->insert([
            'col1' => 'row1',
            'col2' => 1,
            'col3' => 'val',
            'created_by' => $admin->id,
            'created_name' => $admin->first_name.' '.$admin->last_name,
            'created_on' => now(),
            'modified_by' => $admin->id,
            'modified_name' => $admin->first_name.' '.$admin->last_name,
            'modified_on' => now()
        ]);
        $f_id = $tableData->insert([
            'col1' => 'row2_',
            'col2' => 2,
            'created_by' => $admin->id,
            'created_name' => $admin->first_name.' '.$admin->last_name,
            'created_on' => now(),
            'modified_by' => $admin->id,
            'modified_name' => $admin->first_name.' '.$admin->last_name,
            'modified_on' => now()
        ]);


        //add to 'tables' and ['ddls', 'ddl_items', 'ddl_references']
        $table->id = $this->createTable($admin, $user->id);

        //favorite row - #2 for admin
        FavoriteRow::create([
            'user_id' => $admin->id,
            'table_id' => $table->id,
            'row_id' => $f_id,
            'created_by' => $admin->id,
            'created_name' => $admin->first_name.' '.$admin->last_name,
            'created_on' => now(),
            'modified_by' => $admin->id,
            'modified_name' => $admin->first_name.' '.$admin->last_name,
            'modified_on' => now()
        ]);

        //add to 'table_fields' and 'user_headers'
        $this->createHeaders('col1','First String', $table->id, $admin, $user->id);
        $this->createHeaders('col2','Integer', $table->id, $admin, $user->id);
        $this->createHeaders('col3','Second String', $table->id, $admin, $user->id);
        $this->createHeaders('created_by','Created By', $table->id, $admin, $user->id, 'User');
        $this->createHeaders('created_on','Created On', $table->id, $admin, $user->id, 'Date Time');
        $this->createHeaders('modified_by','Modified By', $table->id, $admin, $user->id, 'User');
        $this->createHeaders('modified_on','Modified On', $table->id, $admin, $user->id, 'Date Time');

        //add right to Visitor
        $this->permissionsService->addSystemRights($table->id);
        $this->viewRepository->addSys($table);

        //create folders
        $this->createFolders($table->id, $admin, $user->id);
    }

    private function createTable($admin, $user_id) {
        $table = Table::create([
            'is_system' => 0,
            'db_name' => 'test_admin',
            'name' => 'Test',
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

        $ddl = DDL::create([
            'table_id' => $table->id,
            'name' => 'ddl1',
            'type' => 'regular',
            'created_by' => $admin->id,
            'created_name' => $admin->first_name.' '.$admin->last_name,
            'created_on' => now(),
            'modified_by' => $admin->id,
            'modified_name' => $admin->first_name.' '.$admin->last_name,
            'modified_on' => now()
        ]);
        DDLItem::create([
            'ddl_id' => $ddl->id,
            'option' => 'miles',
            'created_by' => $admin->id,
            'created_name' => $admin->first_name.' '.$admin->last_name,
            'created_on' => now(),
            'modified_by' => $admin->id,
            'modified_name' => $admin->first_name.' '.$admin->last_name,
            'modified_on' => now()
        ]);
        DDLItem::create([
            'ddl_id' => $ddl->id,
            'option' => 'km',
            'created_by' => $admin->id,
            'created_name' => $admin->first_name.' '.$admin->last_name,
            'created_on' => now(),
            'modified_by' => $admin->id,
            'modified_name' => $admin->first_name.' '.$admin->last_name,
            'modified_on' => now()
        ]);

        return $table;
    }

    private function createHeaders($field, $name, $table_id, $admin, $user_id, $type = 'String') {
        $table_field = TableField::create([
            'table_id' => $table_id,
            'field' => $field,
            'name' => $name,
            'f_type' => $type,
            'created_by' => $admin->id,
            'created_name' => $admin->first_name.' '.$admin->last_name,
            'created_on' => now(),
            'modified_by' => $admin->id,
            'modified_name' => $admin->first_name.' '.$admin->last_name,
            'modified_on' => now()
        ]);

        UserHeaders::create([
            'table_field_id' => $table_field->id,
            'user_id' => $admin->id,
            'order' => $table_field->id,
            'created_by' => $admin->id,
            'created_name' => $admin->first_name.' '.$admin->last_name,
            'created_on' => now(),
            'modified_by' => $admin->id,
            'modified_name' => $admin->first_name.' '.$admin->last_name,
            'modified_on' => now()
        ]);
        UserHeaders::create([
            'table_field_id' => $table_field->id,
            'user_id' => $user_id,
            'order' => $table_field->id,
            'created_by' => $admin->id,
            'created_name' => $admin->first_name.' '.$admin->last_name,
            'created_on' => now(),
            'modified_by' => $admin->id,
            'modified_name' => $admin->first_name.' '.$admin->last_name,
            'modified_on' => now()
        ]);
    }

    private function createFolders($table_id, $admin, $user_id) {
        //create system folder for 'public' tab.
        Folder::create([
            'user_id' => null,
            'name' => 'UNCATEGORIZED',
            'structure' => 'public',
            'is_system' => 1,
            'created_by' => $admin->id,
            'created_name' => $admin->first_name.' '.$admin->last_name,
            'created_on' => now(),
            'modified_by' => $admin->id,
            'modified_name' => $admin->first_name.' '.$admin->last_name,
            'modified_on' => now()
        ]);
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
}
