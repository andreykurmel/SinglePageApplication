<template>
    <div class="container-fluid full-height">
        <div class="row full-height permissions-tab">
            <div class="col-xs-12 col-md-7 full-height" style="padding-right: 0;">
                <div class="top-text">
                    <span>Folder Views</span>
                </div>
                <div class="permissions-panel no-padding" style="height: calc(50% - 30px)">
                    <div class="full-frame">
                        <custom-table
                            :adding-row="addingRow"
                            :all-rows="folderMeta._folder_views"
                            :available-columns="tableViewAva"
                            :behavior="'folder_view'"
                            :cell-height="1"
                            :cell_component_name="'custom-cell-folder-view'"
                            :global-meta="folderMeta"
                            :is-full-width="true"
                            :rows-count="folderMeta._folder_views.length"
                            :selected-row="selectedView"
                            :table-meta="$root.settingsMeta['folder_views']"
                            :user="$root.user"
                            @added-row="addGroup"
                            @updated-row="updateGroup"
                            @delete-row="deleteGroup"
                            @row-index-clicked="rowIndexClickedGroup"
                        ></custom-table>
                    </div>
                </div>
                <div class="top-text">
                    <span>Settings of FolderView:&nbsp;<span>{{ selectedViewName }}</span></span>
                </div>
                <div class="permissions-panel full-frame" style="height: calc(50% - 35px)">
                    <vertical-table
                        v-if="selectedView > -1 && folderMeta._folder_views[selectedView]"
                        :available-columns="colViewAvailable"
                        :behavior="'folder_views'"
                        :cell-height="1"
                        :global-meta="folderMeta"
                        :max-cell-rows="0"
                        :no_height_limit="true"
                        :settings-meta="$root.settingsMeta"
                        :table-meta="$root.settingsMeta['folder_views']"
                        :table-row="folderMeta._folder_views[selectedView]"
                        :td="'custom-cell-folder-view'"
                        :user="$root.user"
                        :widths="{name: '35%', col: '65%', history: 0, no_margins: true}"
                        @updated-cell="updateGroup"
                    ></vertical-table>
                </div>
            </div>
            <div class="col-xs-12 col-md-5 full-height">
                <div class="top-text">
                    <span>Tables and assocaited MRVs(click to edit) of FolderView:&nbsp;<span>{{
                            selectedViewName
                        }}</span></span>
                </div>
                <div class="permissions-panel no-padding">
                    <div v-if="selectedView > -1" class="full-frame">
                        <folder-views-tree
                            :assigned_views="folderMeta._folder_views[selectedView]._assigned_view_names"
                            :checked_tables="folderMeta._folder_views[selectedView]._checked_tables"
                            :folder_view_id="folderMeta._folder_views[selectedView].id"
                            :tree="folderMeta._sub_tree"
                            @updated-views="updatedViews"
                            @open-view-assign="openViewAssign"
                        ></folder-views-tree>
                    </div>
                </div>
            </div>
        </div>

        <!--Popup for assign views-->
        <table-views-popup
            v-if="viewTbMeta"
            :table-meta="viewTbMeta"
            :is-limited="'MRV'"
            :init_show="true"
            @popup-close="viewTbMeta = null"
        ></table-views-popup>

        <!--Popup for assign permissions-->
        <permissions-settings-popup
            v-if="viewTbMeta"
            :table-meta="viewTbMeta"
            :user="$root.user"
        ></permissions-settings-popup>

        <!--Popup for adding column links-->
        <grouping-settings-popup
            v-if="viewTbMeta"
            :table-meta="viewTbMeta"
            :user="$root.user"
        ></grouping-settings-popup>

        <!--Popup for showing ref conditions -->
        <ref-conditions-popup
            v-if="viewTbMeta"
            :table-meta="viewTbMeta"
            :user="$root.user"
            :table_id="viewTbMeta ? viewTbMeta.id : null"
        ></ref-conditions-popup>
    </div>
</template>

