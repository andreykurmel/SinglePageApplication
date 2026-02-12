<template>
    <td :style="getCellStyle()" class="td-custom" ref="td" @click="showEdit()">

        <div class="td-wrapper" :style="getTdWrappStyle()">

            <div class="wrapper-inner" :style="getWrapperStyle()">
                <div class="inner-content">

                    <label class="switch_t" v-if="tableHeader.f_type === 'Boolean'" :style="{height: Math.min(maxCellHGT, 17)+'px'}">
                        <input type="checkbox" :checked="pivotChecked" :disabled="!with_edit" @click="pivotClick()">
                        <span class="toggler round" :class="{'disabled': !with_edit}"></span>
                    </label>

                    <div class="content" v-else="">{{ showField() }}</div>

                </div>
            </div>

        </div>

        <!-- ABSOLUTE EDITINGS -->
        <div v-if="isEditing()" class="cell-editing">
            <tablda-select-simple
                v-if="tableHeader.field === 'width_of_table_popup'"
                :options="tableWidths()"
                :table-row="neededPivot || {width_of_table_popup:'full'}"
                :hdr_field="tableHeader.field"
                :fixed_pos="true"
                :style="getEditStyle"
                @selected-item="updateCheckedDDL"
                @hide-select="hideEdit"
            ></tablda-select-simple>

            <div v-else="">{{ hideEdit() }}</div>
        </div>
    </td>
</template>

<script>
import {eventBus} from '../../app';

import CellStyleMixin from '../_Mixins/CellStyleMixin.vue';
import TabldaSelectSimple from "./Selects/TabldaSelectSimple.vue";

export default {
        name: "CustomCellPivotField",
        components: {
            TabldaSelectSimple
        },
        mixins: [
            CellStyleMixin
        ],
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
            tableMeta: Object,//style mixin
            tableHeader: Object,
            tableRow: Object,
            cellHeight: Number,
            maxCellRows: Number,
            isAddRow: Boolean,
            behavior: String,
            allRows: Object|null,
            user: Object,
            with_edit: {
                type: Boolean,
                default: true
            },
            parentRow: Object, //Object with '_fields_pivot'
        },
        computed: {
            neededPivot() {
                if (this.parentRow && this.parentRow._fields_pivot) {
                    return _.find(this.parentRow._fields_pivot, {table_field_id: Number(this.tableRow.id)});
                }
                return null;
            },
            pivotChecked() {
                return this.neededPivot && this.neededPivot[this.tableHeader.field];
            },
            pivotValue() {
                if (this.neededPivot) {
                    return this.neededPivot[this.tableHeader.field];
                }
                switch (this.tableHeader.field) {
                    case 'width_of_table_popup': return 'full';
                    default: return '';
                }
            },
            canCellEdit() {
                return this.with_edit
                    && this.globalMeta._is_owner
                    && !this.inArray(this.tableHeader.field, this.$root.systemFields);
            },
        },
        methods: {
            inArray(item, array) {
                return $.inArray(item, array) > -1;
            },
            isEditing() {
                return this.editing && this.canCellEdit && !this.$root.global_no_edit;
            },
            showEdit() {
                //edit cell
                this.editing = true;
                if (this.isEditing()) {
                    this.oldValue = this.pivotValue;
                    this.$nextTick(function () {
                        if (this.$refs.inline_input) {
                            this.$refs.inline_input.focus();
                        }
                    });
                } else {
                    this.editing = false;
                }
            },
            hideEdit() {
                this.editing = false;
            },
            updateCheckedDDL(item) {
                this.neededPivot[this.tableHeader.field] = item;
                this.pivotClick(this.pivotValue);
            },
            pivotClick(val) {
                this.$emit('check-clicked', this.tableRow.id, {
                    setting: this.tableHeader.field,
                    val: val !== undefined ? val : !this.pivotChecked,
                }, []);
            },
            tableWidths() {
                return [
                    { val:'full', show:'Full', },
                    { val:'field', show:'Field', },
                ];
            },
            showField() {
                let res = '';
                if (this.tableHeader.field === '_name') {
                    res = this.tableRow.name;
                }
                else
                if (this.inArray(this.tableHeader.field, ['width_of_table_popup'])) {
                    let cloud = _.find(this.tableWidths(), {val: this.pivotValue});
                    res = cloud ? cloud.show : res;
                }
                else {
                    res = this.tableRow[this.tableHeader.field];
                }
                return this.$root.strip_danger_tags(res);
            },
        },
        mounted() {
        }
    }
</script>

<style lang="scss" scoped>
    @import "CustomCell.scss";
</style>