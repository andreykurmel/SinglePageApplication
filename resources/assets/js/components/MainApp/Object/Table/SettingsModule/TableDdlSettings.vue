<template>
    <div class="full-height" :style="sysStyleWiBg">
        <div class="full-height permissions-tab">

            <div class="permissions-panel full-height" :style="$root.themeMainBgStyle">
                <div class="permissions-menu-header m-5">
                    <button class="btn btn-default h36" :style="textSysStyle" :class="{active : activeTab === 'list'}" @click="activeTab = 'list'">
                        List
                    </button>
                    <button class="btn btn-default h36" :style="textSysStyle" :class="{active : activeTab === 'options'}" @click="activeTab = 'options'">
                        Options
                    </button>

                    <div class="flex flex--center-v flex--end" style="position: absolute; top: 0; right: 0; height: 32px; width: 50%;">

                        <button v-show="activeTab === 'list'"
                                class="btn btn-default btn-sm blue-gradient"
                                :style="$root.themeButtonStyle"
                                style="float: right;padding: 0 3px;"
                                @click="show_auto_ddl = true"
                                title="Copy DDL from Tables">Auto DDL</button>

                        <button v-show="activeTab === 'list'"
                                class="btn btn-default btn-sm blue-gradient"
                                :style="$root.themeButtonStyle"
                                style="float: right;padding: 0 3px;"
                                @click="show_copy_ddl = true"
                                title="Copy DDL from Tables">Copy</button>

                        <label v-if="sel_ddl" class="flex flex--center ml15 mr0" style="margin: 0 0 0 5px;white-space: nowrap;width: 300px;" :style="textSysStyleSmart">
                            Loaded DDL:&nbsp;
                            <select-block
                                :options="ddlOpts()"
                                :sel_value="sel_ddl.id"
                                :style="{ maxWidth:'200px', height:'32px', }"
                                @option-select="ddlChange"
                            ></select-block>
                        </label>

                        <info-sign-link v-show="activeTab === 'list'"
                                        class="ml5 mr5"
                                        :app_sett_key="'help_link_settings_ddls'"
                                        :hgt="30"
                                        :txt="'for Settings/DDL/List'"
                        ></info-sign-link>
                        <info-sign-link v-show="activeTab === 'options'"
                                        class="ml5 mr5"
                                        :app_sett_key="'help_link_settings_ddls_options'"
                                        :hgt="30"
                                        :txt="'for Settings/DDL/Options'"
                        ></info-sign-link>
                    </div>
                </div>
                <div class="permissions-menu-body" style="border: 1px solid #CCC;">

                    <div v-show="activeTab === 'list'" class="permissions-panel full-height no-padding">
                        <custom-table
                            v-if="draw_table"
                            :cell_component_name="'custom-cell-settings-ddl'"
                            :global-meta="tableMeta"
                            :table-meta="$root.settingsMeta['ddl']"
                            :all-rows="tableMeta._ddls"
                            :rows-count="tableMeta._ddls.length"
                            :cell-height="1"
                            :max-cell-rows="0"
                            :is-full-width="true"
                            :adding-row="addingRow"
                            :behavior="'settings_ddl'"
                            :user="user"
                            :selected-row="selectedDDL"
                            :available-columns="$root.user.is_admin
                                ? ['name','datas_sort','keep_sort_order','ignore_lettercase','owner_shared','admin_public','_unit_uses','_input_uses','notes']
                                : ['name','datas_sort','keep_sort_order','ignore_lettercase','owner_shared','_unit_uses','_input_uses','notes']"
                            :use_theme="true"
                            @added-row="addDDLRow"
                            @updated-row="updateDDLRow"
                            @delete-row="deleteDDLRow"
                            @reorder-rows="rowsReordered"
                            @row-index-clicked="rowIndexClickedDDL"
                        ></custom-table>
                    </div>

                    <div v-show="activeTab === 'options'" class="permissions-panel full-height no-padding">
                        <div class="full-frame ddl-panel">
                            <custom-table
                                v-if="sel_ddl && sel_ddl._references"
                                :cell_component_name="'custom-cell-settings-ddl'"
                                :global-meta="tableMeta"
                                :table-meta="$root.settingsMeta['ddl_references']"
                                :settings-meta="$root.settingsMeta"
                                :all-rows="sel_ddl._references || []"
                                :rows-count="sel_ddl._references.length || 0"
                                :cell-height="1"
                                :max-cell-rows="0"
                                :is-full-width="true"
                                :adding-row="addingRefRow"
                                :behavior="'settings_ddl_items'"
                                :user="user"
                                :forbidden-columns="forbidCol"
                                :parent-row="sel_ddl"
                                :use_theme="true"
                                :with_edit="!!$root.checkAvailable($root.user, 'ddl_ref')"
                                :widths_div="1.5"
                                @added-row="addReferenceRow"
                                @updated-row="updateReferenceRow"
                                @delete-row="deleteReferenceRow"
                                @show-add-ref-cond="showAddRefCond"
                            ></custom-table>
                        </div>

                        <div v-if="!sel_ddl" style="height: 30px;"></div>
                        <div v-if="sel_ddl" class="left-elem field-loader-wrapper">
                            <div class="field-loader">
                                <select class="form-control field-selector"
                                        v-model="sel_ddl.items_pos"
                                        @change="updateDDLRow(sel_ddl, 'no')"
                                >
                                    <option value="before">Before</option>
                                    <option value="after">After</option>
                                </select>
                            </div>
                            <div class="field-loader" style="width: auto">
                                <label :style="textSysStyleSmart">RC-Based Items</label>
                            </div>
                        </div>
                        <div v-if="sel_ddl" class="right-elem field-loader-wrapper">
                            <div class="field-loader-bg">
                                <select-with-folder-structure
                                    :cur_val="table_for_load_ddl"
                                    :available_tables="$root.settingsMeta.available_tables"
                                    :user="user"
                                    @sel-changed="tablePdvChanged"
                                    class="form-control">
                                </select-with-folder-structure>
                            </div>
                            <div class="field-loader">
                                <select class="form-control field-selector" v-model="field_for_load_ddl">
                                    <option v-for="tableHeader in pdv_ddl_fields" :value="tableHeader.id">{{ tableHeader.name }}</option>
                                </select>
                            </div>
                            <button class="btn btn-default btn-sm blue-gradient"
                                    :style="$root.themeButtonStyle"
                                    @click="loadDDLOptions()"
                                    title="Populate Distinctive Value"
                            >PDV</button>

                            <button class="btn btn-default btn-sm blue-gradient"
                                    :style="$root.themeButtonStyle"
                                    @click="importRegular = true"
                            >Import</button>

                            <button class="btn btn-default btn-sm blue-gradient"
                                    :style="$root.themeButtonStyle"
                                    @click="setAutoColors()"
                            >Auto Color</button>
                        </div>

                        <div class="full-frame ddl-panel">
                            <custom-table
                                v-if="sel_ddl && sel_ddl._items"
                                :cell_component_name="'custom-cell-settings-ddl'"
                                :global-meta="tableMeta"
                                :table-meta="$root.settingsMeta['ddl_items']"
                                :all-rows="sel_ddl._items || []"
                                :rows-count="sel_ddl._items.length || 0"
                                :cell-height="1"
                                :max-cell-rows="0"
                                :is-full-width="true"
                                :adding-row="addingRow"
                                :behavior="'settings_ddl_items'"
                                :user="user"
                                :forbidden-columns="forbidCol"
                                :parent-row="sel_ddl"
                                :use_theme="true"
                                :widths_div="2"
                                @added-row="addItemRow"
                                @updated-row="updateItemRow"
                                @delete-row="deleteItemRow"
                                @show-add-ref-cond="showAddRefCond"
                            ></custom-table>
                        </div>
                    </div>
                </div>
            </div>

            <!--Import regular ddl options-->
            <ddl-paste-options-popup
                v-if="importRegular"
                @parse-options="parseImport"
                @popup-close="importRegular = false"
            ></ddl-paste-options-popup>

        </div>

        <copy-ddl-from-table-popup
                v-if="show_copy_ddl"
                :table-meta="tableMeta"
                @popup-close="show_copy_ddl = false"
        ></copy-ddl-from-table-popup>

        <auto-ddl-creation-popup
                v-if="show_auto_ddl"
                :table-meta="tableMeta"
                :request-params="$root.request_params"
                @popup-close="creationClosed"
        ></auto-ddl-creation-popup>
    </div>
