<template>
    <div v-if="tableMeta && $root.settingsMeta" class="tab-settings full-height">
        <div v-if="!$root.AddonAvailableToUser(tableMeta, 'calendar')" class="row full-frame flex flex--center">
            <label>Addon is unavailable!</label>
        </div>
        <div v-else class="full-height">
            <div class="menu-header">
                <button class="btn btn-default btn-sm left-btn"
                        :class="{active : acttab === 'settings'}"
                        :style="textSysStyle"
                        @click="changeActab('settings')"
                >Settings</button>
                <template v-for="calend in tableMeta._calendar_addons" v-if="calend.calendar_active">
                    <button class="btn btn-default btn-sm left-btn"
                            :class="{active : acttab === calend.id}"
                            :style="textSysStyle"
                            style="margin-right: 3px;"
                            @click="changeActab(calend.id)"
                    ><i class="far fa-calendar"></i>&nbsp;{{ calend.name }}</button>
                </template>

                <div v-show="acttab === 'settings'" class="pull-right" style="margin: 0 5px 0 15px">
                    <info-sign-link v-show="stTab === 'list'" :app_sett_key="'help_link_calendar_tab'" :hgt="26" :txt="'for Calendar/List'"></info-sign-link>
                    <info-sign-link v-show="stTab === 'general'" :app_sett_key="'help_link_calendar_tab'" :hgt="26" :txt="'for Calendar/General'"></info-sign-link>
                </div>
                <div v-show="acttab !== 'settings'" class="pull-right" style="margin: 0 5px 0 15px">
                    <info-sign-link :app_sett_key="'help_link_calendar_tab_data'" :hgt="26" :txt="'for Calendar/Data Tab'"></info-sign-link>
                </div>
                <button v-if="$root.AddonAvailableToUser(tableMeta, 'calendar', 'edit')"
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
                    <calendar-settings
                        :table_id="tableMeta.id"
                        :table-meta="tableMeta"
                        @set-sub-tab="(key) => { stTab = key; }"
                    ></calendar-settings>
                </div>

                <!--BASICS TAB-->

                <div class="full-frame flex flex--col" v-show="acttab !== 'settings'" v-if="canDraw">
                    <div class="flex__elem-remain">
                        <calendar-tab
                            v-if="tableMeta && selectedCalendar"
                            :table-meta="tableMeta"
                            :request-params="request_params"
                            :current-page-rows="currentPageRows"
                            :selected-calendar="selectedCalendar"
                            :selected-state="selectedState"
                            :add_click="add_click"
                            @show-src-record="showSrcRecord"
                        ></calendar-tab>
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

import {SpecialFuncs} from "../../../../classes/SpecialFuncs";

import CalendarSettings from "./Calendar/CalendarSettings";
import CalendarTab from "./Calendar/CalendarTab";
import InfoSignLink from "../../../CustomTable/Specials/InfoSignLink";
import TablePagination from "../../../CustomTable/Pagination/TablePagination";

import CanEditMixin from "../../../_Mixins/CanViewEditMixin";
import CellStyleMixin from "../../../_Mixins/CellStyleMixin";
import KanbanTab from "./Kanban/KanbanTab.vue";

export default {
    name: "TabCalendarView",
    mixins: [
        CanEditMixin,
        CellStyleMixin,
    ],
    components: {
        KanbanTab,
        TablePagination,
        InfoSignLink,
        CalendarTab,
        CalendarSettings,
    },
    data: function () {
        return {
            stTab: 'list',
            calendarStates: [],
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
        selectedState() {
            return _.find(this.calendarStates, {id: this.acttab});
        },
        selectedCalendar() {
            return _.find(this.tableMeta._calendar_addons, {id: this.acttab});
        },
        canDraw() {
            return this.isVisible && this.acttab;
        },
        canPaginate() {
            return this.selectedCalendar && this.selectedCalendar.calendar_data_range == '0';
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
        createStates() {
            _.each(this.tableMeta._calendar_addons, (calend) => {
                this.calendarStates.push({
                    id: calend.id,
                    type: '',
                    start: '',
                })
            });
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

    .blue-gradient {
        margin-right: 5px;
    }

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

        .right-elm {
            float: right;
            margin-left: 10px;
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

    .btn-default {
        height: 30px;
    }
}
</style>