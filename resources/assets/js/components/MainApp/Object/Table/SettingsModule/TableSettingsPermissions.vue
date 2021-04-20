<template>
    <div class="container-fluid full-height">
        <div class="row full-height permissions-tab">
            <!--LEFT SIDE-->
            <div class="col-xs-6 full-height" style="padding-right: 0;">
                <div class="top-text">
                    <span>Permissions list</span>
                    <button class="btn btn-default btn-sm blue-gradient"
                            :style="$root.themeButtonStyle"
                            style="float: right;padding: 0 3px;"
                            @click="show_copy_permis = true"
                            title="Copy Permission">Copy</button>
                </div>
                <div class="permissions-panel no-padding" style="height: calc(50% - 30px);">
                    <custom-table
                        :cell_component_name="'custom-cell-settings-permission'"
                        :global-meta="tableMeta"
                        :table-meta="$root.settingsMeta['table_permissions']"
                        :all-rows="tableMeta._table_permissions"
                        :rows-count="tableMeta._table_permissions.length"
                        :cell-height="$root.cellHeight"
                        :max-cell-rows="$root.maxCellRows"
                        :is-full-width="true"
                        :behavior="'table_permission_group'"
                        :user="user"
                        :adding-row="addingRow"
                        :selected-row="selectedGroup"
                        :available-columns="permissionAvailable"
                        :use_theme="true"
                        :no_width="true"
                        @added-row="addGroup"
                        @updated-row="updateGroup"
                        @delete-row="deleteGroup"
                        @row-index-clicked="rowIndexClickedGroup"
                    ></custom-table>
                </div>

                <div class="top-text">
                    <span>Assigning Permissions</span>
                </div>
                <div class="permissions-panel no-padding" style="height: calc(50% - 35px);">
                    <custom-table
                            :cell_component_name="'custom-cell-settings-permission'"
                            :global-meta="tableMeta"
                            :table-meta="$root.settingsMeta['user_groups_2_table_permissions']"
                            :all-rows="tableMeta._user_groups_2_table_permissions"
                            :rows-count="tableMeta._user_groups_2_table_permissions.length"
                            :cell-height="$root.cellHeight"
                            :max-cell-rows="$root.maxCellRows"
                            :is-full-width="true"
                            :behavior="'table_permission_group'"
                            :user="user"
                            :adding-row="addingRow"
                            :use_theme="true"
                            :widths_div="2"
                            @added-row="addUserGroup"
                            @updated-row="updateUserGroup"
                            @delete-row="deleteUserGroup"
                            @show-def-val-popup="showDefValPopup"
                    ></custom-table>
                </div>
            </div>
            <!--RIGHT-->
            <div class="col-xs-6 full-height" style="">
                <div class="top-text">
                    <span>Permission Details ( <span>{{ (selectedGroup > -1 ? tableMeta._table_permissions[selectedGroup].name : '') }}</span> )</span>

                    <info-sign-link
                            class="right-elem"
                            :app_sett_key="'help_link_settings_permissions'"
                            :hgt="26"
                    ></info-sign-link>
                </div>
                <div class="permissions-panel">
                    <div class="permissions-menu-header">
                        <button class="btn btn-default btn-sm" :class="{active : activeRightTab === 'data_range'}" @click="activeRightTab = 'data_range'">
                            Data Range
                        </button>
                        <button class="btn btn-default btn-sm" :class="{active : activeRightTab === 'others'}" @click="activeRightTab = 'others'">
                            Operational Privileges
                        </button>
                    </div>
                    <div class="permissions-menu-body">

                        <div class="full-frame" v-show="activeRightTab === 'data_range'">
                            <custom-table
                                :cell_component_name="'custom-cell-settings-permission'"
                                :global-meta="tableMeta"
                                :table-meta="$root.settingsMeta['table_permissions_2_table_column_groups']"
                                :all-rows="selectedGroup > -1 ? tableMeta._table_permissions[selectedGroup]._permission_columns : null"
                                :rows-count="selectedGroup > -1 ? tableMeta._table_permissions[selectedGroup]._permission_columns.length : 0"
                                :cell-height="$root.cellHeight"
                                :max-cell-rows="$root.maxCellRows"
                                :is-full-width="true"
                                :adding-row="selectedGroup > -1 ? addingRow : {active: false}"
                                :user="user"
                                :behavior="'permission_group_col'"
                                :forbidden-columns="$root.systemFields"
                                :use_theme="true"
                                :widths_div="2"
                                :excluded_row_values="[{ field: 'view', excluded: [0] }]"
                                :style="{height: '50%'}"
                                @added-row="addGroupColumnPermis"
                                @updated-row="updateGroupColumnPermis"
                                @delete-row="deleteGroupColumnPermis"
                            ></custom-table>

                            <custom-table
                                v-if="selectedGroup > -1"
                                :cell_component_name="'custom-cell-settings-permission'"
                                :global-meta="tableMeta"
                                :table-meta="$root.settingsMeta['table_permissions_2_table_row_groups']"
                                :all-rows="selectedGroup > -1 ? tableMeta._table_permissions[selectedGroup]._permission_rows : null"
                                :rows-count="selectedGroup > -1 ? tableMeta._table_permissions[selectedGroup]._permission_rows.length : 0"
                                :cell-height="$root.cellHeight"
                                :max-cell-rows="$root.maxCellRows"
                                :is-full-width="true"
                                :adding-row="selectedGroup > -1 ? addingRow : {active: false}"
                                :user="user"
                                :behavior="'permission_group_row'"
                                :forbidden-columns="$root.systemFields"
                                :use_theme="true"
                                :widths_div="2"
                                :excluded_row_values="[{ field: 'view', excluded: [0] }]"
                                :style="{height: '50%'}"
                                @added-row="addGroupRowPermis"
                                @updated-row="updateGroupRowPermis"
                                @delete-row="deleteGroupRowPermis"
                            ></custom-table>
                        </div>

                        <div class="full-frame others-tab" v-show="activeRightTab === 'others'" v-if="selectedGroup > -1">

                            <!----------- Basics -------------->

                            <tab-settings-permissions-basics
                                    :table-meta="tableMeta"
                                    :sel-permission="tableMeta._table_permissions[selectedGroup]"
                                    :user="user"
                                    @update-group="updateGroup"
                                    @share-group-row="shareGroupRow"
                                    @share-group-column="shareGroupColumn"
                            ></tab-settings-permissions-basics>
                        </div>

                    </div>
                </div>
            </div>
        </div>

        <default-fields-pop-up
            v-if="showDefVal"
            :table-permission-id="table_permis_id_def_fields"
            :user-group-id="user_group_id_def_fields"
            :table-meta="tableMeta"
            :default-fields="def_fields_arr"
            :user="user"
            :cell-height="$root.cellHeight"
            :max-cell-rows="$root.maxCellRows"
            @popup-close="hideDefValPopup"
        ></default-fields-pop-up>

        <copy-permission-from-table-popup
                v-if="show_copy_permis"
                :table-meta="tableMeta"
                @popup-close="closeCopyPermis"
        ></copy-permission-from-table-popup>
    </div>
