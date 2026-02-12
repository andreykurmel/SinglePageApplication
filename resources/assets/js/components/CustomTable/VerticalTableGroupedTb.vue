<template>
    <table class="tablda-like">
        <tr v-for="curHeaderRow in maxRowsInHeader">
            <th v-for="(tableHeader, index) in vertTableFieldObject.group"
                v-if="getMultiColspan(index, curHeaderRow)
                    && getMultiRowspan(index, curHeaderRow)"
                :rowspan="getMultiRowspan(index, curHeaderRow)"
                :colspan="getMultiColspan(index, curHeaderRow)"
                :style="{
                    backgroundColor: tableHeader.header_background,
                    width: colWiPercent(tableHeader),
                }"
                @mouseenter="(e) => { $root.showHoverTooltip(e, tableHeader) }"
                @mouseleave="$root.leaveHoverTooltip"
            >
                <div v-if="nameNotHidden(tableHeader)" class="full-height flex flex--center flex--wrap" :style="textStyle">
                    <span class="head-content">{{ getMultiName(tableHeader, curHeaderRow) }}</span>

                    <template v-if="tableHeader.f_required && lastRow(tableHeader, curHeaderRow)">
                        <span class="required-wildcart">*</span>
                    </template>

                    <span v-if="hasInlineLink(tableHeader)"
                          class="pointer ml5"
                          @click.prevent.stop="$emit('active-group-inlined-link', vertTableFieldObject, tableHeader)"
                    >
                        <span v-if="!tableHeader.__inlined_link" class="fa fa-plus blue"></span>
                        <span v-else class="fa fa-minus blue"></span>
                    </span>
                </div>

                <header-resizer :table-header="tableHeader"
                                :init_width="tableHeader.width"
                                :user="user"
                ></header-resizer>
            </th>
        </tr>
        <!--Unit row in headers-->
        <tr>
            <th v-for="tableHeader in vertTableFieldObject.group"
                :key="tableHeader.id"
                v-if="tableHeader.unit || tableHeader.unit_display"
                :is="'custom-head-cell-table-data'"
                :table-meta="tableMeta"
                :table-header="tableHeader"
                :max-cell-rows="maxCellRows"
                :user="user"
                @updated-cell="updatedCell"
            ></th>
        </tr>
        <!--DATA-->
        <tr>
            <td v-for="tableHeader in vertTableFieldObject.group"
                v-if="valueNotHidden(tableHeader)"
                :key="tableHeader.id"
                :is="td"
                :global-meta="globalMeta"
                :table-meta="tableMeta"
                :settings-meta="settingsMeta"
                :table-row="tableRow || {}"
                :table-header="tableHeader"
                :cell-value="tableRow ? tableRow[tableHeader.field] : null"
                :user="user"
                :cell-height="cellHeight"
                :max-cell-rows="maxCellRows"
                :selected-cell="selectedCell"
                :is-selected-ext="selectedCell.is_selected(tableMeta, tableHeader)"
                :row-index="-1"
                :table_id="tableMeta.id"
                :behavior="behavior"
                :ref_tb_from_refcond="ref_tb_from_refcond"
                :no_ddl_colls="no_ddl_colls"
                :is-vert-table="true"
                :with_edit="with_edit"
                :is-add-row="isAddRow"
                :extra-pivot-fields="extraPivotFields"
                :is-link="isLink"
                :class="tableHeader.f_type !== 'Boolean' ? 'edit-cell' : ''"
                @show-add-ref-cond="showAddRefCond"
                @show-src-record="showSrcRecord"
                @updated-cell="updatedCell"
                @show-add-ddl-option="showAddDDLOption"
                @show-def-val-popup="showDefValPopup"
                @cell-menu="showRowMenu"
            ></td>
            <td v-else></td>
        </tr>

        <div v-if="row_menu_show && row_menu.row && row_menu.hdr"
             ref="row_menu"
             class="float-rom-menu"
             :style="rowMenuStyle"
        >
            <a @click="copyCell(row_menu.row, row_menu.hdr);row_menu_show = false;">Copy Cell</a>
        </div>
    </table>
</template>

<script>
import {eventBus} from "../../app";

import {SelectedCells} from '../../classes/SelectedCells';
import {VerticalTableFldObject} from './VerticalTableFldObject';

import HeaderResizer from "./Header/HeaderResizer";

