<template>
    <div class="copy-to-others-settings copy-to-others-lvl">
        <div>
            <label>
                <span class="indeterm_check__wrap">
                    <span class="indeterm_check" @click="sendSettings('data')">
                        <i v-if="settings.data" class="glyphicon glyphicon-ok group__icon"></i>
                    </span>
                </span>
                <span>Data</span>
            </label>
        </div>
        <div class="copy-to-others-lvl">
            <label>
                <span class="indeterm_check__wrap">
                    <span class="indeterm_check" @click="sendSettings('data_attach')">
                        <i v-if="settings.data_attach" class="glyphicon glyphicon-ok group__icon"></i>
                    </span>
                </span>
                <span>Attachments</span>
            </label>
        </div>
        <div>
            <label>
                <span class="indeterm_check__wrap">
                    <span class="indeterm_check" @click="sendSettings('basics')">
                        <i v-if="settings.basics" class="glyphicon glyphicon-ok group__icon"></i>
                    </span>
                </span>
                <span>Basics</span>
            </label>
        </div>
        <div>
            <label>
                <span class="indeterm_check__wrap">
                    <span class="indeterm_check" @click="toOthersSetGroups();sendSettings()">
                        <i v-if="toOthersGroups" class="glyphicon glyphicon-ok group__icon"></i>
                    </span>
                </span>
                <span>Grouping</span>
            </label>
        </div>
        <div class="copy-to-others-lvl">
            <label>
                <span class="indeterm_check__wrap">
                    <span class="indeterm_check" @click="sendSettings('grouping_rows')">
                        <i v-if="settings.grouping_rows" class="glyphicon glyphicon-ok group__icon"></i>
                    </span>
                </span>
                <span>Rows</span>
            </label>
            <label>
                <span class="indeterm_check__wrap">
                    <span class="indeterm_check" @click="sendSettings('grouping_columns')">
                        <i v-if="settings.grouping_columns" class="glyphicon glyphicon-ok group__icon"></i>
                    </span>
                </span>
                <span>Columns</span>
            </label>
            <label>
                <span class="indeterm_check__wrap">
                    <span class="indeterm_check" @click="sendSettings('grouping_rcs')">
                        <i v-if="settings.grouping_rcs" class="glyphicon glyphicon-ok group__icon"></i>
                    </span>
                </span>
                <span>RCs</span>
            </label>
        </div>
        <div>
            <label>
                <span class="indeterm_check__wrap">
                    <span class="indeterm_check" @click="sendSettings('links')">
                        <i v-if="settings.links" class="glyphicon glyphicon-ok group__icon"></i>
                    </span>
                </span>
                <span>Links</span>
            </label>
        </div>
        <div>
            <label>
                <span class="indeterm_check__wrap">
                    <span class="indeterm_check" @click="sendSettings('ddls')">
                        <i v-if="settings.ddls" class="glyphicon glyphicon-ok group__icon"></i>
                    </span>
                </span>
                <span>DDLs</span>
            </label>
        </div>
        <div>
            <label>
                <span class="indeterm_check__wrap">
                    <span class="indeterm_check" @click="sendSettings('cond_formats')">
                        <i v-if="settings.cond_formats" class="glyphicon glyphicon-ok group__icon"></i>
                    </span>
                </span>
                <span>Conditional Formattings (CFs)</span>
            </label>
        </div>
        <div v-if="$root.tableMeta && $root.AddonAvailableToUser($root.tableMeta, 'bi') && $root.tableMeta.add_bi">
            <label>
                <span class="indeterm_check__wrap">
                    <span class="indeterm_check" @click="sendSettings('addon_bi')">
                        <i v-if="settings.addon_bi" class="glyphicon glyphicon-ok group__icon"></i>
                    </span>
                </span>
                <span>Addon: BI</span>
            </label>
        </div>
        <div v-if="$root.tableMeta && $root.AddonAvailableToUser($root.tableMeta, 'map') && $root.tableMeta.add_map">
            <label>
                <span class="indeterm_check__wrap">
                    <span class="indeterm_check" @click="sendSettings('addon_map')">
                        <i v-if="settings.addon_map" class="glyphicon glyphicon-ok group__icon"></i>
                    </span>
                </span>
                <span>Addon: GSI</span>
            </label>
        </div>
        <div v-if="$root.tableMeta && $root.AddonAvailableToUser($root.tableMeta, 'request') && $root.tableMeta.add_request">
            <label>
                <span class="indeterm_check__wrap">
                    <span class="indeterm_check" @click="sendSettings('addon_request')">
                        <i v-if="settings.addon_request" class="glyphicon glyphicon-ok group__icon"></i>
                    </span>
                </span>
                <span>Addon: DCR</span>
            </label>
        </div>
        <div v-if="$root.tableMeta && $root.AddonAvailableToUser($root.tableMeta, 'alert') && $root.tableMeta.add_alert">
            <label>
                <span class="indeterm_check__wrap">
                    <span class="indeterm_check" @click="sendSettings('addon_alert')">
                        <i v-if="settings.addon_alert" class="glyphicon glyphicon-ok group__icon"></i>
                    </span>
                </span>
                <span>Addon: ANA</span>
            </label>
        </div>
        <div v-if="$root.tableMeta && $root.AddonAvailableToUser($root.tableMeta, 'kanban') && $root.tableMeta.add_kanban">
            <label>
                <span class="indeterm_check__wrap">
                    <span class="indeterm_check" @click="sendSettings('addon_kanban')">
                        <i v-if="settings.addon_kanban" class="glyphicon glyphicon-ok group__icon"></i>
                    </span>
                </span>
                <span>Addon: Kanban</span>
            </label>
        </div>
        <div v-if="$root.tableMeta && $root.AddonAvailableToUser($root.tableMeta, 'gantt') && $root.tableMeta.add_gantt">
            <label>
                <span class="indeterm_check__wrap">
                    <span class="indeterm_check" @click="sendSettings('addon_gantt')">
                        <i v-if="settings.addon_gantt" class="glyphicon glyphicon-ok group__icon"></i>
                    </span>
                </span>
                <span>Addon: Gantt</span>
            </label>
        </div>
        <div v-if="$root.tableMeta && $root.AddonAvailableToUser($root.tableMeta, 'email') && $root.tableMeta.add_email">
            <label>
                <span class="indeterm_check__wrap">
                    <span class="indeterm_check" @click="sendSettings('addon_email')">
                        <i v-if="settings.addon_email" class="glyphicon glyphicon-ok group__icon"></i>
                    </span>
                </span>
                <span>Addon: Email</span>
            </label>
        </div>
        <div v-if="$root.tableMeta && $root.AddonAvailableToUser($root.tableMeta, 'calendar') && $root.tableMeta.add_calendar">
            <label>
                <span class="indeterm_check__wrap">
                    <span class="indeterm_check" @click="sendSettings('addon_calendar')">
                        <i v-if="settings.addon_calendar" class="glyphicon glyphicon-ok group__icon"></i>
                    </span>
                </span>
                <span>Addon: Calendar</span>
            </label>
        </div>
        <div v-if="$root.tableMeta && $root.AddonAvailableToUser($root.tableMeta, 'twilio') && $root.tableMeta.add_twilio">
            <label>
                <span class="indeterm_check__wrap">
                    <span class="indeterm_check" @click="sendSettings('addon_twilio')">
                        <i v-if="settings.addon_twilio" class="glyphicon glyphicon-ok group__icon"></i>
                    </span>
                </span>
                <span>Addon: SMS</span>
            </label>
        </div>
        <div v-if="$root.tableMeta && $root.AddonAvailableToUser($root.tableMeta, 'tournament') && $root.tableMeta.add_tournament">
            <label>
                <span class="indeterm_check__wrap">
                    <span class="indeterm_check" @click="sendSettings('addon_tournament')">
                        <i v-if="settings.addon_tournament" class="glyphicon glyphicon-ok group__icon"></i>
                    </span>
                </span>
                <span>Addon: Brackets</span>
            </label>
        </div>
        <div v-if="$root.tableMeta && $root.AddonAvailableToUser($root.tableMeta, 'grouping') && $root.tableMeta.add_grouping">
            <label>
                <span class="indeterm_check__wrap">
                    <span class="indeterm_check" @click="sendSettings('addon_grouping')">
                        <i v-if="settings.addon_grouping" class="glyphicon glyphicon-ok group__icon"></i>
                    </span>
                </span>
                <span>Addon: Grouping</span>
            </label>
        </div>
        <div v-if="$root.tableMeta && $root.AddonAvailableToUser($root.tableMeta, 'report') && $root.tableMeta.add_report">
            <label>
                <span class="indeterm_check__wrap">
                    <span class="indeterm_check" @click="sendSettings('addon_report')">
                        <i v-if="settings.addon_report" class="glyphicon glyphicon-ok group__icon"></i>
                    </span>
                </span>
                <span>Addon: Report</span>
            </label>
        </div>
        <div v-if="$root.tableMeta && $root.AddonAvailableToUser($root.tableMeta, 'ai') && $root.tableMeta.add_ai">
            <label>
                <span class="indeterm_check__wrap">
                    <span class="indeterm_check" @click="sendSettings('addon_ai')">
                        <i v-if="settings.addon_ai" class="glyphicon glyphicon-ok group__icon"></i>
                    </span>
                </span>
                <span>Addon: AI</span>
            </label>
        </div>
        <div v-if="$root.tableMeta && $root.AddonAvailableToUser($root.tableMeta, 'simplemap') && $root.tableMeta.add_simplemap">
            <label>
                <span class="indeterm_check__wrap">
                    <span class="indeterm_check" @click="sendSettings('addon_simplemap')">
                        <i v-if="settings.addon_simplemap" class="glyphicon glyphicon-ok group__icon"></i>
                    </span>
                </span>
                <span>Addon: Map</span>
            </label>
        </div>
    </div>
