<template>
    <div :style="{display: b_margin ? 'flex' : 'block'}">
        <table :style="{ width: (isFullWidth ? '100%' : '1px'), marginBottom: b_margin+'px' }" :id="tb_id">
            <!--!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!-->
            <colgroup>
                <col :style="sticky({width: widths.index_col+'px'}, -2)"/>
                <col v-if="listViewActions" :style="sticky({width: widths.favorite_col+'px'}, -1)"/>
                <col v-for="(hdr,i) in showMetaFields" :style="sticky({width: tdCellWidth(hdr)+'px'}, i)"/>
                <col v-if="inArray(behavior, actionArray)" :style="{width: widths.action_col+'px'}"/>
            </colgroup>
            <!--Headers-->
            <thead class="table-head" :style="{zIndex: zi.head}">

            <!--Adding row in headers-->
            <tr v-if="addingRow.active && addingRow.position === 'top'" class="adding_row">
                <td :style="sticky({width: widths.index_col+'px'}, -2)"></td>
                <td v-if="listViewActions" class="_no_png" :style="sticky({width: widths.favorite_col+'px'}, -1)"></td>

                <template v-for="(tableHeader, i) in showMetaFields">
                    <td :is="cell_component_name"
                        :global-meta="globalMeta"
                        :table-meta="tableMeta"
                        :settings-meta="settingsMeta"
                        :rows-count="rowsCount"
                        :ref_tb_from_refcond="ref_tb_from_refcond"
                        :table-row="objectForAdd"
                        :table-header="tableHeader"
                        :cell-value="objectForAdd[tableHeader.field]"
                        :cell-height="cellHeight"
                        :max-cell-rows="maxCellRows"
                        :behavior="behavior"
                        :selected-cell="selectedCell"
                        :is-selected="selectedCell.is_selected(tableMeta, tableHeader, -1)"
                        :user="user"
                        :is-add-row="true"
                        :no_width="no_width"
                        :parent-row="parentRow"
                        :link_popup_conditions="link_popup_conditions"
                        :use_theme="use_theme"
                        :is_visible="is_visible"

                        :style="sticky({width: tdCellWidth(tableHeader)+'px'}, i)"
                        @updated-cell="checkRowAutocomplete"
                        @show-src-record="showSrcRecord"
                        @show-add-ddl-option="showAddDDLOption"
                    ></td>
                </template>

                <td v-if="inArray(behavior, actionArray)"
                    class="centered-cell"
                    :width="widths.action_col"
                    :style="{width: widths.action_col+'px'}">
                    <button class="btn btn-primary btn-sm blue-gradient add-btn"
                            :style="addBtnStyle"
                            :disabled="!with_edit"
                            @click="addRow()"
                    >Add</button>
                </td>
            </tr>

            <!--Multiheader layout fix (to fix columns width in system tables)-->
            <tr v-if="maxRowsInHeader > 1" class="multiheader-fix">
                <th :style="sticky(getThSysWidth(widths.index_col), -2, true)"></th>
                <th v-if="inArray(behavior, ['list_view', 'favorite']) && user.id"
                    :class="{'action-cell': true, 'sm-font': tdCellHGT < 30}"
                    class="_no_png"
                    :style="sticky(getThSysWidth(widths.favorite_col), -1, true)"
                ></th>

                <th v-for="(tableHeader,i) in showMetaFields"
                    class="centered-cell"
                    :style="sticky(getThStyle(tableHeader, null, 1), i, true)"
                ></th>

                <th v-if="inArray(behavior, actionArray)" :style="getThSysWidth(widths.action_col)"></th>
            </tr>

            <!--Main Headers-->
            <tr v-for="curHeaderRow in maxRowsInHeader">
                <th :rowspan="maxRowsInHeader+1"
                    v-if="curHeaderRow === 1"
                    ref="idx_hdr"
                    :style="sticky(getThSysWidth(widths.index_col), -2, true)"
                >#</th>
                <th class="_no_png"
                    :rowspan="maxRowsInHeader+1"
                    v-if="curHeaderRow === 1 && listViewActions"
                    :style="sticky(getThSysWidth(widths.favorite_col), -1, true)"
                >
                    <i class="fa fa-info-circle"></i>
                    <div v-if="behavior === 'list_view' || behavior === 'favorite'" class="header_checks">
                        <template v-if="notForbidden('i_favorite')">
                            <a @click.prevent="toggleAllFavorites()" title="Toggle Selected Rows">
                                <i class="glyphicon"
                                   title="All to Favorite"
                                   :class="[selectedRowsAreFavorites() ? 'glyphicon-star': 'glyphicon-star-empty']"
                                   :style="{color: selectedRowsAreFavorites() ? $root.themeButtonBgColor : '#FD0'}"></i>
                            </a>
                            |
                        </template>
                        <template v-if="notForbidden('i_remove') && canDelete">
                            <a @click="emitDeleteSelected()" title="Delete Selected Rows">
                                <i title="Delete All" class="glyphicon glyphicon-remove"></i>
                            </a>
                            |
                        </template>
                        <template v-if="notForbidden('i_check')" title="Select Rows">
                            <span title="Check All" class="indeterm_check__wrap">
                                <span class="indeterm_check" @click="toggleSelectedRows();$emit('row-selected');">
                                    <i v-if="hasSelectedRows() >= allRows.length" class="glyphicon glyphicon-ok group__icon"></i>
                                    <i v-else-if="hasSelectedRows()" class="glyphicon glyphicon-minus group__icon"></i>
                                </span>
                            </span>
                            <div v-if="hasSelectedRows()">({{ hasSelectedRows() }})</div>
                        </template>
                    </div>
                </th>

                <th v-for="(tableHeader, index) in showMetaFields"
                    v-if="getMultiColspan(index, curHeaderRow, showMetaFields)
                        && getMultiRowspan(tableHeader, curHeaderRow)"
                    :rowspan="getMultiRowspan(tableHeader, curHeaderRow)"
                    :colspan="getMultiColspan(index, curHeaderRow, showMetaFields)"
                    :class="[overHeader === tableHeader.field ? 'th-overed' : '']"
                    :style="sticky(getThStyle(tableHeader, curHeaderRow, 0, index), index, true)"
                    @mouseenter="(e) => { $root.showHoverTooltip(e, tableHeader) }"
                    @mouseleave="$root.leaveHoverTooltip"
                >
                    <div class="full-height flex flex--center" :style="textStyle">
                        <span class="head-content"
                              draggable="true"
                              @dragstart="(!tableHeader.is_floating && canDragColumn ? startChangeHeadersOrder(tableHeader, index) : null)"
                              @dragover.prevent=""
                              @dragenter="(!tableHeader.is_floating && canDragColumn ? overHeader = tableHeader.field : null)"
                              @dragend="(!tableHeader.is_floating && canDragColumn ? overHeader = null : null)"
                              @drop="(!tableHeader.is_floating && canDragColumn ? endChangeHeadersOrder(tableHeader, index) : null)"
                        >
                            {{ getMultiName(tableHeader, curHeaderRow) }}

                            <span v-if="headerHasCheckBox(tableHeader) && lastRow(tableHeader, curHeaderRow)" class="indeterm_check__wrap">
                                <span class="indeterm_check" @click="checkClicked(tableHeader.field, !allCellsChecked(tableHeader.field), allRows)">
                                    <i v-if="allCellsChecked(tableHeader.field)" class="glyphicon glyphicon-ok group__icon"></i>
                                </span>
                            </span>
                        </span>

                        <template v-if="tableHeader.f_required && lastRow(tableHeader, curHeaderRow)">
                            <span class="required-wildcart">*</span>
                        </template>

                        <template v-if="behavior === 'list_view' && lastRow(tableHeader, curHeaderRow)">
                            <span>{{ getSortLvl(tableHeader) }}</span>
                            <header-menu-elem :table-header="tableHeader"
                                              :selected-col="index"
                                              :is-owner="user.id === tableMeta.user_id"
                                              @field-sort-asc="sortByField(tableHeader, 'asc')"
                                              @field-sort-desc="sortByField(tableHeader, 'desc')"
                                              @show-header-settings="showHeaderSettings"
                            ></header-menu-elem>
                        </template>
                    </div>

                    <header-resizer :table-header="tableHeader"
                                    :init_width="tdCellWidth(tableHeader)"
                                    :user="user"
                    ></header-resizer>
                </th>

                <th :rowspan="maxRowsInHeader+1"
                    v-if="curHeaderRow === 1 && inArray(behavior, actionArray)"
                    :style="getThSysWidth(widths.action_col)"
                >Actions</th>
            </tr>

            <!--Unit row in headers-->
            <tr>
                <th v-for="(tableHeader, index) in showMetaFields"
                    :key="tableHeader.id"
                    v-if="(tableHeader.unit || tableHeader.unit_display)"
                    :is="'custom-head-cell-table-data'"
                    :table-meta="tableMeta"
                    :table-header="tableHeader"
                    :max-cell-rows="maxCellRows"
                    :user="user"
                    :style="sticky(getThStyle(tableHeader), index, true)"
                    @updated-cell="updatedRow"
                ></th>
            </tr>

            </thead>

            <!--!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!-->
            <!--Body-->
            <tbody class="table-body">

            <!--Present Row-->
            <tr
                v-for="(tableRow, index) in allRows"
                v-if="tableRow && !rowExcludedByValue(tableRow)"
                :style="getPresentRowStyle(tableRow, index)"
                :ref="'cttr_'+tableMeta.id+'_'+tableRow.id"
                @mouseenter="hoverRow(index)"
                @mouseleave="hoverClear()"
                @contextmenu.prevent="showRowMenu(tableRow, index)"
            >
                <td :width="widths.index_col"
                    class="content-padding centered-cell"
                    :style="sticky(presentOverStyle(tableRow), -2)"
                >
                    <div class="full-height flex flex--center" :style="borderPaddings">
                        <span
                            v-if="canDragRow"
                            class="content-name no-padding"
                            title="Click&Drag to change order"
                            :class="behavior === 'cond_format' ? 'blue-gradient' : ''"
                            :style="behavior === 'cond_format' ? $root.themeButtonStyle : {}"
                            draggable="true"
                            @dragstart="startChangeRowOrder(tableRow)"
                            @dragover.prevent=""
                            @dragenter="overRow = tableRow.id"
                            @dragend="overRow = null"
                            @drop="endChangeRowOrder(tableRow)"
                        >
                            <a @click.prevent="rowIndexClicked(index)" :style="{color: behavior === 'cond_format' ? '#FFF' : null}">
                                <span>{{ ((page-1)*(tableMeta.rows_per_page || rowsCount))+index+1 }}</span>
                                <i v-if="index === hover_row && inArray(behavior, popupArray)" class="glyphicon glyphicon-resize-full"></i>
                            </a>
                        </span>

                        <a v-else-if="inArray(behavior, linkArray)" @click.prevent="rowIndexClicked(index)">
                            <span>{{ ((page-1)*(tableMeta.rows_per_page || rowsCount))+index+1 }}</span>
                            <i v-if="index === hover_row && inArray(behavior, popupArray)" class="glyphicon glyphicon-resize-full"></i>
                        </a>

                        <span v-else="">{{ ((page-1)*(tableMeta.rows_per_page || rowsCount))+index+1 }}</span>

                    </div>
                </td>

                <td v-if="listViewActions"
                    :class="{'action-cell': true, 'sm-font': tdCellHGT < 30}"
                    class="_no_png"
                    :width="widths.favorite_col"
                    :style="sticky(textStyle, -1)">
                    <template v-if="(behavior === 'request_view' && !tableRow.id)">
                        <a @click="deleteRow(tableRow, index)"><i class="glyphicon glyphicon-remove"></i></a>
                    </template>
                    <div v-if="inArray(behavior, ['list_view','favorite'])">
                        <template v-if="notForbidden('i_favorite')">
                            <a @click.prevent="toggleFavoriteRow(tableRow)">
                                <i class="glyphicon"
                                   title="Add to Favorite"
                                   :class="[tableRow._is_favorite ? 'glyphicon-star': 'glyphicon-star-empty']"
                                   :style="{color: tableRow._is_favorite ? $root.themeButtonBgColor : '#FD0'}"></i>
                            </a>
                            |
                        </template>
                        <template v-if="notForbidden('i_remove') && canDelete">
                            <a v-if="canDeleteRow(tableRow)" @click="deleteRow(tableRow, index)">
                                <i title="Delete" class="glyphicon glyphicon-remove"></i>
                            </a>
                            <i v-else="" title="Not Allowed" class="glyphicon glyphicon-remove gl-gray"></i>
                            |
                        </template>
                        <template v-if="notForbidden('i_check')">
                            <span title="Check Row" class="indeterm_check__wrap">
                                <span class="indeterm_check" @click="ifNeedMassCheck(tableRow);">
                                    <i v-if="tableRow._checked_row" class="glyphicon glyphicon-ok group__icon"></i>
                                </span>
                            </span>
                        </template>
                    </div>
                </td>

                <template v-for="(tableHeader,i) in showMetaFields">
                    <!--Name Column on the 'settings/display' is draggable vertically-->
                    <td v-if="behavior === 'settings_display' && tableHeader.field === 'name'"
                        :is="'settings-display-name-cell'"
                        :class="[overRow === tableRow.field ? 'td-overed' : '']"
                        :table-row="tableRow"
                        :table-header="tableHeader"
                        :cell-height="cellHeight"
                        :max-cell-rows="maxCellRows"

                        :style="sticky({width: tdCellWidth(tableHeader)+'px'}, i)"
                        @drag-start="(canDragColumn ? startChangeHeadersOrder(tableRow, index) : null)"
                        @drag-enter="(canDragColumn ? overRow = tableRow.field : null)"
                        @drag-end="(canDragColumn ? overRow = null : null)"
                        @drop-event="(canDragColumn ? endChangeHeadersOrder(tableRow, index, 1) : null)"
                    ></td>
                    <!-- ^^^^^^^^^^^^^ -->
                    <td v-else=""
                        :is="cell_component_name"
                        :global-meta="globalMeta"
                        :table-meta="tableMeta"
                        :row-index="index"
                        :rows-count="rowsCount"
                        :ref_tb_from_refcond="ref_tb_from_refcond"
                        :table_id="table_id"
                        :table-row="tableRow"
                        :table-header="tableHeader"
                        :cell-value="tableRow[tableHeader.field]"
                        :cell-height="cellHeight"
                        :max-cell-rows="maxCellRows"
                        :selected-cell="selectedCell"
                        :is-selected="selectedCell.is_selected(tableMeta, tableHeader, index)"
                        :behavior="behavior"
                        :user="user"
                        :condition-array="conditionArray"
                        :with_edit="with_edit"
                        :is-add-row="false"
                        :no_width="no_width"
                        :parent-row="parentRow"
                        :link_popup_conditions="link_popup_conditions"
                        :use_theme="use_theme"
                        :is_visible="is_visible"

                        :style="sticky({width: tdCellWidth(tableHeader)+'px'}, i)"
                        @updated-cell="updatedRow"
                        @check-clicked="checkClicked"
                        @show-src-record="showSrcRecord"
                        @radio-checked="radioChecked"
                        @show-add-ddl-option="showAddDDLOption"
                        @show-def-val-popup="showDefValPopup"
                        @show-add-ref-cond="showAddRefCond"
                    ></td>
                </template>

                <td v-if="inArray(behavior, actionArray)"
                    class="centered-cell"
                    :width="widths.action_col"
                    :style="textStyle">
                    <button v-if="inArray(behavior, actionDelArray)
                                && (!tableRow.is_system || inArray(behavior, actionDelSystem))
                                && (behavior != 'cond_format' || tableRow.user_id == user.id)
                                && !delRestricted"
                            class="blue-gradient"
                            :style="(use_theme ? $root.themeButtonStyle : null)"
                            :disabled="!with_edit"
                            @click="deleteRow(tableRow, index)"
                    >
                        <i class="glyphicon glyphicon-trash"></i>
                    </button>
                    <template v-if="inArray(behavior, ['invite_module']) && tableRow.status != 2">
                        | <button :style="(use_theme ? $root.themeButtonStyle : null)" @click="$emit('resend-action', tableRow)">Resend</button>
                    </template>
                </td>
            </tr>

            <!--New Row-->
            <tr v-if="addingRow.active && addingRow.position === 'bottom'">
                <td :style="sticky({width: widths.index_col+'px'}, -2)"></td>
                <td v-if="listViewActions"
                    class="_no_png"
                    :class="{'action-cell': true, 'sm-font': tdCellHGT < 30}"
                    :style="sticky({width: widths.favorite_col+'px'}, -1)"
                ></td>

                <template v-for="(tableHeader, i) in showMetaFields">
                    <td :is="cell_component_name"
                        :global-meta="globalMeta"
                        :table-meta="tableMeta"
                        :settings-meta="settingsMeta"
                        :rows-count="rowsCount"
                        :ref_tb_from_refcond="ref_tb_from_refcond"
                        :table-row="objectForAdd"
                        :table-header="tableHeader"
                        :cell-value="objectForAdd[tableHeader.field]"
                        :cell-height="cellHeight"
                        :max-cell-rows="maxCellRows"
                        :behavior="behavior"
                        :user="user"
                        :is-add-row="true"
                        :no_width="no_width"
                        :with_edit="with_edit"
                        :parent-row="parentRow"
                        :link_popup_conditions="link_popup_conditions"
                        :use_theme="use_theme"
                        :is_visible="is_visible"

                        :style="sticky({width: tdCellWidth(tableHeader)+'px'}, i)"
                        @show-add-ddl-option="showAddDDLOption"
                        @show-src-record="showSrcRecord"
                    ></td>
                </template>

                <td v-if="inArray(behavior, actionArray)"
                    class="centered-cell"
                    :width="widths.action_col"
                    :style="textStyle">
                    <button class="btn btn-primary btn-sm blue-gradient add-btn"
                            :style="addBtnStyle"
                            :disabled="!with_edit"
                            @click="addRow()"
                    >Add</button>
                </td>
            </tr>

            </tbody>
        </table>
        <div v-if="r_margin" :style="{width: r_margin+'px', flexShrink: 0}"></div>

        <div v-if="row_menu_show && row_menu.row && row_menu.idx > -1 && behavior == 'list_view'"
             ref="row_menu"
             class="float-rom-menu"
             :style="rowMenuStyle"
        >
            <button class="btn btn-default blue-gradient"
                    style="padding: 0 3px;"
                    :style="$root.themeButtonStyle"
                    @click="rowIndexClicked(row_menu.idx);row_menu_show = false;"
            >Popup</button>
            <br>
            <button class="btn btn-default blue-gradient"
                    style="padding: 0 3px;"
                    :style="$root.themeButtonStyle"
                    :disabled="!canAdd"
                    @click="rowInsertPop(row_menu.row, -1);row_menu_show = false;"
            >Insert Above</button>
            <br>
            <button class="btn btn-default blue-gradient"
                    style="padding: 0 3px;"
                    :style="$root.themeButtonStyle"
                    :disabled="!canAdd"
                    @click="rowInsertPop(row_menu.row, 0);row_menu_show = false;"
            >Insert Below</button>
            <br>
            <button class="btn btn-default blue-gradient"
                    style="padding: 0 3px;"
                    :style="$root.themeButtonStyle"
                    :disabled="!canAdd"
                    @click="rowInsertPop(row_menu.row, 0, 'copy');row_menu_show = false;"
            >Duplicate</button>
            <br>
            <button class="btn btn-default blue-gradient"
                    style="padding: 0 3px;"
                    :style="$root.themeButtonStyle"
                    :disabled="!row_menu.can_del"
                    @click="deleteRow(row_menu.row, row_menu.idx);row_menu_show = false;"
            >Delete</button>
        </div>
    </div>
