<template>
    <table class="tablda-like">
        <tr v-for="curHeaderRow in maxRowsInHeader">
            <th v-for="(tableHeader, index) in vertTableFieldObject.group"
                v-if="getMultiColspan(index, curHeaderRow, vertTableFieldObject.group)
                    && getMultiRowspan(tableHeader, curHeaderRow)"
                :rowspan="getMultiRowspan(tableHeader, curHeaderRow)"
                :colspan="getMultiColspan(index, curHeaderRow, vertTableFieldObject.group)"
                :style="{backgroundColor: tableHeader.header_background}"
                @mouseenter="(e) => { $root.showHoverTooltip(e, tableHeader) }"
                @mouseleave="$root.leaveHoverTooltip"
            >
                <div class="full-height flex flex--center flex--wrap" :style="textStyle">
                    <span class="head-content">{{ getMultiName(tableHeader, curHeaderRow) }}</span>

                    <template v-if="tableHeader.f_required && lastRow(tableHeader, curHeaderRow)">
                        <span class="required-wildcart">*</span>
                    </template>
                </div>

                <header-resizer :table-header="tableHeader"
                                :init_width="tableHeader.width"
                                :user="user"
                ></header-resizer>
            </th>
        </tr>
        <tr>
            <th v-for="tableHeader in vertTableFieldObject.group"
                v-if="tableHeader.unit || tableHeader.unit_display"
                @mouseenter="(e) => { $root.showHoverTooltip(e, tableHeader) }"
                @mouseleave="$root.leaveHoverTooltip"
            >
                <span>{{ tableHeader.unit_display || tableHeader.unit }}</span>
            </th>
        </tr>
        <tr>
            <td v-for="tableHeader in vertTableFieldObject.group"
                :key="tableHeader.id"
                :is="td"
                :global-meta="globalMeta"
                :table-meta="tableMeta"
                :settings-meta="settingsMeta"
                :table-row="tableRow || {}"
                :table-header="tableHeader"
                :cell-value="tableRow ? tableRow[tableHeader.field] : null"
                :user="user"
                :cell-height="cellHeight"
                :max-cell-rows="maxCellRows"
                :selected-cell="selectedCell"
                :is-selected="selectedCell.is_selected(tableMeta, tableHeader)"
                :row-index="-1"
                :table_id="tableMeta.id"
                :behavior="behavior"
                :ref_tb_from_refcond="ref_tb_from_refcond"
                :no_ddl_colls="no_ddl_colls"
                :is-vert-table="true"
                :with_edit="with_edit"
                :is-add-row="isAddRow"
                :class="tableHeader.f_type !== 'Boolean' ? 'edit-cell' : ''"
                @show-add-ref-cond="showAddRefCond"
                @show-src-record="showSrcRecord"
                @updated-cell="updatedCell"
                @show-add-ddl-option="showAddDDLOption"
                @show-def-val-popup="showDefValPopup"
            ></td>
        </tr>
    </table>
</template>

