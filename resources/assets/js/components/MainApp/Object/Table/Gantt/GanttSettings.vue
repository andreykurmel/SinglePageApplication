<template>
    <div class="full-height">
        <div class="full-height permissions-tab p5" :style="$root.themeMainBgStyle">

            <div class="permissions-panel full-height" :style="$root.themeMainBgStyle">

                <div class="permissions-menu-header">
                    <button class="btn btn-default btn-sm" :style="textSysStyle" :class="{active : activeTab === 'list'}" @click="changeTab('list')">
                        List
                    </button>
                    <button class="btn btn-default btn-sm" :style="textSysStyle" :class="{active : activeTab === 'general'}" @click="changeTab('general')">
                        General
                    </button>
                    <button class="btn btn-default btn-sm" :style="textSysStyle" :class="{active : activeTab === 'fld_specific'}" @click="changeTab('fld_specific')">
                        Field Specific
                    </button>

                    <div v-if="selectedGantt" class="flex flex--center-v flex--end" style="position: absolute; top: 0; right: 0; height: 32px; width: 50%;">
                        <label class="flex flex--center ml15 mr0" style="margin: 0 0 0 5px;white-space: nowrap;" :style="textSysStyleSmart">
                            Loaded Gantt:&nbsp;
                            <select-block
                                :options="ganttOpts()"
                                :sel_value="selectedGantt.id"
                                :style="{ width:'150px', height:'32px', }"
                                @option-select="rowIndexClicked"
                            ></select-block>
                        </label>
                    </div>
                </div>
                <div class="permissions-menu-body" style="border: 1px solid #CCC;">

                    <div class="full-height permissions-panel no-padding" v-show="activeTab === 'list'" :style="$root.themeMainBgStyle">
                        <custom-table
                            :cell_component_name="'custom-cell-addon'"
                            :global-meta="tableMeta"
                            :table-meta="$root.settingsMeta['table_gantts']"
                            :settings-meta="$root.settingsMeta"
                            :all-rows="tableMeta._gantt_addons"
                            :adding-row="{ active: true, position: 'bottom' }"
                            :rows-count="tableMeta._gantt_addons.length"
                            :cell-height="1"
                            :max-cell-rows="0"
                            :is-full-width="true"
                            :with_edit="canAddonEdit()"
                            :selected-row="selIdx"
                            :user="$root.user"
                            :behavior="'settings_gantt_add'"
                            :available-columns="availableGanttColumns"
                            :use_theme="true"
                            @added-row="addGantt"
                            @delete-row="deleteGantt"
                            @updated-row="updateGantt"
                            @row-index-clicked="rowIndexClicked"
                        ></custom-table>
                    </div>

                    <div class="full-height permissions-panel no-padding"
                         v-if="selectedGantt"
                         v-show="activeTab === 'general'"
                         :style="$root.themeMainBgStyle"
                    >
                        <div class="additionals_sett flex flex--col" :style="textSysStyleSmart">
                            <div class="form-group flex flex--center-v h36">
                                <label>Date info for top / horizontal axis:&nbsp;</label>
                                <select class="form-control" :style="textSysStyle" :disabled="!canPermisEdit()" v-model="selectedGantt.gantt_info_type" @change="updateGantt(selectedGantt)" style="width: 100px;">
                                    <option :value="null">Auto</option>
                                    <option value="year">Year</option>
                                    <option value="month">Month</option>
                                    <option value="week">Week</option>
                                    <option value="day">Day</option>
                                </select>
                            </div>
                            <div class="form-group flex flex--center-v h36">
                                <label>Navigation, Top:&nbsp;</label>
                                <span class="indeterm_check__wrap">
                                    <span class="indeterm_check" @click="clickCheckbox('gantt_navigation')">
                                        <i v-if="selectedGantt.gantt_navigation" class="glyphicon glyphicon-ok group__icon"></i>
                                    </span>
                                </span>
                                <label>&nbsp;&nbsp;&nbsp;Bottom:&nbsp;</label>
                                <span class="indeterm_check__wrap">
                                    <span class="indeterm_check" @click="clickCheckbox('gantt_navigator_bottom')">
                                        <i v-if="selectedGantt.gantt_navigator_bottom" class="glyphicon glyphicon-ok group__icon"></i>
                                    </span>
                                </span>
                                <label>&nbsp;&nbsp;&nbsp;Height:&nbsp;</label>
                                <input
                                    type="number"
                                    :style="textSysStyle"
                                    :disabled="!canPermisEdit()"
                                    style="width: 60px;"
                                    class="form-control w-sm"
                                    v-model="selectedGantt.gantt_navigator_height"
                                    @change="updateGantt(selectedGantt)"
                                />
                                <label>px</label>
                            </div>
                            <div class="form-group flex flex--center-v h36">
                                <label>Show Item Name:&nbsp;</label>
                                <span class="indeterm_check__wrap">
                                    <span class="indeterm_check" @click="clickCheckbox('gantt_show_names')">
                                        <i v-if="selectedGantt.gantt_show_names" class="glyphicon glyphicon-ok group__icon"></i>
                                    </span>
                                </span>
                                <label>&nbsp;&nbsp;&nbsp;Row Height:&nbsp;</label>
                                <input
                                    type="number"
                                    :style="textSysStyle"
                                    :disabled="!canPermisEdit()"
                                    style="width: 60px;"
                                    class="form-control w-sm"
                                    v-model="selectedGantt.gantt_row_height"
                                    @change="updateGantt(selectedGantt)"
                                />
                                <label>px</label>
                            </div>
                            <div v-if="presentGrouping()" class="form-group flex flex--center-v h36">
                                <label>Highlight on Hover:&nbsp;</label>
                                <span class="indeterm_check__wrap">
                                    <span class="indeterm_check" @click="clickCheckbox('gantt_highlight')">
                                        <i v-if="selectedGantt.gantt_highlight" class="glyphicon glyphicon-ok group__icon"></i>
                                    </span>
                                </span>
                            </div>
                            <div class="form-group flex flex--center-v h36">
                                <label>Weekday format:&nbsp;</label>
                                <select class="form-control" :style="textSysStyle" :disabled="!canPermisEdit()" v-model="selectedGantt.gantt_day_format" @change="updateGantt(selectedGantt)" style="width: 175px;">
                                    <option :value="null">M</option>
                                    <option value="part">Mon</option>
                                    <option value="full">Monday</option>
                                    <option value="extend">Monday, {{ today }}</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="full-height permissions-panel no-padding relative"
                         v-if="selectedGantt"
                         v-show="activeTab === 'fld_specific'"
                         :style="$root.themeMainBgStyle"
                    >
                        <div class="vertical-buttons">
                            <button class="btn btn-default btn-sm" :style="textSysStyle" :class="{active : activeSpecificTab === 'charts'}" @click="activeSpecificTab = 'charts'">
                                Chart
                            </button>
                            <button class="btn btn-default btn-sm" :style="textSysStyle" :class="{active : activeSpecificTab === 'headers'}" @click="activeSpecificTab = 'headers'">
                                Header
                            </button>
                        </div>
                        <div class="absolute-frame" style="left: 32px; z-index: 100; background: #FFF; border-left: 1px solid #CCC;">
                            <span v-if="presentGrouping()" class="noter">
                                No additional column allowed for levelled header.
                            </span>
                            <custom-table
                                :cell_component_name="'custom-cell-addon'"
                                :global-meta="tableMeta"
                                :table-meta="$root.settingsMeta['table_gantt_settings']"
                                :settings-meta="$root.settingsMeta"
                                :all-rows="tableMeta._fields"
                                :rows-count="tableMeta._fields.length"
                                :parent-row="selectedGantt"
                                :cell-height="1"
                                :user="$root.user"
                                :with_edit="canPermisEdit()"
                                :behavior="'settings_gantt_specific'"
                                :available-columns="activeSpecificTab === 'headers' ? availableSettingsColumnsHeader : availableSettingsColumnsBar"
                                @updated-row="syncSetting"
                            ></custom-table>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
