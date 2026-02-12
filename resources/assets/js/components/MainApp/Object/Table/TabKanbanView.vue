<template>
    <div v-if="tableMeta && $root.settingsMeta" class="tab-settings full-height">
        <div v-if="!$root.AddonAvailableToUser(tableMeta, 'kanban')" class="row full-frame flex flex--center">
            <label>Addon is unavailable!</label>
        </div>
        <div v-else class="full-height">
            <div class="menu-header">
                <button class="btn btn-default btn-sm left-btn"
                        :class="{active : acttab === 'settings'}"
                        :style="textSysStyle"
                        @click="changeActab('settings')"
                >Settings</button>
                <template v-for="knb in ActiveKanbanFields">
                    <button class="btn btn-default btn-sm left-btn"
                            :class="{active : acttab === knb.id}"
                            :style="textSysStyle"
                            style="margin-right: 3px;"
                            @click="changeActab(knb.id)"
                    ><i class="fab fa-trello"></i>&nbsp;{{ kbName(knb) }}</button>
                </template>

                <div v-show="acttab === 'settings'" class="pull-right" style="margin: 0 5px 0 15px">
                    <info-sign-link v-show="activeSub === 'list'" :app_sett_key="'help_link_kanban_tab'" :hgt="26" :txt="'for Kanban/List'"></info-sign-link>
                    <info-sign-link v-show="activeSub === 'general'" :app_sett_key="'help_link_kanban_tab_general'" :hgt="26" :txt="'for Kanban/General'"></info-sign-link>
                    <info-sign-link v-show="activeSub === 'specific'" :app_sett_key="'help_link_kanban_tab_specific'" :hgt="26" :txt="'for Kanban/Specific'"></info-sign-link>
                </div>
                <div v-show="acttab !== 'settings'" class="pull-right" style="margin: 0 5px 0 15px">
                    <info-sign-link :app_sett_key="'help_link_kanban_tab_data'" :hgt="26" :txt="'for Kanban/Data Tab'"></info-sign-link>
                </div>
                <button v-if="$root.AddonAvailableToUser(tableMeta, 'kanban', 'edit')"
                        v-show="acttab !== 'settings'"
                        class="btn btn-primary btn-sm blue-gradient pull-right"
                        :disabled="!canAdd"
                        :style="$root.themeButtonStyle"
                        @click="add_click++"
                >Add</button>
            </div>
            <div class="menu-body" :style="$root.themeMainBgStyle">

                <!--SETTINGS TAB-->

                <div class="full-frame" v-if="tableMeta._is_owner" v-show="acttab === 'settings'">
                    <kanban-settings
                        :table_id="tableMeta.id"
                        :table-meta="tableMeta"
                        @set-sub-tab="(key) => { activeSub = key; }"
                    ></kanban-settings>
                </div>

                <!--BASICS TAB-->

                <div v-for="selKanb in ActiveKanbanFields" v-show="acttab === selKanb.id" class="full-frame flex flex--col">
                    <div class="flex__elem-remain">
                        <kanban-tab
                            :table-meta="tableMeta"
                            :request-params="request_params"
                            :current-page-rows="currentPageRows"
                            :selected-kanban="selKanb"
                            :add_click="add_click"
                            :is-visible="isVisible && acttab === selKanb.id"
                            @show-src-record="showSrcRecord"
                        ></kanban-tab>
                    </div>
                    <table-pagination
                        v-if="selKanb.kanban_data_range == '0' && request_params"
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

    import KanbanSettings from "./Kanban/KanbanSettings";
    import KanbanTab from "./Kanban/KanbanTab";

    import CanEditMixin from "../../../_Mixins/CanViewEditMixin";
    import CellStyleMixin from "../../../_Mixins/CellStyleMixin";
    import InfoSignLink from "../../../CustomTable/Specials/InfoSignLink";
    import TablePagination from "../../../CustomTable/Pagination/TablePagination";

    export default {
        name: "TabKanbanView",
        mixins: [
            CanEditMixin,
            CellStyleMixin,
        ],
        components: {
            TablePagination,
            InfoSignLink,
            KanbanTab,
            KanbanSettings,
        },
        data: function () {
            return {
                acttab: 'settings',
                activeSub: 'list',
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
            ActiveKanbanFields() {
                return _.filter(this.tableMeta._kanban_settings, (knb) => {
                    return knb.kanban_active;
                });
            },
            canDraw() {
                return this.isVisible && this.acttab;
            },
        },
        watch: {
            table_id(val) {
                this.changeActab('settings');
            },
        },
        methods: {
            kbName(knb) {
                return knb.kanban_field_name || _.find(this.tableMeta._fields, {id: Number(knb.table_field_id)}).name;
            },
            changeActab(val) {
                this.acttab = '';
                this.$nextTick(() => {
                    this.acttab = val;
                    let selKanb = _.find(this.ActiveKanbanFields, {id: Number(val)});
                    this.$root.selectedAddon.sub_name = selKanb ? selKanb.kanban_field_name : '';
                    this.$root.selectedAddon.sub_id = selKanb ? selKanb.id : '';
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
            border: none;
            border-top: 1px solid #CCC;
        }

        .btn-default {
            height: 30px;
        }
    }
</style>