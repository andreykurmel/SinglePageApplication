<template>
    <div class="grouping-wrapper full-frame">
        <div ref="grouping_component" :redraw="isVisible" class="full-frame">
            <table ref="grouping_table" :style="{ marginLeft: grpML+'px' }" class="grouping-table">
                <colgroup>
                    <col v-for="(hdr,i) in visibleGroups" :style="{width: localCellWidth(hdr, hdr.id)+'px'}"/>
                    <col v-for="(hdr,i) in visibleColgrFields" :style="{width: localCellWidth(hdr)+'px'}"/>
                </colgroup>

                <thead :style="{zIndex: zi.head}" class="table-head">
                <!--Multiheader layout fix-->
                <tr class="multiheader-fix">
                    <td v-for="(hdr,i) in visibleGroups" :style="{width: localCellWidth(hdr, hdr.id)+'px'}"></td>
                    <td v-for="(hdr,i) in visibleColgrFields" :style="{width: localCellWidth(hdr)+'px'}"></td>
                </tr>
                <!--Main Headers-->
                <tr v-for="curHeaderRow in maxRowsInHeader">
                    <template v-if="curHeaderRow == 1">
                        <th v-for="(hdr,i) in visibleGroups"
                            :class="'lvl'+i"
                            :rowspan="maxRowsInHeader+1"
                            :style="grpColor(i)"
                            class="txt--center relative"
                        >
                            <div class="pointer bold" @click="globalCollapse(i)">{{
                                    allIsCollapsed(i) ? '+' : '-'
                                }}
                            </div>
                            <header-resizer :hdr_key="'indent'"
                                            :table-header="getIndentHdr(hdr.id)"
                                            :user="user"
                                            @col-resized="$emit('col-resized')"
                            ></header-resizer>
                        </th>
                    </template>
                    <th v-for="(hdr,i) in visibleColgrFields"
                        v-if="getMultiColspan(i, curHeaderRow) && getMultiRowspan(i, curHeaderRow)"
                        :class="[overHeader === hdr.id ? 'th-overed' : '']"
                        :colspan="getMultiColspan(i, curHeaderRow)"
                        :rowspan="getMultiRowspan(i, curHeaderRow)"
                        :style="getThStl(hdr)"
                        class="relative"
                    >
                        <div :style="tableMeta.is_system ? textSysStyle : textStyle"
                             class="full-height flex flex--center"
                             draggable="true"
                             style="cursor: pointer;"
                             @dragend="(!hdr.is_floating && with_edit ? overHeader = null : null)"
                             @dragenter="(!hdr.is_floating && with_edit ? overHeader = hdr.id : null)"
                             @dragstart="(!hdr.is_floating && with_edit ? startChangeHdrOrder(hdr) : null)"
                             @drop="(!hdr.is_floating && with_edit ? endChangeHdrOrder(hdr) : null)"
                             @dragover.prevent=""
                        >
                            <span class="head-content">{{ getMultiName(hdr, curHeaderRow) }}</span>
                            <span v-if="hdr.f_required" class="required-wildcart">*</span>
                        </div>

                        <header-menu-elem v-if="canSort && lastRow(hdr, curHeaderRow)"
                                          :table-meta="tableMeta"
                                          :table-header="hdr"
                                          :rotate="true"
                                          :only_sorting="true"
                                          style="position: absolute; right: -3px; bottom: -3px;"
                                          @field-sort-asc="sortByField(hdr, 'asc')"
                                          @field-sort-desc="sortByField(hdr, 'desc')"
                        ></header-menu-elem>
                        <span v-if="getSortLvl(hdr)" style="position: absolute; bottom: 0px; left: 3px;">{{ getSortLvl(hdr) }}</span>

                        <header-resizer :init_width="localCellWidth(hdr)"
                                        :table-header="hdr"
                                        :user="user"
                                        @col-resized="$emit('col-resized')"
                        ></header-resizer>
                    </th>
                </tr>
                <!--Unit row in headers-->
                <tr>
                    <th :is="'custom-head-cell-table-data'"
                        v-for="(hdr,i) in visibleColgrFields"
                        v-if="(hdr.unit || hdr.unit_display)"
                        :key="hdr.id"
                        :max-cell-rows="maxCellRows"
                        :style="getThStl(hdr)"
                        :table-header="hdr"
                        :table-meta="tableMeta"
                        :user="user"
                    ></th>
                </tr>
                </thead>
                <tbody class="table-body">
                <template v-for="i1 in sortedKeys[0]"
                          v-if="groupedRows[i1] && !isSys(i1) && !getLvlVariable(i1, '', '', '', '', 0, '__collapsed')"
                >
                    <tr v-if="visibleGroups[0]">
                        <!-- Header LVL1 -->
                        <td class="group-header" @click="collapsedClick(i1, '', '', '', '', 1)">
                            <div class="flex">
                                <div class="ml10 mr5">
                                    {{ getLvlVariable(i1, '', '', '', '', 1, '__collapsed') ? '+' : '-' }}
                                </div>
                                <div v-if="settByFld(visibleGroups[0].id, 'field_name_visible')" class="mr5">
                                    {{ visibleGroups[0].name + ': ' }}
                                </div>
                                <table style="width: min-content;">
                                    <tr>
                                        <td :is="cell_component_name"
                                            :behavior="'grouping_table'"
                                            :cell-height="1"
                                            :cell-value="i1"
                                            :global-meta="tableMeta"
                                            :is-add-row="true"
                                            :max-cell-rows="1"
                                            :rows-count="1"
                                            :settings-meta="$root.settingsMeta"
                                            :table-header="visibleGroups[0]"
                                            :table-meta="tableMeta"
                                            :table-row="{}"
                                            :use_theme="true"
                                            :user="user"
                                            :with_edit="false"
                                            style="border: none; white-space: nowrap; cursor: auto;"
                                            @show-src-record="showSrcRecord"
                                        ></td>
                                    </tr>
                                </table>
                                <div v-if="getLvlVariable(i1, '', '', '', '', 1, '__collapsed')">
                                    &nbsp;({{ getLvlVariable(i1, '', '', '', '', 1, '__len') }})
                                </div>
                            </div>
                        </td>
                        <td v-for="(hdr,i) in visibleGroups" v-if="i > 0" :style="subTotWi(hdr, hdr.id)"
                            class="group-header" @click="collapsedClick(i1, '', '', '', '', 1)"></td>
                        <td v-for="(hdr,i) in visibleColgrFields" :style="subTotWi(hdr)" class="group-header"
                            @click="collapsedClick(i1, '', '', '', '', 1)">
                            <div :style="{textAlign: hdr.col_align}">{{ calculateStats(hdr, groupedRows[i1], 0) }}</div>
                        </td>
                    </tr>

                    <template v-for="i2 in sortedKeys[1]"
                              v-if="groupedRows[i1][i2] && !isSys(i2) && !getLvlVariable(i1, i2, '', '', '', 1, '__collapsed')"
                    >
                        <tr v-if="visibleGroups[1]">
                            <td v-for="(hdr,i) in visibleGroups" v-if="i < 1" :class="'lvl'+i"
                                :style="grpColor(i)"></td>
                            <!-- Header LVL2 -->
                            <td class="group-header" @click="collapsedClick(i1, i2, '', '', '', 2)">
                                <div class="flex">
                                    <div class="ml10 mr5">
                                        {{ getLvlVariable(i1, i2, '', '', '', 2, '__collapsed') ? '+' : '-' }}
                                    </div>
                                    <div v-if="settByFld(visibleGroups[1].id, 'field_name_visible')" class="mr5">
                                        {{ visibleGroups[1].name + ': ' }}
                                    </div>
                                    <table style="width: min-content;">
                                        <tr>
                                            <td :is="cell_component_name"
                                                :behavior="'grouping_table'"
                                                :cell-height="1"
                                                :cell-value="i2"
                                                :global-meta="tableMeta"
                                                :is-add-row="true"
                                                :max-cell-rows="1"
                                                :rows-count="1"
                                                :settings-meta="$root.settingsMeta"
                                                :table-header="visibleGroups[1]"
                                                :table-meta="tableMeta"
                                                :table-row="{}"
                                                :use_theme="true"
                                                :user="user"
                                                :with_edit="false"
                                                style="border: none; white-space: nowrap; cursor: auto;"
                                                @show-src-record="showSrcRecord"
                                            ></td>
                                        </tr>
                                    </table>
                                    <div v-if="getLvlVariable(i1, i2, '', '', '', 2, '__collapsed')">
                                        &nbsp;({{ getLvlVariable(i1, i2, '', '', '', 2, '__len') }})
                                    </div>
                                </div>
                            </td>
                            <td v-for="(hdr,i) in visibleGroups" v-if="i > 1" :style="subTotWi(hdr)"
                                class="group-header" @click="collapsedClick(i1, i2, '', '', '', 2)"></td>
                            <td v-for="(hdr,i) in visibleColgrFields" :style="subTotWi(hdr)" class="group-header"
                                @click="collapsedClick(i1, i2, '', '', '', 2)">
                                <div :style="{textAlign: hdr.col_align}">{{
                                        calculateStats(hdr, groupedRows[i1][i2], 1)
                                    }}
                                </div>
                            </td>
                        </tr>

                        <template v-for="i3 in sortedKeys[2]"
                                  v-if="groupedRows[i1][i2][i3] && !isSys(i3) && !getLvlVariable(i1, i2, i3, '', '', 2, '__collapsed')"
                        >
                            <tr v-if="visibleGroups[2]">
                                <td v-for="(hdr,i) in visibleGroups" v-if="i < 2" :class="'lvl'+i"
                                    :style="grpColor(i)"></td>
                                <!-- Header LVL3 -->
                                <td class="group-header" @click="collapsedClick(i1, i2, i3, '', '', 3)">
                                    <div class="flex">
                                        <div class="ml10 mr5">
                                            {{ getLvlVariable(i1, i2, i3, '', '', 3, '__collapsed') ? '+' : '-' }}
                                        </div>
                                        <div v-if="settByFld(visibleGroups[2].id, 'field_name_visible')" class="mr5">
                                            {{ visibleGroups[2].name + ': ' }}
                                        </div>
                                        <table style="width: min-content;">
                                            <tr>
                                                <td :is="cell_component_name"
                                                    :behavior="'grouping_table'"
                                                    :cell-height="1"
                                                    :cell-value="i3"
                                                    :global-meta="tableMeta"
                                                    :is-add-row="true"
                                                    :max-cell-rows="1"
                                                    :rows-count="1"
                                                    :settings-meta="$root.settingsMeta"
                                                    :table-header="visibleGroups[2]"
                                                    :table-meta="tableMeta"
                                                    :table-row="{}"
                                                    :use_theme="true"
                                                    :user="user"
                                                    :with_edit="false"
                                                    style="border: none; white-space: nowrap; cursor: auto;"
                                                    @show-src-record="showSrcRecord"
                                                ></td>
                                            </tr>
                                        </table>
                                        <div v-if="getLvlVariable(i1, i2, i3, '', '', 3, '__collapsed')">
                                            &nbsp;({{ getLvlVariable(i1, i2, i3, '', '', 3, '__len') }})
                                        </div>
                                    </div>
                                </td>
                                <td v-for="(hdr,i) in visibleGroups" v-if="i > 2" :style="subTotWi(hdr)"
                                    class="group-header" @click="collapsedClick(i1, i2, i3, '', '', 3)"></td>
                                <td v-for="(hdr,i) in visibleColgrFields" :style="subTotWi(hdr)" class="group-header"
                                    @click="collapsedClick(i1, i2, i3, '', '', 3)">
                                    <div :style="{textAlign: hdr.col_align}">
                                        {{ calculateStats(hdr, groupedRows[i1][i2][i3], 2) }}
                                    </div>
                                </td>
                            </tr>

                            <template v-for="i4 in sortedKeys[3]"
                                      v-if="groupedRows[i1][i2][i3][i4] && !isSys(i4) && !getLvlVariable(i1, i2, i3, i4, '', 3, '__collapsed')"
                            >
                                <tr v-if="visibleGroups[3]">
                                    <td v-for="(hdr,i) in visibleGroups" v-if="i < 3" :class="'lvl'+i"
                                        :style="grpColor(i)"></td>
                                    <!-- Header LVL4 -->
                                    <td class="group-header" @click="collapsedClick(i1, i2, i3, i4, '', 4)">
                                        <div class="flex">
                                            <div class="ml10 mr5">
                                                {{ getLvlVariable(i1, i2, i3, i4, '', 4, '__collapsed') ? '+' : '-' }}
                                            </div>
                                            <div v-if="settByFld(visibleGroups[3].id, 'field_name_visible')"
                                                 class="mr5">{{ visibleGroups[3].name + ': ' }}
                                            </div>
                                            <table style="width: min-content;">
                                                <tr>
                                                    <td :is="cell_component_name"
                                                        :behavior="'grouping_table'"
                                                        :cell-height="1"
                                                        :cell-value="i4"
                                                        :global-meta="tableMeta"
                                                        :is-add-row="true"
                                                        :max-cell-rows="1"
                                                        :rows-count="1"
                                                        :settings-meta="$root.settingsMeta"
                                                        :table-header="visibleGroups[3]"
                                                        :table-meta="tableMeta"
                                                        :table-row="{}"
                                                        :use_theme="true"
                                                        :user="user"
                                                        :with_edit="false"
                                                        style="border: none; white-space: nowrap; cursor: auto;"
                                                        @show-src-record="showSrcRecord"
                                                    ></td>
                                                </tr>
                                            </table>
                                            <div v-if="getLvlVariable(i1, i2, i3, i4, '', 4, '__collapsed')">
                                                &nbsp;({{ getLvlVariable(i1, i2, i3, i4, '', 4, '__len') }})
                                            </div>
                                        </div>
                                    </td>
                                    <td v-for="(hdr,i) in visibleGroups" v-if="i > 3" :style="subTotWi(hdr)"
                                        class="group-header" @click="collapsedClick(i1, i2, i3, i4, '', 4)"></td>
                                    <td v-for="(hdr,i) in visibleColgrFields" :style="subTotWi(hdr)"
                                        class="group-header" @click="collapsedClick(i1, i2, i3, i4, '', 4)">
                                        <div :style="{textAlign: hdr.col_align}">
                                            {{ calculateStats(hdr, groupedRows[i1][i2][i3][i4], 3) }}
                                        </div>
                                    </td>
                                </tr>

                                <template v-for="i5 in sortedKeys[4]"
                                          v-if="groupedRows[i1][i2][i3][i4][i5] && !isSys(i5) && !getLvlVariable(i1, i2, i3, i4, i5, 4, '__collapsed')"
                                >
                                    <tr v-if="visibleGroups[4]">
                                        <td v-for="(hdr,i) in visibleGroups" v-if="i < 4" :class="'lvl'+i"
                                            :style="grpColor(i)"></td>
                                        <!-- Header LVL5 -->
                                        <td class="group-header" @click="collapsedClick(i1, i2, i3, i4, i5, 5)">
                                            <div class="flex">
                                                <div class="ml10 mr5">{{
                                                        getLvlVariable(i1, i2, i3, i4, i5, 5, '__collapsed') ? '+' : '-'
                                                    }}
                                                </div>
                                                <div v-if="settByFld(visibleGroups[4].id, 'field_name_visible')"
                                                     class="mr5">{{ visibleGroups[4].name + ': ' }}
                                                </div>
                                                <table style="width: min-content;">
                                                    <tr>
                                                        <td :is="cell_component_name"
                                                            :behavior="'grouping_table'"
                                                            :cell-height="1"
                                                            :cell-value="i5"
                                                            :global-meta="tableMeta"
                                                            :is-add-row="true"
                                                            :max-cell-rows="1"
                                                            :rows-count="1"
                                                            :settings-meta="$root.settingsMeta"
                                                            :table-header="visibleGroups[4]"
                                                            :table-meta="tableMeta"
                                                            :table-row="{}"
                                                            :use_theme="true"
                                                            :user="user"
                                                            :with_edit="false"
                                                            style="border: none; white-space: nowrap; cursor: auto;"
                                                            @show-src-record="showSrcRecord"
                                                        ></td>
                                                    </tr>
                                                </table>
                                                <div v-if="getLvlVariable(i1, i2, i3, i4, i5, 5, '__collapsed')">&nbsp;({{
                                                        getLvlVariable(i1, i2, i3, i4, i5, 5, '__len')
                                                    }})
                                                </div>
                                            </div>
                                        </td>
                                        <td v-for="(hdr,i) in visibleGroups" v-if="i > 4" :style="subTotWi(hdr)"
                                            class="group-header" @click="collapsedClick(i1, i2, i3, i4, i5, 5)"></td>
                                        <td v-for="(hdr,i) in visibleColgrFields" :style="subTotWi(hdr)"
                                            class="group-header" @click="collapsedClick(i1, i2, i3, i4, i5, 5)">
                                            <div :style="{textAlign: hdr.col_align}">
                                                {{ calculateStats(hdr, groupedRows[i1][i2][i3][i4][i5], 4) }}
                                            </div>
                                        </td>
                                    </tr>

                                    <tr
                                        v-for="(tableRow, index) in groupedRows[i1][i2][i3][i4][i5].rows"
                                        v-if="tableRow && !rowExcludedByValue(tableRow)"
                                        :style="getPresentRowStyle(tableRow, index, tableMeta)"
                                    >
                                        <td v-for="(hdr,i) in visibleGroups" v-if="i < 5" :class="'lvl'+i"
                                            :style="grpColor(i)"></td>
                                        <!--<td v-for="(tableHeader, i) in visibleColgrFields">{{ tableRow[tableHeader.field] }}</td>-->
                                        <template v-for="(tableHeader, i) in visibleColgrFields">
                                            <td :is="cell_component_name"
                                                :all-rows="groupedRows[i1][i2][i3][i4][i5]"
                                                :behavior="'grouping_table'"
                                                :cell-height="cellHeight"
                                                :cell-value="tableRow[tableHeader.field]"
                                                :fixed-width="true"
                                                :global-meta="tableMeta"
                                                :is-add-row="true"
                                                :max-cell-rows="maxCellRows"
                                                :row-index="groupedRows[i1][i2][i3][i4][i5].length || -1"
                                                :rows-count="groupedRows[i1][i2][i3][i4][i5].length"
                                                :settings-meta="$root.settingsMeta"
                                                :table-header="tableHeader"
                                                :table-meta="tableMeta"
                                                :table-row="tableRow"
                                                :use_theme="true"
                                                :user="user"
                                                :with_edit="with_edit"

                                                @updated-cell="updatedRow"
                                                @show-add-ddl-option="showAddDDLOption"
                                                @show-src-record="showSrcRecord"
                                                @cell-menu="showRowMenu"
                                            ></td>
                                        </template>
                                    </tr>

                                    <!--<tr v-if="!getLvlVariable(i1, i2, i3, i4, i5, 5, '__collapsed') && getLvlVariable(i1, i2, i3, i4, i5, 5, '__subtot')">
                                        <td v-for="(hdr,i) in visibleGroups" :class="i < 5 ? 'lvl'+i : 'subtotal'" :style="i < 5 ? grpColor(i) : subTotWi(hdr)"></td>
                                        <td v-for="(hdr,i) in visibleColgrFields" class="subtotal" :style="subTotWi(hdr)">
                                            <div :style="{textAlign: hdr.col_align}">{{ calculateStats(hdr, groupedRows[i1][i2][i3][i4][i5], 4) }}</div>
                                        </td>
                                    </tr>-->
                                </template>

                                <!--<tr v-if="!getLvlVariable(i1, i2, i3, i4, '', 4, '__collapsed') && getLvlVariable(i1, i2, i3, i4, '', 4, '__subtot')">
                                    <td v-for="(hdr,i) in visibleGroups" :class="i < 4 ? 'lvl'+i : 'subtotal'" :style="i < 4 ? grpColor(i) : subTotWi(hdr)"></td>
                                    <td v-for="(hdr,i) in visibleColgrFields" class="subtotal" :style="subTotWi(hdr)">
                                        <div :style="{textAlign: hdr.col_align}">{{ calculateStats(hdr, groupedRows[i1][i2][i3][i4], 3) }}</div>
                                    </td>
                                </tr>-->
                            </template>

                            <!--<tr v-if="!getLvlVariable(i1, i2, i3, '', '', 3, '__collapsed') && getLvlVariable(i1, i2, i3, '', '', 3, '__subtot')">
                                <td v-for="(hdr,i) in visibleGroups" :class="i < 3 ? 'lvl'+i : 'subtotal'" :style="i < 3 ? grpColor(i) : subTotWi(hdr)"></td>
                                <td v-for="(hdr,i) in visibleColgrFields" class="subtotal" :style="subTotWi(hdr)">
                                    <div :style="{textAlign: hdr.col_align}">{{ calculateStats(hdr, groupedRows[i1][i2][i3], 2) }}</div>
                                </td>
                            </tr>-->
                        </template>

                        <!--<tr v-if="!getLvlVariable(i1, i2, '', '', '', 2, '__collapsed') && getLvlVariable(i1, i2, '', '', '', 2, '__subtot')">
                            <td v-for="(hdr,i) in visibleGroups" :class="i < 2 ? 'lvl'+i : 'subtotal'" :style="i < 2 ? grpColor(i) : subTotWi(hdr)"></td>
                            <td v-for="(hdr,i) in visibleColgrFields" class="subtotal" :style="subTotWi(hdr)">
                                <div :style="{textAlign: hdr.col_align}">{{ calculateStats(hdr, groupedRows[i1][i2], 1) }}</div>
                            </td>
                        </tr>-->
                    </template>

                    <!--<tr v-if="!getLvlVariable(i1, '', '', '', '', 1, '__collapsed') && getLvlVariable(i1, '', '', '', '', 1, '__subtot')">
                        <td v-for="(hdr,i) in visibleGroups" :class="i < 1 ? 'lvl'+i : 'subtotal'" :style="i < 1 ? grpColor(i) : subTotWi(hdr)"></td>
                        <td v-for="(hdr,i) in visibleColgrFields" class="subtotal" :style="subTotWi(hdr)">
                            <div :style="{textAlign: hdr.col_align}">{{ calculateStats(hdr, groupedRows[i1], 0) }}</div>
                        </td>
                    </tr>-->
                </template>

                <!--<tr v-if="!getLvlVariable('', '', '', '', '', 0 , '__collapsed') && getLvlVariable('', '', '', '', '', 0, '__subtot')">-->
                <tr>
                    <td v-for="(hdr,i) in visibleGroups" :class="i < 0 ? 'lvl'+i : 'subtotal'"
                        :style="i < 0 ? grpColor(i) : subTotWi(hdr)"></td>
                    <td v-for="(hdr,i) in visibleColgrFields" :style="subTotWi(hdr)" class="subtotal">
                        <div :style="{textAlign: hdr.col_align}">{{ calculateStats(hdr, groupedRows, -1) }}</div>
                    </td>
                </tr>
                </tbody>
            </table>
        </div>
        <div :draw_finished="drawFinished()"></div>

        <div v-if="row_menu_show && row_menu.row"
             ref="row_menu"
             :style="rowMenuStyle"
             class="float-rom-menu"
        >
            <a @click="rowIndexClicked(row_menu.row);row_menu_show = false;">Popup</a>
            <a v-if="row_menu.hdr" @click="copyCell(row_menu.row, row_menu.hdr);row_menu_show = false;">Copy Cell</a>
            <a :class="!canAdd ? 'disabled' : ''" @click="rowInsertPop(row_menu.row, -1);row_menu_show = false;">Insert
                Above</a>
            <a :class="!canAdd ? 'disabled' : ''" @click="rowInsertPop(row_menu.row, 0);row_menu_show = false;">Insert
                Below</a>
            <a :class="!canAdd ? 'disabled' : ''" @click="rowInsertPop(row_menu.row, 0, 'copy');row_menu_show = false;">Duplicate</a>
            <a :class="!row_menu.can_del ? 'disabled' : ''" @click="deletedRow(row_menu.row);row_menu_show = false;">Delete</a>
        </div>
    </div>
