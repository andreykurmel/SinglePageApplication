<?php

use Illuminate\Database\Seeder;
use Vanguard\Models\Table\Table;
use Vanguard\Models\Table\TableField;
use Vanguard\User;

class CorrespondencesSeeder extends Seeder
{
    private $permissionsService;
    private $permissionsRepository;
    private $DDLRepository;

    /**
     * CorrespondencesSeeder constructor.
     */
    public function __construct()
    {
        $this->permissionsService = new \Vanguard\Services\Tablda\Permissions\TablePermissionService();
        $this->permissionsRepository = new \Vanguard\Repositories\Tablda\Permissions\TablePermissionRepository();
        $this->DDLRepository = new \Vanguard\Repositories\Tablda\DDLRepository();
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
                'created_on' => now(),
                'modified_by' => $user->id,
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
        $permis = $this->permissionsRepository->getSysPermission($table->id, 1);
        $permis->update(['can_edit_tb' => 1]);



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
                'created_on' => now(),
                'modified_by' => $user->id,
                'modified_on' => now(),
            ]);
        }

        $this->create('user_id','Owner', $table, $user, 'User');
        $this->create('active','On/Off', $table, $user, 'Boolean', 0, 'Input', 1);
        $this->create('correspondence_app_id','App,Name', $table, $user, 'String', 1, 'S-Select');
        $this->create('app_table','App,Array', $table, $user, 'String', 1, 'Input');
        $this->create('data_table','Data,Table', $table, $user, 'RefTable',0, 'S-Select');
        $opt_field = $this->create('options','Options', $table, $user, 'String', 0, 'M-Select');
        $this->create('on_change_app_id','On Change Handler', $table, $user, 'String', 0, 'S-Select');
        $this->create('notes','Notes', $table, $user);

        //DDL for 'Options' Tables.tbl
        if (!$opt_field->ddl_id) {
            $ddl = $this->DDLRepository->addDDL([
                'table_id' => $table->id,
                'name' => 'OptionsDDL',
                'type' => 'Regular',
            ]);

            $this->DDLRepository->addDDLItem(['ddl_id' => $ddl->id, 'option' => 'filter:true',]);
            $this->DDLRepository->addDDLItem(['ddl_id' => $ddl->id, 'option' => 'del_icon:hide',]);
            $this->DDLRepository->addDDLItem(['ddl_id' => $ddl->id, 'option' => 'check_icon:hide',]);
            $this->DDLRepository->addDDLItem(['ddl_id' => $ddl->id, 'option' => 'is_public:true',]);
            $this->DDLRepository->addDDLItem(['ddl_id' => $ddl->id, 'option' => 'paste2import:true',]);
            $this->DDLRepository->addDDLItem(['ddl_id' => $ddl->id, 'option' => 'rts:true',]);
            $this->DDLRepository->addDDLItem(['ddl_id' => $ddl->id, 'option' => 'download:true',]);
            $this->DDLRepository->addDDLItem(['ddl_id' => $ddl->id, 'option' => 'json_drill:false',]);
            $this->DDLRepository->addDDLItem(['ddl_id' => $ddl->id, 'option' => 'halfmoon:true',]);
            $this->DDLRepository->addDDLItem(['ddl_id' => $ddl->id, 'option' => 'copy_from_model:true',]);
            $this->DDLRepository->addDDLItem(['ddl_id' => $ddl->id, 'option' => 'section_parse:true',]);
            $this->DDLRepository->addDDLItem(['ddl_id' => $ddl->id, 'option' => 'deletable:false',]);
            $this->DDLRepository->addDDLItem(['ddl_id' => $ddl->id, 'option' => 'search_block:true',]);
            $this->DDLRepository->addDDLItem(['ddl_id' => $ddl->id, 'option' => 'cond_format:true',]);
            $this->DDLRepository->addDDLItem(['ddl_id' => $ddl->id, 'option' => 'view_popup:true',]);
            $this->DDLRepository->addDDLItem(['ddl_id' => $ddl->id, 'option' => 'cell_height:true',]);
            $this->DDLRepository->addDDLItem(['ddl_id' => $ddl->id, 'option' => 'string_replace:true',]);
            $this->DDLRepository->addDDLItem(['ddl_id' => $ddl->id, 'option' => 'fill_attachments:true',]);
            $this->DDLRepository->addDDLItem(['ddl_id' => $ddl->id, 'option' => 'rl_calc:true',]);
            $this->DDLRepository->addDDLItem(['ddl_id' => $ddl->id, 'option' => 'recalc_rl:true',]);

            $opt_field->ddl_id = $ddl->id;
            $opt_field->save();
        }

        //add right to Visitor
        $this->permissionsService->addSystemRights($table->id, 1);
        $permis = $this->permissionsRepository->getSysPermission($table->id, 1);
        $permis->update(['can_edit_tb' => 1]);



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
                'created_on' => now(),
                'modified_by' => $user->id,
                'modified_on' => now(),
            ]);
        }

        $this->create('user_id','Owner', $table, $user, 'User');
        $this->create('correspondence_app_id','App,Name', $table, $user, 'String', 1, 'S-Select');
        $this->create('correspondence_table_id','App,Array', $table, $user, 'String', 1, 'S-Select');
        $this->create('app_field','App,Property', $table, $user, 'String');
        $this->create('_data_table_id','Data,Table', $table, $user, 'RefTable',0, 'S-Select');
        $this->create('data_field','Data,Field', $table, $user, 'RefField',0, 'S-Select');
        $this->create('link_table_db','Inheritance,Table', $table, $user, 'RefTable',0, 'S-Select');
        $this->create('link_field_db','Inheritance,Field', $table, $user, 'RefField',0, 'S-Select');
        $inh_field = $this->create('link_field_type','Inheritance,Type', $table, $user, 'String',0, 'M-Select');
        $opt_field = $this->create('options','Options', $table, $user, 'String',0, 'M-Select');
        $this->create('notes','Notes', $table, $user);

        //DDL for 'Options' Fields.tbl
        if (!$opt_field->ddl_id) {
            $ddl = $this->DDLRepository->addDDL([
                'table_id' => $table->id,
                'name' => 'OptionsDDL',
                'type' => 'Regular',
            ]);

            $this->DDLRepository->addDDLItem(['ddl_id' => $ddl->id, 'option' => 'key:true',]);
            $this->DDLRepository->addDDLItem(['ddl_id' => $ddl->id, 'option' => 'show:true',]);
            $this->DDLRepository->addDDLItem(['ddl_id' => $ddl->id, 'option' => 'display_top:true',]);
            $this->DDLRepository->addDDLItem(['ddl_id' => $ddl->id, 'option' => 'copy_prefix:true',]);
            $this->DDLRepository->addDDLItem(['ddl_id' => $ddl->id, 'option' => 'in_url:true',]);
            $this->DDLRepository->addDDLItem(['ddl_id' => $ddl->id, 'option' => 'is_rts:dx',]);
            $this->DDLRepository->addDDLItem(['ddl_id' => $ddl->id, 'option' => 'is_rts:dy',]);
            $this->DDLRepository->addDDLItem(['ddl_id' => $ddl->id, 'option' => 'is_rts:dz',]);
            $this->DDLRepository->addDDLItem(['ddl_id' => $ddl->id, 'option' => 'is_fld:name',]);
            $this->DDLRepository->addDDLItem(['ddl_id' => $ddl->id, 'option' => 'json_drill:false',]);
            $this->DDLRepository->addDDLItem(['ddl_id' => $ddl->id, 'option' => 'on:edit',]);
            $this->DDLRepository->addDDLItem(['ddl_id' => $ddl->id, 'option' => 'logo:in_select',]);

            $opt_field->ddl_id = $ddl->id;
            $opt_field->save();
        }
        //DDL for 'Inheritance Type' Fields.tbl
        if (!$inh_field->ddl_id) {
            $ddl = $this->DDLRepository->addDDL([
                'table_id' => $table->id,
                'name' => 'Inheritance Type',
                'type' => 'Regular',
            ]);

            $this->DDLRepository->addDDLItem(['ddl_id' => $ddl->id, 'option' => '3d:materials',]);
            $this->DDLRepository->addDDLItem(['ddl_id' => $ddl->id, 'option' => '3d:sections',]);
            $this->DDLRepository->addDDLItem(['ddl_id' => $ddl->id, 'option' => '3d:nodes',]);
            $this->DDLRepository->addDDLItem(['ddl_id' => $ddl->id, 'option' => '3d:members',]);
            $this->DDLRepository->addDDLItem(['ddl_id' => $ddl->id, 'option' => '3d:geom',]);
            $this->DDLRepository->addDDLItem(['ddl_id' => $ddl->id, 'option' => '3d:lcs',]);
            $this->DDLRepository->addDDLItem(['ddl_id' => $ddl->id, 'option' => '3d:eqs',]);
            $this->DDLRepository->addDDLItem(['ddl_id' => $ddl->id, 'option' => '3d:loading',]);
            $this->DDLRepository->addDDLItem(['ddl_id' => $ddl->id, 'option' => 'popup:equipment',]);
            $this->DDLRepository->addDDLItem(['ddl_id' => $ddl->id, 'option' => 'popup:nodes',]);
            $this->DDLRepository->addDDLItem(['ddl_id' => $ddl->id, 'option' => 'popup:members',]);
            $this->DDLRepository->addDDLItem(['ddl_id' => $ddl->id, 'option' => 'popup:lcs',]);
            $this->DDLRepository->addDDLItem(['ddl_id' => $ddl->id, 'option' => '3d:colors_eq',]);
            $this->DDLRepository->addDDLItem(['ddl_id' => $ddl->id, 'option' => '3d:colors_mem',]);
            $this->DDLRepository->addDDLItem(['ddl_id' => $ddl->id, 'option' => '2d:eqpt_lib',]);
            $this->DDLRepository->addDDLItem(['ddl_id' => $ddl->id, 'option' => '2d:line_lib',]);
            $this->DDLRepository->addDDLItem(['ddl_id' => $ddl->id, 'option' => '2d:sectors',]);
            $this->DDLRepository->addDDLItem(['ddl_id' => $ddl->id, 'option' => '2d:pos',]);
            $this->DDLRepository->addDDLItem(['ddl_id' => $ddl->id, 'option' => '2d:equipment',]);
            $this->DDLRepository->addDDLItem(['ddl_id' => $ddl->id, 'option' => '2d:data_conn',]);
            $this->DDLRepository->addDDLItem(['ddl_id' => $ddl->id, 'option' => '2d:data_eqpt',]);
            $this->DDLRepository->addDDLItem(['ddl_id' => $ddl->id, 'option' => '2d:filters',]);
            $this->DDLRepository->addDDLItem(['ddl_id' => $ddl->id, 'option' => '2d:eqpt_colors',]);
            $this->DDLRepository->addDDLItem(['ddl_id' => $ddl->id, 'option' => '2d:g_settings',]);
            $this->DDLRepository->addDDLItem(['ddl_id' => $ddl->id, 'option' => 'popup:feedline',]);
            $this->DDLRepository->addDDLItem(['ddl_id' => $ddl->id, 'option' => '2d:tech_list',]);
            $this->DDLRepository->addDDLItem(['ddl_id' => $ddl->id, 'option' => '2d:eqpt_sett',]);
            $this->DDLRepository->addDDLItem(['ddl_id' => $ddl->id, 'option' => '3d:wid_sett',]);
            $this->DDLRepository->addDDLItem(['ddl_id' => $ddl->id, 'option' => '3d:secpos',]);
            $this->DDLRepository->addDDLItem(['ddl_id' => $ddl->id, 'option' => '3d:pos_to_mbr',]);
            $this->DDLRepository->addDDLItem(['ddl_id' => $ddl->id, 'option' => 'popup:rls',]);

            $inh_field->ddl_id = $ddl->id;
            $inh_field->save();
        }

        //add right to Visitor
        $this->permissionsService->addSystemRights($table->id, 1);
        $permis = $this->permissionsRepository->getSysPermission($table->id, 1);
        $permis->update(['can_edit_tb' => 1]);



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
                'created_on' => now(),
                'modified_by' => $user->id,
                'modified_on' => now(),
            ]);
        }

        $this->create('avail_to_user','User', $table, $user, 'User');
        $this->create('top_tab','Header,Top Tabs', $table, $user, 'String', 1);
        $this->create('select','Header,Select', $table, $user, 'String');
        $this->create('style','Tabs,Style', $table, $user, 'String', 0, 'Select', 'vh_tabs');
        $this->create('accordion','Tabs,APanel', $table, $user, 'String');
        $this->create('horizontal','Tabs,Horizontal', $table, $user, 'String');
        $this->create('vertical','Tabs,Vertical', $table, $user, 'String');
        $this->create('db_table','App,Array', $table, $user, 'String', 0, 'S-Select');
        $this->create('type_tablda','App,Component', $table, $user, 'String', 0, 'S-Select');
        $opt_field = $this->create('options','Options', $table, $user, 'String',0, 'M-Select');
        $this->create('model_3d','GUI', $table, $user, 'String', 0, 'S-Select');

        //DDL for 'Options' Fields.tbl
        if (!$opt_field->ddl_id) {
            $ddl = $this->DDLRepository->addDDL([
                'table_id' => $table->id,
                'name' => 'OptionsDDL',
                'type' => 'Regular',
            ]);

            $this->DDLRepository->addDDLItem(['ddl_id' => $ddl->id, 'option' => 'is_master:true',]);
            $this->DDLRepository->addDDLItem(['ddl_id' => $ddl->id, 'option' => 'is_hidden:true',]);

            $opt_field->ddl_id = $ddl->id;
            $opt_field->save();
        }

        //add right to Visitor
        $this->permissionsService->addSystemRights($table->id);
        $permis = $this->permissionsRepository->getSysPermission($table->id, 1);
        $permis->update(['can_edit_tb' => 1]);



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
                'created_on' => now(),
                'modified_by' => $user->id,
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
        $permis = $this->permissionsRepository->getSysPermission($table->id, 1);
        $permis->update(['can_edit_tb' => 1]);



        $table = Table::where('db_name', '=', 'stim_app_view_feedbacks')->first();
        if (!$table) {
            $table = Table::create([
                'is_system' => 2,
                'db_name' => 'stim_app_view_feedbacks',
                'name' => 'Stim View Feedbacks',
                'user_id' => $user->id,
                'rows_per_page' => 100,
                'source' => 'scratch',
                'created_by' => $user->id,
                'created_on' => now(),
                'modified_by' => $user->id,
                'modified_on' => now(),
            ]);
        }

        $this->create('email_to','To', $table, $user);
        $this->create('email_cc','CC', $table, $user);
        $this->create('email_bcc','BCC', $table, $user);
        $this->create('email_subject','Subject', $table, $user);
        $this->create('email_body','Body', $table, $user);
        $this->create('purpose','Purpose', $table, $user, 'String', 1);
        $this->create('_edit_email','Request,Message', $table, $user);
        $this->create('_send_email','Request,Action', $table, $user);
        $this->create('send_date','Request,Date Time', $table, $user, 'Date Time');
        $this->create('request_pass','Request,Password', $table, $user);

        //add right to Visitor
        $this->permissionsService->addSystemRights($table->id);
        $permis = $this->permissionsRepository->getSysPermission($table->id, 1);
        $permis->update(['can_edit_tb' => 1]);



        $table = Table::where('db_name', '=', 'stim_app_view_feedback_results')->first();
        if (!$table) {
            $table = Table::create([
                'is_system' => 2,
                'db_name' => 'stim_app_view_feedback_results',
                'name' => 'Stim View Feedback Results',
                'user_id' => $user->id,
                'rows_per_page' => 100,
                'source' => 'scratch',
                'created_by' => $user->id,
                'created_on' => now(),
                'modified_by' => $user->id,
                'modified_on' => now(),
            ]);
        }

        $this->create('user_signature','Received,Signature', $table, $user, 'String', 1);
        $this->create('received_date','Received,Date Time', $table, $user, 'Date Time', 1);
        $this->create('notes','Received,Notes', $table, $user, 'String', 1);
        $this->create('received_attachments','Received,Attachments', $table, $user, 'Attachment');

        //add right to Visitor
        $this->permissionsService->addSystemRights($table->id);
        $permis = $this->permissionsRepository->getSysPermission($table->id, 1);
        $permis->update(['can_edit_tb' => 1]);



        $table = Table::where('db_name', '=', 'user_activity')->first();
        if (!$table) {
            $table = Table::create([
                'is_system' => 1,
                'db_name' => 'user_activity',
                'name' => 'Activity',
                'user_id' => $user->id,
                'rows_per_page' => 100,
                'source' => 'scratch',
                'created_by' => $user->id,
                'created_on' => now(),
                'modified_by' => $user->id,
                'modified_on' => now(),
            ]);
        }

        $this->create('user_id','User', $table, $user, 'User');
        $this->create('ip_address','Ip Address', $table, $user);
        $this->create('description_time','Time,Logged In', $table, $user);
        $this->create('ending_time','Time,Logged Out', $table, $user);
        $this->create('difference_time','Time,Length', $table, $user, 'Duration');
        $this->create('lat','Location,Latitude', $table, $user);
        $this->create('lng','Location,Longitude', $table, $user);
        $this->create('year','Date,Year', $table, $user);
        $this->create('month','Date,Month', $table, $user);
        $this->create('week','Date,Week', $table, $user);
        $this->create('user_agent','User Agent', $table, $user);

        //add right to Visitor
        $this->permissionsService->addSystemRights($table->id, 0, ['user_id']);
        $permis = $this->permissionsRepository->getSysPermission($table->id, 1);
        $permis->update(['can_edit_tb' => 1]);
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
     * @return \Illuminate\Database\Eloquent\Model|TableField
     */
    private function create($field, $name, $table, $user, $type = 'String', $required = 0, $inpType = 'Input', $f_default = '') {
        $present = $table->_fields()->where('field', $field)->first();
        if (!$present) {
            return TableField::create([
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
            return $present;
        }
    }

    /**
     * @param Table $table
     * @param string $field
     * @param array $params
     */
    private function update(Table $table, string $field, array $params)
    {
        $present = $table->_fields()->where('field', '=', $field)->first();
        if ($present) {
            $present->update($params);
        }
    }
}
