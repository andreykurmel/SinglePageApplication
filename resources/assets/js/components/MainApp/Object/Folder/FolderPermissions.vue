<template>
    <div class="container-fluid full-height">
        <div class="row full-height permissions-tab">
            <div class="col-xs-12 col-md-4 full-height" style="width: 40%;padding-right: 0;">
                <span v-if="hasNewPermis" class="new_present">(Please check and save Shared Tables Tree before new rows will be stored)</span>
                <div class="top-text">
                    <span>Settings</span>
                </div>
                <div class="permissions-panel no-padding">
                    <div class="full-frame">
                        <custom-table
                                :cell_component_name="'custom-cell-settings-permission'"
                                :global-meta="folderMeta"
                                :table-meta="settingsMeta['folder_permissions']"
                                :all-rows="folderPermissions"
                                :rows-count="folderPermissions.length"
                                :cell-height="1"
                                :is-full-width="true"
                                :behavior="'folder_permission_group'"
                                :user="$root.user"
                                :adding-row="addingRow"
                                :selected-row="selectedGroup"
                                :forbidden-columns="$root.systemFields"
                                :special_extras="{cannot_delete_system: true}"
                                @added-row="addGroup"
                                @updated-row="updateGroup"
                                @delete-row="deleteGroup"
                                @row-index-clicked="rowIndexClickedGroup"
                        ></custom-table>
                    </div>
                </div>
            </div>
            <div class="col-xs-12 col-md-8 full-height" style="width: 60%;">
                <div class="top-text">
                    <span>Tables & Permissions ( <span>{{ selectedPermissionName }}</span> ). Click on a table to Assign Permissions.</span>
                </div>
                <div class="permissions-panel no-padding">
                    <div v-if="selectedGroup > -1" class="full-height">
                        <folder-permissions-tree
                            :is_system="selectedPermission.is_system"
                            :user_group_id="selectedPermission.user_group_id"
                            :is_active="selectedPermission.is_f_active"
                            :is_app="selectedPermission.is_f_apps"
                            :tree="folderMeta._sub_tree"
                            :checked_tables="selectedPermission._checked_tables"
                            :assigned_permissions="selectedPermission._assigned_permissions"
                            :button_style="{top: '-36px'}"
                            @assigned-new-permission="reloadPermisAssigned"
                            @changed-shared-tables="reloadPermisAssigned"
                            @open-permis-assign="openPermisAssign"
                        ></folder-permissions-tree>
                    </div>
                </div>
            </div>
        </div>

        <!--Popup for assign permissions-->
        <permissions-settings-popup
                v-if="assignTableMeta"
                :table-meta="assignTableMeta"
                :user="$root.user"
                :init_show="true"
                @hidden-form="reloadPermisAssigned()"
        ></permissions-settings-popup>

        <!--Popup for adding column links-->
        <grouping-settings-popup
                v-if="assignTableMeta"
                :table-meta="assignTableMeta"
                :user="$root.user"
        ></grouping-settings-popup>

        <!--Popup for showing ref conditions -->
        <ref-conditions-popup
                v-if="assignTableMeta"
                :table-meta="assignTableMeta"
                :user="$root.user"
                :table_id="assignTableMeta ? assignTableMeta.id : null"
        ></ref-conditions-popup>
    </div>
</template>

