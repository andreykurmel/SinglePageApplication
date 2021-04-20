<template>
    <div class="container-fluid full-height">
        <div v-if="!$root.AddonAvailableToUser(tableMeta, 'request')" class="row full-frame flex flex--center">
            <label>Addon is unavailable!</label>
        </div>
        <div v-else="" class="row full-height permissions-tab">
            <!--LEFT SIDE-->
            <div class="col-xs-5 full-height" style="padding-right: 0;">
                <div class="top-text">
                    <span>Data Collection & Request (DCR)</span>
                </div>
                <div class="permissions-panel no-padding">
                    <custom-table
                        v-if="isVisible"
                        :cell_component_name="'custom-cell-settings-permission'"
                        :global-meta="tableMeta"
                        :table-meta="$root.settingsMeta['table_permissions']"
                        :all-rows="tableMeta._table_requests"
                        :rows-count="tableMeta._table_requests.length"
                        :cell-height="$root.cellHeight"
                        :max-cell-rows="$root.maxCellRows"
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
                    <!--<vertical-rows-table-->
                        <!--v-if="isVisible"-->
                        <!--:cell_component_name="'custom-cell-settings-permission'"-->
                        <!--:global-meta="tableMeta"-->
                        <!--:table-meta="$root.settingsMeta['table_permissions']"-->
                        <!--:all-rows="tableMeta._table_requests"-->
                        <!--:page="1"-->
                        <!--:rows-count="tableMeta._table_requests.length"-->
                        <!--:cell-height="$root.cellHeight"-->
                        <!--:max-cell-rows="$root.maxCellRows"-->
                        <!--:is-full-width="true"-->
                        <!--:with_edit="tableMeta._is_owner"-->
                        <!--:behavior="'table_permission_group'"-->
                        <!--:user="user"-->
                        <!--:adding-row="addingRow"-->
                        <!--:selected-row="selectedGroup"-->
                        <!--:available-columns="requestAvailable"-->
                        <!--@added-row="addGroup"-->
                        <!--@updated-row="updateGroup"-->
                        <!--@delete-row="deleteGroup"-->
                        <!--@row-index-clicked="rowIndexClickedGroup"-->
                    <!--&gt;</vertical-rows-table>-->
                </div>
            </div>
            <!--RIGHT SIDE-->
            <div class="col-xs-7 full-height">
                <div class="top-text">
                    <span>Specifications of Current DCR ( <span>{{ (selectedGroup > -1 ? tableMeta._table_requests[selectedGroup].name : '') }}</span> )</span>
                </div>
                <div class="permissions-panel">
                    <div class="permissions-menu-header">
                        <button class="btn btn-default btn-sm" :class="{active : activeRightTab === 'design'}" @click="activeRightTab = 'design'">
                            Design
                        </button>
                        <button class="btn btn-default btn-sm" :class="{active : activeRightTab === 'columns'}" @click="activeRightTab = 'columns'">
                            Columns
                        </button>
                        <button class="btn btn-default btn-sm" :class="{active : activeRightTab === 'defaults'}" @click="activeRightTab = 'defaults'">
                            Defaults
                        </button>
                        <button class="btn btn-default btn-sm" :class="{active : activeRightTab === 'actions'}" @click="activeRightTab = 'actions'">
                            Action & Status
                        </button>
                        <button class="btn btn-default btn-sm" :class="{active : activeRightTab === 'notifs'}" @click="activeRightTab = 'notifs'">
                            Notifications
                        </button>
                    </div>
                    <div class="permissions-menu-body">

                        <div class="full-frame defaults-tab" v-show="activeRightTab === 'design' && selectedGroup > -1" style="padding: 0">
                            <tab-settings-requests-row
                                    :table_id="tableMeta.id"
                                    :table-meta="tableMeta"
                                    :cell-height="$root.cellHeight"
                                    :max-cell-rows="$root.maxCellRows"
                                    :table-request="$root.settingsMeta['table_permissions']"
                                    :request-row="selectedGroup > -1 ? tableMeta._table_requests[selectedGroup] : {}"
                                    :with_edit="tableMeta._is_owner"
                                    @updated-cell="updateGroup"
                                    @upload-file="uploadGroupFile"
                                    @del-file="delGroupFile"
                            ></tab-settings-requests-row>
                        </div>

                        <div class="full-frame" v-show="activeRightTab === 'columns'">
                            <custom-table
                                :cell_component_name="'custom-cell-settings-permission'"
                                :global-meta="tableMeta"
                                :table-meta="$root.settingsMeta['table_permissions_2_table_column_groups']"
                                :all-rows="selectedGroup > -1 ? tableMeta._table_requests[selectedGroup]._permission_columns : null"
                                :rows-count="selectedGroup > -1 ? tableMeta._table_requests[selectedGroup]._permission_columns.length : 0"
                                :cell-height="$root.cellHeight"
                                :max-cell-rows="$root.maxCellRows"
                                :is-full-width="true"
                                :with_edit="tableMeta._is_owner"
                                :adding-row="selectedGroup > -1 ? addingRow : {active: false}"
                                :user="user"
                                :show-info-key="'add_notes'"
                                :behavior="'permission_group_col'"
                                :forbidden-columns="$root.systemFields"
                                :excluded_row_values="[{ field: 'view', excluded: [0] }]"
                                @added-row="addGroupColumnPermis"
                                @updated-row="updateGroupColumnPermis"
                                @delete-row="deleteGroupColumnPermis"
                            ></custom-table>
                        </div>

                        <div class="full-frame defaults-tab" v-show="activeRightTab === 'defaults'">
                            <default-fields-table
                                    v-if="selectedGroup > -1 && activeRightTab === 'defaults'"
                                    :table-permission-id="tableMeta._table_requests[selectedGroup].id"
                                    :user-group-id="null"
                                    :table-meta="tableMeta"
                                    :default-fields="tableMeta._table_requests[selectedGroup]._default_fields"
                                    :user="user"
                                    :with_edit="tableMeta._is_owner"
                                    :cell-height="$root.cellHeight"
                                    :max-cell-rows="$root.maxCellRows"
                            ></default-fields-table>
                        </div>

                        <div class="full-frame defaults-tab" v-show="activeRightTab === 'actions' && selectedGroup > -1">
                            <tab-settings-submission-row
                                    :table-meta="tableMeta"
                                    :table_id="tableMeta.id"
                                    :cell-height="$root.cellHeight"
                                    :max-cell-rows="$root.maxCellRows"
                                    :table-request="$root.settingsMeta['table_permissions']"
                                    :request-row="selectedGroup > -1 ? tableMeta._table_requests[selectedGroup] : {}"
                                    :with_edit="tableMeta._is_owner"
                                    @updated-cell="updateGroup"
                            ></tab-settings-submission-row>
                        </div>

                        <div class="full-frame defaults-tab" v-show="activeRightTab === 'notifs' && selectedGroup > -1" style="padding: 0;">
                            <tab-settings-request-notifs
                                    :table_id="tableMeta.id"
                                    :cell-height="$root.cellHeight"
                                    :max-cell-rows="$root.maxCellRows"
                                    :table-request="$root.settingsMeta['table_permissions']"
                                    :request-row="selectedGroup > -1 ? tableMeta._table_requests[selectedGroup] : {}"
                                    :with_edit="tableMeta._is_owner"
                                    @updated-cell="updateGroup"
                            ></tab-settings-request-notifs>
                        </div>

                    </div>
                </div>
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

    export default {
        name: "TabSettingsRequests",
        components: {
            TabSettingsRequestNotifs,
            VerticalRowsTable,
            TabSettingsSubmissionRow,
            TabSettingsRequestsRow,
            VerticalTable,
            CustomTable,
            DefaultFieldsTable,
        },
        data: function () {
            return {
                activeRightTab: 'design',
                selectedGroup: -1,
                addingRow: {
                    active: true,
                    position: 'bottom'
                },
                requestAvailable: [
                    'name',
                    'pass',
                    //'user_link',
                    'active',
                    '_embed_dcr',
                ],
            }
        },
        props:{
            tableMeta: Object,
            cellHeight: Number,
            maxCellRows: Number,
            table_id: Number|null,
            user:  Object,
            isVisible: Boolean,
        },
        watch: {
            table_id: function(val) {
                this.selectedGroup = -1;
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
                fields.is_request = 1;

                axios.post('/ajax/table-permission', {
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

                axios.put('/ajax/table-permission', {
                    table_permission_id: group_id,
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
                axios.delete('/ajax/table-permission', {
                    params: {
                        table_permission_id: tableRow.id
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
                formData.append('table_permission_id', group_id);
                formData.append('field', field);
                formData.append('u_file', file);
                axios.post('/ajax/table-permission/dcr-file', formData, {
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
                axios.delete('/ajax/table-permission/dcr-file', {
                    params: {
                        table_permission_id: group_id,
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
                this.updateGroupColumn(tableColumn.table_column_group_id, 1, 0, undefined);
            },
            updateGroupColumnPermis(tableColumn) {
                this.updateGroupColumn(tableColumn.table_column_group_id, tableColumn.view, tableColumn.edit, tableColumn.shared);
            },
            deleteGroupColumnPermis(tableColumn) {
                this.updateGroupColumn(tableColumn.table_column_group_id, 0, 0, undefined);
            },
            shareGroupColumn(column_id, shared) {
                this.updateGroupColumn(column_id, undefined, undefined, shared);
            },
            updateGroupColumn(tb_column_group_id, viewed, edited, shared) {

                let permis_columns = this.tableMeta._table_requests[this.selectedGroup]._permission_columns;
                let columnGr = _.find(permis_columns, {table_column_group_id: Number(tb_column_group_id)});

                if (columnGr) {
                    viewed = viewed === undefined ? columnGr.view : viewed;
                    edited = edited === undefined ? columnGr.edit : edited;
                    shared = shared === undefined ? columnGr.shared : shared;
                }

                this.$root.sm_msg_type = 1;
                axios.post('/ajax/table-permission/column', {
                    table_permission_id: this.tableMeta._table_requests[this.selectedGroup].id,
                    table_column_group_id: tb_column_group_id,
                    view: viewed ? 1 : 0,
                    edit: edited ? 1 : 0,
                    shared: shared ? 1 : 0,
                }).then(({ data }) => {
                    let idx = _.findIndex(permis_columns, (el) => {
                        return el.table_column_group_id == tb_column_group_id;
                    });
                    if (idx > -1) {
                        permis_columns.splice(idx, 1 ,data);
                    } else {
                        permis_columns.push( data );
                    }
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
</style>