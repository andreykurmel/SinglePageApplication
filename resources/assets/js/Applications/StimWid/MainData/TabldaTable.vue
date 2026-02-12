<template>
    <div class="tablda-table-wrapper flex" :class="wrap_class || 'full-frame'">

        <template v-if="show_type === 'vertical' && allshow">
            <vertical-table
                    v-if="metaTable.is_loaded"
                    :tb_id="tb_id"
                    :td="'custom-cell-table-data'"
                    :global-meta="metaTable.params"
                    :table-meta="metaTable.params"
                    :settings-meta="$root.settingsMeta"
                    :table-row="verticalRow"
                    :user="$root.user"
                    :cell-height="$root.cellHeight"
                    :max-cell-rows="$root.maxCellRows"
                    :behavior="is_showed ? 'list_view' : ''"
                    :is-add-row="!found_model._id"
                    :available-columns="stim_link_params.avail_cols_for_app"
                    :style="{
                        maxWidth: '800px',
                        paddingTop: ((metaTable.params.row_space_size/100 * $root.themeTextLineHeigh) || metaTable.params.vert_tb_rowspacing) + 'px',
                    }"
                    @updated-cell="checkRowBknd"
                    @show-src-record="showSrcRecord"
                    @show-add-ddl-option="showAddDDLOption"
            ></vertical-table>
        </template>
        <template v-else-if="show_type === 'chart' && allshow">
            <tab-bi-view
                    v-if="metaTable.is_loaded && allRows.is_loaded && is_showed && local_req_params.table_id"
                    :table_id="metaTable.table_id"
                    :table-meta="metaTable.params"
                    :rows_count="allRows.rows_count"
                    :request_params="local_req_params"
                    :ext-filters="allRows.filters"
                    :export_prefix="get_export_prefix"
                    :is-visible="is_showed"
                    :is-transparent="true"
                    class="full-frame"
            ></tab-bi-view>
        </template>
        <template v-else-if="show_type === 'map' && allshow">
            <tab-map-view
                    v-if="metaTable.is_loaded && allRows.is_loaded && is_showed && local_req_params.table_id"
                    :table_id="metaTable.table_id"
                    :table-meta="metaTable.params"
                    :can-edit="metaTable.can_edit"
                    :is-visible="is_showed"
                    :request_params="local_req_params"
                    class="full-frame"
            ></tab-map-view>
        </template>
        <template v-else-if="show_type === 'table' && allshow">
            <div v-if="stim_link_params.filters_active && metaTable.is_loaded"
                 class="filters-wrapper"
                 :style="{width: show_tb_filters ? filter_sizes.width+'px' : '0px', padding: show_tb_filters ? '5px' : '0px'}"
            >
                <div class="filters-wrapper--head">
                    <span v-show="show_tb_filters">Filters</span>
                    <span class="glyphicon"
                          :class="[show_tb_filters ? 'glyphicon-triangle-left' : 'glyphicon-triangle-right']"
                          :style="{right: show_tb_filters ? '0px' : '-22px'}"
                          @click="show_tb_filters = !show_tb_filters"
                    ></span>
                </div>
                <div class="filters-wrapper--body" v-show="show_tb_filters">
                    <filters-block-with-combos
                            v-if="allRows.filters"
                            :table-meta="metaTable.params"
                            :input_filters="allRows.filters"
                            :available-columns="stim_link_params.avail_cols_for_app"
                            @changed-filter="changedFilter"
                            @applied-saved-filter="applyCombo"
                    ></filters-block-with-combos>
                </div>
                <header-resizer :table-header="filter_sizes" @resize-finished="saveFltrSiz"></header-resizer>
            </div>
            <custom-table
                    v-if="metaTable.is_loaded"
                    :tb_id="tb_id"
                    :cell_component_name="'custom-cell-table-data'"
                    :table_id="metaTable.table_id"
                    :global-meta="metaTable.params"
                    :table-meta="metaTable.params"
                    :settings-meta="$root.settingsMeta"
                    :all-rows="allRows.all_rows"
                    :rows-count="allRows.rows_count"
                    :page="allRows.page"
                    :is-pagination="isPagination"
                    :user="$root.user"
                    :sort="allRows.sort"
                    :cell-height="$root.cellHeight"
                    :max-cell-rows="$root.maxCellRows"
                    :behavior="is_showed ? 'list_view' : ''"
                    :adding-row="adding_row"
                    :external-object-for-add="metaTable.empty_row"
                    :available-columns="stim_link_params.avail_cols_for_app"
                    :forbidden-columns="forbidden_columns"
                    :style="{border: no_border ? 'none' : '1px solid #CCC'}"
                    @added-row="insertRow"
                    @updated-row="updateRow"
                    @delete-row="deleteRow"
                    @mass-updated-rows="massUpdatedRows"
                    @change-page="changePage"
                    @row-index-clicked="rowIndexClicked"
                    @sort-by-field="sortByFieldTablda"
                    @sub-sort-by-field="subSortByFieldTablda"
                    @delete-selected-rows="deleteSelectedRowsHandler"
                    @check-row="toggleAllCheckBoxes"
                    @show-header-settings="showHeaderSettings"
                    @backend-row-checked="afterBackendCheck"
                    @show-src-record="showSrcRecord"
                    @row-selected="emitPermissions"
                    @show-add-ddl-option="showAddDDLOption"
            ></custom-table>
            <custom-edit-pop-up
                    v-if="metaTable.is_loaded && editPopUpRow"
                    :global-meta="metaTable.params"
                    :table-meta="metaTable.params"
                    :table-row="editPopUpRow"
                    :settings-meta="$root.settingsMeta"
                    :role="popUpRole"
                    :input_component_name="'custom-cell-table-data'"
                    :behavior="is_showed ? 'list_view' : ''"
                    :user="$root.user"
                    :cell-height="$root.cellHeight"
                    :max-cell-rows="$root.maxCellRows"
                    :available-columns="stim_link_params.avail_cols_for_app"
                    @popup-insert="insertRow"
                    @popup-update="updateRow"
                    @popup-copy="copyRow"
                    @popup-delete="deleteRow"
                    @popup-close="closePopUp"
                    @another-row="anotherRowPopup"
                    @backend-row-checked="afterBackendCheck"
                    @show-src-record="showSrcRecord"
            ></custom-edit-pop-up>
        </template>

        <!-- Popup for settings up Column Settings for Table (also in Settings/Basics) -->
        <for-settings-pop-up
                v-if="metaTable.is_loaded && $root.settingsMeta['table_fields'] && editDisplaySettingsRow"
                :global-meta="metaTable.params"
                :table-meta="$root.settingsMeta['table_fields']"
                :settings-meta="$root.settingsMeta"
                :table-row="editDisplaySettingsRow"
                :user="$root.user"
                :cell-height="$root.cellHeight"
                :max-cell-rows="$root.maxCellRows"
                @popup-update="updateDisplayRow"
                @popup-close="editDisplaySettingsRow = null"
                @another-row="anotherSettingsRowPopup"
                @direct-row="showHeaderSettings"
        ></for-settings-pop-up>

        <!-- Link Popups -->
        <template v-for="(linkObj, idx) in linkPopups">
            <header-history-pop-up
                v-if="linkObj.key === 'show' && linkObj.link.link_type === 'History'"
                :idx="linkObj.index"
                :table-meta="tableMeta"
                :table-row="linkObj.row"
                :history-header="linkObj.header"
                :link="linkObj.link"
                :popup-key="idx"
                :is-visible="true"
                @popup-close="closeLinkPopup"
            ></header-history-pop-up>
            <link-pop-up
                v-else-if="linkObj.key === 'show'"
                :source-meta="tableMeta"
                :idx="linkObj.index"
                :link="linkObj.link"
                :meta-header="linkObj.header"
                :meta-row="linkObj.row"
                :popup-key="idx"
                :available-columns="linkObj.avail_columns"
                :view_authorizer="{
                    mrv_marker: $root.is_mrv_page,
                    srv_marker: $root.is_srv_page,
                    dcr_marker: $root.is_dcr_page,
                }"
                @show-src-record="showSrcRecord"
                @link-popup-close="closeLinkPopup"
            ></link-pop-up>
        </template>

        <!-- Copy and Replace funcs -->
        <copy-and-replace-pop-up
                v-if="showCopyPopup && is_showed && local_req_params.table_id"
                :table-meta="metaTable.params"
                :request_params="local_req_params"
                :all-rows="allRows.all_rows"
                :avail-fields="stim_link_params.avail_cols_for_app"
                :force-columns="stim_link_params.linkMap()"
                @popup-close="showCopyPopup = false"
                @after-copy="afterCopied"
        ></copy-and-replace-pop-up>

        <!--Parse and Paste-->
        <parse-and-paste-popup
                v-if="showParsePopup"
                :table-meta="metaTable.params"
                :avail-fields="stim_link_params.avail_cols_for_app"
                :replace-values="replaceInherits"
                @popup-close="showParsePopup = false;"
                @paste-completed="parseOrCopyCompleted"
        ></parse-and-paste-popup>

        <!--Rotate Translate Scale-->
        <stim-rotation-pop-up
                v-if="showRTSPopup"
                :stim-link="stim_link_params"
                :meta-rows="allRows"
                @popup-close="rtsClosed()"
                @rts-completed="rtsCompleted"
        ></stim-rotation-pop-up>

        <!--Rotate Translate Scale-->
        <stim-copy-from-model-popup
                v-if="showCopyFromModel"
                :stim-link="master_stim_link"
                :cur_stim_link="stim_link_params"
                :found-model="found_model"
                :sel_1_tab="sel_1_tab"
                :sel_1_sub_tab="sel_1_sub_tab"
                :sel_2_tab="sel_2_tab"
                :sel_2_sub_tab="sel_2_sub_tab"
                @popup-close="copyFromModelClosed()"
                @copy-model-completed="copyFromModelFinished()"
        ></stim-copy-from-model-popup>

        <!--Rotate Translate Scale-->
        <stim-rel-calcs-popup
                v-if="showRLCalcPopup"
                :stim-link="master_stim_link"
                :found-model="found_model"
                @popup-close="copyFromModelClosed()"
                @recalc-completed="copyFromModelFinished()"
        ></stim-rel-calcs-popup>

        <!--Add Select Option Popup-->
        <add-option-popup
                v-if="addOptionPopup.show"
                :table-header="addOptionPopup.tableHeader"
                :table-row="addOptionPopup.tableRow"
                :table-meta="metaTable.params"
                :settings-meta="$root.settingsMeta"
                :user="$root.user"
                @updated-row="updateRow"
                @hide="addOptionPopup.show = false"
                @show-src-record="showSrcRecord"
        ></add-option-popup>

        <!--Popup for settings up Conditional Formatting for Table-->
        <conditional-formatting-popup
                v-if="metaTable.params"
                :table-meta="metaTable.params"
                :settings-meta="$root.settingsMeta"
                :cell-height="$root.cellHeight"
                :max-cell-rows="$root.maxCellRows"
                @popup-close="reshowComponent()"
        ></conditional-formatting-popup>

        <overview-formats-popup
            v-if="metaTable.params"
            :table-meta="metaTable.params"
        ></overview-formats-popup>

        <!--Popup for settings up Views for Table-->
        <table-views-popup
                v-if="metaTable.params"
                :table-meta="metaTable.params"
        ></table-views-popup>

        <!--Popup for assign permissions-->
        <permissions-settings-popup
                v-if="metaTable.params"
                :table-meta="metaTable.params"
                :user="$root.user"
        ></permissions-settings-popup>


        <!--Popup for adding column links-->
        <ddl-settings-popup
                v-if="metaTable.params"
                :table-meta="metaTable.params"
                :settings-meta="$root.settingsMeta"
                :cell-height="$root.cellHeight"
                :max-cell-rows="$root.maxCellRows"
                :user="$root.user"
        ></ddl-settings-popup>


        <!--Popup for adding column links-->
        <grouping-settings-popup
                v-if="metaTable.params"
                :table-meta="metaTable.params"
                :user="$root.user"
        ></grouping-settings-popup>


        <!--Popup for showing ref conditions -->
        <ref-conditions-popup
                v-if="metaTable.params"
                :table-meta="metaTable.params"
                :user="$root.user"
                :table_id="metaTable.params ? metaTable.params.id : null"
        ></ref-conditions-popup>
    </div>
