<template>
    <div class="full-height chart-wrapper">
        <div class="left-elements no-gs-drag" v-if="chart.chart_settings && chart.chart_settings.wait_for_update">
            <i class="fas fa-sync" aria-hidden="true" @click="saveOrDelChart('__update_cache')"></i>
        </div>

        <div class="right-elements no-gs-drag" v-if="!!biSettings.bi_can_settings && canChartEdit()">
            <chart-dimensions-button
                :chart_hash="chart.__gs_hash"
                :all_settings="chart.chart_settings"
                :can_edit="canEdit"
                @dimensions-changed="dimChange"
                @clone-vertical="cloneVertical"
                @delete-chart="saveOrDelChart('', true)"
                @open-settings="opnSettings()"
                @refresh-chart="saveOrDelChart('__update_cache')"
            >
                <template v-slot:dwn_button>
                    <chart-export-button :can_action="activeTab === 'table'" :chart_uuid="chart_uuid" :export_prefix="export_prefix"></chart-export-button>
                </template>
            </chart-dimensions-button>
        </div>

        <div class="chart-body" ref="chart_body" :style="{
            backgroundColor: (chart.chart_settings.dimensions.back_color || '#FFF'),
            borderRadius: (biSettings.bi_corner_radius || 0)+'px',
        }">

            <!--TEXT DATA TAB-->
            <div class="full-frame" v-if="activeTab === 'text'">
                <div class="" v-html="parseTextContent()"></div>
            </div>

            <!--NO FIELDS-->
            <div v-else-if="!requiredChart" class="full-height flex flex--center bold">Required fields: (X), (Y)</div>
            <div v-else-if="!requiredTable" class="full-height flex flex--center bold">Required fields: (About)</div>
            <div v-else-if="data_receiving_error" class="full-height flex flex--center bold">Data receiving error! Please check chart settings.</div>

            <!--NO DATA-->
            <div v-else-if="!chart_data" class="full-height flex flex--center">
                <img height="75" src="/assets/img/Loading_icon.gif">
            </div>

            <!--CHART TAB-->
            <div class="full-frame" v-else-if="activeTab === 'chart'">
                <bi-chart
                    v-if="chart_data && draw_highchart"
                    :table-meta="tableMeta"
                    :chart_data="chart_data"
                    :all_settings="chart.chart_settings"
                    :export_prefix="export_prefix"
                    :request_params="request_params"
                    @too-much-groups="chartTooMuchGroups"
                ></bi-chart>
            </div>

            <!--PIVOT TABLE TAB-->
            <div class="full-frame" v-else-if="activeTab === 'table'">
                <bi-pivot-table
                    v-if="table_data"
                    :table_meta="tableMeta"
                    :table_data="table_data"
                    :table_data_2="table_data_2"
                    :table_data_3="table_data_3"
                    :table_data_4="table_data_4"
                    :table_data_5="table_data_5"
                    :all_settings="chart.chart_settings"
                    :chart_uuid="chart_uuid"
                    :request_params="request_params"
                    :grid-stack="gridStack"
                    :ext-filters="extFilters"
                    :can-edit="canEdit"
                    @active-change="saveOrDelChart"
                    @show-src-record="showSrcRecord"
                ></bi-pivot-table>
            </div>

        </div>
    </div>
</template>

