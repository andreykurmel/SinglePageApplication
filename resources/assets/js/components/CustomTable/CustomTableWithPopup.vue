<template>
    <div class="full-frame">
        <custom-table
                :cell_component_name="$root.tdCellComponent(tableMeta.is_system)"
                :global-meta="globalMeta"
                :table-meta="tableMeta"
                :settings-meta="$root.settingsMeta"
                :all-rows="allRows"
                :rows-count="allRows.length"
                :cell-height="cellHeight"
                :max-cell-rows="maxCellRows"
                :is-full-width="true"
                :adding-row="addingRow"
                :user="user"
                :behavior="behavior"
                :del-restricted="delRestricted"
                :link_popup_conditions="link_popup_conditions"
                :link_popup_tablerow="link_popup_tablerow"
                :use_theme="true"
                :show_rows_sum="show_rows_sum"
                :forbidden-columns="forbiddenColumns"
                :available-columns="availableColumns"
                @added-row="addedRow"
                @updated-row="updatedRow"
                @delete-row="deleteRow"
                @show-src-record="showSrcRecord"
                @row-index-clicked="rowIndexClicked"
        ></custom-table>

        <custom-edit-pop-up
                v-if="tableMeta && editPopUpRow"
                :idx="1"
                :global-meta="tableMeta"
                :table-meta="tableMeta"
                :table-row="editPopUpRow"
                :settings-meta="$root.settingsMeta"
                :role="'update'"
                :input_component_name="$root.tdCellComponent(tableMeta.is_system)"
                :behavior="behavior"
                :user="user"
                :cell-height="cellHeight"
                :max-cell-rows="maxCellRows"
                :forbidden-columns="forbiddenColumns"
                :available-columns="availableColumns"
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
    import CustomEditPopUp from "../CustomPopup/CustomEditPopUp";
    import CustomTable from "./CustomTable";

    export default {
        name: "CustomTableWithPopup",
        mixins: [
        ],
        components: {
            CustomTable,
            CustomEditPopUp,
        },
        data: function () {
            return {
                editPopUpRow: null,
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
            delRestricted: Boolean,
            show_rows_sum: Boolean,
        },
        computed: {
        },
        methods: {
            //custom table proxy
            copyRow(tableRow) {
                this.addedRow(tableRow);
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
            //sys methods
            showSrcRecord(lnk, header, tableRow) {
                this.$emit('show-src-record', lnk, header, tableRow);
            },
            rowIndexClicked(index) {
                this.showPopupIndex(index);
                this.$emit('row-index-clicked', index);
            },

            //popup functions
            showPopupIndex(idx) {
                this.editPopUpRow = this.allRows[idx];
            },
            closePopUp() {
                this.editPopUpRow = null;
            },
            anotherRowPopup(is_next) {
                let row_id = (this.editPopUpRow ? this.editPopUpRow.id : null);
                this.$root.anotherPopup(this.allRows, row_id, is_next, this.showPopupIndex);
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