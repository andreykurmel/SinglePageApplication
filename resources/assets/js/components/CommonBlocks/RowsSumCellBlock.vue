<template>
    <td @click="showEdit()" class="td-sum" :style="sumCellStyle">

        <template v-if="overviewStyle">
            <!-- Row Groups -->
            <div v-if="calcHeaderFunc" class="txt--center">
                <div v-if="tableHeader.field === '_of_name'" class="flex flex--center">
                    <b>Row Group:</b>
                </div>
                <a v-else-if="tableHeader._cf_row_group_id"
                   title="Open row group in popup."
                   :style="{color: rowGroupIsApplied ? null : '#222'}"
                   @click.stop="showGroupsPopup('row', tableHeader._cf_row_group_id)"
                >
                    {{ getRowGroupName() }}
                </a>
            </div>
            <!-- Column Groups -->
            <div v-else="" class="txt--center">
                <div v-if="tableHeader.field === '_of_name'" class="flex flex--center">
                    <b>Column Group:</b>
                </div>
                <a v-else-if="tableHeader._cf_col_group_id"
                   title="Open column group in popup."
                   @click.stop="showGroupsPopup('col', tableHeader._cf_col_group_id)"
                >
                    {{ getColumnGroupName() }}
                </a>
            </div>
        </template>

        <template v-else>
            <div v-if="calcHeaderFunc" class="flex flex--col flex--center">
                <div v-for="reslt in calcRowsResult()">
                    <b class="elipsis">{{ reslt }}</b>
                </div>
            </div>

            <template v-else="">
                <tablda-select-simple
                    v-if="editing"
                    :fld_input_type="'M-Select'"
                    :options="availRowSumFormulas()"
                    :table-row="groupFunctions"
                    :hdr_field="tableHeader.field"
                    :can_empty="true"
                    :fixed_pos="true"
                    :style="getEditStyle"
                    @selected-item="updateCheckedDDL"
                    @hide-select="hideEdit"
                ></tablda-select-simple>

                <div v-else-if="!editing" v-for="func in showVal()">
                    <b class="elipsis txt--center">{{ func }}</b>
                </div>
            </template>
        </template>
    </td>
</template>

