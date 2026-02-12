<template>
    <div class="pt15" :style="{height: totalHgt}">

        <template v-if="linkedTbMeta && loadedRows && linkedRowsObject[dcrLinkedTable.linked_table_id]">

            <div :style="{height: minimalHeight ? minimalHeight+'px' : 'auto'}">
                <i v-if="currentTableAlign === 'start' && popupViewType === viewTypeTable" class="fas fa-align-left left-top" @click="currentTableAlign = 'center'"></i>
                <i v-if="currentTableAlign === 'center' && popupViewType === viewTypeTable" class="fas fa-align-center left-top" @click="currentTableAlign = 'end'"></i>
                <i v-if="currentTableAlign === 'end' && popupViewType === viewTypeTable" class="fas fa-align-right left-top" @click="currentTableAlign = 'start'"></i>

                <div
                    v-if="viewsAreAvail(dcrLinkedTable)"
                    class="header-btn"
                    :style="{right: ctlgHasPopup ? '20px' : '0'}"
                    @mouseenter="show_vtype = true"
                    @mouseleave="show_vtype = false"
                >
                    <i class="fas pull-right"
                       :title="viewTypeTitle()"
                       :class="viewTypeIcon()"
                       :style="{marginLeft: '3px'}"
                    ></i>
                    <div v-if="show_vtype" class="view-type-wrapper">
                        <i v-for="type in allTypes(dcrLinkedTable)"
                           v-if="popupViewType != type"
                           class="fas pull-right"
                           :class="viewTypeIcon(type)"
                           :title="viewTypeTooltip(type)"
                           @click="popupViewType = type"
                        ></i>
                    </div>
                </div>

                <div v-if="ctlgHasPopup" class="header-btn" style="top: -5px;">
                    <i class="fa fa-shopping-cart" @click="catalogPopupShow = true"></i>
                </div>

                <listing-view
                    v-if="popupViewType === viewTypeListing"
                    :table-meta="linkedTbMeta"
                    :all-rows="linkedRowsObject[dcrLinkedTable.linked_table_id]"
                    :user="$root.user"
                    :rows-count="linkedRowsObject[dcrLinkedTable.linked_table_id].length"
                    :cell-height="$root.cellHeight"
                    :max-cell-rows="$root.maxCellRows"
                    :is-full-width="true"
                    :behavior="'dcr_linked_tb'"
                    :with_edit="with_edit"
                    :with-border="false"
                    :dcr-linked="dcrLinkedTable"
                    :with-adding="addingRow"
                    :active-height-watcher="dcrLinkedTable.max_height_inline_embd < 200"
                    :is-visible="isVisible"
                    @added-row="insertLinked"
                    @updated-row="updateLinked"
                    @delete-row="deleteLinked"
                    @show-src-record="showSrcRecord"
                    @created-object-for-add="fillLinkedParam"
                    @total-tb-height-changed="totalTbHeightChanged"
                ></listing-view>
                <board-view
                    v-if="popupViewType === viewTypeBoards"
                    :table-meta="linkedTbMeta"
                    :all-rows="linkedRowsObject[dcrLinkedTable.linked_table_id]"
                    :user="$root.user"
                    :rows-count="linkedRowsObject[dcrLinkedTable.linked_table_id].length"
                    :cell-height="$root.cellHeight"
                    :max-cell-rows="$root.maxCellRows"
                    :is-full-width="true"
                    :behavior="'dcr_linked_tb'"
                    :with_edit="with_edit"
                    :with-border="false"
                    :with-adding="addingRow"
                    :dcr-linked="dcrLinkedTable"
                    :is-visible="isVisible"
                    :active-height-watcher="dcrLinkedTable.max_height_inline_embd < 200"
                    @added-row="insertLinked"
                    @updated-row="updateLinked"
                    @delete-row="deleteLinked"
                    @show-src-record="showSrcRecord"
                    @created-object-for-add="fillLinkedParam"
                    @total-tb-height-changed="totalTbHeightChanged"
                ></board-view>
                <custom-table-with-popup
                    v-if="popupViewType === viewTypeTable"
                    :global-meta="linkedTbMeta"
                    :table-meta="linkedTbMeta"
                    :settings-meta="$root.settingsMeta"
                    :all-rows="linkedRowsObject[dcrLinkedTable.linked_table_id]"
                    :rows-count="linkedRowsObject[dcrLinkedTable.linked_table_id].length"
                    :cell-height="1"
                    :max-cell-rows="0"
                    :user="$root.user"
                    :with_edit="with_edit"
                    :behavior="'dcr_linked_tb'"
                    :adding-row="addingRow"
                    :foreign-special="foreSpec"
                    :is-full-width="!!dcrLinkedTable.embd_fit_width"
                    :show_rows_sum="!!dcrLinkedTable.embd_stats"
                    :has-float-actions="!!dcrLinkedTable.embd_float_actions"
                    :active-height-watcher="dcrLinkedTable.max_height_inline_embd < 200"
                    :special_extras="specialExtras"
                    :external_align="currentTableAlign"
                    :is-visible="isVisible"
                    @added-row="insertLinked"
                    @updated-row="updateLinked"
                    @delete-row="deleteLinked"
                    @show-src-record="showSrcRecord"
                    @created-object-for-add="fillLinkedParam"
                    @total-tb-height-changed="totalTbHeightChanged"
                ></custom-table-with-popup>
            </div>

            <div
                v-if="loadedCatalog && catalogMeta && catalogRows && dcrLinkedTable.ctlg_display_option == 'inline'"
                :style="{height: ctlgHeight ? ctlgHeight+'px' : 'auto'}"
            >
                <filters-block
                    v-if="hasCtlgFilters"
                    :table-meta="catalogMeta"
                    :input_filters="catalogFilters"
                    :fixed-pos="true"
                    :absolute-state="true"
                    :ignore-can-change="true"
                    :placed="'top_filters'"
                    :style="{justifyContent: 'center', height: '38px', paddingTop: '6px'}"
                    @changed-filter="ctlgFilter()"
                ></filters-block>
                <board-view
                    :table-meta="catalogMeta"
                    :all-rows="catalogRows"
                    :user="$root.user"
                    :rows-count="catalogRows.length"
                    :cell-height="$root.cellHeight"
                    :max-cell-rows="$root.maxCellRows"
                    :is-full-width="true"
                    :behavior="'dcr_linked_tb'"
                    :with_edit="false"
                    :with-border="false"
                    :with-adding="{active: false}"
                    :dcr-linked="dcrLinkedTable"
                    :columns-num="columnsNum"
                    :ctlg-amount-field="ctlgAmountField ? ctlgAmountField.field : null"
                    :active-height-watcher="dcrLinkedTable.ctlg_board_view_height < 200"
                    :external-width="100"
                    :is-visible="isVisible"
                    :style="{height: hasCtlgFilters ? 'calc(100% - 38px) !important' : '100%'}"
                    @updated-ctlg="updateCatalog"
                    @show-src-record="showSrcRecord"
                    @total-tb-height-changed="ctlgHeightChanged"
                ></board-view>
            </div>

            <slot-popup
                v-if="catalogPopupShow"
                :popup_width="950"
                @popup-close="catalogPopupShow = false"
            >
                <template v-slot:title>
                    <span>Catalog</span>
                </template>
                <template v-slot:body>
                    <filters-block
                        v-if="hasCtlgFilters"
                        :table-meta="catalogMeta"
                        :input_filters="catalogFilters"
                        :fixed-pos="true"
                        :absolute-state="true"
                        :ignore-can-change="true"
                        :placed="'top_filters'"
                        :style="{justifyContent: 'center', height: '38px', paddingTop: '6px'}"
                        @changed-filter="ctlgFilter()"
                    ></filters-block>
                    <board-view
                        :table-meta="catalogMeta"
                        :all-rows="catalogRows"
                        :user="$root.user"
                        :rows-count="catalogRows.length"
                        :cell-height="$root.cellHeight"
                        :max-cell-rows="$root.maxCellRows"
                        :is-full-width="true"
                        :behavior="'dcr_linked_tb'"
                        :with_edit="false"
                        :with-border="false"
                        :with-adding="{active: false}"
                        :dcr-linked="dcrLinkedTable"
                        :columns-num="columnsNum"
                        :ctlg-amount-field="ctlgAmountField ? ctlgAmountField.field : null"
                        :external-width="100"
                        :is-visible="isVisible"
                        :style="{height: hasCtlgFilters ? 'calc(100% - 38px) !important' : '100%'}"
                        @updated-ctlg="updateCatalog"
                        @show-src-record="showSrcRecord"
                    ></board-view>
                </template>
            </slot-popup>
        </template>

        <div v-else="" class="full-height flex flex--center">
            <img height="75" src="/assets/img/Loading_icon.gif">
        </div>

    </div>
