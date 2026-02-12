<script>
    import {VerticalTableFldObject} from "../CustomTable/VerticalTableFldObject";

    /**
     *  can be present:
     *
     *  this.forbiddenColumns: Array
     *  this.availableColumns: Array
     *  */
    export default {
        data: function () {
            return {
                actionArray: [
                    'settings_ddl', 'settings_ddl_items', 'dcr_group', 'dcr_linked_tb', 'cell_page', 'settings_groupings',
                    'table_permission_group','permission_group_col', 'permission_group_row','settings_report_specific',
                    'data_sets', 'data_sets_rows', 'data_sets_items', 'sett_usergroup_conds', 'data_sets_ref_condition_items',
                    'cond_format', 'plans', 'plan_items', 'settings_display_links', 'alert_anr', 'alert_ufv', 'tw_history_call',
                    'user_conn', 'invite_module', 'link_popup', 'folder_permission_group', 'stim_views', 'twilio_keys', 'tw_history',
                    'settings_display_add','folder_view', 'alert_notif', 'alert_notif_autom', 'table_views','settings_kanban_add',
                    'settings_calendar_add','settings_gantt_add','settings_bi_add', 'alert_snapshot', 'alert_notif_clicktoupdate',
                    'settings_grouping_stat','settings_grouping_fields','ref_conds','ai_addon','data_sets_colgroups','settings_simplemap_add',
                ],
                actionDelArray: [
                    'settings_ddl', 'settings_ddl_items', 'dcr_group', 'dcr_linked_tb', 'cell_page', 'settings_groupings',
                    'table_permission_group','permission_group_col', 'permission_group_row','settings_report_specific',
                    'data_sets', 'data_sets_rows', 'data_sets_items', 'sett_usergroup_conds', 'data_sets_ref_condition_items',
                    'cond_format', 'settings_display_links', 'alert_anr', 'alert_ufv', 'tw_history_call',
                    'user_conn', 'invite_module', 'link_popup', 'folder_permission_group', 'stim_views', 'twilio_keys', 'tw_history',
                    'settings_display_add','folder_view', 'alert_notif', 'alert_notif_autom', 'table_views','settings_kanban_add',
                    'settings_calendar_add','settings_gantt_add','settings_bi_add', 'alert_snapshot', 'alert_notif_clicktoupdate',
                    'settings_grouping_stat','settings_grouping_fields','ref_conds','ai_addon','data_sets_colgroups','settings_simplemap_add',
                ],
                actionDelSystem: [
                    'permission_group_col', 'permission_group_row',
                ],
                linkArray: [
                    'list_view', 'favorite', 'request_view', 'settings_display', 'settings_display_add', 'dcr_linked_tb',
                    'settings_ddl', 'table_permission_group', 'folder_permission_group','settings_kanban_add', 'alert_ufv',
                    'data_sets', 'data_sets_rows', 'plans', 'data_sets_ref_condition_items', 'settings_display_links', 'alert_anr',
                    'folder_view', 'alert_notif', 'alert_notif_autom', 'link_popup', 'table_views', 'stim_views', 'user_conn',
                    'settings_calendar_add','settings_gantt_add','settings_bi_add','settings_report_specific', 'settings_groupings',
                    'settings_grouping_stat','settings_grouping_fields','ref_conds','ai_addon','data_sets_colgroups','settings_simplemap_add',
                ],
                popupArray: [
                    'list_view', 'favorite', 'link_popup', 'settings_display',
                ],
            }
        },
        methods: {
            inArray(item, array) {
                return $.inArray(item, array) > -1;
            },
            isShowField(tableHeader, excluded) {
                let isShowed = this.isVertTable
                    ? VerticalTableFldObject.fieldSetting('fld_popup_shown', tableHeader, null, this.behavior)
                    : tableHeader.is_showed;

                return !tableHeader._permis_hidden
                    && (isShowed || excluded)
                    && (!this.forbiddenColumns || !this.inArray(tableHeader.field, this.forbiddenColumns))
                    && (!this.availableColumns || this.inArray(tableHeader.field, this.availableColumns));
            },
            isShowFieldElem(tableHeader) {
                return this.isShowField(tableHeader)
                    && (
                        !this.hasFloatColumns
                        ||
                        Boolean(tableHeader.is_floating) === Boolean(this.isFloatingTable)
                    );
            },
        },
    }
</script>