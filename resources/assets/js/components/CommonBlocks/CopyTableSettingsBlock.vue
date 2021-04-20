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
        props:{
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