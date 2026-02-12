<template>
    <div class="full-height" :style="sysStyleWiBg">
        <div v-if="!$root.AddonAvailableToUser(tableMeta, 'alert')" class="full-frame flex flex--center">
            <label>Addon is unavailable!</label>
        </div>
        <div v-else="" class="full-height permissions-tab">
            <div class="permissions-panel full-height" :style="$root.themeMainBgStyle">

                <div class="permissions-menu-header">
                    <button class="btn btn-default btn-sm" :style="textSysStyle" :class="{active : activeRightTab === 'list'}" @click="activeRightTab = 'list'">
                        List
                    </button>
                    <button class="btn btn-default btn-sm" :style="textSysStyle" :class="{active : activeRightTab === 'triggers'}" @click="activeRightTab = 'triggers'">
                        Triggers
                    </button>
                    <button class="btn btn-default btn-sm" :style="textSysStyle" :class="{active : activeRightTab === 'notifs'}" @click="activeRightTab = 'notifs'">
                        Notifications
                    </button>
                    <button class="btn btn-default btn-sm" :style="textSysStyle" :class="{active : activeRightTab === 'automations'}" @click="activeRightTab = 'automations'">
                        Automations
                    </button>

                    <div v-if="sel_alert" class="flex flex--center-v flex--end" style="position: absolute; top: 0; right: 0; height: 32px; width: 50%;">

                        <label class="flex flex--center ml15 mr0" style="margin: 0 0 0 5px;white-space: nowrap;width: 300px;" :style="textSysStyleSmart">
                            Loaded ANA:&nbsp;
                            <select-block
                                :options="anaOpts()"
                                :sel_value="sel_alert.id"
                                :style="{ maxWidth:'200px', height:'32px', }"
                                @option-select="anaChange"
                            ></select-block>
                        </label>

                        <info-sign-link v-show="activeRightTab === 'list'" class="ml5 mr5 ilnk" :app_sett_key="'help_link_alerts_popup'" :hgt="30" :txt="'for Alerts/List'"></info-sign-link>
                        <info-sign-link v-show="activeRightTab === 'triggers'" class="ml5 mr5 ilnk" :app_sett_key="'help_link_alerts_popup_triggers'" :hgt="30" :txt="'for Alerts/Triggers'"></info-sign-link>
                        <info-sign-link v-show="activeRightTab === 'notifs' && tbNot === 'mails'" class="ml5 mr5 ilnk" :app_sett_key="'help_link_alerts_popup_notifs_mail'" :hgt="30" :txt="'for Alerts/Notifications/Emails'"></info-sign-link>
                        <info-sign-link v-show="activeRightTab === 'notifs' && tbNot === 'sms'" class="ml5 mr5 ilnk" :app_sett_key="'help_link_alerts_popup_notifs_sms'" :hgt="30" :txt="'for Alerts/Notifications/SMS'"></info-sign-link>
                        <info-sign-link v-show="activeRightTab === 'notifs' && tbNot === 'click_update'" class="ml5 mr5 ilnk" :app_sett_key="'help_link_alerts_popup_notifs_upd'" :hgt="30" :txt="'for Alerts/Notifications/Update'"></info-sign-link>
                        <info-sign-link v-show="activeRightTab === 'automations' && tbAuto === 'UFV'" class="ml5 mr5 ilnk" :app_sett_key="'help_link_alerts_popup_automations_ufv'" :hgt="30" :txt="'for Alerts/Automations/UFV'"></info-sign-link>
                        <info-sign-link v-show="activeRightTab === 'automations' && tbAuto === 'ANR'" class="ml5 mr5 ilnk" :app_sett_key="'help_link_alerts_popup_automations_anr'" :hgt="30":txt="'for Alerts/Automations/ANR'"></info-sign-link>
                        <info-sign-link v-show="activeRightTab === 'automations' && tbAuto === 'SEMLS'" class="ml5 mr5 ilnk" :app_sett_key="'help_link_alerts_popup_automations_semls'" :hgt="30":txt="'for Alerts/Automations/Emails'"></info-sign-link>
                        <info-sign-link v-show="activeRightTab === 'automations' && tbAuto === 'SNPHT'" class="ml5 mr5 ilnk" :app_sett_key="'help_link_alerts_popup_automations_snap'" :hgt="30":txt="'for Alerts/Automations/Snapshots'"></info-sign-link>
                    </div>
                </div>
                <div class="permissions-menu-body" style="border: 1px solid #CCC;">

                    <div class="full-height permissions-panel no-padding" v-show="activeRightTab === 'list'" :style="$root.themeMainBgStyle">
                        <custom-table
                            :cell_component_name="'custom-cell-alert-notif'"
                            :global-meta="tableMeta"
                            :table-meta="$root.settingsMeta['table_alerts']"
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
                    </div>

                    <div class="full-height permissions-panel" v-if="sel_alert && activeRightTab === 'triggers'" :style="$root.themeMainBgStyle">
                        <alert-triggers
                            :table-meta="tableMeta"
                            :alert_sett="sel_alert"
                            :can_edit="canEditAlert"
                            @update-alert="updateAlert"
                        ></alert-triggers>
                    </div>

                    <div class="full-height permissions-panel" v-if="sel_alert && activeRightTab === 'notifs'" :style="$root.themeMainBgStyle">
                        <alert-notifs
                            :table_id="table_id"
                            :table-meta="tableMeta"
                            :alert_sett="sel_alert"
                            :can_edit="canEditAlert"
                            @update-alert="updateAlert"
                            @set-sub-tab="(key) => { tbNot = key; }"
                        ></alert-notifs>
                    </div>

                    <div class="full-height permissions-panel" v-if="sel_alert && activeRightTab === 'automations'" :style="$root.themeMainBgStyle">
                        <alert-automations
                            :table-meta="tableMeta"
                            :alert_sett="sel_alert"
                            :can_edit="canEditAlert"
                            @update-alert="updateAlert"
                            @set-sub-tab="(key) => { tbAuto = key; }"
                        ></alert-automations>
                    </div>

                </div>

            </div>
        </div>
    </div>
