<template>
    <table class="spaced-table" :style="styleSpacing" :id="tb_id">
        <colgroup>
            <col :width="widths_name">
            <col :width="widths_col">
            <col v-if="hasUnits" :width="widths.unit">
            <col v-if="canSeeHistory && widths.history" :width="widths.history">
        </colgroup>
        <tbody>
        <template v-for="(vertTableFieldObject, index) in sortedTableMetaFields">

            <!--SUB HEADERS-->
            <tr v-for="(sub,idx) in vertTableFieldObject.sub_headers">
                <td colspan="2" :style="getSubHeaderStyle(idx+vertTableFieldObject.base_subs_lvl)">
                    <vertical-table-border :level="(idx+vertTableFieldObject.base_subs_lvl)"></vertical-table-border>
                    <label :style="getSubHdrStyle()">{{ $root.strip_tags(sub) }}</label>
                </td>
                <td v-if="hasUnits"></td>
                <td v-if="canSeeHistory && widths.history"></td>
            </tr>
            <!--SUB HEADERS-->

            <!--SINGLE COLUMN-->
            <template v-if="vertTableFieldObject.single">
                <tr><!--DIVIDER-->
                    <td colspan="2"><vertical-table-border :level="vertTableFieldObject.level"></vertical-table-border></td>
                    <td v-if="hasUnits"></td>
                    <td v-if="canSeeHistory && widths.history"></td>
                </tr>
                <tr v-if="nameNotHidden(vertTableFieldObject.single, true)">
                    <td colspan="2" :style="{backgroundColor: vert_bg_avail ? vertTableFieldObject.single.header_background || vert_bg_color : null}">
                        <vertical-table-border :level="vertTableFieldObject.level"></vertical-table-border>
                        <label :style="getLabelStyle(vertTableFieldObject.level)">
                            <span>{{ getHeader(vertTableFieldObject.single.name) }}</span>
                            <span v-if="vertTableFieldObject.single.f_required" class="required-wildcart">*</span>
                        </label>
                    </td>
                    <td v-if="hasUnits"></td>
                    <td v-if="canSeeHistory && widths.history"></td>
                </tr>
                <tr v-if="tooltip_pos === 'up' && vertTableFieldObject.single.tooltip_show && vertTableFieldObject.single.tooltip ">
                    <td><vertical-table-border :level="vertTableFieldObject.level"></vertical-table-border></td>
                    <td>
                        <span class="tooltip-class" :style="headerTitleStyle(vertTableFieldObject)">{{ vertTableFieldObject.single.tooltip }}</span>
                    </td>
                    <td v-if="hasUnits"></td>
                    <td v-if="canSeeHistory && widths.history"></td>
                </tr>
                <tr>
                    <td v-if="nameNotHidden(vertTableFieldObject.single, false)"
                        :style="{backgroundColor: vert_bg_avail ? vertTableFieldObject.single.header_background || vert_bg_color : null}"
                    >
                        <vertical-table-border :level="vertTableFieldObject.level"></vertical-table-border>
                        <label :style="getLabelStyle(vertTableFieldObject.level)">
                            <span>{{ getHeader(vertTableFieldObject.single.name) }}</span>
                            <span v-if="vertTableFieldObject.single.f_required" class="required-wildcart">*</span>
                        </label>
                    </td>

                    <td v-if="valueNotHidden(vertTableFieldObject.single)"
                        :is="td"
                        :global-meta="globalMeta"
                        :table-meta="tableMeta"
                        :settings-meta="settingsMeta"
                        :table-row="tableRow || {}"
                        :table-header="vertTableFieldObject.single"
                        :cell-value="tableRow ? tableRow[vertTableFieldObject.single.field] : null"
                        :user="user"
                        :cell-height="cellHeight"
                        :max-cell-rows="maxCellRows"
                        :selected-cell="selectedCell"
                        :is-selected="selectedCell.is_selected(tableMeta, vertTableFieldObject.single)"
                        :row-index="-1"
                        :table_id="tableMeta.id"
                        :behavior="behavior"
                        :ref_tb_from_refcond="ref_tb_from_refcond"
                        :no_ddl_colls="no_ddl_colls"
                        :is-vert-table="true"
                        :with_edit="with_edit"
                        :is-add-row="isAddRow"
                        :is_def_fields="is_def_fields"
                        :no_height_limit="no_height_limit"
                        :parent-row="parentRow"
                        :class="vertTableFieldObject.single.f_type !== 'Boolean' ? 'edit-cell' : ''"
                        :colspan="nameNotHidden(vertTableFieldObject.single, false) ? 1 : 2"
                        :style="checkNoBorder(vertTableFieldObject.single)"
                        @show-add-ref-cond="showAddRefCond"
                        @show-src-record="showSrcRecord"
                        @updated-cell="updatedCell"
                        @show-add-ddl-option="showAddDDLOption"
                        @show-def-val-popup="showDefValPopup"
                        @cell-menu="showRowMenu"
                    ></td>
                    <td v-else :colspan="nameNotHidden(vertTableFieldObject.single, false) ? 1 : 2"></td>

                    <td v-if="hasUnits">
                        <label class="label-padding">{{ getCurUnit(vertTableFieldObject.single) }}</label>
                    </td>
                    <td v-if="canSeeHistory && widths.history">
                        <button v-if="canSeeHistory && vertTableFieldObject.single.show_history"
                                class="btn btn-sm btn-default cell-btn"
                                @click="$emit('toggle-history', vertTableFieldObject.single)"
                                title="History"
                        ><img src="/assets/img/history.png" width="20" height="20"></button>
                    </td>
                </tr>
                <tr v-if="tooltip_pos === 'down' && vertTableFieldObject.single.tooltip_show && vertTableFieldObject.single.tooltip ">
                    <td><vertical-table-border :level="vertTableFieldObject.level"></vertical-table-border></td>
                    <td>
                        <span class="tooltip-class" :style="headerTitleStyle(vertTableFieldObject)">{{ vertTableFieldObject.single.tooltip }}</span>
                    </td>
                    <td v-if="hasUnits"></td>
                    <td v-if="canSeeHistory && widths.history"></td>
                </tr>
            </template>
            <!--SINGLE COLUMN-->

            <!--COLUMNS ARE GROUPED IN TABLE-->
            <template v-else="">
                <tr>
                    <td v-if="vertTableFieldObject.level">
                        <vertical-table-border :level="vertTableFieldObject.level"></vertical-table-border>
                    </td>
                    <td :colspan="vertTableFieldObject.level ? 1 : 2">
                        <div>
                            <vertical-table-grouped-tb
                                    :vert-table-field-object="vertTableFieldObject"
                                    :td="td"
                                    :global-meta="globalMeta"
                                    :table-meta="tableMeta"
                                    :settings-meta="settingsMeta"
                                    :table-row="tableRow || {}"
                                    :user="user"
                                    :cell-height="cellHeight"
                                    :max-cell-rows="maxCellRows"
                                    :selected-cell="selectedCell"
                                    :behavior="behavior"
                                    :ref_tb_from_refcond="ref_tb_from_refcond"
                                    :no_ddl_colls="no_ddl_colls"
                                    :with_edit="with_edit"
                                    :is-add-row="isAddRow"
                                    :hide-names="hideNames"
                                    @show-add-ref-cond="showAddRefCond"
                                    @show-src-record="showSrcRecord"
                                    @updated-cell="updatedCell"
                                    @show-add-ddl-option="showAddDDLOption"
                                    @show-def-val-popup="showDefValPopup"
                            ></vertical-table-grouped-tb>
                        </div>
                    </td>
                    <!--<td v-if="hasUnits"></td>-->
                    <!--<td v-if="canSeeHistory && widths.history"></td>-->
                </tr>
            </template>
            <!--COLUMNS ARE GROUPED IN TABLE-->

            <!--DCR LINKED TABLE-->
            <template v-if="vertTableFieldObject.dcr_linked">
                <tr v-if="vertTableFieldObject.dcr_linked.header">
                    <td colspan="2" :style="{backgroundColor: vert_bg_avail ? vertTableFieldObject.single.header_background || vert_bg_color : null}">
                        <vertical-table-border
                            v-if="vertTableFieldObject.dcr_linked.style == 'Default'"
                            :level="vertTableFieldObject.level"
                        ></vertical-table-border>
                        <label :style="getLabelStyle(vertTableFieldObject.level)">
                            <span>{{ vertTableFieldObject.dcr_linked.header }}</span>
                        </label>
                    </td>
                    <td v-if="hasUnits"></td>
                    <td v-if="canSeeHistory && widths.history"></td>
                </tr>
                <tr>
                    <td v-if="vertTableFieldObject.dcr_linked.style == 'Default'">
                        <vertical-table-border :level="vertTableFieldObject.level"></vertical-table-border>
                    </td>
                    <td :colspan="vertTableFieldObject.dcr_linked.style == 'Default' ? 1 : 2">
                        <vertical-linked-table
                            :parent-row-id="tableRow.id"
                            :linked-rows-object="dcrLinkedRows"
                            :dcr-linked-table="vertTableFieldObject.dcr_linked"
                            :with_edit="with_edit"
                            @linked-update="$emit('linked-update')"
                        ></vertical-linked-table>
                    </td>
                    <td v-if="hasUnits"></td>
                    <td v-if="canSeeHistory && widths.history"></td>
                </tr>
            </template>
            <!--DCR LINKED TABLE-->

        </template>
        </tbody>

        <div v-if="row_menu_show && row_menu.row && row_menu.hdr"
             ref="row_menu"
             class="float-rom-menu"
             :style="rowMenuStyle"
        >
            <a v-if="row_menu.hdr" @click="copyCell(row_menu.row, row_menu.hdr);row_menu_show = false;">Copy Cell</a>
        </div>
    </table>
