<template>
    <div class="custom-table-wrapper" :class="{'flex flex--col': !!show_rows_sum, 'full-frame': !tableAutoHeight}">
        <div ref="scroll_wrapper"
             @scroll="tableScroll"
             :class="{'full-frame': !tableAutoHeight}"
             :style="{
                 overflowX: show_rows_sum ? 'hidden' : null,
                 width: show_rows_sum ? 'fit-content' : null,
             }"
        >
            <sticky-table-component
                    :tb_id="tb_id"
                    :global-meta="globalMeta"
                    :table-meta="tableMeta"
                    :settings-meta="settingsMeta"
                    :all-rows="allRows"
                    :user="user"
                    :is-full-width="isFullWidth"
                    :cell-height="cellHeight"
                    :max-cell-rows="behavior === 'list_view' ? maxCellRows : 1"
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
                    :del-restricted="delRestricted"
                    :parent-row="parentRow"
                    :headers-changer="headersChanger"
                    :link_popup_conditions="link_popup_conditions"
                    :use_theme="use_theme"
                    :no_width="no_width"
                    :is_visible="is_visible"
                    :no_height_limit="no_height_limit"

                    :widths="widths"
                    :list-view-actions="listViewActions"
                    :max-rows-in-header="maxRowsInHeader"
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
                    @show-def-val-popup="showDefValPopup"
                    @reorder-rows="reorderRows"
                    @show-header-settings="showHeaderSettings"
                    @show-add-ref-cond="showAddRefCond"
                    @col-resized="colResized"
            ></sticky-table-component>
        </div>


        <!--Sum Total Rows-->
        <rows-sum-block
                v-if="show_rows_sum && can_sum"
                :table-meta="tableMeta"
                :all-rows="allRows"
                :widths="widths"
                :list-view-actions="listViewActions"
                :is-floating-table="false"
                :has-float-columns="Boolean(floatingTableWidth)"
                :is-full-width="false"
                :cell-height="cellHeight"
                :behavior="behavior"
                :forbidden-columns="forbiddenColumns"
                :available-columns="availableColumns"
        ></rows-sum-block>


        <!--Pagination Elements-->
        <table-pagination v-if="isPagination"
                          :page="page"
                          :table-meta="tableMeta"
                          :rows-count="rowsCount"
                          @change-page="changePage"
        ></table-pagination>
    </div>
</template>

<script>
import {SelectedCells} from '../../classes/SelectedCells';

import {eventBus} from '../../app';

import StickyTableComponent from "./StickyTableComponent";
import TablePagination from "./Pagination/TablePagination.vue";
import RowsSumBlock from "../CommonBlocks/RowsSumBlock.vue";

import IsShowFieldMixin from './../_Mixins/IsShowFieldMixin.vue';
import LinkEmptyObjectMixin from './../_Mixins/LinkEmptyObjectMixin.vue';
import CanEditMixin from '../_Mixins/CanViewEditMixin.vue';
import CheckRowBackendMixin from './../_Mixins/CheckRowBackendMixin.vue';
import CellStyleMixin from './../_Mixins/CellStyleMixin.vue';
import SrvMixin from "../_Mixins/SrvMixin.vue";

