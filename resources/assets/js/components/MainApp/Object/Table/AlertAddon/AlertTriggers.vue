<template>
    <div class="full-height" :style="$root.themeMainBgStyle">
        <table class="spaced-table" style="table-layout: fixed" :style="$root.themeMainBgStyle">
            <colgroup>
                <col style="width: 12%">
                <col style="width: 88%">
            </colgroup>
            <tbody :style="textSysStyleSmart">

            <tr>
                <td colspan="2" class="pad-bot"></td>
            </tr>

            <tr>
                <td colspan="2" class="pad-bot">
                    <label>Check one or multiple items (only selected record group (RGrp) will be monitored):</label>
                </td>
            </tr>

            <tr>
                <td class="pad-bot">
                    <span class="indeterm_check__wrap">
                        <span class="indeterm_check checkbox-input"
                              @click="!can_edit ? null : updateCheckBox('on_added')"
                              :class="{'disabled': !can_edit}"
                              :style="checkboxSys"
                        >
                            <i v-if="alert_sett.on_added" class="glyphicon glyphicon-ok group__icon"></i>
                        </span>
                    </span>
                    <label>When:</label>
                </td>
                <td class="pad-bot flex flex--center-v">
                    <label class="th_style flex__elem-remain">New Record Added</label>
                    <label class="th_style">Row Group:</label>
                    <select-block
                            :options="getRGrps()"
                            :sel_value="alert_sett.on_added_row_group_id"
                            :style="{ maxWidth:'200px', height:'32px', }"
                            :with_links="true"
                            :is_disabled="!can_edit"
                            :button_txt="'Add New'"
                            @option-select="(opt) => { updateSelect('on_added_row_group_id', opt.val) }"
                            @link-click="showRGRP(alert_sett.on_added_row_group_id)"
                            @button-click="showRGRP(null)"
                    ></select-block>
                </td>
            </tr>

            <tr>
                <td class="pad-bot">
                    <span class="indeterm_check__wrap">
                        <span class="indeterm_check checkbox-input"
                              @click="!can_edit ? null : updateCheckBox('on_deleted')"
                              :class="{'disabled': !can_edit}"
                              :style="checkboxSys"
                        >
                            <i v-if="alert_sett.on_deleted" class="glyphicon glyphicon-ok group__icon"></i>
                        </span>
                    </span>
                    <label>When:</label>
                </td>
                <td class="pad-bot flex flex--center-v">
                    <label class="th_style flex__elem-remain">Record Deleted</label>
                    <label class="th_style">Row Group:</label>
                    <select-block
                            :options="getRGrps()"
                            :sel_value="alert_sett.on_deleted_row_group_id"
                            :style="{ maxWidth:'200px', height:'32px', }"
                            :with_links="true"
                            :is_disabled="!can_edit"
                            :button_txt="'Add New'"
                            @option-select="(opt) => { updateSelect('on_deleted_row_group_id', opt.val) }"
                            @link-click="showRGRP(alert_sett.on_deleted_row_group_id)"
                            @button-click="showRGRP(null)"
                    ></select-block>
                </td>
            </tr>

            <tr>
                <td class="pad-bot">
                    <span class="indeterm_check__wrap">
                        <span class="indeterm_check checkbox-input"
                              @click="!can_edit ? null : updateCheckBox('on_updated')"
                              :class="{'disabled': !can_edit}"
                              :style="checkboxSys"
                        >
                            <i v-if="alert_sett.on_updated" class="glyphicon glyphicon-ok group__icon"></i>
                        </span>
                    </span>
                    <label>When:</label>
                </td>
                <td class="pad-bot flex flex--center-v">
                    <label class="th_style flex__elem-remain">Existing Record Updated</label>
                    <label class="th_style">Row Group:</label>
                    <select-block
                            :options="getRGrps()"
                            :sel_value="alert_sett.on_updated_row_group_id"
                            :style="{ maxWidth:'200px', height:'32px', }"
                            :with_links="true"
                            :is_disabled="!can_edit"
                            :button_txt="'Add New'"
                            @option-select="(opt) => { updateSelect('on_updated_row_group_id', opt.val) }"
                            @link-click="showRGRP(alert_sett.on_updated_row_group_id)"
                            @button-click="showRGRP(null)"
                    ></select-block>
                </td>
            </tr>

            <tr>
                <td class="pad-bot"></td>
                <td class="" style="border: 1px solid #CCC;height: 175px;">
                    <custom-table
                            :cell_component_name="'custom-cell-alert-notif'"
                            :global-meta="tableMeta"
                            :table-meta="$root.settingsMeta['table_alert_conditions']"
                            :all-rows="alert_sett._conditions"
                            :rows-count="alert_sett._conditions.length"
                            :cell-height="1"
                            :max-cell-rows="0"
                            :is-full-width="true"
                            :user="$root.user"
                            :behavior="'alert_notif'"
                            :adding-row="addingRow"
                            :available-columns="availAleConds"
                            :use_theme="true"
                            :with_edit="can_edit"
                            @added-row="addAlertCond"
                            @updated-row="updateAlertCond"
                            @delete-row="deleteAlertCond"
                    ></custom-table>
                </td>
            </tr>

            <tr>
                <td class="pad-bot pad-top">
                    <span class="indeterm_check__wrap">
                        <span class="indeterm_check checkbox-input"
                              @click="!can_edit ? null : updateCheckBox('on_snapshot')"
                              :class="{'disabled': !can_edit}"
                              :style="checkboxSys"
                        >
                            <i v-if="alert_sett.on_snapshot" class="glyphicon glyphicon-ok group__icon"></i>
                        </span>
                    </span>
                    <label>Scheduled:</label>
                </td>
                <td class="pad-bot pad-top flex flex--center-v" style="color: black;">
                    <select-block
                            :options="[
                                {val: 'recurring', show: 'Recurring'},
                                {val: 'one_time', show: 'One-time'},
                            ]"
                            :sel_value="alert_sett.snapshot_type"
                            :style="{ width:'125px', height:'32px', }"
                            :is_disabled="!can_edit"
                            class="margin-r"
                            @option-select="(opt) => { updateSelect('snapshot_type', opt.val) }"
                    ></select-block>

                    <div v-show="alert_sett.snapshot_type === 'one_time'" class="flex flex--center-v">
                        <input ref="picker_one_datetime"
                               @blur="hidePicker('snapshot_onetime_datetime', 'picker_one_datetime')"
                               :disabled="!can_edit"
                               class="form-control"
                               style="height: 32px; width: 240px;">
                    </div>

                    <div v-show="alert_sett.snapshot_type === 'recurring'" class="flex flex--center-v">
                        <select-block
                                :options="[
                                    {val: 'hourly', show: 'Hourly'},
                                    {val: 'daily', show: 'Daily'},
                                    {val: 'weekly', show: 'Weekly'},
                                    {val: 'monthly', show: 'Monthly'},
                                ]"
                                :sel_value="alert_sett.snapshot_frequency"
                                :style="{ width:'105px', height:'32px', }"
                                :is_disabled="!can_edit"
                                class="margin-r"
                                @option-select="(opt) => { updateSelect('snapshot_frequency', opt.val) }"
                        ></select-block>

                        <div v-show="alert_sett.snapshot_frequency === 'hourly'" class="flex flex--center-v" style="color: white;">
                            <span>Minute&nbsp;</span>
                            <input v-model="alert_sett.snapshot_hourly_freq"
                                   :disabled="!can_edit"
                                   class="form-control"
                                   @change="hourlyChanged"
                                   type="number"
                                   style="height: 32px; width: 70px;">
                        </div>

                        <select-block
                                v-show="alert_sett.snapshot_frequency === 'weekly'"
                                :is_multiselect="true"
                                :options="[
                                    {val: ['Sunday','Monday','Tuesday','Wednesday','Thursday','Friday','Saturday'].sort(), show: 'All Days'},
                                    {val: 'Sunday', show: 'Sunday'},
                                    {val: 'Monday', show: 'Monday'},
                                    {val: 'Tuesday', show: 'Tuesday'},
                                    {val: 'Wednesday', show: 'Wednesday'},
                                    {val: 'Thursday', show: 'Thursday'},
                                    {val: 'Friday', show: 'Friday'},
                                    {val: 'Saturday', show: 'Saturday'},
                                ]"
                                :sel_value="alert_sett.snapshot_day_freq"
                                :style="{ width:'150px', height:'32px', }"
                                :is_disabled="!can_edit"
                                class="margin-r"
                                @option-select="updFreq"
                        ></select-block>

                        <select-block
                                v-show="alert_sett.snapshot_frequency === 'monthly'"
                                :options="[
                                    {val: 'first', show: 'The 1st day'},
                                    {val: 'last', show: 'The last day'},
                                    {val: 'day', show: 'Specific day'},
                                ]"
                                :sel_value="alert_sett.snapshot_month_freq"
                                :style="{ width:'150px', height:'32px', }"
                                :is_disabled="!can_edit"
                                class="margin-r"
                                @option-select="(opt) => { updateSelect('snapshot_month_freq', opt.val) }"
                        ></select-block>
                        <div v-show="alert_sett.snapshot_frequency === 'monthly' && alert_sett.snapshot_month_freq === 'day'" class="flex flex--center-v margin-r">
                            <span class="white-bold">Day&nbsp;</span>
                            <input class="form-control"
                                   :disabled="!can_edit"
                                   v-model="alert_sett.snapshot_month_day"
                                   @change="sendUpdate('snapshot_month_day')"
                                   style="height: 32px; width: 60px;">
                        </div>
                        <input v-show="date_month_visible"
                               :disabled="!can_edit"
                               ref="picker_date_month"
                               @blur="hidePicker('snapshot_month_date', 'picker_date_month')"
                               class="form-control margin-r"
                               style="height: 32px; width: 130px;">

                        <input v-show="alert_sett.snapshot_frequency !== 'hourly'"
                               :disabled="!can_edit"
                               ref="picker_time_snapshot"
                               @blur="hidePicker('snapshot_time', 'picker_time_snapshot')"
                               class="form-control"
                               style="height: 32px; width: 120px;">
                    </div>

                    <select-block
                        :can_search="true"
                        :options="timezonesOpts()"
                        :sel_value="alert_sett.snapshot_timezone"
                        :style="{ width:'200px', height:'32px', }"
                        :is_disabled="!can_edit"
                        class="ml15"
                        @option-select="(opt) => { updateSelect('snapshot_timezone', opt.val) }"
                    ></select-block>

                    <div class="flex flex--center-v no-wrap" style="width: 300px; margin-left: auto;">
                        <label class="th_style">Row Group:</label>
                        <select-block
                            :options="getRGrps()"
                            :sel_value="alert_sett.snp_row_group_id"
                            :style="{ maxWidth:'200px', height:'32px', }"
                            :with_links="true"
                            :is_disabled="!can_edit"
                            :button_txt="'Add New'"
                            @option-select="(opt) => { updateSelect('snp_row_group_id', opt.val) }"
                            @link-click="showRGRP(alert_sett.snp_row_group_id)"
                            @button-click="showRGRP(null)"
                        ></select-block>
                    </div>
                </td>
            </tr>

            </tbody>
        </table>
    </div>