</template>

<script>
    import {SelectedCells} from '../../classes/SelectedCells';

    import {eventBus} from '../../app';

    import CustomHeadCellTableData from '../CustomCell/CustomHeadCellTableData.vue';
    import CustomCellTableData from '../CustomCell/CustomCellTableData.vue';
    import CustomCellSystemTableData from '../CustomCell/CustomCellSystemTableData.vue';
    import CustomCellCorrespTableData from '../CustomCell/CustomCellCorrespTableData.vue';
    import CustomCellSettingsDisplay from '../CustomCell/CustomCellSettingsDisplay.vue';
    import CustomCellDisplayLinks from '../CustomCell/CustomCellDisplayLinks.vue';
    import CustomCellSettingsDdl from '../CustomCell/CustomCellSettingsDdl.vue';
    import CustomCellSettingsPermission from '../CustomCell/CustomCellSettingsPermission.vue';
    import CustomCellColRowGroup from '../CustomCell/CustomCellColRowGroup.vue';
    import CustomCellKanbanSett from '../CustomCell/CustomCellKanbanSett.vue';
    import CustomCellRefConds from '../CustomCell/CustomCellRefConds.vue';
    import CustomCellCondFormat from '../CustomCell/CustomCellCondFormat.vue';
    import CustomCellAlertNotif from '../CustomCell/CustomCellAlertNotif.vue';
    import CustomCellPlans from '../CustomCell/CustomCellPlans.vue';
    import CustomCellConnection from '../CustomCell/CustomCellConnection.vue';
    import CustomCellUserGroups from '../CustomCell/CustomCellUserGroups.vue';
    import CustomCellInvitations from '../CustomCell/CustomCellInvitations.vue';
    import CustomCellFolderView from '../CustomCell/CustomCellFolderView.vue';
    import CustomCellTableView from '../CustomCell/CustomCellTableView.vue';
    import CustomCellStimAppView from '../CustomCell/CustomCellStimAppView.vue';
    import SettingsDisplayNameCell from '../CustomCell/SettingsDisplayNameCell.vue';
    import HeaderMenuElem from './Header/HeaderMenuElem.vue';
    import HeaderResizer from './Header/HeaderResizer.vue';

    import IsShowFieldMixin from '../_Mixins/IsShowFieldMixin.vue';
    import TestRowColMixin from '../_Mixins/TestRowColMixin.vue';
    import CanEditMixin from '../_Mixins/CanViewEditMixin.vue';
    import CheckRowBackendMixin from '../_Mixins/CheckRowBackendMixin.vue';
    import CellStyleMixin from '../_Mixins/CellStyleMixin.vue';
    import HeaderRowColSpanMixin from '../_Mixins/HeaderRowColSpanMixin.vue';

    export default {
        name: "StickyTableComponent",
        mixins: [
            IsShowFieldMixin,
            TestRowColMixin,
            CanEditMixin,
            CheckRowBackendMixin,
            CellStyleMixin,
            HeaderRowColSpanMixin,
        ],
        components: {
            CustomCellTableData,
            CustomCellSystemTableData,
            CustomCellCorrespTableData,
            CustomCellSettingsDisplay,
            CustomCellDisplayLinks,
            CustomCellSettingsDdl,
            CustomCellSettingsPermission,
            CustomHeadCellTableData,
            CustomCellColRowGroup,
            CustomCellKanbanSett,
            CustomCellRefConds,
            CustomCellCondFormat,
            CustomCellAlertNotif,
            CustomCellPlans,
            CustomCellConnection,
            CustomCellUserGroups,
            CustomCellInvitations,
            CustomCellFolderView,
            CustomCellTableView,
            CustomCellStimAppView,
            SettingsDisplayNameCell,
            HeaderMenuElem,
            HeaderResizer,
        },
        data: function () {
            return {
                r_margin: 50,
                b_margin: 50,
                zi: {
                    f_hdr: 200,
                    head: 150,
                    f_data: 100,
                },
                row_menu: {
                    row: null,
                    idx: -1,
                    can_del: false,
                },
                row_menu_show: false,
                row_menu_left: 0,
                row_menu_top: 0,

                hover_row: null,
                draggedRow: null,
                draggedIndex: null,
                draggedHeader: null,
                overHeader: null,
                overRow: null,
            }
        },
        props:{
            tb_id: String,
            globalMeta: Object,
            tableMeta: Object,
            settingsMeta: Object,
            allRows: Object|null,
            user: Object,
            isFullWidth: Boolean,
            cellHeight: Number,
            maxCellRows: Number,
            page: Number,
            rowsCount: Number,
            cell_component_name: String,
            behavior: String,
            sort: Array,
            forbiddenColumns: Array, // for IsShowFieldMixin.vue
            availableColumns: Array, // for IsShowFieldMixin.vue
            addingRow: Object,
            selectedRow: Number,
            conditionArray: Array|null,
            headersWithCheck: Array,
            ref_tb_from_refcond: Object|null,
            with_edit: Boolean,
            table_id: Number,
            excluded_row_values: Array,
            delRestricted: Boolean,
            parentRow: Object,
            headersChanger: Object,
            link_popup_conditions: Object|Array,
            use_theme: Boolean,
            no_width: Boolean,
            is_visible: Boolean,


            //systems
            widths: Object,
            listViewActions: Boolean,
            maxRowsInHeader: Number,
            selectedCell: SelectedCells,
            objectForAdd: Object,
        },
        computed: {
            canDragRow() {
                let avail = this.$root.checkAvailable(this.user,'drag_rows');
                let has_right = in_array(this.behavior, ['list_view','cond_format'])
                    && (
                        this.globalMeta._is_owner
                        ||
                        (this.globalMeta._current_right && this.globalMeta._current_right.can_drag_rows)
                    );

                return avail
                    && has_right
                    && !this.sort.length;
            },
            canDragColumn() {
                return this.globalMeta.is_system
                    || this.globalMeta._is_owner
                    || (this.globalMeta._current_right && this.globalMeta._current_right.can_drag_columns);
            },
            addBtnStyle() {
                let style = _.cloneDeep(this.textStyle);
                style.maxHeight = this.tdCellHGT+'px';
                if (this.use_theme) {
                    Object.assign(style, this.$root.themeButtonStyle);
                }
                return style;
            },
            rowMenuStyle() {
                return {
                    top: this.row_menu_top+'px',
                    left: this.row_menu_left+'px',
                };
            },
            showMetaFields() {
                let showed = _.filter(this.tableMeta._fields, (hdr) => {
                    return this.isShowFieldElem(hdr);
                });
                return _.orderBy(showed, ['is_floating'], ['desc']);
            },
            hasFloating() {
                return _.findIndex(this.showMetaFields, {is_floating: 1}) > -1;
            },
            lastFloating() {
                return _.findLastIndex(this.showMetaFields, {is_floating: 1});
            },
        },
        methods: {
            showRowMenu(tableRow, index) {
                this.row_menu_show = true;
                this.row_menu_left = window.event.clientX;
                this.row_menu_top = window.event.clientY;
                this.row_menu.row = tableRow;
                this.row_menu.idx = index;
                this.row_menu.can_del = this.canDeleteRow(tableRow);
            },
            //hover row
            hoverRow(idx) {
                this.hover_row = idx;
            },
            hoverClear() {
                this.hover_row = null;
            },
            //check rows
            ifNeedMassCheck(tableRow) {
                tableRow._checked_row = !tableRow._checked_row;
                if (window.event.ctrlKey) {
                    let cur_value = tableRow._checked_row;
                    let start = false;
                    let end = false;
                    _.each(this.allRows, (row) => {
                        end = end || tableRow.id == row.id;
                        if (end) {
                            return;
                        }

                        if (!start) {
                            start = cur_value == !!row._checked_row;
                        } else {
                            row._checked_row = cur_value;
                        }
                    });
                }
                this.$emit('row-selected');
            },

            selectedRowsAreFavorites() {
                let checked = _.filter(this.allRows, (row) => {
                    return row._checked_row;
                });

                if (checked.length === this.$root.listTableRows.length) {
                    return this.$root.tableMeta._view_rows_count === this.$root.tableMeta._fav_rows_count;
                } else {
                    return checked.length && !_.find(checked, (row) => {
                            return !row._is_favorite;
                        });
                }
            },
            hasSelectedRows() {
                let checked = _.filter(this.allRows, (row) => {
                   return row && row._checked_row;
                });

                if (checked.length === this.$root.listTableRows.length) {
                    return this.$root.tableMeta._view_rows_count;
                } else {
                    return checked.length;
                }
            },

            //row showing methods
            getPresentRowStyle(tableRow, index) {
                let res = {};
                //stim preselect
                let stim = _.find(this.$root.tablda_highlights, (el) => {
                    return this.tableMeta.id == el.table_id && tableRow.id == el.row_id;
                });
                if (stim) {
                    res.border = '2px solid #44F';
                }
                //data set select
                if (this.selectedRow === index || this.behavior === 'data_sets_columns') {
                    let present = !this.conditionArray || _.findIndex(this.conditionArray, {id: Number(tableRow.id)}) > -1;
                    res.backgroundColor = present ? '#FFD' : '';
                }
                else //ListView is new
                if (tableRow._is_new && this.inArray(this.behavior, ['list_view', 'request_view'])) {
                    res.backgroundColor = '#DFD';
                }
                else //CondFormats
                if (this.tableMeta._cond_formats && this.tableMeta._cond_formats.length) {

                    _.each(this.tableMeta._cond_formats, (format) => {
                        if (
                            format.status == 1 //check that Format is Active
                            &&
                            this.testRow(tableRow, format.id) //check saved result that current row is active for format
                            &&
                            !format.table_column_group_id
                        ) {
                            res.backgroundColor = format.bkgd_color || null;
                            res.color = format.color || null;
                            if (format.font_size) {
                                res.fontSize = format.font_size + 'px';
                                res.lineHeight = (Number(format.font_size) + 2) + 'px';
                            }
                            let fonts = this.$root.parseMsel(format.font);
                            _.each(fonts, (f) => {
                                (f === 'Italic' ? res.fontStyle = 'italic' : res.fontStyle = res.fontStyle || null);
                                (f === 'Bold' ? res.fontWeight = 'bold' : res.fontWeight = res.fontWeight || null);
                                (f === 'Strikethrough' ? res.textDecoration = 'line-through' : res.textDecoration = res.textDecoration || null);
                                (f === 'Overline' ? res.textDecoration = 'overline' : res.textDecoration = res.textDecoration || null);
                                (f === 'Underline' ? res.textDecoration = 'underline' : res.textDecoration = res.textDecoration || null);
                            });
                            res.freezed_by_format = (res.freezed_by_format || format.activity === 'Freezed');
                            res.hidden_by_format = !format.show_table_data;
                        }
                    });
                    res.backgroundColor = (res.hidden_by_format ? '#CCC' : res.backgroundColor);

                }
                return res;
            },
            rowExcludedByValue(row) {
                let res = false;

                _.each(this.excluded_row_values, (values_object) => {
                    let fld = values_object.field;
                    res = res || _.indexOf(values_object.excluded, row[fld]) > -1;
                });

                //exclude global hidden Columns from showing them in 'settings display'
                if (this.behavior === 'settings_display' && row._permis_hidden) {
                    res = true;
                }

                return res;
            },

            //additional functions
            headerHasCheckBox(tableHeader) {
                return this.inArray(tableHeader.field, this.headersWithCheck) && this.allRows;
            },
            checkClicked(type, status, arr) {
                let ids = arr.filter((el) => {
                    return !this.inArray(el.code, ['q_tables', 'row_table']);
                });
                ids = _.map(ids, 'id');

                this.$emit('check-row', type, status, ids);
            },
            allCellsChecked(field) {
                let unchecked = -1;

                if (this.behavior === 'data_sets_columns' && field === 'checked') {
                    unchecked = _.findIndex(this.allRows, (el) => {
                        return !(
                            this.conditionArray
                            &&
                            _.findIndex(this.conditionArray, {id: Number(el.id)}) > -1
                        );
                    });
                } else {
                    unchecked = _.findIndex(this.allRows, (el) => {
                        return !this.inArray(el.code, ['q_tables', 'row_table']) && !el[field];
                    });
                }

                return unchecked === -1;
            },

            //rows changing
            showAddDDLOption(tableHeader, tableRow) {
                this.$emit('show-add-ddl-option', tableHeader, tableRow);
            },
            rowInsertPop(tableRow, dir, copy) {
                this.$emit('insert-pop-row', to_float(tableRow.row_order + dir), copy ? tableRow : null);
            },
            addRow() {
                this.$emit('added-row');
            },
            updatedRow(params, hdr) {
                this.$emit('updated-row', params, hdr);
            },
            deleteRow(tableRow, index) {
                this.$emit('delete-row', tableRow, index);
            },
            rowIndexClicked(index) {
                this.$emit('row-index-clicked', index);
            },
            radioChecked(index) {
                this.$emit('radio-checked', index);
            },
            showHeaderSettings(header) {
                this.$emit('show-header-settings', header);
            },

            //mass rows actions
            emitDeleteSelected() {
                if (this.hasSelectedRows()) {
                    this.$emit('delete-selected-rows');
                } else {
                    Swal('No record has been selected.');
                }
            },
            toggleSelectedRows() {
                let status = !this.hasSelectedRows();
                _.each(this.allRows, (row) => {
                    this.$set(row, '_checked_row', status);
                });
            },
            toggleAllFavorites() {
                if (this.hasSelectedRows()) {
                    this.$emit('toggle-all-favorites', this.selectedRowsAreFavorites() ? 0 : 1);
                } else {
                    Swal('No record has been selected.');
                }
            },

            //sorting
            sortByField(tableHeader, $dir) {
                let spec_key = window.event.ctrlKey || window.event.altKey || window.event.shiftKey;
                this.$emit(spec_key ? 'sub-sort-by-field' : 'sort-by-field', tableHeader, $dir);
            },
            getSortLvl(tableHeader) {
                let idx = _.findIndex(this.sort, {field: tableHeader.field});
                return idx > -1 ? idx+1 : '';
            },

            //change favorite rows
            toggleFavoriteRow(tableRow) {
                this.$emit('toggle-favorite-row', tableRow);
            },

            //change order of the headers
            startChangeHeadersOrder(tableHeader, index) {
                this.draggedHeader = tableHeader;
                this.draggedIndex = index;
            },
            endChangeHeadersOrder(tableHeader, index, isGlobal) {
                if (this.draggedHeader && this.draggedHeader.order !== tableHeader.order) {

                    let h1 = this.showMetaFields[this.draggedIndex];
                    let h2 = this.showMetaFields[index];
                    if (h1 && h2 && !isGlobal) {
                        let ii1 = _.findIndex(this.tableMeta._fields, {field: h1.field});
                        let ii2 = _.findIndex(this.tableMeta._fields, {field: h2.field});
                        //put dragged header before target header
                        let dragged = this.tableMeta._fields.splice(ii1, 1)[0];
                        this.tableMeta._fields.splice((ii1 > ii2 ? ii2 : ii2 - 1), 0, dragged);
                    }

                    if (this.user.id && !this.user.view_hash && !this.user.selected_view) {
                        axios.put('/ajax/settings/change-order-column', {
                            table_id: (isGlobal ? this.globalMeta.id : this.tableMeta.id),
                            select_user_header_id: this.draggedHeader.user_header_id,
                            target_user_header_id: tableHeader.user_header_id
                        }).then(({ data }) => {
                            if (isGlobal && data._fields) {
                                this.globalMeta._fields = data._fields;
                            }
                        }).catch(errors => {
                            Swal('', getErrors(errors));
                        }).finally(() => {
                            this.draggedHeader = null;
                            this.draggedIndex = null;
                        });
                    }
                }
            },
            //change order of the rows
            startChangeRowOrder(tableRow) {
                this.draggedRow = tableRow;
            },
            endChangeRowOrder(tableRow) {
                if (this.draggedRow) {
                    if (this.user.id && !this.user.view_hash && !this.user.selected_view) {
                        axios.put('/ajax/settings/change-row-order', {
                            table_id: this.tableMeta.id,
                            from_order: this.draggedRow.row_order,
                            to_order: tableRow.row_order,
                            from_id: this.draggedRow.id,
                            to_id: tableRow.id,
                        }).then(({ data }) => {
                            this.$emit('reorder-rows');
                        }).catch(errors => {
                            Swal('', getErrors(errors));
                        }).finally(() => {
                            this.draggedRow = null;
                        });
                    }
                }
            },
            presentOverStyle(tableRow) {
                let style = _.cloneDeep(this.textStyle);
                if (this.overRow === tableRow.id) {
                    if (this.draggedRow) {
                        style.borderTop = '2px dashed #000';
                    }
                }
                return style;
            },

            //src record and tables function
            showSrcRecord(lnk, header, tableRow) {
                this.$emit('show-src-record', lnk, header, tableRow);
            },
            showAddRefCond(refId) {
                this.$emit('show-add-ref-cond', refId);
            },
            showDefValPopup(tableRow) {
                this.$emit('show-def-val-popup', tableRow);
            },


            //STICKY STYLES
            getThStyle(tableHeader, curHeaderRow, hidden, index) {
                let style = this.$root.themeTableHeaderBgStyle;
                let bkg = style.background;
                if (index && tableHeader.header_background) {
                    let colspan = this.getMultiColspan(index, curHeaderRow, this.showMetaFields) - 1;
                    let all_the_same_clr = true;
                    for (let i = 1; i <= colspan; i++) {
                        let next_hdr = this.showMetaFields[i+index];
                        all_the_same_clr = all_the_same_clr
                            && next_hdr
                            && next_hdr.header_background === tableHeader.header_background;
                    }
                    if (all_the_same_clr) {
                        bkg = this.$root.buildCssGradient(tableHeader.header_background) || style.background;
                    }
                }

                return {
                    width: this.tdCellWidth(tableHeader)+'px',
                    background: bkg,
                    lineHeight: this.textStyle.lineHeight,
                    fontSize: this.textStyle.fontSize,
                };
            },
            getThSysWidth(width) {
                let style = this.$root.themeTableHeaderBgStyle;
                return {
                    width: width+'px',
                    background: style.background,
                    lineHeight: this.textStyle.lineHeight,
                    fontSize: this.textStyle.fontSize,
                };
            },
            sticky(style, h_idx, is_header) {
                let is_flo = this.showMetaFields[h_idx] ? this.showMetaFields[h_idx].is_floating : this.hasFloating;
                if (is_flo) {
                    return {
                        ...style,
                        ...this.stickyCell(h_idx, is_header)
                    };
                } else {
                    return style;
                }
            },
            stickyCell(h_idx, is_header) {
                return {
                    position: 'sticky',
                    zIndex: (is_header ? this.zi.f_hdr : this.zi.f_data),
                    left: this.calcLeft(h_idx)+'px',
                    borderRight: h_idx === this.lastFloating ? '1px solid #222' : null,
                };
            },
            calcLeft(h_idx) {
                let left = 0;
                left += (-2 < h_idx ? this.widths.index_col : 0);
                left += (-1 < h_idx && this.listViewActions ? this.widths.favorite_col : 0);
                _.each(this.showMetaFields, (fld, i) => {
                    if (i < h_idx) {
                        left += this.tdCellWidth(fld);
                    }
                });
                return left;
            },
            //-------------------

            //backend autocomplete
            checkRowAutocomplete() {
                this.checkRowOnBackend( this.tableMeta.id, this.objectForAdd );
            },

            //keys
            keydownHandler(e) {
                if (e.keyCode == 27) {
                    this.overRow = null;
                    this.overHeader = null;
                    this.$forceUpdate();
                }
            },
            clickHandler(e) {
                if (e.button == 0 && this.row_menu_show && this.$refs.row_menu && !$(this.$refs.row_menu).has(e.target).length) {
                    this.row_menu_show = false;
                }
            },

            //drawing
            notForbidden(i_col) {
                return !this.inArray(i_col, this.forbiddenColumns);
            },

            //external scroll//
            externalScroll(table_id, row_id) {
                if (this.tableMeta.id == table_id) {
                    let refs = this.$refs['cttr_'+table_id+'_'+row_id];
                    let rrow = _.first(refs);
                    rrow ? rrow.scrollIntoView({block: 'center'}) : null;
                }
            },
        },
        mounted() {
            this.r_margin = in_array(this.behavior, ['list_view','request_view','link_popup','favorite']) ? this.r_margin : 0;//only for 'data tables'
            this.b_margin = in_array(this.behavior, ['list_view','link_popup','favorite']) ? this.b_margin : 0;//only for 'data tables'
            eventBus.$on('global-keydown', this.keydownHandler);
            eventBus.$on('global-click', this.clickHandler);
            eventBus.$on('scroll-tabldas-to-row', this.externalScroll);
        },
        beforeDestroy() {
            eventBus.$off('global-keydown', this.keydownHandler);
            eventBus.$off('global-click', this.clickHandler);
            eventBus.$off('scroll-tabldas-to-row', this.externalScroll);
        }
    }
</script>

<style lang="scss" scoped>
    @import "CustomTable";

    .indeterm_check__wrap {
        top: 2px;
        position: relative;

        .indeterm_check {
            .group__icon {
                font-size: 0.9em;
                top: 0;
            }
        }
    }

    input[type="radio"], input[type="checkbox"] {
        margin: 0;
    }

    .float-rom-menu {
        position: fixed;
        z-index: 5000;
        text-align: left;

        button {
            opacity: 0.7;

            &:hover {
                opacity: 1;
            }
        }
    }
</style>