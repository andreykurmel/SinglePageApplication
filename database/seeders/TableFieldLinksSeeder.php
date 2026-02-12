<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Vanguard\Models\Table\Table;
use Vanguard\Models\Table\TableField;
use Vanguard\User;

class TableFieldLinksSeeder extends Seeder
{
    private $permissionsService;

    /**
     * TableFieldLinksSeeder constructor.
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

        $table = Table::where('db_name', 'table_field_links')->first();
        if (!$table) {
            $table = Table::create([
                'is_system' => 1,
                'db_name' => 'table_field_links',
                'name' => 'Table Field Links',
                'user_id' => $user->id,
                'rows_per_page' => 100,
                'source' => 'scratch',
                'created_by' => $user->id,
                'created_on' => now(),
                'modified_by' => $user->id,
                'modified_on' => now(),
            ]);
        }

        //headers
        $this->create('name', 'Name', $table, $user, 'String', 1);
        $this->create('table_field_id', 'Placement Field', $table, $user, 'String', 1);
        $this->create('icon', 'Icon', $table, $user);
        $this->create('link_pos', 'Position', $table, $user);
        $this->create('link_type', 'Type', $table, $user, 'String', 1);
        $this->create('lnk_header', 'Header', $table, $user, 'Boolean');
        $this->create('_link_reverse', 'Reverse', $table, $user);

        $this->create('table_ref_condition_id', 'RC Name', $table, $user);
        $this->create('tooltip', 'Tooltip for the link:', $table, $user);
        $this->create('link_display', 'Open Style', $table, $user);
        $this->create('pop_width_px', 'Pop-up size,Width (px),Default', $table, $user, 'Integer');
        $this->create('pop_width_px_min', 'Pop-up size,Width (px),Min.', $table, $user, 'Integer');
        $this->create('pop_width_px_max', 'Pop-up size,Width (px),Max.', $table, $user, 'Integer');
        $this->create('pop_height', 'Pop-up size,Height (%),Default', $table, $user, 'Integer');
        $this->create('pop_height_min', 'Pop-up size,Height (%),Min.', $table, $user, 'Integer');
        $this->create('pop_height_max', 'Pop-up size,Height (%),Max.', $table, $user, 'Integer');
        $this->create('popup_can_table', 'Display Options,Table', $table, $user, 'Boolean');
        $this->create('popup_can_list', 'Display Options,List', $table, $user, 'Boolean');
        $this->create('popup_can_board', 'Display Options,Board', $table, $user, 'Boolean');
        $this->create('popup_display', 'Display Options,Default', $table, $user, 'String');
        $this->create('show_sum', 'Table Display,Show STATS', $table, $user, 'Boolean');
        $this->create('table_def_align', 'Table Display,Default Alignment', $table, $user);
        $this->create('table_fit_width', 'Table Display,Fit to Width', $table, $user, 'Boolean');
        $this->create('floating_action', 'Table Display,Floating Action', $table, $user, 'Boolean');
        $this->create('listing_field_id', 'List Display,Field', $table, $user);
        $this->create('listing_panel_status', 'List Display,Panel Status', $table, $user);
        $this->create('listing_header_wi', 'List Display,Header Width,px or %', $table, $user);
        $this->create('listing_rows_width', 'List Display,Panel Width,px or %', $table, $user);
        $this->create('listing_rows_min_width', 'List Display,Panel Width,Min (px)', $table, $user);
        $this->create('share_is_web', 'Is this for linked records?', $table, $user, 'Boolean');
        $this->create('share_record_link_id', 'Associated "Record" type link:', $table, $user);
        $this->create('web_prefix', 'URL Prefix', $table, $user);
        $addr = $this->create('address_field_id', 'Field saving Web Address(es)/URLs', $table, $user);
        $addr->update([
            'tooltip' => 'Separate items in the field by semi-colon, comma, space, or line breaker.',
        ]);
        $label4add = $this->create('multiple_web_label_fld_id', 'Field saving labels for Web Address(es)/URLs', $table, $user);
        $label4add->update([
            'tooltip' => 'Separate items in the field by semi-colon, comma, space, or line breaker.',
        ]);
        $this->create('hide_empty_web', 'Hide icon if the cell in a row of the selected field for "Web Address" is empty:', $table, $user, 'Boolean');
        $this->create('table_app_id', 'Linked Built-In App', $table, $user);
        $this->create('link_field_lat', ',Latitude', $table, $user);
        $this->create('link_field_lng', ',Longitude', $table, $user);
        $this->create('link_field_address', 'OR,Address', $table, $user);

        $this->create('lnk_dcr_permission_id', 'Permission for visiting linked records from,DCR', $table, $user, 'String');
        $this->create('lnk_srv_permission_id', 'Permission for visiting linked records from,SRV', $table, $user, 'String');
        $this->create('lnk_mrv_permission_id', 'Permission for visiting linked records from,MRV', $table, $user, 'String');
        $this->create('editability_rced_fields', 'Editability of referenced fields', $table, $user, 'Boolean');
        $this->create('always_available', 'Works w/o linked table sharing', $table, $user, 'Boolean');
        $this->create('can_row_add', 'Add records(AND selected permission for visiting)', $table, $user, 'Boolean');
        $this->create('can_row_delete', 'Delete records(AND selected permission for visiting)', $table, $user, 'Boolean');
        $this->create('add_record_limit', 'Max. number of records allowed to be added to the linked table through this link', $table, $user, 'Integer');
        $this->create('link_preview_fields', 'Info panel display on hovering over the link,Fields', $table, $user, 'String', 0, '', 'M-Select', 3);
        $this->create('link_preview_show_flds', 'Info panel display on hovering over the link,Show Field Name', $table, $user, 'Boolean');
        $this->create('email_addon_fields', 'Fields for emailing', $table, $user, 'String', 0, '', 'M-Select', 3);
        $this->create('history_fld_id', 'History for', $table, $user);
        $this->create('linked_report_id', 'Linked Report', $table, $user);

        $this->create('inline_in_vert_table', 'Inline display,Availability', $table, $user, 'Boolean');
        $this->create('inline_is_opened', 'Inline display,Open', $table, $user, 'Boolean');
        $this->create('inline_width', 'Inline display,Default Width', $table, $user, 'String');
        $this->create('inline_style', 'Inline display,Style', $table, $user, 'String');
        
        $this->create('inline_hide_tab', 'Inline display,Hide the tab', $table, $user, 'Boolean');
        $this->create('inline_hide_boundary', 'Inline display,No boundary line', $table, $user, 'Boolean');
        $this->create('inline_hide_padding', 'Inline display,No boundary padding', $table, $user, 'Boolean');
        $this->create('max_height_in_vert_table', 'Max height(px) of the in-line display panel', $table, $user);
        $this->create('avail_addons', 'Add-ons', $table, $user, 'String', 0, '', 'M-Select');
        $this->create('link_export_json_drill', 'Json Drilling', $table, $user, 'Boolean');
        $this->create('json_import_field_id', 'Field saving the file to be imported', $table, $user);
        $this->create('json_export_field_id', 'Field for saving exported file', $table, $user);
        $this->create('json_export_filename_table', 'Exported file name,Table', $table, $user, 'Boolean');
        $this->create('json_export_filename_link', 'Exported file name,Link', $table, $user, 'Boolean');
        $this->create('json_export_filename_fields', 'Exported file name,Fields', $table, $user, 'String', 0, '', 'M-Select');
        $this->create('json_export_filename_year', 'Exported file name,Year', $table, $user, 'Boolean');
        $this->create('json_export_filename_time', 'Exported file name,Time', $table, $user, 'Boolean');
        $this->create('json_auto_export', 'Auto Exporting', $table, $user, 'Boolean');
        $this->create('json_auto_remove_after_export', 'Remove linked data after exporting', $table, $user, 'Boolean');
        $this->create('link_export_drilled_fields', 'Exported Fields', $table, $user, 'String', 0, '', 'M-Select');

        $this->create('payment_method_fld_id', 'Payment method', $table, $user, 'String');
        $this->create('payment_paypal_keys_id', 'Payment connections,PayPal', $table, $user, 'String');
        $this->create('payment_stripe_keys_id', 'Payment connections,Stripe', $table, $user, 'String');
        $this->create('payment_description_fld_id', 'Services / Goods', $table, $user, 'Integer');
        $this->create('payment_amount_fld_id', 'Amount to pay', $table, $user, 'Integer');
        $this->create('payment_customer_fld_id', 'Payer / Customer', $table, $user, 'Integer');
        $this->create('payment_history_payee_fld_id', 'Payment confirmation:,Payee', $table, $user, 'Integer');
        $this->create('payment_history_amount_fld_id', 'Payment confirmation:,Amount paid', $table, $user, 'Integer');
        $this->create('payment_history_date_fld_id', 'Payment confirmation:,Date', $table, $user, 'Integer');

        $this->create('eri_parser_file_id', 'Field for saving ERI source file', $table, $user);
        $this->create('eri_writer_file_id', 'Field for saving ERI output file', $table, $user);
        $this->create('eri_parser_link_id', 'ERI Parser for settings', $table, $user);
        $this->create('eri_remove_prev_records', 'Remove existing records with the same values for inherited fields', $table, $user, 'Boolean');
        $this->create('eri_writer_filename_fields', 'ERI file name,Fields', $table, $user, 'String', 0, '', 'M-Select');
        $this->create('eri_writer_filename_year', 'ERI file name,YYMMDD', $table, $user, 'Boolean');
        $this->create('eri_writer_filename_time', 'ERI file name,HHMMSS', $table, $user, 'Boolean');

        $this->create('da_loading_type', 'Parsing tool', $table, $user);
        $this->create('da_loading_gemini_key_id', 'AI Model', $table, $user);
        $this->create('da_loading_image_field_id', 'Field saving the image', $table, $user);
        $this->create('da_loading_output_table_id', 'Table for storing parsing output', $table, $user);
        $this->create('da_loading_remove_prev_rec', 'Remove existing records with the same values for inherited fields', $table, $user, 'Boolean');

        $this->create('mto_dal_pdf_doc_field_id', 'Field saving the pdf', $table, $user);
        $this->create('mto_dal_pdf_output_table_id', 'Table for storing parsing output', $table, $user);
        $this->create('mto_dal_pdf_remove_prev_rec', 'Remove existing records with the same values for inherited fields', $table, $user, 'Boolean');

        $this->create('mto_geom_doc_field_id', 'Field saving the pdf', $table, $user);
        $this->create('mto_geom_output_table_id', 'Table for storing parsing output', $table, $user);
        $this->create('mto_geom_remove_prev_rec', 'Remove existing records with the same values for inherited fields', $table, $user, 'Boolean');

        $this->create('ai_extract_doc_field_id', 'Field saving the doc', $table, $user);
        $this->create('ai_extract_ai_id', 'AI APIs', $table, $user);
        $this->create('ai_extract_output_table_id', 'Table for storing output', $table, $user);
        $this->create('ai_extract_remove_prev_rec', 'Remove existing records with the same values for inherited fields', $table, $user, 'Boolean');

        $this->create('smart_select_source_field_id', 'Source Field', $table, $user);
        $this->create('smart_select_target_field_id', 'Target Field', $table, $user);
        $this->create('smart_select_data_range', 'Data Range', $table, $user);

        $this->create('created_by', 'Created By', $table, $user, 'User');
        $this->create('created_on', 'Created On', $table, $user, 'Date Time');
        $this->create('modified_by', 'Modified By', $table, $user, 'User');
        $this->create('modified_on', 'Modified On', $table, $user, 'Date Time');

        $this->update($table, 'lnk_dcr_permission_id', ['is_table_field_in_popup'=>1, 'is_start_table_popup'=>1]);
        $this->update($table, 'lnk_srv_permission_id', ['is_table_field_in_popup'=>1]);
        $this->update($table, 'lnk_mrv_permission_id', ['is_table_field_in_popup'=>1]);
        $this->update($table, 'pop_width_px', ['is_table_field_in_popup'=>1, 'is_start_table_popup'=>1]);
        $this->update($table, 'pop_width_px_min', ['is_table_field_in_popup'=>1]);
        $this->update($table, 'pop_width_px_max', ['is_table_field_in_popup'=>1]);
        $this->update($table, 'pop_height', ['is_table_field_in_popup'=>1]);
        $this->update($table, 'pop_height_min', ['is_table_field_in_popup'=>1]);
        $this->update($table, 'pop_height_max', ['is_table_field_in_popup'=>1]);
        $this->update($table, 'popup_can_table', ['is_table_field_in_popup'=>1, 'is_start_table_popup'=>1]);
        $this->update($table, 'popup_can_list', ['is_table_field_in_popup'=>1]);
        $this->update($table, 'popup_can_board', ['is_table_field_in_popup'=>1]);
        $this->update($table, 'popup_display', ['is_table_field_in_popup'=>1]);
        $this->update($table, 'json_export_filename_table', ['is_table_field_in_popup'=>1, 'is_start_table_popup'=>1]);
        $this->update($table, 'json_export_filename_link', ['is_table_field_in_popup'=>1]);
        $this->update($table, 'json_export_filename_fields', ['is_table_field_in_popup'=>1]);
        $this->update($table, 'json_export_filename_year', ['is_table_field_in_popup'=>1]);
        $this->update($table, 'json_export_filename_time', ['is_table_field_in_popup'=>1]);
        $this->update($table, 'show_sum', ['is_table_field_in_popup'=>1, 'is_start_table_popup'=>1]);
        $this->update($table, 'table_def_align', ['is_table_field_in_popup'=>1]);
        $this->update($table, 'table_fit_width', ['is_table_field_in_popup'=>1]);
        $this->update($table, 'floating_action', ['is_table_field_in_popup'=>1]);
        $this->update($table, 'link_preview_fields', ['is_table_field_in_popup'=>1, 'is_start_table_popup'=>1]);
        $this->update($table, 'link_preview_show_flds', ['is_table_field_in_popup'=>1]);
        $this->update($table, 'payment_paypal_keys_id', ['is_table_field_in_popup'=>1, 'is_start_table_popup'=>1]);
        $this->update($table, 'payment_stripe_keys_id', ['is_table_field_in_popup'=>1]);
        $this->update($table, 'payment_description_fld_id', ['is_table_field_in_popup'=>1, 'is_start_table_popup'=>1]);
        $this->update($table, 'payment_amount_fld_id', ['is_table_field_in_popup'=>1]);
        $this->update($table, 'payment_customer_fld_id', ['is_table_field_in_popup'=>1]);
        $this->update($table, 'payment_history_payee_fld_id', ['is_table_field_in_popup'=>1, 'is_start_table_popup'=>1]);
        $this->update($table, 'payment_history_amount_fld_id', ['is_table_field_in_popup'=>1]);
        $this->update($table, 'payment_history_date_fld_id', ['is_table_field_in_popup'=>1]);
        $this->update($table, 'inline_style', ['is_table_field_in_popup'=>1, 'is_start_table_popup'=>1]);
        $this->update($table, 'inline_in_vert_table', ['is_table_field_in_popup'=>1]);
        $this->update($table, 'inline_is_opened', ['is_table_field_in_popup'=>1]);
        $this->update($table, 'inline_width', ['is_table_field_in_popup'=>1]);
        $this->update($table, 'inline_hide_tab', ['is_table_field_in_popup'=>1]);
        $this->update($table, 'inline_hide_boundary', ['is_table_field_in_popup'=>1]);
        $this->update($table, 'inline_hide_padding', ['is_table_field_in_popup'=>1]);
        $this->update($table, 'listing_field_id', ['is_table_field_in_popup'=>1, 'is_start_table_popup'=>1]);
        $this->update($table, 'listing_panel_status', ['is_table_field_in_popup'=>1]);
        $this->update($table, 'listing_header_wi', ['is_table_field_in_popup'=>1]);
        $this->update($table, 'listing_rows_width', ['is_table_field_in_popup'=>1]);
        $this->update($table, 'listing_rows_min_width', ['is_table_field_in_popup'=>1]);
        $this->update($table, 'eri_writer_filename_fields', ['is_table_field_in_popup'=>1, 'is_start_table_popup'=>1]);
        $this->update($table, 'eri_writer_filename_year', ['is_table_field_in_popup'=>1]);
        $this->update($table, 'eri_writer_filename_time', ['is_table_field_in_popup'=>1]);

        //add right to Visitor
        $this->permissionsService->addSystemRights($table->id);





        $table = Table::where('db_name', 'table_field_link_params')->first();
        if (!$table) {
            $table = Table::create([
                'is_system' => 1,
                'db_name' => 'table_field_link_params',
                'name' => 'Table Field Link Params',
                'user_id' => $user->id,
                'rows_per_page' => 100,
                'source' => 'scratch',
                'created_by' => $user->id,
                'created_on' => now(),
                'modified_by' => $user->id,
                'modified_on' => now(),
            ]);
        }

        //headers
        $this->create('param', 'App,Parameter', $table, $user, 'String', 1);
        $this->create('value', 'Or,Value', $table, $user);
        $this->create('column_id', 'Or,Column', $table, $user);

        //add right to Visitor
        $this->permissionsService->addSystemRights($table->id);





        $table = Table::where('db_name', 'table_field_link_eri_tables')->first();
        if (!$table) {
            $table = Table::create([
                'is_system' => 1,
                'db_name' => 'table_field_link_eri_tables',
                'name' => 'Table Field Link Eri Tables',
                'user_id' => $user->id,
                'rows_per_page' => 100,
                'source' => 'scratch',
                'created_by' => $user->id,
                'created_on' => now(),
                'modified_by' => $user->id,
                'modified_on' => now(),
            ]);
        }

        //headers
        $this->create('eri_part_id', 'Part', $table, $user, 'String', 1);
        $this->create('eri_table_id', 'Table', $table, $user, 'String', 1);
        $this->create('is_active', 'Status', $table, $user, 'Boolean');

        //add right to Visitor
        $this->permissionsService->addSystemRights($table->id);





        $table = Table::where('db_name', 'table_field_link_eri_fields')->first();
        if (!$table) {
            $table = Table::create([
                'is_system' => 1,
                'db_name' => 'table_field_link_eri_fields',
                'name' => 'Table Field Link Eri Fields',
                'user_id' => $user->id,
                'rows_per_page' => 100,
                'source' => 'scratch',
                'created_by' => $user->id,
                'created_on' => now(),
                'modified_by' => $user->id,
                'modified_on' => now(),
            ]);
        }

        //headers
        $this->create('eri_variable', 'ERI Variable', $table, $user, 'String', 0);
        $this->create('_conversion_button', 'Conversion', $table, $user, 'String', 0);
        $this->create('eri_field_id', 'Field', $table, $user, 'String', 1);
        $this->create('eri_master_field_id', 'Inherit From', $table, $user, 'String', 0);
        $this->create('is_active', 'Status', $table, $user, 'Boolean');

        //add right to Visitor
        $this->permissionsService->addSystemRights($table->id);





        $table = Table::where('db_name', 'table_field_link_da_loadings')->first();
        if (!$table) {
            $table = Table::create([
                'is_system' => 1,
                'db_name' => 'table_field_link_da_loadings',
                'name' => 'Table Field Link DA Loadings',
                'user_id' => $user->id,
                'rows_per_page' => 100,
                'source' => 'scratch',
                'created_by' => $user->id,
                'created_on' => now(),
                'modified_by' => $user->id,
                'modified_on' => now(),
            ]);
        }

        //headers
        $this->create('column_key', 'Columns', $table, $user);
        $this->create('da_field_id', 'Field', $table, $user);
        $this->create('da_master_field_id', 'Inherit From', $table, $user);
        $this->create('is_active', 'Status', $table, $user, 'Boolean');

        //add right to Visitor
        $this->permissionsService->addSystemRights($table->id);





        $table = Table::where('db_name', 'table_field_link_eri_field_conversions')->first();
        if (!$table) {
            $table = Table::create([
                'is_system' => 1,
                'db_name' => 'table_field_link_eri_field_conversions',
                'name' => 'Table Field Link Eri Field Conversions',
                'user_id' => $user->id,
                'rows_per_page' => 100,
                'source' => 'scratch',
                'created_by' => $user->id,
                'created_on' => now(),
                'modified_by' => $user->id,
                'modified_on' => now(),
            ]);
        }

        //headers
        $this->create('eri_convers', 'ERI value', $table, $user, 'String', 1);
        $this->create('tablda_convers', 'Tablda value', $table, $user, 'String', 1);

        //add right to Visitor
        $this->permissionsService->addSystemRights($table->id);





        $table = Table::where('db_name', 'table_field_link_eri_parts')->first();
        if (!$table) {
            $table = Table::create([
                'is_system' => 1,
                'db_name' => 'table_field_link_eri_parts',
                'name' => 'Table Field Link Eri Parts',
                'user_id' => $user->id,
                'rows_per_page' => 100,
                'source' => 'scratch',
                'created_by' => $user->id,
                'created_on' => now(),
                'modified_by' => $user->id,
                'modified_on' => now(),
            ]);
        }

        //headers
        $this->create('part', 'Part', $table, $user, 'String', 1);
        $this->create('type', 'Type', $table, $user, 'String', 0);
        $this->create('section_q_identifier', 'Identifier 1', $table, $user, 'String', 0);
        $this->create('section_r_identifier', 'Identifier 2', $table, $user, 'String', 0);
        $this->create('_list_vars', 'Variables', $table, $user, 'String', 0);

        //add right to Visitor
        $this->permissionsService->addSystemRights($table->id);





        $table = Table::where('db_name', 'table_field_link_eri_part_variables')->first();
        if (!$table) {
            $table = Table::create([
                'is_system' => 1,
                'db_name' => 'table_field_link_eri_part_variables',
                'name' => 'Table Field Link Eri Part Variables',
                'user_id' => $user->id,
                'rows_per_page' => 100,
                'source' => 'scratch',
                'created_by' => $user->id,
                'created_on' => now(),
                'modified_by' => $user->id,
                'modified_on' => now(),
            ]);
        }

        //headers
        $this->create('variable_name', 'Variables', $table, $user, 'String', 1);
        $this->create('var_notes', 'Notes', $table, $user, 'String', 0);

        //add right to Visitor
        $this->permissionsService->addSystemRights($table->id);





        $table = Table::where('db_name', 'table_field_link_to_dcr')->first();
        if (!$table) {
            $table = Table::create([
                'is_system' => 1,
                'db_name' => 'table_field_link_to_dcr',
                'name' => 'Table Field Link To Dcr',
                'user_id' => $user->id,
                'rows_per_page' => 100,
                'source' => 'scratch',
                'created_by' => $user->id,
                'created_on' => now(),
                'modified_by' => $user->id,
                'modified_on' => now(),
            ]);
        }

        //headers
        $this->create('table_field_link_id', 'Item', $table, $user, 'String', 1);
        $this->create('status', 'Set Limit', $table, $user, 'Boolean');
        $this->create('add_limit', 'Number', $table, $user, 'Integer');

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
     * @param string $default
     * @param string $inp_type
     * @param int $max_rows
     */
    private function create($field, $name, $table, $user, $type = 'String', $required = 0, $default = '', $inp_type = 'Input', $max_rows = 1): TableField
    {
        $present = $table->_fields()->where('field', $field)->first();
        if (!$present) {
            $present = TableField::create([
                'table_id' => $table->id,
                'field' => $field,
                'name' => $name,
                'input_type' => $inp_type,
                'f_type' => $type,
                'f_default' => $default,
                'f_required' => $required,
                'verttb_cell_height' => $max_rows,
                'created_by' => $user->id,
                'created_on' => now(),
                'modified_by' => $user->id,
                'modified_on' => now()
            ]);
        } else {
            $present->name = $name;
            $present->input_type = $inp_type;
            $present->f_type = $type;
            $present->f_default = $default;
            $present->f_required = $required;
            $present->verttb_cell_height = $max_rows;
            $present->save();
        }
        return $present;
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
