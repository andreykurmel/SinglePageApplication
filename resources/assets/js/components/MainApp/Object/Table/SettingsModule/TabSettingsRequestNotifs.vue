<template>
    <div class="container-fluid full-height">
        <div class="row full-height permissions-tab">
            <div class="permissions-panel full-height">
                <div class="permissions-menu-header">
                    <button class="btn btn-default btn-sm" :class="{active : activeTab === 'sav'}" @click="activeTab = 'sav'">
                        Saving
                    </button>
                    <button class="btn btn-default btn-sm" :class="{active : activeTab === 'submis'}" @click="activeTab = 'submis'">
                        Submission
                    </button>
                    <button class="btn btn-default btn-sm" :class="{active : activeTab === 'updat'}" @click="activeTab = 'updat'">
                        Updating
                    </button>
                </div>
                <div class="permissions-menu-body">

                    <div class="full-frame defaults-tab" v-show="activeTab === 'sav'">
                        <tab-settings-notification-row
                                :table_id="table_id"
                                :cell-height="cellHeight"
                                :max-cell-rows="maxCellRows"
                                :table-request="tableRequest"
                                :request-row="requestRow"
                                :with_edit="with_edit"
                                :prefix_fld="'dcr_save_'"
                                @updated-cell="updateNotif"
                        ></tab-settings-notification-row>
                    </div>

                    <div class="full-frame defaults-tab" v-show="activeTab === 'submis'">
                        <tab-settings-notification-row
                                :table_id="table_id"
                                :cell-height="cellHeight"
                                :max-cell-rows="maxCellRows"
                                :table-request="tableRequest"
                                :request-row="requestRow"
                                :with_edit="with_edit"
                                :prefix_fld="'dcr_'"
                                @updated-cell="updateNotif"
                        ></tab-settings-notification-row>
                    </div>

                    <div class="full-frame defaults-tab" v-show="activeTab === 'updat'">
                        <tab-settings-notification-row
                                :table_id="table_id"
                                :cell-height="cellHeight"
                                :max-cell-rows="maxCellRows"
                                :table-request="tableRequest"
                                :request-row="requestRow"
                                :with_edit="with_edit"
                                :prefix_fld="'dcr_upd_'"
                                @updated-cell="updateNotif"
                        ></tab-settings-notification-row>
                    </div>

                </div>
            </div>
        </div>
    </div>
</template>

<script>
    import TabSettingsNotificationRow from "./TabSettingsNotificationRow";

    export default {
        name: "TabSettingsRequestNotifs",
        components: {
            TabSettingsNotificationRow
        },
        data: function () {
            return {
                activeTab: 'submis',
            }
        },
        props:{
            table_id: Number,
            cellHeight: Number,
            maxCellRows: Number,
            tableRequest: Object,
            requestRow: Object,
            with_edit: Boolean,
        },
        watch: {
            table_id: function(val) {
                this.activeTab = 'submis';
            }
        },
        methods: {
            updateNotif(requestRow) {
                this.$emit('updated-cell', requestRow);
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
</style>