<script>
import CustomTable from './../../../CustomTable/CustomTable';
import FolderViewsTree from './FolderViewsTree';
import TableViewsPopup from "../../../CustomPopup/TableViewsPopup.vue";
import PermissionsSettingsPopup from "../../../CustomPopup/PermissionsSettingsPopup.vue";
import RefConditionsPopup from "../../../CustomPopup/RefConditionsPopup.vue";
import GroupingSettingsPopup from "../../../CustomPopup/GroupingSettingsPopup.vue";

export default {
    name: "FolderViews",
    components: {
        GroupingSettingsPopup,
        RefConditionsPopup,
        PermissionsSettingsPopup,
        TableViewsPopup,
        CustomTable,
        FolderViewsTree,
    },
    data: function () {
        return {
            viewTbMeta: null,
            selectedView: -1,
            addingRow: {
                active: true,
                position: 'bottom'
            },
            tableViewAva: [
                'name',
                'is_active',
            ],
            colViewAvailable: [
                'def_table_id',
                'side_top',
                'side_left_menu',
                'side_left_filter',
                'side_right',
                'is_locked',
                'lock_pass',
                'hash',
            ],
        }
    },
    props: {
        folderMeta: Object,
        cellHeight: Number
    },
    computed: {
        selectedViewName() {
            if (this.selectedView > -1) {
                let ug_id = this.folderMeta._folder_views[this.selectedView].user_group_id;
                let group = _.find(this.$root.user._user_groups, {id: Number(ug_id)});
                return group ? group.name : this.folderMeta._folder_views[this.selectedView].name;
            } else {
                return '';
            }
        }
    },
    methods: {
        rowIndexClickedGroup(index) {
            this.selectedView = -1;
            this.$nextTick(() => {
                this.selectedView = index;
            });
        },
        addGroup(folderRow) {
            $.LoadingOverlay('show');
            axios.post('/ajax/folder/view', {
                name: folderRow.name,
                user_link: folderRow.user_link,
                folder_id: this.folderMeta.id,
            }).then(({data}) => {
                if (this.folderMeta._folder_views) {
                    this.folderMeta._folder_views.push(data);
                }
                this.selectedView = -1;
            }).catch(errors => {
                Swal('Info', getErrors(errors));
            }).finally(() => {
                $.LoadingOverlay('hide');
            });
        },
        updateGroup(folderRow) {
            let row_id = folderRow.id;
            let fields = _.cloneDeep(folderRow);//copy object
            this.$root.deleteSystemFields(fields);

            $.LoadingOverlay('show');
            axios.put('/ajax/folder/view', {
                folder_view_id: row_id,
                fields: fields,
            }).then(({data}) => {
            }).catch(errors => {
                Swal('Info', getErrors(errors));
            }).finally(() => {
                $.LoadingOverlay('hide');
            });
        },
        deleteGroup(folderRow) {
            $.LoadingOverlay('show');
            axios.delete('/ajax/folder/view', {
                params: {
                    folder_view_id: folderRow.id
                }
            }).then(({data}) => {
                let idx = _.findIndex(this.folderMeta._folder_views, {id: folderRow.id});
                if (idx > -1) {
                    this.selectedView = -1;
                    this.folderMeta._folder_views.splice(idx, 1);
                }
            }).catch(errors => {
                Swal('Info', getErrors(errors));
            }).finally(() => {
                $.LoadingOverlay('hide');
            });
        },
        updatedViews(new_views) {
            let upRow = this.folderMeta._folder_views[this.selectedView];
            if (upRow) {
                upRow._checked_tables = new_views;
                if (upRow.def_table_id && !_.find(new_views, {id: Number(upRow.def_table_id)})) {
                    upRow.def_table_id = null;
                }
            }
            this.rowIndexClickedGroup(this.selectedView);
        },
        //Assign Table Views
        openViewAssign(table_id) {
            $.LoadingOverlay('show');
            axios.post('/ajax/table-data/get-headers', {
                table_id: table_id,
                user_id: this.$root.user.id,
            }).then(({ data }) => {
                this.viewTbMeta = data;
            }).catch(errors => {
                Swal('Info', getErrors(errors));
            }).finally(() => {
                $.LoadingOverlay('hide');
            });
        },
    },
    mounted() {
    }
}
</script>

<style lang="scss" scoped>
@import "../Table/SettingsModule/TabSettingsPermissions";
</style>