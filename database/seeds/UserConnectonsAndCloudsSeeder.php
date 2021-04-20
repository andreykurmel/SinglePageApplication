<?php

use Illuminate\Database\Seeder;
use Vanguard\Models\Table\Table;
use Vanguard\Models\Table\TableField;
use Vanguard\User;

class UserConnectonsAndCloudsSeeder extends Seeder
{
    private $permissionsService;
    private $service;
    private $viewRepository;

    /**
     * UserConnectonsAndCloudsSeeder constructor.
     *
     * @param \Vanguard\Services\Tablda\Permissions\TablePermissionService $permissionsService
     * @param \Vanguard\Services\Tablda\HelperService $service
     */
    public function __construct(
        \Vanguard\Services\Tablda\Permissions\TablePermissionService $permissionsService,
        \Vanguard\Services\Tablda\HelperService $service,
        \Vanguard\Repositories\Tablda\TableViewRepository $tableViewRepository
    )
    {
        $this->permissionsService = $permissionsService;
        $this->service = $service;
        $this->viewRepository = $tableViewRepository;
    }

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $user = User::where('role_id', '=', '1')->first();

        $table_sub = Table::where('db_name', 'user_connections')->first();
        if (!$table_sub) {
            $table_sub = Table::create([
                'is_system' => 1,
                'db_name' => 'user_connections',
                'name' => 'User Connections',
                'user_id' => $user->id,
                'rows_per_page' => 100,
                'source' => 'scratch',
                'created_by' => $user->id,
                'created_name' => $user->first_name . ' ' . $user->last_name,
                'created_on' => now(),
                'modified_by' => $user->id,
                'modified_name' => $user->first_name . ' ' . $user->last_name,
                'modified_on' => now(),
            ]);
        }
        //headers for 'User Connections'
        $this->create('name', 'Name', $table_sub, $user);
        $this->create('host', 'Host', $table_sub, $user);
        $this->create('login', 'Username', $table_sub, $user);
        $this->create('pass', 'Password', $table_sub, $user, 'Password');
        $this->create('db', 'DB', $table_sub, $user);
        $this->create('table', 'Table', $table_sub, $user);
        $this->create('created_by', 'Created By', $table_sub, $user, 'User');
        $this->create('created_on', 'Created On', $table_sub, $user, 'Date Time');
        $this->create('modified_by', 'Modified By', $table_sub, $user, 'User');
        $this->create('modified_on', 'Modified On', $table_sub, $user, 'Date Time');
        //add right to Visitor
        $this->permissionsService->addSystemRights($table_sub->id, 1);
        $this->viewRepository->addSys($table_sub);



        $table_sub = Table::where('db_name', 'user_clouds')->first();
        if (!$table_sub) {
            $table_sub = Table::create([
                'is_system' => 1,
                'db_name' => 'user_clouds',
                'name' => 'User Clouds',
                'user_id' => $user->id,
                'rows_per_page' => 100,
                'source' => 'scratch',
                'created_by' => $user->id,
                'created_name' => $user->first_name . ' ' . $user->last_name,
                'created_on' => now(),
                'modified_by' => $user->id,
                'modified_name' => $user->first_name . ' ' . $user->last_name,
                'modified_on' => now(),
            ]);
        }
        //headers for 'User Clouds'
        $this->create('name', 'Name', $table_sub, $user, 'String', 1);
        $this->create('cloud', 'Cloud', $table_sub, $user, 'String', 1);
        $this->create('msg_to_user', 'Status', $table_sub, $user);
        $this->create('root_folder', 'Root folder *', $table_sub, $user, 'String', 0, 'TablDA_backup');
        $this->create('created_by', 'Created By', $table_sub, $user, 'User');
        $this->create('created_on', 'Created On', $table_sub, $user, 'Date Time');
        $this->create('modified_by', 'Modified By', $table_sub, $user, 'User');
        $this->create('modified_on', 'Modified On', $table_sub, $user, 'Date Time');
        //add right to Visitor
        $this->permissionsService->addSystemRights($table_sub->id, 1);
        $this->viewRepository->addSys($table_sub);