</template>

<script>
    import {eventBus} from '../../../../../app';

    import {RefCondHelper} from "../../../../../classes/helpers/RefCondHelper";
    import {DDLHelper} from "../../../../../classes/helpers/DDLHelper";
    import {Endpoints} from "../../../../../classes/Endpoints";

    import CellStyleMixin from "../../../../_Mixins/CellStyleMixin";

    import CustomTable from '../../../../CustomTable/CustomTable';
    import SelectWithFolderStructure from "../../../../CustomCell/InCell/SelectWithFolderStructure";
    import InfoSignLink from "../../../../CustomTable/Specials/InfoSignLink";
    import CopyDdlFromTablePopup from "../../../../CustomPopup/CopyDdlFromTablePopup";
    import SelectBlock from "../../../../CommonBlocks/SelectBlock";
    import DdlPasteOptionsPopup from "../../../../CustomPopup/DdlPasteOptionsPopup";
    import AutoDdlCreationPopup from "../../../../CustomPopup/AutoDdlCreationPopup.vue";

    export default {
        name: "TableDdlSettings",
        components: {
            AutoDdlCreationPopup,
            DdlPasteOptionsPopup,
            SelectBlock,
            CopyDdlFromTablePopup,
            InfoSignLink,
            SelectWithFolderStructure,
            CustomTable,
        },
        mixins: [
            CellStyleMixin,
        ],
        data: function () {
            return {
                draw_table: true,
                activeTab: 'list',
                show_auto_ddl: false,
                show_copy_ddl: false,
                selectedDDL: this.init_ddl_idx,
                addingRow: {
                    active: true,
                    position: 'bottom'
                },
                table_for_load_ddl: this.tableMeta.id,
                field_for_load_ddl: null,
                pdv_ddl_fields: [],
                importRegular: false,
            }
        },
        computed: {
            forbidCol() {
                let forbid = _.concat(['notes'], this.$root.systemFields);
                if (this.sel_ddl.owner_shared || this.sel_ddl.admin_public) {
                    forbid.push('apply_target_row_group_id');
                }
                return forbid;
            },
            addingRefRow() {
                return {
                    active: true,
                    position: 'bottom'
                }
            },
            sel_ddl() {
                return this.tableMeta._ddls[this.selectedDDL];
            },
            sysStyleWiBg() {
                return {
                    ...this.textSysStyle,
                    ...this.$root.themeMainBgStyle,
                };
            },
        },
        props: {
            tableMeta: Object,
            user:  Object,
            table_id: Number,
            init_ddl_idx: {
                type: Number,
                default: 0,
            },
            redraw: Boolean,
        },
        watch: {
            redraw(val) {
                if (val) {
                    this.redrawTb();
                }
            },
            table_id: function(val) {
                this.selectedDDL = -1;
                this.tablePdvChanged(val);
            }
        },
        methods: {
            ddlOpts() {
                return _.map(this.tableMeta._ddls, (refc) => {
                    return { val:refc.id, show:refc.name };
                });
            },
            ddlChange(opt) {
                this.selectedDDL = _.findIndex(this.tableMeta._ddls, {id: Number(opt.val)});
            },
            tablePdvChanged(val) {
                this.table_for_load_ddl = val;
                this.field_for_load_ddl = null;
                this.load_pdv_ddl_fields();
            },
            load_pdv_ddl_fields() {
                axios.get('/ajax/settings/table-fields', {
                    params: {
                        table_id: this.table_for_load_ddl
                    }
                }).then(({ data }) => {
                    this.pdv_ddl_fields = data;
                }).catch(errors => {
                    Swal('Info', getErrors(errors));
                });
            },
            //DDL Functions
            addDDLRow(tableRow, referenceRow) {
                this.$root.sm_msg_type = 1;

                let fields = _.cloneDeep(tableRow);//copy object
                this.$root.deleteSystemFields(fields);

                axios.post('/ajax/ddl', {
                    table_id: this.tableMeta.id,
                    fields: fields
                }).then(({ data }) => {
                    DDLHelper.setUses(this.tableMeta, data);
                    this.tableMeta._ddls = data;
                    this.selectedDDL = this.tableMeta._ddls.length-1;

                    if (referenceRow) {
                        let ddl = _.find(this.tableMeta._ddls, {name: tableRow.name});
                        if (ddl) {
                            referenceRow.ddl_id = ddl.id;
                            this.addReferenceRow(referenceRow);
                        }
                    }
                }).catch(errors => {
                    Swal('Info', getErrors(errors));
                }).finally(() => {
                    this.$root.sm_msg_type = 0;
                });
            },
            updateDDLRow(tableRow, noselect) {
                this.$root.sm_msg_type = 1;

                let row_id = tableRow.id;
                let fields = _.cloneDeep(tableRow);//copy object
                this.$root.deleteSystemFields(fields);

                let changedShared = tableRow._changed_field === 'owner_shared' || tableRow._changed_field === 'admin_public';
                let hasNotStaticRef = _.find(tableRow._references || [], (ref) => {
                    let refCond = _.find(this.tableMeta._ref_conditions, {id: Number(ref.table_ref_condition_id)});
                    return refCond && !refCond.rc_static;
                });
                if (changedShared && hasNotStaticRef) {
                    Swal('Info', 'Note: not STATIC RefConditions are not available for SHARED DDL. They were removed.');
                }

                axios.put('/ajax/ddl', {
                    table_id: this.tableMeta.id,
                    ddl_id: row_id,
                    fields: fields
                }).then(({ data }) => {
                    DDLHelper.setUses(this.tableMeta, data);
                    this.tableMeta._ddls = data;
                    if (!noselect) {
                        this.selectedDDL = -1;
                    }
                    if (changedShared) {
                        Endpoints.reloadSharedDDLs();
                    }
                }).catch(errors => {
                    Swal('Info', getErrors(errors));
                }).finally(() => {
                    this.$root.sm_msg_type = 0;
                });
            },
            deleteDDLRow(tableRow) {
                this.$root.sm_msg_type = 1;
                let ddl_id = tableRow.id;
                axios.delete('/ajax/ddl', {
                    params: {
                        table_id: this.tableMeta.id,
                        ddl_id: ddl_id
                    }
                }).then(({ data }) => {
                    DDLHelper.setUses(this.tableMeta, data);
                    this.tableMeta._ddls = data;
                    this.selectedDDL = -1;
                    RefCondHelper.setUses(this.tableMeta);
                }).catch(errors => {
                    Swal('Info', getErrors(errors));
                }).finally(() => {
                    this.$root.sm_msg_type = 0;
                });
            },
            rowsReordered() {
                this.$root.sm_msg_type = 2;
                axios.get('/ajax/settings/load/ddls', {
                    params: { table_id: this.tableMeta.id }
                }).then(({ data }) => {
                    DDLHelper.setUses(this.tableMeta, data);
                    this.tableMeta._ddls = data;
                    this.redrawTb();
                }).catch(errors => {
                    Swal('Info', getErrors(errors));
                }).finally(() => {
                    this.$root.sm_msg_type = 0;
                });
            },
            redrawTb() {
                this.draw_table = false;
                this.$nextTick(() => {
                    this.draw_table = true;
                });
            },
            rowIndexClickedDDL(index) {
                this.selectedDDL = index;
                this.activeTab = 'options';
            },

            //DDL Items Functions
            addItemRow(tableRow) {
                this.$root.sm_msg_type = 1;

                let fields = _.cloneDeep(tableRow);//copy object
                this.$root.deleteSystemFields(fields);

                axios.post('/ajax/ddl/item', {
                    table_id: this.tableMeta.id,
                    ddl_id: this.sel_ddl.id,
                    fields: fields
                }).then(({ data }) => {
                    this.tableMeta._ddls[this.selectedDDL]._items = data;
                }).catch(errors => {
                    Swal('Info', getErrors(errors));
                }).finally(() => {
                    this.$root.sm_msg_type = 0;
                });
            },
            updateItemRow(tableRow) {
                this.$root.sm_msg_type = 1;

                let row_id = tableRow.id;
                let fields = _.cloneDeep(tableRow);//copy object
                this.$root.deleteSystemFields(fields);

                axios.put('/ajax/ddl/item', {
                    table_id: this.tableMeta.id,
                    ddl_id: this.sel_ddl.id,
                    ddl_item_id: row_id,
                    fields: fields
                }).then(({ data }) => {
                    this.tableMeta._ddls[this.selectedDDL]._items = data;
                    if (tableRow._changed_field === 'opt_color') {
                        eventBus.$emit('reload-page');
                    }
                }).catch(errors => {
                    Swal('Info', getErrors(errors));
                }).finally(() => {
                    this.$root.sm_msg_type = 0;
                });
            },
            deleteItemRow(tableRow) {
                this.$root.sm_msg_type = 1;
                let ddl_id = tableRow.id;
                axios.delete('/ajax/ddl/item', {
                    params: {
                        table_id: this.tableMeta.id,
                        ddl_id: this.sel_ddl.id,
                        ddl_item_id: ddl_id
                    }
                }).then(({ data }) => {
                    this.tableMeta._ddls[this.selectedDDL]._items = data;
                }).catch(errors => {
                    Swal('Info', getErrors(errors));
                }).finally(() => {
                    this.$root.sm_msg_type = 0;
                });
            },

            //DDL Reference Functions
            addReferenceRow(tableRow) {
                this.$root.sm_msg_type = 1;

                let fields = _.cloneDeep(tableRow);//copy object
                this.$root.deleteSystemFields(fields);

                axios.post('/ajax/ddl/reference', {
                    table_id: this.tableMeta.id,
                    ddl_id: this.sel_ddl.id,
                    fields: fields
                }).then(({ data }) => {
                    this.tableMeta._ddls[this.selectedDDL]._references = data;
                    RefCondHelper.setUses(this.tableMeta);
                }).catch(errors => {
                    Swal('Info', getErrors(errors));
                }).finally(() => {
                    this.$root.sm_msg_type = 0;
                });
            },
            eventCreateDDLAndReferenceRow(table_id, tableRow, referenceRow) {
                if (this.tableMeta.id == table_id) {
                    this.addDDLRow(tableRow, referenceRow);
                }
            },
            eventUpdateReferenceRow(table_id, tableRow) {
                if (this.tableMeta.id == table_id) {
                    this.selectedDDL = _.findIndex(this.tableMeta._ddls, {id: tableRow.ddl_id});
                    this.updateReferenceRow(tableRow);
                }
            },
            updateReferenceRow(tableRow) {
                this.$root.sm_msg_type = 1;

                let row_id = tableRow.id;
                let fields = _.cloneDeep(tableRow);//copy object
                this.$root.deleteSystemFields(fields);
                RefCondHelper.setUses(this.tableMeta);

                axios.put('/ajax/ddl/reference', {
                    table_id: this.tableMeta.id,
                    ddl_id: this.sel_ddl.id,
                    ddl_ref_id: row_id,
                    fields: fields
                }).then(({ data }) => {
                    this.tableMeta._ddls[this.selectedDDL]._references = data;
                    if ($.inArray(tableRow._changed_field, ['show_field_id','image_field_id','color_field_id']) > -1)
                    {
                        eventBus.$emit('reload-page', this.tableMeta.id);
                    }
                }).catch(errors => {
                    Swal('Info', getErrors(errors));
                }).finally(() => {
                    this.$root.sm_msg_type = 0;
                });
            },
            eventDeleteReferenceRow(table_id, tableRow) {
                if (this.tableMeta.id == table_id) {
                    this.selectedDDL = _.findIndex(this.tableMeta._ddls, {id: tableRow.ddl_id});
                    this.deleteReferenceRow(tableRow);
                }
            },
            deleteReferenceRow(tableRow) {
                this.$root.sm_msg_type = 1;
                let ddl_id = tableRow.id;
                axios.delete('/ajax/ddl/reference', {
                    params: {
                        table_id: this.tableMeta.id,
                        ddl_id: this.sel_ddl.id,
                        ddl_ref_id: ddl_id
                    }
                }).then(({ data }) => {
                    this.tableMeta._ddls[this.selectedDDL]._references = data;
                    RefCondHelper.setUses(this.tableMeta);
                }).catch(errors => {
                    Swal('Info', getErrors(errors));
                }).finally(() => {
                    this.$root.sm_msg_type = 0;
                });
            },

            //Other
            showAddRefCond(refId) {
                eventBus.$emit('show-ref-conditions-popup', this.tableMeta.db_name, refId);
            },
            isShowedToggled(table_field_ids, status) {
                if (this.user.id) {
                    axios.put('/ajax/settings/show-columns-toggle', {
                        table_field_ids: table_field_ids,
                        is_showed: status ? 1 : 0
                    }).then(({ data }) => {
                    }).catch(errors => {
                        Swal('Info', getErrors(errors));
                    });
                }
            },
            loadDDLOptions() {
                if (this.table_for_load_ddl && this.field_for_load_ddl && this.selectedDDL > -1) {
                    this.$root.sm_msg_type = 2;
                    axios.post('/ajax/ddl/fill-from-field', {
                        table_id: this.table_for_load_ddl,
                        table_field_id: this.field_for_load_ddl,
                        ddl_id: this.sel_ddl.id,
                    }).then(({ data }) => {
                        this.tableMeta._ddls[this.selectedDDL]._items = data;
                    }).catch(errors => {
                        Swal('Info', getErrors(errors));
                    }).finally(() => {
                        this.$root.sm_msg_type = 0;
                    });
                }
            },
            setAutoColors() {
                this.$root.sm_msg_type = 1;
                axios.post('/ajax/ddl/item/fill-colors', {
                    ddl_id: this.sel_ddl.id,
                }).then(({ data }) => {
                    this.tableMeta._ddls[this.selectedDDL]._items = data;
                }).catch(errors => {
                    Swal('Info', getErrors(errors));
                }).finally(() => {
                    this.$root.sm_msg_type = 0;
                });
            },
            parseImport(parseImportOptions) {
                if (parseImportOptions && this.selectedDDL > -1) {
                    this.$root.sm_msg_type = 1;
                    axios.post('/ajax/ddl/parse-options', {
                        options: parseImportOptions,
                        ddl_id: this.sel_ddl.id,
                    }).then(({ data }) => {
                        this.tableMeta._ddls[this.selectedDDL]._items = data;
                        this.importRegular = false;
                    }).catch(errors => {
                        Swal('Info', getErrors(errors));
                    }).finally(() => {
                        this.$root.sm_msg_type = 0;
                    });
                }
            },
            creationClosed(changed) {
                if (this.changed) {
                    this.activeTab = 'list';
                    this.selectedDDL = this.init_ddl_idx;
                }
                this.show_auto_ddl = false;
            },
        },
        mounted() {
            this.load_pdv_ddl_fields();

            eventBus.$on('event-create-ddl-and-reference-row', this.eventCreateDDLAndReferenceRow);
            eventBus.$on('event-update-ddl-reference-row', this.eventUpdateReferenceRow);
            eventBus.$on('event-delete-ddl-reference-row', this.eventDeleteReferenceRow);
        },
        beforeDestroy() {
            eventBus.$off('event-create-ddl-and-reference-row', this.eventCreateDDLAndReferenceRow);
            eventBus.$off('event-update-ddl-reference-row', this.eventUpdateReferenceRow);
            eventBus.$off('event-delete-ddl-reference-row', this.eventDeleteReferenceRow);
        }
    }
</script>

<style lang="scss" scoped>
    @import "./TabSettingsDdls";
    @import "TabSettingsPermissions";

    .absolute-sorting {
        position: absolute;
        bottom: 0;
        left: 0;
        right: 0;
        height: auto;
        padding: 0 5px;
        border-top: 1px solid #777;
    }
    .ddl-panel {
        height: calc(50% - 20px) !important;
    }
    .permissions-tab {
        padding: 5px;
    }
</style>