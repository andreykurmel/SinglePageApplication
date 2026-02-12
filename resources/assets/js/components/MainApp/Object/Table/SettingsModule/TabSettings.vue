<template>
    <div v-if="tableMeta && settingsMeta" class="tab-settings full-height">
        <div class="menu-header" v-if="avaTabs.length > 1">
            <button v-if="avaTabs.indexOf('basics') > -1"
                    class="btn btn-default btn-sm pull-right"
                    :class="{active : settingsTab.key === 'basics'}"
                    :style="textSysStyle"
                    @click="settingsTab.key = 'basics'"
            >Basics</button>
            <button v-if="avaTabs.indexOf('ref_conds') > -1 && tableMeta._is_owner"
                    class="btn btn-default btn-sm pull-right"
                    :class="{active : settingsTab.key === 'ref_conds'}"
                    :style="textSysStyle"
                    :disabled="!$root.checkAvailable(user, 'group_refs')"
                    @click="settingsTab.key = 'ref_conds'"
            >RCs</button>
            <button v-if="avaTabs.indexOf('data_sets') > -1 && tableMeta._is_owner"
                    class="btn btn-default btn-sm pull-right"
                    :class="{active : settingsTab.key === 'data_sets'}"
                    :style="textSysStyle"
                    :disabled="!groupingAvailable"
                    @click="settingsTab.key = 'data_sets'"
            >Grouping</button>
            <button v-if="avaTabs.indexOf('links') > -1 && tableMeta._is_owner"
                    class="btn btn-default btn-sm pull-right"
                    :class="{active : settingsTab.key === 'links'}"
                    :style="textSysStyle"
                    @click="settingsTab.key = 'links'"
            >Links</button>
            <button v-if="avaTabs.indexOf('ddl') > -1 && tableMeta._is_owner"
                    class="btn btn-default btn-sm pull-right"
                    :class="{active : settingsTab.key === 'ddl'}"
                    :style="textSysStyle"
                    @click="settingsTab.key = 'ddl'"
            >DDLs</button>
            <button v-if="avaTabs.indexOf('permissions') > -1 && tableMeta._is_owner"
                    class="btn btn-default btn-sm pull-right"
                    :class="{active : settingsTab.key === 'permissions'}"
                    :style="textSysStyle"
                    @click="settingsTab.key = 'permissions'"
            >Share</button>
            <button v-if="avaTabs.indexOf('addons') > -1 && tableMeta._is_owner"
                    class="btn btn-default btn-sm pull-right"
                    :class="{active : settingsTab.key === 'addons'}"
                    :style="textSysStyle"
                    @click="settingsTab.key = 'addons'"
            >Add-ons</button>
        </div>
        <div class="menu-body" :style="bodyStyle">

            <!--BASICS TAB-->

            <div v-if="avaTabs.indexOf('basics') > -1" class="full-height" v-show="settingsTab.key === 'basics'">
                <div class="full-frame">
                    <table-settings-basics
                            :table-meta="tableMeta"
                            :settings-meta="settingsMeta"
                            :user="user"
                            :table_id="table_id"
                            :is-visible="isVisible && settingsTab.key === 'basics'"
                            :init_tab="basics_init_tab"
                            :filter_for_field="basics_filter_for_field"
                    ></table-settings-basics>
                </div>
            </div>

            <!--REF CONDS TAB-->

            <div v-if="avaTabs.indexOf('ref_conds') > -1" v-show="tableMeta._is_owner && settingsTab.key === 'ref_conds'" class="full-height">
                <div class="full-frame">
                    <tab-settings-refcond-tabs
                        :table-meta="tableMeta"
                        :settings-meta="settingsMeta"
                        :user="user"
                        :table_id="table_id"
                        :filter_id="ref_conds_filter"
                    ></tab-settings-refcond-tabs>
                </div>
            </div>

            <!--DATA SETS TAB-->

            <div v-if="avaTabs.indexOf('data_sets') > -1" v-show="tableMeta._is_owner && settingsTab.key === 'data_sets'" class="full-height">
                <div class="full-frame">
                    <table-grouping-settings
                        :table-meta="tableMeta"
                        :settings-meta="settingsMeta"
                        :user="user"
                        :table_id="table_id"
                        @show-src-record="showLinkedRows"
                    ></table-grouping-settings>
                </div>
            </div>

            <!--LINKS TAB-->

            <div v-if="avaTabs.indexOf('links') > -1" v-show="tableMeta._is_owner && settingsTab.key === 'links'" class="full-height">
                <div class="full-frame">
                    <table-settings-display-links
                        :table-meta="tableMeta"
                        :settings-meta="settingsMeta"
                        :user="user"
                        :table_id="table_id"
                        :filter_id="links_filter"
                    ></table-settings-display-links>
                </div>
            </div>

            <!--DDL TAB-->

            <div v-if="avaTabs.indexOf('ddl') > -1" v-show="tableMeta._is_owner && settingsTab.key === 'ddl'" class="full-height">
                <div class="full-frame">
                    <table-ddl-settings
                        :table-meta="tableMeta"
                        :settings-meta="settingsMeta"
                        :user="user"
                        :table_id="table_id"
                        :redraw="tableMeta._is_owner && settingsTab.key === 'ddl'"
                    ></table-ddl-settings>
                </div>
            </div>

            <!--PERMISSIONS TAB-->

            <div v-if="avaTabs.indexOf('permissions') > -1" v-show="tableMeta._is_owner && settingsTab.key === 'permissions'" class="full-height">
                <div class="full-frame">
                    <table-settings-permissions
                        :table-meta="tableMeta"
                        :user="user"
                        :table_id="table_id"
                    ></table-settings-permissions>
                </div>
            </div>

            <!--ADDONS TAB-->

            <div v-if="avaTabs.indexOf('addons') > -1 && !basics_filter_for_field" v-show="tableMeta._is_owner && settingsTab.key === 'addons'" class="full-height bg-white p5 border-gray">
                <div class="full-frame">
                    <table-settings-addons
                        :table-meta="tableMeta"
                        :tb_meta="tableMeta"
                        @prop-changed="updateTable"
                    ></table-settings-addons>
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
    import TableSettingsBasics from "./TableSettingsBasics.vue";
    import TabSettingsRefcondTabs from "./TabSettingsRefcondTabs";
    import TableSettingsAddons from "./TableSettingsAddons.vue";
    import TableSettingsModule from "../../../../CommonBlocks/TableSettingsModule.vue";

    export default {
        name: "TabSettings",
        mixins: [
            CellStyleMixin,
        ],
        components: {
            TableSettingsModule,
            TableSettingsAddons,
            TabSettingsRefcondTabs,
            TableGroupingSettings,
            TableSettingsBasics,
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
            user: Object,
            isVisible: Boolean,
            avaTabs: {
                type: Array,
                default: function () {
                    return ['basics','ref_conds','data_sets','ddl','links','permissions','addons'];
                }
            },
            settingsTab: {
                type: Object,
                default: function () {
                    return {key: _.first(this.avaTabs)};
                }
            },
            basics_filter_for_field: String,
            basics_init_tab: String,
            ref_conds_filter: Number,
            links_filter: Number,
        },
        computed: {
            groupingAvailable() {
                return this.user.is_admin
                    || this.$root.checkAvailable(this.user, 'group_rows')
                    || this.$root.checkAvailable(this.user, 'group_columns');
            },
            bodyStyle() {
                return {
                    ...this.$root.themeMainBgStyle,
                    ...{
                        left: this.avaTabs.length > 1 ? '32px' : '0',
                    },
                };
            },
        },
        methods: {
            showLinkedRows(lnk, header, tableRow, behavior) {
                this.$emit('show-src-record', lnk, header, tableRow, behavior);
            },
            updateTable(prop_name) {
                this.$root.updateTable(this.tableMeta, prop_name);
            },
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