        $table_sub = Table::where('db_name', 'user_api_keys')->first();
        if (!$table_sub) {
            $table_sub = Table::create([
                'is_system' => 1,
                'db_name' => 'user_api_keys',
                'name' => 'User Api Keys',
                'user_id' => $user->id,
                'rows_per_page' => 100,
                'source' => 'scratch',
                'created_by' => $user->id,
                'created_name' => $user->first_name . ' ' . $user->last_name,
                'created_on' => now(),
                'modified_by' => $user->id,
                'modified_name' => $user->first_name . ' ' . $user->last_name,
                'modified_on' => now(),
            ]);
        }
        //headers for 'User API Keys'
        $this->create('is_active', 'Default', $table_sub, $user, 'Radio');
        $this->create('key', 'Key', $table_sub, $user, 'String', 1);
        //add right to Visitor
        $this->permissionsService->addSystemRights($table_sub->id, 1);
        $this->viewRepository->addSys($table_sub);



        $table_sub = Table::where('db_name', 'user_payment_keys')->first();
        if (!$table_sub) {
            $table_sub = Table::create([
                'is_system' => 1,
                'db_name' => 'user_payment_keys',
                'name' => 'User Payment Keys',
                'user_id' => $user->id,
                'rows_per_page' => 100,
                'source' => 'scratch',
                'created_by' => $user->id,
                'created_name' => $user->first_name . ' ' . $user->last_name,
                'created_on' => now(),
                'modified_by' => $user->id,
                'modified_name' => $user->first_name . ' ' . $user->last_name,
                'modified_on' => now(),
            ]);
        }
        //headers for 'User API Keys'
        $this->create('name', 'Name', $table_sub, $user, 'String', 1);
        $this->create('mode', 'Mode', $table_sub, $user, 'String');
        $this->create('public_key', 'Public Key', $table_sub, $user, 'String');
        $this->create('secret_key', 'Secret Key', $table_sub, $user, 'String');
        //add right to Visitor
        $this->permissionsService->addSystemRights($table_sub->id, 1);
        $this->viewRepository->addSys($table_sub);



        $table_sub = Table::where('db_name', 'user_email_accounts')->first();
        if (!$table_sub) {
            $table_sub = Table::create([
                'is_system' => 1,
                'db_name' => 'user_email_accounts',
                'name' => 'User Email Accounts',
                'user_id' => $user->id,
                'rows_per_page' => 100,
                'source' => 'scratch',
                'created_by' => $user->id,
                'created_name' => $user->first_name . ' ' . $user->last_name,
                'created_on' => now(),
                'modified_by' => $user->id,
                'modified_name' => $user->first_name . ' ' . $user->last_name,
                'modified_on' => now(),
            ]);
        }
        //headers for 'User API Keys'
        $this->create('email', 'Email', $table_sub, $user, 'String', 1);
        $this->create('app_pass', 'App Password', $table_sub, $user, 'String', 1);
        //add right to Visitor
        $this->permissionsService->addSystemRights($table_sub->id, 1);
        $this->viewRepository->addSys($table_sub);
    }

    private function create($field, $name, $table, $user, $type = 'String', $required = 0, $default = '') {
        $present = $table->_fields->where('field', $field)->first();
        if (!$present) {
            TableField::create([
                'table_id' => $table->id,
                'field' => $field,
                'name' => $name,
                'f_type' => $type,
                'f_default' => $default,
                'f_required' => $required,
                'created_by' => $user->id,

                'created_on' => now(),
                'modified_by' => $user->id,

                'modified_on' => now()
            ]);
        } else {
            $present->name = $name;
            $present->f_type = $type;
            $present->f_default = $default;
            $present->f_required = $required;
            $present->save();
        }
    }
}
