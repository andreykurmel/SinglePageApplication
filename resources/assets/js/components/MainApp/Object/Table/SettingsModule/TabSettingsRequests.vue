<template>
    <div class="full-height" :style="sysStyleWiBg">
        <div v-if="!$root.AddonAvailableToUser(tableMeta, 'request')" class="full-frame flex flex--center">
            <label>Addon is unavailable!</label>
        </div>
        <div v-else="" class="full-height permissions-tab">
            <div class="permissions-panel" :style="$root.themeMainBgStyle">
                <div class="permissions-menu-header">
                    <button class="btn btn-default btn-sm" :style="textSysStyle" :class="{active : activeRightTab === 'list'}" @click="activeRightTab = 'list'">
                        List
                    </button>
                    <button class="btn btn-default btn-sm" :style="textSysStyle" :class="{active : activeRightTab === 'design'}" @click="activeRightTab = 'design'">
                        Design
                    </button>
                    <button class="btn btn-default btn-sm" :style="textSysStyle" :class="{active : activeRightTab === 'columns'}" @click="activeRightTab = 'columns'">
                        Fields
                    </button>
                    <button class="btn btn-default btn-sm" :style="textSysStyle" :class="{active : activeRightTab === 'actions'}" @click="activeRightTab = 'actions'">
                        Action & Status
                    </button>
                    <button class="btn btn-default btn-sm" :style="textSysStyle" :class="{active : activeRightTab === 'notifs'}" @click="activeRightTab = 'notifs'">
                        Notifications
                    </button>

                    <div v-if="selected_req" class="flex flex--center-v flex--end" style="position: absolute; top: 2px; right: 0; height: 30px; width: calc(100% - 510px);">

                        <div class="flex flex--center ml10 mr0" style="white-space: nowrap;font-weight: bold;margin-bottom: 5px;" :style="textSysStyleSmart">
                            <button class="btn btn-default btn-sm blue-gradient full-height"
                                    :style="$root.themeButtonStyle"
                                    @click="copyPopup = true"
                            >Copy</button>
                        </div>
                        
                        <label class="flex flex--center ml10 mr0" style="white-space: nowrap;" :style="textSysStyleSmart">
                            DCR Loaded:&nbsp;
                            <select-block
                                :options="dcrOpts(true)"
                                :sel_value="selected_req.id"
                                :style="{ maxWidth:'250px', height:'32px', minWidth: '100px', }"
                                :link_path="getLinkHash()"
                                @option-select="seleChange"
                            ></select-block>
                        </label>


                        <info-sign-link v-show="activeRightTab === 'list'" class="ml5 mr5 ilnk" :app_sett_key="'help_link_dcr_tab'" :hgt="30" :txt="'for DCR/List'"></info-sign-link>
                        <info-sign-link v-show="activeRightTab === 'design' && actDes === 'overall'" class="ml5 mr5 ilnk" :app_sett_key="'help_link_dcr_tab_design_all'" :hgt="30" :txt="'for DCR/Design/Overall'"></info-sign-link>
                        <info-sign-link v-show="activeRightTab === 'design' && actDes === 'title'" class="ml5 mr5 ilnk" :app_sett_key="'help_link_dcr_tab_design_title'" :hgt="30" :txt="'for DCR/Design/Title'"></info-sign-link>
                        <info-sign-link v-show="activeRightTab === 'design' && actDes === 'form'" class="ml5 mr5 ilnk" :app_sett_key="'help_link_dcr_tab_design_form'" :hgt="30" :txt="'for DCR/Design/Form'"></info-sign-link>
                        <info-sign-link v-show="activeRightTab === 'design' && actDes === 'top_msg'" class="ml5 mr5 ilnk" :app_sett_key="'help_link_dcr_tab_design_top_msg'" :hgt="30" :txt="'for DCR/Design/Top Message'"></info-sign-link>
                        <info-sign-link v-show="activeRightTab === 'columns' && activeColTab === 'this_tb' && activeColThisTbTab === 'col_group'" class="ml5 mr5 ilnk" :app_sett_key="'help_link_dcr_tab_columns_group'" :hgt="30" :txt="'for DCR/Columns/Groups'"></info-sign-link>
                        <info-sign-link v-show="activeRightTab === 'columns' && activeColTab === 'this_tb' && activeColThisTbTab === 'tb_display'" class="ml5 mr5 ilnk" :app_sett_key="'help_link_dcr_tab_columns_tb_display'" :hgt="30" :txt="'for DCR/Columns/Display'"></info-sign-link>
                        <info-sign-link v-show="activeRightTab === 'columns' && activeColTab === 'this_tb' && activeColThisTbTab === 'default'" class="ml5 mr5 ilnk" :app_sett_key="'help_link_dcr_tab_columns_default'" :hgt="30" :txt="'for DCR/Columns/Default'"></info-sign-link>
                        <info-sign-link v-show="activeRightTab === 'columns' && activeColTab === 'embed'" class="ml5 mr5 ilnk" :app_sett_key="'help_link_dcr_tab_columns_embed'" :hgt="30" :txt="'for DCR/Columns/Embed'"></info-sign-link>
                        <info-sign-link v-show="activeRightTab === 'actions'" class="ml5 mr5 ilnk" :app_sett_key="'help_link_dcr_tab_actions'" :hgt="30" :txt="'for DCR/Actions'"></info-sign-link>
                        <info-sign-link v-show="activeRightTab === 'notifs' && actNots === 'sav'" class="ml5 mr5 ilnk" :app_sett_key="'help_link_dcr_tab_notifs_sav'" :hgt="30" :txt="'for DCR/Notifications/Saving'"></info-sign-link>
                        <info-sign-link v-show="activeRightTab === 'notifs' && actNots === 'submis'" class="ml5 mr5 ilnk" :app_sett_key="'help_link_dcr_tab_notifs_submit'" :hgt="30" :txt="'for for DCR/Notifications/Submitting'"></info-sign-link>
                        <info-sign-link v-show="activeRightTab === 'notifs' && actNots === 'updat'" class="ml5 mr5 ilnk" :app_sett_key="'help_link_dcr_tab_notifs_updat'" :hgt="30" :txt="'for DCR/Notifications/Updating'"></info-sign-link>
                    </div>
                </div>
                <div class="permissions-menu-body">

                    <div class="full-frame defaults-tab flex flex--col" v-show="activeRightTab === 'list'" :style="$root.themeMainBgStyle">
                        <div style="height: 30%;">
                            <custom-table
                                v-if="isVisible"
                                :cell_component_name="'custom-cell-settings-dcr'"
                                :global-meta="tableMeta"
                                :table-meta="$root.settingsMeta['table_data_requests']"
                                :all-rows="tableMeta._table_requests"
                                :rows-count="tableMeta._table_requests.length"
                                :cell-height="1.2"
                                :max-cell-rows="0"
                                :is-full-width="true"
                                :with_edit="canAddonEdit()"
                                :behavior="'table_permission_group'"
                                :user="$root.user"
                                :adding-row="addingRow"
                                :selected-row="selectedGroup"
                                :available-columns="requestAvailable"
                                @added-row="addGroup"
                                @updated-row="updateGroup"
                                @delete-row="deleteGroup"
                                @row-index-clicked="rowIndexClickedGroup"
                            ></custom-table>
                        </div>
                        <div class="top-text" style="background: linear-gradient(rgb(255, 255, 255), rgb(209, 209, 209) 50%, rgb(199, 199, 199) 50%, rgb(230, 230, 230));">
                            <span style="color: #444; font-weight: bold; font-size: 18px; line-height: 18px; padding-left: 5px;">Access</span>
                        </div>
                        <div style="height: 70%; padding: 5px; overflow: auto;">
                            <tab-settings-access-row
                                v-if="selected_req"
                                :table-meta="tableMeta"
                                :cell-height="1"
                                :max-cell-rows="0"
                                :table-request="$root.settingsMeta['table_data_requests']"
                                :request-row="selectedGroup > -1 ? selected_req : {}"
                                :with_edit="canPermisEdit()"
                                @updated-cell="updateGroup"
                            ></tab-settings-access-row>
                        </div>
                    </div>

                    <div class="full-frame defaults-tab" v-show="activeRightTab === 'design'" :style="$root.themeMainBgStyle">
                        <tab-settings-requests-row
                                v-if="selected_req"
                                :table_id="tableMeta.id"
                                :table-meta="tableMeta"
                                :cell-height="1"
                                :max-cell-rows="0"
                                :table-request="$root.settingsMeta['table_data_requests']"
                                :request-row="selected_req"
                                :with_edit="canPermisEdit()"
                                @updated-cell="updateGroup"
                                @upload-file="uploadGroupFile"
                                @del-file="delGroupFile"
                                @set-sub-tab="(key) => { actDes = key; }"
                        ></tab-settings-requests-row>
                    </div>

                    <div class="full-frame defaults-tab flex flex--col" v-show="activeRightTab === 'columns'" :style="$root.themeMainBgStyle">
                        <div class="permissions-menu-header" :style="$root.themeMainBgStyle" style="padding: 0 0 0 5px;">
                            <button class="btn btn-default btn-sm" :class="{active : activeColTab === 'this_tb'}" :style="textSysStyle" @click="activeColTab = 'this_tb'">
                                This Table
                            </button>
                            <button class="btn btn-default btn-sm" :class="{active : activeColTab === 'embed'}" :style="textSysStyle" @click="activeColTab = 'embed'">
                                Embedded Tables
                            </button>

                            <div v-if="activeColTab === 'this_tb'" class="flex flex--center-v flex--end" style="position: absolute; top: 2px; right: 5px; height: 30px; width: 250px;">
                                <label class="flex flex--center" style="line-height: 20px;">
                                    <a @click="showSettingsPoPopup" style="cursor: pointer; text-decoration: underline;" :style="textSysStyleSmart">Settings</a>
                                </label>
                            </div>
                        </div>
                        <div class="permissions-menu-body" style="border: 1px solid #CCC; overflow: auto;">
                            <tab-settings-requests-this-table
                                v-if="tableMeta && selected_req"
                                v-show="activeColTab === 'this_tb'"
                                :table_id="tableMeta.id"
                                :table-meta="tableMeta"
                                :dcr-object="selected_req"
                                :with-edit="canPermisEdit()"
                                :can-adding-row="selectedGroup > -1"
                                @check-row="dcrSettCheck"
                                @subtab-change="(key) => { activeColThisTbTab = key; }"
                            ></tab-settings-requests-this-table>

                            <tab-settings-requests-linked-tables
                                v-if="tableMeta && selected_req"
                                v-show="activeColTab === 'embed'"
                                :table-meta="tableMeta"
                                :dcr-object="selected_req"
                            ></tab-settings-requests-linked-tables>
                        </div>
                    </div>

                    <div class="full-frame defaults-tab" v-show="activeRightTab === 'actions'" :style="$root.themeMainBgStyle">
                        <tab-settings-submission-row
                                v-if="selected_req"
                                :table-meta="tableMeta"
                                :table_id="tableMeta.id"
                                :cell-height="1"
                                :max-cell-rows="0"
                                :table-request="$root.settingsMeta['table_data_requests']"
                                :request-row="selectedGroup > -1 ? selected_req : {}"
                                :with_edit="canPermisEdit()"
                                @updated-cell="updateGroup"
                        ></tab-settings-submission-row>
                    </div>

                    <div class="full-frame defaults-tab" v-show="activeRightTab === 'notifs'" :style="$root.themeMainBgStyle">
                        <tab-settings-request-notifs
                                v-if="selected_req"
                                :table-meta="tableMeta"
                                :table_id="tableMeta.id"
                                :cell-height="1"
                                :max-cell-rows="0"
                                :table-request="$root.settingsMeta['table_data_requests']"
                                :request-row="selectedGroup > -1 ? selected_req : {}"
                                :with_edit="canPermisEdit()"
                                @updated-cell="updateGroup"
                                @set-sub-tab="(key) => { actNots = key; }"
                        ></tab-settings-request-notifs>
                    </div>

                </div>
            </div>
        </div>

        <slot-popup
            v-if="copyPopup"
            :popup_width="400"
            :popup_height="175"
            @popup-close="copyPopup = false"
        >
            <template v-slot:title>
                <span>Copy Settings From</span>
            </template>
            <template v-slot:body>
                <div class="p10">
                    <div class="flex flex--center-v" style="margin: 15px 0;">
                        <label class="no-margin no-wrap">Select source DCR:&nbsp;</label>
                        <select-block
                            :options="dcrOpts()"
                            :sel_value="copy_from_dcr_id"
                            :fixed_pos="true"
                            style="height: 32px"
                            @option-select="(opt) => { copy_from_dcr_id = opt.val; }"
                        ></select-block>
                    </div>
                    <button class="btn btn-default btn-sm blue-gradient pull-right"
                            :style="$root.themeButtonStyle"
                            @click="copyFullDcr()"
                    >Copy</button>
                </div>
            </template>
        </slot-popup>
    </div>
