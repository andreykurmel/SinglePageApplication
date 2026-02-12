<template>
    <div class="permissions-panel full-height">
        <div class="permissions-menu-header">
            <button class="btn btn-default btn-sm" :style="textSysStyle" :class="{active : activeTab === 'list'}" @click="activeTab = 'list'">
                List
            </button>
            <button class="btn btn-default btn-sm" :style="textSysStyle" :class="{active : activeTab === 'settings'}" @click="activeTab = 'settings'">
                General Settings
            </button>
            <button class="btn btn-default btn-sm" :style="textSysStyle" :class="{active : activeTab === 'filtering'}" @click="activeTab = 'filtering'">
                Filtering
            </button>

            <div v-if="curView" style="position: absolute; top: 0; right: 0; width: 50%;">
                <label class="pull-right flex flex--center" style="margin: 0 0 0 5px;white-space: nowrap;">
                    Loaded MRV/Select to change:&nbsp;
                    <select-block
                        :options="mrvOpts()"
                        :sel_value="curView.id"
                        :style="{ width:'200px', height:'32px', }"
                        :link_path="getViewHsh()"
                        @option-select="selemrvChange"
                    ></select-block>
                </label>
            </div>
        </div>
        <div class="permissions-menu-body" style="border: 1px solid #CCC;">
            <div v-show="activeTab === 'list'" class="full-frame">
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

            <div v-show="activeTab === 'settings'" v-if="curView" class="full-frame" style="padding: 5px">
                <div class="relative">
                    <vertical-table
                        :td="'custom-cell-table-view'"
                        :global-meta="tableMeta"
                        :table-meta="$root.settingsMeta['table_views']"
                        :settings-meta="$root.settingsMeta"
                        :table-row="curView"
                        :user="$root.user"
                        :cell-height="1"
                        :max-cell-rows="0"
                        :behavior="'table_views'"
                        :available-columns="colViewAvailable"
                        :no_height_limit="true"
                        :widths="{name: '35%', col: '65%', history: 0, no_margins: true}"
                        @updated-cell="updateStart"
                    ></vertical-table>
                </div>
                <div class="flex">
                    <div class="flex no-wrap" style="margin-left: 30%; padding-top: 15px;">
                        <label>QR Code:&nbsp;</label>
                        <label class="switch_t" style="display: inline-block;margin-right: 5px;">
                            <input type="checkbox" v-model="curView['mrv_qr_with_name']" :disabled="!canEditView" @change="updateStart(curView)">
                            <span class="toggler round" :class="[!canEditView ? 'disabled' : '']"></span>
                        </label>
                        <label>Name:</label>
                    </div>
                    <div class="full-width txt--right">
                        <img v-if="curView && curView.qr_mrv_link" :src="curView.qr_mrv_link" width="300" height="300">
                        <span v-else>Construction...</span>
                    </div>
                </div>
            </div>

            <div v-show="activeTab === 'filtering'" v-if="curView" class="full-frame" style="padding: 5px">
                <div><label style="font-size: 1.4em;">Filtering</label></div>
                <div class="flex flex--center form-group" style="justify-content: left;">
                    <label class="switch_t" style="margin: 0 10px 0 0;">
                        <input type="checkbox"
                               :disabled="!canEditView"
                               v-model="curView.view_filtering"
                               @change="updateStart(curView)">
                        <span class="toggler round" :class="{'disabled': !canEditView}"></span>
                    </label>
                    <span style="font-size: 1.2em;">Ask for inputs and apply filters to records for loading the view.</span>
                </div>
                <div v-if="curView.view_filtering" class="params-wrapper border-gray" style="height: calc(100% - 125px);">
                    <custom-table
                        :cell_component_name="'custom-cell-table-view'"
                        :global-meta="tableMeta"
                        :table-meta="$root.settingsMeta['table_view_filtering']"
                        :all-rows="curView._filtering"
                        :rows-count="curView._filtering.length"
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
                    <span style="font-size: 1.2em; justify-content: left;">Only records that meet the criteria comparing the values of the fields with the entered or selected values will be loaded.</span>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    import {eventBus} from '../../../../../app';

    import CustomTable from '../../../../CustomTable/CustomTable';
    import SelectBlock from "../../../../CommonBlocks/SelectBlock";

    import CellStyleMixin from "../../../../_Mixins/CellStyleMixin";
    import IsShowFieldMixin from "../../../../_Mixins/IsShowFieldMixin";

    export default {
        name: "TableViewModule",
        components: {
            SelectBlock,
            CustomTable,
        },
        mixins: [
            CellStyleMixin,
            IsShowFieldMixin,
        ],
        data: function () {
            return {
                activeTab: 'list',
                curFlash: '',
                selectedView: 0,
                addingRow: {
                    active: this.tableMeta._is_owner || (this.tableMeta._current_right && this.tableMeta._current_right.can_create_condformat),
                    position: 'bottom'
                },
                viewRow: null,
                tableViewAva: [
                    'name',
                    'custom_path',
                    'parts_avail',
                    'parts_default',
                    'access_permission_id',
                    'is_active',
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
                    '_embd',
                    'is_locked',
                    'lock_pass',
                    'srv_fltrs_on_top',
                    'srv_fltrs_ontop_pos',
                ],
            }
        },
        props:{
            tableMeta: Object,
            noGridViewRelation: Boolean,
        },
        computed: {
            curView() {
                return this.tableMeta._views[this.selectedView];
            },
            canEditView() {
                return this.tableMeta._is_owner
                    || // OR user with available rights for add View
                    (this.tableMeta._current_right && this.tableMeta._current_right.can_create_view);
            }
        },
        methods: {
            //top select
            mrvOpts() {
                return _.map(this.tableMeta._views, (vv) => {
                    return { val:vv.id, show:vv.name };
                });
            },
            selemrvChange(opt) {
                this.selectedView = _.findIndex(this.tableMeta._views, {id: Number(opt.val)});
            },
            getViewHsh() {
                return this.curView && this.curView.hash
                    ? (this.curView.custom_path ? this.$root.app_url : this.$root.clear_url) + '/mrv/' + (this.curView.custom_path || this.curView.hash)
                    : '#';
            },
            //
            rowIndexClickedView(index) {
                this.selectedView = -1;
                this.$nextTick(() => {
                    this.selectedView = index;
                });
            },
            //Table View Functions
            insertStart(row) {
                this.viewRow = row;

                if (this.noGridViewRelation) {
                    this.immediateHandle('insert');
                } else {
                    eventBus.$emit('global-get-view-object', '', 'add');
                }
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

                setTimeout(() => {
                    this.$emit('flash-msg', this.curFlash, false);
                }, 3000);

                if (this.noGridViewRelation) {
                    this.immediateHandle('update');
                } else {
                    eventBus.$emit('global-get-view-object', '', 'update');
                }
            },
            deleteStart(row) {
                this.viewRow = row;

                if (this.noGridViewRelation) {
                    this.immediateHandle('delete');
                } else {
                    eventBus.$emit('global-get-view-object', '', 'del');
                }
            },
            immediateHandle(type) {
                let hidden_columns = _.filter(this.tableMeta._fields, (el) => {
                    return !this.isShowField(el);
                });
                hidden_columns = _.map(hidden_columns, 'field');

                let vData = this.$root.getTableViewData(this.tableMeta, {
                    hidden_columns: hidden_columns,
                    page: 1,
                    sort: [],
                    user_id: this.$root.user.id,
                    search_keywords: null,
                    search_columns: [],
                    search_direct_row_id: null,
                });

                switch (type) {
                    case 'insert': this.saveViewClickedHandler(vData); break;
                    case 'update': this.updateLoadedViewHandler(vData); break;
                    case 'delete': this.deleteViewClickedHandler(vData); break;
                }
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
                    Swal('Info', getErrors(errors));
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
                    if (this.viewRow) {
                        this.viewRow.qr_mrv_link = data.qr_mrv_link + '?v=' + uuidv4();
                        this.rowIndexClickedView(this.selectedView);
                        //from ListView --- this.getTableData('page');
                    }
                }).catch(errors => {
                    Swal('Info', getErrors(errors));
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
                    Swal('Info', getErrors(errors));
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
                    table_view_id: this.curView.id,
                    fields: fields,
                }).then(({ data }) => {
                    this.tableMeta._views[this.selectedView]._filtering.push(data);
                }).catch(errors => {
                    Swal('Info', getErrors(errors));
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
                    table_view_id: this.curView.id,
                    fields: fields,
                }).then(({ data }) => {
                    //
                }).catch(errors => {
                    Swal('Info', getErrors(errors));
                }).finally(() => {
                    this.$root.sm_msg_type = 0;
                });
            },
            deleteFilteringHandler(tableRow) {
                this.$root.sm_msg_type = 1;
                let idx = _.findIndex(this.curView._filtering, {id: tableRow.id});
                axios.delete('/ajax/table-view/filtering', {
                    params: {
                        table_view_filtering_id: tableRow.id,
                        table_view_id: tableRow.table_view_id,
                    }
                }).then(({ data }) => {
                    this.tableMeta._views[this.selectedView]._filtering.splice(idx, 1);
                }).catch(errors => {
                    Swal('Info', getErrors(errors));
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
            showTableViewsHandler(db_name, right_tab, view_id) {
                if (db_name === this.tableMeta.db_name && right_tab === 'multiple') {
                    this.selectedView = _.findIndex(this.tableMeta._views, {id: Number(view_id)});
                }
            },
        },
        mounted() {
            eventBus.$on('global-return-view-object', this.viewObjectReturned);
            eventBus.$on('show-table-views-popup', this.showTableViewsHandler);
        },
        beforeDestroy() {
            eventBus.$off('global-return-view-object', this.viewObjectReturned);
            eventBus.$off('show-table-views-popup', this.showTableViewsHandler);
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