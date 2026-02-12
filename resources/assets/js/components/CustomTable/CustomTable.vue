<template>
    <div class="custom-table-wrapper" :class="{'flex flex--col': !!show_rows_sum, 'full-frame': !tableAutoHeight}">
        <div ref="scroll_wrapper"
             @scroll="tableScroll"
             @click.self="unselectCell"
             :class="{'full-frame': !tableAutoHeight}"
             :style="{
                 overflowX: show_rows_sum ? 'hidden' : null,
             }"
        >
            <sticky-table-component
                v-if="canDraw"
                :tb_id="tb_id"
                :global-meta="globalMeta"
                :table-meta="tableMeta"
                :settings-meta="settingsMeta"
                :all-rows="allRows"
                :user="user"
                :is-full-width="isFullWidth"
                :cell-height="cellHeight"
                :max-cell-rows="maxCellRows"
                :page="page"
                :rows-count="rowsCount"
                :cell_component_name="cell_component_name"
                :behavior="behavior"
                :sort="sort"
                :forbidden-columns="forbiddenColumns"
                :available-columns="availableColumns"
                :adding-row="addingRow"
                :selected-row="selectedRow"
                :condition-array="conditionArray"
                :headers-with-check="headersWithCheck"
                :ref_tb_from_refcond="ref_tb_from_refcond"
                :with_edit="with_edit"
                :table_id="table_id"
                :excluded_row_values="excluded_row_values"
                :parent-row="parentRow"
                :headers-changer="headersChanger"
                :required-changer="requiredChanger"
                :link_popup_conditions="link_popup_conditions"
                :use_theme="use_theme"
                :no_width="no_width"
                :is_visible="is_visible"
                :no_height_limit="no_height_limit"
                :foreign-special="foreignSpecial"
                :special_extras="special_extras"
                :link_popup_tablerow="link_popup_tablerow"
                :is-link="isLink"
                :active-height-watcher="activeHeightWatcher"
                :has-float-actions="hasFloatActions"
                :scroll-view="scrollView"
                :external_align="external_align"

                :widths="widths"
                :list-view-actions="listViewActions"
                :selected-cell="selectedCell"
                :object-for-add="curObjectForAdd"

                @show-add-ddl-option="showAddDDLOption"
                @insert-pop-row="insertPopRow"
                @added-row="addRow"
                @updated-row="updatedRow"
                @delete-row="deleteRow"
                @delete-selected-rows="emitDeleteSelected"
                @row-index-clicked="rowIndexClicked"
                @check-row="checkClicked"
                @row-selected="rowSelected"
                @radio-checked="radioChecked"
                @sort-by-field="sortByField"
                @sub-sort-by-field="subSortByField"
                @toggle-favorite-row="toggleFavoriteRow"
                @toggle-all-favorites="toggleAllFavorites"
                @show-src-record="showSrcRecord"
                @resend-action="resendAction"
                @call-back="sendCallBack"
                @show-def-val-popup="showDefValPopup"
                @reorder-rows="reorderRows"
                @show-header-settings="showHeaderSettings"
                @show-add-ref-cond="showAddRefCond"
                @col-resized="colResized"
                @total-tb-height-changed="totalTbHeightChanged"
                @redraw-table="RedrawTable"
            ></sticky-table-component>
        </div>


        <!--Sum Total Rows-->
        <div class="sum_tot_wrap"
             ref="sum_tot_wrap"
             @scroll="sumTotWrapScroll"
             :style="{overflowY: vertScroll ? 'scroll' : 'auto'}"
        >
            <rows-sum-block
                v-if="show_rows_sum && can_sum"
                :global-meta="globalMeta"
                :table-meta="tableMeta"
                :all-rows="allRows"
                :widths="widths"
                :list-view-actions="listViewActions"
                :is-floating-table="false"
                :is-full-width="isFullWidth"
                :cell-height="cellHeight"
                :behavior="behavior"
                :is-link="isLink"
                :special_extras="special_extras"
                :external_align="external_align"
                :has-float-actions="hasFloatActions"
                :forbidden-columns="forbiddenColumns"
                :available-columns="availableColumns"
            ></rows-sum-block>
        </div>


        <!--Pagination Elements-->
        <table-pagination v-if="isPagination"
                          :page="page"
                          :table-meta="tableMeta"
                          :rows-count="rowsCount"
                          :vert-scroll="vertScroll"
                          :hor-scroll="horScroll"
                          @change-page="changePage"
        ></table-pagination>
    </div>
</template>

