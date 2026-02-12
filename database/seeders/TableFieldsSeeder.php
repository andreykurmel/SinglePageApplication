<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Vanguard\Models\Table\Table;
use Vanguard\Models\Table\TableField;
use Vanguard\Models\AppSetting;
use Vanguard\Models\FormulaHelper;
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
        $this->create('name','Name', $table, $user);
        //in Settings/Basics order
        //Input tab
        $this->create('input_type','Input Type', $table, $user);
        $this->create('f_default','Default', $table, $user);
        $this->create('f_formula','Input: Formula,Details', $table, $user, 'String', 0, '', 'Formula', '', 'left');
        $this->create('is_uniform_formula','Input: Formula,Uniform', $table, $user, 'Boolean');
        $this->create('ddl_id','Input: Select,DDL Name', $table, $user);
        $this->create('ddl_add_option','Input: Select,Add New', $table, $user, 'Boolean');
        $this->create('ddl_auto_fill','Input: Select,Auto Pop.', $table, $user, 'Boolean');
        $this->create('ddl_style','Input: Select,Listing Style in Form', $table, $user);
        $this->create('is_inherited_tree','Input: Select,Inherit From Source', $table, $user, 'Boolean');
        $this->create('mirror_rc_id','Mirroring,RC', $table, $user);
        $this->create('mirror_field_id','Mirroring,Field', $table, $user);
        $this->create('mirror_part','Mirroring,Part', $table, $user);
        $this->create('mirror_one_value','Mirroring,First Option', $table, $user, 'Boolean');
        $this->create('mirror_editable','Mirroring,Editing,Allow', $table, $user, 'Boolean');
        $this->create('mirror_edit_component','Mirroring,Editing,Input', $table, $user);
        $this->create('fetch_source_id','Fetch,Source Field', $table, $user);
        $this->create('fetch_by_row_cloud_id','Fetch,Access,By Row', $table, $user);
        $this->create('fetch_one_cloud_id','Fetch,Access,By Column', $table, $user);
        $this->create('fetch_uploading','Fetch,Uploading', $table, $user, 'Boolean');
        $this->create('has_copy_prefix','Copy,Prefix,Status', $table, $user, 'Boolean');
        $this->create('copy_prefix_value','Copy,Prefix,String', $table, $user);
        $this->create('has_copy_suffix','Copy,Suffix,Status', $table, $user, 'Boolean');
        $this->create('copy_suffix_value','Copy,Suffix,String', $table, $user);
        $this->create('has_datetime_suffix','Copy,Suffix,Date Time', $table, $user, 'Boolean');
        //Standard tab
        $this->create('header_background','Header BKGD Color', $table, $user, 'Color');
        $this->create('header_unit_ddl','Unit,DDL in Header?', $table, $user, 'Boolean');
        $this->create('unit_ddl_id','Unit,DDL', $table, $user);
        $this->create('unit','Unit,Original', $table, $user);
        $this->create('f_required','Required', $table, $user, 'Boolean');
        $this->create('tooltip','Tooltip,Content', $table, $user, 'String', 0, '', 'Input', '', 'left');
        $this->create('tooltip_show','Tooltip,Show', $table, $user, 'Boolean');
        $this->create('placeholder_content','Placeholder,Content', $table, $user, 'String', 0, '', 'Input', '', 'left');
        $this->create('placeholder_only_form','Placeholder,Form Only', $table, $user, 'Boolean');
        $this->create('default_stats','DFLT STATS for LKD RCDs', $table, $user, 'String', 0, '', 'M-Select');
        $this->create('is_search_autocomplete_display','Search Display', $table, $user, 'Boolean');
        $this->create('is_unique_collection','Unique', $table, $user, 'Boolean');
        $this->create('header_triangle','Settings', $table, $user, 'Boolean');
        $this->create('validation_rules','Validations', $table, $user, 'String');
        //Customizable tab
        $this->create('filter','Filter,Status', $table, $user, 'Boolean');
        $this->create('filter_type','Filter,Type', $table, $user);
        $this->create('filter_search','Filter,Search', $table, $user, 'Boolean');
        $this->create('popup_header','Shown in Form Header,Name', $table, $user, 'Boolean');
        $this->create('popup_header_val','Shown in Form Header,Value', $table, $user, 'Boolean');
        $this->create('is_floating','Floating in Grid View?', $table, $user, 'Boolean');
        $this->create('unit_display','Unit,Display', $table, $user);
        $this->create('min_width','Width,Min', $table, $user);
        $this->create('max_width','Width,Max', $table, $user);
        $this->create('width','Width,Default', $table, $user);
        $this->create('show_history','History', $table, $user, 'Boolean');
        $this->create('show_zeros','Show Zeros', $table, $user, 'Boolean');
        $this->create('col_align','Align', $table, $user, 'String');
        $this->create('image_fitting','Image Fitting', $table, $user, 'String');
        $this->create('fill_by_asterisk','Mask w/ Asterisks', $table, $user, 'Boolean');
        //Pop-up tab
        $this->create('fld_popup_shown','Field Display,Status', $table, $user, 'Boolean');
        $this->create('fld_display_name','Field Display,Name', $table, $user, 'Boolean');
        $this->create('fld_display_value','Field Display,Value', $table, $user, 'Boolean');
        $this->create('fld_display_border','Field Display,Border', $table, $user, 'Boolean');
        $this->create('fld_display_header_type','Field Display,Header', $table, $user, 'String');
        $this->create('is_topbot_in_popup','Field Display,Name/Val.,Top/Bot', $table, $user, 'Boolean');
        $this->create('verttb_he_auto','Text Row,Max. Qty,Auto', $table, $user, 'Boolean');
        $this->create('verttb_row_height','Text Row,Max. Qty,Input', $table, $user, 'Integer');
        $this->create('verttb_cell_height','Text Row,Height', $table, $user, 'Integer');
        $this->create('is_show_on_board','Board Display, Shown', $table, $user, 'Boolean');
        $this->create('is_default_show_in_popup','Form,Default Visibility', $table, $user, 'Boolean');
        $this->create('width_of_table_popup','Form,Table Display,Width', $table, $user, 'String');
        $this->create('is_start_table_popup','Form,Table Display,Start', $table, $user, 'Boolean');
        $this->create('is_table_field_in_popup','Form,Table Display,Status', $table, $user, 'Boolean');
        $this->create('is_hdr_lvl_one_row','Form,Table Display,HLiOR', $table, $user, 'Boolean');
        $this->create('form_row_spacing','Form,Row Spacing (after)', $table, $user, 'Integer');
        $this->create('pop_tab_name','Tabs,Name', $table, $user, 'String');
        $this->create('pop_tab_order','Tabs,Order', $table, $user, 'Integer');
        $this->create('section_header','Added Section (before),Header', $table, $user, 'String');
        $this->create('section_font','Added Section (before),Font Style', $table, $user, 'String', 0, '', 'M-Select');
        $this->create('section_size','Added Section (before),Size (pt)', $table, $user, 'Integer');
        $this->create('section_align_h','Added Section (before),AlignH', $table, $user, 'String', 0, '', 'S-Select');
        $this->create('section_align_v','Added Section (before),AlignV', $table, $user, 'String', 0, '', 'S-Select');
        $this->create('section_height','Added Section (before),Height (px)', $table, $user, 'Integer');
        //Others tab
        $this->create('markerjs_annotations','Marker.js,Annotation', $table, $user, 'Boolean');
        $this->create('markerjs_cropro','Marker.js,CroPro', $table, $user, 'Boolean');
        $this->create('twilio_google_acc_id','Google,Email', $table, $user);
        $this->create('twilio_sendgrid_acc_id','Twilio,Email (Sendgrid)', $table, $user);
        $this->create('twilio_sms_acc_id','Twilio,SMS', $table, $user);
        $this->create('twilio_voice_acc_id','Twilio,Voice', $table, $user);
        $this->create('twilio_sender_name','Sender,Name', $table, $user);
        $this->create('twilio_sender_email','Sender,Email', $table, $user);

        //in GSI module order
        $this->create('is_lat_field','Position,Latitude', $table, $user, 'Radio');
        $this->create('is_long_field','Position,Longitude', $table, $user, 'Radio');
        $this->create('info_box','Info Panel,Show', $table, $user, 'Boolean');
        $this->create('is_info_header_field','Info Panel,Header,Name', $table, $user, 'Boolean');
        $this->create('is_info_header_value','Info Panel,Header,Value', $table, $user, 'Boolean');
        $this->create('map_find_street_field','Address,Street', $table, $user, 'Radio');
        $this->create('map_find_city_field','Address,City', $table, $user, 'Radio');
        $this->create('map_find_state_field','Address,State', $table, $user, 'Radio');
        $this->create('map_find_county_field','Address,County', $table, $user, 'Radio');
        $this->create('map_find_zip_field','Address,Zip Code', $table, $user, 'Radio');
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

        $formulaExamples = [
            ['key' => 'formula_example_','val' => '123 + 234<br>{Field} ~ "string"<br>Pow({Field}, 2)<br>If({Field} > 10,"Completed","In progress")'],
            ['key' => 'formula_example_sqrt','val' => '2 + Sqrt({Field})'],
            ['key' => 'formula_example_pow','val' => 'Pow({Field}, 2) - 12'],
            ['key' => 'formula_example_yrday','val' => 'YrDay({Field})'],
            ['key' => 'formula_example_date','val' => 'Date({Field},"Y-m-d")'],
            ['key' => 'formula_example_time','val' => 'Time({Field},"H:i:s")'],
            ['key' => 'formula_example_moday','val' => 'MoDay({Field})'],
            ['key' => 'formula_example_wkday','val' => 'WkDay({Field}, "Number")'],
            ['key' => 'formula_example_week','val' => 'Week({Field})'],
            ['key' => 'formula_example_month','val' => 'Month({Field}, "Name")'],
            ['key' => 'formula_example_year','val' => 'Year({Field})'],
            ['key' => 'formula_example_today','val' => 'Today("US/Eastern","Y-m-d")'],
            ['key' => 'formula_example_tomorrow','val' => 'Tomorrow("US/Eastern","Y-m-d")'],
            ['key' => 'formula_example_yesterday','val' => 'Yesterday("US/Eastern","Y-m-d")'],
            ['key' => 'formula_example_timechange','val' => 'TimeChange("Now", "Add", "2 days")'],
            ['key' => 'formula_example_duration','val' => 'Duration({Field1}, {Field2})'],
            ['key' => 'formula_example_andx','val' => 'ANDX({Field1} > 10, {Field2} == "condition")'],
            ['key' => 'formula_example_orx','val' => 'ORX({Field1} > 10, {Field2} == "condition")'],
            ['key' => 'formula_example_if','val' => 'If({Field1} > 10,"Completed","In progress")'],
            ['key' => 'formula_example_switch','val' => 'Switch({Field}, ["a", "b", "c"], ["alpha", "beta", "gamma"], "not found")'],
            ['key' => 'formula_example_isempty','val' => 'Isempty({Field}, "Empty", "Filled")'],
            ['key' => 'formula_example_asum','val' => 'ASUM([{Field1}, {Field2}, {Field3}])'],
            ['key' => 'formula_example_amin','val' => 'AMIN([{Field1}, {Field2}, {Field3}])'],
            ['key' => 'formula_example_amax','val' => 'AMAX([{Field1}, {Field2}, {Field3}])'],
            ['key' => 'formula_example_amean','val' => 'AMEAN([{Field1}, {Field2}, {Field3}])'],
            ['key' => 'formula_example_aavg','val' => 'AAVG([{Field1}, {Field2}, {Field3}])'],
            ['key' => 'formula_example_avar','val' => 'AVAR([{Field1}, {Field2}, {Field3}])'],
            ['key' => 'formula_example_astd','val' => 'ASTD([{Field1}, {Field2}, {Field3}])'],
            ['key' => 'formula_example_ddloption','val' => 'DDLOption ({Field}, "Value")'],
            ['key' => 'formula_example_ai_create','val' => 'AI_CREATE ("Something what should be done by ChatGPT")'],
            ['key' => 'formula_example_ai_extract','val' => 'AI_EXTRACT ({PromptField}, "DocField")'],

            ['key' => 'formula_example_count','val' => 'COUNT("Ref Cond Name", {Field})'],
            ['key' => 'formula_example_countunique','val' => 'COUNTUNIQUE("Ref Cond Name", {Field})'],
            ['key' => 'formula_example_sum','val' => 'SUM("Ref Cond Name", {Field})'],
            ['key' => 'formula_example_min','val' => 'MIN("Ref Cond Name", {Field})'],
            ['key' => 'formula_example_max','val' => 'MAX("Ref Cond Name", {Field})'],
            ['key' => 'formula_example_mean','val' => 'MEAN("Ref Cond Name", {Field})'],
            ['key' => 'formula_example_avg','val' => 'AVG("Ref Cond Name", {Field})'],
            ['key' => 'formula_example_var','val' => 'VAR("Ref Cond Name", {Field})'],
            ['key' => 'formula_example_std','val' => 'STD("Ref Cond Name", {Field})'],
        ];
        foreach ($formulaExamples as $arr) {
            AppSetting::updateOrCreate(['key' => $arr['key']], $arr);
            FormulaHelper::updateOrCreate(['formula' => $arr['key']], [
                'formula' => $arr['key'],
                'notes' => $arr['val'],
            ]);
        }



        //headers for 'Tooltips'
        $table = Table::where('db_name', '=', 'table_fields__for_tooltips')->first();

        $this->create('table_id','Table', $table, $user, 'TableId');
        $this->create('name','Name', $table, $user);
        $this->create('tooltip','Tooltip,Content', $table, $user);
        $this->create('tooltip_show','Tooltip,Show', $table, $user, 'Boolean');

        //add right to Visitor
        $this->permissionsService->addSystemRights($table->id);
        $this->viewRepository->addSys($table);



        //headers for 'GANTT SETTINGS'
        $table = Table::where('db_name', '=', 'table_gantts')->first();

        $this->create('name','Name', $table, $user, 'String', 1);
        $this->create('gantt_data_range','Data Range', $table, $user, 'DataRange', 1);
        $this->create('description','Description', $table, $user, 'String');
        $this->create('gantt_active','Status', $table, $user, 'Boolean');

        //add right to Visitor
        $this->permissionsService->addSystemRights($table->id);
        $this->viewRepository->addSys($table);



        //headers for 'MAP SETTINGS'
        $table = Table::where('db_name', '=', 'table_maps')->first();

        $this->create('name','Name', $table, $user, 'String', 1);
        $this->create('map_data_range','Data Range', $table, $user, 'DataRange', 1);
        $this->create('description','Description', $table, $user, 'String');
        $this->create('map_active','Status', $table, $user, 'Boolean');

        //add right to Visitor
        $this->permissionsService->addSystemRights($table->id);
        $this->viewRepository->addSys($table);



        //headers for 'GANTT SETTINGS'
        $table = Table::where('db_name', '=', 'table_gantt_settings')->first();

        $this->create('table_field_id','Field', $table, $user);

        $this->create('is_gantt_main_group','Left Side Header,Grouping,Level 2', $table, $user, 'Radio');
        $this->create('is_gantt_parent_group','Left Side Header,Grouping,Level 1', $table, $user, 'Radio');
        $this->create('is_gantt_group','Left Side Header,Rows', $table, $user, 'Radio', 1);
        $this->create('gantt_left_header','Left Side Header, Additional', $table, $user, 'Boolean');

        $this->create('is_gantt_name','Bars,Items', $table, $user, 'Radio', 1);
        $this->create('is_gantt_parent','Bars,Dependency - Parent', $table, $user, 'Radio');
        $this->create('is_gantt_start','Bars,Start', $table, $user, 'Radio', 1);
        $this->create('is_gantt_end','Bars,End', $table, $user, 'Radio', 1);
        $this->create('is_gantt_progress','Bars,Progress', $table, $user, 'Radio');
        $this->create('is_gantt_color','Bars,Color', $table, $user, 'Radio');
        $this->create('gantt_tooltip','Bars,Tooltip', $table, $user, 'Boolean');
        $this->create('is_gantt_label_symbol','Bars,Label / Symbol', $table, $user, 'Radio');
        $this->create('is_gantt_milestone','Bars,Milestone', $table, $user, 'Radio');

        //add right to Visitor
        $this->permissionsService->addSystemRights($table->id);
        $this->viewRepository->addSys($table);



        //headers for 'CALENDAR SETTINGS'
        $table = Table::where('db_name', '=', 'table_calendars')->first();

        $this->create('name','Name', $table, $user, 'String', 1);
        $this->create('calendar_data_range','Data Range', $table, $user, 'DataRange', 1);
        $this->create('description','Description', $table, $user, 'String');
        $this->create('calendar_active','Status', $table, $user, 'Boolean');

        //add right to Visitor
        $this->permissionsService->addSystemRights($table->id);
        $this->viewRepository->addSys($table);



        //headers for 'DDL'

        $table = Table::where('db_name', '=', 'ddl')->first();

        $this->create('name','Name', $table, $user, 'String', 1);
        $this->create('datas_sort','Sorting,Order', $table, $user, 'String', 0);
        $this->create('keep_sort_order','Sorting,Keep Part Order', $table, $user, 'Boolean');
        $this->create('ignore_lettercase','Sorting,Disregard Letter Case', $table, $user, 'Boolean');
        $this->create('owner_shared','Share', $table, $user, 'Boolean', 0, '', 'Input', 'Share DDL across your tables');
        $this->create('admin_public','Public', $table, $user, 'Boolean', 0, '', 'Input', 'Share DDL to all users');
        $this->create('_unit_uses', 'Uses for Field(s),Unit', $table, $user, 'String', 0, '', 'M-Select');
        $this->create('_input_uses', 'Uses for Field(s),Input', $table, $user, 'String', 0, '', 'M-Select');
        $this->create('notes','Description', $table, $user);

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
        $this->create('max_selections','Allowed Number of Selections', $table, $user);
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
        $this->create('_ref_colors','Reference,Image / Color', $table, $user);
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
        $this->create('image_ref_path','Image', $table, $user, 'Attachment');
        $this->create('color','Color', $table, $user, 'Color');
        $this->create('max_selections','Allowed Number of Selections', $table, $user);

        //add right to Visitor
        $this->permissionsService->addSystemRights($table->id);
        $this->viewRepository->addSys($table);



        // TABLE REF CONDITIONS

        $table = Table::where('db_name', '=', 'table_ref_conditions')->first();

        $this->create('name','Name', $table, $user, 'String', 1);
        $this->create('ref_table_id','Source Table*', $table, $user, 'String', 0);
        $this->create('rc_static','Static', $table, $user, 'Boolean', 0, '', 'Input', 'Only "Static" RC can be used in shared DDL');
        //calc fields
        $this->create('_create_reverse', 'Reverse', $table, $user);
        $this->create('_out_uses', 'Uses', $table, $user);
        $this->create('_uses_rows', 'Row Groups', $table, $user, 'String', 0, '', 'M-Select');
        $this->create('_uses_links', '"Record" Type Links', $table, $user, 'String', 0, '', 'M-Select');
        $this->create('_uses_ddls', 'DDLs', $table, $user, 'String', 0, '', 'M-Select');
        $this->create('_uses_mirrors', 'Mirroring', $table, $user, 'String', 0, '', 'M-Select');
        $this->create('_uses_formulas', 'Formula', $table, $user, 'String', 0, '', 'M-Select');
        $this->create('_has_inheritance', 'Inheriting', $table, $user, 'Boolean');
        //
        $this->update($table, '_uses_rows', ['is_topbot_in_popup'=>1]);
        $this->update($table, '_uses_links', ['is_topbot_in_popup'=>1]);
        $this->update($table, '_uses_ddls', ['is_topbot_in_popup'=>1]);
        $this->update($table, '_uses_mirrors', ['is_topbot_in_popup'=>1]);
        $this->update($table, '_uses_formulas', ['is_topbot_in_popup'=>1]);
        $this->update($table, '_has_inheritance', ['is_topbot_in_popup'=>1]);
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

        $this->create('group_logic','Group,Logic', $table, $user);
        $this->create('group_clause','Group,Name', $table, $user, 'String', 1, 'A');
        $this->create('logic_operator','Conditions,Logic', $table, $user);
        $this->create('item_type','Conditions,Type', $table, $user, 'String', 1);
        $this->create('compared_field_id','Details,Source Field,Name', $table, $user);
        $this->create('compared_part','Details,Source Field,Part', $table, $user);
        $this->create('compared_operator','Details,COPR', $table, $user);
        $this->create('table_field_id','Details,Present Field,Name', $table, $user);
        $this->create('field_part','Details,Present Field,Part', $table, $user);
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



        // USER SUB GROUPS
        $table = Table::where('db_name', '=', 'user_group_subgroups')->first();

        $this->create('subgroup_id','Subgroup', $table, $user, 'S-Select', 1);

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
        $this->create('user_link','Access', $table, $user);
        $this->create('is_active','Status', $table, $user, 'Boolean');
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
        $this->create('dcr_sec_background_by','Background by', $table, $user, 'String', 0, '', 'S-Select');
        $this->create('dcr_sec_scroll_style','Style', $table, $user, 'String', 0, '', 'S-Select');
        $this->create('dcr_sec_slide_top_header','Title, Top Message and Notes as the First Slide', $table, $user, 'Boolean');
        $this->create('dcr_sec_slide_progresbar','Progress Bar', $table, $user, 'Boolean');
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
        $this->create('dcr_signature_txt','Signature', $table, $user);
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
        $this->create('dcr_save_signature_txt','Signature', $table, $user);
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
        $this->create('dcr_upd_signature_txt','Signature', $table, $user);
        $this->create('dcr_upd_email_message','Opening Message', $table, $user);
        $this->create('dcr_upd_email_format','Format', $table, $user, 'String', 0, 'table');
        $this->create('dcr_upd_email_col_group_id','Col. Group', $table, $user);

        $this->create('name','Name', $table, $user, 'String', 0, '', 'Input', '', 'left');
        $this->create('custom_url','Custom URL', $table, $user, 'String', 0, '', 'Input', '', 'left');
        $this->create('description','Description', $table, $user, 'String', 0, '', 'Input', '', 'left');
        $this->create('pass','Password', $table, $user, 'String', 0, '', 'Input', '', 'left');
        $this->create('one_per_submission','Max. Number of Records per Submission', $table, $user, 'Integer', 0, '1', 'Input', '', 'center');
        $this->create('active','Status', $table, $user, 'Boolean', 0, '', 'Input', '', 'center');
        $this->create('permission_dcr_id','Permission', $table, $user, 'String', 0, '', 'Input', '', 'left');
        //$this->create('row_request','Max Rows', $table, $user);
        $this->permissionsService->addSystemRights($table->id);
        $this->viewRepository->addSys($table);



        // TablePermissions 2 ColumnGroups
        $table = Table::where('db_name', '=', 'table_data_requests_2_table_column_groups')->first();
        $this->create('table_column_group_id','Column Groups', $table, $user, 'String', 1, '', 'Input', '', 'left');
        $this->create('view','View', $table, $user, 'Boolean', 0, '', 'Input', '', 'center');
        $this->create('edit','Edit', $table, $user, 'Boolean', 0, '', 'Input', '', 'center');
        $this->permissionsService->addSystemRights($table->id);
        $this->viewRepository->addSys($table);



        // TablePermissions 2 DefValues
        $table = Table::where('db_name', '=', 'table_data_requests_def_fields')->first();
        $this->create('default','Default', $table, $user, 'String', 0, '', 'Input', '', 'center');
        $this->permissionsService->addSystemRights($table->id);
        $this->viewRepository->addSys($table);



        // Dcr Linked Tables
        $table = Table::where('db_name', '=', 'dcr_linked_tables')->first();
        $this->create('name','Name', $table, $user, 'String', 1, '', 'Input', '', 'left');
        $this->create('passed_ref_cond_id','RC', $table, $user, 'String', 1, '', 'Input', '', 'left');
        $this->create('linked_table_id','Table', $table, $user, 'String', 0, '', 'Input', '', 'left');
        $this->create('linked_permission_id','Permission', $table, $user, 'String', 1, '', 'Input', '', 'left');
        $this->create('is_active','Status', $table, $user, 'Boolean', 0, '', 'Input', '', 'center');

        $this->create('embd_table','Display Options,Table', $table, $user, 'Boolean', 0, '', 'Input', '', 'center');
        $this->create('embd_listing','Display Options,List', $table, $user, 'Boolean', 0, '', 'Input', '', 'center');
        $this->create('embd_board','Display Options,Board', $table, $user, 'Boolean', 0, '', 'Input', '', 'center');
        $this->create('default_display','Display Options,Default', $table, $user, 'String', 0, '', 'Input', '', 'left');
        $this->create('embd_stats','Table Display,Show STATS', $table, $user, 'Boolean', 0, '', 'Input', '', 'center');
        $this->create('embd_table_align','Table Display,Default Alignment', $table, $user, 'String', 0, '', 'Input', '', 'center');
        $this->create('embd_fit_width','Table Display,Fit to Width', $table, $user, 'Boolean', 0, '', 'Input', '', 'center');
        $this->create('embd_float_actions','Table Display,Floating Actions', $table, $user, 'Boolean', 0, '', 'Input', '', 'center');
        $this->create('listing_field_id', 'List Display,Field', $table, $user, 'String', 0, '', 'Input', '', 'left');
        $this->create('listing_rows_width', 'List Display,Panel Width,px or %', $table, $user, 'String', 0, '', 'Input', '', 'left');
        $this->create('listing_rows_min_width', 'List Display,Panel Width,Min (px)', $table, $user, 'String', 0, '', 'Input', '', 'left');
        $this->create('max_nbr_rcds_embd','Max. number of records allowed to be added to the embedded table', $table, $user, 'String', 0, '', 'Input', '', 'left');
        $this->create('header','Placement,Header', $table, $user, 'String', 0, '', 'Input', '', 'left');
        $this->create('position_field_id','Placement,Field,Name', $table, $user, 'String', 0, '', 'Input', '', 'left');
        $this->create('position','Placement,Field,Order', $table, $user, 'String', 0, '', 'Input', '', 'left');
        $this->create('style','Placement,Field,Style', $table, $user, 'String', 0, '', 'Input', '', 'left');
        $this->create('placement_tab_name','Placement,Tab,Name', $table, $user, 'String', 0, '', 'Input', '', 'left');
        $this->create('placement_tab_order','Placement,Tab,Order', $table, $user, 'Integer', 0, '', 'Input', '', 'left');
        $this->create('max_height_inline_embd','Max. height(px) of the in-line display panel', $table, $user, 'String', 0, '', 'Input', '', 'left');

        $this->create('ctlg_columns_number','Max. Number of Columns of Boards of the Catalog display', $table, $user);
        $this->create('ctlg_table_id','Source Table for Catalog', $table, $user);
        $this->create('ctlg_data_range','Data Range', $table, $user);
        $this->create('ctlg_visible_field_ids','Visible Fields of Catalog Table', $table, $user, 'String', 0, '', 'M-Select');
        $this->create('ctlg_filter_field_ids','Available Filter Fields of Catalog', $table, $user, 'String', 0, '', 'M-Select');
        $this->create('ctlg_parent_link_field_id','Connection Fields,Embedded Table', $table, $user);
        $this->create('ctlg_distinct_field_id','Connection Fields,Catalog (used to list records)', $table, $user);
        $this->create('ctlg_parent_quantity_field_id','Embedded table field specifying the quantity or amount require for adding or selecting an item (record)', $table, $user);
        $this->create('ctlg_display_option','Display Option', $table, $user);

        $this->update($table, 'embd_table', ['is_table_field_in_popup'=>1, 'is_start_table_popup'=>1]);
        $this->update($table, 'embd_listing', ['is_table_field_in_popup'=>1]);
        $this->update($table, 'embd_board', ['is_table_field_in_popup'=>1]);
        $this->update($table, 'default_display', ['is_table_field_in_popup'=>1]);
        $this->update($table, 'embd_stats', ['is_table_field_in_popup'=>1, 'is_start_table_popup'=>1]);
        $this->update($table, 'embd_fit_width', ['is_table_field_in_popup'=>1]);
        $this->update($table, 'embd_table_align', ['is_table_field_in_popup'=>1]);
        $this->update($table, 'embd_float_actions', ['is_table_field_in_popup'=>1]);
        $this->update($table, 'listing_field_id', ['is_table_field_in_popup'=>1, 'is_start_table_popup'=>1]);
        $this->update($table, 'listing_rows_width', ['is_table_field_in_popup'=>1]);
        $this->update($table, 'listing_rows_min_width', ['is_table_field_in_popup'=>1]);
        $this->update($table, 'header', ['is_table_field_in_popup'=>1, 'is_start_table_popup'=>1]);
        $this->update($table, 'position', ['is_table_field_in_popup'=>1]);
        $this->update($table, 'position_field_id', ['is_table_field_in_popup'=>1]);
        $this->update($table, 'style', ['is_table_field_in_popup'=>1]);
        $this->update($table, 'placement_tab_name', ['is_table_field_in_popup'=>1]);
        $this->update($table, 'placement_tab_order', ['is_table_field_in_popup'=>1]);
        $this->update($table, 'ctlg_parent_link_field_id', ['is_table_field_in_popup'=>1, 'is_start_table_popup'=>1]);
        $this->update($table, 'ctlg_distinct_field_id', ['is_table_field_in_popup'=>1]);

        $this->permissionsService->addSystemRights($table->id);
        $this->viewRepository->addSys($table);



        // Dcr Notif Linked Table
        $table = Table::where('db_name', '=', 'dcr_notif_linked_tables')->first();
        $this->create('link_id','Link', $table, $user, 'String', 1, '', 'Input', '', 'left');
        $this->create('related_format','Format', $table, $user, 'String', 0, '', 'Input', '', 'left');
        $this->create('related_col_group_id','Col. Group', $table, $user, 'String', 0, '', 'Input', '', 'left');
        $this->create('description','Description', $table, $user, 'String', 0, '', 'Input', '', 'left');
        $this->create('is_active','Status', $table, $user, 'Boolean', 0, '', 'Input', '', 'center');

        $this->permissionsService->addSystemRights($table->id);
        $this->viewRepository->addSys($table);



        // TABLE ROW GROUPS
        $table = Table::where('db_name', '=', 'table_row_groups')->first();

        $this->create('name','Name', $table, $user, 'String', 1);
        $this->create('preview_col_id','Column Group for Preview', $table, $user);
        $this->create('row_ref_condition_id','Referencing Condition', $table, $user);
        $this->create('description','Description', $table, $user);

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
        $this->create('execution_delay','Delay', $table, $user, 'Time');
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

        //Conditions
        $table = Table::where('db_name', '=', 'table_alert_click_updates')->first();
        $this->create('table_field_id','Field to be updated', $table, $user, 'String', 1);
        $this->create('new_value','New Value', $table, $user);
        //add right to Visitor
        $this->permissionsService->addSystemRights($table->id);
        $this->viewRepository->addSys($table);

        //Snapshot fields
        $table = Table::where('db_name', '=', 'table_alert_snapshot_fields')->first();
        $this->create('current_field_id','This Table', $table, $user);
        $this->create('source_field_id','Source Table (Data Range)', $table, $user);
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
        $this->create('source','Method', $table, $user);
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
        $this->create('source','Method', $table, $user);
        $this->create('input','Input', $table, $user);
        $this->create('inherit_field_id','Inherit/Field', $table, $user);
        //for temp fields
        $this->create('temp_table_field_id','Field', $table, $user);
        $this->create('temp_source','Method', $table, $user);
        $this->create('temp_input','Input', $table, $user);
        $this->create('temp_inherit_field_id','Inherit/Field', $table, $user);
        //add right to Visitor
        $this->permissionsService->addSystemRights($table->id);
        $this->viewRepository->addSys($table);



        // TABLE VIEWS
        $table = Table::where('db_name', '=', 'table_views')->first();

        $pdefa = \Vanguard\Services\Tablda\HelperService::viewPartsDef();
        $this->create('name','Name', $table, $user, 'String', 1);
        $this->create('custom_path','Custom URL', $table, $user);
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
        $this->create('_embd','Access,EMBD', $table, $user);
        $this->create('is_locked','Access,Lock', $table, $user, 'Boolean');
        $this->create('lock_pass','Access,PWD', $table, $user, 'Password');
        $this->create('qr_mrv_link','Access,QR Code', $table, $user);
        $this->create('view_filtering','Filtering,Apply filtering', $table, $user, 'Boolean');
        $this->create('srv_fltrs_on_top','Filters,On Top', $table, $user, 'Boolean');
        $this->create('srv_fltrs_ontop_pos','Filters,Position', $table, $user, 'S-Select');
        $this->create('is_active','Status', $table, $user, 'Boolean');
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
        $this->update($table, '_embd', ['is_table_field_in_popup'=>1, 'is_start_table_popup'=>1]);
        $this->update($table, 'is_locked', ['is_table_field_in_popup'=>1]);
        $this->update($table, 'lock_pass', ['is_table_field_in_popup'=>1]);
        $this->update($table, 'qr_mrv_link', ['is_table_field_in_popup'=>1]);
        $this->update($table, 'srv_fltrs_on_top', ['is_table_field_in_popup'=>1, 'is_start_table_popup'=>1]);
        $this->update($table, 'srv_fltrs_ontop_pos', ['is_table_field_in_popup'=>1]);

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
        $this->create('kanban_field_name','Name', $table, $user);
        $this->create('table_field_id','Field', $table, $user, 'String', 1);
        $this->create('kanban_data_range','Data Range', $table, $user, 'DataRange', 1);
        $this->create('kanban_field_description','Description', $table, $user);
        $this->create('kanban_active','Status', $table, $user, 'Boolean');
        $this->permissionsService->addSystemRights($table->id);
        $this->viewRepository->addSys($table);
        // Kanban Settings
        $table = Table::where('db_name', '=', 'table_kanban_settings_2_table_fields')->first();
        $this->create('_name','Name', $table, $user, 'String');
        $this->create('is_header_show','Shown in card header,Name', $table, $user, 'Boolean');
        $this->create('is_header_value','Shown in card header,Value', $table, $user, 'Boolean');
        $this->create('table_show_name','Show Field,Name', $table, $user, 'Boolean');
        $this->create('table_show_value','Show Field,Value', $table, $user, 'Boolean');
        $this->create('cell_border','Show Field,Border', $table, $user, 'Boolean');
        $this->create('picture_style','Image Display,Style', $table, $user, 'String');
        $this->create('picture_fit','Image Display,Fit', $table, $user, 'String');
        $this->create('width_of_table_popup','Table Display,Width', $table, $user, 'String');
        $this->create('is_start_table_popup','Table Display,Start', $table, $user, 'Boolean');
        $this->create('is_table_field_in_popup','Table Display,Status', $table, $user, 'Boolean');
        $this->create('is_hdr_lvl_one_row','Table Display,HLiOR', $table, $user, 'Boolean');
        $this->permissionsService->addSystemRights($table->id);
        $this->viewRepository->addSys($table);
        // Kanban Group Params
        $table = Table::where('db_name', '=', 'table_kanban_group_params')->first();
        $this->create('table_field_id','Field', $table, $user, 'String', 1);
        $this->create('stat','Card Header STATS', $table, $user, 'String', 1);
        $this->permissionsService->addSystemRights($table->id);
        $this->viewRepository->addSys($table);


        // Incoming Links
        $table = Table::where('db_name', '=', 'incoming_links')->first();
        $this->create('incoming_allow','Allow', $table, $user, 'Boolean');
        $this->create('user_id','From,User', $table, $user, 'User');
        $this->create('table_name','From,Table', $table, $user, 'String');
        $this->create('ref_cond_name','RC', $table, $user, 'String');
        $this->create('rc_inheriting','Inheriting', $table, $user, 'Boolean');
        $this->create('use_category','Use,Category', $table, $user, 'String');
        $this->create('use_name','Use,Name', $table, $user, 'String');
        $this->permissionsService->addSystemRights($table->id);
        $this->viewRepository->addSys($table);


        // Emails Settings
        $table = Table::where('db_name', '=', 'email_settings')->first();
        $this->create('scenario','Scenario/Uses', $table, $user, 'String');
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


        // Formula Helpers
        $table = Table::where('db_name', '=', 'formula_helpers')->first();
        $fh_field = $this->create('formula','Formula', $table, $user, 'String');
        $this->create('notes','Notes', $table, $user, 'String');
        $this->permissionsService->addSystemRights($table->id);
        $this->viewRepository->addSys($table);
        if (! $fh_field->_ddl) {
            $repo = new \Vanguard\Repositories\Tablda\DDLRepository();
            $ddl = $repo->addDDL([
                'table_id' => $table->id,
                'name' => 'FormulaDDL',
                'type' => 'Regular',
            ]);

            foreach ($formulaExamples as $arr) {
                $repo->addDDLItem([
                    'ddl_id' => $ddl->id,
                    'option' => $arr['key'],
                    'show_option' => ucfirst(str_replace('formula_example_', '', $arr['key']) ?: 'general'),
                ]);
            }

            $fh_field->input_type = 'S-Select';
            $fh_field->ddl_id = $ddl->id;
            $fh_field->save();
        }


        // Promo Codes
        $table = Table::where('db_name', '=', 'promo_codes')->first();
        $this->create('code','Code', $table, $user, 'String', 1);
        $this->create('credit','Credit', $table, $user, 'Integer', 1);
        $this->create('start_at','Dates,Start', $table, $user, 'Date', 1);
        $this->create('end_at','Dates,Expiration', $table, $user, 'Date', 1);
        $this->create('is_active','Status', $table, $user, 'Boolean');
        $this->create('notes','Notes', $table, $user, 'String');
        $this->permissionsService->addSystemRights($table->id);
        $this->viewRepository->addSys($table);


        // Pages Contents
        $table = Table::where('db_name', '=', 'page_contents')->first();
        $this->create('name','Name', $table, $user, 'String');
        $this->create('table_id','Table', $table, $user, 'String', 1);
        $this->create('table_view_id','View', $table, $user, 'String', 1);
        $this->create('view_part','Part', $table, $user, 'String');
        $this->permissionsService->addSystemRights($table->id);
        $this->viewRepository->addSys($table);


        // Table Tournaments
        $table = Table::where('db_name', '=', 'table_tournaments')->first();
        $this->create('name','Name', $table, $user, 'String', 1);
        $this->create('tb_tour_data_range','Data Range', $table, $user, 'DataRange', 1);
        $this->create('description','Description', $table, $user, 'String');
        $this->create('tour_active','Status', $table, $user, 'Boolean');
        $this->permissionsService->addSystemRights($table->id);
        $this->viewRepository->addSys($table);


        // Table Simplemaps
        $table = Table::where('db_name', '=', 'table_simplemaps')->first();
        $this->create('name','Name', $table, $user, 'String', 1);
        $this->create('map','Map', $table, $user, 'String', 1);
        $this->create('level_fld_id','Region ID', $table, $user, 'String', 1);
        $this->create('multirec_style','Multi-Record,Style', $table, $user, 'String', 0, 'tabs');
        $this->create('multirec_fld_id','Multi-Record,Name', $table, $user, 'String');
        $this->create('tb_smp_data_range','Data Range', $table, $user, 'DataRange', 1);
        $this->create('smp_active','Status', $table, $user, 'Boolean');

        $this->create('locations_name_fld_id','Name', $table, $user);
        $this->create('locations_lat_fld_id','Latitude', $table, $user);
        $this->create('locations_long_fld_id','Longitude', $table, $user);
        $this->create('locations_descr_fld_id','Description', $table, $user);
        $this->create('locations_icon_shape','Icon,Shape', $table, $user);
        $this->create('locations_icon_color_fld_id','Icon,Color', $table, $user);

        $this->permissionsService->addSystemRights($table->id);
        $this->viewRepository->addSys($table);
        // Simplemaps Settings
        $table = Table::where('db_name', '=', 'table_simplemaps_2_table_fields')->first();
        $this->create('_name','Name', $table, $user, 'String');
        $this->create('is_header_show','Shown in card header,Name', $table, $user, 'Boolean');
        $this->create('is_header_value','Shown in card header,Value', $table, $user, 'Boolean');
        $this->create('table_show_name','Show Field,Name', $table, $user, 'Boolean');
        $this->create('table_show_value','Show Field,Value', $table, $user, 'Boolean');
        $this->create('cell_border','Show Field,Border', $table, $user, 'Boolean');
        $this->create('picture_style','Image Display,Style', $table, $user, 'String');
        $this->create('picture_fit','Image Display,Fit', $table, $user, 'String');
        $this->create('width_of_table_popup','Table Display,Width', $table, $user, 'String');
        $this->create('is_start_table_popup','Table Display,Start', $table, $user, 'Boolean');
        $this->create('is_table_field_in_popup','Table Display,Status', $table, $user, 'Boolean');
        $this->create('is_hdr_lvl_one_row','Table Display,HLiOR', $table, $user, 'Boolean');

        $this->permissionsService->addSystemRights($table->id);
        $this->viewRepository->addSys($table);


        // Table Groupings
        $table = Table::where('db_name', '=', 'table_groupings')->first();
        $this->create('name','Name', $table, $user, 'String', 1);
        $this->create('rg_data_range','Data Range,Rows', $table, $user, 'DataRange');
        $this->create('rg_colgroup_id','Data Range,Columns', $table, $user, 'String');
        $this->create('rg_alignment','Alignment', $table, $user, 'String');
        $this->create('description','Description', $table, $user, 'String');
        $this->create('_button','STATS', $table, $user, 'Button');
        $this->create('rg_active','Status', $table, $user, 'Boolean');
        $this->permissionsService->addSystemRights($table->id);
        $this->viewRepository->addSys($table);


        // Table Grouping Fields
        $table = Table::where('db_name', '=', 'table_grouping_fields')->first();
        $this->create('field_id','Field', $table, $user, 'RefField', 1);
        $this->create('field_name_visible','Field Name', $table, $user, 'Boolean');
        $this->create('sorting','Sorting', $table, $user, 'String');
        $this->create('color','Color', $table, $user, 'Color');
        $this->create('indent','Indent', $table, $user, 'String');
        $this->create('default_state','Default State', $table, $user, 'String');
        $this->create('_button','STATS', $table, $user, 'Button');
        $this->create('rg_active','Status', $table, $user, 'Boolean');
        $this->permissionsService->addSystemRights($table->id);
        $this->viewRepository->addSys($table);


        // Table Grouping Stats
        $table = Table::where('db_name', '=', 'table_grouping_stats')->first();
        $this->create('field_id','Field', $table, $user, 'RefField', 1);
        $this->create('stat_fn','Stat', $table, $user, 'String');
        $this->permissionsService->addSystemRights($table->id);
        $this->viewRepository->addSys($table);


        // Table Grouping Field Stats
        $table = Table::where('db_name', '=', 'table_grouping_field_stats')->first();
        $this->create('field_id','Field', $table, $user, 'RefField', 1);
        $this->create('stat_fn','Stat', $table, $user, 'String');
        $this->permissionsService->addSystemRights($table->id);
        $this->viewRepository->addSys($table);


        // Table Chart Tabs
        $table = Table::where('db_name', '=', 'table_chart_tabs')->first();
        $this->create('name','Name', $table, $user, 'String', 1);
        $this->create('chart_data_range','Data Range', $table, $user, 'DataRange', 1);
        $this->create('description','Description', $table, $user, 'String');
        $this->create('chart_active','Status', $table, $user, 'Boolean');
        $this->permissionsService->addSystemRights($table->id);
        $this->viewRepository->addSys($table);


        // Table DCR Fields
        $table = Table::where('db_name', '=', 'table_data_requests_2_table_fields')->first();
        $this->create('_name','Name', $table, $user, 'String');
        $this->create('fld_popup_shown','Field Display,Status', $table, $user, 'Boolean');
        $this->create('fld_display_name','Field Display,Name', $table, $user, 'Boolean');
        $this->create('fld_display_value','Field Display,Value', $table, $user, 'Boolean');
        $this->create('fld_display_border','Field Display,Border', $table, $user, 'Boolean');
        $this->create('fld_display_header_type','Field Display,Header', $table, $user, 'String');
        $this->create('is_topbot_in_popup','Field Display,Name/Val.,Top/Bot', $table, $user, 'Boolean');
        $this->create('width_of_table_popup','Table Display,Width', $table, $user, 'String');
        $this->create('is_start_table_popup','Table Display,Start', $table, $user, 'Boolean');
        $this->create('is_table_field_in_popup','Table Display,Status', $table, $user, 'Boolean');
        $this->create('is_hdr_lvl_one_row','Table Display,HLiOR', $table, $user, 'Boolean');
        $this->create('is_dcr_section','Section or Accordion Panel or HTab,Status', $table, $user, 'Boolean');
        $this->create('dcr_section_name','Section or Accordion Panel or HTab,Panel or Tab Name', $table, $user);
        $this->permissionsService->addSystemRights($table->id);
        $this->viewRepository->addSys($table);


        // Table SRV Fields
        $table = Table::where('db_name', '=', 'table_srv_2_table_fields')->first();
        $this->create('_name','Name', $table, $user, 'String');
        $this->create('width_of_table_popup','Table Display,Width', $table, $user, 'String');
        $this->create('is_start_table_popup','Table Display,Start', $table, $user, 'Boolean');
        $this->create('is_table_field_in_popup','Table Display,Status', $table, $user, 'Boolean');
        $this->create('is_hdr_lvl_one_row','Table Display,HLiOR', $table, $user, 'Boolean');
        $this->create('is_dcr_section','Sections', $table, $user, 'Boolean');
        $this->permissionsService->addSystemRights($table->id);
        $this->viewRepository->addSys($table);


        // Table Report Templates
        $table = Table::where('db_name', '=', 'table_report_templates')->first();
        $this->create('name','Name', $table, $user, 'String', 1);
        $this->create('doc_type','Doc Type', $table, $user, 'String', 1, 'ms_word', 'S-Select');
        $this->create('connected_cloud_id','Connected Cloud', $table, $user, 'String', 0, '', 'S-Select');
        $this->create('template_source','Template File,Source', $table, $user, 'String', 1, 'Upload', 'S-Select');
        $this->create('template_file','Template File,Upload', $table, $user, 'Attachment', 1);
        $this->create('cloud_report_folder','Folder for cloud Reports', $table, $user, 'String');
        $this->permissionsService->addSystemRights($table->id);
        $this->viewRepository->addSys($table);
        // Table Reports
        $table = Table::where('db_name', '=', 'table_reports')->first();
        $this->create('report_name','Name', $table, $user, 'String', 1);
        $this->create('report_data_range','Data Range', $table, $user, 'DataRange', 1, 0);
        $this->create('report_template_id','Template', $table, $user, 'String', 1, '', 'S-Select');
        $this->create('report_file_formula','File Name', $table, $user, 'String', 0, '', 'Formula');
        $this->create('report_field_id','Field For Saving Reports', $table, $user, 'RefField', 1);
        $this->create('_make_reports','Generate', $table, $user, 'String');
        $this->permissionsService->addSystemRights($table->id);
        $this->viewRepository->addSys($table);
        // Table Report Sources
        $table = Table::where('db_name', '=', 'table_report_sources')->first();
        $this->create('name','Name', $table, $user, 'String', 1);
        $this->create('ref_link_id','Record Type Links', $table, $user, 'RefRefLink');
        $this->create('description','Description', $table, $user, 'String');
        $this->permissionsService->addSystemRights($table->id);
        $this->viewRepository->addSys($table);
        // Table Report Source Variables
        $table = Table::where('db_name', '=', 'table_report_source_variables')->first();
        $this->create('variable','Variable', $table, $user, 'String', 1);
        $this->create('variable_type','Object Type', $table, $user, 'String', 1);
        $this->create('var_object_id','Object', $table, $user, 'String');
        $this->create('additional_attributes','Attributes', $table, $user, 'String');
        $this->permissionsService->addSystemRights($table->id);
        $this->viewRepository->addSys($table);

        // Table Automation Payments
        $table = Table::where('db_name', '=', 'automation_histories')->first();
        $this->create('user_id', 'User', $table, $user, 'User');
        $this->create('table_id', 'Table', $table, $user, 'RefTable',0, '', 'S-Select');
        $this->create('function','Function', $table, $user);
        $this->create('name','Name', $table, $user);
        $this->create('component','Component', $table, $user);
        $this->create('part','Part', $table, $user);
        $this->create('exec_time','Execution,Duration', $table, $user);
        $this->create('start_time','Execution,Time', $table, $user, 'Date Time');
        $this->create('year','Year', $table, $user);
        $this->create('month','Month', $table, $user);
        $this->create('week','Week', $table, $user);
        $this->create('day','Day', $table, $user);
        $this->permissionsService->addSystemRights($table->id);
        $this->viewRepository->addSys($table);

        // Table AI
        $table = Table::where('db_name', '=', 'table_ais')->first();
        $this->create('name','Name', $table, $user, 'String', 1);
        $this->create('openai_key_id', 'API', $table, $user, 'String',1, '', 'S-Select');
        $this->create('related_table_ids', 'Table(s)', $table, $user, 'RefTable',1, '', 'M-Select');
        $this->create('ai_data_range','Data Range', $table, $user, 'DataRange', 1);
        $this->create('description','Description', $table, $user);
        $this->create('is_right_panel','Right Side Panel', $table, $user, 'Radio');
        $this->permissionsService->addSystemRights($table->id);
        $this->viewRepository->addSys($table);
    }

    /**
     * @param $field
     * @param $name
     * @param $table
     * @param $user
     * @param $type
     * @param $required
     * @param $default
     * @param $inp_type
     * @param $tooltip
     * @param $col_align
     * @return TableField
     */
    private function create($field, $name, $table, $user, $type = 'String', $required = 0, $default = '', $inp_type = 'Input', $tooltip = '', $col_align = 'center') {
        $present = $table->_fields()->where('field', $field)->first();
        if (!$present) {
            return TableField::create([
                'table_id' => $table->id,
                'field' => $field,
                'name' => $name,
                'input_type' => $inp_type,
                'f_type' => $type,
                'f_default' => $default,
                'f_required' => $required,
                'tooltip' => $tooltip,
                'col_align' => $col_align,
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
            $present->col_align = $col_align;
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
        $present = $table->_fields()->where('field', $field)->first();
        if ($present) {
            $present->update($params);
        }
    }
}