<script>
    import {ChartFunctions} from './ChartFunctions';
    import {SpecialFuncs} from '../../../../../classes/SpecialFuncs';

    import {eventBus} from '../../../../../app';

    import BiPivotTable from './BiPivotTable';
    import ChartDimensionsButton from './ChartDimensionsButton.vue';
    import ChartExportButton from "./ChartExportButton";
    import BiChart from "./BiChart";
    import BiChartSettings from "./BiChartSettings";
    import SlotPopup from "../../../../CustomPopup/SlotPopup";

    export default {
        name: "BiChartElement",
        components: {
            SlotPopup,
            BiChartSettings,
            BiChart,
            ChartExportButton,
            BiPivotTable,
            ChartDimensionsButton,
        },
        mixins: [
        ],
        data: function () {
            return {
                chart_uuid: uuidv4(),

                chart_data:  null,
                table_data:  null,
                table_data_2:  null,
                table_data_3:  null,
                table_data_4:  null,
                table_data_5:  null,

                activeTab: 'settings',
                showLoadingWhenChange: [],
                shouldNotRecreate: ['dimensions','pivot.data_widths','pivot.vert_widths'],//optimization of settings which are not change dataset
                initially_loaded: false,
                wait_for_redraw: false,
                draw_highchart: true,
                data_receiving_error: false,
            }
        },
        props: {
            chart: Object,
            tableMeta: Object,
            request_params: Object,
            canEdit: Boolean,
            isVisible: Boolean,
            export_prefix: String,
            gridstackCache: Object,
            gridStack: Object,
            extFilters: Array,
            biSettings: Object,
            triggerBiSaving: Number,
        },
        computed: {
            yColNotNumber() {
                let number_arr = ['Integer', 'Decimal', 'Currency', 'Percentage', 'Auto Number'];
                let y_axis_col = _.find(this.tableMeta._fields, {field: this.chart.chart_settings.bi_chart.y_axis.field});
                return y_axis_col && $.inArray(y_axis_col.f_type, number_arr) === -1;
            },
            //required fields
            requiredChart() {
                return this.chart.chart_settings.elem_type !== 'bi_chart' ||
                    (this.chart.chart_settings.bi_chart.x_axis.field && this.chart.chart_settings.bi_chart.y_axis.field);
            },
            requiredTable() {
                return this.chart.chart_settings.elem_type !== 'pivot_table' || this.chart.chart_settings.pivot_table.about.field;
            },
        },
        watch: {
            isVisible: {
                handler(val) {
                    if (val) {
                        //Initial loading (or gridStack recreated)
                        if (!this.initially_loaded) {
                            if (this.gridstackCache[this.chart.id]) {
                                this.dataSet(this.gridstackCache[this.chart.id]);//Optimization for recreating GridStack component (active 0.1s while recreating)
                            } else {
                                this.saveOrDelChart('__update_cache');
                            }
                        }
                        //Redraw if GridView changed but BI was invisible
                        else if (this.wait_for_redraw) {
                            this.redrawComponent();
                        }
                        this.initially_loaded = true;
                        this.wait_for_redraw = false;
                    }
                },
                immediate: true,
            },
        },
        methods: {
            canChartEdit() {
                return this.$root.addonCanPermisEdit(this.tableMeta, this.chart, '_chart_rights');
            },
            saveOrDelChart(paramName, should_del) {
                if (paramName === 'y_axis.col') {
                    this.chart.chart_settings.bi_chart.y_axis.calc_val = (this.yColNotNumber ? 0 : 1);
                }
                if (paramName === 'dimensions') {
                    if (this.chart.chart_settings.elem_type === 'bi_chart' && this.activeTab === 'table') {
                        this.changeChart('settings');
                    }
                    if (this.chart.chart_settings.elem_type === 'pivot_table' && this.activeTab === 'chart') {
                        this.changeChart('settings');
                    }
                }

                if (!this.canEdit && paramName !== '__update_cache') {
                    return;
                }

                //filter tags
                this.chart.chart_settings.text_data.content = SpecialFuncs.strip_tags(this.chart.chart_settings.text_data.content, '<span><p><ul><ol><li><b><i><s><sub><sup><u><span><h1><h2><h3><h4><h5><h6><em><strong><br>');

                let title = '';
                switch (this.chElType()) {
                    case 'chart': title = this.chart.chart_settings.bi_chart.labels.general; break;
                    case 'table': title = this.chart.chart_settings.pivot_table.labels.general; break;
                }

                this.chart.name = this.chart.chart_settings.name || '';
                this.chart.chart_settings.wait_for_update = false;

                let ch_data_range = String(this.chart.chart_settings.data_range);
                if (ch_data_range === null || ch_data_range === 'null') {
                    ch_data_range = String(this.biSettings.chart_data_range);
                }
                let input_data = {
                    id: this.chart.id,
                    table_chart_tab_id: this.biSettings.id,
                    table_id: this.tableMeta.id,
                    row_idx: this.chart.row_idx,
                    col_idx: this.chart.col_idx,
                    name: this.chart.name,
                    title: title,
                    all_settings: this.chart.chart_settings,
                    request_params: SpecialFuncs.dataRangeRequestParams(ch_data_range, this.tableMeta.id, this.request_params),
                    changed_param: paramName || '__update_cache',
                };
                if (should_del) {
                    input_data.should_del = 1;
                }

                if ($.inArray(paramName, this.showLoadingWhenChange) > -1) {
                    this.$root.sm_msg_type = 1;
                }

                if ($.inArray(paramName, this.shouldNotRecreate) === -1) {
                    this.chart_data = null;
                    this.table_data = null;
                }

                if (this.triggerBiSaving) {
                    this.$emit('set-chart-in-saving-process', 1);
                }

                axios.post('/ajax/table/chart', input_data).then(({ data }) => {
                    if ($.inArray(paramName, this.shouldNotRecreate) > -1) {
                        return;
                    }
                    this.data_receiving_error = data && data.data_receiving_error;

                    if (data && data.error) {
                        if (data.code === 2 && this.chart.chart_settings.bi_chart.y_axis.calc_val) {
                            this.chart.chart_settings.bi_chart.y_axis.calc_val = 0;
                        }
                        Swal('Info', data.error);
                    }
                    if (data && data.id) {
                        this.dataSet(data);
                    }

                    //FOR PERMISSIONS SYNCHRONIZATION
                    if (this.tableMeta && this.tableMeta._is_owner && this.tableMeta._bi_charts) {
                        let c_id = data && data.id ? data.id : this.chart.chart_settings.id;
                        let ch_idx = _.findIndex(this.tableMeta._bi_charts, {id: Number(c_id)});
                        if (input_data.should_del && ch_idx > -1) {
                            this.tableMeta._bi_charts.splice(ch_idx, 1);
                        }
                        else if (data && data.chart_obj) {
                            data.chart_obj.chart_settings = _.merge(ChartFunctions.emptySettings(), data.chart_obj.chart_settings);
                            if (ch_idx > -1) {
                                this.$root.assignObject(data.chart_obj, this.tableMeta._bi_charts[ch_idx]);
                            } else {
                                this.tableMeta._bi_charts.push(data.chart_obj);
                            }
                        }
                    }
                    //---

                    this.checkStringVals(this.chart.chart_settings.pivot_table.about, this.table_data);
                    this.checkStringVals(this.chart.chart_settings.pivot_table.about_2, this.table_data_2);
                    this.checkStringVals(this.chart.chart_settings.pivot_table.about_3, this.table_data_3);
                    this.checkStringVals(this.chart.chart_settings.pivot_table.about_4, this.table_data_4);
                    this.checkStringVals(this.chart.chart_settings.pivot_table.about_5, this.table_data_5);
                    this.initially_loaded = true;
                    this.wait_for_redraw = false;

                    if (this.triggerBiSaving) {
                        window.setTimeout(() => {
                            window.dom_as_png(this.$refs.chart_body, this.chart.id, {
                                noscale: true,
                                nooverlay: true
                            }, this.storeChartForReports);
                        }, 1000);
                    }
                }).catch(errors => {
                    Swal('Info', getErrors(errors));
                }).finally(() => {
                    this.$root.sm_msg_type = 0;
                });

                if (should_del) {
                    this.$emit('del-chart', this.chart);
                }
            },
            storeChartForReports(elem, name, dataUrl) {
                if (this.triggerBiSaving) {
                    let data = new FormData();
                    data.append('table_id', this.tableMeta.id);
                    data.append('chart_id', this.chart.id);
                    data.append('row_id', this.triggerBiSaving);
                    data.append('chart_image', dataURLtoFile(dataUrl));
                    axios.post('/ajax/table/chart/store', data, {
                        headers: {
                            'Content-Type': 'multipart/form-data'
                        },
                    }).then(() => {
                        this.$emit('set-chart-in-saving-process', -1);
                    });
                }
            },
            copySetts() {
                return _.cloneDeep(this.chart.chart_settings);
            },
            chartTooMuchGroups() {
                /*this.chart.chart_settings.bi_chart.x_axis.field = null;
                this.chart.chart_settings.bi_chart.x_axis.l1_group_fld = null;
                this.chart.chart_settings.bi_chart.x_axis.l2_group_fld = null;
                this.saveOrDelChart('x_axis.l1_group_fld');*/
                Swal('Info','The total number of data groups (*subgroups) exceeds the limit (> 100). Please optimize your data and/or BI settings.');
            },

            changedType() {
                if (this.chart.chart_settings.bi_chart.chart_sub_type === 'pie') {
                    this.chart.chart_settings.bi_chart.x_axis.l2_group_fld = null;
                    this.chart.chart_settings.bi_chart.x_axis.group_field = null;
                }
            },
            changeChart(status) {
                this.activeTab = status;
                this.chart.chart_settings.active_type = status;
            },
            checkStringVals(about, data) {
                if (about.calc_val > 0 && data && data.length) {
                    let is_string = _.find(data, (row) => {
                        return isNaN(String(row.y || 0));
                    });
                    if (is_string) {
                        about.calc_val = 0;
                        Swal('Info','Present text values in "About". Only "Count" function is available!');
                        this.saveOrDelChart('calc_val');
                    }
                }
            },

            //show link
            showSrcRecord(lnk, header, tableRow) {
                this.$emit('show-src-record', lnk, header, tableRow, 'bi_module');
            },
            dimChange(reload) {
                if (reload) {
                    this.predictType();
                }
                this.saveOrDelChart('dimensions');
            },
            
            //Settings
            chElType() {
                return ChartFunctions.chElType(this.chart.chart_settings);
            },
            ucElType() {
                return ChartFunctions.chElType(this.chart.chart_settings, true);
            },
            predictType() {
                this.changeChart(this.chElType());
            },
            opnSettings() {
                this.$emit('settings-showed', true, {
                    title: this.ucElType(),
                    all: this.copySetts(),
                    canedit: this.canEdit,
                    uuid: this.chart_uuid,
                });
            },
            svSettings(uuid, all_settings, param_name) {
                if (this.chart_uuid === uuid) {
                    this.chart.chart_settings = {
                        ...this.chart.chart_settings,
                        ...all_settings
                    };
                    if (param_name) {
                        this.predictType();
                        this.saveOrDelChart(param_name);
                    }
                }
            },

            //cloneVertical
            cloneVertical() {
                this.$emit('clone-vertical', this.chart);
            },
            
            //cache sys
            dataSet(data) {
                let is_new = !this.chart.id;

                this.chart_data = data.chart_data;
                this.table_data = this.prepareTbData(data.table_data);
                this.table_data_2 = this.prepareTbData(data.table_data_2);
                this.table_data_3 = this.prepareTbData(data.table_data_3);
                this.table_data_4 = this.prepareTbData(data.table_data_4);
                this.table_data_5 = this.prepareTbData(data.table_data_5);

                this.chart.id = data.id;
                this.chart.chart_settings.id = data.id;
                this.chart.title = data.chart_obj.title;
                this.chart.cached_data = {
                    chart_data: this.chart_data,
                    table_data: this.table_data,
                    table_data_2: this.table_data_2,
                    table_data_3: this.table_data_3,
                    table_data_4: this.table_data_4,
                    table_data_5: this.table_data_5,
                };

                this.gridstackCache[data.id] = data;
                if (is_new) {
                    this.$emit('added-chart', this.chart);
                }
            },
            prepareTbData(input) {
                for (let i = 1; i <= ChartFunctions.maxLVL(); i++) {
                    let horfirst = [];
                    let vertfirst = [];
                    _.each(input, (el) => { //All data should be 'string' for correct working of grouping
                        if (el['hor_l'+i] !== undefined) {
                            let hi = _.findIndex(horfirst, (h) => {//Ignore letter case -> make all elements similar to first
                                return String(h).toLowerCase() === String(el['hor_l'+i]).toLowerCase();
                            });
                            if (hi > -1) {
                                el['hor_l'+i] = horfirst[hi];
                            } else {
                                el['hor_l'+i] = String(el['hor_l'+i]);
                                horfirst.push(el['hor_l'+i]);
                            }
                        }
                        if (el['vert_l'+i] !== undefined) {
                            let hi = _.findIndex(vertfirst, (h) => {//Ignore letter case -> make all elements similar to first
                                return String(h).toLowerCase() === String(el['vert_l'+i]).toLowerCase();
                            });
                            if (hi > -1) {
                                el['vert_l'+i] = vertfirst[hi];
                            } else {
                                el['vert_l'+i] = String(el['vert_l'+i]);
                                vertfirst.push(el['vert_l'+i]);
                            }
                        }
                    });
                }
                return input;
            },
            //text element parser
            parseTextContent() {
                let content = this.chart.chart_settings.text_data.content;
                let variables = content.match(newRegexp('\\$[\\p{L}\\d]+'));
                _.each(variables, (vr,i) => {
                    let is_chart = false, datavar = this.findDatavar('vars_table', vr);
                    if (!datavar) {
                        is_chart = true;
                        datavar = this.findDatavar('vars_chart', vr);
                    }
                    let chart = _.find(this.tableMeta._bi_charts, {id: Number(datavar ? datavar.chart_id : 0)});
                    let replacer = this.calcReplacer(chart, datavar, is_chart);
                    content = content.replace(new RegExp(vr.replace('\$','\\\$'), 'gi'), replacer || vr);
                });
                return content;
            },
            calcReplacer(chart, datavar, is_chart) {
                if (!datavar || !chart || !chart.cached_data) {
                    return '';
                }
                let cachedata = this.cachedataget(chart, datavar, is_chart);
                let res = 0;
                _.each(cachedata, (el) => {
                    if (
                        (!datavar.hor_1 || el.hor_l1 == datavar.hor_1)
                        && (!datavar.hor_2 || el.hor_l2 == datavar.hor_2)
                        && (!datavar.hor_3 || el.hor_l3 == datavar.hor_3)
                        && (!datavar.vert_1 || el.vert_l1 == datavar.vert_1)
                        && (!datavar.vert_2 || el.vert_l2 == datavar.vert_2)
                        && (!datavar.vert_3 || el.vert_l3 == datavar.vert_3)
                        && (!datavar.group_1 || el.x == datavar.group_1)
                        && (!datavar.group_2 || el.l1 == datavar.group_2)
                    ) {
                        res += to_float(el.y);
                    }
                });
                return String(res);
            },
            cachedataget(chart, datavar, is_chart) {
                if (is_chart) {
                    return chart.cached_data.chart_data;
                } else {
                    switch (datavar.about || 1) {
                        case 5: return chart.cached_data.table_data_5;
                        case 4: return chart.cached_data.table_data_4;
                        case 3: return chart.cached_data.table_data_3;
                        case 2: return chart.cached_data.table_data_2;
                        default: return chart.cached_data.table_data;
                    }
                }
            },
            findDatavar(prop, vriable) {
                return _.find(this.chart.chart_settings.text_data[prop], (el) => {
                    return String(el.name).toLowerCase() === String(vriable).toLowerCase()
                        || String('$'+el.name).toLowerCase() === String(vriable).toLowerCase();
                });
            },
            biViewUpdateAll(tab_id, force) {
                if (tab_id != this.chart.table_chart_tab_id) {
                    return;
                }
                if (this.chart.chart_settings.wait_for_update || force) {
                    this.saveOrDelChart('__update_cache');
                }
            },
            recalcBiHe(tab_id, mult, cell_hgt) {
                if (tab_id != this.chart.table_chart_tab_id) {
                    return;
                }
                let newhe = Math.round(this.chart.chart_settings.dimensions.gs_he*mult);
                if (newhe*cell_hgt < window.innerHeight) {
                    this.chart.chart_settings.dimensions.gs_he = newhe;
                    this.chart.col_idx = Math.round(this.chart.col_idx * mult);
                    this.saveOrDelChart('dimensions');
                }
            },
            redrawComponent() {
                if (!this.isVisible) {
                    this.wait_for_redraw = true;
                    return;
                }

                if (!this.chart.chart_settings.no_auto_update) {
                    this.saveOrDelChart('__update_cache');
                } else {
                    this.chart.chart_settings.wait_for_update = true;
                }
            },
            redrawHighchart(hash) {
                if (this.chart.__gs_hash === hash) {
                    this.draw_highchart = false;
                    this.$nextTick(() => {
                        this.draw_highchart = true;
                    });
                }
            },
        },
        created() {
        },
        mounted() {
            this.$nextTick(() => {
                //set correct tab after gridstack recreate
                this.activeTab = this.chart.chart_settings.active_type;
            });
            eventBus.$on('bi-chart-update-all-clicked', this.biViewUpdateAll);
            eventBus.$on('bi-chart-recalc-height', this.recalcBiHe);
            eventBus.$on('bi-chart-save-settings', this.svSettings);
            eventBus.$on('bi-chart-redraw-highcharts', this.redrawHighchart);
            eventBus.$on('new-request-params', this.redrawComponent);
        },
        beforeDestroy() {
            eventBus.$off('bi-chart-update-all-clicked', this.biViewUpdateAll);
            eventBus.$off('bi-chart-recalc-height', this.recalcBiHe);
            eventBus.$off('bi-chart-save-settings', this.svSettings);
            eventBus.$off('bi-chart-redraw-highcharts', this.redrawHighchart);
            eventBus.$off('new-request-params', this.redrawComponent);
        }
    }
</script>

<style lang="scss" scoped>
    @import "BiModule";

    .left-elements {
        position: absolute;
        left: 0;
        top: 0;
        z-index: 100;
        cursor: pointer;
    }
</style>