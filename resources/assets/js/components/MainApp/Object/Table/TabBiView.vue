<template>
    <div v-if="tableMeta && $root.settingsMeta" class="tab-settings full-height">
        <div v-if="!$root.AddonAvailableToUser(tableMeta, 'bi')" class="row full-frame flex flex--center">
            <label>Addon is unavailable!</label>
        </div>
        <div v-else class="full-height">
            <div class="menu-header">
                <button v-if="tableMeta._is_owner"
                        class="btn btn-default btn-sm left-btn"
                        :class="{active : acttab === 'settings'}"
                        :style="textSysStyle"
                        @click="changeActab('settings')"
                >List</button>
                <template v-for="tab in tableMeta._chart_tabs" v-if="tab.chart_active">
                    <button class="btn btn-default btn-sm left-btn"
                            :class="{active : acttab === tab.id}"
                            :style="textSysStyle"
                            style="margin-right: 3px;"
                            @click="changeActab(tab.id)"
                    ><span class="glyphicon glyphicon-equalizer"></span>&nbsp;{{ tab.name }}</button>
                </template>

                <div class="flex flex--center-v flex--end" style="position: absolute; top: 0; right: 5px; height: 32px;">
                    <label v-if="acttab === 'settings' && selectedTab" class="flex flex--center ml15 mr0" style="margin: 0 0 0 5px;white-space: nowrap;" :style="textSysStyleSmart">
                        Loaded Chart Tab:&nbsp;
                        <select-block
                            :options="lochaOpts()"
                            :sel_value="selectedTab.id"
                            :style="{ width:'150px', height:'32px', }"
                            @option-select="rowIndexClicked"
                        ></select-block>
                    </label>
                    <button
                        v-show="acttab !== 'settings' && foundBiToUpdate"
                        class="btn btn-primary btn-sm blue-gradient mini-add"
                        @click="biUpdateAll()"
                        style="margin-right: 3px;"
                        :style="$root.themeButtonStyle"
                    >Update All</button>
                    <button v-if="visBtn && biCanAdd()"
                            v-show="acttab !== 'settings'"
                            class="btn btn-primary btn-sm blue-gradient"
                            :style="$root.themeButtonStyle"
                            @click="addClick"
                    >Add</button>
                    <bi-settings-button
                        v-if="visBtn && selectedTab"
                        :table-meta="tableMeta"
                        :bi-settings="selectedTab"
                        @updated-row="updateChartTab"
                        style="height: 36px;"
                    ></bi-settings-button>
                </div>
            </div>
            <div class="menu-body" :style="$root.themeMainBgStyle" :class="[triggerBiSaving ? 'for-saving-bottom' : '']">

                <!--SETTINGS TAB-->

                <div class="full-frame" v-show="acttab === 'settings'">
                    <custom-table
                        :cell_component_name="'custom-cell-addon'"
                        :global-meta="tableMeta"
                        :table-meta="$root.settingsMeta['table_chart_tabs']"
                        :settings-meta="$root.settingsMeta"
                        :all-rows="tableMeta._chart_tabs"
                        :adding-row="{ active: true, position: 'bottom' }"
                        :rows-count="tableMeta._chart_tabs.length"
                        :cell-height="1"
                        :max-cell-rows="0"
                        :is-full-width="true"
                        :with_edit="canAddonEdit()"
                        :selected-row="selIdx"
                        :user="$root.user"
                        :behavior="'settings_bi_add'"
                        :available-columns="['name','description','chart_data_range','chart_active']"
                        :use_theme="true"
                        @added-row="addChartTab"
                        @delete-row="deleteChartTab"
                        @updated-row="updateChartTab"
                        @row-index-clicked="rowIndexClicked"
                    ></custom-table>
                </div>

                <!--BASICS TAB-->

                <div v-for="biSett in tableMeta._chart_tabs"
                     v-show="acttab == biSett.id || triggerBiSaving"
                     class="full-frame flex flex--col"
                     style="overflow-x: hidden;"
                >
                    <tab-chart
                        v-if="tableMeta && biSett && biSett.chart_active"
                        :rows_count="rows_count"
                        :table-meta="tableMeta"
                        :request_params="request_params"
                        :bi-settings="biSett"
                        :export_prefix="export_prefix"
                        :ext-filters="extFilters"
                        :is-visible="isVisible"
                        :is-transparent="isTransparent"
                        :trigger-bi-saving="triggerBiSaving"
                        @show-src-record="showSrcRecord"
                        @set-chart-in-saving-process="(val) => { $emit('set-chart-in-saving-process', val) }"
                    ></tab-chart>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import {eventBus} from "../../../../app";

import {SpecialFuncs} from "../../../../classes/SpecialFuncs";
import {ChartFunctions} from "./ChartAddon/ChartFunctions";

import CanEditMixin from "../../../_Mixins/CanViewEditMixin";
import CellStyleMixin from "../../../_Mixins/CellStyleMixin";

import TabChart from "./ChartAddon/TabChart";
import BiSettingsButton from "../../../Buttons/BiSettingsButton";
import CustomTable from "../../../CustomTable/CustomTable";
import SelectBlock from "../../../CommonBlocks/SelectBlock";
import BiChartElement from "./ChartAddon/BiChartElement.vue";