export default {
        name: "CustomTable",
        mixins: [
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
                selectedCell: new SelectedCells(),
                floatingLeftPos: 0,
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
            delRestricted: Boolean,
            parentRow: Object,
            headersChanger: Object,
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

            //multiheaders functions
            maxRowsInHeader() {
                let max = 0;
                _.each(this.tableMeta._fields, (el) => {
                    if (el.name && this.isShowField(el)) {
                        max = Math.max(max, el.name.split(',').length);
                    }
                });
                return max;
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

                let fav_c = this.canRemove ? 80 : 60;
                if (this.canSrvShow) {
                    fav_c += 20;
                }

                let act_c = 55 + (this.behavior === 'invite_module' ? 45 : 0);

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
            isShowFieldElem(tableHeader) {
                return this.isShowField(tableHeader);
            },
            RedrawTable() {
                /*this.selectedCell.copy_mode = !this.selectedCell.copy_mode;
                 this.$nextTick(() => {
                 this.selectedCell.copy_mode = !this.selectedCell.copy_mode;
                 });*/
            },

            //backend autocomplete
            checkRowAutocomplete() {
                if (!this.tableMeta.is_system) {
                    this.$nextTick(() => {
                        this.checkRowOnBackend(
                            this.tableMeta.id,
                            this.curObjectForAdd,
                            this.getLinkParams(this.link_popup_conditions, this.link_popup_tablerow)
                        ).then((data) => {
                            this.$emit('backend-row-checked', this.curObjectForAdd, data); //STIM 3D APP
                        });
                    });
                }
            },

            //rows changing
            showAddDDLOption(tableHeader, tableRow) {
                this.$emit('show-add-ddl-option', tableHeader, tableRow);
            },
            insertPopRow(order, copy_row) {
                this.newObject();
                this.curObjectForAdd.row_order = order;
                if (copy_row) {
                    _.each(copy_row, (val, key) => {
                        this.curObjectForAdd[key] = val;
                    });
                }
                this.addRow(true, 'Set Default Values (DVs) for the fields with “Required” input, or use “Add” button for adding a new record.');
                this.curObjectForAdd.row_order = null;
            },
            addRow(no_new, spec_error_message) {
                if (this.$root.setCheckRequired(this.tableMeta, this.curObjectForAdd, spec_error_message)) {
                    let obj = Object.assign({}, this.curObjectForAdd);
                    this.newObject();

                    this.$emit('added-row', obj, false, no_new);
                }
                this.$forceUpdate();
            },
            updatedRow(params, hdr) {
                if (this.$root.setCheckRequired(this.tableMeta, params)) {
                    this.$emit('updated-row', params, hdr);
                }
                this.$forceUpdate();
            },
            deleteRow(tableRow, index) {
                this.$emit('delete-row', tableRow, index);
                this.$forceUpdate();
            },
            rowIndexClicked(index) {
                this.selectedCell.clear();
                this.$emit('row-index-clicked', index);
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

            //global key handler
            globalKeyHandler(e) {
                if (this.behavior === 'list_view' && e.ctrlKey && e.keyCode === 13 && this.addingRow.active) {//ctrl + 'enter' + 'active top adding row'
                    this.addRow();
                }

                if (['INPUT', 'TEXTAREA'].indexOf(e.target.nodeName) > -1) {
                    return;
                }

                if (this.selectedCell.has_row() && this.selectedCell.has_col()) {
                    if ((!this.$root.data_is_editing && !e.shiftKey) || (this.$root.data_is_editing && e.ctrlKey)) {
                        if (e.keyCode === 37) {//left arrow
                            this.selectedCell.next_col(this.tableMeta, false);
                            let sel_fld = _.find(this.tableMeta._fields, {field: this.selectedCell.get_col()}) || {};
                            $(this.$refs.scroll_wrapper).scrollLeft(this.$refs.scroll_wrapper.scrollLeft - Number(sel_fld.width));
                        }
                        if (e.keyCode === 38 && this.selectedCell.get_row() > 0) {//up arrow
                            this.selectedCell.next_row(this.allRows, false);
                            $(this.$refs.scroll_wrapper).scrollTop(this.$refs.scroll_wrapper.scrollTop - Number(this.tdCellHGT));
                        }
                        if (e.keyCode === 39) {//right arrow
                            let old_field = _.find(this.tableMeta._fields, {field: this.selectedCell.get_col()}) || {};
                            if (this.selectedCell.notFloatingOrNotFirst(this.tableMeta, old_field)) {
                                let sel_fld = _.find(this.tableMeta._fields, {field: this.selectedCell.get_col()}) || {};
                                $(this.$refs.scroll_wrapper).scrollLeft(this.$refs.scroll_wrapper.scrollLeft + Number(sel_fld.width));
                            }
                        }
                        if (e.keyCode === 40 && this.selectedCell.get_row() < (this.allRows.length - 1)) {//down arrow
                            this.selectedCell.next_row(this.allRows, true);
                            $(this.$refs.scroll_wrapper).scrollTop(this.$refs.scroll_wrapper.scrollTop + Number(this.tdCellHGT));
                        }
                    }

                    if (e.shiftKey && e.keyCode === 191) {//shift + '?'
                        this.rowIndexClicked(this.selectedCell.get_row());
                    }

                    if (this.behavior === 'list_view') {
                        if (e.ctrlKey && e.keyCode === 67) {//ctrl + 'c'
                            this.selectedCell.start_copy(this.tableMeta, this.allRows);
                        }
                        if (e.ctrlKey && e.keyCode === 86 && this.canEditSelected()) {//ctrl + 'v'
                            let sel_fld = _.find(this.tableMeta._fields, {field: this.selectedCell.get_col()}) || {};
                            if (sel_fld.f_type === 'Attachment') {
                                return;
                            } else {
                                this.pasteData();
                            }
                        }
                        if (e.ctrlKey && e.keyCode === 90 && this.canEditSelected()) {//ctrl + 'z'
                            let $rev_rows = this.$root.data_reverser.do_reverse(this.tableMeta.id);
                            this.$emit('mass-updated-rows', $rev_rows);
                        }
                        if (e.keyCode === 46 && this.canEditSelected()) {//delete
                            let $changed_rows = this.selectedCell.delete_in_selected(this.tableMeta, this.allRows);
                            this.$emit('mass-updated-rows', $changed_rows);
                        }
                        if (e.keyCode === 27) {//esc
                            this.selectedCell.clear();
                        }
                    }
                }
            },
            canEditSelected() {
                let can = true;
                let idxs = this.selectedCell.idxs(this.tableMeta);
                for (let r = idxs.row_start; r <= idxs.row_end; r++) {
                    for (let c = idxs.col_start; c <= idxs.col_end; c++) {
                        let fld = this.tableMeta._fields[c];
                        can = can && this.canEditCell(fld, this.allRows[r]);
                    }
                }
                return can;
            },
            pasteData() {
                let envs = this.selectedCell.idxs(this.tableMeta, '');
                let tocopy = this.selectedCell.idxs(this.tableMeta, 'copy_');
                let len = Math.abs(envs.row_end - envs.row_start) || Math.abs(tocopy.row_end - tocopy.row_start);
                this.$root.data_reverser.pre_change(this.allRows, envs.row_start, len);
                this.selectedCell.fill_copy(this.tableMeta, this.allRows).then(($filled_rows) => {
                    this.$root.data_reverser.after_change(this.tableMeta.id, this.allRows);
                    this.$emit('mass-updated-rows', $filled_rows);
                });
            },

            //transits
            resendAction(tableRow) {
                this.$emit('resend-action', tableRow);
            },
            showDefValPopup(tableRow) {
                this.$emit('show-def-val-popup', tableRow);
            },
            reorderRows() {
                this.$emit('reorder-rows');
            },

            //scrolls
            tableScroll() {
                this.floatingLeftPos = this.$refs.scroll_wrapper.scrollLeft;
            },

            //events
            addInlineClickedHandler() {
                if (this.inArray(this.behavior, ['list_view', 'request_view'])) {
                    this.addRow();
                }
            },

            newObject() {
                this.createObjectForAdd();
                this.checkRowAutocomplete();
            },
        },
        mounted() {
            eventBus.$on('global-keydown', this.globalKeyHandler);
            eventBus.$on('add-inline-clicked', this.addInlineClickedHandler);
        },
        beforeDestroy() {
            eventBus.$off('global-keydown', this.globalKeyHandler);
            eventBus.$off('add-inline-clicked', this.addInlineClickedHandler);
        }
    }
</script>

<style lang="scss" scoped>
    @import "./CustomTable.scss";
</style>