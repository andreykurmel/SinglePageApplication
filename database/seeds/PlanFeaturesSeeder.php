<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Vanguard\Models\Table\Table;
use Vanguard\Models\Table\TableField;
use Vanguard\User;

class PlanFeaturesSeeder extends Seeder
{
    private $permissionsService;
    private $permissionsRepository;
    private $viewRepository;

    /**
     * PlanFeaturesSeeder constructor.
     */
    public function __construct()
    {
        $this->permissionsService = new \Vanguard\Services\Tablda\Permissions\TablePermissionService();
        $this->viewRepository = new \Vanguard\Repositories\Tablda\TableViewRepository();
        $this->permissionsRepository = new \Vanguard\Repositories\Tablda\Permissions\TablePermissionRepository();
    }

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $user = User::where('role_id', '=', '1')->first();

        $table = Table::where('db_name', 'plan_features')->first();

        if (!$table) {
            $table = Table::create([
                'is_system' => 1,
                'db_name' => 'plan_features',
                'name' => 'Features',
                'user_id' => $user->id,
                'rows_per_page' => 100,
                'source' => 'scratch',
                'created_by' => $user->id,
                'created_on' => now(),
                'modified_by' => $user->id,
                'modified_on' => now(),
            ]);
        }

        //headers for 'Plans'

        $this->create('object_id', 'User', $table, $user, 'User');
        $this->create('q_tables', 'Menutree,General,General,Quantity of tables', $table, $user);
        $this->create('row_table', 'Menutree,General,General,Maximum records/rows per table', $table, $user);
        $this->create('data_storage_backup', 'Data,Tab,Storage & Backup', $table, $user, 'Boolean');
        $this->create('data_build', 'Data,Method,Method,Build/Update', $table, $user, 'Boolean');
        $this->create('data_csv', 'Data,Method,Method,CSV/Excel Import', $table, $user, 'Boolean');
        $this->create('data_mysql', 'Data,Method,Method,MySQL Import', $table, $user, 'Boolean');
        $this->create('data_remote', 'Data,Method,Method,Remote MySQL', $table, $user, 'Boolean');
        $this->create('data_ref', 'Data,Method,Method,Referencing', $table, $user, 'Boolean');
        $this->create('data_paste', 'Data,Method,Method,Paste to Import', $table, $user, 'Boolean');
        $this->create('data_g_sheets', 'Data,Method,Method,Google Sheets', $table, $user, 'Boolean');
        $this->create('data_web_scrap', 'Data,Method,Method,Web Scrap', $table, $user, 'Boolean');
        $this->create('unit_conversions', 'Settings,Display,Display,Unit Conversion', $table, $user, 'Boolean');
        $this->create('group_rows', 'Settings,Grouping,Grouping,Rows', $table, $user, 'Boolean');
        $this->create('group_columns', 'Settings,Grouping,Grouping,Columns', $table, $user, 'Boolean');
        $this->create('group_refs', 'Settings,Grouping,Grouping,Referencing Conditions', $table, $user, 'Boolean');
        $this->create('link_type_record', 'Settings,Functions,Link Type,Record', $table, $user, 'Boolean');
        $this->create('link_type_web', 'Settings,Functions,Link Type,Web', $table, $user, 'Boolean');
        $this->create('link_type_app', 'Settings,Functions,Link Type,App', $table, $user, 'Boolean');
        $this->create('ddl_ref', 'Settings,DDL,Type,Referencing', $table, $user, 'Boolean');
        $this->create('drag_rows', 'Settings,Basics,Basics,Change Rows Order', $table, $user, 'Boolean');
        $this->create('permission_col_view', 'Settings,Permissions,Columns,View', $table, $user, 'Boolean');
        $this->create('permission_col_edit', 'Settings,Permissions,Columns,Edit', $table, $user, 'Boolean');
        $this->create('permission_row_view', 'Settings,Permissions,Rows,View', $table, $user, 'Boolean');
        $this->create('permission_row_edit', 'Settings,Permissions,Rows,Edit', $table, $user, 'Boolean');
        $this->create('permission_row_del', 'Settings,Permissions,Rows,Delete', $table, $user, 'Boolean');
        $this->create('permission_row_add', 'Settings,Permissions,Others,Add New Record', $table, $user, 'Boolean');
        $this->create('permission_views', 'Settings,Permissions,Others,Views', $table, $user, 'Boolean');
        $this->create('permission_cond_format', 'Settings,Permissions,Others,Conditional Formatting', $table, $user, 'Boolean');
        $this->create('dwn_print', 'Settings,Permissions,Downloading,Print', $table, $user, 'Boolean');
        $this->create('dwn_csv', 'Settings,Permissions,Downloading,CSV', $table, $user, 'Boolean');
        $this->create('dwn_pdf', 'Settings,Permissions,Downloading,PDF', $table, $user, 'Boolean');
        $this->create('dwn_xls', 'Settings,Permissions,Downloading,XLSX', $table, $user, 'Boolean');
        $this->create('dwn_json', 'Settings,Permissions,Downloading,JSON', $table, $user, 'Boolean');
        $this->create('dwn_xml', 'Settings,Permissions,Downloading,XML', $table, $user, 'Boolean');
        $this->create('can_google_autocomplete', 'Google Address Autocomplete', $table, $user, 'Boolean');
        $this->create('form_visibility', 'Form Visibility', $table, $user, 'Boolean');
        $this->create('field_type_user', 'Field Type: User', $table, $user, 'Boolean');
        $this->create('apps_are_avail', 'Subdomain and Apps', $table, $user, 'Boolean');

