<template>
    <div class="container-fluid full-height">
        <div class="row full-height permissions-tab">
            <!--LEFT SIDE-->
            <div class="col-xs-7 full-height" style="padding-right: 0;">
                <div class="top-text" :style="textSysStyle">
                    <span>List</span>
                </div>
                <div class="permissions-panel no-padding vert-panel-lg">
                    <custom-table
                            :cell_component_name="'custom-cell-table-view'"
                            :global-meta="tableMeta"
                            :table-meta="$root.settingsMeta['table_views']"
                            :all-rows="tableMeta._views"
                            :rows-count="tableMeta._views.length"
                            :cell-height="1"
                            :max-cell-rows="0"
                            :is-full-width="true"
                            :user="$root.user"
                            :behavior="'table_views'"
                            :adding-row="addingRow"
                            :available-columns="tableViewAva"
                            :selected-row="selectedView"
                            :use_theme="true"
                            :no_height_limit="true"
                            :with_edit="!!canEditView"
                            @added-row="insertStart"
                            @updated-row="updateStart"
                            @delete-row="deleteStart"
                            @row-index-clicked="rowIndexClickedView"
                    ></custom-table>
                </div>
                <div class="top-text" :style="textSysStyle">
                    <span>Additional Details of View: <span>{{ (selectedView > -1 && tableMeta._views[selectedView] ? tableMeta._views[selectedView].name : '') }}</span></span>
                </div>
                <div class="permissions-panel vert-panel-sm">
                    <div class="full-frame" :style="textSysStyle">
                        <template v-if="selectedView > -1 && tableMeta._views[selectedView]">
                            <div><label style="font-size: 1.4em;">Filtering</label></div>
                            <div class="flex flex--center form-group" style="justify-content: left;">
                                <label class="switch_t" style="margin: 0 10px 0 0;">
                                    <input type="checkbox"
                                           :disabled="!canEditView"
                                           v-model="tableMeta._views[selectedView].view_filtering"
                                           @change="updateStart(tableMeta._views[selectedView])">
                                    <span class="toggler round" :class="{'disabled': !canEditView}"></span>
                                </label>
                                <span style="font-size: 1.2em;">Ask for inputs and apply filters to records for loading the view.</span>
                            </div>
                            <div v-if="tableMeta._views[selectedView].view_filtering" class="params-wrapper border-gray" style="height: calc(100% - 125px);">
                                <custom-table
                                        :cell_component_name="'custom-cell-table-view'"
                                        :global-meta="tableMeta"
                                        :table-meta="$root.settingsMeta['table_view_filtering']"
                                        :all-rows="tableMeta._views[selectedView]._filtering"
                                        :rows-count="tableMeta._views[selectedView]._filtering.length"
                                        :cell-height="1"
                                        :max-cell-rows="0"
                                        :is-full-width="true"
                                        :behavior="'table_views'"
                                        :user="$root.user"
                                        :adding-row="addingRow"
                                        :use_theme="true"
                                        @added-row="addFilteringHandler"
                                        @updated-row="updateFilteringHandler"
                                        @delete-row="deleteFilteringHandler"
                                ></custom-table>
                            </div>
                            <div>
                                <span style="font-size: 1.2em; justify-content: left;">Only records with the values for fields meeting the criteria comparing with entered or selected values for the fields will be loaded.</span>
                            </div>
                        </template>
                    </div>
                </div>
            </div>
            <!--RIGHT SIDE-->
            <div class="col-xs-5 full-height">
                <div class="top-text" :style="textSysStyle">
                    <span>Additional Details of View: <span>{{ (selectedView > -1 && tableMeta._views[selectedView] ? tableMeta._views[selectedView].name : '') }}</span></span>
                </div>
                <div class="permissions-panel full-frame">
                    <template v-if="selectedView > -1 && tableMeta._views[selectedView]">
                        <vertical-table
                                :td="'custom-cell-table-view'"
                                :global-meta="tableMeta"
                                :table-meta="$root.settingsMeta['table_views']"
                                :settings-meta="$root.settingsMeta"
                                :table-row="tableMeta._views[selectedView]"
                                :user="$root.user"
                                :cell-height="1"
                                :max-cell-rows="0"
                                :behavior="'table_views'"
                                :available-columns="colViewAvailable"
                                :no_height_limit="true"
                                :widths="{name: '35%', col: '65%', history: 0, no_margins: true}"
                                @updated-cell="updateStart"
                        ></vertical-table>
                    </template>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    import {eventBus} from '../../../../../app';

    import CustomTable from '../../../../CustomTable/CustomTable';
    import VerticalTable from "../../../../CustomTable/VerticalTable";

    import CellStyleMixin from "../../../../_Mixins/CellStyleMixin";

    export default {
        name: "TableViewModule",
        components: {
            VerticalTable,
            CustomTable,
        },
        mixins: [
            CellStyleMixin,
        ],
        data: function () {
            return {
                curFlash: '',
                selectedView: 0,
                addingRow: {
                    active: this.tableMeta._is_owner || (this.tableMeta._current_right && this.tableMeta._current_right.can_create_condformat),
                    position: 'bottom'
                },
                viewRow: null,
                tableViewAva: [
                    'name',
                    'parts_avail',
                    'parts_default',
                    'access_permission_id',
                ],
                colViewAvailable: [
                    'row_group_id',
                    'col_group_id',
                    'can_show_srv',
                    'side_top',
                    'side_left_menu',
                    'side_left_filter',
                    'side_right',
                    'can_sorting',
                    'column_order',
                    'can_filter',
                    'can_hide',
                    'is_active',
                    '_embd',
                    'is_locked',
                    'lock_pass',
                ],
            }
        },
        props:{
            tableMeta: Object,
            table_id: Number|null,
        },
        computed: {
            canEditView() {
                return this.tableMeta._is_owner
                    || // OR user with available rights for add View
                    (this.tableMeta._current_right && this.tableMeta._current_right.can_create_view);
            }
        },
        watch: {
            table_id: function(val) {
                this.selectedView = -1;
            }
        },
        methods: {
            rowIndexClickedView(index) {
                this.selectedView = -1;
                this.$nextTick(() => {
                    this.selectedView = index;
                });
            },
            //Table View Functions
            insertStart(row) {
                this.viewRow = row;
                eventBus.$emit('global-get-view-object', '', 'add');
            },
            updateStart(row) {
                this.viewRow = row;
                if (row._changed_field == 'can_sorting' && row.can_sorting) {
                    this.curFlash = 'Current row order saved to the view.';
                    this.$emit('flash-msg', this.curFlash, true);
                }
                if (row._changed_field == 'column_order' && row.column_order) {
                    this.curFlash = 'Current column order saved to the view.';
                    this.$emit('flash-msg', this.curFlash, true);
                }
                if (row._changed_field == 'can_filter' && row.can_filter) {
                    this.curFlash = 'Current filters saved to the view.';
                    this.$emit('flash-msg', this.curFlash, true);
                }
                if (row._changed_field == 'can_hide' && row.can_hide) {
                    this.curFlash = 'Current XGrps visibility saved to the view.';
                    this.$emit('flash-msg', this.curFlash, true);
                }
                eventBus.$emit('global-get-view-object', '', 'update');
                setTimeout(() => {
                    this.$emit('flash-msg', this.curFlash, false);
                }, 3000);
            },
            deleteStart(row) {
                this.viewRow = row;
                eventBus.$emit('global-get-view-object', '', 'del');
            },
            //change rows
            saveViewClickedHandler(vData) {
                this.viewRow.data = vData;
                this.viewRow.lock_pass = this.viewRow.lock_pass ? 1 : 0;
                let fields = _.cloneDeep(this.viewRow);//copy object
                this.$root.deleteSystemFields(fields);

                this.$root.sm_msg_type = 1;
                axios.post('/ajax/table-view', {
                    table_id: this.tableMeta.id,
                    fields: fields,
                }).then(({ data }) => {
                    this.tableMeta._views.push(data);
                }).catch(errors => {
                    Swal('', getErrors(errors));
                }).finally(() => {
                    this.$root.sm_msg_type = 0;
                    this.viewRow = null;
                });
            },
            updateLoadedViewHandler(data) {
                this.viewRow.data = data;
                let fields = _.cloneDeep(this.viewRow);//copy object
                this.$root.deleteSystemFields(fields);

                this.$root.sm_msg_type = 1;
                axios.put('/ajax/table-view', {
                    table_id: this.tableMeta.id,
                    view_id: this.viewRow.id,
                    fields: fields,
                }).then(({ data }) => {
                    //from ListView --- this.getTableData('page');
                }).catch(errors => {
                    Swal('', getErrors(errors));
                }).finally(() => {
                    this.$root.sm_msg_type = 0;
                    this.viewRow = null;
                });
            },
            deleteViewClickedHandler(data) {
                this.$root.sm_msg_type = 1;
                let idx = _.findIndex(this.tableMeta._views, {id: this.viewRow.id});
                axios.delete('/ajax/table-view', {
                    params: {
                        table_view_id: this.viewRow.id,
                    }
                }).then(({ data }) => {
                    this.tableMeta._views.splice(idx, 1);
                }).catch(errors => {
                    Swal('', getErrors(errors));
                }).finally(() => {
                    this.$root.sm_msg_type = 0;
                    this.viewRow = null;
                });
            },
            //change filtering
            addFilteringHandler(tableRow) {
                let fields = _.cloneDeep(tableRow);//copy object
                this.$root.deleteSystemFields(fields);

                this.$root.sm_msg_type = 1;
                axios.post('/ajax/table-view/filtering', {
                    table_view_id: this.tableMeta._views[this.selectedView].id,
                    fields: fields,
                }).then(({ data }) => {
                    this.tableMeta._views[this.selectedView]._filtering.push(data);
                }).catch(errors => {
                    Swal('', getErrors(errors));
                }).finally(() => {
                    this.$root.sm_msg_type = 0;
                });
            },
            updateFilteringHandler(tableRow) {
                let fields = _.cloneDeep(tableRow);//copy object
                this.$root.deleteSystemFields(fields);

                this.$root.sm_msg_type = 1;
                axios.put('/ajax/table-view/filtering', {
                    table_view_filtering_id: tableRow.id,
                    table_view_id: this.tableMeta._views[this.selectedView].id,
                    fields: fields,
                }).then(({ data }) => {
                    //
                }).catch(errors => {
                    Swal('', getErrors(errors));
                }).finally(() => {
                    this.$root.sm_msg_type = 0;
                });
            },
            deleteFilteringHandler(tableRow) {
                this.$root.sm_msg_type = 1;
                let idx = _.findIndex(this.tableMeta._views[this.selectedView]._filtering, {id: tableRow.id});
                axios.delete('/ajax/table-view/filtering', {
                    params: {
                        table_view_filtering_id: tableRow.id,
                        table_view_id: tableRow.table_view_id,
                    }
                }).then(({ data }) => {
                    this.tableMeta._views[this.selectedView]._filtering.splice(idx, 1);
                }).catch(errors => {
                    Swal('', getErrors(errors));
                }).finally(() => {
                    this.$root.sm_msg_type = 0;
                });
            },
            viewObjectReturned(obj, type) {
                switch (type) {
                    case 'add': this.saveViewClickedHandler(obj);
                        break;
                    case 'update': this.updateLoadedViewHandler(obj);
                        break;
                    case 'del': this.deleteViewClickedHandler(obj);
                        break;
                }
            },
        },
        mounted() {
            eventBus.$on('global-return-view-object', this.viewObjectReturned);
        },
        beforeDestroy() {
            eventBus.$off('global-return-view-object', this.viewObjectReturned);
        }
    }
</script>

<style lang="scss" scoped>
    @import "./TabSettingsPermissions";

    .vert-panel-sm {
        height: calc(50% - 30px) !important;
        background-color: #FFF;
    }
    .vert-panel-lg {
        height: calc(50% - 35px) !important;
        background-color: #FFF;
    }
    .permissions-tab {
        .permissions-panel {
            .permissions-menu-body {
                div {
                    border: none;
                }
            }
        }
    }
</style>