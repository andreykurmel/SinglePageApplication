<template>
    <td @click="showEdit()" class="td-sum" :style="sumCellStyle">

        <div v-if="calcHeaderFunc" class="flex flex--center">
            <b class="elipsis">{{ calcRowsResult(tableHeader) }}</b>
        </div>

        <div v-else="" class="flex flex--center">
            <select
                    v-if="editing"
                    v-model="tableHeader.default_stats"
                    ref="inline_input"
                    @blur="hideEdit()"
                    class="form-control cell-input">
                <option></option>
                <option v-for="frm in $root.availRowSumFormulas" :value="frm">{{ String(frm).toUpperCase() }}</option>
            </select>

            <b class="elipsis" v-else="">{{ showVal(tableHeader.default_stats) }}</b>
        </div>
    </td>
</template>

<script>
    import CellStyleMixin from "./../_Mixins/CellStyleMixin.vue";

    export default {
        name: "RowsSumCellBlock",
        mixins: [
            CellStyleMixin,
        ],
        data: function () {
            return {
                editing: false,
            };
        },
        props:{
            tableHeader: Object,
            calcHeaderFunc: Boolean,
            allRows: Array,
        },
        computed: {
            sumCellStyle() {
                let style = this.textStyle;
                style.width = this.tdCellWidth(this.tableHeader)+'px';
                return style;
            },
        },
        methods: {
            // MAIN FUNC
            calcRowsResult(header) {
                let def_stat = String(header.default_stats).toLowerCase();
                if (!def_stat) {
                    return '';
                }

                let result;
                if (!this.allRows.length) {
                    result = NaN;
                } else {
                    switch (def_stat) {
                        //#app_avail_formulas
                        case 'count': result = this.countFunc(header.field); break;
                        case 'countunique': result = this.countuniqueFunc(header.field); break;
                        case 'sum': result = this.sumFunc(header.field); break;
                        case 'min': result = this.minFunc(header.field); break;
                        case 'max': result = this.maxFunc(header.field); break;
                        case 'mean': result = this.avgFunc(header.field); break;
                        case 'avg': result = this.meanFunc(header.field); break;
                        case 'var': result = this.varFunc(header.field); break;
                        case 'std': result = this.stdFunc(header.field); break;
                    }
                }
                return isNaN(result) ? '' : result;
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
            showVal(val) {
                return val ? String(val).toUpperCase() : '';
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