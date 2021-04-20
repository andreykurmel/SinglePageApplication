<template>
    <div class="full-height">
        <table class="spaced-table" style="table-layout: fixed">
            <colgroup>
                <col :width="100">
                <col :width="300">
            </colgroup>
            <tbody>

            <tr>
                <td colspan="2" class="pad-bot"></td>
            </tr>

            <tr>
                <td colspan="2" class="pad-bot">
                    <label>Trigger: check one or multiple items:</label>
                </td>
            </tr>

            <tr>
                <td class="pad-bot">
                    <span class="indeterm_check__wrap">
                        <span class="indeterm_check checkbox-input"
                              @click="updateCheckBox('on_added')"
                              :style="$root.checkBoxStyle"
                        >
                            <i v-if="alert_sett.on_added" class="glyphicon glyphicon-ok group__icon"></i>
                        </span>
                    </span>
                    <label>When:</label>
                </td>
                <td class="pad-bot">
                    <label class="th_style">New Record Added</label>
                </td>
            </tr>

            <tr>
                <td class="pad-bot">
                    <span class="indeterm_check__wrap">
                        <span class="indeterm_check checkbox-input"
                              @click="updateCheckBox('on_deleted')"
                              :style="$root.checkBoxStyle"
                        >
                            <i v-if="alert_sett.on_deleted" class="glyphicon glyphicon-ok group__icon"></i>
                        </span>
                    </span>
                    <label>When:</label>
                </td>
                <td class="pad-bot">
                    <label class="th_style">Record Deleted</label>
                </td>
            </tr>

            <tr>
                <td class="pad-bot">
                    <span class="indeterm_check__wrap">
                        <span class="indeterm_check checkbox-input"
                              @click="updateCheckBox('on_updated')"
                              :style="$root.checkBoxStyle"
                        >
                            <i v-if="alert_sett.on_updated" class="glyphicon glyphicon-ok group__icon"></i>
                        </span>
                    </span>
                    <label>When:</label>
                </td>
                <td class="pad-bot">
                    <label class="th_style">Existing Record Updated</label>
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
                            :cell-height="$root.cellHeight"
                            :max-cell-rows="$root.maxCellRows"
                            :is-full-width="true"
                            :user="$root.user"
                            :behavior="'alert_notif'"
                            :adding-row="addingRow"
                            :available-columns="availAleConds"
                            :use_theme="true"
                            @added-row="addAlertCond"
                            @updated-row="updateAlertCond"
                            @delete-row="deleteAlertCond"
                    ></custom-table>
                </td>
            </tr>

            <tr>
                <td colspan="2" class="pad-bot">
                    <label>Automation:</label>
                </td>
            </tr>

            </tbody>
        </table>
    </div>
</template>

<script>
    import CustomTable from "../../../../CustomTable/CustomTable";

    export default {
        name: "AlertTriggers",
        components: {
            CustomTable,
        },
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
        },
        computed: {
        },
        watch: {
        },
        methods: {
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