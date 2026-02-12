<template>
    <div class="flex full-height"
         :class="{'flex--center-h': selectedKanban.kanban_center_align, 'flex--wrap': selectedKanban.kanban_center_align}"
         @click.self="selCards = []"
         v-if="drawKanb"
    >
        <div v-if="!tableRows" class="full-frame flex flex--center bold" :style="{color: smartTextColor}">Loading...</div>

        <div v-for="el in distVals"
             v-if="tableRows && (!selectedKanban.kanban_hide_empty_tab || (el.rows && el.rows.length))"
             class="kanban_column"
             :style="{
                    borderLeft: dragColumn && overVal === el.val ? '5px dashed #000' : null,
                    backgroundColor: dragRow && overVal === el.val ? '#F77' : null,
                    minHeight: (Number(selectedKanban.kanban_card_height || 300)+70)+'px',
                }"
             @dragover.prevent=""
             @dragenter="overVal = el.val"
             @dragend="dragOverEnd()"
             @drop="dragCardEnd(el.val)"
        >
            <div class="flex flex--col">
                <div class="column_header"
                     :draggable="canEdit"
                     @dragstart="dragColumn = el.val"
                     :style="colStl"
                >
                    <div class="flex flex--center-v">
                        <single-td-field
                                :table-meta="tableMeta"
                                :table-header="kanbanHeader"
                                :td-value="el.val"
                                :ext-row="el.rows ? el.rows[0] : null"
                                :no_width="true"
                                :with_edit="false"
                                style="display: inline-block;background-color: transparent;"
                        ></single-td-field>
                        <span>{{ countString(el) }}</span>
                    </div>
                    <header-menu-elem
                        class="pull-right"
                        style="position: absolute;right: 25px;top: 0;"
                        :spec-icon="'fa fa-cog'"
                        :table-meta="tableMeta"
                        @field-sort-asc="sortRows(el, 'asc')"
                        @field-sort-desc="sortRows(el, 'desc')"
                    ></header-menu-elem>
                    <span class="glyphicon pull-right"
                          :class="[el.collapsed ? 'glyphicon-triangle-top' : 'glyphicon-triangle-bottom']"
                          style="cursor: pointer;position: absolute;right: 5px;top: 3px;"
                          @click="collapseCol(el)"></span>
                </div>
                <div class="flex__elem-remain flex flex--col" style="overflow-x: hidden; overflow-y: scroll;">
                    <div v-for="row in el.rows"
                         :style="{marginTop: dragRow && overRowId === row.id ? '50px' : null, transition: 'all 0.7s'}"
                         @dragover.prevent=""
                         @dragenter="overRowId = row.id"
                         @dragend="dragOverEnd()"
                    >
                        <kanban-card
                            v-if="selectedKanban"
                            :table-meta="tableMeta"
                            :table-row="row"
                            :kanban-sett="selectedKanban"
                            :ext-collapse="isCollapsed(row.id)"
                            :card-selected="isCardSel(row.id)"
                            :drag-row="dragRow"
                            :style="cardStyle(row)"
                            :can-edit="canEdit"
                            @drag-move="dragMove(row)"
                            @show-popup="(row) => { showPopUp(row, el.rows) }"
                            @row-update="updateRow"
                            @change-selected="(eve) => changeSelected(eve, row)"
                            @change-collapsed="changeCollapsed"
                        ></kanban-card>
                    </div>
                    <div class="flex__elem-remain"
                         @dragover.prevent=""
                         @dragenter="overRowId = null"
                         @click="selCards = []"
                         :style="remainStl"
                    ></div>
                </div>
            </div>
        </div>

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
import {Endpoints} from "../../../../../classes/Endpoints";
import {StatHelper} from "../../../../../classes/StatHelper";
import {SpecialFuncs} from "../../../../../classes/SpecialFuncs";

import {eventBus} from '../../../../../app';

import KanbanCard from "./KanbanCard";
import CustomEditPopUp from "../../../../CustomPopup/CustomEditPopUp";
import HeaderMenuElem from "../../../../CustomTable/Header/HeaderMenuElem";

import MixinForAddons from "./../MixinForAddons";
import CellStyleMixin from "../../../../_Mixins/CellStyleMixin";