</template>

<script>
    import {eventBus} from '../../../../../app';

    import TabSettingsPermissionsBasics from "./TabSettingsPermissionsBasics";
    import CustomTable from '../../../../CustomTable/CustomTable';
    import DefaultFieldsPopUp from '../../../../CustomPopup/DefaultFieldsPopUp';
    import InfoSignLink from "../../../../CustomTable/Specials/InfoSignLink";
    import CopyPermissionFromTablePopup from "../../../../CustomPopup/CopyPermissionFromTablePopup";

    export default {
        name: "TableSettingsPermissions",
        components: {
            CopyPermissionFromTablePopup,
            InfoSignLink,
            TabSettingsPermissionsBasics,
            CustomTable,
            DefaultFieldsPopUp,
        },
        data: function () {
            return {
                show_copy_permis: false,
                table_permis_id_def_fields: null,
                user_group_id_def_fields: null,
                def_fields_arr: [],

                activeRightTab: 'data_range',
                selectedGroup: this.init_ddl_idx,
                addingRow: {
                    active: true,
                    position: 'bottom'
                },
                showDefVal: false,
                permissionAvailable: [
                    'name',
                ],
            }
        },
        props:{
            tableMeta: Object,
            cellHeight: Number,
            maxCellRows: Number,
            table_id: Number|null,
            user:  Object,
            init_ddl_idx: {
                type: Number,
                default: -1,
            },
        },
        watch: {
            table_id: function(val) {
                this.selectedGroup = -1;
                this.activeRightTab = 'data_range';
            }
        },
        methods: {
            closeCopyPermis(new_permissions) {
                if (new_permissions) {
                    this.selectedGroup = -1;
                    this.activeRightTab = 'data_range';
                    this.tableMeta._table_permissions = new_permissions;
                }
                this.show_copy_permis = false;
            },
            rowIndexClickedGroup(index) {
                this.activeRightTab = 'data_range';
                this.selectedGroup = index;
            },
            //Table Permissions Functions
            addGroup(tableRow) {
                this.$root.sm_msg_type = 1;

                let fields = _.cloneDeep(tableRow);//copy object
                this.$root.deleteSystemFields(fields);
                fields.active = fields.active ? 1 : 0;
                fields.is_request = 0;

                axios.post('/ajax/table-permission', {
                    table_id: this.tableMeta.id,
                    fields: fields,
                }).then(({ data }) => {
                    this.tableMeta._table_permissions.push(data);
                    this.selectedGroup = -1;
                    this.$nextTick(() => {
                        this.selectedGroup = this.tableMeta._table_permissions.length-1;
                    });
                }).catch(errors => {
                    Swal('', getErrors(errors));
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
                    Swal('', getErrors(errors));
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
                    let idx = _.findIndex(this.tableMeta._table_permissions, {id: Number(tableRow.id)});
                    if (idx > -1) {
                        this.selectedGroup = -1;
                        this.tableMeta._table_permissions.splice(idx, 1);
                    }
                    this.tableMeta._user_groups_2_table_permissions = _.filter(this.tableMeta._user_groups_2_table_permissions, (item) => {
                        return item.table_permission_id != Number(tableRow.id);
                    });
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

                let permis_columns = this.tableMeta._table_permissions[this.selectedGroup]._permission_columns;
                let columnGr = _.find(permis_columns, (el) => {return el.table_column_group_id == tb_column_group_id});

                if (columnGr) {
                    viewed = viewed === undefined ? columnGr.view : viewed;
                    edited = edited === undefined ? columnGr.edit : edited;
                    shared = shared === undefined ? columnGr.shared : shared;
                }

                this.$root.sm_msg_type = 1;
                axios.post('/ajax/table-permission/column', {
                    table_permission_id: this.tableMeta._table_permissions[this.selectedGroup].id,
                    table_column_group_id: tb_column_group_id,
                    view: viewed ? 1 : 0,
                    edit: edited ? 1 : 0,
                    shared: shared ? 1 : 0,
                }).then(({ data }) => {
                    let idx = _.findIndex(permis_columns, (el) => {return el.table_column_group_id == tb_column_group_id});
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

            //User Group Rows Functions
            addGroupRowPermis(tableRow) {
                this.updateGroupRow(tableRow.table_row_group_id, 1, 0, 0, undefined);
            },
            updateGroupRowPermis(tableRow) {
                this.updateGroupRow(tableRow.table_row_group_id, tableRow.view, tableRow.edit, tableRow.delete, tableRow.shared);
            },
            deleteGroupRowPermis(tableRow) {
                this.updateGroupRow(tableRow.table_row_group_id, 0, 0, 0, undefined);
            },
            shareGroupRow(row_id, shared) {
                this.updateGroupRow(row_id, undefined, undefined, undefined, shared);
            },
            updateGroupRow(tb_row_group_id, viewed, edited, deleted, shared) {

                let permis_rows = this.tableMeta._table_permissions[this.selectedGroup]._permission_rows;
                let rowGr = _.find(permis_rows, (el) => {return el.table_row_group_id == tb_row_group_id});

                if (rowGr) {
                    viewed = viewed === undefined ? rowGr.view : viewed;
                    edited = edited === undefined ? rowGr.edit : edited;
                    deleted = deleted === undefined ? rowGr.delete : deleted;
                    shared = shared === undefined ? rowGr.shared : shared;
                }

                this.$root.sm_msg_type = 1;
                axios.post('/ajax/table-permission/row', {
                    table_permission_id: this.tableMeta._table_permissions[this.selectedGroup].id,
                    table_row_group_id: tb_row_group_id,
                    view: viewed ? 1 : 0,
                    edit: edited ? 1 : 0,
                    del: deleted ? 1 : 0,
                    shared: shared ? 1 : 0,
                }).then(({ data }) => {
                    let idx = _.findIndex(permis_rows, (el) => {return el.table_row_group_id == tb_row_group_id});
                    if (idx > -1) {
                        permis_rows.splice(idx, 1 ,data);
                    } else {
                        permis_rows.push( data );
                    }
                    this.$forceUpdate();
                }).catch(errors => {
                    Swal('', getErrors(errors));
                }).finally(() => {
                    this.$root.sm_msg_type = 0;
                });
            },

            //User Group Functions
            addUserGroup(tableRow) {
                this.$root.sm_msg_type = 1;
                axios.post('/ajax/table-permission/user-group', {
                    table_permission_id: tableRow.table_permission_id,
                    user_group_id: tableRow.user_group_id,
                    is_active: tableRow.is_active ? 1 : 0,
                }).then(({ data }) => {
                    this.tableMeta._user_groups_2_table_permissions.push( data );
                }).catch(errors => {
                    Swal('', getErrors(errors));
                }).finally(() => {
                    this.$root.sm_msg_type = 0;
                });
            },
            updateUserGroup(tableRow) {
                this.$root.sm_msg_type = 1;
                axios.put('/ajax/table-permission/user-group', {
                    table_permission_id: tableRow.table_permission_id,
                    user_group_id: tableRow.user_group_id,
                    is_active: tableRow.is_active ? 1 : 0,
                }).then(({ data }) => {
                }).catch(errors => {
                    Swal('', getErrors(errors));
                }).finally(() => {
                    this.$root.sm_msg_type = 0;
                });
            },
            deleteUserGroup(tableRow) {
                this.$root.sm_msg_type = 1;
                axios.delete('/ajax/table-permission/user-group', {
                    params: {
                        table_permission_id: tableRow.table_permission_id,
                        user_group_id: tableRow.user_group_id,
                    }
                }).then(({ data }) => {
                    let idx = _.findIndex(this.tableMeta._user_groups_2_table_permissions, {id: Number(tableRow.id)});
                    if (idx > -1) {
                        this.tableMeta._user_groups_2_table_permissions.splice(idx, 1);
                    }
                }).catch(errors => {
                    Swal('', getErrors(errors));
                }).finally(() => {
                    this.$root.sm_msg_type = 0;
                });
            },

            //Other
            showDefValPopup(row) {
                this.table_permis_id_def_fields = row.table_permission_id;
                this.user_group_id_def_fields = row.user_group_id;
                let permis = _.find(this.tableMeta._table_permissions, {id: Number(row.table_permission_id)});
                if (permis) {
                    this.def_fields_arr = _.filter(permis._default_fields, {user_group_id: Number(row.user_group_id)});
                    this.showDefVal = row.user_group_id && row.table_permission_id;
                }
            },
            hideDefValPopup() {
                let permis = _.find(this.tableMeta._table_permissions, {id: Number(this.table_permis_id_def_fields)});
                if (permis) {
                    _.each(this.def_fields_arr, (df) => {
                        if (!_.find(permis._default_fields, {id: Number(df.id)})) {
                            permis._default_fields.push(df);
                        }
                    });
                }
                this.showDefVal = false;
            },
            SettingsPermisSelectedOff() {
                this.selectedGroup = -1;
            },
        },
        mounted() {
            eventBus.$on('settings-permissions-selected-off', this.SettingsPermisSelectedOff);
        },
        beforeDestroy() {
            eventBus.$off('settings-permissions-selected-off', this.SettingsPermisSelectedOff);
        }
    }
</script>

<style lang="scss" scoped>
    @import "TabSettingsPermissions";
</style>