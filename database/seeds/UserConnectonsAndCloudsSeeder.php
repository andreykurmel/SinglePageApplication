<?php

use Illuminate\Database\Seeder;
use Vanguard\Models\Table\Table;
use Vanguard\Models\Table\TableField;
use Vanguard\Repositories\Tablda\Permissions\TablePermissionRepository;
use Vanguard\Repositories\Tablda\TableViewRepository;
use Vanguard\Services\Tablda\Permissions\TablePermissionService;
use Vanguard\User;

class UserConnectonsAndCloudsSeeder extends Seeder
{
    private $permissionsService;
    private $permissionsRepository;
    private $viewRepository;

    /**
     * UserConnectonsAndCloudsSeeder constructor.
     */
    public function __construct()
    {
        $this->permissionsService = new TablePermissionService();
        $this->permissionsRepository = new TablePermissionRepository();
        $this->viewRepository = new TableViewRepository();
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
                'created_on' => now(),
                'modified_by' => $user->id,
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
        $permis = $this->permissionsRepository->getSysPermission($table_sub->id, 1);
        $permis->update(['can_edit_tb' => 1]);



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
                'created_on' => now(),
                'modified_by' => $user->id,
                'modified_on' => now(),
            ]);
        }
        //headers for 'User Clouds'
        $this->create('name', 'Name', $table_sub, $user, 'String', 1);
        $this->create('cloud', 'Cloud', $table_sub, $user, 'String', 1);
        $this->create('msg_to_user', 'Status', $table_sub, $user, 'String', 0, '', 'You can connect Google account to import data and use backup functions. We use readonly access to your drive and spreadsheets for working import functions and write access to store backups of your tables from our application to your drive.');
        $this->create('created_by', 'Created By', $table_sub, $user, 'User');
        $this->create('created_on', 'Created On', $table_sub, $user, 'Date Time');
        $this->create('modified_by', 'Modified By', $table_sub, $user, 'User');
        $this->create('modified_on', 'Modified On', $table_sub, $user, 'Date Time');
        //add right to Visitor
        $this->permissionsService->addSystemRights($table_sub->id, 1);
        $this->viewRepository->addSys($table_sub);
        $permis = $this->permissionsRepository->getSysPermission($table_sub->id, 1);
        $permis->update(['can_edit_tb' => 1]);



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
                'created_on' => now(),
                'modified_by' => $user->id,
                'modified_on' => now(),
            ]);
        }
        //headers for 'User API Keys'
        $this->create('name', 'Name', $table_sub, $user);
        $this->create('air_type', 'Type', $table_sub, $user);
        $this->create('air_base', 'Base ID', $table_sub, $user);
        $this->create('is_active', 'Default', $table_sub, $user, 'Radio');
        $this->create('key', 'Key', $table_sub, $user, 'String', 1);
        $this->create('notes', 'Notes', $table_sub, $user);
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
                'created_on' => now(),
                'modified_by' => $user->id,
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
                'created_on' => now(),
                'modified_by' => $user->id,
                'modified_on' => now(),
            ]);
        }
        //headers for 'User API Keys'
        $this->create('email', 'Email', $table_sub, $user, 'String', 1);
        $this->create('app_pass', 'App Password', $table_sub, $user, 'String', 1);
        //add right to Visitor
        $this->permissionsService->addSystemRights($table_sub->id, 1);
        $this->viewRepository->addSys($table_sub);



        $table_sub = Table::where('db_name', 'table_email_addon_settings')->first();
        if (!$table_sub) {
            $table_sub = Table::create([
                'is_system' => 1,
                'db_name' => 'table_email_addon_settings',
                'name' => 'Email Addon Settings',
                'user_id' => $user->id,
                'rows_per_page' => 100,
                'source' => 'scratch',
                'created_by' => $user->id,
                'created_on' => now(),
                'modified_by' => $user->id,
                'modified_on' => now(),
            ]);
        }
        //headers for 'Email Addon Settings'
        $this->create('name', 'Name', $table_sub, $user, 'String', 1);
        $this->create('description', 'Description', $table_sub, $user, 'String');
        //add right to Visitor
        $this->permissionsService->addSystemRights($table_sub->id, 1);
        $this->viewRepository->addSys($table_sub);
    }

    /**
     * @param $field
     * @param $name
     * @param $table
     * @param $user
     * @param string $type
     * @param int $required
     * @param string $default
     * @param string $tooltip
     */
    private function create($field, $name, $table, $user, $type = 'String', $required = 0, $default = '', $tooltip = '')
    {
        $present = $table->_fields()->where('field', $field)->first();
        if (!$present) {
            TableField::create([
                'table_id' => $table->id,
                'field' => $field,
                'name' => $name,
                'f_type' => $type,
                'f_default' => $default,
                'f_required' => $required,
                'tooltip' => $tooltip,
                'tooltip_show' => $tooltip ? 1 : 0,
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
            $present->tooltip = $tooltip;
            $present->tooltip_show = $tooltip ? 1 : 0;
            $present->save();
        }
    }
}
