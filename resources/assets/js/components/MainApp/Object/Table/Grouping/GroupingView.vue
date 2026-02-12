<template>
    <div v-if="tableMeta && requestParams && tableRows" class="full-height relative">
        <div class="full-frame">
            <grouping-table
                :cell_component_name="$root.tdCellComponent(tableMeta.is_system)"
                :selected-grouping="selectedGrouping"
                :table-meta="tableMeta"
                :grouped-rows="groupedRows"
                :sorted-keys="sortedKeys"
                :total-count="tableRows.length"
                :page="requestParams.page"
                :cell-height="$root.cellHeight"
                :max-cell-rows="$root.maxCellRows"
                :user="$root.user"
                :with_edit="canEdit"
                :is-visible="isVisible"
                :visible-groups="visibleGroups"
                :more-sorting="moreSorting"
                @updated-row="updateRow"
                @delete-row="deleteRow"
                @show-src-record="showSrcRecord"
                @row-index-clicked="showPopIdx"
                @insert-pop-row="insertRowPopup"
                @sort-by-field="applySorting"
            ></grouping-table>
        </div>
        <!--Pagination Elements-->
        <table-pagination
            v-if="selectedGrouping.rg_data_range == '0' && requestParams"
            :page="requestParams.page"
            :table-meta="tableMeta"
            :rows-count="tableMeta._view_rows_count || 0"
            @change-page="changePg"
        ></table-pagination>

        <!--Popup-->
        <custom-edit-pop-up
            v-if="tableMeta && editPopupRow"
            :global-meta="tableMeta"
            :table-meta="tableMeta"
            :table-row="editPopupRow"
            :settings-meta="$root.settingsMeta"
            :role="editPopupRole"
            :input_component_name="$root.tdCellComponent(tableMeta.is_system)"
            :behavior="'list_view'"
            :user="$root.user"
            :cell-height="1"
            :max-cell-rows="0"
            :with_edit="canEdit"
            @popup-insert="insertRow"
            @popup-update="updateRow"
            @popup-copy="copyRow"
            @popup-delete="deleteRow"
            @popup-close="closePopUp"
            @show-src-record="showSrcRecord"
            @another-row="anotherRowPopup"
        ></custom-edit-pop-up>
    </div>
</template>

