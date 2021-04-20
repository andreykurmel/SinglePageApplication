<template>
    <div class="chart-row full-height">
        <table ref="tb" class="full-frame table-layout">
            <tr>
                <td
                    v-for="(col_group, idx) in chartRow"
                    :style="cellStyle(col_group)"
                >
                    <div v-for="(ch, key) in col_group" v-if="ch" class="chart-elem" :style="chartStyle(ch, key, col_group.length)">
                        <bi-chart-element
                            :row-idx="rowIdx"
                            :col-idx="tonum(idx)"
                            :table-meta="tableMeta"
                            :request_params="request_params"
                            :row_state_hash="row_state_hash"
                            :export_name="export_name"
                            :chart="ch"
                            :can-edit="ch._can_edit"
                            :is-visible="isVisible"
                            :is-transparent="isTransparent"
                            :precached-datas="precachedDatas"

                            @add-chart="addedChartStore"
                            @del-chart="delChart"
                            @clone-vertical="cloneChartVert"
                            @show-src-record="showSrcRecord"

                            @drag-chart="(ch) => { $emit('drag-chart', ch) }"
                            @drop-chart="(ch, type) => { $emit('drop-chart', ch, type) }"
                        ></bi-chart-element>
                    </div>
                </td>
                <td :style="{width: '1px'}" v-if="canCreate && bi_setts.can_add">
                    <div class="chart-elem full-height add-chart-wrapper">
                        <span class="glyphicon glyphicon-plus-sign add-chart-btn" @click="addChartStart()"></span>
                    </div>
                </td>
            </tr>
        </table>
    </div>
</template>

<script>
    import {SpecialFuncs} from './../../../../../classes/SpecialFuncs';

    import {ChartFunctions} from './ChartFunctions';

    import BiChartElement from './BiChartElement';

    export default {
        name: "BiChartRow",
        components: {
            BiChartElement,
        },
        data: function () {
            return {
                bi_setts: ChartFunctions.settsFromMeta(this.tableMeta, this),
            }
        },
        props:{
            rowIdx: Number|String,
            chartRow: Object,
            tableMeta: Object,
            request_params: Object,
            chart_height: Number,
            canCreate: Boolean,
            isVisible: Boolean,
            isTransparent: Boolean,
            row_state_hash: String,
            export_name: String,
            precachedDatas: Object,
        },
        methods: {
            //show link
            showSrcRecord(lnk, header, tableRow) {
                this.$emit('show-src-record', lnk, header, tableRow, 'bi_module');
            },
            tonum(idx) {
                return to_float(idx);
            },

            //styles
            cellStyle(col_group) {
                let obj = {};
                let maxwi = 0;
                _.each(col_group, (ch) => {
                    try {
                        maxwi = Math.max(maxwi, ch.chart_settings.dimensions.width);
                    } catch (e) {}
                });
                if (maxwi) {
                    obj.width = maxwi+'%';
                    obj.maxWidth = '1px';
                }
                return obj;
            },
            chartStyle(ch, key, len) {
                return {
                    height: ch.chart_settings.dimensions.height
                        ? ch.chart_settings.dimensions.height+'px'
                        : (ch.chart_settings.elem_type === 'bi_chart' ? '450px' : ''),
                    border: this.isTransparent ? 'none' : '1px solid #CCC',
                    marginBottom: key !== len-1 ? this.bi_setts.cell_height+'px' : null,
                }
            },
            addChartStart() {
                let key = SpecialFuncs.lastKey(this.chartRow);
                this.$set(this.chartRow, String(key), [{
                    id: null,
                    col_idx: key,
                    row_idx: this.rowIdx,
                    user_id: this.$root.user.id,
                    table_id: this.tableMeta.id,
                    chart_settings: ChartFunctions.emptySettings(this.chart_height),
                    cached_data: [],
                    _can_edit: true,
                }]);
            },
            addedChartStore(chart) {
                this.$emit('chart-addition', chart);
            },
            delChart(chart) {
                this.$emit('chart-delete', chart);
            },
            cloneChartVert(chart) {
                this.$emit('clone-chart', chart);
            },
        },
        created() {
            if (!Object.keys(this.chartRow).length) {
                this.addChartStart();
            }
        }
    }
</script>

<style lang="scss" scoped>
    .chart-row {
        //overflow: hidden;

        .table-layout {
            background-color: inherit !important;
            border-collapse: initial !important;

            td {
                position: relative;
                padding: 5px 0 0 5px;

                .chart-elem {
                    border: 1px solid #CCC;
                    border-radius: 5px;
                    background-color: #FFF;
                    min-width: 75px;
                    min-height: 50px;
                    //overflow: auto;
                }

                .add-chart-wrapper {
                    display: flex;
                    justify-content: center;
                    align-items: center;
                    min-width: 15px;

                    span {
                        opacity: 0.5;
                        cursor: pointer;

                        &:hover {
                            opacity: 1;
                        }
                    }
                }

                &:last-child {
                    padding: 5px 5px 0 5px;
                }
            }
        }
    }
</style>