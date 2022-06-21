<template>
    <div class="container-fluid full-height">
        <div v-if="!$root.AddonAvailableToUser(tableMeta, 'request')" class="row full-frame flex flex--center">
            <label>Addon is unavailable!</label>
        </div>
        <div v-else="" class="row full-height permissions-tab">
            <!--LEFT SIDE-->
            <div class="col-xs-5 full-height" style="padding-right: 0;">
                <div class="top-text" :style="textSysStyle">
                    <span>Data Collection & Request (DCR)</span>
                </div>
                <div class="permissions-panel no-padding">
                    <custom-table
                        v-if="isVisible"
                        :cell_component_name="'custom-cell-settings-dcr'"
                        :global-meta="tableMeta"
                        :table-meta="$root.settingsMeta['table_data_requests']"
                        :all-rows="tableMeta._table_requests"
                        :rows-count="tableMeta._table_requests.length"
                        :cell-height="1"
                        :max-cell-rows="0"
                        :is-full-width="true"
                        :with_edit="tableMeta._is_owner"
                        :behavior="'table_permission_group'"
                        :user="user"
                        :adding-row="addingRow"
                        :selected-row="selectedGroup"
                        :available-columns="requestAvailable"
                        @added-row="addGroup"
                        @updated-row="updateGroup"
                        @delete-row="deleteGroup"
                        @row-index-clicked="rowIndexClickedGroup"
                    ></custom-table>
                </div>
            </div>
            <!--RIGHT SIDE-->
            <div class="col-xs-7 full-height">
                <div class="top-text" :style="textSysStyle">
                    <span>Specifications of Current DCR ( <span>{{ (tableMeta._table_requests[selectedGroup] ? tableMeta._table_requests[selectedGroup].name : '') }}</span> )</span>
                </div>
                <div class="permissions-panel" :style="{height: activeRightTab === 'columns' ? 'calc(40% - 35px)' : 'calc(100% - 35px)'}">
                    <div class="permissions-menu-header">
                        <button class="btn btn-default btn-sm" :style="textSysStyle" :class="{active : activeRightTab === 'design'}" @click="activeRightTab = 'design'">
                            Design
                        </button>
                        <button class="btn btn-default btn-sm" :style="textSysStyle" :class="{active : activeRightTab === 'columns'}" @click="activeRightTab = 'columns'">
                            Columns
                        </button>
                        <button class="btn btn-default btn-sm" :style="textSysStyle" :class="{active : activeRightTab === 'defaults'}" @click="activeRightTab = 'defaults'">
                            Defaults
                        </button>
                        <button class="btn btn-default btn-sm" :style="textSysStyle" :class="{active : activeRightTab === 'actions'}" @click="activeRightTab = 'actions'">
                            Action & Status
                        </button>
                        <button class="btn btn-default btn-sm" :style="textSysStyle" :class="{active : activeRightTab === 'notifs'}" @click="activeRightTab = 'notifs'">
                            Notifications
                        </button>
                        <button class="btn btn-default btn-sm" :style="textSysStyle" :class="{active : activeRightTab === 'access'}" @click="activeRightTab = 'access'">
                            Access
                        </button>
                    </div>
                    <div class="permissions-menu-body">

                        <div class="full-frame defaults-tab" v-show="activeRightTab === 'design'" style="padding: 0">
                            <tab-settings-requests-row
                                    v-if="tableMeta._table_requests[selectedGroup]"
                                    :table_id="tableMeta.id"
                                    :table-meta="tableMeta"
                                    :cell-height="1"
                                    :max-cell-rows="0"
                                    :table-request="$root.settingsMeta['table_data_requests']"
                                    :request-row="selectedGroup > -1 ? tableMeta._table_requests[selectedGroup] : {}"
                                    :with_edit="tableMeta._is_owner"
                                    @updated-cell="updateGroup"
                                    @upload-file="uploadGroupFile"
                                    @del-file="delGroupFile"
                            ></tab-settings-requests-row>
                        </div>

                        <div class="full-frame no-padding defaults-tab" v-show="activeRightTab === 'columns'">
                            <custom-table
                                v-if="tableMeta._table_requests[selectedGroup]"
                                :cell_component_name="'custom-cell-settings-dcr'"
                                :global-meta="tableMeta"
                                :table-meta="$root.settingsMeta['table_data_requests_2_table_column_groups']"
                                :all-rows="tableMeta._table_requests[selectedGroup]._data_request_columns"
                                :rows-count="tableMeta._table_requests[selectedGroup]._data_request_columns.length"
                                :cell-height="1"
                                :max-cell-rows="0"
                                :is-full-width="true"
                                :with_edit="tableMeta._is_owner"
                                :adding-row="selectedGroup > -1 ? addingRow : {active: false}"
                                :user="user"
                                :show-info-key="'add_notes'"
                                :behavior="'dcr_group'"
                                :forbidden-columns="$root.systemFields"
                                :excluded_row_values="[{ field: 'view', excluded: [0] }]"
                                @added-row="addGroupColumnPermis"
                                @updated-row="updateGroupColumnPermis"
                                @delete-row="deleteGroupColumnPermis"
                            ></custom-table>
                        </div>

                        <div class="full-frame defaults-tab" v-if="activeRightTab === 'defaults'">
                            <default-fields-table
                                    v-if="tableMeta._table_requests[selectedGroup]"
                                    :table-dcr-id="tableMeta._table_requests[selectedGroup].id"
                                    :user-group-id="null"
                                    :table-meta="tableMeta"
                                    :default-fields="tableMeta._table_requests[selectedGroup]._default_fields"
                                    :user="user"
                                    :with_edit="tableMeta._is_owner"
                                    :cell-height="1"
                                    :max-cell-rows="0"
                            ></default-fields-table>
                        </div>

                        <div class="full-frame defaults-tab" v-show="activeRightTab === 'actions'">
                            <tab-settings-submission-row
                                    v-if="tableMeta._table_requests[selectedGroup]"
                                    :table-meta="tableMeta"
                                    :table_id="tableMeta.id"
                                    :cell-height="1"
                                    :max-cell-rows="0"
                                    :table-request="$root.settingsMeta['table_data_requests']"
                                    :request-row="selectedGroup > -1 ? tableMeta._table_requests[selectedGroup] : {}"
                                    :with_edit="tableMeta._is_owner"
                                    @updated-cell="updateGroup"
                            ></tab-settings-submission-row>
                        </div>

                        <div class="full-frame defaults-tab" v-show="activeRightTab === 'notifs'" style="padding: 0;">
                            <tab-settings-request-notifs
                                    v-if="tableMeta._table_requests[selectedGroup]"
                                    :table-meta="tableMeta"
                                    :table_id="tableMeta.id"
                                    :cell-height="1"
                                    :max-cell-rows="0"
                                    :table-request="$root.settingsMeta['table_data_requests']"
                                    :request-row="selectedGroup > -1 ? tableMeta._table_requests[selectedGroup] : {}"
                                    :with_edit="tableMeta._is_owner"
                                    @updated-cell="updateGroup"
                            ></tab-settings-request-notifs>
                        </div>

                        <div class="full-frame defaults-tab" v-show="activeRightTab === 'access'">
                            <tab-settings-access-row
                                    v-if="tableMeta._table_requests[selectedGroup]"
                                    :table-meta="tableMeta"
                                    :cell-height="1"
                                    :max-cell-rows="0"
                                    :table-request="$root.settingsMeta['table_data_requests']"
                                    :request-row="selectedGroup > -1 ? tableMeta._table_requests[selectedGroup] : {}"
                                    :with_edit="tableMeta._is_owner"
                                    @updated-cell="updateGroup"
                            ></tab-settings-access-row>
                        </div>

                    </div>
                </div>

                <template v-if="tableMeta._table_requests[selectedGroup] && activeRightTab === 'columns'">
                    <div class="top-text" :style="textSysStyle">Embed Linked Tables</div>
                    <div class="permissions-panel" style="height: calc(60% - 30px)">
                        <tab-settings-requests-linked-tables
                            :table-meta="tableMeta"
                            :dcr-object="tableMeta._table_requests[selectedGroup]"
                        ></tab-settings-requests-linked-tables>
                    </div>
                </template>
            </div>
        </div>
    </div>
