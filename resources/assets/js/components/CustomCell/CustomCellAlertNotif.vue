<template>
    <td :style="getCellStyle"
        class="td-custom"
        ref="td"
        @click="showEdit()"
    >
        <single-td-field
                v-if="inArray(tableHeader.field, ['new_value']) && globalTbHeader"
                :table-meta="globalMeta"
                :table-header="globalTbHeader"
                :td-value="tableRow.new_value"
                :style="{width: '100%'}"
                :with_edit="true"
                :force_edit="true"
                @updated-td-val="(val) => {tableRow.new_value = val;updateValue()}"
        ></single-td-field>

        <div v-else="" class="td-wrapper" :style="getTdWrappStyle">

            <div class="wrapper-inner" :style="getWrapperStyle">
                <div class="inner-content">

                    <label class="switch_t" v-if="inArray(tableHeader.field, ['is_active'])" :style="{height: Math.min(maxCellHGT, 17)+'px'}">
                        <input type="checkbox" ref="inline_input" v-model="tableRow[tableHeader.field]" @change="updateValue()">
                        <span class="toggler round"></span>
                    </label>

                    <a v-else-if="tableHeader.field === 'mail_col_group_id'"
                       @click.stop="showGroupsPopup('col', tableRow.mail_col_group_id)"
                    >{{ showField() }}</a>

                    <div v-else="" class="content">{{ showField() }}</div>

                </div>
            </div>

        </div>



        <!-- ABSOLUTE EDITINGS -->
        <div v-if="isEditing()" class="cell-editing">
            
            <tablda-select-simple
                    v-if="tableHeader.field === 'mail_col_group_id'"
                    :options="globalColGroups()"
                    :table-row="tableRow"
                    :hdr_field="tableHeader.field"
                    :can_empty="true"
                    :fixed_pos="reactive_provider.fixed_ddl_pos"
                    :embed_func_txt="'Add New'"
                    :style="getEditStyle"
                    @selected-item="updateCheckedDDL"
                    @hide-select="hideEdit"
                    @embed-func="showGroupsPopup('col')"
            ></tablda-select-simple>

            <tablda-select-simple
                    v-else-if="tableHeader.field === 'logic'"
                    :options="[
                        {val: 'and', show: 'AND'},
                        {val: 'or', show: 'OR'},
                    ]"
                    :table-row="tableRow"
                    :hdr_field="tableHeader.field"
                    :fixed_pos="reactive_provider.fixed_ddl_pos"
                    :style="getEditStyle"
                    @selected-item="updateCheckedDDL"
                    @hide-select="hideEdit"
            ></tablda-select-simple>

            <tablda-select-simple
                    v-else-if="tableHeader.field === 'condition'"
                    :options="[
                        {val: '<', show: '<'},
                        {val: '=', show: '='},
                        {val: '>', show: '>'},
                        {val: '!=', show: '!='},
                    ]"
                    :table-row="tableRow"
                    :hdr_field="tableHeader.field"
                    :fixed_pos="reactive_provider.fixed_ddl_pos"
                    :style="getEditStyle"
                    @selected-item="updateCheckedDDL"
                    @hide-select="hideEdit"
            ></tablda-select-simple>

            <tablda-select-simple
                    v-else-if="tableHeader.field === 'table_field_id'"
                    :options="nameFields()"
                    :table-row="tableRow"
                    :hdr_field="tableHeader.field"
                    :can_empty="true"
                    :fixed_pos="reactive_provider.fixed_ddl_pos"
                    :style="getEditStyle"
                    @selected-item="updateCheckedDDL"
                    @hide-select="hideEdit"
            ></tablda-select-simple>

            <input
                    v-else-if="inArray(tableHeader.field, ['name'])"
                    v-model="tableRow[tableHeader.field]"
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

    import TabldaSelectSimple from "./Selects/TabldaSelectSimple";
    import SingleTdField from "../CommonBlocks/SingleTdField";

    export default {
        components: {
            SingleTdField,
            TabldaSelectSimple,
        },
        name: "CustomCellAlertNotif",
        mixins: [
            Select2DDLMixin,
            CellStyleMixin,
        ],
        inject: {
            reactive_provider: {
                from: 'reactive_provider',
                default: () => { return {} }
            }
        },
        data: function () {
            return {
                editing: false,
                oldVal: null,
            }
        },
        computed: {
            globalTbHeader() {
                return _.find(this.globalMeta._fields, {id: Number(this.tableRow.table_field_id)});
            },
        },
        props:{
            globalMeta: Object,
            tableMeta: Object,
            tableHeader: Object,
            tableRow: Object,
            cellHeight: Number,
            maxCellRows: Number,
            user: Object,
        },
        methods: {
            inArray(item, array) {
                return $.inArray(item, array) > -1;
            },
            isEditing() {
                return this.editing && !this.$root.global_no_edit;
            },
            showEdit() {
                if (this.inArray(this.tableHeader.field, ['is_active'])) {
                    return;
                }
                this.editing = true;
                if (this.isEditing()) {
                    this.oldValue = this.tableRow[this.tableHeader.field];
                    this.$nextTick(function () {
                        if (this.$refs.inline_input) {
                            if (this.$refs.inline_input && this.$refs.inline_input.nodeName === 'SELECT'){
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
            hideEdit() {
                this.editing = false;
            },
            updateValue() {
                if (this.tableRow[this.tableHeader.field] !== this.oldValue) {
                    if (this.tableHeader.field === 'table_field_id') {
                        this.tableRow.new_value = null;
                    }
                    this.$emit('updated-cell', this.tableRow);
                }
            },
            updateCheckedDDL(item) {
                this.tableRow[this.tableHeader.field] = item;
                this.updateValue();
            },
            showField() {
                let res = '';
                if (this.tableHeader.field === 'table_field_id' && this.tableRow.table_field_id) {
                    res = this.globalTbHeader ? this.$root.uniqName(this.globalTbHeader.name) : this.tableRow.table_field_id;
                }
                else
                if (this.tableHeader.field === 'mail_col_group_id' && this.tableRow.mail_col_group_id) {
                    let colGr = _.find(this.globalMeta._column_groups, {id: Number(this.tableRow.mail_col_group_id)});
                    res = colGr ? colGr.name : this.tableRow.mail_col_group_id;
                }
                else
                if (this.tableHeader.field === 'mail_format' && this.tableRow.mail_format) {
                    switch (this.tableRow.mail_format) {
                        case 'table': res = 'Tabular'; break;
                        case 'list': res = 'Listing'; break;
                        default: res = ''; break;
                    }
                }
                else
                if (this.tableHeader.field === 'logic' && this.tableRow.logic) {
                    switch (this.tableRow.logic) {
                        case 'or': res = 'OR'; break;
                        default: res = 'AND'; break;
                    }
                }
                else {
                    res = this.tableRow[this.tableHeader.field];
                }
                return this.$root.strip_tags(res);
            },

            //arrays for selects
            globalColGroups() {
                return _.map(this.globalMeta._column_groups, (cg) => {
                    return { val: cg.id, show: cg.name, }
                });
            },
            nameFields() {
                let fields = _.filter(this.globalMeta._fields, (hdr) => { return !this.inArray(hdr.field, this.$root.systemFields) });
                return _.map(fields, (hdr) => {
                    return { val: hdr.id, show: this.$root.uniqName(hdr.name), }
                });
            },

            //Group Links
            showGroupsPopup(type, id) {
                eventBus.$emit('show-grouping-settings-popup', this.globalMeta.db_name, type, id);
            },
        }
    }
</script>

<style scoped>
    @import "CustomCell.scss";
</style>