<script>
    import {eventBus} from './../../../../app';

    import CustomTable from './../../../CustomTable/CustomTable';
    import FolderPermissionsTree from './FolderPermissionsTree';
    import PermissionsSettingsPopup from "../../../CustomPopup/PermissionsSettingsPopup";
    import GroupingSettingsPopup from "../../../CustomPopup/GroupingSettingsPopup";
    import RefConditionsPopup from "../../../CustomPopup/RefConditionsPopup";

    export default {
        name: "FolderPermissions",
        components: {
            RefConditionsPopup,
            GroupingSettingsPopup,
            PermissionsSettingsPopup,
            CustomTable,
            FolderPermissionsTree,
        },
        data: function () {
            return {
                forbiddenColumns: [
                    'shared',
                    'notes',
                    'created_on',
                    'created_by',
                    'modified_on',
                    'modified_by'
                ],
                selectedGroup: -1,
                addingRow: {
                    active: true,
                    position: 'bottom'
                },
                folderPermissions: [],
                permis_changed: false,
                assignTableMeta: null,
            }
        },
        props: {
            folderMeta: Object,
            settingsMeta: Object,
        },
        computed: {
            selectedPermission() {
                return this.folderPermissions[this.selectedGroup];
            },
            selectedPermissionName() {
                return this.selectedPermission ? this.selectedPermission.name : '';
            },
            hasNewPermis() {
                return _.find(this.folderPermissions, (permis) => {
                    return permis._checked_tables && !permis._checked_tables.length;
                });
            },
        },
        methods: {
            rowIndexClickedGroup(index) {
                this.selectedGroup = -1;
                this.$nextTick(() => {
                    this.selectedGroup = index;
                });
            },

            //Assign Table Permissions
            openPermisAssign(table_id) {
                $.LoadingOverlay('show');
                axios.post('/ajax/table-data/get-headers', {
                    table_id: table_id,
                    user_id: this.$root.user.id,
                }).then(({ data }) => {
                    this.assignTableMeta = data;
                }).catch(errors => {
                    Swal('Info', getErrors(errors));
                }).finally(() => {
                    $.LoadingOverlay('hide');
                });
            },
            reloadPermisAssigned() {
                if (this.assignTableMeta) {
                    let metaTB = _.find(this.$root.settingsMeta.available_tables, {id: Number(this.assignTableMeta.id)});
                    if (metaTB) {
                        metaTB._table_permissions = this.assignTableMeta._table_permissions;
                    }
                }
                this.assignTableMeta = null;
                axios.post('/ajax/user-group/reload', {}).then(({ data }) => {
                    let tmp = this.selectedGroup;
                    this.selectedGroup = -1;

                    this.$root.user._user_groups = data._user_groups;
                    this.$root.user._sys_user_groups = data._sys_user_groups;
                    this.buildFolderPermissions();

                    this.$nextTick(() => {
                        this.selectedGroup = tmp;
                    });
                }).catch(errors => {
                    Swal('Info', getErrors(errors));
                });
            },

            //Work with groups
            addGroup(folderPermis) {
                let u_group = _.find(this.$root.user._user_groups, {id: Number(folderPermis.user_group_id)});
                if (u_group) {
                    u_group._new_folder_permis = 1;
                    this.selectedGroup = -1;
                    this.buildFolderPermissions();
                } else {
                    Swal('Info', 'Error.');
                }
            },
            //update all 'is_active' and 'is_app' in `user_groups_2_table_permissions` (checked_tables are the same)
            updateGroup(folderPermis) {
                $.LoadingOverlay('show');
                axios.post('/ajax/folder/permission/tables', {
                    user_group_id: folderPermis.user_group_id,
                    is_active: folderPermis.is_f_active ? 1 : 0,
                    is_app: folderPermis.is_f_apps ? 1 : 0,
                    checked_tables: _.map(folderPermis._checked_tables, (el) => { return el.table_id}),
                    old_tables: _.map(folderPermis._checked_tables, (el) => { return el.table_id}),
                }).then(({ data }) => {
                    this.reloadPermisAssigned();
                }).catch(errors => {
                    Swal('Info', getErrors(errors));
                }).finally(() => {
                    $.LoadingOverlay('hide');
                });
            },
            //uncheck all 'is_active' and 'is_app' in `user_groups_2_table_permissions` (checked_tables)
            deleteGroup(folderPermis) {
                $.LoadingOverlay('show');
                axios.post('/ajax/folder/permission/tables', {
                    user_group_id: folderPermis.user_group_id,
                    is_active: 0,
                    is_app: 0,
                    checked_tables: [],
                    old_tables: _.map(folderPermis._checked_tables, (el) => { return el.table_id}),
                }).then(({ data }) => {
                    this.reloadPermisAssigned();
                }).catch(errors => {
                    Swal('Info', getErrors(errors));
                }).finally(() => {
                    $.LoadingOverlay('hide');
                });
            },
            buildFolderPermissions() {
                this.folderPermissions = [];

                _.each(this.$root.user._sys_user_groups, (u_group) => {
                    let filtered_checked = _.filter(u_group._tables_shared, (tb) => {
                        return $.inArray(tb.table_id, this.folderMeta._all_table_ids) > -1;
                    });
                    if (! filtered_checked.length) {
                        filtered_checked = _.map(this.folderMeta._all_table_ids, (id) => {
                            return {table_id: id, is_active: 1};
                        });
                    }

                    this.folderPermissions.push({
                        user_group_id: u_group.id,
                        name: u_group.name,
                        is_system: 1,
                        is_f_active: 1,
                        is_f_apps: 0,
                        _checked_tables: filtered_checked,
                        _assigned_permissions: [],
                    });
                });

                _.each(this.$root.user._user_groups, (u_group) => {
                    let filtered_checked = _.filter(u_group._tables_shared, (tb) => {
                        return $.inArray(tb.table_id, this.folderMeta._all_table_ids) > -1;
                    });
                    
                    if (filtered_checked.length || u_group._new_folder_permis) {

                        this.folderPermissions.push({
                            user_group_id: u_group.id,
                            name: u_group.name,
                            is_f_active: _.findIndex(filtered_checked, {is_active: 1}) > -1, //has active item
                            is_f_apps: filtered_checked.length && _.findIndex(filtered_checked, {is_app: 0}) === -1, //all items are 'app'
                            _checked_tables: filtered_checked,
                            _assigned_permissions: u_group._table_permissions
                        });
                        
                    }
                });
            },

            userGroupChangedHandler() {
                this.selectedGroup = -1;
                this.buildFolderPermissions();
            }
        },
        mounted() {
            this.buildFolderPermissions();

            eventBus.$on('user-group-added', this.userGroupChangedHandler);
            eventBus.$on('user-group-deleted', this.userGroupChangedHandler);
        },
        beforeDestroy() {
            eventBus.$off('user-group-added', this.userGroupChangedHandler);
            eventBus.$off('user-group-deleted', this.userGroupChangedHandler);
        }
    }
</script>

<style lang="scss" scoped>
    @import "../Table/SettingsModule/TabSettingsPermissions";
    .new_present {
        color: red;
        font-size: 13px;
        position: absolute;
        bottom: 5px;
        z-index: 100;
        left: 7px;
    }
</style>