</template>

<script>
import {eventBus} from "../../app";

import {SpecialFuncs} from "../../classes/SpecialFuncs";

import CellStyleMixin from './../_Mixins/CellStyleMixin.vue';
import CheckRowBackendMixin from "../_Mixins/CheckRowBackendMixin.vue";
import LinkEmptyObjectMixin from "../_Mixins/LinkEmptyObjectMixin";
import CanViewEditMixin from "../_Mixins/CanViewEditMixin.vue";
import CellMenuMixin from "../_Mixins/CellMenuMixin";
import HeaderRowColSpanMixin from '../_Mixins/HeaderRowColSpanMixin.vue';

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
import CustomCellSimplemapSett from '../CustomCell/CustomCellSimplemapSett.vue';
import CustomCellRefConds from '../CustomCell/CustomCellRefConds.vue';
import CustomCellCondFormat from '../CustomCell/CustomCellCondFormat.vue';
import CustomCellPlans from '../CustomCell/CustomCellPlans.vue';
import CustomCellConnection from '../CustomCell/CustomCellConnection.vue';
import CustomCellPages from '../CustomCell/CustomCellPages.vue';
import CustomCellUserGroups from '../CustomCell/CustomCellUserGroups.vue';
import CustomCellInvitations from '../CustomCell/CustomCellInvitations.vue';
import CustomCellTwilio from '../CustomCell/CustomCellTwilio.vue';
import HeaderMenuElem from "./Header/HeaderMenuElem.vue";
import HeaderResizer from "./Header/HeaderResizer.vue";
import StickyTableComponent from "./StickyTableComponent.vue";

