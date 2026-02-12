<template>
    <div class="full-height permissions-tab" :style="textSysStyle">
        <div class="permissions-menu-header">
            <button class="btn btn-default btn-sm" :style="textSysStyle" :class="{active : activeTab === 'UFV'}" @click="changeTab('UFV')">
                Updating Field Value (UFV)
            </button>
            <button class="btn btn-default btn-sm" :style="textSysStyle" :class="{active : activeTab === 'ANR'}" @click="changeTab('ANR')">
                Adding New Records (ANR)
            </button>
            <button class="btn btn-default btn-sm" :style="textSysStyle" :class="{active : activeTab === 'SEMLS'}" @click="changeTab('SEMLS')">
                Sending Emails
            </button>
            <button class="btn btn-default btn-sm" :style="textSysStyle" :class="{active : activeTab === 'SNPHT'}" @click="changeTab('SNPHT')">
                Snapshot
            </button>

            <div v-if="activeTab" class="flex flex--center-v flex--end" style="position: absolute; top: 0; right: 0; height: 32px;" :style="textSysStyleSmart">
                <span>Enabled:&nbsp;</span>
                <label class="switch_t">
                    <input type="checkbox" :checked="sliderKey()" :disabled="!can_edit" @click="sliderChange()">
                    <span class="toggler round" :class="{'disabled': !can_edit}"></span>
                </label>
            </div>
        </div>
        <div class="permissions-menu-body" style="border: 1px solid #CCC;">

            <alert-automation-ufv
                    v-show="alert_sett.enabled_ufv && activeTab === 'UFV'"
                    :table-meta="tableMeta"
                    :alert_sett="alert_sett"
                    :can_edit="can_edit"
                    style="padding: 5px;"
                    @update-alert="sendUpdate"
            ></alert-automation-ufv>

            <alert-automation-anr
                    v-show="alert_sett.enabled_anr && activeTab === 'ANR'"
                    :table-meta="tableMeta"
                    :alert_sett="alert_sett"
                    :can_edit="can_edit"
                    style="padding: 5px;"
                    @update-alert="sendUpdate"
            ></alert-automation-anr>

            <alert-automation-sending-emails
                    v-show="alert_sett.enabled_sending && activeTab === 'SEMLS'"
                    :table-meta="tableMeta"
                    :alert_sett="alert_sett"
                    :can_edit="can_edit"
                    style="padding: 5px;"
                    @update-alert="sendUpdate"
            ></alert-automation-sending-emails>

            <alert-automation-snapshot
                    v-show="alert_sett.enabled_snapshot && activeTab === 'SNPHT'"
                    :table-meta="tableMeta"
                    :alert_sett="alert_sett"
                    :can_edit="can_edit"
                    style="padding: 5px;"
                    @update-alert="sendUpdate"
            ></alert-automation-snapshot>

        </div>
    </div>
</template>

<script>
    import CellStyleMixin from "../../../../_Mixins/CellStyleMixin";

    import AlertAutomationAnr from "./AlertAutomationAnr";
    import AlertAutomationUfv from "./AlertAutomationUfv";
    import AlertAutomationSendingEmails from "./AlertAutomationSendingEmails";
    import AlertAutomationSnapshot from "./AlertAutomationSnapshot.vue";

    export default {
        name: "AlertAutomations",
        components: {
            AlertAutomationSnapshot,
            AlertAutomationSendingEmails,
            AlertAutomationUfv,
            AlertAutomationAnr,
        },
        mixins: [
            CellStyleMixin,
        ],
        data: function () {
            return {
                activeTab: 'UFV',
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
        },
        methods: {
            sliderKey() {
                let key = '';
                switch (this.activeTab) {
                    case 'UFV': key = 'enabled_ufv'; break;
                    case 'ANR': key = 'enabled_anr'; break;
                    case 'SEMLS': key = 'enabled_sending'; break;
                    case 'SNPHT': key = 'enabled_snapshot'; break;
                }
                return this.alert_sett[key];
            },
            sliderChange() {
                switch (this.activeTab) {
                    case 'UFV': this.alert_sett.enabled_ufv = this.alert_sett.enabled_ufv ? 0 : 1; break;
                    case 'ANR': this.alert_sett.enabled_anr = this.alert_sett.enabled_anr ? 0 : 1; break;
                    case 'SEMLS': this.alert_sett.enabled_sending = this.alert_sett.enabled_sending ? 0 : 1; break;
                    case 'SNPHT': this.alert_sett.enabled_snapshot = this.alert_sett.enabled_snapshot ? 0 : 1; break;
                }
                this.sendUpdate();
            },
            changeTab(key) {
                this.activeTab = key;
                this.$emit('set-sub-tab', key);
            },
            updateCheckBox(param) {
                this.alert_sett[param] = this.alert_sett[param] ? 0 : 1;
                this.sendUpdate();
            },
            sendUpdate() {
                this.$emit('update-alert', this.alert_sett);
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
    .permissions-tab {
        position: relative;
    }

    .permissions-menu-body {
        label {
            margin: 0;
        }
    }
</style>