        $this->create('created_by', 'Created By', $table, $user, 'User');
        $this->create('created_on', 'Created On', $table, $user, 'Date Time');
        $this->create('modified_by', 'Modified By', $table, $user, 'User');
        $this->create('modified_on', 'Modified On', $table, $user, 'Date Time');

        //add right to Visitor
        $this->permissionsService->addSystemRights($table->id, 0, ['object_id']);
        $this->viewRepository->addSys($table);
        $permis = $this->permissionsRepository->getSysPermission($table->id, 1);
        $permis->update(['can_edit_tb' => 1]);


        $table = Table::where('db_name', 'plans_view')->first();

        if (!$table) {
            //Create table 'Plans' as view for customize available features for each Plan
            $table = Table::create([
                'is_system' => 1,
                'db_name' => 'plans_view',
                'name' => 'Plans',
                'user_id' => $user->id,
                'rows_per_page' => 100,
                'source' => 'scratch',
                'created_by' => $user->id,
                'created_on' => now(),
                'modified_by' => $user->id,
                'modified_on' => now(),
            ]);
        }

        $this->create('code', 'Code', $table, $user);
        $this->create('who_can_view', 'Visibility', $table, $user);
        $this->create('category1', 'Index,#1', $table, $user);
        $this->create('category2', 'Index,#2', $table, $user);
        $this->create('category3', 'Index,#3', $table, $user);
        $this->create('sub_feat', 'Feature,Category', $table, $user);
        $this->create('feature', 'Feature,Name', $table, $user);
        $this->create('plan_basic', 'Plan,Basic', $table, $user, 'Boolean');
        $this->create('plan_standard', 'Plan,Standard', $table, $user, 'Boolean');
        $this->create('plan_advanced', 'Plan,Advanced', $table, $user, 'Boolean');
        $this->create('plan_enterprise', 'Plan,Enterprise', $table, $user, 'Boolean');
        $this->create('desc', 'Description,Description', $table, $user);

        $this->create('created_by', 'Created By', $table, $user, 'User');
        $this->create('created_on', 'Created On', $table, $user, 'Date Time');
        $this->create('modified_by', 'Modified By', $table, $user, 'User');
        $this->create('modified_on', 'Modified On', $table, $user, 'Date Time');

        $this->fillPV([
            'code' => 'q_tables',
            'category1' => 'Menutree',
            'category2' => 'General',
            'category3' => '',
            'feature' => 'Quantity of tables',
            'sub_feat' => 'Regular',
            'plan_basic' => '50',
            'plan_standard' => '200',
            'plan_advanced' => '500',
            'plan_enterprise' => '1000',
            'desc' => 'How many tables user can create',
            'created_by' => $user->id,
            'created_on' => now(),
            'modified_by' => $user->id,
            'modified_on' => now()
        ]);

        $this->fillPV([
            'code' => 'row_table',
            'category1' => 'Menutree',
            'category2' => 'General',
            'category3' => '',
            'feature' => 'Maximum records/rows per table',
            'sub_feat' => 'Regular',
            'plan_basic' => '1000',
            'plan_standard' => '5000',
            'plan_advanced' => '50000',
            'plan_enterprise' => '100000',
            'desc' => 'How many rows user can add to table',
            'created_by' => $user->id,
            'created_on' => now(),
            'modified_by' => $user->id,
            'modified_on' => now()
        ]);

