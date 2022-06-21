<template>
    <td :style="getCustomCellStyle"
        class="td-custom"
        ref="td"
        @click="showEdit()"
    >
        <div class="td-wrapper" :style="getTdWrappStyle">

            <div class="wrapper-inner" :style="getWrapperStyle">
                <div class="inner-content"
                     :style="{textAlign: (tableHeader.field === 'checked' && behavior === 'data_sets_columns') ? 'center' : ''}">

                    <!--Column Groups-->
                    <span v-if="tableHeader.field === 'checked' && behavior === 'data_sets_columns'" class="indeterm_check__wrap">
                        <span class="indeterm_check" @click="checkClicked()" ref="inline_input">
                            <i v-if="fieldChecked()" class="glyphicon glyphicon-ok group__icon"></i>
                        </span>
                    </span>

                    <a v-else-if="tableHeader.field === 'row_ref_condition_id'"
                       title="Open ref condition in popup."
                       @click.stop="showAddRefCond(tableRow.row_ref_condition_id)"
                    >{{ showField() }}</a>

                    <div v-else="">{{ showField() }}</div>

                </div>
            </div>

        </div>



        <!-- ABSOLUTE EDITINGS -->
        <div v-if="isEditing()" class="cell-editing">

            <!--Regular RowGroups Cells-->
            <tablda-select-simple
                    v-if="tableHeader.field === 'row_ref_condition_id'"
                    :options="globalRefConds()"
                    :table-row="tableRow"
                    :hdr_field="tableHeader.field"
                    :can_empty="true"
                    :fixed_pos="true"
                    :embed_func_txt="isVertTable ? 'Add New' : ''"
                    :style="getEditStyle"
                    @selected-item="updateCheckedDDL"
                    @hide-select="hideEdit"
                    @embed-func="showAddRefCond(tableRow.row_ref_condition_id)"
            ></tablda-select-simple>

            <!--Other Cells-->
            <input
                    v-else-if="inArray(tableHeader.field, ['name', 'description', 'notes'])"
                    v-model="tableRow[tableHeader.field]"
                    @blur="hideEdit();updateValue()"
                    ref="inline_input"
                    class="form-control full-height"
                    :style="getEditStyle">

            <div v-else="">{{ hideEdit() }}</div>
            
        </div>
        <!-- ABSOLUTE EDITINGS -->
        
    </td>
</template>

<script>
import Select2DDLMixin from './../_Mixins/Select2DDLMixin.vue';
import CellStyleMixin from '../_Mixins/CellStyleMixin.vue';

import SelectWithFolderStructure from './InCell/SelectWithFolderStructure.vue';
import TabldaSelectSimple from "./Selects/TabldaSelectSimple";

export default {
        name: "CustomCellColRowGroup",
        mixins: [
            Select2DDLMixin,
            CellStyleMixin,
        ],
        components: {
            TabldaSelectSimple,
            SelectWithFolderStructure,
        },
        data: function () {
            return {
                editing: false,
                compared_values: [],
                source_field_for_values: null
            }
        },
        computed: {
            getCustomCellStyle() {
                return this.getCellStyle;
            },
            canCellEdit() {
                return this.tableHeader.f_type !== 'Attachment'
                    && this.globalMeta && this.globalMeta._is_owner
                    && !this.inArray(this.tableHeader.field, this.$root.systemFields)
                    && (this.tableHeader.field !== 'logic_operator' || can_logic_operator)
                    && (this.tableHeader.field !== 'group_logic' || can_group_logic)
                    && this.behavior !== 'data_sets_columns';
            }
        },
        props:{
            globalMeta: Object,
            tableMeta: Object,
            tableHeader: Object,
            tableRow: Object,
            rowsCount: Number,
            rowIndex: Number,
            cellHeight: Number,
            maxCellRows: Number,
            isAddRow: Boolean,
            behavior: String,
            user: Object,
            conditionArray: {
                type: Array|null,
                default: null
            },
            parentRow: Object,
            isVertTable: Boolean,
        },
        methods: {
            inArray(item, array) {
                return $.inArray(item, array) > -1;
            },
            isEditing: function () {
                return this.editing && this.canCellEdit && !this.$root.global_no_edit;
            },
            showEdit: function () {
                if (!this.canCellEdit || (this.tableHeader.field === 'checked' && this.behavior === 'data_sets_columns')) {
                    return;
                }
                //edit cell
                if (this.canCellEdit) {
                    let field_id = (this.behavior === 'data_sets_ref_condition_items' ? this.tableRow.compared_field_id : this.tableRow.table_field_id);
                    this.editing = true;
                    this.oldValue = this.tableRow[this.tableHeader.field];
                    this.$nextTick(function () {
                        if (this.$refs.inline_input) {
                            if (this.$refs.inline_input && this.$refs.inline_input.nodeName === 'SELECT') {
                                this.showHideDDLs(this.$root.selectParam);
                                this.ddl_cached = false;
                            } else {
                                this.$refs.inline_input.focus();
                            }
                        }
                    });
                } else {
                    this.editing = false;
                }
            },
            hideEdit: function () {
                this.editing = false;
            },
            updateValue: function () {
                if (this.tableRow[this.tableHeader.field] !== this.oldValue) {
                    this.$emit('updated-cell', this.tableRow);
                }
            },
            updateCheckedDDL(item, show) {
                this.tableRow[this.tableHeader.field] = item;
                this.updateValue();
            },
            checkClicked() {
                this.$emit('check-clicked', 'select', !this.fieldChecked(), [this.tableRow]);
            },
            fieldChecked() {
                return this.conditionArray
                    &&
                    _.findIndex(this.conditionArray, {id: Number(this.tableRow.id)}) > -1;
            },
            showField() {
                let res = '';

                if (this.isAddRow && this.inArray(this.tableHeader.field, this.$root.systemFields)) {
                    res = 'Auto'
                }
                else
                if (this.tableHeader.field === 'field_value' && this.parentRow.listing_field !== 'id') {
                    let row = JSON.parse( this.tableRow.row_json );
                    if (row) {
                        res = row[this.parentRow.listing_field];
                    }
                }
                else
                if (this.tableHeader.field === 'table_field_id') {
                    res = this.$root.uniqName( this.tableRow.name );
                }
                else
                if (this.tableHeader.field === 'row_ref_condition_id' && this.tableRow.row_ref_condition_id) {
                    let ref_cond = _.find(this.globalMeta._ref_conditions, {id: Number(this.tableRow.row_ref_condition_id)});
                    res = ref_cond ? ref_cond.name : '';
                }
                else {
                    res = this.tableRow[this.tableHeader.field];
                }

                return this.$root.strip_tags(res);
            },
            showAddRefCond(refId) {
                this.$emit('show-add-ref-cond', refId)
            },

            //arrays for selects
            globalRefConds() {
                return _.map(this.globalMeta._ref_conditions, (rc) => {
                    return { val: rc.id, show: rc.name, }
                });
            },
        }
    }
</script>

<style lang="scss" scoped>
    @import "CustomCell.scss";

    .folder-option {
        color: #000000;
        font-weight: bold;
        background-color: #EEEEEE;
    }
</style>