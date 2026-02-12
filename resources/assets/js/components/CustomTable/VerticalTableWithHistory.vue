<template>
    <div class="flex full-height">
        <div class="flex__elem-remain">
            <div class="flex__elem__inner" style="padding-right: 5px;">
                <vertical-table
                    :td="td"
                    :global-meta="globalMeta"
                    :table-meta="tableMeta"
                    :settings-meta="settingsMeta"
                    :table-row="tableRow"
                    :user="user"
                    :cell-height="cellHeight"
                    :max-cell-rows="maxCellRows"
                    :behavior="behavior"
                    :forbidden-columns="forbiddenColumns"
                    :available-columns="availableColumns"
                    :can-see-history="canSeeHistory && !tableMeta.enabled_activities"
                    :is-add-row="isAddRow"
                    :with_edit="with_edit"
                    :active-height-watcher="activeHeightWatcher"
                    :is-link="isLink"
                    :visible="visible"
                    :can-redefine-width="canRedefineWidth"
                    @updated-cell="updatedCell"
                    @toggle-history="toggleHistory"
                    @show-add-ddl-option="showAddDDLOption"
                    @show-src-record="showSrcRecord"
                    @hist-updated="histUpdated"
                    @total-tb-height-changed="totalTbHeightChanged"
                ></vertical-table>
            </div>
        </div>

        <history-elem
            v-if="open_history"
            class="history-tab"
            :user="user"
            :table-meta="tableMeta"
            :history-header="history_header"
            :table-row="tableRow"
            :redraw_history="redraw_history"
            :can-del="true"
        ></history-elem>

        <table-activities
            v-if="tableMeta.enabled_activities"
            class="history-tab"
            :table-meta="tableMeta"
            :table-row="tableRow"
            :user="user"
            :redraw_history="redraw_history"
        ></table-activities>
    </div>
</template>

<script>
import HistoryElem from "../CommonBlocks/HistoryElem";
import TableActivities from "../CommonBlocks/TableActivities";

export default {
        name: "VerticalTableWithHistory",
        mixins: [
        ],
        components: {
            TableActivities,
            HistoryElem,
        },
        data: function () {
            return {
                redraw_history: 0,
                open_history: 0,
                history_header: null,
            };
        },
        watch: {
            'tableRow.id': function ($val) {
                this.open_history = 0;
            },
        },
        props:{
            td: String,
            settingsMeta: Object,
            globalMeta: Object,
            tableMeta: Object,
            tableRow: Object,
            user: Object,
            cellHeight: Number,
            maxCellRows: Number,
            behavior: String,
            forbiddenColumns: Array,
            availableColumns: Array,
            canSeeHistory: Boolean|Number,
            isAddRow: Boolean,
            activeHeightWatcher: Boolean,
            with_edit: {
                type: Boolean,
                default: true
            },
            isLink: Object,
            canRedefineWidth: Boolean,
            visible: Boolean,
        },
        methods: {
            totalTbHeightChanged(height) {
                this.$emit('total-tb-height-changed', height);
            },
            //proxies
            showSrcRecord(lnk, header, tableRow) {
                this.$emit('show-src-record', lnk, header, tableRow);
            },
            updatedCell(tableRow, hdr) {
                this.$emit('updated-cell', tableRow, hdr);
                this.redraw_history++;
            },
            showAddDDLOption(tableHeader, tableRow) {
                this.$emit('show-add-ddl-option', tableHeader, tableRow);
            },
            toggleHistory(tableHeader) {
                if (this.open_history !== tableHeader.id) {
                    this.open_history = tableHeader.id;
                    this.history_header = tableHeader;
                } else {
                    this.open_history = 0;
                }
                this.$emit('toggle-history', !!this.open_history);
            },
            histUpdated() {
                this.redraw_history++;
            },
        },
        mounted() {
            if (this.tableMeta.enabled_activities) {
                this.$emit('toggle-history', true);
            }
        },
        beforeDestroy() {
        }
    }
</script>

<style lang="scss" scoped>
    .history-tab {
        height: 100%;
        border: 1px solid #CCC;
        overflow: auto;
        background-color: #fff;
        border-radius: 4px;
        padding: 5px;//needed for 'Edit Popup Attachments'
        flex-basis: 300px;
        margin-left: 5px;
    }
</style>