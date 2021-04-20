<?php

use Illuminate\Database\Seeder;
use Vanguard\Models\Table\Table;
use Vanguard\Models\Table\TableField;
use Vanguard\User;

class CorrespondencesSeeder extends Seeder
{
    private $permissionsService;

    /**
     * TableFieldsSeeder constructor.
     *
     * @param \Vanguard\Services\Tablda\Permissions\TablePermissionService $permissionsService
     */
    public function __construct(
        \Vanguard\Services\Tablda\Permissions\TablePermissionService $permissionsService
    )
    {
        $this->permissionsService = $permissionsService;
    }

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $user = User::where('role_id', '=', '1')->first();



        $table = Table::where('db_name', '=', 'correspondence_apps')->first();
        if (!$table) {
            $table = Table::create([
                'is_system' => 2,
                'db_name' => 'correspondence_apps',
                'name' => 'Apps',
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

        $this->create('user_id','Owner', $table, $user, 'User');
        //$this->create('subdomain','Subdomain', $table, $user);
        //$this->create('icon_full_path','Icon Link', $table, $user);
        $this->create('name','Name', $table, $user, 'String', 1);
        $this->create('app_path','App Path', $table, $user, 'String', 1);
        $this->create('open_as_popup','Open as Popup', $table, $user, 'Boolean', 0, 'Input', 1);
        $this->create('is_public','Public', $table, $user, 'Boolean', 0, 'Input', 1);
        $this->create('notes','Notes', $table, $user);

        //add right to Visitor
        $this->permissionsService->addSystemRights($table->id, 1);



        $table = Table::where('db_name', '=', 'correspondence_tables')->first();
        if (!$table) {
            $table = Table::create([
                'is_system' => 2,
                'db_name' => 'correspondence_tables',
                'name' => 'Tables',
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

        $this->create('user_id','Owner', $table, $user, 'User');
        $this->create('active','On/Off', $table, $user, 'Boolean', 0, 'Input', 1);
        $this->create('correspondence_app_id','App,Name', $table, $user, 'String', 1, 'S-Select');
        $this->create('app_table','App,Array', $table, $user, 'String', 1, 'S-Select');
        $this->create('data_table','Data,Table', $table, $user, 'RefTable',0, 'S-Select');
        $this->create('options','Options', $table, $user, 'String', 0, 'M-Select');
        $this->create('on_change_app_id','On Change Handler', $table, $user, 'String', 0, 'S-Select');
        $this->create('notes','Notes', $table, $user);

        //add right to Visitor
        $this->permissionsService->addSystemRights($table->id, 1);



        $table = Table::where('db_name', '=', 'correspondence_fields')->first();
        if (!$table) {
            $table = Table::create([
                'is_system' => 2,
                'db_name' => 'correspondence_fields',
                'name' => 'Fields',
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

        $this->create('user_id','Owner', $table, $user, 'User');
        $this->create('correspondence_app_id','App,Name', $table, $user, 'String', 1, 'S-Select');
        $this->create('correspondence_table_id','App,Array', $table, $user, 'String', 1, 'S-Select');
        $this->create('app_field','App,Property', $table, $user, 'String', 1);
        $this->create('_data_table_id','Data,Table', $table, $user, 'RefTable',0, 'S-Select');
        $this->create('data_field','Data,Field', $table, $user, 'RefField',0, 'S-Select');
        $this->create('link_table_db','Inheritance,Table', $table, $user, 'RefTable',0, 'S-Select');
        $this->create('link_field_db','Inheritance,Field', $table, $user, 'RefField',0, 'S-Select');
        $this->create('link_field_type','Inheritance,Type', $table, $user, 'String',0, 'M-Select');
        $this->create('options','Options', $table, $user, 'String',0, 'M-Select');
        $this->create('notes','Notes', $table, $user);

        //add right to Visitor
        $this->permissionsService->addSystemRights($table->id, 1);



        $table = Table::where('db_name', '=', 'correspondence_stim_3d')->first();
        if (!$table) {
            $table = Table::create([
                'is_system' => 2,
                'db_name' => 'correspondence_stim_3d',
                'name' => 'UI Parts',
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

        $this->create('top_tab','Header,Top Tabs', $table, $user, 'String', 1);
        $this->create('select','Header,Select', $table, $user, 'String');
        $this->create('style','Tabs,Style', $table, $user, 'String', 0, 'Select', 'vh_tabs');
        $this->create('accordion','Tabs,APanel', $table, $user, 'String');
        $this->create('horizontal','Tabs,Horizontal', $table, $user, 'String');
        $this->create('vertical','Tabs,Vertical', $table, $user, 'String');
        $this->create('db_table','App,Array', $table, $user, 'String', 0, 'S-Select');
        $this->create('type_tablda','App,Component', $table, $user, 'String', 0, 'S-Select');
        $this->create('options','Options', $table, $user, 'String',0, 'M-Select');
        $this->create('model_3d','GUI', $table, $user, 'String', 0, 'S-Select');

        //add right to Visitor
        $this->permissionsService->addSystemRights($table->id);



        $table = Table::where('db_name', '=', 'stim_app_views')->first();
        if (!$table) {
            $table = Table::create([
                'is_system' => 2,
                'db_name' => 'stim_app_views',
                'name' => 'Stim Views',
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

        $this->create('name','Name', $table, $user);
        $this->create('source_string','Source String', $table, $user);
        $this->create('side_top','Side Panels,Top', $table, $user);
        $this->create('side_left','Side Panels,Left', $table, $user);
        $this->create('side_right','Side Panels,Right', $table, $user);
        $this->create('is_active','Public Access,Status', $table, $user, 'Boolean');
        $this->create('is_locked','Public Access,Lock', $table, $user, 'Boolean');
        $this->create('lock_pass','Public Access,PWD', $table, $user);

        $this->update($table, 'side_top', ['is_table_field_in_popup'=>1, 'is_start_table_popup'=>1]);
        $this->update($table, 'side_left', ['is_table_field_in_popup'=>1]);
        $this->update($table, 'side_right', ['is_table_field_in_popup'=>1]);
        $this->update($table, 'is_active', ['is_table_field_in_popup'=>1, 'is_start_table_popup'=>1]);
        $this->update($table, 'is_locked', ['is_table_field_in_popup'=>1]);
        $this->update($table, 'lock_pass', ['is_table_field_in_popup'=>1]);

        //add right to Visitor
        $this->permissionsService->addSystemRights($table->id);
    }

    /**
     * @param $field
     * @param $name
     * @param $table
     * @param $user
     * @param string $type
     * @param int $required
     * @param string $inpType
     * @param string $f_default
     */
    private function create($field, $name, $table, $user, $type = 'String', $required = 0, $inpType = 'Input', $f_default = '') {
        $present = $table->_fields->where('field', $field)->first();
        if (!$present) {
            TableField::create([
                'table_id' => $table->id,
                'field' => $field,
                'name' => $name,
                'input_type' => $inpType,
                'f_type' => $type,
                'f_default' => $f_default,
                'f_required' => $required,
                'created_by' => $user->id,

                'created_on' => now(),
                'modified_by' => $user->id,

                'modified_on' => now()
            ]);
        } else {
            $present->name = $name;
            $present->input_type = $inpType;
            $present->f_type = $type;
            $present->f_default = $f_default;
            $present->f_required = $required;
            $present->save();
        }
    }

    /**
     * @param Table $table
     * @param string $field
     * @param array $params
     */
    private function update(Table $table, string $field, array $params)
    {
        $present = $table->_fields->where('field', '=', $field)->first();
        if ($present) {
            $present->update($params);
        }
    }
}
