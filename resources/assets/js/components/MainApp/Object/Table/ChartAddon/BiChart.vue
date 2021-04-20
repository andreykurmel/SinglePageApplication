<template>
    <div class="full-frame">
        <div ref="chart" class="full-height"></div>
    </div>
</template>

<script>
    import {eventBus} from './../../../../../app';

    export default {
        name: "BiChart",
        components: {
        },
        data: function () {
            return {
                x_datas: [],
                y_series: [],
                dec: 0,
                currency: false,
                highchart: null,
                total_groups_l1: [],
                add_groups_l2: [],
            }
        },
        props:{
            tableMeta: Object,
            all_settings: Object,
            chart_data: Array,
            export_name: String,
        },
        computed: {
        },
        watch: {
        },
        methods: {
            //PREPARE
            prepareData() {
                this.x_datas = [];
                this.y_series = [];

                let x_axis = this.all_settings.bi_chart.x_axis;
                let y_axis = this.all_settings.bi_chart.y_axis;
                let y_axis_col = _.find(this.tableMeta._fields, {field: y_axis.field});

                if (!x_axis.field || !y_axis.field || !y_axis_col) {
                    return;
                }

                let col_name = this.$root.uniqName( y_axis_col.name );

                this.currency = y_axis_col.f_type === 'Currency';
                this.dec = y_axis_col.f_size - parseInt(y_axis_col.f_size);
                this.dec = Math.round(this.dec*10);

                //filter empty rows for x_axis.field.
                //let all_rows = _.filter(this.chart_data, (row) => { return !!row['x']; });
                let all_rows = this.chart_data;

                //group l1,l2 all_rows if needed
                this.total_groups_l1 = [col_name];
                this.add_groups_l2 = [];

                //X-AXIS PREPARING --->
                this.x_datas = _.uniq( _.map(all_rows, 'x') );
                //<--- X-AXIS PREPARING

                //Y-AXIS PREPARING --->
                if (!x_axis.l1_group_fld || x_axis.l1_group_fld === x_axis.field) {
                    //group x-values with the same name
                    let idx = 0, y = [];
                    _.each(this.x_datas, (x) => {
                        y[ idx++ ] = this.findGroupVal(all_rows, x);
                    });
                    this.pushYseries(y, col_name);
                } else {
                    //group x-values with the same name
                    this.total_groups_l1 = _.keys( _.groupBy(all_rows, 'l1') );

                    if (x_axis.l2_group_fld) {
                        //2 lvl itemization
                        this.add_groups_l2 = _.keys( _.groupBy(all_rows, 'l2') );
                        _.each(this.total_groups_l1, (l1_group) => {
                            _.each(this.add_groups_l2, (group_l2) => {
                                let idx = 0, y = [];
                                _.each(this.x_datas, (x) => {
                                    y[ idx++ ] = this.findGroupVal(all_rows, x, l1_group, group_l2);
                                });
                                this.pushYseries(y, l1_group, group_l2);
                            });
                        });
                    } else {
                        //1 lvl itemization
                        _.each(this.total_groups_l1, (l1_group) => {
                            let idx = 0, y = [];
                            _.each(this.x_datas, (x) => {
                                y[ idx++ ] = this.findGroupVal(all_rows, x, l1_group);
                            });
                            this.pushYseries(y, l1_group);
                        });
                    }

                }

                let grlen = this.total_groups_l1.length+1;
                let addlen = this.add_groups_l2.length+1;
                if (grlen*addlen > 100) {
                    this.x_datas = [];
                    this.y_series = [];
                    this.$emit('too-much-groups');
                }
                //<--- Y-AXIS PREPARING
            },
            pushYseries(data, group, add_group) {
                this.y_series.push({
                    id: window.uuidv4(),
                    name: group + (add_group ? ' ('+add_group+')' : ''),
                    data: data,
                    stack: group,
                    l1: group,
                    l2: add_group
                });
            },
            findGroupVal(all_rows, x, l1, l2) {
                let res = 0;
                _.each(all_rows, (el) => {
                    if (
                        el.x == x
                        &&
                        (l1===undefined || el.l1 == l1)
                        &&
                        (l2===undefined || el.l2 == l2)
                    ) {
                        res = el.y;
                    }
                });
                return this.parseFloatAdv(res);
            },
            parseFloatAdv(str) {
                return str ? parseFloat( String(str).replace(/,/gi, '') ) : 0;
            },

            //DATA XRANGE
            prepareDataXrange() {
                let top = this.all_settings.bi_chart.tslh.top;
                let bottom = this.all_settings.bi_chart.tslh.bottom;
                let start = this.all_settings.bi_chart.tslh.start;
                let end = this.all_settings.bi_chart.tslh.end;

                let fld = _.find(this.tableMeta._fields, {field: top});
                let top_unit = fld ? ' '+(fld.unit || fld.unit_display) : '';
                fld = _.find(this.tableMeta._fields, {field: bottom});
                let bottom_unit = fld ? ' '+(fld.unit || fld.unit_display) : '';

                if (!top || !bottom || !start || !end) {
                    return;
                }

                let all_rows = this.chart_data;

                //X-AXIS PREPARING --->
                let minX, maxX;
                _.each(all_rows, (row) => {
                    if (minX === undefined || minX > Number(row.bottom)) {
                        minX = Number(row.bottom);
                    }
                    if (maxX === undefined || maxX < Number(row.top)) {
                        maxX = Number(row.top);
                    }
                });
                minX *= 0.9;
                maxX *= 1.1;

                let step = (maxX-minX)/200;//max 200 row in the graph

                this.x_datas = [minX];
                let i = minX;
                while (i <= maxX) {
                    i += Number(step);
                    this.x_datas.push( i );
                }
                //<--- X-AXIS PREPARING

                //Y-AXIS PREPARING --->
                this.y_series = [];
                let colors = ['#2f7ed8', '#0d233a', '#8bbc21', '#910000', '#1aadce', '#492970', '#f28f43', '#77a1e5', '#c42525', '#a6c96a'];
                _.each(all_rows, (row, clr_idx) => {
                    let c_idx = clr_idx % 10;
                    let added = false;
                    _.each(this.x_datas, (col, idx_col) => {
                        if (Number(col) > Number(row.bottom-step) && Number(col) < Number(row.top-step)) {
                            this.y_series.push({
                                x: Date.parse(row.x),
                                x2: Date.parse(row.x2),
                                y: idx_col,
                                color: colors[c_idx],
                                tooltip1: this.$root.uniqName(row.tooltip1),
                                tooltip2: this.$root.uniqName(row.tooltip2),
                                front_fld: (!added ? this.$root.uniqName(row.front_fld) : ''),
                                d_range_x: moment(row.x).format('YYYY-MM-DD'),
                                d_range_x2: moment(row.x2).format('YYYY-MM-DD'),
                                d_range_y: row.bottom + bottom_unit,
                                d_range_y2: row.top + top_unit,
                                bottom: row.bottom,
                                top: row.top,
                            });
                            added = true;
                        }
                    });
                });
                //<--- Y-AXIS PREPARING

            },

            // DRAW
            buildChart() {
                this.$nextTick(function () {
                    let chart_settings = this.all_settings.bi_chart;

                    let y_label = '';
                    if (chart_settings.labels.y_label && chart_settings.chart_sub_type !== 'TSLH') {
                        y_label = chart_settings.labels.y_label
                            +' ('
                            + chart_settings.y_axis.group_function
                            +')';
                    } else {
                        y_label = chart_settings.labels.y_label;
                    }

                    let x_cols, graph_data, tslh_cols, tslh_data,  x_formatter;

                    //PREPARE DATAS
                    if (chart_settings.chart_sub_type !== 'TSLH') {

                        x_cols = this.x_datas;
                        graph_data = this.y_series;
                        //special rules for chart type = 'pie'
                        if (chart_settings.chart_sub_type === 'pie' && graph_data.length) {
                            _.each(graph_data[0].data, (val, idx) => {
                                graph_data[0].data[idx] = {
                                    name: x_cols[idx],
                                    y: val
                                };
                            });
                            graph_data.splice(1);//'pie' is available only without grouping
                        }

                    } else {

                        tslh_cols = this.x_datas;
                        tslh_data = [];
                        tslh_data.push({
                            data: this.y_series,
                            tooltip: {
                                pointFormat: '<span>{point.tooltip1}</span><br>' +
                                '<span>{point.tooltip2}</span><br>' +
                                '<span>{point.d_range_y} To {point.d_range_y2}</span><br>' +
                                '<span>{point.d_range_x} To {point.d_range_x2}</span><br>',
                                headerFormat: ''
                            }
                        });
                        x_formatter = _.concat(
                            _.map(this.y_series, (elem) => {
                                return elem.x;
                            }),
                            _.map(this.y_series, (elem) => {
                                return elem.x2;
                            })
                        );
                        x_formatter = _.uniq(x_formatter);
                    }
                    //^^^^^^^
                    let dim_height = this.all_settings.dimensions.height || 450;

                    this.highchart = Highcharts.chart(this.$refs.chart, {

                        chart: {
                            type: chart_settings.chart_sub_type === 'TSLH' ? 'xrange' : chart_settings.chart_sub_type,
                            height: dim_height-55,
                        },

                        title: {
                            text: chart_settings.labels.general
                        },

                        legend: {
                            enabled: chart_settings.chart_sub_type !== 'TSLH' && chart_settings.show_legend
                        },

                        plotOptions: {
                            pie: {
                                dataLabels: {
                                    format: '<b>{point.name}</b>: '
                                    + (this.currency && chart_settings.y_axis.calc_val == 1 ? '$' : '')
                                    + '{point.y:.'+this.dec+'f}',
                                }
                            },
                            series: {
                                stacking: chart_settings.chart_type == 'grouped_stacked' ? 'normal' : ''
                            },
                            xrange: {
                                dataLabels: {
                                    align: 'left',
                                    verticalAlign: 'bottom',
                                    enabled: true,
                                    format: '<b>{point.front_fld}</b>',
                                },
                                pointPadding: 0,
                                borderWidth: 0,
                                groupPadding: 0,
                                borderRadius: 0,
                                pointWidth: Math.ceil(dim_height/200),
                            },
                        },

                        xAxis: {
                            type: chart_settings.chart_sub_type === 'TSLH' ? 'datetime' : 'linear',
                            categories: x_cols,//for other chart types
                            title: {
                                text: chart_settings.labels.x_label
                            },
                            tickPositions: chart_settings.chart_sub_type === 'TSLH' ? x_formatter : undefined,
                            labels: {
                                formatter: function() {
                                    return chart_settings.chart_sub_type === 'TSLH'
                                        ? moment(this.value).format('YYYY-MM-DD')
                                        : this.value;
                                },
                                rotation: chart_settings.chart_sub_type === 'TSLH' ? 70 : 0,
                            },
                        },

                        yAxis: {
                            tickmarkPlacement: chart_settings.chart_sub_type === 'TSLH' ? 'on' : 'between',
                            categories: tslh_cols,//for x-range
                            labels: {
                                formatter: function() {
                                    return chart_settings.chart_sub_type === 'TSLH'
                                        ? Math.round(this.value)
                                        : this.value;
                                },
                            },
                            title: {
                                text: y_label
                            },
                        },

                        tooltip: {
                            valueDecimals: this.dec,
                            valuePrefix: (this.currency && chart_settings.y_axis.calc_val == 1 ? '$' : '')
                        },

                        exporting: {
                            filename: this.export_name+'chart'+' '+moment().format('YYYY-MM-DD HH_mm_ss')
                        },

                        series: chart_settings.chart_sub_type === 'TSLH' ? tslh_data : graph_data
                    });
                });
            },
            DrawChart() {
                if (this.all_settings.bi_chart.chart_sub_type === 'TSLH') {
                    this.prepareDataXrange();
                } else {
                    this.prepareData();
                }
                this.buildChart();
            },
        },
        created() {
        },
        mounted() {
            this.DrawChart();
            eventBus.$on('redraw-gannt', this.DrawChart);
        },
        beforeDestroy() {
            if (this.highchart) {
                this.highchart.destroy();
                this.highchart = null;
            }
            eventBus.$on('redraw-gannt', this.DrawChart);
        }
    }
</script>

<style lang="scss" scoped>
</style>