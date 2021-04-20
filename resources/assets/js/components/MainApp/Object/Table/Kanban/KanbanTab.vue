<template>
    <div class="flex full-height" @click.self="selCards = []">
        <div v-for="el in distVals"
             v-if="!tableMeta.kanban_hide_empty_tab || (el.rows && el.rows.length)"
             class="kanban_column"
             :style="{
                    borderLeft: dragColumn && overVal === el.val ? '5px dashed #000' : null,
                    backgroundColor: dragRow && overVal === el.val ? '#F77' : null,
                }"
             @dragover.prevent=""
             @dragenter="overVal = el.val"
             @dragend="dragOverEnd()"
             @drop="dragCardEnd(el.val)"
        >
            <div class="flex flex--col">
                <div class="column_header"
                     draggable="true"
                     @dragstart="dragColumn = el.val"
                     :style="{width: (tableMeta.kanban_card_width || 300)+'px'}"
                >
                    <div class="flex flex--center-v">
                        <single-td-field
                                :table-meta="tableMeta"
                                :table-header="headerFld"
                                :td-value="el.val"
                                :ext-row="el.rows ? el.rows[0] : null"
                                :no_width="true"
                                :with_edit="false"
                                style="display: inline-block;background-color: transparent;"
                        ></single-td-field>
                        <span>({{ el.count }})</span>
                    </div>
                    <header-menu-elem
                        class="pull-right"
                        style="position: absolute;right: 25px;top: 0;"
                        :spec-icon="'fa fa-cog'"
                        @field-sort-asc="sortRows(el, 'asc')"
                        @field-sort-desc="sortRows(el, 'desc')"
                    ></header-menu-elem>
                    <span class="glyphicon pull-right"
                          :class="[el.collapsed ? 'glyphicon-triangle-top' : 'glyphicon-triangle-bottom']"
                          style="cursor: pointer;position: absolute;right: 5px;top: 3px;"
                          @click="collapseCol(el)"></span>
                </div>
                <div class="flex__elem-remain flex flex--col" style="overflow-x: hidden;">
                    <div v-for="row in el.rows"
                         :style="{marginTop: dragRow && overRowId === row.id ? '50px' : null, transition: 'all 0.7s'}"
                         @dragover.prevent=""
                         @dragenter="overRowId = row.id"
                         @dragend="dragOverEnd()"
                    >
                        <kanban-card
                            v-if="kanbanFld._kanban_setting"
                            :table-meta="tableMeta"
                            :table-row="row"
                            :kanban-sett="kanbanFld._kanban_setting"
                            :ext-collapse="isCollapsed(row.id)"
                            :card-selected="isCardSel(row.id)"
                            :drag-row="dragRow"
                            :style="cardStyle(row)"
                            @drag-move="dragMove(row)"
                            @show-popup="(row) => { showPopUp(row, el.rows) }"
                            @row-update="updateRow"
                            @change-selected="(eve) => changeSelected(eve, row)"
                            @change-collapsed="changeCollapsed"
                        ></kanban-card>
                    </div>
                    <div class="flex__elem-remain" @dragover.prevent="" @dragenter="overRowId = null" @click="selCards = []"></div>
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
                :cell-height="$root.cellHeight"
                :max-cell-rows="$root.maxCellRows"
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
    import {eventBus} from './../../../../../app';

    import KanbanCard from "./KanbanCard";
    import CustomEditPopUp from "../../../../CustomPopup/CustomEditPopUp";
    import HeaderMenuElem from "../../../../CustomTable/Header/HeaderMenuElem";
    import SingleTdField from "../../../../CommonBlocks/SingleTdField";

    export default {
        name: "KanbanTab",
        components: {
            SingleTdField,
            HeaderMenuElem,
            CustomEditPopUp,
            KanbanCard,
        },
        data: function () {
            return {
                collapsedIds: [],
                selCards: [],
                distVals: [],
                editallRows: [],
                editPopupRow: null,
                editPopupRole: 'update',
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
        props:{
            tableMeta: Object,
            allRows: Array,
            kanbanFld: Object,
            add_click: Number,
        },
        computed: {
            headerFld() {
                return this.kanbanFld._kanban_setting
                    ? _.find(this.tableMeta._fields, {id: Number(this.kanbanFld._kanban_setting.kanban_group_field_id)})
                    : null;
            },
        },
        watch: {
            kanbanFld: {
                handler: function(val) {
                    this.selCards = [];
                    this.columnsOrder = val._kanban_setting && val._kanban_setting.columns_order
                        ? JSON.parse(val._kanban_setting.columns_order)
                        : [];
                    this.cardsOrder = val._kanban_setting && val._kanban_setting.cards_order
                        ? JSON.parse(val._kanban_setting.cards_order)
                        : {};
                    this.loadDistinctiveVals()
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
                if (!eve.ctrlKey) {
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
                    this.dragPos.off_left = this.dragPos.off_left
                        || (window.event.offsetX > 0 ? window.event.offsetX : 0);
                    this.dragPos.top = (window.event.clientY - this.dragPos.off_top)+1;
                    this.dragPos.left = (window.event.clientX - this.dragPos.off_left);
                }
            },
            dragCardEnd(val) {
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
                    _.each(this.allRows, (row) => {
                        if ($.inArray(row.id, this.selCards) > -1) {
                            row[this.kanbanFld.field] = val;
                            this.updateRow(row);
                        }
                    });
                    this.selCards = [];
                } else {
                    this.dragRow[this.kanbanFld.field] = val;
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
                this.$emit('save-backend', 'kanban_sort_type', 'custom');
            },
            changeCardOrder(val) {
                if (this.headerFld) {
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
                if (this.tableMeta._is_owner) {
                    this.$root.sm_msg_type = 1;
                    axios.put('/ajax/settings/kanban', {
                        table_id: this.tableMeta.id,
                        kanban_id: this.kanbanFld._kanban_setting.id,
                        field: field,
                        val: val,
                    }).then(({ data }) => {
                        this.kanbanFld._kanban_setting[field] = val;
                    }).catch(errors => {
                        Swal('', getErrors(errors));
                    }).finally(() => {
                        this.$root.sm_msg_type = 0;
                    });
                } else {
                    this.kanbanFld._kanban_setting[field] = val;
                }
            },

            //edit rows
            insertRow(tableRow) {
                eventBus.$emit('list-view-insert-row', tableRow);
            },
            copyRow(tableRow) {
                eventBus.$emit('list-view-copy-row', tableRow);
            },
            updateRow(tableRow) {
                eventBus.$emit('list-view-update-row', tableRow);
            },
            deleteRow(tableRow) {
                eventBus.$emit('list-view-delete-row', tableRow);
            },

            //work with popup
            showSrcRecord(lnk, header, tableRow) {
                this.$emit('show-src-record', lnk, header, tableRow);
            },
            showPopUp(row, collection) {
                this.editallRows = collection;
                this.editPopupRow = row;
                this.editPopupRole = 'update';
            },
            showPopIdx(idx) {
                this.editPopupRow = this.editallRows[idx];
                this.editPopupRole = 'update';
            },
            closePopUp() {
                this.editPopupRow = null;
            },
            anotherRowPopup(is_next) {
                let row_id = (this.editPopupRow ? this.editPopupRow.id : null);
                this.$root.anotherPopup(this.editallRows, row_id, is_next, this.showPopIdx);
            },

            //load possible columns
            loadDistinctiveVals() {
                //get 'vals'
                let vals = _.map(this.allRows, (row) => {
                    return row[this.kanbanFld.field] || '';
                });
                vals = _.uniq(vals);
                //order Columns
                let ordered = _.uniq(_.concat( _.clone(this.columnsOrder), vals ));
                if ($.inArray(this.tableMeta.kanban_sort_type, ['asc','desc']) > -1) {
                    ordered = _.orderBy( _.map(ordered, (vl) => { return {vl:vl}; }), 'vl', this.tableMeta.kanban_sort_type);
                    ordered = _.map(ordered, (el) => { return el.vl; });
                }
                //parse disctinctive vals
                let cardOrdCh = false;
                let newDistArr = [];
                _.each(ordered, (vl) => {
                    let oldDist = _.find(this.distVals, {val: vl});
                    let rows = _.filter(this.allRows, (row) => {
                        return row[this.kanbanFld.field] == vl;
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
            rowsLoaded(type) {
                this.$nextTick(() => {
                    //if (type === 'rows-changed') {
                        this.loadDistinctiveVals();
                    //}
                });
            },
            sortRows(el, type) {
                if (this.headerFld) {
                    el.sort = type || el.sort;
                    let partOrder = this.cardsOrder[el.val] || [];
                    let rowvals = _.map(el.rows, 'id');
                    let rowOrd = _.uniq(_.concat( _.clone(partOrder), rowvals ));
                    if (type) {
                        rowOrd = _.orderBy(el.rows, this.headerFld.field, type);
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
        },
        mounted() {
            eventBus.$on('new-request-params', this.rowsLoaded);
        },
        beforeDestroy() {
            eventBus.$off('new-request-params', this.rowsLoaded);
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