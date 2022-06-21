<template>
    <div class="container-fluid full-height">
        <div class="row full-height">
            <!--LEFT SIDE-->
            <div class="col-xs-12 full-height" :style="{width: '25%', paddingRight: 0}">
                <div class="top-text" :style="textSysStyle">
                    <span>Dropdown Lists (DDLs)</span>
                    <button class="btn btn-default btn-sm blue-gradient"
                            :style="$root.themeButtonStyle"
                            style="float: right;padding: 0 3px;"
                            @click="show_copy_ddl = true"
                            title="Copy DDL from Tables">Copy</button>
                </div>
                <div class="body-panel no-padding" style="margin-top: 6px">
                    <custom-table
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
                            :available-columns="['name']"
                            :use_theme="true"
                            :no_width="true"
                            @added-row="addDDLRow"
                            @updated-row="updateDDLRow"
                            @delete-row="deleteDDLRow"
                            @row-index-clicked="rowIndexClickedDDL"
                    ></custom-table>

                    <div class="absolute-sorting">
                        <vertical-table
                            v-if="tableMeta._ddls[selectedDDL]"
                            :td="'custom-cell-settings-ddl'"
                            :global-meta="tableMeta"
                            :table-meta="$root.settingsMeta['ddl']"
                            :settings-meta="$root.settingsMeta"
                            :table-row="tableMeta._ddls[selectedDDL]"
                            :cell-height="1"
                            :max-cell-rows="0"
                            :user="$root.user"
                            :behavior="'settings_ddl'"
                            :disabled_sel="true"
                            :is_small_spacing="'yes'"
                            :available-columns="['datas_sort']"
                            :widths="{ name: '35%', col: '65%', history: 0, unit: 0, }"
                            style="table-layout: auto"
                            @updated-cell="updateDDLRow"
                        ></vertical-table>
                    </div>
                </div>
            </div>
            <!--RIGHT SIDE-->
            <div class="col-xs-12 full-height" :style="{width: '75%'}">
                <div class="top-text" :style="textSysStyle">
                    <span v-if="!tableMeta._ddls[selectedDDL]">You should select the DDL</span>
                    <span v-else="">Options for DDL: {{ tableMeta._ddls[selectedDDL].name }}</span>

                    <info-sign-link
                            class="right-elem"
                            :app_sett_key="'help_link_settings_ddls'"
                            :hgt="26"
                    ></info-sign-link>
                </div>
                <div class="body-panel no-padding" :style="{backgroundColor: selectedDDL > -1 ? 'inherit' : null}" :class="[selectedDDL > -1 ? '' : 't5']">

                    <div class="full-frame ddl-panel">
                        <custom-table
                                v-if="tableMeta._ddls[selectedDDL]"
                                :cell_component_name="'custom-cell-settings-ddl'"
                                :global-meta="tableMeta"
                                :table-meta="$root.settingsMeta['ddl_references']"
                                :settings-meta="$root.settingsMeta"
                                :all-rows="tableMeta._ddls[selectedDDL]._references || []"
                                :rows-count="tableMeta._ddls[selectedDDL]._references.length || 0"
                                :cell-height="1"
                                :max-cell-rows="0"
                                :is-full-width="true"
                                :adding-row="addingRefRow"
                                :behavior="'settings_ddl_items'"
                                :user="user"
                                :forbidden-columns="forbidCol"
                                :parent-row="tableMeta._ddls[selectedDDL]"
                                :use_theme="true"
                                :with_edit="!!$root.checkAvailable($root.user, 'ddl_ref')"
                                :widths_div="1.5"
                                @added-row="addReferenceRow"
                                @updated-row="updateReferenceRow"
                                @delete-row="deleteReferenceRow"
                                @show-add-ref-cond="showAddRefCond"
                        ></custom-table>
                    </div>

                    <div v-if="!tableMeta._ddls[selectedDDL]" style="height: 30px;"></div>
                    <div v-if="tableMeta._ddls[selectedDDL]" class="left-elem field-loader-wrapper">
                        <div class="field-loader">
                            <select class="form-control field-selector"
                                    v-model="tableMeta._ddls[selectedDDL].items_pos"
                                    @change="updateDDLRow(tableMeta._ddls[selectedDDL], 'no')"
                            >
                                <option value="before">Before</option>
                                <option value="after">After</option>
                            </select>
                        </div>
                        <div class="field-loader" style="width: auto">
                            <label :style="textSysStyle">RC-Based Items</label>
                        </div>
                    </div>
                    <div v-if="tableMeta._ddls[selectedDDL]" class="right-elem field-loader-wrapper">
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
                    </div>

                    <div class="full-frame ddl-panel">
                        <custom-table
                                v-if="tableMeta._ddls[selectedDDL]"
                                :cell_component_name="'custom-cell-settings-ddl'"
                                :global-meta="tableMeta"
                                :table-meta="$root.settingsMeta['ddl_items']"
                                :all-rows="tableMeta._ddls[selectedDDL]._items || []"
                                :rows-count="tableMeta._ddls[selectedDDL]._items.length || 0"
                                :cell-height="1"
                                :max-cell-rows="0"
                                :is-full-width="true"
                                :adding-row="addingRow"
                                :behavior="'settings_ddl_items'"
                                :user="user"
                                :forbidden-columns="forbidCol"
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

            <!--Import regular ddl options-->
            <div v-show="importRegular" class="modal-wrapper">
                <div class="modal">
                    <div class="modal-dialog modal-sm">
                        <div class="modal-content">
                            <div class="modal-body">
                                <label>Paste options below:</label>
                                <textarea v-model="parseImportOptions" class="form-control" rows="7"></textarea>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-success" @click="parseImport()">Parse &amp; Import</button>
                                <button type="button" class="btn btn-default" @click="importRegular = false">Cancel</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>

        <copy-ddl-from-table-popup
                v-if="show_copy_ddl"
                :table-meta="tableMeta"
                @popup-close="show_copy_ddl = false"
        ></copy-ddl-from-table-popup>
    </div>