export default {
    name: "TabBiView",
    mixins: [
        CanEditMixin,
        CellStyleMixin,
    ],
    components: {
        BiChartElement,
        SelectBlock,
        CustomTable,
        BiSettingsButton,
        TabChart,
    },
    data: function () {
        return {
            canDraw: false,
            selIdx: -1,
            acttab: '',
        }
    },
    props:{
        table_id: Number,
        tableMeta: Object,
        rows_count: Number,
        request_params: Object,
        export_prefix: String,
        isVisible: Boolean,
        isTransparent: Boolean,
        triggerBiSaving: Number,
        extFilters: Array,
    },
    computed: {
        selectedTab() {
            return this.tableMeta._chart_tabs[this.selIdx];
        },
        biCharts() {
            return this.tableMeta._bi_charts || [];
        },
        foundBiToUpdate() {
            return _.find(this.biCharts, (ch) => {
                return !!ch.chart_settings
                    && !!ch.chart_settings.wait_for_update
                    && (
                        this.selectedTab
                        ? ch.table_chart_tab_id == this.selectedTab.id
                        : ch.table_chart_tab_id == null
                    );
            });
        },
        visBtn() {
            return this.acttab !== 'settings' && this.$root.AddonAvailableToUser(this.tableMeta, 'bi', 'edit');
        },
    },
    watch: {
        table_id(val) {
            this.prepareCharts();
            this.changeActab('settings');
        },
    },
    methods: {
        canAddonEdit() {
            return this.$root.addonCanEditGeneral(this.tableMeta, 'bi');
        },
        addClick() {
            eventBus.$emit('bi-tab-add-clicked', this.selectedTab.id);
        },
        biCanAdd() {
            return this.selectedTab && this.selectedTab.bi_can_add;
        },
        changeActab(val) {
            this.acttab = '';
            this.$nextTick(() => {
                this.selIdx = _.findIndex(this.tableMeta._chart_tabs, {id: Number(val)});
                this.acttab = val;
            });
        },
        showSrcRecord(lnk, header, tableRow) {
            this.$emit('show-src-record', lnk, header, tableRow);
        },
        biUpdateAll() {
            eventBus.$emit('bi-chart-update-all-clicked', this.selectedTab ? this.selectedTab.id : null);
        },
        //TABS
        lochaOpts() {
            return _.map(this.tableMeta._chart_tabs, (fld) => {
                return { val:fld.id, show:fld.name, };
            });
        },
        rowIndexClicked(optOrIdx) {
            if (typeof optOrIdx === 'object') {
                this.selIdx = _.findIndex(this.tableMeta._chart_tabs, {id: Number(optOrIdx.val)});
            } else {
                this.selIdx = optOrIdx;
            }
        },
        addChartTab(tableRow) {
            let fields = _.cloneDeep(tableRow);//copy object
            this.$root.deleteSystemFields(fields);

            $.LoadingOverlay('show');
            axios.post('/ajax/table/chart/tab', {
                table_id: this.tableMeta.id,
                fields: fields
            }).then(({ data }) => {
                this.tableMeta._chart_tabs = data;
            }).catch(errors => {
                Swal('Info', getErrors(errors));
            }).finally(() => {
                $.LoadingOverlay('hide');
            });
        },
        updateChartTab(tableRow) {
            if (!tableRow.id) {
                return;
            }
            let fields = _.cloneDeep(tableRow);//copy object
            this.$root.deleteSystemFields(fields);
            let reloadCh = tableRow._changed_field === 'chart_data_range';
            let rowId = tableRow.id;

            $.LoadingOverlay('show');
            axios.put('/ajax/table/chart/tab', {
                model_id: rowId,
                fields: fields
            }).then(({ data }) => {
                this.tableMeta._chart_tabs = data;
                if (reloadCh) {
                    eventBus.$emit('bi-chart-update-all-clicked', rowId, 'force');
                }
            }).catch(errors => {
                Swal('Info', getErrors(errors));
            }).finally(() => {
                $.LoadingOverlay('hide');
            });
        },
        deleteChartTab(tableRow) {
            $.LoadingOverlay('show');
            let tab_id = tableRow.id;
            axios.delete('/ajax/table/chart/tab', {
                params: {
                    model_id: tab_id
                }
            }).then(({ data }) => {
                this.tableMeta._chart_tabs = data;
                this.tableMeta._bi_charts = _.filter(this.tableMeta._bi_charts, (ch) => {
                    return ch.table_chart_tab_id != tab_id;
                });
            }).catch(errors => {
                Swal('Info', getErrors(errors));
            }).finally(() => {
                $.LoadingOverlay('hide');
            });
        },
        prepareCharts() {
            if (!this.tableMeta._bi_charts) {
                this.$set(this.tableMeta, '_bi_charts', []);
            }
            _.each(this.tableMeta._bi_charts, (ch) => {
                ch.chart_settings = _.merge(ChartFunctions.emptySettings(), ch.chart_settings);
            });

            this.canDraw = false;
            this.$nextTick(() => {
                this.canDraw = true;
            });
        },
    },
    mounted() {
        this.prepareCharts();
        this.changeActab(this.tableMeta._is_owner ? 'settings' : _.first(this.tableMeta._chart_tabs).id);
    },
    beforeDestroy() {
    }
}
</script>

<style lang="scss" scoped>
.tab-settings {
    position: relative;
    height: 100%;

    .blue-gradient {
        margin-right: 5px;
    }

    .menu-header {
        position: relative;
        margin-left: 5px;
        top: 3px;

        .left-btn {
            position: relative;
            top: 5px;
        }

        button {
            background-color: #CCC;
            outline: none;
            &.active {
                background-color: #FFF;
            }
            &:not(.active):hover {
                color: black;
            }
        }

        .right-elm {
            float: right;
            margin-left: 10px;
        }
    }

    .menu-body {
        position: absolute;
        top: 35px;
        right: 5px;
        left: 5px;
        bottom: 5px;
        border-radius: 5px;
        border: 1px solid #CCC;
    }

    .btn-default {
        height: 30px;
    }
    .for-saving-bottom {
        bottom: initial !important;
    }
}
</style>