<script>
    import {SelectedCells} from './../../classes/SelectedCells';
    import {VerticalTableFldObject} from './VerticalTableFldObject';

    import HeaderResizer from "./Header/HeaderResizer";

    import CustomHeadCellTableData from '../CustomCell/CustomHeadCellTableData.vue';
    import CustomCellTableData from '../CustomCell/CustomCellTableData.vue';
    import CustomCellSystemTableData from '../CustomCell/CustomCellSystemTableData.vue';
    import CustomCellCorrespTableData from "../CustomCell/CustomCellCorrespTableData.vue";
    import CustomCellSettingsDisplay from '../CustomCell/CustomCellSettingsDisplay.vue';
    import CustomCellDisplayLinks from '../CustomCell/CustomCellDisplayLinks.vue';
    import CustomCellSettingsDdl from '../CustomCell/CustomCellSettingsDdl.vue';
    import CustomCellSettingsPermission from '../CustomCell/CustomCellSettingsPermission.vue';
    import CustomCellColRowGroup from '../CustomCell/CustomCellColRowGroup.vue';
    import CustomCellKanbanSett from '../CustomCell/CustomCellKanbanSett.vue';
    import CustomCellRefConds from '../CustomCell/CustomCellRefConds.vue';
    import CustomCellCondFormat from '../CustomCell/CustomCellCondFormat.vue';
    import CustomCellPlans from '../CustomCell/CustomCellPlans.vue';
    import CustomCellConnection from '../CustomCell/CustomCellConnection.vue';
    import CustomCellUserGroups from '../CustomCell/CustomCellUserGroups.vue';
    import CustomCellInvitations from '../CustomCell/CustomCellInvitations.vue';
    import CustomCellTableView from '../CustomCell/CustomCellTableView.vue';
    import CustomCellStimAppView from '../CustomCell/CustomCellStimAppView.vue';
    import CustomCellFolderView from '../CustomCell/CustomCellFolderView.vue';

    import HeaderRowColSpanMixin from './../_Mixins/HeaderRowColSpanMixin.vue';
    import CellStyleMixin from './../_Mixins/CellStyleMixin.vue';

    export default {
        name: "VerticalTableGroupedTb",
        mixins: [
            HeaderRowColSpanMixin,
            CellStyleMixin,
        ],
        components: {
            HeaderResizer,
            CustomCellTableData,
            CustomCellSystemTableData,
            CustomCellCorrespTableData,
            CustomCellSettingsDisplay,
            CustomCellDisplayLinks,
            CustomCellSettingsDdl,
            CustomCellSettingsPermission,
            CustomCellColRowGroup,
            CustomCellKanbanSett,
            CustomCellRefConds,
            CustomCellCondFormat,
            CustomCellPlans,
            CustomCellConnection,
            CustomCellUserGroups,
            CustomCellInvitations,
            CustomCellTableView,
            CustomCellStimAppView,
            CustomCellFolderView,
        },
        data: function () {
            return {
            };
        },
        inject: {
            reactive_provider: {
                from: 'reactive_provider',
                default: () => { return {} }
            }
        },
        props:{
            vertTableFieldObject: VerticalTableFldObject,

            settingsMeta: {
                type: Object,
                default: function () {
                    return {};
                }
            },
            selectedCell: SelectedCells,
            globalMeta: Object,
            tableMeta: Object,
            tableRow: Object,
            cellHeight: Number,
            maxCellRows: {
                type: Number,
                default: 0
            },
            user: Object,
            td: String,
            behavior: String,
            ref_tb_from_refcond: Object|null,
            no_ddl_colls: Array,
            isAddRow: Boolean,
            with_edit: {
                type: Boolean,
                default: true
            },
        },
        computed: {
            sub_remover() {
                return this.vertTableFieldObject.sub_header_name ? this.vertTableFieldObject.sub_header_name+',' : '';
            },
            maxRowsInHeader() {
                let max = 0;
                _.each(this.vertTableFieldObject.group, (el) => {
                    if (el.name) {
                        max = Math.max(max, this.splitName(el.name).length);
                    }
                });
                return max;
            },
        },
        watch: {
            sub_remover: {
                handler(val) {
                    this.sub_header_remover = val;
                },
                immediate: true,
            }
        },
        methods: {
            //proxies
            showSrcRecord(lnk, header, tableRow) {
                this.$emit('show-src-record', lnk, header, tableRow);
            },
            showAddRefCond(refId) {
                this.$emit('show-add-ref-cond', refId);
            },
            updatedCell(tableRow, hdr) {
                this.$emit('updated-cell', tableRow, hdr);
            },
            showDefValPopup(tableRow) {
                this.$emit('show-def-val-popup', tableRow);
            },
            showAddDDLOption(tableHeader, tableRow) {
                this.$emit('show-add-ddl-option', tableHeader, tableRow);
            },
            //for HeaderRowColSpanMixin
            isShowFieldElem(header) {
                return true;//all are already filtered
            },
        },
        mounted() {
        },
        beforeDestroy() {
        }
    }
</script>

<style lang="scss" scoped>
    @import './../CommonBlocks/TabldaLike';
    .tablda-like {
        width: 100%;
    }
</style>