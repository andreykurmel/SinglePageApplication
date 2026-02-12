<template>
    <div class="link-data" style="background-color: inherit;">
        <div class="link-menu flex">
            <button class="btn btn-default mr5" :class="{active: activeTab === 'outgo'}" :style="textSysStyle" @click="changeTab('outgo')">
                Outgoing
            </button>
            <button class="btn btn-default mr5" :class="{active: activeTab === 'incom'}" :style="textSysStyle" @click="changeTab('incom')">
                Incoming
            </button>
            <button class="btn btn-default mr5" :class="{active: activeTab === 'rc_map'}" :style="textSysStyle" @click="changeTab('rc_map')">
                Map
            </button>

            <div style="position: absolute;right: 5px;">
                <info-sign-link v-show="activeTab === 'outgo' && activeSub === 'list'"
                                class="ml5 mr5"
                                :app_sett_key="'help_link_settings_refconds'"
                                :hgt="30"
                                :txt="'for Settings/RefConds/List'"
                ></info-sign-link>
                <info-sign-link v-show="activeTab === 'outgo' && activeSub === 'lcs'"
                                class="ml5 mr5"
                                :app_sett_key="'help_link_settings_refconds_details'"
                                :hgt="30"
                                :txt="'for Settings/RefConds/Details'"
                ></info-sign-link>
                <info-sign-link v-show="activeTab === 'incom'"
                                class="ml5 mr5"
                                :app_sett_key="'help_link_settings_refconds_incom'"
                                :hgt="30"
                                :txt="'for Settings/RefConds/Incoming'"
                ></info-sign-link>
                <info-sign-link v-show="activeTab === 'rc_map'"
                                class="ml5 mr5"
                                :app_sett_key="'help_link_settings_refconds_rc_map'"
                                :hgt="30"
                                :txt="'for Settings/Links/Map'"
                ></info-sign-link>
            </div>
        </div>

        <!--Tab with link settings-->
        <div class="link-tab" v-show="activeTab === 'outgo'">
            <tab-settings-ref-conditions
                :table-meta="tableMeta"
                :settings-meta="settingsMeta"
                :user="user"
                :table_id="table_id"
                :filter_id="filter_id"
                @set-sub-tab="(key) => { activeSub = key; }"
            ></tab-settings-ref-conditions>
        </div>

        <!--Summarize incoming link-->
        <div class="link-tab" v-show="activeTab === 'incom'">
            <custom-table
                v-if="sortApplied && tableMeta && incomLinks()"
                :cell_component_name="'custom-cell-incoming-links'"
                :global-meta="tableMeta"
                :table-meta="settingsMeta['incoming_links']"
                :settings-meta="settingsMeta"
                :all-rows="incomLinks()"
                :rows-count="incomLinks().length"
                :cell-height="1"
                :max-cell-rows="0"
                :is-full-width="true"
                :user="user"
                :available-columns="availableRcIncom"
                :behavior="'incoming_links'"
                :use_theme="true"
                :headers-changer="{'ref_cond_name':'Name'}"
                @updated-row="updateIncomLink"
                @sort-by-field="sortByFieldFront"
            ></custom-table>
        </div>

        <!--Summarize RCs as Map-->
        <div class="link-tab" v-if="activeTab === 'rc_map'">
            <table-settings-ref-cond-maps
                :table-meta="tableMeta"
            ></table-settings-ref-cond-maps>
        </div>
    </div>
</template>

<script>
    import TableSortMixin from "../../../../_Mixins/TableSortMixin";
    import CellStyleMixin from "../../../../_Mixins/CellStyleMixin";
    import IncomLinksMixin from "./IncomLinksMixin";

    import TableSettingsRefCondMaps from "./RefCondMaps/TableSettingsRefCondMaps.vue";
    import TabSettingsRefConditions from "./TabSettingsRefConditions";
    import CustomTable from "../../../../CustomTable/CustomTable";
    import InfoSignLink from "../../../../CustomTable/Specials/InfoSignLink.vue";
    import SelectBlock from "../../../../CommonBlocks/SelectBlock.vue";

    export default {
        name: "TabSettingsRefcondTabs",
        mixins: [
            TableSortMixin,
            CellStyleMixin,
            IncomLinksMixin,
        ],
        components: {
            TableSettingsRefCondMaps,
            SelectBlock,
            InfoSignLink,
            CustomTable,
            TabSettingsRefConditions,
        },
        data: function () {
            return {
                activeTab: 'outgo',
                activeSub: 'list',
            }
        },
        props:{
            tableMeta: Object,
            settingsMeta: Object,
            table_id: Number|null,
            user: Object,
            filter_id: Number,
        },
        watch: {
            table_id(val) {
                this.clearIncom();
                this.changeTab('outgo');
            },
        },
        methods: {
            changeTab(tab) {
                this.activeTab = tab;
                if (this.activeTab === 'incom') {
                    this.loadIncomings();
                }
            },
            sortByFieldFront(tableHeader, $dir, $sub) {
                this.sort = this.sortByFieldWrows(this.sort, tableHeader, $dir, $sub);
                this.sortRows();
            },
            sortRows() {
                if (this.filter_id > 0) {
                    return;
                }
                let sortFlds = _.map(this.sort, 'field');
                let sortDirections = _.map(this.sort, 'direction');
                this.tableMeta.__incoming_links = _.orderBy(this.tableMeta.__incoming_links, sortFlds, sortDirections);
                this.sortApplied = false;
                this.$nextTick(() => {
                    this.sortApplied = true;
                });
            },
        },
    }
</script>

<style lang="scss" scoped>
    .link-data {
        height: 100%;
        padding: 5px 5px 7px 5px;

        .link-menu {
            button {
                background-color: #CCC;
                outline: 0;
            }
            .active {
                background-color: #FFF;
            }
        }

        .link-tab {
            height: calc(100% - 30px);
            position: relative;
            top: -3px;
            border: 1px solid #CCC;
            border-radius: 4px;
        }
    }
    .btn-default {
        height: 36px;
    }
</style>