        $this->fillPV([
            'code' => 'data_storage_backup',
            'category1' => 'Data',
            'category2' => 'Tab',
            'category3' => '',
            'feature' => 'Storage & Backup',
            'sub_feat' => 'Regular',
            'plan_basic' => '0',
            'plan_standard' => '0',
            'plan_advanced' => '1',
            'plan_enterprise' => '1',
            'desc' => 'User can use Data / Storage&Backup tab',
            'created_by' => $user->id,
            'created_on' => now(),
            'modified_by' => $user->id,
            'modified_on' => now()
        ]);

        $this->fillPV([
            'code' => 'data_build',
            'category1' => 'Data',
            'category2' => 'Method',
            'category3' => '',
            'feature' => 'Build/Update',
            'sub_feat' => 'Regular',
            'plan_basic' => '1',
            'plan_standard' => '1',
            'plan_advanced' => '1',
            'plan_enterprise' => '1',
            'desc' => 'User can use this import in Data / Method',
            'created_by' => $user->id,
            'created_on' => now(),
            'modified_by' => $user->id,
            'modified_on' => now()
        ]);

        $this->fillPV([
            'code' => 'data_csv',
            'category1' => 'Data',
            'category2' => 'Method',
            'category3' => '',
            'feature' => 'CSV/Excel Import',
            'sub_feat' => 'Regular',
            'plan_basic' => '1',
            'plan_standard' => '1',
            'plan_advanced' => '1',
            'plan_enterprise' => '1',
            'desc' => 'User can use this import in Data / Method',
            'created_by' => $user->id,
            'created_on' => now(),
            'modified_by' => $user->id,
            'modified_on' => now()
        ]);

        $this->fillPV([
            'code' => 'data_mysql',
            'category1' => 'Data',
            'category2' => 'Method',
            'category3' => '',
            'feature' => 'MySQL Import',
            'sub_feat' => 'Regular',
            'plan_basic' => '0',
            'plan_standard' => '1',
            'plan_advanced' => '1',
            'plan_enterprise' => '1',
            'desc' => 'User can use this import in Data / Method',
            'created_by' => $user->id,
            'created_on' => now(),
            'modified_by' => $user->id,
            'modified_on' => now()
        ]);

        $this->fillPV([
            'code' => 'data_remote',
            'category1' => 'Data',
            'category2' => 'Method',
            'category3' => '',
            'feature' => 'Remote MySQL',
            'sub_feat' => 'Regular',
            'plan_basic' => '0',
            'plan_standard' => '1',
            'plan_advanced' => '1',
            'plan_enterprise' => '1',
            'desc' => 'User can use this import in Data / Method',
            'created_by' => $user->id,
            'created_on' => now(),
            'modified_by' => $user->id,
            'modified_on' => now()
        ]);

        $this->fillPV([
            'code' => 'data_ref',
            'category1' => 'Data',
            'category2' => 'Method',
            'category3' => '',
            'feature' => 'Referencing',
            'sub_feat' => 'Advanced',
            'plan_basic' => '0',
            'plan_standard' => '0',
            'plan_advanced' => '1',
            'plan_enterprise' => '1',
            'desc' => 'User can use this import in Data / Method',
            'created_by' => $user->id,
            'created_on' => now(),
            'modified_by' => $user->id,
            'modified_on' => now()
        ]);

        $this->fillPV([
            'code' => 'data_paste',
            'category1' => 'Data',
            'category2' => 'Method',
            'category3' => '',
            'feature' => 'Paste to Import',
            'sub_feat' => 'Regular',
            'plan_basic' => '1',
            'plan_standard' => '1',
            'plan_advanced' => '1',
            'plan_enterprise' => '1',
            'desc' => 'User can use this import in Data / Method',
            'created_by' => $user->id,
            'created_on' => now(),
            'modified_by' => $user->id,
            'modified_on' => now()
        ]);

