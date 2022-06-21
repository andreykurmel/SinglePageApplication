<template>
    <td :style="getCustomCellStyle"
        class="td-custom"
        ref="td"
        @click="showEdit()"
    >
        <div class="td-wrapper" :style="getTdWrappStyle">

            <div class="wrapper-inner" :style="getWrapperStyle">
                <div class="inner-content">

                    <label class="switch_t" v-if="tableHeader.field === 'status'" :style="{height: Math.min(maxCellHGT, 17)+'px'}">
                        <input type="checkbox" :disabled="!enabledStatus" v-model="editValue" @change="updateValue()">
                        <span class="toggler round" :class="[!enabledStatus ? 'disabled' : '']"></span>
                    </label>

                    <label class="switch_t" v-else-if="tableHeader.f_type === 'Boolean'" :style="{height: Math.min(maxCellHGT, 17)+'px'}">
                        <input type="checkbox" v-model="editValue" @change="updateValue()">
                        <span class="toggler round"></span>
                    </label>

                    <a v-else-if="inArray(tableHeader.field, ['table_column_group_id', 'table_row_group_id']) && editValue"
                       title="Open row/column group in popup."
                       @click.stop="showinlineGroupsPopup()"
                    >
                        <span :class="{'is_select': is_sel}">{{ showField() }}</span>
                    </a>

                    <div v-else-if="is_multisel">
                        <span v-for="str in editValue" v-if="str" :class="{'is_select': is_sel, 'm_sel__wrap': is_multisel}">
                            <span v-html="str"></span>
                            <span v-if="is_sel && is_multisel" class="m_sel__remove" @click.prevent.stop="updateCheckedDDL(str)">&times;</span>
                        </span>
                    </div>

                    <div v-else="">
                        <span v-if="showField()" :class="{'is_select': is_sel}">{{ showField() }}</span>
                        <button v-if="tableHeader.field === 'font_size' && showField() && canEdit"
                                class="btn btn-danger btn-sm btn-deletable flex flex--center"
                                @click="updateCheckedDDL(null)"
                        >
                            <span>×</span>
                        </button>
                    </div>

                </div>
            </div>

        </div>



        <!-- ABSOLUTE EDITINGS -->
        <div v-if="tableHeader.f_type === 'Color'" class="cell-editing">
            <tablda-colopicker
                    :init_color="editValue"
                    :fixed_pos="true"
                    :avail_null="true"
                    :can_edit="canEdit"
                    @set-color="setColor"
            ></tablda-colopicker>
        </div>

        <div v-else-if="isEditing()" class="cell-editing">

            <tablda-select-simple
                    v-if="tableHeader.field === 'compare'"
                    :options="[
                        {val: '<', show: '<'},
                        {val: '=', show: '='},
                        {val: '>', show: '>'},
                        {val: '!=', show: '!='},
                        {val: 'Include', show: 'Include'},
                    ]"
                    :table-row="tableRow"
                    :hdr_field="tableHeader.field"
                    :fixed_pos="true"
                    :style="getEditStyle"
                    @selected-item="updateCheckedDDL"
                    @hide-select="hideEdit"
            ></tablda-select-simple>

            <tablda-select-simple
                    v-else-if="tableHeader.field === 'font'"
                    :options="[
                        {val: 'Normal', show: 'Normal'},
                        {val: 'Italic', show: 'Italic'},
                        {val: 'Bold', show: 'Bold'},
                        {val: 'Strikethrough', show: 'Strikethrough'},
                        {val: 'Overline', show: 'Overline'},
                        {val: 'Underline', show: 'Underline'},
                    ]"
                    :table-row="tableRow"
                    :hdr_field="tableHeader.field"
                    :fld_input_type="tableHeader.input_type"
                    :fixed_pos="true"
                    :style="getEditStyle"
                    @selected-item="updateCheckedDDL"
                    @hide-select="hideEdit"
            ></tablda-select-simple>

            <tablda-select-simple
                    v-else-if="tableHeader.field === 'font_size'"
                    :options="[
                        {val: '10', show: '10'},
                        {val: '12', show: '12'},
                        {val: '14', show: '14'},
                        {val: '16', show: '16'},
                        {val: '20', show: '20'},
                    ]"
                    :table-row="tableRow"
                    :hdr_field="tableHeader.field"
                    :fixed_pos="true"
                    :style="getEditStyle"
                    @selected-item="updateCheckedDDL"
                    @hide-select="hideEdit"
            ></tablda-select-simple>

            <tablda-select-simple
                    v-else-if="tableHeader.field === 'activity'"
                    :options="[
                        {val: 'Active', show: 'Active'},
                        {val: 'Freezed', show: 'Freezed'},
                    ]"
                    :table-row="tableRow"
                    :hdr_field="tableHeader.field"
                    :fixed_pos="true"
                    :style="getEditStyle"
                    @selected-item="updateCheckedDDL"
                    @hide-select="hideEdit"
            ></tablda-select-simple>

            <tablda-select-simple
                    v-else-if="tableHeader.field === 'table_column_group_id'"
                    :options="globalColGroups()"
                    :table-row="tableRow"
                    :hdr_field="tableHeader.field"
                    :can_empty="true"
                    :fixed_pos="true"
                    :embed_func_txt="'Add New'"
                    :style="getEditStyle"
                    @selected-item="updateCheckedDDL"
                    @hide-select="hideEdit"
                    @embed-func="showGroupsPopup('col')"
            ></tablda-select-simple>

            <tablda-select-simple
                    v-else-if="tableHeader.field === 'table_row_group_id'"
                    :options="globalRowGroups()"
                    :table-row="tableRow"
                    :hdr_field="tableHeader.field"
                    :can_empty="true"
                    :fixed_pos="true"
                    :embed_func_txt="'Add New'"
                    :style="getEditStyle"
                    @selected-item="updateCheckedDDL"
                    @hide-select="hideEdit"
                    @embed-func="showGroupsPopup('row')"
            ></tablda-select-simple>

            <input
                    v-else-if="inArray(tableHeader.field, ['name'])"
                    v-model="editValue"
                    @blur="hideEdit();updateValue()"
                    ref="inline_input"
                    class="form-control full-height"
                    :style="getEditStyle"/>

            <div v-else="">{{ hideEdit() }}</div>

        </div>
        <!-- ABSOLUTE EDITINGS -->

    </td>
