<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Vanguard\Models\Table\Table;
use Vanguard\Models\Table\TableField;
use Vanguard\User;
use Vanguard\Repositories\Tablda\DDLRepository;
use Vanguard\Repositories\Tablda\Permissions\TableRefConditionRepository;

class PlanFeaturesSeeder extends Seeder
{
    private $permissionsService;
    private $service;
    private $viewRepository;

    /**
     * PlanFeaturesSeeder constructor.
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

        $this->create('object_id','User', $table, $user, 'User');
        $this->create('q_tables','Menutree,General,General,Quantity of tables', $table, $user);
        $this->create('row_table','Menutree,General,General,Maximum records/rows per table', $table, $user);
        $this->create('data_build','Data,Method,Method,Build/Update', $table, $user, 'Boolean');
        $this->create('data_csv','Data,Method,Method,CSV Import', $table, $user, 'Boolean');
        $this->create('data_mysql','Data,Method,Method,MySQL Import', $table, $user, 'Boolean');
        $this->create('data_remote','Data,Method,Method,Remote MySQL', $table, $user, 'Boolean');
        $this->create('data_ref','Data,Method,Method,Referencing', $table, $user, 'Boolean');
        $this->create('data_paste','Data,Method,Method,Paste to Import', $table, $user, 'Boolean');
        $this->create('data_g_sheet','Data,Method,Method,Google Sheet', $table, $user, 'Boolean');
        $this->create('data_web_scrap','Data,Method,Method,Web Scrap', $table, $user, 'Boolean');
        $this->create('unit_conversion','Settings,Display,Display,Unit Conversion', $table, $user, 'Boolean');
        $this->create('group_rows','Settings,Grouping,Grouping,Rows', $table, $user, 'Boolean');
        $this->create('group_columns','Settings,Grouping,Grouping,Columns', $table, $user, 'Boolean');
        $this->create('group_users','Settings,Grouping,Grouping,Users', $table, $user, 'Boolean');
        $this->create('group_refs','Settings,Grouping,Grouping,Referencing Conditions', $table, $user, 'Boolean');
        $this->create('link_type_record','Settings,Functions,Link Type,Record', $table, $user, 'Boolean');
        $this->create('link_type_table','Settings,Functions,Link Type,Table', $table, $user, 'Boolean');
        $this->create('link_type_local','Settings,Functions,Link Type,Local', $table, $user, 'Boolean');
        $this->create('link_type_web','Settings,Functions,Link Type,Web', $table, $user, 'Boolean');
        $this->create('ddl_ref','Settings,DDL,Type,Referencing', $table, $user, 'Boolean');
        $this->create('drag_rows','Settings,Basics,Basics,Change Rows Order', $table, $user, 'Boolean');
        $this->create('permission_col_view','Settings,Permissions,Columns,View', $table, $user, 'Boolean');
        $this->create('permission_col_edit','Settings,Permissions,Columns,Edit', $table, $user, 'Boolean');
        $this->create('permission_row_view','Settings,Permissions,Rows,View', $table, $user, 'Boolean');
        $this->create('permission_row_edit','Settings,Permissions,Rows,Edit', $table, $user, 'Boolean');
        $this->create('permission_row_del','Settings,Permissions,Rows,Delete', $table, $user, 'Boolean');
        $this->create('permission_row_add','Settings,Permissions,Others,Add New Record', $table, $user, 'Boolean');
        $this->create('permission_views','Settings,Permissions,Others,Views', $table, $user, 'Boolean');
        $this->create('permission_cond_format','Settings,Permissions,Others,Conditional Formatting', $table, $user, 'Boolean');
        $this->create('dwn_print','Settings,Permissions,Downloading,Print', $table, $user, 'Boolean');
        $this->create('dwn_csv','Settings,Permissions,Downloading,CSV', $table, $user, 'Boolean');
        $this->create('dwn_pdf','Settings,Permissions,Downloading,PDF', $table, $user, 'Boolean');
        $this->create('dwn_xls','Settings,Permissions,Downloading,XLSX', $table, $user, 'Boolean');
        $this->create('dwn_json','Settings,Permissions,Downloading,JSON', $table, $user, 'Boolean');
        $this->create('dwn_xml','Settings,Permissions,Downloading,XML', $table, $user, 'Boolean');
        $this->create('can_google_autocomplete','Google Address Autocomplete', $table, $user, 'Boolean');
        $this->create('form_visibility','Form Visibility', $table, $user, 'Boolean');

        $this->create('created_by','Created By', $table, $user, 'User');
        $this->create('created_on','Created On', $table, $user, 'Date Time');
        $this->create('modified_by','Modified By', $table, $user, 'User');
        $this->create('modified_on','Modified On', $table, $user, 'Date Time');

        //add right to Visitor
        $this->permissionsService->addSystemRights($table->id, 0, ['object_id']);
        $this->viewRepository->addSys($table);





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
        $this->create('category1', 'Category,#1', $table, $user);
        $this->create('category2', 'Category,#2', $table, $user);
        $this->create('category3', 'Category,#3', $table, $user);
        $this->create('feature', 'Feature,Feature', $table, $user);
        $this->create('plan_basic', 'Plan,Basic', $table, $user, 'Boolean');
        $this->create('plan_advanced', 'Plan,Advanced', $table, $user, 'Boolean');
        $this->create('plan_enterprise', 'Plan,Enterprise', $table, $user, 'Boolean');
        $this->create('desc', 'Description,Description', $table, $user);

        $this->create('created_by', 'Created By', $table, $user, 'User');
        $this->create('created_on', 'Created On', $table, $user, 'Date Time');
        $this->create('modified_by', 'Modified By', $table, $user, 'User');
        $this->create('modified_on', 'Modified On', $table, $user, 'Date Time');

        if (!DB::table('plans_view')->where('code', 'q_tables')->first()) {
            DB::table('plans_view')->insert([
                'code' => 'q_tables',
                'category1' => 'Menutree',
                'category2' => 'General',
                'category3' => null,
                'feature' => 'Quantity of tables',
                'created_by' => $user->id,
                'created_on' => now(),
                'modified_by' => $user->id,
                'modified_on' => now()
            ]);
        }

        if (!DB::table('plans_view')->where('code', 'row_table')->first()) {
            DB::table('plans_view')->insert([
                'code' => 'row_table',
                'category1' => 'Menutree',
                'category2' => 'General',
                'category3' => null,
                'feature' => 'Maximum records/rows per table',
                'created_by' => $user->id,
                'created_on' => now(),
                'modified_by' => $user->id,
                'modified_on' => now()
            ]);
        }

        if (!DB::table('plans_view')->where('code', 'data_build')->first()) {
            DB::table('plans_view')->insert([
                'code' => 'data_build',
                'category1' => 'Data',
                'category2' => 'Method',
                'category3' => null,
                'feature' => 'Build/Update',
                'created_by' => $user->id,
                'created_on' => now(),
                'modified_by' => $user->id,
                'modified_on' => now()
            ]);
        }

        if (!DB::table('plans_view')->where('code', 'data_csv')->first()) {
            DB::table('plans_view')->insert([
                'code' => 'data_csv',
                'category1' => 'Data',
                'category2' => 'Method',
                'category3' => null,
                'feature' => 'CSV Import',
                'created_by' => $user->id,
                'created_on' => now(),
                'modified_by' => $user->id,
                'modified_on' => now()
            ]);
        }

        if (!DB::table('plans_view')->where('code', 'data_mysql')->first()) {
            DB::table('plans_view')->insert([
                'code' => 'data_mysql',
                'category1' => 'Data',
                'category2' => 'Method',
                'category3' => null,
                'feature' => 'MySQL Import',
                'created_by' => $user->id,
                'created_on' => now(),
                'modified_by' => $user->id,
                'modified_on' => now()
            ]);
        }

        if (!DB::table('plans_view')->where('code', 'data_remote')->first()) {
            DB::table('plans_view')->insert([
                'code' => 'data_remote',
                'category1' => 'Data',
                'category2' => 'Method',
                'category3' => null,
                'feature' => 'Remote MySQL',
                'created_by' => $user->id,
                'created_on' => now(),
                'modified_by' => $user->id,
                'modified_on' => now()
            ]);
        }

        if (!DB::table('plans_view')->where('code', 'data_ref')->first()) {
            DB::table('plans_view')->insert([
                'code' => 'data_ref',
                'category1' => 'Data',
                'category2' => 'Method',
                'category3' => null,
                'feature' => 'Referencing',
                'created_by' => $user->id,
                'created_on' => now(),
                'modified_by' => $user->id,
                'modified_on' => now()
            ]);
        }

        if (!DB::table('plans_view')->where('code', 'data_paste')->first()) {
            DB::table('plans_view')->insert([
                'code' => 'data_paste',
                'category1' => 'Data',
                'category2' => 'Method',
                'category3' => null,
                'feature' => 'Paste to Import',
                'created_by' => $user->id,
                'created_on' => now(),
                'modified_by' => $user->id,
                'modified_on' => now()
            ]);
        }

        if (!DB::table('plans_view')->where('code', 'data_g_sheet')->first()) {
            DB::table('plans_view')->insert([
                'code' => 'data_g_sheet',
                'category1' => 'Data',
                'category2' => 'Method',
                'category3' => null,
                'feature' => 'Google Sheet',
                'created_by' => $user->id,
                'created_on' => now(),
                'modified_by' => $user->id,
                'modified_on' => now()
            ]);
        }

        if (!DB::table('plans_view')->where('code', 'data_web_scrap')->first()) {
            DB::table('plans_view')->insert([
                'code' => 'data_web_scrap',
                'category1' => 'Data',
                'category2' => 'Method',
                'category3' => null,
                'feature' => 'Web Scrap',
                'created_by' => $user->id,
                'created_on' => now(),
                'modified_by' => $user->id,
                'modified_on' => now()
            ]);
        }

        if (!DB::table('plans_view')->where('code', 'unit_conversion')->first()) {
            DB::table('plans_view')->insert([
                'code' => 'unit_conversion',
                'category1' => 'Settings',
                'category2' => 'Display',
                'category3' => null,
                'feature' => 'Unit Conversion',
                'created_by' => $user->id,
                'created_on' => now(),
                'modified_by' => $user->id,
                'modified_on' => now()
            ]);
        }

        if (!DB::table('plans_view')->where('code', 'group_rows')->first()) {
            DB::table('plans_view')->insert([
                'code' => 'group_rows',
                'category1' => 'Settings',
                'category2' => 'Grouping',
                'category3' => null,
                'feature' => 'Rows',
                'created_by' => $user->id,
                'created_on' => now(),
                'modified_by' => $user->id,
                'modified_on' => now()
            ]);
        }

        if (!DB::table('plans_view')->where('code', 'group_columns')->first()) {
            DB::table('plans_view')->insert([
                'code' => 'group_columns',
                'category1' => 'Settings',
                'category2' => 'Grouping',
                'category3' => null,
                'feature' => 'Columns',
                'created_by' => $user->id,
                'created_on' => now(),
                'modified_by' => $user->id,
                'modified_on' => now()
            ]);
        }

        if (!DB::table('plans_view')->where('code', 'group_users')->first()) {
            DB::table('plans_view')->insert([
                'code' => 'group_users',
                'category1' => 'Settings',
                'category2' => 'Grouping',
                'category3' => null,
                'feature' => 'Users',
                'created_by' => $user->id,
                
                'created_on' => now(),
                'modified_by' => $user->id,
                
                'modified_on' => now()
            ]);
        }

        if (!DB::table('plans_view')->where('code', 'group_refs')->first()) {
            DB::table('plans_view')->insert([
                'code' => 'group_refs',
                'category1' => 'Settings',
                'category2' => 'Grouping',
                'category3' => 'Link Type',
                'feature' => 'Referencing Conditions',
                'created_by' => $user->id,
                'created_on' => now(),
                'modified_by' => $user->id,
                'modified_on' => now()
            ]);
        }

        if (!DB::table('plans_view')->where('code', 'link_type_record')->first()) {
            DB::table('plans_view')->insert([
                'code' => 'link_type_record',
                'category1' => 'Settings',
                'category2' => 'Functions',
                'category3' => 'Link Type',
                'feature' => 'Record',
                'created_by' => $user->id,
                'created_on' => now(),
                'modified_by' => $user->id,
                'modified_on' => now()
            ]);
        }

        if (!DB::table('plans_view')->where('code', 'link_type_table')->first()) {
            DB::table('plans_view')->insert([
                'code' => 'link_type_table',
                'category1' => 'Settings',
                'category2' => 'Functions',
                'category3' => 'Link Type',
                'feature' => 'Table',
                'created_by' => $user->id,
                'created_on' => now(),
                'modified_by' => $user->id,
                'modified_on' => now()
            ]);
        }

        if (!DB::table('plans_view')->where('code', 'link_type_local')->first()) {
            DB::table('plans_view')->insert([
                'code' => 'link_type_local',
                'category1' => 'Settings',
                'category2' => 'Functions',
                'category3' => 'Link Type',
                'feature' => 'Local',
                'created_by' => $user->id,
                'created_on' => now(),
                'modified_by' => $user->id,
                'modified_on' => now()
            ]);
        }

        if (!DB::table('plans_view')->where('code', 'link_type_web')->first()) {
            DB::table('plans_view')->insert([
                'code' => 'link_type_web',
                'category1' => 'Settings',
                'category2' => 'Functions',
                'category3' => null,
                'feature' => 'Web',
                'created_by' => $user->id,
                'created_on' => now(),
                'modified_by' => $user->id,
                'modified_on' => now()
            ]);
        }

        if (!DB::table('plans_view')->where('code', 'ddl_ref')->first()) {
            DB::table('plans_view')->insert([
                'code' => 'ddl_ref',
                'category1' => 'Settings',
                'category2' => 'DDL',
                'category3' => null,
                'feature' => 'Type: Referencing',
                'created_by' => $user->id,
                'created_on' => now(),
                'modified_by' => $user->id,
                'modified_on' => now()
            ]);
        }

        if (!DB::table('plans_view')->where('code', 'drag_rows')->first()) {
            DB::table('plans_view')->insert([
                'code' => 'drag_rows',
                'category1' => 'Settings',
                'category2' => 'Basics',
                'category3' => null,
                'feature' => 'Change Rows Order',
                'created_by' => $user->id,
                'created_on' => now(),
                'modified_by' => $user->id,
                'modified_on' => now()
            ]);
        }

        if (!DB::table('plans_view')->where('code', 'permission_col_view')->first()) {
            DB::table('plans_view')->insert([
                'code' => 'permission_col_view',
                'category1' => 'Settings',
                'category2' => 'Permissions',
                'category3' => 'Columns',
                'feature' => 'View',
                'created_by' => $user->id,
                'created_on' => now(),
                'modified_by' => $user->id,
                'modified_on' => now()
            ]);
        }

        if (!DB::table('plans_view')->where('code', 'permission_col_edit')->first()) {
            DB::table('plans_view')->insert([
                'code' => 'permission_col_edit',
                'category1' => 'Settings',
                'category2' => 'Permissions',
                'category3' => 'Columns',
                'feature' => 'Edit',
                'created_by' => $user->id,
                'created_on' => now(),
                'modified_by' => $user->id,
                'modified_on' => now()
            ]);
        }

        if (!DB::table('plans_view')->where('code', 'permission_row_view')->first()) {
            DB::table('plans_view')->insert([
                'code' => 'permission_row_view',
                'category1' => 'Settings',
                'category2' => 'Permissions',
                'category3' => 'Rows',
                'feature' => 'View',
                'created_by' => $user->id,
                'created_on' => now(),
                'modified_by' => $user->id,
                'modified_on' => now()
            ]);
        }

        if (!DB::table('plans_view')->where('code', 'permission_row_edit')->first()) {
            DB::table('plans_view')->insert([
                'code' => 'permission_row_edit',
                'category1' => 'Settings',
                'category2' => 'Permissions',
                'category3' => 'Rows',
                'feature' => 'Edit',
                'created_by' => $user->id,
                'created_on' => now(),
                'modified_by' => $user->id,
                'modified_on' => now()
            ]);
        }

        if (!DB::table('plans_view')->where('code', 'permission_row_del')->first()) {
            DB::table('plans_view')->insert([
                'code' => 'permission_row_del',
                'category1' => 'Settings',
                'category2' => 'Permissions',
                'category3' => 'Rows',
                'feature' => 'Delete',
                'created_by' => $user->id,
                'created_on' => now(),
                'modified_by' => $user->id,
                'modified_on' => now()
            ]);
        }

        if (!DB::table('plans_view')->where('code', 'permission_row_add')->first()) {
            DB::table('plans_view')->insert([
                'code' => 'permission_row_add',
                'category1' => 'Settings',
                'category2' => 'Permissions',
                'category3' => 'Others',
                'feature' => 'Add New Record',
                'created_by' => $user->id,
                'created_on' => now(),
                'modified_by' => $user->id,
                'modified_on' => now()
            ]);
        }

        if (!DB::table('plans_view')->where('code', 'permission_views')->first()) {
            DB::table('plans_view')->insert([
                'code' => 'permission_views',
                'category1' => 'Settings',
                'category2' => 'Permissions',
                'category3' => 'Others',
                'feature' => 'Views',
                'created_by' => $user->id,
                'created_on' => now(),
                'modified_by' => $user->id,
                'modified_on' => now()
            ]);
        }

        if (!DB::table('plans_view')->where('code', 'permission_cond_format')->first()) {
            DB::table('plans_view')->insert([
                'code' => 'permission_cond_format',
                'category1' => 'Settings',
                'category2' => 'Permissions',
                'category3' => 'Others',
                'feature' => 'Conditional Formatting',
                'created_by' => $user->id,
                'created_on' => now(),
                'modified_by' => $user->id,
                'modified_on' => now()
            ]);
        }

        if (!DB::table('plans_view')->where('code', 'dwn_print')->first()) {
            DB::table('plans_view')->insert([
                'code' => 'dwn_print',
                'category1' => 'Settings',
                'category2' => 'Permissions',
                'category3' => 'Downloading',
                'feature' => 'Print',
                'created_by' => $user->id,
                'created_on' => now(),
                'modified_by' => $user->id,
                'modified_on' => now()
            ]);
        }

        if (!DB::table('plans_view')->where('code', 'dwn_csv')->first()) {
            DB::table('plans_view')->insert([
                'code' => 'dwn_csv',
                'category1' => 'Settings',
                'category2' => 'Permissions',
                'category3' => 'Downloading',
                'feature' => 'CSV',
                'created_by' => $user->id,
                'created_on' => now(),
                'modified_by' => $user->id,
                'modified_on' => now()
            ]);
        }

        if (!DB::table('plans_view')->where('code', 'dwn_pdf')->first()) {
            DB::table('plans_view')->insert([
                'code' => 'dwn_pdf',
                'category1' => 'Settings',
                'category2' => 'Permissions',
                'category3' => 'Downloading',
                'feature' => 'PDF',
                'created_by' => $user->id,
                'created_on' => now(),
                'modified_by' => $user->id,
                'modified_on' => now()
            ]);
        }

        if (!DB::table('plans_view')->where('code', 'dwn_xls')->first()) {
            DB::table('plans_view')->insert([
                'code' => 'dwn_xls',
                'category1' => 'Settings',
                'category2' => 'Permissions',
                'category3' => 'Downloading',
                'feature' => 'XLSX',
                'created_by' => $user->id,
                'created_on' => now(),
                'modified_by' => $user->id,
                'modified_on' => now()
            ]);
        }

        if (!DB::table('plans_view')->where('code', 'dwn_json')->first()) {
            DB::table('plans_view')->insert([
                'code' => 'dwn_json',
                'category1' => 'Settings',
                'category2' => 'Permissions',
                'category3' => 'Downloading',
                'feature' => 'JSON',
                'created_by' => $user->id,
                'created_on' => now(),
                'modified_by' => $user->id,
                'modified_on' => now()
            ]);
        }

        if (!DB::table('plans_view')->where('code', 'dwn_xml')->first()) {
            DB::table('plans_view')->insert([
                'code' => 'dwn_xml',
                'category1' => 'Settings',
                'category2' => 'Permissions',
                'category3' => 'Downloading',
                'feature' => 'XML',
                'created_by' => $user->id,
                'created_on' => now(),
                'modified_by' => $user->id,
                'modified_on' => now()
            ]);
        }

        if (!DB::table('plans_view')->where('code', 'can_google_autocomplete')->first()) {
            DB::table('plans_view')->insert([
                'code' => 'can_google_autocomplete',
                'category1' => null,
                'category2' => null,
                'category3' => null,
                'feature' => 'Google Address Autocomplete',
                'created_by' => $user->id,
                'created_on' => now(),
                'modified_by' => $user->id,
                'modified_on' => now()
            ]);
        }

        if (!DB::table('plans_view')->where('code', 'form_visibility')->first()) {
            DB::table('plans_view')->insert([
                'code' => 'form_visibility',
                'category1' => null,
                'category2' => null,
                'category3' => null,
                'feature' => 'Form Visibility',
                'created_by' => $user->id,
                'created_on' => now(),
                'modified_by' => $user->id,
                'modified_on' => now()
            ]);
        }

        //add right to Visitor
        $this->permissionsService->addSystemRights($table->id);
        $this->viewRepository->addSys($table);
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