        $this->fillPV([
            'code' => 'data_g_sheets',
            'category1' => 'Data',
            'category2' => 'Method',
            'category3' => '',
            'feature' => 'Google Sheets',
            'sub_feat' => 'Regular',
            'plan_basic' => '0',
            'plan_standard' => '1',
            'plan_advanced' => '1',
            'plan_enterprise' => '1',
            'desc' => 'User can use this import in Data / Method',
            'created_by' => $user->id,
            'created_on' => now(),
            'modified_by' => $user->id,
            'modified_on' => now()
        ]);

        $this->fillPV([
            'code' => 'data_web_scrap',
            'category1' => 'Data',
            'category2' => 'Method',
            'category3' => '',
            'feature' => 'Web Scrap',
            'sub_feat' => 'Regular',
            'plan_basic' => '0',
            'plan_standard' => '1',
            'plan_advanced' => '1',
            'plan_enterprise' => '1',
            'desc' => 'User can use this import in Data / Method',
            'created_by' => $user->id,
            'created_on' => now(),
            'modified_by' => $user->id,
            'modified_on' => now()
        ]);

        $this->fillPV([
            'code' => 'unit_conversions',
            'category1' => 'Settings',
            'category2' => 'Display',
            'category3' => '',
            'feature' => 'Unit Conversion',
            'sub_feat' => 'Advanced',
            'plan_basic' => '0',
            'plan_standard' => '0',
            'plan_advanced' => '1',
            'plan_enterprise' => '1',
            'desc' => 'User can convert values',
            'created_by' => $user->id,
            'created_on' => now(),
            'modified_by' => $user->id,
            'modified_on' => now()
        ]);

        $this->fillPV([
            'code' => 'group_rows',
            'category1' => 'Settings',
            'category2' => 'Tab',
            'category3' => 'Grouping',
            'feature' => 'Rows',
            'sub_feat' => 'Regular',
            'plan_basic' => '1',
            'plan_standard' => '1',
            'plan_advanced' => '1',
            'plan_enterprise' => '1',
            'desc' => 'Settings / Grouping / Rows tab is available',
            'created_by' => $user->id,
            'created_on' => now(),
            'modified_by' => $user->id,
            'modified_on' => now()
        ]);

        $this->fillPV([
            'code' => 'group_columns',
            'category1' => 'Settings',
            'category2' => 'Tab',
            'category3' => 'Grouping',
            'feature' => 'Columns',
            'sub_feat' => 'Regular',
            'plan_basic' => '1',
            'plan_standard' => '1',
            'plan_advanced' => '1',
            'plan_enterprise' => '1',
            'desc' => 'Settings / Grouping / Columns tab is available',
            'created_by' => $user->id,
            'created_on' => now(),
            'modified_by' => $user->id,
            'modified_on' => now()
        ]);

        $this->fillPV([
            'code' => 'group_refs',
            'category1' => 'Settings',
            'category2' => 'Tab',
            'category3' => '',
            'feature' => 'Referencing Conditions',
            'sub_feat' => 'Regular',
            'plan_basic' => '0',
            'plan_standard' => '1',
            'plan_advanced' => '1',
            'plan_enterprise' => '1',
            'desc' => 'Settings / RCs tab is available',
            'created_by' => $user->id,
            'created_on' => now(),
            'modified_by' => $user->id,
            'modified_on' => now()
        ]);

        $this->fillPV([
            'code' => 'link_type_record',
            'category1' => 'Settings',
            'category2' => 'Functions',
            'category3' => 'Link Type',
            'feature' => 'Record',
            'sub_feat' => 'Regular',
            'plan_basic' => '1',
            'plan_standard' => '1',
            'plan_advanced' => '1',
            'plan_enterprise' => '1',
            'desc' => 'Settings / Links LinkType=Record is available',
            'created_by' => $user->id,
            'created_on' => now(),
            'modified_by' => $user->id,
            'modified_on' => now()
        ]);

        $this->fillPV([
            'code' => 'link_type_web',
            'category1' => 'Settings',
            'category2' => 'Functions',
            'category3' => 'Link Type',
            'feature' => 'Web',
            'sub_feat' => 'Regular',
            'plan_basic' => '1',
            'plan_standard' => '1',
            'plan_advanced' => '1',
            'plan_enterprise' => '1',
            'desc' => 'Settings / Links LinkType=Web is available',
            'created_by' => $user->id,
            'created_on' => now(),
            'modified_by' => $user->id,
            'modified_on' => now()
        ]);

