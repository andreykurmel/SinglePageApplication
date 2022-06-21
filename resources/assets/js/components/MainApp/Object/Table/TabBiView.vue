<template>
    <div v-if="tableMeta && settingsMeta"
         class="tab-settings"
         :class="{'full-height': !rows_count}"
    >
        <div v-if="!$root.AddonAvailableToUser(tableMeta, 'bi')" class="row full-frame flex flex--center">
            <label>Addon is unavailable!</label>
        </div>
        <template v-else="">

            <div v-if="!rows_count" class="full-frame flex flex--center bold" :style="{backgroundColor: '#FFF'}">
                No Records!
            </div>

            <div class="grid-wrap"
                 v-show="rows_count"
                 v-if="candraw"
                 :style="{ margin: (5 - bi_cell_space)+'px' }"
            >
                <div class="grid-stack">
                    <template v-for="ch in tableMeta._charts_saved">
                        <bi-grid-wrap :gs_hash="ch.__gs_hash"
                                      :gs_wi="ch.chart_settings.dimensions.gs_wi"
                                      :gs_he="ch.chart_settings.dimensions.gs_he"
                                      :gs_x="ch.row_idx"
                                      :gs_y="ch.col_idx"
                        >
                            <bi-chart-element
                                    :table-meta="tableMeta"
                                    :request_params="request_params"
                                    :row_state_hash="row_state_hash"
                                    :export_name="export_name"
                                    :chart="ch"
                                    :can-edit="ch._can_edit"
                                    :is-visible="isVisible"
                                    :is-transparent="isTransparent"
                                    :precached-datas="precachedDatas"
                                    :grid-stack="grid"
                                    @settings-showed="canChange"
                                    @added-chart="makeWidget"
                                    @del-chart="delWidget"
                                    @clone-vertical="cloneWidget"
                                    @show-src-record="showSrcRecord"
                            ></bi-chart-element>
                        </bi-grid-wrap>
                    </template>
                </div>
            </div>

            <slot-popup v-if="settings_obj" :popup_width="850" @popup-close="settings_obj = null">
                <template v-slot:title>
                    <span>Setup - {{ settings_obj.title }}</span>
                </template>
                <template v-slot:body>
                    <bi-chart-settings
                            :table-meta="tableMeta"
                            :can-edit="settings_obj.canedit"
                            :all_settings="settings_obj.all"
                            :request_params="request_params"
                            @settings-changed="emitSettings"
                    ></bi-chart-settings>
                </template>
            </slot-popup>

        </template>

    </div>
</template>

