<template>
    <td :style="getCellStyle"
        class="td-custom"
        ref="td"
    >
        <div class="td-wrapper" :style="getTdWrappStyle">

            <div class="wrapper-inner" :style="getWrapperStyle">
                <div class="inner-content" :style="{textAlign: ['is_show','is_header'].indexOf(tableHeader.field) > -1 ? 'center' : ''}">

                    <label v-if="tableHeader.field === 'is_show'" class="switch_t">
                        <input type="checkbox" :checked="is_show_checked" @click="is_show_click">
                        <span class="toggler round"></span>
                    </label>

                    <input v-else-if="tableHeader.field === 'is_header'" :checked="is_header_checked" @click="is_header_click" type="radio"/>

                    <div v-else="">{{ $root.uniqName( tableRow.name ) }}</div>

                </div>
            </div>

        </div>
        
    </td>
</template>

<script>
    import CellStyleMixin from '../_Mixins/CellStyleMixin.vue';

    export default {
        name: "CustomCellKanbanSett",
        mixins: [
            CellStyleMixin,
        ],
        components: {
        },
        inject: {
            reactive_provider: {
                from: 'reactive_provider',
                default: () => { return {} }
            }
        },
        data: function () {
            return {
            }
        },
        computed: {
            is_header_checked() {
                return this.parentRow
                    && this.parentRow.kanban_group_field_id == this.tableRow.id;
            },
            is_show_checked() {
                return this.parentRow
                    && this.parentRow._columns
                    && _.find(this.parentRow._columns, {field: this.tableRow.field});
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
            parentRow: Object,
        },
        methods: {
            is_show_click() {
                this.$emit('check-clicked', this.tableRow.id, !this.is_show_checked, []);
            },
            is_header_click() {
                this.$emit('check-clicked', 'is_header', this.tableRow.id, []);
            },
        }
    }
</script>

<style lang="scss" scoped>
    @import "CustomCell.scss";

    .td-wrapper {
        input {
            margin: 0px;
        }
    }
</style>