</template>

<script>
import {eventBus} from "../../../../../app";

import {SpecialFuncs} from "../../../../../classes/SpecialFuncs";
import {DcrEndpoints} from "../../../../../classes/DcrEndpoints";

import CellStyleMixin from "../../../../_Mixins/CellStyleMixin";

import CustomTable from '../../../../CustomTable/CustomTable';
import TabSettingsRequestsRow from "./TabSettingsRequestsRow";
import TabSettingsSubmissionRow from "./TabSettingsSubmissionRow";
import VerticalRowsTable from "../../../../CustomTable/VerticalRowsTable";
import TabSettingsRequestNotifs from "./TabSettingsRequestNotifs";
import TabSettingsRequestsLinkedTables from "./TabSettingsRequestsLinkedTables";
import TabSettingsAccessRow from "./TabSettingsAccessRow";
import SelectBlock from "../../../../CommonBlocks/SelectBlock";
import InfoSignLink from "../../../../CustomTable/Specials/InfoSignLink";
import TabSettingsRequestsThisTable from "./TabSettingsRequestsThisTable.vue";

export default {
    name: "TabSettingsRequests",
    components: {
        TabSettingsRequestsThisTable,
        InfoSignLink,
        SelectBlock,
        TabSettingsAccessRow,
        TabSettingsRequestsLinkedTables,
        TabSettingsRequestNotifs,
        VerticalRowsTable,
        TabSettingsSubmissionRow,
        TabSettingsRequestsRow,
        CustomTable,
    },
    mixins: [
        CellStyleMixin,
    ],
    data: function () {
        return {
            copyPopup: false,
            copy_from_dcr_id: null,
            withEdit: true,
            actDes: 'overall',
            actNots: 'submis',
            activeColTab: 'this_tb',
            activeColThisTbTab: 'col_group',
            activeRightTab: 'list',
            selectedGroup: 0,
            requestAvailable: [
                'name',
                'custom_url',
                'description',
                'pass',
                'one_per_submission',
                'permission_dcr_id',
                'active',
            ],
        }
    },
    props:{
        tableMeta: Object,
        table_id: Number|null,
        isVisible: Boolean,
    },
    computed: {
        addingRow() {
            return {
                active: this.canAddonEdit(),
                position: 'bottom',
            };
        },
        selected_req() {
            return this.tableMeta._table_requests[this.selectedGroup];
        },
        sysStyleWiBg() {
            return {
                ...this.textSysStyle,
                ...this.$root.themeMainBgStyle,
            };
        },
    },
    watch: {
        table_id: function(val) {
            this.selectedGroup = 0;
            this.activeRightTab = 'list';
        }
    },
    methods: {
        canPermisEdit() {
            return this.withEdit && this.$root.addonCanPermisEdit(this.tableMeta, this.selected_req, '_dcr_rights');
        },
        canAddonEdit() {
            return this.$root.addonCanEditGeneral(this.tableMeta, 'request');
        },
        //top select
        dcrOpts(filter) {
            let dcrs = _.filter(this.tableMeta._table_requests, (dcr) => {
                return !filter || !this.selected_req || dcr.id != this.selected_req.id;
            });
            return _.map(dcrs, (dcr) => {
                return { val:dcr.id, show:dcr.name };
            });
        },
        seleChange(opt) {
            this.selectedGroup = _.findIndex(this.tableMeta._table_requests, {id: Number(opt.val)});
        },
        getLinkHash() {
            let domain = this.$root.clear_url + '/dcr/';
            return this.selected_req && this.selected_req.link_hash
                ? domain+this.selected_req.link_hash
                : '#';
        },
        copyFullDcr(asd) {
            this.$root.sm_msg_type = 1;
            axios.post('/ajax/table-data-request/copy', {
                from_data_request_id: this.copy_from_dcr_id,
                to_data_request_id: this.selected_req.id,
                full_copy: 1,
            }).then(({ data }) => {
                if (data && _.first(data)) {
                    SpecialFuncs.assignProps(this.selected_req, _.first(data));
                    this.copy_from_dcr_id = null;
                }
            }).catch(errors => {
                Swal('Info', getErrors(errors));
            }).finally(() => {
                this.$root.sm_msg_type = 0;
            });
        },
        //
        rowIndexClickedGroup(index) {
            this.activeRightTab = 'list';
            this.selectedGroup = index;
        },
        //Table Permissions Functions
        addGroup(tableRow) {
            this.$root.sm_msg_type = 1;

            let fields = _.cloneDeep(tableRow);//copy object
            this.$root.deleteSystemFields(fields);
            fields.active = fields.active ? 1 : 0;

            axios.post('/ajax/table-data-request', {
                table_id: this.tableMeta.id,
                fields: fields,
            }).then(({ data }) => {
                this.tableMeta._table_requests.push(data);
                this.selectedGroup = -1;
            }).catch(errors => {
                Swal({
                    title: 'Info',
                    text: getErrors(errors),
                    customClass: 'no-wrap'
                });
            }).finally(() => {
                this.$root.sm_msg_type = 0;
            });
        },
        updateGroup(tableRow, changedKey) {
            DcrEndpoints.update(tableRow)
                .then(({ data }) => {
                    if (changedKey === 'dcr_record_url_field_id') {
                        this.fillDcrUrl(group_id);
                    }
                    if (changedKey === 'dcr_record_status_id') {
                        eventBus.$emit('reload-meta-table');
                    }
                    tableRow.qr_link = data.qr_link + '?v=' + uuidv4();
                });
        },
        fillDcrUrl(dcr_id) {
            axios.post('/ajax/table-data-request/dcr-url-record', {
                table_data_request_id: dcr_id,
            }).then(({ data }) => {
                eventBus.$emit('reload-page');
            });
        },
        deleteGroup(tableRow) {
            this.$root.sm_msg_type = 1;
            axios.delete('/ajax/table-data-request', {
                params: {
                    table_dcr_id: tableRow.id
                }
            }).then(({ data }) => {
                let idx = _.findIndex(this.tableMeta._table_requests, {id: Number(tableRow.id)});
                if (idx > -1) {
                    this.selectedGroup = -1;
                    this.tableMeta._table_requests.splice(idx, 1);
                }
            }).catch(errors => {
                Swal('Info', getErrors(errors));
            }).finally(() => {
                this.$root.sm_msg_type = 0;
            });
        },
        uploadGroupFile(tableRow, field, file) {
            DcrEndpoints.uploadFile(tableRow, field, file);
        },
        delGroupFile(tableRow, field) {
            DcrEndpoints.delFile(tableRow, field);
        },

        //Fields settings
        dcrSettCheck(field, val) {
            this.withEdit = false;
            DcrEndpoints.checkColumn(this.selected_req, field, val)
                .then(({ data }) => {
                    this.withEdit = true;
                });
        },

        //events
        showSettingsPoPopup() {
            eventBus.$emit('show-table-settings-all-popup', {tab:'bas_popup'});
        },
    },
    mounted() {
    },
    beforeDestroy() {
    }
}
</script>

<style lang="scss" scoped>
    @import "./TabSettingsPermissions";

    .permissions-tab {
        padding: 5px;
        height: 100%;

        .permissions-panel {
            height: 100%;
        }
    }
    .vertical-buttons {
        transform: rotate(-90deg);
        transform-origin: top right;
        right: calc(100% - 5px);
        position: absolute;
        white-space: nowrap;
        top: 5px;
        display: flex;
        flex-direction: row-reverse;

        .btn {
            outline: none;
            background-color: #CCC;
        }
        .btn.active {
            background-color: #FFF;
        }
    }
</style>