</template>

<script>
    import {ThreeHelper} from '../../../classes/helpers/ThreeHelper';
    import {MetaTabldaTable} from '../../../classes/MetaTabldaTable';
    import {MetaTabldaRows} from '../../../classes/MetaTabldaRows';
    import {StimLinkParams} from '../../../classes/StimLinkParams';
    import {FoundModel} from '../../../classes/FoundModel';

    import {eventBus} from "../../../app";

    import {mapActions, mapMutations, mapState} from 'vuex';

    import CheckRowBackendMixin from './../../../components/_Mixins/CheckRowBackendMixin.vue';
    import TableCheckboxesMixin from './../../../components/_Mixins/TableCheckboxesMixin.vue';
    import TableSortMixin from './../../../components/_Mixins/TableSortMixin.vue';
    import VerticalRowMixin from './VerticalRowMixin.vue';

    import CustomTable from "../../../components/CustomTable/CustomTable";
    import CustomEditPopUp from "../../../components/CustomPopup/CustomEditPopUp";
    import TabBiView from "../../../components/MainApp/Object/Table/TabBiView";
    import CopyAndReplacePopUp from "../../../components/CustomPopup/CopyAndReplacePopUp";
    import ParseAndPastePopup from "../../../components/CustomPopup/ParseAndPastePopup";
    import ForSettingsPopUp from "../../../components/CustomPopup/ForSettingsPopUp";
    import StimRotationPopUp from "../../../components/CustomPopup/StimRotationPopUp";
    import AddOptionPopup from "../../../components/CustomPopup/AddOptionPopup";
    import StimCopyFromModelPopup from "./StimCopyFromModelPopup";
    import ConditionalFormattingPopup from "../../../components/CustomPopup/CondFormatsPopup";
    import TableViewsPopup from "../../../components/CustomPopup/TableViewsPopup";
    import PermissionsSettingsPopup from "../../../components/CustomPopup/PermissionsSettingsPopup";
    import DdlSettingsPopup from "../../../components/CustomPopup/DdlSettingsPopup";
    import GroupingSettingsPopup from "../../../components/CustomPopup/GroupingSettingsPopup";
    import RefConditionsPopup from "../../../components/CustomPopup/RefConditionsPopup";
    import TabMapView from "../../../components/MainApp/Object/Table/TabMapView";
    import StimRelCalcsPopup from "./StimRelCalcsPopup";
    import HeaderResizer from "../../../components/CustomTable/Header/HeaderResizer";
    import OverviewFormatsPopup from "../../../components/CustomPopup/OverviewFormatsPopup";
    import HeaderHistoryPopUp from "../../../components/CustomPopup/HeaderHistoryPopUp";
    import FiltersBlockWithCombos from "../../../components/CommonBlocks/FiltersBlockWithCombos";

    export default {
        name: 'TabldaTable',
        mixins: [
            CheckRowBackendMixin,
            TableCheckboxesMixin,
            TableSortMixin,
            VerticalRowMixin,
        ],
        components: {
            FiltersBlockWithCombos,
            HeaderHistoryPopUp,
            OverviewFormatsPopup,
            HeaderResizer,
            StimRelCalcsPopup,
            TabMapView,
            RefConditionsPopup,
            GroupingSettingsPopup,
            DdlSettingsPopup,
            PermissionsSettingsPopup,
            TableViewsPopup,
            ConditionalFormattingPopup,
            StimCopyFromModelPopup,
            AddOptionPopup,
            StimRotationPopUp,
            ForSettingsPopUp,
            ParseAndPastePopup,
            CopyAndReplacePopUp,
            TabBiView,
            CustomEditPopUp,
            CustomTable,
        },
        data() {
            return {
                can_version: true,
                local_req_params: {},
                allshow: true,
                addOptionPopup: {
                    show: false,
                    tableHeader: null,
                    tableRow: null,
                },
                replaceInherits: {},
                showRLCalcPopup: false,
                showCopyFromModel: false,
                showRTSPopup: false,
                showParsePopup: false,
                isPagination: true,
                show_tb_filters: false,
                can_first_set_model: false,
                forbidden_columns: ['i_srv','i_favorite'],

                localMetaTable: new MetaTabldaTable({
                    table_id: this.stim_link_params.table_id,
                    user_id: this.$root.user.id,
                    user_hash: this.vhash,
                }),
                localAllRows: new MetaTabldaRows(this.stim_link_params, this.$root.app_stim_uh),

                old_found_model_row: null,
                editPopUpRow: null,
                popUpRole: null,
                editDisplaySettingsRow: null,
                showCopyPopup: false,

                linkPopups: [],
                keyIdx: 0,
                progress_rl: 0,

                filter_sizes:{
                    width: 220,
                    max_width: 450,
                    min_width: 150,
                },
            }
        },
        computed: {
            ...mapState({
                viewSettings: state => state.settings_3d,
                vuex_links: state => state.stim_links_container,
                vuex_stim_settings: state => state.stim_settings,
            }),
            vhash() {
                return this.$root.user.view_all ? this.$root.user.view_all.hash : null;
            },
            metaTable() {
                return this.foreign_meta_table || this.localMetaTable;
            },
            allRows() {
                return this.foreign_all_rows || this.localAllRows;
            },
            found_model_row() {
                return this.found_model.rows ? this.found_model.rows.master_row : null;
            },
            loaded_model() {
                return this.found_model.meta && this.found_model.meta.is_loaded
                    && this.found_model.rows && this.found_model.rows.is_loaded;
            },
            get_export_prefix() {
                if (this.metaTable.is_loaded && this.found_model) {
                    return this.found_model.selected_html;// + '_' + this.metaTable.params.name + '_';
                } else {
                    return '';
                }
            },
            master_stim_link() {
                let tbid = this.found_model && this.found_model.meta ? this.found_model.meta.table_id : null;
                return _.find(this.vuex_links, {table_id: tbid});
            },
        },
        props: {
            tb_id: String,
            is_showed: Boolean,
            sel_1_tab: String,
            sel_1_sub_tab: String,
            sel_2_tab: String,
            sel_2_sub_tab: String,
            show_type: String,
            adding_row: Object,
            stim_link_params: StimLinkParams,
            found_model: FoundModel,

            add_popup_handler_click: Number,
            insert_handler_click: Number,
            update_handler_click: Number,
            copy_rows_handler_click: Number,
            parse_paste_handler_click: Number,
            rts_popup_handler_click: Number,
            parse_sections_handler_click: Number,
            copy_from_model_handler_click: Number,
            fill_attachments_handler_click: Number,
            rl_calculation_handler_click: Number,

            foreign_meta_table: MetaTabldaTable,
            foreign_all_rows: MetaTabldaRows,
            master_table: Boolean,

            no_border: Boolean,
            wrap_class: String,
        },
        watch: {
            update_handler_click(val) {
                this.updateRow(this.allRows.master_row);
            },
            insert_handler_click(val) {
                this.insertRow(this.metaTable.empty_row);
            },
            add_popup_handler_click(val) {
                this.addPopupClickedHandler();
            },
            copy_rows_handler_click(val) {
                if (_.find(this.allRows.all_rows, {_checked_row: true})) {
                    this.showCopyPopup = true;
                } else {
                    Swal('Info','You should select some rows!');
                }
            },
            parse_paste_handler_click(val) {
                this.showParsePopup = true;
            },
            copy_from_model_handler_click(val) {
                this.showCopyFromModel = true;
            },
            fill_attachments_handler_click(val) {
                this.stimFillEqptAttachments();
            },
            rl_calculation_handler_click(val) {
                this.showRLCalcPopup = true;
            },
            rts_popup_handler_click(val) {
                if (_.find(this.allRows.all_rows, {_checked_row: true})) {
                    this.showRTSPopup = true;
                } else {
                    Swal('Info','You should select some rows!');
                }
            },
            parse_sections_handler_click(val) {
                this.stimSectionsParse();
            },
            is_showed: {
                handler(val) {
                    if (val) {
                        this.$root.sm_msg_type = 2;
                        this.metaTable.loadHeaders().then(() => {
                            this.emitPermissions();
                            let stfiwi = this.metaTable && this.metaTable.params && this.metaTable.params._cur_settings
                                ? this.metaTable.params._cur_settings.stim_filter_width
                                : null;
                            this.filter_sizes.width = Number(stfiwi)
                                || Number(readLocalStorage('local_stim_filter_width'))
                                || 220;

                        }).finally(() => {
                            this.$root.sm_msg_type = 0;
                        });
                    }
                    if (val && !this.allRows.is_loaded) {
                        this.loadAllRows();
                    }
                    if (val && this.allRows.is_loaded) {
                        this.local_req_params = this.allRows.rows_count ? this.allRows.rowsRequest(true) : {};//
                    }
                },
                immediate: true,
            },
            found_model_row: {
                handler(val) {
                    this.replaceInherits = this.stim_link_params.masterInherits(val || {});
                    //for not master table only
                    if (!this.master_table) {
                        //updated FoundModel
                        //REPLACE CHILDREN INHERITED COLUMNS on MASTER UPDATE
                        /*let old = this.old_found_model_row;
                        if (val && old && val.id == old.id) {
                            let fld, from, to;
                            _.each(this.stim_link_params.link_fields, (link) => {
                                if (val[link.link_field_db] !== old[link.link_field_db]) {
                                    fld = link.data_field;
                                    from = old[link.link_field_db];
                                    to = val[link.link_field_db];
                                }
                            });

                            if (fld) {
                                this.allRows.massReplace(fld, from, to, old, this.stim_link_params).then(() => {
                                    this.loadAllRows();
                                    this.adding_row.active = false;
                                });
                            }
                        }
                        //loaded new FoundModel
                        else {
                            this.loadAllRows();
                        }
                        this.old_found_model_row = _.cloneDeep(val);*/
                        this.loadAllRows();
                    }
                },
                deep: true,
            },
            adding_row: {
                handler(val) {
                    if (val && val.active) {
                        this.can_first_set_model = true;
                    }
                },
                deep: true,
            },
        },
        methods: {
            ...mapActions([
                'SET_SELECTED_MODEL_ROW'
            ]),
            ...mapMutations([
                'REDRAW_3D',
            ]),
            saveFltrSiz() {
                this.$root.changeStimFilterWi(this.filter_sizes.width, this.metaTable.params);
            },
            reshowComponent() {
                this.allshow = false;
                this.$nextTick(() => {
                    this.allshow = true;
                });
            },
            //EMIT PERMISSIONS
            emitPermissions() {
                this.$emit('meta-permissions', {
                    can_add: this.metaTable.can_add,
                    can_edit: this.metaTable.can_edit,
                    can_delete: this.metaTable.can_delete,
                    has_checked: this.allRows.all_rows && _.find(this.allRows.all_rows, {_checked_row: true}),
                    has_parse_paste: this.stim_link_params.app_table_options.indexOf('paste2import:true') > -1,
                    has_section_parse: this.stim_link_params.app_table_options.indexOf('section_parse:true') > -1,
                    has_search_block: this.stim_link_params.app_table_options.indexOf('search_block:true') > -1,
                    has_fill_attachments: this.stim_link_params.app_table_options.indexOf('fill_attachments:true') > -1,
                    has_rts: this.stim_link_params.app_table_options.indexOf('rts:true') > -1,
                    has_rl_calculator: this.stim_link_params.app_table_options.indexOf('rl_calc:true') > -1,
                    has_download: this.stim_link_params.download_active,
                    has_halfmoon: this.stim_link_params.halfmoon_active,
                    has_condformat: this.stim_link_params.condformat_active,
                    has_cellheight_btn: this.stim_link_params.cellheight_btn_active,
                    has_string_replace: this.stim_link_params.string_replace_active,
                    has_copy_childrene: this.stim_link_params.copy_childrene_active,
                    has_viewpop: this.stim_link_params.has_viewpop_active,
                    cur_page: this.allRows.page,
                });
            },

            //create empty row
            createEmptyRow() {
                this.can_first_set_model = true;
                let empty_row = this.metaTable.emptyObject();
                this.autoFillFromMasterTable(empty_row);
                return empty_row;
            },

            //ROWS
            loadAllRows(searchObj) {
                if (this.is_showed) {
                    if (!this.master_table) {
                        this.allRows.setModelAndGroup(this.found_model_row, this.stim_link_params);
                    }
                    this.allRowsLoadingProcess(searchObj);
                    if (!this.found_model._id) {
                        this.metaTable.empty_row = this.createEmptyRow();
                    }
                } else {
                    this.allRows.is_loaded = false;
                }
            },
            allRowsLoadingProcess(searchObj) {
                this.$root.sm_msg_type = 2;
                this.allRows.loadRows(this.metaTable.params, searchObj).finally(() => {
                    this.emitPermissions();
                    this.local_req_params = this.allRows.rows_count ? this.allRows.rowsRequest(true) : {};
                    this.$root.sm_msg_type = 0;
                });
            },
            //AUTOFILL ROW PARAMS
            autoFillFromMasterTable(tableRow) {
                if (this.found_model._id && !this.master_table) {
                    tableRow = this.stim_link_params.fillRowFromMaster(this.found_model_row, tableRow, this.can_first_set_model);
                    this.can_first_set_model = false;
                }
                let onedit = _.find(this.stim_link_params.on_edit_changers, {data_field: tableRow._changed_field});
                if (onedit) {
                    let fld = _.find(this.stim_link_params.app_fields, (fld) => {
                        return fld.app_field == onedit.notes;
                    });
                    if (fld) {
                        tableRow[fld.data_field] = fld.notes;
                    }
                }
            },
            //CHANGE ROWS//
            copyRow(tableRow) {
                this.insertRow(tableRow, true);
            },
            insertRow(tableRow, copy) {
                if (this.$root.setCheckRequired(this.metaTable.params, tableRow)) {

                    //save 3d settings when inserted new master record.
                    if (this.master_table) {
                        let user_key = _.find(this.stim_link_params.app_fields, {app_field: 'usergroup'});
                        let mod_key = _.find(this.stim_link_params.app_fields, {app_field: 'model'});
                        if (user_key && mod_key) {
                            this.viewSettings.usergroup = tableRow[user_key.data_field];
                            this.viewSettings.model = tableRow[mod_key.data_field];
                            this.viewSettings.saveSettings('clone');
                        }
                    }

                    this.autoFillFromMasterTable(tableRow);
                    let row_3d = this.allRows.convertOne(tableRow);
                    this.$root.sm_msg_type = 1;
                    this.allRows.insertRow(tableRow, undefined, copy).then((data) => {
                        data.rows[0]._is_new = 1;
                        if (row_3d && data && data.rows) {
                            row_3d._id = data.rows[0].id;
                        }
                        this.metaTable.empty_row = this.createEmptyRow();
                        this.$emit('row-inserted', data);
                    }).finally(() => {
                        this.$root.sm_msg_type = 0;
                    });
                }
            },
            massUpdatedRows(massTableRows, top_changed) {
                let can = true;
                _.each(massTableRows, (tableRow) => {
                    can = can && this.$root.setCheckRequired(this.metaTable.params, tableRow);
                    this.autoFillFromMasterTable(tableRow);
                });

                if (can) {
                    this.$root.sm_msg_type = 1;
                    this.$root.prevent_cell_edit = true;
                    this.allRows.massUpdatedRows(this.metaTable.params, massTableRows, top_changed, top_changed).then((data) => {
                        if (!top_changed) {
                            let is3dLC = ThreeHelper.watched3d_is_needed_action(this.stim_link_params.app_fields, massTableRows);
                            if (is3dLC) {
                                this.REDRAW_3D('soft');
                            } else {
                                this.$emit('row-updated', data);
                            }
                        }
                    }).finally(() => {
                        this.$root.sm_msg_type = 0;
                        this.$root.prevent_cell_edit = false;
                    });

                    if (top_changed) {
                        this.found_model.setSelectedRow(null);
                        this.SET_SELECTED_MODEL_ROW(null);
                    }
                }
            },
            updateRow(tableRow, changefFld, top_changed) {
                this.massUpdatedRows([tableRow], top_changed);
            },
            deleteRow(tableRow) {
                this.$root.sm_msg_type = 1;
                this.allRows.deleteRow(tableRow).then((data) => {
                    this.$emit('row-deleted', data);
                }).finally(() => {
                    this.$root.sm_msg_type = 0;
                });
            },

            //SORTING
            sortByFieldTablda(tableHeader, $dir, $sub) {
                this.allRows.sort = this.sortByFieldWrows(this.allRows.sort, tableHeader, $dir, $sub);
                this.getTableData('sort');
            },
            subSortByFieldTablda(tableHeader, $dir) {
                this.allRows.sort = this.subSortByFieldWrows(this.allRows.sort, tableHeader, $dir);
                this.getTableData('sort');
            },
            changePage(page) {
                this.allRows.page = page;
                this.getTableData('page');
            },

            //ROW POPUP
            rowIndexClicked(index) {
                this.getEditPopupRow(index);
                this.popUpRole = 'update';
            },
            closePopUp() {
                this.editPopUpRow = null;
            },
            getEditPopupRow(index) {
                this.editPopUpRow = this.allRows.getRow(index);
            },
            addPopupClickedHandler() {
                this.can_first_set_model = true;
                this.editPopUpRow = this.createEmptyRow();
                this.popUpRole = 'add';
            },
            anotherRowPopup(is_next) {
                let row_id = (this.editPopUpRow ? this.editPopUpRow.id : null);
                this.$root.anotherPopup(this.allRows.all_rows, row_id, is_next, this.rowIndexClicked);
            },

            //CHECK ROWS AND DELETE
            deleteSelectedRowsHandler() {
                this.deleteSelectedRowsMix(this.allRows.all_rows, this.allRows.rowsRequest(), this.metaTable.table_id, this.allRows.rows_count);
            },
            toggleAllCheckBoxes(field, status, ids) {
                this.toggleAllCheckMix(this.allRows.all_rows, this.metaTable.params, field, status, ids);
            },

            //VERTICAL TABLE
            recheckBacknd(table_id) {
                if (table_id && table_id != this.metaTable.table_id) {
                    return;
                }
                this.checkRowBknd();
            },
            checkRowBknd() {
                if (this.allRows.is_loaded) {
                    if (Number(this.verticalRow.id)) {
                        this.autoFillFromMasterTable(this.verticalRow);
                        if (this.metaTable.can_edit && this.allRows.master_row) {
                            let top_changed = _.find(this.stim_link_params.app_fields, (app) => {
                                return app.options.match(/display_top:true/gi) && app.data_field === this.verticalRow._changed_field;
                            });
                            this.updateRow(this.allRows.master_row, null, top_changed);
                        }
                    } else {
                        let promise = this.checkRowOnBackend(this.metaTable.table_id, this.verticalRow);
                        if (promise) {
                            promise.then((data) => {
                                this.autoFillFromMasterTable(this.verticalRow);
                                if (this.metaTable.can_edit && this.allRows.master_row) {//Update in master and usual tables
                                    this.updateRow(this.allRows.master_row);
                                }
                                if (this.metaTable.can_edit && !this.allRows.master_row && !this.master_table && this.found_model_row) {//Auto save in usual table
                                    this.insertRow(this.verticalRow);
                                }
                                if (!this.allRows.master_row && this.master_table) {//For model 3D drawing in new master row
                                    this.allRows.set3DTempRow(this.verticalRow);
                                    this.$emit('new-row-changed', this.verticalRow);
                                }
                            });
                        }
                    }
                }
            },

            //CUSTOM TABLE OR POPUP
            afterBackendCheck(tableRow, data) {
                this.autoFillFromMasterTable(tableRow);
            },

            //SETTINGS POPUP
            showHeaderSettings(tableHeader) {
                if (!isNaN(tableHeader)) {
                    this.editDisplaySettingsRow = this.metaTable.params._fields[tableHeader];
                } else {
                    this.editDisplaySettingsRow = tableHeader;
                }
            },
            closeHeaderSettings(tableHeader) {
                this.editDisplaySettingsRow = null;
            },
            anotherSettingsRowPopup(is_next) {
                let row_id = (this.editDisplaySettingsRow ? this.editDisplaySettingsRow.id : null);
                this.$root.anotherPopup(this.metaTable.params._fields, row_id, is_next, this.showHeaderSettings);
            },
            updateDisplayRow(tableRow) {
                this.$root.sm_msg_type = 1;
                this.metaTable.updateSettings(tableRow).finally(() => {
                    this.$root.sm_msg_type = 0;
                    this.allRowsLoadingProcess();
                });
            },

            //TableCheckboxesMixin.vue AND TableSortMixin.vue
            getTableData(type) {
                this.allRows.rows_per_page = this.metaTable.params.rows_per_page;
                if (type === 'sort' && this.allRows.rows_count < this.allRows.rows_per_page) {
                    this.allRows.localSort();
                } else {
                    this.allRowsLoadingProcess();
                }
            },

            //FILTERS
            changedFilter() {
                this.allRowsLoadingProcess();
                this.$emit('reload-3d', 'soft');
            },
            applyCombo(combo) {
                this.allRows.filters = combo;
                this.changedFilter();
            },

            //LINK POPUPS
            showSrcRecord(lnk, header, tableRow) {
                let index = this.linkPopups.filter((el) => {return el.key === 'show'}).length;
                let ava_ref_condition = _.find(this.metaTable.params._ref_conditions, {id: Number(lnk.table_ref_condition_id)});
                let stim_link = ava_ref_condition && this.vuex_links
                    ? _.find(this.vuex_links, {table_id: Number(ava_ref_condition.ref_table_id)})
                    : null;
                this.linkPopups.push({
                    key: 'show',
                    index: index,
                    link: lnk,
                    header: header,
                    row: tableRow,
                    behavior: 'list_view',
                    avail_columns: stim_link ? stim_link.avail_cols_for_app : undefined,
                });
            },
            closeLinkPopup(idx, should_update, table_id) {
                if (idx > -1) {
                    this.linkPopups[idx] = { key: 'hide' };
                    this.$forceUpdate();
                    eventBus.$emit('cell-app-has-been-closed', table_id);//update 3d model and other tabs if something was changed in LinkPopup.
                }
            },

            //COPY AND REPLACE POPUPS
            afterCopied(data, all_rows_checked) {
                if (all_rows_checked) {
                    this.allRowsLoadingProcess();
                    this.showCopyPopup = false;
                }
                this.$emit('row-inserted', data);
            },
            parseOrCopyCompleted() {
                this.loadAllRows();
                this.showParsePopup = false;
            },

            //RTS Popup
            rtsClosed() {
                this.showRTSPopup = false;
                this.loadAllRows();
            },
            rtsCompleted() {
                this.allRowsLoadingProcess();
                this.$emit('reload-3d', 'soft');
            },

            //RTS Popup AND RL Calcs Popup
            copyFromModelClosed() {
                this.showCopyFromModel = false;
                this.showRLCalcPopup = false;
            },
            copyFromModelFinished() {
                this.showCopyFromModel = false;
                this.showRLCalcPopup = false;
                this.allRowsLoadingProcess();
                this.$emit('reload-3d', 'soft');
            },

            //SectionsParse
            stimSectionsParse() {
                $.LoadingOverlay('show');

                let request_params = this.allRows.rowsRequest();
                request_params.rows_per_page = 0;

                axios.post('?method=do_sec_parse', {
                    app_table: this.stim_link_params.app_table,
                    request_params: request_params,
                }).then(({data}) => {
                    this.allRowsLoadingProcess();
                    this.$emit('reload-3d', 'soft');
                }).catch(errors => {
                    Swal('Info', getErrors(errors));
                }).finally(() => {
                    $.LoadingOverlay('hide');
                });
            },

            //FillEqptAttachments
            stimFillEqptAttachments() {
                $.LoadingOverlay('show');

                let request_params = this.allRows.rowsRequest();
                request_params.rows_per_page = 0;

                axios.post('?method=eqpt_fill_attachments', {
                    app_table: this.stim_link_params.app_table,
                    request_params: request_params,
                }).then(({data}) => {
                    this.allRowsLoadingProcess();
                    this.$emit('reload-3d', 'soft');
                }).catch(errors => {
                    Swal('Info', getErrors(errors));
                }).finally(() => {
                    $.LoadingOverlay('hide');
                });
            },

            //keydowns
            globalKeyHandler(e) {
                if (['INPUT', 'TEXTAREA'].indexOf(e.target.nodeName) === -1) {//target not in Input
                    let cmdOrCtrl = e.metaKey || e.ctrlKey;
                    if (cmdOrCtrl) {
                        if ([40,34].indexOf(e.keyCode) > -1) {//ctrl + down arrow or pgdn
                            this.isPagination = !this.isPagination;
                        }
                    }
                }
            },

            //closed popup application -> reload all rows.
            appClosedReloadTabRows(table_id, app_name) {
                if (this.stim_link_params.table_id === table_id) {
                    this.loadAllRows();
                    this.$emit('reload-3d', 'soft');
                } else {
                    this.allRows.is_loaded = false;
                }
            },

            //search word changed -> reload all rows.
            checkTableAndReloadRows(table_id, searchObj) {
                if (this.stim_link_params.table_id === table_id) {
                    this.loadAllRows(searchObj);
                }
            },

            //autoload new data
            intervalTickHandler() {
                if (this.metaTable.is_loaded && this.allRows.is_loaded && !this.$root.sm_msg_type) {
                    let list_row_ids = _.map(this.allRows.all_rows, 'id');
                    axios.post('/ajax/table/version_hash', {
                        table_id: this.metaTable.params.id,
                        row_list_ids: list_row_ids,
                        row_fav_ids: [],
                    }).then(({ data }) => {
                        let changed = this.metaTable.params.version_hash !== data.version_hash;
                        if (changed) {
                            this.metaTable.params.version_hash = data.version_hash;
                            this.loadAllRows();
                        }
                        if (data.job_msg) {
                            Swal('Info', data.job_msg);
                        }
                    });
                }
            },

            //popup
            showAddDDLOption(tableHeader, tableRow) {
                this.addOptionPopup = {
                    show: true,
                    tableHeader: tableHeader,
                    tableRow: tableRow,
                };
            },
        },
        mounted() {
            if (this.stim_link_params.app_table_options.indexOf('del_icon:hide') > -1) {
                this.forbidden_columns.push('i_remove');
            }
            if (this.stim_link_params.app_table_options.indexOf('check_icon:hide') > -1) {
                this.forbidden_columns.push('i_check');
            }

            //sync datas with collaborators
            setInterval(() => {
                if (!localStorage.getItem('no_ping') && this.is_showed) {
                    this.intervalTickHandler();
                }
            }, this.$root.version_hash_delay);

            eventBus.$on('recheck-backend', this.checkRowBknd);
            eventBus.$on('global-keydown', this.globalKeyHandler);
            eventBus.$on('reload-filters', this.checkTableAndReloadRows);
            eventBus.$on('cell-app-has-been-closed', this.appClosedReloadTabRows);
            eventBus.$on('stim-search-word-changed', this.checkTableAndReloadRows);
        },
        beforeDestroy() {
            eventBus.$off('recheck-backend', this.checkRowBknd);
            eventBus.$off('global-keydown', this.globalKeyHandler);
            eventBus.$off('reload-filters', this.checkTableAndReloadRows);
            eventBus.$off('cell-app-has-been-closed', this.appClosedReloadTabRows);
            eventBus.$off('stim-search-word-changed', this.checkTableAndReloadRows);
        }
    }
</script>

<style lang="scss" scoped>
    .tablda-table-wrapper {
        .popup-wrapper {
            z-index: 12000;
        }

        .filters-wrapper {
            position: relative;
            width: 220px;
            flex-shrink: 0;
            padding: 5px;
            border: 1px solid #CCC;

            .filters-wrapper--head {
                position: relative;
                height: 30px;
                font-size: 18px;
                font-weight: bold;
                text-align: center;
                background-color: #DDD;
                border: 1px solid #AAA;

                .glyphicon {
                    position: absolute;
                    right: 0;
                    top: 5px;
                    cursor: pointer;
                    z-index: 1700;
                }
            }

            .filters-wrapper--body {
                height: calc(100% - 30px);
                position: relative;
            }
        }
    }
</style>