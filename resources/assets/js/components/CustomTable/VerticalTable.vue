<template>
    <table class="spaced-table"
           :id="tb_id"
           ref="vertTb"
           :draw_handler="fullTableHeight()"
    >
        <colgroup>
            <col :width="widths_name">
            <col :width="widths_col">
            <col v-if="(hasUnits || hasHistory) && widths.history" :width="widths.history">
        </colgroup>
        <tbody>

        <tr v-if="!sortedTableMetaFields.length">
            <td :colspan="(hasUnits || hasHistory) ? 3 : 2" style="text-align: center">
                <label>No field is turned ON for display or</label><br>
                <label>no field is available per permission</label>
            </td>
        </tr>

        <tr v-if="sortedTableMetaFields.length && canRedefineWidth">
            <td style="position: relative; height: 7px;">
                <i class="glyphicon glyphicon-triangle-bottom"
                   style="position: absolute; right: -8px; top: -3px; font-size: 14px; z-index: 10; cursor: pointer;"
                   draggable="true"
                   @dragstart="dragResizeStart"
                   @drag="dragResizeDo"
                   @dragend="dragResizeEnd"
                ></i>
            </td>
            <td></td>
            <td v-if="(hasUnits || hasHistory) && widths.history"></td>
        </tr>

        <template v-for="(vtfo, index) in sortedTableMetaFields">

            <!--SECTION PART-->
            <tr v-if="getSection(vtfo)">
                <td :colspan="((hasUnits || hasHistory) && widths.history) ? 3 : 2">
                    <div class="full-frame flex" :style="sectionFlexStyle(vtfo)">
                        <div>{{ getSection(vtfo) }}</div>
                    </div>
                </td>
            </tr>

            <!--SUB HEADERS-->
            <template v-if="!vtfo.is_hlior && nameNotHidden(vtfo)">
                <tr v-for="(sub,idx) in vtfo.sub_headers"
                    v-if="!lastGroupLvl(vtfo, sub)"
                >
                    <td :colspan="2 + spanDataCellApplied(vtfo)" :style="getSubHeaderStyle(idx, vtfo)">
                        <vertical-table-border
                            v-if="!vtfo.is_hlior"
                            :level="(idx+vtfo.base_subs_lvl)"
                        ></vertical-table-border>
                        <label :style="getSubHdrStyle()">{{ $root.strip_danger_tags(sub) }}</label>
                    </td>
                    <td v-if="(hasUnits || hasHistory) && widths.history"></td>
                </tr>
            </template>
            <!--SUB HEADERS-->

            <!--SINGLE COLUMN-->
            <template v-if="vtfo.single">
                <!--DIVIDER-->
                <tr v-if="!vtfo.is_hlior && vtfo.level && nameNotHidden(vtfo)">
                    <td :colspan="2 + spanDataCellApplied(vtfo)">
                        <vertical-table-border
                            :level="vtfo.level"
                        ></vertical-table-border>
                    </td>
                    <td v-if="(hasUnits || hasHistory) && widths.history"></td>
                </tr>
                <!--HEADER ABOVE THE CELL-->
                <tr v-if="nameNotHidden(vtfo, true)">
                    <td :colspan="2 + spanDataCellApplied(vtfo)"
                        :style="{backgroundColor: vert_bg_avail ? vtfo.single.header_background || vert_bg_color : null}"
                    >
                        <vertical-table-border
                            v-if="!vtfo.is_hlior"
                            :level="vtfo.level"
                        ></vertical-table-border>
                        <label :style="getLabelStyle(vtfo)">
                            <span v-if="vtfo.is_hlior">
                                {{ vtfo.level ? vtfo.global_subs.join(', ')+', ' : '' }}
                            </span>
                            <span>{{ getHeader(vtfo.single) }}</span>
                            <span v-if="vtfo.single.f_required" class="required-wildcart">*</span>
                            <span v-if="hasInlineLink(vtfo.single)"
                                  class="pointer"
                                  @click.prevent.stop="vtfo.single.__inlined_link = !vtfo.single.__inlined_link"
                            >
                                <span v-if="!vtfo.single.__inlined_link" class="fa fa-plus blue"></span>
                                <span v-else class="fa fa-minus blue"></span>
                            </span>
                        </label>
                    </td>
                    <td v-if="(hasUnits || hasHistory) && widths.history"></td>
                </tr>
                <!--TOOLTIP TOP-->
                <tr v-if="tooltip_pos === 'up' && vtfo.single.tooltip_show && vtfo.single.tooltip ">
                    <td v-if="nameNotHidden(vtfo, false)">
                        <vertical-table-border
                            v-if="!vtfo.is_hlior"
                            :level="vtfo.level"
                        ></vertical-table-border>
                    </td>
                    <td :colspan="(nameNotHidden(vtfo, false) ? 1 : 2) + spanDataCellApplied(vtfo)">
                        <vertical-table-border
                            v-if="!nameNotHidden(vtfo, false) && !vtfo.is_hlior"
                            :level="vtfo.level"
                        ></vertical-table-border>
                        <span class="tooltip-class" :style="headerTitleStyle(vtfo)" v-html="vtfo.single.tooltip"></span>
                    </td>
                    <td v-if="(hasUnits || hasHistory) && widths.history"></td>
                </tr>
                <!--CONTENT-->
                <tr>
                    <td v-if="nameNotHidden(vtfo, false)"
                        :style="{backgroundColor: vert_bg_avail ? vtfo.single.header_background || vert_bg_color : null}"
                    >
                        <vertical-table-border
                            v-if="!vtfo.is_hlior"
                            :level="vtfo.level"
                        ></vertical-table-border>
                        <label :style="getLabelStyle(vtfo)">
                            <span v-if="vtfo.is_hlior">
                                {{ vtfo.level ? vtfo.global_subs.join(', ')+', ' : '' }}
                            </span>
                            <span>{{ getHeader(vtfo.single) }}</span>
                            <span v-if="vtfo.single.f_required" class="required-wildcart">*</span>
                            <span v-if="hasInlineLink(vtfo.single)"
                                  class="pointer"
                                  @click.prevent.stop="vtfo.single.__inlined_link = !vtfo.single.__inlined_link"
                            >
                                <span v-if="!vtfo.single.__inlined_link" class="fa fa-plus blue"></span>
                                <span v-else class="fa fa-minus blue"></span>
                            </span>
                        </label>
                    </td>

                    <!--TOP/BOT enabled-->
                    <td v-if="valueNotHidden(vtfo.single) && !nameNotHidden(vtfo, false)"
                        :colspan="2 + spanDataCellApplied(vtfo)"
                    >
                        <vertical-table-border
                            v-if="!vtfo.is_hlior"
                            :level="vtfo.level"
                        ></vertical-table-border>
                        <table :style="getTopBotCellStyle(vtfo)">
                            <tr>
                                <td :is="td"
                                    :global-meta="globalMeta"
                                    :table-meta="tableMeta"
                                    :settings-meta="settingsMeta"
                                    :table-row="tableRow || {}"
                                    :table-header="vtfo.single"
                                    :cell-value="tableRow ? tableRow[vtfo.single.field] : null"
                                    :user="user"
                                    :cell-height="cellHeight"
                                    :max-cell-rows="maxCellRows"
                                    :selected-cell="selectedCell"
                                    :is-selected-ext="selectedCell.is_selected(tableMeta, vtfo.single)"
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
                                    :extra-pivot-fields="extraPivotFields"
                                    :is-link="isLink"
                                    :class="vtfo.single.f_type !== 'Boolean' ? 'edit-cell' : ''"
                                    :style="checkNoBorder(vtfo.single)"
                                    @show-add-ref-cond="showAddRefCond"
                                    @show-src-record="showSrcRecord"
                                    @updated-cell="updatedCell"
                                    @show-add-ddl-option="showAddDDLOption"
                                    @show-def-val-popup="showDefValPopup"
                                    @cell-menu="showRowMenu"
                                ></td>
                            </tr>
                        </table>
                    </td>
                    <!--TOP/BOT disabled-->
                    <td v-else-if="valueNotHidden(vtfo.single)"
                        :is="td"
                        :global-meta="globalMeta"
                        :table-meta="tableMeta"
                        :settings-meta="settingsMeta"
                        :table-row="tableRow || {}"
                        :table-header="vtfo.single"
                        :cell-value="tableRow ? tableRow[vtfo.single.field] : null"
                        :user="user"
                        :cell-height="cellHeight"
                        :max-cell-rows="maxCellRows"
                        :selected-cell="selectedCell"
                        :is-selected-ext="selectedCell.is_selected(tableMeta, vtfo.single)"
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
                        :extra-pivot-fields="extraPivotFields"
                        :is-link="isLink"
                        :class="vtfo.single.f_type !== 'Boolean' ? 'edit-cell' : ''"
                        :style="checkNoBorder(vtfo.single)"
                        :colspan="1 + spanDataCellApplied(vtfo)"
                        @show-add-ref-cond="showAddRefCond"
                        @show-src-record="showSrcRecord"
                        @updated-cell="updatedCell"
                        @show-add-ddl-option="showAddDDLOption"
                        @show-def-val-popup="showDefValPopup"
                        @cell-menu="showRowMenu"
                    ></td>
                    <!--Empty cell-->
                    <td v-else :colspan="(nameNotHidden(vtfo, false) ? 1 : 2) + spanDataCellApplied(vtfo)"></td>

                    <td v-if="(hasUnits || hasHistory) && widths.history">
                        <!--UNIT-->
                        <label v-if="getCurUnit(vtfo.single)"
                               class="label-padding"
                        >{{ getCurUnit(vtfo.single) }}</label>
                        <!--HISTORY-->
                        <button v-if="canSeeHistory && vtfo.single.show_history"
                                class="btn btn-sm btn-default cell-btn"
                                @click="$emit('toggle-history', vtfo.single)"
                                title="History"
                        ><img src="/assets/img/history.png" width="20" height="20"></button>
                    </td>
                </tr>
                <!--TOOLTIP BOTTOM-->
                <tr v-if="tooltip_pos === 'down' && vtfo.single.tooltip_show && vtfo.single.tooltip ">
                    <td v-if="nameNotHidden(vtfo, false)">
                        <vertical-table-border
                            v-if="!vtfo.is_hlior"
                            :level="vtfo.level"
                        ></vertical-table-border>
                    </td>
                    <td :colspan="(nameNotHidden(vtfo, false) ? 1 : 2) + spanDataCellApplied(vtfo)">
                        <vertical-table-border
                            v-if="!nameNotHidden(vtfo, false) && !vtfo.is_hlior"
                            :level="vtfo.level"
                        ></vertical-table-border>
                        <span class="tooltip-class" :style="headerTitleStyle(vtfo)" v-html="vtfo.single.tooltip"></span>
                    </td>
                    <td v-if="(hasUnits || hasHistory) && widths.history"></td>
                </tr>
                <tr v-if="hasInlineLink(vtfo.single) && vtfo.single.__inlined_link">
                    <td v-if="vtfo.single.__inlined_field_width">
                        <vertical-table-border
                            v-if="!vtfo.is_hlior"
                            :level="vtfo.level"
                        ></vertical-table-border>
                    </td>
                    <td :colspan="(vtfo.single.__inlined_field_width ? 1 : 2) + spanDataCellApplied(vtfo)">
                        <vertical-table-border
                            v-if="!vtfo.is_hlior && !vtfo.single.__inlined_field_width"
                            :level="vtfo.level"
                        ></vertical-table-border>
                        <inline-linked-tabs
                            :table-header="vtfo.single"
                            :table-row="tableRow"
                            :table-meta="tableMeta"
                            :redraw_history="redraw_history"
                            :with_edit="with_edit"
                            :inlined_full="vtfo.single.__inlined_field_width"
                            :style="inlinedLinkStl(vtfo)"
                            @show-src-record="showSrcRecord"
                            @hist-updated="histUpdated"
                            @redraw-inlined-width="redrawInlinedWidth(vtfo.single)"
                        ></inline-linked-tabs>
                    </td>
                </tr>
                <!-- SPACE BETWEEN ROWS -->
                <tr v-if="nameNotHidden(vtfo) || valueNotHidden(vtfo.single)">
                    <td :colspan="((hasUnits || hasHistory) && widths.history) ? 3 : 2" :style="styleSpacing(vtfo)">
                        <vertical-table-border
                            v-if="!vtfo.is_hlior && anotherSpacing(vtfo)"
                            :level="vtfo.level"
                        ></vertical-table-border>
                    </td>
                </tr>
            </template>
            <!--SINGLE COLUMN-->

            <!--COLUMNS ARE GROUPED IN TABLE-->
            <template v-else="">
                <!--HEADER ABOVE THE TABLE-->
                <tr v-if="nameNotHidden(vtfo, true)">
                    <td :colspan="2 + spanDataCellApplied(vtfo)"
                        :style="{backgroundColor: vert_bg_avail ? vertObjHeader(vtfo).header_background || vert_bg_color : null}"
                    >
                        <vertical-table-border
                            v-if="!vtfo.is_hlior"
                            :level="vtfo.level-1"
                        ></vertical-table-border>
                        <label :style="getLabelStyle(vtfo, -1)">{{ getGroupTbHeader(vtfo) }}</label>
                    </td>
                    <td v-if="(hasUnits || hasHistory) && widths.history"></td>
                </tr>
                <!--TABLE-->
                <tr>
                    <td v-if="!vtfo.table_full_wi || (vtfo.level && nameNotHidden(vtfo, false))">
                        <vertical-table-border
                            v-if="vtfo.level && !vtfo.is_hlior"
                            :level="vtfo.level-1"
                        ></vertical-table-border>
                        <label :style="getLabelStyle(vtfo, -1)">{{ getGroupTbHeader(vtfo) }}</label>
                    </td>
                    <td :colspan="(!vtfo.table_full_wi || (vtfo.level && nameNotHidden(vtfo, false)) ? 1 : 2) + spanDataCellApplied(vtfo)">
                        <vertical-table-border
                            v-if="vtfo.level && !vtfo.is_hlior && nameNotHidden(vtfo, true)"
                            :level="vtfo.level-1"
                        ></vertical-table-border>
                        <div style="margin-top: 5px;" :style="{marginLeft: tbMargin(vtfo)+'px'}">
                            <vertical-table-grouped-tb
                                    :vert-table-field-object="vtfo"
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
                                    :extra-pivot-fields="extraPivotFields"
                                    :is-link="isLink"
                                    :is_def_fields="is_def_fields"
                                    @show-add-ref-cond="showAddRefCond"
                                    @show-src-record="showSrcRecord"
                                    @updated-cell="updatedCell"
                                    @show-add-ddl-option="showAddDDLOption"
                                    @show-def-val-popup="showDefValPopup"
                                    @active-group-inlined-link="toggleInlinedLink"
                            ></vertical-table-grouped-tb>
                        </div>
                    </td>
                </tr>
                <tr v-if="hasInlineLink(vtfo.group_lnk) && vtfo.group_lnk.__inlined_link">
                    <td v-if="vtfo.group_lnk.__inlined_field_width">
                        <vertical-table-border
                            v-if="!vtfo.is_hlior"
                            :level="vtfo.level"
                        ></vertical-table-border>
                    </td>
                    <td :colspan="(vtfo.group_lnk.__inlined_field_width ? 1 : 2) + spanDataCellApplied(vtfo)">
                        <vertical-table-border
                            v-if="!vtfo.is_hlior && !vtfo.group_lnk.__inlined_field_width"
                            :level="vtfo.level"
                        ></vertical-table-border>
                        <inline-linked-tabs
                            :table-header="vtfo.group_lnk"
                            :table-row="tableRow"
                            :table-meta="tableMeta"
                            :redraw_history="redraw_history"
                            :with_edit="with_edit"
                            :inlined_full="vtfo.group_lnk.__inlined_field_width"
                            :style="inlinedLinkStl(vtfo)"
                            @show-src-record="showSrcRecord"
                            @hist-updated="histUpdated"
                            @redraw-inlined-width="redrawInlinedWidth(vtfo.single)"
                        ></inline-linked-tabs>
                    </td>
                </tr>
                <!-- SPACE BETWEEN ROWS -->
                <tr>
                    <td :colspan="(hasUnits || hasHistory) ? 3 : 2" :style="styleSpacing(vtfo)">
                        <vertical-table-border
                            v-if="!vtfo.is_hlior && anotherSpacing(vtfo)"
                            :level="vtfo.level-1"
                        ></vertical-table-border>
                    </td>
                </tr>
            </template>
            <!--COLUMNS ARE GROUPED IN TABLE-->

            <!--DCR LINKED TABLE-->
            <template v-if="vtfo.dcr_linked">
                <tr v-if="vtfo.dcr_linked.header">
                    <td :colspan="2 + spanDataCellApplied(vtfo, vtfo.dcr_linked)"
                        :style="{backgroundColor: vert_bg_avail ? vtfo.single.header_background || vert_bg_color : null}"
                    >
                        <vertical-table-border
                            v-if="!vtfo.is_hlior && vtfo.dcr_linked.style == 'Default'"
                            :level="vtfo.level-1"
                        ></vertical-table-border>
                        <label :style="getLabelStyle(vtfo, -1)">
                            <span>{{ vtfo.dcr_linked.header }}</span>
                        </label>
                    </td>
                    <td v-if="(hasUnits || hasHistory) && widths.history"></td>
                </tr>
                <tr>
                    <td v-if="vtfo.dcr_linked.style == 'Default'">
                        <vertical-table-border
                            v-if="!vtfo.is_hlior"
                            :level="vtfo.level"
                        ></vertical-table-border>
                    </td>
                    <td :colspan="(vtfo.dcr_linked.style == 'Default' ? 1 : 2) + spanDataCellApplied(vtfo, vtfo.dcr_linked)">
                        <vertical-linked-table
                            :parent-meta="globalMeta"
                            :parent-row="tableRow"
                            :linked-rows-object="dcrLinkedRows"
                            :dcr-linked-table="vtfo.dcr_linked"
                            :with_edit="with_edit"
                            :is-visible="visible"
                            @linked-update="$emit('linked-update')"
                        ></vertical-linked-table>
                    </td>
                    <td v-if="(hasUnits || hasHistory) && widths.history"></td>
                </tr>
                <!-- SPACE BETWEEN ROWS -->
                <tr>
                    <td :colspan="(hasUnits || hasHistory) ? 3 : 2" :style="styleSpacing(vtfo)">
                        <vertical-table-border
                            v-if="!vtfo.is_hlior && vtfo.dcr_linked.style == 'Default' && anotherSpacing(vtfo)"
                            :level="vtfo.level-1"
                        ></vertical-table-border>
                    </td>
                </tr>
            </template>
            <!--DCR LINKED TABLE-->

        </template>

        <!-- DCR CATALOG ELEMENT -->
        <tr v-if="ctlgAmountField">
            <td>
                <label :style="tableMeta.is_system ? textSysStyle : textStyle">Amount: </label>
            </td>
            <td :colspan="1 + spanDataCellApplied({})">
                <input class="form-control input-sm" type="number" v-model="tableRow.__ctlg_amount" @change="$emit('updated-ctlg')">
            </td>
            <td v-if="(hasUnits || hasHistory) && widths.history"></td>
        </tr>

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