<script>
    import {SpecialFuncs} from '../../../../classes/SpecialFuncs';
    import {ChartFunctions} from './ChartAddon/ChartFunctions';

    import BiChartElement from './ChartAddon/BiChartElement.vue';
    import BiGridWrap from "./ChartAddon/BiGridWrap";
    import SlotPopup from "../../../CustomPopup/SlotPopup";
    import BiChartSettings from "./ChartAddon/BiChartSettings";

    import {eventBus} from '../../../../app';

    export default {
        name: "TabBiView",
        components: {
            BiChartSettings,
            SlotPopup,
            BiGridWrap,
            BiChartElement,
        },
        data: function () {
            return {
                settings_obj: null,
                candraw: false,
                showedCharts: [],
                precachedDatas: {},
                grid: null,
                change_handler: true,
                bi_settings: {},
            }
        },
        props:{
            table_id: Number,
            rows_count: Number,
            tableMeta: Object,
            settingsMeta: Object,
            request_params: Object,
            row_state_hash: String,
            export_name: String,
            user:  Object,
            canCreate: Boolean,
            isVisible: Boolean,
            isTransparent: Boolean,
        },
        computed: {
            bi_cell_space() {
                let space = Number(this.bi_settings.cell_spacing) / 2;
                return !isNaN(space) ? space : 25/2;
            },
        },
        watch: {
            table_id: function(val) {
                this.loadSavedCharts();
            }
        },
        methods: {
            canChange(val, settings_obj) {
                this.change_handler = !val;
                this.settings_obj = settings_obj;
            },
            emitSettings(all_settings, param_name) {
                eventBus.$emit('save-settings-chart', this.settings_obj.uuid, all_settings, param_name);
            },
            makeWidget(newChart) {
                let iidd = newChart.chart_settings.dimensions.gs_hash;
                this.$nextTick(() => {
                    this.grid.makeWidget();
                });
            },
            addWidget(newChart) {
                let widget = newChart || ChartFunctions.newChart(this.tableMeta.id, this.$root.user.id, 0, 0);
                this.tableMeta._charts_saved.push(widget);
                this.loadSavedCharts();
            },
            cloneWidget(chart) {
                let newChart = _.cloneDeep(chart);
                newChart.id = null;
                newChart.chart_settings.id = null;
                newChart.col_idx = 0;
                newChart.row_idx = 0;
                newChart.is_cloned = true;
                newChart.__gs_hash = uuidv4();
                this.addWidget(newChart);
            },
            delWidget(chart) {
                let ii = _.findIndex(this.tableMeta._charts_saved, {id: Number(chart.id)});
                if (ii > -1) {
                    this.tableMeta._charts_saved.splice(ii, 1);
                }
                this.loadSavedCharts();
            },
            //change functions
            checkSaved() {
                if (!this.tableMeta._charts_saved) {
                    this.$set(this.tableMeta, '_charts_saved', []);
                }
                this.bi_settings = ChartFunctions.settsFromMeta(this.tableMeta, this);
            },
            loadSavedCharts() {
                this.grid ? this.grid.destroy() : null;
                this.candraw = false;
                this.checkSaved();
                _.each(this.tableMeta._charts_saved, (ch) => {
                    ch.chart_settings = _.merge(ChartFunctions.emptySettings(), ch.chart_settings);
                });
                this.$nextTick(() => {
                    this.candraw = true;
                    this.$nextTick(() => {
                        this.grid = window.GridWrap.GridStack.init({
                            disableDrag: !!this.bi_settings.fix_layout,
                            disableResize: !!this.bi_settings.fix_layout,
                            cellHeight: this.bi_settings.cell_height || 50,
                            margin: this.bi_cell_space,
                            handleClass: 'chart-body',
                        });
                        this.grid.on('change', this.changedPosition);
                    });
                });
            },
            applyGridstackSettings() {
                this.checkSaved();
                if (this.bi_settings.fix_layout) {
                    this.grid.disable();
                } else {
                    this.grid.enable();
                }
                this.grid.cellHeight(this.bi_settings.cell_height || 50, true);
            },

            //drag
            changedPosition() {
                if (this.change_handler) {
                    let items = $('.grid-stack > div');
                    _.each(items, (it) => {
                        let hsh = $(it).attr('hash');
                        _.each(this.tableMeta._charts_saved, (cha) => {
                            if (cha.__gs_hash === hsh) {
                                cha.row_idx = $(it).attr('gs-x');
                                cha.col_idx = $(it).attr('gs-y');
                                cha.chart_settings.dimensions.gs_wi = $(it).attr('gs-w');
                                cha.chart_settings.dimensions.gs_he = $(it).attr('gs-h');
                            }
                        });
                    });

                    if (this.tableMeta._is_owner) {
                        axios.post('/ajax/table/realign-charts', {
                            table_id: this.tableMeta.id,
                            charts: this.tableMeta._charts_saved,
                        });
                    }
                    eventBus.$emit('redraw-gannt');
                }
            },

            //show link
            showSrcRecord(lnk, header, tableRow) {
                this.$emit('show-src-record', lnk, header, tableRow, 'bi_module');
            },
        },
        mounted() {
            this.loadSavedCharts();
            eventBus.$on('bi-view-changed-settings', this.applyGridstackSettings);
            eventBus.$on('bi-view-recreate', this.loadSavedCharts);
            eventBus.$on('bi-add-clicked', this.addWidget);
        },
        beforeDestroy() {
            eventBus.$off('bi-view-changed-settings', this.applyGridstackSettings);
            eventBus.$off('bi-view-recreate', this.loadSavedCharts);
            eventBus.$off('bi-add-clicked', this.addWidget);
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