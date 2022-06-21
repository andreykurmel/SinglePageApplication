<?php

use Illuminate\Database\Seeder;
use Vanguard\Models\Table\Table;
use Vanguard\Models\Table\TableField;
use Vanguard\User;

class TableFieldsSeeder extends Seeder
{
    private $permissionsService;
    private $viewRepository;

    /**
     * TableFieldsSeeder constructor.
     * @param \Vanguard\Services\Tablda\Permissions\TablePermissionService $permissionsService
     * @param \Vanguard\Repositories\Tablda\TableViewRepository $tableViewRepository
     */
    public function __construct(
        \Vanguard\Services\Tablda\Permissions\TablePermissionService $permissionsService,
        \Vanguard\Repositories\Tablda\TableViewRepository $tableViewRepository
    )
    {
        $this->permissionsService = $permissionsService;
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

        //headers for 'Table Headers'

        $table = Table::where('db_name', '=', 'table_fields')->first();
        $this->create('kanban_field_name','Name', $table, $user);
        $this->create('name','Name', $table, $user);
        //in Settings/Basics order
        //Input tab
        $this->create('input_type','Input Type', $table, $user);
        $this->create('f_formula','Input: Formula,Details', $table, $user, 'String', 0, '', 'Formula');
        $this->create('is_uniform_formula','Input: Formula,Uniform', $table, $user, 'Boolean');
        $this->create('ddl_id','Input: Select,DDL Name', $table, $user);
        $this->create('ddl_add_option','Input: Select,Add New', $table, $user, 'Boolean');
        $this->create('ddl_auto_fill','Input: Select,Auto Pop.', $table, $user, 'Boolean');
        $this->create('ddl_style','Input: Select,Listing Style in Form', $table, $user);
        $this->create('mirror_rc_id','Mirror Source,RC', $table, $user);
        $this->create('mirror_field_id','Mirror Source,Field', $table, $user);
        $this->create('mirror_part','Mirror Source,Part', $table, $user);
        $this->create('fetch_source_id','Fetch Source', $table, $user);
        //Standard tab
        $this->create('header_background','Header BKGD Color', $table, $user, 'Color');
        $this->create('header_unit_ddl','Unit,DDL in Header?', $table, $user, 'Boolean');
        $this->create('unit_ddl_id','Unit,DDL', $table, $user);
        $this->create('unit','Unit,Original', $table, $user);
        $this->create('f_required','Required', $table, $user, 'Boolean');
        $this->create('tooltip','Tooltip,Content', $table, $user);
        $this->create('tooltip_show','Tooltip,Show', $table, $user, 'Boolean');
        $this->create('placeholder_content','Placeholder,Content', $table, $user);
        $this->create('placeholder_only_form','Placeholder,Form Only', $table, $user, 'Boolean');
        $this->create('default_stats','DFLT STATS for LKD RCDs', $table, $user, 'String');
        $this->create('is_search_autocomplete_display','Search Display', $table, $user, 'Boolean');
        $this->create('is_unique_collection','Unique', $table, $user, 'Boolean');
        //Customizable tab
        $this->create('filter','Filter,Status', $table, $user, 'Boolean');
        $this->create('filter_type','Filter,Type', $table, $user);
        $this->create('popup_header','Shown in Form Header?', $table, $user, 'Boolean');
        $this->create('is_floating','Floating in Grid View?', $table, $user, 'Boolean');
        $this->create('unit_display','Unit,Display', $table, $user);
        $this->create('min_width','Width,Min', $table, $user);
        $this->create('max_width','Width,Max', $table, $user);
        $this->create('width','Width,Default', $table, $user);
        $this->create('show_history','History', $table, $user, 'Boolean');
        $this->create('show_zeros','Show Zeros', $table, $user, 'Boolean');
        $this->create('col_align','Align', $table, $user, 'String');
        //Pop-up tab
        $this->create('fld_display_name','Field Display,Name', $table, $user, 'Boolean');
        $this->create('fld_display_value','Field Display,Value', $table, $user, 'Boolean');
        $this->create('fld_display_border','Field Display,Border', $table, $user, 'Boolean');
        $this->create('is_topbot_in_popup','Field Display,Name/Val.,Top/Bot', $table, $user, 'Boolean');
        $this->create('verttb_he_auto','Text Row,Max. Qty,Auto', $table, $user, 'Boolean');
        $this->create('verttb_row_height','Text Row,Max. Qty,Input', $table, $user, 'Integer');
        $this->create('verttb_cell_height','Text Row,Height', $table, $user, 'Integer');
        $this->create('is_show_on_board','Board Display, Shown', $table, $user, 'Boolean');
        $this->create('is_image_on_board','Board Display, Image', $table, $user, 'Radio');
        $this->create('image_display_view','Board Display,Image Display,View', $table, $user, 'String');
        $this->create('image_display_fit','Board Display,Image Display,Fit', $table, $user, 'String');
        $this->create('is_default_show_in_popup','Form,Default Visibility', $table, $user, 'Boolean');
        $this->create('is_start_table_popup','Form,Table Display,Start', $table, $user, 'Boolean');
        $this->create('is_table_field_in_popup','Form,Table Display,Status', $table, $user, 'Boolean');
        $this->create('is_dcr_section','Form,DCR Sections', $table, $user, 'Boolean');

        //in GSI module order
        $this->create('is_lat_field','Position,Latitude', $table, $user, 'Radio');
        $this->create('is_long_field','Position,Longitude', $table, $user, 'Radio');
        $this->create('info_box','Info Box,Show', $table, $user, 'Boolean');
        $this->create('is_info_header_field','Info Box,Header', $table, $user, 'Radio');
        $this->create('map_find_street_field','Address,Street', $table, $user, 'Radio');
        $this->create('map_find_city_field','Address,City', $table, $user, 'Radio');
        $this->create('map_find_state_field','Address,State', $table, $user, 'Radio');
        $this->create('map_find_county_field','Address,County', $table, $user, 'Radio');
        $this->create('map_find_zip_field','Address,Zip Code', $table, $user, 'Radio');
        //gantt settings
        $this->create('is_gantt_main_group','Left Side Header,Grouping,Level 2', $table, $user, 'Radio');
        $this->create('is_gantt_parent_group','Left Side Header,Grouping,Level 1', $table, $user, 'Radio');
        $this->create('is_gantt_group','Left Side Header,Rows', $table, $user, 'Radio', 1);
        $this->create('is_gantt_left_header','Left Side Header, Additional', $table, $user, 'Boolean');
        $this->create('is_gantt_name','Bars,Items', $table, $user, 'Radio', 1);
        $this->create('is_gantt_parent','Bars,Dependency - Parent', $table, $user, 'Radio');
        $this->create('is_gantt_start','Bars,Start', $table, $user, 'Radio', 1);
        $this->create('is_gantt_end','Bars,End', $table, $user, 'Radio', 1);
        $this->create('is_gantt_progress','Bars,Progress', $table, $user, 'Radio');
        $this->create('is_gantt_color','Bars,Color', $table, $user, 'Radio');
        $this->create('is_gantt_tooltip','Bars,Tooltip', $table, $user, 'Boolean');
        $this->create('is_gantt_label_symbol','Bars,Label / Symbol', $table, $user, 'Radio');
        $this->create('is_gantt_milestone','Bars,Milestone', $table, $user, 'Radio');
        //calendar settings
        $this->create('is_calendar_start','Details,Start', $table, $user, 'Radio', 1);
        $this->create('is_calendar_end','Details,End', $table, $user, 'Radio');
        $this->create('is_calendar_title','Details,Title', $table, $user, 'Radio', 1);
        $this->create('is_calendar_cond_format','Details,CFs', $table, $user, 'Radio');
        //not showed
        $this->create('order','Order', $table, $user);
        $this->create('formula_symbol','Formula Symbol', $table, $user);
        //system
        $this->create('created_by','Created By', $table, $user, 'User');
        $this->create('created_on','Created On', $table, $user, 'Date Time');
        $this->create('modified_by','Modified By', $table, $user, 'User');
        $this->create('modified_on','Modified On', $table, $user, 'Date Time');

        //add right to Visitor
        $this->permissionsService->addSystemRights($table->id);
        $this->viewRepository->addSys($table);



        //headers for 'Tooltips'
        $table = Table::where('db_name', '=', 'table_fields__for_tooltips')->first();

        $this->create('table_id','Table', $table, $user, 'TableId');
        $this->create('name','Name', $table, $user);
        $this->create('tooltip','Tooltip,Content', $table, $user);
        $this->create('tooltip_show','Tooltip,Show', $table, $user, 'Boolean');

        //add right to Visitor
        $this->permissionsService->addSystemRights($table->id);
        $this->viewRepository->addSys($table);



        //headers for 'DDL'

        $table = Table::where('db_name', '=', 'ddl')->first();

        $this->create('name','Name', $table, $user, 'String', 1);
        $this->create('notes','Notes', $table, $user);
        $this->create('datas_sort','Sorting', $table, $user);

        $this->create('created_by','Created By', $table, $user, 'User');
        $this->create('created_on','Created On', $table, $user, 'Date Time');
        $this->create('modified_by','Modified By', $table, $user, 'User');
        $this->create('modified_on','Modified On', $table, $user, 'Date Time');

        //add right to Visitor
        $this->permissionsService->addSystemRights($table->id);



        //headers for 'DDL Items'

        $table = Table::where('db_name', '=', 'ddl_items')->first();

        $this->create('apply_target_row_group_id','Apply to RGrp', $table, $user);
        $this->create('option','Option,Value', $table, $user);
        $this->create('show_option','Option,Show', $table, $user);
        $this->create('opt_color','Color', $table, $user, 'Color');
        $this->create('image_path','Image', $table, $user, 'Attachment');
        $this->create('notes','Notes', $table, $user);

        $this->create('created_by','Created By', $table, $user, 'User');
        $this->create('created_on','Created On', $table, $user, 'Date Time');
        $this->create('modified_by','Modified By', $table, $user, 'User');
        $this->create('modified_on','Modified On', $table, $user, 'Date Time');

        //add right to Visitor
        $this->permissionsService->addSystemRights($table->id);
        $this->viewRepository->addSys($table);



        //headers for 'DDL References'

        $table = Table::where('db_name', '=', 'ddl_references')->first();

        $this->create('apply_target_row_group_id','Apply to RGrp', $table, $user);
        $this->create('table_ref_condition_id','Reference,RC Name', $table, $user, 'String', 1);
        $this->create('target_field_id','Reference,Source Table Fields,Value', $table, $user, 'String');
        $this->create('show_field_id','Reference,Source Table Fields,Show', $table, $user, 'String');
        $this->create('image_field_id','Reference,Source Table Fields,Image', $table, $user);
        $this->create('_ref_colors','Reference,Color', $table, $user);
        $this->create('notes','Notes', $table, $user);

        $this->create('created_by','Created By', $table, $user, 'User');
        $this->create('created_on','Created On', $table, $user, 'Date Time');
        $this->create('modified_by','Modified By', $table, $user, 'User');
        $this->create('modified_on','Modified On', $table, $user, 'Date Time');

        //add right to Visitor
        $this->permissionsService->addSystemRights($table->id);
        $this->viewRepository->addSys($table);



        //headers for 'DDL Reference Colors'

        $table = Table::where('db_name', '=', 'ddl_reference_colors')->first();

        $this->create('ref_value','Option', $table, $user);
        $this->create('color','Color', $table, $user, 'Color');

        //add right to Visitor
        $this->permissionsService->addSystemRights($table->id);
        $this->viewRepository->addSys($table);



        // TABLE REF CONDITIONS

        $table = Table::where('db_name', '=', 'table_ref_conditions')->first();

        $this->create('name','Name', $table, $user, 'String', 1);
        $this->create('ref_table_id','Source Table*', $table, $user, 'String', 0);
        //calc fields
        $this->create('_uses_rows', 'Uses,Row Groups', $table, $user, 'String', 0, '', 'M-Select');
        $this->create('_uses_links', 'Uses,"Record" Type Links', $table, $user, 'String', 0, '', 'M-Select');
        $this->create('_uses_ddls', 'Uses,DDLs', $table, $user, 'String', 0, '', 'M-Select');
        //
        $this->create('notes','Notes', $table, $user);

        $this->create('created_by','Created By', $table, $user, 'User');
        $this->create('created_on','Created On', $table, $user, 'Date Time');
        $this->create('modified_by','Modified By', $table, $user, 'User');
        $this->create('modified_on','Modified On', $table, $user, 'Date Time');

        //add right to Visitor
        $this->permissionsService->addSystemRights($table->id);
        $this->viewRepository->addSys($table);


        // TABLE REF CONDITIONS ITEMS
        $table = Table::where('db_name', '=', 'table_ref_condition_items')->first();

        $this->create('group_logic','Group,Logic', $table, $user, 'String');
        $this->create('group_clause','Group,Name', $table, $user, 'String', 1, 'A');
        $this->create('logic_operator','Conditions,Logic', $table, $user);
        $this->create('item_type','Conditions,Type', $table, $user, 'String', 1);
        $this->create('compared_field_id','Details,Source Field', $table, $user);
        $this->create('compared_operator','Details,COPR', $table, $user, 'String');
        $this->create('table_field_id','Details,Present Field', $table, $user, 'String');
        $this->create('compared_value','Details,Value', $table, $user);

        $this->create('created_by','Created By', $table, $user, 'User');
        $this->create('created_on','Created On', $table, $user, 'Date Time');
        $this->create('modified_by','Modified By', $table, $user, 'User');
        $this->create('modified_on','Modified On', $table, $user, 'Date Time');

        //add right to Visitor
        $this->permissionsService->addSystemRights($table->id);
        $this->viewRepository->addSys($table);



        // USER GROUPS
        $table = Table::where('db_name', '=', 'user_groups')->first();

        $this->create('name','Name', $table, $user, 'String', 1);

        //add right to Visitor
        $this->permissionsService->addSystemRights($table->id);
        $this->viewRepository->addSys($table);



        // User Group 2 Users
        $table = Table::where('db_name', '=', 'user_groups_2_table_permissions')->first();

        $this->create('user_group_id','Usergroup', $table, $user, 'String', 1);
        $this->create('table_permission_id','Permission', $table, $user, 'String', 1);
        $this->create('_dv','DV', $table, $user, 'String');
        $this->create('is_active','Status', $table, $user, 'Boolean');

        //add right to Visitor
        $this->permissionsService->addSystemRights($table->id);
        $this->viewRepository->addSys($table);



        // User Group 2 Users
        $table = Table::where('db_name', '=', 'user_groups_2_users')->first();

        $this->create('username','User\'s name', $table, $user, 'String', 1);
        $this->create('is_edit_added','Manager', $table, $user, 'Boolean');

        //add right to Visitor
        $this->permissionsService->addSystemRights($table->id);
        $this->viewRepository->addSys($table);



        // User Group Conditions
        $table = Table::where('db_name', '=', 'user_group_conditions')->first();

        $this->create('logic_operator','Logic', $table, $user);
        $this->create('user_field','Field', $table, $user, 'String', 1);
        $this->create('compared_operator','Condition', $table, $user, 'String', 1);
        $this->create('compared_value','Value', $table, $user, 'String', 1);

        //add right to Visitor
        $this->permissionsService->addSystemRights($table->id);
        $this->viewRepository->addSys($table);



        // Table permissions
        $table = Table::where('db_name', '=', 'table_permissions')->first();
        $this->create('notes','Note', $table, $user);
        $this->create('name','Name', $table, $user, 'String');
        $this->permissionsService->addSystemRights($table->id);
        $this->viewRepository->addSys($table);



        // Folder Views
        $table = Table::where('db_name', '=', 'folder_views')->first();
        $this->create('name','Name', $table, $user, 'String', 1);
        $this->create('is_active','Access,Status', $table, $user, 'Boolean');
        $this->create('user_link','Access,Address', $table, $user);
        $this->create('hash','Access,EMBD', $table, $user);
        $this->create('is_locked','Access,Lock', $table, $user, 'Boolean');
        $this->create('lock_pass','Access,PWD', $table, $user);
        $this->create('side_top','Side Panels,Top', $table, $user);
        $this->create('side_left_menu','Side Panels,Left,Menu', $table, $user);
        $this->create('side_left_filter','Side Panels,Left,Filter', $table, $user);
        $this->create('side_right','Side Panels,Right', $table, $user);
        $this->create('def_table_id','Default Table', $table, $user);

        $this->update($table, 'side_top', ['is_table_field_in_popup'=>1, 'is_start_table_popup'=>1]);
        $this->update($table, 'side_left_menu', ['is_table_field_in_popup'=>1]);
        $this->update($table, 'side_left_filter', ['is_table_field_in_popup'=>1]);
        $this->update($table, 'side_right', ['is_table_field_in_popup'=>1]);
        $this->update($table, 'is_active', ['is_table_field_in_popup'=>1, 'is_start_table_popup'=>1]);
        $this->update($table, 'hash', ['is_table_field_in_popup'=>1]);
        $this->update($table, 'is_locked', ['is_table_field_in_popup'=>1]);
        $this->update($table, 'lock_pass', ['is_table_field_in_popup'=>1]);

        $this->permissionsService->addSystemRights($table->id);
        $this->viewRepository->addSys($table);



        // TablePermissions 2 ColumnGroups
        $table = Table::where('db_name', '=', 'table_permissions_2_table_column_groups')->first();
        $this->create('table_column_group_id','Column Groups', $table, $user, 'String', 1);
        $this->create('view','View', $table, $user, 'Boolean');
        $this->create('edit','Edit', $table, $user, 'Boolean');
        //$this->create('delete','delete', $table, $user, 'Boolean');
        $this->permissionsService->addSystemRights($table->id);
        $this->viewRepository->addSys($table);



        // TablePermissions 2 RowGroups
        $table = Table::where('db_name', '=', 'table_permissions_2_table_row_groups')->first();
        $this->create('table_row_group_id','Row Groups', $table, $user, 'String', 1);
        $this->create('view','View', $table, $user, 'Boolean');
        $this->create('edit','Edit', $table, $user, 'Boolean');
        $this->create('delete','Delete', $table, $user, 'Boolean');
        $this->permissionsService->addSystemRights($table->id);
        $this->viewRepository->addSys($table);



        // TablePermissions 2 DefValues
        $table = Table::where('db_name', '=', 'table_permission_def_fields')->first();
        $this->create('default','Default', $table, $user);
        $this->permissionsService->addSystemRights($table->id);
        $this->viewRepository->addSys($table);



        // Table data_requests
        $table = Table::where('db_name', '=', 'table_data_requests')->first();
        $this->create('notes','Note', $table, $user);
        $this->create('dcr_sec_background_by','Background by', $table, $user, 'String', 0, '', 'S-Select');
        $this->create('dcr_sec_scroll_style','Style', $table, $user, 'String', 0, '', 'S-Select');
        $this->create('dcr_sec_line_top','Top', $table, $user, 'Boolean');
        $this->create('dcr_sec_line_bot','Bot', $table, $user, 'Boolean');
        $this->create('dcr_sec_line_color','Color', $table, $user, 'Color');
        $this->create('dcr_sec_line_thick','Thickness', $table, $user, 'Integer');
        $this->create('dcr_sec_bg_top','Top', $table, $user, 'Color', 0, '', 'Input', 'Section Background Color');
        $this->create('dcr_sec_bg_bot','Bot', $table, $user, 'Color');
        $this->create('dcr_sec_bg_img','BGI', $table, $user, 'String', 0, '', 'Input', 'Section Background Image');
        $this->create('dcr_sec_bg_img_fit','Fit', $table, $user);

        $this->create('dcr_title_background_by','Background by', $table, $user, 'String', 0, '', 'S-Select');
        $this->create('dcr_title','Title', $table, $user);
        $this->create('dcr_title_width','Width', $table, $user, 'String', 0, 600);
        $this->create('dcr_title_height','Height', $table, $user, 'String', 0, 100);
        $this->create('dcr_title_bg_color','BGC', $table, $user, 'Color', 0, '', 'Input', 'Title Background Color');
        $this->create('dcr_title_font_type','Font', $table, $user, 'String', 0, 'Arial');
        $this->create('dcr_title_font_size','Size', $table, $user, 'String', 0, 24);
        $this->create('dcr_title_font_color','Color', $table, $user, 'Color');
        $this->create('dcr_title_font_style','Style', $table, $user, 'String', 0, '', 'M-Select');
        $this->create('dcr_title_bg_img','BGI', $table, $user, 'String', 0, '', 'Input', 'Title Background Image');
        $this->create('dcr_title_bg_fit','Fit', $table, $user);

        $this->create('dcr_form_line_type','DIV Style', $table, $user);
        $this->create('dcr_form_line_top','Top', $table, $user, 'Boolean');
        $this->create('dcr_form_line_bot','Bot', $table, $user, 'Boolean');
        $this->create('dcr_form_line_thick','Thickness', $table, $user, 'Integer');
        $this->create('dcr_form_line_radius','Radius', $table, $user, 'Integer');
        $this->create('dcr_form_line_color','Color', $table, $user, 'Color');
        $this->create('dcr_form_bg_color','BGC', $table, $user, 'Color', 0, '', 'Input', 'Form Background Color');
        $this->create('dcr_form_transparency','Transparency', $table, $user, 'Integer');
        $this->create('dcr_form_message','Top Message', $table, $user);
        $this->create('dcr_form_message_font','Font', $table, $user);
        $this->create('dcr_form_message_size','Size', $table, $user);
        $this->create('dcr_form_message_color','Color', $table, $user);
        $this->create('dcr_form_message_style','Style', $table, $user, 'String', 0, '', 'M-Select');
        $this->create('dcr_form_width','Width', $table, $user, 'Integer');
        $this->create('dcr_form_shadow','Shadow', $table, $user, 'Boolean');
        $this->create('dcr_form_shadow_color','Color', $table, $user, 'Color');
        $this->create('dcr_form_shadow_dir','Style', $table, $user);
        $this->create('dcr_form_line_height','Row height', $table, $user);
        $this->create('dcr_form_font_size','Font size', $table, $user);

        $this->create('one_per_submission','One per Submission', $table, $user, 'Boolean');
        $this->create('dcr_record_status_id','Status', $table, $user);
        $this->create('dcr_record_url_field_id','Field saving record specific URL', $table, $user);
        $this->create('dcr_record_allow_unfinished','Allow saving unfinished form and Submitting later (enable “Save”).', $table, $user);
        $this->create('dcr_record_visibility_id','Visibility', $table, $user);
        $this->create('dcr_record_editability_id','Editability', $table, $user);
        $this->create('dcr_record_save_visibility_def','Default value upon saving', $table, $user);
        $this->create('dcr_record_save_editability_def','Editability', $table, $user);
        $this->create('dcr_record_visibility_def','Default value upon submitting', $table, $user);
        $this->create('dcr_record_editability_def','Editability', $table, $user);
        $this->create('stored_row_protection','Password protection for retrieving a saved or submitted form', $table, $user);
        $this->create('stored_row_pass_id','Fields saving password', $table, $user);

        //submission
        $this->create('dcr_confirm_msg','Confirming Message', $table, $user);
        $this->create('dcr_unique_msg','Uniqueness Check Message', $table, $user);
        $this->create('dcr_email_field_id','To', $table, $user);
        $this->create('dcr_email_field_static','and', $table, $user);
        $this->create('dcr_cc_email_field_id','Cc', $table, $user);
        $this->create('dcr_cc_email_field_static','and', $table, $user);
        $this->create('dcr_bcc_email_field_id','Bcc', $table, $user);
        $this->create('dcr_bcc_email_field_static','and', $table, $user);
        $this->create('dcr_email_subject','Subject', $table, $user);
        $this->create('dcr_addressee_field_id','Addressee', $table, $user);
        $this->create('dcr_addressee_txt','Addressee', $table, $user);
        $this->create('dcr_email_message','Opening Message', $table, $user);
        $this->create('dcr_email_format','Format', $table, $user, 'String', 0, 'table');
        $this->create('dcr_email_col_group_id','Col. Group', $table, $user);
        //save
        $this->create('dcr_save_confirm_msg','Confirming Message', $table, $user);
        $this->create('dcr_save_unique_msg','Uniqueness Check Message', $table, $user);
        $this->create('dcr_save_email_field_id','To', $table, $user);
        $this->create('dcr_save_email_field_static','and', $table, $user);
        $this->create('dcr_save_cc_email_field_id','Cc', $table, $user);
        $this->create('dcr_save_cc_email_field_static','and', $table, $user);
        $this->create('dcr_save_bcc_email_field_id','Bcc', $table, $user);
        $this->create('dcr_save_bcc_email_field_static','and', $table, $user);
        $this->create('dcr_save_email_subject','Subject', $table, $user);
        $this->create('dcr_save_addressee_field_id','Addressee', $table, $user);
        $this->create('dcr_save_addressee_txt','Addressee', $table, $user);
        $this->create('dcr_save_email_message','Opening Message', $table, $user);
        $this->create('dcr_save_email_format','Format', $table, $user, 'String', 0, 'table');
        $this->create('dcr_save_email_col_group_id','Col. Group', $table, $user);
        //update
        $this->create('dcr_upd_confirm_msg','Confirming Message', $table, $user);
        $this->create('dcr_upd_unique_msg','Uniqueness Check Message', $table, $user);
        $this->create('dcr_upd_email_field_id','To', $table, $user);
        $this->create('dcr_upd_email_field_static','and', $table, $user);
        $this->create('dcr_upd_cc_email_field_id','Cc', $table, $user);
        $this->create('dcr_upd_cc_email_field_static','and', $table, $user);
        $this->create('dcr_upd_bcc_email_field_id','Bcc', $table, $user);
        $this->create('dcr_upd_bcc_email_field_static','and', $table, $user);
        $this->create('dcr_upd_email_subject','Subject', $table, $user);
        $this->create('dcr_upd_addressee_field_id','Addressee', $table, $user);
        $this->create('dcr_upd_addressee_txt','Addressee', $table, $user);
        $this->create('dcr_upd_email_message','Opening Message', $table, $user);
        $this->create('dcr_upd_email_format','Format', $table, $user, 'String', 0, 'table');
        $this->create('dcr_upd_email_col_group_id','Col. Group', $table, $user);

        $this->create('name','Name', $table, $user, 'String');
        $this->create('pass','Password', $table, $user);
        $this->create('user_link','Address', $table, $user);
        //$this->create('row_request','Max Rows', $table, $user);
        $this->create('active','Status', $table, $user, 'Boolean');
        $this->create('_embed_dcr','EMBD', $table, $user);
        $this->permissionsService->addSystemRights($table->id);
        $this->viewRepository->addSys($table);



        // TablePermissions 2 ColumnGroups
        $table = Table::where('db_name', '=', 'table_data_requests_2_table_column_groups')->first();
        $this->create('table_column_group_id','Column Groups', $table, $user, 'String', 1);
        $this->create('view','View', $table, $user, 'Boolean');
        $this->create('edit','Edit', $table, $user, 'Boolean');
        $this->permissionsService->addSystemRights($table->id);
        $this->viewRepository->addSys($table);



        // TablePermissions 2 DefValues
        $table = Table::where('db_name', '=', 'table_data_requests_def_fields')->first();
        $this->create('default','Default', $table, $user);
        $this->permissionsService->addSystemRights($table->id);
        $this->viewRepository->addSys($table);



        // TablePermissions 2 DefValues
        $table = Table::where('db_name', '=', 'dcr_linked_tables')->first();
        $this->create('passed_ref_cond_id','Passing RefCondModel', $table, $user);
        $this->create('is_active','Status', $table, $user, 'Boolean');
        $this->create('linked_table_id','Linked Table,Name', $table, $user, 'String', true);
        $this->create('linked_permission_id','Linked Table,Permission', $table, $user, 'String', true);
        $this->create('header','Position,Header', $table, $user);
        $this->create('position','Position,Order', $table, $user);
        $this->create('position_field_id','Position,Field', $table, $user);
        $this->create('style','Style', $table, $user);

        $this->update($table, 'header', ['is_table_field_in_popup'=>1, 'is_start_table_popup'=>1]);
        $this->update($table, 'position', ['is_table_field_in_popup'=>1]);
        $this->update($table, 'position_field_id', ['is_table_field_in_popup'=>1]);
        $this->update($table, 'style', ['is_table_field_in_popup'=>1]);

        $this->permissionsService->addSystemRights($table->id);
        $this->viewRepository->addSys($table);



        // TABLE ROW GROUPS
        $table = Table::where('db_name', '=', 'table_row_groups')->first();

        $this->create('name','Name', $table, $user, 'String', 1);
        $this->create('row_ref_condition_id','Referencing Condition', $table, $user);
        $this->create('notes','Notes', $table, $user);

        $this->create('created_by','Created By', $table, $user, 'User');
        $this->create('created_on','Created On', $table, $user, 'Date Time');
        $this->create('modified_by','Modified By', $table, $user, 'User');
        $this->create('modified_on','Modified On', $table, $user, 'Date Time');

        //add right to Visitor
        $this->permissionsService->addSystemRights($table->id);
        $this->viewRepository->addSys($table);



        //Table Row Group Conditions
        $table = Table::where('db_name', '=', 'table_row_group_conditions')->first();

        $this->create('logic_operator','Logic', $table, $user);
        $this->create('table_field_id','Field', $table, $user, 'String', 1);
        $this->create('compared_operator','Condition', $table, $user, 'String', 1);
        $this->create('compared_value','Value', $table, $user, 'String');

        $this->create('created_by','Created By', $table, $user, 'User');
        $this->create('created_on','Created On', $table, $user, 'Date Time');
        $this->create('modified_by','Modified By', $table, $user, 'User');
        $this->create('modified_on','Modified On', $table, $user, 'Date Time');

        //add right to Visitor
        $this->permissionsService->addSystemRights($table->id);
        $this->viewRepository->addSys($table);



        //Table Row Group Conditions
        $table = Table::where('db_name', '=', 'table_row_group_regulars')->first();

        $this->create('field_value','Field Value', $table, $user);

        //add right to Visitor
        $this->permissionsService->addSystemRights($table->id);
        $this->viewRepository->addSys($table);



        // TABLE COLUMN GROUPS
        $table = Table::where('db_name', '=', 'table_column_groups')->first();

        $this->create('name','Name', $table, $user, 'String', 1);

        $this->create('created_by','Created By', $table, $user, 'User');
        $this->create('created_on','Created On', $table, $user, 'Date Time');
        $this->create('modified_by','Modified By', $table, $user, 'User');
        $this->create('modified_on','Modified On', $table, $user, 'Date Time');

        //add right to Visitor
        $this->permissionsService->addSystemRights($table->id);
        $this->viewRepository->addSys($table);



        // TableColumnGroups 2 TableFields
        $table = Table::where('db_name', '=', 'table_column_groups_2_table_fields')->first();

        $this->create('table_field_id','Field', $table, $user);
        $this->create('checked','Select', $table, $user);

        $this->create('created_by','Created By', $table, $user, 'User');
        $this->create('created_on','Created On', $table, $user, 'Date Time');
        $this->create('modified_by','Modified By', $table, $user, 'User');
        $this->create('modified_on','Modified On', $table, $user, 'Date Time');

        //add right to Visitor
        $this->permissionsService->addSystemRights($table->id);
        $this->viewRepository->addSys($table);



        // COND FORMATS
        $table = Table::where('db_name', '=', 'cond_formats')->first();

        $this->create('name','Name', $table, $user, 'String', 1);
        $this->create('table_row_group_id','Data Range,Row Groups', $table, $user, 'Integer', 0, '', 'S-Select');
        $this->create('table_column_group_id','Data Range,Column Groups', $table, $user, 'Integer', 0, '', 'S-Select');
        $this->create('bkgd_color','BKGD Color', $table, $user, 'Color');
        $this->create('font_size','Text Font,Size', $table, $user, 'Integer', 0, '', 'S-Select');
        $this->create('color','Text Font,Color', $table, $user, 'Color');
        $this->create('font','Text Font,Style', $table, $user, 'String', 0, '', 'M-Select');
        $this->create('activity','Editability', $table, $user, 'String', 0, '', 'S-Select');
        $this->create('show_table_data','Visibility,Grid View', $table, $user, 'Boolean');
        $this->create('show_form_data','Visibility,Form', $table, $user, 'Boolean');
        $this->create('status','Status', $table, $user, 'Boolean');

        $this->create('created_by','Created By', $table, $user, 'User');
        $this->create('created_on','Created On', $table, $user, 'Date Time');
        $this->create('modified_by','Modified By', $table, $user, 'User');
        $this->create('modified_on','Modified On', $table, $user, 'Date Time');

        //add right to Visitor
        $this->permissionsService->addSystemRights($table->id);
        $this->viewRepository->addSys($table);



        // TABLE ALERTS
        $table = Table::where('db_name', '=', 'table_alerts')->first();
        $this->create('name','Name', $table, $user, 'String', 1);
        $this->create('is_active','Status', $table, $user, 'Boolean');
        $this->create('description','Description', $table, $user);
        $this->update($table, 'description', ['show_zeros'=>0, 'col_align'=>'left']);
        //add right to Visitor
        $this->permissionsService->addSystemRights($table->id);
        $this->viewRepository->addSys($table);

        //Conditions
        $table = Table::where('db_name', '=', 'table_alert_conditions')->first();
        $this->create('is_active','Status', $table, $user, 'Boolean');
        $this->create('logic','Logic', $table, $user);
        $this->create('table_field_id','Field', $table, $user, 'String', 1);
        $this->create('condition','Condition', $table, $user);
        $this->create('new_value','New Value', $table, $user);
        //add right to Visitor
        $this->permissionsService->addSystemRights($table->id);
        $this->viewRepository->addSys($table);

        //ufv Table
        $table = Table::where('db_name', '=', 'alert_ufv_tables')->first();
        $this->create('is_active','Status', $table, $user, 'Boolean');
        $this->create('name','Name', $table, $user);
        $this->create('table_ref_cond_id','Records', $table, $user);
        //add right to Visitor
        $this->permissionsService->addSystemRights($table->id);
        $this->viewRepository->addSys($table);

        //ufv Fields
        $table = Table::where('db_name', '=', 'alert_ufv_table_fields')->first();
        $this->create('table_field_id','Field', $table, $user);
        $this->create('source','Source', $table, $user);
        $this->create('input','Input', $table, $user);
        $this->create('inherit_field_id','Inherit/Field', $table, $user);
        //add right to Visitor
        $this->permissionsService->addSystemRights($table->id);
        $this->viewRepository->addSys($table);

        //ANR Table
        $table = Table::where('db_name', '=', 'alert_anr_tables')->first();
        $this->create('need_approve','Status', $table, $user, 'Boolean');
        //
        $this->create('is_active','Status', $table, $user, 'Boolean');
        $this->create('name','Name', $table, $user);
        $this->create('table_id','Table', $table, $user);
        $this->create('qty','Qty', $table, $user);
        //for temp fields
        $this->create('temp_is_active','Status', $table, $user, 'Boolean');
        $this->create('temp_name','Name', $table, $user);
        $this->create('temp_table_id','Table', $table, $user);
        $this->create('temp_qty','Qty', $table, $user);
        //add right to Visitor
        $this->permissionsService->addSystemRights($table->id);
        $this->viewRepository->addSys($table);

        //ANR Fields
        $table = Table::where('db_name', '=', 'alert_anr_table_fields')->first();
        $this->create('table_field_id','Field', $table, $user);
        $this->create('source','Source', $table, $user);
        $this->create('input','Input', $table, $user);
        $this->create('inherit_field_id','Inherit/Field', $table, $user);
        //for temp fields
        $this->create('temp_table_field_id','Field', $table, $user);
        $this->create('temp_source','Source', $table, $user);
        $this->create('temp_input','Input', $table, $user);
        $this->create('temp_inherit_field_id','Inherit/Field', $table, $user);
        //add right to Visitor
        $this->permissionsService->addSystemRights($table->id);
        $this->viewRepository->addSys($table);



        // TABLE VIEWS
        $table = Table::where('db_name', '=', 'table_views')->first();

        $pdefa = \Vanguard\Services\Tablda\HelperService::viewPartsDef();
        $this->create('name','Name', $table, $user, 'String', 1);
        $this->create('parts_avail','Parts,Availability', $table, $user, 'String', 0, $pdefa, 'M-Select');
        $this->create('parts_default','Parts,Default', $table, $user, 'String', 0, '', 'S-Select');
        $this->create('row_group_id','Data Range,Row Group', $table, $user, 'Integer', 0, '', 'S-Select');
        $this->create('col_group_id','Data Range,Column Group', $table, $user, 'Integer', 0, '', 'S-Select');
        $this->create('can_show_srv','Data Range,S(RV)', $table, $user, 'Boolean');
        $this->create('access_permission_id','Permission', $table, $user, 'Integer', 0, '', 'S-Select');
        $this->create('side_top','Side Panels,Top', $table, $user);
        $this->create('side_left_menu','Side Panels,Left,Menu', $table, $user);
        $this->create('side_left_filter','Side Panels,Left,Filter', $table, $user);
        $this->create('side_right','Side Panels,Right', $table, $user);
        $this->create('can_sorting','Initial Loading,Order,Row', $table, $user, 'Boolean');
        $this->create('column_order','Initial Loading,Order,Column', $table, $user, 'Boolean');
        $this->create('can_filter','Initial Loading,Filters', $table, $user, 'Boolean');
        $this->create('can_hide','Initial Loading,Hide/Show XGrps', $table, $user, 'Boolean');
        $this->create('is_active','Access,Status', $table, $user, 'Boolean');
        $this->create('_embd','Access,EMBD', $table, $user);
        $this->create('is_locked','Access,Lock', $table, $user, 'Boolean');
        $this->create('lock_pass','Access,PWD', $table, $user, 'String');
        $this->create('view_filtering','Filtering,Apply filtering', $table, $user, 'Boolean');
        //$this->create('user_link','Access,Address', $table, $user, 'String');

        $this->update($table, 'row_group_id', ['is_table_field_in_popup'=>1, 'is_start_table_popup'=>1]);
        $this->update($table, 'col_group_id', ['is_table_field_in_popup'=>1]);
        $this->update($table, 'can_show_srv', ['is_table_field_in_popup'=>1]);
        $this->update($table, 'side_top', ['is_table_field_in_popup'=>1, 'is_start_table_popup'=>1]);
        $this->update($table, 'side_left_menu', ['is_table_field_in_popup'=>1]);
        $this->update($table, 'side_left_filter', ['is_table_field_in_popup'=>1]);
        $this->update($table, 'side_right', ['is_table_field_in_popup'=>1]);
        $this->update($table, 'can_sorting', ['is_table_field_in_popup'=>1, 'is_start_table_popup'=>1]);
        $this->update($table, 'column_order', ['is_table_field_in_popup'=>1]);
        $this->update($table, 'can_filter', ['is_table_field_in_popup'=>1]);
        $this->update($table, 'can_hide', ['is_table_field_in_popup'=>1]);
        $this->update($table, 'is_active', ['is_table_field_in_popup'=>1, 'is_start_table_popup'=>1]);
        $this->update($table, '_embd', ['is_table_field_in_popup'=>1]);
        $this->update($table, 'is_locked', ['is_table_field_in_popup'=>1]);
        $this->update($table, 'lock_pass', ['is_table_field_in_popup'=>1]);

        //add right to Visitor
        $this->permissionsService->addSystemRights($table->id);
        $this->viewRepository->addSys($table);



        // TABLE VIEW FILTERING
        $table = Table::where('db_name', '=', 'table_view_filtering')->first();

        $this->create('active','Status', $table, $user, 'Boolean');
        $this->create('field_id','Field', $table, $user, 'String', 1, '', 'S-Select');
        $this->create('criteria','Criteria', $table, $user, 'String', 1, '', 'S-Select');
        $this->create('input_only','Input Only?', $table, $user, 'Boolean');

        //add right to Visitor
        $this->permissionsService->addSystemRights($table->id);
        $this->viewRepository->addSys($table);




        // VIRTUAL TABLES !!!

        // Folder Permissions
        $table = Table::where('db_name', '=', 'folder_permissions')->first();
        $this->create('user_group_id','User Group', $table, $user, 'String', 1);
        $this->create('is_f_apps','As App', $table, $user, 'Boolean');
        $this->create('is_f_active','Status', $table, $user, 'Boolean');
        $this->permissionsService->addSystemRights($table->id);
        $this->viewRepository->addSys($table);


        // Kanban Settings
        $table = Table::where('db_name', '=', 'table_kanban_settings')->first();
        $this->create('kanban_name','Fields', $table, $user, 'String');
        $this->create('is_header','Card header', $table, $user, 'Radio');
        $this->create('table_show_name','Show Field,Name', $table, $user, 'Boolean');
        $this->create('table_field_id','Show Field,Value', $table, $user, 'Boolean');
        $this->create('cell_border','Field Border', $table, $user, 'Boolean');
        $this->create('picture_style','Image Display,Style', $table, $user, 'String');
        $this->create('picture_fit','Image Display,Fit', $table, $user, 'String');
        $this->permissionsService->addSystemRights($table->id);
        $this->viewRepository->addSys($table);


        // Incoming Links
        $table = Table::where('db_name', '=', 'incoming_links')->first();
        $this->create('incoming_allow','Allow', $table, $user, 'Boolean');
        $this->create('user_id','From,User', $table, $user, 'User');
        $this->create('table_name','From,Table', $table, $user, 'String');
        $this->create('ref_cond_name','RC', $table, $user, 'String');
        $this->create('use_category','Use,Category', $table, $user, 'String');
        $this->create('use_name','Use,Name', $table, $user, 'String');
        $this->permissionsService->addSystemRights($table->id);
        $this->viewRepository->addSys($table);


        // Emails Settings
        $table = Table::where('db_name', '=', 'email_settings')->first();
        $this->create('scenario','Scenatio/Uses', $table, $user, 'String');
        $this->create('sender_name','Sender,Name', $table, $user, 'String');
        $this->create('sender_email','Sender,Email', $table, $user, 'String');
        $this->create('to','To', $table, $user, 'String');
        $this->create('cc','Cc', $table, $user, 'String');
        $this->create('bcc','Bcc', $table, $user, 'String');
        $this->create('subject','Subject', $table, $user, 'String');
        $this->permissionsService->addSystemRights($table->id);
        $this->viewRepository->addSys($table);


        // Emails Settings
        $table = Table::where('db_name', '=', 'uploading_file_formats')->first();
        $this->create('category','Category', $table, $user, 'String');
        $this->create('format','Format', $table, $user, 'String');
        $this->permissionsService->addSystemRights($table->id);
        $this->viewRepository->addSys($table);
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
     * @param string $tooltip
     */
    private function create($field, $name, $table, $user, $type = 'String', $required = 0, $default = '', $inp_type = 'Input', $tooltip = '') {
        $present = $table->_fields()->where('field', $field)->first();
        if (!$present) {
            TableField::create([
                'table_id' => $table->id,
                'field' => $field,
                'name' => $name,
                'input_type' => $inp_type,
                'f_type' => $type,
                'f_default' => $default,
                'f_required' => $required,
                'tooltip' => $tooltip,
                'order' => TableField::where('table_id', '=', $table->id)->count()+1,
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
            $present->tooltip = $tooltip;
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
        $present = $table->_fields()->where('field', $field)->first();
        if ($present) {
            $present->update($params);
        }
    }
}
