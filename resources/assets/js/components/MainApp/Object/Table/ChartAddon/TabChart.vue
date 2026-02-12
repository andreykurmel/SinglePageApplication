<template>
    <div>
        <div v-if="!rows_count" :style="{backgroundColor: '#FFF'}" class="full-frame flex flex--center bold">
            No Records In Table!
        </div>

        <div v-if="candraw"
             v-show="rows_count"
             :style="{ margin: (5 - calc_cell_space)+'px' }"
             class="grid-wrap"
        >
            <div ref="stackwrap" class="grid-stack">
                <template v-for="ch in availCharts">
                    <bi-grid-wrap :gs_hash="ch.__gs_hash"
                                  :gs_he="ch.chart_settings.dimensions.gs_he"
                                  :gs_wi="ch.chart_settings.dimensions.gs_wi"
                                  :gs_x="ch.row_idx"
                                  :gs_y="ch.col_idx"
                    >
                        <bi-chart-element
                            :bi-settings="biSettings"
                            :can-edit="ch._can_edit"
                            :chart="ch"
                            :export_prefix="export_prefix"
                            :ext-filters="extFilters"
                            :grid-stack="grid"
                            :gridstack-cache="gridstackCache"
                            :is-transparent="isTransparent"
                            :is-visible="isVisible"
                            :request_params="request_params"
                            :table-meta="tableMeta"
                            :trigger-bi-saving="triggerBiSaving"
                            @settings-showed="canChange"
                            @added-chart="makeWidget"
                            @del-chart="delWidget"
                            @clone-vertical="cloneWidget"
                            @show-src-record="showSrcRecord"
                            @set-chart-in-saving-process="(val) => { $emit('set-chart-in-saving-process', val) }"
                        ></bi-chart-element>
                    </bi-grid-wrap>
                </template>
            </div>
        </div>

        <slot-popup v-if="settings_obj" :popup_width="950" @popup-close="settings_obj = null">
            <template v-slot:title>
                <span>Setup - {{ settings_obj.title }}</span>
                <div class="flex" style="width: 250px; position: absolute; right: 30px; top: 0px; z-index: 30;">
                    <select-block
                        :options="mapCharts(settings_obj.all)"
                        :sel_value="chart_to_copy"
                        style="padding: 0 3px !important; height: 24px;"
                        @option-select="(opt) => { chart_to_copy = opt.val }"
                    ></select-block>
                    <button :style="$root.themeButtonStyle"
                            class="btn btn-primary btn-sm hdr-style"
                            style="padding: 0 3px !important; height: 24px;"
                            @click="copyChrt"
                    >Copy
                    </button>
                </div>
            </template>
            <template v-slot:body>
                <bi-chart-settings
                    :all_settings="settings_obj.all"
                    :can-edit="settings_obj.canedit"
                    :request_params="request_params"
                    :table-meta="tableMeta"
                    @settings-changed="emitSettings"
                ></bi-chart-settings>
            </template>
        </slot-popup>

    </div>
</template>

<script>
import {eventBus} from '../../../../../app';

import {ChartFunctions} from './ChartFunctions';

import BiChartElement from './../ChartAddon/BiChartElement.vue';
import BiGridWrap from "./../ChartAddon/BiGridWrap";
import BiChartSettings from "./../ChartAddon/BiChartSettings";
import SelectBlock from "../../../../CommonBlocks/SelectBlock";

