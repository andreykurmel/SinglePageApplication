<template>
    <div class="full-height permissions-tab" :style="$root.themeMainBgStyle">

        <div v-if="!$root.AddonAvailableToUser(tableMeta, 'simplemap')" class="permissions-panel full-height flex flex--center" :style="$root.themeMainBgStyle">
            <label>Addon is unavailable!</label>
        </div>
        <div v-else class="permissions-panel full-height" :style="$root.themeMainBgStyle">
            <div class="menu-header">
                <button v-if="canAddonEdit()"
                        class="btn btn-default btn-sm left-btn"
                        :class="{active : activeTab === 'settings'}"
                        :style="textSysStyle"
                        @click="changeActab('settings')"
                >Settings</button>
                <template v-for="simplemap in ActiveSimplemaps">
                    <button class="btn btn-default btn-sm left-btn"
                            :class="{active : activeTab === simplemap.id}"
                            :style="textSysStyle"
                            style="margin-right: 3px;"
                            @click="changeActab(simplemap.id)"
                    ><i class="fas fa-chart-bar"></i>&nbsp;{{ simplemap.name }}</button>
                </template>

                <div class="flex flex--center-v" style="position: absolute; right: 0; top: 3px; height: 30px;">
                    <label v-if="activeTab !== 'settings'"
                           class="flex flex--center"
                           style="margin: 0 0 0 15px; white-space: nowrap;"
                           :style="textSysStyleSmart"
                    >
                        Loaded View:&nbsp;
                        <select-block
                            :options="smpOpts()"
                            :sel_value="activeTab"
                            :style="{ width:'150px', height:'32px', }"
                            @option-select="smpChange"
                        ></select-block>
                    </label>

                    <div v-show="activeTab === 'settings'" style="margin: 0 5px 0 15px">
                        <info-sign-link v-show="activeSub === 'list'" :app_sett_key="'help_link_simplemap_tab'" :hgt="26" :txt="'for Simplemap/List'"></info-sign-link>
                        <info-sign-link v-show="activeSub === 'general'" :app_sett_key="'help_link_simplemap_tab_settings'" :hgt="26" :txt="'for Simplemap/General'"></info-sign-link>
                        <info-sign-link v-show="activeSub === 'fields'" :app_sett_key="'help_link_simplemap_tab_fields'" :hgt="26" :txt="'for Simplemap/Fields'"></info-sign-link>
                    </div>
                    <div v-show="activeTab !== 'settings'" style="margin: 0 5px 0 15px">
                        <info-sign-link :app_sett_key="'help_link_simplemap_tab_data'" :hgt="26" :txt="'for Simplemap/Data Tab'"></info-sign-link>
                    </div>
                </div>
            </div>
            <div class="menu-body" style="height: calc(100% - 27px); border: 1px solid #ccc;" :style="$root.themeMainBgStyle">

                <!--SETTINGS TAB-->

                <div class="full-frame" v-show="activeTab === 'settings'">
                    <simplemap-settings
                        v-if="canAddonEdit()"
                        :table_id="table_id"
                        :table-meta="tableMeta"
                    ></simplemap-settings>
                </div>

                <!--BASICS TAB-->

                <div v-for="selSimplemap in ActiveSimplemaps"
                     v-if="canDraw && activeTab === selSimplemap.id"
                     class="full-frame"
                >
                    <simplemap-instance
                        :table-meta="tableMeta"
                        :selected-simplemap="selSimplemap"
                        :current-page-rows="currentPageRows"
                        :request-params="request_params"
                    ></simplemap-instance>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    import CellStyleMixin from "./../../../_Mixins/CellStyleMixin.vue";

    import InfoSignLink from "../../../CustomTable/Specials/InfoSignLink.vue";
    import SelectBlock from "../../../CommonBlocks/SelectBlock.vue";
    import FieldsChecker from "../../../CommonBlocks/FieldsChecker.vue";
    import SimplemapSettings from "./Simplemap/SimplemapSettings.vue";
    import SimplemapInstance from "./Simplemap/SimplemapInstance.vue";

    export default {
        name: "TabSimplemapView",
        mixins: [
            CellStyleMixin,
        ],
        components: {
            SimplemapInstance,
            SimplemapSettings,
            FieldsChecker,
            SelectBlock,
            InfoSignLink,
        },
        data: function () {
            return {
                canDraw: true,
                selectedCol: -1,
                activeTab: '',
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
            ActiveSimplemaps() {
                return _.filter(this.tableMeta._simplemaps, (gr) => {
                    return gr.smp_active;
                });
            },
            selectedSimplemap() {
                return _.find(this.ActiveSimplemaps, {id: Number(this.activeTab)});
            },
        },
        watch: {
            table_id(val) {
                this.changeActab('settings');
            },
            isVisible(val) {
                if (val && !this.activeTab) {
                    this.$nextTick(() => {
                        let first = _.first(this.ActiveSimplemaps) || {};
                        let tabName = this.canAddonEdit() ? 'settings': (first.id || 'settings');
                        this.changeActab(tabName);
                    });
                }
            },
        },
        methods: {
            canAddonEdit() {
                return this.$root.addonCanEditGeneral(this.tableMeta, 'simplemap');
            },
            getOnlyColumns() {
                let ColGr = _.find(this.tableMeta._column_groups, {id: Number(this.selectedSimplemap.rg_colgroup_id)});
                return ColGr ? _.map(ColGr._fields, 'field') : null;
            },
            handleRedraw() {
                this.canDraw = false;
                this.$nextTick(() => {
                    this.canDraw = true;
                });
            },
            smpOpts() {
                return _.map(this.ActiveSimplemaps, (smp) => {
                    return {
                        val: smp.id,
                        show: smp.name,
                    };
                });
            },
            smpChange(opt) {
                this.changeActab(opt.val);
            },
            changeActab(val) {
                this.activeTab = '';
                this.$nextTick(() => {
                    this.activeTab = val;
                    let selSimplemap = _.find(this.ActiveSimplemaps, {id: Number(val)});
                    this.$root.selectedAddon.sub_name = selSimplemap ? selSimplemap.name : '';
                    this.$root.selectedAddon.sub_id = selSimplemap ? selSimplemap.id : '';
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