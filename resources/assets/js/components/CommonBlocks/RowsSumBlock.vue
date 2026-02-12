<template>
    <div ref="tbWrap"
         :style="{
            justifyContent: primaryAlign,
            display: 'flex',
         }"
    >
        <table :style="{
            width: (isFullWidth ? '100%' : '1px'),
            borderLeft: primaryAlign !== 'start' ? '1px solid #ccc' : null,
        }">
            <tbody class="table-body">

            <!-- Settings row -->
            <tr>
                <td :style="sticky({width: widths.index_col+'px'}, -2)">
                    <div v-if="statsStyle" class="flex flex--center">
                        <b>STATS:</b>
                    </div>
                </td>
                <td v-if="listViewActions" :style="sticky({width: widths.favorite_col+'px'}, -1)"></td>

                <template v-for="(tableHeader, i) in showMetaFields">
                    <td :is="'rows-sum-cell-block'"
                        :table-meta="globalMeta"
                        :table-header="tableHeader"
                        :group-functions="groupFunctions"
                        :behavior="behavior"
                        :special_extras="special_extras"
                        :style="sticky({width: tdCellWidth(tableHeader)+'px'}, i)"
                    ></td>
                </template>

                <td v-if="inArray(behavior, actionArray)" :style="stickyRight({width: widths.action_col+'px'})"></td>
            </tr>
            <!--Sum Row-->
            <tr>
                <td :style="sticky({width: widths.index_col+'px'}, -2)">
                    <div v-if="statsStyle" class="flex flex--center">
                        <b>VALUE:</b>
                    </div>
                </td>
                <td v-if="listViewActions" :style="sticky({width: widths.favorite_col+'px'}, -1)"></td>

                <template v-for="(tableHeader, i) in showMetaFields">
                    <td :is="'rows-sum-cell-block'"
                        :table-meta="globalMeta"
                        :table-header="tableHeader"
                        :group-functions="groupFunctions"
                        :calc-header-func="true"
                        :all-rows="allRows"
                        :behavior="behavior"
                        :special_extras="special_extras"
                        :style="sticky({width: tdCellWidth(tableHeader)+'px'}, i)"
                    ></td>
                </template>

                <td v-if="inArray(behavior, actionArray)" :style="stickyRight({width: widths.action_col+'px'})"></td>
            </tr>

            </tbody>
        </table>
        <div v-if="r_margin" :style="{width: r_margin+'px', flexShrink: 0}"></div>
    </div>
</template>

<script>
    import {SpecialFuncs} from "../../classes/SpecialFuncs";

    import StickyColumnsMixin from '../_Mixins/StickyColumnsMixin.vue';
    import JustifyTableMixin from '../_Mixins/JustifyTableMixin.vue';

    import RowsSumCellBlock from "./RowsSumCellBlock.vue";

    export default {
        components: {
            RowsSumCellBlock
        },
        name: "RowsSumBlock",
        mixins: [
            StickyColumnsMixin,
            JustifyTableMixin,
        ],
        data: function () {
            return {
                groupFunctions: {},
            };
        },
        computed: {
            statsStyle() {
                return this.behavior !== 'cond_format_overview';
            },
            overviewStyle() {
                return this.behavior === 'cond_format_overview';
            },
        },
        props:{
            globalMeta: Object,
            tableMeta: Object,
            allRows: Array,
            widths: Object,
            listViewActions: Boolean,
            isFloatingTable: Boolean,
            hasFloatColumns: Boolean,
            isFullWidth: Boolean,
            cellHeight: Number,
            behavior: String,
            hasFloatActions: Boolean,// for StickyColumnsMixin
            forbiddenColumns: Array, // for IsShowFieldMixin
            availableColumns: Array, // for IsShowFieldMixin
            special_extras: Object,
            external_align: String, // for JustifyTableMixin
            isLink: Object, // for JustifyTableMixin
        },
        methods: {
            inArray(item, array) {
                return $.inArray(item, array) > -1;
            },
        },
        mounted() {
            let obj = {};
            _.each(this.tableMeta._fields, (header) => {
                obj[header.field] = SpecialFuncs.parseMsel(header.default_stats);
            });
            this.groupFunctions = obj;
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
                    overflow: hidden;
                    padding: 0 3px;
                }
            }
        }
    }
</style>