export default {
    name: "KanbanTab",
    mixins: [
        MixinForAddons,
        CellStyleMixin,
    ],
    components: {
        HeaderMenuElem,
        CustomEditPopUp,
        KanbanCard,
    },
    data: function () {
        return {
            drawKanb: true,
            wait_for_loading: true,
            collapsedIds: [],
            selCards: [],
            distVals: [],
            editallRows: [],
            overVal: null,
            overRowId: null,
            lastColOrdJson: '',
            columnsOrder: [],
            cardsOrder: {},
            dragColumn: null,
            dragRow: null,
            dragPos: {
                left: 0,
                top: 0,
                off_left: 0,
                off_top: 0,
            },
        }
    },
    props: {
        tableMeta: Object,
        requestParams: Object,//MixinForAddon
        selectedKanban: Object,
        currentPageRows: Array,//MixinForAddon
        add_click: Number,
        isVisible: Boolean,
    },
    computed: {
        colStl() {
            return {
                width: (this.selectedKanban.kanban_card_width || 300)+'px',
                maxWidth: (window.innerWidth - 25)+'px',
            };
        },
        remainStl() {
            return {
                minWidth: Math.min(this.selectedKanban.kanban_card_width, (window.innerWidth - 25))+'px',
            };
        },
        kanbanHeader() {
            return  _.find(this.tableMeta._fields, {id: Number(this.selectedKanban.table_field_id)});
        },
        canEdit() {
            return this.$root.AddonAvailableToUser(this.tableMeta, 'kanban', 'edit');
        },
        updateField() {//MixinForAddons
            return this.kanbanHeader ? this.kanbanHeader.field : '';
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
        add_click() {
            this.editPopupRow = this.$root.emptyObject(this.tableMeta);
            this.editPopupRole = 'add';
        },
    },
    methods: {
        //collapses
        collapseCol(el) {
            el.collapsed = !el.collapsed;
            let r_ids = _.map(el.rows, (row) => { return row.id; });
            if (el.collapsed) {
                this.collapsedIds = _.uniq( _.concat(this.collapsedIds, r_ids) );
            } else {
                this.collapsedIds = _.filter(this.collapsedIds, (id) => { return r_ids.indexOf(id) === -1; });
            }
        },
        changeCollapsed(row_id) {
            let i = this.collapsedIds.indexOf(row_id);
            if (i > -1) {
                this.collapsedIds.splice(i, 1);
            } else {
                this.collapsedIds.push(row_id);
            }
        },
        isCollapsed(row_id) {
            return this.collapsedIds.indexOf(row_id) > -1;
        },
        //select card
        isCardSel(row_id) {
            return $.inArray(row_id, this.selCards) > -1;
        },
        changeSelected(eve, row) {
            let cmdOrCtrl = eve.metaKey || eve.ctrlKey;
            if (!cmdOrCtrl) {
                this.selCards = [];
            }
            this.selCards.push(row.id);
        },

        //drag card
        cardStyle(row) {
            if (this.dragRow && this.dragRow.id === row.id) {
                return {
                    transition: 'initial',
                    position: 'fixed',
                    top: this.dragPos.top+'px',
                    left: this.dragPos.left+'px',
                    zIndex: 500,
                };
            } else
            if (this.dragRow && this.isCardSel(row.id)) {
                return { opacity: '0.1', };
            } else {
                return { opacity: '1', };
            }
        },
        dragMove(tableRow) {
            this.dragRow = tableRow;
            if (window.event.clientY && window.event.clientX) {
                if (!this.dragPos.off_top && !this.dragPos.off_left) {
                    let rect = window.event.target.getBoundingClientRect();
                    this.dragPos.off_left = Math.max( this.$root.lastMouseClick.clientX - rect.left, 0 );
                    this.dragPos.off_left = Math.min( this.dragPos.off_left, rect.width );
                }
                this.dragPos.top = (window.event.clientY - this.dragPos.off_top)+1;
                this.dragPos.left = (window.event.clientX - this.dragPos.off_left);
            }
        },
        dragCardEnd(val) {
            if (!this.canEdit) {
                return;
            }
            let last_id = this.dragRow ? this.dragRow.id : 0;
            if (this.dragRow) {
                this.dragCard(val);
                this.changeCardOrder(val);
            }
            if (this.dragColumn) {
                this.changeColOrder(val);
            }
            this.loadDistinctiveVals();
            this.dragOverEnd();
            //activate dragged row
            this.selCards.push(last_id);
        },
        dragOverEnd() {
            this.dragColumn = null;
            this.dragRow = null;
            this.overVal = null;
            this.overRowId = null;
            this.dragPos.off_top = 0;
            this.dragPos.off_left = 0;
            this.dragPos.top = 0;
            this.dragPos.left = 0;
        },
        dragCard(val) {
            if (this.selCards.length) {
                this.selCards.push(this.dragRow.id);
                _.each(this.tableRows, (row) => {
                    if ($.inArray(row.id, this.selCards) > -1) {
                        row[this.kanbanHeader.field] = this.asValue(val);
                        this.updateRow(row);
                    }
                });
                this.selCards = [];
            } else {
                this.dragRow[this.kanbanHeader.field] = this.asValue(val);
                this.updateRow(this.dragRow);
            }
        },

        //change card/column order
        changeColOrder(val) {
            this.columnsOrder = _.filter(this.columnsOrder, (ord) => {
                return ord !== this.dragColumn;
            });
            let ii = this.columnsOrder.indexOf(val);
            if (ii > -1) {
                this.columnsOrder.splice(ii, 0, this.dragColumn);
            }
            this.saveKanban('kanban_sort_type', 'custom');
        },
        changeCardOrder(val) {
            if (this.kanbanHeader) {
                _.each(this.cardsOrder, (arrs, i) => {
                    this.cardsOrder[i] = _.filter(arrs, (ord) => {
                        return ord !== this.dragRow.id;
                    });
                });
                let partOrder = this.cardsOrder[val] || [];
                let ii = partOrder.indexOf(this.overRowId);
                if (ii > -1) {
                    partOrder.splice(ii, 0, this.dragRow.id);
                } else {
                    partOrder.push(this.dragRow.id);
                }
                this.cardsOrder[val] = partOrder;
                this.saveKanban( 'cards_order', JSON.stringify(this.cardsOrder) );
            }
        },
        saveKanban(field, val) {
            if (this.canEdit) {
                this.$root.sm_msg_type = 1;
                axios.put('/ajax/settings/kanban', {
                    table_id: this.tableMeta.id,
                    kanban_id: this.selectedKanban.id,
                    field: field,
                    val: val,
                }).catch(errors => {
                    Swal('Info', getErrors(errors));
                }).finally(() => {
                    this.$root.sm_msg_type = 0;
                });
            }
            this.selectedKanban[field] = val;
        },

        //popup
        showPopUp(row, collection) {
            this.editallRows = collection;
            this.editPopupRow = row;
            this.editPopupRole = 'update';
        },
        anotherRowPopup(is_next) {
            let row_id = (this.editPopupRow ? this.editPopupRow.id : null);
            this.$root.anotherPopup(this.editallRows, row_id, is_next, this.showPopIdx);
        },
        showPopIdx(idx) {
            this.editPopupRow = this.editallRows[idx];
            this.editPopupRole = 'update';
        },

        //load possible columns
        asHeader(val) {
            return String(val).replace(/[\[\]\"]/gi, '').split(',').sort().join(', ');
        },
        asValue(val) {
            if (SpecialFuncs.isMSEL(this.kanbanHeader.input_type)) {
                return '[' + String(val).split(',').map((el) => {
                    return '"' + _.trim(el) + '"';
                }).join() + ']';
            } else {
                return val;
            }
        },
        loadDistinctiveVals() {
            this.selCards = [];
            this.columnsOrder = JSON.parse(this.selectedKanban.columns_order) || [];
            this.cardsOrder = JSON.parse(this.selectedKanban.cards_order) || {};
            //get 'vals'
            let vals = _.map(this.tableRows, (row) => {
                return row[this.kanbanHeader.field] || '';
            });
            vals = _.uniq(vals);
            //order Columns
            let ordered = _.concat( _.clone(this.columnsOrder), vals );
            ordered = _.uniq(_.map(ordered, (ord) => {
                return this.asHeader(ord);
            }));
            if ($.inArray(this.selectedKanban.kanban_sort_type, ['asc','desc']) > -1) {
                ordered = _.orderBy( _.map(ordered, (vl) => { return {vl:vl}; }), 'vl', this.selectedKanban.kanban_sort_type);
                ordered = _.map(ordered, (el) => { return el.vl; });
            }
            //parse disctinctive vals
            let cardOrdCh = false;
            let newDistArr = [];
            _.each(ordered, (vl) => {
                let oldDist = _.find(this.distVals, {val: vl});
                let rows = _.filter(this.tableRows, (row) => {
                    return this.asHeader(row[this.kanbanHeader.field] || '') == vl;
                });
                let newDist = {
                    val: vl || '',
                    sort: oldDist ? oldDist.sort : '',
                    rows: rows,
                    count: rows.length,
                    collapsed: oldDist ? oldDist.collapsed : false,
                };
                let res = this.sortRows(newDist);
                cardOrdCh = cardOrdCh || res;
                //save dist
                newDistArr.push(newDist);
            });
            if (this.lastColOrdJson !== JSON.stringify(ordered)) {
                this.saveKanban('columns_order', JSON.stringify(ordered));
            }
            if (cardOrdCh) {
                this.saveKanban('cards_order', JSON.stringify(this.cardsOrder));
            }
            this.distVals = newDistArr;
            this.columnsOrder = ordered;
            this.lastColOrdJson = JSON.stringify(ordered);
        },
        sortRows(el, type) {
            if (this.kanbanHeader) {
                el.sort = type || el.sort;
                let partOrder = this.cardsOrder[el.val] || [];
                let rowvals = _.map(el.rows, 'id');
                let rowOrd = _.uniq(_.concat( _.clone(partOrder), rowvals ));
                if (type) {
                    rowOrd = _.orderBy(el.rows, this.kanbanHeader.field, type);
                    rowOrd = _.map(rowOrd, 'id');
                }
                el.rows = el.rows.sort((a,b) => { return rowOrd.indexOf(a.id) - rowOrd.indexOf(b.id);} );
                if (JSON.stringify(this.cardsOrder[el.val]) !== JSON.stringify(rowOrd)) {
                    this.cardsOrder[el.val] = rowOrd;
                    return true;
                }
            }
            return false;
        },
        drawAddon() {//Needed for MixinForAddons.vue
            this.loadDistinctiveVals();
        },
        mountedFunc() {
            if (this.isVisible) {
                this.getRows(this.selectedKanban.kanban_data_range, 'kanban', this.selectedKanban.id);
            } else {
                this.wait_for_loading = true;
            }
        },
        redrawKanban(id, with_rows) {
            if (this.selectedKanban.id == id) {
                if (with_rows) {
                    this.mountedFunc();
                } else {
                    this.drawKanb = false;
                    this.$nextTick(() => {
                        this.drawKanb = true;
                    });
                }
            }
        },
        countString(el) {
            if (this.selectedKanban._group_params && this.selectedKanban._group_params.length) {
                let $result = [];
                _.each(this.selectedKanban._group_params, (param) => {
                    let fld = _.find(this.tableMeta._fields, {id: Number(param.table_field_id)});
                   if (fld) {
                       let calc = '';
                       switch (param.stat) {
                           case 'COUNT': calc = StatHelper.count(el.rows); break;
                           case 'COUNTUNIQUE': calc = StatHelper.countUnique(el.rows, fld.field); break;
                           case 'SUM': calc = StatHelper.sum(el.rows, fld.field); break;
                           case 'MIN': calc = StatHelper.min(el.rows, fld.field); break;
                           case 'MAX': calc = StatHelper.max(el.rows, fld.field); break;
                           case 'MEAN': calc = StatHelper.mean(el.rows, fld.field); break;
                           case 'AVG': calc = StatHelper.avg(el.rows, fld.field); break;
                           case 'VAR': calc = StatHelper.variance(el.rows, fld.field); break;
                           case 'STD': calc = StatHelper.std(el.rows, fld.field); break;
                       }
                       calc = SpecialFuncs.showhtml(fld, {}, calc, this.$root.tableMeta);
                       $result.push( /*this.$root.uniqName(fld.name) + ' ' +*/ calc );
                   }
                });
                if ($result.length) {
                    return '(' + $result.join(' | ') + ')';
                }
            }
            return '';//el.count;
        },
    },
    mounted() {
        this.mountedFunc();
        eventBus.$on('redraw-kanban', this.redrawKanban);
        eventBus.$on('new-request-params', this.mountedFunc);
    },
    beforeDestroy() {
        eventBus.$off('redraw-kanban', this.redrawKanban);
        eventBus.$off('new-request-params', this.mountedFunc);
    }
}
</script>

<style lang="scss" scoped>
    .kanban_column {
        padding: 10px 5px 5px 5px;
        margin: 5px;
        background-color: #EEE;
        border-radius: 5px;

        .column_header {
            padding: 0 10px 10px 10px;
            font-weight: bold;
            position: relative;
            cursor: pointer;
        }
    }
</style>