</template>

<script>
import {UnitConversion} from '../../classes/UnitConversion';
import {SelectedCells} from '../../classes/SelectedCells';
import {VerticalTableFldObject} from './VerticalTableFldObject';

import SortFieldsForVerticalMixin from '../_Mixins/SortFieldsForVerticalMixin.vue';
import CellStyleMixin from './../_Mixins/CellStyleMixin.vue';
import CellMenuMixin from "../_Mixins/CellMenuMixin";

import CustomCellTableData from '../CustomCell/CustomCellTableData.vue';
import CustomCellSystemTableData from '../CustomCell/CustomCellSystemTableData.vue';
import CustomCellCorrespTableData from "../CustomCell/CustomCellCorrespTableData.vue";
import CustomCellSettingsDisplay from '../CustomCell/CustomCellSettingsDisplay.vue';
import CustomCellDisplayLinks from '../CustomCell/CustomCellDisplayLinks.vue';
import CustomCellSettingsDdl from '../CustomCell/CustomCellSettingsDdl.vue';
import CustomCellSettingsPermission from '../CustomCell/CustomCellSettingsPermission.vue';
import CustomCellSettingsDcr from '../CustomCell/CustomCellSettingsDcr.vue';
import CustomCellColRowGroup from '../CustomCell/CustomCellColRowGroup.vue';
import CustomCellKanbanSett from '../CustomCell/CustomCellKanbanSett.vue';
import CustomCellRefConds from '../CustomCell/CustomCellRefConds.vue';
import CustomCellCondFormat from '../CustomCell/CustomCellCondFormat.vue';
import CustomCellPlans from '../CustomCell/CustomCellPlans.vue';
import CustomCellConnection from '../CustomCell/CustomCellConnection.vue';
import CustomCellUserGroups from '../CustomCell/CustomCellUserGroups.vue';
import CustomCellInvitations from '../CustomCell/CustomCellInvitations.vue';
import CustomCellTableView from '../CustomCell/CustomCellTableView.vue';
import CustomCellStimAppView from '../CustomCell/CustomCellStimAppView.vue';
import CustomCellFolderView from '../CustomCell/CustomCellFolderView.vue';

