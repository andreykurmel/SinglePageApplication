<template>
    <td :style="getCustomCellStyle"
        class="td-custom"
        ref="td"
        @click="showEdit()"
    >
        <div class="td-wrapper" :style="getTdWrappStyle">

            <div class="wrapper-inner" :style="getWrapperStyle">
                <div class="inner-content">

                    <span v-if="tableHeader.field === 'is_edit_added' && tableRow._link" class="indeterm_check__wrap checkbox-input">
                        <span class="indeterm_check" ref="inline_input" @click="updateManagerCheck()">
                            <i v-if="tableRow._link.is_edit_added" class="glyphicon glyphicon-ok group__icon"></i>
                        </span>
                    </span>

                    <div v-else="" v-html="showField()"></div>

                </div>
            </div>

        </div>



        <!-- ABSOLUTE EDITINGS -->
        <div v-if="isEditing()" class="cell-editing">

            <!--User Group Conditions-->
            <tablda-select-simple
                    v-if="tableHeader.field === 'user_field'"
                    :options="usr_fields"
                    :table-row="tableRow"
                    :hdr_field="tableHeader.field"
                    :can_empty="true"
                    :fixed_pos="reactive_provider.fixed_ddl_pos"
                    :style="getEditStyle"
                    @selected-item="updateCheckedDDL"
                    @hide-select="hideEdit"
            ></tablda-select-simple>

            <tablda-select-simple
                    v-else-if="tableHeader.field === 'logic_operator'"
                    :options="[
                        {val: 'AND', show: 'AND'},
                        {val: 'OR', show: 'OR'},
                    ]"
                    :table-row="tableRow"
                    :hdr_field="tableHeader.field"
                    :fixed_pos="reactive_provider.fixed_ddl_pos"
                    :style="getEditStyle"
                    @selected-item="updateCheckedDDL"
                    @hide-select="hideEdit"
            ></tablda-select-simple>

            <tablda-select-simple
                    v-else-if="tableHeader.field === 'compared_operator'"
                    :options="[
                        {val: '<', show: '<'},
                        {val: '=', show: '='},
                        {val: '>', show: '>'},
                        {val: '!=', show: '!='},
                        {val: 'Include', show: 'Include'},
                    ]"
                    :table-row="tableRow"
                    :hdr_field="tableHeader.field"
                    :can_empty="true"
                    :fixed_pos="reactive_provider.fixed_ddl_pos"
                    :style="getEditStyle"
                    @selected-item="updateCheckedDDL"
                    @hide-select="hideEdit"
            ></tablda-select-simple>

            <input
                    v-else-if="tableHeader.field === 'compared_value'"
                    v-model="tableRow[tableHeader.field]"
                    @blur="hideEdit();updateValue()"
                    ref="inline_input"
                    class="form-control full-height"
                    :style="getEditStyle">

            <input
                    v-else-if="inArray(tableHeader.field, ['name', 'notes'])"
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

    import TabldaSelectSimple from "./Selects/TabldaSelectSimple";

    export default {
        components: {
            TabldaSelectSimple,
        },
        name: "CustomCellUserGroups",
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
                usr_fields: [
                    {val: 'first_name', show: 'First Name'},
                    {val: 'last_name', show: 'Last Name'},
                    {val: 'email', show: 'Email'},
                    {val: 'company', show: 'Company'},
                    {val: 'team', show: 'Team'},
                    {val: 'phone', show: 'Phone Number'},
                ],
            }
        },
        props:{
            tableMeta: Object,
            tableHeader: Object,
            tableRow: Object,
            rowsCount: Number,
            rowIndex: Number,
            cellHeight: Number,
            maxCellRows: Number,
            isAddRow: Boolean,
            user: Object,
        },
        computed: {
            getCustomCellStyle() {
                let obj = this.getCellStyle;
                let effect_fld = ['logic_operator'].indexOf(this.tableHeader.field) > -1;
                obj.backgroundColor = (this.canCellEdit || !effect_fld ? 'initial' : '#C7C7C7');
                return obj;
            },
            canCellEdit() {
                let can_logic_operator = (this.rowIndex >= 0 ? this.rowIndex > 0 : this.rowsCount > 0);
                return !this.inArray(this.tableHeader.field, this.$root.systemFields)
                    && (this.tableHeader.field !== 'logic_operator' || can_logic_operator);
            },
        },
        methods: {
            inArray(item, array) {
                return $.inArray(item, array) > -1;
            },
            isEditing: function () {
                return this.editing && this.canCellEdit && !this.$root.global_no_edit;
            },
            showEdit: function () {
                if (!this.canCellEdit) {
                    return;
                }
                //edit cell
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
            hideEdit: function () {
                this.editing = false;
            },
            updateValue: function () {
                if (this.tableRow[this.tableHeader.field] !== this.oldValue) {
                    this.$emit('updated-cell', this.tableRow);
                }
            },
            updateCheckedDDL(item) {
                this.tableRow[this.tableHeader.field] = item;
                this.updateValue();
            },
            updateManagerCheck() {
                this.tableRow._link.is_edit_added = !Boolean(this.tableRow._link.is_edit_added);
                this.$emit('updated-cell', this.tableRow._link);
            },
            showField() {
                let res = '';

                if (this.isAddRow && this.inArray(this.tableHeader.field, this.$root.systemFields)) {
                    res = 'Auto'
                }
                else
                if (this.tableHeader.field === 'user_field' && this.tableRow.user_field) {
                    let obj = _.find(this.usr_fields, {val: this.tableRow.user_field});
                    res = obj ? obj.show : this.tableRow.user_field;
                }
                else
                if (this.tableHeader.field === 'username') {
                    res = this.$root.getUserSimple(this.tableRow, this.tableMeta._owner_settings);
                }
                else {
                    res = this.tableRow[this.tableHeader.field];
                }

                return this.$root.strip_tags(res);
            },
        },
        mounted() {
        }
    }
</script>

<style scoped>
    @import "CustomCell.scss";
</style>