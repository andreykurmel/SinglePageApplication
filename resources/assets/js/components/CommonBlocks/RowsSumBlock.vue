<template>
    <table :style="{ width: (isFullWidth ? '100%' : '1px') }">
        <tbody class="table-body">

        <!-- settings row -->
        <tr>
            <td :width="widths.index_col">
                <div class="flex flex--center">
                    <b>STATS:</b>
                </div>
            </td>
            <td v-if="listViewActions" :width="widths.favorite_col"></td>

            <template v-for="tableHeader in tableMeta._fields">
                <td v-if="isShowFieldElem(tableHeader)"
                    :is="'rows-sum-cell-block'"
                    :table-header="tableHeader">
                </td>
            </template>

            <td v-if="inArray(behavior, actionArray)" :width="widths.action_col"></td>
        </tr>
        <!--Sum Row-->
        <tr>
            <td :width="widths.index_col">
                <div class="flex flex--center">
                    <b>VALUE:</b>
                </div>
            </td>
            <td v-if="listViewActions" :width="widths.favorite_col"></td>

            <template v-for="tableHeader in tableMeta._fields">
                <td v-if="isShowFieldElem(tableHeader)"
                    :is="'rows-sum-cell-block'"
                    :table-header="tableHeader"
                    :calc-header-func="true"
                    :all-rows="allRows">
                </td>
            </template>

            <td v-if="inArray(behavior, actionArray)" :width="widths.action_col"></td>
        </tr>

        </tbody>
    </table>
</template>

<script>
    import IsShowFieldMixin from '../_Mixins/IsShowFieldMixin.vue';
    import RowsSumCellBlock from "./RowsSumCellBlock.vue";

    export default {
        components: {
            RowsSumCellBlock
        },
        name: "RowsSumBlock",
        mixins: [
            IsShowFieldMixin
        ],
        data: function () {
            return {
            };
        },
        props:{
            tableMeta: Object,
            allRows: Array,
            widths: Object,
            listViewActions: Boolean,
            isFloatingTable: Boolean,
            hasFloatColumns: Boolean,
            isFullWidth: Boolean,
            cellHeight: Number,
            behavior: String,
            forbiddenColumns: Array, // for IsShowFieldMixin.vue
            availableColumns: Array, // for IsShowFieldMixin.vue
        },
        methods: {
            inArray(item, array) {
                return $.inArray(item, array) > -1;
            },
        },
        mounted() {
        }
    }
</script>

<style lang="scss" scoped>
    @import "../CustomTable/CustomTable";

    table {
        border-top: 1px solid #cccccc;

        .table-body {
            td {
                b {
                    display: block;
                    max-width: 100%;
                    text-overflow: ellipsis;
                    overflow: hidden;
                    padding: 0 3px;
                }
            }
        }
    }
</style>