import VerticalTableBorder from "./VerticalTableBorder";
import VerticalTableGroupedTb from "./VerticalTableGroupedTb";
import VerticalLinkedTable from "./VerticalLinkedTable";

import {eventBus} from "../../app";

export default {
        name: "VerticalTable",
        mixins: [
            SortFieldsForVerticalMixin,
            CellStyleMixin,
            CellMenuMixin,
        ],
        components: {
            VerticalLinkedTable,
            VerticalTableGroupedTb,
            VerticalTableBorder,
            CustomCellTableData,
            CustomCellSystemTableData,
            CustomCellCorrespTableData,
            CustomCellSettingsDisplay,
            CustomCellDisplayLinks,
            CustomCellSettingsDdl,
            CustomCellSettingsPermission,
            CustomCellSettingsDcr,
            CustomCellColRowGroup,
            CustomCellKanbanSett,
            CustomCellRefConds,
            CustomCellCondFormat,
            CustomCellPlans,
            CustomCellConnection,
            CustomCellUserGroups,
            CustomCellInvitations,
            CustomCellTableView,
            CustomCellStimAppView,
            CustomCellFolderView,
        },
        data: function () {
            return {
                selectedCell: new SelectedCells(this.disabled_sel),
                sortedTableMetaFields: [],
                hdr_margin_lft: 15,
            };
        },
        props:{
            tb_id: String,
            settingsMeta: {
                type: Object,
                default: function () {
                    return {};
                }
            },
            globalMeta: Object,
            tableMeta: Object,
            tableRow: Object,
            cellHeight: Number,
            maxCellRows: {
                type: Number,
                default: 0
            },
            tooltip_pos: {
                type: String,
                default: 'up'
            },
            user: Object,
            forbiddenColumns: {
                type: Array,
                default: function () {
                    return [];
                }
            },
            availableColumns: Array,
            hideNames: Array,
            hideBorders: Array,
            td: String,
            behavior: String,
            widths: {
                type: Object,
                default: function () {
                    return {
                        name: '30%',
                        col: '60%',
                        history: '35px',
                        unit: '45px',
                    };
                }
            },
            canSeeHistory: Boolean|Number,
            ref_tb_from_refcond: Object|null,
            no_ddl_colls: Array,
            isAddRow: Boolean,
            is_def_fields: Boolean,
            is_small_spacing: String,
            with_edit: {
                type: Boolean,
                default: true
            },
            disabled_sel: Boolean,
            no_height_limit: Boolean,
            dcrObject: Object,
            dcrLinkedRows: Object,
            parentRow: Object,
        },
        watch: {
            tableRow: {
                handler(val) {
                    let vertObjects = this.createSortedTableMetaFields(this.tableMeta._fields);
                    this.sortedTableMetaFields = this.prepareDcrLinked(vertObjects);
                    this.$emit('showed-elements', this.sortedTableMetaFields.length);
                },
                immediate: true,
                deep: true,
            },
        },
        computed: {
            styleSpacing() {
                return {
                    borderSpacing: (this.is_small_spacing === 'yes' ? '0 3px' : '0 7px'),
                };
            },
            hasUnits() {
                let has = false;
                if (this.tableMeta) {
                    _.each(this.tableMeta._fields, function (el) {
                        if (el.unit) {
                            has = true;
                        }
                    });
                }
                return has;
            },
            widths_name() {
                return this.tableMeta.vert_tb_hdrwidth ? this.tableMeta.vert_tb_hdrwidth+'%' : this.widths.name;
            },
            widths_col() {
                return this.tableMeta.vert_tb_hdrwidth ? (100 - this.tableMeta.vert_tb_hdrwidth)+'%' : this.widths.col;
            },
            vert_bg_avail() {
                return !!this.tableMeta.vert_tb_bgcolor;
            },
            vert_bg_color() {
                return this.vert_bg_avail ? this.$root.themeTableBgColor(this.tableMeta) || '#DDD' : null;
            },
        },
        methods: {
            nameNotHidden(tableHeader, targettopbot) {
                return (targettopbot === !!tableHeader.is_topbot_in_popup)
                    &&
                    tableHeader.fld_display_name
                    &&
                    (!this.hideNames || this.hideNames.indexOf(tableHeader.field) > -1);
            },
            valueNotHidden(tableHeader) {
                return !!tableHeader.fld_display_value;
            },
            checkNoBorder(tableHeader) {
                let noborder = !tableHeader.fld_display_border
                    || (this.hideBorders && this.hideBorders.indexOf(tableHeader.field) > -1);
                return noborder ? {border: 'none'} : null;
            },
            headerTitleStyle(vertTableFieldObject) {
                let style = _.cloneDeep(this.textStyle);
                style.fontSize = Number(this.themeTextFontSize-2) + 'px';
                style.textAlign = 'left' /* || vertTableFieldObject.single.col_align || 'right'*/;
                return style;
            },
            getCurUnit(header) {
                return UnitConversion.showUnit(header, this.tableMeta);
            },
            /**
             * createSortedTableMetaFields
             * @param fields
             * @returns {Array} of {VerticalTableFldObject}
             */
            createSortedTableMetaFields(fields) {
                let fld_objects = this.sortAndFilterFields(this.tableMeta, fields, this.tableRow, !!this.is_def_fields);
                let all_is_single = (this.behavior === 'kanban_view' && !this.tableMeta.kanban_form_table);
                //level headers
                return VerticalTableFldObject.buildSubHeaders(fld_objects, all_is_single);
            },

            getHeader(name) {
                return _.last(name.split(','));
            },
            getSubHeaderStyle(level) {
                return {
                    fontSize: (1.3 - level*0.1)+'em',
                    paddingLeft: (level * this.hdr_margin_lft)+'px',
                };
            },
            getLabelStyle(level) {
                let style = _.cloneDeep(this.textStyle);
                style.marginLeft = (level * this.hdr_margin_lft) + 'px';
                return style;
            },
            getSubHdrStyle(level) {
                let style = _.cloneDeep(this.textStyle);
                style.fontSize = Number(this.themeTextFontSize+4) + 'px';
                return style;
            },
            //sys methods
            inArray(item, array) {
                return $.inArray(item, array) > -1;
            },

            //proxies
            showSrcRecord(lnk, header, tableRow) {
                this.$emit('show-src-record', lnk, header, tableRow);
            },
            showAddRefCond(refId) {
                this.$emit('show-add-ref-cond', refId);
            },
            updatedCell(tableRow, hdr) {
                this.$emit('updated-cell', tableRow, hdr);
            },
            showDefValPopup(tableRow) {
                this.$emit('show-def-val-popup', tableRow);
            },
            showAddDDLOption(tableHeader, tableRow) {
                this.$emit('show-add-ddl-option', tableHeader, tableRow);
            },

            //Dcr Linked tables
            /**
             *
             * @param vertObjects {Array} of {VerticalTableFldObject}
             * @returns {Array} of {VerticalTableFldObject}
             */
            prepareDcrLinked(vertObjects) {
                _.each(vertObjects, (verticalTableFldObject) => {
                    verticalTableFldObject.dcr_linked = this.foundLinkedDcr(verticalTableFldObject);
                });
                return vertObjects;
            },
            /**
             *
             * @param verticalTableFldObject {VerticalTableFldObject}
             */
            foundLinkedDcr(verticalTableFldObject) {
                if (!this.dcrObject || !this.dcrLinkedRows || !this.dcrObject._dcr_linked_tables) {
                    return null;
                }

                let ids = null;
                if (verticalTableFldObject) {
                    ids = verticalTableFldObject.single
                        ? [verticalTableFldObject.single.id]
                        : _.map(verticalTableFldObject.group || [], 'id');
                }

                return _.find(this.dcrObject._dcr_linked_tables, (link_tb) => {
                    return link_tb.is_active
                        && link_tb.linked_table_id
                        && (!ids
                            ? !link_tb.position_field_id
                            : ids.indexOf(link_tb.position_field_id) > -1);
                });
            },
        },
        mounted() {
            this.$set(this.tableRow, '_check_sign', uuidv4());

            _.each(this.sortedTableMetaFields, (el) => {
                if (!this.selectedCell.get_col()) {
                    let f = _.first(this.sortedTableMetaFields);
                    this.selectedCell.single_select(f.single ? f.single : f.group[0]);
                }
            });

            //for CondFormats loading
            if (this.isAddRow && this.tableRow) {
                this.updatedCell(this.tableRow);
            }

            eventBus.$on('global-click', this.clickHandler);
        },
        beforeDestroy() {
            eventBus.$off('global-click', this.clickHandler);
        }
    }
</script>

<style lang="scss" scoped>
    .spaced-table {
        height: min-content;
        width: 100%;
        border-collapse: separate;
        border-spacing: 0 7px;
        position: relative;
        table-layout: fixed;

        td {
            padding: 0;
            vertical-align: middle;
            position: relative;
            max-width: 530px;

            label {
                margin: 0;
            }
            .label-padding {
                padding-left: 5px;
            }
        }
        .edit-cell {
            border: 1px solid #CCC;
            border-radius: 4px;
        }
        .cell-btn {
            max-width: 100%;
            padding: 5px;
        }
    }
    .tooltip-class {
        position: relative;
        top: 2px;
        font-style: italic;
        line-height: 1;
        color: #333;
        display: block;
    }

    .float-rom-menu {
        position: fixed;
        z-index: 5000;
        text-align: left;
        background-color: #333;

        a {
            display: block;
            color: #FFF;
            padding: 1px 5px;
            cursor: pointer;
            text-decoration: none;

            &:hover {
                background-color: #555;
            }
        }
    }
</style>