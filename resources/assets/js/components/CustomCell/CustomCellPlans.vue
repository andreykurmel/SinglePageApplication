<template>
    <td :style="getCellStyle"
        class="td-custom"
        ref="td"
        @click="showEdit()"
    >
        <div class="td-wrapper" :style="getTdWrappStyle">

            <div class="wrapper-inner" :style="getWrapperStyle">
                <div class="inner-content">

                    <label class="switch_t"
                           v-if="tableHeader.field === 'value' && (tableRow.code !== 'q_tables' && tableRow.code !== 'row_table')"
                           :style="{height: Math.min(maxCellHGT, 17)+'px'}">
                        <input type="checkbox" :disabled="!globalMeta._is_owner" v-model="tableRow[tableHeader.field]" @change="updateValue()">
                        <span class="toggler round"></span>
                    </label>

                    <input
                            v-else-if="isEditing()"
                            v-model="tableRow[tableHeader.field]"
                            @blur="hideEdit();updateValue()"
                            ref="inline_input"
                            class="form-control full-height"
                            :style="getEditStyle">

                    <div v-else="">{{ showField() }}</div>

                </div>
            </div>

        </div>

    </td>
</template>

<script>
    import CellStyleMixin from '../_Mixins/CellStyleMixin.vue';

    export default {
        name: "CustomCellPlans",
        mixins: [
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
            }
        },
        props:{
            globalMeta: {
                type: Object,
                default: function () {
                    return {};
                }
            },
            tableHeader: Object,
            tableRow: Object,
            cellHeight: Number,
            maxCellRows: Number,
            isAddRow: Boolean,
            user: Object,
        },
        methods: {
            inArray(item, array) {
                return $.inArray(item, array) > -1;
            },
            notPresent(item, array) {
                return _.findIndex(array, {id: Number(item.id)}) === -1;
            },
            isEditing: function () {
                return this.editing
                    && this.user.is_admin
                    && !this.inArray(this.tableHeader.field, this.$root.systemFields)
                    && !this.$root.global_no_edit;
            },
            showEdit: function () {
                //edit cell
                this.editing = true;
                if (this.isEditing()) {
                    this.oldValue = this.tableRow[this.tableHeader.field];
                    this.$nextTick(function () {
                        if (this.$refs.inline_input && this.$refs.inline_input.nodeName === 'SELECT'){
                            this.showHideDDLs(this.$root.selectParam);
                            this.ddl_cached = false;
                        } else {
                            this.$refs.inline_input.focus();
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
                    if (this.tableHeader.field === 'table_ref_condition_id') {
                        this.tableRow.target_field_id = null;
                    }
                    this.$emit('updated-cell', this.tableRow);
                }
            },
            showField() {
                let res = '';

                if (this.isAddRow && this.inArray(this.tableHeader.field, this.$root.systemFields)) {
                    res = 'Auto'
                }
                else
                if (this.tableHeader.f_type === 'User') {
                    res = this.$root.smallUserStr(this.tableRow, this.tableHeader, this.tableRow[this.tableHeader.field]);
                }
                else
                if (this.tableHeader.field === 'table_ref_condition_id' && this.tableRow.table_ref_condition_id) {
                    let ref_cond = _.find(this.globalMeta._ref_conditions, {id: Number(this.tableRow.table_ref_condition_id)});
                    res = ref_cond ? ref_cond.name : '';
                }
                else {
                    res = this.tableRow[this.tableHeader.field];
                }

                return this.$root.strip_tags(res);
            },
        },
        created() {

        }
    }
</script>

<style scoped>
    @import "CustomCell.scss";
</style>