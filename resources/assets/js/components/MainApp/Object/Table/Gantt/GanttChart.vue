<template>
    <div class="full-height" style="padding: 5px;">
        <div class="full-frame" ref="wrapper">
            <div ref="chart" @contextmenu.prevent="showRow"></div>

            <div v-if="warnMessage" class="full-height white bold flex flex--center" v-html="warnMessage"></div>
        </div>

        <custom-edit-pop-up
                v-if="tableMeta && editPopupRow"
                :global-meta="tableMeta"
                :table-meta="tableMeta"
                :table-row="editPopupRow"
                :settings-meta="$root.settingsMeta"
                :role="editPopupRole"
                :input_component_name="$root.tdCellComponent(tableMeta.is_system)"
                :behavior="'list_view'"
                :user="$root.user"
                :cell-height="1"
                :max-cell-rows="0"
                @popup-insert="insertRow"
                @popup-update="updateRow"
                @popup-copy="copyRow"
                @popup-delete="deleteRow"
                @show-src-record="showSrcRecord"
                @popup-close="closePopUp"
        ></custom-edit-pop-up>
    </div>
</template>

<script>
    import {SpecialFuncs} from "../../../../../classes/SpecialFuncs";
    import {Endpoints} from "../../../../../classes/Endpoints";

    import {eventBus} from '../../../../../app';

    import CellStyleMixin from "./../../../../_Mixins/CellStyleMixin.vue";
    import MixinForAddons from "./../MixinForAddons";
    
    import CustomEditPopUp from "../../../../CustomPopup/CustomEditPopUp";

    export default {
        name: "GanttChart",
        mixins: [
            CellStyleMixin,
            MixinForAddons,
        ],
        components: {
            CustomEditPopUp
        },
        data: function () {
            return {
                warnMessage: '',
                highchart: null,
                curSelRow: null,
                defColors: [
                    '#7cb5ec','#434348','#90ed7d','#f7a35c','#8085e9',
                    '#f15c80','#e4d354','#2b908f','#f45b5b','#91e8e1'
                ],
                defIdx: 0,
                redrawOnEdit: true,
            }
        },
        props: {
            tableMeta: Object,
            requestParams: Object,//MixinForAddon
            currentPageRows: Array,//MixinForAddon
            selectedGantt: Object,
            selectedState: Object,
            add_click: Number,
        },
        computed: {
        },
        watch: {
        },
        methods: {
            getClr() {
                let clr = this.defColors[this.defIdx];
                this.defIdx++;
                this.defIdx = (this.defIdx >= this.defColors.length ? 0 : this.defIdx);
                return clr;
            },
            //Helpers
            getChartElem(cats, obj, row, parent_row, nodrag) {
                let comple = to_float( row[obj.progress_fld] );
                let el = {
                    completed: comple > 1 ? comple/100 : comple,
                    symbol_lab: row[obj.symbol_fld] ? String(row[obj.symbol_fld]) : undefined,
                    dependency: row[obj.parent_fld] ? String(row[obj.parent_fld]) : undefined,
                    parent: parent_row && parent_row.id != row.id ? String(parent_row.id) : undefined,
                    name: String( this.$root.rcShow(row, obj.group_fld) ),
                    label: row[obj.name_fld] ? String( this.$root.rcShow(row, obj.name_fld) ) : undefined,
                    start: moment.utc( row[obj.start_fld] ).valueOf(),
                    end: moment.utc( row[obj.end_fld] ).valueOf(),
                    id: String(row.id),
                    y: cats.indexOf(row[obj.group_fld]),
                    color: row[obj.color_fld] ? String(row[obj.color_fld]) : this.getClr(),
                    milestone: row[obj.milestone_fld] ? (!!row[obj.milestone_fld]) : undefined,
                };
                if (nodrag) {
                    el.dragDrop = {
                        draggableX: false,
                        draggableY: false,
                    };
                }

                _.each(obj.xTable, (fld) => {
                    el['ext_'+fld.field] = row[fld.field];
                });
                _.each(obj.xTooltip, (fld) => {
                    el['ext_'+fld.field] = row[fld.field];
                });

                el['ext_'+obj.name_fld] = row[obj.name_fld];
                el['ext_'+obj.start_fld] = row[obj.start_fld];
                el['ext_'+obj.end_fld] = row[obj.end_fld];
                el['ext_'+obj.group_fld] = row[obj.group_fld];
                return el;
            },
            getFldObj() {
                //Fields settings
                return {
                    main_group_fld: ( _.find(this.tableMeta._fields, (fld) => {
                            return _.find(this.selectedGantt._specifics, {table_field_id: Number(fld.id), is_gantt_main_group: 1});
                        }) || {} ).field,

                    parent_group_fld: ( _.find(this.tableMeta._fields, (fld) => {
                            return _.find(this.selectedGantt._specifics, {table_field_id: Number(fld.id), is_gantt_parent_group: 1});
                        }) || {} ).field,

                    group_fld: ( _.find(this.tableMeta._fields, (fld) => {
                            return _.find(this.selectedGantt._specifics, {table_field_id: Number(fld.id), is_gantt_group: 1});
                        }) || {} ).field,

                    name_fld: ( _.find(this.tableMeta._fields, (fld) => {
                            return _.find(this.selectedGantt._specifics, {table_field_id: Number(fld.id), is_gantt_name: 1});
                        }) || {} ).field,

                    start_fld: ( _.find(this.tableMeta._fields, (fld) => {
                            return _.find(this.selectedGantt._specifics, {table_field_id: Number(fld.id), is_gantt_start: 1});
                        }) || {} ).field,

                    end_fld: ( _.find(this.tableMeta._fields, (fld) => {
                            return _.find(this.selectedGantt._specifics, {table_field_id: Number(fld.id), is_gantt_end: 1});
                        }) || {} ).field,

                    color_fld: ( _.find(this.tableMeta._fields, (fld) => {
                            return _.find(this.selectedGantt._specifics, {table_field_id: Number(fld.id), is_gantt_color: 1});
                        }) || {} ).field,

                    parent_fld: ( _.find(this.tableMeta._fields, (fld) => {
                            return _.find(this.selectedGantt._specifics, {table_field_id: Number(fld.id), is_gantt_parent: 1});
                        }) || {} ).field,

                    progress_fld: ( _.find(this.tableMeta._fields, (fld) => {
                            return _.find(this.selectedGantt._specifics, {table_field_id: Number(fld.id), is_gantt_progress: 1});
                        }) || {} ).field,

                    symbol_fld: ( _.find(this.tableMeta._fields, (fld) => {
                            return _.find(this.selectedGantt._specifics, {table_field_id: Number(fld.id), is_gantt_label_symbol: 1});
                        }) || {} ).field,

                    milestone_fld: ( _.find(this.tableMeta._fields, (fld) => {
                            return _.find(this.selectedGantt._specifics, {table_field_id: Number(fld.id), is_gantt_milestone: 1});
                        }) || {} ).field,

                    xTable: _.filter(this.tableMeta._fields, (fld) => {
                            return _.find(this.selectedGantt._specifics, {table_field_id: Number(fld.id), gantt_left_header: 1});
                        }),

                    xTooltip: _.filter(this.tableMeta._fields, (fld) => {
                            return _.find(this.selectedGantt._specifics, {table_field_id: Number(fld.id), gantt_tooltip: 1});
                        }),
                };
            },
            getCategories(obj) {
                let cats = _.map(this.tableRows, (row) => { return this.$root.rcShow(row, obj.group_fld); });
                //let parents = _.map(this.tableRows, (row) => { return this.$root.rcShow(row, obj.parent_group_fld); });
                //let mains = _.map(this.tableRows, (row) => { return this.$root.rcShow(row, obj.main_group_fld); });
                return ( _.uniq(cats) ).sort();
            },
            recursiveGroup(cats, obj, dataRows, groupFlds, parentRow) {
                let seris = [];
                if (groupFlds && groupFlds.length) {
                    let grFld = _.first(groupFlds);
                    let groupRows = _.groupBy(dataRows, grFld);
                    _.each(groupRows, (group) => {
                        let subParentRow = _.cloneDeep( _.minBy(group, obj.start_fld) );
                        let maxrow = _.maxBy(group, obj.end_fld);

                        (obj.milestone_fld ? subParentRow[obj.milestone_fld] = false : '');
                        subParentRow[obj.end_fld] = maxrow[obj.end_fld];
                        subParentRow[obj.group_fld] = this.$root.rcShow(maxrow, grFld);//maxrow[grFld];
                        subParentRow[obj.name_fld] = this.$root.rcShow(maxrow, grFld);//maxrow[grFld];
                        subParentRow.id = uuidv4();

                        if (obj.progress_fld) { // apply min to 'virtual line'
                            let minprog = _.minBy(group, (ingroup) => {
                                let comple = to_float( ingroup[obj.progress_fld] );
                                return comple > 1 ? comple/100 : comple;
                            });
                            subParentRow[obj.progress_fld] = minprog[obj.progress_fld];
                        }

                        seris.push(this.getChartElem(cats, obj, subParentRow, parentRow, 'nodrag'));

                        let lowerGroupFlds = _.filter(groupFlds, (el) => { return el != grFld; });
                        let subseris = this.recursiveGroup(cats, obj, group, lowerGroupFlds, subParentRow);
                        seris = seris.concat(subseris);
                    });
                } else {
                    _.each(dataRows, (rr) => {
                        seris.push(this.getChartElem(cats, obj, rr, parentRow));
                    });
                }
                return seris;
            },
            buildTree(cats, obj) {
                let groups = [];
                if (obj.parent_group_fld) {
                    groups.push(obj.parent_group_fld);//Lvl1
                }
                if (obj.main_group_fld) {
                    groups.push(obj.main_group_fld);//Lvl2
                }
                return this.recursiveGroup(cats, obj, this.tableRows, groups);
            },

            //Build chart
            drawChart() {
                let scrollTop = this.$refs.wrapper.scrollTop;
                this.$nextTick(() => {
                    let obj = this.getFldObj();

                    //Required fields
                    if (!obj.name_fld || !obj.start_fld || !obj.end_fld || !obj.group_fld) {
                        this.warnMessage = [];
                        (!obj.name_fld ? this.warnMessage.push('"Bars,Items" is empty!') : '');
                        (!obj.start_fld ? this.warnMessage.push('"Bars,Start" is empty!') : '');
                        (!obj.end_fld ? this.warnMessage.push('"Bars,End" is empty!') : '');
                        (!obj.group_fld ? this.warnMessage.push('"Header,Rows" is empty!') : '');
                        this.warnMessage = this.warnMessage.join('<br>');
                        return;
                    }

                    //Data Series
                    let cats = this.getCategories(obj);
                    let seris = this.buildTree(cats, obj);
                    let day = 1000 * 60 * 60 * 24;

                    //Chart Params
                    let param = {

                        chart: {
                            spacingRight: 25,
                        },

                        plotOptions: {
                            series: {
                                animation: false, // Do not animate dependency connectors
                                dragDrop: {
                                    draggableX: true,
                                    draggableY: true,
                                    //dragMinY: 0,
                                    //dragMaxY: cats.length-1,
                                    dragPrecisionX: day, // Snap to 1day
                                },
                                dataLabels: {
                                    enabled: !!this.selectedGantt.gantt_show_names,
                                    formatter: function() {
                                        return obj.progress_fld ? (this.point.completed*100)+'%' : this.point.label;
                                    },
                                },
                                allowPointSelect: true,
                                point: {
                                    events: {
                                        select: this.selectListener,
                                        drop: this.dropListener,
                                    }
                                }
                            }
                        },

                        yAxis: {
                            type: obj.parent_group_fld ? 'treegrid' : 'category',
                            uniqueNames: true,
                        },

                        xAxis: {
                            currentDateIndicator: true,
                        },

                        tooltip: {},

                    };

                    param.series = [{
                        name: '',
                        data: seris,
                    }];
                    //Highlight elem only for 'GROUP' functions
                    // if (obj.parent_group_fld && !!this.selectedGantt.gantt_highlight) {
                    //     param.series = _.map(seris, (elem) => {
                    //         return {
                    //             name: elem.name,
                    //             data: [elem],
                    //         };
                    //     });
                    // } else {
                    //     param.series = [{
                    //         name: '',
                    //         data: seris,
                    //     }];
                    // }

                    //Tooltip settings
                    if (obj.xTooltip.length) {
                        param.tooltip.pointFormatter = function () {
                            let point = this;
                            let str = [];
                            _.each(obj.xTooltip, (fld) => {
                                str.push('<span>'+point['ext_'+fld.field]+'</span>');
                            });
                            return str.join('<br>');
                        }
                    }

                    //Left Grid
                    if (obj.xTable.length) {
                        let grid_cols = [];
                        _.each(obj.xTable, (fld) => {
                            grid_cols.push({
                                title: {
                                    text: fld.name,
                                },
                                labels: {
                                    format: '{point.ext_'+fld.field+'}',
                                }
                            });
                        });
                        param.yAxis.grid = {
                            enabled: true,
                            borderColor: 'rgba(0,0,0,0.3)',
                            borderWidth: 1,
                            columns: grid_cols,
                        };
                    } else {
                        //Use Names
                        param.yAxis.categories = cats;
                        //param.yAxis.min = 0;
                        //param.yAxis.max = cats.length-1;
                    }

                    //Data Label
                    if (obj.symbol_fld) {
                        param.series[0].dataLabels = [{
                            enabled: true,
                            format: '<div style="width: 20px; height: 20px; overflow: hidden; margin-left: -25px" class="flex flex--center">' +
                                '<img src="'+this.$root.clear_url+'/file/{point.symbol_lab}" style="max-height: 100%; max-width: 100%;"></div>',
                            useHTML: true,
                            align: 'left'
                        }, {
                            enabled: !!this.selectedGantt.gantt_show_names,
                            formatter: function() {
                                return obj.progress_fld ? (this.point.completed*100)+'%' : this.point.label;
                            },
                        }];
                    }

                    //Navigation
                    if (this.selectedGantt.gantt_navigation) {
                        param.rangeSelector = {
                            enabled: true,
                        };
                    }
                    if (this.selectedGantt.gantt_navigator_bottom) {
                        param.navigator = {
                            enabled: true,
                            liveRedraw: true,
                            series: {
                                type: 'gantt',
                                pointPlacement: 0.5,
                                pointPadding: 0.25,
                            },
                            yAxis: {
                                //min: 0,
                                //max: cats.length-1,
                                reversed: true,
                                categories: cats,
                            },
                            height: to_float(this.selectedGantt.gantt_navigator_height),
                        };
                        param.scrollbar = {
                            enabled: true,
                        };
                    }

                    //DateTime format for xAxis
                    param.xAxis.dateTimeLabelFormats = {
                        millisecond: '%H:%M:%S.%L',
                        second: '%H:%M:%S',
                        minute: '%H:%M',
                        hour: '%H:%M',
                        day: '%E',//day: '%a',
                        week: 'Week %W',
                        month: '%b',
                        year: '%Y'
                    };
                    if (this.selectedGantt.gantt_day_format === 'part') {
                        param.xAxis.dateTimeLabelFormats.day = '%a';
                    }
                    if (this.selectedGantt.gantt_day_format === 'full') {
                        param.xAxis.dateTimeLabelFormats.day = '%A';
                    }
                    if (this.selectedGantt.gantt_day_format === 'extend') {
                        param.xAxis.dateTimeLabelFormats.day = '%A, %m/%d/%Y';
                    }

                    //set RowHeight
                    param.yAxis.staticScale = to_float(this.selectedGantt.gantt_row_height);
                    param.xAxis.grid = {
                        cellHeight: to_float(this.selectedGantt.gantt_row_height)
                    };
                    param.xAxis = [param.xAxis, param.xAxis];//set to both top xAxis

                    //Tick Interval
                    switch (this.selectedGantt.gantt_info_type) {
                        case 'day':
                            param.xAxis[1].tickInterval = day/24;
                            param.xAxis[0].tickInterval = 1*day;
                            break;
                        case 'week':
                            param.xAxis[1].tickInterval = 1*day;
                            param.xAxis[0].tickInterval = 7*day;
                            break;
                        case 'month':
                            param.xAxis[1].tickInterval = 7*day;
                            param.xAxis[0].tickInterval = 30*day;
                            break;
                        case 'year':
                            param.xAxis[1].tickInterval = 30*day;
                            param.xAxis[0].tickInterval = 365*day;
                            break;
                    }

                    //Draw Chart
                    try {
                        this.highchart = Highcharts.ganttChart(this.$refs.chart, param);
                        this.$nextTick(() => {
                            $(this.$refs.wrapper).scrollTop(scrollTop);
                        });
                    } catch (e) {
                        Swal('Info','Incorrect Data or Settings!');
                    }

                    //clear colors index
                    this.defIdx = 0;
                });
            },
            dropListener(e) {
                let tableRow = _.find(this.tableRows, {id: to_float(e.target ? e.target.id : null)});
                let obj = this.getFldObj();

                if (tableRow && obj.name_fld && obj.start_fld && obj.end_fld && obj.group_fld) {
                    let cats = this.getCategories(obj);
                    if (!obj.parent_group_fld && !obj.main_group_fld) {
                        tableRow[obj.group_fld] = cats[to_float(e.target.y)];
                    }
                    tableRow[obj.start_fld] = moment.utc( e.target.start ).format('YYYY-MM-DD');
                    tableRow[obj.end_fld] = moment.utc(e.target.end).format('YYYY-MM-DD');
                    this.updateRow(tableRow);
                }

                return true;
            },
            selectListener(e) {
                this.curSelRow = _.find(this.tableRows, {id: to_float(e.target ? e.target.id : null)});
            },
            showRow() {
                if (this.curSelRow) {
                    this.popupClick(this.curSelRow.id);
                }
            },

            drawAddon() {
                this.drawChart();
            },
            mountedFunc() {
                this.getRows(this.selectedGantt.gantt_data_range, 'gantt', this.selectedGantt.id);
            },
        },
        mounted() {
            this.mountedFunc();
            eventBus.$on('new-request-params', this.mountedFunc);
            eventBus.$on('redraw-gannt', this.mountedFunc);
        },
        beforeDestroy() {
            eventBus.$off('new-request-params', this.mountedFunc);
            eventBus.$off('redraw-gannt', this.mountedFunc);
        }
    }
</script>

<style lang="scss" scoped>
</style>