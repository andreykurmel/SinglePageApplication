<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Vanguard\Models\Correspondences\CorrespApp;
use Vanguard\Models\Table\Table;
use Vanguard\Models\Table\TableField;
use Vanguard\Role;
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
        $this->create('controller','Controller', $table, $user, 'String',1);
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
            $this->DDLRepository->addDDLItem(['ddl_id' => $ddl->id, 'option' => 'is_export_import:file',]);

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
            $this->DDLRepository->addDDLItem(['ddl_id' => $ddl->id, 'option' => '2d:elevs_lib',]);
            $this->DDLRepository->addDDLItem(['ddl_id' => $ddl->id, 'option' => '2d:azimuth_lib',]);
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
        $this->create('top_tab','SELECTs,Level 1', $table, $user, 'String', 1);
        $this->create('select','SELECTs,Level 2', $table, $user, 'String');
        $this->create('row_order','Order', $table, $user, 'String');
        $this->create('style','Tabs,Style', $table, $user, 'String', 0, 'Select', 'vh_tabs');
        $this->create('accordion','Tabs,APanel', $table, $user, 'String');
        $this->create('horizontal_lvl1','Tabs,Horizontal', $table, $user, 'String');
        $this->create('vertical_lvl1','Tabs,Vertical', $table, $user, 'String');
        $this->create('horizontal_lvl2','Tabs,Horizontal 2', $table, $user, 'String');
        $this->create('vertical_lvl2','Tabs,Vertical 2', $table, $user, 'String');
        $this->create('db_table','App,Array', $table, $user, 'String', 0, 'S-Select');
        $this->create('type_tablda','App,Component', $table, $user, 'String', 0, 'S-Select');
        $opt_field = $this->create('options','Options', $table, $user, 'String',0, 'M-Select');
        $this->create('model_3d','GUI', $table, $user, 'String', 0, 'S-Select');
        $this->create('stimvis_status','Conditional Display,Status', $table, $user, 'String', 0, 'S-Select');
        $this->create('stimvis_table_id','Conditional Display,Table', $table, $user, 'RefTable', 0, 'S-Select');
        $this->create('stimvis_field_id','Conditional Display,Field', $table, $user, 'RefField', 0, 'S-Select');
        $this->create('stimvis_operator','Conditional Display,Operator', $table, $user, 'String', 0, 'S-Select');
        $this->create('stimvis_value','Conditional Display,Option', $table, $user);

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
        $permis->update(['can_drag_rows' => 1]);



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


        //Add available Correspondence Apps.
        $adminRole = Role::where('name', 'Admin')->first();
        $admin = User::where('role_id', $adminRole->id)->first();
        if (! CorrespApp::where('code', '=', 'e3c')->count()) {
            CorrespApp::create([
                'user_id' => $admin->id,
                'is_active' => 1,
                'name' => 'E3C',
                'db' => 'app_data',
                'app_path' => '/',
                'subdomain' => null,
                'is_public' => 0,
                'code' => 'e3c',
                'type' => 'iframe',
                'controller' => null,
                'open_as_popup' => 0,
            ]);
        }
        if (! CorrespApp::where('code', '=', 'risa3d_parser')->count()) {
            CorrespApp::create([
                'user_id' => $admin->id,
                'is_active' => 1,
                'name' => 'RISA3D Parser',
                'db' => 'app_data',
                'app_path' => '/risa3d/parser',
                'subdomain' => 'stim',
                'is_public' => 0,
                'code' => 'risa3d_parser',
                'type' => 'local',
                'controller' => 'Risa3dParserController',
                'open_as_popup' => 1,
            ]);
        }
        if (! CorrespApp::where('code', '=', 'risa3d_deleter')->count()) {
            CorrespApp::create([
                'user_id' => $admin->id,
                'is_active' => 1,
                'name' => 'RISA3D Deleter',
                'db' => 'app_data',
                'app_path' => '/risa3d/deleter',
                'subdomain' => 'stim',
                'is_public' => 0,
                'code' => 'risa3d_deleter',
                'type' => 'local',
                'controller' => 'Risa3dDeleterController',
                'open_as_popup' => 1,
            ]);
        }
        if (! CorrespApp::where('code', '=', 'stim_3d')->count()) {
            CorrespApp::create([
                'user_id' => $admin->id,
                'is_active' => 1,
                'name' => '3D',
                'db' => 'app_data',
                'app_path' => '/3d',
                'subdomain' => 'stim',
                'is_public' => 0,
                'code' => 'stim_3d',
                'type' => 'local',
                'controller' => 'StimWidController',
                'open_as_popup' => 0,
            ]);
        }
        if (! CorrespApp::where('code', '=', 'stim_ma_json')->count()) {
            CorrespApp::create([
                'user_id' => $admin->id,
                'is_active' => 1,
                'name' => 'STIM APP - MA - Writing JSON',
                'db' => 'app_data',
                'app_path' => '/stim/ma/json',
                'subdomain' => 'stim',
                'is_public' => 0,
                'code' => 'stim_ma_json',
                'type' => 'local',
                'controller' => 'StimMaJsonController',
                'open_as_popup' => 1,
            ]);
        }
        if (! CorrespApp::where('code', '=', 'call_api_design')->count()) {
            CorrespApp::create([
                'user_id' => $admin->id,
                'is_active' => 1,
                'name' => 'Call Api Design',
                'db' => 'app_data',
                'app_path' => '/MA',
                'subdomain' => 'stim',
                'is_public' => 0,
                'code' => 'call_api_design',
                'type' => 'local',
                'controller' => 'CallApiDesignController',
                'open_as_popup' => 0,
            ]);
        }
        if (! CorrespApp::where('code', '=', 'calc_params_for_node')->count()) {
            CorrespApp::create([
                'user_id' => $admin->id,
                'is_active' => 1,
                'name' => 'Calc Params for Node',
                'db' => 'app_data',
                'app_path' => '/calc/params/node',
                'subdomain' => 'stim',
                'is_public' => 0,
                'code' => 'calc_params_for_node',
                'type' => 'local',
                'controller' => 'CalcParamsForNode',
                'open_as_popup' => 1,
            ]);
        }
        if (! CorrespApp::where('code', '=', 'calc_node_values')->count()) {
            CorrespApp::create([
                'user_id' => $admin->id,
                'is_active' => 1,
                'name' => 'Calc Node Values',
                'db' => 'app_data',
                'app_path' => '/calc/node/values',
                'subdomain' => 'stim',
                'is_public' => 0,
                'code' => 'calc_node_values',
                'type' => 'local',
                'controller' => 'CalcNodeValues',
                'open_as_popup' => 1,
            ]);
        }
        if (! CorrespApp::where('code', '=', 'stim_calculate_loads')->count()) {
            CorrespApp::create([
                'user_id' => $admin->id,
                'is_active' => 1,
                'name' => 'STIM App - MA - Loading Calc.',
                'db' => 'app_data',
                'app_path' => '/stim/ma/calculate_loads',
                'subdomain' => 'stim',
                'is_public' => 0,
                'code' => 'stim_calculate_loads',
                'type' => 'local',
                'controller' => 'RisaCalculateLoadsController',
                'open_as_popup' => 1,
            ]);
        }
        if (! CorrespApp::where('code', '=', 'payment_processing')->count()) {
            CorrespApp::create([
                'user_id' => $admin->id,
                'is_active' => 1,
                'name' => 'Payment Processing',
                'db' => 'app_data',
                'app_path' => '/payment',
                'subdomain' => 'support',
                'is_public' => 1,
                'code' => 'payment_processing',
                'type' => 'local',
                'controller' => null,
                'open_as_popup' => 0,
            ]);
        }
        if (! CorrespApp::where('code', '=', 'general_json_export')->count()) {
            CorrespApp::create([
                'user_id' => $admin->id,
                'is_active' => 1,
                'name' => 'Export JSON',
                'db' => 'app_data',
                'app_path' => '/general-json/export',
                'subdomain' => 'support',
                'is_public' => 1,
                'code' => 'general_json_export',
                'type' => 'local',
                'controller' => 'GeneralJsonImportExportController',
                'open_as_popup' => 1,
            ]);
        }
        if (! CorrespApp::where('code', '=', 'general_json_import')->count()) {
            CorrespApp::create([
                'user_id' => $admin->id,
                'is_active' => 1,
                'name' => 'Import JSON',
                'db' => 'app_data',
                'app_path' => '/general-json/import',
                'subdomain' => 'support',
                'is_public' => 1,
                'code' => 'general_json_import',
                'type' => 'local',
                'controller' => 'GeneralJsonImportExportController',
                'open_as_popup' => 1,
            ]);
        }
        if (! CorrespApp::where('code', '=', 'eri_parser')->count()) {
            CorrespApp::create([
                'user_id' => $admin->id,
                'is_active' => 1,
                'name' => 'ERI Parser',
                'db' => 'app_data',
                'app_path' => '/eri-parser-writer/parse',
                'subdomain' => 'support',
                'is_public' => 1,
                'code' => 'eri_parser',
                'type' => 'local',
                'controller' => 'EriParserWriterController',
                'open_as_popup' => 1,
            ]);
        }
        if (! CorrespApp::where('code', '=', 'eri_writer')->count()) {
            CorrespApp::create([
                'user_id' => $admin->id,
                'is_active' => 1,
                'name' => 'ERI Writer',
                'db' => 'app_data',
                'app_path' => '/eri-parser-writer/write',
                'subdomain' => 'support',
                'is_public' => 1,
                'code' => 'eri_writer',
                'type' => 'local',
                'controller' => 'EriParserWriterController',
                'open_as_popup' => 1,
            ]);
        }
        if (! CorrespApp::where('code', '=', 'da_loading_ocr_parse')->count()) {
            CorrespApp::create([
                'user_id' => $admin->id,
                'is_active' => 1,
                'name' => 'tnxTower MTO DA Loading OCR Parser',
                'db' => 'app_data',
                'app_path' => '/da-loading/ocr-parser',
                'subdomain' => 'support',
                'is_public' => 1,
                'code' => 'da_loading_ocr_parse',
                'type' => 'local',
                'controller' => 'GeneralParserController',
                'open_as_popup' => 1,
            ]);
        }
        if (! CorrespApp::where('code', '=', 'mto_dal_pdf_parse')->count()) {
            CorrespApp::create([
                'user_id' => $admin->id,
                'is_active' => 1,
                'name' => 'tnxTower MTO DAL PDF Parser',
                'db' => 'app_data',
                'app_path' => '/mto-dal/pdf-parser',
                'subdomain' => 'support',
                'is_public' => 1,
                'code' => 'mto_dal_pdf_parse',
                'type' => 'local',
                'controller' => 'GeneralParserController',
                'open_as_popup' => 1,
            ]);
        }
        if (! CorrespApp::where('code', '=', 'mto_geometry_pdf_parse')->count()) {
            CorrespApp::create([
                'user_id' => $admin->id,
                'is_active' => 1,
                'name' => 'tnxTower MTO Geometry PDF Parser',
                'db' => 'app_data',
                'app_path' => '/mto-dal/geometry-parser',
                'subdomain' => 'support',
                'is_public' => 1,
                'code' => 'mto_geometry_pdf_parse',
                'type' => 'local',
                'controller' => 'GeneralParserController',
                'open_as_popup' => 1,
            ]);
        }
        if (! CorrespApp::where('code', '=', 'ai_extractm')->count()) {
            CorrespApp::create([
                'user_id' => $admin->id,
                'is_active' => 1,
                'name' => 'AI_EXTRACT1M',
                'db' => 'app_data',
                'app_path' => '/ai/extract-multiple',
                'subdomain' => 'support',
                'is_public' => 1,
                'code' => 'ai_extractm',
                'type' => 'local',
                'controller' => 'GeneralParserController',
                'open_as_popup' => 1,
            ]);
        }
        if (! CorrespApp::where('code', '=', 'smart_autoselect')->count()) {
            CorrespApp::create([
                'user_id' => $admin->id,
                'is_active' => 1,
                'name' => 'Smart Auto Select',
                'db' => 'app_data',
                'app_path' => '/smart/autoselect',
                'subdomain' => 'support',
                'is_public' => 1,
                'code' => 'smart_autoselect',
                'type' => 'local',
                'controller' => 'GeneralParserController',
                'open_as_popup' => 1,
            ]);
        }
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