<script>
    import {SpecialFuncs} from "../../classes/SpecialFuncs";
    import {UnitConversion} from "../../classes/UnitConversion";

    import CellStyleMixin from "./../_Mixins/CellStyleMixin.vue";

    import TabldaSelectSimple from "../CustomCell/Selects/TabldaSelectSimple";

    import {eventBus} from "../../app";

    export default {
        name: "RowsSumCellBlock",
        components: {
            TabldaSelectSimple,
        },
        mixins: [
            CellStyleMixin,
        ],
        data: function () {
            return {
                editing: false,
            };
        },
        props: {
            tableMeta: Object,
            groupFunctions: Object,
            tableHeader: Object,
            calcHeaderFunc: Boolean,
            allRows: Array,
            behavior: String,
            special_extras: Object,
        },
        computed: {
            sumCellStyle() {
                let style = this.textStyle;
                style.width = this.tdCellWidth(this.tableHeader)+'px';
                return style;
            },
            funcArray() {
                return this.groupFunctions[this.tableHeader.field] || [];
            },
            overviewStyle() {
                return this.behavior === 'cond_format_overview';
            },
            rowGroupIsApplied() {
                return !this.special_extras.direct_row || this.testRow(this.special_extras.direct_row, this.tableHeader._cf_id)
            },
        },
        methods: {
            getRowGroupName() {
                let row_gr = _.find(this.tableMeta._row_groups, {id: Number(this.tableHeader._cf_row_group_id)})
                    || _.find(this.tableMeta._gen_row_groups, {id: Number(this.tableHeader._cf_row_group_id)});
                return row_gr ? row_gr.name : '';
            },
            getColumnGroupName() {
                let col_gr = _.find(this.tableMeta._column_groups, {id: Number(this.tableHeader._cf_col_group_id)});
                return col_gr ? col_gr.name : '';
            },
            showGroupsPopup(type, id) {
                eventBus.$emit('show-grouping-settings-popup', this.tableMeta.db_name, type, id);
            },
            availRowSumFormulas() {
                return _.map(this.$root.availRowSumFormulas, (frm) => {
                    return { val: frm, show: String(frm).toUpperCase(), }
                });
            },
            updateCheckedDDL(item) {
                if (this.funcArray.indexOf(item) > -1) {
                    this.groupFunctions[this.tableHeader.field].splice( this.funcArray.indexOf(item), 1 );
                } else {
                    this.groupFunctions[this.tableHeader.field].push(item);
                }
            },
            // MAIN FUNC
            calcRowsResult() {
                let unit = SpecialFuncs.currencySign(this.tableHeader, '', this.tableMeta);
                let arr = [];
                _.each(this.funcArray, (func) => {
                    if (!func || !this.allRows.length) {
                        return;
                    }
                    let result;
                    switch (String(func).toLowerCase()) {
                        //#app_avail_formulas
                        case 'count': result = this.countFunc(this.tableHeader.field); break;
                        case 'countunique': result = this.countuniqueFunc(this.tableHeader.field); break;
                        case 'sum': result = this.sumFunc(this.tableHeader.field); break;
                        case 'min': result = this.minFunc(this.tableHeader.field); break;
                        case 'max': result = this.maxFunc(this.tableHeader.field); break;
                        case 'mean': result = this.meanFunc(this.tableHeader.field); break;
                        case 'avg': result = this.avgFunc(this.tableHeader.field); break;
                        case 'var': result = this.varFunc(this.tableHeader.field); break;
                        case 'std': result = this.stdFunc(this.tableHeader.field); break;
                    }
                    if (unit && result) {
                        result = String(unit) + String(result);
                    }
                    arr.push(
                        String(result).length > 7
                            ? Number(result).toPrecision(7)
                            : result
                    );
                });
                return arr;
            },

            // HELPERS
            notNum(i, field) {
                return this.allRows[i][field] && this.allRows[i][field] != parseFloat(this.allRows[i][field])
            },
            countFunc(field) {
                return this.allRows.length;
            },
            countuniqueFunc(field) {
                return _.uniqBy(this.allRows, field).length;
            },
            sumFunc(field) {
                let result = 0;
                for (let i = 0; i < this.allRows.length; i++) {
                    if (this.notNum(i, field)) {
                        result = NaN;
                        break;
                    }
                    result += this.$root.getFloat(this.allRows[i][field]);
                }
                return result;
            },
            minFunc(field) {
                let result = +Infinity;
                for (let i = 0; i < this.allRows.length; i++) {
                    if (this.notNum(i, field)) {
                        result = NaN;
                        break;
                    }
                    result = Math.min(result, this.$root.getFloat(this.allRows[i][field]));
                }
                return result;
            },
            maxFunc(field) {
                let result = -Infinity;
                for (let i = 0; i < this.allRows.length; i++) {
                    if (this.notNum(i, field)) {
                        result = NaN;
                        break;
                    }
                    result = Math.max(result, this.$root.getFloat(this.allRows[i][field]));
                }
                return result;
            },
            avgFunc(field) {
                let result = this.sumFunc(field);
                return !isNaN(result) ? (result / this.allRows.length) : NaN;
            },
            meanFunc(field) {
                let min = this.minFunc(field);
                let max = this.maxFunc(field);
                return !isNaN(min) && !isNaN(max) ? (min + max) / 2 : NaN;
            },
            varFunc(field) {
                let mean = this.avgFunc(field);
                if (isNaN(mean)) {
                    return NaN;
                }
                let result = 0;
                for (let i = 0; i < this.allRows.length; i++) {
                    if (this.notNum(i, field)) {
                        result = NaN;
                        break;
                    }
                    result += Math.pow( (this.$root.getFloat(this.allRows[i][field]) - mean), 2);
                }
                return !isNaN(result) ? (result / (this.allRows.length-1)) : NaN;
            },
            stdFunc(field) {
                let result = this.varFunc(field);
                return !isNaN(result) ? Math.sqrt(result) : NaN;
            },


            //EDIT FUNCTIONS
            showEdit() {
                if (!this.calcHeaderFunc) {
                    this.editing = true;
                    this.$nextTick(function () {
                        if (this.$refs.inline_input) {
                            this.$refs.inline_input.focus();
                        }
                    });
                }
            },
            hideEdit() {
                this.editing = false;
            },
            showVal() {
                return _.map(this.funcArray, (func) => {
                    return String(func).toUpperCase();
                });
            }
        },
        mounted() {
        }
    }
</script>

<style lang="scss" scoped>
    @import "../CustomTable/CustomTable";

    .cell-input {
        height: 100%;
        padding: 3px 6px;
    }
</style>