</template>

<script>
import CustomTable from '../../../../CustomTable/CustomTable';
import DefaultFieldsTable from '../../../../CustomPopup/DefaultFieldsTable';
import VerticalTable from "../../../../CustomTable/VerticalTable";
import TabSettingsRequestsRow from "./TabSettingsRequestsRow";
import TabSettingsSubmissionRow from "./TabSettingsSubmissionRow";
import VerticalRowsTable from "../../../../CustomTable/VerticalRowsTable";
import TabSettingsRequestNotifs from "./TabSettingsRequestNotifs";
import TabSettingsRequestsLinkedTables from "./TabSettingsRequestsLinkedTables";
import TabSettingsAccessRow from "./TabSettingsAccessRow";

import CellStyleMixin from "../../../../_Mixins/CellStyleMixin";

export default {
        name: "TabSettingsRequests",
        components: {
            TabSettingsAccessRow,
            TabSettingsRequestsLinkedTables,
            TabSettingsRequestNotifs,
            VerticalRowsTable,
            TabSettingsSubmissionRow,
            TabSettingsRequestsRow,
            VerticalTable,
            CustomTable,
            DefaultFieldsTable,
        },
        mixins: [
            CellStyleMixin
        ],
        data: function () {
            return {
                activeRightTab: 'design',
                selectedGroup: 0,
                addingRow: {
                    active: true,
                    position: 'bottom'
                },
                requestAvailable: [
                    'name',
                    'pass',
                    'active',
                ],
            }
        },
        props:{
            tableMeta: Object,
            table_id: Number|null,
            user:  Object,
            isVisible: Boolean,
        },
        watch: {
            table_id: function(val) {
                this.selectedGroup = 0;
                this.activeRightTab = 'design';
            }
        },
        methods: {
            rowIndexClickedGroup(index) {
                this.activeRightTab = 'design';
                this.selectedGroup = index;
            },
            //Table Permissions Functions
            addGroup(tableRow) {
                this.$root.sm_msg_type = 1;

                let fields = _.cloneDeep(tableRow);//copy object
                this.$root.deleteSystemFields(fields);
                fields.active = fields.active ? 1 : 0;

                axios.post('/ajax/table-data-request', {
                    table_id: this.tableMeta.id,
                    fields: fields,
                }).then(({ data }) => {
                    this.tableMeta._table_requests.push(data);
                    this.selectedGroup = -1;
                }).catch(errors => {
                    Swal({
                        title: '',
                        text: getErrors(errors),
                        customClass: 'no-wrap'
                    });
                }).finally(() => {
                    this.$root.sm_msg_type = 0;
                });
            },
            updateGroup(tableRow) {
                this.$root.sm_msg_type = 1;

                let group_id = tableRow.id;
                let fields = _.cloneDeep(tableRow);//copy object
                this.$root.deleteSystemFields(fields);

                axios.put('/ajax/table-data-request', {
                    table_data_request_id: group_id,
                    fields: fields
                }).then(({ data }) => {
                }).catch(errors => {
                    Swal({
                        title: '',
                        text: getErrors(errors),
                        customClass: 'no-wrap'
                    });
                }).finally(() => {
                    this.$root.sm_msg_type = 0;
                });
            },
            deleteGroup(tableRow) {
                this.$root.sm_msg_type = 1;
                axios.delete('/ajax/table-data-request', {
                    params: {
                        table_dcr_id: tableRow.id
                    }
                }).then(({ data }) => {
                    let idx = _.findIndex(this.tableMeta._table_requests, {id: Number(tableRow.id)});
                    if (idx > -1) {
                        this.selectedGroup = -1;
                        this.tableMeta._table_requests.splice(idx, 1);
                    }
                }).catch(errors => {
                    Swal('', getErrors(errors));
                }).finally(() => {
                    this.$root.sm_msg_type = 0;
                });
            },
            uploadGroupFile(tableRow, field, file) {
                this.$root.sm_msg_type = 1;
                let group_id = tableRow.id;
                let formData = new FormData();
                formData.append('table_data_request_id', group_id);
                formData.append('field', field);
                formData.append('u_file', file);
                axios.post('/ajax/table-data-request/dcr-file', formData, {
                    headers: {
                        'Content-Type': 'multipart/form-data'
                    }
                }).then(({ data }) => {
                    tableRow[field] = data.filepath;
                }).catch(errors => {
                    Swal('', getErrors(errors));
                }).finally(() => {
                    this.$root.sm_msg_type = 0;
                });
            },
            delGroupFile(tableRow, field) {
                this.$root.sm_msg_type = 1;
                let group_id = tableRow.id;
                axios.delete('/ajax/table-data-request/dcr-file', {
                    params: {
                        table_data_request_id: group_id,
                        field: field,
                        u_file: 'delete',
                    }
                }).then(({ data }) => {
                    tableRow[field] = null;
                }).catch(errors => {
                    Swal('', getErrors(errors));
                }).finally(() => {
                    this.$root.sm_msg_type = 0;
                });
            },

            //User Group Columns Functions
            addGroupColumnPermis(tableColumn) {
                this.updateGroupColumn(tableColumn.table_column_group_id, 1, 0);
            },
            updateGroupColumnPermis(tableColumn) {
                this.updateGroupColumn(tableColumn.table_column_group_id, tableColumn.view, tableColumn.edit);
            },
            deleteGroupColumnPermis(tableColumn) {
                this.updateGroupColumn(tableColumn.table_column_group_id, 0, 0);
            },
            updateGroupColumn(tb_column_group_id, viewed, edited) {

                let req_columns = this.tableMeta._table_requests[this.selectedGroup]._data_request_columns;
                let columnGr = _.find(req_columns, {table_column_group_id: Number(tb_column_group_id)});

                if (columnGr) {
                    viewed = viewed === undefined ? columnGr.view : viewed;
                    edited = edited === undefined ? columnGr.edit : edited;
                }

                this.$root.sm_msg_type = 1;
                axios.post('/ajax/table-data-request/column', {
                    table_data_request_id: this.tableMeta._table_requests[this.selectedGroup].id,
                    table_column_group_id: tb_column_group_id,
                    view: viewed ? 1 : 0,
                    edit: edited ? 1 : 0,
                }).then(({ data }) => {
                    let idx = _.findIndex(req_columns, (el) => {
                        return el.table_column_group_id == tb_column_group_id;
                    });
                    if (idx > -1) {
                        req_columns.splice(idx, 1 ,data);
                    } else {
                        req_columns.push( data );
                    }
                }).catch(errors => {
                    Swal('', getErrors(errors));
                }).finally(() => {
                    this.$root.sm_msg_type = 0;
                });
            },

            //Linked Tables for DCR
            addLinkedDcr(tableRow) {
                Swal('', 'Unavailable');return;
                this.$root.sm_msg_type = 1;

                let fields = _.cloneDeep(tableRow);//copy object
                this.$root.deleteSystemFields(fields);
                fields.active = fields.active ? 1 : 0;

                axios.post('/ajax/table-data-request/linked-dcr', {
                    table_id: this.tableMeta.id,
                    fields: fields,
                }).then(({ data }) => {
                    //
                }).catch(errors => {
                    Swal('', getErrors(errors));
                }).finally(() => {
                    this.$root.sm_msg_type = 0;
                });
            },
            updateLinkedDcr(tableRow) {
                Swal('', 'Unavailable');return;
                this.$root.sm_msg_type = 1;

                let group_id = tableRow.id;
                let fields = _.cloneDeep(tableRow);//copy object
                this.$root.deleteSystemFields(fields);

                axios.put('/ajax/table-data-request/linked-dcr', {
                    table_data_request_id: group_id,
                    fields: fields
                }).then(({ data }) => {
                }).catch(errors => {
                    Swal('', getErrors(errors));
                }).finally(() => {
                    this.$root.sm_msg_type = 0;
                });
            },
            deleteLinkedDcr(tableRow) {
                Swal('', 'Unavailable');return;
                this.$root.sm_msg_type = 1;
                axios.delete('/ajax/table-data-request/linked-dcr', {
                    params: {
                        table_data_request_id: tableRow.id
                    }
                }).then(({ data }) => {
                    //
                }).catch(errors => {
                    Swal('', getErrors(errors));
                }).finally(() => {
                    this.$root.sm_msg_type = 0;
                });
            },
        },
        mounted() {
        },
        beforeDestroy() {
        }
    }
</script>

<style lang="scss" scoped>
    @import "./TabSettingsPermissions";

    .part-panel-sm {
        height: calc(30% - 8px);
        background-color: #FFF;
    }
    .part-panel-lg {
        height: calc(70% - 8px);
        background-color: #FFF;
    }
    .btn-default {
        height: 30px;
    }
</style>