export default {
    name: "TabChart",
    components: {
        SelectBlock,
        BiChartSettings,
        BiGridWrap,
        BiChartElement,
    },
    data: function () {
        return {
            chart_to_copy: null,
            settings_obj: null,
            candraw: false,
            gridstackCache: {},
            grid: null,
            change_handler: true,
        }
    },
    props: {
        rows_count: Number,
        tableMeta: Object,
        request_params: Object,
        export_prefix: String,
        isVisible: Boolean,
        isTransparent: Boolean,
        extFilters: Array,
        biSettings: Object,
        triggerBiSaving: Number,
    },
    computed: {
        calc_cell_space() {
            let space = Number(this.biSettings.bi_cell_spacing) / 2;
            return !isNaN(space) ? space : 25 / 2;
        },
        availCharts() {
            return _.filter(this.tableMeta._bi_charts, (ch) => {
                return ch.table_chart_tab_id == this.biSettings.id;
            });
        },
    },
    watch: {},
    methods: {
        canChange(val, settings_obj) {
            this.change_handler = !val;
            this.settings_obj = settings_obj;
        },
        emitSettings(all_settings, param_name) {
            eventBus.$emit('bi-chart-save-settings', this.settings_obj.uuid, all_settings, param_name);
        },
        makeWidget(newChart) {
            let iidd = newChart.chart_settings.dimensions.gs_hash;
            this.$nextTick(() => {
                this.grid.makeWidget();
            });
        },
        maxColIdx() {//max vert post to add to the end
            let maxCol = 0;
            _.each(this.availCharts, (ch) => {
                let yPos = to_float(ch.col_idx) + to_float(ch.chart_settings.dimensions.gs_he);
                if (yPos > maxCol) {
                    maxCol = yPos;
                }
            });
            return maxCol;
        },
        addWidget(newChart) {
            let widget = newChart || ChartFunctions.newChart(this.biSettings.id, this.tableMeta.id, this.$root.user.id, 0, this.maxColIdx());
            this.tableMeta._bi_charts.push(widget);
            this.createGridStack();
        },
        cloneWidget(chart) {
            let newChart = _.cloneDeep(chart);
            newChart.id = null;
            newChart.chart_settings.id = null;
            newChart.table_chart_tab_id = this.biSettings.id;
            newChart.col_idx = this.maxColIdx();
            newChart.row_idx = 0;
            newChart.is_cloned = true;
            newChart.__gs_hash = uuidv4();
            this.addWidget(newChart);
        },
        delWidget(chart) {
            let ii = _.findIndex(this.tableMeta._bi_charts, {id: Number(chart.id)});
            if (ii > -1) {
                this.tableMeta._bi_charts.splice(ii, 1);
            }
            this.createGridStack();
        },
        //change functions
        createGridStack() {
            this.grid ? this.grid.destroy() : null;
            this.candraw = false;

            this.$nextTick(() => {
                this.candraw = true;

                this.$nextTick(() => {
                    this.grid = window.GridStack.init({
                        disableDrag: !!this.biSettings.bi_fix_layout,
                        disableResize: !!this.biSettings.bi_fix_layout,
                        cellHeight: this.biSettings.bi_cell_height || 50,
                        margin: this.calc_cell_space,
                        handleClass: 'chart-body',
                    }, this.$refs.stackwrap);
                    this.grid.on('change', this.changedPosition);
                });
            });
        },
        applyGridstackSettings(prop) {
            if (['bi_cell_height', 'bi_cell_spacing'].indexOf(prop) > -1) {
                this.createGridStack();
                return;
            }

            if (this.biSettings.bi_fix_layout) {
                this.grid.disable();
            } else {
                this.grid.enable();
            }
            this.grid.cellHeight(this.biSettings.bi_cell_height || 50, true);
        },

        //drag
        changedPosition(a, b, c) {
            if (this.change_handler) {
                let items = $('.grid-stack .grid-stack-item');
                _.each(items, (it) => {
                    let attributes = it.attributes || ($(it)[0] || []).attributes;
                    let hsh = attributes['hash'] ? attributes['hash'].value : 'tmp';
                    _.each(this.tableMeta._bi_charts, (cha) => {
                        if (cha.__gs_hash === hsh) {
                            let gsW = attributes['gs-w'] ? attributes['gs-w'].value : 0;
                            let gsH = attributes['gs-h'] ? attributes['gs-h'].value : 0;
                            let gsX = attributes['gs-x'] ? attributes['gs-x'].value : 0;
                            let gsY = attributes['gs-y'] ? attributes['gs-y'].value : 0;
                            if (
                                (to_float(cha.chart_settings.dimensions.gs_wi) != to_float(gsW))
                                || (to_float(cha.chart_settings.dimensions.gs_he) != to_float(gsH))
                            ) {
                                eventBus.$emit('bi-chart-redraw-highcharts', cha.__gs_hash);
                            }
                            cha.row_idx = gsX;
                            cha.col_idx = gsY;
                            cha.chart_settings.dimensions.gs_wi = gsW;
                            cha.chart_settings.dimensions.gs_he = gsH;
                        }
                    });
                });

                if (this.tableMeta._is_owner) {
                    axios.post('/ajax/table/realign-charts', {
                        table_id: this.tableMeta.id,
                        charts: this.tableMeta._bi_charts,
                    });
                }
            }
        },

        //show link
        showSrcRecord(lnk, header, tableRow) {
            this.$emit('show-src-record', lnk, header, tableRow, 'bi_module');
        },
        mapCharts(set_obj) {
            return _.filter(this.availCharts, (chrt) => {
                return chrt.chart_settings.elem_type == set_obj.elem_type
                    && chrt.chart_settings.id != set_obj.id;
            }).map((chrt) => {
                return {
                    val: chrt.chart_settings.id,
                    show: chrt.chart_settings.name || ChartFunctions.chElType(chrt.chart_settings),
                };
            });
        },
        copyChrt() {
            if (this.chart_to_copy && this.settings_obj) {
                let chrt = _.find(this.tableMeta._bi_charts, (ch) => {
                    return ch.id == this.chart_to_copy;
                });
                chrt = chrt ? chrt.chart_settings : null;
                if (chrt) {
                    this.settings_obj.all.bi_chart = _.cloneDeep(chrt.bi_chart);
                    this.settings_obj.all.pivot_table = _.cloneDeep(chrt.pivot_table);
                    this.settings_obj.all.text_data = _.cloneDeep(chrt.text_data);
                    this.emitSettings(this.settings_obj.all, '_copied_chart');
                }
            }
        },
        addClicked(tabId) {
            if (tabId == this.biSettings.id) {
                this.addWidget();
            }
        }
    },
    mounted() {
        this.createGridStack();
        eventBus.$on('bi-tab-changed-settings', this.applyGridstackSettings);
        eventBus.$on('bi-tab-add-clicked', this.addClicked);
    },
    beforeDestroy() {
        eventBus.$off('bi-tab-changed-settings', this.applyGridstackSettings);
        eventBus.$off('bi-tab-add-clicked', this.addClicked);
    }
}
</script>

<style lang="scss" scoped>
.tab-settings {
    padding: 0;

    .grid-wrap {
        //
    }
}
</style>