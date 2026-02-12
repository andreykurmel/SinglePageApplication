<template>
    <td :style="getCustomCellStyle"
        class="td-custom"
        ref="td"
        @click="showEdit()"
    >
        <div class="td-wrapper" :style="getTdWrappStyle()">

            <div class="wrapper-inner" :style="getWrapperStyle()">
                <div class="inner-content"
                     :style="{textAlign: (tableHeader.field === 'checked' && behavior === 'data_sets_columns') ? 'center' : ''}">

                    <!--Column Groups-->
                    <span v-if="tableHeader.field === 'checked' && behavior === 'data_sets_columns'" class="indeterm_check__wrap">
                        <span class="indeterm_check" @click="checkClicked()" ref="inline_input">
                            <i v-if="fieldChecked()" class="glyphicon glyphicon-ok group__icon"></i>
                        </span>
                    </span>

                    <div v-else-if="tableHeader.f_type === 'Boolean'">
                        <label class="switch_t" :style="{height: Math.min(maxCellHGT, 17)+'px'}">
                            <input type="checkbox" :disabled="!canCellEdit" v-model="tableRow[tableHeader.field]" @change="updateValue()">
                            <span class="toggler round" :class="[!canCellEdit ? 'disabled' : '']"></span>
                        </label>
                    </div>

                    <a v-else-if="tableHeader.f_type === 'DataRange'"
                       title="Open data range in popup."
                       @click.stop="showRG(tableRow[tableHeader.field], globalMeta)"
                    >{{ showField() }}</a>

                    <a v-else-if="tableHeader.field === 'row_ref_condition_id'"
                       title="Open ref condition in popup."
                       @click.stop="showAddRefCond(tableRow.row_ref_condition_id)"
                    >{{ showField() }}</a>

                    <a v-else-if="(behavior === 'data_sets_rows' || behavior === 'data_sets') && tableHeader.field === 'name'"
                       title="Load the settings."
                       @click.stop="showLinkedRows()"
                    >
                        <span>{{ showField() }}</span>
                    </a>

                    <div v-else-if="behavior === 'data_sets_colgroups' && tableHeader.field === 'name'">
                        <span>{{ showField() }}</span>
                        <span v-if="!isAddRow">({{ tableRow._fields ? tableRow._fields.length : 0 }})</span>
                    </div>

                    <div v-else="">{{ showField() }}</div>

                </div>
            </div>

        </div>



        <!-- ABSOLUTE EDITINGS -->
        <div v-if="isEditing()" class="cell-editing">

            <tablda-select-simple
                    v-if="tableHeader.f_type === 'DataRange'"
                    :options="getRGr(globalMeta)"
                    :table-row="tableRow"
                    :hdr_field="tableHeader.field"
                    :fixed_pos="true"
                    :embed_func_txt="'Add New'"
                    :style="getEditStyle"
                    @selected-item="updateCheckedDDL"
                    @hide-select="hideEdit"
                    @embed-func="showRG(null, globalMeta)"
            ></tablda-select-simple>

            <!--Regular RowGroups Cells-->
            <tablda-select-simple
                    v-else-if="tableHeader.field === 'row_ref_condition_id'"
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

            <!--Regular RowGroups Cells-->
            <tablda-select-simple
                    v-else-if="tableHeader.field === 'preview_col_id'"
                    :options="globalCols()"
                    :table-row="tableRow"
                    :hdr_field="tableHeader.field"
                    :can_empty="true"
                    :fixed_pos="true"
                    :style="getEditStyle"
                    @selected-item="updateCheckedDDL"
                    @hide-select="hideEdit"
            ></tablda-select-simple>

            <!--Twilio Cells-->
            <tablda-select-simple
                    v-else-if="tableHeader.field === 'acc_twilio_key_id'"
                    :options="globalTwilios()"
                    :table-row="tableRow"
                    :hdr_field="tableHeader.field"
                    :fixed_pos="true"
                    :style="getEditStyle"
                    @selected-item="updateCheckedDDL"
                    @hide-select="hideEdit"
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
import DataRangeMixin from '../_Mixins/DataRangeMixin.vue';

import SelectWithFolderStructure from './InCell/SelectWithFolderStructure.vue';
import TabldaSelectSimple from "./Selects/TabldaSelectSimple";
import SelectBlock from "../CommonBlocks/SelectBlock.vue";

export default {
        name: "CustomCellColRowGroup",
        mixins: [
            Select2DDLMixin,
            CellStyleMixin,
            DataRangeMixin,
        ],
        components: {
            SelectBlock,
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
                return this.getCellStyle();
            },
            canCellEdit() {
                return this.with_edit
                    && !this.tableRow.is_system
                    && this.tableHeader.f_type !== 'Attachment'
                    //&& this.globalMeta && this.globalMeta._is_owner
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
            with_edit: {
                type: Boolean,
                default: true
            },
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
                    this.tableRow._changed_field = this.tableHeader.field;
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
                if (this.tableHeader.f_type === 'DataRange') {
                    let val = this.tableRow[this.tableHeader.field];
                    res = val === null ? '' : this.rgName(this.tableRow[this.tableHeader.field], this.globalMeta);
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
                else
                if (this.tableHeader.field === 'preview_col_id') {
                    let col = _.find(this.globalCols(), {val: Number(this.tableRow.preview_col_id)});
                    res = col ? col.show : this.tableRow.preview_col_id;
                }
                else
                if (this.tableHeader.field === 'acc_twilio_key_id' && this.tableRow.acc_twilio_key_id) {
                    let tw = _.find(this.globalTwilios(), {val: Number(this.tableRow.acc_twilio_key_id)});
                    res = tw ? tw.show : this.tableRow.acc_twilio_key_id;
                }
                else {
                    res = this.tableRow[this.tableHeader.field];
                }

                return this.$root.strip_danger_tags(res);
            },
            showAddRefCond(refId) {
                this.$emit('show-add-ref-cond', refId);
            },
            showLinkedRows() {
                let lif = _.find(this.tableMeta._fields, {field: this.tableRow.listing_field});

                let colId = this.behavior === 'data_sets' ? this.tableRow.id : this.tableRow.preview_col_id;
                let prevCol = _.find(this.globalMeta._column_groups, {id: Number(colId)});

                let lnk = {
                    id: null,
                    link_type: 'Record',
                    link_display: 'Popup',
                    popup_display: 'Table',
                    pop_width_px: parseInt(window.innerWidth * 0.7) - 555,
                    pop_height: 70,
                    always_available: true,
                    name: this.tableRow.name,
                    listing_field_id: lif ? lif.id : null,
                    table_ref_condition_id: this.tableRow.row_ref_condition_id,
                    dir_table_id: ! this.tableRow.row_ref_condition_id ? this.tableRow.table_id : null,
                    extra_ids: this.tableRow._regulars ? _.map(this.tableRow._regulars, 'field_value') : null,
                    avail_columns: prevCol ? _.map(prevCol._fields, 'field') : null,
                };
                this.$emit('show-src-record', lnk, {field:''}, {}, 'list_view');
            },

            //arrays for selects
            globalRefConds() {
                return _.map(this.globalMeta._ref_conditions, (rc) => {
                    return { val: rc.id, show: rc.name, }
                });
            },
            globalCols() {
                return _.map(this.globalMeta._column_groups, (col) => {
                    return { val: col.id, show: col.name, }
                });
            },
            globalTwilios() {
                return _.map(this.$root.user._twilio_api_keys, (tw, k) => {
                    return { val: tw.id, show: '#'+(k+1)+': '+tw.name, }
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