export default {
    name: "GroupingTable",
    mixins: [
        CellStyleMixin,
        CheckRowBackendMixin,
        LinkEmptyObjectMixin,
        CanViewEditMixin,
        CellMenuMixin,
        HeaderRowColSpanMixin,
    ],
    components: {
        StickyTableComponent,
        HeaderResizer,
        HeaderMenuElem,

        CustomHeadCellTableData,
        CustomCellTableData,
        CustomCellSystemTableData,
        CustomCellCorrespTableData,
        CustomCellSettingsDisplay,
        CustomCellDisplayLinks,
        CustomCellSettingsDdl,
        CustomCellSettingsPermission,
        CustomCellColRowGroup,
        CustomCellKanbanSett,
        CustomCellSimplemapSett,
        CustomCellRefConds,
        CustomCellCondFormat,
        CustomCellPlans,
        CustomCellConnection,
        CustomCellPages,
        CustomCellUserGroups,
        CustomCellInvitations,
        CustomCellTwilio,
    },
    data: function () {
        return {
            draggedHeader: null,
            overHeader: null,
            grpML: 0,
            globalMeta: this.tableMeta,//For mixins
            behavior: 'grouping_table',
            zi: {
                f_hdr: 200,
                head: 150,
                f_data: 100,
            },
        };
    },
    props: {
        visibleGroups: Array,
        cell_component_name: String,
        selectedGrouping: Object,
        tableMeta: Object,
        groupedRows: Object,
        sortedKeys: Object,
        moreSorting: Array,
        cellHeight: Number,
        maxCellRows: {
            type: Number,
            default: 0
        },
        user: Object,
        page: Number,
        excluded_row_values: Array,
        with_edit: Boolean,
        totalCount: Number,
        isVisible: Boolean,
    },
    watch: {
        isVisible: {
            handler(val) {
                if (val) {
                    this.$nextTick(this.grpTbStyle);
                }
            },
            immediate: true,
        },
    },
    computed: {
        totalLen() {
            return this.visibleGroups.length + this.visibleColgrFields.length;
        },
        activeSetts() {
            return _.filter(this.selectedGrouping._settings, (st) => {
                return st.rg_active;
            });
        },
        visibleColgrFields() {
            let availIds = _.filter(this.selectedGrouping._gr_related_fields, (fld) => fld._grs.fld_visible);
            availIds = _.sortBy(availIds, (fld) => fld._grs.fld_order);
            availIds = _.map(availIds, 'id');

            let fields = _.filter(this.tableMeta._fields, (tableHeader) => {
                return !tableHeader._permis_hidden && this.$root.inArray(tableHeader.id, availIds);
            });
            fields = _.sortBy(fields, (item) => {
                return availIds.indexOf(item.id);
            });
            return fields;
        },
        canSort() {
            return this.selectedGrouping.rg_data_range && this.selectedGrouping.rg_data_range !== '0';
        },
    },
    methods: {
        isShowFieldElem(hdr) {
            return true;
        },
        allIsCollapsed(i) {
            let res = 1;
            _.each(this.groupedRows, (l1, i1) => {
                if (!this.isSys(i1)) {
                    if (i == 0) {
                        res = res && l1['__collapsed'];
                    }
                    _.each(l1, (l2, i2) => {
                        if (!this.isSys(i2)) {
                            if (i == 1) {
                                res = res && l2['__collapsed'];
                            }
                            _.each(l2, (l3, i3) => {
                                if (!this.isSys(i3)) {
                                    if (i == 2) {
                                        res = res && l3['__collapsed'];
                                    }
                                    _.each(l3, (l4, i4) => {
                                        if (!this.isSys(i4)) {
                                            if (i == 3) {
                                                res = res && l4['__collapsed'];
                                            }
                                            _.each(l4, (l5, i5) => {
                                                if (!this.isSys(i5)) {
                                                    if (i == 4) {
                                                        res = res && l5['__collapsed'];
                                                    }
                                                }
                                            });
                                        }
                                    });
                                }
                            });
                        }
                    });
                }
            });
            return res;
        },
        globalCollapse(i) {
            $.LoadingOverlay('show');
            let st = this.allIsCollapsed(i) ? 0 : 1;
            _.each(this.groupedRows, (l1, i1) => {
                if (!this.isSys(i1)) {
                    if (i == 0) {
                        l1['__collapsed'] = st;
                    }
                    _.each(l1, (l2, i2) => {
                        if (!this.isSys(i2)) {
                            if (i == 1) {
                                l2['__collapsed'] = st;
                            }
                            _.each(l2, (l3, i3) => {
                                if (!this.isSys(i3)) {
                                    if (i == 2) {
                                        l3['__collapsed'] = st;
                                    }
                                    _.each(l3, (l4, i4) => {
                                        if (!this.isSys(i4)) {
                                            if (i == 3) {
                                                l4['__collapsed'] = st;
                                            }
                                            _.each(l4, (l5, i5) => {
                                                if (!this.isSys(i5)) {
                                                    if (i == 4) {
                                                        l5['__collapsed'] = st;
                                                    }
                                                }
                                            });
                                        }
                                    });
                                }
                            });
                        }
                    });
                }
            });
        },
        grpTbStyle() {
            let cWi = this.$refs.grouping_component ? this.$refs.grouping_component.clientWidth : 0;
            let tWi = this.$refs.grouping_table ? this.$refs.grouping_table.clientWidth : 0;
            switch (this.selectedGrouping.rg_alignment) {
                case 'start':
                    this.grpML = 0;
                    break;
                case 'center':
                    this.grpML = Math.max(cWi - tWi, 0) / 2;
                    break;
                case 'end':
                    this.grpML = Math.max(cWi - tWi, 0);
                    break;
            }
        },
        settByFld(id, key) {
            let grSett = _.find(this.selectedGrouping._settings, {field_id: Number(id)}) || {};
            return grSett[key];
        },
        grpColor(i) {
            let grpFld = this.activeSetts[i] || {};
            let tbHeader = _.find(this.tableMeta._fields, {id: Number(grpFld.field_id)}) || {};
            return {
                width: this.localCellWidth(tbHeader, tbHeader.id) + 'px',
                backgroundColor: grpFld.color,
            };
        },
        subTotWi(grpOrField, hdrFieldId) {
            let tbHeader = grpOrField.field_id
                ? (_.find(this.tableMeta._fields, {id: Number(grpOrStat.field_id)}) || {})
                : grpOrField;
            return {
                width: this.localCellWidth(tbHeader, hdrFieldId) + 'px',
            };
        },
        calculateStats(statHdr, rowsTree, grIdx) {
            let plain = this.treeToPlain(rowsTree);
            let numbers = _.filter(plain, (item) => !isNaN(item[statHdr.field] || 0));
            numbers = _.map(numbers, (item) => Number(item[statHdr.field] || 0));

            let grpFld = this.activeSetts[grIdx] || {};
            let stat = _.find(grpFld._field_stats, {field_id: Number(statHdr.id)}) || {};
            if (!stat.stat_fn) {
                stat = _.find(this.selectedGrouping._global_stats, {field_id: Number(statHdr.id)}) || {};
            }
            let result;

            switch (stat.stat_fn) {
                case 'count':
                    result = plain.length;
                    break;
                case 'countunique':
                    result = _.uniqBy(plain, statHdr.field).length;
                    break;
                case 'sum':
                    result = _.sum(numbers);
                    break;
                case 'max':
                    result = _.max(numbers);
                    break;
                case 'min':
                    result = _.min(numbers);
                    break;
                case 'avg':
                    result = _.sum(numbers) / plain.length;
                    break;
                case 'mean':
                    result = (_.max(numbers) + _.min(numbers)) / 2;
                    break;
                case 'var':
                    result = this.varFn(numbers);
                    break;
                case 'std':
                    result = Math.sqrt(this.varFn(numbers));
                    break;
            }
            return stat.stat_fn ? this.formatSubtotal(statHdr, stat.stat_fn, result) : '';
        },
        formatSubtotal(header, stat_fn, result) {
            if (['count', 'countunique'].indexOf(stat_fn) > -1) {
                return Number(result).toFixed(0);
            } else {
                return SpecialFuncs.showhtml(header, {__no_html: true}, result, this.tableMeta);
            }
        },
        varFn(numbers) {
            let mean = (_.max(numbers) + _.min(numbers)) / 2;
            let res = 0;
            _.each(numbers, (item) => {
                res += Math.pow((item - mean), 2);
            });
            return res / (numbers.length - 1);
        },
        treeToPlain(rowsTree) {
            let result = [];
            _.each(rowsTree, (subgroup, key) => {
                if (!this.isSys(key)) {
                    if (subgroup.length) {
                        result = _.concat(result, subgroup);
                    } else {
                        let sub = this.treeToPlain(subgroup);
                        result = _.concat(result, sub);
                    }
                }
            });
            return result;
        },
        collapsedClick(i1, i2, i3, i4, i5, lvl) {
            switch (lvl) {
                case 0:
                    this.groupedRows['__collapsed'] = this.groupedRows['__collapsed'] ? 0 : 1;
                    break;
                case 1:
                    this.groupedRows[i1]['__collapsed'] = this.groupedRows[i1]['__collapsed'] ? 0 : 1;
                    break;
                case 2:
                    this.groupedRows[i1][i2]['__collapsed'] = this.groupedRows[i1][i2]['__collapsed'] ? 0 : 1;
                    break;
                case 3:
                    this.groupedRows[i1][i2][i3]['__collapsed'] = this.groupedRows[i1][i2][i3]['__collapsed'] ? 0 : 1;
                    break;
                case 4:
                    this.groupedRows[i1][i2][i3][i4]['__collapsed'] = this.groupedRows[i1][i2][i3][i4]['__collapsed'] ? 0 : 1;
                    break;
                case 5:
                    this.groupedRows[i1][i2][i3][i4][i5]['__collapsed'] = this.groupedRows[i1][i2][i3][i4][i5]['__collapsed'] ? 0 : 1;
                    break;
            }
        },
        //Draw
        isSys(ii) {
            return ['__len', '__subtot', '__collapsed'].indexOf(ii) > -1;
        },
        getLvlVariable(i1, i2, i3, i4, i5, lvl, key) {
            switch (lvl) {
                case 0:
                    return this.groupedRows[key];
                case 1:
                    return this.groupedRows[i1][key];
                case 2:
                    return this.groupedRows[i1][i2][key];
                case 3:
                    return this.groupedRows[i1][i2][i3][key];
                case 4:
                    return this.groupedRows[i1][i2][i3][i4][key];
                case 5:
                    return this.groupedRows[i1][i2][i3][i4][i5][key];
            }
        },
        receiveLvl(i1, i2, i3, i4, i5, lvl) {
            switch (lvl) {
                case 1:
                    return i1;
                case 2:
                    return i2;
                case 3:
                    return i3;
                case 4:
                    return i4;
                case 5:
                    return i5;
            }
        },
        //Styles
        getIndentHdr(hdrFieldId) {
            return _.find(this.activeSetts, {field_id: Number(hdrFieldId)});
        },
        localCellWidth(tableHeader, hdrFieldId) {
            if (hdrFieldId) {
                let sett = _.find(this.activeSetts, {field_id: Number(hdrFieldId)});
                return this.tdCellWidth({
                    width: sett ? sett.indent : null,
                    min_width: 10,
                    max_width: 500,
                });
            }
            return this.tdCellWidth(tableHeader);
        },
        getThStl(tableHeader) {
            let style = this.$root.themeTableHeaderBgStyle;
            let bkg = style.background;
            if (tableHeader.header_background) {
                bkg = this.$root.buildCssGradient(tableHeader.header_background) || style.background;
            }
            let text = this.tableMeta.is_system ? this.textSysStyle : this.textStyle;

            return {
                width: this.localCellWidth(tableHeader) + 'px',
                background: bkg,
                ...text,
            };
        },
        //sys methods
        inArray(item, array) {
            return $.inArray(item, array) > -1;
        },
        showSrcRecord(lnk, header, tableRow) {
            this.$emit('show-src-record', lnk, header, tableRow);
        },
        rowIndexClicked(row) {
            this.$emit('row-index-clicked', row);
        },
        rowInsertPop(tableRow, dir, copy) {
            $.LoadingOverlay('show');
            if (this.canAdd) {
                this.$emit('insert-pop-row', to_float(tableRow.row_order + dir), tableRow, copy ? tableRow : null);
            }
        },
        updatedRow(tableRow, hdr) {
            this.$emit('updated-row', tableRow, hdr);
        },
        deletedRow(tableRow) {
            $.LoadingOverlay('show');
            this.$emit('delete-row', tableRow);
        },
        showDefValPopup(tableRow, moreParam) {
            this.$emit('show-def-val-popup', tableRow, moreParam);
        },
        showAddDDLOption(tableHeader, tableRow) {
            this.$emit('show-add-ddl-option', tableHeader, tableRow);
        },
        drawFinished() {
            $.LoadingOverlay('hide');
        },
        //change order of the headers
        startChangeHdrOrder(tableHeader) {
            this.draggedHeader = tableHeader;
        },
        endChangeHdrOrder(tableHeader) {
            if (this.draggedHeader && this.draggedHeader.order !== tableHeader.order) {

                let before = _.find(this.selectedGrouping._gr_related_fields, {id: Number(this.draggedHeader.id)});
                let after = _.find(this.selectedGrouping._gr_related_fields, {id: Number(tableHeader.id)});
                if (before && after) {
                    before._grs.fld_order = after._grs.fld_order - 0.5;
                }

                if (this.user.id && !this.user.view_hash) {
                    axios.post('/ajax/addon-grouping/related-fields/reorder', {
                        table_grouping_id: this.selectedGrouping.id,
                        before_id: this.draggedHeader.id,
                        after_id: tableHeader.id,
                    }).then(({data}) => {
                        this.tableMeta._groupings = data;
                    }).catch(errors => {
                        Swal('Info', getErrors(errors));
                    }).finally(() => {
                        this.draggedHeader = null;
                    });
                }
            }
        },
        sortByField(header, direction) {
            this.$emit('sort-by-field', header, direction);
        },
        getSortLvl(tableHeader) {
            let idx = _.findIndex(this.moreSorting, {fld: tableHeader.field});
            return idx > -1 ? idx+1 : '';
        },
        keydownHandler(e) {
            if (e.keyCode == 27) {
                this.overHeader = null;
                this.$forceUpdate();
            }
        },
    },
    created() {
        this.fillHeaderRowColSpanCache(this.visibleGroups);
    },
    mounted() {
        eventBus.$on('global-keydown', this.keydownHandler);
        eventBus.$on('global-click', this.clickHandler);
    },
    beforeDestroy() {
        eventBus.$off('global-keydown', this.keydownHandler);
        eventBus.$off('global-click', this.clickHandler);
    }
}
</script>

