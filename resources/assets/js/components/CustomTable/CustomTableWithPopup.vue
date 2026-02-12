<template>
    <div class="full-frame">
        <custom-table-v2
            v-if="use_virtual_scroll"
            :table-meta="tableMeta"
            :all-rows="allRows"
            :rows-count="allRows.length"
            :cell-height="cellHeight"
            :adding-row="addingRow"
            :user="user"
            :behavior="behavior"
            :link_popup_conditions="link_popup_conditions"
            :link_popup_tablerow="link_popup_tablerow"
            :show_rows_sum="show_rows_sum"
            :forbidden-columns="forbiddenColumns"
            :available-columns="availableColumns"
            :is-link="isLink"
            :foreign-special="foreignSpecial"
            :special_extras="special_extras"
            :visible="isVisible"
            :with_edit="with_edit"
            :external_align="external_align"
            @added-row="addedRow"
            @updated-row="updatedRow"
            @delete-row="deleteRow"
            @sort-by-field="sortByField"
            @sub-sort-by-field="subSortByField"
            @show-src-record="showSrcRecord"
            @row-index-clicked="rowIndexClicked"
            @created-object-for-add="createdObjectFoAdd"
        ></custom-table-v2>
        <custom-table
            v-else
            :cell_component_name="$root.tdCellComponent(tableMeta.is_system)"
            :table_id="tableMeta.id"
            :global-meta="globalMeta"
            :table-meta="tableMeta"
            :settings-meta="$root.settingsMeta"
            :all-rows="allRows"
            :rows-count="allRows.length"
            :cell-height="cellHeight"
            :max-cell-rows="maxCellRows"
            :adding-row="addingRow"
            :user="user"
            :behavior="behavior"
            :link_popup_conditions="link_popup_conditions"
            :link_popup_tablerow="link_popup_tablerow"
            :use_theme="true"
            :show_rows_sum="show_rows_sum"
            :forbidden-columns="forbiddenColumns"
            :available-columns="availableColumns"
            :is-link="isLink"
            :active-height-watcher="activeHeightWatcher"
            :has-float-actions="hasFloatActions"
            :foreign-special="foreignSpecial"
            :special_extras="special_extras"
            :external_align="external_align"
            :is-full-width="isFullWidth"
            :is_visible="isVisible"
            :with_edit="with_edit"
            @added-row="addedRow"
            @updated-row="updatedRow"
            @delete-row="deleteRow"
            @sort-by-field="sortByField"
            @sub-sort-by-field="subSortByField"
            @show-src-record="showSrcRecord"
            @row-index-clicked="rowIndexClicked"
            @created-object-for-add="createdObjectFoAdd"
            @total-tb-height-changed="totalTbHeightChanged"
        ></custom-table>

        <custom-edit-pop-up
            v-if="tableMeta && editPopUpRow"
            :idx="1"
            :global-meta="tableMeta"
            :table-meta="tableMeta"
            :table-row="editPopUpRow"
            :settings-meta="$root.settingsMeta"
            :role="role"
            :input_component_name="$root.tdCellComponent(tableMeta.is_system)"
            :behavior="behavior"
            :user="user"
            :cell-height="cellHeight"
            :max-cell-rows="maxCellRows"
            :with_edit="with_edit"
            :forbidden-columns="forbiddenColumns"
            :available-columns="availableColumns"
            @popup-insert="addFromPopupClicked"
            @popup-copy="copyRow"
            @popup-update="updatedRow"
            @popup-delete="deleteRow"
            @popup-close="closePopUp"
            @show-src-record="showSrcRecord"
            @another-row="anotherRowPopup"
        ></custom-edit-pop-up>
    </div>
</template>

<script>
    import {eventBus} from "../../app";

    import CustomEditPopUp from "../CustomPopup/CustomEditPopUp";
    import CustomTable from "./CustomTable";
    import BoardTable from "./BoardTable.vue";
    import CustomTableV2 from "./CustomTableV2.vue";
    import BoardView from "./BoardView.vue";

    export default {
        name: "CustomTableWithPopup",
        mixins: [
        ],
        components: {
            BoardView,
            CustomTableV2,
            BoardTable,
            CustomTable,
            CustomEditPopUp,
        },
        data: function () {
            return {
                editPopUpRow: null,
                role: 'update',
            };
        },
        props:{
            globalMeta: Object,
            tableMeta: Object,
            allRows: Object|null,
            cellHeight: Number,
            maxCellRows: {
                type: Number,
                default: 0
            },
            user: Object,
            addingRow: {
                type: Object,
                default: function () {
                    return {
                        active: false,
                        position: null
                    };
                }
            },
            forbiddenColumns: {
                type: Array,
                default: function () {
                    return [];
                }
            },
            availableColumns: Array,
            behavior: String,
            link_popup_conditions: Object|Array,
            link_popup_tablerow: Object|Array, // for LinkEmptyObjectMixin.vue
            show_rows_sum: Boolean,
            activeHeightWatcher: Boolean,
            hasFloatActions: Boolean,
            isLink: Object,
            isFullWidth: Boolean,
            isVisible: Boolean,
            use_virtual_scroll: Boolean,
            with_edit: Boolean,
            foreignSpecial: Object,
            special_extras: Object,
            external_align: String,
        },
        computed: {
        },
        methods: {
            totalTbHeightChanged(height) {
                this.$emit('total-tb-height-changed', height);
            },
            //custom table proxy
            copyRow(tableRow) {
                this.$emit('copy-row', tableRow);
            },
            addedRow(tableRow) {
                this.$emit('added-row', tableRow);
            },
            updatedRow(tableRow, hdr) {
                this.$emit('updated-row', tableRow, hdr);
            },
            deleteRow(tableRow, index) {
                this.$emit('delete-row', tableRow, index);
            },
            sortByField(tableHeader, $dir) {
                this.$emit('sort-by-field', tableHeader, $dir);
            },
            subSortByField(tableHeader, $dir) {
                this.$emit('sub-sort-by-field', tableHeader, $dir);
            },
            //sys methods
            showSrcRecord(lnk, header, tableRow) {
                this.$emit('show-src-record', lnk, header, tableRow);
            },
            rowIndexClicked(index, row) {
                this.showPopupIndex(index, row);
                this.$emit('row-index-clicked', index);
            },
            createdObjectFoAdd(tableRow) {
                this.$emit('created-object-for-add', tableRow);
            },

            //popup functions
            showPopupIndex(idx, row) {
                this.editPopUpRow = idx === -1 ? row : this.allRows[idx];
                this.role = idx === -1 ? 'add' : 'update';
            },
            closePopUp() {
                this.editPopUpRow = null;
            },
            anotherRowPopup(is_next) {
                let row_id = (this.editPopUpRow ? this.editPopUpRow.id : null);
                this.$root.anotherPopup(this.allRows, row_id, is_next, this.showPopupIndex);
            },
            addFromPopupClicked(row) {
                eventBus.$emit('add-inline-clicked', this.tableMeta.db_name, [this.behavior]);
            },
        },
        mounted() {
        },
        beforeDestroy() {
        }
    }
</script>

<style lang="scss" scoped>
</style>