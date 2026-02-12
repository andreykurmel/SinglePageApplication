<template>
    <div v-if="tableMeta && $root.settingsMeta" class="tab-settings full-height">
        <div v-if="!$root.AddonAvailableToUser(tableMeta, 'gantt')" class="row full-frame flex flex--center">
            <label>Addon is unavailable!</label>
        </div>
        <div v-else class="full-height">
            <div class="menu-header">
                <button class="btn btn-default btn-sm left-btn"
                        :class="{active : acttab === 'settings'}"
                        :style="textSysStyle"
                        @click="changeActab('settings')"
                >Settings</button>
                <template v-for="gantt in tableMeta._gantt_addons" v-if="gantt.gantt_active">
                    <button class="btn btn-default btn-sm left-btn"
                            :class="{active : acttab === gantt.id}"
                            :style="textSysStyle"
                            style="margin-right: 3px;"
                            @click="changeActab(gantt.id)"
                    ><i class="fas fa-chart-bar"></i>&nbsp;{{ gantt.name }}</button>
                </template>

                <div v-show="acttab === 'settings'" class="pull-right" style="margin: 0 5px 0 15px">
                    <info-sign-link v-show="activeSub === 'list'" :app_sett_key="'help_link_gantt_tab'" :hgt="26" :txt="'for Gantt/List'"></info-sign-link>
                    <info-sign-link v-show="activeSub === 'general'" :app_sett_key="'help_link_gantt_tab_general'" :hgt="26" :txt="'for Gantt/General'"></info-sign-link>
                    <info-sign-link v-show="activeSub === 'fld_specific'" :app_sett_key="'help_link_gantt_tab_specific'" :hgt="26" :txt="'for Gantt/Field Specific'"></info-sign-link>
                </div>
                <div v-show="acttab !== 'settings'" class="pull-right" style="margin: 0 5px 0 15px">
                    <info-sign-link :app_sett_key="'help_link_gantt_tab_data'" :hgt="26" :txt="'for Gantt/Data Tab'"></info-sign-link>
                </div>
                <button v-if="$root.AddonAvailableToUser(tableMeta, 'gantt', 'edit')"
                        v-show="acttab !== 'settings'"
                        class="btn btn-primary btn-sm blue-gradient pull-right"
                        :disabled="!canAdd"
                        :style="$root.themeButtonStyle"
                        @click="add_click++"
                >Add</button>
            </div>
            <div class="menu-body" :style="$root.themeMainBgStyle">

                <!--SETTINGS TAB-->

                <div class="full-frame" v-show="acttab === 'settings'">
                    <gantt-settings
                        :table_id="tableMeta.id"
                        :table-meta="tableMeta"
                        @set-sub-tab="(key) => { activeSub = key; }"
                    ></gantt-settings>
                </div>

                <!--BASICS TAB-->

                <div class="full-frame flex flex--col" v-show="acttab !== 'settings'" v-if="canDraw">
                    <div class="flex__elem-remain">
                        <gantt-chart
                            v-if="tableMeta && selectedGantt"
                            :table-meta="tableMeta"
                            :request-params="request_params"
                            :current-page-rows="currentPageRows"
                            :selected-gantt="selectedGantt"
                            :add_click="add_click"
                            @show-src-record="showSrcRecord"
                        ></gantt-chart>
                    </div>
                    <table-pagination
                        v-if="canPaginate && request_params"
                        :page="request_params.page"
                        :table-meta="tableMeta"
                        :rows-count="tableMeta._view_rows_count || 0"
                        :style="{ position: 'relative', height: '32px' }"
                        @change-page="changePg"
                    ></table-pagination>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    import {eventBus} from "../../../../app";

    import GanttSettings from "./Gantt/GanttSettings";
    import GanttChart from "./Gantt/GanttChart";
    import InfoSignLink from "../../../CustomTable/Specials/InfoSignLink";
    import TablePagination from "../../../CustomTable/Pagination/TablePagination";

    import CanEditMixin from "../../../_Mixins/CanViewEditMixin";
    import CellStyleMixin from "../../../_Mixins/CellStyleMixin";
    import KanbanTab from "./Kanban/KanbanTab.vue";

    export default {
        name: "TabGanttView",
        mixins: [
            CanEditMixin,
            CellStyleMixin,
        ],
        components: {
            KanbanTab,
            TablePagination,
            InfoSignLink,
            GanttChart,
            GanttSettings,
        },
        data: function () {
            return {
                activeSub: 'list',
                acttab: 'settings',
                add_click: 0,
            }
        },
        props:{
            table_id: Number,
            tableMeta: Object,
            request_params: Object,
            currentPageRows: Array,
            isVisible: Boolean,
        },
        computed: {
            selectedGantt() {
                return _.find(this.tableMeta._gantt_addons, {id: this.acttab});
            },
            canDraw() {
                return this.isVisible && this.acttab;
            },
            canPaginate() {
                return this.selectedGantt && this.selectedGantt.gantt_data_range == '0';
            },
        },
        watch: {
            table_id(val) {
                this.changeActab('settings');
            },
        },
        methods: {
            changeActab(val) {
                this.acttab = '';
                this.$nextTick(() => {
                    this.acttab = val;
                });
            },
            showSrcRecord(lnk, header, tableRow) {
                this.$emit('show-src-record', lnk, header, tableRow);
            },
            changePg(page) {
                eventBus.$emit('changed-page', page);
            },
        },
        mounted() {
        },
        beforeDestroy() {
        }
    }
</script>

<style lang="scss" scoped>
    .tab-settings {
        position: relative;
        height: 100%;

        .menu-header {
            position: relative;
            margin-left: 5px;
            top: 3px;

            .left-btn {
                position: relative;
                top: 5px;
            }

            button {
                background-color: #CCC;
                outline: none;
                &.active {
                    background-color: #FFF;
                }
                &:not(.active):hover {
                    color: black;
                }
            }
        }

        .menu-body {
            position: absolute;
            top: 35px;
            right: 5px;
            left: 5px;
            bottom: 5px;
            border-radius: 5px;
            border: 1px solid #CCC;
        }

        .pull-right {
            margin-right: 5px;
        }
    }
</style>