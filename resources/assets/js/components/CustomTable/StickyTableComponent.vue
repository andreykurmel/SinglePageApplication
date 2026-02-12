<template>
    <div ref="tbWrap"
         :draw_handler="fullTableHeight()"
         :style="{
            justifyContent: primaryAlign,
            display: 'flex',
            width: isFullWidth ? 'calc(100% - 1px)' : null,
         }"
    >
        <table
            :id="tb_id"
            :style="{
                width: (isFullWidth ? '100%' : '1px'),
                marginBottom: no_b_margin ? 0 : b_margin+'px',
                borderLeft: primaryAlign !== 'start' ? '1px solid #ccc' : null,
            }"
        >

            <!--No Visible Fields-->
            <tr v-if="noVisibleLnkFlds">
                <td style="text-align: center; min-width: 600px">
                    <label>No field is turned ON for display or</label><br>
                    <label>no field is available per permission</label>
                </td>
            </tr>

            <!--!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!-->
            <colgroup v-if="!noVisibleLnkFlds && !isFullWidth">
                <col :style="sticky({width: widths.index_col+'px'}, -2)"/>
                <col v-if="listViewActions" :style="sticky({width: widths.favorite_col+'px'}, -1)"/>
                <col v-for="(hdr,i) in showMetaFields" :style="sticky({width: tdCellWidth(hdr)+'px'}, i)"/>
                <col v-if="inArray(behavior, actionArray)" :style="stickyRight({width: widths.action_col+'px'})"/>
            </colgroup>
            <!--Headers-->
            <thead v-if="!noVisibleLnkFlds" :style="{zIndex: zi.head}" class="table-head">

            <!--Adding row in headers-->
            <tr v-if="addingRow.active && addingRow.position === 'top'" class="adding_row">
                <td :style="sticky({width: widths.index_col+'px'}, -2)"></td>
                <td v-if="listViewActions" :style="sticky({width: widths.favorite_col+'px'}, -1)"
                    class="_no_png action-cell">
                    <a v-if="canAdd" :class="{'disabled': !with_edit}" title="Add Row" @click="addRow()">
                        <i class="glyphicon glyphicon-plus hover-red" title="Add Row"></i>
                    </a>
                </td>

                <template v-for="(tableHeader, i) in showMetaFields">
                    <td :is="cell_component_name"
                        :all-rows="rowsForSelected"
                        :behavior="behavior"
                        :cell-height="cellHeight"
                        :cell-value="objectForAdd[tableHeader.field]"
                        :foreign-special="foreignSpecial"
                        :global-meta="globalMeta"
                        :is-add-row="true"
                        :is-link="isLink"
                        :is-selected-ext="selectedCell.is_selected(tableMeta, tableHeader, rowsCount || -1)"
                        :is_visible="is_visible"
                        :link_popup_conditions="link_popup_conditions"
                        :max-cell-rows="maxCellRows"
                        :no_height_limit="no_height_limit"
                        :no_width="no_width"
                        :parent-row="parentRow"
                        :ref_tb_from_refcond="ref_tb_from_refcond"
                        :row-index="rowsCount || -1"
                        :rows-count="rowsCount"
                        :selected-cell="selectedCell"
                        :settings-meta="settingsMeta"
                        :special_extras="special_extras"
                        :style="sticky({width: tdCellWidth(tableHeader)+'px'}, i)"
                        :table-header="tableHeader"
                        :table-meta="tableMeta"
                        :table-row="objectForAdd"
                        :use_theme="use_theme"

                        :user="user"
                        @updated-cell="addCellUpdated(tableHeader)"
                        @show-src-record="showSrcRecord"
                        @show-add-ddl-option="showAddDDLOption"
                    ></td>
                </template>

                <td v-if="inArray(behavior, actionArray)"
                    :style="stickyRight({width: widths.action_col+'px'})"
                    :width="widths.action_col"
                    class="centered-cell">
                    <button :disabled="!with_edit"
                            :class="{'disabled': !with_edit}"
                            :style="addBtnStyle"
                            class="btn btn-primary btn-sm blue-gradient add-btn"
                            @click="addRow()"
                    >Add
                    </button>
                </td>
            </tr>

            <!--Multiheader layout fix (to fix columns width in system tables)-->
            <tr v-if="maxRowsInHeader > 1" class="multiheader-fix">
                <th :style="sticky(getThSysWidth(widths.index_col), -2, true)"></th>
                <th v-if="listViewActions"
                    :class="{'action-cell': true, 'sm-font': tdCellHGT < 30}"
                    :style="sticky(getThSysWidth(widths.favorite_col), -1, true)"
                    class="_no_png"
                ></th>

                <th v-for="(tableHeader,i) in showMetaFields"
                    :style="sticky(getThStyle(tableHeader, null, 1), i, true)"
                    class="centered-cell"
                ></th>

                <th v-if="inArray(behavior, actionArray)" :style="stickyRight(getThSysWidth(widths.action_col))"></th>
            </tr>

            <!--Main Headers-->
            <tr v-for="curHeaderRow in maxRowsInHeader">
                <th v-if="curHeaderRow === 1"
                    ref="idx_hdr"
                    :rowspan="maxRowsInHeader+1"
                    :style="sticky(getThSysWidth(widths.index_col), -2, true)"
                >#
                </th>
                <th v-if="curHeaderRow === 1 && listViewActions"
                    :rowspan="maxRowsInHeader+1"
                    :style="sticky(getThSysWidth(widths.favorite_col), -1, true)"
                    class="_no_png"
                >
                    <i v-if="!hasSelectedRows()" class="fa fa-info-circle"></i>
                    <div v-if="behavior === 'list_view' || behavior === 'favorite'" class="header_checks">
                        <span v-if="notForbidden('i_srv') && canSRV(tableMeta)">&nbsp;&nbsp;&nbsp;&nbsp;</span>
                        <template v-if="notForbidden('i_favorite') && user.id">
                            <a title="Toggle Selected Rows" @click.prevent="toggleAllFavorites()">
                                <i :class="[selectedRowsAreFavorites() ? 'glyphicon-star': 'glyphicon-star-empty']"
                                   :style="{color: selectedRowsAreFavorites() ? $root.themeButtonBgColor : '#FD0'}"
                                   class="glyphicon"
                                   title="Favorite/Unfavorite Selected Items"></i>
                            </a>
                            |
                        </template>
                        <template v-if="notForbidden('i_remove') && canDelete">
                            <a title="Delete Selected Rows" :class="{'disabled': !with_edit}" @click="emitDeleteSelected()">
                                <i :class="hasSelectedRows() ? 'hover-red' : 'gray disabled'"
                                   :title="$root.all_checked_rows ? 'Delete All' : 'Delete Selected'"
                                   class="glyphicon glyphicon-remove"
                                ></i>
                            </a>
                            |
                        </template>
                        <template v-if="notForbidden('i_check')" title="Select Rows">
                            <span class="indeterm_check__wrap" title="Check All">
                                <span class="indeterm_check" @click="toggleSelectedRows();$emit('row-selected');">
                                    <i v-if="rowsForSelected && hasSelectedRows() >= rowsForSelected.length"
                                       class="glyphicon glyphicon-ok group__icon"></i>
                                    <i v-else-if="hasSelectedRows()" class="glyphicon glyphicon-minus group__icon"></i>
                                </span>
                            </span>
                            <div v-if="hasSelectedRows()">({{ hasSelectedRows() }})</div>
                        </template>
                    </div>
                </th>

                <th v-for="(tableHeader, index) in showMetaFields"
                    v-if="getMultiColspan(index, curHeaderRow)
                        && getMultiRowspan(index, curHeaderRow)"
                    :class="[overHeader === tableHeader.field ? 'th-overed' : '']"
                    :colspan="getMultiColspan(index, curHeaderRow)"
                    :rowspan="getMultiRowspan(index, curHeaderRow)"
                    :style="sticky(getThStyle(tableHeader, curHeaderRow, 0, index), index, true)"
                    @mouseenter="(e) => { $root.showHoverTooltip(e, tableHeader) }"
                    @mouseleave="$root.leaveHoverTooltip"
                >
                    <div :style="tableMeta.is_system ? textSysStyle : textStyle" class="full-height flex flex--center">
                        <span class="head-content"
                              draggable="true"
                              @dragend="(!tableHeader.is_floating && canDragColumn ? overHeader = null : null)"
                              @dragenter="(!tableHeader.is_floating && canDragColumn ? overHeader = tableHeader.field : null)"
                              @dragstart="(!tableHeader.is_floating && canDragColumn ? startChangeHeadersOrder(tableHeader, index) : null)"
                              @drop="(!tableHeader.is_floating && canDragColumn ? endChangeHeadersOrder(tableHeader, index) : null)"
                              @dragover.prevent=""
                        >
                            {{ getMultiName(tableHeader, curHeaderRow) }}

                            <template v-if="headerHasCheckBox(tableHeader) && lastRow(tableHeader, curHeaderRow)">
                                <div v-if="behavior === 'table_field_link_columns'">
                                    <button class="btn btn-primary btn-sm blue-gradient add-btn m5 white"
                                            title="Sync with GridView"
                                            @click="showHeaderSettings(tableHeader)"
                                    >Sync</button>
                                </div>
                                <label v-if="special_extras.header_check === 'slider'" class="switch_t">
                                    <input :checked="allCellsChecked(tableHeader.field)"
                                           type="checkbox"
                                           @click="checkClicked(tableHeader.field, !allCellsChecked(tableHeader.field), allRows)">
                                    <span class="toggler round"></span>
                                </label>
                                <span v-else class="indeterm_check__wrap">
                                    <span class="indeterm_check"
                                          @click="checkClicked(tableHeader.field, !allCellsChecked(tableHeader.field), allRows)">
                                        <i v-if="allCellsChecked(tableHeader.field)"
                                           class="glyphicon glyphicon-ok group__icon"></i>
                                    </span>
                                </span>
                            </template>
                        </span>

                        <template v-if="isRequired(tableHeader) && lastRow(tableHeader, curHeaderRow)">
                            <span class="required-wildcart">*</span>
                        </template>

                        <span v-if="tableHeader.input_type === 'Mirror' && lastRow(tableHeader, curHeaderRow)"
                              style="position: absolute; left: 3px; bottom: 0px;">
                            <img height="14" src="/assets/img/thumb_icon_mirror.png">
                        </span>

                        <template v-if="sortAvail(tableHeader, curHeaderRow)">
                            <header-menu-elem :is-owner="user.id === tableMeta.user_id"
                                              :only_sorting="behavior === 'link_popup'"
                                              :rotate="true"
                                              :table-header="tableHeader"
                                              :table-meta="tableMeta"
                                              style="position: absolute; right: -3px; bottom: -3px;"
                                              @field-sort-asc="sortByField(tableHeader, 'asc')"
                                              @field-sort-desc="sortByField(tableHeader, 'desc')"
                                              @show-header-settings="showHeaderSettings"
                            ></header-menu-elem>
                            <span v-if="getSortLvl(tableHeader)"
                                  style="position: absolute; top: 0px; right: 3px;">{{ getSortLvl(tableHeader) }}</span>
                        </template>
                    </div>

                    <header-resizer :init_width="tdCellWidth(tableHeader)"
                                    :table-header="tableHeader"
                                    :user="user"
                                    :all_rows="allRows"
                                    :table_meta="tableMeta"
                                    @start-resize="$emit('start-resize')"
                                    @col-resized="$emit('col-resized')"
                    ></header-resizer>
                </th>

                <th v-if="curHeaderRow === 1 && inArray(behavior, actionArray)"
                    :rowspan="maxRowsInHeader+1"
                    :style="stickyRight(getThSysWidth(widths.action_col))"
                    style="overflow: hidden;"
                >Actions
                </th>
            </tr>

            <!--Unit row in headers-->
            <tr>
                <th :is="'custom-head-cell-table-data'"
                    v-for="(tableHeader, index) in showMetaFields"
                    v-if="(tableHeader.unit || tableHeader.unit_display)"
                    :key="tableHeader.id"
                    :max-cell-rows="maxCellRows"
                    :style="sticky(getThStyle(tableHeader), index, true)"
                    :table-header="tableHeader"
                    :table-meta="tableMeta"
                    :user="user"
                    @updated-cell="updatedRow"
                ></th>
            </tr>

            </thead>

            <!--!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!-->
            <!--Body-->
            <tbody v-if="!noVisibleLnkFlds" class="table-body">

            <!--New Row-->
            <tr v-if="addingRow.active && addingRow.position === 'body_top'">
                <td :style="sticky({width: widths.index_col+'px'}, -2)" class="txt--center">
                    <a v-if="!tableMeta.is_system && with_edit" @click.prevent="rowIndexClicked(-1, objectForAdd)">
                        <i class="fa fa-plus blue" title="Open Add Row Popup"></i>
                    </a>
                    <i v-if="!tableMeta.is_system && !with_edit" class="fa fa-plus gray" title="Open Add Row Popup"></i>
                </td>
                <td v-if="listViewActions"
                    :class="{'action-cell': true, 'sm-font': tdCellHGT < 30}"
                    :style="sticky({width: widths.favorite_col+'px'}, -1)"
                    class="_no_png"
                ></td>

                <template v-for="(tableHeader, i) in showMetaFields">
                    <td :is="cell_component_name"
                        :all-rows="allRows"
                        :behavior="behavior"
                        :cell-height="cellHeight"
                        :cell-value="objectForAdd[tableHeader.field]"
                        :foreign-special="foreignSpecial"
                        :global-meta="globalMeta"
                        :is-add-row="true"
                        :is-link="isLink"
                        :is-selected-ext="selectedCell.is_selected(tableMeta, tableHeader, rowsCount || -1)"
                        :is_visible="is_visible"
                        :link_popup_conditions="link_popup_conditions"
                        :max-cell-rows="maxCellRows"
                        :no_height_limit="no_height_limit"
                        :no_width="no_width"
                        :parent-row="parentRow"
                        :ref_tb_from_refcond="ref_tb_from_refcond"
                        :row-index="rowsCount || -1"
                        :rows-count="rowsCount"
                        :selected-cell="selectedCell"
                        :settings-meta="settingsMeta"
                        :special_extras="special_extras"
                        :style="sticky({width: tdCellWidth(tableHeader)+'px'}, i)"
                        :table-header="tableHeader"
                        :table-meta="tableMeta"
                        :table-row="objectForAdd"
                        :use_theme="use_theme"
                        :user="user"

                        :with_edit="with_edit"
                        @updated-cell="addCellUpdated(tableHeader)"
                        @show-add-ddl-option="showAddDDLOption"
                        @show-src-record="showSrcRecord"
                    ></td>
                </template>

                <td v-if="inArray(behavior, actionArray)"
                    :style="stickyRight(textStyle)"
                    :width="widths.action_col"
                    class="centered-cell">
                    <button :disabled="!with_edit"
                            :class="{'disabled': !with_edit}"
                            :style="addBtnStyle"
                            class="btn btn-primary btn-sm blue-gradient add-btn"
                            @click="addRow()"
                    >Add
                    </button>
                </td>
            </tr>

            <!--Present Row-->
            <tr
                v-for="(tableRow, index) in allRows"
                v-if="tableRow && !rowExcludedByValue(tableRow)"
                :ref="'cttr_'+tableMeta.id+'_'+tableRow.id"
                :style="getPresentRowStyle(tableRow, index, tableMeta)"
                class="icon_on_hover"
            >
                <td :style="sticky(presentOverStyle(tableRow), -2)"
                    :width="widths.index_col"
                    class="content-padding centered-cell"
                >
                    <div :style="borderPaddings" class="full-height flex flex--center">
                        <span
                            v-if="canDragRow"
                            :class="inArray(behavior, btnBehaviors) ? 'blue-gradient' : ''"
                            :style="inArray(behavior, btnBehaviors) ? $root.themeButtonStyle : {}"
                            class="content-name no-padding"
                            draggable="true"
                            title="Click&Drag to change order"
                            @dragend="overRow = null"
                            @dragenter="overRow = tableRow.id"
                            @dragstart="startChangeRowOrder(tableRow)"
                            @drop="endChangeRowOrder(tableRow)"
                            @click.prevent="rowIndexClicked(index, tableRow)"
                            @dragover.prevent=""
                        >
                            <a :style="{color: inArray(behavior, btnBehaviors) ? '#FFF' : null}">
                                <span>{{ ((page - 1) * (tableMeta.rows_per_page || rowsCount)) + index + 1 }}</span>
                                <i v-if="inArray(behavior, popupArray)"
                                   class="glyphicon glyphicon-resize-full target_icon"></i>
                            </a>
                        </span>

                        <a v-else-if="inArray(behavior, linkArray)" @click.prevent="rowIndexClicked(index, tableRow)">
                            <span>{{ ((page - 1) * (tableMeta.rows_per_page || rowsCount)) + index + 1 }}</span>
                            <i v-if="inArray(behavior, popupArray)"
                               class="glyphicon glyphicon-resize-full target_icon"></i>
                        </a>

                        <span v-else="">{{ ((page - 1) * (tableMeta.rows_per_page || rowsCount)) + index + 1 }}</span>

                    </div>
                </td>

                <td v-if="listViewActions"
                    :class="{'action-cell': true, 'sm-font': tdCellHGT < 30}"
                    :style="sticky(textStyle, -1)"
                    :width="widths.favorite_col"
                    class="_no_png">
                    <template v-if="behavior === 'request_view'">
                        <a @click="deleteRow(tableRow, index)"><i class="glyphicon glyphicon-remove hover-red"></i></a>
                    </template>
                    <div v-if="inArray(behavior, ['list_view','favorite'])">
                        <template v-if="notForbidden('i_srv') && canSRV(tableMeta)">
                            <srv-block
                                :table-meta="tableMeta"
                                :table-row="tableRow"
                                :with-delimiter="true"
                                style="font-size: 16px; position: relative; top: 2px;"
                            ></srv-block>
                        </template>
                        <template v-if="notForbidden('i_favorite') && user.id">
                            <a @click.prevent="toggleFavoriteRow(tableRow)">
                                <i :class="[tableRow._is_favorite ? 'glyphicon-star': 'glyphicon-star-empty']"
                                   :style="{color: tableRow._is_favorite ? $root.themeButtonBgColor : '#FD0'}"
                                   class="glyphicon"
                                   title="Add to Favorite"></i>
                            </a>
                            |
                        </template>
                        <template v-if="notForbidden('i_remove') && canDelete">
                            <a v-if="canDeleteRow(tableRow)" :class="{'disabled': !with_edit}" @click="deleteRow(tableRow, index)">
                                <i class="glyphicon glyphicon-remove hover-red" title="Delete"></i>
                            </a>
                            <i v-else="" class="glyphicon glyphicon-remove gl-gray" title="Not Allowed"></i>
                            |
                        </template>
                        <template v-if="notForbidden('i_check')">
                            <span class="indeterm_check__wrap" title="Check Row">
                                <span class="indeterm_check" @click="ifNeedMassCheck(tableRow)">
                                    <i v-if="tableRow._checked_row" class="glyphicon glyphicon-ok group__icon"></i>
                                </span>
                            </span>
                        </template>
                    </div>
                </td>

                <template v-for="(tableHeader,i) in showMetaFields">
                    <!--Name Column on the 'settings/display' is draggable vertically-->
                    <td :is="'settings-display-name-cell'"
                        v-if="behavior === 'settings_display' && tableHeader.field === 'name'"
                        :cell-height="cellHeight"
                        :class="[overRow === tableRow.field ? 'td-overed' : '']"
                        :global-meta="globalMeta"
                        :max-cell-rows="maxCellRows"
                        :style="sticky({width: tdCellWidth(tableHeader)+'px'}, i)"
                        :table-header="tableHeader"
                        :table-meta="tableMeta"

                        :table-row="tableRow"
                        @drag-start="(canDragColumn ? startChangeHeadersOrder(tableRow, index) : null)"
                        @drag-enter="(canDragColumn ? overRow = tableRow.field : null)"
                        @drag-end="(canDragColumn ? overRow = null : null)"
                        @drop-event="(canDragColumn ? endChangeHeadersOrder(tableRow, index, 1) : null)"
                    ></td>
                    <!-- ^^^^^^^^^^^^^ -->
                    <td v-else-if="outOfViewport(tableHeader, index, i)"
                        :style="{
                            height: (tdCellHGT + 5)+'px',
                            width: $root.getFloat(tableHeader.width)+'px',
                        }"
                    ></td>
                    <td :is="cell_component_name"
                        v-else=""
                        :all-rows="allRows"
                        :behavior="behavior"
                        :cell-height="cellHeight"
                        :cell-value="tableRow[tableHeader.field]"
                        :condition-array="conditionArray"
                        :foreign-special="foreignSpecial"
                        :global-meta="globalMeta"
                        :is-add-row="false"
                        :is-link="isLink"
                        :is-selected-ext="selectedCell.is_selected(tableMeta, tableHeader, index)"
                        :is_visible="is_visible"
                        :link_popup_conditions="link_popup_conditions"
                        :max-cell-rows="maxCellRows"
                        :no_height_limit="no_height_limit"
                        :no_width="no_width"
                        :parent-row="parentRow"
                        :ref_tb_from_refcond="ref_tb_from_refcond"
                        :row-index="index"
                        :rows-count="rowsCount"
                        :selected-cell="selectedCell"
                        :special_extras="special_extras"
                        :style="sticky({width: tdCellWidth(tableHeader)+'px'}, i)"
                        :table-header="tableHeader"
                        :table-meta="tableMeta"
                        :table-row="tableRow"
                        :table_id="table_id"
                        :use_theme="use_theme"
                        :user="user"

                        :with_edit="with_edit"
                        @updated-cell="updatedRow"
                        @check-clicked="checkClicked"
                        @show-src-record="showSrcRecord"
                        @radio-checked="radioChecked"
                        @show-add-ddl-option="showAddDDLOption"
                        @show-def-val-popup="showDefValPopup"
                        @show-add-ref-cond="showAddRefCond"
                        @cell-menu="showRowMenu"
                        @call-back="callBack"
                    ></td>
                </template>

                <td v-if="inArray(behavior, actionArray)"
                    :style="stickyRight(textStyle)"
                    :width="widths.action_col"
                    class="centered-cell">
                    <template v-if="inArray(behavior, ['invite_module']) && tableRow.status != 2">
                        <button :style="(use_theme ? $root.themeButtonStyle : null)"
                                @click="$emit('resend-action', tableRow)">Resend
                        </button>
                        |
                    </template>
                    <button v-if="canTrash(tableRow)"
                            :disabled="!with_edit"
                            :class="{'disabled': !with_edit}"
                            :style="(use_theme ? $root.themeButtonStyle : null)"
                            class="blue-gradient"
                            @click="deleteRow(tableRow, index)"
                    >
                        <i class="glyphicon glyphicon-trash"></i>
                    </button>
                    <template v-if="inArray(behavior, ['twilio_keys'])">
                        |
                        <button :style="(use_theme ? $root.themeButtonStyle : null)" class="blue-gradient"
                                @click="twilioTestPop(tableRow.id)">App
                        </button>
                    </template>
                </td>
            </tr>

            <!--New Row-->
            <tr v-if="addingRow.active && addingRow.position === 'bottom'">
                <td :style="sticky({width: widths.index_col+'px'}, -2)" class="txt--center">
                    <a v-if="!tableMeta.is_system && with_edit" @click.prevent="rowIndexClicked(-1, objectForAdd)">
                        <i class="fa fa-plus blue" title="Open Add Row Popup"></i>
                    </a>
                    <i v-if="!tableMeta.is_system && !with_edit" class="fa fa-plus gray" title="Open Add Row Popup"></i>
                </td>
                <td v-if="listViewActions"
                    :class="{'action-cell': true, 'sm-font': tdCellHGT < 30}"
                    :style="sticky({width: widths.favorite_col+'px'}, -1)"
                    class="_no_png"
                ></td>

                <template v-for="(tableHeader, i) in showMetaFields">
                    <td :is="cell_component_name"
                        :all-rows="allRows"
                        :behavior="behavior"
                        :cell-height="cellHeight"
                        :cell-value="objectForAdd[tableHeader.field]"
                        :foreign-special="foreignSpecial"
                        :global-meta="globalMeta"
                        :is-add-row="true"
                        :is-link="isLink"
                        :is_visible="is_visible"
                        :link_popup_conditions="link_popup_conditions"
                        :max-cell-rows="maxCellRows"
                        :no_height_limit="no_height_limit"
                        :no_width="no_width"
                        :parent-row="parentRow"
                        :ref_tb_from_refcond="ref_tb_from_refcond"
                        :rows-count="rowsCount"
                        :settings-meta="settingsMeta"
                        :special_extras="special_extras"
                        :style="sticky({width: tdCellWidth(tableHeader)+'px'}, i)"
                        :table-header="tableHeader"
                        :table-meta="tableMeta"
                        :table-row="objectForAdd"
                        :use_theme="use_theme"
                        :user="user"

                        :with_edit="with_edit"
                        @updated-cell="addCellUpdated(tableHeader)"
                        @show-add-ddl-option="showAddDDLOption"
                        @show-src-record="showSrcRecord"
                    ></td>
                </template>

                <td v-if="inArray(behavior, actionArray)"
                    :style="stickyRight(textStyle)"
                    :width="widths.action_col"
                    class="centered-cell">
                    <button :disabled="!with_edit"
                            :class="{'disabled': !with_edit}"
                            :style="addBtnStyle"
                            class="btn btn-primary btn-sm blue-gradient add-btn"
                            @click="addRow()"
                    >Add
                    </button>
                </td>
            </tr>

            </tbody>
        </table>
        <div v-if="r_margin" :style="{width: r_margin+'px', flexShrink: 0}">
            <div v-if="tableMeta._is_owner && behavior == 'list_view'" class="flex flex--center add-col">
                <i class="fa fa-plus" @click="showAddColumn"></i>
            </div>
        </div>

        <div v-if="row_menu_show && row_menu.row && row_menu.idx > -1"
             ref="row_menu"
             :style="rowMenuStyle"
             class="float-rom-menu"
        >
            <a @click="rowIndexClicked(row_menu.idx, row_menu.row);row_menu_show = false;">Popup</a>
            <a v-if="row_menu.hdr" @click="copyCell(row_menu.row, row_menu.hdr);row_menu_show = false;">Copy Cell</a>
            <a :class="!canAdd || !with_edit ? 'disabled' : ''" @click="rowInsertPop(row_menu.row, -1);row_menu_show = false;">Insert
                Above</a>
            <a :class="!canAdd || !with_edit ? 'disabled' : ''" @click="rowInsertPop(row_menu.row, 0);row_menu_show = false;">Insert
                Below</a>
            <a :class="!canAdd || !with_edit ? 'disabled' : ''" @click="rowInsertPop(row_menu.row, 0, 'copy');row_menu_show = false;">Duplicate</a>
            <a :class="!row_menu.can_del || !with_edit ? 'disabled' : ''"
               @click="deleteRow(row_menu.row, row_menu.idx);row_menu_show = false;">Delete</a>
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
import CustomCellSettingsDcr from '../CustomCell/CustomCellSettingsDcr.vue';
import CustomCellPivotField from '../CustomCell/CustomCellPivotField.vue';
import CustomCellColRowGroup from '../CustomCell/CustomCellColRowGroup.vue';
import CustomCellKanbanSett from '../CustomCell/CustomCellKanbanSett.vue';
import CustomCellSimplemapSett from '../CustomCell/CustomCellSimplemapSett.vue';
import CustomCellRefConds from '../CustomCell/CustomCellRefConds.vue';
import CustomCellCondFormat from '../CustomCell/CustomCellCondFormat.vue';
import CustomCellAlertNotif from '../CustomCell/CustomCellAlertNotif.vue';
import CustomCellAddon from '../CustomCell/CustomCellAddon.vue';
import CustomCellPlans from '../CustomCell/CustomCellPlans.vue';
import CustomCellTwilio from '../CustomCell/CustomCellTwilio.vue';
import CustomCellConnection from '../CustomCell/CustomCellConnection.vue';
import CustomCellPages from '../CustomCell/CustomCellPages.vue';
import CustomCellUserGroups from '../CustomCell/CustomCellUserGroups.vue';
import CustomCellInvitations from '../CustomCell/CustomCellInvitations.vue';
import CustomCellFolderView from '../CustomCell/CustomCellFolderView.vue';
import CustomCellTableView from '../CustomCell/CustomCellTableView.vue';
import CustomCellStimAppView from '../CustomCell/CustomCellStimAppView.vue';
import CustomCellIncomingLinks from '../CustomCell/CustomCellIncomingLinks.vue';
import SettingsDisplayNameCell from '../CustomCell/SettingsDisplayNameCell.vue';
import HeaderMenuElem from './Header/HeaderMenuElem.vue';
import HeaderResizer from './Header/HeaderResizer.vue';
import SrvBlock from "../CommonBlocks/SrvBlock";