</template>

<script>
import {SpecialFuncs} from "../../classes/SpecialFuncs";

import CheckRowBackendMixin from './../_Mixins/CheckRowBackendMixin.vue';
import LinkEmptyObjectMixin from './../_Mixins/LinkEmptyObjectMixin.vue';
import ViewTypeLinkMixin from './../CommonBlocks/Link/ViewTypeLinkMixin.vue';

import CustomTable from "./CustomTable";
import ListingView from "./ListingView.vue";
import BoardView from "./BoardView.vue";
import CustomTableWithPopup from "./CustomTableWithPopup.vue";
import VerticalTableWithHistory from "./VerticalTableWithHistory.vue";
import FiltersBlock from "../CommonBlocks/FiltersBlock.vue";
import {eventBus} from "../../app";

export default {
        name: "VerticalLinkedTable",
        mixins: [
            CheckRowBackendMixin,
            LinkEmptyObjectMixin,
            ViewTypeLinkMixin,
        ],
        components: {
            FiltersBlock,
            VerticalTableWithHistory,
            CustomTableWithPopup,
            BoardView,
            ListingView,
            CustomTable,
        },
        data: function () {
            return {
                catalogFilters: [],
                catalogPopupShow: false,
                loadedCatalog: false,
                catalogMeta: null,
                catalogRows: [],
                loadedRows: false,
                linkedTbMeta: null,
                ctlgHeight: Number(this.dcrLinkedTable.ctlg_board_view_height),
                minimalHeight: Number(this.dcrLinkedTable.max_height_inline_embd),
                currentTableAlign: this.dcrLinkedTable ? this.dcrLinkedTable.embd_table_align : 'start',
            }
        },
        computed: {
            columnsNum() {
                let num = Number(this.dcrLinkedTable.ctlg_columns_number);
                if (screen.width <= 425) {
                    num = Math.min(num, 2);
                }
                if (screen.width <= 768) {
                    num = Math.min(num, 3);
                }
                if (screen.width <= 1024) {
                    num = Math.min(num, 4);
                }
                return num;
            },
            hasCtlgFilters() {
                return this.dcrLinkedTable.ctlg_filter_field_ids
                    && this.dcrLinkedTable.ctlg_filter_field_ids.length
                    && this.catalogFilters.length;
            },
            totalHgt() {
                let pt15 = 15;
                let total = this.minimalHeight ? (this.minimalHeight + pt15) : 0;
                if (this.dcrLinkedTable.ctlg_is_active && this.ctlgHeight) {
                    total += this.ctlgHeight;
                }
                return total ? total+'px' : null;
            },
            ctlgHasPopup() {
                return this.loadedCatalog
                    && this.catalogMeta
                    && this.catalogRows.length
                    && this.dcrLinkedTable.ctlg_display_option == 'popup';
            },
            specialExtras() { // Can remove linked rows if "Parent Row" is not saved on back-end
                return this.parentRow && this.parentRow.id
                    ? {}
                    : { force_delete: true };
            },
            foreSpec() {
                return SpecialFuncs.specialParams('', this.dcrLinkedTable.id, this.parentRow);
            },
            addingRow() {
                return {
                    immediate: true,
                    active: this.noAddLimit,
                    position: 'bottom',
                };
            },
            noAddLimit() {
                return !Number(this.dcrLinkedTable.max_nbr_rcds_embd)
                    || (
                        this.linkedRowsObject[this.dcrLinkedTable.linked_table_id]
                        && this.linkedRowsObject[this.dcrLinkedTable.linked_table_id].length < this.dcrLinkedTable.max_nbr_rcds_embd
                    );
            },
            ctlgAmountField() {
                return this.loadedCatalog && this.linkedTbMeta
                    ? _.find(this.linkedTbMeta._fields, {id: Number(this.dcrLinkedTable.ctlg_parent_quantity_field_id)})
                    : null;
            },
        },
        props:{
            parentMeta: Object,
            parentRow: Object,
            dcrLinkedTable: {
                type: Object,
                required: true,
            },
            linkedRowsObject: {
                type: Object,//each fields is Array
                required: true,
            },
            with_edit: {
                type: Boolean,
                default: true
            },
            isVisible: Boolean,
        },
        methods: {
            totalTbHeightChanged(height) {
                this.minimalHeight = Number(height);

                switch (this.popupViewType) {
                    case this.viewTypeListing:
                        this.minimalHeight += 80 + (this.with_edit ? 30 : 0);
                        break;
                    case this.viewTypeTable:
                        this.minimalHeight += 6;
                        break;
                    case this.viewTypeBoards:
                        this.minimalHeight += 6;
                        break;
                }
                console.log('totalTbHeightChanged', this.minimalHeight, this.totalHgt);
            },
            ctlgHeightChanged(height) {
                this.ctlgHeight = Number(height) + (this.hasCtlgFilters ? 38 : 0);
                console.log('ctlgHeightChanged', this.ctlgHeight, this.totalHgt);
            },
            loadLinkedMeta() {
                return new Promise((resolve) => {
                    axios.post('/ajax/table-data/get-headers', {
                        table_id: this.dcrLinkedTable.linked_table_id,
                        user_id: this.$root.user.id,
                        special_params: this.foreSpec,
                    }).then(({ data }) => {
                        this.linkedTbMeta = data;
                        //NOTE: rewrite table defaults
                        this.linkedTbMeta.listing_rowswi = 160;
                        this.linkedTbMeta.primary_width = 100;
                        resolve();
                    }).catch(errors => {
                        Swal('Info', getErrors(errors));
                    });
                });
            },
            loadDcrRows() {
                if (this.parentRow && Number(this.parentRow.id)) {
                    return new Promise((resolve) => {
                        axios.post('/ajax/table-data/get-dcr-rows', {
                            special_params: this.foreSpec,
                            parent_row_dcr: this.parentRow,
                        }).then(({data}) => {
                            this.linkedRowsObject[this.dcrLinkedTable.linked_table_id] = data.rows;
                            this.loadedRows = true;
                            resolve();
                        }).catch(errors => {
                            Swal('Info', getErrors(errors));
                        });
                    });
                } else {
                    return new Promise((resolve) => {
                        this.loadedRows = true;
                        resolve();
                    });
                }
            },
            fillLinkedParam(tableRow) {
                let rc = _.find(this.parentMeta._ref_conditions, {id: Number(this.dcrLinkedTable.passed_ref_cond_id)});
                if (rc) {
                    this.$root.assignObject(this.getLinkParams(rc._items, this.parentRow), tableRow);
                }
            },
            insertLinked(tableRow) {
                if (this.noAddLimit) {
                    this.linkedRowsObject[this.dcrLinkedTable.linked_table_id].push(tableRow);
                    this.sendEvent();
                } else {
                    Swal({ title: 'Info', html: '"Max. number of records allowed to be added to the embedded table" has been reached' });
                }
            },
            updateLinked(tableRow) {
                this.$nextTick(() => {
                    let special_params = this.foreSpec;
                    this.checkRowOnBackend(this.linkedTbMeta.id, tableRow, '', special_params, true).then((data) => {
                        if (data.row) {
                            this.$root.assignObject(data.row, tableRow);
                            this.sendEvent();
                        }
                    });
                });
            },
            deleteLinked(tableRow, idx) {
                if (idx > -1) {
                    this.linkedRowsObject[this.dcrLinkedTable.linked_table_id].splice(idx, 1);
                    this.sendEvent(this.popupViewType === this.viewTypeListing);
                }
            },
            showSrcRecord(lnk, header, tableRow) {
                this.$emit('show-src-record', lnk, header, tableRow);
            },
            sendEvent(redraw) {
                this.loadedRows = ! redraw;
                this.$nextTick(() => {
                    this.loadedRows = true;
                    this.$emit('linked-update');
                });
            },
            updateCatalog() {
                let parentField = _.find(this.linkedTbMeta._fields, {id: Number(this.dcrLinkedTable.ctlg_parent_link_field_id)});
                let catalogField = _.find(this.catalogMeta._fields, {id: Number(this.dcrLinkedTable.ctlg_distinct_field_id)});

                let promises = [];

                _.each(this.catalogRows, ctlgRow => {
                    let dcrLnkIdx = _.findIndex(this.linkedRowsObject[this.dcrLinkedTable.linked_table_id], (lnkRow) => {
                        return lnkRow[parentField.field] == ctlgRow[catalogField.field];
                    });
                    let dcrLinkedRow = this.linkedRowsObject[this.dcrLinkedTable.linked_table_id][dcrLnkIdx];

                    if (ctlgRow.__ctlg_amount > 0) {
                        if (dcrLinkedRow && dcrLinkedRow[this.ctlgAmountField.field] != Number(ctlgRow.__ctlg_amount)) {
                            //Update
                            dcrLinkedRow[this.ctlgAmountField.field] = Number(ctlgRow.__ctlg_amount);
                            this.updateLinked(dcrLinkedRow);
                        } else
                        if (! dcrLinkedRow) {
                            //Create
                            let newLinked = SpecialFuncs.emptyRow(this.linkedTbMeta);
                            this.fillLinkedParam(newLinked);
                            newLinked[this.ctlgAmountField.field] = Number(ctlgRow.__ctlg_amount);
                            newLinked[parentField.field] = ctlgRow[catalogField.field];

                            let promis = this.checkRowOnBackend(this.linkedTbMeta.id, newLinked);
                            promis.then((data) => {
                                this.insertLinked(newLinked);
                            });
                            promises.push(promis);
                        }
                    } else
                    if (dcrLinkedRow) {
                        //Delete
                        this.deleteLinked(dcrLinkedRow, dcrLnkIdx);
                    }
                });

                Promise.all(promises).then(() => {
                    console.log('updateCatalog', this.catalogRows, this.linkedRowsObject[this.dcrLinkedTable.linked_table_id]);
                    this.sendEvent();
                });
            },
            loadCatalog() {
                if (this.dcrLinkedTable.ctlg_is_active && this.dcrLinkedTable.ctlg_table_id) {
                    axios.post('/ajax/table-data/get-dcr-catalog', {
                        special_params: this.foreSpec,
                        dcr_linked_table: this.dcrLinkedTable,
                        filters: this.catalogFilters,
                    }).then(({data}) => {
                        this.loadedCatalog = true;
                        this.catalogMeta = data.meta;
                        this.catalogFilters = data.filters;
                        this.catalogRows = this.fillCatalogAmounts(data.rows);
                    }).catch(errors => {
                        Swal('Info', getErrors(errors));
                    });
                }
            },
            fillCatalogAmounts(rows) {
                let parentField = _.find(this.linkedTbMeta._fields, {id: Number(this.dcrLinkedTable.ctlg_parent_link_field_id)});
                let catalogField = _.find(this.catalogMeta._fields, {id: Number(this.dcrLinkedTable.ctlg_distinct_field_id)});

                _.each(rows, row => {
                    row.__ctlg_amount = 0;
                    if (parentField && catalogField && this.ctlgAmountField) {
                        let dcrLinkedRow = _.find(this.linkedRowsObject[this.dcrLinkedTable.linked_table_id], (lnkRow) => {
                            return lnkRow[parentField.field] == row[catalogField.field];
                        });
                        if (dcrLinkedRow) {
                            row.__ctlg_amount += Number(dcrLinkedRow[this.ctlgAmountField.field]);
                        }
                    }
                });

                return rows;
            },
            ctlgFilter(filters) {
                if (filters) {
                    this.catalogFilters = filters;
                }
                this.loadCatalog();
            },
        },
        mounted() {
            this.popupViewType = this.dcrLinkedTable.default_display || 'Table';

            Promise.all([
                this.loadLinkedMeta(),
                this.loadDcrRows(),
            ]).then(() => {
                this.loadCatalog();
            });
        },
        beforeDestroy() {
        }
    }
</script>

<style lang="scss" scoped>
.header-btn {
    cursor: pointer;
    position: absolute;
    right: 0;
    top: 0;
    z-index: 100;
    color: #555 !important;

    .view-type-wrapper {
        right: 18px;
        position: absolute;
        background-color: transparent;
        width: max-content;
        white-space: nowrap;
        top: 0;
    }

    .fas {
        color: #555 !important;
        margin: 0 3px;
    }
}
.left-top {
    position: absolute;
    left: 0;
    top: 0;
    z-index: 100;
}
</style>