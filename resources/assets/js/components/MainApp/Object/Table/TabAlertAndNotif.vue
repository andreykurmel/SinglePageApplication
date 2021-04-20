<template>
    <div class="container-fluid full-height">
        <div v-if="!$root.AddonAvailableToUser(tableMeta, 'alert')" class="row full-frame flex flex--center">
            <label>Addon is unavailable!</label>
        </div>
        <div v-else="" class="row full-height permissions-tab">
            <!--LEFT SIDE-->
            <div class="col-xs-4 full-height" style="padding-right: 0;">
                <div class="top-text">
                    <span>Alerts, Notifications & Automation (ANA)</span>
                </div>
                <div class="permissions-panel no-padding">
                    <custom-table
                            :cell_component_name="'custom-cell-alert-notif'"
                            :global-meta="tableMeta"
                            :table-meta="settingsMeta['table_alerts']"
                            :all-rows="tableMeta._alerts"
                            :rows-count="tableMeta._alerts.length"
                            :cell-height="$root.cellHeight"
                            :max-cell-rows="$root.maxCellRows"
                            :is-full-width="true"
                            :user="user"
                            :behavior="'alert_notif'"
                            :adding-row="addingRow"
                            :available-columns="availAlerts"
                            :selected-row="selectedAlert"
                            :use_theme="true"
                            @added-row="addAlert"
                            @updated-row="updateAlert"
                            @delete-row="deleteAlert"
                            @row-index-clicked="rowIndexClicked"
                    ></custom-table>
                </div>
            </div>
            <!--RIGHT SIDE-->
            <div class="col-xs-8 full-height">
                <div class="top-text">
                    <span>{{ (selectedAlert > -1 ? tableMeta._alerts[selectedAlert].name + ':' : 'Select an A&amp;N ...') }}</span>

                    <info-sign-link
                            :app_sett_key="'help_link_alerts_popup'"
                            :hgt="30"
                            class="right-elem"
                    ></info-sign-link>
                </div>
                <div class="permissions-panel">


                    <div class="permissions-menu-header">
                        <button class="btn btn-default btn-sm" :class="{active : activeRightTab === 'triggers'}" @click="activeRightTab = 'triggers'">
                            Triggers & Automation
                        </button>
                        <button class="btn btn-default btn-sm" :class="{active : activeRightTab === 'notifs'}" @click="activeRightTab = 'notifs'">
                            Notifications
                        </button>
                    </div>
                    <div class="permissions-menu-body" style="border: 1px solid #CCC;">

                        <div class="full-height permissions-panel" v-if="sel_alert && activeRightTab === 'triggers'">
                            <alert-triggers
                                    :table-meta="tableMeta"
                                    :alert_sett="sel_alert"
                                    @update-alert="updateAlert"
                            ></alert-triggers>
                        </div>

                        <div class="full-height permissions-panel" v-if="sel_alert && activeRightTab === 'notifs'">
                            <alert-notifs
                                    :table_id="table_id"
                                    :table-meta="tableMeta"
                                    :alert_sett="sel_alert"
                                    @update-alert="updateAlert"
                            ></alert-notifs>
                        </div>

                    </div>

                </div>
            </div>
        </div>
    </div>
</template>

<script>
    import CustomTable from '../../../CustomTable/CustomTable';
    import InfoSignLink from "../../../CustomTable/Specials/InfoSignLink";
    import AlertTriggers from "./AlertAddon/AlertTriggers";
    import AlertNotifs from "./AlertAddon/AlertNotifs";

    export default {
        name: "TabAlertAndNotif",
        components: {
            AlertNotifs,
            AlertTriggers,
            CustomTable,
            InfoSignLink,
        },
        data: function () {
            return {
                activeRightTab: 'triggers',
                addingRow: {
                    active: true,
                    position: 'bottom'
                },
                selectedAlert: -1,
                availAlerts: [
                    'name',
                    'is_active',
                ],
            }
        },
        props:{
            table_id: Number,
            tableMeta: Object,
            settingsMeta: Object,
            user:  Object,
        },
        computed: {
            sel_alert() {
                return this.tableMeta._alerts[this.selectedAlert];
            },
            sel_alert_field() {
                return this.sel_alert ? _.find(this.tableMeta._fields, {id: Number(this.sel_alert.table_field_id)}) : null;
            },
        },
        watch: {
            table_id(val) {
                this.selectedAlert = -1;
            }
        },
        methods: {
            inArray(item, arr) {
                return $.inArray(item, arr) > -1;
            },
            addAlert(tableRow) {
                let fields = _.cloneDeep(tableRow);//copy object
                this.$root.deleteSystemFields(fields);

                $.LoadingOverlay('show');
                axios.post('/ajax/table/alert', {
                    table_id: this.tableMeta.id,
                    fields: fields
                }).then(({ data }) => {
                    this.tableMeta._alerts.push(data);
                }).catch(errors => {
                    Swal('', getErrors(errors));
                }).finally(() => {
                    $.LoadingOverlay('hide');
                });
            },
            updateAlert(tableRow) {
                let alert_id = tableRow.id;
                let fields = _.cloneDeep(tableRow);//copy object
                this.$root.deleteSystemFields(fields);

                $.LoadingOverlay('show');
                axios.put('/ajax/table/alert', {
                    table_alert_id: alert_id,
                    fields: fields
                }).then(({ data }) => {
                    tableRow.recipients = data.recipients;
                }).catch(errors => {
                    Swal('', getErrors(errors));
                }).finally(() => {
                    $.LoadingOverlay('hide');
                });
            },
            deleteAlert(tableRow) {
                $.LoadingOverlay('show');
                axios.delete('/ajax/table/alert', {
                    params: {
                        table_alert_id: tableRow.id
                    }
                }).then(({ data }) => {
                    let idx = _.findIndex(this.tableMeta._alerts, {id: tableRow.id});
                    if (idx > -1) {
                        this.tableMeta._alerts.splice(idx, 1);
                        this.selectedAlert = -1;
                    }
                }).catch(errors => {
                    Swal('', getErrors(errors));
                }).finally(() => {
                    $.LoadingOverlay('hide');
                });
            },
            rowIndexClicked(index) {
                this.selectedAlert = index;
            },
        },
        mounted() {
        },
        beforeDestroy() {
        }
    }
</script>

<style lang="scss" scoped>
    @import "SettingsModule/TabSettingsPermissions";
</style>