</template>

<script>
    import CustomTable from "../../../../CustomTable/CustomTable";
    import ForSettingsPopUp from "../../../../CustomPopup/ForSettingsPopUp";
    import SelectBlock from "../../../../CommonBlocks/SelectBlock";

    import CellStyleMixin from "../../../../_Mixins/CellStyleMixin";

    export default {
        name: "GanttSettings",
        components: {
            SelectBlock,
            ForSettingsPopUp,
            CustomTable,
        },
        mixins: [
            CellStyleMixin,
        ],
        data: function () {
            return {
                selIdx: 0,
                activeTab: 'list',
                activeSpecificTab: 'headers',
                availableGanttColumns: [
                    'name',
                    'gantt_data_range',
                    'gantt_active',
                    'description',
                ],
                availableSettingsColumnsHeader: [
                    'table_field_id',
                    'gantt_left_header',
                    'is_gantt_group',
                    'is_gantt_parent_group',
                    'is_gantt_main_group',
                ],
                availableSettingsColumnsBar: [
                    'table_field_id',
                    'is_gantt_name',
                    'is_gantt_parent',
                    'is_gantt_start',
                    'is_gantt_end',
                    'is_gantt_progress',
                    'is_gantt_color',
                    'gantt_tooltip',
                    'is_gantt_label_symbol',
                    'is_gantt_milestone',
                ],
            }
        },
        props:{
            tableMeta: Object,
        },
        computed: {
            selectedGantt() {
                return this.tableMeta._gantt_addons[this.selIdx];
            },
            today() {
                return moment().format('M/D/Y');
            },
        },
        watch: {
        },
        methods: {
            presentGrouping() {
                return _.find(this.selectedGantt._specifics, (spec) => {
                    return spec.is_gantt_main_group || spec.is_gantt_parent_group;
                });
            },
            canPermisEdit() {
                return this.$root.addonCanPermisEdit(this.tableMeta, this.selectedGantt, '_gantt_rights');
            },
            canAddonEdit() {
                return this.$root.addonCanEditGeneral(this.tableMeta, 'gantt');
            },
            clickCheckbox(key) {
                if (!this.canAddonEdit()) {
                    return;
                }
                this.selectedGantt[key] = this.selectedGantt[key] ? 0 : 1;
                this.updateGantt(this.selectedGantt);
            },
            changeTab(key) {
                this.activeTab = key;
                this.$emit('set-sub-tab', key);
            },
            ganttOpts() {
                return _.map(this.tableMeta._gantt_addons, (fld) => {
                    return { val:fld.id, show:fld.name, };
                });
            },
            rowIndexClicked(optOrIdx) {
                if (typeof optOrIdx === 'object') {
                    this.selIdx = _.findIndex(this.tableMeta._gantt_addons, {id: Number(optOrIdx.val)});
                } else {
                    this.selIdx = optOrIdx;
                }
            },
            //Gantt
            addGantt(tableRow) {
                let fields = _.cloneDeep(tableRow);//copy object
                this.$root.deleteSystemFields(fields);

                $.LoadingOverlay('show');
                axios.post('/ajax/addon-gantt', {
                    table_id: this.tableMeta.id,
                    fields: fields
                }).then(({ data }) => {
                    this.tableMeta._gantt_addons = data;
                }).catch(errors => {
                    Swal('Info', getErrors(errors));
                }).finally(() => {
                    $.LoadingOverlay('hide');
                });
            },
            updateGantt(tableRow) {
                let fields = _.cloneDeep(tableRow);//copy object
                this.$root.deleteSystemFields(fields);

                $.LoadingOverlay('show');
                axios.put('/ajax/addon-gantt', {
                    model_id: tableRow.id,
                    fields: fields
                }).then(({ data }) => {
                    this.tableMeta._gantt_addons = data;
                }).catch(errors => {
                    Swal('Info', getErrors(errors));
                }).finally(() => {
                    $.LoadingOverlay('hide');
                });
            },
            deleteGantt(tableRow) {
                $.LoadingOverlay('show');
                axios.delete('/ajax/addon-gantt', {
                    params: {
                        model_id: tableRow.id
                    }
                }).then(({ data }) => {
                    this.tableMeta._gantt_addons = data;
                }).catch(errors => {
                    Swal('Info', getErrors(errors));
                }).finally(() => {
                    $.LoadingOverlay('hide');
                });
            },
            syncSetting(tableRow) {
                $.LoadingOverlay('show');
                axios.post('/ajax/addon-gantt/settings', {
                    model_id: this.selectedGantt.id,
                    field_id: tableRow.table_field_id,
                    datas: tableRow,
                }).then(({ data }) => {
                    this.selectedGantt._specifics = data;
                    this.redrawSpecific();
                }).catch(errors => {
                    Swal('Info', getErrors(errors));
                }).finally(() => {
                    $.LoadingOverlay('hide');
                });
            },
            redrawSpecific() {
                let last = this.activeSpecificTab;
                this.activeSpecificTab = '';
                this.$nextTick(() => {
                    this.activeSpecificTab = last;
                });
            },
        },
        mounted() {
        },
        beforeDestroy() {
        }
    }
</script>

<style lang="scss" scoped>
    @import "./../SettingsModule/TabSettingsPermissions";

    .additionals_sett {
        padding: 15px;
        white-space: nowrap;

        label {
            margin: 0;
        }
        .form-group {
            margin-right: 30px;
        }
        .h36 {
            height: 36px;
        }
    }
    .noter {
        position: absolute;
        right: 10px;
        bottom: 5px;
        z-index: 100;
        color: red;
        font-size: 18px;
    }
    .vertical-buttons {
        transform: rotate(-90deg);
        transform-origin: top right;
        right: calc(100% - 5px);
        position: absolute;
        white-space: nowrap;
        top: 5px;

        .btn {
            outline: none;
            background-color: #CCC;
        }
        .btn.active {
            background-color: #FFF;
        }
    }
</style>