        $this->fillPV([
            'code' => 'link_type_app',
            'category1' => 'Settings',
            'category2' => 'Functions',
            'category3' => 'Link Type',
            'feature' => 'App',
            'sub_feat' => 'Advanced',
            'plan_basic' => '0',
            'plan_standard' => '0',
            'plan_advanced' => '1',
            'plan_enterprise' => '1',
            'desc' => 'Settings / Links LinkType=App is available',
            'created_by' => $user->id,
            'created_on' => now(),
            'modified_by' => $user->id,
            'modified_on' => now()
        ]);

        $this->fillPV([
            'code' => 'ddl_ref',
            'category1' => 'Settings',
            'category2' => 'DDL',
            'category3' => '',
            'feature' => 'Type: Referencing',
            'sub_feat' => 'Advanced',
            'plan_basic' => '0',
            'plan_standard' => '0',
            'plan_advanced' => '1',
            'plan_enterprise' => '1',
            'desc' => 'Settings / DDL Reference is available',
            'created_by' => $user->id,
            'created_on' => now(),
            'modified_by' => $user->id,
            'modified_on' => now()
        ]);

        $this->fillPV([
            'code' => 'drag_rows',
            'category1' => 'Settings',
            'category2' => 'Basics',
            'category3' => '',
            'feature' => 'Change Rows Order',
            'sub_feat' => 'Regular',
            'plan_basic' => '0',
            'plan_standard' => '1',
            'plan_advanced' => '1',
            'plan_enterprise' => '1',
            'desc' => 'User can drag rows',
            'created_by' => $user->id,
            'created_on' => now(),
            'modified_by' => $user->id,
            'modified_on' => now()
        ]);

        $this->fillPV([
            'code' => 'permission_col_view',
            'category1' => 'Settings',
            'category2' => 'Permissions',
            'category3' => 'Columns',
            'feature' => 'View',
            'sub_feat' => 'Regular',
            'plan_basic' => '1',
            'plan_standard' => '1',
            'plan_advanced' => '1',
            'plan_enterprise' => '1',
            'desc' => 'User can set "view" in Settings / Share / DataRange / Columns',
            'created_by' => $user->id,
            'created_on' => now(),
            'modified_by' => $user->id,
            'modified_on' => now()
        ]);

        $this->fillPV([
            'code' => 'permission_col_edit',
            'category1' => 'Settings',
            'category2' => 'Permissions',
            'category3' => 'Columns',
            'feature' => 'Edit',
            'sub_feat' => 'Regular',
            'plan_basic' => '1',
            'plan_standard' => '1',
            'plan_advanced' => '1',
            'plan_enterprise' => '1',
            'desc' => 'User can set "edit" in Settings / Share / DataRange / Columns',
            'created_by' => $user->id,
            'created_on' => now(),
            'modified_by' => $user->id,
            'modified_on' => now()
        ]);

        $this->fillPV([
            'code' => 'permission_row_view',
            'category1' => 'Settings',
            'category2' => 'Permissions',
            'category3' => 'Rows',
            'feature' => 'View',
            'sub_feat' => 'Regular',
            'plan_basic' => '1',
            'plan_standard' => '1',
            'plan_advanced' => '1',
            'plan_enterprise' => '1',
            'desc' => 'User can set "view" in Settings / Share / DataRange / Rows',
            'created_by' => $user->id,
            'created_on' => now(),
            'modified_by' => $user->id,
            'modified_on' => now()
        ]);

        $this->fillPV([
            'code' => 'permission_row_edit',
            'category1' => 'Settings',
            'category2' => 'Permissions',
            'category3' => 'Rows',
            'feature' => 'Edit',
            'sub_feat' => 'Regular',
            'plan_basic' => '1',
            'plan_standard' => '1',
            'plan_advanced' => '1',
            'plan_enterprise' => '1',
            'desc' => 'User can set "edit" in Settings / Share / DataRange / Rows',
            'created_by' => $user->id,
            'created_on' => now(),
            'modified_by' => $user->id,
            'modified_on' => now()
        ]);

        $this->fillPV([
            'code' => 'permission_row_del',
            'category1' => 'Settings',
            'category2' => 'Permissions',
            'category3' => 'Rows',
            'feature' => 'Delete',
            'sub_feat' => 'Regular',
            'plan_basic' => '1',
            'plan_standard' => '1',
            'plan_advanced' => '1',
            'plan_enterprise' => '1',
            'desc' => 'User can set "delete" in Settings / Share / DataRange / Rows',
            'created_by' => $user->id,
            'created_on' => now(),
            'modified_by' => $user->id,
            'modified_on' => now()
        ]);

