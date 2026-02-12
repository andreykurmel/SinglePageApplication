<template>
    <div class="full-height permissions-tab p5" :style="$root.themeMainBgStyle">

        <div v-if="!$root.AddonAvailableToUser(tableMeta, 'report')" class="permissions-panel full-height flex flex--center" :style="$root.themeMainBgStyle">
            <label>Addon is unavailable!</label>
        </div>
        <div v-else class="permissions-panel full-height" :style="$root.themeMainBgStyle">
            <div class="permissions-menu-header">
                <button class="btn btn-default btn-sm" :style="textSysStyle" :class="{active : activeTab === 'list'}" @click="activeTab = 'list'">
                    Models
                </button>
                <button class="btn btn-default btn-sm" :style="textSysStyle" :class="{active : activeTab === 'details'}" @click="activeTab = 'details'">
                    Settings
                </button>
                <button class="btn btn-default btn-sm" :style="textSysStyle" :class="{active : activeTab === 'templates'}" @click="activeTab = 'templates'">
                    Templates
                </button>

                <div class="flex flex--center-v flex--end" style="position: absolute; top: 0; right: 0; height: 32px; width: 50%;">
                    <label v-if="activeTab !== 'templates'"
                           class="flex flex--center ml15 mr0"
                           style="margin: 0 0 0 5px;white-space: nowrap;width: 450px;"
                           :style="textSysStyleSmart"
                    >
                        Loaded Settings for Model:&nbsp;
                        <select-block
                            :options="reprtsOpts()"
                            :sel_value="selReprt ? selReprt.id : null"
                            :style="{ maxWidth:'200px', height:'32px', }"
                            @option-select="reprtsChange"
                        ></select-block>
                    </label>

                    <info-sign-link v-show="activeTab === 'templates'" class="ml5 mr5" :app_sett_key="'help_link_report_adn_templates'" :hgt="30" :txt="'for Report/Templates'"></info-sign-link>
                    <info-sign-link v-show="activeTab === 'list'" class="ml5 mr5" :app_sett_key="'help_link_report_adn_list'" :hgt="30" :txt="'Report/List'"></info-sign-link>
                    <info-sign-link v-show="activeTab === 'details'" class="ml5 mr5" :app_sett_key="'help_link_report_adn_details'" :hgt="30" :txt="'for Report/Details'"></info-sign-link>
                </div>
            </div>
            <div class="permissions-menu-body" style="border: 1px solid #CCC;">

                <div class="full-height permissions-panel no-padding" v-show="activeTab === 'templates'" :style="$root.themeMainBgStyle">
                    <custom-table
                        :cell_component_name="'custom-cell-addon'"
                        :global-meta="tableMeta"
                        :table-meta="$root.settingsMeta['table_report_templates']"
                        :settings-meta="$root.settingsMeta"
                        :all-rows="tableMeta._report_templates"
                        :rows-count="tableMeta._report_templates.length"
                        :adding-row="{active: canAddonEdit(), position: 'bottom'}"
                        :cell-height="1"
                        :max-cell-rows="0"
                        :is-full-width="true"
                        :with_edit="canAddonEdit()"
                        :user="$root.user"
                        :behavior="'settings_report_specific'"
                        :use_theme="true"
                        @added-row="addTmplat"
                        @updated-row="updateTmplat"
                        @delete-row="deleteTmplat"
                    ></custom-table>
                </div>

                <div class="full-height permissions-panel no-padding" v-show="activeTab === 'list'" :style="$root.themeMainBgStyle">
                    <custom-table
                        :cell_component_name="'custom-cell-addon'"
                        :global-meta="tableMeta"
                        :table-meta="$root.settingsMeta['table_reports']"
                        :settings-meta="$root.settingsMeta"
                        :all-rows="tableMeta._reports"
                        :rows-count="tableMeta._reports.length"
                        :adding-row="{active: true, position: 'bottom'}"
                        :cell-height="1"
                        :max-cell-rows="0"
                        :is-full-width="true"
                        :with_edit="canAddonEdit()"
                        :selected-row="selectedCol"
                        :user="$root.user"
                        :behavior="'settings_report_specific'"
                        :use_theme="true"
                        @added-row="addReprt"
                        @updated-row="updateReprt"
                        @delete-row="deleteReprt"
                        @row-index-clicked="rowIndexReprt"
                        @show-add-ddl-option="runReportsMaking"
                    ></custom-table>
                </div>

                <div class="full-height permissions-panel no-padding" v-show="activeTab === 'details'" :style="$root.themeMainBgStyle">
                    <div v-if="!selReprt" class="flex flex--center full-height bold bg-white">No report selected!</div>
                    <div v-else class="permissions-panel full-height" :style="$root.themeMainBgStyle">
                        <div class="relative">
                            <label class="ml5 bold white mb0">Links</label>
                        </div>
                        <div class="" style="border: 1px solid #CCC; height: calc(50% - 27px);">
                            <div class="full-height permissions-panel no-padding" :style="$root.themeMainBgStyle">
                                <custom-table
                                    v-if="selReprt"
                                    :cell_component_name="'custom-cell-addon'"
                                    :global-meta="tableMeta"
                                    :table-meta="$root.settingsMeta['table_report_sources']"
                                    :settings-meta="$root.settingsMeta"
                                    :all-rows="selReprt._sources"
                                    :rows-count="selReprt._sources.length"
                                    :adding-row="{active: true, position: 'bottom'}"
                                    :cell-height="1"
                                    :max-cell-rows="0"
                                    :is-full-width="true"
                                    :with_edit="canPermisEdit()"
                                    :selected-row="selectedSource"
                                    :user="$root.user"
                                    :behavior="'settings_report_specific'"
                                    :use_theme="true"
                                    @added-row="addSourc"
                                    @updated-row="updateSourc"
                                    @delete-row="deleteSourc"
                                    @row-index-clicked="rowIndexSourc"
                                ></custom-table>
                            </div>
                        </div>
                        <div class="relative">
                            <label class="ml5 bold white mb0 mt5">Correspondences</label>

                            <div class="flex flex--center-v flex--end" style="position: absolute; top: 3px; right: 0; height: 32px; width: 50%;">
                                <label class="flex flex--center ml15 mr0" style="margin: 0 0 0 5px;white-space: nowrap;width: 300px;" :style="textSysStyleSmart">
                                    Loaded Links:&nbsp;
                                    <select-block
                                        :options="srcsOpts()"
                                        :sel_value="selRepSource ? selRepSource.id : null"
                                        :style="{ maxWidth:'200px', height:'32px', }"
                                        @option-select="srcsChange"
                                    ></select-block>
                                </label>
                            </div>
                        </div>
                        <div class="" style="border: 1px solid #CCC; height: calc(50% - 40px);">
                            <div class="full-height permissions-panel no-padding" :style="$root.themeMainBgStyle">
                                <custom-table
                                    v-if="selRepSource"
                                    :cell_component_name="'custom-cell-addon'"
                                    :global-meta="tableMeta"
                                    :table-meta="$root.settingsMeta['table_report_source_variables']"
                                    :settings-meta="$root.settingsMeta"
                                    :all-rows="selRepSource._variables"
                                    :rows-count="selRepSource._variables.length"
                                    :adding-row="{active: true, position: 'bottom'}"
                                    :cell-height="1"
                                    :max-cell-rows="0"
                                    :is-full-width="true"
                                    :with_edit="canPermisEdit()"
                                    :user="$root.user"
                                    :behavior="'settings_report_specific'"
                                    :use_theme="true"
                                    :parent-row="selRepSource"
                                    @added-row="addVariab"
                                    @updated-row="updateVariab"
                                    @delete-row="deleteVariab"
                                    @show-add-ddl-option="showAttributePop"
                                ></custom-table>
                                <div v-else class="flex flex--center full-height bold bg-white">No RC selected!</div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>

        <template v-for="saver in forBiSavers">
            <report-bi-saver
                v-if="saver.active"
                :ref_cond_id="saver.ref_cond_id"
                :ref_table_id="saver.ref_table_id"
                :tbl-row="saver.row"
                @charts-are-saved="runSingleReport(saver)"
            ></report-bi-saver>
        </template>
        
        <report-variable-attributes-pop-up
            v-if="reportVariable"
            :report-variable="reportVariable"
            @popup-close="hideAttributePop"
        ></report-variable-attributes-pop-up>
    </div>
