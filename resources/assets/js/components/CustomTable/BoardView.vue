<template>
    <div v-if="tableMeta" class="full-height relative">
        <div class="full-frame" :class="{'pb30': isPagination}">
            <div ref="scroll_wrapper" :style="{width: mainWi+'%', marginLeft: mLeft}">
                <board-table
                    :table-meta="tableMeta"
                    :all-rows="allRows"
                    :page="page"
                    :rows-count="tableMeta._view_rows_count || 0"
                    :cell-height="cellHeight"
                    :max-cell-rows="maxCellRows"
                    :full-width-cell="fullWidthCell"
                    :user="user"
                    :behavior="behavior"
                    :with-border="false"
                    :with-header="true"
                    :with_edit="with_edit"
                    :with-adding="withAdding"
                    :dcr-linked="dcrLinked"
                    :active-height-watcher="activeHeightWatcher"
                    :columns-num="columnsNum"
                    :ctlg-amount-field="ctlgAmountField"
                    :is-visible="isVisible"
                    @added-row="insertRow"
                    @updated-row="updateRow"
                    @delete-row="deleteRow"
                    @show-src-record="showSrcRecord"
                    @show-add-ddl-option="showAddDDLOption"
                    @created-object-for-add="createdObjectFoAdd"
                    @total-tb-height-changed="totalTbHeightChanged"
                    @updated-ctlg="$emit('updated-ctlg')"
                ></board-table>
            </div>
        </div>
        <!--Pagination Elements-->
        <table-pagination
            v-if="isPagination"
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
    import TablePagination from "./Pagination/TablePagination.vue";
    import BoardTable from "./BoardTable.vue";
    import VerticalTableWithHistory from "./VerticalTableWithHistory.vue";

    export default {
        name: "BoardView",
        mixins: [
        ],
        components: {
            VerticalTableWithHistory,
            BoardTable,
            TablePagination,
        },
        data: function () {
            return {
                vertScroll: false,
                horScroll: false,
            }
        },
        props: {
            tableMeta: {
                type: Object,
                required: true,
            },
            allRows: Object|null,
            user: Object,
            page: {
                type: Number,
                default: 1
            },
            with_edit: {
                type: Boolean,
                default: true
            },
            rowsCount: Number,
            fullWidthCell: Boolean,
            cellHeight: Number,
            maxCellRows: {
                type: Number,
                default: 0
            },
            dcrLinked: Object,
            behavior: String,
            withBorder: Boolean,
            isPagination: Boolean,
            withAdding: Object, //{active: true/false}
            activeHeightWatcher: Boolean,
            columnsNum: Number,
            ctlgAmountField: String,
            externalWidth: Number,
            isVisible: Boolean,
        },
        computed: {
            mainWi() {
                return Number(this.externalWidth || this.tableMeta.primary_width) || 70;
            },
            mLeft() {
                let part = 100 - this.mainWi;
                if (this.tableMeta.primary_align === 'end') {
                    return part + '%';
                }
                if (this.tableMeta.primary_align === 'center') {
                    return (part/2) + '%';
                }
                return '0';
            },
        },
        methods: {
            checkScrolls() {
                if (this.$refs.scroll_wrapper) {
                    this.vertScroll = this.$refs.scroll_wrapper.scrollHeight > this.$refs.scroll_wrapper.offsetHeight;
                    this.horScroll = this.$refs.scroll_wrapper.scrollWidth > this.$refs.scroll_wrapper.offsetWidth;
                }
            },
            insertRow(tableRow) {
                if (this.$root.setCheckRequired(this.tableMeta, tableRow)) {
                    this.$emit('added-row', tableRow);
                }
            },
            updateRow(tableRow, hdr) {
                if (this.$root.setCheckRequired(this.tableMeta, tableRow)) {
                    this.$emit('updated-row', tableRow, hdr);
                }
            },
            deleteRow(tableRow, index) {
                this.$emit('delete-row', tableRow, index);
            },
            showAddDDLOption(tableHeader, tableRow) {
                this.$emit('show-add-ddl-option', tableHeader, tableRow);
            },
            createdObjectFoAdd(tableRow) {
                this.$emit('created-object-for-add', tableRow);
            },
            showSrcRecord(lnk, header, tableRow) {
                this.$emit('show-src-record', lnk, header, tableRow);
            },
            changePage(page) {
                this.$emit('change-page', page);
            },
            totalTbHeightChanged(height) {
                this.$emit('total-tb-height-changed', height);
            },
        },
        mounted() {
        },
        beforeDestroy() {
        }
    }
</script>