<script>
    import {eventBus} from "../../../../../app";

    import {ChartFunctions} from "../ChartAddon/ChartFunctions";
    import {SpecialFuncs} from "../../../../../classes/SpecialFuncs";

    import CellStyleMixin from "../../../../_Mixins/CellStyleMixin";
    import MixinForAddons from "./../MixinForAddons";

    import TablePagination from "../../../../CustomTable/Pagination/TablePagination.vue";
    import GroupingTable from "../../../../CustomTable/GroupingTable.vue";
    import CustomEditPopUp from "../../../../CustomPopup/CustomEditPopUp.vue";
    import CustomTable from "../../../../CustomTable/CustomTable.vue";

    export default {
        name: "GroupingView",
        components: {
            CustomTable,
            CustomEditPopUp,
            GroupingTable,
            TablePagination,
        },
        mixins: [
            CellStyleMixin,
            MixinForAddons,
        ],
        data: function () {
            return {
                moreSorting: [],
                requestAdditionals: null,
                behavior: 'grouping_table',
                redrawOnEdit: true,
                groupedRows: [],
                visibleGroups: [],
                sortedKeys: {
                    0: [''],
                    1: [''],
                    2: [''],
                    3: [''],
                    4: [''],
                },
                wait_for_loading: true,
            }
        },
        props: {
            tableMeta: Object,
            requestParams: Object,//MixinForAddon
            currentPageRows: Array,//MixinForAddon
            selectedGrouping: Object,
            add_click: Number,
            isVisible: Boolean,
        },
        computed: {
            canEdit() {
                return this.$root.AddonAvailableToUser(this.tableMeta, 'grouping', 'edit');
            },
            activeSettings() {
                return _.filter(this.selectedGrouping._settings, (st) => { return st.rg_active; });
            },
        },
        watch: {
            isVisible: {
                handler(val) {
                    if (val) {
                        if (this.wait_for_loading) {
                            this.mountedFunc();
                        }
                        this.wait_for_loading = false;
                    }
                },
                immediate: true,
            },
        },
        methods: {
            canPermisEdit() {
                return this.$root.addonCanPermisEdit(this.tableMeta, this.selectedGroup, '_group_rights');
            },
            canAddonEdit() {
                return this.$root.addonCanEditGeneral(this.tableMeta, 'group');
            },

            //popup
            anotherRowPopup(is_next) {
                let row_id = (this.editPopupRow ? this.editPopupRow.id : null);
                this.$root.anotherPopup(this.tableRows, row_id, is_next, this.showPopIdx);
            },
            showPopIdx(idx) {
                if (typeof idx === 'object') {
                    idx = _.findIndex(this.tableRows, {id: Number(idx.id)});
                }
                this.editPopupRow = this.tableRows[idx];
                this.editPopupRole = 'update';
            },
            insertRowPopup(order, selected_row, copy_row) {
                if (copy_row) {
                    this.copyRow(copy_row);
                } else {
                    let row = SpecialFuncs.emptyRow(this.tableMeta);
                    row.row_order = order;
                    _.each(this.activeSettings, (sett) => {
                        let fld = _.find(this.tableMeta._fields, {id: Number(sett.field_id)});
                        if (fld) {
                            row[fld.field] = selected_row[fld.field];
                        }
                    });

                    if (this.$root.setCheckRequired(this.tableMeta, row, 'Set Default Values (DVs) for the fields with “Required” input, or use “Add” button for adding a new record.')) {
                        this.insertRow(row, selected_row);
                    }
                }
            },
            applySorting(header, direction) {
                this.moreSorting = [{ field: header.field, direction: direction }];
                this.mountedFunc();
            },

            //Draw and mount
            changePg(page) {
                $.LoadingOverlay('show');
                eventBus.$emit('changed-page', page);
            },
            drawAddon(type) {//Needed for MixinForAddons.vue
                if (type === 'update') {
                    return;
                }

                if (type === 'insert') {
                    this.tableRows = null;
                    this.$nextTick(() => {
                        this.mountedFunc();
                    });
                } else {
                    this.groupedRows = this.buildGroupedRows(this.tableRows);
                    this.buildSortedKeys();
                }
            },
            buildSortedKeys() {
                this.visibleGroups = [];
                for (let i = 0; i < ChartFunctions.maxLVL(); i++) {
                    let sett = this.activeSettings[i] || {};
                    let fld = _.find(this.tableMeta._fields, {id: Number(sett.field_id)});
                    if (fld) {
                        this.visibleGroups[i] = fld;
                        this.sortedKeys[i] = _.uniq(_.map(this.tableRows, fld.field));
                        if (sett.sorting) {
                            this.sortedKeys[i] = this.sortedKeys[i].sort((a, b) => {
                                if (sett.sorting === 'desc') {
                                    return String(a).toLowerCase() < String(b).toLowerCase() ? 1
                                        : String(a).toLowerCase() > String(b).toLowerCase() ? -1 : 0;
                                } else {
                                    return String(a).toLowerCase() > String(b).toLowerCase() ? 1
                                        : String(a).toLowerCase() < String(b).toLowerCase() ? -1 : 0;
                                }
                            });
                        }
                    }
                }
            },
            buildGroupedRows(rows, level = 0) {
                if (level >= ChartFunctions.maxLVL()) {
                    return {
                        rows: rows,
                        __len: rows.length,
                        __subtot: 0,//1,
                        __collapsed: 0,
                    };
                }
                let sett = this.activeSettings[level] || {};
                let fld = _.find(this.tableMeta._fields, {id: Number(sett.field_id)});
                let grouped = fld ? _.groupBy(rows, fld.field) : {'': rows};
                let counter = 0;
                _.each(grouped, (groups, i) => {
                    grouped[i] = this.buildGroupedRows(groups, level+1);
                    grouped[i]['__collapsed'] = sett.default_state === 'collapsed' ? 1 : 0;
                    counter += grouped[i]['__len'];
                });
                grouped['__len'] = counter;
                grouped['__subtot'] = 0;//fld ? 1 : 0; //NOTE: needed for bottom sub-totals
                grouped['__collapsed'] = 0;
                return grouped;
            },
            setAdditionals() {
                let $sort = [];
                _.each(this.activeSettings, (sett) => {
                    let fld = _.find(this.tableMeta._fields, {id: Number(sett.field_id)});
                    $sort.push({ 'field': fld.field, 'direction': sett.sorting });
                });
                _.each(this.moreSorting, (sort) => {
                    $sort.push({ 'field': sort.field, 'direction': sort.direction });
                });
                this.requestAdditionals = { 'sort': $sort };

                if (!this.selectedGrouping.rg_data_range || this.selectedGrouping.rg_data_range === '0') {
                    this.moreSorting = this.requestParams.sort;
                }
            },
            mountedFunc() {
                if (this.isVisible) {
                    this.setAdditionals();
                    this.getRows(this.selectedGrouping.rg_data_range, 'grouping', this.selectedGrouping.id, this.requestAdditionals);
                } else {
                    this.wait_for_loading = true;
                }
            },
            newParamsHandler(key, from, listView) {
                if (from === 'update' && !listView) {
                    return;
                }
                if (from === 'insert') {
                    this.tableRows = null;
                }
                this.$nextTick(() => {
                    this.mountedFunc();
                });
            },
        },
        mounted() {
            this.mountedFunc();
            eventBus.$on('new-request-params', this.newParamsHandler);
        },
        beforeDestroy() {
            eventBus.$off('new-request-params', this.newParamsHandler);
        }
    }
</script>

<style lang="scss" scoped>
    @import "./../SettingsModule/TabSettingsPermissions";
</style>