        $this->fillPV([
            'code' => 'permission_row_add',
            'category1' => 'Settings',
            'category2' => 'Permissions',
            'category3' => 'Others',
            'feature' => 'Add New Record',
            'sub_feat' => 'Regular',
            'plan_basic' => '1',
            'plan_standard' => '1',
            'plan_advanced' => '1',
            'plan_enterprise' => '1',
            'desc' => 'User can set "add" in Settings / Share / OperationalPrivileges / Basics',
            'created_by' => $user->id,
            'created_on' => now(),
            'modified_by' => $user->id,
            'modified_on' => now()
        ]);

        $this->fillPV([
            'code' => 'permission_views',
            'category1' => 'Settings',
            'category2' => 'Permissions',
            'category3' => 'Others',
            'feature' => 'Views',
            'sub_feat' => 'Regular',
            'plan_basic' => '1',
            'plan_standard' => '1',
            'plan_advanced' => '1',
            'plan_enterprise' => '1',
            'desc' => 'User can share Views in Settings / Share / OperationalPrivileges / Views',
            'created_by' => $user->id,
            'created_on' => now(),
            'modified_by' => $user->id,
            'modified_on' => now()
        ]);

        $this->fillPV([
            'code' => 'permission_cond_format',
            'category1' => 'Settings',
            'category2' => 'Permissions',
            'category3' => 'Others',
            'feature' => 'Conditional Formatting',
            'sub_feat' => 'Regular',
            'plan_basic' => '0',
            'plan_standard' => '1',
            'plan_advanced' => '1',
            'plan_enterprise' => '1',
            'desc' => 'User can share CFs in Settings / Share / OperationalPrivileges / ConditionalFormattings',
            'created_by' => $user->id,
            'created_on' => now(),
            'modified_by' => $user->id,
            'modified_on' => now()
        ]);

        $this->fillPV([
            'code' => 'form_visibility',
            'category1' => 'Settings',
            'category2' => 'Cond Format',
            'category3' => '',
            'feature' => 'Form Visibility',
            'sub_feat' => 'Regular',
            'plan_basic' => '0',
            'plan_standard' => '1',
            'plan_advanced' => '1',
            'plan_enterprise' => '1',
            'desc' => '"Visibility" columns are available in Conditional Formattings popup (button on GridView tab)',
            'created_by' => $user->id,
            'created_on' => now(),
            'modified_by' => $user->id,
            'modified_on' => now()
        ]);

        $this->fillPV([
            'code' => 'dwn_print',
            'category1' => 'Settings',
            'category2' => 'Permissions',
            'category3' => 'Downloading',
            'feature' => 'Print',
            'sub_feat' => 'Regular',
            'plan_basic' => '1',
            'plan_standard' => '1',
            'plan_advanced' => '1',
            'plan_enterprise' => '1',
            'desc' => 'User can share Download method in Settings / Share / OperationalPrivileges / Downloading',
            'created_by' => $user->id,
            'created_on' => now(),
            'modified_by' => $user->id,
            'modified_on' => now()
        ]);

        $this->fillPV([
            'code' => 'dwn_csv',
            'category1' => 'Settings',
            'category2' => 'Permissions',
            'category3' => 'Downloading',
            'feature' => 'CSV',
            'sub_feat' => 'Regular',
            'plan_basic' => '1',
            'plan_standard' => '1',
            'plan_advanced' => '1',
            'plan_enterprise' => '1',
            'desc' => 'User can share Download method in Settings / Share / OperationalPrivileges / Downloading',
            'created_by' => $user->id,
            'created_on' => now(),
            'modified_by' => $user->id,
            'modified_on' => now()
        ]);

        $this->fillPV([
            'code' => 'dwn_pdf',
            'category1' => 'Settings',
            'category2' => 'Permissions',
            'category3' => 'Downloading',
            'feature' => 'PDF',
            'sub_feat' => 'Regular',
            'plan_basic' => '0',
            'plan_standard' => '1',
            'plan_advanced' => '1',
            'plan_enterprise' => '1',
            'desc' => 'User can share Download method in Settings / Share / OperationalPrivileges / Downloading',
            'created_by' => $user->id,
            'created_on' => now(),
            'modified_by' => $user->id,
            'modified_on' => now()
        ]);