</template>

<script>
    import {SpecialFuncs} from "../../../../../classes/SpecialFuncs";
    import {OptionsHelper} from "../../../../../classes/helpers/OptionsHelper";
    import {MomentTzHelper} from "../../../../../classes/helpers/MomentTzHelper";

    import {eventBus} from "../../../../../app";

    import CustomTable from "../../../../CustomTable/CustomTable";
    import SelectBlock from "../../../../CommonBlocks/SelectBlock";

    import CellStyleMixin from "../../../../_Mixins/CellStyleMixin";

    export default {
        name: "AlertTriggers",
        components: {
            SelectBlock,
            CustomTable,
        },
        mixins: [
            CellStyleMixin,
        ],
        data: function () {
            return {
                date_month_visible: true,
                addingRow: {
                    active: true,
                    position: 'bottom'
                },
                availAleConds: ['table_field_id','logic','new_value','condition','is_active'],
            }
        },
        props:{
            tableMeta: Object,
            alert_sett: Object,
            can_edit: Boolean|Number,
        },
        computed: {
        },
        watch: {
            'alert_sett.id': function ($val) {
                this.setDatePickers();
            },
        },
        methods: {
            updFreq(opt) {
                let el = typeof opt.val === 'object' ? opt.val : [opt.val];
                if (_.intersection(this.alert_sett.snapshot_day_freq, el).length) {
                    this.alert_sett.snapshot_day_freq = _.difference(this.alert_sett.snapshot_day_freq, el);
                } else {
                    this.alert_sett.snapshot_day_freq = _.concat(this.alert_sett.snapshot_day_freq, el);
                }
                this.alert_sett.snapshot_day_freq = this.alert_sett.snapshot_day_freq.sort();
                this.sendUpdate('snapshot_day_freq');
            },
            timezonesOpts() {
                return MomentTzHelper.timezones();
            },
            updateSelect(key, option) {
                this.alert_sett[key] = option;
                this.sendUpdate(key);
            },
            updateCheckBox(param) {
                this.alert_sett[param] = this.alert_sett[param] ? 0 : 1;
                this.sendUpdate(param);
            },
            hourlyChanged() {
                this.alert_sett.snapshot_hourly_freq = Math.min(parseInt(this.alert_sett.snapshot_hourly_freq), 59);
                this.alert_sett.snapshot_hourly_freq = Math.max(parseInt(this.alert_sett.snapshot_hourly_freq), 0);
                this.sendUpdate('snapshot_hourly_freq');
            },
            sendUpdate(key) {
                this.date_month_visible = this.alert_sett.snapshot_frequency === 'monthly' && this.alert_sett.snapshot_month_freq === 'date';
                this.$emit('update-alert', this.alert_sett);
                this.setDatePickers();
            },
            hidePicker(key, ref) {
                let value = $(this.$refs[ref]).val();
                if (value === 'Invalid date') {
                    value = null;
                }
                if (value && ['snapshot_onetime_datetime','snapshot_time'].indexOf(key) > -1) {
                    let timezone = this.alert_sett.snapshot_timezone || this.$root.user.timezone || moment.tz.guess();
                    value = key === 'snapshot_time'
                        ? SpecialFuncs.timeToUTC(value, timezone, SpecialFuncs.timeFormat())
                        : SpecialFuncs.convertToUTC(value, timezone);
                }
                this.alert_sett[key] = value;
                this.sendUpdate(key);
            },
            addAlertCond(tableRow) {
                let fields = _.cloneDeep(tableRow);//copy object
                this.$root.deleteSystemFields(fields);

                $.LoadingOverlay('show');
                axios.post('/ajax/table/alert/condition', {
                    table_alert_id: this.alert_sett.id,
                    fields: fields
                }).then(({ data }) => {
                    this.alert_sett._conditions = data._conditions;
                }).catch(errors => {
                    Swal('Info', getErrors(errors));
                }).finally(() => {
                    $.LoadingOverlay('hide');
                });
            },
            updateAlertCond(tableRow) {
                let fields = _.cloneDeep(tableRow);//copy object
                this.$root.deleteSystemFields(fields);

                $.LoadingOverlay('show');
                axios.put('/ajax/table/alert/condition', {
                    table_alert_id: this.alert_sett.id,
                    table_cond_id: tableRow.id,
                    fields: fields
                }).then(({ data }) => {
                    this.alert_sett._conditions = data._conditions;
                }).catch(errors => {
                    Swal('Info', getErrors(errors));
                }).finally(() => {
                    $.LoadingOverlay('hide');
                });
            },
            deleteAlertCond(tableRow) {
                $.LoadingOverlay('show');
                axios.delete('/ajax/table/alert/condition', {
                    params: {
                        table_alert_id: this.alert_sett.id,
                        table_cond_id: tableRow.id
                    }
                }).then(({ data }) => {
                    this.alert_sett._conditions = data._conditions;
                }).catch(errors => {
                    Swal('Info', getErrors(errors));
                }).finally(() => {
                    $.LoadingOverlay('hide');
                });
            },
            getRGrps() {
                return OptionsHelper.rowGroup(this.tableMeta, true);
            },
            showRGRP(id) {
                eventBus.$emit('show-grouping-settings-popup', this.tableMeta.db_name, 'row', id);
            },
            setDatePickers() {
                if (! this.alert_sett.snapshot_timezone) {
                    this.alert_sett.snapshot_timezone = this.$root.user.timezone || moment.tz.guess();
                }

                $(this.$refs.picker_one_datetime).val( this.alert_sett.snapshot_onetime_datetime
                    ? SpecialFuncs.convertToLocal(this.alert_sett.snapshot_onetime_datetime, this.alert_sett.snapshot_timezone)
                    : null );

                $(this.$refs.picker_time_snapshot).val( this.alert_sett.snapshot_time
                    ? SpecialFuncs.timeToLocal(this.alert_sett.snapshot_time, this.alert_sett.snapshot_timezone, SpecialFuncs.timeFormat())
                    : null );

                $(this.$refs.picker_date_month).val( this.alert_sett.snapshot_month_date ? this.alert_sett.snapshot_month_date : null );
            },
        },
        mounted() {
            $(this.$refs.picker_one_datetime).datetimepicker({
                useCurrent: false,
                defaultDate: null,
                format: SpecialFuncs.dateTimeFormat(),
            }).on("input", (e) => {
                let val = $(this.$refs.picker_one_datetime).val();
                if (String(val).match(/am|pm/gi)) {
                    $(this.$refs.picker_one_datetime).val( moment( val ).format('YYYY-MM-DD HH:mm:ss') );
                }
            });

            $(this.$refs.picker_time_snapshot).datetimepicker({
                useCurrent: false,
                defaultDate: null,
                format: SpecialFuncs.timeFormat(),
            }).on("input", (e) => {
                let val = $(this.$refs.picker_time_snapshot).val();
                if (String(val).match(/am|pm/gi)) {
                    $(this.$refs.picker_time_snapshot).val( moment( val ).format('YYYY-MM-DD HH:mm:ss') );
                }
            });

            $(this.$refs.picker_date_month).datetimepicker({
                useCurrent: false,
                defaultDate: null,
                format: SpecialFuncs.dateFormat(),
            }).on("input", (e) => {
                let val = $(this.$refs.picker_date_month).val();
                if (String(val).match(/am|pm/gi)) {
                    $(this.$refs.picker_date_month).val( moment( val ).format('YYYY-MM-DD HH:mm:ss') );
                }
            });

            this.$nextTick(() => {
                this.date_month_visible = this.alert_sett.snapshot_frequency === 'monthly' && this.alert_sett.snapshot_month_freq === 'date';
                this.setDatePickers();
            });
        },
        beforeDestroy() {
        }
    }
</script>

<style lang="scss" scoped>
    @import "./../SettingsModule/TabSettingsPermissions";

    .spaced-table {
        width: 100%;

        label {
            margin: 0;
        }

        .th_style {
            color: #555;
            display: block;
            padding: 5px;
            background: linear-gradient(rgb(255, 255, 255), rgb(209, 209, 209) 50%, rgb(199, 199, 199) 50%, rgb(230, 230, 230));
            border: 1px solid #CCC;
        }

        .pad-bot {
            padding-bottom: 15px;
        }
        .pad-top {
            padding-top: 15px;
        }
        .margin-r {
            margin-right: 15px;
        }
        .white-bold {
            color: white;
            font-weight: bold;
        }
    }
</style>