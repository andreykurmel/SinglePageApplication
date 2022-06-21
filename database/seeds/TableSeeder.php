<?php

use Illuminate\Database\Seeder;
use Vanguard\Models\Table\Table;
use Vanguard\Models\AppSetting;
use Vanguard\User;

class TableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //App Settings
        if ( ! AppSetting::where('key', 'dcr_home_demo')->first()) {
            AppSetting::create([
                'key' => 'dcr_home_demo',
                'val' => '/dcr/b4551f41-ab9d-442a-bd2e-0d8e7a60b981/Online%20Contact/Request%20for%20a%20Demo',
            ]);
        }
        if ( ! AppSetting::where('key', 'dcr_home_contact')->first()) {
            AppSetting::create([
                'key' => 'dcr_home_contact',
                'val' => '/dcr/b4551f41-ab9d-442a-bd2e-0d8e7a60b981/Online%20Contact/Contact',
            ]);
        }
        if ( ! AppSetting::where('key', 'app_enterprise_dcr')->first()) {
            AppSetting::create([
                'key' => 'app_enterprise_dcr',
                'val' => '/dcr/b4551f41-ab9d-442a-bd2e-0d8e7a60b981/Online%20Contact/Contact',
            ]);
        }
        if ( ! AppSetting::where('key', 'app_plan_comparison')->first()) {
            AppSetting::create([
                'key' => 'app_plan_comparison',
                'val' => '/mrv/e39626ec-560a-4d97-9eac-a97621d2b4a0',
            ]);
        }
        if ( ! AppSetting::where('key', 'app_our_benefits')->first()) {
            AppSetting::create([
                'key' => 'app_our_benefits',
                'val' => '/introduction/A%20Glance/Top%20Reasons%20Why%20TablDA_',
            ]);
        }
        if ( ! AppSetting::where('key', 'app_pricing_view')->first()) {
            AppSetting::create([
                'key' => 'app_pricing_view',
                'val' => '/introduction/Registration%20_%20Subscription',
            ]);
        }
        if ( ! AppSetting::where('key', 'app_usr_public_domains')->first()) {
            AppSetting::create([
                'key' => 'app_usr_public_domains',
                'val' => 'gmail.com, yahoo.com, hotmail.com, outlook.com',
            ]);
        }
        if ( ! AppSetting::where('key', 'addon_email_body_info')->first()) {
            AppSetting::create([
                'key' => 'addon_email_body_info',
                'val' => 'Max. 50 rows of data allowed. Additional rows will be ignored.',
            ]);
        }


        //settings for system tables
        $user = User::where('role_id', '=', '1')->first();
        if (!Table::where('db_name', 'table_fields')->first()) {
            Table::create([
                'is_system' => 1,
                'db_name' => 'table_fields',
                'name' => 'Table Fields',
                'user_id' => $user->id,
                'rows_per_page' => 100,
                'source' => 'scratch',
                'created_by' => $user->id,
                'created_on' => now(),
                'modified_by' => $user->id,
                'modified_on' => now(),
            ]);
        }
        if (!Table::where('db_name', 'table_fields__for_tooltips')->first()) {
            Table::create([
                'is_system' => 1,
                'db_name' => 'table_fields__for_tooltips',
                'name' => 'Tooltips',
                'user_id' => $user->id,
                'rows_per_page' => 100,
                'source' => 'scratch',
                'created_by' => $user->id,
                'created_on' => now(),
                'modified_by' => $user->id,
                'modified_on' => now(),
            ]);
        }

        if (!Table::where('db_name', 'ddl')->first()) {
            Table::create([
                'is_system' => 1,
                'db_name' => 'ddl',
                'name' => 'DDL',
                'user_id' => $user->id,
                'rows_per_page' => 100,
                'source' => 'scratch',
                'created_by' => $user->id,
                'created_on' => now(),
                'modified_by' => $user->id,
                'modified_on' => now(),
            ]);
        }

        if (!Table::where('db_name', 'ddl_items')->first()) {
            Table::create([
                'is_system' => 1,
                'db_name' => 'ddl_items',
                'name' => 'DDL Items',
                'user_id' => $user->id,
                'rows_per_page' => 100,
                'source' => 'scratch',
                'created_by' => $user->id,
                'created_on' => now(),
                'modified_by' => $user->id,
                'modified_on' => now(),
            ]);
        }

        if (!Table::where('db_name', 'ddl_references')->first()) {
            Table::create([
                'is_system' => 1,
                'db_name' => 'ddl_references',
                'name' => 'DDL References',
                'user_id' => $user->id,
                'rows_per_page' => 100,
                'source' => 'scratch',
                'created_by' => $user->id,
                'created_on' => now(),
                'modified_by' => $user->id,
                'modified_on' => now(),
            ]);
        }

        if (!Table::where('db_name', 'ddl_reference_colors')->first()) {
            Table::create([
                'is_system' => 1,
                'db_name' => 'ddl_reference_colors',
                'name' => 'DDL Reference Colors',
                'user_id' => $user->id,
                'rows_per_page' => 100,
                'source' => 'scratch',
                'created_by' => $user->id,
                'created_on' => now(),
                'modified_by' => $user->id,
                'modified_on' => now(),
            ]);
        }

        // USER GROUPS


        if (!Table::where('db_name', 'user_groups')->first()) {
            Table::create([
                'is_system' => 1,
                'db_name' => 'user_groups',
                'name' => 'User Groups',
                'user_id' => $user->id,
                'rows_per_page' => 100,
                'source' => 'scratch',
                'created_by' => $user->id,
                'created_on' => now(),
                'modified_by' => $user->id,
                'modified_on' => now(),
            ]);
        }

        if (!Table::where('db_name', 'user_groups_2_table_permissions')->first()) {
            Table::create([
                'is_system' => 1,
                'db_name' => 'user_groups_2_table_permissions',
                'name' => 'UserGroups to TablePermissions',
                'user_id' => $user->id,
                'rows_per_page' => 100,
                'source' => 'scratch',
                'created_by' => $user->id,
                'created_on' => now(),
                'modified_by' => $user->id,
                'modified_on' => now(),
            ]);
        }

        if (!Table::where('db_name', 'user_groups_2_users')->first()) {
            Table::create([
                'is_system' => 1,
                'db_name' => 'user_groups_2_users',
                'name' => 'Individuals',
                'user_id' => $user->id,
                'rows_per_page' => 100,
                'source' => 'scratch',
                'created_by' => $user->id,
                'created_on' => now(),
                'modified_by' => $user->id,
                'modified_on' => now(),
            ]);
        }

        if (!Table::where('db_name', 'user_group_conditions')->first()) {
            Table::create([
                'is_system' => 1,
                'db_name' => 'user_group_conditions',
                'name' => 'Conditions',
                'user_id' => $user->id,
                'rows_per_page' => 100,
                'source' => 'scratch',
                'created_by' => $user->id,
                'created_on' => now(),
                'modified_by' => $user->id,
                'modified_on' => now(),
            ]);
        }


        // PERMISSIONS TABLE AND FOLDER


        if (!Table::where('db_name', 'table_permissions')->first()) {
            Table::create([
                'is_system' => 1,
                'db_name' => 'table_permissions',
                'name' => 'Table Permissions',
                'user_id' => $user->id,
                'rows_per_page' => 100,
                'source' => 'scratch',
                'created_by' => $user->id,
                'created_on' => now(),
                'modified_by' => $user->id,
                'modified_on' => now(),
            ]);
        }

        if (!Table::where('db_name', 'table_permissions_2_table_column_groups')->first()) {
            Table::create([
                'is_system' => 1,
                'db_name' => 'table_permissions_2_table_column_groups',
                'name' => 'Table Permissions Columns',
                'user_id' => $user->id,
                'rows_per_page' => 100,
                'source' => 'scratch',
                'created_by' => $user->id,
                'created_on' => now(),
                'modified_by' => $user->id,
                'modified_on' => now(),
            ]);
        }

        if (!Table::where('db_name', 'table_permissions_2_table_row_groups')->first()) {
            Table::create([
                'is_system' => 1,
                'db_name' => 'table_permissions_2_table_row_groups',
                'name' => 'Table Permissions Rows',
                'user_id' => $user->id,
                'rows_per_page' => 100,
                'source' => 'scratch',
                'created_by' => $user->id,
                'created_on' => now(),
                'modified_by' => $user->id,
                'modified_on' => now(),
            ]);
        }

        if (!Table::where('db_name', 'table_permission_def_fields')->first()) {
            Table::create([
                'is_system' => 1,
                'db_name' => 'table_permission_def_fields',
                'name' => 'Table Permission Defaults',
                'user_id' => $user->id,
                'rows_per_page' => 100,
                'source' => 'scratch',
                'created_by' => $user->id,
                'created_on' => now(),
                'modified_by' => $user->id,
                'modified_on' => now(),
            ]);
        }

        if (!Table::where('db_name', 'folder_views')->first()) {
            Table::create([
                'is_system' => 1,
                'db_name' => 'folder_views',
                'name' => 'Folder Views',
                'user_id' => $user->id,
                'rows_per_page' => 100,
                'source' => 'scratch',
                'created_by' => $user->id,
                'created_on' => now(),
                'modified_by' => $user->id,
                'modified_on' => now(),
            ]);
        }


        // DATA REQUEST TABLES


        if (!Table::where('db_name', 'table_data_requests')->first()) {
            Table::create([
                'is_system' => 1,
                'db_name' => 'table_data_requests',
                'name' => 'Table Data Requests',
                'user_id' => $user->id,
                'rows_per_page' => 100,
                'source' => 'scratch',
                'created_by' => $user->id,
                'created_on' => now(),
                'modified_by' => $user->id,
                'modified_on' => now(),
            ]);
        }

        if (!Table::where('db_name', 'table_data_requests_2_table_column_groups')->first()) {
            Table::create([
                'is_system' => 1,
                'db_name' => 'table_data_requests_2_table_column_groups',
                'name' => 'Table Data Request Columns',
                'user_id' => $user->id,
                'rows_per_page' => 100,
                'source' => 'scratch',
                'created_by' => $user->id,
                'created_on' => now(),
                'modified_by' => $user->id,
                'modified_on' => now(),
            ]);
        }

        if (!Table::where('db_name', 'table_data_requests_def_fields')->first()) {
            Table::create([
                'is_system' => 1,
                'db_name' => 'table_data_requests_def_fields',
                'name' => 'Table Data Request Defaults',
                'user_id' => $user->id,
                'rows_per_page' => 100,
                'source' => 'scratch',
                'created_by' => $user->id,
                'created_on' => now(),
                'modified_by' => $user->id,
                'modified_on' => now(),
            ]);
        }

        if (!Table::where('db_name', 'dcr_linked_tables')->first()) {
            Table::create([
                'is_system' => 1,
                'db_name' => 'dcr_linked_tables',
                'name' => 'DCR Linked Tables',
                'user_id' => $user->id,
                'rows_per_page' => 100,
                'source' => 'scratch',
                'created_by' => $user->id,
                'created_on' => now(),
                'modified_by' => $user->id,
                'modified_on' => now(),
            ]);
        }


        // TABLE ROW GROUPS


        if (!Table::where('db_name', 'table_row_groups')->first()) {
            Table::create([
                'is_system' => 1,
                'db_name' => 'table_row_groups',
                'name' => 'Row Groups',
                'user_id' => $user->id,
                'rows_per_page' => 100,
                'source' => 'scratch',
                'created_by' => $user->id,
                'created_on' => now(),
                'modified_by' => $user->id,
                'modified_on' => now(),
            ]);
        }

        if (!Table::where('db_name', 'table_row_group_conditions')->first()) {
            Table::create([
                'is_system' => 1,
                'db_name' => 'table_row_group_conditions',
                'name' => 'Conditions',
                'user_id' => $user->id,
                'rows_per_page' => 100,
                'source' => 'scratch',
                'created_by' => $user->id,
                'created_on' => now(),
                'modified_by' => $user->id,
                'modified_on' => now(),
            ]);
        }

        if (!Table::where('db_name', 'table_row_group_regulars')->first()) {
            Table::create([
                'is_system' => 1,
                'db_name' => 'table_row_group_regulars',
                'name' => 'Regulars',
                'user_id' => $user->id,
                'rows_per_page' => 100,
                'source' => 'scratch',
                'created_by' => $user->id,
                'created_on' => now(),
                'modified_by' => $user->id,
                'modified_on' => now(),
            ]);
        }


        // TABLE COLUMN GROUPS


        if (!Table::where('db_name', 'table_column_groups')->first()) {
            Table::create([
                'is_system' => 1,
                'db_name' => 'table_column_groups',
                'name' => 'Column Groups',
                'user_id' => $user->id,
                'rows_per_page' => 100,
                'source' => 'scratch',
                'created_by' => $user->id,
                'created_on' => now(),
                'modified_by' => $user->id,
                'modified_on' => now(),
            ]);
        }

        if (!Table::where('db_name', 'table_column_groups_2_table_fields')->first()) {
            Table::create([
                'is_system' => 1,
                'db_name' => 'table_column_groups_2_table_fields',
                'name' => 'Columns',
                'user_id' => $user->id,
                'rows_per_page' => 100,
                'source' => 'scratch',
                'created_by' => $user->id,
                'created_on' => now(),
                'modified_by' => $user->id,
                'modified_on' => now(),
            ]);
        }


        // TABLE REF CONDITIONS


        if (!Table::where('db_name', 'table_ref_conditions')->first()) {
            Table::create([
                'is_system' => 1,
                'db_name' => 'table_ref_conditions',
                'name' => 'Referencing Conditions',
                'user_id' => $user->id,
                'rows_per_page' => 100,
                'source' => 'scratch',
                'created_by' => $user->id,
                'created_on' => now(),
                'modified_by' => $user->id,
                'modified_on' => now(),
            ]);
        }

        if (!Table::where('db_name', 'table_ref_condition_items')->first()) {
            Table::create([
                'is_system' => 1,
                'db_name' => 'table_ref_condition_items',
                'name' => 'Details',
                'user_id' => $user->id,
                'rows_per_page' => 100,
                'source' => 'scratch',
                'created_by' => $user->id,
                'created_on' => now(),
                'modified_by' => $user->id,
                'modified_on' => now(),
            ]);
        }


        // COND FORMATS


        if (!Table::where('db_name', 'cond_formats')->first()) {
            Table::create([
                'is_system' => 1,
                'db_name' => 'cond_formats',
                'name' => 'Conditional Formattings',
                'user_id' => $user->id,
                'rows_per_page' => 100,
                'source' => 'scratch',
                'created_by' => $user->id,
                'created_on' => now(),
                'modified_by' => $user->id,
                'modified_on' => now(),
            ]);
        }


        // TABLE ALERTS


        if (!Table::where('db_name', 'table_alerts')->first()) {
            Table::create([
                'is_system' => 1,
                'db_name' => 'table_alerts',
                'name' => 'Alerts and Notifications',
                'user_id' => $user->id,
                'rows_per_page' => 100,
                'source' => 'scratch',
                'created_by' => $user->id,
                'created_on' => now(),
                'modified_by' => $user->id,
                'modified_on' => now(),
            ]);
        }

        if (!Table::where('db_name', 'table_alert_conditions')->first()) {
            Table::create([
                'is_system' => 1,
                'db_name' => 'table_alert_conditions',
                'name' => 'Alert Conditions',
                'user_id' => $user->id,
                'rows_per_page' => 100,
                'source' => 'scratch',
                'created_by' => $user->id,
                'created_on' => now(),
                'modified_by' => $user->id,
                'modified_on' => now(),
            ]);
        }

        if (!Table::where('db_name', 'alert_ufv_tables')->first()) {
            Table::create([
                'is_system' => 1,
                'db_name' => 'alert_ufv_tables',
                'name' => 'Alert Ufv Table',
                'user_id' => $user->id,
                'rows_per_page' => 100,
                'source' => 'scratch',
                'created_by' => $user->id,
                'created_on' => now(),
                'modified_by' => $user->id,
                'modified_on' => now(),
            ]);
        }

        if (!Table::where('db_name', 'alert_ufv_table_fields')->first()) {
            Table::create([
                'is_system' => 1,
                'db_name' => 'alert_ufv_table_fields',
                'name' => 'Alert Ufv Field',
                'user_id' => $user->id,
                'rows_per_page' => 100,
                'source' => 'scratch',
                'created_by' => $user->id,
                'created_on' => now(),
                'modified_by' => $user->id,
                'modified_on' => now(),
            ]);
        }

        if (!Table::where('db_name', 'alert_anr_tables')->first()) {
            Table::create([
                'is_system' => 1,
                'db_name' => 'alert_anr_tables',
                'name' => 'Alert Anr Table',
                'user_id' => $user->id,
                'rows_per_page' => 100,
                'source' => 'scratch',
                'created_by' => $user->id,
                'created_on' => now(),
                'modified_by' => $user->id,
                'modified_on' => now(),
            ]);
        }

        if (!Table::where('db_name', 'alert_anr_table_fields')->first()) {
            Table::create([
                'is_system' => 1,
                'db_name' => 'alert_anr_table_fields',
                'name' => 'Alert Anr Field',
                'user_id' => $user->id,
                'rows_per_page' => 100,
                'source' => 'scratch',
                'created_by' => $user->id,
                'created_on' => now(),
                'modified_by' => $user->id,
                'modified_on' => now(),
            ]);
        }


        // TABLE VIEWS


        if (!Table::where('db_name', 'table_views')->first()) {
            Table::create([
                'is_system' => 1,
                'db_name' => 'table_views',
                'name' => 'Table Views',
                'user_id' => $user->id,
                'rows_per_page' => 100,
                'source' => 'scratch',
                'created_by' => $user->id,
                'created_on' => now(),
                'modified_by' => $user->id,
                'modified_on' => now(),
            ]);
        }


        // TABLE VIEW FILTERING


        if (!Table::where('db_name', 'table_view_filtering')->first()) {
            Table::create([
                'is_system' => 1,
                'db_name' => 'table_view_filtering',
                'name' => 'Table View Filtering',
                'user_id' => $user->id,
                'rows_per_page' => 100,
                'source' => 'scratch',
                'created_by' => $user->id,
                'created_on' => now(),
                'modified_by' => $user->id,
                'modified_on' => now(),
            ]);
        }


        // VIRTUAL TABLES !!!

        // Folder Permissions
        if (!Table::where('db_name', 'folder_permissions')->first()) {
            Table::create([
                'is_system' => 1,
                'db_name' => 'folder_permissions',
                'name' => 'Folder Permissions',
                'user_id' => $user->id,
                'rows_per_page' => 100,
                'source' => 'scratch',
                'created_by' => $user->id,
                'created_on' => now(),
                'modified_by' => $user->id,
                'modified_on' => now(),
            ]);
        }

        // Kanban Settings
        if (!Table::where('db_name', 'table_kanban_settings')->first()) {
            Table::create([
                'is_system' => 1,
                'db_name' => 'table_kanban_settings',
                'name' => 'Table Kanban Settings',
                'user_id' => $user->id,
                'rows_per_page' => 100,
                'source' => 'scratch',
                'created_by' => $user->id,
                'created_on' => now(),
                'modified_by' => $user->id,
                'modified_on' => now(),
            ]);
        }

        // Incoming Links
        if (!Table::where('db_name', 'incoming_links')->first()) {
            Table::create([
                'is_system' => 1,
                'db_name' => 'incoming_links',
                'name' => 'Incoming Links',
                'user_id' => $user->id,
                'rows_per_page' => 100,
                'source' => 'scratch',
                'created_by' => $user->id,
                'created_on' => now(),
                'modified_by' => $user->id,
                'modified_on' => now(),
            ]);
        }

        // Email Settings
        if (!Table::where('db_name', 'email_settings')->first()) {
            Table::create([
                'is_system' => 1,
                'db_name' => 'email_settings',
                'name' => 'Emails',
                'user_id' => $user->id,
                'rows_per_page' => 100,
                'source' => 'scratch',
                'created_by' => $user->id,
                'created_on' => now(),
                'modified_by' => $user->id,
                'modified_on' => now(),
            ]);
        }

        // Uploading File Formats
        if (!Table::where('db_name', 'uploading_file_formats')->first()) {
            Table::create([
                'is_system' => 1,
                'db_name' => 'uploading_file_formats',
                'name' => 'Uploading File Formats',
                'user_id' => $user->id,
                'rows_per_page' => 100,
                'source' => 'scratch',
                'created_by' => $user->id,
                'created_on' => now(),
                'modified_by' => $user->id,
                'modified_on' => now(),
            ]);
        }
    }
}