        $this->fillPV([
            'code' => 'dwn_xls',
            'category1' => 'Settings',
            'category2' => 'Permissions',
            'category3' => 'Downloading',
            'feature' => 'XLSX',
            'sub_feat' => 'Regular',
            'plan_basic' => '0',
            'plan_standard' => '1',
            'plan_advanced' => '1',
            'plan_enterprise' => '1',
            'desc' => 'User can share Download method in Settings / Share / OperationalPrivileges / Downloading',
            'created_by' => $user->id,
            'created_on' => now(),
            'modified_by' => $user->id,
            'modified_on' => now()
        ]);

        $this->fillPV([
            'code' => 'dwn_json',
            'category1' => 'Settings',
            'category2' => 'Permissions',
            'category3' => 'Downloading',
            'feature' => 'JSON',
            'sub_feat' => 'Regular',
            'plan_basic' => '0',
            'plan_standard' => '1',
            'plan_advanced' => '1',
            'plan_enterprise' => '1',
            'desc' => 'User can share Download method in Settings / Share / OperationalPrivileges / Downloading',
            'created_by' => $user->id,
            'created_on' => now(),
            'modified_by' => $user->id,
            'modified_on' => now()
        ]);

        $this->fillPV([
            'code' => 'dwn_xml',
            'category1' => 'Settings',
            'category2' => 'Permissions',
            'category3' => 'Downloading',
            'feature' => 'XML',
            'sub_feat' => 'Regular',
            'plan_basic' => '1',
            'plan_standard' => '1',
            'plan_advanced' => '1',
            'plan_enterprise' => '1',
            'desc' => 'User can share Download method in Settings / Share / OperationalPrivileges / Downloading',
            'created_by' => $user->id,
            'created_on' => now(),
            'modified_by' => $user->id,
            'modified_on' => now()
        ]);

        $this->fillPV([
            'code' => 'can_google_autocomplete',
            'category1' => '',
            'category2' => '',
            'category3' => '',
            'feature' => 'Google Address Autocomplete',
            'sub_feat' => 'Regular',
            'plan_basic' => '0',
            'plan_standard' => '1',
            'plan_advanced' => '1',
            'plan_enterprise' => '1',
            'desc' => 'User can use GoogleApi in Address field to search location (each request is charged by Google)',
            'created_by' => $user->id,
            'created_on' => now(),
            'modified_by' => $user->id,
            'modified_on' => now()
        ]);

        $this->fillPV([
            'code' => 'field_type_user',
            'category1' => 'Field',
            'category2' => 'Type',
            'category3' => '',
            'feature' => 'User',
            'sub_feat' => 'Advanced',
            'plan_basic' => '0',
            'plan_standard' => '0',
            'plan_advanced' => '1',
            'plan_enterprise' => '1',
            'desc' => 'Available "User" type for column in Data / FieldSettings',
            'created_by' => $user->id,
            'created_on' => now(),
            'modified_by' => $user->id,
            'modified_on' => now()
        ]);

        $this->fillPV([
            'code' => 'apps_are_avail',
            'category1' => 'Subdomain',
            'category2' => 'App',
            'category3' => '',
            'feature' => 'Available apps',
            'sub_feat' => 'Advanced',
            'plan_basic' => '0',
            'plan_standard' => '0',
            'plan_advanced' => '1',
            'plan_enterprise' => '1',
            'desc' => 'User can use subdomain and applications',
            'created_by' => $user->id,
            'created_on' => now(),
            'modified_by' => $user->id,
            'modified_on' => now()
        ]);

        //add right to Visitor
        $this->permissionsService->addSystemRights($table->id);
        $this->viewRepository->addSys($table);
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
     * @param string $default
     */
    protected function create($field, $name, $table, $user, $type = 'String', $required = 0, $default = '')
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

    /**
     * @param array $input
     */
    protected function fillPV(array $input)
    {
        if(!DB::table('plans_view')->where('code', $input['code'])->first()) {
            DB::table('plans_view')->insert($input);
        } else {
            DB::table('plans_view')->where('code', $input['code'])->update($input);
        }
        (new \Vanguard\Repositories\Tablda\PlanRepository())->updateAllFeatures($input);
    }
}
