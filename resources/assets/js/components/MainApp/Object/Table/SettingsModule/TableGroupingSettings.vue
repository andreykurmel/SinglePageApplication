<template>
    <div class="container-fluid full-height">
        <div class="row full-height permissions-tab">
            <!--LEFT SIDE-->
            <div class="full-height col-xs-6" :style="{paddingRight: 0, width: '40%'}">
                <div class="top-text" :style="textSysStyleSmart">
                    <span v-if="is_popup_type">Group Type: {{ activeLeftTab === 'rows' ? 'Rows' : 'Columns' }}</span>
                    <span v-else="">Define Row and Column Groups</span>
                </div>
                <div class="permissions-panel">
                    <div class="permissions-menu-header">
                        <button class="btn btn-default btn-sm"
                                :class="{active : activeLeftTab === 'rows'}"
                                :style="textSysStyle"
                                :disabled="!$root.checkAvailable(user, 'group_rows')"
                                @click="activeLeftTab = 'rows'">
                            Rows
                        </button>
                        <button class="btn btn-default btn-sm"
                                :class="{active : activeLeftTab === 'columns'}"
                                :style="textSysStyle"
                                :disabled="!$root.checkAvailable(user, 'group_columns')"
                                @click="activeLeftTab = 'columns'">
                            Columns
                        </button>
                    </div>
                    <div class="permissions-menu-body">

                        <div class="full-frame"
                             v-show="activeLeftTab === 'rows' && $root.checkAvailable(user, 'group_rows')"
                        >
                            <custom-table
                                :cell_component_name="'custom-cell-col-row-group'"
                                :global-meta="tableMeta"
                                :table-meta="settingsMeta['table_row_groups']"
                                :all-rows="tableMeta._row_groups"
                                :rows-count="tableMeta._row_groups.length"
                                :cell-height="1"
                                :max-cell-rows="0"
                                :is-full-width="true"
                                :behavior="'data_sets_rows'"
                                :user="user"
                                :adding-row="addingRow"
                                :selected-row="selectedRowGroup"
                                :available-columns="['name']"
                                :use_theme="true"
                                :no_width="true"
                                @added-row="addRowGroup"
                                @updated-row="updateRowGroup"
                                @delete-row="deleteRowGroup"
                                @row-index-clicked="rowIndexClickedRowGroup"
                                @show-src-record="showLinkedRows"
                            ></custom-table>
                        </div>

                        <div class="full-frame"
                             v-show="activeLeftTab === 'columns' && $root.checkAvailable(user, 'group_columns')"
                        >
                            <custom-table
                                :cell_component_name="'custom-cell-col-row-group'"
                                :global-meta="tableMeta"
                                :table-meta="settingsMeta['table_column_groups']"
                                :all-rows="tableMeta._column_groups"
                                :rows-count="tableMeta._column_groups.length"
                                :cell-height="1"
                                :max-cell-rows="0"
                                :is-full-width="true"
                                :behavior="'data_sets_colgroups'"
                                :user="user"
                                :adding-row="addingRow"
                                :selected-row="selectedColGroup"
                                :forbidden-columns="$root.systemFields"
                                :use_theme="true"
                                :no_width="true"
                                @added-row="addColGroup"
                                @updated-row="updateColGroup"
                                @delete-row="deleteColGroup"
                                @row-index-clicked="rowIndexClickedColGroup"
                                @show-src-record="showLinkedRows"
                            ></custom-table>
                        </div>

                    </div>
                </div>
            </div>

            <!--RIGHT SIDE-->

            <div class="full-height col-xs-6" :style="{width: '60%'}">

                <div v-show="activeLeftTab === 'rows' && $root.checkAvailable(user, 'group_rows')"
                     class="full-height"
                >
                    <div class="top-text flex flex--space" :style="textSysStyleSmart">
                        <div v-if="!selRowGroup">You should select RowGroup</div>
                        <div v-else="" class="flex flex--center-v no-wrap">
                            Row Group:&nbsp;
                            <select-block
                                :options="rowGroupOpts()"
                                :sel_value="selRowGroup.id"
                                :style="{ width:'250px', height:'28px', }"
                                @option-select="rowGroupChanged"
                            ></select-block>
                        </div>

                        <info-sign-link
                            class="right-elem"
                            :app_sett_key="'help_link_settings_data_sets'"
                            :hgt="18"
                            :txt="'for Settings/Row Groups'"
                        ></info-sign-link>
                    </div>
                    <div class="permissions-panel no-padding" :class="[selectedRowGroup > -1 ? 'inherit-bg' : '']">
                        <div class="full-frame flex flex--col flex--space">

                            <div class="full-frame data-set-panel-sm pt5">
                                <vertical-table
                                        v-if="selRowGroup"
                                        class="spaced-table__fix"
                                        :td="'custom-cell-col-row-group'"
                                        :global-meta="tableMeta"
                                        :table-meta="settingsMeta['table_row_groups']"
                                        :settings-meta="settingsMeta"
                                        :table-row="selRowGroup"
                                        :user="user"
                                        :cell-height="1"
                                        :max-cell-rows="0"
                                        :behavior="'data_sets'"
                                        :with_edit="!selRowGroup.is_system"
                                        :available-columns="['row_ref_condition_id','preview_col_id','description']"
                                        :widths="{name: '35%', col: '65%', history: 0, no_margins: true}"
                                        @show-add-ref-cond="showAddRefCond"
                                        @updated-cell="updateRowGroup"
                                ></vertical-table>
                            </div>

                            <div class="field-loader-wrapper">
                                <div class="field-loader" v-if="tableMeta && selRowGroup">
                                    <label  :style="textSysStyleSmart">Listing field:</label>
                                    <select class="form-control field-selector"
                                            :disabled="!!selRowGroup.is_system"
                                            v-model="selRowGroup.listing_field"
                                            @change="updateRowGroup(selRowGroup)"
                                    >
                                        <option v-for="tableHeader in tableMeta._fields" :value="tableHeader.field">{{ tableHeader.name }}</option>
                                    </select>
                                </div>
                            </div>

                            <div class="full-frame data-set-panel-lg">
                                <custom-table
                                        v-if="selRowGroup"
                                        :cell_component_name="'custom-cell-col-row-group'"
                                        :global-meta="tableMeta"
                                        :table-meta="settingsMeta['table_row_group_regulars']"
                                        :all-rows="selRowGroup.listing_field
                                            ? selRowGroup._regulars
                                            : null"
                                        :rows-count="selRowGroup.listing_field
                                            ? selRowGroup._regulars.length
                                            : 0"
                                        :cell-height="1"
                                        :max-cell-rows="0"
                                        :is-full-width="true"
                                        :user="user"
                                        :behavior="'data_sets_items'"
                                        :forbidden-columns="$root.systemFields"
                                        :parent-row="selRowGroup"
                                        :headers-changer="regularsHeadersChanger"
                                        :use_theme="true"
                                        :with_edit="!selRowGroup.is_system"
                                        @added-row="addRowGroupRegular"
                                        @updated-row="updateRowGroupRegular"
                                        @delete-row="deleteRowGroupRegular"
                                ></custom-table>
                            </div>

                        </div>
                    </div>
                </div>

                <div v-show="activeLeftTab === 'columns' && $root.checkAvailable(user, 'group_columns')"
                     class="full-height"
                >
                    <div class="top-text flex flex--space" :style="textSysStyleSmart">
                        <div v-if="!selColGroup">You should select ColGroup</div>
                        <div v-else="" class="flex flex--center-v no-wrap">
                            Columns for Group:&nbsp;
                            <select-block
                                :options="colGroupOpts()"
                                :sel_value="selColGroup.id"
                                :style="{ width:'250px', height:'28px', }"
                                @option-select="colGroupChanged"
                            ></select-block>
                        </div>

                        <info-sign-link
                            class="right-elem"
                            :app_sett_key="'help_link_settings_data_sets_columns'"
                            :hgt="18"
                            :txt="'for Settings/Col Groups'"
                        ></info-sign-link>
                    </div>
                    <div class="permissions-panel">
                        <custom-table
                                v-if="tableMeta"
                                :cell_component_name="'custom-cell-col-row-group'"
                                :global-meta="tableMeta"
                                :table-meta="settingsMeta['table_column_groups_2_table_fields']"
                                :all-rows="selectedColGroup > -1 ? tableMeta._fields : null"
                                :rows-count="selectedColGroup > -1 ? tableMeta._fields.length : 0"
                                :cell-height="1"
                                :max-cell-rows="0"
                                :is-full-width="true"
                                :user="user"
                                :behavior="'data_sets_columns'"
                                :condition-array="selColGroup ? selColGroup._fields : null"
                                :headers-with-check="headersWithCheck"
                                :forbidden-columns="$root.systemFields"
                                :use_theme="true"
                                @check-row="toggleFieldForColGroup"
                        ></custom-table>
                    </div>
                </div>

            </div>
        </div>
    </div>
