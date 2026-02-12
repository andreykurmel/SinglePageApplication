<template>
    <div class="flex flex--col">
        <custom-table
            :cell_component_name="'custom-cell-settings-dcr'"
            :global-meta="tableMeta"
            :table-meta="$root.settingsMeta['dcr_linked_tables']"
            :all-rows="dcrObject._dcr_linked_tables"
            :rows-count="dcrObject._dcr_linked_tables.length"
            :cell-height="1"
            :max-cell-rows="0"
            :is-full-width="true"
            :with_edit="$root.ownerOf(tableMeta)"
            :adding-row="addingRow"
            :user="$root.user"
            :behavior="'dcr_linked_tb'"
            :available-columns="['name','is_active','linked_table_id','passed_ref_cond_id','linked_permission_id']"
            :forbidden-columns="$root.systemFields"
            style="height: 20%; min-height: 120px;"
            @added-row="addLinkedDcr"
            @updated-row="updateLinkedDcr"
            @delete-row="deleteLinkedDcr"
            @row-index-clicked="linkedDcrClickedGroup"
            @show-add-ref-cond="linkedPermisPopup"
        ></custom-table>
        <div class="full-frame" style="height: 80%" :style="textSysStyle">
            <div class="bold white flex flex--center-v" style="height: 38px; justify-content: space-between; overflow: hidden;" :style="$root.themeMainBgStyle">
                <div class="permissions-menu-header no-margin" :style="$root.themeMainBgStyle" style="padding: 11px 0 0 5px;">
                    <button class="btn btn-default btn-sm" :class="{active : embedTab === 'embedding'}" :style="textSysStyle" @click="embedTab = 'embedding'">
                        Embedding Details
                    </button>
                    <button class="btn btn-default btn-sm" :class="{active : embedTab === 'catalog'}" :style="textSysStyle" @click="embedTab = 'catalog'">
                        Extension "Catalog"
                    </button>
                </div>

                <label v-if="embedTab === 'embedding'" class="flex flex--center mb0 mr5" style="white-space: nowrap;" :style="textSysStyleSmart">
                    Linked Table Loaded:&nbsp;
                    <select-block
                        :options="linkedOpts()"
                        :sel_value="dcrlinkedRow ? dcrlinkedRow.id : null"
                        :style="{ maxWidth:'300px', height:'32px', }"
                        @option-select="linkedDcrChange"
                    ></select-block>
                </label>
                <div v-if="embedTab === 'catalog' && dcrlinkedRow" class="flex flex--center-v" style="position: absolute; top: 2px; right: 10px; height: 32px;" :style="textSysStyleSmart">
                    <label class="no-margin">Status:&nbsp;</label>
                    <label class="switch_t">
                        <input type="checkbox" :checked="dcrlinkedRow.ctlg_is_active" :disabled="!$root.ownerOf(tableMeta)" @click="statusChange()">
                        <span class="toggler round" :class="{'disabled': !$root.ownerOf(tableMeta)}"></span>
                    </label>
                </div>
            </div>
            <div v-if="dcrlinkedRow" v-show="embedTab === 'embedding'" class="flex flex--col" style="height: calc(100% - 40px);">
                <div class="full-height relative p5">
                    <vertical-table
                        :td="'custom-cell-settings-dcr'"
                        :global-meta="tableMeta"
                        :table-meta="$root.settingsMeta['dcr_linked_tables']"
                        :settings-meta="$root.settingsMeta"
                        :table-row="dcrlinkedRow"
                        :user="$root.user"
                        :cell-height="1"
                        :max-cell-rows="0"
                        :behavior="'dcr_linked_tb'"
                        :available-columns="['header','position_field_id','position','style','max_nbr_rcds_embd','max_height_inline_embd','placement_tab_name','placement_tab_order']"
                        :forbidden-columns="$root.systemFields"
                        @updated-cell="updateLinkedDcr"
                    ></vertical-table>
                </div>

                <div class="full-height relative">
                    <div class="permissions-menu-header no-margin" :style="$root.themeMainBgStyle" style="padding: 5px 0 0 5px;">
                        <button class="btn btn-default btn-sm" :class="{active : subLinkedTab === 'options'}" :style="textSysStyle" @click="setSub('options')">
                            Options
                        </button>
                        <button class="btn btn-default btn-sm" :class="{active : subLinkedTab === 'table'}" :style="textSysStyle" @click="setSub('table')">
                            Table
                        </button>
                        <button class="btn btn-default btn-sm" :class="{active : subLinkedTab === 'listing'}" :style="textSysStyle" @click="setSub('listing')">
                            List
                        </button>
                        <button class="btn btn-default btn-sm" :class="{active : subLinkedTab === 'board'}" :style="textSysStyle" @click="setSub('board')">
                            Board
                        </button>
                    </div>
                    <div class="permissions-menu-body p5" style="overflow: auto;">
                        <board-setting-block
                            v-if="subLinkedTab === 'board'"
                            :tb_meta="tableMeta"
                            :board_settings="dcrlinkedRow"
                            :no_header="true"
                            :with_theme="true"
                            @val-changed="updateBoardDcr"
                        ></board-setting-block>
                        <vertical-table
                            v-else-if="subLinkedTab"
                            :td="'custom-cell-settings-dcr'"
                            :global-meta="tableMeta"
                            :table-meta="$root.settingsMeta['dcr_linked_tables']"
                            :settings-meta="$root.settingsMeta"
                            :table-row="dcrlinkedRow"
                            :user="$root.user"
                            :cell-height="1"
                            :max-cell-rows="0"
                            :behavior="'dcr_linked_tb'"
                            :available-columns="getSubLinkedColumns()"
                            :forbidden-columns="$root.systemFields"
                            @updated-cell="updateLinkedDcr"
                        ></vertical-table>
                    </div>
                </div>
            </div>
            <div v-if="dcrlinkedRow" v-show="embedTab === 'catalog'" class="flex flex--col p5" style="height: calc(100% - 40px);">
                <vertical-table
                    :td="'custom-cell-settings-dcr'"
                    :global-meta="tableMeta"
                    :table-meta="$root.settingsMeta['dcr_linked_tables']"
                    :settings-meta="$root.settingsMeta"
                    :table-row="dcrlinkedRow"
                    :user="$root.user"
                    :cell-height="1"
                    :max-cell-rows="0"
                    :behavior="'dcr_linked_tb'"
                    :available-columns="['ctlg_columns_number','ctlg_data_range','ctlg_table_id','ctlg_distinct_field_id',
                        'ctlg_parent_link_field_id','ctlg_parent_quantity_field_id','ctlg_display_option',
                        'ctlg_visible_field_ids','ctlg_filter_field_ids']"
                    :forbidden-columns="$root.systemFields"
                    @updated-cell="updateLinkedDcr"
                ></vertical-table>
                <board-setting-block
                    v-if="ctlgTb"
                    :tb_meta="ctlgTb"
                    :board_settings="dcrlinkedRow"
                    :prefix="'ctlg_'"
                    :no_header="true"
                    :with_theme="true"
                    @val-changed="updateBoardDcr"
                ></board-setting-block>
            </div>
        </div>

        <permissions-settings-popup
            v-if="linkedMeta"
            :table-meta="linkedMeta"
            :user="$root.user"
            :init_show="true"
            @hidden-form="linkedPermisClose()"
        ></permissions-settings-popup>
    </div>