</template>

<script>
    import CellStyleMixin from "../../../_Mixins/CellStyleMixin";

    import CustomTable from '../../../CustomTable/CustomTable';
    import InfoSignLink from "../../../CustomTable/Specials/InfoSignLink";
    import AlertTriggers from "./AlertAddon/AlertTriggers";
    import AlertNotifs from "./AlertAddon/AlertNotifs";
    import AlertAutomations from "./AlertAddon/AlertAutomations";
    import SelectBlock from "../../../CommonBlocks/SelectBlock";

    export default {
        name: "TabAlertAndNotif",
        components: {
            SelectBlock,
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
                tbNot: 'mails',
                tbAuto: 'UFV',
                activeRightTab: 'list',
                selectedAlert: 0,
                availAlerts: [
                    'name',
                    'description',
                    'execution_delay',
                    'is_active',
                ],
            }
        },
        props:{
            table_id: Number,
            tableMeta: Object,
            user: Object,
        },
        computed: {
            sel_alert() {
                return this.tableMeta._alerts[this.selectedAlert];
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
            sysStyleWiBg() {
                return {
                    ...this.textSysStyle,
                    ...this.$root.themeMainBgStyle,
                };
            },
        },
        watch: {
            table_id(val) {
                this.selectedAlert = 0;
            }
        },
        methods: {
            anaOpts() {
                return _.map(this.tableMeta._alerts, (dcr) => {
                    return { val:dcr.id, show:dcr.name };
                });
            },
            anaChange(opt) {
                this.selectedAlert = _.findIndex(this.tableMeta._alerts, {id: Number(opt.val)});
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
                    Swal('Info', getErrors(errors));
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
                    Swal('Info', getErrors(errors));
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
                    Swal('Info', getErrors(errors));
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
    .permissions-tab {
        padding: 5px;
    }
</style>