</template>

<script>
    import {eventBus} from '../../../../../app';

    import {RefCondHelper} from "../../../../../classes/helpers/RefCondHelper";

    import CellStyleMixin from "../../../../_Mixins/CellStyleMixin";

    import CustomTable from '../../../../CustomTable/CustomTable';
    import SelectWithFolderStructure from "../../../../CustomCell/InCell/SelectWithFolderStructure";
    import InfoSignLink from "../../../../CustomTable/Specials/InfoSignLink";
    import CopyDdlFromTablePopup from "../../../../CustomPopup/CopyDdlFromTablePopup";
    import VerticalTable from "../../../../CustomTable/VerticalTable";

    export default {
        name: "TableDdlSettings",
        components: {
            VerticalTable,
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
                parseImportOptions: '',
                forbidCol: _.concat(['notes'], this.$root.systemFields)
            }
        },
        computed: {
            addingRefRow() {
                return {
                    active: true,
                    position: 'bottom'
                }
            },
        },
        props:{
            tableMeta: Object,
            user:  Object,
            table_id: Number,
            init_ddl_idx: {
                type: Number,
                default: 0,
            },
        },
        watch: {
            table_id: function(val) {
                this.selectedDDL = -1;
                this.tablePdvChanged(val);
            }
        },
        methods: {
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
                    Swal('', getErrors(errors));
                });
            },
            //DDL Functions
            addDDLRow(tableRow) {
                this.$root.sm_msg_type = 1;

                let fields = _.cloneDeep(tableRow);//copy object
                this.$root.deleteSystemFields(fields);

                axios.post('/ajax/ddl', {
                    table_id: this.tableMeta.id,
                    fields: fields
                }).then(({ data }) => {
                    this.tableMeta._ddls = data;
                    this.selectedDDL = this.tableMeta._ddls.length-1;
                }).catch(errors => {
                    Swal('', getErrors(errors));
                }).finally(() => {
                    this.$root.sm_msg_type = 0;
                });
            },
            updateDDLRow(tableRow, noselect) {
                this.$root.sm_msg_type = 1;

                let row_id = tableRow.id;
                let fields = _.cloneDeep(tableRow);//copy object
                this.$root.deleteSystemFields(fields);

                axios.put('/ajax/ddl', {
                    table_id: this.tableMeta.id,
                    ddl_id: row_id,
                    fields: fields
                }).then(({ data }) => {
                    this.tableMeta._ddls = data;
                    if (!noselect) {
                        this.selectedDDL = -1;
                    }
                }).catch(errors => {
                    Swal('', getErrors(errors));
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
                    this.tableMeta._ddls = data;
                    this.selectedDDL = -1;
                    RefCondHelper.setUses(this.tableMeta);
                }).catch(errors => {
                    Swal('', getErrors(errors));
                }).finally(() => {
                    this.$root.sm_msg_type = 0;
                });
            },
            rowIndexClickedDDL(index) {
                this.selectedDDL = index;
            },

            //DDL Items Functions
            addItemRow(tableRow) {
                this.$root.sm_msg_type = 1;

                let fields = _.cloneDeep(tableRow);//copy object
                this.$root.deleteSystemFields(fields);

                axios.post('/ajax/ddl/item', {
                    table_id: this.tableMeta.id,
                    ddl_id: this.tableMeta._ddls[this.selectedDDL].id,
                    fields: fields
                }).then(({ data }) => {
                    this.tableMeta._ddls[this.selectedDDL]._items = data;
                }).catch(errors => {
                    Swal('', getErrors(errors));
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
                    ddl_id: this.tableMeta._ddls[this.selectedDDL].id,
                    ddl_item_id: row_id,
                    fields: fields
                }).then(({ data }) => {
                    this.tableMeta._ddls[this.selectedDDL]._items = data;
                    if (tableRow._changed_field === 'opt_color') {
                        eventBus.$emit('reload-page');
                    }
                }).catch(errors => {
                    Swal('', getErrors(errors));
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
                        ddl_id: this.tableMeta._ddls[this.selectedDDL].id,
                        ddl_item_id: ddl_id
                    }
                }).then(({ data }) => {
                    this.tableMeta._ddls[this.selectedDDL]._items = data;
                }).catch(errors => {
                    Swal('', getErrors(errors));
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
                    ddl_id: this.tableMeta._ddls[this.selectedDDL].id,
                    fields: fields
                }).then(({ data }) => {
                    this.tableMeta._ddls[this.selectedDDL]._references = data;
                    RefCondHelper.setUses(this.tableMeta);
                }).catch(errors => {
                    Swal('', getErrors(errors));
                }).finally(() => {
                    this.$root.sm_msg_type = 0;
                });
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
                    ddl_id: this.tableMeta._ddls[this.selectedDDL].id,
                    ddl_ref_id: row_id,
                    fields: fields
                }).then(({ data }) => {
                    this.tableMeta._ddls[this.selectedDDL]._references = data;
                    if ($.inArray(tableRow._changed_field, ['show_field_id','image_field_id']) > -1)
                    {
                        eventBus.$emit('reload-page', this.tableMeta.id);
                    }
                }).catch(errors => {
                    Swal('', getErrors(errors));
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
                        ddl_id: this.tableMeta._ddls[this.selectedDDL].id,
                        ddl_ref_id: ddl_id
                    }
                }).then(({ data }) => {
                    this.tableMeta._ddls[this.selectedDDL]._references = data;
                    RefCondHelper.setUses(this.tableMeta);
                }).catch(errors => {
                    Swal('', getErrors(errors));
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
                        Swal('', getErrors(errors));
                    });
                }
            },
            loadDDLOptions() {
                if (this.table_for_load_ddl && this.field_for_load_ddl && this.selectedDDL > -1) {
                    this.$root.sm_msg_type = 2;
                    axios.post('/ajax/ddl/fill-from-field', {
                        table_id: this.table_for_load_ddl,
                        table_field_id: this.field_for_load_ddl,
                        ddl_id: this.tableMeta._ddls[this.selectedDDL].id,
                    }).then(({ data }) => {
                        this.tableMeta._ddls[this.selectedDDL]._items = data;
                    }).catch(errors => {
                        Swal('', getErrors(errors));
                    }).finally(() => {
                        this.$root.sm_msg_type = 0;
                    });
                }
            },
            parseImport() {
                if (this.parseImportOptions && this.selectedDDL > -1) {
                    this.$root.sm_msg_type = 1;
                    axios.post('/ajax/ddl/parse-options', {
                        options: this.parseImportOptions,
                        ddl_id: this.tableMeta._ddls[this.selectedDDL].id,
                    }).then(({ data }) => {
                        this.tableMeta._ddls[this.selectedDDL]._items = data;
                        this.importRegular = false;
                    }).catch(errors => {
                        Swal('', getErrors(errors));
                    }).finally(() => {
                        this.$root.sm_msg_type = 0;
                    });
                }
            },
        },
        mounted() {
            this.load_pdv_ddl_fields();

            eventBus.$on('event-update-ddl-reference-row', this.eventUpdateReferenceRow);
            eventBus.$on('event-delete-ddl-reference-row', this.eventDeleteReferenceRow);
        },
        beforeDestroy() {
            eventBus.$off('event-update-ddl-reference-row', this.eventUpdateReferenceRow);
            eventBus.$off('event-delete-ddl-reference-row', this.eventDeleteReferenceRow);
        }
    }
</script>

<style lang="scss" scoped>
    @import "./TabSettingsDdls";

    .absolute-sorting {
        position: absolute;
        bottom: 0;
        left: 0;
        right: 0;
        height: auto;
        padding: 0 5px;
        border-top: 1px solid #777;
    }
</style>