</template>

<script>
    import {SpecialFuncs} from "../../../../classes/SpecialFuncs";
    import {Endpoints} from "../../../../classes/Endpoints";

    import CellStyleMixin from "./../../../_Mixins/CellStyleMixin.vue";

    import SelectBlock from "../../../CommonBlocks/SelectBlock";
    import CustomTable from "../../../CustomTable/CustomTable";
    import InfoSignLink from "../../../CustomTable/Specials/InfoSignLink.vue";
    import ReportBiSaver from "./Report/ReportBiSaver.vue";
    import ReportVariableAttributesPopUp from "../../../CustomPopup/ReportVariableAttributesPopUp.vue";

    export default {
        name: "TabReportView",
        mixins: [
            CellStyleMixin,
        ],
        components: {
            ReportVariableAttributesPopUp,
            ReportBiSaver,
            InfoSignLink,
            CustomTable,
            SelectBlock,
        },
        data: function () {
            return {
                reportVariable: null,
                activeTab: 'list',
                activeSubTab: 'rcs',
                selectedCol: -1,
                selectedSource: 0,
                forBiSavers: [],
            }
        },
        props: {
            table_id: Number,
            tableMeta: Object,
            tableRows: Array,
            isVisible: Boolean,
            request_params: Object,
        },
        computed: {
            selReprt() {
                return this.tableMeta._reports && this.tableMeta._reports[this.selectedCol]
                    ? this.tableMeta._reports[this.selectedCol]
                    : null;
            },
            selRepSource() {
                return this.selReprt && this.selReprt._sources
                    ? this.selReprt._sources[this.selectedSource]
                    : null;
            },
        },
        watch: {
            table_id(val) {
                this.rowIndexReprt(0);
                this.selectedSource = 0;
                this.activeTab = 'list';
                this.activeSubTab = 'rcs';
            },
            isVisible(val) {
                if (val && this.selectedCol < 0) {
                    this.rowIndexReprt(0);
                }
            },
        },
        methods: {
            canPermisEdit() {
                return this.$root.addonCanPermisEdit(this.tableMeta, this.selReprt, '_report_rights');
            },
            canAddonEdit() {
                return this.$root.addonCanEditGeneral(this.tableMeta, 'report');
            },
            reprtsOpts() {
                return _.map(this.tableMeta._reports, (tour) => {
                    return { val:tour.id, show:tour.report_name, };
                });
            },
            reprtsChange(opt) {
                this.rowIndexReprt( _.findIndex(this.reprtsOpts(), {val: Number(opt.val)}) );
            },
            rowIndexReprt(index) {
                this.selectedCol = index;
                this.selectedSource = 0;
                this.activeTab = 'details';
                this.activeSubTab = 'rcs';

                if (this.selReprt && !this.$root.reportTemplates[this.selReprt.report_template_id]) {
                    this.cacheVariables();
                }
            },
            srcsOpts() {
                return _.map(this.selReprt._sources, (tour) => {
                    return { val:tour.id, show:tour.name, };
                });
            },
            srcsChange(opt) {
                this.rowIndexSourc( _.findIndex(this.srcsOpts(), {val: Number(opt.val)}) );
            },
            rowIndexSourc(index) {
                this.selectedSource = index;
                this.activeSubTab = 'corrs';
            },
            cacheVariables() {
                axios.get('/ajax/addon-report/template-vars', {
                    params: {report_id: this.selReprt.id},
                }).then(({data}) => {
                    this.$root.reportTemplates[this.selReprt.report_template_id] = _.map(data, (el) => {
                        return {val: el, show: el};
                    });
                }).catch(errors => {
                    Swal('Info', getErrors(errors));
                });
            },

            //REPORTS
            addReprt(report) {
                axios.post('/ajax/addon-report', {
                    table_id: this.tableMeta.id,
                    fields: report,
                }).then(({data}) => {
                    this.tableMeta._reports = data;
                    this.rowIndexReprt(this.selectedCol);
                }).catch(errors => {
                    Swal('Info', getErrors(errors));
                });
            },
            updateReprt(report) {
                axios.put('/ajax/addon-report', {
                    model_id: report.id,
                    fields: report,
                }).then(({data}) => {
                    this.tableMeta._reports = data;
                }).catch(errors => {
                    Swal('Info', getErrors(errors));
                });
            },
            deleteReprt(report) {
                axios.delete('/ajax/addon-report', {
                    params: {
                        model_id: report.id,
                    }
                }).then(({data}) => {
                    this.tableMeta._reports = data;
                }).catch(errors => {
                    Swal('Info', getErrors(errors));
                });
            },

            //TEMPLATES
            addTmplat(report) {
                axios.post('/ajax/addon-report/template', {
                    table_id: this.tableMeta.id,
                    fields: report,
                }).then(({data}) => {
                    this.tableMeta._report_templates = data;
                }).catch(errors => {
                    Swal('Info', getErrors(errors));
                });
            },
            updateTmplat(report) {
                axios.put('/ajax/addon-report/template', {
                    model_id: report.id,
                    fields: report,
                }).then(({data}) => {
                    this.tableMeta._report_templates = data;
                    this.cacheVariables();
                }).catch(errors => {
                    Swal('Info', getErrors(errors));
                });
            },
            deleteTmplat(report) {
                axios.delete('/ajax/addon-report/template', {
                    params: {
                        model_id: report.id,
                    }
                }).then(({data}) => {
                    this.tableMeta._report_templates = data;
                }).catch(errors => {
                    Swal('Info', getErrors(errors));
                });
            },

            //SOURCES
            addSourc(source) {
                axios.post('/ajax/addon-report/source', {
                    report_id: this.selReprt.id,
                    fields: source,
                }).then(({data}) => {
                    this.tableMeta._reports = data;
                }).catch(errors => {
                    Swal('Info', getErrors(errors));
                });
            },
            updateSourc(source) {
                axios.put('/ajax/addon-report/source', {
                    model_id: source.id,
                    fields: source,
                }).then(({data}) => {
                    this.tableMeta._reports = data;
                }).catch(errors => {
                    Swal('Info', getErrors(errors));
                });
            },
            deleteSourc(source) {
                axios.delete('/ajax/addon-report/source', {
                    params: {
                        model_id: source.id,
                    }
                }).then(({data}) => {
                    this.tableMeta._reports = data;
                }).catch(errors => {
                    Swal('Info', getErrors(errors));
                });
            },

            //VARIABLES
            addVariab(variable) {
                axios.post('/ajax/addon-report/variable', {
                    report_source_id: this.selRepSource.id,
                    fields: variable,
                }).then(({data}) => {
                    this.tableMeta._reports = data;
                }).catch(errors => {
                    Swal('Info', getErrors(errors));
                });
            },
            updateVariab(variable) {
                axios.put('/ajax/addon-report/variable', {
                    model_id: variable.id,
                    fields: variable,
                }).then(({data}) => {
                    this.tableMeta._reports = data;
                }).catch(errors => {
                    Swal('Info', getErrors(errors));
                });
            },
            deleteVariab(variable) {
                axios.delete('/ajax/addon-report/variable', {
                    params: {
                        model_id: variable.id,
                    }
                }).then(({data}) => {
                    this.tableMeta._reports = data;
                }).catch(errors => {
                    Swal('Info', getErrors(errors));
                });
            },

            //Attributes popup
            showAttributePop(tableHeader, tableRow) {
                this.reportVariable = tableRow;
            },
            hideAttributePop(AttributeString) {
                if (this.reportVariable.additional_attributes !== AttributeString) {
                    this.reportVariable.additional_attributes = AttributeString;
                    this.updateVariab(this.reportVariable);
                }
                this.reportVariable = null;
            },
            
            //Make Reports
            runReportsMaking(report) {
                let hasBiVariables = _.filter(report._sources, (src) => {
                    return _.find(src._variables, {variable_type: 'bi'});
                });
                if (hasBiVariables.length) {
                    Swal({
                        title: 'Info',
                        html: 'Reports process have been started!'
                            + (report.report_data_range !== '0' ? '<br>Warning: reports with "BI" variables can be done for rows which are visible in GridView only!' : '')
                    });
                    _.each(hasBiVariables, (source) => {
                        let refTable = _.find(this.tableMeta._ref_conditions, {id: Number(source.ref_cond_id)}) || this.tableMeta;
                        _.each(this.tableRows, (row) => {
                            this.forBiSavers.push({
                                active: 1,
                                row_id: row['id'],
                                row: row,
                                ref_cond_id: source.ref_cond_id,
                                ref_table_id: refTable.id,
                                report: report,
                            });
                        });
                    });
                } else {
                    Endpoints.runReportsMaking(report, this.tableMeta, this.request_params);
                }
            },
            runSingleReport(saver) {
                Endpoints.runReportsMaking(saver.report, this.tableMeta, this.request_params, saver.row_id, true);
                saver.active = 0;
            },
        },
        mounted() {
        },
        beforeDestroy() {
        }
    }
</script>

<style lang="scss" scoped>
    @import "./SettingsModule/TabSettingsPermissions";
    .flex--end {
        .form-control {
            padding: 3px 6px;
            height: 28px;
        }
    }
</style>