import VerticalTableBorder from "./VerticalTableBorder";
import VerticalTableGroupedTb from "./VerticalTableGroupedTb";
import VerticalLinkedTable from "./VerticalLinkedTable";

import {eventBus} from "../../app";
import CellTableSysContent from "../CustomCell/InCell/CellTableSysContent.vue";

export default {
        name: "VerticalTable",
        mixins: [
            SortFieldsForVerticalMixin,
            CellStyleMixin,
            CellMenuMixin,
        ],
        components: {
            CellTableSysContent,
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
                titleWidth: null,
                resizerStart: 0,
                fullTableWidth: 0,
                totalTbHeight: 0,
                redraw_history: 0,
                selectedCell: new SelectedCells(this.disabled_sel),
                sortedTableMetaFields: [],
                isVertTable: true,
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
            with_edit: {
                type: Boolean,
                default: true
            },
            disabled_sel: Boolean,
            no_height_limit: Boolean,
            dcrObject: Object,
            dcrLinkedRows: Object,
            parentRow: Object,
            extraPivotFields: Array,
            headersChanger: Object,
            activeHeightWatcher: Boolean,
            isLink: Object,
            canRedefineWidth: Boolean,
            visible: Boolean,
            foreignTitleWidth: Number,
            ctlgAmountField: String,
        },
        watch: {
            foreignTitleWidth() {
                this.titleWidth = this.initialHeaderWidth();
            },
            tableRow: {
                handler(val) {
                    let vertObjects = this.createSortedTableMetaFields(this.tableMeta._fields);
                    this.sortedTableMetaFields = this.prepareDcrLinked(vertObjects);
                    this.$emit('showed-elements', this.sortedTableMetaFields.length);
                },
                immediate: true,
                deep: true,
            },
            visible: {
                handler(val) {
                    if (val) {
                        if (! this.fullTableWidth) {
                            this.fullTableWidth = Number(this.$refs.vertTb.clientWidth);
                        }
                        this.fullTableHeight();
                    }
                }
            },
        },
        computed: {
            hasUnits() {
                let has = false;
                if (this.sortedTableMetaFields) {
                    _.each(this.sortedTableMetaFields, (vertTableFieldObject) => {
                        if (vertTableFieldObject.single && this.getCurUnit(vertTableFieldObject.single)) {
                            has = true;
                        }
                    });
                }
                return has;
            },
            hasHistory() {
                if (!this.canSeeHistory) {
                    return false;
                }
                let has = false;
                if (this.sortedTableMetaFields) {
                    _.each(this.sortedTableMetaFields, (vertTableFieldObject) => {
                        if (vertTableFieldObject.single && vertTableFieldObject.single.show_history) {
                            has = true;
                        }
                    });
                }
                return has;
            },
            widths_name() {
                if (this.titleWidth) {
                    return this.titleWidth < 1
                        ? ((this.titleWidth*100) + '%')
                        : (this.titleWidth + 'px');
                } else {
                    return this.widths.name;
                }
            },
            widths_col() {
                if (this.titleWidth) {
                    return this.titleWidth < 1
                        ? ((100 - (this.titleWidth*100)) + '%')
                        : '100%';//('calc(100% - ' + this.titleWidth + 'px)');
                } else {
                    return this.widths.col;
                }
            },
            vert_bg_avail() {
                return !!this.tableMeta.vert_tb_bgcolor;
            },
            vert_bg_color() {
                return this.vert_bg_avail ? this.$root.themeTableBgColor(this.tableMeta) || '#DDD' : null;
            },
        },
        methods: {
            initialHeaderWidth() {
                if (this.foreignTitleWidth) {
                    return Number(this.foreignTitleWidth) || 0;
                }

                if (this.isLink) {
                    let val = Number(this.isLink.listing_header_wi) || 0.35;
                    if (val > 1) {
                        val = val / this.fullTableWidth;
                    }
                    return val*100;
                }
                let percents = Number(this.tableMeta.vert_tb_hdrwidth) || 35;
                return percents > 1 ? percents/100 : percents;
            },
            fullTableHeight() {
                if (this.activeHeightWatcher) {
                    window.setTimeout(() => {
                        if (this.$refs.vertTb) {
                            let curHeight = Number(this.$refs.vertTb.clientHeight);
                            if (Math.abs(this.totalTbHeight - curHeight) > 10) {
                                this.totalTbHeight = curHeight;
                                this.$emit('total-tb-height-changed', this.totalTbHeight);
                            }
                        }
                    }, 100);
                }
            },
            getSection(vertTableFieldObject) {
                let hdr = vertTableFieldObject.single || _.first(vertTableFieldObject.group);
                return hdr ? hdr.section_header : '';
            },
            sectionFlexStyle(vertTableFieldObject) {
                let hdr = vertTableFieldObject.single || _.first(vertTableFieldObject.group);
                let res = {
                    //paddingLeft: (vertTableFieldObject.level*15) + 'px',
                    justifyContent: hdr.section_align_h === 'Right' ? 'right' : (hdr.section_align_h === 'Center' ? 'center' : 'left'),
                    alignItems: hdr.section_align_v === 'Top' ? 'start' : (hdr.section_align_v === 'Bottom' ? 'end' : 'center'),
                    height: Number(hdr.section_height || 50)+'px',
                    fontSize: Number(hdr.section_size || 16)+'px',
                    fontStyle: null,
                    fontWeight: null,
                    textDecoration: null,
                };

                let fonts = this.$root.parseMsel(hdr.section_font);
                _.each(fonts, (f) => {
                    (f === 'Italic' ? res.fontStyle = 'italic' : res.fontStyle = res.fontStyle || null);
                    (f === 'Bold' ? res.fontWeight = 'bold' : res.fontWeight = res.fontWeight || null);
                    (f === 'Strikethrough' ? res.textDecoration = 'line-through' : res.textDecoration = res.textDecoration || null);
                    (f === 'Overline' ? res.textDecoration = 'overline' : res.textDecoration = res.textDecoration || null);
                    (f === 'Underline' ? res.textDecoration = 'underline' : res.textDecoration = res.textDecoration || null);
                });

                return res;
            },
            styleSpacing(vertTableFieldObject) {
                let he = (this.tableMeta.row_space_size/100 * this.lineHeight)
                    || this.rowSpacing(vertTableFieldObject);
                return {
                    height: he + 'px',
                };
            },
            rowSpacing(vertTableFieldObject) {
                vertTableFieldObject = vertTableFieldObject || {};
                let paRow = this.parentRow || {};
                return paRow.kanban_row_spacing
                    || this.spacingHeader(vertTableFieldObject)
                    || this.tableMeta.vert_tb_rowspacing;
            },
            spacingHeader(vertTableFieldObject) {
                vertTableFieldObject = vertTableFieldObject || {};
                let taHeader = vertTableFieldObject.single || _.last(vertTableFieldObject.group) || {};
                return taHeader.form_row_spacing;
            },
            anotherSpacing(vertTableFieldObject) {
                //OLD: this.spacingHeader(vertTableFieldObject) != this.rowSpacing(vertTableFieldObject) //But empty between the same rows
                return this.spacingHeader(vertTableFieldObject) || this.rowSpacing(vertTableFieldObject);
            },
            hdrMargnLft(vertObj) {
                return vertObj.is_hlior ? 0 : 15;
            },

            //INLINED LINK
            toggleInlinedLink(vertTableFieldObject, tableHeader) {
                let key = '__inlined_link';
                tableHeader[key] = !tableHeader[key];
                if (tableHeader[key]) {
                    _.each(vertTableFieldObject.group, (hdrTable) => {
                        hdrTable[key] = false;
                    });
                    tableHeader[key] = true;
                }
                vertTableFieldObject.group_lnk = tableHeader;
            },
            hasInlineLink(tableHeader) {
                let links = tableHeader ? _.filter(tableHeader._links, {link_type: 'Record'}) : [];
                return _.find(links, {inline_in_vert_table: 1})
                    && ! this.is_def_fields;
            },
            inlinedLinkStl(vertObj) {
                if (
                    vertObj.is_hlior
                    || (vertObj.single && vertObj.single.__inlined_field_width)
                    || (vertObj.group_lnk && vertObj.group_lnk.__inlined_field_width)
                ) {
                    return {};
                }
                return {
                    marginLeft: (vertObj.level * this.hdrMargnLft(vertObj))+'px',
                };
            },
            lastGroupLvl(vertObj, sub) {
                return vertObj.group && vertObj.sub_headers.indexOf(sub) == (vertObj.sub_headers.length - 1);
            },
            tbMargin(vertObj) {
                if (vertObj.level && !vertObj.is_hlior && this.nameNotHidden(vertObj, true)) {
                    return (vertObj.level - 1) * 15
                }
                return 0;
            },
            vertObjHeader(vertObj) {
                if (vertObj.group) {
                    return _.first(vertObj.group);
                }
                return vertObj.single;
            },
            nameNotHidden(vertObj, targettopbot) {
                let tableHeader = this.vertObjHeader(vertObj);
                if (targettopbot === undefined) {
                    targettopbot = !! VerticalTableFldObject.fieldSetting('is_topbot_in_popup', tableHeader, null, this.behavior);
                }

                return (targettopbot === !! VerticalTableFldObject.fieldSetting('is_topbot_in_popup', tableHeader, null, this.behavior))
                    &&
                    VerticalTableFldObject.fieldSetting('fld_display_name', tableHeader, null, this.behavior)
                    &&
                    (!this.hideNames || this.hideNames.indexOf(tableHeader.field) > -1);
            },
            valueNotHidden(tableHeader) {
                return !! VerticalTableFldObject.fieldSetting('fld_display_value', tableHeader, null, this.behavior);
            },
            checkNoBorder(tableHeader) {
                let noborder = ! VerticalTableFldObject.fieldSetting('fld_display_border', tableHeader, null, this.behavior)
                    || (this.hideBorders && this.hideBorders.indexOf(tableHeader.field) > -1);
                return noborder ? {border: 'none'} : null;
            },
            headerTitleStyle(vertTableFieldObject) {
                let style = _.cloneDeep(this.tableMeta.is_system ? this.textSysStyle : this.textStyle);
                style.fontSize = Number(parseInt(style.fontSize) - 2) + 'px';
                style.textAlign = 'left' /* || vertTableFieldObject.single.col_align || 'right'*/;
                return style;
            },
            getCurUnit(header) {
                return UnitConversion.showUnit(header, this.tableMeta);
            },
            spanDataCellApplied(vtfo, dcrLinked) {
                if (! this.tableMeta.no_span_data_cell_in_popup) {
                    let hasUnitCell = (this.hasUnits || this.hasHistory) && this.widths.history;
                    let emptyPresentUnitCell = true;
                    if (vtfo.single && ! dcrLinked) {
                        emptyPresentUnitCell = !this.getCurUnit(vtfo.single) && (!this.canSeeHistory || !vtfo.single.show_history);
                    }
                    return hasUnitCell && emptyPresentUnitCell ? 1 : 0;
                }
                return 0;
            },
            /**
             * createSortedTableMetaFields
             * @param fields
             * @returns {Array} of {VerticalTableFldObject}
             */
            createSortedTableMetaFields(fields) {
                let pr = this.parentRow || {};
                let fld_objects = this.sortAndFilterFields(this.tableMeta, fields, this.tableRow, !!this.is_def_fields);
                let all_is_single = (this.behavior === 'kanban_view' && !pr.kanban_form_table);
                //level headers
                return VerticalTableFldObject.buildSubHeaders(fld_objects, all_is_single, this.extraPivotFields, this.behavior);
            },

            getHeader(field) {
                if (this.headersChanger && this.headersChanger[field.field]) {
                    return this.headersChanger[field.field];
                }
                switch (VerticalTableFldObject.fieldSetting('fld_display_header_type', field, null, this.behavior)) {
                    case 'symbol': return field.formula_symbol;
                    case 'tooltip': return field.tooltip;
                    case 'placeholder': return field.placeholder_content;
                    case 'default':
                    default: return _.last(field.name.split(','));
                }
            },
            getGroupTbHeader(vertObj) {
                return vertObj.is_hlior
                    ? vertObj.global_subs.join(', ')
                    : _.last(vertObj.global_subs);
            },
            getSubHeaderStyle(idx, vertObj) {
                let level = idx + vertObj.base_subs_lvl;
                return {
                    fontSize: (1.3 - level*0.1)+'em',
                    paddingLeft: (level * this.hdrMargnLft(vertObj))+'px',
                };
            },
            getLabelStyle(vertObj, modifier) {
                let style = _.cloneDeep(this.tableMeta.is_system ? this.textSysStyle : this.textStyle);
                let lvl = vertObj.level + to_float(modifier);
                if (lvl < 0) {
                    lvl = 0;
                }
                style.marginLeft = (lvl * this.hdrMargnLft(vertObj)) + 'px';
                return style;
            },
            getSubHdrStyle(level) {
                let style = _.cloneDeep(this.tableMeta.is_system ? this.textSysStyle : this.textStyle);
                style.lineHeight = Number(parseInt(style.fontSize) + 6) + 'px';
                style.fontSize = Number(parseInt(style.fontSize) + 4) + 'px';
                return style;
            },
            getTopBotCellStyle(vertObj) {
                let px = vertObj.level * this.hdrMargnLft(vertObj);
                return {
                    width: 'calc(100% - ' + px + 'px)',
                    marginLeft: px + 'px',
                };
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
                this.redraw_history++;
            },
            showDefValPopup(tableRow, moreParam) {
                this.$emit('show-def-val-popup', tableRow, moreParam);
            },
            showAddDDLOption(tableHeader, tableRow) {
                this.$emit('show-add-ddl-option', tableHeader, tableRow);
            },
            histUpdated() {
                this.$emit('hist-updated');
            },
            redrawInlinedWidth(singleOrGroup) {
                singleOrGroup.__inlined_field_width = ! singleOrGroup.__inlined_field_width;
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

            //resize
            dragResizeStart(e) {
                if (this.initialHeaderWidth()) {
                    e = e || window.event;
                    this.resizerStart = e.clientX;
                } else {
                    Swal('Info','Resize is not available!');
                }
            },
            dragResizeDo(e) {
                e = e || window.event;
                if (e.clientX) {
                    let change = Number(e.clientX - this.resizerStart) / this.fullTableWidth;
                    let initWidth = this.initialHeaderWidth();
                    let percents = initWidth < 1 ? initWidth : initWidth / this.fullTableWidth;
                    this.titleWidth = percents + change;
                }
            },
            dragResizeEnd(e) {
                if (this.isLink) {
                    this.isLink.listing_header_wi = Number(this.titleWidth).toFixed(3);
                    this.$root.sm_msg_type = 1;
                    axios.put('/ajax/settings/data/link', {
                        table_link_id: this.isLink.id,
                        fields: {
                            listing_header_wi: this.isLink.listing_header_wi,
                        },
                    }).finally(() => {
                        this.$root.sm_msg_type = 0;
                    });
                } else {
                    this.tableMeta.vert_tb_hdrwidth = Number(this.titleWidth * 100).toFixed(1);
                    this.$root.updateTable(this.tableMeta, 'vert_tb_hdrwidth');
                }
            },
        },
        mounted() {
            this.fullTableWidth = Number(this.$refs.vertTb.clientWidth);
            this.titleWidth = this.initialHeaderWidth();

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

            if (this.$root.is_dcr_page) {
                setInterval(() => {
                    this.fullTableHeight();
                }, 2000);
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
        border-spacing: 0 0;
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