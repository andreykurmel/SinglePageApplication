<template>
    <div class="full-height chart-wrapper">
        <div class="left-elements no-gs-drag" v-if="chart.chart_settings.wait_for_update">
            <i class="fas fa-sync" aria-hidden="true" @click="saveOrDelChart('__update_cache')"></i>
        </div>

        <div class="right-elements no-gs-drag" v-if="!bi_setts.hide_settings">
            <chart-dimensions-button
                    :all_settings="chart.chart_settings"
                    :can_edit="canEdit"
                    @dimensions-changed="dimChange"
                    @clone-vertical="cloneVertical"
                    @delete-chart="saveOrDelChart('', true)"
                    @open-settings="opnSettings()"
                    @refresh-chart="saveOrDelChart('__update_cache')"
            >
                <template v-slot:dwn_button>
                    <chart-export-button :can_action="activeTab === 'table'" :chart_uuid="chart_uuid" :export_name="export_name"></chart-export-button>
                </template>
            </chart-dimensions-button>
        </div>

        <div class="chart-body" :style="{backgroundColor: (chart.chart_settings.dimensions.back_color ? chart.chart_settings.dimensions.back_color: '#FFF')}">

            <!--TEXT DATA TAB-->
            <div class="full-frame" v-if="activeTab === 'text' && visibleData">
                <div class="" v-html="parseTextContent()"></div>
            </div>

            <!--NO FIELDS-->
            <div v-else-if="!requiredChart" class="full-height flex flex--center bold">Required fields: (X), (Y)</div>
            <div v-else-if="!requiredTable" class="full-height flex flex--center bold">Required fields: (About)</div>

            <!--NO DATA-->
            <div v-else-if="!chart_data" class="full-height flex flex--center">
                <img height="75" src="/assets/img/Loading_icon.gif">
            </div>

            <!--CHART TAB-->
            <div class="full-frame" v-else-if="activeTab === 'chart' && visibleData">
                <bi-chart
                    v-if="chart_data"
                    :table-meta="tableMeta"
                    :chart_data="chart_data"
                    :all_settings="chart.chart_settings"
                    :export_name="export_name"
                    @too-much-groups="chartTooMuchGroups"
                ></bi-chart>
            </div>

            <!--PIVOT TABLE TAB-->
            <div class="full-frame" v-else-if="activeTab === 'table' && visibleData">
                <bi-pivot-table
                    v-if="table_data"
                    :table_meta="tableMeta"
                    :table_data="table_data"
                    :table_data_2="table_data_2"
                    :table_data_3="table_data_3"
                    :all_settings="chart.chart_settings"
                    :chart_uuid="chart_uuid"
                    :request_params="request_params"
                    :grid-stack="gridStack"
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

    import BiRequestMixin from "./BiRequestMixin.vue";

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
            BiRequestMixin,
        ],
        data: function () {
            return {
                bi_setts: ChartFunctions.settsFromMeta(this.tableMeta, this),
                chart_uuid: uuidv4(),

                chart_data:  null,
                table_data:  null,
                table_data_2:  null,
                table_data_3:  null,

                activeTab: 'settings',
                should_update_cache: true,
                showLoadingWhenChange: [],
                first_view: this.isVisible,
            }
        },
        props:{
            chart: Object,
            tableMeta: Object,
            request_params: Object,
            canEdit: Boolean,
            isVisible: Boolean,
            row_state_hash: String,
            export_name: String,
            precachedDatas: Object,
            gridStack: Object,
        },
        computed: {
            //chart should update cache
            visibleData() {
                return this.isVisible && !this.should_update_cache;
            },
            shouldUpdateCache() {
                return this.first_view && this.should_update_cache;
            },
            //Y axis col is not Number
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
            //chart should update cache
            shouldUpdateCache(val) {
                if (val) {
                    this.saveOrDelChart('__update_cache');
                }
            },
            row_state_hash(val) {
                if (!this.chart.chart_settings.no_auto_update) {
                    this.should_update_cache = true;
                } else {
                    this.chart.chart_settings.wait_for_update = true;
                }
            },
            isVisible(val) {
                if (val) {
                    this.first_view = true;
                }
            },
        },
        methods: {
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

                let input_data = {
                    id: this.chart.id,
                    table_id: this.tableMeta.id,
                    row_idx: this.chart.row_idx,
                    col_idx: this.chart.col_idx,
                    name: this.chart.name,
                    title: title,
                    all_settings: this.chart.chart_settings,
                    request_params: this.getRequestParams(this.chart.chart_settings, this.tableMeta, this.request_params),
                    changed_param: paramName || '__update_cache',
                };
                if (should_del) {
                    input_data.should_del = 1;
                }

                if ($.inArray(paramName, this.showLoadingWhenChange) > -1) {
                    this.$root.sm_msg_type = 1;
                }

                this.chart_data = null;
                this.table_data = null;
                axios.post('/ajax/table/chart', input_data).then(({ data }) => {
                    if (data && data.error) {
                        if (data.code === 2 && this.chart.chart_settings.bi_chart.y_axis.calc_val) {
                            this.chart.chart_settings.bi_chart.y_axis.calc_val = 0;
                        }
                        Swal('', data.error);
                    }
                    if (data && data.id) {
                        this.dataSet(data);
                    }

                    //FOR PERMISSIONS SYNCHRONIZATION
                    if (this.tableMeta && this.tableMeta._is_owner && this.tableMeta._charts) {
                        let c_id = data && data.id ? data.id : this.chart.chart_settings.id;
                        let ch_idx = _.findIndex(this.tableMeta._charts, {id: Number(c_id)});
                        if (input_data.should_del && ch_idx > -1) {
                            this.tableMeta._charts.splice(ch_idx, 1);
                        } else {
                            if (ch_idx > -1) {
                                Object.assign(this.tableMeta._charts[ch_idx], data.chart_obj);
                            } else {
                                this.tableMeta._charts.push(data.chart_obj);
                            }
                        }
                    }
                    //---

                    this.checkStringVals(this.chart.chart_settings.pivot_table.about, this.table_data);
                    this.checkStringVals(this.chart.chart_settings.pivot_table.about_2, this.table_data_2);
                    this.checkStringVals(this.chart.chart_settings.pivot_table.about_3, this.table_data_3);
                }).catch(errors => {
                    Swal('', getErrors(errors));
                }).finally(() => {
                    this.$root.sm_msg_type = 0;
                });

                if (should_del) {
                    this.$emit('del-chart', this.chart);
                }
            },
            copySetts() {
                return _.cloneDeep(this.chart.chart_settings);
            },
            chartTooMuchGroups() {
                this.chart.chart_settings.bi_chart.x_axis.l1_group_fld = null;
                this.chart.chart_settings.bi_chart.x_axis.l2_group_fld = null;
                this.saveOrDelChart('x_axis.l1_group_fld');
                Swal('The total number of data groups (*subgroups) exceeds the limit (> 100). Please optimize your data and/or BI settings.');
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
                        Swal('Present text values in "About". Only "Count" function is available!');
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
                let type = '';
                switch (this.chart.chart_settings.elem_type) {
                    case 'bi_chart': type = 'chart'; break;
                    case 'pivot_table': type = 'table'; break;
                    case 'text_data': type = 'text'; break;
                }
                return type;
            },
            ucElType() {
                let str = this.chElType();
                return str[0].toUpperCase() + str.slice(1);
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
            savCache(data) {
                this.precachedDatas[data.id] = data;
            },
            checkCache() {
                if (this.precachedDatas[this.chart.id]) {
                    this.dataSet(this.precachedDatas[this.chart.id]);
                } else {
                    this.saveOrDelChart('__update_cache');
                }
            },
            dataSet(data) {
                let is_new = !this.chart.id;

                this.chart.id = data.id;
                this.chart.chart_settings.id = data.id;
                this.chart.title = data.chart_obj.title;
                this.chart.cached_data = {
                    chart_data: data.chart_data,
                    table_data: data.table_data,
                    table_data_2: data.table_data_2,
                    table_data_3: data.table_data_3,
                };

                this.chart_data = data.chart_data;
                this.table_data = data.table_data;
                this.table_data_2 = data.table_data_2;
                this.table_data_3 = data.table_data_3;

                this.savCache(data);
                this.$nextTick(() => {
                    this.should_update_cache = false;
                });
                if (is_new) {
                    this.$emit('added-chart', this.chart);
                }
            },
            recalcBiHe(mult, cell_hgt) {
                let newhe = Math.round(this.chart.chart_settings.dimensions.gs_he*mult);
                if (newhe*cell_hgt < window.innerHeight) {
                    this.chart.chart_settings.dimensions.gs_he = newhe;
                    this.chart.col_idx = Math.round(this.chart.col_idx * mult);
                    this.saveOrDelChart('dimensions');
                }
            },
            //text element parser
            parseTextContent() {
                let content = this.chart.chart_settings.text_data.content;
                let variables = content.match(/\$[\w\d]+/gi);
                _.each(variables, (vr,i) => {
                    let is_chart = false, datavar = this.findDatavar('vars_table', vr);
                    if (!datavar) {
                        is_chart = true;
                        datavar = this.findDatavar('vars_chart', vr);
                    }
                    let chart = _.find(this.tableMeta._charts_saved, {id: Number(datavar ? datavar.chart_id : 0)});
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
            biViewUpdateAll() {
                if (this.chart.chart_settings.wait_for_update) {
                    this.saveOrDelChart('__update_cache');
                }
            },
        },
        created() {
        },
        mounted() {
            this.$nextTick(function () {
                this.activeTab = this.chart.chart_settings.active_type;
                if (this.shouldUpdateCache) {
                    this.checkCache();
                }
            });
            eventBus.$on('bi-view-update-all', this.biViewUpdateAll);
            eventBus.$on('recalc-bi-height', this.recalcBiHe);
            eventBus.$on('save-settings-chart', this.svSettings);
        },
        beforeDestroy() {
            eventBus.$off('bi-view-update-all', this.biViewUpdateAll);
            eventBus.$off('recalc-bi-height', this.recalcBiHe);
            eventBus.$off('save-settings-chart', this.svSettings);
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