import StickyColumnsMixin from '../_Mixins/StickyColumnsMixin.vue';
import TestRowColMixin from '../_Mixins/TestRowColMixin.vue';
import CanEditMixin from '../_Mixins/CanViewEditMixin.vue';
import CheckRowBackendMixin from '../_Mixins/CheckRowBackendMixin.vue';
import HeaderRowColSpanMixin from '../_Mixins/HeaderRowColSpanMixin.vue';
import SrvMixin from '../_Mixins/SrvMixin.vue';
import CellMenuMixin from "../_Mixins/CellMenuMixin.vue";
import JustifyTableMixin from "../_Mixins/JustifyTableMixin.vue";

export default {
    name: "StickyTableComponent",
    mixins: [
        StickyColumnsMixin,
        TestRowColMixin,
        CanEditMixin,
        CheckRowBackendMixin,
        HeaderRowColSpanMixin,
        SrvMixin,
        CellMenuMixin,
        JustifyTableMixin,
    ],
    components: {
        SrvBlock,
        CustomCellTableData,
        CustomCellSystemTableData,
        CustomCellCorrespTableData,
        CustomCellSettingsDisplay,
        CustomCellDisplayLinks,
        CustomCellSettingsDdl,
        CustomCellSettingsPermission,
        CustomCellSettingsDcr,
        CustomCellPivotField,
        CustomHeadCellTableData,
        CustomCellColRowGroup,
        CustomCellKanbanSett,
        CustomCellSimplemapSett,
        CustomCellRefConds,
        CustomCellCondFormat,
        CustomCellAlertNotif,
        CustomCellAddon,
        CustomCellPlans,
        CustomCellTwilio,
        CustomCellConnection,
        CustomCellPages,
        CustomCellUserGroups,
        CustomCellInvitations,
        CustomCellFolderView,
        CustomCellTableView,
        CustomCellStimAppView,
        SettingsDisplayNameCell,
        CustomCellIncomingLinks,
        HeaderMenuElem,
        HeaderResizer,
    },
    data: function () {
        return {
            btnBehaviors: ['cond_format', 'settings_grouping_fields', 'ref_conds', 'settings_display_links', 'settings_ddl'],
            totalTbHeight: 0,
            draggedRow: null,
            draggedIndex: null,
            draggedHeader: null,
            overHeader: null,
            overRow: null,
            cachedRectWidth: 0,
        }
    },
    props: {
        tb_id: String,
        globalMeta: Object,
        tableMeta: Object,
        settingsMeta: Object,
        allRows: Object | null,
        headerAllRows: Array | null,
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
        addingRow: {
            type: Object,
            default: function () {
                return { active: false };
            }
        },
        selectedRow: Number,
        conditionArray: Array | null,
        headersWithCheck: Array,
        ref_tb_from_refcond: Object | null,
        with_edit: Boolean,
        table_id: Number,
        excluded_row_values: Array,
        parentRow: Object,
        headersChanger: Object,
        requiredChanger: Array,
        link_popup_conditions: Object | Array,
        use_theme: Boolean,
        no_width: Boolean,
        is_visible: Boolean,
        no_height_limit: Boolean,
        foreignSpecial: Object,
        special_extras: {
            type: Object,
            default: function () {
                return {};
            }
        },
        link_popup_tablerow: Object | Array, // for LinkEmptyObjectMixin.vue
        isLink: Object,//CanViewEditMixin.vue
        activeHeightWatcher: Boolean,
        hasFloatActions: Boolean,//StickyColumnsMixin.vue
        scrollView: Object,
        no_b_margin: Boolean,
        no_virtual_scroll: Boolean,
        external_align: String,//JustifyTableMixin.vue


        //systems
        widths: Object,
        listViewActions: Boolean,
        selectedCell: SelectedCells,
        objectForAdd: Object,
    },
    watch: {
        is_visible: function (val) {
            if (val) {
                this.fullTableHeight();
            }
        }
    },
    computed: {
        canDragRow() {
            let availBehaviors = _.concat(['list_view'], this.btnBehaviors);
            let avail = this.$root.checkAvailable(this.user, 'drag_rows');
            let hasRight = in_array(this.behavior, availBehaviors)
                && (
                    this.globalMeta._is_owner
                    ||
                    (this.globalMeta._current_right && this.globalMeta._current_right.can_drag_rows)
                );

            return avail
                && hasRight
                && !this.sort.length;
        },
        canDragColumn() {
            return this.globalMeta.is_system
                || this.globalMeta._is_owner
                || (this.globalMeta._current_right && this.globalMeta._current_right.can_drag_columns);
        },
        addBtnStyle() {
            let style = _.cloneDeep(this.textStyle);
            style.maxHeight = this.tdCellHGT + 'px';
            if (this.use_theme) {
                Object.assign(style, this.$root.themeButtonStyle);
            }
            return style;
        },
        noVisibleLnkFlds() {
            return this.tableMeta._fields && this.tableMeta._fields.length && !this.showMetaFields.length && this.link_popup_tablerow;
        },
        rowsForSelected() {
            return this.headerAllRows || this.allRows || [];
        },
    },
    methods: {
        virtualRedraw() {
            //
        },
        fullTableHeight() {
            if (this.activeHeightWatcher) {
                window.setTimeout(() => {
                    if (this.$refs.tbWrap) {
                        let curHeight = this.$refs.tbWrap.clientHeight;
                        if (Math.abs(this.totalTbHeight - curHeight) > 10) {
                            this.totalTbHeight = curHeight;
                            this.$emit('total-tb-height-changed', this.totalTbHeight);
                        }
                    }
                }, 100);
            }
        },
        //check rows
        sortAvail(tableHeader, curHeaderRow) {
            return (
                    this.behavior === 'incoming_links'
                    ||
                    (this.behavior === 'link_popup' && tableHeader.header_triangle)
                    ||
                    (this.behavior === 'list_view' && tableHeader.header_triangle)
                )
                &&
                this.lastRow(tableHeader, curHeaderRow);
        },
        isRequired(tableHeader) {
            return this.requiredChanger
                ? this.$root.inArray(tableHeader.field, this.requiredChanger)
                : tableHeader.f_required;
        },
        ifNeedMassCheck(tableRow) {
            tableRow._checked_row = !tableRow._checked_row;
            this.$root.actionForMassCheckRows(tableRow);
            this.$emit('row-selected');
        },

        selectedRowsAreFavorites() {
            let checked = _.filter(this.rowsForSelected, (row) => {
                return row._checked_row;
            });

            if (this.$root.all_checked_rows && checked.length === this.rowsForSelected.length) {
                return this.$root.tableMeta._view_rows_count === this.$root.tableMeta._fav_rows_count;
            } else {
                return checked.length && !_.find(checked, (row) => {
                    return !row._is_favorite;
                });
            }
        },
        hasSelectedRows() {
            let checked = _.filter(this.rowsForSelected, (row) => {
                return row && row._checked_row;
            });

            if (this.$root.all_checked_rows && checked.length === this.rowsForSelected.length) {
                return this.$root.tableMeta._view_rows_count;
            } else {
                return checked.length;
            }
        },

        //additional functions
        headerHasCheckBox(tableHeader) {
            return this.inArray(tableHeader.field, this.headersWithCheck) && this.rowsForSelected;
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
                unchecked = _.findIndex(this.rowsForSelected, (el) => {
                    return !(
                        this.conditionArray
                        &&
                        _.findIndex(this.conditionArray, {id: Number(el.id)}) > -1
                    );
                });
            } else {
                unchecked = _.findIndex(this.rowsForSelected, (el) => {
                    return !this.inArray(el.code, ['q_tables', 'row_table']) && !el[field];
                });
            }

            return unchecked === -1;
        },

        //rows changing
        canTrash(tableRow) {
            if (this.special_extras && this.special_extras.cannot_delete_system && tableRow.is_system) {
                return false;
            }
            return this.inArray(this.behavior, this.actionDelArray)
                && (!tableRow.is_system || this.inArray(this.behavior, this.actionDelSystem))
                && (this.behavior != 'cond_format' || tableRow.user_id == this.user.id)
                && this.canDelete;
        },
        showAddDDLOption(tableHeader, tableRow) {
            this.$emit('show-add-ddl-option', tableHeader, tableRow);
        },
        rowInsertPop(tableRow, dir, copy) {
            if (this.canAdd && this.with_edit) {
                this.$emit('insert-pop-row', to_float(tableRow.row_order + dir), tableRow, copy ? tableRow : null);
            }
        },
        addRow() {
            if (this.with_edit) {
                this.$emit('added-row');
            }
        },
        updatedRow(params, hdr) {
            this.$emit('updated-row', params, hdr);
        },
        deleteRow(tableRow, index) {
            if (this.with_edit) {
                this.$emit('delete-row', tableRow, index);
            }
        },
        rowIndexClicked(index, row) {
            this.$emit('row-index-clicked', index, row);
        },
        radioChecked(index) {
            this.$emit('radio-checked', index);
        },
        showHeaderSettings(header) {
            this.$emit('show-header-settings', header);
        },
        showAddColumn() {
            eventBus.$emit('show-add-table-column-popup');
        },
        twilioTestPop(row_id) {
            eventBus.$emit('show-twilio-test-popup', row_id);
        },

        //mass rows actions
        emitDeleteSelected() {
            if (this.hasSelectedRows() && this.with_edit) {
                this.$emit('delete-selected-rows');
            } else {
                //Swal('Info','No record has been selected.');
            }
        },
        toggleSelectedRows() {
            let status = !this.hasSelectedRows();
            let cmdOrCtrl = window.event.metaKey || window.event.ctrlKey;
            this.$root.all_checked_rows = status && cmdOrCtrl;
            _.each(this.rowsForSelected, (row) => {
                this.$set(row, '_checked_row', status);
            });
        },
        toggleAllFavorites() {
            if (this.hasSelectedRows()) {
                this.$emit('toggle-all-favorites', this.selectedRowsAreFavorites() ? 0 : 1);
            } else {
                Swal('Info', 'No record has been selected.');
            }
        },

        //sorting
        sortByField(tableHeader, $dir) {
            let cmdOrCtrl = window.event.metaKey || window.event.ctrlKey;
            let spec_key = cmdOrCtrl || window.event.altKey || window.event.shiftKey;
            this.$emit(spec_key ? 'sub-sort-by-field' : 'sort-by-field', tableHeader, $dir);
        },
        getSortLvl(tableHeader) {
            let idx = _.findIndex(this.sort, {field: tableHeader.field});
            return idx > -1 ? idx + 1 : '';
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

                if (this.user.id && !this.user.view_hash) {
                    axios.put('/ajax/settings/change-order-column', {
                        table_id: (isGlobal ? this.globalMeta.id : this.tableMeta.id),
                        select_user_header_id: this.draggedHeader.user_header_id,
                        target_user_header_id: tableHeader.user_header_id
                    }).then(({data}) => {
                        if (isGlobal && data._fields) {
                            _.each(data._fields, (fld) => {
                                let present = _.find(this.globalMeta._fields, {id: Number(fld.id)});
                                present ? present.order = fld.order : null;
                            });
                            this.globalMeta._fields = _.orderBy(this.globalMeta._fields, 'order');
                            this.$emit('changed-cols-order');
                        }
                    }).catch(errors => {
                        Swal('Info', getErrors(errors));
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
                if (this.user.id && !this.user.view_hash) {
                    axios.put('/ajax/settings/change-row-order', {
                        table_id: this.tableMeta.id,
                        from_order: this.draggedRow.row_order,
                        to_order: tableRow.row_order,
                        from_id: this.draggedRow.id,
                        to_id: tableRow.id,
                    }).then(({data}) => {
                        this.$emit('reorder-rows');
                    }).catch(errors => {
                        Swal('Info', getErrors(errors));
                    }).finally(() => {
                        this.draggedRow = null;
                    });
                }
            }
        },
        presentOverStyle(tableRow) {
            if (this.overRow === tableRow.id) {
                if (this.draggedRow) {
                    let style = _.cloneDeep(this.textStyle);
                    style.borderTop = '2px dashed #000';
                    return style;
                }
            }
            return this.textStyle;
        },

        //src record and tables function
        showSrcRecord(lnk, header, tableRow) {
            this.$emit('show-src-record', lnk, header, tableRow);
        },
        showAddRefCond(refId) {
            this.$emit('show-add-ref-cond', refId);
        },
        showDefValPopup(tableRow, moreParam) {
            this.$emit('show-def-val-popup', tableRow, moreParam);
        },
        callBack(phone) {
            this.$emit('call-back', phone);
        },


        //STICKY STYLES
        getThStyle(tableHeader, curHeaderRow, hidden, index) {
            let style = this.$root.themeTableHeaderBgStyle;
            let bkg = style.background;
            if (index !== undefined && tableHeader.header_background) {
                let colspan = this.getMultiColspan(index, curHeaderRow) - 1;
                let all_the_same_clr = true;
                for (let i = 1; i <= colspan; i++) {
                    let next_hdr = this.showMetaFields[i + index];
                    all_the_same_clr = all_the_same_clr
                        && next_hdr
                        && next_hdr.header_background === tableHeader.header_background;
                }
                if (all_the_same_clr) {
                    bkg = this.$root.buildCssGradient(tableHeader.header_background) || style.background;
                }
            }
            let text = this.tableMeta.is_system ? this.textSysStyle : this.textStyle;

            return {
                width: this.tdCellWidth(tableHeader) + 'px',
                background: bkg,
                ...text,
            };
        },
        getThSysWidth(width) {
            let style = this.$root.themeTableHeaderBgStyle;
            let text = this.tableMeta.is_system ? this.textSysStyle : this.textStyle;
            return {
                width: width + 'px',
                background: style.background,
                ...text,
            };
        },
        //-------------------

        //backend autocomplete
        addCellUpdated(tableHeader) {
            this.$root.checkSingleValidation(tableHeader, this.objectForAdd);
            if (!this.tableMeta.is_system) {
                this.checkRowOnBackend(this.tableMeta.id, this.objectForAdd);
            } else {
                this.$emit('redraw-table');
            }
        },

        //keys
        keydownHandler(e) {
            if (e.keyCode == 27) {
                this.overRow = null;
                this.overHeader = null;
                this.$forceUpdate();
            }
        },

        //drawing
        notForbidden(i_col) {
            return !this.inArray(i_col, this.forbiddenColumns);
        },
        outOfViewport(tableHeader, rowIndex, colIndex) {
            let cannotUseVirtualWith = this.no_virtual_scroll || this.no_width || !this.listViewActions || this.tableMeta.table_engine !== 'virtual';
            if (cannotUseVirtualWith) {
                return false;
            }

            let rowTop = rowIndex * (this.tdCellHGT + 5);
            let rowBottom = rowTop + (this.tdCellHGT + 5);

            let colLeft = this.metaFieldsRights[colIndex - 1] || 0;
            let colRight = this.metaFieldsRights[colIndex] || 0;

            if (this.hasFloating && tableHeader.is_floating) {
                return this.scrollView.top > rowBottom || this.scrollView.bottom < rowTop;
            } else {
                return this.scrollView.top > rowBottom || this.scrollView.bottom < rowTop
                    || this.scrollView.left > colRight || this.scrollView.right < colLeft;
            }
        },

        //external scroll//
        externalScroll(table_id, row_id) {
            if (this.tableMeta.id == table_id) {
                let refs = this.$refs['cttr_' + table_id + '_' + row_id];
                let rrow = _.first(refs);
                if (!!window.chrome && rrow) {
                    rrow.scrollIntoView({block: 'center'});
                }
            }
        },
    },
    created() {
    },
    mounted() {
        this.mountJustifier();

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

.add-col {
    background-color: rgba(255, 255, 255, 0.4);
    height: 40px;

    &:hover {
        background-color: rgba(255, 255, 255, 0.7);
    }

    .fa-plus {
        cursor: pointer;
        font-size: 2em;
    }
}

.icon_on_hover {
    .target_icon {
        display: none;
    }

    &:hover {
        .target_icon {
            display: inline;
        }
    }
}

.srv {
    position: relative;
    top: 1px;
    font-size: 16px;
    color: #039;
}
</style>