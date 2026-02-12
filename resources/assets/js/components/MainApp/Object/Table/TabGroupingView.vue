<template>
    <div class="full-height permissions-tab" :style="$root.themeMainBgStyle">

        <div v-if="!$root.AddonAvailableToUser(tableMeta, 'grouping')" class="permissions-panel full-height flex flex--center" :style="$root.themeMainBgStyle">
            <label>Addon is unavailable!</label>
        </div>
        <div v-else class="permissions-panel full-height" :style="$root.themeMainBgStyle">
            <div class="menu-header">
                <button class="btn btn-default btn-sm left-btn"
                        :class="{active : activeTab === 'settings'}"
                        :style="textSysStyle"
                        @click="changeActab('settings')"
                >Settings</button>
                <template v-for="grouping in ActiveGroupings">
                    <button class="btn btn-default btn-sm left-btn"
                            :class="{active : activeTab === grouping.id}"
                            :style="textSysStyle"
                            style="margin-right: 3px;"
                            @click="changeActab(grouping.id)"
                    ><i class="fas fa-chart-bar"></i>&nbsp;{{ grouping.name }}</button>
                </template>

                <div class="flex flex--center-v" style="position: absolute; right: 0; top: 3px; height: 30px;">
                    <active-groups-button
                        v-if="activeTab !== 'settings' && selectedGrouping"
                        :table-meta="tableMeta"
                        :selected-grouping="selectedGrouping"
                        @handle-redraw="handleRedraw"
                    ></active-groups-button>

                    <show-hide-grouping-button
                        v-if="activeTab !== 'settings' && selectedGrouping"
                        :table-meta="tableMeta"
                        :selected-grouping="selectedGrouping"
                    ></show-hide-grouping-button>

                    <label v-if="activeTab !== 'settings'"
                           class="flex flex--center"
                           style="margin: 0 0 0 15px; white-space: nowrap;"
                           :style="textSysStyleSmart"
                    >
                        Loaded View:&nbsp;
                        <select-block
                            :options="grpOpts()"
                            :sel_value="activeTab"
                            :style="{ width:'150px', height:'32px', }"
                            @option-select="grpChange"
                        ></select-block>
                    </label>

                    <div v-show="activeTab === 'settings'" style="margin: 0 5px 0 15px">
                        <info-sign-link v-show="activeSub === 'list'" :app_sett_key="'help_link_grouping_tab'" :hgt="26" :txt="'for Grouping/List'"></info-sign-link>
                        <info-sign-link v-show="activeSub === 'general'" :app_sett_key="'help_link_grouping_tab_settings'" :hgt="26" :txt="'for Grouping/General'"></info-sign-link>
                    </div>
                    <div v-show="activeTab !== 'settings'" style="margin: 0 5px 0 15px">
                        <info-sign-link :app_sett_key="'help_link_grouping_tab_data'" :hgt="26" :txt="'for Grouping/Data Tab'"></info-sign-link>
                    </div>
                </div>
            </div>
            <div class="menu-body" style="height: calc(100% - 27px); border: 1px solid #ccc;" :style="$root.themeMainBgStyle">

                <!--SETTINGS TAB-->

                <div class="full-frame" v-show="activeTab === 'settings'">
                    <grouping-settings
                        :table-meta="tableMeta"
                        @set-sub-tab="(key) => { activeSub = key; }"
                        @should-redraw="handleRedraw"
                    ></grouping-settings>
                </div>

                <!--BASICS TAB-->

                <div v-for="selGrouping in ActiveGroupings"
                     v-if="canDraw"
                     v-show="activeTab === selGrouping.id"
                     class="full-frame"
                >
                    <grouping-view
                        :table-meta="tableMeta"
                        :request-params="request_params"
                        :current-page-rows="currentPageRows"
                        :selected-grouping="selGrouping"
                        :add_click="0"
                        :is-visible="isVisible && activeTab === selGrouping.id"
                        @show-src-record="showSrcRecord"
                    ></grouping-view>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    import CellStyleMixin from "./../../../_Mixins/CellStyleMixin.vue";

    import InfoSignLink from "../../../CustomTable/Specials/InfoSignLink.vue";
    import GroupingSettings from "./Grouping/GroupingSettings.vue";
    import GroupingView from "./Grouping/GroupingView.vue";
    import SelectBlock from "../../../CommonBlocks/SelectBlock.vue";
    import FieldsChecker from "../../../CommonBlocks/FieldsChecker.vue";
    import ShowHideGroupingButton from "../../../Buttons/ShowHideGroupingButton.vue";
    import ActiveGroupsButton from "./Grouping/ActiveGroupsButton.vue";

    export default {
        name: "TabGroupingView",
        mixins: [
            CellStyleMixin,
        ],
        components: {
            ActiveGroupsButton,
            ShowHideGroupingButton,
            FieldsChecker,
            SelectBlock,
            GroupingView,
            GroupingSettings,
            InfoSignLink,
        },
        data: function () {
            return {
                canDraw: true,
                selectedCol: -1,
                activeTab: 'settings',
                activeSub: 'list',
            }
        },
        props: {
            table_id: Number,
            tableMeta: Object,
            request_params: Object,
            currentPageRows: Array,
            isVisible: Boolean,
        },
        computed: {
            ActiveGroupings() {
                return _.filter(this.tableMeta._groupings, (gr) => {
                    return gr.rg_active;
                });
            },
            selectedGrouping() {
                return _.find(this.ActiveGroupings, {id: Number(this.activeTab)});
            },
        },
        watch: {
            table_id(val) {
                this.changeActab('settings');
            },
        },
        methods: {
            getOnlyColumns() {
                let ColGr = _.find(this.tableMeta._column_groups, {id: Number(this.selectedGrouping.rg_colgroup_id)});
                return ColGr ? _.map(ColGr._fields, 'field') : null;
            },
            handleRedraw() {
                this.canDraw = false;
                this.$nextTick(() => {
                    this.canDraw = true;
                });
            },
            grpOpts() {
                return _.map(this.ActiveGroupings, (grp) => {
                    return {
                        val: grp.id,
                        show: grp.name,
                    };
                });
            },
            grpChange(opt) {
                this.changeActab(opt.val);
            },
            changeActab(val) {
                this.activeTab = '';
                this.$nextTick(() => {
                    this.activeTab = val;
                    let selGrouping = _.find(this.ActiveGroupings, {id: Number(val)});
                    this.$root.selectedAddon.sub_name = selGrouping ? selGrouping.name : '';
                    this.$root.selectedAddon.sub_id = selGrouping ? selGrouping.id : '';
                });
            },
            showSrcRecord(lnk, header, tableRow) {
                this.$emit('show-src-record', lnk, header, tableRow);
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
    .flex--end {
        .form-control {
            padding: 3px 6px;
            height: 28px;
        }
    }
</style>