<template>
    <div v-if="tableMeta && settingsMeta" class="tab-settings full-height">
        <div class="menu-header">
            <button class="btn btn-default btn-sm pull-right"
                    :class="{active : settingsTab.key === 'basics'}"
                    :style="textSysStyle"
                    @click="settingsTab.key = 'basics'"
            >Basics</button>
            <button class="btn btn-default btn-sm pull-right"
                    :class="{active : settingsTab.key === 'ref_conds'}"
                    :style="textSysStyle"
                    :disabled="!$root.checkAvailable(user, 'group_refs')"
                    v-if="tableMeta._is_owner"
                    @click="settingsTab.key = 'ref_conds'"
            >RCs</button>
            <button class="btn btn-default btn-sm pull-right"
                    :class="{active : settingsTab.key === 'data_sets'}"
                    :style="textSysStyle"
                    :disabled="!groupingAvailable"
                    v-if="tableMeta._is_owner"
                    @click="settingsTab.key = 'data_sets'"
            >Grouping</button>
            <button class="btn btn-default btn-sm pull-right"
                    :class="{active : settingsTab.key === 'links'}"
                    :style="textSysStyle"
                    v-if="tableMeta._is_owner"
                    @click="settingsTab.key = 'links'"
            >Links</button>
            <button class="btn btn-default btn-sm pull-right"
                    :class="{active : settingsTab.key === 'ddl'}"
                    :style="textSysStyle"
                    v-if="tableMeta._is_owner"
                    @click="settingsTab.key = 'ddl'"
            >DDLs</button>
            <button class="btn btn-default btn-sm pull-right"
                    :class="{active : settingsTab.key === 'permissions'}"
                    :style="textSysStyle"
                    v-if="tableMeta._is_owner"
                    @click="settingsTab.key = 'permissions'"
            >Share</button>
        </div>
        <div class="menu-body" :style="$root.themeMainBgStyle">

            <!--BASICS TAB-->

            <div class="full-height" v-show="settingsTab.key === 'basics'">
                <div class="full-frame">
                    <table-settings-basics
                            :table-meta="tableMeta"
                            :settings-meta="settingsMeta"
                            :user="user"
                            :table_id="table_id"
                            :is-visible="isVisible && settingsTab.key === 'basics'"
                    ></table-settings-basics>
                </div>
            </div>

            <!--DATA SETS TAB-->

            <div v-show="tableMeta._is_owner && settingsTab.key === 'ref_conds'" class="full-height">
                <div class="full-frame">
                    <tab-settings-ref-conditions
                        :table-meta="tableMeta"
                        :settings-meta="settingsMeta"
                        :user="user"
                        :table_id="table_id"
                    ></tab-settings-ref-conditions>
                </div>
            </div>

            <!--DATA SETS TAB-->

            <div v-show="tableMeta._is_owner && settingsTab.key === 'data_sets'" class="full-height">
                <div class="full-frame">
                    <table-grouping-settings
                        :table-meta="tableMeta"
                        :settings-meta="settingsMeta"
                        :user="user"
                        :table_id="table_id"
                    ></table-grouping-settings>
                </div>
            </div>

            <!--LINKS TAB-->

            <div v-show="tableMeta._is_owner && settingsTab.key === 'links'" class="full-height">
                <div class="full-frame">
                    <table-settings-display-links
                        :table-meta="tableMeta"
                        :settings-meta="settingsMeta"
                        :user="user"
                        :table_id="table_id"
                    ></table-settings-display-links>
                </div>
            </div>

            <!--DDL TAB-->

            <div v-show="tableMeta._is_owner && settingsTab.key === 'ddl'" class="full-height">
                <div class="full-frame">
                    <table-ddl-settings
                        :table-meta="tableMeta"
                        :settings-meta="settingsMeta"
                        :user="user"
                        :table_id="table_id"
                    ></table-ddl-settings>
                </div>
            </div>

            <!--PERMISSIONS TAB-->

            <div v-show="tableMeta._is_owner && settingsTab.key === 'permissions'" class="full-height">
                <div class="full-frame">
                    <table-settings-permissions
                        :table-meta="tableMeta"
                        :user="user"
                        :table_id="table_id"
                    ></table-settings-permissions>
                </div>
            </div>

        </div>

    </div>
</template>

<script>
    import {eventBus} from '../../../../../app';

    import CellStyleMixin from "../../../../_Mixins/CellStyleMixin";
    
    import CustomTable from '../../../../CustomTable/CustomTable.vue';
    import TableDdlSettings from './TableDdlSettings.vue';
    import TableSettingsPermissions from './TableSettingsPermissions.vue';
    import TableGroupingSettings from "./TableGroupingSettings";
    import TableSettingsDisplayLinks from './TableSettingsDisplayLinks.vue';
    import TabSettingsRefConditions from "./TabSettingsRefConditions.vue";
    import TableSettingsBasics from "./TableSettingsBasics.vue";

    export default {
        name: "TabSettings",
        mixins: [
            CellStyleMixin,
        ],
        components: {
            TableGroupingSettings,
            TableSettingsBasics,
            TabSettingsRefConditions,
            CustomTable,
            TableDdlSettings,
            TableSettingsPermissions,
            TableSettingsDisplayLinks
        },
        data: function () {
            return {
            }
        },
        props:{
            tableMeta: Object,
            settingsMeta: Object,
            table_id: Number,
            settingsTab: {
                type: Object,
                default: function () {
                    return {key: 'basics'}
                }
            },
            user: Object,
            showInfo: Boolean,
            isVisible: Boolean,
        },
        computed: {
            groupingAvailable() {
                return this.user.is_admin
                    || this.$root.checkAvailable(this.user, 'group_rows')
                    || this.$root.checkAvailable(this.user, 'group_columns');
            }
        },
        methods: {
        },
        mounted() {
        },
        beforeDestroy() {
        }
    }
</script>

<style lang="scss" scoped>
    @import "./TabSettings";
    .btn-sm {
        height: 30px !important;
    }
</style>