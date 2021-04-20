<template>
    <div class="container-fluid full-height">
        <div class="row full-height permissions-tab">
            <div class="col-xs-12 col-md-6 full-height" style="padding-right: 0;">
                <div class="top-text">
                    <span>Folder Views</span>
                </div>
                <div class="permissions-panel no-padding">
                    <div class="full-frame">
                        <custom-table
                                :cell_component_name="'custom-cell-folder-view'"
                                :global-meta="folderMeta"
                                :table-meta="$root.settingsMeta['folder_views']"
                                :all-rows="folderMeta._folder_views"
                                :rows-count="folderMeta._folder_views.length"
                                :cell-height="$root.cellHeight"
                                :is-full-width="true"
                                :behavior="'folder_view'"
                                :user="$root.user"
                                :adding-row="addingRow"
                                :selected-row="selectedView"
                                :available-columns="tableViewAva"
                                @added-row="addGroup"
                                @updated-row="updateGroup"
                                @delete-row="deleteGroup"
                                @row-index-clicked="rowIndexClickedGroup"
                        ></custom-table>
                    </div>
                </div>
            </div>
            <div class="col-xs-12 col-md-6 full-height">
                <div class="top-text">
                    <span>Tables and assocaited TableView of FolderView:<span>{{ selectedViewName }}</span>. Click to edit.</span>
                </div>
                <div class="permissions-panel no-padding" style="height: calc(50% - 35px)">
                    <div v-if="selectedView > -1" class="full-frame">
                        <folder-views-tree
                            :folder_view_id="folderMeta._folder_views[selectedView].id"
                            :tree="folderMeta._sub_tree"
                            :checked_tables="folderMeta._folder_views[selectedView]._checked_tables"
                            :assigned_views="folderMeta._folder_views[selectedView]._assigned_view_names"
                            @updated-views="updatedViews"
                        ></folder-views-tree>
                    </div>
                </div>
                <div class="top-text">
                    <span>Additional Details of FolderView:<span>{{ selectedViewName }}</span></span>
                </div>
                <div class="permissions-panel full-frame" style="height: calc(50% - 35px)">
                    <vertical-table
                            v-if="selectedView > -1 && folderMeta._folder_views[selectedView]"
                            :td="'custom-cell-folder-view'"
                            :global-meta="folderMeta"
                            :table-meta="$root.settingsMeta['folder_views']"
                            :settings-meta="$root.settingsMeta"
                            :table-row="folderMeta._folder_views[selectedView]"
                            :user="$root.user"
                            :fixed_ddl_pos="true"
                            :cell-height="$root.cellHeight"
                            :max-cell-rows="$root.maxCellRows"
                            :behavior="'folder_views'"
                            :available-columns="colViewAvailable"
                            :no_height_limit="true"
                            :widths="{name: '35%', col: '65%', history: 0, no_margins: true}"
                            @updated-cell="updateGroup"
                    ></vertical-table>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    import CustomTable from './../../../CustomTable/CustomTable';
    import FolderViewsTree from './FolderViewsTree';
    import VerticalTable from "../../../CustomTable/VerticalTable";

    export default {
        name: "FolderViews",
        components: {
            VerticalTable,
            CustomTable,
            FolderViewsTree,
        },
        data: function () {
            return {
                selectedView: -1,
                addingRow: {
                    active: true,
                    position: 'bottom'
                },
                tableViewAva: [
                    'name',
                    'def_table_id',
                    'user_link',
                ],
                colViewAvailable: [
                    'side_top',
                    'side_left_menu',
                    'side_left_filter',
                    'side_right',
                    'is_active',
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
                }).then(({ data }) => {
                    this.folderMeta._folder_views.push(data);
                    this.selectedView = -1;
                }).catch(errors => {
                    Swal('', getErrors(errors));
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
                }).then(({ data }) => {
                }).catch(errors => {
                    Swal('', getErrors(errors));
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
                }).then(({ data }) => {
                    let idx = _.findIndex(this.folderMeta._folder_views, {id: folderRow.id});
                    if (idx > -1) {
                        this.selectedView = -1;
                        this.folderMeta._folder_views.splice(idx, 1);
                    }
                }).catch(errors => {
                    Swal('', getErrors(errors));
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
            }
        },
        mounted() {
        }
    }
</script>

<style lang="scss" scoped>
    @import "../Table/SettingsModule/TabSettingsPermissions";
</style>