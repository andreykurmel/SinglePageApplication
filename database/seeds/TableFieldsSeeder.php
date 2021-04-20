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
        //in Settings/Basics order
        $this->create('name','Name', $table, $user);
        $this->create('filter','Filter,Status', $table, $user, 'Boolean');
        $this->create('filter_type','Filter,Type', $table, $user);
        $this->create('popup_header','Shown in Form Header?', $table, $user, 'Boolean');
        $this->create('is_floating','Floating in List View?', $table, $user, 'Boolean');
        $this->create('header_background','Header BKGD Color', $table, $user, 'Color');
        $this->create('header_unit_ddl','Unit,DDL in Header?', $table, $user, 'Boolean');
        $this->create('unit_ddl_id','Unit,DDL', $table, $user);
        $this->create('unit','Unit,Original', $table, $user);
        $this->create('unit_display','Unit,Display', $table, $user);
        $this->create('input_type','Input Type', $table, $user);
        $this->create('f_formula','Input: Formula,Details', $table, $user, 'String', 0, '', 'Formula');
        $this->create('is_uniform_formula','Input: Formula,Uniform', $table, $user, 'Boolean');
        $this->create('ddl_id','Input: Select,DDL Name', $table, $user);
        $this->create('ddl_add_option','Input: Select,Add New', $table, $user, 'Boolean');
        $this->create('ddl_auto_fill','Input: Select,Auto Pop.', $table, $user, 'Boolean');
        $this->create('ddl_style','Input: Select,Listing Style in Form', $table, $user);
        $this->create('min_width','Width,Min', $table, $user);
        $this->create('max_width','Width,Max', $table, $user);
        $this->create('width','Width,Default', $table, $user);
        $this->create('f_required','Required', $table, $user, 'Boolean');
        $this->create('tooltip','Tooltip,Content', $table, $user);
        $this->create('tooltip_show','Tooltip,Show', $table, $user, 'Boolean');
        $this->create('show_history','History', $table, $user, 'Boolean');
        $this->create('placeholder_content','Placeholder,Content', $table, $user);
        $this->create('placeholder_only_form','Placeholder,Form Only', $table, $user, 'Boolean');
        //$this->create('notes','Notes', $table, $user);
        $this->create('verttb_he_auto','Pop-up Panel Text Row,Max. #,Auto', $table, $user, 'Boolean');
        $this->create('verttb_row_height','Pop-up Panel Text Row,Max. #,Input', $table, $user, 'Integer');
        $this->create('verttb_cell_height','Pop-up Panel Text Row,Height', $table, $user, 'Integer');
        $this->create('show_zeros','Show Zeros', $table, $user, 'Boolean');
        $this->create('col_align','Align', $table, $user, 'String');
        $this->create('default_stats','DFLT STATS for LKD RCDs', $table, $user, 'String');
        $this->create('is_show_on_board','Board Display, Shown', $table, $user, 'Boolean');
        $this->create('is_image_on_board','Board Display, Image', $table, $user, 'Radio');
        $this->create('is_search_autocomplete_display','Search Display', $table, $user, 'Boolean');
        $this->create('is_unique_collection','Unique', $table, $user, 'Boolean');
        $this->create('is_default_show_in_popup','Form,Default Visibility', $table, $user, 'Boolean');
        $this->create('is_start_table_popup','Form,Table Display,Start', $table, $user, 'Boolean');
        $this->create('is_table_field_in_popup','Form,Table Display,Status', $table, $user, 'Boolean');
        $this->create('is_dcr_section','Form,DCR Sections', $table, $user, 'Boolean');
        $this->create('is_topbot_in_popup','Form,Top Bot', $table, $user, 'Boolean');
        //in GSI module order
        $this->create('is_lat_field','Latitude', $table, $user, 'Radio');
        $this->create('is_long_field','Longitude', $table, $user, 'Radio');
        $this->create('info_box','Info Box,Info', $table, $user, 'Boolean');
        $this->create('is_info_header_field','Info Box,Header', $table, $user, 'Radio');
        $this->create('map_find_street_field','Street Address', $table, $user, 'Radio');
        $this->create('map_find_city_field','City', $table, $user, 'Radio');
        $this->create('map_find_state_field','State', $table, $user, 'Radio');
        $this->create('map_find_county_field','County', $table, $user, 'Radio');
        $this->create('map_find_zip_field','Zip Code', $table, $user, 'Radio');
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
        $this->create('is_calendar_start','Event Details,Start', $table, $user, 'Radio');
        $this->create('is_calendar_end','Event Details,End', $table, $user, 'Radio');
        $this->create('is_calendar_title','Event Details,Title', $table, $user, 'Radio');
        $this->create('is_calendar_cond_format','Event Details,CFs', $table, $user, 'Radio');
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

        $this->create('created_by','Created By', $table, $user, 'User');
        $this->create('created_on','Created On', $table, $user, 'Date Time');
        $this->create('modified_by','Modified By', $table, $user, 'User');
        $this->create('modified_on','Modified On', $table, $user, 'Date Time');

        //add right to Visitor
        $this->permissionsService->addSystemRights($table->id);



        //headers for 'DDL Items'

        $table = Table::where('db_name', '=', 'ddl_items')->first();

        $this->create('apply_target_row_group_id','Apply to RGRP', $table, $user);
        $this->create('option','Option', $table, $user);
        $this->create('description','Description', $table, $user);
        $this->create('image_path','Image', $table, $user);
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

        $this->create('apply_target_row_group_id','Apply to RGRP', $table, $user);
        $this->create('table_ref_condition_id','Reference,RC Name', $table, $user, 'String', 1);
        $this->create('target_field_id','Reference,Source Table Fields,Value', $table, $user, 'String');
        $this->create('show_field_id','Reference,Source Table Fields,Show', $table, $user, 'String');
        $this->create('sort_type','Reference,Sort', $table, $user, 'String', 0, 'asc');
        $this->create('image_field_id','Reference,Source Table Fields,Image', $table, $user);
        $this->create('notes','Notes', $table, $user);

        $this->create('created_by','Created By', $table, $user, 'User');
        $this->create('created_on','Created On', $table, $user, 'Date Time');
        $this->create('modified_by','Modified By', $table, $user, 'User');
        $this->create('modified_on','Modified On', $table, $user, 'Date Time');

        //add right to Visitor
        $this->permissionsService->addSystemRights($table->id);
        $this->viewRepository->addSys($table);



        // TABLE REF CONDITIONS

        $table = Table::where('db_name', '=', 'table_ref_conditions')->first();

        $this->create('name','Name', $table, $user, 'String', 1);
        $this->create('ref_table_id','Source Table', $table, $user, 'String', 0);
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
        $this->create('logic_operator','Condition,Logic', $table, $user);
        $this->create('item_type','Condition,Type', $table, $user, 'String', 1);
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

        $this->create('one_per_submission','One per Submission', $table, $user, 'Boolean');
        $this->create('dcr_record_status_id','Status', $table, $user);
        $this->create('dcr_record_url_field_id','Field saving record specific URL', $table, $user);
        $this->create('dcr_record_allow_unfinished','Allow saving unfinished form and Submitting later (enable “Save”).', $table, $user);
        $this->create('dcr_record_visibility_id','Visibility', $table, $user);
        $this->create('dcr_record_visibility_def','Default value upon submission / updating, Visibility', $table, $user);
        $this->create('dcr_record_editability_id','Editability', $table, $user);
        $this->create('dcr_record_editability_def','Editability', $table, $user);

        //submission
        $this->create('dcr_confirm_msg','Confirming Message', $table, $user);
        $this->create('dcr_email_field_id','Recipients', $table, $user);
        $this->create('dcr_email_field_static','And', $table, $user);
        $this->create('dcr_email_subject','Subject', $table, $user);
        $this->create('dcr_addressee_field_id','Addressee', $table, $user);
        $this->create('dcr_addressee_txt','Addressee', $table, $user);
        $this->create('dcr_email_message','Opening Message', $table, $user);
        $this->create('dcr_email_format','Format', $table, $user, 'String', 0, 'table');
        $this->create('dcr_email_col_group_id','Col. Group', $table, $user);
        //save
        $this->create('dcr_save_confirm_msg','Confirming Message', $table, $user);
        $this->create('dcr_save_email_field_id','Recipients', $table, $user);
        $this->create('dcr_save_email_field_static','And', $table, $user);
        $this->create('dcr_save_email_subject','Subject', $table, $user);
        $this->create('dcr_save_addressee_field_id','Addressee', $table, $user);
        $this->create('dcr_save_addressee_txt','Addressee', $table, $user);
        $this->create('dcr_save_email_message','Opening Message', $table, $user);
        $this->create('dcr_save_email_format','Format', $table, $user, 'String', 0, 'table');
        $this->create('dcr_save_email_col_group_id','Col. Group', $table, $user);
        //upade
        $this->create('dcr_upd_confirm_msg','Confirming Message', $table, $user);
        $this->create('dcr_upd_email_field_id','Recipients', $table, $user);
        $this->create('dcr_upd_email_field_static','And', $table, $user);
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



        // Folder Views
        $table = Table::where('db_name', '=', 'folder_views')->first();
        $this->create('name','Name', $table, $user, 'String', 1);
        $this->create('is_active','Public Access,Status', $table, $user, 'Boolean');
        $this->create('user_link','Public Access,Address', $table, $user);
        $this->create('hash','Public Access,EMBD', $table, $user);
        $this->create('is_locked','Public Access,Lock', $table, $user, 'Boolean');
        $this->create('lock_pass','Public Access,PWD', $table, $user);
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
        $this->create('show_table_data','Visibility,ListView', $table, $user, 'Boolean');
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



        // TABLE VIEWS
        $table = Table::where('db_name', '=', 'table_views')->first();

        $pdefa = \Vanguard\Services\Tablda\HelperService::viewPartsDef();
        $this->create('name','Name', $table, $user, 'String', 1);
        $this->create('parts_avail','Parts,Availability', $table, $user, 'String', 0, $pdefa, 'M-Select');
        $this->create('parts_default','Parts,Default', $table, $user, 'String', 0, '', 'S-Select');
        $this->create('row_group_id','Data Range,Row Group', $table, $user, 'Integer', 0, '', 'S-Select');
        $this->create('col_group_id','Data Range,Column Group', $table, $user, 'Integer', 0, '', 'S-Select');
        $this->create('access_permission_id','Permission', $table, $user, 'Integer', 0, '', 'S-Select');
        $this->create('side_top','Side Panels,Top', $table, $user);
        $this->create('side_left_menu','Side Panels,Left,Menu', $table, $user);
        $this->create('side_left_filter','Side Panels,Left,Filter', $table, $user);
        $this->create('side_right','Side Panels,Right', $table, $user);
        $this->create('can_sorting','Order,Row', $table, $user, 'Boolean');
        $this->create('column_order','Order,Column', $table, $user, 'Boolean');
        $this->create('is_active','Public Access,Status', $table, $user, 'Boolean');
        $this->create('_embd','Public Access,EMBD', $table, $user);
        $this->create('is_locked','Public Access,Lock', $table, $user, 'Boolean');
        $this->create('lock_pass','Public Access,PWD', $table, $user, 'String');
        $this->create('view_filtering','Filtering,Apply filtering', $table, $user, 'Boolean');
        //$this->create('user_link','Public Access,Address', $table, $user, 'String');

        $this->update($table, 'side_top', ['is_table_field_in_popup'=>1, 'is_start_table_popup'=>1]);
        $this->update($table, 'side_left_menu', ['is_table_field_in_popup'=>1]);
        $this->update($table, 'side_left_filter', ['is_table_field_in_popup'=>1]);
        $this->update($table, 'side_right', ['is_table_field_in_popup'=>1]);
        $this->update($table, 'can_sorting', ['is_table_field_in_popup'=>1, 'is_start_table_popup'=>1]);
        $this->update($table, 'column_order', ['is_table_field_in_popup'=>1]);
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
        $this->create('kanban_name','Name', $table, $user, 'String');
        $this->create('is_show','Show', $table, $user, 'Boolean');
        $this->create('is_header','Header', $table, $user, 'Radio');
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
        $present = $table->_fields->where('field', $field)->first();
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
        $present = $table->_fields->where('field', $field)->first();
        if ($present) {
            $present->update($params);
        }
    }
}
