<template>
    <td :style="getCellStyle"
        class="td-custom"
        ref="td"
        @click="showEdit()"
    >
        <div class="td-wrapper" :style="getTdWrappStyle">

            <div class="wrapper-inner" :style="getWrapperStyle">
                <div class="inner-content" :style="{textAlign: ['Radio','Boolean'].indexOf(tableHeader.f_type) > -1 ? 'center' : ''}">

                    <label v-if="tableHeader.f_type === 'Boolean'" class="switch_t">
                        <input type="checkbox" :checked="is_show_checked" :disabled="!boolEdit" @click="is_show_click">
                        <span class="toggler round" :class="{'disabled': !boolEdit}"></span>
                    </label>

                    <input v-else-if="tableHeader.f_type === 'Radio'" :checked="is_header_checked" @click="is_header_click" type="radio"/>

                    <div v-else="">{{ showField() }}</div>

                </div>
            </div>

        </div>

        <!-- ABSOLUTE EDITINGS -->
        <div v-if="editing" class="cell-editing">

            <tablda-select-simple
                v-if="tableHeader.field === 'picture_style'"
                :options="[
                        {val: 'scroll', show: 'Scroll'},
                        {val: 'slide', show: 'Slide'},
                    ]"
                :table-row="tableRow"
                :hdr_field="tableHeader.field"
                :style="getEditStyle"
                @selected-item="updateCheckedDDL"
                @hide-select="hideEdit"
            ></tablda-select-simple>

            <tablda-select-simple
                v-else-if="tableHeader.field === 'picture_fit'"
                :options="[
                        {val: 'fill', show: 'Fill'},
                        {val: 'width', show: 'Width'},
                        {val: 'height', show: 'Height'},
                    ]"
                :table-row="tableRow"
                :hdr_field="tableHeader.field"
                :style="getEditStyle"
                @selected-item="updateCheckedDDL"
                @hide-select="hideEdit"
            ></tablda-select-simple>

            <div v-else="">{{ hideEdit() }}</div>

        </div>
        
    </td>
</template>

<script>
import CellStyleMixin from '../_Mixins/CellStyleMixin.vue';

import TabldaSelectSimple from "./Selects/TabldaSelectSimple";

export default {
        name: "CustomCellKanbanSett",
        mixins: [
            CellStyleMixin,
        ],
        components: {
            TabldaSelectSimple
        },
        data: function () {
            return {
                editing: false,
            }
        },
        computed: {
            neededPivot() {
                if (this.parentRow && this.parentRow._fields_pivot) {
                    return _.find(this.parentRow._fields_pivot, {table_field_id: Number(this.tableRow.id)});
                }
                return null;
            },
            boolEdit() {
                if (this.tableHeader.field === 'table_show_name') {
                    return !!this.neededPivot;
                }
                return ['Radio','Boolean'].indexOf(this.tableHeader.f_type) > -1;
            },
            is_header_checked() {
                return this.parentRow
                    && this.parentRow.kanban_group_field_id == this.tableRow.id;
            },
            is_show_checked() {
                return this.neededPivot && this.neededPivot[this.tableHeader.field];
            },
        },
        props:{
            globalMeta: Object,
            tableMeta: Object,
            tableHeader: Object,
            tableRow: Object, //TableField object
            cellHeight: Number,
            maxCellRows: Number,
            user: Object,
            parentRow: Object, //TableKanbanSettings object with '_fields_pivot'
        },
        methods: {
            is_show_click() {
                this.$emit('check-clicked', this.tableRow.id, {
                    setting: this.tableHeader.field,
                    val: !this.is_show_checked,
                }, []);
            },
            is_header_click() {
                this.$emit('check-clicked', 'is_header', this.tableRow.id, []);
            },
            showEdit() {
                if (this.neededPivot && this.tableHeader.f_type === 'String') {
                    this.editing = true;
                }
            },
            hideEdit() {
                this.editing = false;
            },
            updateCheckedDDL(item) {
                if (this.neededPivot) {
                    this.neededPivot[this.tableHeader.field] = item;
                }
                this.$emit('check-clicked', this.tableRow.id, {
                    setting: this.tableHeader.field,
                    val: item,
                }, []);
            },
            showField() {
                let res = '';
                if (this.tableHeader.field === 'picture_style') {
                    let sw = this.neededPivot ? this.neededPivot.picture_style : '';
                    switch (sw) {
                        case 'scroll': res = 'Scroll'; break;
                        case 'slide': res = 'Slide'; break;
                    }
                } else
                if (this.tableHeader.field === 'picture_fit') {
                    let sw = this.neededPivot ? this.neededPivot.picture_fit : '';
                    switch (sw) {
                        case 'fill': res = 'Fill'; break;
                        case 'width': res = 'Width'; break;
                        case 'height': res = 'Height'; break;
                    }
                }
                else {
                    res = this.$root.uniqName( this.tableRow.name );
                }
                return this.$root.strip_tags( String(res) );
            },
        }
    }
</script>

<style lang="scss" scoped>
    @import "CustomCell.scss";

    .td-wrapper {
        input {
            margin: 0;
        }
    }
</style>