import CustomHeadCellTableData from '../CustomCell/CustomHeadCellTableData.vue';
import CustomCellTableData from '../CustomCell/CustomCellTableData.vue';
import CustomCellSystemTableData from '../CustomCell/CustomCellSystemTableData.vue';
import CustomCellCorrespTableData from "../CustomCell/CustomCellCorrespTableData.vue";
import CustomCellSettingsDisplay from '../CustomCell/CustomCellSettingsDisplay.vue';
import CustomCellDisplayLinks from '../CustomCell/CustomCellDisplayLinks.vue';
import CustomCellSettingsDdl from '../CustomCell/CustomCellSettingsDdl.vue';
import CustomCellSettingsPermission from '../CustomCell/CustomCellSettingsPermission.vue';
import CustomCellSettingsDcr from '../CustomCell/CustomCellSettingsDcr.vue';
import CustomCellPivotField from '../CustomCell/CustomCellPivotField.vue';
import CustomCellColRowGroup from '../CustomCell/CustomCellColRowGroup.vue';
import CustomCellKanbanSett from '../CustomCell/CustomCellKanbanSett.vue';
import CustomCellSimplemapSett from '../CustomCell/CustomCellSimplemapSett.vue';
import CustomCellRefConds from '../CustomCell/CustomCellRefConds.vue';
import CustomCellCondFormat from '../CustomCell/CustomCellCondFormat.vue';
import CustomCellPlans from '../CustomCell/CustomCellPlans.vue';
import CustomCellTwilio from '../CustomCell/CustomCellTwilio.vue';
import CustomCellConnection from '../CustomCell/CustomCellConnection.vue';
import CustomCellPages from '../CustomCell/CustomCellPages.vue';
import CustomCellUserGroups from '../CustomCell/CustomCellUserGroups.vue';
import CustomCellInvitations from '../CustomCell/CustomCellInvitations.vue';
import CustomCellTableView from '../CustomCell/CustomCellTableView.vue';
import CustomCellStimAppView from '../CustomCell/CustomCellStimAppView.vue';
import CustomCellFolderView from '../CustomCell/CustomCellFolderView.vue';

import HeaderRowColSpanMixin from './../_Mixins/HeaderRowColSpanMixin.vue';
import CellStyleMixin from './../_Mixins/CellStyleMixin.vue';
import CellMenuMixin from "../_Mixins/CellMenuMixin";

export default {
        name: "VerticalTableGroupedTb",
        mixins: [
            HeaderRowColSpanMixin,
            CellStyleMixin,
            CellMenuMixin,
        ],
        components: {
            HeaderResizer,
            CustomHeadCellTableData,
            CustomCellTableData,
            CustomCellSystemTableData,
            CustomCellCorrespTableData,
            CustomCellSettingsDisplay,
            CustomCellDisplayLinks,
            CustomCellSettingsDdl,
            CustomCellSettingsPermission,
            CustomCellSettingsDcr,
            CustomCellPivotField,
            CustomCellColRowGroup,
            CustomCellKanbanSett,
            CustomCellSimplemapSett,
            CustomCellRefConds,
            CustomCellCondFormat,
            CustomCellPlans,
            CustomCellTwilio,
            CustomCellConnection,
            CustomCellPages,
            CustomCellUserGroups,
            CustomCellInvitations,
            CustomCellTableView,
            CustomCellStimAppView,
            CustomCellFolderView,
        },
        data: function () {
            return {
            };
        },
        props:{
            vertTableFieldObject: VerticalTableFldObject,

            settingsMeta: {
                type: Object,
                default: function () {
                    return {};
                }
            },
            selectedCell: SelectedCells,
            globalMeta: Object,
            tableMeta: Object,
            tableRow: Object,
            cellHeight: Number,
            maxCellRows: {
                type: Number,
                default: 0
            },
            user: Object,
            td: String,
            behavior: String,
            ref_tb_from_refcond: Object|null,
            no_ddl_colls: Array,
            isAddRow: Boolean,
            with_edit: {
                type: Boolean,
                default: true
            },
            hideNames: Array,
            extraPivotFields: Array,
            isLink: Object,
            is_def_fields: Boolean,
        },
        computed: {
            sub_remover() {
                return this.vertTableFieldObject.sub_header_name ? this.vertTableFieldObject.sub_header_name+',' : '';
            },
            totWidth() {
                let total = 0;
                _.each(this.vertTableFieldObject.group, (fld) => {
                    total += this.$root.getFloat(fld.width);
                });
                return total;
            },
        },
        watch: {
            sub_remover: {
                handler(val) {
                    this.sub_header_remover = val;
                },
                immediate: true,
            }
        },
        methods: {
            hasInlineLink(tableHeader) {
                return tableHeader
                    && _.find(tableHeader._links, {inline_in_vert_table: 1})
                    && ! this.is_def_fields;
            },
            colWiPercent(header) {
                let wi = (this.$root.getFloat(header.width) / this.totWidth);
                return (wi * 100) + '%';
            },
            nameNotHidden(tableHeader) {
                return VerticalTableFldObject.fieldSetting('fld_display_name', tableHeader, null, this.behavior)
                    &&
                    (!this.hideNames || this.hideNames.indexOf(tableHeader.field) > -1);
            },
            valueNotHidden(tableHeader) {
                return !! VerticalTableFldObject.fieldSetting('fld_display_value', tableHeader, null, this.behavior);
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
            showDefValPopup(tableRow, moreParam) {
                this.$emit('show-def-val-popup', tableRow, moreParam);
            },
            showAddDDLOption(tableHeader, tableRow) {
                this.$emit('show-add-ddl-option', tableHeader, tableRow);
            },
            //for HeaderRowColSpanMixin
            isShowFieldElem(header) {
                return true;//all are already filtered
            },
        },
        created() {
            this.fillHeaderRowColSpanCache(this.vertTableFieldObject.group);
        },
        mounted() {
            eventBus.$on('global-click', this.clickHandler);
        },
        beforeDestroy() {
            eventBus.$off('global-click', this.clickHandler);
        }
    }
</script>

<style lang="scss" scoped>
    @import './../CommonBlocks/TabldaLike';
    .tablda-like {
        width: 100%;
        table-layout: fixed;
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