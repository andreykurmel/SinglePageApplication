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

                    <div v-if="selCalendar" class="flex flex--center-v flex--end" style="position: absolute; top: 0; right: 0; height: 32px; width: 50%;">
                        <label class="flex flex--center ml15 mr0" style="margin: 0 0 0 5px;white-space: nowrap;" :style="textSysStyleSmart">
                            Loaded Settings for Calendar:&nbsp;
                            <select-block
                                :options="calendOpts()"
                                :sel_value="selCalendar.id"
                                :style="{ width:'250px', height:'32px', }"
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
                            :table-meta="$root.settingsMeta['table_calendars']"
                            :settings-meta="$root.settingsMeta"
                            :all-rows="tableMeta._calendar_addons"
                            :adding-row="{ active: true, position: 'bottom' }"
                            :rows-count="tableMeta._calendar_addons.length"
                            :cell-height="1"
                            :max-cell-rows="0"
                            :is-full-width="true"
                            :with_edit="canAddonEdit()"
                            :selected-row="selIdx"
                            :user="$root.user"
                            :behavior="'settings_calendar_add'"
                            :available-columns="availableCalendarColumns"
                            :use_theme="true"
                            @added-row="addCalendar"
                            @delete-row="deleteCalendar"
                            @updated-row="updateCalendar"
                            @row-index-clicked="rowIndexClicked"
                        ></custom-table>
                    </div>

                    <div class="full-height permissions-panel no-padding overflow-auto"
                         v-if="selCalendar"
                         v-show="activeTab === 'general'"
                         :style="$root.themeMainBgStyle"
                    >
                        <div class="additionals_sett" :style="textSysStyle">
                            <div class="form-group flex flex--center-v">
                                <label style="width: 125px;">Start:<span data-v-3c06e661="" class="required-wildcart">*</span>&nbsp;</label>
                                <select class="form-control"
                                        v-model="selCalendar.fldid_calendar_start"
                                        @change="updateCalendar(selCalendar)"
                                        style="width: 200px"
                                        :style="textSysStyle"
                                        :disabled="!canPermisEdit()"
                                >
                                    <option v-for="fld in tableMeta._fields"
                                            v-if="$root.systemFieldsNoId.indexOf(fld.field) === -1"
                                            :value="fld.id">{{ $root.uniqName(fld.name) }}</option>
                                </select>
                                <label style="width: 105px;">&nbsp;&nbsp;&nbsp;End:&nbsp;</label>
                                <select class="form-control"
                                        v-model="selCalendar.fldid_calendar_end"
                                        @change="updateCalendar(selCalendar)"
                                        style="width: 200px"
                                        :style="textSysStyle"
                                        :disabled="!canPermisEdit()"
                                >
                                    <option v-for="fld in tableMeta._fields"
                                            v-if="$root.systemFieldsNoId.indexOf(fld.field) === -1"
                                            :value="fld.id">{{ $root.uniqName(fld.name) }}</option>
                                </select>
                            </div>

                            <div class="form-group flex flex--center-v">
                                <label style="width: 125px;">Title:<span data-v-3c06e661="" class="required-wildcart">*</span>&nbsp;</label>
                                <select class="form-control"
                                        v-model="selCalendar.fldid_calendar_title"
                                        @change="updateCalendar(selCalendar)"
                                        style="width: 200px"
                                        :style="textSysStyle"
                                        :disabled="!canPermisEdit()"
                                >
                                    <option v-for="fld in tableMeta._fields"
                                            v-if="$root.systemFieldsNoId.indexOf(fld.field) === -1"
                                            :value="fld.id">{{ $root.uniqName(fld.name) }}</option>
                                </select>

                                <label style="width: 105px;">&nbsp;&nbsp;&nbsp;CFs:&nbsp;</label>
                                <select class="form-control"
                                        v-model="selCalendar.fldid_calendar_cond_format"
                                        @change="updateCalendar(selCalendar)"
                                        style="width: 200px"
                                        :style="textSysStyle"
                                        :disabled="!canPermisEdit()"
                                >
                                    <option v-for="fld in tableMeta._fields"
                                            v-if="$root.systemFieldsNoId.indexOf(fld.field) === -1"
                                            :value="fld.id">{{ $root.uniqName(fld.name) }}</option>
                                </select>
                            </div>

                            <div class="form-group flex flex--center-v">
                                <label style="width: 125px;">Locale:&nbsp;</label>
                                <select class="form-control"
                                        v-model="selCalendar.calendar_locale"
                                        @change="updateCalendar(selCalendar)"
                                        style="width: 200px"
                                        :style="textSysStyle"
                                        :disabled="!canPermisEdit()"
                                >
                                    <option :value="null">Default</option>
                                    <option v-for="cnt in $root.settingsMeta.countries_all" :value="cnt.iso_3166_2">{{ cnt.name }}</option>
                                </select>

                                <label style="width: 105px;">&nbsp;&nbsp;&nbsp;Timezone:&nbsp;</label>
                                <moment-timezones
                                        :cur_tz="selCalendar.calendar_timezone || $root.user.timezone"
                                        :name="'Timezone'"
                                        style="width: 200px"
                                        :style="textSysStyle"
                                        :is_disabled="!canPermisEdit()"
                                        @changed-tz="updateTZ"
                                ></moment-timezones>
                            </div>
                            
                            <div class="form-group flex flex--center-v">
                                <label style="width: 125px;">Visible Dates:&nbsp;</label>
                                <select class="form-control"
                                        v-model="selCalendar.calendar_init_date"
                                        @change="updateCalendar(selCalendar)"
                                        style="width: 200px"
                                        :style="textSysStyle"
                                        :disabled="!canPermisEdit()"
                                >
                                    <option value="earliest">Earliest</option>
                                    <option value="present">Present</option>
                                    <option value="latest">Latest</option>
                                </select>
                            </div>
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
    import MomentTimezones from "../../../../MomentTimezones";

    import CellStyleMixin from "../../../../_Mixins/CellStyleMixin";
    import SelectBlock from "../../../../CommonBlocks/SelectBlock";

    export default {
        name: "CalendarSettings",
        components: {
            SelectBlock,
            MomentTimezones,
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
                availableCalendarColumns: [
                    'name',
                    'calendar_data_range',
                    'description',
                    'calendar_active',
                ],
            }
        },
        props: {
            tableMeta: Object,
        },
        computed: {
            selCalendar() {
                return this.tableMeta._calendar_addons[this.selIdx];
            },
        },
        watch: {
        },
        methods: {
            canPermisEdit() {
                return this.$root.addonCanPermisEdit(this.tableMeta, this.selCalendar, '_calendar_rights');
            },
            canAddonEdit() {
                return this.$root.addonCanEditGeneral(this.tableMeta, 'calendar');
            },
            changeTab(key) {
                this.activeTab = key;
                this.$emit('set-sub-tab', key);
            },
            calendOpts() {
                return _.map(this.tableMeta._calendar_addons, (fld) => {
                    return { val:fld.id, show:fld.name, };
                });
            },
            rowIndexClicked(optOrIdx) {
                if (typeof optOrIdx === 'object') {
                    this.selIdx = _.findIndex(this.tableMeta._calendar_addons, {id: Number(optOrIdx.val)});
                } else {
                    this.selIdx = optOrIdx;
                }
            },
            //Calendar
            addCalendar(tableRow) {
                let fields = _.cloneDeep(tableRow);//copy object
                this.$root.deleteSystemFields(fields);

                $.LoadingOverlay('show');
                axios.post('/ajax/addon-calendar', {
                    table_id: this.tableMeta.id,
                    fields: fields
                }).then(({ data }) => {
                    this.tableMeta._calendar_addons = data;
                }).catch(errors => {
                    Swal('Info', getErrors(errors));
                }).finally(() => {
                    $.LoadingOverlay('hide');
                });
            },
            updateCalendar(tableRow) {
                let fields = _.cloneDeep(tableRow);//copy object
                this.$root.deleteSystemFields(fields);

                $.LoadingOverlay('show');
                axios.put('/ajax/addon-calendar', {
                    model_id: tableRow.id,
                    fields: fields
                }).then(({ data }) => {
                    this.tableMeta._calendar_addons = data;
                }).catch(errors => {
                    Swal('Info', getErrors(errors));
                }).finally(() => {
                    $.LoadingOverlay('hide');
                });
            },
            deleteCalendar(tableRow) {
                $.LoadingOverlay('show');
                axios.delete('/ajax/addon-calendar', {
                    params: {
                        model_id: tableRow.id
                    }
                }).then(({ data }) => {
                    this.tableMeta._calendar_addons = data;
                }).catch(errors => {
                    Swal('Info', getErrors(errors));
                }).finally(() => {
                    $.LoadingOverlay('hide');
                });
            },
            updateTZ(tz) {
                this.selCalendar.calendar_timezone = tz;
                this.updateCalendar(this.selCalendar);
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
    }
</style>