<script>
import {JsFomulaParser} from "../../classes/JsFomulaParser";

import {eventBus} from '../../app';

import StickyTableComponent from "./StickyTableComponent";
import TablePagination from "./Pagination/TablePagination.vue";
import RowsSumBlock from "../CommonBlocks/RowsSumBlock.vue";

import SelectedCellMixin from './../_Mixins/SelectedCellMixin.vue';
import IsShowFieldMixin from './../_Mixins/IsShowFieldMixin.vue';
import LinkEmptyObjectMixin from './../_Mixins/LinkEmptyObjectMixin.vue';
import CanEditMixin from '../_Mixins/CanViewEditMixin.vue';
import CheckRowBackendMixin from './../_Mixins/CheckRowBackendMixin.vue';
import CellStyleMixin from './../_Mixins/CellStyleMixin.vue';
import SrvMixin from "../_Mixins/SrvMixin.vue";

export default {
        name: "CustomTable",
        mixins: [
            SelectedCellMixin,
            IsShowFieldMixin,
            LinkEmptyObjectMixin,
            CanEditMixin,
            CheckRowBackendMixin,
            CellStyleMixin,
            SrvMixin,
        ],
        components: {
            StickyTableComponent,
            RowsSumBlock,
            TablePagination,
        },
        data: function () {
            return {
                canDraw: true,
                vertScroll: false,
                horScroll: false,
                scrollView: {
                    left: 0,
                    right: 0,
                    top: 0,
                    bottom: 0,
                },
                can_sum: true,
            }
        },
        props:{
            tb_id: String,
            globalMeta: Object,
            tableMeta: {
                type: Object,
                required: true,
            },
            settingsMeta: {
                type: Object,
                default: function () {
                    return {};
                }
            },
            allRows: Object|null,
            user: Object,
            isFullWidth: Boolean,
            fullWidthCell: Boolean,
            isPagination: Boolean,
            cellHeight: Number,
            maxCellRows: {
                type: Number,
                default: 0
            },
            page: {
                type: Number,
                default: 1
            },
            rowsCount: Number,
            cell_component_name: String,
            behavior: {
                type: String,
                default: 'list_view'
            },
            sort: {
                type: Array,
                default: function () {
                    return [];
                }
            },
            forbiddenColumns: {
                type: Array,
                default: function () {
                    return [];
                }
            },
            availableColumns: Array,
            addingRow: {
                type: Object,
                default: function () {
                    return {
                        active: false,
                        position: null
                    };
                }
            },
            selectedRow: {
                type: Number,
                default: -1
            },
            conditionArray: {
                type: Array|null,
                default: null
            },
            headersWithCheck: {
                type: Array,
                default: function () {
                    return [];
                }
            },
            ref_tb_from_refcond: Object|null,
            with_edit: {
                type: Boolean,
                default: true
            },
            table_id: Number,
            //array of objects: [{ field: 'some_row_field', excluded: ['val1', 'val4', 'val56', ...] }, ...]
            excluded_row_values: {
                type: Array,
                default: function () {
                    return [];
                }
            },
            parentRow: Object,
            headersChanger: Object,
            requiredChanger: Array,
            redraw_table: Number,
            link_popup_conditions: Object|Array,
            link_popup_tablerow: Object|Array, // for LinkEmptyObjectMixin.vue
            use_theme: Boolean,
            show_rows_sum: Boolean,
            no_width: Boolean,
            no_height_limit: Boolean,
            widths_div: Number|Object,
            externalObjectForAdd: Object,
            is_visible: Boolean,
            tableAutoHeight: Boolean,
            foreignSpecial: Object,
            special_extras: Object,
            activeHeightWatcher: Boolean,
            hasFloatActions: Boolean,
            external_align: String,
            isLink: Object,//CanViewEditMixin.vue
        },
        computed: {
            floatingTableWidth() {
                let width = 0;
                if (this.behavior !== 'link_popup') {
                    _.each(this.tableMeta._fields, (hdr) => {
                        if (hdr.is_floating && this.isShowField(hdr)) {
                            width += this.tdCellWidth(hdr);
                        }
                    });
                }
                return width;
            },

            //
            listViewActions() {
                let res = this.inArray(this.behavior, ['list_view','favorite','request_view']);
                return Boolean(res);
            },

            //calc widths for system columns
            countStrLen() {
                return this.tableMeta.rows_per_page
                    ? String(this.page * this.tableMeta.rows_per_page).length - 1
                    : String(this.rowsCount).length - 1;
            },
            widths() {
                if (typeof this.widths_div === 'object') {
                    return this.widths_div;
                }

                let index_c = this.tableMeta.is_system == 1
                    ? 35
                    : 45 + (this.countStrLen * 6);

                let fz = Number(this.themeTextFontSize) * (this.themeTextFontFamily === 'monospace' ? 2 : 1.5);
                let fav_c = 2*fz + 25;
                if (this.canRemove) {
                    fav_c += fz;
                }
                if (this.canSrvShow) {
                    fav_c += fz;
                }

                let act_c = 55 + (this.behavior === 'invite_module' ? 65 : 0);

                if (this.widths_div) {
                    index_c /= this.widths_div;
                    fav_c /= this.widths_div;
                    act_c /= this.widths_div;
                }
                return {
                    index_col: index_c,
                    favorite_col: fav_c,
                    action_col: act_c,
                }
            },
            canSrvShow() {
                return !this.$root.inArray('i_srv', this.forbiddenColumns)
                    && this.canSRV(this.tableMeta);
            },
            canRemove() {
                return !this.$root.inArray('i_remove', this.forbiddenColumns) && this.canDelete;
            },
            //adding object
            curObjectForAdd() {
                return this.externalObjectForAdd || this.objectForAdd;
            },
        },
        watch: {
            table_id: function (val) {
                this.newObject();
            },
            redraw_table(val) {
                //apply changed CondFormats after changing the ROW.
                this.RedrawTable();
            },
            addingRow:{
                handler(row) {
                    if (row && row.active) {
                        this.newObject();
                    }
                },
                deep: true,
                immediate: true,
            },
        },
        methods: {
            sumTotWrapScroll(eve) {
                this.$refs.scroll_wrapper.scrollLeft = eve.target.scrollLeft;
            },
            isShowFieldElem(tableHeader) {
                return this.isShowField(tableHeader);
            },
            RedrawTable() {
                this.canDraw = false;
                this.$nextTick(() => {
                    this.canDraw = true;
                });
            },
            checkScrolls() {
                if (this.$refs.scroll_wrapper) {
                    this.vertScroll = this.$refs.scroll_wrapper.scrollHeight > this.$refs.scroll_wrapper.offsetHeight;
                    this.horScroll = this.$refs.scroll_wrapper.scrollWidth > this.$refs.scroll_wrapper.offsetWidth;
                }
            },

            //backend autocomplete
            checkRowAutocomplete() {
                if (!this.tableMeta.is_system) {
                    this.$nextTick(() => {
                        let promise = this.checkRowOnBackend(
                            this.tableMeta.id,
                            this.curObjectForAdd,
                            this.getLinkParams(this.link_popup_conditions, this.link_popup_tablerow),
                            this.foreignSpecial
                        );
                        if (promise) {
                            promise.then((data) => {
                                this.$emit('backend-row-checked', this.curObjectForAdd, data); //STIM 3D APP
                            });
                        }
                    });
                }
            },

            //rows changing
            showAddDDLOption(tableHeader, tableRow) {
                this.$emit('show-add-ddl-option', tableHeader, tableRow);
            },
            popupCopy(copy_row) {
                if (this.$root.setCheckRequired(this.tableMeta, copy_row)) {
                    this.$emit('copy-row', copy_row);
                }
            },
            insertPopRow(order, selected_row, copy_row) {
                if (copy_row) {
                    this.popupCopy(copy_row);
                } else {
                    this.newObject();
                    this.curObjectForAdd.row_order = order;
                    this.addRow(selected_row, 'Set Default Values (DVs) for the fields with “Required” input, or use “Add” button for adding a new record.');
                    this.curObjectForAdd.row_order = null;
                }
            },
            addRow(selected_row, spec_error_message) {
                if (this.$root.setCheckRequired(this.tableMeta, this.curObjectForAdd, spec_error_message)) {
                    let obj = Object.assign({}, this.curObjectForAdd);
                    this.newObject();
                    this.$emit('added-row', obj, false, selected_row);
                }
                this.$forceUpdate();
            },
            updatedRow(row, hdr) {
                if (this.$root.setCheckRequired(this.tableMeta, row)) {
                    this.$emit('updated-row', row, hdr);
                }
                this.$forceUpdate();
            },
            deleteRow(tableRow, index) {
                this.$emit('delete-row', tableRow, index);
                this.$forceUpdate();
            },
            rowIndexClicked(index, row) {
                this.selectedCell.clear();
                this.$emit('row-index-clicked', index, row);
            },
            radioChecked(index) {
                this.$emit('radio-checked', index);
            },
            showHeaderSettings(header) {
                this.$emit('show-header-settings', header);
            },
            showAddRefCond(refId) {
                this.$emit('show-add-ref-cond', refId);
            },
            totalTbHeightChanged(height) {
                if (this.show_rows_sum && this.can_sum && this.$refs.sum_tot_wrap) {
                    height += to_float(this.$refs.sum_tot_wrap.clientHeight);
                }
                this.$emit('total-tb-height-changed', height);
            },
            colResized() {
                this.can_sum = false;
                this.$nextTick(() => {
                    this.can_sum = true;
                });
            },

            //pagination
            changePage(page) {
                this.$emit('change-page', page);
            },

            //additional functions
            checkClicked(type, status, arr) {
                this.$emit('check-row', type, status, arr);
            },
            rowSelected() {
                this.$emit('row-selected');
            },
            sortByField(tableHeader, $dir) {
                this.$emit('sort-by-field', tableHeader, $dir);
            },
            subSortByField(tableHeader, $dir) {
                this.$emit('sub-sort-by-field', tableHeader, $dir);
            },

            //change favorite rows
            toggleFavoriteRow(tableRow) {
                this.$set(tableRow, '_is_favorite', !tableRow._is_favorite);
                this.$emit('toggle-favorite-row', tableRow);
            },
            toggleAllFavorites(status) {
                this.$emit('toggle-all-favorites', status);
            },

            emitDeleteSelected() {
                this.$emit('delete-selected-rows');
            },

            //src record and tables function
            showSrcRecord(lnk, header, tableRow) {
                this.$emit('show-src-record', lnk, header, tableRow);
            },

            //transits
            resendAction(tableRow) {
                this.$emit('resend-action', tableRow);
            },
            sendCallBack(tableRow) {
                this.$emit('call-back', tableRow);
            },
            showDefValPopup(tableRow, moreParam) {
                this.$emit('show-def-val-popup', tableRow, moreParam);
            },
            reorderRows() {
                this.$emit('reorder-rows');
            },

            //scrolls
            tableScroll() {
                if (this.listViewActions) {
                    this.scrollView.left = this.$refs.scroll_wrapper.scrollLeft;
                    this.scrollView.right = this.$refs.scroll_wrapper.scrollLeft + this.$refs.scroll_wrapper.clientWidth;
                    this.scrollView.top = this.$refs.scroll_wrapper.scrollTop;
                    this.scrollView.bottom = this.$refs.scroll_wrapper.scrollTop + this.$refs.scroll_wrapper.clientHeight;
                }
            },

            //events
            addInlineClickedHandler(db_name, behaviors) {
                let avail = !db_name || db_name === this.tableMeta.db_name;
                if (avail && this.is_visible && this.inArray(this.behavior, behaviors || ['list_view', 'request_view'])) {
                    this.addRow();
                }
            },

            newObject() {
                this.createObjectForAdd();
                this.checkRowAutocomplete();
            },
            calculateAllTable(tbid, headerId, rowFormula) {
                if (tbid === this.tableMeta.id) {
                    _.each(this.allRows, (row) => {
                        _.each(this.tableMeta._fields, (fld) => {
                            if (fld.id == headerId) {
                                row[fld.field+'_formula'] = rowFormula;
                            }
                        });
                        JsFomulaParser.checkRowAndCalculate(this.tableMeta, row);
                    });
                }
            },
        },
        mounted() {
            if (this.inArray(this.behavior, ['link_popup','list_view','favorite','request_view','cond_format_overview'])) {
                setInterval(() => {
                    this.checkScrolls();
                }, 1500);
            }
            this.tableScroll();

            eventBus.$on('global-click', this.unselectCellOutside);
            eventBus.$on('global-keydown', this.globalKeyHandler);
            eventBus.$on('add-inline-clicked', this.addInlineClickedHandler);
            eventBus.$on('table-formulas-recalculate', this.calculateAllTable);
        },
        beforeDestroy() {
            eventBus.$off('global-click', this.unselectCellOutside);
            eventBus.$off('global-keydown', this.globalKeyHandler);
            eventBus.$off('add-inline-clicked', this.addInlineClickedHandler);
            eventBus.$off('table-formulas-recalculate', this.calculateAllTable);
        }
    }
</script>

<style lang="scss" scoped>
    @import "./CustomTable.scss";

    .sum_tot_wrap {
        position: relative;
        overflow-x: auto;
        flex-shrink: 0;
    }
</style>