</template>

<script>
import {eventBus} from './../../app';

import Select2DDLMixin from './../_Mixins/Select2DDLMixin.vue';
import CellStyleMixin from '../_Mixins/CellStyleMixin.vue';

import TabldaColopicker from './InCell/TabldaColopicker.vue';
import TabldaSelectSimple from "./Selects/TabldaSelectSimple";

export default {
        name: "CustomCellCondFormat",
        mixins: [
            Select2DDLMixin,
            CellStyleMixin
        ],
        components: {
            TabldaSelectSimple,
            TabldaColopicker,
        },
        data: function () {
            return {
                editing: false,
                oldVal: null,
                editValue: null,
                ref_ddl: [],
                source_field_for_values: null,
            }
        },
        props:{
            globalMeta: Object,
            tableMeta: Object,
            tableHeader: Object,
            tableRow: Object,
            cellHeight: Number,
            maxCellRows: Number,
            user: Object,
            isAddRow: Boolean
        },
        watch: {
        },
        computed: {
            canEdit() {
                return !this.inArray(this.tableHeader.field, this.$root.systemFields)
                    && (this.user.id == this.tableRow.user_id || this.isAddRow)
                    && (
                        ['show_table_data','show_form_data'].indexOf(this.tableHeader.field) === -1
                        ||
                        this.$root.checkAvailable(this.$root.user, 'form_visibility')
                    );
            },
            enabledStatus() {
                return this.user.id && (this.user.id == this.tableRow.user_id || !this.tableRow._always_on);
            },
            getCustomCellStyle() {
                let obj = this.getCellStyle;
                obj.backgroundColor = (this.tableHeader.field === 'color' ? this.tableRow.color: 'transparent');
                return obj;
            },
            is_sel() {
                return this.$root.issel(this.tableHeader.input_type);
            },
            is_multisel() {
                return this.$root.isMSEL(this.tableHeader.input_type);
            },
        },
        methods: {
            inArray(item, array) {
                return $.inArray(item, array) > -1;
            },
            notPresent(item, array) {
                return _.findIndex(array, {id: Number(item.id)}) === -1;
            },
            isEditing: function () {
                return this.editing && this.canEdit && !this.$root.global_no_edit;
            },
            showEdit: function () {
                if (!this.canEdit
                    || this.inArray(this.tableHeader.f_type, ['Boolean'])
                    || this.inArray(this.tableHeader.field, ['status','color'])) {
                    return;
                }
                this.editing = true;
                if (this.isEditing()) {
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
            setColor(clr, save) {
                if (save) {
                    this.$root.saveColorToPalette(clr);
                }
                this.editValue = clr;
                this.updateValue();
            },
            hideEdit: function () {
                this.editing = false;
            },
            updateCheckedDDL(item) {
                if (this.is_multisel) {
                    this.editValue = Array.isArray(this.editValue) ? this.editValue : [String(this.editValue)];

                    if (this.editValue.indexOf(item) > -1) {
                        this.editValue.splice( this.editValue.indexOf(item), 1 );
                    } else {
                        this.editValue.push(item);
                    }
                } else {
                    this.editValue = item;
                }
                this.updateValue();
            },
            updateValue() {
                let newVal = this.convEditValue(this.editValue, true);
                if (newVal !== this.oldValue) {
                    this.tableRow[this.tableHeader.field] = newVal;
                    this.$emit('updated-cell', this.tableRow);
                }
            },
            convEditValue(res, reverse) {
                if (this.is_multisel) {
                    if (reverse) {
                        return (Array.isArray(res) ? JSON.stringify(res) : res);
                    } else {
                        return this.$root.parseMsel(res);
                    }
                }
                return res;
            },
            showField() {
                let res = '';
                if (this.tableHeader.f_type === 'User') {
                    res = this.$root.smallUserStr(this.tableRow, this.tableHeader, this.editValue);
                }
                if (this.tableHeader.field === 'status') {
                    res = (this.tableRow.status == 1 ? 'On' : 'Off');
                }
                else
                if (this.tableHeader.field === 'table_column_group_id' && this.tableRow.table_column_group_id) {
                    let col_gr = _.find(this.globalMeta._column_groups, {id: Number(this.tableRow.table_column_group_id)});
                    res = col_gr ? col_gr.name : '';
                }
                else
                if (this.tableHeader.field === 'table_row_group_id' && this.tableRow.table_row_group_id) {
                    let row_gr = _.find(this.globalMeta._row_groups, {id: Number(this.tableRow.table_row_group_id)});
                    res = row_gr ? row_gr.name : '';
                }
                else {
                    res = this.editValue;
                }
                return this.$root.strip_tags(res);
            },
            showGroupsPopup(type) {
                eventBus.$emit('show-grouping-settings-popup', this.globalMeta.db_name, type);
            },
            showinlineGroupsPopup() {
                let type = '';
                switch (this.tableHeader.field) {
                    case 'table_column_group_id': type = 'col'; break;
                    case 'table_row_group_id': type = 'row'; break;
                }
                if (type) {
                    eventBus.$emit('show-grouping-settings-popup', this.globalMeta.db_name, type, this.editValue);
                }
            },
            globalColGroups() {
                return _.map(this.globalMeta._column_groups, (cg) => {
                    return { val: cg.id, show: cg.name, }
                });
            },
            globalRowGroups() {
                return _.map(this.globalMeta._row_groups, (rg) => {
                    return { val: rg.id, show: rg.name, }
                });
            },
        },
        mounted() {
            this.editValue = this.convEditValue(this.tableRow[this.tableHeader.field]);
        },
    }
</script>

<style lang="scss" scoped>
    @import "CustomCell.scss";

    .btn-deletable {
        position: absolute;
        top: 10%;
        right: 5%;
        bottom: 10%;
        padding: 0 3px;

        span {
            font-size: 1.4em;
            line-height: 0.7em;
            display: inline-block;
        }
    }
</style>