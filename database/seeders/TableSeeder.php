<?php

namespace Database\Seeders;

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
        if ( ! AppSetting::where('key', 'app_max_mirror_records')->first()) {
            AppSetting::create([
                'key' => 'app_max_mirror_records',
                'val' => '',
            ]);
        }
        if ( ! AppSetting::where('key', 'app_max_backend_sync_delay')->first()) {
            AppSetting::create([
                'key' => 'app_max_backend_sync_delay',
                'val' => '',
            ]);
        }
        if ( ! AppSetting::where('key', 'app_max_records_email_adn')->first()) {
            AppSetting::create([
                'key' => 'app_max_records_email_adn',
                'val' => '50',
            ]);
        }
        if ( ! AppSetting::where('key', 'invite_reward_amount')->first()) {
            AppSetting::create([
                'key' => 'invite_reward_amount',
                'val' => '12',
            ]);
        }
        if ( ! AppSetting::where('key', 'stim_user_id')->first()) {
            $stim = User::where('username', '=', 'STIM')->first();
            if (!$stim) {
                $stim = User::create([
                    'email' => 'STIM.TablDA@gmail.com',
                    'username' => 'STIM',
                    'password' => 'no-pass',
                    'role_id' => 3,
                    'status' => 'Active',
                ]);
            }
            AppSetting::create([
                'key' => 'stim_user_id',
                'val' => $stim->id,
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

        if (!Table::where('db_name', 'user_group_subgroups')->first()) {
            Table::create([
                'is_system' => 1,
                'db_name' => 'user_group_subgroups',
                'name' => 'User Sub Groups',
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
        $ddlTbId = Table::where('db_name', 'ddl')->first()->id;
        $ddlRepo = new \Vanguard\Repositories\Tablda\DDLRepository();
        $sysDDL = $ddlRepo->getDDLbyName('DCR Statuses', $ddlTbId);
        if (! $sysDDL) {
            $sysDDL = $ddlRepo->addDDL([
                'name' => 'DCR Statuses',
                'table_id' => $ddlTbId,
                'owner_shared' => 1,
            ]);
            $ddlRepo->addDDLItem([
                'ddl_id' => $sysDDL->id,
                'option' => 'Saved'
            ]);
            $ddlRepo->addDDLItem([
                'ddl_id' => $sysDDL->id,
                'option' => 'Submitted'
            ]);
            $ddlRepo->addDDLItem([
                'ddl_id' => $sysDDL->id,
                'option' => 'Updated'
            ]);
        }
        AppSetting::updateOrCreate(['key' => 'dcr_status_sys_ddl_id'], [
            'key' => 'dcr_status_sys_ddl_id',
            'val' => $sysDDL->id
        ]);

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

        if (!Table::where('db_name', 'dcr_notif_linked_tables')->first()) {
            Table::create([
                'is_system' => 1,
                'db_name' => 'dcr_notif_linked_tables',
                'name' => 'DCR Notification Linked Tables',
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

        if (!Table::where('db_name', 'table_alert_click_updates')->first()) {
            Table::create([
                'is_system' => 1,
                'db_name' => 'table_alert_click_updates',
                'name' => 'Alert Click Updates',
                'user_id' => $user->id,
                'rows_per_page' => 100,
                'source' => 'scratch',
                'created_by' => $user->id,
                'created_on' => now(),
                'modified_by' => $user->id,
                'modified_on' => now(),
            ]);
        }

        if (!Table::where('db_name', 'table_alert_snapshot_fields')->first()) {
            Table::create([
                'is_system' => 1,
                'db_name' => 'table_alert_snapshot_fields',
                'name' => 'Alert Snapshot Fields',
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

        // Kanban Settings
        if (!Table::where('db_name', 'table_kanban_settings_2_table_fields')->first()) {
            Table::create([
                'is_system' => 1,
                'db_name' => 'table_kanban_settings_2_table_fields',
                'name' => 'Table Kanban Settings Pivot',
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

        // Formula Helpers
        if (!Table::where('db_name', 'formula_helpers')->first()) {
            Table::create([
                'is_system' => 1,
                'db_name' => 'formula_helpers',
                'name' => 'Formula Helpers',
                'user_id' => $user->id,
                'rows_per_page' => 100,
                'source' => 'scratch',
                'created_by' => $user->id,
                'created_on' => now(),
                'modified_by' => $user->id,
                'modified_on' => now(),
            ]);
        }

        // Promo Codes
        if (!Table::where('db_name', 'promo_codes')->first()) {
            Table::create([
                'is_system' => 1,
                'db_name' => 'promo_codes',
                'name' => 'Promotion',
                'user_id' => $user->id,
                'rows_per_page' => 100,
                'source' => 'scratch',
                'created_by' => $user->id,
                'created_on' => now(),
                'modified_by' => $user->id,
                'modified_on' => now(),
            ]);
        }

        // Pages Contents
        if (!Table::where('db_name', 'page_contents')->first()) {
            Table::create([
                'is_system' => 1,
                'db_name' => 'page_contents',
                'name' => 'Page Contents',
                'user_id' => $user->id,
                'rows_per_page' => 100,
                'source' => 'scratch',
                'created_by' => $user->id,
                'created_on' => now(),
                'modified_by' => $user->id,
                'modified_on' => now(),
            ]);
        }

        // Table Tournaments
        if (!Table::where('db_name', 'table_tournaments')->first()) {
            Table::create([
                'is_system' => 1,
                'db_name' => 'table_tournaments',
                'name' => 'Table Tournaments',
                'user_id' => $user->id,
                'rows_per_page' => 100,
                'source' => 'scratch',
                'created_by' => $user->id,
                'created_on' => now(),
                'modified_by' => $user->id,
                'modified_on' => now(),
            ]);
        }

        // Table Simplemaps
        if (!Table::where('db_name', 'table_simplemaps')->first()) {
            Table::create([
                'is_system' => 1,
                'db_name' => 'table_simplemaps',
                'name' => 'Table Simplemaps',
                'user_id' => $user->id,
                'rows_per_page' => 100,
                'source' => 'scratch',
                'created_by' => $user->id,
                'created_on' => now(),
                'modified_by' => $user->id,
                'modified_on' => now(),
            ]);
        }
        // Table Simplemap Settings
        if (!Table::where('db_name', 'table_simplemaps_2_table_fields')->first()) {
            Table::create([
                'is_system' => 1,
                'db_name' => 'table_simplemaps_2_table_fields',
                'name' => 'Table Simplemaps Pivot',
                'user_id' => $user->id,
                'rows_per_page' => 100,
                'source' => 'scratch',
                'created_by' => $user->id,
                'created_on' => now(),
                'modified_by' => $user->id,
                'modified_on' => now(),
            ]);
        }

        // Table Ais
        if (!Table::where('db_name', 'table_ais')->first()) {
            Table::create([
                'is_system' => 1,
                'db_name' => 'table_ais',
                'name' => 'Table AIs',
                'user_id' => $user->id,
                'rows_per_page' => 100,
                'source' => 'scratch',
                'created_by' => $user->id,
                'created_on' => now(),
                'modified_by' => $user->id,
                'modified_on' => now(),
            ]);
        }

        // Table Gantts
        if (!Table::where('db_name', 'table_gantts')->first()) {
            Table::create([
                'is_system' => 1,
                'db_name' => 'table_gantts',
                'name' => 'Table Gantt Addons',
                'user_id' => $user->id,
                'rows_per_page' => 100,
                'source' => 'scratch',
                'created_by' => $user->id,
                'created_on' => now(),
                'modified_by' => $user->id,
                'modified_on' => now(),
            ]);
        }

        // Table Maps
        if (!Table::where('db_name', 'table_maps')->first()) {
            Table::create([
                'is_system' => 1,
                'db_name' => 'table_maps',
                'name' => 'Table Map Addons',
                'user_id' => $user->id,
                'rows_per_page' => 100,
                'source' => 'scratch',
                'created_by' => $user->id,
                'created_on' => now(),
                'modified_by' => $user->id,
                'modified_on' => now(),
            ]);
        }

        // Table Gantts
        if (!Table::where('db_name', 'table_gantt_settings')->first()) {
            Table::create([
                'is_system' => 1,
                'db_name' => 'table_gantt_settings',
                'name' => 'Table Gantt Addon Settings',
                'user_id' => $user->id,
                'rows_per_page' => 100,
                'source' => 'scratch',
                'created_by' => $user->id,
                'created_on' => now(),
                'modified_by' => $user->id,
                'modified_on' => now(),
            ]);
        }

        // Table Calendars
        if (!Table::where('db_name', 'table_calendars')->first()) {
            Table::create([
                'is_system' => 1,
                'db_name' => 'table_calendars',
                'name' => 'Table Calendar Addons',
                'user_id' => $user->id,
                'rows_per_page' => 100,
                'source' => 'scratch',
                'created_by' => $user->id,
                'created_on' => now(),
                'modified_by' => $user->id,
                'modified_on' => now(),
            ]);
        }

        // Table Calendars
        if (!Table::where('db_name', 'table_chart_tabs')->first()) {
            Table::create([
                'is_system' => 1,
                'db_name' => 'table_chart_tabs',
                'name' => 'Table Chart Tabs',
                'user_id' => $user->id,
                'rows_per_page' => 100,
                'source' => 'scratch',
                'created_by' => $user->id,
                'created_on' => now(),
                'modified_by' => $user->id,
                'modified_on' => now(),
            ]);
        }

        // Table Calendars
        if (!Table::where('db_name', 'table_data_requests_2_table_fields')->first()) {
            Table::create([
                'is_system' => 1,
                'db_name' => 'table_data_requests_2_table_fields',
                'name' => 'Table Data Request Pivot',
                'user_id' => $user->id,
                'rows_per_page' => 100,
                'source' => 'scratch',
                'created_by' => $user->id,
                'created_on' => now(),
                'modified_by' => $user->id,
                'modified_on' => now(),
            ]);
        }

        // Table Calendars
        if (!Table::where('db_name', 'table_srv_2_table_fields')->first()) {
            Table::create([
                'is_system' => 1,
                'db_name' => 'table_srv_2_table_fields',
                'name' => 'Table SRV Pivot',
                'user_id' => $user->id,
                'rows_per_page' => 100,
                'source' => 'scratch',
                'created_by' => $user->id,
                'created_on' => now(),
                'modified_by' => $user->id,
                'modified_on' => now(),
            ]);
        }

        // Table Groupings
        if (!Table::where('db_name', 'table_groupings')->first()) {
            Table::create([
                'is_system' => 1,
                'db_name' => 'table_groupings',
                'name' => 'Table Groupings',
                'user_id' => $user->id,
                'rows_per_page' => 100,
                'source' => 'scratch',
                'created_by' => $user->id,
                'created_on' => now(),
                'modified_by' => $user->id,
                'modified_on' => now(),
            ]);
        }
        if (!Table::where('db_name', 'table_grouping_fields')->first()) {
            Table::create([
                'is_system' => 1,
                'db_name' => 'table_grouping_fields',
                'name' => 'Table Grouping Fields',
                'user_id' => $user->id,
                'rows_per_page' => 100,
                'source' => 'scratch',
                'created_by' => $user->id,
                'created_on' => now(),
                'modified_by' => $user->id,
                'modified_on' => now(),
            ]);
        }
        if (!Table::where('db_name', 'table_grouping_stats')->first()) {
            Table::create([
                'is_system' => 1,
                'db_name' => 'table_grouping_stats',
                'name' => 'Table Grouping Stats',
                'user_id' => $user->id,
                'rows_per_page' => 100,
                'source' => 'scratch',
                'created_by' => $user->id,
                'created_on' => now(),
                'modified_by' => $user->id,
                'modified_on' => now(),
            ]);
        }
        if (!Table::where('db_name', 'table_grouping_field_stats')->first()) {
            Table::create([
                'is_system' => 1,
                'db_name' => 'table_grouping_field_stats',
                'name' => 'Table Grouping Field Stats',
                'user_id' => $user->id,
                'rows_per_page' => 100,
                'source' => 'scratch',
                'created_by' => $user->id,
                'created_on' => now(),
                'modified_by' => $user->id,
                'modified_on' => now(),
            ]);
        }

        // Table Reports
        if (!Table::where('db_name', 'table_report_templates')->first()) {
            Table::create([
                'is_system' => 1,
                'db_name' => 'table_report_templates',
                'name' => 'Table Report Templates',
                'user_id' => $user->id,
                'rows_per_page' => 100,
                'source' => 'scratch',
                'created_by' => $user->id,
                'created_on' => now(),
                'modified_by' => $user->id,
                'modified_on' => now(),
            ]);
        }
        if (!Table::where('db_name', 'table_reports')->first()) {
            Table::create([
                'is_system' => 1,
                'db_name' => 'table_reports',
                'name' => 'Table Reports',
                'user_id' => $user->id,
                'rows_per_page' => 100,
                'source' => 'scratch',
                'created_by' => $user->id,
                'created_on' => now(),
                'modified_by' => $user->id,
                'modified_on' => now(),
            ]);
        }
        if (!Table::where('db_name', 'table_report_sources')->first()) {
            Table::create([
                'is_system' => 1,
                'db_name' => 'table_report_sources',
                'name' => 'Table Report Sources',
                'user_id' => $user->id,
                'rows_per_page' => 100,
                'source' => 'scratch',
                'created_by' => $user->id,
                'created_on' => now(),
                'modified_by' => $user->id,
                'modified_on' => now(),
            ]);
        }
        if (!Table::where('db_name', 'table_report_source_variables')->first()) {
            Table::create([
                'is_system' => 1,
                'db_name' => 'table_report_source_variables',
                'name' => 'Table Report Source Variables',
                'user_id' => $user->id,
                'rows_per_page' => 100,
                'source' => 'scratch',
                'created_by' => $user->id,
                'created_on' => now(),
                'modified_by' => $user->id,
                'modified_on' => now(),
            ]);
        }
        if (!Table::where('db_name', 'table_kanban_group_params')->first()) {
            Table::create([
                'is_system' => 1,
                'db_name' => 'table_kanban_group_params',
                'name' => 'Table Kanban Group Params',
                'user_id' => $user->id,
                'rows_per_page' => 100,
                'source' => 'scratch',
                'created_by' => $user->id,
                'created_on' => now(),
                'modified_by' => $user->id,
                'modified_on' => now(),
            ]);
        }
        if (!Table::where('db_name', 'automation_histories')->first()) {
            Table::create([
                'is_system' => 1,
                'db_name' => 'automation_histories',
                'name' => 'Automation History',
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
