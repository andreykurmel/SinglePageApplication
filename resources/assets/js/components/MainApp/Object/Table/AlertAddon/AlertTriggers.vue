<template>
    <div class="full-height">
        <table class="spaced-table" style="table-layout: fixed" :style="textSysStyle">
            <colgroup>
                <col style="width: 12%">
                <col style="width: 88%">
            </colgroup>
            <tbody>

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
                            :sel_value="alert_sett.on_added_ref_cond_id"
                            :style="{ maxWidth:'200px', height:'32px', }"
                            :with_links="true"
                            :is_disabled="!can_edit"
                            :button_txt="'Add New'"
                            @option-select="(opt) => { updateSelect('on_added_ref_cond_id', opt) }"
                            @link-click="showRGRP(alert_sett.on_added_ref_cond_id)"
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
                            :sel_value="alert_sett.on_deleted_ref_cond_id"
                            :style="{ maxWidth:'200px', height:'32px', }"
                            :with_links="true"
                            :is_disabled="!can_edit"
                            :button_txt="'Add New'"
                            @option-select="(opt) => { updateSelect('on_deleted_ref_cond_id', opt) }"
                            @link-click="showRGRP(alert_sett.on_deleted_ref_cond_id)"
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
                            :sel_value="alert_sett.on_updated_ref_cond_id"
                            :style="{ maxWidth:'200px', height:'32px', }"
                            :with_links="true"
                            :is_disabled="!can_edit"
                            :button_txt="'Add New'"
                            @option-select="(opt) => { updateSelect('on_updated_ref_cond_id', opt) }"
                            @link-click="showRGRP(alert_sett.on_updated_ref_cond_id)"
                            @button-click="showRGRP(null)"
                    ></select-block>
                </td>
            </tr>

            <tr>
                <td class="pad-bot"></td>
                <td class="pad-bot" style="border: 1px solid #CCC;height: 175px;">
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

            </tbody>
        </table>
    </div>
</template>

<script>
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
            can_edit: Boolean,
        },
        computed: {
        },
        watch: {
        },
        methods: {
            updateSelect(key, option) {
                this.alert_sett[key] = option.val;
                this.sendUpdate();
            },
            updateCheckBox(param) {
                this.alert_sett[param] = this.alert_sett[param] ? 0 : 1;
                this.sendUpdate();
            },
            sendUpdate() {
                this.$emit('update-alert', this.alert_sett);
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
                    Swal('', getErrors(errors));
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
                    Swal('', getErrors(errors));
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
                    Swal('', getErrors(errors));
                }).finally(() => {
                    $.LoadingOverlay('hide');
                });
            },
            getRGrps() {
                let only_current = _.filter(this.tableMeta._row_groups, (rg) => {
                    let rc = _.find(this.tableMeta._ref_conditions, {id: Number(rg.row_ref_condition_id)});
                    return rc && rc.table_id == rc.ref_table_id;
                });
                let rrows = _.map(only_current, (rc) => {
                    return { val:rc.row_ref_condition_id, show:rc.name };
                });
                rrows.unshift({ val:null, show:"" });
                return rrows;
            },
            showRGRP(id) {
                eventBus.$emit('show-grouping-settings-popup', this.tableMeta.db_name, 'row', id);
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

    .spaced-table {
        width: 100%;

        label {
            margin: 0;
        }

        .th_style {
            display: block;
            padding: 5px;
            background: linear-gradient(rgb(255, 255, 255), rgb(209, 209, 209) 50%, rgb(199, 199, 199) 50%, rgb(230, 230, 230));
            border: 1px solid #CCC;
        }

        .pad-bot {
            padding-bottom: 15px;
        }
    }
</style>