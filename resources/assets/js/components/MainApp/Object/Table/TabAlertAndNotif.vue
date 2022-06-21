<template>
    <div class="container-fluid full-height">
        <div v-if="!$root.AddonAvailableToUser(tableMeta, 'alert')" class="row full-frame flex flex--center">
            <label>Addon is unavailable!</label>
        </div>
        <div v-else="" class="row full-height permissions-tab">
            <!--LEFT SIDE-->
            <div class="col-xs-4 full-height" style="padding-right: 0;">
                <div class="top-text" :style="textSysStyle">
                    <span>Alerts, Notifications & Automation (ANA)</span>
                </div>
                <div class="permissions-panel no-padding">
                    <custom-table
                            :cell_component_name="'custom-cell-alert-notif'"
                            :global-meta="tableMeta"
                            :table-meta="settingsMeta['table_alerts']"
                            :all-rows="tableMeta._alerts"
                            :rows-count="tableMeta._alerts.length"
                            :cell-height="1"
                            :max-cell-rows="0"
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
                    <div v-if="sel_alert && alertDescrFld" class="description_ontop">
                        <div><label :style="textSysStyle">Description:</label></div>
                        <div style="height: 100px; border: 1px solid #ccc;">
                            <single-td-field
                                    :table-meta="settingsMeta['table_alerts']"
                                    :table-header="alertDescrFld"
                                    :td-value="sel_alert[alertDescrFld.field]"
                                    :style="{width: '100%'}"
                                    :with_edit="!!isOwner"
                                    :force_edit="!!isOwner"
                                    :ext-row="sel_alert"
                                    class="full-height"
                                    @updated-td-val="(val) => { sel_alert[alertDescrFld.field] = val; updateAlert(sel_alert) }"
                            ></single-td-field>
                        </div>
                    </div>
                </div>
            </div>
            <!--RIGHT SIDE-->
            <div class="col-xs-8 full-height">
                <div class="top-text" :style="textSysStyle">
                    <span>{{ (sel_alert ? sel_alert.name + ':' : 'Select an ANA ...') }}</span>

                    <info-sign-link
                            :app_sett_key="'help_link_alerts_popup'"
                            :hgt="30"
                            class="right-elem"
                    ></info-sign-link>
                </div>
                <div class="permissions-panel">

                    <div class="permissions-menu-header">
                        <button class="btn btn-default btn-sm" :style="textSysStyle" :class="{active : activeRightTab === 'triggers'}" @click="activeRightTab = 'triggers'">
                            Triggers
                        </button>
                        <button class="btn btn-default btn-sm" :style="textSysStyle" :class="{active : activeRightTab === 'notifs'}" @click="activeRightTab = 'notifs'">
                            Notifications
                        </button>
                        <button class="btn btn-default btn-sm" :style="textSysStyle" :class="{active : activeRightTab === 'automations'}" @click="activeRightTab = 'automations'">
                            Automation
                        </button>
                    </div>
                    <div class="permissions-menu-body" style="border: 1px solid #CCC;">

                        <div class="full-height permissions-panel" v-if="sel_alert && activeRightTab === 'triggers'">
                            <alert-triggers
                                    :table-meta="tableMeta"
                                    :alert_sett="sel_alert"
                                    :can_edit="canEditAlert"
                                    @update-alert="updateAlert"
                            ></alert-triggers>
                        </div>

                        <div class="full-height permissions-panel" v-if="sel_alert && activeRightTab === 'notifs'">
                            <alert-notifs
                                    :table_id="table_id"
                                    :table-meta="tableMeta"
                                    :alert_sett="sel_alert"
                                    :can_edit="canEditAlert"
                                    @update-alert="updateAlert"
                            ></alert-notifs>
                        </div>

                        <div class="full-height permissions-panel" v-if="sel_alert && activeRightTab === 'automations'">
                            <alert-automations
                                    :table-meta="tableMeta"
                                    :alert_sett="sel_alert"
                                    :can_edit="canEditAlert"
                                    @update-alert="updateAlert"
                            ></alert-automations>
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
    import AlertAutomations from "./AlertAddon/AlertAutomations";
    import SingleTdField from "../../../CommonBlocks/SingleTdField";

    import CellStyleMixin from "../../../_Mixins/CellStyleMixin";

    export default {
        name: "TabAlertAndNotif",
        components: {
            SingleTdField,
            AlertAutomations,
            AlertNotifs,
            AlertTriggers,
            CustomTable,
            InfoSignLink,
        },
        mixins: [
            CellStyleMixin,
        ],
        data: function () {
            return {
                activeRightTab: 'triggers',
                selectedAlert: 0,
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
            user: Object,
        },
        computed: {
            sel_alert() {
                return this.tableMeta._alerts[this.selectedAlert];
            },
            sel_alert_field() {
                return this.sel_alert ? _.find(this.tableMeta._fields, {id: Number(this.sel_alert.table_field_id)}) : null;
            },
            alertDescrFld() {
                return _.find(this.settingsMeta['table_alerts']._fields, {field: 'description'});
            },
            isOwner() {
                return this.user.id === this.tableMeta.user_id;
            },
            addingRow() {
                return {
                    active: this.isOwner,
                    position: 'bottom'
                };
            },
            canEditAlert() {
                return this.sel_alert && (this.isOwner || this.sel_alert._can_edit);
            },
        },
        watch: {
            table_id(val) {
                this.selectedAlert = 0;
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
    @import "./SettingsModule/TabSettingsPermissions";

    .description_ontop {
        position: absolute;
        bottom: 0;
        width: 100%;
        padding: 3px;
        border-top: 2px solid #ccc;
    }
    .btn-default {
        height: 30px;
    }
</style>