</template>

<script>
    import {eventBus} from '../../../../../app';

    import {RefCondHelper} from "../../../../../classes/helpers/RefCondHelper";

    import CellStyleMixin from "../../../../_Mixins/CellStyleMixin";

    import CustomTable from '../../../../CustomTable/CustomTable';
    import InfoSignLink from "../../../../CustomTable/Specials/InfoSignLink";
    import SelectBlock from "../../../../CommonBlocks/SelectBlock.vue";

    export default {
        name: "TableGroupingSettings",
        components: {
            SelectBlock,
            InfoSignLink,
            CustomTable,
        },
        mixins: [
            CellStyleMixin,
        ],
        data: function () {
            return {
                activeLeftTab: '',
                selectedRowGroup: 0,
                selectedColGroup: 0,
                addingRow: {
                    active: true,
                    position: 'bottom'
                },
                headersWithCheck: ['checked'],
            }
        },
        computed: {
            regularsHeadersChanger() {
                let vl = this.tableMeta && this.tableMeta._row_groups[this.selectedRowGroup] ?
                    _.find(this.tableMeta._fields, {field: this.tableMeta._row_groups[this.selectedRowGroup].listing_field})
                    : null;

                return {
                    'field_value': (vl ? this.$root.uniqName(vl.name) : '')
                };
            },
            selRowGroup() {
                return this.tableMeta._row_groups[this.selectedRowGroup];
            },
            selColGroup() {
                return this.tableMeta._column_groups[this.selectedColGroup];
            },
        },
        props:{
            tableMeta: Object,
            settingsMeta: Object,
            table_id: Number|null,
            user: Object,
            is_popup_type: String,
            foreign_sel_id: Number|String,
        },
        watch: {
            table_id: function(val) {
                this.selectedRowGroup = -1;
                this.selectedColGroup = -1;
                this.activeLeftTab = '';
                if (this.$root.checkAvailable(this.user, 'group_rows')) {
                    this.activeLeftTab = 'rows';
                } else
                if (this.$root.checkAvailable(this.user, 'group_columns')) {
                    this.activeLeftTab = 'columns';
                }
            }
        },
        methods: {
            inArray(item, array) {
                return $.inArray(item, array) > -1;
            },

            rowIndexClickedRowGroup(index) {
                this.selectedRowGroup = index;
            },
            rowIndexClickedColGroup(index) {
                this.selectedColGroup = index;
            },
            rowGroupOpts() {
                return _.map(this.tableMeta._row_groups, (item) => {
                    return { val:item.id, show:item.name };
                });
            },
            colGroupOpts() {
                return _.map(this.tableMeta._column_groups, (item) => {
                    return { val:item.id, show:item.name };
                });
            },
            rowGroupChanged(opt) {
                this.rowIndexClickedRowGroup(
                    _.findIndex(this.tableMeta._row_groups, {id: Number(opt.val)})
                );
            },
            colGroupChanged(opt) {
                this.rowIndexClickedColGroup(
                    _.findIndex(this.tableMeta._column_groups, {id: Number(opt.val)})
                );
            },

            //Row Group Functions
            addRowGroup(tableRow) {
                this.$root.sm_msg_type = 1;

                let fields = _.cloneDeep(tableRow);//copy object
                this.$root.deleteSystemFields(fields);

                axios.post('/ajax/row-group', {
                    table_id: this.tableMeta.id,
                    fields: fields,
                }).then(({ data }) => {
                    this.tableMeta._row_groups.push( data );
                    RefCondHelper.setUses(this.tableMeta);

                    eventBus.$emit('reload-page');
                    this.selectedRowGroup = this.tableMeta._row_groups.length-1;
                }).catch(errors => {
                    Swal('Info', getErrors(errors));
                }).finally(() => {
                    this.$root.sm_msg_type = 0;
                });
            },
            eventCreateRowGroup(table_id, tableRow) {
                if (this.tableMeta.id == table_id) {
                    this.addRowGroup(tableRow);
                }
            },
            eventUpdateRowGroup(table_id, tableRow) {
                if (this.tableMeta.id == table_id) {
                    this.updateRowGroup(tableRow);
                }
            },
            updateRowGroup(tableRow) {
                this.$root.sm_msg_type = 1;

                let group_id = tableRow.id;
                let fields = _.cloneDeep(tableRow);//copy object
                this.$root.deleteSystemFields(fields);
                RefCondHelper.setUses(this.tableMeta);

                axios.put('/ajax/row-group', {
                    table_row_group_id: group_id,
                    fields: fields
                }).then(({ data }) => {
                    if (this.selectedRowGroup > -1 && this.tableMeta._row_groups[this.selectedRowGroup]) {
                        this.tableMeta._row_groups[this.selectedRowGroup]._regulars = data;
                    }
                    eventBus.$emit('reload-page');
                }).catch(errors => {
                    Swal('Info', getErrors(errors));
                }).finally(() => {
                    this.$root.sm_msg_type = 0;
                });
            },
            deleteRowGroup(tableRow) {
                let cf_found = _.find(this.tableMeta._cond_formats, {table_row_group_id: Number(tableRow.id)});
                let tp_found = false;
                _.each(this.tableMeta._table_permissions, (permis) => {
                    tp_found = _.find(permis._permission_rows, {table_row_group_id: Number(tableRow.id)})
                        ? true
                        : tp_found;
                });
                let dcr_found = false;
                _.each(this.tableMeta._table_requests, (permis) => {
                    dcr_found = _.find(permis._permission_rows, {table_row_group_id: Number(tableRow.id)})
                        ? true
                        : dcr_found;
                });
                let str = '';
                if (cf_found || tp_found || dcr_found) {
                    str += 'It is used in the definition of ';
                    str += cf_found ? 'Conditional Formattings, ' : '';
                    str += tp_found ? 'Permissions, ' : '';
                    str += dcr_found ? 'DCR, ' : '';
                    str = str.replace(/, $/, '.');
                }
                Swal({
                    title: 'Info',
                    text: 'Confirm to delete the RowGroup? '+str,
                    confirmButtonClass: 'btn-danger',
                    confirmButtonText: 'Yes',
                    showCancelButton: true,
                    animation: 'slide-from-top'
                }).then(response => {
                    if (response.value) {
                        this.deleteRowGroupConfirmed(tableRow);
                        eventBus.$emit('settings-permissions-selected-off');
                    }
                });
            },
            deleteRowGroupConfirmed(tableRow) {
                this.$root.sm_msg_type = 1;
                axios.delete('/ajax/row-group', {
                    params: {
                        table_row_group_id: tableRow.id
                    }
                }).then(({ data }) => {
                    let idx = _.findIndex(this.tableMeta._row_groups, {id: Number(tableRow.id)});
                    if (idx > -1) {
                        this.selectedRowGroup = -1;
                        this.tableMeta._row_groups.splice(idx, 1);

                        //sync with 'Conditional Formats'
                        this.tableMeta._cond_formats = _.filter(this.tableMeta._cond_formats, (item) => {
                            return item.table_row_group_id != Number(tableRow.id);
                        });
                        //sync with 'Table Permissions'
                        _.each(this.tableMeta._table_permissions, (permis) => {
                            _.remove(permis._permission_rows, {table_row_group_id: Number(tableRow.id)});
                        });
                        //sync with 'DCR module'
                        _.each(this.tableMeta._table_requests, (permis) => {
                            _.remove(permis._permission_rows, {table_row_group_id: Number(tableRow.id)});
                        });

                        eventBus.$emit('reload-page');
                    }
                    RefCondHelper.setUses(this.tableMeta);
                }).catch(errors => {
                    Swal('Info', getErrors(errors));
                }).finally(() => {
                    this.$root.sm_msg_type = 0;
                });
            },

            //Row Group Regular Functions
            addRowGroupRegular(tableRow) {
                this.$root.sm_msg_type = 1;

                let fields = _.cloneDeep(tableRow);//copy object
                this.$root.deleteSystemFields(fields);

                axios.post('/ajax/row-group/regular', {
                    table_row_group_id: this.tableMeta._row_groups[this.selectedRowGroup].id,
                    fields: fields,
                }).then(({ data }) => {
                    this.tableMeta._row_groups[this.selectedRowGroup]._regulars.push( data );
                    eventBus.$emit('reload-page');
                }).catch(errors => {
                    Swal('Info', getErrors(errors));
                }).finally(() => {
                    this.$root.sm_msg_type = 0;
                });
            },
            updateRowGroupRegular(tableRow) {
                this.$root.sm_msg_type = 1;

                let group_id = tableRow.id;
                let fields = _.cloneDeep(tableRow);//copy object
                this.$root.deleteSystemFields(fields);

                axios.put('/ajax/row-group/regular', {
                    table_row_group_regular_id: group_id,
                    fields: fields
                }).then(({ data }) => {
                    let idx = _.findIndex(this.tableMeta._row_groups[this.selectedRowGroup]._regulars, {id: Number(group_id)});
                    if (idx > -1) {
                        this.tableMeta._row_groups[this.selectedRowGroup]._regulars.splice(idx, 1 ,data);
                    }
                    eventBus.$emit('reload-page');
                }).catch(errors => {
                    Swal('Info', getErrors(errors));
                }).finally(() => {
                    this.$root.sm_msg_type = 0;
                });
            },
            deleteRowGroupRegular(tableRow) {
                this.$root.sm_msg_type = 1;
                axios.delete('/ajax/row-group/regular', {
                    params: {
                        table_row_group_regular_id: tableRow.id
                    }
                }).then(({ data }) => {
                    let idx = _.findIndex(this.tableMeta._row_groups[this.selectedRowGroup]._regulars, {id: Number(tableRow.id)});
                    if (idx > -1) {
                        this.tableMeta._row_groups[this.selectedRowGroup]._regulars.splice(idx, 1);
                    }
                    eventBus.$emit('reload-page');
                }).catch(errors => {
                    Swal('Info', getErrors(errors));
                }).finally(() => {
                    this.$root.sm_msg_type = 0;
                });
            },



            //Col Group Functions
            addColGroup(tableRow) {
                this.$root.sm_msg_type = 1;

                let fields = _.cloneDeep(tableRow);//copy object
                this.$root.deleteSystemFields(fields);

                axios.post('/ajax/column-group', {
                    table_id: this.tableMeta.id,
                    fields: fields,
                }).then(({ data }) => {
                    this.tableMeta._column_groups.push( data );
                    this.selectedColGroup = this.tableMeta._column_groups.length-1;
                }).catch(errors => {
                    Swal('Info', getErrors(errors));
                }).finally(() => {
                    this.$root.sm_msg_type = 0;
                });
            },
            updateColGroup(tableRow) {
                this.$root.sm_msg_type = 1;

                let group_id = tableRow.id;
                let fields = _.cloneDeep(tableRow);//copy object
                this.$root.deleteSystemFields(fields);

                axios.put('/ajax/column-group', {
                    table_column_group_id: group_id,
                    fields: fields
                }).then(({ data }) => {
                }).catch(errors => {
                    Swal('Info', getErrors(errors));
                }).finally(() => {
                    this.$root.sm_msg_type = 0;
                });
            },
            deleteColGroup(tableRow) {
                let cf_found = _.find(this.tableMeta._cond_formats, {table_column_group_id: Number(tableRow.id)});
                let tp_found = false;
                _.each(this.tableMeta._table_permissions, (permis) => {
                    tp_found = _.find(permis._permission_columns, {table_column_group_id: Number(tableRow.id)})
                        ? true
                        : tp_found;
                });
                let dcr_found = false;
                _.each(this.tableMeta._table_requests, (permis) => {
                    dcr_found = _.find(permis._data_request_columns, {table_column_group_id: Number(tableRow.id)})
                        ? true
                        : dcr_found;
                });
                let str = '';
                if (cf_found || tp_found || dcr_found) {
                    str += 'It is used in the definition of ';
                    str += cf_found ? 'Conditional Formattings, ' : '';
                    str += tp_found ? 'Permissions, ' : '';
                    str += dcr_found ? 'DCR, ' : '';
                    str = str.replace(/, $/, '.');
                }
                Swal({
                    title: 'Info',
                    text: 'Confirm to delete the ColGroup? '+str,
                    confirmButtonClass: 'btn-danger',
                    confirmButtonText: 'Yes',
                    showCancelButton: true,
                    animation: 'slide-from-top'
                }).then(response => {
                    if (response.value) {
                        this.deleteColGroupConfirmed(tableRow);
                        eventBus.$emit('settings-permissions-selected-off');
                    }
                });
            },
            deleteColGroupConfirmed(tableRow) {
                this.$root.sm_msg_type = 1;
                axios.delete('/ajax/column-group', {
                    params: {
                        table_column_group_id: tableRow.id
                    }
                }).then(({ data }) => {
                    let idx = _.findIndex(this.tableMeta._column_groups, {id: Number(tableRow.id)});
                    if (idx > -1) {
                        this.selectedColGroup = -1;
                        this.tableMeta._column_groups.splice(idx, 1);

                        //sync with 'Conditional Formats'
                        this.tableMeta._cond_formats = _.filter(this.tableMeta._cond_formats, (item) => {
                            return item.table_column_group_id != Number(tableRow.id);
                        });
                        //sync with 'Table Permissions'
                        _.each(this.tableMeta._table_permissions, (permis) => {
                            _.remove(permis._permission_columns, {table_column_group_id: Number(tableRow.id)});
                        });
                        //sync with DCR module
                        _.each(this.tableMeta._table_requests, (permis) => {
                            _.remove(permis._data_request_columns, {table_column_group_id: Number(tableRow.id)});
                        });
                    }
                }).catch(errors => {
                    Swal('Info', getErrors(errors));
                }).finally(() => {
                    this.$root.sm_msg_type = 0;
                });
            },

            //Col Group field Functions
            toggleFieldForColGroup(field, status, ids) {
                let colGroup = this.tableMeta._column_groups[this.selectedColGroup];
                this.$root.sm_msg_type = 1;
                if (status) {
                    axios.post('/ajax/column-group/field', {
                        table_column_group_id: colGroup.id,
                        table_field_ids: ids
                    }).then(({ data }) => {
                        _.each(data, (el) => {
                            this.tableMeta._column_groups[this.selectedColGroup]._fields.push(el);
                        });
                        if (this.tableMeta._groupings && _.find(this.tableMeta._groupings, {rg_colgroup_id: Number(colGroup.id)})) {
                            eventBus.$emit('reload-meta-table');
                        }
                    }).catch(errors => {
                        Swal('Info', getErrors(errors));
                    }).finally(() => {
                        this.$root.sm_msg_type = 0;
                    });
                } else {
                    axios.delete('/ajax/column-group/field', {
                        params: {
                            table_column_group_id: colGroup.id,
                            table_field_ids: ids
                        }
                    }).then(({ data }) => {
                        _.each(ids, (id) => {
                            let idx = _.findIndex(colGroup._fields, {id: Number(id)});
                            if (idx > -1) {
                                this.tableMeta._column_groups[this.selectedColGroup]._fields.splice(idx, 1);
                            }
                        });
                        if (this.tableMeta._groupings && _.find(this.tableMeta._groupings, {rg_colgroup_id: Number(colGroup.id)})) {
                            eventBus.$emit('reload-meta-table');
                        }
                    }).catch(errors => {
                        Swal('Info', getErrors(errors));
                    }).finally(() => {
                        this.$root.sm_msg_type = 0;
                    });
                }
            },

            //Linked Rows
            showLinkedRows(lnk, header, tableRow, behavior) {
                this.$emit('show-src-record', lnk, header, tableRow, behavior);
            },

            //EMITS
            showAddRefCond(refId) {
                eventBus.$emit('show-ref-conditions-popup', this.tableMeta.db_name, refId);
            },
            clearSelected(val) {
                if (!val || val === '_row_groups') {
                    this.selectedRowGroup = -1;
                    this.selectedColGroup = -1;
                }
            },
        },
        mounted() {
            if (this.is_popup_type === 'col') {
                if (this.$root.checkAvailable(this.user, 'group_columns')) {
                    this.activeLeftTab = 'columns';
                    if (this.foreign_sel_id) {
                        this.selectedColGroup = _.findIndex(this.tableMeta._column_groups, {id: Number(this.foreign_sel_id)});
                    }
                } else
                if (this.$root.checkAvailable(this.user, 'group_rows')) {
                    this.activeLeftTab = 'rows';
                }
            } else {
                if (this.$root.checkAvailable(this.user, 'group_rows')) {
                    this.activeLeftTab = 'rows';
                    if (this.foreign_sel_id) {
                        this.selectedRowGroup = _.findIndex(this.tableMeta._row_groups, {id: Number(this.foreign_sel_id)});
                    }
                } else
                if (this.$root.checkAvailable(this.user, 'group_columns')) {
                    this.activeLeftTab = 'columns';
                }
            }

            eventBus.$on('event-create-row-group', this.eventCreateRowGroup);
            eventBus.$on('event-update-row-group', this.eventUpdateRowGroup);
            eventBus.$on('clear-selected-settings-groups', this.clearSelected);
            eventBus.$on('empty-sel', this.clearSelected);
        },
        beforeDestroy() {
            eventBus.$off('event-create-row-group', this.eventCreateRowGroup);
            eventBus.$off('event-update-row-group', this.eventUpdateRowGroup);
            eventBus.$off('clear-selected-settings-groups', this.clearSelected);
            eventBus.$off('empty-sel', this.clearSelected);
        }
    }
</script>

<style lang="scss" scoped>
    @import "TabSettingsPermissions";

    .btn-sm {
        height: 30px;
    }

    .permissions-tab {
        .field-loader-wrapper {
            text-align: right;

            .field-loader {
                height: 30px;

                label {
                    color: #FFF;
                    margin: 0;
                }

                .field-selector {
                    height: 26px;
                    top: 0;
                    width: 150px;
                }
            }
        }
    }
</style>