<style lang="scss" scoped>
.grouping-wrapper {
    position: relative;

    .grouping-table {
        width: 1px;

        .table-head {
            display: table;
            width: 100%;
            table-layout: fixed;
            position: sticky;
            top: 0;
            z-index: 100;
        }

        .table-body {
            display: table;
            width: 100%;
            table-layout: fixed;
        }

        .head-content {
            text-align: center;
        }

        .multiheader-fix {
            td, th {
                height: 0;
                border: none;
                padding: 0;
            }
        }

        tr:hover {
            background-color: #f0f0f0;
        }

        th, td {
            border: 1px solid #ccc;
        }

        .th-overed {
            border-left: 2px dashed #000;
        }

        .subtotal {
            background-color: #EEE;
            font-weight: bold;
            overflow: hidden;
        }

        .group-header {
            font-weight: bold;
            padding: 5px 0;
            cursor: pointer;
            border-top: none;
            border-right: none;
            border-left: none;
            white-space: nowrap;
        }

        .lvl0, .lvl1, .lvl2, .lvl3, .lvl4 {
            border-bottom: none;
            border-top: none;
        }

        .lvl0 {
            background: #FDD;
        }

        .lvl1 {
            background: #DFD;
        }

        .lvl2 {
            background: #FFD;
        }

        .lvl3 {
            background: #DFF;
        }

        .lvl4 {
            background: #DDF;
        }
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
}
</style>