</template>

<script>
import {eventBus} from '../../../../../app';

import {Endpoints} from "../../../../../classes/Endpoints";

import CustomTable from '../../../../CustomTable/CustomTable';
import SelectBlock from "../../../../CommonBlocks/SelectBlock";

import CellStyleMixin from "../../../../_Mixins/CellStyleMixin";
import PermissionsSettingsPopup from "../../../../CustomPopup/PermissionsSettingsPopup.vue";
import BoardSettingBlock from "../../../../CommonBlocks/BoardSettingBlock.vue";

export default {
        name: "TabSettingsRequestsLinkedTables",
        components: {
            BoardSettingBlock,
            PermissionsSettingsPopup,
            SelectBlock,
            CustomTable,
        },
        mixins: [
            CellStyleMixin,
        ],
        data: function () {
            return {
                linkedIdx: -1,
                addingRow: {
                    active: true,
                    position: 'bottom',
                },
                linkedMeta: null,
                embedTab: 'embedding',
                subLinkedTab: 'options',
            }
        },
        props:{
            tableMeta: Object,
            dcrObject: Object,
        },
        computed: {
            ctlgTb() {
                return this.dcrlinkedRow
                    ? _.find(this.$root.settingsMeta.available_tables, {id: Number(this.dcrlinkedRow.ctlg_table_id)})
                    : null;
            },
            dcrlinkedRow() {
                return this.dcrObject._dcr_linked_tables[this.linkedIdx];
            },
            linTb() {
                let row = this.dcrlinkedRow || {};
                return _.find(this.$root.settingsMeta.available_tables, {id: Number(row.linked_table_id)});
            },
            linkRefConds() {
                let data = this.dcrlinkedRow
                    ? _.filter(this.tableMeta._ref_conditions, (rc) => {
                        return rc.ref_table_id == this.dcrlinkedRow.linked_table_id;
                    })
                    : [];
                return _.map(data, (rc) => {
                    return {show: rc.name, val: rc.id};
                });
            },
        },
        methods: {
            setSub(tab) {
                this.subLinkedTab = '';
                this.$nextTick(() => {
                    this.subLinkedTab = tab;
                });
            },
            getSubLinkedColumns() {
                switch (this.subLinkedTab) {
                    case 'options': return ['default_display','embd_table','embd_listing','embd_board'];
                    case 'table': return ['embd_stats','embd_fit_width','embd_table_align','embd_float_actions'];
                    case 'listing': return ['listing_field_id','listing_rows_width','listing_rows_min_width'];
                    case 'board': return [/* used board-setting-block component */];
                }
            },
            linkedOpts() {
                return _.map(this.dcrObject._dcr_linked_tables, (lnk) => {
                    return { val:lnk.id, show:lnk.name };
                });
            },
            linkedDcrChange(opt) {
                this.linkedDcrClickedGroup( _.findIndex(this.dcrObject._dcr_linked_tables, {id: Number(opt.val)}) );
            },
            linkedDcrClickedGroup(index) {
                this.linkedIdx = index;
                this.subLinkedTab = 'options';
            },
            linkedPermisPopup(dcrLinkedTb) {
                $.LoadingOverlay('show');
                Endpoints.loadTbHeaders(dcrLinkedTb.linked_table_id)
                    .then((data) => {
                        this.linkedMeta = data;
                    }).finally(() => {
                        $.LoadingOverlay('hide');
                    });
            },
            linkedPermisClose() {
                this.linkedMeta = null;
                this.linkedDcrClickedGroup(this.linkedIdx);
            },

            //Linked Tables for DCR
            addLinkedDcr(tableRow) {
                this.$root.sm_msg_type = 1;

                let fields = _.cloneDeep(tableRow);//copy object
                this.$root.deleteSystemFields(fields);

                let frst = _.first(_.filter(this.tableMeta._fields, (fld) => {
                    return this.$root.systemFields.indexOf(fld.field) === -1;
                }));
                fields.position_field_id = frst ? frst.id : null;

                axios.post('/ajax/table-data-request/linked-table', {
                    table_dcr_id: this.dcrObject.id,
                    fields: fields,
                }).then(({ data }) => {
                    this.dcrObject._dcr_linked_tables.push(data);
                }).catch(errors => {
                    Swal('Info', getErrors(errors));
                }).finally(() => {
                    this.$root.sm_msg_type = 0;
                });
            },
            statusChange() {
                this.dcrlinkedRow.ctlg_is_active = this.dcrlinkedRow.ctlg_is_active ? 0 : 1;
                this.updateLinkedDcr(this.dcrlinkedRow);
            },
            updateBoardDcr(prop_name, val) {
                this.dcrlinkedRow[prop_name] = val;
                this.updateLinkedDcr(this.dcrlinkedRow);
            },
            updateLinkedDcr(tableRow) {
                this.$root.sm_msg_type = 1;

                this.setValues(tableRow);
                let fields = _.cloneDeep(tableRow);//copy object
                this.$root.deleteSystemFields(fields);

                axios.put('/ajax/table-data-request/linked-table', {
                    table_linked_id: tableRow.id,
                    fields: fields
                }).then(({ data }) => {
                    this.$root.assignObject(data, tableRow);
                }).catch(errors => {
                    Swal('Info', getErrors(errors));
                }).finally(() => {
                    this.$root.sm_msg_type = 0;
                });
            },
            deleteLinkedDcr(tableRow) {
                this.$root.sm_msg_type = 1;
                axios.delete('/ajax/table-data-request/linked-table', {
                    params: {
                        table_linked_id: tableRow.id
                    }
                }).then(({ data }) => {
                    let idx = _.findIndex(this.dcrObject._dcr_linked_tables, {id: Number(tableRow.id)});
                    if (idx > -1) {
                        this.dcrObject._dcr_linked_tables.splice(idx, 1);
                    }
                }).catch(errors => {
                    Swal('Info', getErrors(errors));
                }).finally(() => {
                    this.$root.sm_msg_type = 0;
                });
            },
            updateSelect(key, option) {
                this.dcrlinkedRow[key] = option.val;
                this.updateLinkedDcr(this.dcrlinkedRow);
            },
            setValues(tableRow) {
                if (this.$root.inArray(tableRow._changed_field, ['embd_table','embd_listing','embd_board','default_display'])) {
                    if ((tableRow.default_display === 'Table' && ! tableRow.embd_table)
                        || (tableRow.default_display === 'Listing' && ! tableRow.embd_listing)
                        || (tableRow.default_display === 'Boards' && ! tableRow.embd_board)
                    ) {
                        tableRow.default_display = this.getAvailDefault(tableRow);
                    }
                }
            },
            getAvailDefault(tableRow) {
                return tableRow.embd_table ? 'Table'
                    : (tableRow.embd_listing ? 'Listing'
                        : (tableRow.embd_board ? 'Boards' : 'Table'));
            },
        },
        mounted() {
            if (this.dcrObject._dcr_linked_tables.length) {
                this.linkedDcrClickedGroup(0);
            }
        },
        beforeDestroy() {
        }
    }
</script>

<style lang="scss" scoped>
    @import "./TabSettingsPermissions";
</style>