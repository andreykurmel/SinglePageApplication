<template>
    <div class="container-fluid full-height">
        <div class="row full-height permissions-tab">
            <div class="permissions-panel full-height" :style="bgColor">
                <div class="permissions-menu-header">
                    <button class="btn btn-default btn-sm" :style="textSysStyle" :class="{active : activeTab === 'sav'}" @click="changeTab('sav')">
                        Saving
                    </button>
                    <button class="btn btn-default btn-sm" :style="textSysStyle" :class="{active : activeTab === 'submis'}" @click="changeTab('submis')">
                        Submission
                    </button>
                    <button class="btn btn-default btn-sm" :style="textSysStyle" :class="{active : activeTab === 'updat'}" @click="changeTab('updat')">
                        Updating
                    </button>

                    <div class="flex flex--center-v" style="position: absolute; top: -2px; right: 5px; height: 32px;" :style="textColor">
                        <label class="no-margin">Status:&nbsp;</label>
                        <label class="switch_t">
                            <input type="checkbox" :checked="sliderKey()" :disabled="!with_edit" @click="sliderChange()">
                            <span class="toggler round" :class="{'disabled': !with_edit}"></span>
                        </label>
                    </div>
                </div>
                <div class="permissions-menu-body">

                    <div class="full-frame defaults-tab" v-show="activeTab === 'sav' && sliderKey()" :style="bgColor">
                        <tab-settings-notification-row
                                :table-meta="tableMeta"
                                :table_id="table_id"
                                :cell-height="cellHeight"
                                :max-cell-rows="maxCellRows"
                                :table-request="tableRequest"
                                :request-row="requestRow"
                                :with_edit="with_edit"
                                :prefix_fld="'dcr_save_'"
                                :bg_color="bg_color"
                                @updated-cell="updateNotif"
                                @show-add-ddl-option="linkedColPopup"
                        ></tab-settings-notification-row>
                    </div>

                    <div class="full-frame defaults-tab" v-show="activeTab === 'submis' && sliderKey()" :style="bgColor">
                        <tab-settings-notification-row
                                :table-meta="tableMeta"
                                :table_id="table_id"
                                :cell-height="cellHeight"
                                :max-cell-rows="maxCellRows"
                                :table-request="tableRequest"
                                :request-row="requestRow"
                                :with_edit="with_edit"
                                :prefix_fld="'dcr_'"
                                :bg_color="bg_color"
                                @updated-cell="updateNotif"
                                @show-add-ddl-option="linkedColPopup"
                        ></tab-settings-notification-row>
                    </div>

                    <div class="full-frame defaults-tab" v-show="activeTab === 'updat' && sliderKey()" :style="bgColor">
                        <tab-settings-notification-row
                                :table-meta="tableMeta"
                                :table_id="table_id"
                                :cell-height="cellHeight"
                                :max-cell-rows="maxCellRows"
                                :table-request="tableRequest"
                                :request-row="requestRow"
                                :with_edit="with_edit"
                                :prefix_fld="'dcr_upd_'"
                                :bg_color="bg_color"
                                @updated-cell="updateNotif"
                                @show-add-ddl-option="linkedColPopup"
                        ></tab-settings-notification-row>
                    </div>

                </div>
            </div>
        </div>

        <!--Popup for adding column links-->
        <grouping-settings-popup
            v-if="linkedMeta"
            :table-meta="linkedMeta"
            :user="$root.user"
            :init_show="'col'"
            :foreign_id="linkedColId"
            @hidden-form="linkedColClose"
        ></grouping-settings-popup>
    </div>
</template>

<script>
    import {Endpoints} from "../../../../../classes/Endpoints";

    import StyleMixinWithBg from "../../../../_Mixins/StyleMixinWithBg";

    import TabSettingsNotificationRow from "./TabSettingsNotificationRow";
    import GroupingSettingsPopup from "../../../../CustomPopup/GroupingSettingsPopup.vue";
    import SelectBlock from "../../../../CommonBlocks/SelectBlock.vue";

    export default {
        name: "TabSettingsRequestNotifs",
        components: {
            SelectBlock,
            GroupingSettingsPopup,
            TabSettingsNotificationRow
        },
        mixins: [
            StyleMixinWithBg,
        ],
        data: function () {
            return {
                activeTab: 'submis',
                linkedMeta: null,
                linkedColId: null,
            }
        },
        props:{
            tableMeta: Object,
            table_id: Number,
            cellHeight: Number,
            maxCellRows: Number,
            tableRequest: Object,
            requestRow: Object,
            with_edit: Boolean,
            bg_color: String,
        },
        watch: {
            table_id: function(val) {
                this.changeTab('submis');
            }
        },
        methods: {
            notifKey() {
                switch (this.activeTab) {
                    case 'sav': return 'dcr_save_active_notif';
                    case 'submis': return 'dcr_active_notif';
                    case 'updat': return 'dcr_upd_active_notif';
                }
            },
            sliderKey() {
                return this.requestRow[this.notifKey()];
            },
            sliderChange() {
                this.requestRow[this.notifKey()] = this.requestRow[this.notifKey()] ? 0 : 1;
                this.updateNotif(this.requestRow);
            },
            changeTab(key) {
                this.activeTab = key;
                this.$emit('set-sub-tab', key);
            },
            updateNotif(requestRow) {
                this.$emit('updated-cell', requestRow);
            },
            linkedColPopup(tableId, relatedColGroupId) {
                $.LoadingOverlay('show');
                Endpoints.loadTbHeaders(tableId)
                    .then((data) => {
                        this.linkedMeta = data;
                        this.linkedColId = relatedColGroupId;
                    }).finally(() => {
                        $.LoadingOverlay('hide');
                    });
            },
            linkedColClose() {
                this.linkedMeta = null;
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
    .btn-default {
        height: 30px;
    }

</style>