</template>

<script>
    export default {
        name: "CopyTableSettingsBlock",
        data: function () {
            return {
                settings: {},
            };
        },
        props: {
            selectedSettings: Object,
        },
        computed: {
            toOthersGroups() {
                return this.settings.grouping_rows
                    && this.settings.grouping_columns
                    && this.settings.grouping_rcs;
            }
        },
        methods: {
            setEmptySettings() {
                return {
                    data: true,
                    data_attach: true,
                    basics: true,
                    grouping_rows: true,
                    grouping_columns: true,
                    grouping_rcs: true,
                    links: true,
                    ddls: true,
                    cond_formats: true,
                    addon_bi: true,
                    addon_map: true,
                    addon_request: true,
                    addon_alert: true,
                    addon_kanban: true,
                    addon_gantt: true,
                    addon_email: true,
                    addon_calendar: true,
                    addon_twilio: true,
                    addon_tournament: true,
                    addon_simplemap: true,
                    addon_grouping: true,
                    addon_report: true,
                    addon_ai: true,
                }
            },
            toOthersSetGroups() {
                let status = this.toOthersGroups;
                this.settings.grouping_rows = !status;
                this.settings.grouping_columns = !status;
                this.settings.grouping_rcs = !status;
            },
            sendSettings(key) {
                if (key) {
                    this.settings[key] = !this.settings[key];
                }
                this.$emit('send-settings', this.settings);
            }
        },
        mounted() {
            //if provided settings
            if (this.selectedSettings) {
                this.settings = this.selectedSettings;
                //if provided empty object
                if (this.settings.data === undefined) {
                    this.settings = this.setEmptySettings();
                }
            } else {
                this.settings = this.setEmptySettings();
                this.sendSettings();
            }
        }
    }
</script>

<style lang="scss" scoped>
    .copy-to-others-lvl {
        margin-left: 30px;
    }
    .copy-to-others-settings {
        margin-top: 15px;

        label {
            margin-right: 10px;
        }
    }
</style>